<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Status;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $statusCodeToId = Status::pluck('id', 'code');

        // 管理者ユーザー2人（is_admin=1, status_idはnullでOK）
        $adminA = User::create([
            'name' => '管理者A',
            'email' => 'admin_a@gmail.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
            'status_id' => null,
        ]);
        $adminB = User::create([
            'name' => '管理者B',
            'email' => 'admin_b@gmail.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
            'status_id' => null,
        ]);

        // 一般ユーザー5人（is_admin=0、status_id設定）
        $generalUsers = [
            ['name' => '山田 太郎', 'email' => 'yamada@example.com', 'status' => 'working'],
            ['name' => '佐藤 花', 'email' => 'sato@example.com', 'status' => 'on_break'],
            ['name' => '鈴木 一郎', 'email' => 'suzuki@example.com', 'status' => 'left'],
            ['name' => '田中 美咲', 'email' => 'tanaka@example.com', 'status' => 'working'],
            ['name' => '高橋 健太', 'email' => 'takahashi@example.com', 'status' => 'off'],
            ['name' => '川島 明', 'email' => 'nakamura@example.com', 'status' => 'working'],
            ['name' => '小林 真子', 'email' => 'kobayashi@example.com', 'status' => 'on_break'],
        ];
        foreach ($generalUsers as $info) {
            User::create([
                'name' => $info['name'],
                'email' => $info['email'],
                'password' => bcrypt('password'),
                'is_admin' => false,
                'status_id' => $statusCodeToId[$info['status']]
            ]);
        }

    }
}