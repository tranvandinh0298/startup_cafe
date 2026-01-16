<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductAvailabilityService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $productAvailabilityService;

    public function __construct(ProductAvailabilityService $productAvailabilityService)
    {
        $this->productAvailabilityService = $productAvailabilityService;
    }

    public function index()
    {
        $availability = $this->productAvailabilityService->getAvailability();
        $products = collect(Product::recordActive()->with('category')->get())
            ->map(function ($item) use ($availability) {
                $item->availability = 4; 
                // $availability[$item->id] ?? 0;
                return $item;
            });

        return view('pos.index', [
            'categories' => Category::recordActive()->get(),
            'products' => $products,
            'availability' => $availability
        ]);
    }
}
