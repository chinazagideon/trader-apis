<?php

namespace App\Modules\Dashboard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
use App\Modules\User\Enums\RolesEnum;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role_id == RolesEnum::ADMIN->value;
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation()
    {
        if ($this->user()->role_id !== RolesEnum::ADMIN->value) {
            $this->merge([
                'user_id' => $this->user()?->id,
                'date_from' => Carbon::now()->startOfDay(),
                'date_to' => Carbon::now()->endOfDay(),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {

        return [
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'status' => 'sometimes|string',
            'user_id' => 'required|integer|exists:users,id',
            'currency_id' => 'sometimes|integer|exists:currencies,id',
            'type' => 'sometimes|string',
            'include_chart_data' => 'sometimes|boolean',
            'group_by' => 'sometimes|string|in:day,week,month,year',
        ];
    }
}
