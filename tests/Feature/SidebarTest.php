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
        ->assertSee('href="' . route('assets.index') . '"', false)
        ->assertSee('>Assets</span>', false)
        ->assertDontSee('>Users &amp; Roles</span>', false);
});

it('shows the Locations link to Administrators and hides it from view-only users', function () {
    $administrator = sidebarUser('Administrator');
    $administratorResponse = $this->actingAs($administrator)->get(route('dashboard'));

    $administratorResponse
        ->assertOk()
        ->assertSee('href="' . route('admin.locations.index') . '"', false)
        ->assertSee('>Locations</span>', false);

    $viewer = sidebarUser('EndUser');
    $this->actingAs($viewer)->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('>Locations</span>', false);
});

it('keeps the Users and Roles page protected without permission', function () {
    $user = sidebarUser('EndUser');

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});
