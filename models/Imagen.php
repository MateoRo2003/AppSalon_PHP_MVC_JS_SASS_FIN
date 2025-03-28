<?php

namespace Model;

class Imagen extends ActiveRecord {
    protected static $tabla = 'imagenes';
    protected static $columnasDB = ['id', 'nombre', 'descripcion', 'imagen'];

    public $id;
    public $nombre;
    public $descripcion;
    public $imagen; // Ahora almacenará el contenido binario
   

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
        $this->imagen = $args['imagen'] ?? null; // Almacenar binario
    }


}
