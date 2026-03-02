<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\WmsController;

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

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');*/

Route::post('updateOrderStatus', 'Api\OrderController@updateOrder');

// WMS Pickup Endpoints

Route::prefix('wms')->middleware('wms.api-key')->group(function () {
    Route::post('truck-plan', [WmsController::class, 'updateTruckPlan']);
    Route::get('logs',        [WmsController::class, 'getPickupLogs']);
    Route::get('logs/{id}',   [WmsController::class, 'getLogDetail']);
});
