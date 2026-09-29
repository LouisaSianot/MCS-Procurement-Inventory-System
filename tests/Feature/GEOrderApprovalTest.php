<?php

use App\Models\GEOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('allows an authorized administrator to approve a pending ge order', function () {
    $user = User::factory()->create([
        'name' => 'Approver User',
        'email' => 'approver@example.com',
        'password' => Hash::make('password'),
    ]);
    $role = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'ge-orders.approve', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);
    $user->assignRole($role);

    $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);
    $supplier = Supplier::create([
        'name' => 'Test Supplier',
        'address' => 'Test address',
        'contact' => 'sales@test.com',
        'payment_term' => 'CREDIT',
        'currency' => 'PGK',
    ]);

    $order = GEOrder::create([
        'order_number' => 'GE-00099',
        'user_id' => $user->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'account_code' => '5001-Office Supplies',
        'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
        'order_date' => now()->toDateString(),
        'description' => 'Pending approval test order',
        'status' => GEOrder::STATUS_PENDING,
        'approval_status' => GEOrder::APPROVAL_PENDING_APPROVAL,
        'total_amount' => 100,
    ]);

    $this->actingAs($user)
        ->post(route('ge-orders.approve', $order))
        ->assertRedirect(route('ge-orders.show', $order));

    $order->refresh();
    expect($order->status)->toBe(GEOrder::STATUS_APPROVED)
        ->and($order->approval_status)->toBe(GEOrder::APPROVAL_APPROVED)
        ->and($order->approved_by)->toBe($user->id);
});

it('allows an authorized administrator to reject a pending ge order', function () {
    $user = User::factory()->create([
        'name' => 'Rejector User',
        'email' => 'rejector@example.com',
        'password' => Hash::make('password'),
    ]);
    $role = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'ge-orders.approve', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);
    $user->assignRole($role);

    $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);
    $supplier = Supplier::create([
        'name' => 'Reject Supplier',
        'address' => 'Test address',
        'contact' => 'sales@reject.com',
        'payment_term' => 'CREDIT',
        'currency' => 'PGK',
    ]);

    $order = GEOrder::create([
        'order_number' => 'GE-00100',
        'user_id' => $user->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'account_code' => '5001-Office Supplies',
        'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
        'order_date' => now()->toDateString(),
        'description' => 'Rejection test order',
        'status' => GEOrder::STATUS_PENDING,
        'approval_status' => GEOrder::APPROVAL_PENDING_APPROVAL,
        'total_amount' => 200,
    ]);

    $this->actingAs($user)
        ->from(route('ge-orders.show', $order))
        ->post(route('ge-orders.reject', $order), ['rejection_reason' => 'Supplier quote does not match the approved specification.'])
        ->assertRedirect(route('ge-orders.show', $order));

    $order->refresh();
    expect($order->status)->toBe(GEOrder::STATUS_REJECTED)
        ->and($order->approval_status)->toBe(GEOrder::APPROVAL_REJECTED)
        ->and($order->rejection_reason)->toBe('Supplier quote does not match the approved specification.')
        ->and($order->approved_by)->toBe($user->id);
});

it('blocks a legacy head_of_school role from approving a pending ge order', function () {
    $user = User::factory()->create([
        'name' => 'HoS User',
        'email' => 'hos@example.com',
        'password' => Hash::make('password'),
    ]);
    $role = Role::firstOrCreate(['name' => 'head_of_school', 'guard_name' => 'web']);
    $user->assignRole($role);

    $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);
    $supplier = Supplier::create([
        'name' => 'Legacy HoS Supplier',
        'address' => 'Test address',
        'contact' => 'sales@hos.com',
        'payment_term' => 'CREDIT',
        'currency' => 'PGK',
    ]);

    $order = GEOrder::create([
        'order_number' => 'GE-00101',
        'user_id' => $user->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'account_code' => '5001-Office Supplies',
        'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
        'order_date' => now()->toDateString(),
        'description' => 'Legacy role check',
        'status' => GEOrder::STATUS_PENDING,
        'approval_status' => GEOrder::APPROVAL_PENDING_APPROVAL,
        'total_amount' => 150,
    ]);

    $this->actingAs($user)
        ->post(route('ge-orders.approve', $order))
        ->assertForbidden();

    $order->refresh();
    expect($order->status)->toBe(GEOrder::STATUS_PENDING);
});

