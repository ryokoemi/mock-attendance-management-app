@extends('layouts.app')

@section('title', $isAdmin ? $targetUser->name . 'さんの勤怠' : '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

{{-- ヘッダー：一般(パターン2) or 管理者(パターン4) --}}
@section('header_links')
    @if ($isAdmin)
        {{-- パターン4：管理者 --}}
        <a href="{{ route('admin.attendance.daily') }}" class="header__nav-link">勤怠一覧</a>
        <a href="{{ route('admin.staff.list') }}" class="header__nav-link">スタッフ一覧</a>
        <a href="{{ route('correction.index') }}" class="header__nav-link">申請一覧</a>
        <form action="{{ route('logout') }}" method="POST" class="header__nav-form">
            @csrf
            <button type="submit" class="header__nav-link">ログアウト</button>
        </form>
    @else
        {{-- パターン2：一般ユーザー --}}
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
    <div class="attendance-list-page">
        <div class="attendance-list-page__inner">

            {{-- タイトル --}}
            <h1 class="attendance-list-title">
                @if ($isAdmin)
                    {{ $targetUser->name }}さんの勤怠
                @else
                    勤怠一覧
                @endif
            </h1>

            {{-- 年月行 --}}
            @php
                $displayMonth = sprintf('%04d/%02d', $currentYear, $currentMonth);
            @endphp
            <div class="attendance-list-date-row">
                {{-- 左：前月 --}}
                <div class="attendance-list-date-nav">
                    <img src="{{ asset('images/arrow.png') }}" alt="">
                    @if ($isAdmin)
                        <a href="{{ route('admin.attendance.staff', ['id' => $targetUser->id, 'year' => $prevYear, 'month' => $prevMonth]) }}"
                            class="attendance-list-date-link">前月</a>
                    @else
                        <a href="{{ route('attendance.user_list', ['year' => $prevYear, 'month' => $prevMonth]) }}"
                            class="attendance-list-date-link">前月</a>
                    @endif
                </div>

                {{-- 中央：カレンダー＋YYYY/MM --}}
                <div class="attendance-list-date-center">
                    <img src="{{ asset('images/calendar.png') }}" alt="">
                    <span>{{ $displayMonth }}</span>
                </div>

                {{-- 右：翌月 --}}
                <div class="attendance-list-date-nav">
                    @if ($isAdmin)
                        <a href="{{ route('admin.attendance.staff', ['id' => $targetUser->id, 'year' => $nextYear, 'month' => $nextMonth]) }}"
                            class="attendance-list-date-link">翌月</a>
                    @else
                        <a href="{{ route('attendance.user_list', ['year' => $nextYear, 'month' => $nextMonth]) }}"
                            class="attendance-list-date-link">翌月</a>
                    @endif
                    <img src="{{ asset('images/arrow.png') }}" class="attendance-list-date-arrow--right" alt="">
                </div>
            </div>

            {{-- 一ヶ月分のテーブル --}}
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($days as $day)
                        @php
                            /** @var \Carbon\Carbon $d */
                            $d = $day['date'];
                            $weekMap = [
                                'Sun' => '日',
                                'Mon' => '月',
                                'Tue' => '火',
                                'Wed' => '水',
                                'Thu' => '木',
                                'Fri' => '金',
                                'Sat' => '土',
                            ];
                            $w = $weekMap[$d->format('D')] ?? '';
                            $dateLabel = $d->format('m/d') . '（' . $w . '）';
                            $dateParam = $d->toDateString(); // '2025-11-01'
                        @endphp
                        <tr>
                            <td>{{ $dateLabel }}</td>
                            <td>{{ $day['clock_in'] }}</td>
                            <td>{{ $day['clock_out'] }}</td>
                            <td>{{ $day['break'] }}</td>
                            <td>{{ $day['total'] }}</td>
                            <td>
                                @if ($isAdmin)
                                    {{-- 管理者: スタッフ＋日付ベースで詳細へ --}}
                                    <a href="{{ route('admin.attendance.detail_by_user_date', [
                                        'userId' => $targetUser->id,
                                        'date' => $dateParam,
                                    ]) }}"
                                        class="attendance-table__link-detail">
                                        詳細
                                    </a>
                                @else
                                    {{-- 一般ユーザー: 日付ベースで詳細へ --}}
                                    <a href="{{ route('attendance.detail_by_date', ['date' => $dateParam]) }}"
                                        class="attendance-table__link-detail">
                                        詳細
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- 管理者用：CSV出力ボタン --}}
            @if ($isAdmin)
                <div class="attendance-list-csv">
                    <a href="{{ route('admin.attendance.staff', [
                        'id' => $targetUser->id,
                        'year' => $currentYear,
                        'month' => $currentMonth,
                        'export' => 'csv',
                    ]) }}"
                        class="attendance-list-csv__button">
                        CSV出力
                    </a>
                </div>
            @endif

        </div>
    </div>
@endsection
