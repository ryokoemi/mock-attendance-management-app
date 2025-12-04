<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * 一般ユーザー・管理者 共通ログイン処理
     * （画面は Fortify::loginView で共通、is_admin フラグで出し分け）
     */
    public function store(LoginRequest $request)
    {
        // LoginRequest で形式＋認証チェックまで済んでいる想定
        // withValidator で Auth::validate 済みなら、ここでは Auth::attempt だけ行う

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember', false);

        // 実際にログイン（失敗ケースは withValidator 側で弾いているので基本 true になる想定）
        if (!Auth::attempt($credentials, $remember)) {
            // 念のための保険。ここに来たら再度エラーを返す。
            return back()
                ->withErrors(['email' => 'ログイン情報が登録されていません。'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        // LoginResponse クラスに任せる（管理者なら /admin/attendance/list へ、一般は /attendance へ）
        return app(\Laravel\Fortify\Contracts\LoginResponse::class)->toResponse($request);
    }
}