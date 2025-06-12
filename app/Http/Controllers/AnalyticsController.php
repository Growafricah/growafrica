<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;


class AnalyticsController extends Controller
{

    public function adminIndex()
    {
        $userCounts = [
            'sellers' => User::where('role', 'seller')->count(),
            'buyers' => User::where('role', 'buyer')->count(),
            'admins' => User::whereIn('role', ['admin', 'super_admin'])->count(),
        ];

        $transactionCount = Transaction::count();
        $orderCount = Order::count();
        $totalOrderValue = Order::sum('sub_total');
        $totalOrderItems = OrderItem::sum('quantity');
        $productCount = Product::count();
        $categoryCount = Category::count();

        $orderStatuses = [
            'pending' => Order::where('status', 'pending')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'enroute' => Order::where('status', 'enroute')->count(),
            'processing' => Order::where('status', 'processing')->count(),
        ];

        $totalTransactionValue = Transaction::sum('total_amount');

        return response()->json([
            'userCounts' => $userCounts,
            'transactionCount' => $transactionCount,
            'orderCount' => $orderCount,
            'totalOrderValue' => $totalOrderValue,
            'totalOrderItems' => $totalOrderItems,
            'productCount' => $productCount,
            'categoryCount' => $categoryCount,
            'orderStatuses' => $orderStatuses,
            'totalTransactionValue' => $totalTransactionValue,
        ]);
    }


    public function sellerIndex()
    {
        $sellerId = auth()->user()->id;

        $totalItemOrders = OrderItem::where('seller_id', $sellerId)->count();
        $totalQuantity = OrderItem::where('seller_id', $sellerId)->sum('quantity');
        $returnedOrders = OrderItem::where('seller_id', $sellerId)->where('status', 'return')->count();
        $nonReturnedOrders = OrderItem::where('seller_id', $sellerId)->where('status', '!=', 'return')->count();


        $productOrderCounts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total'))
                ->where('seller_id', $sellerId)
                ->groupBy('product_id')
                ->orderByDesc('total')
                ->with('product:id,name')
                ->get()
                ->map(function($item) {
                    return [
                        'product_name' => $item->product->name,
                        'total_quantity' => $item->total
                    ];
                });


        return response()->json([
            'totalItemOrders' => $totalItemOrders,
            'totalQuantity' => $totalQuantity,
            'returnedOrders' => $returnedOrders,
            'nonReturnedOrders' => $nonReturnedOrders,
            'productOrderCounts' => $productOrderCounts,
        ]);
    }

    // public function orderItemsByYear($year)
    // {
    //     $sellerId = auth()->user()->id;

    //     $monthlySales = OrderItem::select(

    //             DB::raw('MONTH(order_items.created_at) as month'),
    //             DB::raw('SUM(order_items.total) as total'),
    //             'products.category_id',
    //             'categories.name as category_name'
    //         )
    //         ->join('products', 'order_items.product_id', '=', 'products.id')
    //         ->join('categories', 'products.category_id', '=', 'categories.id')
    //         ->where('order_items.seller_id', $sellerId)
    //         ->where('order_items.created_at', $year)
    //         ->groupBy('month', 'products.category_id', 'categories.name')
    //         ->get()
    //         ->groupBy('month')
    //         ->map(function ($items, $month) {
    //             return [
    //                 'month' => $month,
    //                 'categories' => $items->map(function ($item) {
    //                     return [
    //                         'category' => $item->category_name,
    //                         'total' => $item->total
    //                     ];
    //                 })
    //             ];
    //         });

    //     return response()->json([
    //         'year' => $year,
    //         'monthlySales' => $monthlySales,
    //     ]);
    // }

    public function orderItemsByYear($year)
{
    $sellerId = auth()->user()->id;


    // Check if there are any order items for the specified year and seller
    $orderItemsCount = OrderItem::where('seller_id', $sellerId)
        // ->whereYear('created_at', $year)
        ->whereRaw('YEAR(created_at) = ?', [$year])
        ->count();

    // Debugging: Check if there are order items for the given year and seller
    Log::info('Order Items Count: ' . $orderItemsCount);

    if ($orderItemsCount == 0) {
        return response()->json([
            'year' => $orderItemsCount,
            'monthlySales' => []
        ]);
    }


    $monthlySales = OrderItem::select(
            DB::raw('MONTH(order_items.created_at) as month'),
            DB::raw('SUM(order_items.quantity * order_items.unit_price) as total'),
            'products.category_id',
            'categories.name as category_name'
        )
        ->join('products', 'order_items.product_id', '=', 'products.id')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->where('order_items.seller_id', $sellerId)
        ->whereYear('order_items.created_at', $year)
        ->groupBy('month', 'products.category_id', 'categories.name')
        ->orderBy('month')
        ->get();


    $groupedMonthlySales = $monthlySales->groupBy('month')
        ->map(function ($items, $month) {
            return [
                'month' => $month,
                'categories' => $items->map(function ($item) {
                    return [
                        'category' => $item->category_name,
                        'total' => $item->total
                    ];
                })
            ];
        });

    return response()->json([
        'year' => $year,
        'monthlySales' => $groupedMonthlySales,
    ]);
}


}
