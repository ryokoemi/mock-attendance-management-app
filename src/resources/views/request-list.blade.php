@extends('layouts.app')

@section('title', '申請一覧')

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
            <h1 class="attendance-list-title">申請一覧</h1>

            {{-- タブ --}}
            <div class="request-tabs">
                @php
                    $isPendingTab = $activeTab === 'pending';
                    $isApprovedTab = $activeTab === 'approved';
                @endphp

                <a href="{{ route('correction.index', ['tab' => 'pending']) }}"
                    class="request-tabs__item {{ $isPendingTab ? 'request-tabs__item--active' : '' }}">
                    承認待ち
                </a>

                <a href="{{ route('correction.index', ['tab' => 'approved']) }}"
                    class="request-tabs__item {{ $isApprovedTab ? 'request-tabs__item--active' : '' }}">
                    承認済み
                </a>
            </div>
            <div class="request-tabs__border"></div>

            {{-- 申請一覧テーブル --}}
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($corrections as $correction)
                        @php
                            $attendance = $correction->attendance;
                            $userName = $correction->user->name ?? '';
                            $targetDate = $attendance ? \Carbon\Carbon::parse($attendance->date)->format('Y/m/d') : '';
                            $requestDate = $correction->requested_at
                                ? \Carbon\Carbon::parse($correction->requested_at)->format('Y/m/d')
                                : '';
                            $statusLabel = $correction->status === 'approved' ? '承認済み' : '承認待ち';
                        @endphp
                        <tr>
                            <td>{{ $statusLabel }}</td>
                            <td>{{ $userName }}</td>
                            <td>{{ $targetDate }}</td>
                            <td>{{ $correction->reason }}</td>
                            <td>{{ $requestDate }}</td>
                            <td>
                                @if ($activeTab === 'pending')
                                    @if ($isAdmin)
                                        {{-- 管理者 承認待ち：承認画面(13)へ --}}
                                        <a href="{{ route('correction.approve', ['attendance_correct_request_id' => $correction->id]) }}"
                                            class="attendance-table__link-detail">
                                            詳細
                                        </a>
                                    @else
                                        {{-- 一般 承認待ち：自分の勤怠詳細(5)へ（承認待ち表示） --}}
                                        @if ($attendance)
                                            <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}"
                                                class="attendance-table__link-detail">
                                                詳細
                                            </a>
                                        @endif
                                    @endif
                                @else
                                    {{-- 承認済みタブ：一般も管理者も勤怠詳細(5/9)へ --}}
                                    @if ($attendance)
                                        @if ($isAdmin)
                                            <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}"
                                                class="attendance-table__link-detail">
                                                詳細
                                            </a>
                                        @else
                                            <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}"
                                                class="attendance-table__link-detail">
                                                詳細
                                            </a>
                                        @endif
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="attendance-table__empty">
                                データはありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
@endsection
