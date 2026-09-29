<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
            'master-data.manage',
            'users.manage',
        ])->map(fn(string $name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);
        $superAdmin->syncPermissions($permissions);

        $administrator = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $administrator->syncPermissions($permissions);
        Role::firstOrCreate(['name' => 'Purchasing Officer', 'guard_name' => 'web'])->syncPermissions(['master-data.manage']);
        Role::firstOrCreate(['name' => 'Inventory Officer', 'guard_name' => 'web'])->syncPermissions(['master-data.manage']);
        Role::firstOrCreate(['name' => 'EndUser', 'guard_name' => 'web']);

        $user = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name'              => 'Admin User',
            'password'          => Hash::make('password123'),
            'email_verified_at' => now(),
            'user_identifier'   => (string) Str::uuid(),
            'is_active'         => true,
        ]);

        $user->assignRole($administrator);

        User::whereIn('email', ['mkalua44@gmail.com', 'admin@example.com'])
            ->get()
            ->each(fn(User $user) => $user->assignRole($administrator));

        $this->call(GEOrderDemoSeeder::class);
    }
}
