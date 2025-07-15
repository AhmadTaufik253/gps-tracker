<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeofenceController;
use App\Http\Controllers\Api\LocationHistoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/geofence-exit', [GeofenceController::class, 'geofenceExit']);

Route::post('/location-history', [LocationHistoryController::class, 'store']);
Route::get('/location-history/{device_id}', [LocationHistoryController::class, 'index']);

