<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\OrderItem;

class ProductController extends Controller
{

    public function index(){

        try{
             $products = Product::with('category','seller')->where('status',true)->get();

            if($products){

                return response()->json(['status' => 'success', 'message' => 'products retrieved successfully', 'data' => ['products' => $products] ], 200);
            }

        }catch(\Exception $e){

            return response()->json(['status' => 'error', 'message' => 'Could not fetch products' . $e->getMessage()], 500);

        }

    }

    public function fetchMyProducts(){

        try{
             $products = Product::with('category')->where('seller_id',auth()->user()->id)->get();

            if($products){

                return response()->json(['status' => 'success', 'message' => 'My products retrieved successfully', 'data' => ['products' => $products] ], 200);
            }

        }catch(\Exception $e){

            return response()->json(['status' => 'error', 'message' => 'Could not fetch products' . $e->getMessage()], 500);

        }

    }

    public function fetchProductsByCategory($category_id){

        try{
             $products = Product::with('seller')->where('category_id',$category_id)->where('status',true)->get();

            if($products){

                return response()->json(['status' => 'success', 'message' => 'products retrieved successfully', 'data' => ['products' =>  $products] ], 200);

            }else{

                return response()->json(['status' => 'error', 'message' => 'products does not exist'], 400);
            }

        }catch(\Exception $e){

            return response()->json(['status' => 'error', 'message' => 'Could not fetch products' . $e->getMessage()], 500);

        }

    }

    public function fetchPopularProducts(){

        try {

            $popularProducts = OrderItem::groupBy('product_id')->havingRaw('COUNT(*) >=5')->pluck('product_id');


            if($popularProducts->isNotEmpty()){

                $products = Product::where('id',$popularProducts)->get();

            }else{

                return response()->json([
                    'status' => "error",
                    'message' => 'No popular products found'
                ], 400);

            }



            if($products->isNotEmpty()){
                return response()->json([
                    'status' => "success",
                    'message' => 'Popular products retrieved successfully',
                    'data' => ['products' => $products]
                ], 200);
            } else {
                return response()->json([
                    'status' => "error",
                    'message' => 'No popular products found'
                ], 400);
            }

        } catch(\Exception $e){
            return response()->json(['success' => false,'message' => 'Could not fetch popular products: ' . $e->getMessage()], 500);
        }
    }

    public function fetchRecentProducts(){

        try {

            $products = Product::with('category')->orderBy('created_at', 'desc')->limit(50)->get();

            if($products){
                return response()->json([
                    'status' => "success",
                    'message' => 'Popular products retrieved successfully',
                    'data' => ['products' => $products]
                ], 200);
            }

        } catch(\Exception $e){
            return response()->json(['success' => false,'message' => 'Could not fetch recent products: ' . $e->getMessage()], 500);
        }
    }

    public function fetchProduct($product_id){

        try{
             $product = Product::with('category','reviews.user','seller')->where('id',$product_id)->first();

            if($product){

                return response()->json(['status' => 'success', 'message' => 'Product retrieved successfully', 'data' => ['product' =>  $product] ], 200);

            }else{

                return response()->json(['status' => 'error', 'message' => 'Product does not exist'], 400);
            }

        }catch(\Exception $e){

            return response()->json(['status' => 'error', 'message' => 'Could not fetch product' . $e->getMessage()], 500);

        }

    }

    public function create(Request $request){

        $request->validate([

            'name' => 'required|string',
            'category_id' => 'required|string',
            'unit_price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'inventory' => 'nullable|numeric',
            'description' => 'nullable|string',
            'weight' => 'nullable|string',
            'specification' => 'nullable|string',
            'color' => 'nullable|string',
            'images.*' => 'nullable|mimes:jpeg,png,jpg|max:5048',
            'thumbnail' => 'required|mimes:jpeg,png,jpg|max:5048',

        ]);

        DB::beginTransaction();

        try {

            $images=[];

            if ($request->hasFile('images')) {


                foreach ($request->file('images') as $image) {

                    $picture_path = $image->store('productImages', 'public');
                    $pic_url = env('WEB_URL') . '/storage/' . $picture_path;

                    $images[] = $pic_url;

                }



            }

            if ($request->hasFile('thumbnail')) {

                $thumbnail_path = $request->file('thumbnail')->store('productThumbnails', 'public');
                $thumbnail_url = env('WEB_URL') . '/storage/' . $thumbnail_path;


            }


            $product =Product::create([

                'name' => $request->name,
                'seller_id' => auth()->user()->id,
                'category_id' => $request->category_id,
                'unit_price' => $request->unit_price,
                'discount' => $request->discount,
                'inventory' => $request->inventory,
                'description' => $request->description,
                'weight' => $request->weight,
                'specification' => $request->specification,
                'thumbnail' => $thumbnail_url,
                'images' => $images,
                'color' => $request->color,
                'status' => true,
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully',
                'data' => ['product' => $product]], 200);


        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not create category. ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request){

        $data = $request->validate([
            'product_id' =>'required|string',
            'name' => 'required|string',
            'weight' => 'nullable|string',
            'specification' => 'nullable|string',
            'color' => 'nullable|string',
            'category_id' =>'required|string',
            'unit_price' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'inventory' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);


        DB::beginTransaction();

        try {

            $product = Product::where('id',$request->product_id)->first();

            if ($product){

                $product->update($data);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Product updated successfully',
                    'data' => ['product' => $product]], 200);
            }else{
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Unauthorized action'], 403);
            }

        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not update product . ' . $e->getMessage()], 500);
        }
    }

