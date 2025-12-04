<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '勤怠管理システム')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            {{-- 左側：ロゴ --}}
            <div class="header__logo-area">
                @php
        $user = Auth::user();
    @endphp

    @if ($user && ($user->is_admin ?? false))
        {{-- 管理者としてログイン中：/admin/attendance/list --}}
        <a href="{{ route('admin.attendance.daily') }}" class="header__logo">
            <img src="{{ asset('images/logo.svg') }}" alt="coachtech logo">
        </a>
    @elseif ($user)
        {{-- 一般ユーザーとしてログイン中：/attendance --}}
        <a href="{{ route('attendance.create_store') }}" class="header__logo">
            <img src="{{ asset('images/logo.svg') }}" alt="coachtech logo">
        </a>
    @else
        {{-- 未ログイン時：/login へ --}}
        <a href="{{ route('login') }}" class="header__logo">
            <img src="{{ asset('images/logo.svg') }}" alt="coachtech logo">
        </a>
    @endif
            </div>
            {{-- 右側ナビゲーション --}}
            <nav class="header__nav">
                @yield('header_links')
            </nav>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    @yield('scripts')
</body>

</html>
