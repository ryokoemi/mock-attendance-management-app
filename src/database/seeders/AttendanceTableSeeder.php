<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceTableSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('is_admin', false)->get();

        foreach ($users as $user) {
            $dates = collect(range(0, 89))
                ->map(function ($i) {
                    return now()->copy()->subDays($i)->format('Y-m-d');
                })->shuffle()->take(60); // 最大60日

            foreach ($dates as $date) {
                // 既存データチェック
                $exists = Attendance::where([
                    'user_id' => $user->id,
                    'date' => $date,
                ])->exists();

                if (!$exists) {

                    $hour = rand(8, 10);
                    $minute = rand(0, 59);
                    $second = rand(0, 59);
                    $clockInTime = Carbon::parse("$date $hour:$minute:$second");
                    $workHours = rand(6, 10);
                    $clockOutTime = $clockInTime->copy()->addHours($workHours);
                    if ($clockOutTime->hour > 19) {
                        $clockOutTime->setTime(19, 0);
                    }

                    Attendance::create([
                        'user_id' => $user->id,
                        'date' => $date,
                        'clock_in' => $clockInTime->format('Y-m-d H:i:s'),
                        'clock_out' => $clockOutTime->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
    }
}