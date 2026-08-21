<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'Administrator']);
        Role::create(['name' => 'Purchasing Officer']);
        Role::create(['name' => 'Inventory Officer']);
        Role::create(['name' => 'HoS']);
        Role::create(['name' => 'EndUser']);
    }
}
