<?php

namespace Model;

class CierreCaja extends ActiveRecord {
    // Base de datos
    protected static $tabla = 'cierrescaja';
    protected static $columnasDB = ['id', 'fecha', 'total'];

    public $id;
    public $fecha;
    public $total;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->fecha = $args['fecha'] ?? date('Y-m-d');
        $this->total = $args['total'] ?? 0;
    }
}
