<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorrectionRequest;

class ApproveController extends Controller
{
    public function showOrUpdate(Request $request, $attendance_correct_request_id)
    {
        $correction = CorrectionRequest::findOrFail($attendance_correct_request_id);

        if ($request->isMethod('post')) {
            // 申請の承認処理のみ実行
            if ($correction->status !== 'approved') {
                $correction->status = 'approved';
                $correction->approved_at = now();
                $correction->approver_id = auth()->id();
                $correction->save();
            }

            // 最新状態の承認画面をそのまま表示（リダイレクト不要）
            // ボタンが「承認済み」に切り替わる
            // 必要なら最新のcorrection取得（再クエリ）してビューに渡す
            return view('request-approve', compact('correction'));
        }

        // GET時：申請内容を表示
        return view('request-approve', compact('correction'));
    }
}