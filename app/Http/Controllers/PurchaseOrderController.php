<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Models\GEOrder;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status']);
        $orders = PurchaseOrder::with(['geOrder', 'supplier', 'creator'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('po_number', 'like', "%{$search}%")
                        ->orWhereHas('geOrder', fn ($geOrders) => $geOrders->where('order_number', 'like', "%{$search}%"))
                        ->orWhereHas('supplier', fn ($suppliers) => $suppliers->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('order_date')
            ->paginate(15)
            ->withQueryString();

        $statusCounts = [
            'all' => PurchaseOrder::count(),
            'draft' => PurchaseOrder::where('status', PurchaseOrder::STATUS_DRAFT)->count(),
            'ordered' => PurchaseOrder::where('status', PurchaseOrder::STATUS_ORDERED)->count(),
            'backorder' => PurchaseOrder::where('status', PurchaseOrder::STATUS_BACKORDER)->count(),
            'received' => PurchaseOrder::where('status', PurchaseOrder::STATUS_FULLY_RECEIVED)->count(),
        ];

        return view('procurement.index', compact('orders', 'filters', 'statusCounts'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', PurchaseOrder::class);

        $eligibleOrders = $this->eligibleGEOrders();
        $selectedGEOrder = $eligibleOrders->firstWhere('id', (int) $request->integer('ge_order_id'));

        return view('procurement.create', [
            'poNumber' => PurchaseOrder::generateNumber(),
            'eligibleOrders' => $eligibleOrders,
            'selectedGEOrder' => $selectedGEOrder,
            'defaultDate' => now()->toDateString(),
        ]);
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        $data = $request->validated();
        $geOrder = GEOrder::with(['items', 'supplier', 'branch'])->findOrFail($data['ge_order_id']);

        if ($geOrder->status !== GEOrder::STATUS_APPROVED || $geOrder->purchaseOrder()->exists()) {
            throw ValidationException::withMessages(['ge_order_id' => 'Select an approved GE Order that does not already have a Purchase Order.']);
        }

        $purchaseOrder = DB::transaction(function () use ($data, $geOrder, $request) {
            $isOrdered = $data['action'] === 'place_order';
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $data['po_number'],
                'ge_order_id' => $geOrder->id,
                'supplier_id' => $geOrder->supplier_id,
                'branch_id' => $geOrder->branch_id,
                'user_id' => $request->user()->id,
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $isOrdered ? PurchaseOrder::STATUS_ORDERED : PurchaseOrder::STATUS_DRAFT,
                'ordered_at' => $isOrdered ? now() : null,
                'total_amount' => $geOrder->items->sum('total'),
            ]);

            foreach ($geOrder->items as $item) {
                $purchaseOrder->items()->create([
                    'item_id' => $item->item_id,
                    'description' => $item->description,
                    'unit' => $item->unit,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                ]);
            }

            return $purchaseOrder;
        });

        return redirect()->route('procurement.show', $purchaseOrder)
            ->with('success', "Purchase Order {$purchaseOrder->po_number} created successfully.");
    }

    public function show(PurchaseOrder $procurement)
    {
        $procurement->load(['items.item', 'geOrder', 'supplier', 'branch', 'creator']);

        return view('procurement.show', ['purchaseOrder' => $procurement]);
    }

    public function edit(PurchaseOrder $procurement)
    {
        $this->authorize('update', $procurement);
        $procurement->load(['items', 'geOrder', 'supplier', 'branch']);

        return view('procurement.edit', ['purchaseOrder' => $procurement]);
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $procurement)
    {
        $data = $request->validated();
        $procurement->update([
            'po_number' => $data['po_number'],
            'order_date' => $data['order_date'],
            'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'],
            'ordered_at' => $data['status'] === PurchaseOrder::STATUS_ORDERED && ! $procurement->ordered_at ? now() : $procurement->ordered_at,
        ]);

        return redirect()->route('procurement.show', $procurement)->with('success', 'Purchase Order updated.');
    }

    public function destroy(PurchaseOrder $procurement)
    {
        $this->authorize('delete', $procurement);
        $procurement->delete();

        return redirect()->route('procurement.index')->with('success', 'Purchase Order deleted.');
    }

    private function eligibleGEOrders()
    {
        return GEOrder::with(['supplier', 'branch', 'items'])
            ->where('status', GEOrder::STATUS_APPROVED)
            ->whereDoesntHave('purchaseOrder')
            ->latest('approved_at')
            ->get();
    }
}
