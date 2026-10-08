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
            'order_id' => 'required|string|max:255|unique:midasbuy_tokens,order_id',
            'token' => 'required|integer',
            'uid' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:midasbuy_tokens,code',
            // "completed" chưa bao giờ được dùng, trạng thái thật là "success".
            // "delayed" là đơn bị hoãn vì thiếu code token.
            'status' => 'required|in:pending,delayed,success,cancelled',
            // Bắt buộc: thiếu id đơn đối tác thì không thể báo trạng thái về
            // cho đối tác, tool sẽ gọi URL thiếu id và kẹt đơn đó mãi.
            'sale_agent_id' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'sale_agent_id.required' => 'Phải có ID đơn đối tác (sale_agent_id), nếu không tool sẽ không báo được trạng thái về đối tác.',
            'sale_agent_id.min' => 'ID đơn đối tác (sale_agent_id) phải lớn hơn 0.',
        ];
    }
}
