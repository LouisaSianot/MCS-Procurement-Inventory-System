<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\GEOrder;
use App\Models\GEOrderItem;
use App\Models\ItemBranch;
use App\Models\Item;
use App\Models\Location;
use App\Models\PurchaseOrder;
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
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'user_identifier' => (string) \Illuminate\Support\Str::uuid(),
            ]
        );

        if ($role = Role::where('name', 'super_admin')->first()) {
            $user->assignRole($role);
        }

        $suppliers = collect([
            [
                'name' => 'PNG Office Systems',
                'address' => 'Hohola, Port Moresby, NCD',
                'contact' => 'sales@pngofficesystems.com.pg',
                'payment_term' => 'CREDIT',
                'currency' => 'PGK',
            ],
            [
                'name' => 'Bena ICT Solutions',
                'address' => 'Lae, Morobe Province',
                'contact' => 'orders@benaict.com.pg',
                'payment_term' => 'CREDIT',
                'currency' => 'PGK',
            ],
            [
                'name' => 'CleanCare Supplies',
                'address' => 'Waigani, Port Moresby',
                'contact' => 'support@cleancaresupplies.com.pg',
                'payment_term' => 'CREDIT',
                'currency' => 'PGK',
            ],
            [
                'name' => 'Kone Hardware & Tools',
                'address' => 'Goroka, Eastern Highlands',
                'contact' => 'sales@konehardware.com.pg',
                'payment_term' => 'CASH',
                'currency' => 'PGK',
            ],
            [
                'name' => 'Pacific General Traders',
                'address' => 'Kokopo, East New Britain',
                'contact' => 'purchases@pacificgeneraltraders.com.pg',
                'payment_term' => 'CREDIT',
                'currency' => 'PGK',
            ],
        ])->mapWithKeys(function (array $data): array {
            $supplier = Supplier::firstOrCreate(['name' => $data['name']], $data);

            return [$data['name'] => $supplier];
        });

        $items = collect([
            ['description' => 'A4 Copy Paper 80gsm', 'uom' => 'ream', 'category' => 'Consumable', 'sub_category' => 'Stationery', 'supplier_id' => $suppliers['PNG Office Systems']->id],
            ['description' => 'Brother Toner TN-2430', 'uom' => 'unit', 'category' => 'Consumable', 'sub_category' => 'Printing', 'supplier_id' => $suppliers['PNG Office Systems']->id],
            ['description' => 'Dell Latitude 5440 Laptop', 'uom' => 'unit', 'category' => 'Asset', 'sub_category' => 'Computing', 'supplier_id' => $suppliers['Bena ICT Solutions']->id],
            ['description' => 'HP LaserJet Pro M404dn', 'uom' => 'unit', 'category' => 'Asset', 'sub_category' => 'Office Equipment', 'supplier_id' => $suppliers['Bena ICT Solutions']->id],
            ['description' => 'Industrial Floor Cleaner 20L', 'uom' => 'container', 'category' => 'Consumable', 'sub_category' => 'Cleaning', 'supplier_id' => $suppliers['CleanCare Supplies']->id],
            ['description' => 'Toilet Tissue Roll Pack', 'uom' => 'pack', 'category' => 'Consumable', 'sub_category' => 'Cleaning', 'supplier_id' => $suppliers['CleanCare Supplies']->id],
            ['description' => 'M12 Drill Bit Set', 'uom' => 'set', 'category' => 'Consumable', 'sub_category' => 'Tools', 'supplier_id' => $suppliers['Kone Hardware & Tools']->id],
            ['description' => '4MP Outdoor CCTV Camera', 'uom' => 'unit', 'category' => 'Asset', 'sub_category' => 'Security', 'supplier_id' => $suppliers['Pacific General Traders']->id],
            ['description' => '5KVA Generator AVR', 'uom' => 'unit', 'category' => 'Asset', 'sub_category' => 'Power', 'supplier_id' => $suppliers['Pacific General Traders']->id],
        ])->mapWithKeys(function (array $data): array {
            $item = Item::firstOrCreate(['description' => $data['description']], $data);

            return [$data['description'] => $item];
        });

        $orders = [
            [
                'number' => 'GE-00001',
                'status' => GEOrder::STATUS_DRAFT,
                'approval_status' => GEOrder::APPROVAL_NOT_SUBMITTED,
                'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
                'supplier' => 'PNG Office Systems',
                'description' => 'Monthly office stationery replenishment for administrative departments.',
                'notes' => 'High-priority restock before end-of-month reporting cycle.',
                'items' => [
                    ['item' => 'A4 Copy Paper 80gsm', 'unit' => 'ream', 'quantity' => 12, 'unit_price' => 42.00],
                    ['item' => 'Brother Toner TN-2430', 'unit' => 'unit', 'quantity' => 4, 'unit_price' => 260.00],
                ],
            ],
            [
                'number' => 'GE-00002',
                'status' => GEOrder::STATUS_PENDING,
                'approval_status' => GEOrder::APPROVAL_PENDING_APPROVAL,
                'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
                'supplier' => 'Bena ICT Solutions',
                'description' => 'Laptop refresh for finance and admin staff to support field reporting.',
                'notes' => 'Requested after asset review flagged aging workstations.',
                'items' => [
                    ['item' => 'Dell Latitude 5440 Laptop', 'unit' => 'unit', 'quantity' => 3, 'unit_price' => 3850.00],
                    ['item' => 'HP LaserJet Pro M404dn', 'unit' => 'unit', 'quantity' => 1, 'unit_price' => 1850.00],
                ],
            ],
            [
                'number' => 'GE-00003',
                'status' => GEOrder::STATUS_APPROVED,
                'approval_status' => GEOrder::APPROVAL_APPROVED,
                'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
                'supplier' => 'CleanCare Supplies',
                'description' => 'Cleaning consumables for classrooms, restrooms, and office blocks.',
                'notes' => 'Approved for the next monthly facility hygiene schedule.',
                'items' => [
                    ['item' => 'Industrial Floor Cleaner 20L', 'unit' => 'container', 'quantity' => 12, 'unit_price' => 185.00],
                    ['item' => 'Toilet Tissue Roll Pack', 'unit' => 'pack', 'quantity' => 30, 'unit_price' => 32.50],
                ],
            ],
            [
                'number' => 'GE-00004',
                'status' => GEOrder::STATUS_REJECTED,
                'approval_status' => GEOrder::APPROVAL_REJECTED,
                'inventory_flag' => GEOrder::INVENTORY_FLAG_NONSTOCK,
                'supplier' => 'Kone Hardware & Tools',
                'description' => 'Workshop tool purchase for maintenance and minor repair works.',
                'notes' => 'Supplier quote did not match approved specification list.',
                'rejection_reason' => 'Please resubmit with the approved tool specification and pricing comparison.',
                'items' => [
                    ['item' => 'M12 Drill Bit Set', 'unit' => 'set', 'quantity' => 3, 'unit_price' => 420.00],
                ],
            ],
            [
                'number' => 'GE-00005',
                'status' => GEOrder::STATUS_CANCELLED,
                'approval_status' => GEOrder::APPROVAL_NOT_SUBMITTED,
                'inventory_flag' => GEOrder::INVENTORY_FLAG_NONSTOCK,
                'supplier' => 'Pacific General Traders',
                'description' => 'Security system upgrade planned for the main gate and compound office.',
                'notes' => 'Cancelled pending a revised site survey and budget approval.',
                'items' => [
                    ['item' => '4MP Outdoor CCTV Camera', 'unit' => 'unit', 'quantity' => 6, 'unit_price' => 690.00],
                    ['item' => '5KVA Generator AVR', 'unit' => 'unit', 'quantity' => 1, 'unit_price' => 3200.00],
                ],
            ],
            [
                'number' => 'GE-00006',
                'status' => GEOrder::STATUS_APPROVED,
                'approval_status' => GEOrder::APPROVAL_APPROVED,
                'inventory_flag' => GEOrder::INVENTORY_FLAG_STOCK,
                'supplier' => 'Pacific General Traders',
                'description' => 'Security equipment replenishment for campus facilities.',
                'notes' => 'Approved for the next facilities security upgrade.',
                'items' => [
                    ['item' => '4MP Outdoor CCTV Camera', 'unit' => 'unit', 'quantity' => 3, 'unit_price' => 690.00],
                    ['item' => '5KVA Generator AVR', 'unit' => 'unit', 'quantity' => 1, 'unit_price' => 3200.00],
                ],
            ],
        ];

        foreach ($orders as $definition) {
            $orderData = [
                'user_id' => $user->id,
                'supplier_id' => $suppliers[$definition['supplier']]->id,
                'branch_id' => $branch->id,
                'account_code' => '5001-OPERATIONS',
                'inventory_flag' => $definition['inventory_flag'],
                'po_number' => $definition['number'],
                'order_date' => now()->subDays(rand(1, 30))->toDateString(),
                'description' => $definition['description'],
                'notes' => $definition['notes'] ?? 'Operational procurement request.',
                'status' => $definition['status'],
                'approval_status' => $definition['approval_status'],
                'rejection_reason' => $definition['rejection_reason'] ?? null,
                'submitted_at' => $definition['status'] !== GEOrder::STATUS_DRAFT ? now()->subDays(rand(1, 20)) : null,
                'approved_at' => in_array($definition['status'], [GEOrder::STATUS_APPROVED, GEOrder::STATUS_REJECTED], true) ? now()->subDays(rand(1, 10)) : null,
                'approved_by' => in_array($definition['status'], [GEOrder::STATUS_APPROVED, GEOrder::STATUS_REJECTED], true) ? $user->id : null,
                'cancelled_at' => $definition['status'] === GEOrder::STATUS_CANCELLED ? now()->subDays(2) : null,
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

        $purchaseOrderSources = [
            'PO-DEMO-0001' => 'GE-00003',
            'PO-DEMO-0002' => 'GE-00006',
        ];

        foreach ($purchaseOrderSources as $poNumber => $geOrderNumber) {
            $sourceOrder = GEOrder::with('items')->where('order_number', $geOrderNumber)->firstOrFail();
            $purchaseOrder = PurchaseOrder::updateOrCreate(
                ['po_number' => $poNumber],
                [
                    'ge_order_id' => $sourceOrder->id,
                    'supplier_id' => $sourceOrder->supplier_id,
                    'branch_id' => $sourceOrder->branch_id,
                    'user_id' => $sourceOrder->user_id,
                    'order_date' => $sourceOrder->order_date,
                    'expected_delivery_date' => now()->addDays(14)->toDateString(),
                    'notes' => $sourceOrder->description,
                    'status' => PurchaseOrder::STATUS_ORDERED,
                    'total_amount' => $sourceOrder->items->sum('total'),
                    'ordered_at' => now(),
                ]
            );

            foreach ($sourceOrder->items as $sourceItem) {
                $purchaseOrder->items()->updateOrCreate(
                    ['item_id' => $sourceItem->item_id],
                    [
                        'description' => $sourceItem->description,
                        'unit' => $sourceItem->unit,
                        'quantity' => $sourceItem->quantity,
                        'unit_price' => $sourceItem->unit_price,
                        'total' => $sourceItem->total,
                    ]
                );
            }
        }

        $locations = collect([
            'Central Stores' => 'Main campus central inventory store.',
            'ICT Store' => 'Secure storage for computing and security equipment.',
            'Facilities Store' => 'Facilities and cleaning supplies store.',
        ])->mapWithKeys(function (string $description, string $name) use ($branch): array {
            $location = Location::updateOrCreate(
                ['branch_id' => $branch->id, 'name' => $name],
                ['description' => $description]
            );

            return [$name => $location];
        });

        $inventory = [
            ['item' => 'A4 Copy Paper 80gsm', 'location' => 'Central Stores', 'stock' => 32, 'cost' => 42.00, 'reorder_level' => 12],
            ['item' => 'Brother Toner TN-2430', 'location' => 'Central Stores', 'stock' => 8, 'cost' => 260.00, 'reorder_level' => 3],
            ['item' => 'Dell Latitude 5440 Laptop', 'location' => 'ICT Store', 'stock' => 2, 'cost' => 3850.00, 'reorder_level' => 1],
            ['item' => 'HP LaserJet Pro M404dn', 'location' => 'ICT Store', 'stock' => 1, 'cost' => 1850.00, 'reorder_level' => 1],
            ['item' => '4MP Outdoor CCTV Camera', 'location' => 'ICT Store', 'stock' => 3, 'cost' => 690.00, 'reorder_level' => 1],
            ['item' => 'Industrial Floor Cleaner 20L', 'location' => 'Facilities Store', 'stock' => 6, 'cost' => 185.00, 'reorder_level' => 3],
            ['item' => 'Toilet Tissue Roll Pack', 'location' => 'Facilities Store', 'stock' => 20, 'cost' => 32.50, 'reorder_level' => 10],
        ];

        foreach ($inventory as $stock) {
            $item = $items[$stock['item']];

            ItemBranch::updateOrCreate(
                ['item_id' => $item->id, 'location_id' => $locations[$stock['location']]->id],
                [
                    'branch_id' => $branch->id,
                    'uom' => $item->uom,
                    'current_stock' => $stock['stock'],
                    'unit_cost' => $stock['cost'],
                    'reorder_level' => $stock['reorder_level'],
                    'reorder_quantity' => max(1, $stock['reorder_level']),
                ]
            );
        }
    }
}
