@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

{{-- ヘッダー：パターン4（管理者） --}}
@section('header_links')
    <a href="{{ route('admin.attendance.daily') }}" class="header__nav-link">勤怠一覧</a>
    <a href="{{ route('admin.staff.list') }}" class="header__nav-link">スタッフ一覧</a>
    <a href="{{ route('correction.index') }}" class="header__nav-link">申請一覧</a>
    <form action="{{ route('logout') }}" method="POST" class="header__nav-form">
        @csrf
        <button type="submit" class="header__nav-link">ログアウト</button>
    </form>
@endsection

@section('content')
    @php
        $attendance = $correction->attendance;
        $user = $attendance->user;
        $date = \Carbon\Carbon::parse($attendance->date);
        $clockIn = $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '';
        $clockOut = $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '';
    @endphp

    <div class="attendance-list-page">
        <div class="attendance-list-page__inner">

            {{-- タイトル --}}
            <h1 class="attendance-list-title">勤怠詳細</h1>

            <form method="POST"
                action="{{ route('correction.approve', ['attendance_correct_request_id' => $correction->id]) }}">
                @csrf

                <table class="attendance-detail-table">
                    <tbody>
                        {{-- 名前 --}}
                        <tr>
                            <th>名前</th>
                            <td>
                                {{ $user->name }}
                            </td>
                        </tr>

                        {{-- 日付 --}}
                        <tr>
                            <th>日付</th>
                            <td>
                                {{ $date->format('Y年 n月 j日') }}
                            </td>
                        </tr>

                        {{-- 出勤・退勤（閲覧のみ） --}}
                        <tr>
                            <th>出勤・退勤</th>
                            <td>
                                {{ $clockIn }}　〜　{{ $clockOut }}
                            </td>
                        </tr>

                        {{-- 休憩 --}}
                        @php
                            $breaks = $attendance->breakTimes->sortBy('break_num')->values();
                        @endphp
                        @foreach ($breaks as $i => $break)
                            @php
                                $label = $i === 0 ? '休憩' : '休憩' . ($i + 1);
                                $in = $break->break_in ? \Carbon\Carbon::parse($break->break_in)->format('H:i') : '';
                                $out = $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '';
                            @endphp
                            <tr>
                                <th>{{ $label }}</th>
                                <td>
                                    {{ $in }}　〜　{{ $out }}
                                </td>
                            </tr>
                        @endforeach
                        @if ($breaks->isEmpty())
                            <tr>
                                <th>休憩</th>
                                <td></td>
                            </tr>
                        @endif

                        {{-- 備考（申請理由） --}}
                        <tr>
                            <th>備考</th>
                            <td>
                                {{ $correction->reason }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- 右下：承認 or 承認済みボタン --}}
                <div class="attendance-detail-footer">
                    @if ($correction->status === 'approved')
                        <button type="button" class="approval-button approval-button--done" disabled>
                            承認済み
                        </button>
                    @else
                        <button type="submit" class="approval-button approval-button--primary">
                            承認
                        </button>
                    @endif
                </div>
            </form>

        </div>
    </div>
@endsection
