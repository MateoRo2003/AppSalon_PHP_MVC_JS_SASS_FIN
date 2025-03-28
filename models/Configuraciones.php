<?php

namespace Model;

class Configuraciones extends ActiveRecord {
    protected static $tabla = 'configuraciones';
    protected static $columnasDB = ['id', 'recordatorio_dia_antes', 'avisar_nuevo_servicio', 'fecha_creacion', 'fecha_actualizacion'];

    public $id;
    public $recordatorio_dia_antes;
    public $avisar_nuevo_servicio;
    public $fecha_creacion;
    public $fecha_actualizacion;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->recordatorio_dia_antes = $args['recordatorio_dia_antes'] ?? 0;  // 0 como valor predeterminado
        $this->avisar_nuevo_servicio = $args['avisar_nuevo_servicio'] ?? 0;      // 0 como valor predeterminado
        $this->fecha_creacion = $args['fecha_creacion'] ?? date('Y-m-d H:i:s');
        $this->fecha_actualizacion = $args['fecha_actualizacion'] ?? date('Y-m-d H:i:s');
    }

    public static function obtenerConfiguracion() {
        // Suponiendo que la configuración es solo una fila con ID = 1
        return self::find(1);
    }


}

