<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectGEOrderRequest;
use App\Http\Requests\StoreGEOrderRequest;
use App\Http\Requests\UpdateGEOrderRequest;
use App\Models\Branch;
use App\Models\GEOrder;
use App\Models\GEOrderItem;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GEOrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'approval', 'requester', 'from', 'to']);

        $query = GEOrder::with(['requester', 'supplier', 'branch', 'items'])
            ->when($filters['search'] ?? null, function ($q, $v) {
                $q->where(function ($sub) use ($v) {
                    $sub->where('order_number', 'ilike', "%{$v}%")
                        ->orWhere('description', 'ilike', "%{$v}%")
                        ->orWhere('po_number', 'ilike', "%{$v}%")
                        ->orWhereHas('supplier', fn($s) => $s->where('name', 'ilike', "%{$v}%"));
                });
            })
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['approval'] ?? null, fn($q, $v) => $q->where('approval_status', $v))
            ->when($filters['requester'] ?? null, fn($q, $v) => $q->where('user_id', $v))
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('order_date', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('order_date', '<=', $v))
            ->latest();

        $orders = $query->paginate(15)->withQueryString();

        $statusCounts = [
            'all'       => GEOrder::count(),
            'draft'     => GEOrder::where('status', 'draft')->count(),
            'pending'   => GEOrder::where('status', 'pending')->count(),
            'approved'  => GEOrder::where('status', 'approved')->count(),
            'rejected'  => GEOrder::where('status', 'rejected')->count(),
            'cancelled' => GEOrder::where('status', 'cancelled')->count(),
        ];

        $requesters = User::orderBy('name')->get(['id', 'name']);

        return view('ge-orders.index', compact('orders', 'filters', 'statusCounts', 'requesters'));
    }

    public function create()
    {
        $this->authorize('create', GEOrder::class);

        $geNumber    = GEOrder::generateOrderNumber();
        $suppliers   = Supplier::orderBy('name')->get(['id', 'name']);
        $branches    = Branch::orderBy('name')->get(['id', 'name']);
        $accountCodes = collect([
            (object)['code' => '5001-Office Supplies'],
            (object)['code' => '5002-IT Supplies'],
            (object)['code' => '5003-Health & Safety'],
            (object)['code' => '5004-Maintenance'],
        ]);
        $items = Item::orderBy('description')->get(['id', 'description as name', 'uom']);
        $users = User::where('id', auth()->id())->orWhereHas('roles', fn($q) => $q->whereIn('name', ['procurement_officer', 'purchasing_officer']))->orderBy('name')->get(['id', 'name']);

        return view('ge-orders.create', [
            'geNumber'     => $geNumber,
            'suppliers'    => $suppliers,
            'branches'     => $branches,
            'accountCodes' => $accountCodes,
            'items'        => $items,
            'users'        => $users,
            'defaultDate'  => now()->format('Y-m-d'),
            'defaultBranchId' => 201,
            'defaultUserId'   => auth()->id(),
        ]);
    }

    public function store(StoreGEOrderRequest $request)
    {
        $data = $request->validated();

        $isSubmit = ($data['action'] ?? 'save_draft') === 'submit';

        $order = DB::transaction(function () use ($data, $isSubmit, $request) {
            $order = GEOrder::create([
                'order_number'   => $data['order_number'],
                'user_id'        => $data['user_id'],
                'supplier_id'    => $data['supplier_id'],
                'branch_id'      => $data['branch_id'],
                'account_code'   => $data['account_code'],
                'inventory_flag' => $data['inventory_flag'],
                'po_number'      => $data['po_number'] ?? $data['order_number'],
                'order_date'     => $data['order_date'],
                'description'    => $data['description'],
                'notes'          => $data['notes'] ?? null,
                'status'         => $isSubmit ? GEOrder::STATUS_PENDING : GEOrder::STATUS_DRAFT,
                'approval_status' => $isSubmit ? GEOrder::APPROVAL_PENDING_APPROVAL : GEOrder::APPROVAL_NOT_SUBMITTED,
                'submitted_at'   => $isSubmit ? now() : null,
            ]);

            $this->syncItems($order, $data['items']);
            $order->recalcTotal();

            return $order;
        });

        return redirect()
            ->route('ge-orders.show', $order)
            ->with('success', $isSubmit
                ? 'GE Order ' . $order->order_number . ' has been submitted successfully for approval.'
                : 'GE Order ' . $order->order_number . ' saved as draft.');
    }

    public function show(GEOrder $ge_order)
    {
        $ge_order->load(['items', 'requester', 'supplier', 'branch', 'approver']);
        $canApprove = auth()->user()->can('approve', $ge_order);

        return view('ge-orders.show', ['order' => $ge_order, 'canApprove' => $canApprove]);
    }

    public function edit(GEOrder $ge_order)
    {
        $this->authorize('update', $ge_order);
        $ge_order->load(['items', 'requester', 'supplier', 'branch']);

        $suppliers   = Supplier::orderBy('name')->get(['id', 'name']);
        $branches    = Branch::orderBy('name')->get(['id', 'name']);
        $accountCodes = collect([
            (object)['code' => '5001-Office Supplies'],
            (object)['code' => '5002-IT Supplies'],
            (object)['code' => '5003-Health & Safety'],
            (object)['code' => '5004-Maintenance'],
        ]);
        $items = Item::orderBy('description')->get(['id', 'description as name', 'uom']);
        $users = User::where('id', $ge_order->user_id)->orWhereHas('roles', fn($q) => $q->whereIn('name', ['procurement_officer', 'purchasing_officer']))->orderBy('name')->get(['id', 'name']);

        return view('ge-orders.edit', [
            'order'        => $ge_order,
            'suppliers'    => $suppliers,
            'branches'     => $branches,
            'accountCodes' => $accountCodes,
            'items'        => $items,
            'users'        => $users,
        ]);
    }

    public function update(UpdateGEOrderRequest $request, GEOrder $ge_order)
    {
        $data = $request->validated();
        $isSubmit = ($data['action'] ?? 'save_draft') === 'submit';

        DB::transaction(function () use ($ge_order, $data, $isSubmit) {
            $ge_order->update([
                'supplier_id'    => $data['supplier_id'],
                'user_id'        => $data['user_id'],
                'branch_id'      => $data['branch_id'],
                'account_code'   => $data['account_code'],
                'inventory_flag' => $data['inventory_flag'],
                'po_number'      => $data['po_number'] ?? null,
                'order_date'     => $data['order_date'],
                'description'    => $data['description'],
                'notes'          => $data['notes'] ?? null,
                'status'         => $isSubmit ? GEOrder::STATUS_PENDING : GEOrder::STATUS_DRAFT,
                'approval_status' => $isSubmit ? GEOrder::APPROVAL_PENDING_APPROVAL : GEOrder::APPROVAL_NOT_SUBMITTED,
                'submitted_at'   => $isSubmit ? now() : null,
            ]);

            $this->syncItems($ge_order, $data['items']);
            $ge_order->recalcTotal();
        });

        return redirect()
            ->route('ge-orders.show', $ge_order)
            ->with('success', $isSubmit
                ? 'GE Order ' . $ge_order->order_number . ' has been submitted successfully for approval.'
                : 'GE Order ' . $ge_order->order_number . ' updated.');
    }

    public function destroy(GEOrder $ge_order)
    {
        $this->authorize('delete', $ge_order);
        $ge_order->delete();

        return redirect()
            ->route('ge-orders.index')
            ->with('success', 'GE Order deleted.');
    }

    public function submit(Request $request, GEOrder $ge_order)
    {
        $this->authorize('submit', $ge_order);

        $ge_order->update([
            'status'          => GEOrder::STATUS_PENDING,
            'approval_status' => GEOrder::APPROVAL_PENDING_APPROVAL,
            'submitted_at'    => now(),
        ]);

        return redirect()
            ->route('ge-orders.show', $ge_order)
            ->with('success', 'GE Order ' . $ge_order->order_number . ' has been submitted successfully for approval.');
    }

    public function approve(Request $request, GEOrder $ge_order)
    {
        $this->authorize('approve', $ge_order);

        $ge_order->update([
            'status'          => GEOrder::STATUS_APPROVED,
            'approval_status' => GEOrder::APPROVAL_APPROVED,
            'approved_at'     => now(),
            'approved_by'     => $request->user()->id,
            'rejection_reason' => null,
        ]);

        return redirect()
            ->route('ge-orders.show', $ge_order)
            ->with('success', 'GE Order approved. It is now available for procurement.');
    }

    public function reject(RejectGEOrderRequest $request, GEOrder $ge_order)
    {
        $ge_order->update([
            'status'           => GEOrder::STATUS_REJECTED,
            'approval_status'  => GEOrder::APPROVAL_REJECTED,
            'approved_at'      => now(),
            'approved_by'      => $request->user()->id,
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()
            ->route('ge-orders.show', $ge_order)
            ->with('error', 'GE Order rejected.');
    }

    public function cancel(Request $request, GEOrder $ge_order)
    {
        $this->authorize('cancel', $ge_order);

        $ge_order->update([
            'status'        => GEOrder::STATUS_CANCELLED,
            'cancelled_at'  => now(),
        ]);

        return redirect()
            ->route('ge-orders.show', $ge_order)
            ->with('warning', 'GE Order cancelled.');
    }

    /**
     * Replace the order's items, recalculating totals server-side.
     * Browser-submitted totals are never trusted.
     */
    protected function syncItems(GEOrder $order, array $items): void
    {
        $order->items()->delete();

        foreach ($items as $row) {
            $qty   = (float) ($row['quantity'] ?? 0);
            $price = (float) ($row['unit_price'] ?? 0);
            $total = $qty * $price;

            $description = trim((string) ($row['description'] ?? ''));
            if ($description === '' && ! empty($row['item_id'])) {
                $item = Item::find((int) $row['item_id']);
                $description = $item?->description ?? ($row['item_id_text'] ?? '');
            }

            GEOrderItem::create([
                'ge_order_id'  => $order->id,
                'item_id'      => ! empty($row['item_id']) ? (int) $row['item_id'] : null,
                'description'  => $description !== '' ? $description : 'Item',
                'unit'         => $row['unit'] ?? null,
                'quantity'     => $qty,
                'unit_price'   => $price,
                'total'        => $total,
            ]);
        }
    }
}
