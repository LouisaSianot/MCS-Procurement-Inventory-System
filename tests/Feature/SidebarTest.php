<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

function sidebarUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']));

    return $user;
}

it('shows the Assets system link for authenticated users', function () {
    $user = sidebarUser('EndUser');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('href="' . route('inventory.index') . '"', false)
        ->assertSee('>Assets</span>', false)
        ->assertDontSee('>Users &amp; Roles</span>', false);
});

it('keeps the Users and Roles page protected without permission', function () {
    $user = sidebarUser('EndUser');

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});
