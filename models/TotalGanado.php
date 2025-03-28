<?php

namespace Model;

class TotalGanado extends ActiveRecord {
    protected static $tabla = 'citas'; 

    public $fecha;
    public $total_ganado;

    public function __construct($args = []) {
        $this->fecha = $args['fecha'] ?? null;
        $this->total_ganado = $args['total_ganado'] ?? 0;
    }

    public static function obtenerTotales() {
        $consulta = "SELECT 
                        c.fecha, 
                        SUM(s.precio) AS total_ganado
                     FROM citas c
                     INNER JOIN citasServicios ci ON c.id = ci.citaId
                     INNER JOIN servicios s ON ci.servicioid = s.id
                     WHERE c.estado = 3
                     GROUP BY c.fecha
                     ORDER BY c.fecha DESC";

        return self::SQL($consulta);
    }
}
