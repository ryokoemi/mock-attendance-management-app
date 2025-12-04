@extends('layouts.app')

@section('title', '会員登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

{{-- ヘッダー：パターン1（ロゴのみ）なので links は空でOK --}}
@section('header_links')
@endsection

@section('content')
    <div class="l-container--narrow auth-page">
        <h1 class="auth-page__title">会員登録</h1>

        {{-- Fortify の register ルート --}}
        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            <div class="auth-form__group">
                <label class="auth-form__label" for="name">名前</label>
                <input id="name" type="text" name="name" class="auth-form__input" value="{{ old('name') }}">
                @error('name')
                    <p class="auth-form__error">{{ $message }}</p>
                @enderror
            </div>

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

            <div class="auth-form__group">
                <label class="auth-form__label" for="password_confirmation">パスワード確認</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="auth-form__input">
                @error('password_confirmation')
                    <p class="auth-form__error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="auth-form__button">
                登録する
            </button>

            <a href="{{ route('login') }}" class="auth-form__link">
                ログインはこちら
            </a>
        </form>
    </div>
@endsection
