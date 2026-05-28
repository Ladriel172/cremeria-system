<?php

namespace App\Models;

use App\Core\BaseModel;

/**
 * Model Detalle Venta
 * Gestiona los detalles de cada venta
 */
class SaleDetail extends BaseModel {

    protected $table = 'detalle_ventas';

    /**
     * Obtener detalles de una venta
     */
    public function getBySale($saleId) {
        try {
            $sql = "SELECT dv.*, p.nombre, p.codigo_barras, p.imagen
                    FROM {$this->table} dv
                    JOIN productos p ON dv.producto_id = p.id
                    WHERE dv.venta_id = :saleId
                    ORDER BY dv.id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':saleId', $saleId, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return [];
        }
    }

    /**
     * Agregar detalle a venta
     */
    public function add($saleId, $productId, $cantidad, $precioUnitario) {
        try {
            $subtotal = $cantidad * $precioUnitario;

            $sql = "INSERT INTO {$this->table} (venta_id, producto_id, cantidad, precio_unitario, subtotal)
                    VALUES (:saleId, :productId, :cantidad, :precioUnitario, :subtotal)";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':saleId', $saleId, \PDO::PARAM_INT);
            $stmt->bindParam(':productId', $productId, \PDO::PARAM_INT);
            $stmt->bindParam(':cantidad', $cantidad, \PDO::PARAM_INT);
            $stmt->bindParam(':precioUnitario', $precioUnitario);
            $stmt->bindParam(':subtotal', $subtotal);

            return $stmt->execute();

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Obtener productos más vendidos
     */
    public function getTopProducts($days = 7, $limit = 10) {
        try {
            $sql = "SELECT p.id, p.nombre, p.imagen, SUM(dv.cantidad) as total_vendido
                    FROM {$this->table} dv
                    JOIN productos p ON dv.producto_id = p.id
                    JOIN ventas v ON dv.venta_id = v.id
                    WHERE v.created_at >= datetime('now', '-' || :days || ' days')
                    GROUP BY dv.producto_id
                    ORDER BY total_vendido DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':days', $days, \PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return [];
        }
    }

    /**
     * Obtener ingresos por producto en rango de fechas
     */
    public function getRevenueByProduct($startDate, $endDate) {
        try {
            $sql = "SELECT p.id, p.nombre, SUM(dv.subtotal) as ingreso
                    FROM {$this->table} dv
                    JOIN productos p ON dv.producto_id = p.id
                    JOIN ventas v ON dv.venta_id = v.id
                    WHERE v.created_at >= :startDate AND v.created_at <= :endDate
                    AND v.estado = 'completada'
                    GROUP BY dv.producto_id
                    ORDER BY ingreso DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':startDate', $startDate);
            $stmt->bindParam(':endDate', $endDate);

            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return [];
        }
    }

    /**
     * Obtener total de cantidad vendida por producto
     */
    public function getTotalQuantityByProduct($startDate, $endDate) {
        try {
            $sql = "SELECT p.id, p.nombre, SUM(dv.cantidad) as cantidad_vendida
                    FROM {$this->table} dv
                    JOIN productos p ON dv.producto_id = p.id
                    JOIN ventas v ON dv.venta_id = v.id
                    WHERE v.created_at >= :startDate AND v.created_at <= :endDate
                    AND v.estado = 'completada'
                    GROUP BY dv.producto_id
                    ORDER BY cantidad_vendida DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':startDate', $startDate);
            $stmt->bindParam(':endDate', $endDate);

            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return [];
        }
    }
}
