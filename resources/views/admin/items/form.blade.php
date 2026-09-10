<x-app-layout title="{{ $item->exists ? 'Edit Item' : 'Add Item' }}">
    <x-page-header :title="$item->exists ? 'Edit item' : 'Add item'" description="Item IDs are assigned automatically by the existing database strategy." />

    <form method="POST" action="{{ $item->exists ? route('admin.items.update', $item) : route('admin.items.store') }}" class="card max-w-3xl p-6">
        @csrf
        @if($item->exists) @method('PUT') @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <x-form-field name="description" label="Item" :value="old('description', $item->description)" :errors="$errors" required />
            <x-form-field name="uom" label="UOM" :value="old('uom', $item->uom)" :errors="$errors" required />
            <x-form-field name="category" label="Category" type="select" :errors="$errors" required>
                <option value="">Select category</option>
                @foreach($categories as $category => $subcategories)
                <option value="{{ $category }}" @selected(old('category', strtoupper($item->category)) === $category)>{{ $category }}</option>
                @endforeach
            </x-form-field>
            <x-form-field name="sub_category" label="Sub-category" type="select" :errors="$errors" required>
                <option value="">Select a valid sub-category</option>
                @foreach($categories as $category => $subcategories)
                @foreach($subcategories as $subcategory)
                <option value="{{ $subcategory }}" data-category="{{ $category }}" @selected(old('sub_category', $item->sub_category) === $subcategory)>{{ $subcategory }}</option>
                @endforeach
                @endforeach
            </x-form-field>
            <x-form-field name="supplier_id" label="Primary supplier" type="select" :errors="$errors" required>
                <option value="">Select supplier</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $item->supplier_id) === (string) $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </x-form-field>
        </div>

        <p class="mt-3 text-xs text-slate-500">Only sub-categories valid for the category are accepted when the form is saved.</p>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save item</button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const category = document.getElementById('category');
            const subCategory = document.getElementById('sub_category');

            const filterSubcategories = () => {
                Array.from(subCategory.options).forEach((option) => {
                    option.hidden = option.dataset.category && option.dataset.category !== category.value;
                });

                if (subCategory.selectedOptions[0]?.hidden) {
                    subCategory.value = '';
                }
            };

            category.addEventListener('change', filterSubcategories);
            filterSubcategories();
        });
    </script>
</x-app-layout>