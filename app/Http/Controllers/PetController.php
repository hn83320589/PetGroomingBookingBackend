<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PetController extends Controller
{
    // 存入登入身分，0:顧客；1:店員或管理員
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
        // 檢查登入人是顧客還是店員或管理員
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
// 顧客要先檢查是不是自己的寵物，不是則不能刪除
        if ($this->loginRole == 0) {
            $pet = $this->user->pets()->find($id);
            if (!$pet) {
                return response()->json([
                    'message' => '非本人寵物，無法刪除',
                ], 403);
            }
        }

        // 刪除寵物
        $pet = Pet::find($id)->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
//顧客撈出自己的寵物清單，店員則撈出全部寵物清單
        if ($this->loginRole == 0) {
            $pets = $this->user->pets;
        } else {
            $pets = Pet::all();
        }

        return response()->json([
            'status' => 'success',
            'data'   => $pets->load('petAppointments.petAppointmentDetail'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
// 顧客要先檢查是不是自己的寵物，不是則不能查看
        if ($this->loginRole == 0) {
            $pet = $this->user->pets()->find($id);
            if (!$pet) {
                return response()->json([
                    'message' => '非本人寵物，無法查看',
                ], 403);
            }
        }

        $pet = Pet::find($id);

        return response()->json([
            'status' => 'success',
            'data'   => $pet->load('petAppointments.petAppointmentDetail'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 驗證
        $rules = [
            'name'        => [
                'required',
                'max:255',
            ],
            'pet_type_id' => [
                'required',
                'exists:pet_types,id',
            ],
            'gender'      => [
                'required',
                'in:male,female,unknown',
            ],
            'birth_date'  => [
                'required',
                'date',
            ],
            'weight'      => [
                'numeric',
            ],
            'user_id'     => [
                'exists:users,id',
            ],
        ];

        $messages = [
            'name.required'        => '名字為必填',
            'name.max'             => '名字最多255個字元',
            'pet_type_id.required' => '寵物類型為必填',
            'pet_type_id.exists'   => '寵物類型不存在',
            'gender.required'      => '性別為必填',
            'gender.in'            => '性別格式錯誤',
            'birth_date.required'  => '生日為必填',
            'birth_date.date'      => '生日格式錯誤',
            'weight.numeric'       => '體重格式錯誤',
            'user_id.exists'       => '飼主不存在',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }

// 顧客寵物的資料user_id只能是自己，店員可以幫其他顧客新增
        if ($this->loginRole == 0) {
            $request->merge(['user_id' => $this->user->id]);
        }

        $pet = Pet::create($request->all());

        return response()->json([
            'status' => 'success',
            'data'   => $pet,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 驗證
        $rules = [
            'name'        => [
                'max:255',
            ],
            'pet_type_id' => [
                'exists:pet_types,id',
            ],
            'gender'      => [
                'in:male,female,unknown',
            ],
            'birth_date'  => [
                'date',
            ],
            'weight'      => [
                'numeric',
            ],
            'user_id'     => [
                'exists:users,id',
            ],
        ];

        $messages = [
            'name.max'           => '名字最多255個字元',
            'pet_type_id.exists' => '寵物類型不存在',
            'gender.in'          => '性別格式錯誤',
            'birth_date.date'    => '生日格式錯誤',
            'weight.numeric'     => '體重格式錯誤',
            'user_id.exists'     => '飼主不存在',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

// 顧客寵物的資料user_id只能是自己，店員可以幫其他顧客更新
        if ($this->loginRole == 0) {
            $request->merge(['user_id' => $this->user->id]);
        }

        $pet = Pet::find($id);

        if (!$pet) {
            return response()->json([
                'message' => '找不到資料',
            ], 404);
        }

        $pet->update($request->all());

        return response()->json([
            'status' => 'success',
            'data'   => $pet,
        ]);
    }
}
