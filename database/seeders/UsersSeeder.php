<?php

namespace Database\Seeders;

use App\Models\group;
use App\Models\User;
use App\Models\users_groups;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('groups')->truncate();
        DB::table('users_groups')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        User::create([
            "name" => "Ziad",
            "type_id" => 1,
            "email" => "zd@gmail.com",
            "password" => bcrypt("111"),
        ]);
        User::create([
            "name" => "Ahmad",
            "type_id" => 2,
            "email" => "ah@gmail.com",
            "password" => bcrypt("222"),
        ]);
        User::create([
            "name" => "Omar",
            "type_id" => 2,
            "email" => "om@gmail.com",
            "password" => bcrypt("333"),
        ]);
        group::create([
            "name" => "group test1",
            "creater_id" => 1,
        ]);
        group::create([
            "name" => "group test2",
            "creater_id" => 2,
        ]);
        group::create([
            "name" => "group test3",
            "creater_id" => 3,
        ]);
        users_groups::create([
            "group_id" => 1,
            "user_id" => 1,
        ]);
        users_groups::create([
            "group_id" => 2,
            "user_id" => 2,
        ]);
        users_groups::create([
            "group_id" => 3,
            "user_id" => 3,
        ]);
        users_groups::create([
            "group_id" => 1,
            "user_id" => 2,
        ]);
        users_groups::create([
            "group_id" => 2,
            "user_id" => 3,
        ]);
        users_groups::create([
            "group_id" => 3,
            "user_id" => 1,
        ]);
    }
}
