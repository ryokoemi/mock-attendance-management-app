<?php

namespace App\Http\Controllers;

use App\Models\User;

class StaffListController extends Controller
{
    /**
     * スタッフ一覧画面（管理者向け）
     * ルート: GET /admin/staff/list
     */
    public function index()
    {
        // 管理者以外（is_admin = false）のユーザーをスタッフとして一覧表示
        $staff = User::where('is_admin', false)
            ->orderBy('name')
            ->get();

        return view('staff-list', [
            'staff'    => $staff,
            'isAdmin'  => true,  // ヘッダー4を出すためのフラグ
        ]);
    }
}