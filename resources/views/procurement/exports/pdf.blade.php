<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #351719;
            font-size: 10px
        }

        h1 {
            margin: 0;
            color: #351719;
            font-size: 18px
        }

        p {
            margin: 4px 0 16px;
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
            font-size: 9px
        }

        th,
        td {
            padding: 7px;
            border: 1px solid #B5A496
        }

        td.amount {
            text-align: right
        }

        .footer {
            margin-top: 14px;
            font-size: 9px;
            color: #735D57
        }
    </style>
</head>

<body>
    <h1>Purchase Orders</h1>
    <p>Generated {{ now()->format('d M Y, g:i A') }} · {{ $orders->count() }} record(s)
        @if (! empty($filters['search']) || ! empty($filters['status']))
        · Filtered results
        @endif
    </p>
    <table>
        <thead>
            <tr>
                <th>PO #</th>
                <th>GE Order</th>
                <th>Supplier</th>
                <th>Order Date</th>
                <th>Expected Delivery</th>
                <th>Amount (PGK)</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Receiving Person Name</th>
                <th>Receiving Person Position</th>
                <th>Receiving Person Branch</th>
                <th>Receiving Person Phone</th>
                <th>Receiving Person Email</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
            <tr>
                <td>{{ $order->po_number }}</td>
                <td>{{ $order->geOrder?->order_number ?? '—' }}</td>
                <td>{{ $order->supplier?->name ?? '—' }}</td>
                <td>{{ $order->order_date?->format('d M Y') }}</td>
                <td>{{ $order->expected_delivery_date?->format('d M Y') ?? '—' }}</td>
                <td class="amount">K {{ number_format((float) $order->total_amount, 2) }}</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td>{{ $order->creator?->name ?? '—' }}</td>
                <td>{{ $order->receiving_person_name }}</td>
                <td>{{ $order->receiving_person_position }}</td>
                <td>{{ $order->receiving_person_branch }}</td>
                <td>{{ $order->receiving_person_phone ?? '—' }}</td>
                <td>{{ $order->receiving_person_email ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13">No purchase orders found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <p class="footer">MCS Procurement & Inventory System</p>
</body>

</html>