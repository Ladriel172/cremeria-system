<?php

namespace App\Core;

/**
 * Clase para validaciones centralizadas
 * Proporciona métodos para validar diferentes tipos de datos
 */
class Validator {

    protected $data = [];
    protected $errors = [];
    protected $rules = [];

    public function __construct($data = []) {
        $this->data = $data;
    }

    /**
     * Validar datos contra reglas
     *
     * Ejemplo:
     * $v = new Validator($data);
     * $v->validate([
     *     'email' => 'required|email',
     *     'nombre' => 'required|string|min:3|max:100',
     *     'precio' => 'required|numeric|min:0',
     *     'stock' => 'required|integer|min:0'
     * ]);
     */
    public function validate($rules) {
        $this->rules = $rules;
        $this->errors = [];

        foreach($rules as $field => $fieldRules) {
            $ruleArray = explode('|', $fieldRules);
            $value = $this->data[$field] ?? null;

            foreach($ruleArray as $rule) {
                $this->applyRule($field, $rule, $value);
            }
        }

        return empty($this->errors);
    }

    /**
     * Aplicar una regla individual
     */
    protected function applyRule($field, $rule, $value) {
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $param = $parts[1] ?? null;

        switch($ruleName) {
            case 'required':
                if(empty($value) && $value !== '0') {
                    $this->addError($field, "{$field} es requerido");
                }
                break;

            case 'email':
                if(!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "{$field} debe ser un email válido");
                }
                break;

            case 'string':
                if(!empty($value) && !is_string($value)) {
                    $this->addError($field, "{$field} debe ser texto");
                }
                break;

            case 'numeric':
                if(!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "{$field} debe ser un número");
                }
                break;

            case 'integer':
                if(!empty($value) && !is_int($value) && !ctype_digit((string)$value)) {
                    $this->addError($field, "{$field} debe ser un número entero");
                }
                break;

            case 'min':
                if(!empty($value) && strlen($value) < $param) {
                    $this->addError($field, "{$field} debe tener mínimo {$param} caracteres");
                }
                break;

            case 'max':
                if(!empty($value) && strlen($value) > $param) {
                    $this->addError($field, "{$field} debe tener máximo {$param} caracteres");
                }
                break;

            case 'unique':
                // Parámetro: tabla.columna
                [$tabla, $columna] = explode('.', $param);
                if(!empty($value) && $this->existsInDatabase($tabla, $columna, $value)) {
                    $this->addError($field, "{$field} ya existe en el sistema");
                }
                break;

            case 'date':
                if(!empty($value) && !strtotime($value)) {
                    $this->addError($field, "{$field} debe ser una fecha válida");
                }
                break;
        }
    }

    /**
     * Verificar si existe un valor en la base de datos
     */
    protected function existsInDatabase($table, $column, $value) {
        global $db;

        try {
            $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = :value LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':value', $value);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['count'] > 0;

        } catch(\PDOException $e) {
            return false;
        }
    }

    /**
     * Agregar error
     */
    protected function addError($field, $message) {
        if(!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Obtener errores
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Obtener error de campo específico
     */
    public function error($field) {
        return $this->errors[$field] ?? null;
    }

    /**
     * Verificar si hay errores
     */
    public function fails() {
        return !empty($this->errors);
    }

    /**
     * Obtener datos validados
     */
    public function validated() {
        $validated = [];

        foreach(array_keys($this->rules) as $field) {
            if(isset($this->data[$field])) {
                $validated[$field] = $this->data[$field];
            }
        }

        return $validated;
    }

    /**
     * Métodos de validación individuales (opcional, para uso flexible)
     */
    public static function email($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public static function integer($value) {
        return is_int($value) || ctype_digit((string)$value);
    }

    public static function numeric($value) {
        return is_numeric($value);
    }

    public static function min($value, $min) {
        return strlen($value) >= $min;
    }

    public static function max($value, $max) {
        return strlen($value) <= $max;
    }
}
