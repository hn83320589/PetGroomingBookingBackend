<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        User::destroy($id);

        return response()->json([
            'status' => 'success',
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 只有role是管理員與店員才可以撈出顧客清單
        if (!Auth::user()->hasAnyRole(['管理員', '店員'])) {
            return response()->json([
                'message' => '權限不足',
            ], 403);
        }

        // 撈出role為顧客的使用者
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', '顧客');
        })->get();

        return response()->json([
            'status' => 'success',
            'data'   => $users,
        ]);
    }

    /**
     * @param Request $request
     */
    public function login(Request $request)
    {
        // 驗證參數
        $rules = [
            'email'    => [
                'required',
                'email',
            ],
            'password' => [
                'required',
            ],
        ];

        $messages = [
            'email.required'    => 'Email為必填',
            'email.email'       => 'Email格式錯誤',
            'password.required' => '密碼為必填',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $email    = $request->input('email');
        $password = $request->input('password');

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            // 取得token
            $user  = User::where('email', $request->input('email'))->first();
            $token = $user->createToken('login');

            return response()->json($token);
        }

        return response()->json([
            'status'  => 'fail',
            'message' => '帳號或密碼錯誤',
        ], 401);
    }

    public function logout()
    {
        Auth::user()->token()->revoke();

        return response()->json([
            'status' => 'success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $user = Auth::user();

        return response()->json([
            'data' => $user->load('roles'),
        ]);
    }

    /**
     * 註冊使用者
     *
     * @param  Request $request
     * @return void
     */
    public function store(Request $request)
    {
        // 驗證參數
        $rules = [
            'name'                  => [
                'required',
                'max:255',
            ],
            'email'                 => [
                'required',
                'email',
                'max:255',
                'unique:users',
            ],
            'password'              => [
                'required',
                'confirmed',
                Password::min(8),
            ],
            'password_confirmation' => 'required',
        ];

        $messages = [
            'name.required'                  => '姓名為必填',
            'name.max'                       => '姓名最多255個字元',
            'email.required'                 => 'Email為必填',
            'email.email'                    => 'Email格式錯誤',
            'email.max'                      => 'Email最多255個字元',
            'email.unique'                   => 'Email已被使用',
            'password.required'              => '密碼為必填',
            'password.confirmed'             => '密碼不一致',
            'password.min'                   => '密碼最少8個字元',
            'password_confirmation.required' => '確認密碼為必填',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            // 建立使用者
            $user = User::create([
                'name'     => $request->input('name'),
                'email'    => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            // 預設角色為顧客
            $user->assignRole('顧客');

            return response()->json([
                'status' => 'success',
                'data'   => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'fail',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 驗證參數
        $rules = [
            'name' => [
                'required',
                'max:255',
            ],
        ];

        $messages = [
            'name.required' => '姓名為必填',
            'name.max'      => '姓名最多255個字元',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => '找不到資料',
            ], 404);
        }

        $user->update($request->all());

        return response()->json([
            'status' => 'success',
            'data'   => $user,
        ]);
    }
}
