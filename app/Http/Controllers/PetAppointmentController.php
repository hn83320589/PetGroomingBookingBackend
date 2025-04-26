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
                    'exists:pets,id',
                ],
            ];

            $messages = [
                'pet_id.exists' => '寵物不存在',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], 400);
            }

            // 顧客只能看到自己的預約
            if ($this->loginRole == 0) {
                // 沒pet_id就撈該名顧客全部資料
                if ($request->has('pet_id')) {
                    $appointments = PetAppointment::where('pet_id', $request->pet_id)
                        ->whereHas('pet', function ($query) {
                            $query->where('user_id', $this->user->id);
                        })->get();
                } else {
                    $appointments = PetAppointment::whereHas('pet', function ($query) {
                        $query->where('user_id', $this->user->id);
                    })->get();
                }
            } else {
                $appointments = PetAppointment::all();
            }

            // 先檢查appointments是否有日期小於今天的booked資料，有就改成timeout
            $today               = now()->format('Y-m-d');
            $timeoutAppointments = $appointments->where('status', 'booked')
                ->filter(function ($appointment) use ($today) {
                    return $appointment->petAppointmentDetail->contains(function ($detail) use ($today) {
                        return optional($detail->dailyTimeSlot)->slot_date < $today;
                    });
                });

            foreach ($timeoutAppointments as $appointment) {
                $appointment->update(['status' => 'timeout']);
            }

            // 顧客只能看到自己的預約
            if ($this->loginRole == 0) {
                // 沒pet_id就撈該名顧客全部資料
                if ($request->has('pet_id')) {
                    $appointments = PetAppointment::where('pet_id', $request->pet_id)
                        ->whereHas('pet', function ($query) {
                            $query->where('user_id', $this->user->id);
                        })->get();
                } else {
                    $appointments = PetAppointment::whereHas('pet', function ($query) {
                        $query->where('user_id', $this->user->id);
                    })->get();
                }
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
                    return optional(optional($firstDetail)->dailyTimeSlot)->slot_date;
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
                        'appointment_date'       => optional(optional($firstDetail)->dailyTimeSlot)->slot_date,
                        'appointment_start_time' => optional(optional($firstDetail)->dailyTimeSlot)->slot_time,
                        'appointment_end_time'   => optional(optional($lastDetail)->dailyTimeSlot)->slot_time,
                        'service_name'           => optional($appointment->service)->display_name,
                        'bath_product_name'      => optional($appointment->bathProduct)->name,
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
            $completedAppointments = $appointments->where(function ($appointment) {
                return in_array($appointment->status, ['completed', 'cancelled']);
            })->sortByDesc(function ($appointment) {
                // 倒序排序
                if ($appointment->petAppointmentDetail->isEmpty()) {
                    return null;
                }
                $firstDetail = $appointment->petAppointmentDetail->first();
                return optional(optional($firstDetail)->dailyTimeSlot)->slot_date;
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
                        'appointment_date'       => optional(optional($firstDetail)->dailyTimeSlot)->slot_date,
                        'appointment_start_time' => optional(optional($firstDetail)->dailyTimeSlot)->slot_time,
                        'appointment_end_time'   => optional(optional($lastDetail)->dailyTimeSlot)->slot_time,
                        'service_name'           => optional($appointment->service)->display_name,
                        'bath_product_name'      => optional($appointment->bathProduct)->name,
                        'pet_name'               => optional($appointment->pet)->name,
                        'price'                  => $appointment->price,
                        'status'                 => $appointment->status,
                        'service_staff'          => optional(optional($firstDetail)->user)->name,
                        'customer_name'          => optional(optional($appointment->pet)->user)->name,
                    ];
                })
                ->filter()
                ->values();

            // 撈取逾時的預約
            $timeoutAppointments = $appointments->where('status', 'timeout')
                ->sortByDesc(function ($appointment) {
                    // 倒序排序
                    if ($appointment->petAppointmentDetail->isEmpty()) {
                        return null;
                    }
                    $firstDetail = $appointment->petAppointmentDetail->first();
                    return optional(optional($firstDetail)->dailyTimeSlot)->slot_date;
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
                        'appointment_date'       => optional(optional($firstDetail)->dailyTimeSlot)->slot_date,
                        'appointment_start_time' => optional(optional($firstDetail)->dailyTimeSlot)->slot_time,
                        'appointment_end_time'   => optional(optional($lastDetail)->dailyTimeSlot)->slot_time,
                        'service_name'           => optional($appointment->service)->display_name,
                        'bath_product_name'      => optional($appointment->bathProduct)->name,
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
                'timeout'   => $timeoutAppointments,
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
                'nullable',
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
                'nullable',
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
                'unique:pet_appointment_details,user_time_slot_assignment_id',
            ],
            'pet.id'                    => [
                'nullable',
                'exists:pets,id',
            ],
            'pet.name'                  => [
                // 沒有id就必填
                'required_without:pet.id',
                'max:255',
            ],
            'pet.pet_type_id'           => [
                // 沒有id就必填
                'required_without:pet.id',
                'exists:pet_types,id',
            ],
            'pet.weight'                => [
                'nullable',
                'numeric',
            ],
            'pet.birth_date'            => [
                'nullable',
                'date',
            ],
            'pet.gender'                => [
                'nullable',
                'in:male,female,unknown',
            ],
            'pet.is_default'            => [
                'boolean',
            ],
            'pet.user_id'               => [
                'nullable',
                'exists:users,id',
            ],
            'name'                      => [
                'nullable',
                'max:255',
            ],
            'phone'                     => [
                'nullable',
            ],
        ];

        $messages = [
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
            'pet_appointment_details.*.unique'   => '預約時段已存在',
            'customer_id.exists'                 => '顧客不存在',
            'pet.id.exists'                      => '寵物不存在',
            'pet.name.max'                       => '名字最多255個字元',
            'pet.name.required'                  => '名字為必填',
            'pet.pet_type_id.required'           => '寵物類型為必填',
            'pet.pet_type_id.exists'             => '寵物類型不存在',
            'pet.weight.numeric'                 => '體重格式錯誤',
            'pet.birth_date.date'                => '出生日期格式錯誤',
            'pet.gender.in'                      => '性別必須是 male, female 或 unknown',
            'pet.is_default.boolean'             => '是否為預設必須是布林值',
            'pet.user_id.exists'                 => '用戶不存在',
            'name.max'                           => '名字最多255個字元',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            // 檢查pet是否有id參數，沒有就新增，有就更新
            if ($request->has('pet.id') && $request->pet['id'] != null) {
                $pet = Pet::find($request->pet['id']);

                if (!$pet) {
                    return response()->json([
                        'message' => '找不到寵物資料',
                    ], 404);
                }

                $pet->update($request->pet);
            } else {
                // 寵物的使用者為空或是沒有，寫入目前登入的使用者
                if (!$request->has('pet.user_id')) {
                    // 取得目前的 pet 資料
                    $pet = $request->pet;
                    // 修改 user_id
                    $pet['user_id'] = $this->user->id;
                    // 將修改後的資料合併回 Request
                    $request->merge(['pet' => $pet]);
                }

                if ($request->has('pet.id')) {
                    // 移除pet.id的欄位
                    $request->except('pet.id');
                }

                $pet = Pet::create($request->pet);
            }
            $request->merge(['pet_id' => $pet->id]);

            $petAppointmentDetails = $request->pet_appointment_details;

            // 計算price
            $price        = 0;
            $serviceId    = $request->service_id;
            $servicePrice = Service::find($serviceId)->price;
            $petTypePrice = Pet::find($request->pet_id)->petTypePrices()->where('service_id', $serviceId)->first();
            $price        = $servicePrice + $petTypePrice->extra_price;

            if ($request->has('bath_product_id') && $request->bath_product_id != null) {
                $bathProductPrice  = BathProduct::find($request->bath_product_id)->price;
                $price            += $bathProductPrice;
            }

            // 與前端傳進來的不同以後端的計算為主
            if ($request->price != $price) {
                $request['price'] = $price;
            }

            $name  = $request->name;
            $phone = $request->phone;

            $petAppointment = PetAppointment::create($request->except(['pet_appointment_details', 'pet', 'name', 'phone']));

            $petAppointmentDetails  = array_map(function ($petAppointmentDetail) {
                return [
                    'user_time_slot_assignment_id' => $petAppointmentDetail,
                ];
            }, $petAppointmentDetails);

            $petAppointment->petAppointmentDetail()->createMany($petAppointmentDetails);

            // 更新顧客名字與電話
            $petAppointment->pet->user->update([
                'name'  => $name ?? $petAppointment->pet->user->name,
                'phone' => $phone ?? $petAppointment->pet->user->phone,
            ]);

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
            'service_id'                                             => [
                'required',
                'exists:services,id',
            ],
            'price'                                                  => [
                'nullable',
                'numeric',
            ],
            'bath_product_id'                                        => [
                'nullable',
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

            // 更新顧客名字與電話
            $petAppointment->pet->user->update([
                'name'  => $request->name,
                'phone' => $request->phone,
            ]);

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

    /**
     * @param Request $request
     * @param $id
     */
    public function updateStatus(Request $request, $id)
    {
        // 驗證
        $rules = [
            'status' => [
                'required',
                'in:booked,completed,cancelled,timeout',
            ],
        ];

        $messages = [
            'status.required' => '狀態為必填',
            'status.in'       => '狀態格式錯誤',
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

        return response()->json([
            'message' => '更新成功',
            'data'    => $petAppointment,
        ]);
    }
}
