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

Route::prefix('v1')->group(function(){
	Route::apiResource('stocks','Api\v1\StockController');
	Route::apiResource('clients','Api\v1\ClientController');
	Route::apiResource('suppliers','Api\v1\SupplierController');

	Route::get('clientCart/{name}','Api\v1\ClientCartController@viewCart');
	Route::post('clientCart/{wheelId}/{name}','Api\v1\ClientCartController@addToCart');
	Route::delete('clientCart/{wheelId}/{name}','Api\v1\ClientCartController@deleteCartItem');
	Route::delete('clientCart/{wheelId}/{name}','Api\v1\ClientCartController@deleteCartItemAll');

	Route::get('supplierCart/{name}','Api\v1\SupplierCartController@viewCart');
	Route::post('supplierCart/{wheelId}/{name}','Api\v1\SupplierCartController@addToCart');
	Route::delete('supplierCart/{wheelId}/{name}','Api\v1\SupplierCartController@deleteCartItem');
	Route::delete('supplierCart/{wheelId}/{name}','Api\v1\SupplierCartController@deleteCartItemAll');

	Route::get('supplierPinjamCart/{name}','Api\v1\SupplierPinjamanCartController@viewCart');
	Route::post('supplierPinjamCart/{wheelId}/{name}','Api\v1\SupplierPinjamanCartController@addToCart');
	Route::delete('supplierPinjamCart/{wheelId}/{name}','Api\v1\SupplierPinjamanCartController@deleteCartItem');
	Route::delete('supplierPinjamCartAll/{wheelId}/{name}','Api\v1\SupplierPinjamanCartController@deleteCartItemAll');
});

