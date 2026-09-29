<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\ItemBranch;
use App\Models\Location;

class LocationController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Location::class);
        $locations = Location::with('branch')->withCount('itemBranches')->orderBy('branch_id')->orderBy('name')->paginate(20);

        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        $this->authorize('create', Location::class);

        return view('admin.locations.form', ['location' => new Location, 'branches' => Branch::orderBy('name')->get()]);
    }

    public function store(StoreLocationRequest $request)
    {
        Location::create($request->validated());

        return redirect()->route('admin.locations.index')->with('success', 'Location created successfully.');
    }

    public function edit(Location $location)
    {
        $this->authorize('update', $location);

        return view('admin.locations.form', ['location' => $location, 'branches' => Branch::orderBy('name')->get()]);
    }

    public function update(UpdateLocationRequest $request, Location $location)
    {
        $data = $request->validated();

        if ((int) $data['branch_id'] !== (int) $location->branch_id && $this->isReferenced($location)) {
            return back()->withInput()->with('error', 'A location used by inventory or movement history cannot be moved to another branch.');
        }

        $location->update($data);
        ItemBranch::where('location_id', $location->id)->update(['location' => $location->name]);

        return redirect()->route('admin.locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $this->authorize('delete', $location);

        if ($this->isReferenced($location)) {
            return back()->with('error', 'A location used by inventory or movement history cannot be deleted.');
        }

        $location->delete();

        return redirect()->route('admin.locations.index')->with('success', 'Location deleted.');
    }

    private function isReferenced(Location $location): bool
    {
        return $location->itemBranches()->exists()
            || InventoryMovement::where('from_location_id', $location->id)->orWhere('to_location_id', $location->id)->exists();
    }
}
