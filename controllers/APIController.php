<?php

namespace Controllers;
use Model\Configuraciones;
use Model\Cita;
use Model\CitaServicio;
use Model\Servicio;
use Model\Usuario;
use Model\HistorialCita;
use Model\AdminCita;
use Classes\Email;
use Model\TotalGanado;
use Model\CierreCaja;
use Model\Exception; // Asegúrate de que esta clase exista


class APIController {
    public static function index() {
        $servicios = Servicio::all();
        echo json_encode($servicios);
    }

    public static function indexEdicion() {
        $serviciosEdicion = Servicio::all();
        echo json_encode($serviciosEdicion);
    }
    public static function indexAgregar() {
        $serviciosAgregar = Servicio::all();
        echo json_encode($serviciosAgregar);
    }
    public static function listaServicio() {
        $listaServicio = Servicio::all();
        echo json_encode($listaServicio);
    }   
    
    public static function obtenerPrecioTotales() {
        $totales = TotalGanado::obtenerTotales();

        // Devolver la respuesta en JSON
        echo json_encode($totales);
    }
    public static function CerrarCaja()
    {
        // Leer el JSON enviado desde JavaScript
        $datos = json_decode(file_get_contents('php://input'), true);
        
        // Validar que los datos existen
        if (!isset($datos['fecha']) || !isset($datos['citas']) || empty($datos['citas'])) {
            echo json_encode(['message' => 'Datos incompletos o citas vacías']);
            http_response_code(400);
            return;
        }
    
        // Calcular el total sumando los precios de todas las citas
        $totalCalculado = array_reduce($datos['citas'], function ($carry, $cita) {
            return $carry + floatval($cita['precio']);
        }, 0);
    
        // Log para depuración
        error_log("Total calculado en backend: " . $totalCalculado);
    
        // Crear nueva instancia del modelo CierreCaja
        $cierre = new CierreCaja([
            'fecha' => $datos['fecha'],
            'total' => $totalCalculado
        ]);
    
        // Guardar en la base de datos
        $resultadoCierre = $cierre->guardar();
    
        if ($resultadoCierre) {
            $citasActualizadas = [];
            $citasYaActualizadas = [];
    
            foreach ($datos['citas'] as $citaData) {
                if (isset($citaData['id']) && is_numeric($citaData['id'])) {
                    $idCita = $citaData['id'];
    
                    $cita = Cita::find($idCita);
                    if (!$cita) {
                        echo json_encode(['message' => "Cita con ID $idCita no encontrada"]);
                        http_response_code(404);
                        return;
                    }
    
                    if (in_array($idCita, $citasYaActualizadas)) {
                        continue;
                    }
    
                    if ($cita->estado == 3) {
                        $cita->sincronizar(['estado' => 4]);
    
                        if (isset($citaData['precio'])) {
                            $cita->sincronizar(['precio' => $citaData['precio']]);
                        }
    
                        if (isset($citaData['fecha'])) {
                            $cita->sincronizar(['fecha' => $citaData['fecha']]);
                        }
    
                        if ($cita->actualizar()) {
                            $citasActualizadas[] = $idCita;
                            $citasYaActualizadas[] = $idCita;
                        } else {
                            error_log("Error al actualizar la cita con ID $idCita");
                            echo json_encode(['message' => "Error al actualizar la cita con ID $idCita"]);
                            http_response_code(500);
                            return;
                        }
                    } else {
                        if ($cita->estado == 4) {
                            $citasYaActualizadas[] = $idCita;
                        } else {
                            error_log("La cita con ID $idCita no tiene el estado 3, estado actual: " . $cita->estado);
                            echo json_encode(['message' => "La cita con ID $idCita no tiene el estado 3"]);
                            http_response_code(400);
                            return;
                        }
                    }
                } else {
                    echo json_encode(['message' => 'ID de cita no válido']);
                    http_response_code(400);
                    return;
                }
            }
    
            error_log("Citas actualizadas: " . print_r($citasActualizadas, true));
            error_log("Citas enviadas: " . print_r($datos['citas'], true));
    
            echo json_encode(['message' => 'Caja cerrada con éxito y citas actualizadas', 'total' => $totalCalculado]);
        } else {
            echo json_encode(['message' => 'Error al cerrar caja']);
            http_response_code(500);
        }
    }
    
    
    
    
    
    
    


