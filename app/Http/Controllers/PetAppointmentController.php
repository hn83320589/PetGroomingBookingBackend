<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use App\Models\PetAppointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

// 寵物預約相關功能
class PetAppointmentController extends Controller
{
    /**
     * @var mixed
     */
    protected $loginRole;

    /**
     * @var mixed
     */
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();

        if ($this->user->hasAnyRole(['管理員', '店員'])) {
            $this->loginRole = 1;
        } else {
            $this->loginRole = 0;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $petAppointment = PetAppointment::find($id);

        if (!$petAppointment) {
            return response()->json([
                'message' => '找不到資料',
            ], 404);
        }

        $petAppointment->delete();

        return response()->json([
            'message' => '刪除成功',
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 顧客只能看到自己的預約
        if ($this->loginRole == 0) {
            $pets = $this->user->pets();
        } else {
            $pets = Pet::all();
        }

        $appointments = $pets->whereDoesntHave('petAppointments')->load('petAppointments.petAppointmentDetail');

        return response()->json([
            'appointments' => $appointments,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 驗證
        $rules = [
            'pet_id'                                                 => [
                'required',
                'exists:pets,id',
            ],
            'service_id'                                             => [
                'required',
                'exists:services,id',
            ],
            'price'                                                  => [
                'required',
                'numeric',
            ],
            'bath_product_id'                                        => [
                'exists:bath_products,id',
            ],
            'pet_appointment_details'                                => [
                'required',
                'array',
                'min:1',
            ],
            'pet_appointment_details.*.user_time_slot_assignment_id' => [
                'required',
                'exists:user_time_slot_assignments,id',
            ],
        ];

        $messages = [
            'pet_id.required'                                                 => '寵物為必填',
            'pet_id.exists'                                                   => '寵物不存在',
            'service_id.required'                                             => '服務為必填',
            'service_id.exists'                                               => '服務不存在',
            'price.required'                                                  => '價格為必填',
            'price.numeric'                                                   => '價格格式錯誤',
            'bath_product_id.exists'                                          => '進階服務不存在',
            'pet_appointment_details.required'                                => '預約時段為必填',
            'pet_appointment_details.array'                                   => '預約時段格式錯誤',
            'pet_appointment_details.min'                                     => '預約時段格式錯誤',
            'pet_appointment_details.*.user_time_slot_assignment_id.required' => '預約時段為必填',
            'pet_appointment_details.*.user_time_slot_assignment_id.exists'   => '預約時段不存在',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }

        $petAppointmentDetails = $request->pet_appointment_details;
        unset($request['pet_appointment_details']);

        $petAppointment = PetAppointment::create($request->all());

        $petAppointment->petAppointmentDetail()->createMany($petAppointmentDetails);

        return response()->json([
            'message' => '新增成功',
            'data'    => $petAppointment->load('petAppointmentDetail.dailyTimeSlots'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 驗證
        $rules = [
            'pet_id'                                                 => [
                'required',
                'exists:pets,id',
            ],
            'service_id'                                             => [
                'required',
                'exists:services,id',
            ],
            'price'                                                  => [
                'numeric',
            ],
            'bath_product_id'                                        => [
                'exists:bath_products,id',
            ],
            'pet_appointment_details'                                => [
                'array',
                'min:1',
            ],
            'pet_appointment_details.*.user_time_slot_assignment_id' => [
                'required',
                'exists:user_time_slot_assignments,id',
            ],
        ];

        $messages = [
            'pet_id.required'                                                 => '寵物為必填',
            'pet_id.exists'                                                   => '寵物不存在',
            'service_id.required'                                             => '服務為必填',
            'service_id.exists'                                               => '服務不存在',
            'price.numeric'                                                   => '價格格式錯誤',
            'bath_product_id.exists'                                          => '進階服務不存在',
            'pet_appointment_details.array'                                   => '預約時段格式錯誤',
            'pet_appointment_details.min'                                     => '預約時段格式錯誤',
            'pet_appointment_details.*.user_time_slot_assignment_id.required' => '預約時段為必填',
            'pet_appointment_details.*.user_time_slot_assignment_id.exists'   => '預約時段不存在',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }

        $petAppointment = PetAppointment::find($id);

        if (!$petAppointment) {
            return response()->json([
                'message' => '找不到資料',
            ], 404);
        }

        $petAppointment->update($request->all());

        if ($request->has('pet_appointment_details')) {
            $petAppointment->petAppointmentDetail()->delete();

            $petAppointmentDetails = $request->pet_appointment_details;
            unset($request['pet_appointment_details']);

            $petAppointment->petAppointmentDetail()->createMany($petAppointmentDetails);
        }

        return response()->json([
            'message' => '更新成功',
            'data'    => $petAppointment->load('petAppointmentDetail.dailyTimeSlots'),
        ]);
    }
}
