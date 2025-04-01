<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['id' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'admin'],
            ['id' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'user'],
            ['id' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'instructor'],
        ];

        DB::table('roles')->insert($roles);
    }
}