    public static function obtenerTotales() {
        // Obtener todos los registros de usuarios, servicios, citas y suscripciones
        $usuarios = Usuario::all(); // Todos los usuarios
        $servicios = Servicio::all(); // Todos los servicios
        $citas = Cita::all(); // Todas las citas
    
        // Filtrar las citas con estado = 3
        $citasFiltradas = array_filter($citas, function($cita) {
            return $cita->estado == 0; // Solo citas con estado 3
        });
    
        // Filtrar los usuarios con admin = 0 y suscripción = 1 o 0
        $suscripciones = array_filter($usuarios, function($usuario) {
            return $usuario->suscripcion == 1;
        });
        
        $clientes = array_filter($usuarios, function($usuario) {
            return $usuario->admin == 0 && ($usuario->suscripcion == 1 || $usuario->suscripcion == 0);
        });
    
        // Devolver los totales como un objeto JSON
        echo json_encode([
            'totalClientes' => count($clientes),
            'totalServicios' => count($servicios),
            'totalSuscripciones' => count($suscripciones),
            'totalCitas' => count($citasFiltradas) // Usar las citas filtradas con estado 3
        ]);
    }
    
    
    
    
    



    public static function enviarNovedad() {
        // Leer los datos enviados en la solicitud
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
    
        // Validar que el mensaje no esté vacío
        if (!isset($datos['mensaje']) || empty(trim($datos['mensaje']))) {
            error_log("Error: Mensaje vacío recibido en enviarNovedad.");
            echo json_encode(['status' => 'error', 'message' => 'El mensaje no puede estar vacío.']);
            return;
        }
    
        $mensaje = trim($datos['mensaje']);
       // error_log("Mensaje recibido: " . $mensaje);
    
        // Obtener todos los usuarios
        $usuarios = Usuario::all();
    
        if (!$usuarios || count($usuarios) === 0) {
            error_log("Error: No hay clientes registrados para enviar mensajes.");
            echo json_encode(['status' => 'error', 'message' => 'No hay clientes registrados para enviar mensajes.']);
            return;
        }
    
        // Enviar el mensaje a cada usuario
        foreach ($usuarios as $usuario) {
           // error_log("Procesando usuario ID: {$usuario->id}, Nombre: {$usuario->nombre}, Apellido: {$usuario->apellido}, Email: {$usuario->email}");
    
            if (!filter_var($usuario->email, FILTER_VALIDATE_EMAIL)) {
                //error_log("Email inválido para usuario ID {$usuario->id}: {$usuario->email}");
                continue; // Saltar este usuario si el email no es válido
            }
    
            $email = new Email($usuario->nombre,$usuario->email);
            $email->enviarAvisoImportante($usuario->id, $usuario->nombre, $usuario->apellido, $mensaje);
        }
    
        // Respuesta exitosa
        //error_log("Mensaje enviado a todos los clientes correctamente.");
        echo json_encode(['status' => 'success', 'message' => 'Mensaje enviado a todos los clientes.']);
    }
    

    public static function guardarConfiguracion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtener los datos del cuerpo de la solicitud
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Obtener los valores de la configuración o mantener los valores predeterminados
            $recordatorioDiaAntes = isset($data['recordatorioDiaAntes']) ? $data['recordatorioDiaAntes'] : 1; // 1 como valor predeterminado
            $avisarNuevoServicio = isset($data['avisarNuevoServicio']) ? $data['avisarNuevoServicio'] : 1; // 1 como valor predeterminado
    
            // Buscar la configuración existente (se asume que hay solo una con ID = 1)
            $configuracion = Configuraciones::find(1); 
    
            // Actualizar los valores de la configuración
            $configuracion->recordatorio_dia_antes = $recordatorioDiaAntes;
            $configuracion->avisar_nuevo_servicio = $avisarNuevoServicio;
            $configuracion->fecha_actualizacion = date('Y-m-d H:i:s'); // Fecha de modificación
    
            // Usamos el método actualizar() para guardar los cambios
            $resultado = $configuracion->actualizar();
    
