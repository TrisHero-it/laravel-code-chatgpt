<?php

namespace App\Http\Requests\MidasbuyToken;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMidasbuyTokenRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_id' => 'required|string|max:255',
            'token' => 'required|integer',
            'uid' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'status' => 'required|in:pending,completed,cancelled',
        ];
    }
}
