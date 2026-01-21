<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Input Validator
 *
 * Validates input data against a set of rules.
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $validated = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Run validation
     */
    public function validate(): bool
    {
        foreach ($this->rules as $field => $rules) {
            $rules = is_string($rules) ? explode('|', $rules) : $rules;
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }

            // Add to validated if no errors for this field
            if (!isset($this->errors[$field])) {
                $this->validated[$field] = $value;
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply a single rule
     */
    private function applyRule(string $field, mixed $value, string $rule): void
    {
        // Parse rule and parameters
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $params = isset($parts[1]) ? explode(',', $parts[1]) : [];

        $method = 'validate' . str_replace('_', '', ucwords($ruleName, '_'));

        if (method_exists($this, $method)) {
            if (!$this->$method($field, $value, $params)) {
                $this->addError($field, $ruleName, $params);
            }
        }
    }

    /**
     * Add validation error
     */
    private function addError(string $field, string $rule, array $params = []): void
    {
        $messages = [
            'required' => 'The :field field is required.',
            'email' => 'The :field must be a valid email address.',
            'min' => 'The :field must be at least :0 characters.',
            'max' => 'The :field may not be greater than :0 characters.',
            'confirmed' => 'The :field confirmation does not match.',
            'unique' => 'The :field has already been taken.',
            'numeric' => 'The :field must be a number.',
            'integer' => 'The :field must be an integer.',
            'url' => 'The :field must be a valid URL.',
            'alpha' => 'The :field may only contain letters.',
            'alpha_num' => 'The :field may only contain letters and numbers.',
            'alpha_dash' => 'The :field may only contain letters, numbers, dashes and underscores.',
            'in' => 'The selected :field is invalid.',
            'not_in' => 'The selected :field is invalid.',
            'regex' => 'The :field format is invalid.',
            'date' => 'The :field is not a valid date.',
            'before' => 'The :field must be a date before :0.',
            'after' => 'The :field must be a date after :0.',
            'file' => 'The :field must be a file.',
            'image' => 'The :field must be an image.',
            'mimes' => 'The :field must be a file of type: :0.',
            'size' => 'The :field must be :0 kilobytes.',
            'between' => 'The :field must be between :0 and :1.',
        ];

        $message = $messages[$rule] ?? "The :field is invalid.";

        // Replace placeholders
        $message = str_replace(':field', str_replace('_', ' ', $field), $message);
        foreach ($params as $i => $param) {
            $message = str_replace(':' . $i, $param, $message);
        }

        $this->errors[$field] = $message;
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get validated data
     */
    public function getValidated(): array
    {
        return $this->validated;
    }

    // Validation Rules

    private function validateRequired(string $field, mixed $value, array $params): bool
    {
        if (is_null($value)) {
            return false;
        }
        if (is_string($value) && trim($value) === '') {
            return false;
        }
        if (is_array($value) && count($value) === 0) {
            return false;
        }
        return true;
    }

    private function validateEmail(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateMin(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        $min = (int) ($params[0] ?? 0);
        return mb_strlen((string) $value) >= $min;
    }

    private function validateMax(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        $max = (int) ($params[0] ?? PHP_INT_MAX);
        return mb_strlen((string) $value) <= $max;
    }

    private function validateConfirmed(string $field, mixed $value, array $params): bool
    {
        $confirmField = $field . '_confirmation';
        return $value === ($this->data[$confirmField] ?? null);
    }

    private function validateUnique(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;

        $table = $params[0] ?? '';
        $column = $params[1] ?? $field;
        $exceptId = $params[2] ?? null;

        $db = app()->getDatabase();

        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
        $bindings = ['value' => $value];

        if ($exceptId) {
            $sql .= " AND id != :except_id";
            $bindings['except_id'] = $exceptId;
        }

        $count = (int) $db->fetchColumn($sql, $bindings);
        return $count === 0;
    }

    private function validateNumeric(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        return is_numeric($value);
    }

    private function validateInteger(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function validateUrl(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function validateAlpha(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        return (bool) preg_match('/^[\pL\pM]+$/u', $value);
    }

    private function validateAlphaNum(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        return (bool) preg_match('/^[\pL\pM\pN]+$/u', $value);
    }

    private function validateAlphaDash(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        return (bool) preg_match('/^[\pL\pM\pN_-]+$/u', $value);
    }

    private function validateIn(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        return in_array($value, $params);
    }

    private function validateNotIn(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        return !in_array($value, $params);
    }

    private function validateRegex(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        $pattern = $params[0] ?? '';
        return (bool) preg_match($pattern, $value);
    }

    private function validateDate(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        return strtotime($value) !== false;
    }

    private function validateBefore(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        $date = $params[0] ?? 'now';
        return strtotime($value) < strtotime($date);
    }

    private function validateAfter(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        $date = $params[0] ?? 'now';
        return strtotime($value) > strtotime($date);
    }

    private function validateBetween(string $field, mixed $value, array $params): bool
    {
        if (empty($value)) return true;
        $min = (int) ($params[0] ?? 0);
        $max = (int) ($params[1] ?? PHP_INT_MAX);
        $length = mb_strlen((string) $value);
        return $length >= $min && $length <= $max;
    }

    private function validateNullable(string $field, mixed $value, array $params): bool
    {
        return true; // Always passes, just marks field as nullable
    }
}
