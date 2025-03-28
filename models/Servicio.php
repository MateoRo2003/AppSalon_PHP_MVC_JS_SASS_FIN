<?php 

namespace Model;

class Servicio extends ActiveRecord {
    // Base de datos
    protected static $tabla = 'servicios';
    protected static $columnasDB = ['id', 'nombre', 'precio', 'duracion', 'descripcion', 'categoria'];  // Agregado 'duracion'

    public $id;
    public $nombre;
    public $precio;
    public $duracion;  // Agregado 'duracion'
    public $descripcion;
    public $categoria;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->precio = $args['precio'] ?? '';
        $this->duracion = $args['duracion'] ?? 0;  // Establecer duracion por defecto si no se proporciona
        $this->descripcion = $args['descripcion'] ?? '';
        $this->categoria = $args['categoria'] ?? '';
    }
    public static function contarServicios() {
        $consulta = "SELECT COUNT(*) AS totalServicios FROM servicios";
        $resultado = self::consultarSQL($consulta);
        
        // Verificamos si el resultado existe y si tiene la propiedad 'totalServicios'
        return $resultado ? $resultado[0]->totalServicios : 0;
    }
    

    public function validar() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre del Servicio es Obligatorio';
        }
        if(!$this->precio) {
            self::$alertas['error'][] = 'El Precio del Servicio es Obligatorio';
        }
        if(!is_numeric($this->precio)) {
            self::$alertas['error'][] = 'El precio no es válido';
        }

        return self::$alertas;
    }

    // // Método para obtener el nombre del servicio por ID
    // public static function obtenerNombrePorId($id) {
    //     $query = "SELECT nombre FROM " . static::$tabla . " WHERE id = '$id' LIMIT 1";
    //     $resultado = self::$db->query($query);

    //     if ($resultado) {
    //         $registro = $resultado->fetch_assoc();
    //         return $registro['nombre'] ?? null; // Retorna el nombre o null si no se encuentra
    //     }

    //     return null; // Retorna null si la consulta falla
    // }


    public static function obtenerPrecioPorId($idServicio) {
        // Consulta para obtener el precio del servicio
        $consulta = "SELECT precio FROM servicios WHERE id = {$idServicio} LIMIT 1";

        $resultado = self::consultarSQL($consulta);
    
        return $resultado ? $resultado[0]->precio : 0; // Devuelve el precio, o 0 si no se encuentra
    }

   // Función que obtiene los servicios disponibles para una cita específica
//    public static function serviciosDisponibles($citaId)
//    {
//        // Obtener la cita especificada
//        $cita = self::find($citaId);  // Buscamos la cita por ID
//        if (!$cita) {
//            return []; // Si no se encuentra la cita, retornamos un array vacío
//        }
   
//        // Obtenemos la fecha y hora de la cita
//        $fechaCita = $cita->fecha;
//        $horaCita = $cita->hora;
//        $duracionCita = $cita->duracionTotal;  // Duración total de la cita
   
//        // Consulta SQL con los parámetros directamente en la consulta
//        $consulta = "SELECT s.id, s.nombre, s.precio, s.duracion, s.categoria
//                     FROM servicios s
//                     LEFT JOIN citasServicios cs ON cs.servicioId = s.id
//                     LEFT JOIN citas c ON cs.citaId = c.id
//                     WHERE c.fecha = '{$fechaCita}' AND c.hora > '{$horaCita}' 
//                     AND (c.estado = 0 OR c.estado = 3)";  // Solo citas activas
   
//        // Ejecutar la consulta para obtener los datos
//        $servicios = self::SQL($consulta);
   
//        return $servicios;
//    }
   


}