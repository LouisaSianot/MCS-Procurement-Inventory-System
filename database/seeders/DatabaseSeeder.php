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

        $basePermissions = collect([
            'ge-orders.create',
            'ge-orders.view',
            'ge-orders.update',
            'ge-orders.delete',
            'ge-orders.submit',
            'ge-orders.cancel',
            'master-data.manage',
            'users.manage',
        ])->map(fn(string $name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));

        $approvalPermissions = collect([
            'ge-orders.approve',
            'ge-orders.reject',
        ])->map(fn(string $name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);
        $superAdmin->syncPermissions($basePermissions->merge($approvalPermissions));

        $administrator = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $administrator->syncPermissions($basePermissions);

        $purchasingOfficer = Role::firstOrCreate(['name' => 'Purchasing Officer', 'guard_name' => 'web']);
        $purchasingOfficer->syncPermissions($basePermissions->merge($approvalPermissions));

        $inventoryOfficer = Role::firstOrCreate(['name' => 'Inventory Officer', 'guard_name' => 'web']);
        $inventoryOfficer->syncPermissions(['master-data.manage']);
        $endUser = Role::firstOrCreate(['name' => 'EndUser', 'guard_name' => 'web']);

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

        foreach ([
            ['name' => 'Purchasing Officer User', 'email' => 'purchasing@example.com', 'role' => $purchasingOfficer],
            ['name' => 'Inventory Officer User', 'email' => 'inventory@example.com', 'role' => $inventoryOfficer],
            ['name' => 'End User', 'email' => 'enduser@example.com', 'role' => $endUser],
        ] as $account) {
            $roleUser = User::firstOrCreate(['email' => $account['email']], [
                'name'              => $account['name'],
                'password'          => Hash::make('password123'),
                'email_verified_at' => now(),
                'user_identifier'   => (string) Str::uuid(),
                'is_active'         => true,
            ]);

            $roleUser->syncRoles([$account['role']]);
        }

        User::whereIn('email', ['mkalua44@gmail.com', 'admin@example.com'])
            ->get()
            ->each(fn(User $user) => $user->assignRole($administrator));

        $this->call(GEOrderDemoSeeder::class);
    }
}
