<?php

namespace App\Http\Requests\MidasbuyToken;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMidasbuyTokenRequest extends FormRequest
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
            'image' => 'nullable|image|max:255',
            // "delayed" phải có mặt, nếu không form sửa đơn sẽ báo lỗi 422 khi
            // admin chọn "Đang hoãn (thiếu code token)".
            'status' => "required|in:pending,delayed,success,cancelled",
            "code" => "nullable|string|max:255",
        ];
    }
}
