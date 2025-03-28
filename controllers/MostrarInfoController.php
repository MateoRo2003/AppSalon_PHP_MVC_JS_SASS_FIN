<?php

namespace Controllers;

use Classes\Email;
use Model\MostrarInfo;
use MVC\Router;
use Model\Imagen;

class MostrarInfoController {
    public static function index(Router $router) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        

        // Depuración: Verificar el valor de $_SESSION['admin']
        if (isset($_SESSION['admin'])) {
            $_SESSION['admin'] = (int)$_SESSION['admin']; // Asegurar que sea un entero
        }

        // Verificar si la sesión está activa
        if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
            // Verificar si el usuario es administrador (comparación estricta)
            if (isset($_SESSION['admin']) && $_SESSION['admin'] === 1) {
                // Redirigir a /admin si el usuario es admin
                header('Location: /admin');
                exit;
            } else {
                // Redirigir a /cita si no es admin
                header('Location: /cita');
                exit;
            }
        }
        // Obtener las imágenes de la base de datos
        $imagenes = Imagen::all(); // Este método obtiene todas las imágenes

        // Pasar las imágenes a la vista
        $router->render('SobreNosotros/sobre-nosotros', [
            'imagenes' => $imagenes
        ]);
    }
}

