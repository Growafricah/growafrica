<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;


class OrderItemController extends Controller
{


    public function fetchOrderItems(){

        try{

             $orders = OrderItem::with('buyer','product')->where('seller_id',auth()->user()->id)->get();

             if($orders){

                return response()->json(['success' => 'success', 'message' => 'purchases retrieved successfully', 'data' => ['orders' => $orders] ], 200);

             }


        }catch(\Exception $e){

            return response()->json(['status' => 'error', 'message' => 'Could not get purchases' . $e->getMessage()], 500);

        }

    }

    public function updateOrderItem($order_item_id){

        try{

             $orderItem = OrderItem::where('id',$order_item_id)->first();

             if($orderItem){

                $orderItem->update(['status' => "return"]);

                $order=Order::where('id',$orderItem->order_id)->first();

                $new_sub_total = $order->sub_total -  $orderItem->total;

                $new_total = $order->total_amount -  $orderItem->total;

                $new_items_count = $order->items_count -  1;

                $order->update([

                    'sub_total' =>  $new_sub_total,
                    'total_amount' =>  $new_total,
                    'items_count' =>  $new_items_count,
                ]);

                return response()->json(['status' => 'success', 'message' => 'Item status changed to returned',], 200);

             }else{

                return response()->json(['status' => 'error', 'message' => 'item not in order'], 400);
             }


        }catch(\Exception $e){

            return response()->json(['status' => 'error', 'message' => 'Could not get purchases' . $e->getMessage()], 500);

        }

    }


}
