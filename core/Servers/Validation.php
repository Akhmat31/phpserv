<?php

namespace Source;

use Source\Http\Request;

/**
 * Class Validation
 *
 * Request data validation system.
 * Inspired by CodeIgniter 4's Validation class.
 *
 * Usage:
 *   $validator = new Validation();
 *   $validator->validate($request->all(), [
 *       'email' => 'required|email',
 *       'name'  => 'required|min:2|max:50',
 *       'age'   => 'integer|between:18,120',
 *   ]);
 *   if ($validator->fails()) {
 *       $errors = $validator->errors();
 *   }
 */
class Validation
{
    private array $data = [];
    private array $rules = [];
    private array $errors = [];
    private array $customMessages = [];
    private static array $defaultMessages = [
        'required' => 'The {field} field is required.',
        'email' => 'The {field} field must be a valid email address.',
        'url' => 'The {field} field must be a valid URL.',
        'min' => 'The {field} field must be at least {param} characters.',
        'max' => 'The {field} field must not exceed {param} characters.',
        'min_value' => 'The {field} field must be at least {param}.',
        'max_value' => 'The {field} field must not exceed {param}.',
        'between' => 'The {field} field must be between {param}.',
        'integer' => 'The {field} field must be an integer.',
        'numeric' => 'The {field} field must be a number.',
        'string' => 'The {field} field must be a string.',
        'alpha' => 'The {field} field may only contain letters.',
        'alpha_numeric' => 'The {field} field may only contain letters and numbers.',
        'alpha_dash' => 'The {field} field may only contain letters, numbers, dashes, and underscores.',
        'uuid' => 'The {field} field must be a valid UUID.',
        'in' => 'The {field} field must be one of: {param}.',
        'not_in' => 'The {field} field must not be one of: {param}.',
        'regex' => 'The {field} field format is invalid.',
        'date' => 'The {field} field must be a valid date.',
        'boolean' => 'The {field} field must be true or false.',
        'confirmed' => 'The {field} field confirmation does not match.',
    ];

    /**
     * Validate data against a set of rules.
     *
     * @param array $data The input data to validate
     * @param array $rules Rules in ['field' => 'rule1|rule2'] format
     * @param array $messages Custom error messages per field.rule
     * @return bool True if validation passes, false if it fails
     */
    public function validate(array $data, array $rules, array $messages = []): bool
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->errors = [];
        $this->customMessages = $messages;

        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            $ruleList = $this->parseRules($ruleString);

