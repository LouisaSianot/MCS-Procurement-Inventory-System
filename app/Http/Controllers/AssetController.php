<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Branch;
use App\Models\Item;
use App\Models\ItemBranch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function index()
    {
        return view('assets.index', ['assets' => Asset::with(['item', 'branch'])->latest('date')->paginate(15)]);
    }

    public function create()
    {
        return view('assets.create', ['items' => Item::whereHas('branches')->orderBy('description')->get(), 'branches' => Branch::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asset' => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'date' => ['required', 'date'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['required', 'exists:branches,id'],
            'status' => ['required', Rule::in(Asset::STATUSES)],
            'po_number' => ['required', 'string', 'max:50', 'exists:purchase_orders,po_number'],
            'item_id' => ['required', 'exists:items,id'],
        ]);
        abort_unless(ItemBranch::where('item_id', $data['item_id'])->where('branch_id', $data['branch_id'])->exists(), 422, 'The selected ItemID and BranchID must exist in ItemBranch.');
        Asset::create($data);
        return redirect()->route('assets.index')->with('success', 'Asset registered successfully.');
    }
}
