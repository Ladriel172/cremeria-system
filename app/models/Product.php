<?php

namespace App\Models;

use App\Core\BaseModel;

/**
 * Model Producto
 * Gestiona las operaciones CRUD de productos
 */
class Product extends BaseModel {

    protected $table = 'productos';

    /**
     * Buscar productos por nombre o código de barras
     */
    public function search($query, $limit = 10, $offset = 0) {
        try {
            $searchTerm = "%{$query}%";

            $sql = "SELECT * FROM {$this->table}
                    WHERE nombre LIKE :query OR codigo_barras LIKE :query
                    ORDER BY nombre ASC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':query', $searchTerm);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return [];
        }
    }

    /**
     * Obtener producto por código de barras
     */
    public function getByBarcode($barcode) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE codigo_barras = :barcode LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':barcode', $barcode);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return null;
        }
    }

    /**
     * Obtener productos por categoría
     */
    public function getByCategory($category, $limit = null, $offset = 0) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE categoria = :category AND activo = 1 ORDER BY nombre ASC";

            if($limit) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':category', $category);

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
     * Obtener solo productos activos
     */
    public function getActive($limit = null, $offset = 0) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE activo = 1 ORDER BY nombre ASC";

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
     * Reducir stock
     */
    public function reduceStock($productId, $quantity) {
        try {
            $sql = "UPDATE {$this->table} SET stock = stock - :quantity WHERE id = :id AND stock >= :quantity";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':quantity', $quantity, \PDO::PARAM_INT);
            $stmt->bindParam(':id', $productId, \PDO::PARAM_INT);

            return $stmt->execute();

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Aumentar stock
     */
    public function increaseStock($productId, $quantity) {
        try {
            $sql = "UPDATE {$this->table} SET stock = stock + :quantity WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':quantity', $quantity, \PDO::PARAM_INT);
            $stmt->bindParam(':id', $productId, \PDO::PARAM_INT);

            return $stmt->execute();

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si hay stock suficiente
     */
    public function hasStock($productId, $quantity) {
        try {
            $sql = "SELECT stock FROM {$this->table} WHERE id = :id LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $productId, \PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result && $result['stock'] >= $quantity;

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Obtener productos con stock bajo
     */
    public function getLowStock($threshold = 10) {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE stock <= :threshold AND activo = 1
                    ORDER BY stock ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':threshold', $threshold, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return [];
        }
    }

    /**
     * Obtener categorías únicas
     */
    public function getCategories() {
        try {
            $sql = "SELECT DISTINCT categoria FROM {$this->table} WHERE activo = 1 AND categoria IS NOT NULL ORDER BY categoria ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return [];
        }
    }
}
