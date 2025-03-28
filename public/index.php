<?php 
require_once __DIR__ . '/../includes/app.php';
use Controllers\AdminController;
use Controllers\APIController;
use Controllers\CitaController;
use Controllers\LoginController;
use Controllers\ServicioController;
use Controllers\MostrarInfoController;
use Controllers\CrearCitaController;
use Controllers\ImagenController;
use Controllers\GoogleController;
use Model\Configuraciones;
use MVC\Router;
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Solo iniciar la sesión si no está activa
}

$fechaHoy = date('Y-m-d');
$fechaManana = date('Y-m-d', strtotime('+1 day')); // Fecha para el día siguiente
$cacheDir = __DIR__ . '/../cache/';

// Verificar si la carpeta cache/ existe, si no, la crea
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true); // Crea la carpeta con permisos adecuados
}

// NO crear archivo solo con la fecha de mañana
// Verifica si ya se enviaron los recordatorios para mañana
$cacheFile = "$cacheDir/recordatorio_{$fechaManana}.txt"; // Archivo con la fecha de mañana

// Verificamos que no exista ya un archivo para los recordatorios de mañana
if (!file_exists($cacheFile)) {
    // Obtener configuración de recordatorio
    $configuracion = Configuraciones::obtenerConfiguracion();
    
    // Verificar si el recordatorio está activado
    if ($configuracion->recordatorio_dia_antes == 1) {
        try {
            require_once __DIR__ . '/../controllers/LoginController.php';
            $loginController = new LoginController();
            $loginController->enviarRecordatorioCita($cacheDir);  // Aquí se ejecuta la lógica de recordatorio

            // El archivo de la fecha de mañana ahora no es necesario, ya que se crea un archivo único por cita.
            // Comentamos esta parte que estaba creando el archivo global de fecha solo.
            // file_put_contents($cacheFile, "Enviado");  // Indica que los recordatorios fueron enviados para mañana

        } catch (Exception $e) {
            // Si ocurre un error, se puede registrar en un archivo de log
            error_log("Error al enviar recordatorio: " . $e->getMessage());
        }
    } else {
        // echo 'Los recordatorios están desactivados por el administrador.';  
    }
} else {
    // Si el archivo existe, ya no se envían más recordatorios
    // Agregar registro si es necesario
    // error_log("Recordatorios ya enviados para el día siguiente: " . $fechaManana);
}
// Cargar la aplicación








$router = new Router();


//CRUD GOOGLE
$router->get('/login/google', [GoogleController::class, 'login']);
$router->get('/callback', [GoogleController::class, 'callback']);

//crearimagenes
$router->get('/imagenes', [ImagenController::class, 'index']);
$router->post('/admin/imagenes/crear', [ImagenController::class, 'crear']);
$router->post('/admin/imagenes/editar', [ImagenController::class, 'editar']);
$router->post('/admin/imagenes/eliminar', [ImagenController::class, 'eliminar']);
// Iniciar Sesión
$router->get('/login', [LoginController::class, 'login']);
$router->post('/login', [LoginController::class, 'login']);
$router->get('/logout', [LoginController::class, 'logout']);

// Recuperar Password
$router->get('/olvide', [LoginController::class, 'olvide']);
$router->post('/olvide', [LoginController::class, 'olvide']);
$router->get('/recuperar', [LoginController::class, 'recuperar']);
$router->post('/recuperar', [LoginController::class, 'recuperar']);

// Crear Cuenta
$router->get('/crear-cuenta', [LoginController::class, 'crear']);
$router->post('/crear-cuenta', [LoginController::class, 'crear']);
$router->post('/crear-cuentaAdmin', [APIController::class, 'crearPorAdmin']);

// Confirmar cuenta
$router->get('/confirmar-cuenta', [LoginController::class, 'confirmar']);
$router->get('/mensaje', [LoginController::class, 'mensaje']);

// AREA PRIVADA
$router->get('/cita', [CitaController::class,  'index']);
$router->get('/',[MostrarInfoController::class, 'index']);
$router->get('/admin', [AdminController::class,  'index']);  
$router->get('/perfil-editar', [CitaController::class,   'editar']);
$router->post('/perfil-editar', [CitaController::class,  'editar']);
$router->post('/verificar-contra', [CitaController::class, 'verificarContra']);
$router->post('/actualizar-contra', [CitaController::class, 'actualizarContra']);
$router->post('/contactar', [CitaController::class, 'Contactar']);
$router->post('/suscripcion', [CitaController::class, 'Suscripcion']);
$router->get('/promociones', [AdminController::class, 'promociones']);
$router->get('/clientes', [AdminController::class, 'clientes']);
$router->get('/rendimientos', [AdminController::class, 'rendimientos']);

// API de Citas
$router->post('/api/cierreCaja', [APIController::class, 'CerrarCaja']);
$router->get('/api/TotalGanado', [APIController::class, 'obtenerPrecioTotales']);
$router->get('/api/totales', [APIController::class, 'obtenerTotales']);
$router->post('/api/enviar_novedad', [APIController::class, 'enviarNovedad']);
$router->post('/api/guardar-configuracion', [APIController::class, 'guardarConfiguracion']);
$router->get('/api/servicios', [APIController::class, 'index']);
$router->post('/api/servicios-agregar', [APIController::class, 'indexAgregar']);
$router->get('/api/servicios-edicion', [APIController::class, 'indexEdicion']);
$router->get('/api/ListaServicios', [APIController::class, 'listaServicio']);
$router->post('/api/citas', [APIController::class, 'guardar']);
$router->get('/api/historial-citas', [APIController::class, 'obtenerHistorialCitas']);
$router->post('/api/guardar-cita-en-sesion', [APIController::class, 'guardarCitaEnSesion']);
$router->post('/api/guardar-servicio-en-sesion', [APIController::class, 'guardarServicioEnSesion']);
$router->get('/api/historial-citas/detalles-servicio', [APIController::class, 'obtenerDetallesServicioDeSesion']);
$router->get('/api/historial-citas/detalles', [APIController::class, 'obtenerDetallesCitaDeSesion']);
$router->post('/api/anularCita', [APIController::class, 'anularTurnoAdmin']);
$router->post('/api/actualizarturno', [APIController::class, 'actualizarcita']);
$router->post('/api/agregarServicio', [APIController::class, 'AgregarServicio']);
$router->post('/api/posponerFechaAdmin', [APIController::class, 'PosponerFecha']);
$router->post('/api/consultarHorarios', [APIController::class, 'consultarHorarios']);
$router->post('/api/actualizarservicioeditable', [APIController::class, 'actualizarServicioEditable']);
$router->post('/api/anularservicio', [APIController::class, 'anularServicio']);
$router->post('/api/anularturnocompleto', [APIController::class, 'anularTurnoCompleto']);

// Ruta para marcar la cita como realizada
$router->post('/api/realizado', [APIController::class, 'RealizarCita']);
$router->get('/crearCita', [CrearCitaController::class, 'index']);

// CRUD de Servicios
$router->get('/servicios', [ServicioController::class, 'index']);
$router->get('/servicios/crear', [ServicioController::class, 'crear']);
$router->post('/servicios/crear', [ServicioController::class, 'crear']);
$router->get('/servicios/actualizar', [ServicioController::class, 'actualizar']);
$router->post('/servicios/actualizar', [ServicioController::class, 'actualizar']);
$router->post('/servicios/eliminar', [ServicioController::class, 'eliminar']);
// Ruta para acceder a la vista de cita







// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();