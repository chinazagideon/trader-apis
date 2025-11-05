<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Core\Rules\MorphExistsRule;
use App\Modules\Payment\Config\Payment as ConfigPayment;
use App\Modules\Payment\Database\Models\Payment;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        // return $this->user()->can('viewAny', Payment::class);
        return true;
    }

    public function rules(): array
    {
        return [
            // 'id' => 'required|integer|exists:payments,id',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
