<?php

namespace Database\Seeders;

use App\Models\access_types;
use App\Models\user_types;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class TypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('user_types')->truncate();
        DB::table('access_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        user_types::create([
            "name" => "admin",
        ]);
        user_types::create([
            "name" => "user",
        ]);
        access_types::create([
            "name" => "normal",
        ]);
    }
}
