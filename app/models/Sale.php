<?php

namespace App\Models;

use App\Core\BaseModel;

/**
 * Model Venta
 * Gestiona las operaciones CRUD de ventas
 */
class Sale extends BaseModel {

    protected $table = 'ventas';

    /**
     * Obtener ventas por usuario
     */
    public function getByUser($userId, $limit = null, $offset = 0) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE usuario_id = :userId ORDER BY created_at DESC";

            if($limit) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':userId', $userId, \PDO::PARAM_INT);

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
     * Obtener ventas en rango de fechas
     */
    public function getByDateRange($startDate, $endDate) {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE created_at >= :startDate AND created_at <= :endDate
                    ORDER BY created_at DESC";

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
     * Obtener venta con detalles
     */
    public function getWithDetails($saleId) {
        try {
            // Obtener venta
            $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $saleId, \PDO::PARAM_INT);
            $stmt->execute();

            $sale = $stmt->fetch(\PDO::FETCH_ASSOC);

            if(!$sale) {
                return null;
            }

            // Obtener detalles
            $sqlDetails = "SELECT dv.*, p.nombre, p.imagen FROM detalle_ventas dv
                          JOIN productos p ON dv.producto_id = p.id
                          WHERE dv.venta_id = :saleId
                          ORDER BY dv.id";

            $stmtDetails = $this->db->prepare($sqlDetails);
            $stmtDetails->bindParam(':saleId', $saleId, \PDO::PARAM_INT);
            $stmtDetails->execute();

            $sale['detalles'] = $stmtDetails->fetchAll(\PDO::FETCH_ASSOC);

            return $sale;

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return null;
        }
    }

    /**
     * Generar número de folio único
     */
    public function generateFolio() {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $count = $result['total'] ?? 0;

            $year = date('Y');
            $month = date('m');

            return "V-{$year}{$month}-" . str_pad($count + 1, 5, '0', STR_PAD_LEFT);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return "V-" . time();
        }
    }

    /**
     * Obtener total vendido en fecha
     */
    public function getTotalByDate($date) {
        try {
            $sql = "SELECT SUM(total) as total FROM {$this->table}
                    WHERE DATE(created_at) = :date AND estado = 'completada'";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':date', $date);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return floatval($result['total'] ?? 0);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener cantidad de ventas en fecha
     */
    public function getCountByDate($date) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}
                    WHERE DATE(created_at) = :date AND estado = 'completada'";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':date', $date);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return intval($result['total'] ?? 0);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener ticket promedio en rango de fechas
     */
    public function getAverageTicket($startDate, $endDate) {
        try {
            $sql = "SELECT AVG(total) as promedio FROM {$this->table}
                    WHERE created_at >= :startDate AND created_at <= :endDate
                    AND estado = 'completada'";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':startDate', $startDate);
            $stmt->bindParam(':endDate', $endDate);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return floatval($result['promedio'] ?? 0);

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return 0;
        }
    }

    /**
     * Anular venta
     */
    public function cancel($saleId, $userId) {
        try {
            $sql = "UPDATE {$this->table} SET estado = 'anulada' WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $saleId, \PDO::PARAM_INT);

            return $stmt->execute();

        } catch(\PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }
}
