<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::apiResource('stocks','StockController');
Route::apiResource('clients','ClientController');
Route::apiResource('suppliers','SupplierController');

Route::get('clientCart/{name}','ClientCartController@viewCart');
Route::post('clientCart/{wheelId}/{name}','ClientCartController@addToCart');
Route::delete('clientCart/{wheelId}/{name}','ClientCartController@deleteCartItem');
Route::delete('clientCart/{wheelId}/{name}','ClientCartController@deleteCartItemAll');

// Route::get('supplierCart/{name}','SupplierCartController@viewCart');
// Route::post('supplierCart/{wheelId}/{name}','SupplierCartController@addToCart');
// Route::delete('supplierCart/{wheelId}/{name}','SupplierCartController@deleteCartItem');
// Route::delete('supplierCart/{wheelId}/{name}','SupplierCartController@deleteCartItemAll');

Route::get('supplierCart/{name}','SupplierPinjamanCartController@viewCart');
Route::post('supplierCart/{wheelId}/{name}','SupplierPinjamanCartController@addToCart');
Route::delete('supplierCart/{wheelId}/{name}','SupplierPinjamanCartController@deleteCartItem');
Route::delete('supplierCartAll/{wheelId}/{name}','SupplierPinjamanCartController@deleteCartItemAll');
