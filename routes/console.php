<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('setUserTimeSlotAssignment', function () {
    // 新增userTimeSlotAssignment，將user_id = 3 跟daily_time_slots的資料綁定
    $dailyTimeSlot = \App\Models\DailyTimeSlot::all();
    $user = \App\Models\User::find(3);
    $dailyTimeSlot->each(function ($item) use ($user) {
        \App\Models\UserTimeSlotAssignment::create([
            'daily_time_slot_id' => $item->id,
            'user_id' => $user->id,
        ]);
    });

    print_r('done');
})->purpose('Display User Time Slot Assignment');

Artisan::command('petType', function () {
    $petTypes = \App\Models\PetType::all();
    print_r($petTypes->load(['petTypePrices'])->toArray());
})->purpose('Display Pet Appointment');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
