<?php

use App\Models\Branch;
use App\Models\GEOrder;
use App\Models\GEOrderItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('creates a purchase order from an approved GE order and copies its items', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'procurement_officer', 'guard_name' => 'web']));
    $supplier = Supplier::create(['name' => 'Purchase Order Supplier']);
    $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);
    $geOrder = GEOrder::create([
        'order_number' => 'GE-PO-00001', 'user_id' => $user->id, 'supplier_id' => $supplier->id,
        'branch_id' => $branch->id, 'account_code' => '5001-Office Supplies', 'inventory_flag' => 'STOCK',
        'order_date' => now()->toDateString(), 'description' => 'Approved order for a PO',
        'status' => GEOrder::STATUS_APPROVED, 'approval_status' => GEOrder::APPROVAL_APPROVED,
    ]);
    GEOrderItem::create(['ge_order_id' => $geOrder->id, 'description' => 'Copy Paper', 'unit' => 'ream', 'quantity' => 2, 'unit_price' => 20, 'total' => 40]);

    $response = $this->actingAs($user)->post(route('procurement.store'), [
        'po_number' => 'PO-00001', 'ge_order_id' => $geOrder->id, 'order_date' => now()->toDateString(), 'action' => 'place_order',
    ]);

    $purchaseOrder = PurchaseOrder::firstOrFail();
    $response->assertRedirect(route('procurement.show', $purchaseOrder));
    $this->assertDatabaseHas('purchase_orders', ['id' => $purchaseOrder->id, 'ge_order_id' => $geOrder->id, 'status' => 'ordered', 'total_amount' => 40]);
    $this->assertDatabaseHas('purchase_order_items', ['purchase_order_id' => $purchaseOrder->id, 'description' => 'Copy Paper', 'total' => 40]);
});

it('only lists approved GE orders without a purchase order when creating one', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'procurement_officer', 'guard_name' => 'web']));

    $response = $this->actingAs($user)->get(route('procurement.create'));

    $response->assertOk()->assertSee('Select an approved GE Order');
});
