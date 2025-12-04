@extends('layouts.app')

@section('title', '勤怠登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

{{-- ヘッダー：管理者ならパターン4、それ以外は status でパターン2/3 --}}
@section('header_links')
    @if (!empty($isAdmin) && $isAdmin)
        {{-- パターン4：管理者用ヘッダー --}}
        <a href="{{ route('admin.attendance.daily') }}" class="header__nav-link">勤怠一覧</a>
        <a href="{{ route('admin.staff.list') }}" class="header__nav-link">スタッフ一覧</a>
        <a href="{{ route('correction.index') }}" class="header__nav-link">申請一覧</a>
        <form action="{{ route('logout') }}" method="POST" class="header__nav-form">
            @csrf
            <button type="submit" class="header__nav-link">ログアウト</button>
        </form>
    @else
        @if ($status !== 'left')
            {{-- パターン2：一般ユーザー用ヘッダー（出勤前／出勤中／休憩中） --}}
            <a href="{{ route('attendance.create_store') }}" class="header__nav-link">勤怠</a>
            <a href="{{ route('attendance.user_list') }}" class="header__nav-link">勤怠一覧</a>
            <a href="{{ route('correction.index') }}" class="header__nav-link">申請</a>
            <form action="{{ route('logout') }}" method="POST" class="header__nav-form">
                @csrf
                <button type="submit" class="header__nav-link">ログアウト</button>
            </form>
        @else
            {{-- パターン3：退勤後専用ヘッダー --}}
            <a href="{{ route('attendance.user_list') }}" class="header__nav-link">今月の出勤一覧</a>
            <a href="{{ route('correction.index') }}" class="header__nav-link">申請一覧</a>
            <form action="{{ route('logout') }}" method="POST" class="header__nav-form">
                @csrf
                <button type="submit" class="header__nav-link">ログアウト</button>
            </form>
        @endif
    @endif
@endsection

@section('content')

    {{-- $status: 'off' | 'working' | 'on_break' | 'left' --}}
    <div class="attendance-page">
        <div class="attendance-page__inner">

            {{-- ステータスバッジ --}}
            <div class="attendance-status">
                @if ($status === 'off')
                    勤務外
                @elseif($status === 'working')
                    出勤中
                @elseif($status === 'on_break')
                    休憩中
                @elseif($status === 'left')
                    退勤済
                @endif
            </div>

            {{-- 日付 --}}
            <div class="attendance-date">
                {{ $displayDate }}
            </div>

            {{-- 時刻表示：退勤後は退勤時刻、それ以外は現在時刻（JSで更新） --}}
            <div id="attendance-time" class="attendance-time">
                {{ $initialTime }}
            </div>

            {{-- ボタン群 --}}
            @if ($status === 'off')
                {{-- パターン① 出勤前 --}}
                <div class="attendance-buttons attendance-buttons--single">
                    <form method="POST" action="{{ route('attendance.create_store') }}">
                        @csrf
                        <button type="submit" name="action" value="clock_in"
                            class="attendance-button attendance-button--primary">
                            出勤
                        </button>
                    </form>
                </div>
            @elseif($status === 'working')
                {{-- パターン② 出勤後 --}}
                <div class="attendance-buttons">
                    <form method="POST" action="{{ route('attendance.create_store') }}">
                        @csrf
                        <button type="submit" name="action" value="clock_out"
                            class="attendance-button attendance-button--primary">
                            退勤
                        </button>
                    </form>

                    <form method="POST" action="{{ route('attendance.create_store') }}">
                        @csrf
                        <button type="submit" name="action" value="break_in"
                            class="attendance-button attendance-button--secondary">
                            休憩入
                        </button>
                    </form>
                </div>
            @elseif($status === 'on_break')
                {{-- パターン③ 休憩中 --}}
                <div class="attendance-buttons attendance-buttons--single">
                    <form method="POST" action="{{ route('attendance.create_store') }}">
                        @csrf
                        <button type="submit" name="action" value="break_out"
                            class="attendance-button attendance-button--secondary">
                            休憩戻
                        </button>
                    </form>
                </div>
            @elseif($status === 'left')
                {{-- パターン④ 退勤後 --}}
                <div class="attendance-message">
                    お疲れ様でした。
                </div>
            @endif

        </div>
    </div>

    {{-- 現在時刻の自動更新（退勤後以外） --}}
    @if ($status !== 'left')
        <script>
            function updateAttendanceTime() {
                const el = document.getElementById('attendance-time');
                if (!el) return;
                const now = new Date();
                const hh = String(now.getHours()).padStart(2, '0');
                const mm = String(now.getMinutes()).padStart(2, '0');
                el.textContent = hh + ':' + mm;
            }
            updateAttendanceTime();
            setInterval(updateAttendanceTime, 1000 * 30);
        </script>
    @endif
@endsection
