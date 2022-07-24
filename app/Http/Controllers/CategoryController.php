<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\ProductCollection;
use App\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('sort')) {
            $sort = $request->input('sort');
            if ($sort == 'true') {
                $cat = Category::productsSorted();
                return CategoryCollection::collection($cat);
            } else {
                return response()->json([
                    'status' => 'Not Found',
                    'message' => 'Wrong Parameter'
                ], 404);
            }
        }
        $category = Category::all();
        // $cat = Category::find(1);
        // return dd($cat->products);
        return CategoryCollection::collection($category);
    }

    public function products(Request $request)
    {
        if ($request->has('sort')) {
            $sort = $request->input('sort');
            if ($sort == 'true') {
                $product = Product::orderBy('price', 'desc')->get();
                return ProductCollection::collection($product);
            } else {
                return response()->json([
                    'status' => 'Not Found',
                    'message' => 'Wrong Parameter'
                ], 404);
            }
        }
        $products = Product::all();
        return ProductCollection::collection($products);
    }
}
