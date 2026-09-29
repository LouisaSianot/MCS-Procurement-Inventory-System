<?php

use App\Models\Branch;
use App\Models\GEOrder;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\ItemBranch;
use App\Models\Location;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReceiptItem;
use App\Models\Supplier;
use App\Models\User;
use Spatie\Permission\Models\Role;

function inventoryUser(string $role = 'Inventory Officer'): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']));

    return $user;
}

function inventoryRecord(array $overrides = []): ItemBranch
{
    $supplier = Supplier::firstOrCreate(['name' => 'Inventory Test Supplier']);
    $branch = Branch::firstOrCreate(['name' => $overrides['branch_name'] ?? 'Inventory Main']);
    $item = Item::create([
        'description' => $overrides['description'] ?? 'Inventory Paper',
        'uom' => 'ream',
        'category' => $overrides['category'] ?? 'Consumable',
        'sub_category' => 'Stationery',
        'supplier_id' => $supplier->id,
    ]);
    $locationName = $overrides['location'] ?? 'Store Room A';
    $location = Location::firstOrCreate(['branch_id' => $branch->id, 'name' => $locationName]);

    return ItemBranch::create([
        'item_id' => $item->id,
        'branch_id' => $branch->id,
        'location_id' => $location->id,
        'branch' => $branch->name,
        'uom' => 'ream',
        'location' => $overrides['location'] ?? 'Store Room A',
        'current_stock' => $overrides['current_stock'] ?? 12,
        'unit_cost' => $overrides['unit_cost'] ?? 10.50,
        'reorder_level' => $overrides['reorder_level'] ?? 5,
        'reorder_quantity' => $overrides['reorder_quantity'] ?? 20,
    ]);
}

it('lists valid item branch records with related item, branch, value, and reorder data', function () {
    $user = inventoryUser();
    $itemBranch = inventoryRecord(['current_stock' => 12, 'unit_cost' => 10.50]);

    expect($itemBranch->item_id)->toBe($itemBranch->getRawOriginal('item_id'))
        ->and($itemBranch->branch_id)->toBe($itemBranch->getRawOriginal('branch_id'));

    $this->actingAs($user)->get(route('inventory.index'))
        ->assertOk()
        ->assertSee($itemBranch->item->description)
        ->assertSee($itemBranch->branchRecord->name)
        ->assertSee('K 126.00')
        ->assertSee('In Stock');
});

it('derives out of stock low stock and in stock consistently', function () {
    $out = inventoryRecord(['description' => 'Out Item', 'current_stock' => 0, 'reorder_level' => 5]);
    $low = inventoryRecord(['description' => 'Low Item', 'current_stock' => 5, 'reorder_level' => 5]);
    $in = inventoryRecord(['description' => 'In Item', 'current_stock' => 6, 'reorder_level' => 5]);

    expect($out->stockStatus())->toBe(ItemBranch::STATUS_OUT_OF_STOCK)
        ->and($low->stockStatus())->toBe(ItemBranch::STATUS_LOW_STOCK)
        ->and($in->stockStatus())->toBe(ItemBranch::STATUS_IN_STOCK);
});

it('filters inventory by item search location branch category and stock status', function () {
    $user = inventoryUser();
    $main = inventoryRecord(['description' => 'Laser Toner', 'location' => 'Secure Cabinet', 'category' => 'Consumable', 'current_stock' => 2, 'reorder_level' => 5, 'branch_name' => 'Main Store']);
    $other = inventoryRecord(['description' => 'Office Desk', 'location' => 'Furniture Room', 'category' => 'Asset', 'current_stock' => 10, 'reorder_level' => 2, 'branch_name' => 'Other Store']);

    $this->actingAs($user)->get(route('inventory.index', ['search' => 'Laser']))->assertSee('Laser Toner')->assertDontSee('Office Desk');
    $this->actingAs($user)->get(route('inventory.index', ['search' => 'Secure Cabinet']))->assertSee('Laser Toner')->assertDontSee('Office Desk');
    $this->actingAs($user)->get(route('inventory.index', ['location' => $main->location_id]))->assertSee('Laser Toner')->assertDontSee('Office Desk');
    $this->actingAs($user)->get(route('inventory.index', ['branch' => $main->branch_id]))->assertSee('Laser Toner')->assertDontSee('Office Desk');
    $this->actingAs($user)->get(route('inventory.index', ['category' => 'Asset']))->assertSee('Office Desk')->assertDontSee('Laser Toner');
    $this->actingAs($user)->get(route('inventory.index', ['status' => 'low_stock']))->assertSee('Laser Toner')->assertDontSee('Office Desk');
    $this->actingAs($user)->get(route('inventory.index', ['status' => 'in_stock']))->assertSee('Office Desk')->assertDontSee('Laser Toner');
});

