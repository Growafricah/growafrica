<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use App\Notifications\OrderStatusNotification;
use App\Models\Order;
use App\Models\OrderItem;


class OrderController extends Controller
{

    public function index(){

        try{
             $orders = Order::get();

            if($orders){

                return response()->json(['success' => 'success', 'message' => 'orders retrieved successfully', 'data' => ['orders' => $orders] ], 200);
            }

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not fetch orders' . $e->getMessage()], 500);

        }

    }

    public function fetchOrder($order_id){

        try{
             $order = Order::with('orderItems.product.seller','user')->where('id',$order_id)->first();

            if($order){

                return response()->json(['success' => 'success', 'message' => 'order retrieved successfully', 'data' => ['order' =>  $order] ], 200);

            }else{

                return response()->json(['success' => 'error', 'message' => 'Order does not exist'], 400);
            }

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not fetch order' . $e->getMessage()], 500);

        }

    }

    public function fetchMyOrders(){

        try{

             $orders = Order::where('user_id',auth()->user()->id)->get();

             if($orders){

                return response()->json(['success' => 'success', 'message' => 'User orders retrieved successfully', 'data' => ['orders' => $orders] ], 200);

             }


        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not get orders' . $e->getMessage()], 500);

        }

    }

    public function fetchOrdersByUser($user_id){

        try{
             $orders = Order::where('user_id',$user_id)->get();

            if($orders){

                return response()->json(['success' => 'success', 'message' => 'orders retrieved successfully', 'data' => ['orders' => $orders] ], 200);
            }

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not fetch orders' . $e->getMessage()], 500);

        }

    }

    public function update(Request $request){

        $data = $request->validate([

            'order_id' =>'required|string',
            'status' => 'required|string',
        ]);


        DB::beginTransaction();

        try {

            $order = Order::where('id',$request->order_id)->first();

            if ($order){

                $order->update($data);

                // Update each order item associated with the order
                foreach ($order->orderItems as $orderItem) {
                    $orderItem->update(['status' => $data['status']]);
                }


                DB::commit();
                // Dispatch the notification
                $order->user->notify(new OrderStatusNotification($order));

                return response()->json([
                    'status' => 'success',
                    'message' => 'Order updated successfully',
                    'data' => ['order' => $order]], 200);
            }else{

                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Order or user  does not exist'], 400);
            }

        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not update order . ' . $e->getMessage()], 500);
        }
    }


}
