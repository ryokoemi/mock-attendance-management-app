@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

{{-- ヘッダー：一般(パターン2) or 管理者(パターン4) --}}
@section('header_links')
    @if ($isAdmin)
        <a href="{{ route('admin.attendance.daily') }}" class="header__nav-link">勤怠一覧</a>
        <a href="{{ route('admin.staff.list') }}" class="header__nav-link">スタッフ一覧</a>
        <a href="{{ route('correction.index') }}" class="header__nav-link">申請一覧</a>
        <form action="{{ route('logout') }}" method="POST" class="header__nav-form">
            @csrf
            <button type="submit" class="header__nav-link">ログアウト</button>
        </form>
    @else
        <a href="{{ route('attendance.create_store') }}" class="header__nav-link">勤怠</a>
        <a href="{{ route('attendance.user_list') }}" class="header__nav-link">勤怠一覧</a>
        <a href="{{ route('correction.index') }}" class="header__nav-link">申請</a>
        <form action="{{ route('logout') }}" method="POST" class="header__nav-form">
            @csrf
            <button type="submit" class="header__nav-link">ログアウト</button>
        </form>
    @endif
@endsection

@section('content')
    @php
        $date = \Carbon\Carbon::parse($attendance->date);
    @endphp

    <div class="attendance-list-page">
        <div class="attendance-list-page__inner">

            {{-- タイトル --}}
            <h1 class="attendance-list-title">勤怠詳細</h1>

            {{-- 管理者修正後メッセージ --}}
            @if ($isAdmin && session('success'))
                <p class="attendance-detail-message">
                    {{ session('success') }}
                </p>
            @endif

            {{-- 詳細テーブル（5行2列をベースに、休憩は可変） --}}
            <form method="POST"
                action="{{ $isAdmin
                    ? route('admin.attendance.detail', ['id' => $attendance->id])
                    : route('attendance.detail', ['id' => $attendance->id]) }}">
                @csrf
                @method('POST')

                <table class="attendance-detail-table">
                    <tbody>
                        {{-- 名前 --}}
                        <tr>
                            <th>名前</th>
                            <td>
                                {{ $attendance->user->name }}
                            </td>
                        </tr>

                        {{-- 日付 --}}
                        <tr>
                            <th>日付</th>
                            <td>
                                {{ $date->format('Y年 n月 j日') }}
                            </td>
                        </tr>

                        {{-- 出勤・退勤 --}}
                        <tr>
                            <th>出勤・退勤</th>
                            <td>
                                @php
                                    $clockIn = $attendance->clock_in
                                        ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                                        : '';
                                    $clockOut = $attendance->clock_out
                                        ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                                        : '';
                                @endphp
                                <input type="text" name="clock_in" value="{{ old('clock_in', $clockIn) }}"
                                    class="attendance-detail-input">
                                <span>〜</span>
                                <input type="text" name="clock_out" value="{{ old('clock_out', $clockOut) }}"
                                    class="attendance-detail-input">
                                @error('clock_in')
                                    <div class="attendance-detail-error">{{ $message }}</div>
                                @enderror
                                @error('clock_out')
                                    <div class="attendance-detail-error">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>

                        {{-- 休憩 --}}
                        @php
                            $breaks = $breakTimes->sortBy('break_num')->values();
                            $max = max($breaks->count() + 1, 1);
                        @endphp

                        @for ($i = 0; $i < $max; $i++)
                            @php
                                $break = $breaks[$i] ?? null;
                                $label = $i === 0 ? '休憩' : '休憩' . ($i + 1);
                                $in =
                                    $break && $break->break_in
                                        ? \Carbon\Carbon::parse($break->break_in)->format('H:i')
                                        : '';
                                $out =
                                    $break && $break->break_out
                                        ? \Carbon\Carbon::parse($break->break_out)->format('H:i')
                                        : '';
                            @endphp
                            <tr>
                                <th>{{ $label }}</th>
                                <td>
                                    <input type="text" name="break_times[{{ $i }}][break_in]"
                                        value="{{ old("break_times.$i.break_in", $in) }}" class="attendance-detail-input">
                                    <span>〜</span>
                                    <input type="text" name="break_times[{{ $i }}][break_out]"
                                        value="{{ old("break_times.$i.break_out", $out) }}"
                                        class="attendance-detail-input">
                                    @error("break_times.$i.break_in")
                                        <div class="attendance-detail-error">{{ $message }}</div>
                                    @enderror
                                    @error("break_times.$i.break_out")
                                        <div class="attendance-detail-error">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                        @endfor

                        {{-- 備考 --}}
                        <tr>
                            <th>備考</th>
                            <td>
                                <textarea name="reason" rows="3" class="attendance-detail-textarea">{{ old('reason', $latestReason) }}</textarea>
                                @error('reason')
                                    <div class="attendance-detail-error">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- フッターメッセージ or 修正ボタン --}}
                <div class="attendance-detail-footer">
                    @if (!$isAdmin && $pendingRequestExists)
                        <p class="attendance-detail-footer__note">
                            *承認待ちのため修正はできません。
                        </p>
                    @else
                        <button type="submit" class="attendance-detail-submit">
                            修正
                        </button>
                    @endif
                </div>
            </form>

        </div>
    </div>
@endsection
