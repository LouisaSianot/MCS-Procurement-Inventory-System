<?php

use App\Models\Customer;
use App\Models\User;
use Spatie\Permission\Models\Role;

function customerAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']));

    return $user;
}

it('creates customers with v4 types and allocated ids', function () {
    $user = customerAdmin();

    $response = $this->actingAs($user)->post(route('admin.customers.store'), [
        'customer' => 'Jane Customer',
        'customer_type' => 'STAFF',
        'email' => 'jane.customer@example.com',
    ]);

    $customer = Customer::firstOrFail();

    $response->assertRedirect(route('admin.customers.index'));
    expect($customer->id)->toBeGreaterThanOrEqual(4001)->toBeLessThanOrEqual(4999);
    $this->assertDatabaseHas('customers', ['id' => $customer->id, 'customer_type' => 'STAFF']);
});

it('rejects invalid customer types', function () {
    $this->actingAs(customerAdmin())->from(route('admin.customers.create'))
        ->post(route('admin.customers.store'), [
            'customer' => 'Invalid Customer',
            'customer_type' => 'VENDOR',
            'email' => 'invalid.customer@example.com',
        ])
        ->assertRedirect(route('admin.customers.create'))
        ->assertSessionHasErrors('customer_type');
});

it('does not expose an address field in customer forms', function () {
    $this->actingAs(customerAdmin())->get(route('admin.customers.create'))
        ->assertOk()
        ->assertSee('CustomerType')
        ->assertDontSee('Address');
});
