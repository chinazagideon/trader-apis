<?php

namespace App\Modules\Investment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Investment\Database\Models\Investment;
use App\Core\Traits\AuthorizesShowRequest;
class InvestmentShowRequest extends FormRequest
{
    use AuthorizesShowRequest;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return $this->authorizeShowRequest();
        return true;
    }

    public function getModelClass(): string
    {
        return Investment::class;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1|exists:investments,id',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
}

