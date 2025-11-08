<?php

namespace App\Modules\Market\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class MarketCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            "slug" => 'required|string|max:255',
            'currency_id' => 'required|integer|min:1|exists:currencies,id',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255'
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->name, '-'),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'slug.required' => 'The slug is required.',
            'slug.string' => 'The slug must be a string.',
            'slug.max' => 'The slug may not be greater than 255 characters.',
            'currency_id.required' => 'The currency id is required.',
            'currency_id.integer' => 'The currency id must be an integer.',
            'currency_id.min' => 'The currency id must be greater than 0.',
            'currency_id.exists' => 'The selected currency does not exist.',
        ];
    }
}
