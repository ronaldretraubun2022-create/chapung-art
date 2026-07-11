<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $shippingAreas = array_keys(config('chapung.cart.shipping_estimates', []));
        $paymentMethods = array_keys(config('chapung.checkout.payment_methods', []));

        return [
            'checkout_token' => ['required', 'string', 'max:80'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email:rfc', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_whatsapp' => ['nullable', 'string', 'max:50'],
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            'shipping_area' => ['required', 'string', Rule::in($shippingAreas)],
            'shipping_notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'string', Rule::in($paymentMethods)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $shippingAreas = array_keys(config('chapung.cart.shipping_estimates', []));
        $paymentMethods = array_keys(config('chapung.checkout.payment_methods', []));
        $cartMeta = session('cart.meta', []);
        $shippingArea = is_array($cartMeta) ? ($cartMeta['shipping_area'] ?? null) : null;

        $this->merge([
            'shipping_area' => $this->input('shipping_area') ?: ($shippingArea ?: ($shippingAreas[0] ?? 'pickup')),
            'payment_method' => $this->input('payment_method') ?: ($paymentMethods[0] ?? 'bank_transfer'),
        ]);
    }
}
