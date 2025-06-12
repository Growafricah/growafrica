<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Mail\TransactionSuccessMail;
use App\Mail\AdminTransactionSuccessMail;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{


    public function verifyPayment(Request $request){


        try {

                    $ref = $request->input('ref', null);
                    $address = $request->input('address', null);
                    $sub_total = $request->input('sub_total', null);
                    $delivery_fee = $request->input('delivery_fee', null);
                    $total = $request->input('total', null);

                    if ($ref === null || $address === null || $sub_total === null || $delivery_fee === null || $total === null ) {
                        return response()->json(['status' => 'error','message' => 'Missing query params'],400);
                    }
                    $curl = curl_init();

                    curl_setopt_array($curl, array(

                        CURLOPT_URL => "https://api.paystack.co/transaction/verify/$ref",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array("Authorization: Bearer ".env('PAYSTACK_SECRET_KEY')."","Cache-Control: no-cache",),
                    ));

                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);


                    if ($err) {

                        return response()->json(['status' => 'error','message' => 'Payment Failed'],400);

                    } else {

                        $result = json_decode($response);

                        if ($result->data->status != "success"){

                            return response()->json(['status' => 'error','message' => 'Payment Failed'],400);

                        }else{



                            $cart = Cart::where('user_id',auth()->user()->id)->get();

                            $items_count = Cart::where('user_id', auth()->user()->id)->count();


                            if ($cart->isNotEmpty()) {

                                $order=Order::create([

                                    'user_id'=> auth()->user()->id,
                                    'txn_id'=> $result->data->id,
                                    'address'=>$address,
                                    'status'=>"pending",
                                    'items_count'=>$items_count,
                                    'delivery_fee'=> $delivery_fee,
                                    'sub_total'=> $sub_total,
                                    'total_amount'=> $total,
                                ]);

                                foreach ($cart as $cart_item) {

                                    $product = Product::where('id',$cart_item->product_id)->first();
                                    OrderItem::create([
                                        'buyer_id' => auth()->user()->id,
                                        'order_id' => $order->id,
                                        'product_id' => $cart_item->product_id,
                                        'quantity' => $cart_item->quantity,
                                        'unit_price' => $cart_item->unit_price,
                                        'total' => $cart_item->total,
                                        'status' => "pending",
                                        'seller_id' => $product->seller_id,
                                    ]);

                                    $cart_item->delete();
                                }

                            } else {

                                return response()->json(['status' => 'error','message' => 'No item in cart'],400);
                            }



                            Transaction::create([

                                'user_id'=> auth()->user()->id,
                                'order_id'=> $order->id,
                                'txn_id'=> $result->data->id,
                                'reference_code'=>$result->data->reference,
                                'channel'=> $result->data->channel,
                                'status'=>"Success",
                                'total_amount'=>($result->data->amount/100)

                            ]);

                            Mail::to(auth()->user()->email)->send(new TransactionSuccessMail($order));

                            Mail::to('nwanoziep@gmail.com')->send(new AdminTransactionSuccessMail($order));

                            return response()->json(['status' => 'success','message' => 'Payment successful and order taken'], 200);

                        }

                    }

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error','message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }


    }


}