it('blocks unauthorized direct approval requests and invalid transitions', function () {
    $user = User::factory()->create([
        'name' => 'General User',
        'email' => 'general@example.com',
        'password' => Hash::make('password'),
    ]);
    $role = Role::firstOrCreate(['name' => 'EndUser', 'guard_name' => 'web']);
    $user->assignRole($role);

    $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);
    $supplier = Supplier::create([
        'name' => 'Unauthorised Supplier',
        'address' => 'Test address',
        'contact' => 'sales@unauthorised.com',
        'payment_term' => 'CREDIT',
        'currency' => 'PGK',
    ]);

    $order = GEOrder::create([
        'order_number' => 'GE-00102',
        'user_id' => $user->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'account_code' => '5001-Office Supplies',
        'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
        'order_date' => now()->toDateString(),
        'description' => 'Unauthorized direct request',
        'status' => GEOrder::STATUS_PENDING,
        'approval_status' => GEOrder::APPROVAL_PENDING_APPROVAL,
        'total_amount' => 300,
    ]);

    $this->actingAs($user)
        ->post(route('ge-orders.approve', $order))
        ->assertForbidden();

    $approvedOrder = GEOrder::create([
        'order_number' => 'GE-00103',
        'user_id' => $user->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'account_code' => '5001-Office Supplies',
        'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
        'order_date' => now()->toDateString(),
        'description' => 'Already approved order',
        'status' => GEOrder::STATUS_APPROVED,
        'approval_status' => GEOrder::APPROVAL_APPROVED,
        'total_amount' => 250,
    ]);

    $this->actingAs(User::factory()->create([
        'name' => 'Admin Approver',
        'email' => 'adminapprover@example.com',
        'password' => Hash::make('password'),
    ]))
        ->post(route('ge-orders.approve', $approvedOrder))
        ->assertForbidden();
});

it('allows a purchasing officer with approval permission to approve a pending ge order', function () {
    $user = User::factory()->create([
        'name' => 'Purchasing Officer',
        'email' => 'purchasing@example.com',
        'password' => Hash::make('password'),
    ]);

    $role = Role::firstOrCreate(['name' => 'Purchasing Officer', 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'ge-orders.approve', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);
    $user->assignRole($role);

    $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);
    $supplier = Supplier::create([
        'name' => 'Purchasing Supplier',
        'address' => 'Test address',
        'contact' => 'sales@purchasing.com',
        'payment_term' => 'CREDIT',
        'currency' => 'PGK',
    ]);

    $order = GEOrder::create([
        'order_number' => 'GE-00104',
        'user_id' => User::factory()->create()->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'account_code' => '5001-Office Supplies',
        'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
        'order_date' => now()->toDateString(),
        'description' => 'Purchasing approval test',
        'status' => GEOrder::STATUS_PENDING,
        'approval_status' => GEOrder::APPROVAL_PENDING_APPROVAL,
        'total_amount' => 400,
    ]);

    $this->actingAs($user)
        ->post(route('ge-orders.approve', $order))
        ->assertRedirect(route('ge-orders.show', $order));

    $order->refresh();
    expect($order->status)->toBe(GEOrder::STATUS_APPROVED)
        ->and($order->approval_status)->toBe(GEOrder::APPROVAL_APPROVED);
});

it('blocks a procurement officer from approving a ge order', function () {
    $user = User::factory()->create([
        'name' => 'Procurement Officer',
        'email' => 'procurement@example.com',
        'password' => Hash::make('password'),
    ]);

    $role = Role::firstOrCreate(['name' => 'procurement_officer', 'guard_name' => 'web']);
    $user->assignRole($role);

    $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);
    $supplier = Supplier::create([
        'name' => 'Procurement Supplier',
        'address' => 'Test address',
        'contact' => 'sales@procurement.com',
        'payment_term' => 'CREDIT',
        'currency' => 'PGK',
    ]);

    $order = GEOrder::create([
        'order_number' => 'GE-00105',
        'user_id' => User::factory()->create()->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'account_code' => '5001-Office Supplies',
        'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
        'order_date' => now()->toDateString(),
        'description' => 'Procurement approval test',
        'status' => GEOrder::STATUS_PENDING,
        'approval_status' => GEOrder::APPROVAL_PENDING_APPROVAL,
        'total_amount' => 500,
    ]);

    $this->actingAs($user)
        ->post(route('ge-orders.approve', $order))
        ->assertForbidden();
});

it('blocks an admin without explicit approval permission from approving a ge order', function () {
    $user = User::factory()->create([
        'name' => 'Admin Without Permission',
        'email' => 'adminnoperm@example.com',
        'password' => Hash::make('password'),
    ]);

    $role = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
    $user->assignRole($role);

    $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);
    $supplier = Supplier::create([
        'name' => 'Admin Supplier',
        'address' => 'Test address',
        'contact' => 'sales@admin.com',
        'payment_term' => 'CREDIT',
        'currency' => 'PGK',
    ]);

    $order = GEOrder::create([
        'order_number' => 'GE-00106',
        'user_id' => User::factory()->create()->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'account_code' => '5001-Office Supplies',
        'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
        'order_date' => now()->toDateString(),
        'description' => 'Admin no permission test',
        'status' => GEOrder::STATUS_PENDING,
        'approval_status' => GEOrder::APPROVAL_PENDING_APPROVAL,
        'total_amount' => 600,
    ]);

    $this->actingAs($user)
        ->post(route('ge-orders.approve', $order))
        ->assertForbidden();
});
