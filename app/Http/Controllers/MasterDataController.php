<?php

namespace App\Http\Controllers;

use App\Models\GEOrder;
use App\Models\GEOrderItem;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    public const CATEGORIES = ['ASSET' => ['Computer', 'ICT Equipment', 'Tools Equipment', 'Fixtures & Furniture'], 'CONSUMABLE' => ['Stationery', 'Cleaning', 'General', 'Staff']];

    public function suppliers(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $suppliers = Supplier::query()->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('contact', 'like', "%{$search}%"))->orderBy('name')->paginate(12)->withQueryString();

        return view('admin.suppliers.index', compact('suppliers', 'search'));
    }

    public function showSupplier(Supplier $supplier)
    {
        $supplier->load(['geOrders.items.item', 'geOrders.purchaseOrder']);
        $supplierId = $supplier->getKey();

        $orders = $supplier->geOrders
            ->filter(fn (GEOrder $order) => Gate::allows('view', $order))
            ->map(function (GEOrder $order) use ($supplierId) {
                $purchaseOrder = $order->purchaseOrder;
                $order->setAttribute('has_purchase_order', $purchaseOrder !== null);
                $order->setAttribute(
                    'purchase_order_visible',
                    $purchaseOrder !== null
                        && (int) $purchaseOrder->supplier_id === (int) $supplierId
                        && Gate::allows('view', $purchaseOrder)
                );

                if ($purchaseOrder !== null && ! $order->purchase_order_visible) {
                    $order->setRelation('purchaseOrder', null);
                }

                return $order;
            });

        return view('admin.suppliers.show', compact('supplier', 'orders'));
    }

    public function createSupplier()
    {
        return view('admin.suppliers.form', ['supplier' => new Supplier]);
    }

    public function editSupplier(Supplier $supplier)
    {
        return view('admin.suppliers.form', compact('supplier'));
    }

    public function storeSupplier(Request $request)
    {
        Supplier::create($this->supplierData($request));

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $supplier->update($this->supplierData($request));

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroySupplier(Supplier $supplier)
    {
        if ($supplier->items()->exists() || GEOrder::where('supplier_id', $supplier->id)->exists() || PurchaseOrder::where('supplier_id', $supplier->id)->exists()) {
            return back()->with('error', 'This supplier is referenced by items or purchasing records and cannot be deleted.');
        }
        $supplier->delete();

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier deleted.');
    }

    public function items(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $search = mb_substr($search, 0, 100);
        $searchOperator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $items = Item::query()
            ->with('supplier')
            ->when($search !== '', function ($query) use ($search, $searchOperator) {
                $query->where(function ($itemQuery) use ($search, $searchOperator) {
                    $itemQuery
                        ->where('description', $searchOperator, "%{$search}%")
                        ->orWhere('category', $searchOperator, "%{$search}%")
                        ->orWhere('sub_category', $searchOperator, "%{$search}%")
                        ->orWhere('model_number', $searchOperator, "%{$search}%")
                        ->orWhere('uom', $searchOperator, "%{$search}%");
                });
            })
            ->orderBy('description')
            ->paginate(12)
            ->withQueryString();

        return view('admin.items.index', compact('items', 'search'));
    }

    public function createItem()
    {
        return $this->itemForm(new Item);
    }

    public function editItem(Item $item)
    {
        return $this->itemForm($item);
    }

    public function storeItem(Request $request)
    {
        Item::create($this->itemData($request));

        return redirect()->route('admin.items.index')->with('success', 'Item created successfully.');
    }

    public function updateItem(Request $request, Item $item)
    {
        $data = $this->itemData($request);

        if ($item->is_serialized && ! $data['is_serialized'] && $item->serials()->exists()) {
            throw ValidationException::withMessages([
                'is_serialized' => 'This item has serialized units. Handle those serial records before changing it to a non-serialized item.',
            ]);
        }

        $item->update($data);

        return redirect()->route('admin.items.index')->with('success', 'Item updated successfully.');
    }

    public function destroyItem(Item $item)
    {
        if ($item->branches()->exists() || GEOrderItem::where('item_id', $item->id)->exists() || PurchaseOrderItem::where('item_id', $item->id)->exists()) {
            return back()->with('error', 'This item is referenced by inventory or purchase records and cannot be deleted.');
        }
        $item->delete();

        return redirect()->route('admin.items.index')->with('success', 'Item deleted.');
    }

    private function itemForm(Item $item)
    {
        return view('admin.items.form', [
            'item' => $item,
            'suppliers' => Cache::remember('lookup.suppliers.v2', now()->addMinutes(10), fn () => Supplier::orderBy('name')->get(['id', 'name'])->toArray()),
            'categories' => self::CATEGORIES,
        ]);
    }

    private function supplierData(Request $r): array
    {
        return $r->validate(['name' => ['required', 'string', 'max:255'], 'address' => ['nullable', 'string'], 'contact' => ['nullable', 'string', 'max:255'], 'payment_term' => ['required', Rule::in(['CASH', 'CREDIT'])], 'currency' => ['required', 'string', 'size:3']]);
    }

    private function itemData(Request $r): array
    {
        $data = $r->validate(['description' => ['required', 'string', 'max:255'], 'uom' => ['required', 'string', 'max:30'], 'category' => ['required', Rule::in(array_keys(self::CATEGORIES))], 'sub_category' => ['required', 'string'], 'supplier_id' => ['required', Rule::exists((new Supplier)->getTable(), 'id')], 'model_number' => ['nullable', 'string', 'max:255'], 'is_serialized' => ['boolean']]);
        if (! in_array($data['sub_category'], self::CATEGORIES[$data['category']], true)) {
            throw ValidationException::withMessages([
                'sub_category' => 'Select a sub-category that belongs to the selected category.',
            ]);
        }
        $data['category'] = ucfirst(strtolower($data['category']));
        $data['model_number'] = isset($data['model_number']) ? trim($data['model_number']) ?: null : null;
        $data['is_serialized'] = (bool) ($data['is_serialized'] ?? false);

        return $data;
    }
}
