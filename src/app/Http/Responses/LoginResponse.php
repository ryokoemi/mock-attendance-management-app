<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        $isAdmin = $user && (int)($user->is_admin ?? 0) === 1;

        if ($isAdmin) {
            return redirect()->route('admin.attendance.daily'); // /admin/attendance/list
            }

        // 一般ユーザーは /attendance へ
        return redirect()->route('attendance.create_store');   // /attendance
    }

}