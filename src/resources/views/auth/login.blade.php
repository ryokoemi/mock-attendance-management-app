@extends('layouts.app')

@section('title', isset($isAdminLogin) && $isAdminLogin ? '管理者ログイン' : 'ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

{{-- ヘッダー：パターン1（ロゴのみ） --}}
@section('header_links')
@endsection

@section('content')
    <div class="l-container--narrow auth-page">
        <h1 class="auth-page__title">
            {{ isset($isAdminLogin) && $isAdminLogin ? '管理者ログイン' : 'ログイン' }}
        </h1>

        {{-- Fortify の login ルート --}}
        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="auth-form__group">
                <label class="auth-form__label" for="email">メールアドレス</label>
                <input id="email" type="text" name="email" class="auth-form__input" value="{{ old('email') }}">
                @error('email')
                    <p class="auth-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-form__group">
                <label class="auth-form__label" for="password">パスワード</label>
                <input id="password" type="password" name="password" class="auth-form__input">
                @error('password')
                    <p class="auth-form__error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="auth-form__button">
                {{ isset($isAdminLogin) && $isAdminLogin ? '管理者ログインする' : 'ログインする' }}
            </button>

            {{-- 管理者ログイン画面では会員登録リンクを出さない --}}
            @if (empty($isAdminLogin))
                <a href="{{ route('register') }}" class="auth-form__link">
                    会員登録はこちら
                </a>
            @endif
        </form>
    </div>
@endsection
