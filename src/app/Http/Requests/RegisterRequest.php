<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 認可は Fortify 側とルートで制御
    }

    public function rules(): array
    {
        return [
            // 名前: 必須・20文字以内・記号不可（全角ひらがな/カタカナ/漢字・半角英数字・スペースのみ許可など、必要に応じて調整）
            'name' => [
                'required',
                'string',
                'max:20',
                'regex:/^[\pL\pN\s]+$/u', // 文字＋数字＋空白のみ（記号NG）
            ],

            // メール: 必須・メール形式・一意
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            // パスワード: 必須・8文字以上・半角英数・確認用と一致
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^[A-Za-z0-9]+$/', // 半角英数字のみ
                'confirmed',              // password_confirmation と一致
            ],

            // 確認用パスワード: 必須・8文字以上・半角英数
            'password_confirmation' => [
                'required',
                'string',
                'min:8',
                'regex:/^[A-Za-z0-9]+$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'お名前を入力してください',
            'name.max'      => 'お名前は20文字以内で入力してください',
            'name.regex'    => 'お名前に記号は使用できません',

            'email.required' => 'メールアドレスを入力してください',
            'email.email'    => 'メールアドレスの形式で入力してください',
            'email.unique'   => 'このメールアドレスは既に登録されています',

            'password.required'   => 'パスワードを入力してください',
            'password.min'        => 'パスワードは8文字以上で入力してください',
            'password.regex'      => 'パスワードは半角英数字のみ使用できます',
            'password.confirmed'  => 'パスワードと一致しません',

            'password_confirmation.required' => '確認用パスワードを入力してください',
            'password_confirmation.min'      => '確認用パスワードは8文字以上で入力してください',
            'password_confirmation.regex'    => '確認用パスワードは半角英数字のみ使用できます',
        ];
    }
}