<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'delivery_address' => ['required', 'string', 'max:500'],
            'delivery_lat' => ['required', 'numeric', 'between:-90,90'],
            'delivery_lng' => ['required', 'numeric', 'between:-180,180'],
            'payment_method' => ['required', 'in:QRIS,COD'],
            'notes' => ['nullable', 'string', 'max:500'],
            'cart_items' => ['required', 'array', 'min:1'],
            'cart_items.*.id' => ['required', 'string', 'exists:products,id'],
            'cart_items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Custom validation logic for COD minimum order rule.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $items = $this->input('cart_items', []);
            $paymentMethod = $this->input('payment_method');

            $subtotal = 0;
            foreach ($items as $item) {
                $product = Product::find($item['id'] ?? null);
                if ($product) {
                    $qty = (int) ($item['quantity'] ?? 1);
                    $subtotal += ($product->price * $qty);

                    // Check stock sufficiency
                    if ($product->stock < $qty) {
                        $v->errors()->add("cart_items.{$item['id']}", "Stok untuk {$product->title} tidak mencukupi (sisa: {$product->stock}).");
                    }
                }
            }

            // COD Condition: ONLY active if subtotal meets minimum order (Rp 50.000)
            $minCodOrder = 50000;
            if ($paymentMethod === 'COD' && $subtotal < $minCodOrder) {
                $formattedMin = number_format($minCodOrder, 0, ',', '.');
                $formattedSubtotal = number_format($subtotal, 0, ',', '.');
                $v->errors()->add(
                    'payment_method',
                    "Metode pembayaran COD (Bayar di Tempat) HANYA AKTIF untuk pesanan minimal Rp {$formattedMin}. Belanja Anda saat ini Rp {$formattedSubtotal}."
                );
            }
        });
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama penerima wajib diisi.',
            'customer_phone.required' => 'Nomor WhatsApp / telepon wajib diisi.',
            'delivery_address.required' => 'Alamat pengiriman wajib diisi.',
            'cart_items.required' => 'Keranjang belanja Anda masih kosong.',
            'cart_items.min' => 'Pilih minimal 1 produk untuk checkout.',
            'payment_method.required' => 'Pilih metode pembayaran (QRIS atau COD).',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ];
    }
}

