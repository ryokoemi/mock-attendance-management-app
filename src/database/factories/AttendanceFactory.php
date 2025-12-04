<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id'   => null,
            'date'      => null,
            'clock_in'  => null,
            'clock_out' => null,
        ];
    }
}