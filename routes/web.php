<?php

use App\Http\Controllers\Pos\HomeController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
    // return view('welcome', [
    //     'products' => Product::all()
    // ]);
//     return Product::all();
// });

Route::get('/', [HomeController::class, 'index']);
