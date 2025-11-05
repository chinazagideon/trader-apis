<?php

namespace App\Modules\Investment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Investment\Database\Models\Investment;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Investment::class);

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|integer|min:1,exists:users,id',
            'pricing_id' => 'sometimes|integer|min:1,exists:pricings,id',
            'category_id' => 'sometimes|integer|min:1,exists:categories,id',
            'status' => 'sometimes|string|in:pending,cancelled,running,completed',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'notes' => 'sometimes|string|max:500',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1',
        ];
    }
}
