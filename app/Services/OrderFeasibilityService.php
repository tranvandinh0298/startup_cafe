<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\InventoryLot;
use App\Models\OrderItem;
use App\Models\ProductIngredient;

class OrderFeasibilityService
{
    // Config knobs (tune later)
    private int $lowServingsThreshold = 3;     // “low” stock for UI
    private float $riskyUtilization = 0.90;    // >90% of total_possible => risky
    private int $splitMaxPerTicket = 40;

    /**
     * Analyze feasibility for a draft order payload.
     * $items = [
     *   ['product_id' => 1, 'quantity' => 2],
     *   ...
     * ]
     */

    public function analyzeItems(array $items): array
    {
        $normalized = $this->normalizeItems($items);

        $required = $this->buildRequiredMap($normalized); // ingredient_id => base_units

        $stock = $this->loadStockSnapshots(array_keys($required)); // open + unopened + package_size

        $ingredientIssues = $this->buildIngredientIssues($required, $stock);

        $productCaps = $this->buildProductCaps($normalized, $stock);

        $mode = $this->deriveMode($ingredientIssues);
        $summary = $this->buildSummary($ingredientIssues);

        $suggestions = $this->buildSuggestions($normalized, $ingredientIssues, $productCaps, $summary);

        return [
            'feasible' => $mode !== ORDER_FEASIBILITY_IMPOSSIBLE,
            'mode' => $mode,
            'summary' => $summary,
            'ingredient_issues' => $ingredientIssues,
            'product_caps' => $productCaps,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Optional: analyze by order_id (loads order_items).
     */
    public function analyzeOrder(int $orderId): array
    {
        $items = OrderItem::where('order_id', $orderId)
            ->get(['product_id', 'quantity'])
            ->map(fn($x) => ['product_id' => (int)$x->product_id, 'quantity' => (int)$x->quantity])
            ->all();

        return $this->analyzeItems($items);
    }

    private function normalizeItems(array $items): array
    {
        // Merge duplicates: same product_id -> sum quantity
        $map = [];
        foreach ($items as $it) {
            $pid = (int)($it['product_id'] ?? 0);
            $qty = (int)($it['quantity'] ?? 0);
            if ($pid <= 0 || $qty <= 0) {
                continue;
            }
            $map[$pid] = ($map[$pid] ?? 0) + $qty;
        }

        $normalized = [];
        foreach ($map as $pid => $qty) {
            $normalized[] = ['product_id' => $pid, 'quantity' => $qty];
        }
        return $normalized;
    }

    private function buildRequiredMap(array $items): array
    {
        $productIds = array_values(array_unique(array_map(fn($i) => $i['product_id'], $items)));

        $recipes = ProductIngredient::whereIn('product_id', $productIds)
            ->get(['product_id', 'ingredient_id', 'quantity_per_unit'])
            ->groupBy('product_id');

        $required = []; // ingredient_id => base_units

        foreach ($items as $item) {
            $pid = $item['product_id'];
            $qty = $item['quantity'];

            $list = $recipes->get($pid, collect());
            foreach ($list as $r) {
                $iid = (int)$r->ingredient_id;
                $per = (int)$r->quantity_per_unit;
                $required[$iid] = ($required[$iid] ?? 0) + ($qty * $per);
            }
        }

        return $required;
    }

    /**
     * Load open stock, unopened packages, package_size for the ingredients involved.
     */
    private function loadStockSnapshots(array $ingredientIds): array
    {
        // open stock in base units
        $open = InventoryBatch::whereIn('ingredient_id', $ingredientIds)
            ->where('status', PACKAGE_STATUS_OPEN)
            ->where('expired_at', '>', now())
            ->selectRaw('ingredient_id, SUM(remaining_quantity_base) as sum_remaining')
            ->groupBy('ingredient_id')
            ->pluck('sum_remaining', 'ingredient_id')
            ->map(fn($v) => (int)$v)
            ->all();

        // unopened packages count
        $unopened = InventoryLot::whereIn('ingredient_id', $ingredientIds)
            ->where('quantity_packages', '>', 0)
            ->where('expired_at', '>', now()) // ignore expired lots
            ->selectRaw('ingredient_id, SUM(quantity_packages) as sum_packages')
            ->groupBy('ingredient_id')
            ->pluck('sum_packages', 'ingredient_id')
            ->map(fn($v) => (int)$v)
            ->all();

        // package size per ingredient
        $packageSize = Ingredient::whereIn('id', $ingredientIds)
            ->pluck('package_size', 'id')
            ->map(fn($v) => (int)$v)
            ->all();

        return [
            'open' => $open,
            'unopened' => $unopened,
            'package_size' => $packageSize,
        ];
    }

    private function buildIngredientIssues(array $required, array $stock): array
    {
        $issues = [];

        foreach ($required as $ingredientId => $need) {
            $openAvail = $stock['open'][$ingredientId] ?? 0;
            $unopenedPk = $stock['unopened'][$ingredientId] ?? 0;
            $pkgSize = $stock['package_size'][$ingredientId] ?? 0;

            $totalPossible = $openAvail + ($unopenedPk * $pkgSize);

            $status = ORDER_FEASIBILITY_OK;
            if ($need > $totalPossible) {
                $status = ORDER_FEASIBILITY_IMPOSSIBLE;
            } elseif ($need > $openAvail) {
                $status = ORDER_FEASIBILITY_REQUIRE_OPEN;
            }

            $servingsLeftOpen = $pkgSize > 0
                ? intdiv($openAvail, $pkgSize) // rough proxy
                : 0;

            $isLowOperational = $servingsLeftOpen <= $this->lowServingsThreshold;

            // risky if utilization is too high (but still possible)
            if ($status !== ORDER_FEASIBILITY_IMPOSSIBLE && $totalPossible > 0) {
                $util = $need / $totalPossible;
                if ($util >= $this->riskyUtilization) {
                    $status = ORDER_FEASIBILITY_RISKY;
                }
            }

            $openBatchesNeededMin = null;
            if ($need > $openAvail && $pkgSize > 0 && $unopenedPk > 0) {
                $missing = max(0, $need - $openAvail);
                $openBatchesNeededMin = (int)ceil($missing / $pkgSize);
                // cap by available unopened packages
                if ($openBatchesNeededMin > $unopenedPk) {
                    $openBatchesNeededMin = $unopenedPk;
                }
            }

            $issues[] = [
                'ingredient_id' => (int)$ingredientId,
                'required' => (int)$need,
                'open_available' => (int)$openAvail,
                'unopened_packages' => (int)$unopenedPk,
                'package_size' => (int)$pkgSize,
                'total_possible' => (int)$totalPossible,
                'status' => $status,
                'open_batches_needed_min' => $openBatchesNeededMin,
                'operational_low' => $isLowOperational,
            ];
        }

        // Sorting: show worst first for UI
        $rank = [
            ORDER_FEASIBILITY_IMPOSSIBLE => 0,
            ORDER_FEASIBILITY_RISKY => 1,
            ORDER_FEASIBILITY_REQUIRE_OPEN => 2,
            ORDER_FEASIBILITY_OK => 3
        ];
        usort($issues, fn($a, $b) => $rank[$a['status']] <=> $rank[$b['status']]);

        return $issues;
    }

    /**
     * For each product, compute max quantity possible by open-only and by total-possible.
     * This is what you use to suggest modifications like “max 42 cups”.
     */
    private function buildProductCaps(array $items, array $stock): array
    {
        $productIds = array_values(array_unique(array_map(fn($i) => $i['product_id'], $items)));

        $recipesByProduct = ProductIngredient::whereIn('product_id', $productIds)
            ->get(['product_id', 'ingredient_id', 'quantity_per_unit'])
            ->groupBy('product_id');

        $caps = [];

        foreach ($items as $item) {
            $pid = $item['product_id'];
            $requested = $item['quantity'];

            $recipes = $recipesByProduct->get($pid, collect());
            if ($recipes->isEmpty()) {
                // No recipe means not trackable or misconfigured; treat as unlimited (or 0) based on your policy.
                $caps[] = [
                    'product_id' => $pid,
                    'requested_qty' => $requested,
                    'max_open_only' => PHP_INT_MAX,
                    'max_total_possible' => PHP_INT_MAX,
                    'status' => 'OK',
                ];
                continue;
            }

            $limitsOpen = [];
            $limitsTotal = [];

            foreach ($recipes as $r) {
                $iid = (int)$r->ingredient_id;
                $per = (int)$r->quantity_per_unit;

                $openAvail = $stock['open'][$iid] ?? 0;
                $unopenedPk = $stock['unopened'][$iid] ?? 0;
                $pkgSize = $stock['package_size'][$iid] ?? 0;
                $totalPossible = $openAvail + ($unopenedPk * $pkgSize);

                $limitsOpen[] = ($per > 0) ? intdiv($openAvail, $per) : PHP_INT_MAX;
                $limitsTotal[] = ($per > 0) ? intdiv($totalPossible, $per) : PHP_INT_MAX;
            }

            $maxOpen = min($limitsOpen);

            $lowStock = $maxOpen <= $this->lowServingsThreshold;

            $maxTotal = min($limitsTotal);

            $status = ORDER_FEASIBILITY_OK;
            if ($requested > $maxTotal) $status = ORDER_FEASIBILITY_IMPOSSIBLE;
            elseif ($requested > $maxOpen) $status = ORDER_FEASIBILITY_REQUIRE_OPEN;

            $caps[] = [
                'product_id' => $pid,
                'requested_qty' => $requested,
                'max_open_only' => (int)$maxOpen,
                'max_total_possible' => (int)$maxTotal,
                'low_stock' => $lowStock,
                'status' => $status,
            ];
        }

        return $caps;
    }

    private function deriveMode(array $ingredientIssues): string
    {
        $hasImpossible = false;
        $hasRisky = false;
        $hasRequiresOpen = false;

        foreach ($ingredientIssues as $i) {
            if ($i['status'] === ORDER_FEASIBILITY_IMPOSSIBLE) $hasImpossible = true;
            elseif ($i['status'] === ORDER_FEASIBILITY_RISKY) $hasRisky = true;
            elseif ($i['status'] === ORDER_FEASIBILITY_REQUIRE_OPEN) $hasRequiresOpen = true;
        }

        if ($hasImpossible) return ORDER_FEASIBILITY_IMPOSSIBLE;
        if ($hasRisky) return ORDER_FEASIBILITY_RISKY;
        if ($hasRequiresOpen) return ORDER_FEASIBILITY_REQUIRE_OPEN;
        return ORDER_FEASIBILITY_OK;
    }

    private function buildSummary(array $ingredientIssues): array
    {
        $openOnlyOk = true;
        $totalPossibleOk = true;

        $utilizations = [];

        foreach ($ingredientIssues as $i) {
            $need = $i['required'];
            $open = $i['open_available'];
            $total = $i['total_possible'];

            if ($need > $open) $openOnlyOk = false;
            if ($need > $total) $totalPossibleOk = false;

            if ($total > 0) $utilizations[] = $need / $total;
        }

        $riskScore = empty($utilizations) ? 0.0 : max($utilizations);

        return [
            'open_only_ok' => $openOnlyOk,
            'total_possible_ok' => $totalPossibleOk,
            'risk_score' => round($riskScore, 4),
        ];
    }

    private function buildSuggestions(array $items, array $ingredientIssues, array $productCaps, array $summary): array
    {
        $suggestions = [];

        // 1) If impossible: suggest per-product caps
        $hasImpossible = collect($ingredientIssues)->contains(fn($i) => $i['status'] === ORDER_FEASIBILITY_IMPOSSIBLE);

        if ($hasImpossible) {
            foreach ($productCaps as $cap) {
                if ($cap['status'] === ORDER_FEASIBILITY_IMPOSSIBLE) {
                    $suggestions[] = [
                        'type' => 'reduce_product',
                        'product_id' => $cap['product_id'],
                        'requested_qty' => $cap['requested_qty'],
                        'max_qty' => $cap['max_total_possible'],
                        'message' => "Reduce product {$cap['product_id']} to <= {$cap['max_total_possible']}.",
                    ];
                }
            }

            $suggestions[] = [
                'type' => 'split_order',
                'recommended_size' => $this->splitMaxPerTicket,
                'message' => "Split the order into smaller tickets (e.g., {$this->splitMaxPerTicket} items per ticket).",
            ];

            return $suggestions;
        }

        // 2) If requires open: suggest which ingredients to open and how many packages (minimum)
        foreach ($ingredientIssues as $i) {
            if (in_array($i['status'], [ORDER_FEASIBILITY_REQUIRE_OPEN, ORDER_FEASIBILITY_RISKY], true) && ($i['open_batches_needed_min'] ?? null)) {
                $suggestions[] = [
                    'type' => 'open_batches',
                    'ingredient_id' => $i['ingredient_id'],
                    'packages_to_open_min' => $i['open_batches_needed_min'],
                    'unopened_packages' => $i['unopened_packages'],
                    'message' => "Open {$i['open_batches_needed_min']} package(s) for ingredient {$i['ingredient_id']}.",
                ];
            }

            if (
                $i['status'] === ORDER_FEASIBILITY_OK
                && ($i['operational_low'] ?? false)
                && ($i['unopened_packages'] ?? 0) > 0
            ) {
                $suggestions[] = [
                    'type' => 'preemptive_open',
                    'ingredient_id' => $i['ingredient_id'],
                    'packages_to_open_min' => 1,
                    'message' => "Low stock detected; consider opening 1 package in advance.",
                ];
            }
        }

        // 3) Risky: suggest “adjust to practical” (leave buffer)
        if ($summary['risk_score'] >= $this->riskyUtilization) {
            // Suggest caps with buffer: max_open_only or max_total_possible * 0.9
            foreach ($productCaps as $cap) {
                if ($cap['requested_qty'] <= 0 || $cap['max_total_possible'] === PHP_INT_MAX) continue;

                $buffered = (int)floor($cap['max_total_possible'] * 0.9);
                if ($cap['requested_qty'] > $buffered) {
                    $suggestions[] = [
                        'type' => 'reduce_for_buffer',
                        'product_id' => $cap['product_id'],
                        'requested_qty' => $cap['requested_qty'],
                        'recommended_qty' => max(0, $buffered),
                        'message' => "Recommended to reduce product {$cap['product_id']} to {$buffered} to keep buffer.",
                    ];
                }
            }

            $suggestions[] = [
                'type' => 'confirm_risky',
                'message' => "This order is feasible but risky (very tight inventory). Confirm if you want to proceed.",
            ];
        }

        // 4) If requires open for some products: show per-product caps open-only
        foreach ($productCaps as $cap) {
            if ($cap['status'] === ORDER_FEASIBILITY_REQUIRE_OPEN) {
                $suggestions[] = [
                    'type' => 'product_requires_open',
                    'product_id' => $cap['product_id'],
                    'max_open_only' => $cap['max_open_only'],
                    'message' => "Product {$cap['product_id']} exceeds open stock; max without opening is {$cap['max_open_only']}.",
                ];
            }
        }

        // Optional: split large orders even if feasible
        $totalQty = array_sum(array_map(fn($i) => $i['quantity'], $items));
        if ($totalQty > $this->splitMaxPerTicket * 2) {
            $suggestions[] = [
                'type' => 'split_order',
                'recommended_size' => $this->splitMaxPerTicket,
                'message' => "Large order detected; consider splitting into {$this->splitMaxPerTicket}-item tickets for operations.",
            ];
        }

        return $suggestions;
    }
}
