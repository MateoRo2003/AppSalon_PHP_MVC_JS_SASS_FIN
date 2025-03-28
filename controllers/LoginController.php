<?php

namespace Controllers;
use Model\AdminCita;
use Classes\Email;
use Model\Usuario;
use MVC\Router;



class LoginController {
    public static function login(Router $router) {
        $alertas = [];
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
    
            $alertas = $auth->validarLogin();
    
            if (empty($alertas)) {
                // Comprobar que exista el usuario
                $usuario = Usuario::where('email', $auth->email);
    
                if ($usuario) {
                    // Verificar el password y si está verificado
                    if ($usuario->comprobarPasswordAndVerificado($auth->password)) {
                        // Autenticar el usuario
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = trim($usuario->nombre);
                        $_SESSION['apellido'] = trim($usuario->apellido);
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;
    
                        // Redireccionamiento
                        if ((int)$usuario->admin === 1) {
                            $_SESSION['admin'] = $usuario->admin ?? null;
                            header('Location: /admin');
                            exit;
                        } else {
                            unset($_SESSION['admin']); // Elimina 'admin' si no es administrador
                            header('Location: /cita');
                            exit;
                        }
                    }
                } else {
                    // Usuario no encontrado
                    Usuario::setAlerta('error', 'Usuario no encontrado');
                }
            }
        }
    
        $alertas = Usuario::getAlertas();
    
