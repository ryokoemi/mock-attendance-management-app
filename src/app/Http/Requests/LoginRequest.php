<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^[A-Za-z0-9]+$/', // 半角英数字
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email'    => 'メールアドレスの形式で入力してください',

            'password.required' => 'パスワードを入力してください',
            'password.min'      => 'パスワードは8文字以上で入力してください',
            'password.regex'    => 'パスワードは半角英数字のみ使用できます',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // まず通常のバリデーションに通っているかを確認
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            // 入力されたメール・パスワードで一度だけ認証を試行
            $credentials = $this->only('email', 'password');

            if (!Auth::validate($credentials)) {
                // 認証NGの場合、共通メッセージを email フィールドに付ける
                $validator->errors()->add('email', 'ログイン情報が登録されていません');
            }

        });
    }
}