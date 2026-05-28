<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Clase base para todos los modelos
 * Proporciona métodos CRUD comunes
 */
class BaseModel {

    protected $db;
    protected $table;

    public function __construct(PDO $db = null) {
        if($db) {
            $this->db = $db;
        }
    }

    /**
     * Obtener todos los registros
     */
    public function getAll($limit = null, $offset = 0) {
        try {
            $query = "SELECT * FROM {$this->table} ORDER BY id DESC";

            if($limit) {
                $query .= " LIMIT :limit OFFSET :offset";
            }

            $stmt = $this->db->prepare($query);

            if($limit) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            $this->logError($e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un registro por ID
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            $this->logError($e->getMessage());
            return null;
        }
    }

    /**
     * Contar registros
     */
    public function count() {
        try {
            $query = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;

        } catch(PDOException $e) {
            $this->logError($e->getMessage());
            return 0;
        }
    }

    /**
     * Crear un nuevo registro
     */
    public function create($data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));

            $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($query);

            foreach($data as $key => $value) {
                $stmt->bindParam(":{$key}", $data[$key]);
            }

            $stmt->execute();
            return $this->db->lastInsertId();

        } catch(PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar un registro
     */
    public function update($id, $data) {
        try {
            $setClause = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));

            $query = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            foreach($data as $key => $value) {
                $stmt->bindParam(":{$key}", $data[$key]);
            }

            return $stmt->execute();

        } catch(PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un registro
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();

        } catch(PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Registrar errores en log
     */
    protected function logError($message) {
        $logFile = dirname(__DIR__) . '/../../storage/logs/database.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
    }
}
