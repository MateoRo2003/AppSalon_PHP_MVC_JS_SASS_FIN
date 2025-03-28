<?php

namespace Controllers;

use Model\Servicio;
use MVC\Router;
use Model\Configuraciones;
use Model\Usuario;
use Classes\Email;

class ServicioController {
    public static function index(Router $router) {
        // En AdminController.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Solo iniciar la sesión si no está activa
}


        isAdmin();

        $servicios = Servicio::all();

        $router->render('servicios/index', [
            'nombre' => $_SESSION['nombre'],
            'servicios' => $servicios
        ]);
    }




    public static function crear(Router $router) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
    
        isAdmin();
        $servicio = new Servicio;
        $alertas = [];
    
        // Consultar la configuración
        $consulta = "SELECT * FROM configuraciones WHERE id = 1";
        $configuraciones = Configuraciones::SQL($consulta); 
        
        // Validamos que la configuración para enviar correos esté activada
        $enviarCorreos = $configuraciones[0]->avisar_nuevo_servicio == 1; // Asumimos que solo hay una configuración
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $servicio->sincronizar($_POST);
            
            $alertas = $servicio->validar();
    
            if (empty($alertas)) {
                // Guardar el nuevo servicio
                $servicio->guardar();
    
                // Obtener el último servicio guardado (asumiendo que el último servicio es el de mayor ID)
                $ultimoServicio = Servicio::SQL("SELECT * FROM servicios ORDER BY id DESC LIMIT 1")[0];
    
                // Si la configuración permite enviar correos, enviamos un correo a los usuarios suscritos
                if ($enviarCorreos) {
                    // Consultar todos los usuarios con suscripción activa
                    $consultaUsuarios = "SELECT * FROM usuarios WHERE suscripcion = 1"; // Ajusta según la columna real de suscripción
                    $usuarios = Usuario::SQL($consultaUsuarios);
    
                    // Enviar el correo a cada usuario con los detalles del nuevo servicio
                    foreach ($usuarios as $usuario) {
                        $email = new Email($usuario->email, $usuario->nombre);
                        $email->enviarAvisoNuevoServicio(
                            $usuario->id,
                            $usuario->nombre,
                            $usuario->apellido,
                            $ultimoServicio->nombre,  // Nombre del servicio
                            $ultimoServicio->descripcion,  // Descripción del servicio
                            $ultimoServicio->precio  // Precio del servicio
                        );
                    }
                }
    
                // Redirigir a la página de servicios después de guardar
                header('Location: /servicios');
            }
        }
    
        $router->render('servicios/crear', [
            'nombre' => $_SESSION['nombre'],
            'servicio' => $servicio,
            'alertas' => $alertas
        ]);
    }
    

    public static function actualizar(Router $router) {
        // En AdminController.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Solo iniciar la sesión si no está activa
}

        isAdmin();

        if(!is_numeric($_GET['id'])) return;

        $servicio = Servicio::find($_GET['id']);
        $alertas = [];

        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $servicio->sincronizar($_POST);

            $alertas = $servicio->validar();

            if(empty($alertas)) {
                $servicio->guardar();
                header('Location: /servicios');
            }
        }

        $router->render('servicios/actualizar', [
            'nombre' => $_SESSION['nombre'],
            'servicio' => $servicio,
            'alertas' => $alertas
        ]);
    }

    public static function eliminar() {
       // En AdminController.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Solo iniciar la sesión si no está activa
}

        isAdmin();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $servicio = Servicio::find($id);
            $servicio->eliminar();
            header('Location: /servicios');
        }
    }
}