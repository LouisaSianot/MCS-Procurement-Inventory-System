<!doctype html>
<html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;color:#1e293b;font-size:10px}h1{margin:0;color:#0f172a;font-size:18px}p{margin:4px 0 16px;color:#64748b}table{width:100%;border-collapse:collapse}th{background:#0f766e;color:white;text-align:left;font-size:9px}th,td{padding:7px;border:1px solid #cbd5e1}.footer{margin-top:14px;font-size:9px;color:#64748b}</style></head><body>
<h1>Purchase Receipts</h1><p>Generated {{ now()->format('d M Y, g:i A') }} · {{ $receipts->count() }} record(s)</p>
<table><thead><tr><th>Receipt #</th><th>Purchase Order</th><th>Supplier</th><th>Received Date</th><th>Supplier Reference</th><th>Received By</th><th>Notes</th></tr></thead><tbody>
@forelse ($receipts as $receipt)
<tr><td>{{ $receipt->receipt_number }}</td><td>{{ $receipt->purchaseOrder?->po_number ?? '—' }}</td><td>{{ $receipt->purchaseOrder?->supplier?->name ?? '—' }}</td><td>{{ $receipt->received_at?->format('d M Y') }}</td><td>{{ $receipt->supplier_delivery_reference ?? '—' }}</td><td>{{ $receipt->receiver?->name ?? '—' }}</td><td>{{ $receipt->notes ?? '—' }}</td></tr>
@empty
<tr><td colspan="7">No purchase receipts found.</td></tr>
@endforelse
</tbody></table><p class="footer">MCS Procurement & Inventory System</p></body></html>
