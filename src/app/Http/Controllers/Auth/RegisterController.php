<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    protected CreateNewUser $creator;

    public function __construct(CreateNewUser $creator)
    {
        $this->creator = $creator;
    }

    /**
     * 一般ユーザー登録処理
     */
    public function store(RegisterRequest $request)
    {
        // RegisterRequest で既にバリデーション済み

        // ユーザー作成（Fortify のアクションを利用）
        $user = $this->creator->create($request->only('name', 'email', 'password'));

        // そのままログインさせる
        Auth::login($user);

        // Fortify / RouteServiceProvider の設定どおりにリダイレクト
        return redirect()->intended('/attendance');
    }
}