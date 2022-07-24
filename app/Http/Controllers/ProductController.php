<?php

namespace App\Http\Controllers;

use App\Asset;
use App\Http\Requests\ValidateApi;
use App\Http\Resources\AssetCollection;
use App\Http\Resources\ProductCollection;
use App\Product;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\VarDumper\Cloner\Data;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ValidateApi $request)
    {
        $name = $request->input('name');
        $price = $request->input('price');
        $category = $request->input('category_id');
        $images = [];

        foreach ($request->file('images') as $index => $file) {
            $mime = str_replace('image/', '.', $file->getMimeType());
            $image_new_name = Str::slug($name) . '_' . $index . $mime;
            $images[] = $image_new_name;
            $file->move('uploads/images/', $image_new_name);
        }

        $data = [
            'name' => $name,
            'slug' => Str::slug($name),
            'category_id' => $category,
            'price' => $price,
            'images' => $images
        ];

        $product = new Product;
        $product->name = $data['name'];
        $product->slug = $data['slug'];
        $product->price = $data['price'];
        $product->category_id = $data['category_id'];

        $saved_p = $product->save();

        $saved_a = [];
        foreach ($images as $img) {
            $asset = new Asset;
            $asset->product_id = $product->id;
            $asset->image = $img;
            if (!$asset->save()) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Assets not inserted'
                ], 500);
            }
        }

        if (!$saved_p) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Products not inserted'
            ], 500);
        }
        return response()->json([
            'status' => 'Success',
            'message' => 'Data inserted'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        $name = $request->input('name');
        $slug = Str::slug($name);
        $category_id = $request->input('category_id');
        $price = $request->input('price');

        $product->name = $name == '' ? $product->name : $name;
        $product->slug = $slug;
        $product->price =  $price == '' ? $product->price : $price;
        $product->category_id = $category_id == '' ? $product->category_id : $category_id;

        $saved_p = $product->save();
        if (!$saved_p) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Products not inserted'
            ], 500);
        }
        return response()->json([
            'status' => 'Success',
            'message' => 'Data inserted'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        $assets = $product->assets;

        foreach ($assets as $img) {
            $path = public_path('uploads/images/' . $img->image);
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $product->delete();


        return response()->json([
            'status' => 'Success',
            'message' => 'Product Deleted'
        ], 200);
    }

    public function addAssets(Request $request)
    {
        $image = $request->file('images');
        $product_id = $request->input('product_id');

        if ($image == null) {
            return response()->json([
                'status' => 'Fail',
                'message' => 'Please add an image'
            ], 422);
        }

        if ($product_id == '') {
            return response()->json([
                'status' => 'Fail',
                'message' => 'Please input a product id'
            ], 422);
        }

        $name = Product::find($product_id)->name;
        $count = count(Asset::where('product_id', $product_id)->get());

        $mime = str_replace('image/', '.', $image->getMimeType());
        $image_new_name = Str::slug($name) . '_' . $count . $mime;
        $assets = new Asset;
        $assets->product_id = $product_id;
        $assets->image = $image_new_name;
        $saved = $assets->save();

        if ($saved) {

            $image->move('uploads/images/', $image_new_name);

            return response()->json([
                'status' => 'Success',
                'message' => 'Image successfully added'
            ], 200);
        }

        return response()->json([
            'status' => 'Failed',
            'Message' => 'Fail to upload image'
        ], 400);
    }

    public function deleteAssets($id)
    {
        $asset = Asset::find($id);
        if ($asset == null) {
            return response()->json(
                [
                    'status' => 'Fail',
                    'message' => 'Asset not found'
                ],
                500
            );
        }

        $path = public_path('uploads/images/' . $asset->image);

        if (File::exists($path)) {
            File::delete($path);

            $asset->delete();
            return response()->json([
                'status' => 'Success',
                'message' => 'Assets successfully deleted'
            ], 200);
        } else {
            return response()->json([
                'status' => 'Fail',
                'message' => 'fail to delete'
            ], 404);
        }
    }
}
