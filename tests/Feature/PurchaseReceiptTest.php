<?php

use App\Models\Branch;
use App\Models\GEOrder;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\ItemBranch;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceipt;
use App\Models\Supplier;
use App\Models\User;
use Spatie\Permission\Models\Role;

function receiptPurchaseOrder(User $user, string $flag, ?Item $item = null): array
{
    $supplier = Supplier::create(['name' => "Receipt Supplier {$flag}"]);
    $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);
    $geOrder = GEOrder::create([
        'order_number' => "GE-RCPT-{$flag}",
        'user_id' => $user->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'account_code' => '5001',
        'inventory_flag' => $flag,
        'order_date' => now()->toDateString(),
        'description' => 'Receipt test order',
        'status' => GEOrder::STATUS_APPROVED,
        'approval_status' => GEOrder::APPROVAL_APPROVED,
    ]);
    $purchaseOrder = PurchaseOrder::create([
        'po_number' => "PO-RCPT-{$flag}",
        'ge_order_id' => $geOrder->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'user_id' => $user->id,
        'order_date' => now()->toDateString(),
        'status' => PurchaseOrder::STATUS_ORDERED,
    ]);
    $line = PurchaseOrderItem::create([
        'purchase_order_id' => $purchaseOrder->id,
        'item_id' => $item?->id,
        'description' => $item?->description ?? 'Installation service',
        'unit' => 'unit',
        'quantity' => 5,
        'unit_price' => 20,
        'total' => 100,
    ]);

    return [$purchaseOrder, $line, $branch];
}

it('posts a stock receipt, updates branch stock, and completes the purchase order', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'procurement_officer', 'guard_name' => 'web']));
    $item = Item::create(['description' => 'Receipt Paper', 'uom' => 'ream', 'category' => 'Consumable', 'sub_category' => 'Stationery']);
    [$purchaseOrder, $line, $branch] = receiptPurchaseOrder($user, GEOrder::INVENTORY_FLAG_STOCK, $item);

    $response = $this->actingAs($user)->post(route('receiving.store'), [
        'receipt_number' => 'GRN-00001',
        'purchase_order_id' => $purchaseOrder->id,
        'received_at' => now()->toDateString(),
        'items' => [['purchase_order_item_id' => $line->id, 'quantity_received' => 5, 'unit_cost' => 25]],
    ]);

    $receipt = PurchaseReceipt::firstOrFail();
    $response->assertRedirect(route('receiving.show', $receipt));
    $this->assertDatabaseHas('purchase_receipt_items', ['purchase_receipt_id' => $receipt->id, 'purchase_order_item_id' => $line->id, 'quantity_received' => 5]);
    $this->assertDatabaseHas('item_branches', ['item_id' => $item->id, 'branch' => $branch->name, 'current_stock' => 5]);
    $this->assertDatabaseHas('inventory_movements', ['type' => InventoryMovement::TYPE_RECEIPT, 'quantity' => 5, 'stock_after' => 5]);
    expect($purchaseOrder->fresh()->status)->toBe(PurchaseOrder::STATUS_FULLY_RECEIVED);
});

it('does not create inventory movements for non-stock receipts', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'procurement_officer', 'guard_name' => 'web']));
    [$purchaseOrder, $line] = receiptPurchaseOrder($user, GEOrder::INVENTORY_FLAG_NONSTOCK);

    $this->actingAs($user)->post(route('receiving.store'), [
        'receipt_number' => 'GRN-00002',
        'purchase_order_id' => $purchaseOrder->id,
        'received_at' => now()->toDateString(),
        'items' => [['purchase_order_item_id' => $line->id, 'quantity_received' => 3, 'unit_cost' => 20]],
    ])->assertRedirect();

    $this->assertDatabaseCount('inventory_movements', 0);
    $this->assertDatabaseCount('item_branches', 0);
    expect($purchaseOrder->fresh()->status)->toBe(PurchaseOrder::STATUS_PARTIALLY_RECEIVED);
});

it('rejects an over-receipt', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'procurement_officer', 'guard_name' => 'web']));
    $item = Item::create(['description' => 'Over-receipt Item', 'uom' => 'unit', 'category' => 'Consumable', 'sub_category' => 'General']);
    [$purchaseOrder, $line] = receiptPurchaseOrder($user, GEOrder::INVENTORY_FLAG_STOCK, $item);

    $this->actingAs($user)->from(route('receiving.create', ['purchase_order_id' => $purchaseOrder->id]))->post(route('receiving.store'), [
        'receipt_number' => 'GRN-00003',
        'purchase_order_id' => $purchaseOrder->id,
        'received_at' => now()->toDateString(),
        'items' => [['purchase_order_item_id' => $line->id, 'quantity_received' => 6, 'unit_cost' => 20]],
    ])->assertRedirect(route('receiving.create', ['purchase_order_id' => $purchaseOrder->id]))->assertSessionHasErrors('items');

    $this->assertDatabaseCount('purchase_receipts', 0);
});
