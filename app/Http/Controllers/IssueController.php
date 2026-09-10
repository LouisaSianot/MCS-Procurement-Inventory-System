<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Issue;
use App\Models\Item;
use App\Models\InventoryMovement;
use App\Services\StockTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IssueController extends Controller
{
    public function index()
    {
        return view('issues.index', ['issues' => Issue::with(['customer', 'item', 'branch'])->latest('date')->paginate(15)]);
    }

    public function create()
    {
        return view('issues.create', ['customers' => Customer::orderBy('customer')->get(), 'items' => Item::orderBy('description')->get(), 'branches' => Branch::orderBy('name')->get()]);
    }

    public function store(Request $request, StockTransactionService $stock)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'uom' => ['required', 'string', 'max:30'],
            'customer_id' => ['required', 'exists:customers,id'],
            'purpose' => ['required', 'string', 'max:500'],
            'date' => ['required', 'date'],
        ]);
        $data['user_id'] = $request->user()->id;
        DB::transaction(function () use ($stock, $data): void {
            $stock->decrease((int) $data['item_id'], (int) $data['branch_id'], (float) $data['quantity'], InventoryMovement::TYPE_ISSUE, $data['uom']);
            Issue::create($data);
        });
        return redirect()->route('issues.index')->with('success', 'Stock issue recorded successfully.');
    }
}
