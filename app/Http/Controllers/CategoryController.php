<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use App\Models\Category;


class CategoryController extends Controller
{

    public function index(){

        try{
             $categories = Category::get();

            if($categories){

                return response()->json(['success' => 'success', 'message' => 'categories retrieved successfully', 'data' => ['categories' => $categories] ], 200);
            }

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not fetch categories' . $e->getMessage()], 500);

        }

    }

    public function fetchCategory($category_id){

        try{
             $category = Category::where('id',$category_id)->first();

            if( $category){

                return response()->json(['success' => 'success', 'message' => 'Category retrieved successfully', 'data' => ['category' =>  $category] ], 200);

            }else{

                return response()->json(['success' => 'error', 'message' => 'Category does not exist'], 400);
            }

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not fetch category' . $e->getMessage()], 500);

        }

    }

    public function create(Request $request){

        $request->validate(['name' => 'required|string']);
        $name = strtolower($request->name);

        DB::beginTransaction();

        try {

            $check_category = Category::where('name',$name)->first();

            if ($check_category){

                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'category already exist'], 400);

            }else{

                $category=Category::create(['name' => $request->name]);
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Category created successfully',
                    'data' => ['category' => $category]], 200);
            }

        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not create category. ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request){

        $data = $request->validate([

            'name' => 'required|string',
            'category_id' =>'required|string'
        ]);

        $name = strtolower($request->name);

        DB::beginTransaction();

        try {

            $category = Category::where('id',$request->category_id)->first();

            if ($category){

                 $category->update($data);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Category updated successfully',
                    'data' => ['name' => $name]], 200);
            }else{
                // Rollback if any step fails
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Category does not exist'], 403);
            }

        } catch (\Exception $e) {
            // Rollback in case of exception
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not update category . ' . $e->getMessage()], 500);
        }
    }

    public function updateCategoryImage(Request $request){

        $request->validate([

            'image' => 'required|mimes:jpeg,png,jpg,svg|max:5048',
            'category_id' =>'required|string'
        ]);

        DB::beginTransaction();

        try{

            $category = Category::where('id',$request->category_id)->first();

            $picture_path = $request->file('image')->store('categoryImages', 'public');

            $pic_url = env('WEB_URL') . 'storage/' . $picture_path;


            if($category){

                $update = $category->update([ "image" => $pic_url]);
                DB::commit();
                if($update){

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Image uploaded successfully',
                        'data' => ['image' => $pic_url]], 200);
                }else{
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Unauthorized action'], 400);
                }

            }else{
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Category does not exist'], 400);

            }

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'An error occurred .' . $e->getMessage()], 500);
        }

    }


    public function delete($category_id)
    {

        DB::beginTransaction();

        try {

            $category = Category::where('id',$category_id)->first();

            if ($category) {

                $isLinked = DB::table('products')->where('category_id', $category_id)->exists();

                if ($isLinked) {

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Category cannot be deleted,products are listed under it.'
                    ], 400);
                }

                $category->delete();
                DB::commit();
                return response()->json(['status' => 'success','message' => 'Category deleted successfully'], 200);
            } else {
                DB::rollback();
                return response()->json(['status' => 'error','message' => 'Category could not found '],400);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()], 500);
        }

    }

}
