<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\BathProduct;
use App\Models\Notice;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::all();
        $services->load('serviceDescriptions');
        foreach ($services as $service) {
            $service->describe = $service->serviceDescriptions->pluck('content')->toArray();

            // 移除不需要的欄位
            unset($service->serviceDescriptions);
        }

        $batheProducts = BathProduct::all();
        $notices = Notice::all()->pluck('notice')->toArray();

        return response()->json([
            'services' => $services,
            'bath_products' => $batheProducts,
            'notices' => $notices
        ]);
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
