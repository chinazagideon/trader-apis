<?php

namespace App\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

class MorphExistsRule implements ValidationRule
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
        // Now use the morph key to get the model class
        $modelClass = $this->allowedTypes[$morphKey];


        if (!$modelClass || !class_exists($modelClass)) {
            // This case should be handled by a simple 'in' rule on the type field,
            // but we add a check for safety.
            $fail('The selected :attribute type is invalid.');
            return;
        }

        // 3. Check if the ID exists in the resolved model's table
        $exists = DB::table((new $modelClass())->getTable())
            ->where('id', $value)
            ->exists();

        if (!$exists) {
            $fail('The selected :attribute does not exist for the given type.');
        }
    }
}
