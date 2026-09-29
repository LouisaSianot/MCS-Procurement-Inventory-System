<?php

namespace App\Http\Requests;

use App\Models\Item;
use App\Models\ItemBranch;
use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transfer', ItemBranch::class);
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', Rule::exists((new Item)->getTable(), 'id')],
            'from_location_id' => ['required', 'integer', Rule::exists((new Location)->getTable(), 'id')],
            'to_location_id' => ['required', 'integer', Rule::exists((new Location)->getTable(), 'id')],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            $itemId = $this->integer('item_id');
            $fromLocationId = $this->integer('from_location_id');
            $toLocationId = $this->integer('to_location_id');
            $quantity = (float) $this->input('quantity', 0);

            if (! $itemId || ! $fromLocationId || ! $toLocationId) {
                return;
            }

            $sourceLocation = Location::find($fromLocationId);
            $destination = Location::find($toLocationId);
            $source = ItemBranch::query()
                ->where('item_id', $itemId)
                ->where('location_id', $fromLocationId)
                ->first();

            if (! $source || (float) $source->current_stock <= 0) {
                $validator->errors()->add('from_location_id', 'The item has no available stock at the selected source location.');
            } elseif ($quantity > (float) $source->current_stock) {
                $validator->errors()->add('quantity', 'The transfer quantity exceeds available source stock.');
            }

            if ($fromLocationId === $toLocationId) {
                $validator->errors()->add('to_location_id', 'Choose a destination different from the source location.');
            }

            if ($sourceLocation && $destination && $sourceLocation->branch_id !== $destination->branch_id) {
                $validator->errors()->add('to_location_id', 'Source and destination locations must belong to the same branch.');
            }

            if ($source && $sourceLocation && (int) $source->branch_id !== (int) $sourceLocation->branch_id) {
                $validator->errors()->add('from_location_id', 'The selected source location does not match the inventory branch.');
            }
        }];
    }
}
