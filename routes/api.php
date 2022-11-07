<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use App\Http\Controllers\API;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register',[API\UserController::class, 'register']);
Route::post('login',[API\UserController::class,'login']);
Route::post('forget-password',[ API\UserController::class,'forgetPassword']);
Route::post('social-login',[ API\UserController::class, 'socialLogin' ]);
Route::get('user-list',[API\UserController::class, 'userList']);
Route::get('staticdata-list',[API\StaticDataController::class,'getList']);

Route::get('user-detail',[API\UserController::class, 'userDetail']);
Route::get('country-list', [ API\CountryController::class, 'getList' ] );
Route::get('country-detail', [ API\CountryController::class, 'getDetail' ] );
Route::get('city-list', [ API\CityController::class, 'getList' ] );
Route::get('city-detail', [ API\CityController::class, 'getDetail' ] );
Route::get('extracharge-list', [ API\ExtraChargeController::class, 'getList' ] );
Route::get('paymentgateway-list',[API\PaymentGatewayController::class,'getList']);

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::get('dashboard-detail', [ API\UserController::class, 'dashboard' ]);
    Route::post('country-save', [ App\Http\Controllers\CountryController::class, 'store' ] );
    Route::post('country-delete/{id}', [ App\Http\Controllers\CountryController::class, 'destroy' ] );
    Route::post('country-action', [ App\Http\Controllers\CountryController::class, 'action' ] );

    Route::post('city-save', [ App\Http\Controllers\CityController::class, 'store' ] );
    Route::post('city-delete/{id}', [ App\Http\Controllers\CityController::class, 'destroy' ] );
    Route::post('city-action', [ App\Http\Controllers\CityController::class, 'action' ] );

    Route::post('extracharge-save', [ App\Http\Controllers\ExtraChargeController::class, 'store' ] );
    Route::post('extracharge-delete/{id}', [ App\Http\Controllers\ExtraChargeController::class, 'destroy' ] );
    Route::post('extracharge-action', [ App\Http\Controllers\ExtraChargeController::class, 'action' ] );
    
    Route::post('staticdata-save',[ App\Http\Controllers\StaticDataController::class, 'store' ]);
    Route::post('staticdata-delete/{id}',[ App\Http\Controllers\StaticDataController::class, 'destroy' ]);

    Route::get('order-list', [ API\OrderController::class, 'getList' ] );
    Route::get('order-detail', [ API\OrderController::class, 'getDetail' ] );
    Route::post('order-save', [ App\Http\Controllers\OrderController::class, 'store' ] );
    Route::post('order-update/{id}', [ App\Http\Controllers\OrderController::class, 'update' ] );
    Route::post('order-delete/{id}', [ App\Http\Controllers\OrderController::class, 'destroy' ] );
    Route::post('order-action', [ App\Http\Controllers\OrderController::class, 'action' ] );

    Route::post('paymentgateway-save', [ App\Http\Controllers\PaymentGatewayController::class, 'store' ] );
    
    Route::post('payment-save', [ API\PaymentController::class, 'paymentSave' ] );
    Route::get('payment-list', [ API\PaymentController::class, 'getList' ] );

    Route::post('notification-list',[API\NotificationController::class,'getList']);
    
    Route::post('update-user-status',[API\UserController::class, 'updateUserStatus']);
    Route::post('change-password',[API\UserController::class, 'changePassword']);
    Route::post('update-profile',[API\UserController::class,'updateProfile']);
    Route::post('delete-user',[API\UserController::class,'deleteUser']);
    Route::post('user-action', [ API\UserController::class, 'userAction' ] );

    Route::post('update-appsetting',[API\UserController::class,'updateAppSetting']);
    Route::get('get-appsetting',[API\UserController::class,'getAppSetting']);

    Route::get('document-list', [ API\DocumentController::class, 'getList' ] );
    Route::post('document-save', [ App\Http\Controllers\DocumentController::class, 'store' ] );
    Route::post('document-delete/{id}', [ App\Http\Controllers\DocumentController::class, 'destroy' ] );
    Route::post('document-action', [ App\Http\Controllers\DocumentController::class, 'action' ] );

    Route::get('delivery-man-document-list', [ API\DeliveryManDocumentController::class, 'getList' ] );
    Route::post('delivery-man-document-save', [ App\Http\Controllers\DeliveryManDocumentController::class, 'store' ] );
    Route::post('delivery-man-document-delete/{id}', [ App\Http\Controllers\DeliveryManDocumentController::class, 'destroy' ] );
    Route::post('delivery-man-document-action', [ App\Http\Controllers\DeliveryManDocumentController::class, 'action' ] );
    
    Route::post('order-auto-assign', [ App\Http\Controllers\OrderController::class, 'AutoAssignCancelOrder' ] );

    Route::get('place-autocomplete-api', [ API\CommonController::class, 'placeAutoComplete' ] );
    Route::get('place-detail-api', [ API\CommonController::class, 'placeDetail' ] );
    
    Route::get('logout', [ API\UserController::class, 'logout' ]);
});