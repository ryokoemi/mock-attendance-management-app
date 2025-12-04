<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DailyListController extends Controller
{
    /**
     * 勤怠一覧画面（管理者向け）
     * ルート: GET /admin/attendance/list
     */
    public function index(Request $request)
    {
        \Log::info('=== DailyListController@index 開始 ===');

        try {
            $dateString = $request->query('date');
            \Log::info('dateString: ' . ($dateString ?? 'null'));
            
            $date = $dateString
                ? Carbon::createFromFormat('Y-m-d', $dateString)
                : Carbon::today();
            
            \Log::info('date確定: ' . $date->toDateString());
        } catch (\Exception $e) {
            \Log::error('Date parse error: ' . $e->getMessage());
            $date = Carbon::today();
        }

        \Log::info('Attendance クエリ開始');

        $attendances = Attendance::with(['user', 'breakTimes'])
            ->whereDate('date', $date->toDateString())
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('attendances.*')
            ->get();

        \Log::info('attendances 取得完了: ' . count($attendances) . '件');

        // 一覧用に、表示に必要な値を整形
        $rows = $attendances->map(function ($attendance) {
            // 出勤・退勤（00:00形式 or null）
            $clockIn  = $attendance->clock_in  ? Carbon::parse($attendance->clock_in)->format('H:i') : null;
            $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : null;

            // 休憩合計時間（分単位で集計→H:i形式へ）
            $totalBreakMinutes = 0;
            foreach ($attendance->breakTimes as $break) {
                if ($break->break_in && $break->break_out) {
                    $in  = Carbon::parse($break->break_in);
                    $out = Carbon::parse($break->break_out);
                    $totalBreakMinutes += $in->diffInMinutes($out);
                }
            }
            $breakTotal = $totalBreakMinutes > 0
                ? sprintf('%d:%02d', intdiv($totalBreakMinutes, 60), $totalBreakMinutes % 60)
                : null;

            // 勤務合計時間（出勤～退勤 − 休憩合計）
            $workTotal = null;
            if ($attendance->clock_in && $attendance->clock_out) {
                $in  = Carbon::parse($attendance->clock_in);
                $out = Carbon::parse($attendance->clock_out);
                $totalMinutes = $in->diffInMinutes($out) - $totalBreakMinutes;
                if ($totalMinutes < 0) {
                    $totalMinutes = 0;
                }
                $workTotal = sprintf('%d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
            }

            return [
                'attendance_id' => $attendance->id,
                'name'          => $attendance->user->name ?? '',
                'clock_in'      => $clockIn,
                'clock_out'     => $clockOut,
                'break_total'   => $breakTotal,
                'work_total'    => $workTotal,
            ];
        });

        \Log::info('rows 整形完了');

        // 前日・翌日リンク用
        $prevDate = $date->copy()->subDay()->toDateString();
        $nextDate = $date->copy()->addDay()->toDateString();

        \Log::info('view 返却前');

        return view('admin-attendance-list', [
            'date'     => $date,
            'rows'     => $rows,
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
        ]);
    }
}