        $router->render('auth/login', [
            'alertas' => $alertas,
            'nombre' => $_SESSION['nombre'] ?? '',
            'apellido' => $_SESSION['apellido'] ?? '',
            'id' => $_SESSION['id'] ?? null
        ]);
    }
    public static function enviarRecordatorioCita($cacheDir) {
        // Obtener la fecha actual y la del día siguiente
        $hoy = date('Y-m-d');
        $manana = date('Y-m-d', strtotime('+1 day'));
        
        // Consultar las citas para el día siguiente
        $consulta = "SELECT citas.id, citas.fecha, citas.hora, CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS cliente, ";
        $consulta .= "usuarios.email, usuarios.telefono, citas.estado, usuarios.id AS usuarioId ";  
        $consulta .= "FROM citas ";
        $consulta .= "LEFT OUTER JOIN usuarios ON citas.usuarioId = usuarios.id ";
        $consulta .= "WHERE citas.fecha = '$manana' "; // Filtro para el día siguiente
        $consulta .= "ORDER BY citas.fecha, citas.hora ASC"; 
    
        // Ejecutar la consulta con la clase AdminCita
        $citas = AdminCita::SQL($consulta);
    
        // Verificar si hay citas para el día siguiente
        if (empty($citas)) {
            return; // Si no hay citas, no hacer nada
        }
    
        // Iterar sobre las citas obtenidas
        foreach ($citas as $cita) {
            $usuario = Usuario::find($cita->usuarioId);
            if (!$usuario) {
                continue; // Si el usuario no existe, saltamos esta iteración
            }
    
            // Comprobamos si ya se envió el recordatorio para esta cita
            $cacheFile = "$cacheDir/recordatorio_{$cita->fecha}_{$cita->id}.txt";
            
            // Si el archivo ya existe, significa que ya se envió el recordatorio, entonces no enviamos otro
            if (file_exists($cacheFile)) {
                continue; // Saltamos al siguiente si el archivo ya existe
            }
    
            // Enviar correo al cliente con el recordatorio de la cita
            $emailCliente = new Email($usuario->nombre, $usuario->email);
            $emailCliente->enviarRecordatorioCitaCliente(
                $usuario->id,
                $usuario->nombre,
                $usuario->apellido,
                $cita->fecha,
                $cita->hora
            );
    
            // Crear archivo único por cita para evitar enviar el mismo recordatorio
            file_put_contents($cacheFile, "Enviado");  // Marcar que el recordatorio fue enviado
        }
    }
    
    
    
    
    

    public static function logout() {
        session_start();
        $_SESSION = [];
        header('Location: /');
    }

    public static function olvide(Router $router) {

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if(empty($alertas)) {
                 $usuario = Usuario::where('email', $auth->email);

                 if($usuario && $usuario->confirmado === "1") {
                        
                    // Generar un token
                    $usuario->crearToken();
                    $usuario->guardar();

                    //  Enviar el email
                    $email = new Email($usuario->nombre, $usuario->email,$usuario->token);
                    $email->enviarInstrucciones();

                    // Alerta de exito
                    Usuario::setAlerta('exito', 'Revisa tu email');
                 } else {
                     Usuario::setAlerta('error', 'El Usuario no existe o no esta confirmado');
                     
                 }
            } 
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide-password', [
            'alertas' => $alertas
        ]);
    }

    public static function recuperar(Router $router) {
        $alertas = [];
        $error = false;
    
        $token = s($_GET['token']);
    
        // Buscar usuario por su token
        $usuario = Usuario::where('token', $token);
    
        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token No Válido');
            $error = true;
        }
    
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Leer el nuevo password y guardarlo
            $password = new Usuario($_POST);
            $alertas = $password->validarPassword();
    
            if(empty($alertas)) {
                $usuario->password = null;
                $usuario->password = $password->password;
                $usuario->hashPassword();
                $usuario->token = null;
    
                $resultado = $usuario->guardar();
                if($resultado) {
                    // Almacenamos el mensaje de éxito para mostrarlo en la vista
                    Usuario::setAlerta('exito', 'Contraseña guardada con éxito');
                    
                   } else {
                    Usuario::setAlerta('error', 'Error al guardar la contraseña');
                }
            } else {
                // Si hay errores en las validaciones del password
                Usuario::setAlerta('error', 'Las alertas de contraseña no son válidas');
            }
        }
    
        // Renderizamos la vista con las alertas (se mostrarán en la página)
        $alertas = Usuario::getAlertas();
        $router->render('auth/recuperar-password', [
            'alertas' => $alertas, 
            'error' => $error
        ]);
    }
    

    
    public static function crear(Router $router) {
        $usuario = new Usuario;
    
        // Alertas vacías (variable local)
        $alertas = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sincronizamos los datos del formulario con el objeto usuario
            $usuario->sincronizar($_POST);
    
            // Validamos los datos del usuario
            $alertas = $usuario->validarNuevaCuenta();
    
            // Revisar que las alertas estén vacías (es decir, que la validación fue correcta)
            if (empty($alertas)) {
                // Verificar si el usuario ya está registrado
                $usuarioExistente = $usuario->existeUsuario();
    
                // Si el usuario existe
                if ($usuarioExistente) {
                    // Si el usuario está confirmado (confirmado = 1), mostrar alerta de que ya está registrado
                    if ($usuarioExistente->confirmado == 1) {
                        $alertas['error'][] = 'El usuario ya está registrado';
                    } else {
                        // Si el usuario no está confirmado, mostrar mensaje diferente
                        $alertas['error'][] = 'El usuario ya está registrado, pero no ha confirmado su correo. Revisa tu bandeja de entrada.';
                    }
    
                    // Si el usuario está suscrito pero no confirmado
                    if ($usuarioExistente->suscripcion == 1 && $usuarioExistente->confirmado == 0) {
                        // Actualizar el usuario con un nuevo token
                        $usuario->id = $usuarioExistente->id;  // Asignar el ID del usuario existente
                        $usuario->crearToken();  // Generar un nuevo token
                        $usuario->hashPassword();  // Hashear el Password
    
                        // No sobrescribir la suscripción (mantenemos el valor original)
                        $usuario->suscripcion = $usuarioExistente->suscripcion;
    
                        // Enviar correo de confirmación con el nuevo token
                        $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                        $email->enviarConfirmacion();  // Enviar el correo de confirmación
    
                        // Actualizar la suscripción del usuario en la base de datos
                        $resultado = $usuario->actualizar();  // Actualizar el usuario
    
                        // Si se actualizó correctamente, redirigir a la página de mensaje
                        if ($resultado) {
                            header('Location: /mensaje');
                            exit;  // Evitar que el código posterior se ejecute
                        }
                    }
                } else {
                    // Si el usuario no existe, proceder a crear la cuenta normalmente
                    $usuario->hashPassword();  // Hashear el Password
                    $usuario->crearToken();  // Generar el token
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                    $email->enviarConfirmacion();  // Enviar el correo de confirmación
                    $resultado = $usuario->guardar();  // Guardar el nuevo usuario
    
                    // Si se guardó correctamente, redirigir a la página de mensaje
                    if ($resultado) {
                        header('Location: /mensaje');
                        exit;  // Evitar que el código posterior se ejecute
                    }
                }
            }
        }
    
        // Renderizar la vista de creación de cuenta
        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas  // Pasar las alertas como variable local
        ]);
    }
    
    

    public static function mensaje(Router $router) {
        $router->render('auth/mensaje');
    }

    public static function confirmar(Router $router) {
        $alertas = [];
        $token = s($_GET['token']);
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            // Mostrar mensaje de error
            Usuario::setAlerta('error', 'Token No Válido');
        } else {
            // Modificar a usuario confirmado
            $usuario->confirmado = "1";
            $usuario->token = null;
            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta Comprobada Correctamente');
        }
       
        // Obtener alertas
        $alertas = Usuario::getAlertas();

        // Renderizar la vista
        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]);
    }
}