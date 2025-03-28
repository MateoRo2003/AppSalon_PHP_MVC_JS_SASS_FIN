<?php

namespace Controllers;

use Model\Imagen;
use MVC\Router;


class ImagenController {
    public static function index(Router $router) {
        // En AdminController.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Solo iniciar la sesión si no está activa
}

        isAdmin();
    
        $imagenes = Imagen::all(); // Obtener todas las imágenes de la BD
    
        // Recuperar las imágenes y convertirlas en formato adecuado
        // No es necesario convertir a base64 ya que ahora trabajas con 'imagen' directamente
        foreach ($imagenes as $imagen) {
            if ($imagen->imagen) {
                // Solo si la imagen existe, puedes guardarla en base64 para mostrarla en la vista
                $imagen->imagenBase64 = base64_encode($imagen->imagen);
            }
        }
    
        $router->render('imagenes/index', [
            'nombre' => $_SESSION['nombre'],
            'imagenes' => $imagenes
        ]);
    }
    

    public static function crear() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
        isAdmin();
    
        $imagen = new Imagen();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imagen->sincronizar($_POST);
    
            // Verificar si se subió una imagen
            if (!empty($_FILES['archivo']['tmp_name'])) {
                // Leer el archivo y guardarlo como binario
                $imagenBinaria = file_get_contents($_FILES['archivo']['tmp_name']);
                $imagen->imagen = $imagenBinaria; // Guardar en la columna 'imagen'
            }
    
            // Guardar directamente sin validaciones
            $imagen->guardar();
            header("Location: /imagenes");
            exit();
        }
    
        $router->render('imagenes/crear', [
            'imagen' => $imagen
        ]);
    }
    
    

    public static function editar(Router $router) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
        isAdmin();
    
        // Verificar que el ID es un número válido
        if (!is_numeric($_GET['id'])) return;
    
        $imagen = Imagen::find($_GET['id']);
        $alertas = [];
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log('Datos recibidos en editar(): ' . print_r($_POST, true));
            error_log('Archivos subidos: ' . print_r($_FILES, true));
    
            $imagen->sincronizar($_POST);
    
            // Validar si se subió un archivo
            if (!empty($_FILES['archivo']['tmp_name'])) {
                $imagenBinaria = file_get_contents($_FILES['archivo']['tmp_name']);
                $imagen->imagen = $imagenBinaria;
            }
    
            $alertas = $imagen->validar();
    
            if (empty($alertas)) {
                $resultado = $imagen->guardar();
                if ($resultado) {
                    header('Location: /imagenes');
                    exit();
                } else {
                    error_log('Error al guardar la imagen en la base de datos.');
                    $alertas[] = "No se pudo guardar la imagen.";
                }
            }
        }
    
        $router->render('imagenes/editar', [
            'imagen' => $imagen,
            'alertas' => $alertas
        ]);
    }
    
    
    

    public static function eliminar() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
        isAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $imagen = Imagen::find($id);
            $imagen->eliminar();

            header("Location: /imagenes");
            exit();
        }
    }
}
