<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $inventoryId = $this->route('feedInventory')?->id;

        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'feed_type_id' => [
                'required',
                'exists:feed_types,id',
                Rule::unique('feed_inventories')->where(fn ($q) => $q->where('farm_id', $this->input('farm_id')))->ignore($inventoryId),
            ],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
