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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {

        return [
            'date_from' => 'nullable|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'status' => 'nullable|string',
            'user_id' => 'integer|exists:users,id|nullable',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'type' => 'nullable|string',
            'include_chart_data' => 'sometimes|boolean',
            'group_by' => 'sometimes|string|in:day,week,month,year',
        ];
    }



    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation()
    {
        $user_id = null;
        if($this->user()?->isAdmin()) {
            $user_id = $this->input('user_id') ?? $this->route('id') ?? null;
        } else {
            $user_id = $this->user()?->id;
        }
        $this->merge([
            "user_id" => $user_id,
        ]);
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'The user does not exist',
            'date_from.date' => 'The date from must be a valid date',
            'date_to.date' => 'The date to must be a valid date',
            'date_to.after_or_equal' => 'The date to must be after or equal to the date from',
            'status.string' => 'The status must be a string',
            'status.in' => 'The status must be one of: pending, completed, cancelled',
            'currency_id.integer' => 'The currency id must be an integer',
            'currency_id.exists' => 'The currency does not exist',
            'type.string' => 'The type must be a string',
        ];
    }
}
