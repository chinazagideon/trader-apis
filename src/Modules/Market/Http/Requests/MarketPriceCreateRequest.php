<?php

namespace App\Modules\Market\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class MarketPriceCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'market_id' => 'required|integer|min:1|exists:markets,id',
            'currency_id' => 'required|integer|min:1|exists:currencies,id',
            'price' => 'required|numeric|min:0.01',
            'market_cap' => 'required|numeric',
            'total_supply' => 'required|numeric',
            'max_supply' => 'required|numeric',
            'circulating_supply' => 'required|numeric',
            'total_volume' => 'required|numeric',
            'total_volume_24h' => 'required|numeric',
            'total_volume_7d' => 'required|numeric',
            'total_volume_30d' => 'required|numeric'
        ];
    }

    public function messages(): array
    {
        return [
            'market_id.required' => 'The market id is required.',
            'market_id.exists' => 'The selected market does not exist.',
            'currency_id.required' => 'The currency id is required.',
            'currency_id.exists' => 'The selected currency does not exist.',

        ];
    }

    // public function prepareForValidation(): void
    // {
    //     $this->merge([
    //         'market_id' => $this->route('market'),
    //         'currency_id' => $this->route('currency'),
    //     ]);
    // }
}
