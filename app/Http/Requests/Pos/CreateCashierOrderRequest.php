<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class CreateCashierOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_type' => ['required', 'in:dine_in_cashier,takeaway'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'table_id' => ['nullable', 'integer', 'exists:calon_mantu,id'],
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,qris'],
            'amount_paid' => ['required_if:payment_method,cash', 'nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }
}
