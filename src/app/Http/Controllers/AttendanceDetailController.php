<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceDetailRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceDetailController extends Controller
{
    /**
     * 勤怠詳細の表示／更新
     * 一般ユーザー: 修正申請(pending)を作成
     * 管理者      : 勤怠本体を修正し、approved の CorrectionRequest を1件作成
     */
    public function showOrUpdate(AttendanceDetailRequest $request, $id)  // ★ Request → AttendanceDetailRequest
    {
        $attendance = Attendance::with('user', 'breakTimes', 'correctionRequests')->findOrFail($id);

        // ログインユーザー情報
        $user    = Auth::user();
        $isAdmin = (bool) ($user->is_admin ?? false);

        // ==========================
        // 更新系（POST/PUT/PATCH）
        // ==========================
        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch')) {
            // ★ 修正: 手書きの validate() を削除して、validated() を使う
            $validated = $request->validated();  // FormRequest が自動的にバリデーション

            // 管理者：勤怠本体を直接修正し、承認済みの CorrectionRequest を1件作成
            if ($isAdmin) {
                // 出勤・退勤
                $attendance->clock_in  = $validated['clock_in'] ?? $attendance->clock_in;
                $attendance->clock_out = $validated['clock_out'] ?? $attendance->clock_out;
                $attendance->save();

                // 休憩（break_times.* で送られてくる想定）
                $breakData = $validated['break_times'] ?? [];
                foreach ($breakData as $breakNum => $breakFields) {
                    // 空行はスキップ
                    if (empty($breakFields['break_in']) && empty($breakFields['break_out'])) {
                        continue;
                    }

                    $break = BreakTime::firstOrNew([
                        'attendance_id' => $attendance->id,
                        'break_num'     => $breakNum + 1,
                    ]);
                    $break->break_in  = $breakFields['break_in'];
                    $break->break_out = $breakFields['break_out'];
                    $break->save();
                }

                // 管理者による直接修正として、承認済み CorrectionRequest を1件作成
                CorrectionRequest::create([
                    'attendance_id' => $attendance->id,
                    'user_id'       => $attendance->user_id,
                    'reason'        => $validated['reason'] ?? '',
                    'status'        => 'approved',
                    'requested_at'  => now(),
                    'approved_at'   => now(),
                    'approver_id'   => $user->id,
                ]);

                // 修正後は同じ画面へ戻り、「修正しました」を表示
                return redirect()
                    ->route('admin.attendance.detail', ['id' => $attendance->id])
                    ->with('success', '修正しました。');
            }

            // 一般ユーザー：修正申請の作成（pending）
            if (!$attendance->correctionRequests()->where('status', 'pending')->exists()) {
                $attendance->correctionRequests()->create([
                    'user_id'      => $user->id,
                    'reason'       => $validated['reason'] ?? '',
                    'status'       => 'pending',
                    'requested_at' => now(),
                ]);
            }

            // 一般ユーザーは自画面へ戻る
            return redirect()->route('attendance.detail', ['id' => $attendance->id]);
        }

        // ==========================
        // GET：詳細表示
        // ==========================

        // 一般ユーザーで、承認待ちの申請があるかどうか
        $pendingRequestExists = !$isAdmin
            && $attendance->correctionRequests()->where('status', 'pending')->exists();

        // 最新の修正理由（一般・管理者 共通）
        $latestReason = optional(
            $attendance->correctionRequests()->orderByDesc('requested_at')->first()
        )->reason;

        return view('attendance-detail', [
            'attendance'           => $attendance,
            'breakTimes'           => $attendance->breakTimes,
            'user'                 => $user,
            'isAdmin'              => $isAdmin,
            'pendingRequestExists' => $pendingRequestExists,
            'latestReason'         => $latestReason,
        ]);
    }

    /**
     * 一般ユーザー: 日付ベースで勤怠詳細を開く
     */
    public function showByDate($date)
    {
        $user = Auth::user();
        $day = Carbon::parse($date)->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $day)
            ->first();

        if (!$attendance) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date'    => $day,
            ]);
        }

        return redirect()->route('attendance.detail', ['id' => $attendance->id]);
    }

    /**
     * 管理者: スタッフ＋日付ベースで勤怠詳細を開く
     */
    public function showByUserAndDate($userId, $date)
    {
        $day = Carbon::parse($date)->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $day)
            ->first();

        if (!$attendance) {
            $attendance = Attendance::create([
                'user_id' => $userId,
                'date'    => $day,
            ]);
        }

        return redirect()->route('admin.attendance.detail', ['id' => $attendance->id]);
    }
}