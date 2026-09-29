<?php

use App\Models\Branch;
use App\Models\GEOrder;
use App\Models\GEOrderItem;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Spatie\Permission\Models\Role;

function supplierDetailContext(): array
{
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'Purchasing Officer', 'guard_name' => 'web']));
    $supplier = Supplier::create(['name' => 'Supplier Detail Test']);
    $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);

    return compact('user', 'supplier', 'branch');
}

function supplierDetailOrder(Supplier $supplier, User $user, Branch $branch, string $number, string $date = '2026-09-24'): GEOrder
{
    return GEOrder::create([
        'order_number' => $number,
        'user_id' => $user->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'account_code' => '5001',
        'inventory_flag' => 'STOCK',
        'order_date' => $date,
        'description' => 'Supplier detail test order',
        'status' => GEOrder::STATUS_APPROVED,
        'approval_status' => GEOrder::APPROVAL_APPROVED,
    ]);
}

it('shows authorized related GE order lines and the associated purchase order', function () {
    ['user' => $user, 'supplier' => $supplier, 'branch' => $branch] = supplierDetailContext();
    $item = Item::create([
        'description' => 'Supplier Detail Item',
        'uom' => 'box',
        'category' => 'Consumable',
        'sub_category' => 'General',
        'supplier_id' => $supplier->id,
    ]);
    $order = supplierDetailOrder($supplier, $user, $branch, 'GE-SUP-001');
    GEOrderItem::create([
        'ge_order_id' => $order->id,
        'item_id' => $item->id,
        'description' => 'Line-specific description',
        'quantity' => 12.5,
        'unit_price' => 4,
        'total' => 50,
    ]);
    $purchaseOrder = PurchaseOrder::create([
        'po_number' => 'PO-SUP-001',
        'ge_order_id' => $order->id,
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
        'user_id' => $user->id,
        'order_date' => '2026-09-25',
        'status' => PurchaseOrder::STATUS_ORDERED,
        'total_amount' => 50,
        'receiving_person_name' => 'Test Receiver',
        'receiving_person_position' => 'Store Officer',
        'receiving_person_branch' => 'Main Campus',
    ]);

    $this->actingAs($user)
        ->get(route('admin.suppliers.show', $supplier))
        ->assertOk()
        ->assertSee('Related Order Line Items')
        ->assertSee('Supplier Detail Item')
        ->assertSee('Line-specific description')
        ->assertSee('12.50')
        ->assertSee('GE-SUP-001')
        ->assertSee('PO-SUP-001')
        ->assertSee('Approved')
        ->assertSee('Ordered')
        ->assertSee('24 Sep 2026')
        ->assertSee('25 Sep 2026');
});

it('shows a missing PO placeholder and an empty state when there are no order lines', function () {
    ['user' => $user, 'supplier' => $supplier, 'branch' => $branch] = supplierDetailContext();
    $order = supplierDetailOrder($supplier, $user, $branch, 'GE-SUP-002');
    GEOrderItem::create([
        'ge_order_id' => $order->id,
        'description' => 'No catalog item line',
        'quantity' => 1,
        'unit_price' => 5,
        'total' => 5,
    ]);

    $this->actingAs($user)
        ->get(route('admin.suppliers.show', $supplier))
        ->assertOk()
        ->assertSee('Not yet created')
        ->assertSee('No catalog item line');

    $emptySupplier = Supplier::create(['name' => 'Empty Supplier']);
    $this->actingAs($user)
        ->get(route('admin.suppliers.show', $emptySupplier))
        ->assertOk()
        ->assertSee('No related order line items found.');
});

it('shows only this supplier order lines once across multiple orders', function () {
    ['user' => $user, 'supplier' => $supplier, 'branch' => $branch] = supplierDetailContext();
    $item = Item::create([
        'description' => 'Repeated Catalog Item',
        'uom' => 'each',
        'category' => 'Consumable',
        'sub_category' => 'General',
        'supplier_id' => $supplier->id,
    ]);
    foreach ([['GE-SUP-003', 'First occurrence'], ['GE-SUP-004', 'Second occurrence']] as [$number, $description]) {
        $order = supplierDetailOrder($supplier, $user, $branch, $number);
        GEOrderItem::create([
            'ge_order_id' => $order->id,
            'item_id' => $item->id,
            'description' => $description,
            'quantity' => 2,
            'unit_price' => 3,
            'total' => 6,
        ]);
    }

    $otherSupplier = Supplier::create(['name' => 'Other Supplier']);
    $otherOrder = supplierDetailOrder($otherSupplier, $user, $branch, 'GE-OTHER-001');
    GEOrderItem::create([
        'ge_order_id' => $otherOrder->id,
        'description' => 'Unrelated supplier line',
        'quantity' => 99,
        'unit_price' => 1,
        'total' => 99,
    ]);

    $response = $this->actingAs($user)->get(route('admin.suppliers.show', $supplier));

    $response->assertOk()
        ->assertSee('First occurrence')
        ->assertSee('Second occurrence')
        ->assertSee('GE-SUP-003')
        ->assertSee('GE-SUP-004')
        ->assertDontSee('Unrelated supplier line')
        ->assertDontSee('GE-OTHER-001');

    expect(substr_count($response->getContent(), 'Repeated Catalog Item'))->toBe(2);
});

it('does not show a purchase order assigned to a different supplier', function () {
    ['user' => $user, 'supplier' => $supplier, 'branch' => $branch] = supplierDetailContext();
    $order = supplierDetailOrder($supplier, $user, $branch, 'GE-SUP-005');
    GEOrderItem::create([
        'ge_order_id' => $order->id,
        'description' => 'Supplier line with mismatched PO',
        'quantity' => 1,
        'unit_price' => 2,
        'total' => 2,
    ]);

    $otherSupplier = Supplier::create(['name' => 'PO Owner Supplier']);
    PurchaseOrder::create([
        'po_number' => 'PO-OTHER-SUPPLIER',
        'ge_order_id' => $order->id,
        'supplier_id' => $otherSupplier->id,
        'branch_id' => $branch->id,
        'user_id' => $user->id,
        'order_date' => '2026-09-25',
        'status' => PurchaseOrder::STATUS_FULLY_RECEIVED,
        'total_amount' => 2,
        'receiving_person_name' => 'Test Receiver',
        'receiving_person_position' => 'Store Officer',
        'receiving_person_branch' => 'Main Campus',
    ]);

    $this->actingAs($user)
        ->get(route('admin.suppliers.show', $supplier))
        ->assertOk()
        ->assertSee('Restricted')
        ->assertDontSee('PO-OTHER-SUPPLIER')
        ->assertDontSee('Fully received');
});

it('protects the supplier detail route with the existing authentication and role middleware', function () {
    ['supplier' => $supplier] = supplierDetailContext();
    $this->get(route('admin.suppliers.show', $supplier))->assertRedirect(route('login'));

    $unauthorizedUser = User::factory()->create();
    $this->actingAs($unauthorizedUser)
        ->get(route('admin.suppliers.show', $supplier))
        ->assertForbidden();
});
