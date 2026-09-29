<x-app-layout title="{{ $location->exists ? 'Edit Location' : 'Add Location' }}">
    <x-page-header :title="$location->exists ? 'Edit location' : 'Add location'" :breadcrumbs="[['label' => 'Locations', 'url' => route('admin.locations.index')], ['label' => $location->exists ? 'Edit location' : 'Add location']]" />

    <form method="POST" action="{{ $location->exists ? route('admin.locations.update', $location) : route('admin.locations.store') }}" class="card max-w-3xl p-6">
        @csrf
        @if($location->exists) @method('PUT') @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form-field name="branch_id" label="Branch" type="select" :errors="$errors" required>
                <option value="">Select a branch</option>
                @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $location->branch_id) === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </x-form-field>
            <x-form-field name="name" label="Location name" :value="old('name', $location->name)" :errors="$errors" placeholder="e.g. Procurement Store" required />
            <div class="sm:col-span-2"><x-form-field name="description" label="Description" type="textarea" :value="old('description', $location->description)" :errors="$errors" placeholder="Optional details" /></div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $location->exists ? 'Save changes' : 'Create location' }}</button>
        </div>
    </form>
</x-app-layout>
