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
        collect(["Administrator", "Purchasing Officer", "Inventory Officer", "EndUser"])->each(fn (string $name) => Role::firstOrCreate(["name" => $name, "guard_name" => "web"]));
    }
}
