<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use Illuminate\Http\Request;

class CorrectionRequestController extends Controller
{
    /**
     * 申請一覧画面（一般ユーザー・管理者 共通）
     * ルート: GET /stamp_correction_request/list
     */
    public function index(Request $request)
    {
        $user    = $request->user();
        // ミドルウェアで埋め込んだフラグを取得
        $isAdmin = (bool) $request->attributes->get('is_admin_request', false);

        // タブ選択（default: pending）
        $tab = $request->query('tab', 'pending'); // 'pending' or 'approved'

        // ベースクエリ
        $query = CorrectionRequest::with(['user', 'attendance'])
            ->orderByDesc('requested_at');

        // 一般ユーザーは自分の申請のみ
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }
        // 管理者は全員分そのまま

        // タブごとのstatus条件
        if ($tab === 'approved') {
            $query->where('status', 'approved');
        } else {
            $query->where('status', 'pending');
        }

        $corrections = $query->get();

        return view('request-list', [
            'user'        => $user,
            'isAdmin'     => $isAdmin,   // ヘッダー2/4 出し分け用
            'activeTab'   => $tab,       // 'pending' or 'approved'
            'corrections' => $corrections,
        ]);
    }
}