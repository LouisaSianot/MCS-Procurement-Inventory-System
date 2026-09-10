<?php

namespace App\Http\Controllers;

use App\Models\GEOrder;
use App\Models\Customer;
use App\Models\GEOrderItem;
use App\Models\PurchaseOrder;
use App\Models\Item;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{
    public const CATEGORIES = ["ASSET" => ["Computer", "ICT Equipment", "Tools Equipment", "Fixtures & Furniture"], "CONSUMABLE" => ["Stationery", "Cleaning", "General", "Staff"]];

    public function suppliers(Request $request) { $search = trim((string) $request->query("search")); $suppliers = Supplier::query()->when($search, fn ($q) => $q->where("name", "like", "%{$search}%")->orWhere("contact", "like", "%{$search}%"))->orderBy("name")->paginate(12)->withQueryString(); return view("admin.suppliers.index", compact("suppliers", "search")); }
    public function createSupplier() { return view("admin.suppliers.form", ["supplier" => new Supplier]); }
    public function editSupplier(Supplier $supplier) { return view("admin.suppliers.form", compact("supplier")); }
    public function storeSupplier(Request $request) { Supplier::create($this->supplierData($request)); return redirect()->route("admin.suppliers.index")->with("success", "Supplier created successfully."); }
    public function updateSupplier(Request $request, Supplier $supplier) { $supplier->update($this->supplierData($request)); return redirect()->route("admin.suppliers.index")->with("success", "Supplier updated successfully."); }
    public function destroySupplier(Supplier $supplier) { if ($supplier->items()->exists() || GEOrder::where("supplier_id", $supplier->id)->exists() || PurchaseOrder::where("supplier_id", $supplier->id)->exists()) return back()->with("error", "This supplier is referenced by items or purchasing records and cannot be deleted."); $supplier->delete(); return redirect()->route("admin.suppliers.index")->with("success", "Supplier deleted."); }
    public function items(Request $request) { $search = trim((string) $request->query("search")); $items = Item::with("supplier")->when($search, fn ($q) => $q->where("description", "like", "%{$search}%")->orWhere("category", "like", "%{$search}%"))->orderBy("description")->paginate(12)->withQueryString(); return view("admin.items.index", compact("items", "search")); }
    public function createItem() { return $this->itemForm(new Item); }
    public function editItem(Item $item) { return $this->itemForm($item); }
    public function storeItem(Request $request) { Item::create($this->itemData($request)); return redirect()->route("admin.items.index")->with("success", "Item created successfully."); }
    public function updateItem(Request $request, Item $item) { $item->update($this->itemData($request)); return redirect()->route("admin.items.index")->with("success", "Item updated successfully."); }
    public function destroyItem(Item $item) { if ($item->branches()->exists() || GEOrderItem::where("item_id", $item->id)->exists() || PurchaseOrderItem::where("item_id", $item->id)->exists()) return back()->with("error", "This item is referenced by inventory or purchase records and cannot be deleted."); $item->delete(); return redirect()->route("admin.items.index")->with("success", "Item deleted."); }
    public function customers(Request $request) { $search = trim((string) $request->query("search")); $customers = Customer::query()->when($search, fn ($q) => $q->where("customer", "like", "%{$search}%")->orWhere("email", "like", "%{$search}%"))->orderBy("id")->paginate(12)->withQueryString(); return view("admin.customers.index", compact("customers", "search")); }
    public function createCustomer() { return view("admin.customers.form", ["customer" => new Customer]); }
    public function editCustomer(Customer $customer) { return view("admin.customers.form", compact("customer")); }
    public function storeCustomer(Request $request) { Customer::create($this->customerData($request)); return redirect()->route("admin.customers.index")->with("success", "Customer created successfully."); }
    public function updateCustomer(Request $request, Customer $customer) { $customer->update($this->customerData($request)); return redirect()->route("admin.customers.index")->with("success", "Customer updated successfully."); }
    public function destroyCustomer(Customer $customer) { if (class_exists(\App\Models\Issue::class) && $customer->issues()->exists()) return back()->with("error", "This customer is referenced by an issue and cannot be deleted."); $customer->delete(); return redirect()->route("admin.customers.index")->with("success", "Customer deleted."); }
    private function customerData(Request $r): array { return $r->validate(["customer" => ["required", "string", "max:255"], "customer_type" => ["required", Rule::in(Customer::TYPES)], "email" => ["required", "email", "max:255", Rule::unique("customers", "email")->ignore($r->route("customer"))]]); }
    private function itemForm(Item $item) { return view("admin.items.form", ["item" => $item, "suppliers" => Supplier::orderBy("name")->get(), "categories" => self::CATEGORIES]); }
    private function supplierData(Request $r): array { return $r->validate(["name" => ["required", "string", "max:255"], "address" => ["nullable", "string"], "contact" => ["nullable", "string", "max:255"], "payment_term" => ["required", Rule::in(["CASH", "CREDIT"])], "currency" => ["required", "string", "size:3"]]); }
    private function itemData(Request $r): array { $data = $r->validate(["description" => ["required", "string", "max:255"], "uom" => ["required", "string", "max:30"], "category" => ["required", Rule::in(array_keys(self::CATEGORIES))], "sub_category" => ["required", "string"], "supplier_id" => ["required", "exists:suppliers,id"]]); if (! in_array($data["sub_category"], self::CATEGORIES[$data["category"]], true)) return validator([], [])->errors()->add("sub_category", "Select a sub-category that belongs to the selected category.")->throwResponse(); $data["category"] = ucfirst(strtolower($data["category"])); return $data; }
}
