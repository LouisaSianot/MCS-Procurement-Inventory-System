<?php

use App\Models\Branch;
use App\Models\GEOrder;
use App\Models\Item;
use App\Models\ItemBranch;
use App\Models\Location;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

function reportPurchaseOrder(User $user, Supplier $supplier, Branch $branch, string $number, string $date, string $status, float $amount, string $itemDescription): PurchaseOrder
{
    $geOrder = GEOrder::create([
        'order_number' => "GE-{$number}",
        'user_id' => $user->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'account_code' => '5001',
        'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
        'order_date' => $date,
        'description' => "Report {$number}",
        'status' => GEOrder::STATUS_APPROVED,
        'approval_status' => GEOrder::APPROVAL_APPROVED,
        'total_amount' => $amount,
    ]);

    $purchaseOrder = PurchaseOrder::create([
        'po_number' => "PO-{$number}",
        'ge_order_id' => $geOrder->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'user_id' => $user->id,
        'order_date' => $date,
        'status' => $status,
        'total_amount' => $amount,
    ]);

    PurchaseOrderItem::create([
        'purchase_order_id' => $purchaseOrder->id,
        'description' => $itemDescription,
        'unit' => 'each',
        'quantity' => 3,
        'unit_price' => $amount / 3,
        'total' => $amount,
    ]);

    return $purchaseOrder;
}

it('requires authentication to view reports', function () {
    $this->get(route('reports.index'))->assertRedirect(route('login'));
});

it('renders reports safely when no reportable records exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('reports.index'))
        ->assertOk()
        ->assertSee('Total Purchase Orders')
        ->assertSee('No purchase orders found')
        ->assertSee('No low-stock items');
});

it('renders report filters when legacy lookup cache entries are invalid', function () {
    $user = User::factory()->create();
    Branch::create(['name' => 'Cached Branch Fallback']);
    Supplier::create(['name' => 'Cached Supplier Fallback']);

    Cache::put('lookup.branches.v2', 'invalid branch cache');
    Cache::put('lookup.suppliers.v2', 'invalid supplier cache');

    try {
        $this->actingAs($user)->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Cached Branch Fallback')
            ->assertSee('Cached Supplier Fallback');
    } finally {
        Cache::forget('lookup.branches.v2');
        Cache::forget('lookup.suppliers.v2');
    }
});

it('calculates report aggregates and applies combined filters', function () {
    $user = User::factory()->create();
    $main = Branch::firstOrCreate(['name' => 'Reports Main']);
    $other = Branch::firstOrCreate(['name' => 'Reports Other']);
    $includedSupplier = Supplier::create(['name' => 'Included Supplier']);
    $excludedSupplier = Supplier::create(['name' => 'Excluded Supplier']);

    reportPurchaseOrder($user, $includedSupplier, $main, 'REPORT-001', '2026-09-10', PurchaseOrder::STATUS_ORDERED, 150, 'Filtered Item');
    reportPurchaseOrder($user, $excludedSupplier, $other, 'REPORT-002', '2026-08-10', PurchaseOrder::STATUS_FULLY_RECEIVED, 300, 'Excluded Item');

    $stockItem = Item::create(['description' => 'Low Stock Item', 'uom' => 'each', 'category' => 'Consumable', 'sub_category' => 'General', 'supplier_id' => $includedSupplier->id]);
    $location = Location::create(['branch_id' => $main->id, 'name' => 'Reports Store']);
    ItemBranch::create(['item_id' => $stockItem->id, 'branch_id' => $main->id, 'branch' => $main->name, 'location_id' => $location->id, 'location' => $location->name, 'uom' => 'each', 'current_stock' => 2, 'unit_cost' => 25, 'reorder_level' => 5, 'reorder_quantity' => 10]);

    $response = $this->actingAs($user)->get(route('reports.index', [
        'date_from' => '2026-09-01',
        'date_to' => '2026-09-30',
        'branch_id' => $main->id,
        'supplier_id' => $includedSupplier->id,
    ]));

    $response->assertOk()
        ->assertSee('K 150.00')
        ->assertSee('Filtered Item')
        ->assertDontSee('Excluded Item')
        ->assertSee('Included Supplier')
        ->assertSee('Low Stock Item')
        ->assertSee('K 50.00');
});
