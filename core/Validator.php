<?php
/**
 * Input Validation Class
 * Provides comprehensive validation rules and error handling
 */

class Validator {
    private $data = [];
    private $rules = [];
    private $errors = [];
    private $customMessages = [];
    private $customRules = [];

    /**
     * Constructor
     */
    public function __construct($data = [], $rules = []) {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Set validation data
     */
    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * Set validation rules
     */
    public function setRules($rules) {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Add custom validation rule
     */
    public function addRule($name, callable $callback) {
        $this->customRules[$name] = $callback;
        return $this;
    }

    /**
     * Set custom error messages
     */
    public function setMessages($messages) {
        $this->customMessages = $messages;
        return $this;
    }

    /**
     * Validate data against rules
     */
    public function validate() {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleString) {
            $rules = $this->parseRules($ruleString);
            $value = $this->getValue($field);

            foreach ($rules as $rule => $params) {
                if (!$this->validateRule($field, $rule, $value, $params)) {
                    break; // Stop validating this field if one rule fails
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Parse rule string into array
     */
    private function parseRules($ruleString) {
        $rules = [];
        $ruleArray = explode('|', $ruleString);

        foreach ($ruleArray as $rule) {
            $parts = explode(':', $rule, 2);
            $ruleName = $parts[0];
            $params = isset($parts[1]) ? explode(',', $parts[1]) : [];

            $rules[$ruleName] = $params;
        }

        return $rules;
    }

    /**
     * Get value from data array
     */
    private function getValue($field) {
        return $this->data[$field] ?? null;
    }

    /**
     * Validate single rule
     */
    private function validateRule($field, $rule, $value, $params = []) {
        $method = 'validate' . ucfirst($rule);

        if (method_exists($this, $method)) {
            $result = $this->$method($value, $params);
        } elseif (isset($this->customRules[$rule])) {
            $result = call_user_func($this->customRules[$rule], $value, $params, $this->data);
        } else {
            throw new Exception("Validation rule '{$rule}' does not exist");
        }

        if (!$result) {
            $this->addError($field, $rule, $params);
            return false;
        }

        return true;
    }

    /**
     * Add validation error
     */
    private function addError($field, $rule, $params = []) {
        $message = $this->getMessage($field, $rule, $params);
        $this->errors[$field][] = $message;
    }

    /**
     * Get error message
     */
    private function getMessage($field, $rule, $params = []) {
        // Check custom messages first
        $key = $field . '.' . $rule;
        if (isset($this->customMessages[$key])) {
            return $this->customMessages[$key];
        }

        // Default messages
        $messages = [
            'required' => 'The :field field is required',
            'email' => 'The :field must be a valid email address',
            'min' => 'The :field must be at least :param characters',
            'max' => 'The :field may not be greater than :param characters',
            'numeric' => 'The :field must be a number',
            'integer' => 'The :field must be an integer',
            'alpha' => 'The :field may only contain letters',
            'alpha_numeric' => 'The :field may only contain letters and numbers',
            'url' => 'The :field must be a valid URL',
            'ip' => 'The :field must be a valid IP address',
            'date' => 'The :field is not a valid date',
            'before' => 'The :field must be a date before :param',
            'after' => 'The :field must be a date after :param',
            'in' => 'The selected :field is invalid',
            'not_in' => 'The selected :field is invalid',
            'regex' => 'The :field format is invalid',
            'unique' => 'The :field has already been taken',
            'exists' => 'The selected :field is invalid',
            'confirmed' => 'The :field confirmation does not match',
            'different' => 'The :field and :param must be different',
            'same' => 'The :field and :param must match',
        ];

        $message = $messages[$rule] ?? "The :field field is invalid";

        // Replace placeholders
        $message = str_replace(':field', $field, $message);
        if (!empty($params)) {
            $message = str_replace(':param', $params[0], $message);
        }

        return $message;
    }

    /**
     * Validation rules
     */

    private function validateRequired($value) {
        return !is_null($value) && $value !== '' && (!is_array($value) || !empty($value));
    }

    private function validateEmail($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateMin($value, $params) {
        $min = $params[0] ?? 0;
        if (is_string($value)) {
            return strlen($value) >= $min;
        } elseif (is_numeric($value)) {
            return $value >= $min;
        } elseif (is_array($value)) {
            return count($value) >= $min;
        }
        return false;
    }

    private function validateMax($value, $params) {
        $max = $params[0] ?? 0;
        if (is_string($value)) {
            return strlen($value) <= $max;
        } elseif (is_numeric($value)) {
            return $value <= $max;
        } elseif (is_array($value)) {
            return count($value) <= $max;
        }
        return false;
    }

    private function validateNumeric($value) {
        return is_numeric($value);
    }

    private function validateInteger($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function validateAlpha($value) {
        return ctype_alpha($value);
    }

    private function validateAlphaNumeric($value) {
        return ctype_alnum($value);
    }

    private function validateUrl($value) {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function validateIp($value) {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    private function validateDate($value) {
        return strtotime($value) !== false;
    }

    private function validateBefore($value, $params) {
        $date = $params[0] ?? null;
        if (!$date) return false;
        return strtotime($value) < strtotime($date);
    }

    private function validateAfter($value, $params) {
        $date = $params[0] ?? null;
        if (!$date) return false;
        return strtotime($value) > strtotime($date);
    }

    private function validateIn($value, $params) {
        return in_array($value, $params);
    }

    private function validateNotIn($value, $params) {
        return !in_array($value, $params);
    }

    private function validateRegex($value, $params) {
        $pattern = $params[0] ?? '';
        return preg_match($pattern, $value);
    }

    private function validateUnique($value, $params) {
        // This would need database access - implement in extending class
        return true; // Placeholder
    }

    private function validateExists($value, $params) {
        // This would need database access - implement in extending class
        return true; // Placeholder
    }

    private function validateConfirmed($value, $params) {
        $confirmationField = $params[0] ?? $this->getValue($this->data['_field'] . '_confirmation');
        return $value === $confirmationField;
    }

    private function validateDifferent($value, $params) {
        $otherField = $params[0] ?? null;
        if (!$otherField) return true;
        return $value !== $this->getValue($otherField);
    }

    private function validateSame($value, $params) {
        $otherField = $params[0] ?? null;
        if (!$otherField) return true;
        return $value === $this->getValue($otherField);
    }

    /**
     * Get validation errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get errors for specific field
     */
    public function getErrorsFor($field) {
        return $this->errors[$field] ?? [];
    }

    /**
     * Get first error for specific field
     */
    public function getFirstError($field) {
        $errors = $this->getErrorsFor($field);
        return !empty($errors) ? $errors[0] : null;
    }

    /**
     * Check if field has errors
     */
    public function hasErrors($field = null) {
        if ($field === null) {
            return !empty($this->errors);
        }
        return isset($this->errors[$field]);
    }

    /**
     * Get validated data (only fields that passed validation)
     */
    public function getValidatedData() {
        $validated = [];
        foreach ($this->rules as $field => $rules) {
            if (!isset($this->errors[$field])) {
                $validated[$field] = $this->getValue($field);
            }
        }
        return $validated;
    }

    /**
     * Sanitize input data
     */
    public function sanitize($data = null) {
        $data = $data ?? $this->data;
        $security = Security::getInstance();

        if (is_array($data)) {
            return array_map([$security, 'sanitize'], $data);
        }

        return $security->sanitize($data);
    }

    /**
     * Create validator instance
     */
    public static function make($data = [], $rules = []) {
        return new self($data, $rules);
    }
}