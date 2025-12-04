<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonthlyListController extends Controller
{
    /**
     * (4) 勤怠一覧画面（一般ユーザー向け）
     * ルート: GET /attendance/list
     * 今月の自分の勤怠を表示（年月はクエリ ?year=YYYY&month=MM で切替）
     */
    public function userList(Request $request)
    {
        $user = Auth::user();

        // 対象年月（デフォルト: 今月）
        $year  = (int) ($request->query('year', now()->year));
        $month = (int) ($request->query('month', now()->month));

        $date = Carbon::create($year, $month, 1);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth   = $date->copy()->endOfMonth();

        // 対象ユーザーは自分
        $targetUser = $user;

        // 対象月の勤怠を取得
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $targetUser->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('date')
            ->get()
            ->keyBy('date'); // date => Attendance

        // 1ヶ月分の日付ごとの表示用データを組み立て
        $days = [];
        $cursor = $startOfMonth->copy();
        while ($cursor->lte($endOfMonth)) {
            $dateKey = $cursor->toDateString();
            $attendance = $attendances->get($dateKey);

            $clockIn  = $attendance?->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '';
            $clockOut = $attendance?->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '';

            // 休憩合計
            $breakTotalMinutes = 0;
            if ($attendance && $attendance->breakTimes->count()) {
                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_in && $break->break_out) {
                        $in  = Carbon::parse($break->break_in);
                        $out = Carbon::parse($break->break_out);
                        $breakTotalMinutes += $in->diffInMinutes($out);
                    }
                }
            }
            $breakTotal = $breakTotalMinutes > 0
                ? sprintf('%d:%02d', intdiv($breakTotalMinutes, 60), $breakTotalMinutes % 60)
                : '';

            // 勤務合計（単純に出勤〜退勤から休憩を引く）
            $workTotal = '';
            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                $in  = Carbon::parse($attendance->clock_in);
                $out = Carbon::parse($attendance->clock_out);
                $workMinutes = $in->diffInMinutes($out) - $breakTotalMinutes;
                if ($workMinutes < 0) {
                    $workMinutes = 0;
                }
                $workTotal = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
            }

            $days[] = [
                'date'       => $cursor->copy(),   // Carbon（MM/DD（曜）表示用）
                'attendance' => $attendance,
                'clock_in'   => $clockIn,
                'clock_out'  => $clockOut,
                'break'      => $breakTotal,
                'total'      => $workTotal,
            ];

            $cursor->addDay();
        }

        // 前月・翌月リンク用
        $prevMonth = $date->copy()->subMonth();
        $nextMonth = $date->copy()->addMonth();

        return view('attendance-list', [
            'user'        => $user,
            'isAdmin'     => false,          // ヘッダーパターン2
            'targetUser'  => $targetUser,
            'currentYear' => $year,
            'currentMonth'=> $month,
            'prevYear'    => $prevMonth->year,
            'prevMonth'   => $prevMonth->month,
            'nextYear'    => $nextMonth->year,
            'nextMonth'   => $nextMonth->month,
            'days'        => $days,
            // 詳細リンク先: 一般は route('attendance.detail', ['id' => $attendance->id])
        ]);
    }

    /**
     * (11) スタッフ別勤怠一覧画面（管理者向け）
     * ルート: GET /admin/attendance/staff/{id}
     * 今月の指定スタッフの勤怠を表示 & CSV出力
     */
    public function staffList(Request $request, $id)
    {
        $admin = Auth::user(); // 管理者想定（ルートでcan:admin保護済み）

        // 対象スタッフ
        $targetUser = User::findOrFail($id);

        // 対象年月（デフォルト: 今月）
        $year  = (int) ($request->query('year', now()->year));
        $month = (int) ($request->query('month', now()->month));

        $date = Carbon::create($year, $month, 1);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth   = $date->copy()->endOfMonth();

        // 対象月の勤怠
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $targetUser->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $days = [];
        $cursor = $startOfMonth->copy();
        while ($cursor->lte($endOfMonth)) {
            $dateKey = $cursor->toDateString();
            $attendance = $attendances->get($dateKey);

            $clockIn  = $attendance?->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '';
            $clockOut = $attendance?->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '';

            $breakTotalMinutes = 0;
            if ($attendance && $attendance->breakTimes->count()) {
                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_in && $break->break_out) {
                        $in  = Carbon::parse($break->break_in);
                        $out = Carbon::parse($break->break_out);
                        $breakTotalMinutes += $in->diffInMinutes($out);
                    }
                }
            }
            $breakTotal = $breakTotalMinutes > 0
                ? sprintf('%d:%02d', intdiv($breakTotalMinutes, 60), $breakTotalMinutes % 60)
                : '';

            $workTotal = '';
            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                $in  = Carbon::parse($attendance->clock_in);
                $out = Carbon::parse($attendance->clock_out);
                $workMinutes = $in->diffInMinutes($out) - $breakTotalMinutes;
                if ($workMinutes < 0) {
                    $workMinutes = 0;
                }
                $workTotal = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
            }

            $days[] = [
                'date'       => $cursor->copy(),
                'attendance' => $attendance,
                'clock_in'   => $clockIn,
                'clock_out'  => $clockOut,
                'break'      => $breakTotal,
                'total'      => $workTotal,
            ];

            $cursor->addDay();
        }

        // CSV出力リクエストかどうか（例: ?export=csv）
        if ($request->query('export') === 'csv') {
            return $this->exportCsv($targetUser, $year, $month, $days);
        }

        $prevMonth = $date->copy()->subMonth();
        $nextMonth = $date->copy()->addMonth();

        return view('attendance-list', [
            'user'        => $admin,
            'isAdmin'     => true,         // ヘッダーパターン4
            'targetUser'  => $targetUser,
            'currentYear' => $year,
            'currentMonth'=> $month,
            'prevYear'    => $prevMonth->year,
            'prevMonth'   => $prevMonth->month,
            'nextYear'    => $nextMonth->year,
            'nextMonth'   => $nextMonth->month,
            'days'        => $days,
            // 詳細リンク先: 管理者は route('admin.attendance.detail', ['id' => ...])
            // CSVボタン用: route('admin.attendance.staff', ['id' => $targetUser->id, 'year'=>..., 'month'=>..., 'export'=>'csv'])
        ]);
    }

    /**
     * スタッフ別・指定年月の勤怠一覧CSV出力
     */
    protected function exportCsv(User $user, int $year, int $month, array $days): StreamedResponse
    {
        $filename = sprintf('%s_%04d%02d_attendance.csv', $user->name, $year, $month);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($user, $year, $month, $days) {
            $handle = fopen('php://output', 'w');

            // 文字化け対策でBOMを付与（Excel想定の場合）
            fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // ヘッダ行
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩合計', '勤務合計']);

            foreach ($days as $row) {
                $date = $row['date']->format('Y-m-d');
                fputcsv($handle, [
                    $date,
                    $row['clock_in'],
                    $row['clock_out'],
                    $row['break'],
                    $row['total'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}