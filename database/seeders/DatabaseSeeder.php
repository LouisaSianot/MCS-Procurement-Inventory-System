<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);

        $permissions = collect([
            'ge-orders.create',
            'ge-orders.view',
            'ge-orders.update',
            'ge-orders.delete',
            'ge-orders.submit',
            'ge-orders.approve',
            'ge-orders.reject',
            'ge-orders.cancel',
        ])->map(fn(string $name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);
        $superAdmin->syncPermissions($permissions);

        $user = User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name'              => 'Test User',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($superAdmin);

        User::whereIn('email', ['mkalua44@gmail.com', 'test@example.com'])
            ->get()
            ->each(fn(User $user) => $user->assignRole($superAdmin));
    }
}
