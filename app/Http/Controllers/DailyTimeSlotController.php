<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\DailyTimeSlot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class DailyTimeSlotController extends Controller
{
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // 驗證參數，日期與時間
        $rules = [
            'slot_date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'slot_time' => [
                'required',
                'date_format:H:i:s',
            ],
        ];

        $messages = [
            'slot_date.required'    => '日期為必填',
            'slot_date.date_format' => '日期格式錯誤',
            'slot_time.required'    => '時間為必填',
            'slot_time.date_format' => '時間格式錯誤',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }

        // 取得日期與時間
        $slotDate = $request->input('slot_date');
        $slotTime = $request->input('slot_time');

        // 刪除指定的時間槽位
        $dailyTimeSlot = DailyTimeSlot::where('slot_date', $slotDate)
            ->where('slot_time', $slotTime)
            ->delete();

        return response()->json([
            'status'  => $dailyTimeSlot ? 'success' : 'fail',
            'message' => $dailyTimeSlot ? '刪除成功' : '刪除失敗',
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 驗證參數，傳入年跟月的資料，年為避填，月為必填
        $rules = [
            'date'    => [
                'required',
                'date',
            ],
            'user_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    // 檢查user_id是不是店員
                    $user = User::find($value);

                    if (!$user->hasAnyRole(['店員'])) {
                        $fail('使用者不是店員');
                    }
                },
            ],
        ];

        $messages = [
            'date.required'    => '日期為必填',
            'date.date'        => '日期格式錯誤',
            'user_id.required' => '使用者編號為必填',
            'user_id.exists'   => '使用者編號不存在',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }

        // 撈出使用者的時間槽位，並排除掉已有的時間槽位
        $timeSlots = DailyTimeSlot::where('slot_date', $request->input('date'))
            ->whereHas('UserTimeSlotAssignments', function ($query) use ($request) {
                $query->where('user_id', $request->input('user_id'));
            })->whereDoesntHave('petAppointmentDetails')
            ->with('UserTimeSlotAssignments')
            ->get(['id', 'slot_date', 'slot_time'])
            ->map(function ($timeSlot) {
                $userTimeSlotAssignmentId = $timeSlot->userTimeSlotAssignments->first()->id;

                return [
                    'id'                           => $timeSlot->id,
                    'slot_date'                    => $timeSlot->slot_date,
                    'slot_time'                    => $timeSlot->slot_time,
                    'user_time_slot_assignment_id' => $userTimeSlotAssignmentId,
                ];
            });

        // 整理取得的資料，結構為 [ '日期' => [ user_time_slot_assignment_id => '時段1', user_time_slot_assignment_id => '時段2', ... ] ]
        $timeSlots = $timeSlots->groupBy('slot_date')->map(function ($timeSlots) {
            return $timeSlots->pluck('slot_time', 'user_time_slot_assignment_id');
        });

        return response()->json([
            'data' => $timeSlots,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $dailyTimeSlot = DailyTimeSlot::find($id);

        if (!$dailyTimeSlot) {
            return response()->json([
                'message' => '找不到資料',
            ], 404);
        }

        return response()->json([
            'data' => $dailyTimeSlot,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 驗證參數，傳入年跟月的資料，年為避填，月為選填
        $rules = [
            'year'  => [
                'required',
                'date_format:Y',
            ],
            'month' => [
                'nullable',
                'date_format:m',
            ],
        ];

        $messages = [
            'year.required'     => '年份為必填',
            'year.date_format'  => '年份格式錯誤',
            'month.date_format' => '月份格式錯誤',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }

        // 取得年月
        $year  = $request->input('year');
        $month = $request->input('month', null);

        // 展開日期區間，沒有月份則展開一年的日期區間，有月份則展開該月份的日期區間
        $date2 = Carbon::create($year, $month ?? 1, 1);
        $start = $month ? $date2->copy()->startOfMonth() : $date2->copy()->startOfYear();
        $end   = $month ? $date2->copy()->endOfMonth() : $date2->copy()->endOfYear();

        // 取得日期區間內的每一天
        $dates = [];

        for ($date = $start->copy(); $date->lte($end->copy()); $date->addDay()) {
            $dates[] = $date->copy();
        }

        // 先準備所有可能的時間槽位
        $timeSlots = [];

        // 用於存儲日期+時間的組合鍵值
        $dateTimeKeys = [];

        foreach ($dates as $date) {
            $morning   = $this->getTimeSlots($date, 9, 12);
            $afternoon = $this->getTimeSlots($date, 14, 18);
            $times     = array_merge($morning, $afternoon);

            $dateString = $date->format('Y-m-d');

            foreach ($times as $time) {
                $key            = $dateString.'_'.$time;
                $dateTimeKeys[] = $key;

                $timeSlots[$key] = [
                    'slot_date' => $dateString,
                    'slot_time' => $time,
                ];
            }
        }

        // 一次性查詢所有已存在的時間槽位
        $existingSlots = DailyTimeSlot::whereBetween('slot_date', [$start->copy()->format('Y-m-d'), $end->copy()->format('Y-m-d')])
            ->get(['slot_date', 'slot_time'])
            ->map(function ($item) {
                return $item->slot_date.'_'.$item->slot_time;
            })->toArray();

        // 過濾掉已存在的時間槽位
        $newTimeSlots = [];

        foreach ($dateTimeKeys as $key) {
            if (!in_array($key, $existingSlots)) {
                $newTimeSlots[] = $timeSlots[$key];
            }
        }

        if (empty($newTimeSlots)) {
            return response()->json([
                'status'  => 'fail',
                'message' => '無需新增',
            ]);
        }

        $dailyTimeSlot = DailyTimeSlot::insert($newTimeSlots);

        return response()->json([
            'status'  => $dailyTimeSlot ? 'success' : 'fail',
            'message' => $dailyTimeSlot ? '新增成功' : '新增失敗',
        ]);
    }

    /**
     * 取得時間區間
     *
     * @param  Carbon  $date
     * @param  integer $startHour
     * @param  integer $endHour
     * @return array
     */
    private function getTimeSlots(Carbon $date, int $startHour, int $endHour): array
    {
        $timeSlots = [];

        for ($hour = $startHour; $hour < $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                // 時間格式為 HH:MM:SS
                $timeSlots[] = sprintf('%02d:%02d:00', $hour, $minute);
            }
        }

        return $timeSlots;
    }
}
