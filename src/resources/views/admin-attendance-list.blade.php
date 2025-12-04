@extends('layouts.app')

@section('title', '勤怠一覧（日次）')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

{{-- ヘッダー：パターン4（管理者用） --}}
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
    <div class="attendance-list-page">
        <div class="attendance-list-page__inner">

            @php
                $weekMap = [
                    'Sun' => '日',
                    'Mon' => '月',
                    'Tue' => '火',
                    'Wed' => '水',
                    'Thu' => '木',
                    'Fri' => '金',
                    'Sat' => '土',
                ];
                $w = $weekMap[$date->format('D')] ?? '';
                $titleDate = $date->format('Y年n月j日') . '（' . $w . '）';
                $centerLabel = $date->format('Y/m/d');
            @endphp

            {{-- タイトル：YYYY年M月D日の勤怠 --}}
            <h1 class="attendance-list-title">
                {{ $titleDate }}の勤怠
            </h1>

            {{-- 日付行（前日／翌日） --}}
            <div class="attendance-list-date-row">
                {{-- 左：前日 --}}
                <div class="attendance-list-date-nav">
                    <img src="{{ asset('images/arrow.png') }}" alt="">
                    <a href="{{ route('admin.attendance.daily', ['date' => $prevDate]) }}"
                        class="attendance-list-date-link">前日</a>
                </div>

                {{-- 中央：カレンダー＋YYYY/MM/DD --}}
                <div class="attendance-list-date-center">
                    <img src="{{ asset('images/calendar.png') }}" alt="">
                    <span>{{ $centerLabel }}</span>
                </div>

                {{-- 右：翌日 --}}
                <div class="attendance-list-date-nav">
                    <a href="{{ route('admin.attendance.daily', ['date' => $nextDate]) }}"
                        class="attendance-list-date-link">翌日</a>
                    <img src="{{ asset('images/arrow.png') }}" class="attendance-list-date-arrow--right" alt="">
                </div>
            </div>

            {{-- 当日分の勤怠テーブル --}}
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['clock_in'] }}</td>
                            <td>{{ $row['clock_out'] }}</td>
                            <td>{{ $row['break_total'] }}</td>
                            <td>{{ $row['work_total'] }}</td>
                            <td>
                                <a href="{{ route('admin.attendance.detail', ['id' => $row['attendance_id']]) }}"
                                    class="attendance-table__link-detail">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
@endsection
