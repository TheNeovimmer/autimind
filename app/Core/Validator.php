<?php
namespace App\Core;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? '';
            foreach (explode('|', $ruleSet) as $rule) {
                if ($rule === 'required' && empty($value)) {
                    $this->errors[$field][] = "$field is required";
                }
                if (str_starts_with($rule, 'min:') && strlen($value) < (int) explode(':', $rule)[1]) {
                    $this->errors[$field][] = "$field must be at least " . explode(':', $rule)[1] . " characters";
                }
                if (str_starts_with($rule, 'max:') && strlen($value) > (int) explode(':', $rule)[1]) {
                    $this->errors[$field][] = "$field must not exceed " . explode(':', $rule)[1] . " characters";
                }
                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "$field must be a valid email";
                }
                if ($rule === 'confirmed' && $value !== ($data[$field . '_confirmation'] ?? '')) {
                    $this->errors[$field][] = "$field confirmation does not match";
                }
                if (str_starts_with($rule, 'in:') && !in_array($value, explode(',', substr($rule, 3)))) {
                    $this->errors[$field][] = "$field must be one of: " . substr($rule, 3);
                }
            }
        }
        return empty($this->errors);
    }

    public function errors(): array { return $this->errors; }
}
