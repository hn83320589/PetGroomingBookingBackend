<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PetTypeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\DailyTimeSlotController;
use App\Http\Controllers\PetAppointmentController;

Route::post('register', [UserController::class, 'store']);
Route::post('login', [UserController::class, 'login']);

Route::get('petsCategory', [PetTypeController::class, 'index']);
Route::get('services', [ServiceController::class, 'index']);
Route::get('dailyTimeSlots', [DailyTimeSlotController::class, 'index']);
Route::get('statusList', function () {
    return response()->json([
        'data' => [
            'booked'    => '預約中',
            'completed' => '已完成',
            'cancelled' => '已取消',
            'timeout'   => '逾時',
        ],
    ]);
});
Route::get('genderList', function () {
    //'male','female','unknown'
    return response()->json([
        'data' => [
            'male'    => '公',
            'female'  => '母',
            'unknown' => '未知',
        ],
    ]);
});

Route::middleware('auth:api')->group(function () {
    ### 使用者相關API ###
    Route::get('user', [UserController::class, 'show']);
    Route::get('users', [UserController::class, 'index']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('dailyTimeSlots', [DailyTimeSlotController::class, 'destroy']);
    Route::post('logout', [UserController::class, 'logout']);

    Route::post('dailyTimeSlots', [DailyTimeSlotController::class, 'store']);

    ### 寵物相關API ###
    Route::get('pets', [PetController::class, 'index']);
    Route::get('pets/{id}', [PetController::class, 'show']);
    Route::post('pets', [PetController::class, 'store']);
    Route::delete('pets/{id}', [PetController::class, 'destroy']);
    Route::put('pets/{id}', [PetController::class, 'update']);

    ### 預約相關API ###
    Route::get('appointments', [PetAppointmentController::class, 'index']);
    Route::post('appointments', [PetAppointmentController::class, 'store']);
    Route::delete('appointments/{id}', [PetAppointmentController::class, 'destroy']);
    Route::put('appointments/{id}', [PetAppointmentController::class, 'update']);
    Route::patch('appointments/{id}', [PetAppointmentController::class, 'updateStatus']);
});

Route::get('test', function () {
    return 'Hello World';
});
