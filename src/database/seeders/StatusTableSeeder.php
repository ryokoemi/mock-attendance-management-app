<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Status::create(['code' => 'off', 'display_name' => '勤務外']);
        Status::create(['code' => 'working', 'display_name' => '出勤中']);
        Status::create(['code' => 'on_break', 'display_name' => '休憩中']);
        Status::create(['code' => 'left', 'display_name' => '退勤済']);
    }
}