            foreach ($ruleList as $rule) {
                $passed = $this->applyRule($rule, $field, $value, $data);

                if (!$passed) {
                    $this->addError($field, $rule);
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Validate a Request object using its all() method.
     */
    public function validateRequest(Request $request, array $rules, array $messages = []): bool
    {
        return $this->validate($request->all(), $rules, $messages);
    }

    /**
     * Check if validation has failed.
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if validation has passed.
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Get all validation errors.
     *
     * @return array<string, string> Field => error message
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get the first error for a specific field.
     */
    public function error(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Get the validated data (only fields that have rules).
     */
    public function validated(): array
    {
        $result = [];
        foreach (array_keys($this->rules) as $field) {
            if (array_key_exists($field, $this->data)) {
                $result[$field] = $this->data[$field];
            }
        }

        return $result;
    }

    /**
     * Parse a pipe-separated rule string into individual rules.
     *
     * @return array<int, array{name: string, param: ?string}>
     */
    private function parseRules(string $ruleString): array
    {
        $rules = [];
        $parts = explode('|', $ruleString);

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            if (str_contains($part, ':')) {
                [$name, $param] = explode(':', $part, 2);
                $rules[] = ['name' => trim($name), 'param' => trim($param)];
            } else {
                $rules[] = ['name' => $part, 'param' => null];
            }
        }

        return $rules;
    }

    /**
     * Apply a single rule to a field value.
     */
    private function applyRule(array $rule, string $field, mixed $value, array $data): bool
    {
        $name = $rule['name'];
        $param = $rule['param'];

        return match ($name) {
            'required' => $this->ruleRequired($value),
            'email' => $this->ruleEmail($value),
            'url' => $this->ruleUrl($value),
            'min' => $this->ruleMin($value, (int) $param),
            'max' => $this->ruleMax($value, (int) $param),
            'min_value' => $this->ruleMinValue($value, (int) $param),
            'max_value' => $this->ruleMaxValue($value, (int) $param),
            'between' => $this->ruleBetween($value, $param),
            'integer' => $this->ruleInteger($value),
            'numeric' => $this->ruleNumeric($value),
            'string' => $this->ruleString($value),
            'alpha' => $this->ruleAlpha($value),
            'alpha_numeric' => $this->ruleAlphaNumeric($value),
            'alpha_dash' => $this->ruleAlphaDash($value),
            'uuid' => $this->ruleUuid($value),
            'in' => $this->ruleIn($value, $param),
            'not_in' => $this->ruleNotIn($value, $param),
            'regex' => $this->ruleRegex($value, $param),
            'date' => $this->ruleDate($value),
            'boolean' => $this->ruleBoolean($value),
            'confirmed' => $this->ruleConfirmed($value, $field, $data),
            'nullable' => true,
            default => true,
        };
    }

    /**
     * Add an error message for a failed rule.
     */
    private function addError(string $field, array $rule): void
    {
        $ruleName = $rule['name'];
        if (isset($this->errors[$field])) {
            return;
        }

        $customKey = "{$field}.{$ruleName}";
        if (isset($this->customMessages[$customKey])) {
            $message = $this->customMessages[$customKey];
        } elseif (isset($this->customMessages[$ruleName])) {
            $message = $this->customMessages[$ruleName];
        } else {
            $message = self::$defaultMessages[$ruleName] ?? "The {$field} field is invalid.";
        }

        $message = str_replace('{field}', $field, $message);
        $message = str_replace('{param}', (string) ($rule['param'] ?? ''), $message);

        $this->errors[$field] = $message;
    }

    // --- Individual validation rules ---

    private function ruleRequired(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }
        if (is_string($value) && trim($value) === '') {
            return false;
        }
        if (is_array($value) && empty($value)) {
            return false;
        }

        return true;
    }

    private function ruleEmail(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function ruleUrl(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function ruleMin(mixed $value, int $min): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $length = is_string($value) ? strlen($value) : (is_array($value) ? count($value) : 0);

        return $length >= $min;
    }

    private function ruleMax(mixed $value, int $max): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $length = is_string($value) ? strlen($value) : (is_array($value) ? count($value) : 0);

        return $length <= $max;
    }

    private function ruleMinValue(mixed $value, int $min): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_numeric($value) && (float) $value >= $min;
    }

    private function ruleMaxValue(mixed $value, int $max): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_numeric($value) && (float) $value <= $max;
    }

    private function ruleBetween(mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if ($param === null) {
            return false;
        }

        [$min, $max] = array_map('trim', explode(',', $param));

        if (is_numeric($value)) {
            $num = (float) $value;

            return $num >= (float) $min && $num <= (float) $max;
        }

        $length = strlen((string) $value);

        return $length >= (int) $min && $length <= (int) $max;
    }

    private function ruleInteger(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function ruleNumeric(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_numeric($value);
    }

    private function ruleString(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        return is_string($value);
    }

    private function ruleAlpha(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_string($value) && preg_match('/^[a-zA-Z]+$/', $value) === 1;
    }

    private function ruleAlphaNumeric(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_string($value) && preg_match('/^[a-zA-Z0-9]+$/', $value) === 1;
    }

    private function ruleAlphaDash(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_string($value) && preg_match('/^[a-zA-Z0-9_-]+$/', $value) === 1;
    }

    private function ruleUuid(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_string($value) && preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value
        ) === 1;
    }

    private function ruleIn(mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if ($param === null) {
            return false;
        }

        $allowed = array_map('trim', explode(',', $param));

        return in_array((string) $value, $allowed, true);
    }

    private function ruleNotIn(mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if ($param === null) {
            return true;
        }

        $forbidden = array_map('trim', explode(',', $param));

        return !in_array((string) $value, $forbidden, true);
    }

    private function ruleRegex(mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if ($param === null) {
            return false;
        }

        return is_string($value) && preg_match('/' . $param . '/', $value) === 1;
    }

    private function ruleDate(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        return strtotime($value) !== false;
    }

    private function ruleBoolean(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_bool($value)) {
            return true;
        }

        return in_array($value, [0, 1, '0', '1', 'true', 'false', true, false], true);
    }

    private function ruleConfirmed(mixed $value, string $field, array $data): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $confirmationField = $field . '_confirmation';

        return isset($data[$confirmationField]) && $data[$confirmationField] === $value;
    }
}
