<?php

namespace App\Http\Controllers;

use App\Models\Adjustment;
use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Services\StockTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdjustmentController extends Controller
{
    public function index()
    {
        return view('adjustments.index', ['adjustments' => Adjustment::with(['item', 'branch'])->latest('date')->paginate(15)]);
    }

    public function create()
    {
        return view('adjustments.create', ['items' => Item::orderBy('description')->get(), 'branches' => Branch::orderBy('name')->get()]);
    }

    public function store(Request $request, StockTransactionService $stock)
    {
        $data = $request->validate([
            'adjustment_type' => ['required', Rule::in(Adjustment::TYPES)],
            'branch_id' => ['required', 'exists:branches,id'],
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'uom' => ['required', 'string', 'max:30'],
            'date' => ['required', 'date'],
            'purpose' => ['required', 'string', 'max:500'],
        ]);
        $data['user_id'] = $request->user()->id;
        DB::transaction(function () use ($stock, $data): void {
            $type = $data['adjustment_type'] === 'Adjust-IN' ? InventoryMovement::TYPE_ADJUST_IN : InventoryMovement::TYPE_ADJUST_OUT;
            $method = $data['adjustment_type'] === 'Adjust-IN' ? 'increase' : 'decrease';
            $stock->{$method}((int) $data['item_id'], (int) $data['branch_id'], (float) $data['quantity'], $type, $data['uom']);
            Adjustment::create($data);
        });
        return redirect()->route('adjustments.index')->with('success', 'Stock adjustment recorded successfully.');
    }
}
