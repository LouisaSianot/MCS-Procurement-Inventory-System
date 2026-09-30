<?php

use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function itemManager(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'Inventory Officer', 'guard_name' => 'web']));

    return $user;
}

it('renders and creates an item from the admin form', function () {
    $user = itemManager();
    $supplier = Supplier::create(['name' => 'Test Supplier']);

    $this->actingAs($user)
        ->get(route('admin.items.create'))
        ->assertOk()
        ->assertSee('Add item');

    $this->actingAs($user)
        ->post(route('admin.items.store'), [
            'description' => 'Laptop computer',
            'uom' => 'each',
            'category' => 'ASSET',
            'sub_category' => 'Computer',
            'supplier_id' => $supplier->id,
        ])
        ->assertRedirect(route('admin.items.index'));

    expect(Item::query()->where('description', 'Laptop computer')->first())
        ->not->toBeNull()
        ->category->toBe('Asset');
});

it('searches items by partial description and model number in the admin listing', function () {
    $user = itemManager();
    $supplier = Supplier::create(['name' => 'Test Supplier']);

    Item::create([
        'description' => 'Industrial Safety Gloves',
        'uom' => 'pair',
        'category' => 'Consumable',
        'sub_category' => 'General',
        'supplier_id' => $supplier->id,
        'model_number' => 'GLOVE-XL-2024',
        'is_serialized' => false,
    ]);

    Item::create([
        'description' => 'Laptop Docking Station',
        'uom' => 'each',
        'category' => 'Asset',
        'sub_category' => 'Computer',
        'supplier_id' => $supplier->id,
        'model_number' => 'DOCK-900',
        'is_serialized' => false,
    ]);

    $this->actingAs($user)
        ->get(route('admin.items.index', ['search' => 'glov']))
        ->assertOk()
        ->assertSee('Industrial Safety Gloves')
        ->assertDontSee('Laptop Docking Station');

    $this->actingAs($user)
        ->get(route('admin.items.index', ['search' => 'GLOVE']))
        ->assertOk()
        ->assertSee('Industrial Safety Gloves')
        ->assertDontSee('Laptop Docking Station');

    $this->actingAs($user)
        ->get(route('admin.items.index', ['search' => '2024']))
        ->assertOk()
        ->assertSee('Industrial Safety Gloves')
        ->assertDontSee('Laptop Docking Station');
});

it('rejects a sub-category from another category', function () {
    $user = itemManager();
    $supplier = Supplier::create(['name' => 'Test Supplier']);

    $this->actingAs($user)
        ->from(route('admin.items.create'))
        ->post(route('admin.items.store'), [
            'description' => 'Cleaning supplies',
            'uom' => 'pack',
            'category' => 'ASSET',
            'sub_category' => 'Cleaning',
            'supplier_id' => $supplier->id,
        ])
        ->assertSessionHasErrors('sub_category');
});