            // // Responder con el resultado
            // echo json_encode([
            //     'resultado' => $resultado,
            //     'recordatorioDiaAntes' => $configuracion->recordatorio_dia_antes,
            //     'avisarNuevoServicio' => $configuracion->avisar_nuevo_servicio,
            //     'mensaje' => $resultado ? 'Configuración actualizada correctamente' : 'Hubo un error al actualizar la configuración'
            // ]);
        }
    }
    
    
    
    





    public static function obtenerHistorialCitas() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
        
        // Obtén el ID del usuario de la sesión
        $usuarioId = $_SESSION['id'] ?? null;
    
        // Verifica si el usuario está autenticado
        if (!$usuarioId) {
            echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
            return;
        }
    
        // Consulta SQL actualizada para incluir el usuarioId
        $consulta = "SELECT citas.id AS citaId, citas.fecha, citas.hora, citas.finalizacion, servicios.nombre AS servicioNombre, 
                     servicios.precio AS servicioPrecio, citas.estado, servicios.id AS servicioId, citas.usuarioId, citas.duracionTotal 
                     FROM citas 
                     LEFT JOIN citasServicios ON citasServicios.citaId = citas.id 
                     LEFT JOIN servicios ON servicios.id = citasServicios.servicioId 
                     WHERE citas.usuarioId = {$usuarioId} AND citas.fecha >= now()

                     ORDER BY citas.fecha asc";
    
        // Ejecutar la consulta para obtener los datos
        $historial = HistorialCita::SQL($consulta);
        
        // Devolver los datos en formato JSON
        echo json_encode(['success' => true, 'historial' => $historial]);
    }

    public static function guardarCitaEnSesion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $citaId = intval($data['citaId'] ?? 0);
        
        if ($citaId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de cita no válido.']);
            return;
        }
    
        $_SESSION['citaId'] = $citaId;
        echo json_encode(['success' => true]);
    }
    
    public static function obtenerDetallesServicioDeSesion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
        $servicioId = $_SESSION['servicioId'] ?? null;
        $usuarioId = $_SESSION['id'] ?? null;
    
        if (!$usuarioId || !$servicioId) {
            echo json_encode(['success' => false, 'message' => 'Usuario no autenticado o ID de servicio no especificado.']);
            return;
        }
    
        // Escapar los valores para prevenir inyección SQL
        $servicioId = intval($servicioId); // Asegúrate de que sea un entero
        $usuarioId = intval($usuarioId); // Asegúrate de que sea un entero
    
        // Construir la consulta SQL para obtener los detalles del servicio
        $consulta = "SELECT servicios.id AS servicioId, servicios.nombre, servicios.precio,servicios.duracion
                     FROM servicios 
                     WHERE servicios.id = $servicioId";
    
        // Ejecutar la consulta
        $servicio = Servicio::SQL($consulta);
    
        if (empty($servicio)) {
            echo json_encode(['success' => false, 'message' => 'Servicio no encontrado.']);
            return;
        }
    
        echo json_encode(['success' => true, 'servicio' => $servicio[0]]);
    }
    
    public static function guardarServicioEnSesion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['servicioId'])) {
            $_SESSION['servicioId'] = $data['servicioId']; // Guardar el servicioId en la sesión
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Servicio ID no proporcionado']);
        }
    }
    


    
    public static function obtenerDetallesCitaDeSesion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
        $citaId = $_SESSION['citaId'] ?? null;
        $usuarioId = $_SESSION['id'] ?? null;
        
        if (!$usuarioId || !$citaId) {
            echo json_encode(['success' => false, 'message' => 'Usuario no autenticado o ID de cita no especificado.']);
            return;
        }
    
        // Escapar los valores para prevenir inyección SQL
        $citaId = intval($citaId); // Asegúrate de que sea un entero
        $usuarioId = intval($usuarioId); // Asegúrate de que sea un entero
    
        // Construir la consulta SQL directamente
        $consulta = "SELECT citas.id AS citaId, citas.fecha, citas.hora, servicios.nombre AS servicioNombre, servicios.id as servicioId
                     FROM citas 
                     LEFT JOIN citasServicios ON citasServicios.citaId = citas.id 
                     LEFT JOIN servicios ON servicios.id = citasServicios.servicioId 
                     WHERE citas.id = $citaId AND citas.usuarioId = $usuarioId";
    
        // Ejecutar la consulta
        $cita = HistorialCita::SQL($consulta);
        
        if (empty($cita)) {
            echo json_encode(['success' => false, 'message' => 'Cita no encontrada.']);
            return;
        }
        
        echo json_encode(['success' => true, 'cita' => $cita[0]]);
    }
    



    public static function guardarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtener el ID del usuario a actualizar
            $id = $_POST['id'] ?? null;
    
            // Crear una instancia del modelo Usuario con los datos a actualizar
            $usuario = Usuario::find($id);
            $usuario->nombre = $_POST['nombre'] ?? $usuario->nombre;
            $usuario->apellido = $_POST['apellido'] ?? $usuario->apellido;
    
            // Guardar los cambios en la base de datos
            $resultado = $usuario->guardar();
             // Si el guardado fue exitoso, actualizar la sesión
            if ($resultado) {
                $_SESSION['nombre'] = $usuario->nombre;
                $_SESSION['apellido'] = $usuario->apellido;
                // Redirigir a la página de citas
                // header('Location: /cita/index');
                // exit();
            }

    
            // Responder con el resultado y los nuevos datos
            echo json_encode([
                'resultado' => $resultado,
                'nombre' => $usuario->nombre, // Agregar el nuevo nombre
                'apellido' => $usuario->apellido, // Agregar el nuevo apellido
                'mensaje' => 'Usuario actualizado correctamente'
            ]);
        }
    }
    
    public static function actualizarcita() {
        // Asegurarse de que la respuesta siempre sea JSON
        header('Content-Type: application/json');
    
        // Obtener los datos de la solicitud
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    
        // Verificar si los datos de citaId y usuarioId están presentes
        if (isset($data['citaid']) && !empty($data['citaid'])) {
            $citaId = intval($data['citaid']);
        } else {
            echo json_encode(['resultado' => false, 'error' => 'Cita ID no proporcionado']);
            return;
        }
    
        // Si el usuarioId se está pasando desde el frontend, no hace falta obtenerlo de la sesión
        if (isset($data['usuarioId']) && !empty($data['usuarioId'])) {
            $usuarioId = intval($data['usuarioId']);
        } else {
            echo json_encode(['resultado' => false, 'error' => 'Usuario ID no proporcionado']);
            return;
        }
    
        // Obtener la cita actual
        $cita = Cita::findByCitaAndUsuario($citaId, $usuarioId);
    
        if ($cita) {
            // Guardar la fecha anterior antes de sincronizar los nuevos datos
            $fechaAntigua = $cita->fecha;
    
            // Verificar que se recibieron los datos de fecha y hora
            if (isset($data['fecha']) && isset($data['hora'])) {
                $cita->fecha = $data['fecha']; // Actualizar la fecha
                $cita->hora = $data['hora'];   // Actualizar la hora
    
                // Verificar si se recibió la fecha de finalización y actualizarla si es necesario
                if (isset($data['horaFinal'])) {
                    $cita->finalizacion = $data['horaFinal']; // Actualizar la fecha de finalización
                    error_log("Nueva hora de finalización recibida: " . $cita->finalizacion);
                } else {
                    error_log("No se recibió la nueva hora de finalización.");
                }
    
                $resultado = $cita->actualizar();
    
                if ($resultado) {
                    // Obtener información del usuario
                    $usuario = Usuario::find($usuarioId);
    
                    // Preparar y enviar el correo
                    if ($usuario) {
                        $email = new Email($usuario->email, $usuario->nombre);
                        $email->enviarAvisoCambioFecha(
                            $usuarioId,
                            $usuario->apellido,
                            $usuario->nombre,
                            $fechaAntigua,
                            $cita->fecha,
                            $cita->hora // Nueva hora
                        );
                    }
    
                    echo json_encode(['resultado' => true]);
                } else {
                    echo json_encode(['resultado' => false, 'error' => 'Error al actualizar la cita']);
                }
            } else {
                echo json_encode(['resultado' => false, 'error' => 'No se recibieron datos de fecha o hora']);
            }
        } else {
            echo json_encode(['resultado' => false, 'error' => 'Cita no encontrada']);
        }
    }
    
    


    
    
    public static function actualizarServicioEditable() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
    header('Content-Type: application/json'); // Forzar el tipo de contenido a JSON
        ob_start(); // Captura toda la salida no deseada
        
        $usuarioId = intval($_SESSION['id'] ?? 0);
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $citaId = intval($data['citaId'] ?? 0);
        // Verificar que los datos contengan servicioIdAntiguo y servicioIdNuevo
        if ($data && isset($data['servicioIdAntiguo']) && isset($data['servicioIdNuevo'])) {
            $citaServicio = CitaServicio::findByCitaAndServicio($citaId, $data['servicioIdAntiguo']);
    
            if ($citaServicio) {
                $citaServicio->servicioId = $data['servicioIdNuevo'];
                $resultado = $citaServicio->actualizar();
    
                if ($resultado) {
                    $usuario = Usuario::find($usuarioId);
                    $servicioAntiguoNombre = Servicio::obtenerNombrePorId($data['servicioIdAntiguo']);
                    $servicioNuevoNombre = Servicio::obtenerNombrePorId($data['servicioIdNuevo']);
    
                    if ($usuario && $servicioAntiguoNombre && $servicioNuevoNombre) {
                        $email = new Email($usuario->email, $usuario->nombre);
                        $email->enviarAvisoServicio(
                            $usuarioId,
                            $usuario->nombre,
                            $usuario->apellido,
                            $servicioAntiguoNombre,
                            $servicioNuevoNombre
                        );
                    }
    
                    ob_end_clean();
                    echo json_encode(['resultado' => true]);
                    return;
                } else {
                    ob_end_clean();
                    echo json_encode(['resultado' => false, 'error' => 'Error al actualizar el servicio']);
                    return;
                }
            } else {
                ob_end_clean();
                echo json_encode(['resultado' => false, 'error' => 'Servicio antiguo no encontrado para esta cita']);
                return;
            }
        } else {
            ob_end_clean();
            echo json_encode(['resultado' => false, 'error' => 'Datos no válidos']);
            return;
        }
    }
    
    
