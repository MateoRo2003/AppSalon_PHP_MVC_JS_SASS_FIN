<?php

namespace Model;

class Cita extends ActiveRecord {
    // Base de datos
    protected static $tabla = 'citas';
    protected static $columnasDB = ['id', 'fecha', 'hora', 'usuarioId', 'estado', 'duracionTotal', 'observaciones', 'finalizacion']; // Añadimos 'duracionTotal'

    public $id;
    public $fecha;
    public $hora;
    public $usuarioId;
    public $estado;  // Nueva propiedad para 'estado'
    public $duracionTotal; // Nueva propiedad para 'duracionTotal'
    public $observaciones; // Nueva propiedad para 'obersevaciones'
    public $finalizacion;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->fecha = $args['fecha'] ?? '';
        $this->hora = $args['hora'] ?? '';
        $this->usuarioId = $args['usuarioId'] ?? '';
        $this->estado = $args['estado'] ?? 0;  // Por defecto, el estado es 0
        $this->duracionTotal = $args['duracionTotal'] ?? null;  // Añadimos el valor para 'duracionTotal'
        $this->observaciones = $args['observaciones'] ?? '';  // Añadimos el valor para 'obersevaciones'
        $this->finalizacion = $args['finalizacion'] ?? null;
    }
    
    public static function contarCitas() {
        $consulta = "SELECT COUNT(*) AS totalCitas FROM citas";
        $resultado = self::consultarSQL($consulta);
        return $resultado ? $resultado[0]->totalCitas : 0;
    }
    
    public function validarCita() {
        self::$alertas = [];
    
        if (!$this->fecha) {
            self::$alertas['error'][] = 'La fecha es obligatoria.';
        }
        if (!$this->hora) {
            self::$alertas['error'][] = 'La hora es obligatoria.';
        }
    
        return self::$alertas;
    }
 


}
