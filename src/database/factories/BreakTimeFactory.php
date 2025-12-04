<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class BreakTimeFactory extends Factory
{
    public function definition()
    {
        // 仮のダミー値を設定。実際の用途ではstateで上書き予定
        $breakIn = $this->faker->dateTimeBetween('10:00:00', '16:00:00');
        $breakMinutes = $this->faker->numberBetween(30, 90);
        $breakOut = Carbon::instance($breakIn)->addMinutes($breakMinutes);

        return [
            'attendance_id' => 1, // ダミーID。テスト・Seederで上書き
            'break_num'     => 1, // 基本は1件目扱い
            'break_in'      => $breakIn->format('Y-m-d H:i:s'),
            'break_out'     => $breakOut->format('Y-m-d H:i:s'),
        ];
    }
}