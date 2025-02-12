<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('petType', function () {
    $petTypes = \App\Models\PetType::all();
    print_r($petTypes->load(['petTypePrices'])->toArray());
})->purpose('Display Pet Appointment');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