// APIController.php
// APIController.php
public static function anularServicio() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();  // Solo iniciar la sesión si no está activa
    }
    $usuarioId = intval($_SESSION['id'] ?? 0);
    // Verificamos que la solicitud sea POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtenemos los datos del cuerpo de la solicitud
        $data = json_decode(file_get_contents("php://input"), true);

        // Verificamos que servicioId y citaId estén presentes
        if (isset($data['servicioId'], $data['citaId']) && !empty($data['servicioId']) && !empty($data['citaId'])) {
            $servicioId = $data['servicioId'];
            $citaId = $data['citaId']; // Ahora obtenemos citaId desde la solicitud

            // Buscamos la relación en la tabla citasServicios
            $citaServicio = CitaServicio::findByCitaAndServicio($citaId, $servicioId);

            if ($citaServicio) {
                 
                // $usuario = Usuario::find($usuarioId);
                // $email = new Email($usuario->nombre,$usuario->apellido);
                // $email -> ServicioAnulado(
                //     $usuarioId,
                //     $usuario->nombre,
                //     $usuario->apellido
                // );

                // Si la relación existe, la eliminamos
                if ($citaServicio->eliminar()) {
                   


                    // Si la eliminación fue exitosa, respondemos con éxito
                    echo json_encode(['success' => true]);
                } else {
                    // Si hubo un error al eliminar la relación
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar la relación']);
                }
            } else {
                // Si no se encuentra la relación en citasServicios
                echo json_encode(['success' => false, 'message' => 'Relación no encontrada']);
            }
        } else {
            // Si no se enviaron servicioId o citaId
            echo json_encode(['success' => false, 'message' => 'Faltan datos en la solicitud']);
        }
    } else {
        // Si la solicitud no es POST
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
}

