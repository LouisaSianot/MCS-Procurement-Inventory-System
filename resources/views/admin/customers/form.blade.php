<x-app-layout title="{{ $customer->exists ? 'Edit Customer' : 'Add Customer' }}">
    <x-page-header :title="$customer->exists ? 'Edit customer' : 'Add customer'" description="CustomerID is assigned from the v4 range 4001-4999." />
    <form method="POST" action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}" class="card max-w-3xl p-6">@csrf @if($customer->exists) @method('PUT') @endif
        <div class="grid gap-5 sm:grid-cols-2"><x-form-field name="customer" label="Customer" :value="old('customer', $customer->customer)" :errors="$errors" required /><x-form-field name="email" label="Email" type="email" :value="old('email', $customer->email)" :errors="$errors" required /><x-form-field name="customer_type" label="CustomerType" type="select" :errors="$errors" required><option value="">Select type</option>@foreach(\App\Models\Customer::TYPES as $type)<option value="{{ $type }}" @selected(old('customer_type', $customer->customer_type) === $type)>{{ $type }}</option>@endforeach</x-form-field></div>
        <div class="mt-6 flex justify-end gap-3"><a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Save customer</button></div>
    </form>
</x-app-layout>
