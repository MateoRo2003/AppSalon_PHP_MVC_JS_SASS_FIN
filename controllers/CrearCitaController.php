<?php

namespace Controllers;

use Model\Servicio;
use MVC\Router;

class CrearCitaController {
   
   
    public static function index(Router $router) {
       
           // En AdminController.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Solo iniciar la sesión si no está activa
}

            isAdmin();
            $servicios = Servicio::all();
            // Renderizar la página principal si no hay sesión activa
            $router->render('CrearCita/index', [
                'servicios' => $servicios
            ]);
         }
        
    

}