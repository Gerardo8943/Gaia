<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class TransferStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'from_location_id' => ['required', 'integer', 'exists:locations,id', 'different:to_location_id'],
            'to_location_id' => ['required', 'integer', 'exists:locations,id'],
            'resource_id' => ['required', 'integer', 'exists:resources,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
