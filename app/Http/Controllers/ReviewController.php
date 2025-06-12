<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use App\Models\Review;
use App\Models\Product;

class ReviewController extends Controller
{

    public function create(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string',
            'rating' => 'required|numeric',
            'comment' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $product = Product::where('id', $request->product_id)->first();

            if ($product) {
                $review = Review::create([
                    'user_id' => auth()->user()->id,
                    'product_id' => $request->product_id,
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                ]);

                // Calculate the new average rating
                $averageRating = Review::where('product_id', $request->product_id)
                                       ->avg('rating');

                // Update the product's rating field
                $product->rating = $averageRating;
                $product->save();

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Review successful',
                    'data' => ['review' => $review]
                ], 200);
            } else {
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Product does not exist'], 400);
            }

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not create review. ' . $e->getMessage()], 500);
        }
    }

    public function reviews($product_id){

        try{
             $review = Product::with('reviews.user')->where('id',$product_id)->get();

            if($review){

                return response()->json(['success' => 'success', 'message' => 'reviews retrieved successfully', 'data' => ['reviews' => $review] ], 200);
            }

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not fetch reviews' . $e->getMessage()], 500);

        }

    }

}


