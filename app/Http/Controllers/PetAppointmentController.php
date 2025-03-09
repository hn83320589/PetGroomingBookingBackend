<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Service;
use App\Models\BathProduct;
use Illuminate\Http\Request;
use App\Models\PetAppointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
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
    public function index(Request $request)
    {
        try {
            // 驗證
            $rules = [
                'pet_id' => [
                    'required',
                    'exists:pets,id',
                ],
            ];

            $messages = [
                'pet_id.required' => '寵物為必填',
                'pet_id.exists'   => '寵物不存在',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], 400);
            }

            // 顧客只能看到自己的預約
            if ($this->loginRole == 0) {
                $appointments = PetAppointment::where('pet_id', $request->pet_id)
                    ->whereHas('pet', function ($query) {
                        $query->where('user_id', $this->user->id);
                    })->get();
            } else {
                $appointments = PetAppointment::all();
            }

            $appointments = $appointments->load([
                'petAppointmentDetail.dailyTimeSlot',
                'service',
                'pet.user',
                'petAppointmentDetail.user',
            ]);

            // 先過濾出未完成的預約
            $bookedAppointments = $appointments->where('status', 'booked')
                ->sortBy(function ($appointment) {
                    // 排序依據預約日期
                    if ($appointment->petAppointmentDetail->isEmpty()) {
                        return null;
                    }
                    $firstDetail = $appointment->petAppointmentDetail->first();
                    return optional(optional($firstDetail)->dailyTimeSlot)->date;
                })
                ->map(function ($appointment) {
                    // 檢查是否有詳情資料
                    if ($appointment->petAppointmentDetail->isEmpty()) {
                        return null;
                    }

                    $firstDetail = $appointment->petAppointmentDetail->first();
                    $lastDetail  = $appointment->petAppointmentDetail->last();

                    return [
                        'id'                     => $appointment->id,
                        'appointment_date'       => optional(optional($firstDetail)->dailyTimeSlot)->date,
                        'appointment_start_time' => optional(optional($firstDetail)->dailyTimeSlot)->start_time,
                        'appointment_end_time'   => optional(optional($lastDetail)->dailyTimeSlot)->end_time,
                        'service_name'           => optional($appointment->service)->name,
                        'pet_name'               => optional($appointment->pet)->name,
                        'price'                  => $appointment->price,
                        'status'                 => $appointment->status,
                        'service_staff'          => optional(optional($firstDetail)->user)->name,
                        'customer_name'          => optional(optional($appointment->pet)->user)->name,
                    ];
                })
                ->filter() // 過濾掉 null 值
                ->values(); // 重設索引

            // 已完成或取消的資料
            $completedAppointments = $appointments->where('status', '!=', 'booked')
                ->sortByDesc(function ($appointment) {
                    // 倒序排序
                    if ($appointment->petAppointmentDetail->isEmpty()) {
                        return null;
                    }
                    $firstDetail = $appointment->petAppointmentDetail->first();
                    return optional(optional($firstDetail)->dailyTimeSlot)->date;
                })
                ->map(function ($appointment) {
                    // 檢查是否有詳情資料
                    if ($appointment->petAppointmentDetail->isEmpty()) {
                        return null;
                    }

                    $firstDetail = $appointment->petAppointmentDetail->first();
                    $lastDetail  = $appointment->petAppointmentDetail->last();

                    return [
                        'id'                     => $appointment->id,
                        'appointment_date'       => optional(optional($firstDetail)->dailyTimeSlot)->date,
                        'appointment_start_time' => optional(optional($firstDetail)->dailyTimeSlot)->start_time,
                        'appointment_end_time'   => optional(optional($lastDetail)->dailyTimeSlot)->end_time,
                        'service_name'           => optional($appointment->service)->name,
                        'pet_name'               => optional($appointment->pet)->name,
                        'price'                  => $appointment->price,
                        'status'                 => $appointment->status,
                        'service_staff'          => optional(optional($firstDetail)->user)->name,
                        'customer_name'          => optional(optional($appointment->pet)->user)->name,
                    ];
                })
                ->filter()
                ->values();

            return response()->json([
                'booked'    => $bookedAppointments,
                'completed' => $completedAppointments,
            ]);
        } catch (\Exception $e) {
            \Log::error('獲取預約列表出錯: '.$e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'message' => '獲取預約列表失敗',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 驗證
        $rules = [
            'pet_id'                    => [
                'required',
                'exists:pets,id',
            ],
            'service_id'                => [
                'required',
                'exists:services,id',
            ],
            'price'                     => [
                'required',
                'numeric',
            ],
            'bath_product_id'           => [
                'exists:bath_products,id',
            ],
            'pet_appointment_details'   => [
                'required',
                'array',
                'min:1',
            ],
            'pet_appointment_details.*' => [
                'required',
                'exists:user_time_slot_assignments,id',
            ],
        ];

        $messages = [
            'pet_id.required'                    => '寵物為必填',
            'pet_id.exists'                      => '寵物不存在',
            'service_id.required'                => '服務為必填',
            'service_id.exists'                  => '服務不存在',
            'price.required'                     => '價格為必填',
            'price.numeric'                      => '價格格式錯誤',
            'bath_product_id.exists'             => '進階服務不存在',
            'pet_appointment_details.required'   => '預約時段為必填',
            'pet_appointment_details.array'      => '預約時段格式錯誤',
            'pet_appointment_details.min'        => '預約時段格式錯誤',
            'pet_appointment_details.*.required' => '預約時段為必填',
            'pet_appointment_details.*.exists'   => '預約時段不存在',
            'customer_id.exists'                 => '顧客不存在',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $petAppointmentDetails = $request->pet_appointment_details;
            unset($request['pet_appointment_details']);

            // 計算price
            $price        = 0;
            $serviceId    = $request->service_id;
            $servicePrice = Service::find($serviceId)->price;
            $petTypePrice = Pet::find($request->pet_id)->petTypePrices()->where('service_id', $serviceId)->first();
            $price        = $servicePrice + $petTypePrice->price;

            if ($request->has('bath_product_id')) {
                $bathProductPrice  = BathProduct::find($request->bath_product_id)->price;
                $price            += $bathProductPrice;
            }

            // 與前端傳進來的不同以後端的計算為主
            if ($request->price != $price) {
                $request['price'] = $price;
            }

            $petAppointment = PetAppointment::create($request->all());

            $petAppointmentDetails = array_map(function ($petAppointmentDetail) {
                return [
                    'user_time_slot_assignment_id' => $petAppointmentDetail,
                ];
            }, $petAppointmentDetails);

            $petAppointment->petAppointmentDetail()->createMany($petAppointmentDetails);

            DB::commit();

            return response()->json([
                'message' => '新增成功',
                'data'    => $petAppointment->load('petAppointmentDetail.dailyTimeSlot'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return response()->json([
                'message' => '新增失敗',
            ], 400);
        }
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

        DB::beginTransaction();

        try {
            $petAppointment = PetAppointment::find($id);

            if (!$petAppointment) {
                return response()->json([
                    'message' => '找不到資料',
                ], 404);
            }

            // 計算price
            $price        = 0;
            $serviceId    = $request->service_id;
            $servicePrice = Service::find($serviceId)->price;
            $petTypePrice = Pet::find($request->pet_id)->petTypePrices()->where('service_id', $serviceId)->first();
            $price        = $servicePrice + $petTypePrice->price;

            if ($request->has('bath_product_id')) {
                $bathProductPrice  = BathProduct::find($request->bath_product_id)->price;
                $price            += $bathProductPrice;
            }

            // 與前端傳進來的不同以後端的計算為主
            if ($request->price != $price) {
                $request['price'] = $price;
            }

            $petAppointment->update($request->all());

            if ($request->has('pet_appointment_details')) {
                $petAppointment->petAppointmentDetail()->delete();

                $petAppointmentDetails = $request->pet_appointment_details;
                unset($request['pet_appointment_details']);

                $petAppointmentDetails = array_map(function ($petAppointmentDetail) {
                    return [
                        'user_time_slot_assignment_id' => $petAppointmentDetail,
                    ];
                }, $petAppointmentDetails);

                $petAppointment->petAppointmentDetail()->createMany($petAppointmentDetails);
            }

            return response()->json([
                'message' => '更新成功',
                'data'    => $petAppointment->load('petAppointmentDetail.dailyTimeSlot'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return response()->json([
                'message' => '更新失敗',
            ], 400);
        }
    }
}
