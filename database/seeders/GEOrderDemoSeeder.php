<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\GEOrder;
use App\Models\GEOrderItem;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class GEOrderDemoSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        if ($role = Role::where('name', 'super_admin')->first()) {
            $user->assignRole($role);
        }

        $suppliers = collect([
            ['name' => 'PNG Office Supplies', 'address' => 'Port Moresby', 'contact' => 'sales@pngoffice.example'],
            ['name' => 'Tech Supplies Ltd', 'address' => 'Lae', 'contact' => 'orders@techsupplies.example'],
        ])->mapWithKeys(function (array $data): array {
            $supplier = Supplier::firstOrCreate(['name' => $data['name']], $data);

            return [$data['name'] => $supplier];
        });

        $items = collect([
            ['description' => 'A4 Copy Paper', 'uom' => 'ream', 'category' => 'Consumable', 'sub_category' => 'Stationery', 'supplier_id' => $suppliers['PNG Office Supplies']->id],
            ['description' => 'Printer Toner Cartridge', 'uom' => 'unit', 'category' => 'Consumable', 'sub_category' => 'Stationery', 'supplier_id' => $suppliers['PNG Office Supplies']->id],
            ['description' => 'Laptop Computer', 'uom' => 'unit', 'category' => 'Asset', 'sub_category' => 'Computer', 'supplier_id' => $suppliers['Tech Supplies Ltd']->id],
        ])->mapWithKeys(function (array $data): array {
            $item = Item::firstOrCreate(['description' => $data['description']], $data);

            return [$data['description'] => $item];
        });

        $orders = [
            [
                'number' => 'GE-DEMO-001',
                'status' => GEOrder::STATUS_DRAFT,
                'approval_status' => GEOrder::APPROVAL_NOT_SUBMITTED,
                'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
                'supplier' => 'PNG Office Supplies',
                'description' => 'Draft stationery replenishment for testing edit and delete actions.',
                'items' => [['item' => 'A4 Copy Paper', 'unit' => 'ream', 'quantity' => 10, 'unit_price' => 42.50]],
            ],
            [
                'number' => 'GE-DEMO-002',
                'status' => GEOrder::STATUS_PENDING,
                'approval_status' => GEOrder::APPROVAL_PENDING_APPROVAL,
                'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
                'supplier' => 'Tech Supplies Ltd',
                'description' => 'Pending computer purchase for approval workflow testing.',
                'items' => [['item' => 'Laptop Computer', 'unit' => 'unit', 'quantity' => 2, 'unit_price' => 3850.00]],
            ],
            [
                'number' => 'GE-DEMO-003',
                'status' => GEOrder::STATUS_APPROVED,
                'approval_status' => GEOrder::APPROVAL_APPROVED,
                'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
                'supplier' => 'PNG Office Supplies',
                'description' => 'Approved toner order for procurement and receiving testing.',
                'items' => [['item' => 'Printer Toner Cartridge', 'unit' => 'unit', 'quantity' => 4, 'unit_price' => 275.00]],
            ],
            [
                'number' => 'GE-DEMO-004',
                'status' => GEOrder::STATUS_REJECTED,
                'approval_status' => GEOrder::APPROVAL_REJECTED,
                'inventory_flag' => GEOrder::INVENTORY_FLAG_NONSTOCK,
                'supplier' => 'Tech Supplies Ltd',
                'description' => 'Rejected one-off service order for workflow testing.',
                'rejection_reason' => 'Please provide a revised supplier quotation.',
                'items' => [['description' => 'Equipment repair service', 'unit' => 'service', 'quantity' => 1, 'unit_price' => 1200.00]],
            ],
            [
                'number' => 'GE-DEMO-005',
                'status' => GEOrder::STATUS_CANCELLED,
                'approval_status' => GEOrder::APPROVAL_NOT_SUBMITTED,
                'inventory_flag' => GEOrder::INVENTORY_FLAG_NONSTOCK,
                'supplier' => 'PNG Office Supplies',
                'description' => 'Cancelled non-stock order for read-only state testing.',
                'items' => [['description' => 'Office cleaning service', 'unit' => 'service', 'quantity' => 1, 'unit_price' => 650.00]],
            ],
        ];

        foreach ($orders as $definition) {
            $orderData = [
                'user_id' => $user->id,
                'supplier_id' => $suppliers[$definition['supplier']]->id,
                'branch_id' => $branch->id,
                'account_code' => '5001-Office Supplies',
                'inventory_flag' => $definition['inventory_flag'],
                'po_number' => $definition['number'],
                'order_date' => now()->toDateString(),
                'description' => $definition['description'],
                'notes' => 'Demo record for UI testing.',
                'status' => $definition['status'],
                'approval_status' => $definition['approval_status'],
                'rejection_reason' => $definition['rejection_reason'] ?? null,
                'submitted_at' => $definition['status'] !== GEOrder::STATUS_DRAFT ? now() : null,
                'approved_at' => in_array($definition['status'], [GEOrder::STATUS_APPROVED, GEOrder::STATUS_REJECTED], true) ? now() : null,
                'approved_by' => in_array($definition['status'], [GEOrder::STATUS_APPROVED, GEOrder::STATUS_REJECTED], true) ? $user->id : null,
                'cancelled_at' => $definition['status'] === GEOrder::STATUS_CANCELLED ? now() : null,
            ];

            if (Schema::hasColumn('ge_orders', 'date')) {
                $orderData['date'] = $orderData['order_date'];
                $orderData['originator_id'] = $user->id;
                $orderData['branch'] = $branch->name;
            }

            $order = GEOrder::firstOrNew(['order_number' => $definition['number']]);
            GEOrder::withoutEvents(function () use ($order, $orderData): void {
                $order->forceFill($orderData)->save();
            });
            $order->items()->delete();

            foreach ($definition['items'] as $line) {
                $item = isset($line['item']) ? $items[$line['item']] : null;
                $description = $line['description'] ?? $item?->description;
                $quantity = $line['quantity'];
                $unitPrice = $line['unit_price'];
                $itemData = [
                    'ge_order_id' => $order->id,
                    'item_id' => $item?->id,
                    'description' => $description,
                    'unit' => $line['unit'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $quantity * $unitPrice,
                ];

                if (Schema::hasColumn('ge_order_items', 'item_description')) {
                    $itemData['item_description'] = $description;
                    $itemData['uom'] = $line['unit'];
                    $itemData['unit_cost'] = $unitPrice;
                    $itemData['total_cost'] = $quantity * $unitPrice;
                }

                $orderItem = new GEOrderItem;
                GEOrderItem::withoutEvents(function () use ($orderItem, $itemData): void {
                    $orderItem->forceFill($itemData)->save();
                });
            }

            $order->recalcTotal();
        }
    }
}
