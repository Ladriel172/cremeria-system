<?php

namespace App\Models;

use App\Core\BaseModel;

/**
 * Model Usuario
 * Gestiona las operaciones CRUD de usuarios
 */
class User extends BaseModel {

    protected $table = 'usuarios';

    /**
     * Obtener usuario por email
     */
    public function getByEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE correo = :email LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return null;
        }
    }

    /**
     * Obtener usuario por email y verificar contraseña
     */
    public function authenticate($email, $password) {
        try {
            $user = $this->getByEmail($email);

            if(!$user) {
                return null;
            }

            // Verificar contraseña
            if(!password_verify($password, $user['password'])) {
                return null;
            }

            // Verificar que usuario esté activo
            if($user['estado'] !== 'activo') {
                return null;
            }

            return $user;

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return null;
        }
    }

    /**
     * Obtener solo usuarios activos
     */
    public function getActive($limit = null, $offset = 0) {
        try {
            $sql = "SELECT id, nombre, correo, rol, estado, created_at FROM {$this->table}
                    WHERE estado = 'activo'
                    ORDER BY nombre ASC";

            if($limit) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }

            $stmt = $this->db->prepare($sql);

            if($limit) {
                $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return [];
        }
    }

    /**
     * Obtener usuarios por rol
     */
    public function getByRole($role, $limit = null, $offset = 0) {
        try {
            $sql = "SELECT id, nombre, correo, rol, estado, created_at FROM {$this->table}
                    WHERE rol = :role
                    ORDER BY nombre ASC";

            if($limit) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':role', $role);

            if($limit) {
                $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return [];
        }
    }

    /**
     * Cambiar estado de usuario
     */
    public function toggleStatus($userId) {
        try {
            $sql = "UPDATE {$this->table} SET estado = CASE WHEN estado='activo' THEN 'inactivo' ELSE 'activo' END WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);

            return $stmt->execute();

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword($userId, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);

            $sql = "UPDATE {$this->table} SET password = :password, updated_at = datetime('now','localtime') WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);

            return $stmt->execute();

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si email existe
     */
    public function emailExists($email, $excludeUserId = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE correo = :email";

            if($excludeUserId) {
                $sql .= " AND id != :id";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);

            if($excludeUserId) {
                $stmt->bindParam(':id', $excludeUserId, \PDO::PARAM_INT);
            }

            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result['count'] > 0;

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Obtener usuario sin mostrar contraseña
     */
    public function getByIdSafe($userId) {
        try {
            $sql = "SELECT id, nombre, correo, rol, estado, created_at, updated_at FROM {$this->table} WHERE id = :id LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return null;
        }
    }

    /**
     * Contar usuarios por rol
     */
    public function countByRole($role) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE rol = :role";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':role', $role);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return 0;
        }
    }
}