public static function anularTurnoCompleto() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();  // Solo iniciar la sesión si no está activa
    }
    $usuarioId = intval($_SESSION['id'] ?? 0);

    // Verificamos que la solicitud sea POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtenemos los datos del cuerpo de la solicitud
        $data = json_decode(file_get_contents("php://input"), true);

        // Verificamos que citaId esté presente
        if (isset($data['citaId']) && !empty($data['citaId'])) {
            $citaId = $data['citaId']; // Obtener citaId desde la solicitud

            // Buscar la cita por ID
            $cita = Cita::find($citaId);

            if ($cita) {
                // Cambiar el estado de la cita a 2 (anulada)
                $cita->estado = 2; // Establecemos el nuevo estado directamente

                // Asignar 0 a duracionTotal
                $cita->duracionTotal = 0;

                // Obtener el usuario para enviar el correo
                $usuario = Usuario::find($usuarioId);
                $email = new Email($usuario->nombre, $usuario->apellido);
                $email->TurnoAnulado($usuarioId, $usuario->nombre, $usuario->apellido);

                // Enviar el correo notificando la cancelación
                $email->TurnoAnulado($usuarioId, $usuario->nombre, $usuario->apellido);

                // Usamos el método actualizar() para guardar el cambio
                if ($cita->actualizar()) {
                    echo json_encode(['success' => true, 'message' => 'El turno ha sido anulado correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado de la cita']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Cita no encontrada']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Faltan datos en la solicitud']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
}



public static function AgregarServicio() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();  // Solo iniciar la sesión si no está activa
    }
    header('Content-Type: application/json'); // Forzar el tipo de contenido a JSON
    ob_start(); // Captura toda la salida no deseada

    $usuarioId = intval($_SESSION['id'] ?? 0);
    $data = json_decode(file_get_contents("php://input"), true);

    // Verificar si el usuario está autenticado
    if (!$usuarioId) {
        echo json_encode(['resultado' => false, 'error' => 'Usuario no autenticado']);
        return;
    }

    // Obtener los IDs de los servicios que se desean agregar a la cita
    $serviciosIds = $data['serviciosIds'] ?? [];
    $citaId = intval($data['citaId'] ?? 0);

    // Verificar que la cita y los servicios existen
    if ($citaId <= 0 || empty($serviciosIds)) {
        echo json_encode(['resultado' => false, 'error' => 'Datos de cita o servicios no válidos']);
        return;
    }

    // Comprobar si la cita pertenece al usuario
    $cita = Cita::find($citaId);
    if (!$cita || $cita->usuarioId != $usuarioId) {
        echo json_encode(['resultado' => false, 'error' => 'Cita no encontrada o no pertenece al usuario']);
        return;
    }

    // Agregar los servicios a la cita
    foreach ($serviciosIds as $servicioId) {
        // Comprobar si el servicio existe
        $servicio = Servicio::find($servicioId);
        if ($servicio) {
            // Insertar el servicio en la tabla de unión
            $citaServicio = new CitaServicio();
            $citaServicio->citaId = $citaId;
            $citaServicio->servicioId = $servicioId;
            $citaServicio->guardar(); // Guardar en la base de datos
        } else {
            echo json_encode(['resultado' => false, 'error' => "Servicio con ID $servicioId no encontrado"]);
            return;
        }
    }
    ob_end_clean();
    // Responder con éxito si se agregaron los servicios
    echo json_encode(['resultado' => true, 'mensaje' => 'Servicios agregados a la cita']);
    // Terminar el buffer de salida
}
public static function guardar() {
    // Log para ver los datos recibidos en POST
    //error_log("Datos recibidos en POST: " . print_r($_POST, true));

    // Almacena la Cita y devuelve el ID
    $cita = new Cita($_POST);
    $cita->duracionTotal = $_POST['duracionTotal'] ?? '';
    $cita->observaciones = $_POST['observacionesInfante'] ?? ''; // 🔹 Guardar observaciones
    $cita->finalizacion = $_POST['finalizacionPrevista'] ?? ''; // 🔹 Guardar finalización prevista

    // Log para verificar si la finalización prevista está presente
    //error_log("Finalización prevista: " . $cita->finalizacion);

    $resultado = $cita->guardar();

    // Log para verificar si la cita se guardó y devolvió un ID
    //error_log("Resultado de guardar cita: " . print_r($resultado, true));

    $id = $resultado['id'] ?? null;
    if (!$id) {
        error_log("Error: No se obtuvo un ID después de guardar la cita.");
        echo json_encode(['error' => 'No se pudo guardar la cita']);
        return;
    }

    // Almacena los Servicios con el ID de la Cita
    $idServicios = explode(",", $_POST['servicios'] ?? '');
    $nombreServicios = '';
    foreach ($idServicios as $idServicio) {
        $args = [
            'citaId' => $id,
            'servicioId' => $idServicio
        ];
        $citaServicio = new CitaServicio($args);
        $citaServicio->guardar();

        // Obtener el nombre del servicio
        $servicio = Servicio::find($idServicio);
        $nombreServicios .= $servicio->nombre . ', ';
    }

    $nombreServicios = rtrim($nombreServicios, ', ');

    // Obtener los detalles del usuario
    $usuarioId = $_POST['usuarioId'] ?? null;
    $usuario = Usuario::find($usuarioId);
    $nombre = $usuario->nombre ?? '';
    $apellido = $usuario->apellido ?? '';
    $email = $usuario->email ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $observaciones = $_POST['observacionesInfante'] ?? '';

    // Log antes de enviar el correo
    //error_log("Enviando correo con observaciones: " . $observacionesInfante);

    // Enviar el correo al cliente
    $emailCliente = new Email($nombre, $email);
    $emailCliente->enviarAvisoCitaCliente(
        $usuarioId,
        $nombre,
        $apellido,
        $fecha,
        $hora,
        $nombreServicios
    );

    // Devolver el resultado
    echo json_encode(['resultado' => $resultado]);
}


    public static function eliminar() {
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $cita = Cita::find($id);
            $cita->eliminar();
            header('Location:' . $_SERVER['HTTP_REFERER']);
        }
    }public static function PosponerFecha()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Solo iniciar la sesión si no está activa
        }
        header('Content-Type: application/json'); // Establecer el tipo de contenido a JSON
        ob_start(); // Captura la salida para evitar mostrar algo antes de que se retorne la respuesta
    
        // Obtener los datos enviados en la solicitud
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    
        // Verificar que los datos requeridos estén presentes
        if (!isset($data['citaid']) || !isset($data['fecha']) || !isset($data['hora']) || !isset($data['horaFinal']) || !isset($data['observacion']) || !isset($data['email']) || !isset($data['usuarioId'])) {
            echo json_encode(['resultado' => false, 'error' => 'Faltan datos en la solicitud']);
            ob_end_clean();
            return; // Detener la ejecución si faltan datos
        }
    
        // Obtener el ID de la cita desde la solicitud
        $citaId = intval($data['citaid']);
        $usuarioId = intval($data['usuarioId']); // Obtener el usuarioId desde el frontend
    
        // Obtener la cita actual usando el ID proporcionado
        $cita = Cita::find($citaId);
    
        if ($cita) {
            // Guardar la fecha y horas antiguas antes de actualizar
            $fechaAntigua = $cita->fecha;
            $horaAntigua = $cita->hora;
            $horaFinalAntigua = $cita->finalizacion ?? null; // Si la cita ya tenía horaFinal
    
            // Actualizar la cita con los nuevos datos
            $cita->fecha = $data['fecha']; // Nueva fecha
            $cita->hora = $data['hora']; // Nueva hora
            $cita->finalizacion = $data['horaFinal']; // Nueva hora de finalización
            $observacion = $data['observacion']; // Observación enviada por el admin
    
            // Guardar la cita actualizada
            $resultado = $cita->actualizar();
    
            if ($resultado) {
                // Aquí usamos el correo recibido desde el frontend
                $emailCliente = $data['email'];  // Correo electrónico del cliente
                $usuario = Usuario::find($usuarioId); // Obtener el usuario actual con el usuarioId pasado desde el frontend
    
                if ($usuario) {
                    // Log para verificar el usuario encontrado
                    error_log("Usuario encontrado: " . $usuario->nombre . " " . $usuario->apellido);
    
                    // Preparar y enviar el correo con la información de la cita
                    $email = new Email($usuario->nombre, $emailCliente);  // Usamos el email del cliente
                    $email->enviarAvisoCambioFechaCliente(
                        $usuario->id,
                        $usuario->nombre,
                        $usuario->apellido,
                        $fechaAntigua, // Fecha anterior
                        $cita->fecha, // Nueva fecha
                        $horaAntigua, // Hora anterior
                        $cita->hora,  // Nueva hora
                        $horaFinalAntigua, // Hora final anterior
                        $cita->hora_final, // Nueva hora final
                        $observacion  // Observación sobre el cambio
                    );
                } else {
                    // Log si no se encuentra el usuario
                    error_log("No se encontró al usuario con ID: " . $usuarioId);
                }
    
                ob_end_clean();
                // Retornar una respuesta positiva al frontend
                echo json_encode(['resultado' => true, 'message' => 'Cita actualizada y correo enviado']);
            } else {
                ob_end_clean();
                echo json_encode(['resultado' => false, 'error' => 'Error al actualizar la cita']);
            }
        } else {
            ob_end_clean();
            echo json_encode(['resultado' => false, 'error' => 'Cita no encontrada']);
        }
    }
    
    


    public static function AnularTurnoAdmin()
    {
        header('Content-Type: application/json'); // Establecer el tipo de contenido a JSON
        ob_start(); // Captura la salida para evitar mostrar algo antes de que se retorne la respuesta
    
        // Obtener los datos enviados en la solicitud
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    
        // Verificar que los datos requeridos estén presentes
        if (!isset($data['citaid']) || !isset($data['email']) || !isset($data['observacion']) || !isset($data['usuarioId'])) {
            echo json_encode(['resultado' => false, 'error' => 'Faltan datos en la solicitud']);
            ob_end_clean();
            return; // Detener la ejecución si faltan datos
        }
    
        // Obtener los datos de la solicitud
        $citaId = intval($data['citaid']);
        $email = $data['email'];  // Correo electrónico del cliente
        $usuarioId = intval($data['usuarioId']);
        $observacion = $data['observacion'];
    
        // Buscar la cita por ID
        $cita = Cita::find($citaId);
    
        if ($cita) {
            // Cambiar el estado de la cita a 2 (anulada)
            $cita->estado = 2;  // Estado "anulada"
            
            // Actualizar la cita
            $resultado = $cita->actualizar();
    
            if ($resultado) {
                // Si la cita se actualizó correctamente, enviamos el correo al cliente
                $usuario = Usuario::find($usuarioId);  // Obtener el usuario administrador
    
                if ($usuario) {
                    // Crear la instancia del correo
                    $emailCliente = new Email($usuario->nombre, $email);  // Crear email al cliente
                    // Enviar el correo notificando la anulación
                    $emailCliente->enviarAvisoAnulacionCitaCliente(
                        $usuario->id,
                        $usuario->nombre,
                        $usuario->apellido,
                        $cita->fecha,
                        $cita->hora,
                        $observacion
                    );
                }
    
                // Retornar respuesta exitosa
                ob_end_clean();
                echo json_encode(['resultado' => true, 'message' => 'Cita anulada y correo enviado al cliente']);
            } else {
                // Si hubo un error al actualizar la cita
                ob_end_clean();
                echo json_encode(['resultado' => false, 'error' => 'Error al actualizar el estado de la cita']);
            }
        } else {
            // Si no se encontró la cita
            ob_end_clean();
            echo json_encode(['resultado' => false, 'error' => 'Cita no encontrada']);
        }
    }
    
    public static function RealizarCita(){
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    header('Content-Type: application/json'); // Establecer el tipo de contenido a JSON
    ob_start(); // Captura la salida para evitar mostrar algo antes de que se retorne la respuesta

    // Verificar que el ID de la cita esté presente en la solicitud
    if (!isset($_POST['id'])) {
        echo json_encode(['resultado' => false, 'error' => 'Faltan datos en la solicitud']);
        ob_end_clean();
        return; // Detener la ejecución si falta el ID
    }

    // Obtener el ID de la cita desde la solicitud
    $citaId = intval($_POST['id']);

    // Buscar la cita por ID
    $cita = Cita::find($citaId);

    if ($cita) {
        // Cambiar el estado de la cita a 3 (Realizada)
        $cita->estado = 3; // El estado "3" es "Realizada"
        
        // Actualizar la cita en la base de datos
        $resultado = $cita->actualizar();

        if ($resultado) {
            header('Location:' . $_SERVER['HTTP_REFERER']);
        } else {
            ob_end_clean();
            echo json_encode(['resultado' => false, 'error' => 'Error al actualizar el estado de la cita']);
        }
    } else {
        ob_end_clean();
        echo json_encode(['resultado' => false, 'error' => 'Cita no encontrada']);
    }
}



