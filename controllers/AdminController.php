<?php 

namespace Controllers;
use Model\Configuraciones;
use Model\AdminCita;
use MVC\Router;
use Model\Cita;
use Model\Servicio;
use Model\Usuario;
use Model\CierreCaja;

class AdminController {
    public static function index( Router $router ) {
      // En AdminController.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Solo iniciar la sesión si no está activa
}


        isAdmin();
        date_default_timezone_set('America/Argentina/Buenos_Aires'); // Ajusta según tu país
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $fechas = explode('-', $fecha);

        if( !checkdate( $fechas[1], $fechas[2], $fechas[0]) ) {
            header('Location: /404');
        }

        // Consultar la base de datos
        // Modificar la consulta en el backend para incluir el estado de la cita y ordenar las citas
        $consulta = "SELECT citas.id, citas.fecha, citas.hora, citas.observaciones, citas.duracionTotal, citas.finalizacion,  CONCAT( usuarios.nombre, ' ', usuarios.apellido) as cliente, ";
        $consulta .= " usuarios.email, usuarios.telefono, servicios.nombre as servicio, servicios.precio, servicios.duracion, servicios.categoria, citas.estado, usuarios.id AS usuarioId ";  
        $consulta .= " FROM citas ";
        $consulta .= " LEFT OUTER JOIN usuarios ";
        $consulta .= " ON citas.usuarioId=usuarios.id ";
        $consulta .= " LEFT OUTER JOIN citasServicios ";
        $consulta .= " ON citasServicios.citaId=citas.id ";
        $consulta .= " LEFT OUTER JOIN servicios ";
        $consulta .= " ON servicios.id=citasServicios.servicioId ";
        $consulta .= " ORDER BY 
            CASE 
                WHEN citas.estado = 0 THEN 1 
                WHEN citas.estado = 3 THEN 2 
                WHEN citas.estado = 2 THEN 3 
            END, citas.hora ASC"; 
        

        $citas = AdminCita::SQL($consulta);
        
        $router->render('admin/index', [
            'nombre' => $_SESSION['nombre'],
            'citas' => $citas, 
            'fecha' => $fecha
        ]);
    }
    
    public static function promociones(Router $router) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
    
        isAdmin(); // Verifica que sea un administrador
    
        // Consultar la base de datos para obtener las configuraciones
        $consulta = "SELECT * FROM configuraciones WHERE id = 1"; // Suponiendo que solo tienes una fila de configuración
        $configuraciones = Configuraciones::SQL($consulta);  // Ejecuta la consulta y obtiene los resultados
       // debuguear($configuraciones);
        // Enviar las configuraciones a la vista
        $router->render('admin/promociones', [
            'configuraciones' => $configuraciones
        ]);
    }


    public static function rendimientos(Router $router) {
        $cierres = CierreCaja::all(); // Obtener todos los cierres de caja

        $router->render('admin/rendimientos', [
            'cierres' => $cierres
        ]);
    }


    public static function clientes(Router $router) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
    
        isAdmin(); // Verifica que sea un administrador
    
        // Verificar si el parámetro 'tipo' está presente en la URL
        $tipo = $_GET['tipo'] ?? 'cliente';  // Si no se pasa, se muestra 'cliente' por defecto
    
        // Filtrar los clientes según el tipo
        if ($tipo == 'admin') {
            $clientes = Usuario::allAdmin();  // Usar un método para obtener solo administradores
        } elseif ($tipo == 'cliente') {
            $clientes = Usuario::allCliente();  // Usar un método para obtener solo clientes normales
        } else {
            // Si no se pasa tipo, redirigir a 'clientes?tipo=cliente' para evitar valores incorrectos
           // header('Location: /clientes?tipo=cliente');
            exit;
        }
    
        // Enviar los clientes a la vista
        $router->render('admin/clientes', [
            'clientes' => $clientes,
            'tipo' => $tipo  // Pasar el tipo a la vista para mantener el filtro
        ]);
    }
    
    
    
    
    
    


}
