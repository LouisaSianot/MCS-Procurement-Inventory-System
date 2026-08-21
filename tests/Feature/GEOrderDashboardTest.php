<?php

use App\Http\Controllers\GEOrderController;
use App\Models\User;

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
