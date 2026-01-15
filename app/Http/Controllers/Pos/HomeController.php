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
        return view('pos.index', [
            'categories' => Category::recordActive()->get(),
            'products' => Product::recordActive()->with('category')->get(),
            'availability' => $this->productAvailabilityService->getAvailability()
        ]);
    }
}