public static function crearPorAdmin()
{
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $fecha = $_POST['fecha']; // Fecha seleccionada
    $hora = $_POST['hora']; // Hora seleccionada
    $servicios = $_POST['servicios']; // Servicios seleccionados
    $telefono = isset($_POST['telefono']) ? $_POST['telefono'] : null; // Si el teléfono se envía, lo asignamos, si no, lo dejamos en null.
    $horaPrevista = $_POST['hora-prevista']; // Hora de finalización prevista

    // Validar que los campos requeridos no estén vacíos
    if (empty($nombre) || empty($apellido) || empty($email) || empty($fecha) || empty($hora) || empty($servicios)) {
        echo json_encode(["error" => "Todos los campos son obligatorios."]);
        return;
    }

    // Calcular la duración total de los servicios seleccionados (en minutos)
    $duracionTotal = 0;
    foreach ($servicios as $servicioId) {
        $servicio = Servicio::find($servicioId); // Obtener el servicio
        if ($servicio && isset($servicio->duracion)) {
            $duracionTotal += $servicio->duracion; // Asumimos que 'duracion' está en minutos
        }
    }

    if ($duracionTotal <= 0) {
        $duracionTotal = 0;
    }

    try {
        // 1. Crear el usuario
        $usuario = new Usuario();
        $usuario->nombre = $nombre;
        $usuario->apellido = $apellido;
        $usuario->email = $email;
        $usuario->telefono = $telefono;
        $usuario->confirmado = 0; 
        $usuario->guardar(); 

        $usuarioId = $usuario->obtenerUltimoId();
        if (empty($usuarioId)) {
            throw new Exception("El ID del usuario no se generó correctamente.");
        }

        // 2. Crear la cita
        $cita = new Cita();
        $cita->usuarioId = $usuarioId;
        $cita->fecha = $fecha;
        $cita->hora = $hora;
        $cita->estado = 0; 
        $cita->duracionTotal = $duracionTotal;
        $cita->finalizacion = $horaPrevista;
        $cita->guardar(); 
        
        $citaId = $cita->obtenerUltimoId();

        // 3. Vincular los servicios
        $nombreServicios = '';
        foreach ($servicios as $servicioId) {
            $citaServicio = new CitaServicio();
            $citaServicio->citaId = $citaId;
            $citaServicio->servicioId = $servicioId;
            $citaServicio->guardar(); 

            $servicio = Servicio::find($servicioId);
            $nombreServicios .= $servicio->nombre . ', ';
        }

        $nombreServicios = rtrim($nombreServicios, ', ');

        // 4. Enviar correo al cliente
        $emailCliente = new Email($usuario->nombre, $email);
        $emailCliente->enviarAvisoCitaCliente(
            $usuario->id,
            $usuario->nombre,
            $usuario->apellido,
            $cita->fecha,
            $cita->hora,
            $nombreServicios
        );

        // Respuesta en JSON para el frontend
        echo json_encode(["success" => "La cita se ha creado correctamente."]);

    } catch (Exception $e) {
        echo json_encode(["error" => "Error en la creación de la cita: " . $e->getMessage()]);
    }
}




public static function consultarHorarios()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();  // Solo iniciar la sesión si no está activa
    }

    // Obtener el cuerpo de la solicitud y decodificarlo
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (!isset($inputData['fecha'])) {
        echo json_encode(['error' => 'Fecha no proporcionada']);
        return;
    }

    $fecha = $inputData['fecha'];

    // Asegúrate de que la fecha esté en el formato correcto
    $fecha = date('Y-m-d', strtotime($fecha));  // Ajustar el formato de fecha según sea necesario

    // Consultar las citas con la fecha seleccionada
    $citas = Cita::consultarPorFecha($fecha);

    if (empty($citas)) {
        echo json_encode([]);
        return;
    }

    echo json_encode($citas);
}





}