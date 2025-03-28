<?php

namespace Controllers;
use Classes\Email;
use MVC\Router;
use Model\Usuario;
use Model\Servicio;
use Model\Imagen;
class CitaController {
    public static function index( Router $router ) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
    
        // Verifica si el usuario está autenticado
        isAuth();
    
        // Obtener el 'id' del usuario desde la sesión
        $id = $_SESSION['id'];
    
        // Obtener los datos del usuario desde la base de datos utilizando el modelo Usuario
        $usuario = Usuario::find($id); // Asumiendo que tienes un método 'find' en el modelo Usuario para buscar por ID
    
        // Obtener los servicios desde la base de datos
        $servicios = Servicio::all();
        $imagenes = Imagen::all();
        // Renderizar la vista 'cita/index' y pasar todos los datos
        $router->render('cita/index', [
            'nombre' => $usuario->nombre, // Nombre del usuario desde la base de datos
            'apellido' => $usuario->apellido, // Apellido del usuario desde la base de datos
            'id' => $usuario->id,
            'email' => $usuario->email, // ID del usuario desde la base de datos
            'servicios' => $servicios,
            'imagenes' => $imagenes // Servicios obtenidos desde la base de datos
        ]);
    }
    

    // public static function renderBarra(Router $router) {
    //     if (session_status() == PHP_SESSION_NONE) {
    //         session_start();  // Solo iniciar la sesión si no está activa
    //     }
    
    //     // Verifica si el usuario está autenticado
    //     isAuth();
    
    //     // Obtener el 'id' del usuario desde la sesión
    //     $id = $_SESSION['id'];
    
    //     // Obtener los datos del usuario desde la base de datos utilizando el modelo Usuario
    //     $usuario = Usuario::find($id); // Asumiendo que tienes un método 'find' en el modelo Usuario para buscar por ID
        
    //     // Obtener los servicios desde la base de datos
    //     $servicios = Servicio::all();
        
    //     // Renderizar la vista 'template/barra' y pasar los datos del usuario
    //     $router->render('template/barra', [
    //         'nombre' => $usuario->nombre, // Nombre del usuario desde la base de datos
    //         'apellido' => $usuario->apellido, // Apellido del usuario desde la base de datos
    //         'email' => $usuario->email, // Correo del usuario desde la base de datos
    //         'id' => $usuario->id, // ID del usuario desde la base de datos
    //         'servicios' => $servicios  // Servicios obtenidos desde la base de datos
    //     ]);
    // }
    
    
    public static function Suscripcion()
    {
        // Obtener los datos del formulario
        $correo = $_POST['EMAIL'] ?? null;
    
        // Validar que el campo de correo no esté vacío
        if (empty($correo)) {
            echo "El correo es obligatorio.";
            return;
        }
    
        // Validar el formato del correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo "El formato del correo no es válido.";
            return;
        }
    
        // Verificar si el correo ya existe en la base de datos
        $usuario = Usuario::findByEmail($correo); // Usamos un método en el modelo Usuario para buscar por correo
    
        if ($usuario) {
            // Si el correo existe, actualizamos la suscripción
            $usuario->suscripcion = 1;
            $usuario->guardar(); // Guarda el cambio en la base de datos
    
            // Enviar correo de confirmación (opcional)
            try {
                $email = new Email($usuario->nombre, $correo);
                $email->enviarConfirmacionSuscripcion($correo);
    
                // Redirigir a una página de agradecimiento o éxito
                header('Location: /'); // Cambiar según sea necesario
                exit;
            } catch (Exception $e) {
               // echo "<script>alert('Hubo un error al procesar tu suscripción. Por favor, inténtalo nuevamente.'); window.location.href = '/suscripcion';</script>";
            }
        } else {
            // Si no existe el correo, crear un nuevo usuario con la suscripción activa
            $nuevoUsuario = new Usuario([
                'email' => $correo,
                'suscripcion' => 1,  // Suscripción activada
            ]);
            $nuevoUsuario->guardar(); // Guardar el nuevo usuario en la base de datos
    
            // Enviar correo de confirmación (opcional)
            try {
                $email = new Email('Nuevo Usuario', $correo); // Podrías cambiar el nombre por el que corresponda
                $email->enviarConfirmacionSuscripcion($correo);
    
                // Redirigir a una página de agradecimiento o éxito
                header('Location: /'); // Cambiar según sea necesario
                exit;
            } catch (Exception $e) {
              //  echo "<script>alert('Hubo un error al procesar tu suscripción. Por favor, inténtalo nuevamente.'); window.location.href = '/suscripcion';</script>";
            }
        }
    }
    public static function Contactar()
    {
        // Obtener los datos del formulario
        $nombre = $_POST['nombre'] ?? null;
        $correo = $_POST['correo'] ?? null;
        $asunto = $_POST['asunto'] ?? null;
        $mensaje = $_POST['mensaje'] ?? null;
    
        // Validar que los campos requeridos no estén vacíos
        if (empty($nombre) || empty($correo) || empty($asunto) || empty($mensaje)) {
            echo "Todos los campos son obligatorios.";
            return;
        }
    
        try {
            // Crear la instancia de la clase Email
            $email = new Email($nombre, $correo);
    
            // Enviar el correo al administrador
            $email->enviarMensajeContacto(
                $nombre,
                $correo,
                $asunto,
                $mensaje
            );
    
            // Enviar el correo al cliente para confirmar que su mensaje ha sido recibido
            $email->enviarMensajeContactoCliente($nombre, $correo);
    
            header('Location: /'); // Redirige a /login en caso de éxito
            exit;
        } catch (Exception $e) {
            // En caso de error
            echo "<script>alert('Hubo un error al enviar el mensaje. Por favor, inténtalo nuevamente.'); window.location.href = '/contactar';</script>";
        }
    }
    

    	




    public static function editar(Router $router) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
        isAuth(); // Asegura que el usuario está autenticado
    
        // Obtener el ID del usuario autenticado desde la sesión
        $id = $_SESSION['id'];
        $usuario = Usuario::find($id);
        $alertas = [];
        $actualizado = false; // Variable para indicar si se ha actualizado
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sincroniza los datos recibidos en la solicitud POST con el objeto $usuario
            $usuario->sincronizar($_POST);
            // Opcional: Validación de los datos antes de guardar
            $alertas = $usuario->validar();
    
            if (empty($alertas)) {
                $resultado = $usuario->guardar();
                
                if ($resultado) {
                    // Actualiza los datos en la sesión
                    $_SESSION['nombre'] = $usuario->nombre;
                    $_SESSION['apellido'] = $usuario->apellido;
    
                    // Indica que se ha actualizado el perfil
                    $actualizado = true;
                }
            }
            
            // Envía respuesta JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $actualizado, // Indica si se actualizó o no
                'message' => $actualizado ? 'Perfil actualizado con éxito.' : 'Error al actualizar el perfil.'
            ]);
            exit; // Asegúrate de salir después de enviar la respuesta
        }
    
        // Renderiza la vista con el formulario de perfil, pasando el objeto $usuario, las alertas y la variable de actualización
        $router->render('cita/index', [
            'usuario' => $usuario,
            'alertas' => $alertas,
            'actualizado' => $actualizado // Agrega esta línea
        ]);
    }




    public static function verificarContra() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
        header('Content-Type: application/json');
    
        // Verificar si el usuario está autenticado
        $usuarioId = $_SESSION['id'] ?? null;
        if (!$usuarioId) {
            echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
            return;
        }
    
        $contraseñaActual = $_POST['contraseña_actual'] ?? '';
    
        // Buscar al usuario por ID
        $usuario = Usuario::find($usuarioId);
    
        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
            return;
        }
    
        // Verificar contraseña
        if (password_verify($contraseñaActual, $usuario->password)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta.']);
        }
    }
    


    public static function actualizarContra() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
        header('Content-Type: application/json');
    
        $usuarioId = $_SESSION['id'] ?? null;
        if (!$usuarioId) {
            echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
            return;
        }
    
        $nuevaContraseña = $_POST['nueva_contraseña'] ?? '';
        $confirmarContraseña = $_POST['confirmar_contraseña'] ?? '';
    
        // Validar que las contraseñas no estén vacías
        if (empty($nuevaContraseña) || empty($confirmarContraseña)) {
            echo json_encode(['success' => false, 'message' => 'Ambos campos son obligatorios.']);
            return;
        }
    
        // Validar longitud y coincidencia
        if (strlen($nuevaContraseña) < 6) {
            echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres.']);
            return;
        }
    
        if ($nuevaContraseña !== $confirmarContraseña) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
            return;
        }
    
        // Buscar al usuario
        $usuario = Usuario::find($usuarioId);
        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
            return;
        }
    
        // Actualizar la contraseña
        $usuario->password = password_hash($nuevaContraseña, PASSWORD_BCRYPT);
        $usuario->guardar();
    
        echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
    }
    

}


