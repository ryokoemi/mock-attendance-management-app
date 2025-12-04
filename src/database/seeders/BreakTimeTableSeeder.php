<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakTimeTableSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::all();
        $faker = \Faker\Factory::create();

        foreach ($attendances as $attendance) {
            if (!$attendance->clock_in || !$attendance->clock_out) {
                continue;
            }

            $breakMax = rand(0, 3);

            for ($i = 1; $i <= $breakMax; $i++) {
                // すでに同じ休憩番号（break_num）がある場合はスキップ（冪等）
                if (BreakTime::where([
                    'attendance_id' => $attendance->id,
                    'break_num' => $i
                ])->exists()) {
                    continue;
                }

                $attendanceDate = Carbon::parse($attendance->date);
                $clockIn = Carbon::parse($attendance->clock_in);
                $clockOut = Carbon::parse($attendance->clock_out);

                $breakStartMin = $attendanceDate->copy()->setTime(10, 0);
                $breakStartMax = $attendanceDate->copy()->setTime(16, 0);

                if ($clockIn->gt($breakStartMin)) $breakStartMin = $clockIn->copy();
                if ($clockOut->lt($breakStartMax)) $breakStartMax = $clockOut->copy();
                if ($breakStartMin->gte($breakStartMax)) continue;

                $breakIn = $faker->dateTimeBetween($breakStartMin, $breakStartMax);
                $breakMinutes = rand(30, 90);
                $breakOut = (clone $breakIn)->modify("+{$breakMinutes} minutes");
                if ($breakOut > $clockOut) {$breakOut = $clockOut;
                }

                BreakTime::factory()
                    ->state([
                        'attendance_id' => $attendance->id,
                        'break_num' => $i,
                        'break_in' => $breakIn->format('Y-m-d H:i:s'),
                        'break_out' => $breakOut->format('Y-m-d H:i:s'),
                    ])
                    ->create();
            }
        }
    }
}