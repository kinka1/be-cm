<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::query()->updateOrCreate(
            ['role_name' => 'operator'],
            ['permissions' => json_encode([])]
        );

        Role::query()->updateOrCreate(
            ['role_name' => 'supervisor'],
            ['permissions' => json_encode([])]
        );

        Role::query()->updateOrCreate(
            ['role_name' => 'admin'],
            ['permissions' => json_encode([])]
        );
    }
}
