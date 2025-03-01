<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PetTypeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\DailyTimeSlotController;

Route::post('register', [UserController::class, 'store']);
Route::post('login', [UserController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('user', [UserController::class, 'show']);
    Route::get('users', [UserController::class, 'index']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    Route::delete('dailyTimeSlots', [DailyTimeSlotController::class, 'destroy']);

    Route::post('logout', [UserController::class, 'logout']);

    Route::post('dailyTimeSlots', [DailyTimeSlotController::class, 'store']);

});

Route::get('petsCategory', [PetTypeController::class, 'index']);
Route::get('services', [ServiceController::class, 'index']);
Route::get('dailyTimeSlots', [DailyTimeSlotController::class, 'index']);

Route::get('test', function () {
    return 'Hello World';
});
