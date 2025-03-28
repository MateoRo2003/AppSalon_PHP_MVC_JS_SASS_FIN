<?php

namespace Model;

class AdminCita extends ActiveRecord {
    protected static $tabla = 'citasServicios';
    protected static $columnasDB = ['id', 'hora', 'cliente', 'email', 'telefono', 'servicio', 'precio', 'estado', 'usuarioId', 'fecha', 'observaciones', 'finalizacion', 'duracionTotal']; 

    public $id;
    public $hora;
    public $cliente;
    public $email;
    public $telefono;
    public $servicio;
    public $precio;
    public $estado;
    public $usuarioId;
    public $fecha; // Agregamos la propiedad fecha
    public $observaciones;
    public $finalizacion;
    public $duracionTotal;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->hora = $args['hora'] ?? '';
        $this->cliente = $args['cliente'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->servicio = $args['servicio'] ?? '';
        $this->precio = $args['precio'] ?? '';
        $this->estado = $args['estado'] ?? null;
        $this->usuarioId = $args['usuarioId'] ?? null;
        $this->fecha = $args['fecha'] ?? null; // Asignamos la fecha correctamente
        $this->observaciones = $args['observaciones'] ?? '';
        $this->finalizacion = $args['finalizacion'] ?? '';
        $this->duracionTotal = $args['duracionTotal'] ?? '';
    }




    
}
