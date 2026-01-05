<?php

namespace App\Core;

/**
 * Base Controller Class
 * All controllers should extend this class
 */
abstract class Controller
{
    protected Request $request;
    protected Response $response;

    /**
     * Validate request data
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $ruleArray = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;

            foreach ($ruleArray as $rule) {
                $ruleName = $rule;
                $ruleValue = null;

                // Parse rule with parameter (e.g., min:3)
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $ruleValue] = explode(':', $rule, 2);
                }

                // Required validation
                if ($ruleName === 'required' && (!isset($data[$field]) || $data[$field] === '')) {
                    $errors[$field][] = "The $field field is required";
                    continue;
                }

                // Skip other validations if field is not present
                if (!isset($data[$field])) {
                    continue;
                }

                $value = $data[$field];

                // Email validation
                if ($ruleName === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "The $field must be a valid email address";
                }

                // Min length validation
                if ($ruleName === 'min' && strlen($value) < (int)$ruleValue) {
                    $errors[$field][] = "The $field must be at least $ruleValue characters";
                }

                // Max length validation
                if ($ruleName === 'max' && strlen($value) > (int)$ruleValue) {
                    $errors[$field][] = "The $field must not exceed $ruleValue characters";
                }

                // Numeric validation
                if ($ruleName === 'numeric' && !is_numeric($value)) {
                    $errors[$field][] = "The $field must be a number";
                }

                // String validation
                if ($ruleName === 'string' && !is_string($value)) {
                    $errors[$field][] = "The $field must be a string";
                }

                // Confirmed validation (e.g., password confirmation)
                if ($ruleName === 'confirmed') {
                    $confirmField = $field . '_confirmation';
                    if (!isset($data[$confirmField]) || $data[$confirmField] !== $value) {
                        $errors[$field][] = "The $field confirmation does not match";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Get authenticated user from request
     */
    protected function user(Request $request): ?array
    {
        return $request->user ?? null;
    }
}
