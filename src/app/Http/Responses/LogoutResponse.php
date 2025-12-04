<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // ログアウト前のセッションから is_admin を取得
        // または、直前のリクエスト URL で判定
        $previousUrl = $request->headers->get('referer', '');

        // 管理者画面からのログアウトか判定
        if (strpos($previousUrl, '/admin') !== false) {
            return redirect('/admin/login');
        }

        // デフォルトは /login へ
        return redirect('/login');
    }
}