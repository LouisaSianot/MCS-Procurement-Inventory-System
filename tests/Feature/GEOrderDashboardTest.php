<?php

use App\Http\Controllers\GEOrderController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('renders the dashboard with live empty GE order data', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Total GE Orders')
        ->assertSee('No GE orders have been created yet.')
        ->assertSee('href="' . route('ge-orders.index') . '"', false);
});

it('registers the GE order resource routes with the controller', function () {
    expect(route('ge-orders.index'))->toContain('/ge-orders')
        ->and(app('router')->getRoutes()->getByName('ge-orders.index')->getActionName())
        ->toBe(GEOrderController::class . '@index')
        ->and(app('router')->getRoutes()->getByName('ge-orders.show')->getActionName())
        ->toBe(GEOrderController::class . '@show');
});

it('shows only implemented quick actions with valid destinations', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('New GE Order')
        ->assertSee('Add Supplier')
        ->assertSee('Receive Purchase')
        ->assertSee('Create Item')
        ->assertSee('data-lucide="square-plus"', false)
        ->assertSee('href="' . route('ge-orders.create') . '"', false)
        ->assertSee('href="' . route('admin.suppliers.create') . '"', false)
        ->assertSee('href="' . route('receiving.create') . '"', false)
        ->assertSee('href="' . route('admin.items.create') . '"', false)
        ->assertDontSee('>Issue Stock</span>', false)
        ->assertDontSee('>Stock Adjustment</span>', false)
        ->assertDontSee('>Register Asset</span>', false);
});
