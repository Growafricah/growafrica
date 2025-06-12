<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{

    public function index(){

        try{
             $cart = Cart::with('product')->where('user_id',auth()->user()->id)->get();

            if($cart){

                return response()->json(['success' => 'success', 'message' => 'cart retrieved successfully', 'data' => ['cart' => $cart] ], 200);
            }

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not fetch products' . $e->getMessage()], 500);

        }

    }

    public function updateItemQuantity(Request $request){

        $request->validate([

            'product_id' => 'required|string',
            'quantity' => 'required|numeric'
        ]);

        DB::beginTransaction();

        try {

            $cart_item = Cart::where('product_id',$request->product_id)->where('user_id',auth()->user()->id)->first();

            $product = Product::where('id',$request->product_id)->first();

            if (!$cart_item){

                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'product does not exist in cart'], 400);

            }else{

                $price = $request->quantity * $product->unit_price;

                $cart_item->update([

                    'total' => $price,
                    'quantity' => $request->quantity,
                ]);
                DB::commit();
                return response()->json(['status' => 'success','message' => 'Item updated successfully'], 200);
            }

        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not update item. ' . $e->getMessage()], 500);
        }
    }

    public function addItem(Request $request){

        $request->validate([

            'product_id' => 'required|string',
            'quantity' => 'required|numeric'
        ]);

        DB::beginTransaction();

        try {

            $cart_item = Cart::where('product_id',$request->product_id)->where('user_id',auth()->user()->id)->first();

            if ($cart_item){

                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'item already existing cart'], 400);

            }

            $product = Product::where('id',$request->product_id)->first();

            if (!$product){

                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'product does not exist in cart'], 400);

            }else{

                $price = $request->quantity * $product->unit_price;

                Cart::create([

                    'user_id' => auth()->user()->id,
                    'product_id' => $request->product_id,
                    'unit_price' => $product->unit_price,
                    'quantity' => $request->quantity,
                    'total' => $price

                ]);
                DB::commit();
                return response()->json(['status' => 'success','message' => 'Item added successfully'], 200);
            }

        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not add item. ' . $e->getMessage()], 500);
        }
    }

    public function massLoadCart(Request $request){

        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|string',
            'items.*.quantity' => 'required|numeric'
        ]);

        DB::beginTransaction();

        try {

            $userId = auth()->check() ? auth()->user()->id : null;


            foreach ($request->items as $item) {

                $productId = $item['product_id'];
                $quantity = $item['quantity'];

                if ($userId) {

                    $cartItemQuery = Cart::where('product_id', $productId)->where('user_id', $userId);
                }

                $cart_item = $cartItemQuery->first();

                if ($cart_item) {
                    continue; // Skip existing items
                }

                $product = Product::where('id', $productId)->first();

                if (!$product) {
                    continue; // Skip non-existing products
                }

                $price = $quantity * $product->unit_price;

                Cart::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'unit_price' => $product->unit_price,
                    'quantity' => $quantity,
                    'total' => $price
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Items added successfully'], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not add items. ' . $e->getMessage()], 500);
        }
    }

    public function empty() {

        DB::beginTransaction();

        try {

            $cart = Cart::where('user_id',auth()->user()->id)->get();

            if ($cart->isNotEmpty()) {

                foreach ($cart as $cart_item) {
                    $cart_item->delete();
                }
                DB::commit();
                return response()->json(['status' => 'success','message' => 'Cart emptied successfully'], 200);
            } else {
                DB::rollback();
                return response()->json(['status' => 'error','message' => 'No item in cart'],400);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()], 500);
        }

    }

    public function removeItem($product_id){

        DB::beginTransaction();

        try {

            $product = Cart::where('product_id',$product_id)->where('user_id',auth()->user()->id)->first();

            if ($product) {

                $product->delete();
                DB::commit();
                return response()->json(['status' => 'success','message' => 'Item removed from cart successfully'], 200);
            } else {
                DB::rollback();
                return response()->json(['status' => 'error','message' => 'Item not found in cart'],400);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()], 500);
        }

    }

}