    public function updateImages(Request $request, $id) {
        $request->validate([
            'images.*' => 'nullable|mimes:jpeg,png,jpg|max:5048',
        ]);

        DB::beginTransaction();

        try {

            $product = Product::findOrFail($id);


            $existingImages = $product->images ?? [];


            if ($request->hasFile('images')) {
                $newImages = [];


                foreach ($request->file('images') as $image) {
                    $picture_path = $image->store('productImages', 'public');
                    $pic_url = env('WEB_URL') . '/storage/' . $picture_path;
                    $newImages[] = $pic_url;
                }


                $allImages = array_merge($existingImages, $newImages);


                $product->images = $allImages;
                $product->save();
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Product images updated successfully',
                'data' => ['product' => $product]
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not update product images. ' . $e->getMessage()], 500);
        }
    }

    public function deleteImage(Request $request, $product_id){
        $request->validate([
            'image_url' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($product_id);
            $image_url = $request->image_url;


            $images = $product->images;
            $index = array_search($image_url, $images);

            if ($index !== false) {

                unset($images[$index]);


                $images = array_values($images);


                $path = str_replace(env('WEB_URL') . '/storage/', '', $image_url);
                Storage::disk('public')->delete($path);


                $product->images = $images;
                $product->save();

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Product image deleted successfully',
                    'data' => ['product' => $product]
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Image not found in product',
                ], 404);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not delete product image. ' . $e->getMessage()], 500);
        }
    }

    public function updateThumbnail(Request $request, $id){
        $request->validate([
            'thumbnail' => 'nullable|mimes:jpeg,png,jpg|max:5048',
        ]);

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);


            if ($request->hasFile('thumbnail')) {

                $old_thumbnail_path = str_replace(env('WEB_URL') . '/storage/', '', $product->thumbnail);
                Storage::disk('public')->delete($old_thumbnail_path);

                $thumbnail_path = $request->file('thumbnail')->store('productThumbnails', 'public');
                $thumbnail_url = env('WEB_URL') . '/storage/' . $thumbnail_path;
                $product->thumbnail = $thumbnail_url;
                $product->save();
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Product thumbnail updated successfully',
                'data' => ['product' => $product]
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not update product thumbnail. ' . $e->getMessage()], 500);
        }
    }

    public function search($search_term){
        try {

            $search_term = strtolower($search_term);

            $products = Product::where('status',true)->where('name', 'like', '%' . $search_term . '%')->orWhere('description', 'like', '%' . $search_term . '%')->get();

            if($products){

                return response()->json(['status' => 'success','message' => 'Product search successfull','data' => ['products' => $products]], 200);
            }

        } catch (\Exception $e) {

                return response()->json(['status' => 'error', 'message' => 'Could not find request. ' . $e->getMessage()], 500);
        }
    }

    public function delete($product_id)
    {

        DB::beginTransaction();

        try {

            $product = Product::where('id',$product_id)->first();

            if ($product) {

                $isLinkedToOrder = DB::table('order_items')->where('product_id', $product_id)->exists();

                if ($isLinkedToOrder) {

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Product cannot be delisted, it is linked to a transaction kindly delist'
                    ], 400);
                }

                $product->delete();
                DB::commit();
                return response()->json(['status' => 'success','message' => 'Product deleted successfully'], 200);
            } else {
                DB::rollback();
                return response()->json(['status' => 'error','message' => 'Product not found '],400);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()], 500);
        }

    }

    public function delist($product_id){


        DB::beginTransaction();

        try {

            $product = Product::where('id',$product_id)->first();

            if ($product){

                $product->update(['status' => false]);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Product delisted successfully',
                    'data' => ['product' => $product]], 200);
            }else{
                // Rollback if any step fails
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Unauthorized action'], 403);
            }

        } catch (\Exception $e) {
            // Rollback in case of exception
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not delist product . ' . $e->getMessage()], 500);
        }
    }


}


