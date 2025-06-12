<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TransactionController;


Route::group(['prefix' => 'auth'], function () {

    Route::post('/seller/join', [AuthController::class, 'sellerSignUp']);
    Route::post('/buyer/join', [AuthController::class, 'buyerSignUp']);
    Route::post('/verification', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode']);
    Route::post('/signin', [AuthController::class, 'signIn']);
    Route::post('/admin/signin', [AuthController::class, 'adminSignIn']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPasswordWithOTP']);

});



Route::group(['prefix' => 'category'], function () {

    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{category_id}', [CategoryController::class, 'fetchCategory']);

});


Route::group(['prefix' => 'product'], function () {

    Route::get('/', [ProductController::class, 'index']);
    Route::get('/category/{category_id}', [ProductController::class, 'fetchProductsByCategory']);
    Route::get('/popular', [ProductController::class, 'fetchPopularProducts']);
    Route::get('/recent', [ProductController::class, 'fetchRecentProducts']);
    Route::get('/{product_id}', [ProductController::class, 'fetchProduct']);
    Route::get('/search/{search_term}', [ProductController::class, 'search']);

});



Route::group(['prefix' => 'review'], function () {

    Route::get('/{product_id}', [ReviewController::class, 'reviews']);

});




Route::group(['middleware' => 'auth:sanctum'], function(){

    Route::group(['prefix' => 'analytics'], function () {

        Route::get('/admin', [AnalyticsController::class, 'adminIndex']);
        Route::get('/seller', [AnalyticsController::class, 'sellerIndex']);
        Route::post('/total/category/sale/{year}', [AnalyticsController::class, 'orderItemsByYear']);

    });


    Route::group(['prefix' => 'user'], function () {

        Route::get('/profile', [UserController::class, 'index']);
        Route::post('/update/profile', [UserController::class, 'updateProfile']);
        Route::post('/update/profile-pic', [UserController::class, 'uploadProfilePic']);
        Route::post('/change-password', [UserController::class, 'changePassword']);
        Route::post('/store/close', [UserController::class, 'closeStore']);
        Route::post('/store/open', [UserController::class, 'openStore']);
        Route::get('/{user_id}', [UserController::class, 'fetchUser']);

        // Admin routes
        Route::post('/admin/create', [UserController::class, 'createAdmin']);
        Route::delete('/admin/delete/{user_id}', [UserController::class, 'deleteAdmin']);
        Route::get('/admins/all', [UserController::class, 'fetchAllAdmins']);


        Route::get('/sellers/all', [UserController::class, 'fetchAllSellers']);
        Route::get('/buyers/all', [UserController::class, 'fetchAllBuyers']);
        Route::post('/activate/{user_id}', [UserController::class, 'activate']);
        Route::post('/deactivate/{user_id}', [UserController::class, 'deactivate']);

        Route::post('/send-mail', [UserController::class, 'sendMail']);

        Route::post('update/bank-details', [UserController::class, 'updateBankAccountDetails']);


        Route::post('/kyc-application', [UserController::class, 'applyKyc']);

        Route::post('update/kyc-application-status/{user_id}', [UserController::class, 'updateSellerKyc']);

        Route::get('/sellers/kyc/pending', [UserController::class, 'fetchPendingKyc']);

        Route::get('/sellers/kyc/applied', [UserController::class, 'fetchAppliedKyc']);

        Route::get('/sellers/kyc/approved', [UserController::class, 'fetchApprovedKyc']);

        Route::get('/sellers/kyc/rejected', [UserController::class, 'fetchRejectedKyc']);

    });


    Route::group(['prefix' => 'cart'], function () {

        Route::get('/', [CartController::class, 'index']);
        Route::post('/update-quantity', [CartController::class, 'updateItemQuantity']);
        Route::post('/add', [CartController::class, 'addItem']);
        Route::post('/mass-load', [CartController::class, 'massLoadCart']);
        Route::delete('/empty', [CartController::class, 'empty']);
        Route::delete('/remove/{product_id}', [CartController::class, 'removeItem']);

    });


    Route::group(['prefix' => 'category'], function () {

        Route::post('/create', [CategoryController::class, 'create']);
        Route::post('/update', [CategoryController::class, 'update']);
        Route::post('/upload/image', [CategoryController::class, 'updateCategoryImage']);
        Route::delete('/delete/{category_id}', [CategoryController::class, 'delete']);

    });


    Route::group(['prefix' => 'orders'], function () {



        Route::get('/my-orders', [OrderController::class, 'fetchMyOrders']);
        Route::post('/update', [OrderController::class, 'update']);

        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{order_id}', [OrderController::class, 'fetchOrder']);
        Route::get('/user/{user_id}', [OrderController::class, 'fetchOrdersByUser']);

    });



    Route::group(['prefix' => 'purchase'], function () {


        Route::get('/items', [OrderItemController::class, 'fetchOrderItems']);

        Route::post('update/item/{order_item_id}', [OrderitemController::class, 'updateOrderItem']);


    });


    Route::group(['prefix' => 'product'], function () {

        Route::get('/my-products', [ProductController::class, 'fetchMyProducts']);
        Route::post('/create', [ProductController::class, 'create']);
        Route::post('/update', [ProductController::class, 'update']);
        Route::delete('/delete/{product_id}', [ProductController::class, 'delete']);
        Route::post('/delist/{product_id}', [ProductController::class, 'delist']);
        Route::post('/update/images/{id}', [ProductController::class, 'updateImages']);
        Route::post('/update/thumbnail/{id}', [ProductController::class, 'updateThumbnail']);
        Route::delete('/delete/image/{product_id}', [ProductController::class, 'deleteImage']);

    });


    Route::group(['prefix' => 'review'], function () {
        Route::post('/', [ReviewController::class, 'create']);
    });



    Route::group(['prefix' => 'transactions'], function () {

        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/user', [TransactionController::class, 'fetchMyTransactions']);

    });


    Route::post('/processpay', [PaymentController::class, 'verifyPayment']);





});



