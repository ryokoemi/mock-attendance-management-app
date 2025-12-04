<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\MonthlyListController;
use App\Http\Controllers\DailyListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\StaffListController;
use App\Http\Controllers\ApproveController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;

Route::middleware('guest')->get('/admin/login', function () {
    return view('auth.login', ['isAdminLogin' => true]);
})->name('admin.login');

// Fortify の登録・ログイン画面を使いつつ、POST だけ FormRequest 付きコントローラへ
Route::post('/register', [RegisterController::class, 'store'])->name('register');
Route::post('/login', [LoginController::class, 'store'])->name('login');

// 共通ホーム（ロゴクリック用）
Route::middleware('auth')->get('/home', function (Request $request) {
    $user = $request->user();

    if ($user && ($user->is_admin ?? false)) {
        return redirect()->route('admin.attendance.daily');
    }

    return redirect()->route('attendance.create_store');
})->name('home');

// 一般ユーザー向け
Route::middleware('auth')->group(function () {
    // 勤怠登録
    Route::match(['get', 'post'], '/attendance', [AttendanceController::class, 'createOrStore'])
        ->name('attendance.create_store');

    // 勤怠一覧
    Route::get('/attendance/list', [MonthlyListController::class, 'userList'])
        ->name('attendance.user_list');

    // 勤怠詳細（一般用）
    Route::match(['get', 'post'], '/attendance/detail/{id}', [AttendanceDetailController::class, 'showOrUpdate'])
        ->name('attendance.detail');

    // 日付ベースで詳細（勤怠が無い日も含めて）
    Route::get('/attendance/detail/date/{date}', [AttendanceDetailController::class, 'showByDate'])
        ->name('attendance.detail_by_date');
});

// 修正申請一覧（一般・管理者 共通）
Route::middleware(['auth', 'check.role.for.correction'])
    ->get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])
    ->name('correction.index');

// 管理者専用
Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    // ★ 重要：/list を {id} より先に定義する
    // 勤怠一覧（日次 全スタッフ分）
    Route::get('/attendance/list', [DailyListController::class, 'index'])
        ->name('attendance.daily');

    // スタッフ一覧
    Route::get('/staff/list', [StaffListController::class, 'index'])
        ->name('staff.list');

    // スタッフ別勤怠（月次/日次）
    Route::get('/attendance/staff/{id}', [MonthlyListController::class, 'staffList'])
        ->name('attendance.staff');

    // 日付ベースで勤怠詳細を開く
    Route::get('/attendance/detail/user/{userId}/date/{date}', [AttendanceDetailController::class, 'showByUserAndDate'])
        ->name('attendance.detail_by_user_date');

    // 勤怠詳細（管理者用）※/admin/attendance/{id}
    // ★ 重要：{id} は最後に定義する
    Route::match(['get', 'post', 'put', 'patch'], '/attendance/{id}', [AttendanceDetailController::class, 'showOrUpdate'])
        ->name('attendance.detail');
});

// 修正申請承認（管理者専用）
Route::middleware(['auth', 'can:admin'])
    ->match(['get', 'post'], '/stamp_correction_request/approve/{attendance_correct_request_id}', [ApproveController::class, 'showOrUpdate'])
    ->name('correction.approve');
