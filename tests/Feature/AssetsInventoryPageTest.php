<?php

use App\Models\User;

it('redirects the assets page to asset-filtered inventory', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('assets.index'))
        ->assertRedirect(route('inventory.index', ['category' => 'Asset']));
});

it('allows the inventory page to render with refreshed lookup data', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('inventory.index'))
        ->assertOk()
        ->assertSee('Inventory');
});
