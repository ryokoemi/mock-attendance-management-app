<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Attendance;

class CorrectionRequestFactory extends Factory
{
    public function definition()
    {
        // ダミー用だが外部キーがNULL不可の場合にも困らないようにセット
        return [
            'attendance_id'   => Attendance::factory(), // テスト等で自動生成
            'user_id'         => User::factory(),       // テスト等で自動生成
            'reason'          => $this->faker->realText(30),
            'status'          => $this->faker->randomElement(['pending', 'approved']),
            'requested_at'    => $this->faker->dateTimeThisMonth(),
            'approved_at'     => null, // 状態次第でnullにも
            'approver_id'     => null, // テストやstateで必要に応じて指定
        ];
    }
}