<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PetType;

class PetTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $petTypes = PetType::all();
        $petTypes->load('petTypePrices');
        foreach ($petTypes as $petType) {
            $petType->extra_price = $petType->petTypePrices->mapWithKeys(function ($petTypePrice) {
                // 將等級與加購價組成陣列，等級為 key，加購價為 value
                return [$petTypePrice->tier_level => $petTypePrice->extra_price];
            })->toArray(); // 確保結果是單一的陣列

            // 移除不需要的欄位
            unset($petType->petTypePrices);
        }

        return response()->json($petTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
