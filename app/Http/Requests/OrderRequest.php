<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hold_id' => 'required|integer|exists:holds,id',
        ];
    }

    public function messages(): array
    {
        return [
            'hold_id.required' => 'Hold id is required',
            'hold_id.exists'   => 'Hold not found',
        ];
    }
}
