@extends('layouts.app')

@section('title', 'スタッフ一覧')

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

            {{-- タイトル --}}
            <h1 class="attendance-list-title">スタッフ一覧</h1>

            {{-- スタッフ一覧テーブル --}}
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th class="attendance-table__cell-name">名前</th>
                        <th>メールアドレス</th>
                        <th>月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staff as $member)
                        <tr>
                            <td class="attendance-table__cell-name">{{ $member->name }}</td>
                            <td>{{ $member->email }}</td>
                            <td>
                                <a href="{{ route('admin.attendance.staff', ['id' => $member->id]) }}"
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