it('shows receipt movement history with its purchase order and GRN references', function () {
    $user = inventoryUser();
    $itemBranch = inventoryRecord(['description' => 'Movement Paper']);
    $geOrder = GEOrder::create(['order_number' => 'GE-INVENTORY-MOVE', 'user_id' => $user->id, 'supplier_id' => $itemBranch->item->supplier_id, 'branch_id' => $itemBranch->branch_id, 'account_code' => '5001', 'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK, 'order_date' => now()->toDateString(), 'description' => 'Movement order', 'status' => GEOrder::STATUS_APPROVED, 'approval_status' => GEOrder::APPROVAL_APPROVED]);

    expect($geOrder->user_id)->toBe($geOrder->getRawOriginal('user_id'));

    $po = PurchaseOrder::create(['po_number' => 'PO-INVENTORY-MOVE', 'ge_order_id' => $geOrder->id, 'supplier_id' => $itemBranch->item->supplier_id, 'branch_id' => $itemBranch->branch_id, 'user_id' => $user->id, 'order_date' => now()->toDateString(), 'status' => PurchaseOrder::STATUS_ORDERED]);
    $poItem = PurchaseOrderItem::create(['purchase_order_id' => $po->id, 'item_id' => $itemBranch->item_id, 'description' => 'Movement Paper', 'unit' => 'ream', 'quantity' => 2, 'unit_price' => 10, 'total' => 20]);
    $receipt = PurchaseReceipt::create(['receipt_number' => 'GRN-INVENTORY-MOVE', 'purchase_order_id' => $po->id, 'received_by' => $user->id, 'received_at' => now()]);
    $receiptItem = PurchaseReceiptItem::create(['purchase_receipt_id' => $receipt->id, 'purchase_order_item_id' => $poItem->id, 'quantity_received' => 2, 'unit_cost' => 10]);
    InventoryMovement::create(['item_branch_id' => $itemBranch->id, 'purchase_receipt_item_id' => $receiptItem->id, 'type' => InventoryMovement::TYPE_RECEIPT, 'quantity' => 2, 'unit_cost' => 10, 'stock_after' => 14]);

    $this->actingAs($user)->get(route('inventory.show', $itemBranch))
        ->assertOk()
        ->assertSee('PO-INVENTORY-MOVE')
        ->assertSee('GRN-INVENTORY-MOVE')
        ->assertSee('Stock After');
});

it('keeps inventory read-only for an end user and rejects direct inventory creation routes', function () {
    $user = inventoryUser('EndUser');
    $itemBranch = inventoryRecord();

    $this->actingAs($user)->get(route('inventory.index'))->assertOk();
    $this->actingAs($user)->get(route('inventory.show', $itemBranch))->assertOk();
    $this->actingAs($user)->get('/inventory/create')->assertNotFound();
    $this->actingAs($user)->post('/inventory', [])->assertMethodNotAllowed();
});

it('does not permit negative current stock values', function () {
    $itemBranch = inventoryRecord();

    $itemBranch->current_stock = -1;
    $itemBranch->save();
})->throws(InvalidArgumentException::class);

it('transfers stock atomically and records both location balances and the acting user', function () {
    $user = inventoryUser();
    $source = inventoryRecord(['current_stock' => 100]);
    $destination = Location::create(['branch_id' => $source->branch_id, 'name' => 'Admin Store']);

    $this->actingAs($user)->get(route('inventory.transfers.create'))
        ->assertOk()
        ->assertSee($source->item->description)
        ->assertSee($source->locationRecord->name)
        ->assertSee($destination->name);

    $this->actingAs($user)->post(route('inventory.transfers.store'), [
        'item_id' => $source->item_id,
        'from_location_id' => $source->location_id,
        'to_location_id' => $destination->id,
        'quantity' => 30,
    ])->assertRedirect(route('inventory.index'))->assertSessionHas('success');

    $source->refresh();
    $destinationStock = ItemBranch::where('item_id', $source->item_id)->where('location_id', $destination->id)->firstOrFail();
    expect((float) $source->current_stock)->toBe(70.0)
        ->and((float) $destinationStock->current_stock)->toBe(30.0)
        ->and((float) $source->current_stock + (float) $destinationStock->current_stock)->toBe(100.0);

    $this->assertDatabaseHas('inventory_movements', [
        'item_branch_id' => $source->id,
        'type' => InventoryMovement::TYPE_TRANSFER,
        'from_location_id' => $source->location_id,
        'to_location_id' => $destination->id,
        'performed_by' => $user->id,
        'stock_after' => 70,
        'destination_stock_after' => 30,
    ]);
});

it('rejects transfers exceeding available stock and leaves both locations unchanged', function () {
    $user = inventoryUser();
    $source = inventoryRecord(['current_stock' => 100]);
    $destination = Location::create(['branch_id' => $source->branch_id, 'name' => 'Admin Store']);
    ItemBranch::create(['item_id' => $source->item_id, 'branch_id' => $source->branch_id, 'branch' => $source->branch, 'location_id' => $destination->id, 'location' => $destination->name, 'uom' => 'ream', 'current_stock' => 30]);

    $this->actingAs($user)->from(route('inventory.transfers.create'))->post(route('inventory.transfers.store'), [
        'item_id' => $source->item_id,
        'from_location_id' => $destination->id,
        'to_location_id' => $source->location_id,
        'quantity' => 100,
    ])->assertRedirect(route('inventory.transfers.create'))->assertSessionHasErrors('quantity');

    expect((float) $source->fresh()->current_stock)->toBe(100.0)
        ->and((float) ItemBranch::where('item_id', $source->item_id)->where('location_id', $destination->id)->value('current_stock'))->toBe(30.0);
    $this->assertDatabaseCount('inventory_movements', 0);
});

it('rejects cross-branch transfers and blocks transfers for view-only users', function () {
    $officer = inventoryUser();
    $source = inventoryRecord(['current_stock' => 100]);
    $otherBranch = Branch::create(['name' => 'Other Inventory Branch']);
    $foreignLocation = Location::create(['branch_id' => $otherBranch->id, 'name' => 'Foreign Store']);

    $this->actingAs($officer)->from(route('inventory.transfers.create'))->post(route('inventory.transfers.store'), [
        'item_id' => $source->item_id,
        'from_location_id' => $source->location_id,
        'to_location_id' => $foreignLocation->id,
        'quantity' => 10,
    ])->assertRedirect(route('inventory.transfers.create'))->assertSessionHasErrors('to_location_id');

    expect((float) $source->fresh()->current_stock)->toBe(100.0);

    $viewer = inventoryUser('EndUser');
    $this->actingAs($viewer)->post(route('inventory.transfers.store'), [
        'item_id' => $source->item_id,
        'from_location_id' => $source->location_id,
        'to_location_id' => $foreignLocation->id,
        'quantity' => 10,
    ])->assertForbidden();
    $this->assertDatabaseCount('inventory_movements', 0);
});

it('allows authorized users to manage unique locations and prevents deleting referenced locations', function () {
    $user = inventoryUser();
    $branch = Branch::create(['name' => 'Location Management Branch']);

    $this->actingAs($user)->post(route('admin.locations.store'), [
        'branch_id' => $branch->id,
        'name' => 'Procurement Store',
        'description' => 'Main receiving area',
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.locations.index'));

    $this->actingAs($user)->get(route('admin.locations.index'))
        ->assertOk()
        ->assertSee('Procurement Store');

    $location = Location::where('branch_id', $branch->id)->where('name', 'Procurement Store')->firstOrFail();
    $this->actingAs($user)->from(route('admin.locations.create'))->post(route('admin.locations.store'), [
        'branch_id' => $branch->id,
        'name' => 'Procurement Store',
    ])->assertRedirect(route('admin.locations.create'))->assertSessionHasErrors('name');

    $item = Item::create(['description' => 'Location Test Item', 'uom' => 'each', 'category' => 'Consumable', 'sub_category' => 'General']);
    ItemBranch::create([
        'item_id' => $item->id,
        'branch_id' => $branch->id,
        'branch' => $branch->name,
        'location_id' => $location->id,
        'location' => $location->name,
        'current_stock' => 1,
    ]);

    $this->from(route('admin.locations.index'))->actingAs($user)->delete(route('admin.locations.destroy', $location))
        ->assertRedirect(route('admin.locations.index'))
        ->assertSessionHas('error');
    $this->assertDatabaseHas('locations', ['id' => $location->id]);

    $viewer = inventoryUser('EndUser');
    $this->actingAs($viewer)->post(route('admin.locations.store'), [
        'branch_id' => $branch->id,
        'name' => 'Unauthorized Store',
    ])->assertForbidden();
});
