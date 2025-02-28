<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PetTypeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\DailyTimeSlotController;

Route::get('/user', function (Request $request)
{
    return $request->user();
})->middleware('auth:sanctum');

Route::get('petsCategory', [PetTypeController::class, 'index']);
Route::get('services', [ServiceController::class, 'index']);
Route::get('dailyTimeSlots', [DailyTimeSlotController::class, 'index']);
Route::post('dailyTimeSlots', [DailyTimeSlotController::class, 'store']);

Route::get('test', function ()
{
    return 'Hello World';
});
