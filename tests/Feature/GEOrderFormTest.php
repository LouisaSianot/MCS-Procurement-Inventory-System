<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows item id and uom fields only for stock ge order lines', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));

    $stockResponse = $this->actingAs($user)->get(route('ge-orders.create'));

    $stockResponse->assertOk()
        ->assertSee('name="items[0][item_id_text]"', false)
        ->assertSee('name="items[0][unit]"', false)
        ->assertSee('<th data-stock-only class="">UOM</th>', false);

    $nonStockResponse = $this->withSession([
        '_old_input' => [
            'inventory_flag' => 'NON-STOCK',
            'items' => [['description' => 'Consulting service', 'quantity' => '1']],
        ],
    ])->get(route('ge-orders.create'));

    $nonStockResponse->assertOk()
        ->assertDontSee('name="items[0][item_id_text]"', false)
        ->assertDontSee('name="items[0][unit]"', false)
        ->assertSee('table-fixed w-[62rem] min-w-[62rem]', false)
        ->assertSee('<col data-stock-only class="w-32 hidden">', false)
        ->assertSee('<th data-stock-only class="hidden">UOM</th>', false)
        ->assertSee('<td class="align-top hidden" data-stock-only>', false);
});
