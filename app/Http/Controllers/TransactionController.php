<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use App\Models\Transaction;


class TransactionController extends Controller
{

    public function index(){

        try{
             $transactions = Transaction::with('user')->get();

            if($transactions){

                return response()->json(['success' => 'success', 'message' => 'transactions retrieved successfully', 'data' => ['transactions' => $transactions] ], 200);
            }

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not fetch transactions' . $e->getMessage()], 500);

        }

    }

    public function fetchMyTransactions(){

        try{
             $transactions = Transaction::where('user_id',auth()->user()->id)->get();

            if($transactions){

                return response()->json(['success' => 'success', 'message' => 'transactions retrieved successfully', 'data' => ['transactions' => $transactions] ], 200);
            }

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not fetch transactions' . $e->getMessage()], 500);

        }

    }

}
