<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;

class CorrectionRequestTableSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::all();
        $faker = \Faker\Factory::create();

        // 管理者のみ（is_admin=true）
        $admins = User::where('is_admin', true)->get();

        foreach ($attendances->shuffle()->take(20) as $attendance) {
            if (!CorrectionRequest::where('attendance_id', $attendance->id)->exists()) {
                // ★ 修正：ランダムではなく、attendance の所有者を使う
                $user = $attendance->user;

                $status = $faker->randomElement(['pending', 'approved']);
                $extra = [];

                if ($status === 'approved') {
                    // 承認者は管理者からランダム
                    $approver = $admins->random();
                    $extra = [
                        'approved_at' => now(),
                        'approver_id' => $approver->id,
                    ];
                }

                CorrectionRequest::factory()->state(array_merge([
                    'attendance_id' => $attendance->id,
                    'user_id'       => $user->id,  // ★ attendance の所有者 ID
                    'status'        => $status,
                    'reason'        => $faker->randomElement(['遅延のため', '中抜けのため']),
                    'requested_at'  => now(),
                ], $extra))->create();
            }
        }
    }
}