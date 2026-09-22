<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #351719;
            font-size: 9px
        }

        h1 {
            margin: 0;
            color: #351719;
            font-size: 18px
        }

        h2 {
            margin: 18px 0 7px;
            color: #351719;
            font-size: 12px
        }

        p {
            margin: 4px 0 12px;
            color: #735D57
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th {
            background: #5A2022;
            color: #F3EDE3;
            text-align: left;
            font-size: 8px
        }

        th,
        td {
            padding: 5px;
            border: 1px solid #B5A496
        }

        .number {
            text-align: right
        }

        .summary td:first-child {
            font-weight: bold;
            width: 55%
        }

        .footer {
            margin-top: 14px;
            font-size: 8px;
            color: #735D57
        }
    </style>
</head>

<body>
    <h1>Procurement & Inventory Report</h1>
    <p>Generated {{ now()->format('d M Y, g:i A') }} · @if (collect($filters)->filter()->isNotEmpty()) Filtered results @else All records @endif</p>

    <h2>Procurement overview</h2>
    <table class="summary">
        <tbody>
            <tr>
                <td>Total Purchase Orders</td>
                <td class="number">{{ $procurementOverview->total_purchase_orders }}</td>
            </tr>
            <tr>
                <td>Total Procurement Value</td>
                <td class="number">K {{ number_format((float) $procurementOverview->total_procurement_value, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Purchase order status</h2>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th class="number">Purchase Orders</th>
                <th class="number">Procurement Value</th>
            </tr>
        </thead>
        <tbody>@forelse ($statusBreakdown as $status)<tr>
                <td>{{ ucfirst($status->status) }}</td>
                <td class="number">{{ $status->purchase_order_count }}</td>
                <td class="number">K {{ number_format((float) $status->procurement_value, 2) }}</td>
            </tr>@empty<tr>
                <td colspan="3">No purchase orders found.</td>
            </tr>@endforelse</tbody>
    </table>

    <h2>Procurement activity</h2>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th class="number">Purchase Orders</th>
                <th class="number">Procurement Value</th>
            </tr>
        </thead>
        <tbody>@forelse ($procurementActivity as $activity)<tr>
                <td>{{ \Carbon\Carbon::parse($activity->month_start)->format('M Y') }}</td>
                <td class="number">{{ $activity->purchase_order_count }}</td>
                <td class="number">K {{ number_format((float) $activity->procurement_value, 2) }}</td>
            </tr>@empty<tr>
                <td colspan="3">No procurement activity found.</td>
            </tr>@endforelse</tbody>
    </table>

    <h2>Top suppliers</h2>
    <table>
        <thead>
            <tr>
                <th>Supplier</th>
                <th class="number">Purchase Orders</th>
                <th class="number">Procurement Value</th>
            </tr>
        </thead>
        <tbody>@forelse ($supplierSummary as $supplier)<tr>
                <td>{{ $supplier->name }}</td>
                <td class="number">{{ $supplier->purchase_order_count }}</td>
                <td class="number">K {{ number_format((float) $supplier->procurement_value, 2) }}</td>
            </tr>@empty<tr>
                <td colspan="3">No supplier data found.</td>
            </tr>@endforelse</tbody>
    </table>

    <h2>Most purchased items</h2>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>UOM</th>
                <th class="number">Quantity</th>
                <th class="number">Procurement Value</th>
            </tr>
        </thead>
        <tbody>@forelse ($itemPurchasingSummary as $item)<tr>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->unit ?: '—' }}</td>
                <td class="number">{{ number_format((float) $item->quantity_ordered, 2) }}</td>
                <td class="number">K {{ number_format((float) $item->procurement_value, 2) }}</td>
            </tr>@empty<tr>
                <td colspan="4">No item purchasing data found.</td>
            </tr>@endforelse</tbody>
    </table>

    <h2>Inventory summary</h2>
    <table class="summary">
        <tbody>
            <tr>
                <td>Inventory Items</td>
                <td class="number">{{ $inventorySummary->inventory_item_count }}</td>
            </tr>
            <tr>
                <td>Current Stock</td>
                <td class="number">{{ number_format((float) $inventorySummary->current_stock, 2) }}</td>
            </tr>
            <tr>
                <td>Inventory Value</td>
                <td class="number">K {{ number_format((float) $inventorySummary->inventory_value, 2) }}</td>
            </tr>
            <tr>
                <td>Low-stock Items</td>
                <td class="number">{{ $inventorySummary->low_stock_count }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Low stock</h2>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Branch</th>
                <th class="number">Current Stock</th>
                <th class="number">Reorder Level</th>
                <th class="number">Reorder Quantity</th>
                <th class="number">Inventory Value</th>
            </tr>
        </thead>
        <tbody>@forelse ($lowStockItems as $itemBranch)<tr>
                <td>{{ $itemBranch->item?->description }}</td>
                <td>{{ $itemBranch->branchRecord?->name }}</td>
                <td class="number">{{ number_format((float) $itemBranch->current_stock, 2) }}</td>
                <td class="number">{{ number_format((float) $itemBranch->reorder_level, 2) }}</td>
                <td class="number">{{ number_format((float) $itemBranch->reorder_quantity, 2) }}</td>
                <td class="number">K {{ number_format((float) $itemBranch->inventoryValue(), 2) }}</td>
            </tr>@empty<tr>
                <td colspan="6">No low-stock items found.</td>
            </tr>@endforelse</tbody>
    </table>
    <p class="footer">MCS Procurement & Inventory System</p>
</body>

</html>