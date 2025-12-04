<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function createOrStore(Request $request)
{
    $user     = Auth::user();
    $isAdmin  = (bool) ($user->is_admin ?? false);
    $today    = Carbon::today();
    $now      = Carbon::now();

    $todayDateString = $today->format('Y-m-d');

    // 本日の勤怠記録（自分のみ）
    $attendance = Attendance::where('user_id', $user->id)
        ->where('date', $todayDateString)
        ->first();

    // 画面共通で使う日付表示（例：2025年11月15日（土））
    $weekdayMap  = [
        'Sun' => '日', 'Mon' => '月', 'Tue' => '火',
        'Wed' => '水', 'Thu' => '木', 'Fri' => '金', 'Sat' => '土',
    ];
    $w           = $weekdayMap[$today->format('D')] ?? '';
    $displayDate = $today->format('Y年n月j日') . '（' . $w . '）';

    // ★ ここから追加：users.status_id から status コードを取得
    $statusCode = 'off'; // デフォルト
    if ($user->status_id && $user->status) {
        $statusCode = $user->status->code; // 'off', 'working', 'on_break', 'left'
    }

    // --------- ① 出勤前（まだ出勤打刻がない日） ---------
    if (!$attendance) {
        if ($request->isMethod('post') && $request->input('action') === 'clock_in') {
            Attendance::create([
                'user_id'  => $user->id,
                'date'     => $todayDateString,
                'clock_in' => $now->format('Y-m-d H:i:s'),
            ]);
            return redirect()->route('attendance.create_store');
        }

        return view('attendance', [
            'status'      => $statusCode,  // ★ users.status_id を使う
            'displayDate' => $displayDate,
            'initialTime' => $now->format('H:i'),
            'isAdmin'     => $isAdmin,
        ]);
    }

    // --------- ④ 退勤後（退勤打刻済み） ---------
    if ($attendance->clock_out) {
        $clockOut = Carbon::parse($attendance->clock_out);

        return view('attendance', [
            'status'      => 'left',          // 退勤済
            'displayDate' => $displayDate,
            'initialTime' => $clockOut->format('H:i'),
            'isAdmin'     => $isAdmin,
        ]);
    }

    // --------- ③ 休憩中（本日未終了の休憩が存在） ---------
    $break = BreakTime::where('attendance_id', $attendance->id)
        ->whereNull('break_out')
        ->latest('break_in')
        ->first();

    if ($break) {
        if ($request->isMethod('post') && $request->input('action') === 'break_out') {
            $break->break_out = $now->format('Y-m-d H:i:s');
            $break->save();
            return redirect()->route('attendance.create_store');
        }

        return view('attendance', [
            'status'      => 'on_break',      // 休憩中
            'displayDate' => $displayDate,
            'initialTime' => $now->format('H:i'),
            'isAdmin'     => $isAdmin,
        ]);
    }

    // --------- ② 出勤後（出勤済み＋退勤前＋休憩中でない） ---------
    if ($request->isMethod('post')) {
        $action = $request->input('action');

        if ($action === 'clock_out') {
            $attendance->clock_out = $now->format('Y-m-d H:i:s');
            $attendance->save();
            return redirect()->route('attendance.create_store');
        }

        if ($action === 'break_in') {
            $breakNum = BreakTime::where('attendance_id', $attendance->id)->count() + 1;
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_num'     => $breakNum,
                'break_in'      => $now->format('Y-m-d H:i:s'),
            ]);
            return redirect()->route('attendance.create_store');
        }
    }

    // GETまたはPOST後の再描画（出勤後）
    return view('attendance', [
        'status'      => 'working',         // 出勤中
        'displayDate' => $displayDate,
        'initialTime' => $now->format('H:i'),
        'isAdmin'     => $isAdmin,
    ]);
}
}