<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            StatusTableSeeder::class,
            UserTableSeeder::class,
            AttendanceTableSeeder::class,
            BreakTimeTableSeeder::class,
            CorrectionRequestTableSeeder::class,
        ]);
    }
}
