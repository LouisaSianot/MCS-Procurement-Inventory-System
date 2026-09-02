<?php

use App\Models\Branch;
use App\Models\GEOrder;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\ItemBranch;
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

    return ItemBranch::create([
        'item_id' => $item->id,
        'branch_id' => $branch->id,
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
    $this->actingAs($user)->get(route('inventory.index', ['branch' => $main->branch_id]))->assertSee('Laser Toner')->assertDontSee('Office Desk');
    $this->actingAs($user)->get(route('inventory.index', ['category' => 'Asset']))->assertSee('Office Desk')->assertDontSee('Laser Toner');
    $this->actingAs($user)->get(route('inventory.index', ['status' => 'low_stock']))->assertSee('Laser Toner')->assertDontSee('Office Desk');
    $this->actingAs($user)->get(route('inventory.index', ['status' => 'in_stock']))->assertSee('Office Desk')->assertDontSee('Laser Toner');
});

it('shows receipt movement history with its purchase order and GRN references', function () {
    $user = inventoryUser();
    $itemBranch = inventoryRecord(['description' => 'Movement Paper']);
    $geOrder = GEOrder::create(['order_number' => 'GE-INVENTORY-MOVE', 'user_id' => $user->id, 'supplier_id' => $itemBranch->item->supplier_id, 'branch_id' => $itemBranch->branch_id, 'account_code' => '5001', 'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK, 'order_date' => now()->toDateString(), 'description' => 'Movement order', 'status' => GEOrder::STATUS_APPROVED, 'approval_status' => GEOrder::APPROVAL_APPROVED]);
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
