<?php

namespace App\Modules\Investment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Investment\Database\Models\Investment;
class DestroyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('delete', Investment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [

        ];
    }
}

