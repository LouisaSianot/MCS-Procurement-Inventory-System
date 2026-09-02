<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseReceiptRequest;
use App\Models\GEOrder;
use App\Models\InventoryMovement;
use App\Models\ItemBranch;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseReceiptController extends Controller
{
    public function index()
    {
        $receipts = PurchaseReceipt::with(['purchaseOrder.supplier', 'receiver'])
            ->latest('received_at')
            ->latest('id')
            ->paginate(15);

        return view('receiving.index', compact('receipts'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', PurchaseReceipt::class);

        $purchaseOrders = PurchaseOrder::with(['supplier', 'branch', 'geOrder', 'items.receiptItems'])
            ->whereIn('status', [PurchaseOrder::STATUS_ORDERED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED])
            ->latest('ordered_at')
            ->get();
        $selectedPurchaseOrder = $purchaseOrders->firstWhere('id', (int) $request->integer('purchase_order_id'));

        return view('receiving.create', [
            'purchaseOrders' => $purchaseOrders,
            'selectedPurchaseOrder' => $selectedPurchaseOrder,
            'receiptNumber' => PurchaseReceipt::generateNumber(),
            'defaultDate' => now()->toDateString(),
        ]);
    }

    public function store(StorePurchaseReceiptRequest $request)
    {
        $data = $request->validated();

        $receipt = DB::transaction(function () use ($data, $request) {
            $purchaseOrder = PurchaseOrder::query()
                ->lockForUpdate()
                ->with(['geOrder', 'branch', 'items.receiptItems'])
                ->findOrFail($data['purchase_order_id']);

            if (! in_array($purchaseOrder->status, [PurchaseOrder::STATUS_ORDERED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED], true)) {
                throw ValidationException::withMessages(['purchase_order_id' => 'Only ordered or partially received purchase orders can be received.']);
            }

            $lines = $purchaseOrder->items->keyBy('id');
            foreach ($data['items'] as $row) {
                $line = $lines->get((int) $row['purchase_order_item_id']);
                if (! $line) {
                    throw ValidationException::withMessages(['items' => 'Every receipt line must belong to the selected purchase order.']);
                }

                $received = (float) $line->receiptItems->sum('quantity_received');
                if ((float) $row['quantity_received'] > ((float) $line->quantity - $received)) {
                    throw ValidationException::withMessages(['items' => "Received quantity for {$line->description} exceeds the outstanding quantity."]);
                }

                if ($purchaseOrder->geOrder->inventory_flag === GEOrder::INVENTORY_FLAG_STOCK && ! $line->item_id) {
                    throw ValidationException::withMessages(['items' => "Stock receipt line {$line->description} must reference an inventory item."]);
                }
            }

            $receipt = PurchaseReceipt::create([
                'receipt_number' => $data['receipt_number'],
                'purchase_order_id' => $purchaseOrder->id,
                'received_by' => $request->user()->id,
                'received_at' => $data['received_at'],
                'supplier_delivery_reference' => $data['supplier_delivery_reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $row) {
                /** @var PurchaseOrderItem $line */
                $line = $lines->get((int) $row['purchase_order_item_id']);
                $receiptItem = $receipt->items()->create([
                    'purchase_order_item_id' => $line->id,
                    'quantity_received' => $row['quantity_received'],
                    'unit_cost' => $row['unit_cost'],
                ]);

                if ($purchaseOrder->geOrder->inventory_flag === GEOrder::INVENTORY_FLAG_STOCK) {
                    $itemBranch = ItemBranch::query()
                        ->where('item_id', $line->item_id)
                        ->where('branch_id', $purchaseOrder->branch_id)
                        ->lockForUpdate()
                        ->first();

                    if (! $itemBranch) {
                        $itemBranch = ItemBranch::create([
                            'item_id' => $line->item_id,
                            'branch_id' => $purchaseOrder->branch_id,
                            // Retained only for legacy rows while branch_id becomes authoritative.
                            'branch' => $purchaseOrder->branch->name,
                            'uom' => $line->unit,
                        ]);
                    }

                    $oldStock = (float) $itemBranch->current_stock;
                    $quantity = (float) $row['quantity_received'];
                    $newStock = $oldStock + $quantity;
                    $itemBranch->update([
                        'current_stock' => $newStock,
                        'unit_cost' => $newStock > 0
                            ? (($oldStock * (float) $itemBranch->unit_cost) + ($quantity * (float) $row['unit_cost'])) / $newStock
                            : 0,
                    ]);

                    InventoryMovement::create([
                        'item_branch_id' => $itemBranch->id,
                        'purchase_receipt_item_id' => $receiptItem->id,
                        'type' => InventoryMovement::TYPE_RECEIPT,
                        'quantity' => $quantity,
                        'unit_cost' => $row['unit_cost'],
                        'stock_after' => $newStock,
                    ]);
                }
            }

            $purchaseOrder->load('items.receiptItems');
            $fullyReceived = $purchaseOrder->items->every(fn (PurchaseOrderItem $line) =>
                (float) $line->receiptItems->sum('quantity_received') >= (float) $line->quantity
            );
            $purchaseOrder->update([
                'status' => $fullyReceived
                    ? PurchaseOrder::STATUS_FULLY_RECEIVED
                    : PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            ]);

            return $receipt;
        });

        return redirect()->route('receiving.show', $receipt)
            ->with('success', "Receipt {$receipt->receipt_number} posted successfully.");
    }

    public function show(PurchaseReceipt $receiving)
    {
        $receiving->load(['purchaseOrder.supplier', 'purchaseOrder.branch', 'items.purchaseOrderItem.item', 'items.inventoryMovement.itemBranch', 'receiver']);

        return view('receiving.show', ['receipt' => $receiving]);
    }
}
