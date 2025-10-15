<?php

namespace App\Modules\Notification\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class EntityTypeMorphRule implements ValidationRule
{
    protected string $typeField;
    protected array $allowedTypes;

    public function __construct(string $typeField, array $allowedTypes)
    {
        $this->typeField = $typeField;
        $this->allowedTypes = $allowedTypes;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get the actual morph type value from the request
        $morphKey = request()->input($this->typeField);

        // Check if the entity type is in allowed types
        if (!isset($this->allowedTypes[$morphKey])) {
            $fail('The selected :attribute type is invalid.');
            return;
        }

        // Get the model class from the mapping
        $modelClass = $this->allowedTypes[$morphKey];

        if (!$modelClass || !class_exists($modelClass)) {
            $fail('The selected :attribute type is invalid.');
            return;
        }

        // Check if the ID exists in the resolved model's table
        $exists = DB::table((new $modelClass())->getTable())
            ->where('id', $value)
            ->exists();

        if (!$exists) {
            $fail('The selected :attribute does not exist for the given type.');
        }
    }
}
