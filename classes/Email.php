<?php
namespace Classes;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class Email{

    public $email;
    public $nombre;
    public $token;

    public function __construct($nombre=null, $email, $token = null){
        $this->email=$email;
        $this->nombre=$nombre;
        $this->token=$token;
    }
    
    public function enviarConfirmacion(){
        // Crear el objeto de email
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;        
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS']; 
        $mail->SMTPSecure = 'tls'; // Usa TLS
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->CharSet = 'UTF-8';
    
        $mail->setFrom('bucicardi05@gmail.com', 'Barberia');
        $mail->addAddress($this->email);
        $mail->Subject = 'Confirma tu Cuenta';
        
        // Configurar el contenido HTML
        $mail->isHTML(true);
        $contenido = "<html>
                        <head>
                            <style>
                                body{font-family:'Arial',sans-serif;background-color:#f7f7f7;color:#333;margin:0;padding:0}.container{width:100%;max-width:600px;margin:30px auto;background-color:#fff;padding:30px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.1)}.header{text-align:center;margin-bottom:20px}.header h1{color:#4CAF50;font-size:24px}.content{font-size:16px;line-height:1.6;color:#555}.btn{display:inline-block;padding:12px 24px;margin-top:20px;background-color:#4CAF50;color:white;text-decoration:none;border-radius:5px;font-weight:bold;text-align:center}.footer{margin-top:30px;font-size:12px;text-align:center;color:#888}
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>Barberia</h1>
                                </div>
                                <div class='content'>
                                    <p><strong>Hola, " . $this->nombre . "</strong></p>
                                    <p>Has creado tu cuenta en <strong>Barberia</strong>. Para completar tu registro, solo debes confirmar tu cuenta presionando el siguiente enlace:</p>
                                    <p><a href='" . $_ENV['APP_URL'] . "/confirmar-cuenta?token=" . $this->token . "' class='btn'>Confirmar Cuenta</a></p>
                                    <p>Si no solicitaste esta cuenta, puedes ignorar este mensaje.</p>
                                </div>
                                <div class='footer'>
                                    <p>&copy; " . date("Y") . " Barberia. Todos los derechos reservados.</p>
                                </div>
                            </div>
                        </body>
                      </html>";
    
        $mail->Body = $contenido;
    
        $mail->send();
    }
    public function enviarInstrucciones() {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls'; // Usa TLS
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->CharSet = 'UTF-8';
        
        // Establecer el remitente y el destinatario
        $mail->setFrom('bucicardi05@gmail.com', 'Barberia');
        $mail->addAddress($this->email);
        $mail->Subject = 'Restablecer Contraseña';
        
        // Contenido HTML del correo
        $mail->isHTML(true);
        $contenido = "<html>
                        <head>
                            <style>
                                body{font-family:'Arial',sans-serif;background-color:#f7f7f7;color:#333;margin:0;padding:0}.container{width:100%;max-width:600px;margin:30px auto;background-color:#fff;padding:30px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.1)}.header{text-align:center;margin-bottom:20px}.header h1{color:#4CAF50;font-size:24px}.content{font-size:16px;line-height:1.6;color:#555}.btn{display:inline-block;padding:12px 24px;margin-top:20px;background-color:#4CAF50;color:white;text-decoration:none;border-radius:5px;font-weight:bold;text-align:center}.footer{margin-top:30px;font-size:12px;text-align:center;color:#888}
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>Barberia</h1>
                                </div>
                                <div class='content'>
                                    <p><strong>Hola, " . $this->nombre . "</strong>, has solicitado restablecer tu contraseña. Sigue el siguiente enlace para hacerlo:</p>
                                    <p><a href='" . $_ENV['APP_URL'] . "/recuperar?token=" . $this->token . "' class='btn'>Restablecer Contraseña</a></p>
                                    <p>Si tú no solicitaste este cambio, puedes ignorar el mensaje.</p>
                                </div>
                                <div class='footer'>
                                    <p>&copy; " . date("Y") . " Barberia. Todos los derechos reservados.</p>
                                </div>
                            </div>
                        </body>
                      </html>";
        
        // Establecer el cuerpo del correo
        $mail->Body = $contenido;
        
        // Intentar enviar el correo
        try {
            $mail->send();
        } catch (Exception $e) {
            error_log('Error al enviar el correo: ' . $mail->ErrorInfo);
        }
    }
    
    public function enviarAvisoServicio($usuarioId, $nombre, $apellido, $servicioAntiguoNombre, $servicioNuevoNombre) {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->CharSet = 'UTF-8';
        
        // Configuración del remitente y destinatario
        $mail->setFrom('bucicardi05@gmail.com', 'Barberia');
        $mail->addAddress($_ENV['EMAIL_USER']); // Enviar al administrador
        
        // Asunto del correo
        $mail->Subject = 'Notificación de Cambio de Servicio en Barberia';
        
        // Contenido del mensaje en HTML
        $mail->isHTML(true);
        $contenido = "<html>
                        <head>
                            <style>
                                body{font-family:'Arial',sans-serif;background-color:#f7f7f7;color:#333;margin:0;padding:0}.container{width:100%;max-width:600px;margin:30px auto;background-color:#fff;padding:30px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.1)}.header{text-align:center;margin-bottom:20px}.header h1{color:#4CAF50;font-size:24px}.content{font-size:16px;line-height:1.6;color:#555}.footer{margin-top:30px;font-size:12px;text-align:center;color:#888}
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>Barberia</h1>
                                </div>
                                <div class='content'>
                                    <p><strong>Notificación de Cambio de Servicio</strong></p>
                                    <p>El cliente <strong>{$nombre} {$apellido}</strong> (ID: {$usuarioId}) ha decidido cambiar su servicio.</p>
                                    <p><strong>Servicio anterior:</strong> {$servicioAntiguoNombre}</p>
                                    <p><strong>Servicio nuevo:</strong> {$servicioNuevoNombre}</p>
                                    <p>Atentamente, Barberia.</p>
                                </div>
                                <div class='footer'>
                                    <p>&copy; " . date("Y") . " Barberia. Todos los derechos reservados.</p>
                                </div>
                            </div>
                        </body>
                      </html>";
        
        // Asignar el contenido al cuerpo del correo
        $mail->Body = $contenido;
        
        // Enviar el correo
        try {
            $mail->send();
            echo "Correo de aviso enviado al administrador.";
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    }
    public function enviarAvisoCambioFecha($usuarioId, $nombre, $apellido, $fechaAntigua, $fechaNueva, $horaNueva) {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->CharSet = 'UTF-8';
        
        // Configuración del remitente y destinatario
        $mail->setFrom('bucicardi05@gmail.com', 'Barberia');
        $mail->addAddress($_ENV['EMAIL_USER']); // Enviar al administrador
        
        // Asunto del correo
        $mail->Subject = 'Notificación de Cambio de Fecha en Barberia';
        
        // Contenido del mensaje en HTML
        $mail->isHTML(true);
        $contenido = "<html>
                        <head>
                            <style>
                                body{font-family:'Arial',sans-serif;background-color:#f7f7f7;color:#333;margin:0;padding:0}.container{width:100%;max-width:600px;margin:30px auto;background-color:#fff;padding:30px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.1)}.header{text-align:center;margin-bottom:20px}.header h1{color:#4CAF50;font-size:24px}.content{font-size:16px;line-height:1.6;color:#555}.footer{margin-top:30px;font-size:12px;text-align:center;color:#888}
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>Barberia</h1>
                                </div>
                                <div class='content'>
                                    <p><strong>Notificación de Cambio de Fecha de Cita</strong></p>
                                    <p>El cliente <strong>{$nombre} {$apellido}</strong> (ID: {$usuarioId}) ha realizado un cambio en la fecha de su cita.</p>
                                    <p><strong>Fecha anterior:</strong> {$fechaAntigua}</p>
                                    <p><strong>Nueva fecha:</strong> {$fechaNueva} a las {$horaNueva}</p>
                                    <p>Atentamente, Barberia.</p>
                                </div>
                                <div class='footer'>
                                    <p>&copy; " . date("Y") . " Barberia. Todos los derechos reservados.</p>
                                </div>
                            </div>
                        </body>
                      </html>";
        
        // Asignar el contenido al cuerpo del correo
        $mail->Body = $contenido;
        
        // Enviar el correo
        try {
            $mail->send();
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    }
    public function enviarAvisoNuevoServicio($usuarioId, $nombre, $apellido, $servicioNombre, $servicioDescripcion, $servicioPrecio) {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->CharSet = 'UTF-8';
        
        // Configuración del remitente y destinatario
        $mail->setFrom('bucicardi05@gmail.com', 'Barberia');
        $mail->addAddress($_ENV['EMAIL_USER']); // Enviar al administrador
        
        // Asunto del correo
        $mail->Subject = 'Nuevo Servicio Disponible en Barberia';
        
        // Contenido del mensaje en HTML
        $mail->isHTML(true);
        $contenido = "<html>
                        <head>
                            <style>
                                body{font-family:'Arial',sans-serif;background-color:#f7f7f7;color:#333;margin:0;padding:0}
                                .container{width:100%;max-width:600px;margin:30px auto;background-color:#fff;padding:30px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.1)}
                                .header{text-align:center;margin-bottom:20px}
                                .header h1{color:#4CAF50;font-size:24px}
                                .content{font-size:16px;line-height:1.6;color:#555}
                                .footer{margin-top:30px;font-size:12px;text-align:center;color:#888}
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>Barberia</h1>
                                </div>
                                <div class='content'>
                                    <p><strong>Nuevo Servicio Disponible en Barberia</strong></p>
                                    <p>Hola <strong>{$nombre} {$apellido}</strong>,</p>
                                    <p>Te informamos que se ha añadido un nuevo servicio en Barberia:</p>
                                    <p><strong>Nombre del Servicio:</strong> {$servicioNombre}</p>
                                    <p><strong>Descripción:</strong> {$servicioDescripcion}</p>
                                    <p><strong>Precio:</strong> {$servicioPrecio}</p>
                                    <p>Atentamente, Barberia.</p>
                                </div>
                                <div class='footer'>
                                    <p>&copy; " . date("Y") . " Barberia. Todos los derechos reservados.</p>
                                </div>
                            </div>
                        </body>
                      </html>";
        
        // Asignar el contenido al cuerpo del correo
        $mail->Body = $contenido;
        
        // Enviar el correo
        try {
            $mail->send();
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    }
    
    public function servicioAnulado($usuarioId, $nombre, $apellido) {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->CharSet = 'UTF-8';
    
        // Configuración del remitente y destinatario
        $mail->setFrom('bucicardi05@gmail.com', 'Barberia');
        $mail->addAddress($_ENV['EMAIL_USER']); // Enviar al administrador
    
        // Asunto del correo
        $mail->Subject = 'Notificación de Turno Anulado en Barberia';
    
        // Contenido del mensaje en HTML
        $mail->isHTML(true);
        $contenido = "<html>
                        <head>
                            <style>
                                body{font-family:'Arial',sans-serif;background-color:#f7f7f7;color:#333;margin:0;padding:0}
                                .container{width:100%;max-width:600px;margin:30px auto;background-color:#fff;padding:30px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.1)}
                                .header{text-align:center;margin-bottom:20px}
                                .header h1{color:#4CAF50;font-size:24px}
                                .content{font-size:16px;line-height:1.6;color:#555}
                                .footer{margin-top:30px;font-size:12px;text-align:center;color:#888}
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>Barberia</h1>
                                </div>
                                <div class='content'>
                                    <p><strong>Notificación de Turno Anulado</strong></p>
                                    <p>El cliente <strong>{$nombre} {$apellido}</strong> (ID: {$usuarioId}) ha decidido anular su cita.</p>
                                    <p>Atentamente, Barberia.</p>
                                </div>
                                <div class='footer'>
                                    <p>&copy; " . date("Y") . " Barberia. Todos los derechos reservados.</p>
                                </div>
                            </div>
                        </body>
                      </html>";
    
        // Asignar el contenido al cuerpo del correo
        $mail->Body = $contenido;
    
        // Enviar el correo
        try {
            $mail->send();
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    }
    
    public function turnoAnulado($usuarioId, $nombre, $apellido) {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->CharSet = 'UTF-8';
    
        // Configuración del remitente y destinatario
        $mail->setFrom('bucicardi05@gmail.com', 'Barberia');
        $mail->addAddress($_ENV['EMAIL_USER']); // Enviar al administrador
    
        // Asunto del correo
        $mail->Subject = 'Notificación de Turno Anulado en Barberia';
    
        // Contenido del mensaje en HTML
        $mail->isHTML(true);
        $contenido = "<html>
                        <head>
                            <style>
                                body{font-family:'Arial',sans-serif;background-color:#f7f7f7;color:#333;margin:0;padding:0}
                                .container{width:100%;max-width:600px;margin:30px auto;background-color:#fff;padding:30px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.1)}
                                .header{text-align:center;margin-bottom:20px}
                                .header h1{color:#4CAF50;font-size:24px}
                                .content{font-size:16px;line-height:1.6;color:#555}
                                .footer{margin-top:30px;font-size:12px;text-align:center;color:#888}
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>Barberia</h1>
                                </div>
                                <div class='content'>
                                    <p><strong>Notificación de Turno Anulado</strong></p>
                                    <p>El cliente <strong>{$nombre} {$apellido}</strong> (ID: {$usuarioId}) ha decidido anular su turno.</p>
                                    <p>Atentamente, Barberia.</p>
                                </div>
                                <div class='footer'>
                                    <p>&copy; " . date("Y") . " Barberia. Todos los derechos reservados.</p>
                                </div>
                            </div>
                        </body>
                      </html>";
    
        // Asignar el contenido al cuerpo del correo
        $mail->Body = $contenido;
    
        // Enviar el correo
        try {
            $mail->send();
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    }
    

    public function enviarAvisoCambioFechaCliente($usuarioId, $nombre, $apellido, $fechaAntigua, $fechaNueva, $horaAntigua, $horaNueva, $horaFinalAntigua, $horaFinalNueva, $observacion) {
        $mail = new PHPMailer(true);
    
        try {
            // Usar variables de entorno con fallback a valores por defecto
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = getenv('EMAIL_HOST') ?: $_ENV['EMAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = getenv('EMAIL_USER') ?: $_ENV['EMAIL_USER']; // Correo del administrador
            $mail->Password = getenv('EMAIL_PASS') ?: $_ENV['EMAIL_PASS']; // Contraseña de la cuenta
            $mail->SMTPSecure = 'tls';
            $mail->Port = getenv('EMAIL_PORT') ?: $_ENV['EMAIL_PORT'];
            $mail->CharSet = 'UTF-8';
    
            // Configuración del remitente y destinatario
            $mail->setFrom($mail->Username, 'Barberia'); // Usa el mismo correo que en `Username`
            $mail->addAddress($this->email); // Enviar al correo del cliente
    
            // Asunto del correo
            $mail->Subject = 'Notificación de Cambio en Tu Cita en Barberia';
    
            // Contenido del mensaje en HTML
            $contenido = "<html>
                <body>
                    <p><strong>Estimado/a {$nombre} {$apellido},</strong></p>
                    <p>Te informamos que tu cita programada en <strong>Barberia</strong> ha sido reprogramada.</p>
                    <p><strong>Fecha Original:</strong> {$fechaAntigua}</p>
                    <p><strong>Nueva Fecha:</strong> {$fechaNueva}</p>
                    <p><strong>Hora Original:</strong> {$horaAntigua} - {$horaFinalAntigua}</p>
                    <p><strong>Nueva Hora:</strong> {$horaNueva} - {$horaFinalNueva}</p>
                    <p><strong>Observación del Administrador:</strong> {$observacion}</p>
                    <p>Si tienes alguna consulta, no dudes en contactarnos.</p>
                    <p>Atentamente,<br> <strong>Barberia</strong></p>
                </body>
            </html>";
    
            // Asignar el contenido al cuerpo del correo
            $mail->isHTML(true);
            $mail->Body = $contenido;
            $mail->AltBody = "Estimado/a {$nombre} {$apellido}, tu cita en Barberia ha sido reprogramada.
            Fecha Original: {$fechaAntigua}, Nueva Fecha: {$fechaNueva}.
            Hora Original: {$horaAntigua} - {$horaFinalAntigua}, Nueva Hora: {$horaNueva} - {$horaFinalNueva}.
            Observación del Administrador: {$observacion}.";
    
            // Enviar el correo
            $mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar el correo: {$mail->ErrorInfo}");
        }
    }
    
    public function enviarAvisoAnulacionCitaCliente($usuarioId, $nombre, $apellido, $fecha, $hora, $observacion) {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = getenv('EMAIL_HOST') ?: $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = getenv('EMAIL_USER') ?: $_ENV['EMAIL_USER']; // Correo del administrador
        $mail->Password = getenv('EMAIL_PASS') ?: $_ENV['EMAIL_PASS']; // Contraseña de la cuenta
        $mail->SMTPSecure = 'tls';
        $mail->Port = getenv('EMAIL_PORT') ?: $_ENV['EMAIL_PORT'];
        $mail->CharSet = 'UTF-8';
    
        // Configuración del remitente y destinatario
        $mail->setFrom($mail->Username, 'Barberia'); // Usa el mismo correo que en `Username`
        $mail->addAddress($this->email); // Enviar al correo del cliente
    
        // Asunto del correo
        $mail->Subject = 'Notificación de Anulación de Tu Cita en Barberia';
    
        // Contenido del mensaje en HTML
        $contenido = "<html>";
        $contenido .= "<p><strong>Estimado/a {$nombre} {$apellido},</strong></p>";
        $contenido .= "<p>Lamentamos informarte que tu cita programada en Barberia ha sido anulada.</p>";
        $contenido .= "<p><strong>Fecha de Cita:</strong> {$fecha}</p>";
        $contenido .= "<p><strong>Hora de Cita:</strong> {$hora}</p>";
        $contenido .= "<p><strong>Observación del Administrador:</strong> {$observacion}</p>";
        $contenido .= "<p>Si tienes alguna pregunta o deseas reagendar, por favor contáctanos.</p>";
        $contenido .= "<p>Atentamente, Barberia.</p>";
        $contenido .= "</html>";
    
        // Asignar el contenido al cuerpo del correo
        $mail->Body = $contenido;
        $mail->isHTML(true);
    
        // Enviar el correo
        try {
            $mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar el correo: {$mail->ErrorInfo}");
        }
    }
    
        public function enviarAvisoCitaCliente($usuarioId, $nombre, $apellido, $fecha, $hora, $servicios)
        {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = $_ENV['EMAIL_HOST'] ;
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['EMAIL_USER'] ; // Correo del administrador
            $mail->Password = $_ENV['EMAIL_PASS'] ; // Contraseña de la cuenta
            $mail->SMTPSecure = 'tls';
            $mail->Port = $_ENV['EMAIL_PORT'] ;
            $mail->CharSet = 'UTF-8';
        
            // Configuración del remitente y destinatario
            $mail->setFrom('bucicardi05@gmail.com', 'Barberia');
            $mail->addAddress($this->email); // Enviar al correo del cliente
        
            // Asunto del correo
            $mail->Subject = 'Confirmación de tu Cita en Barberia';
        
            // Contenido del mensaje en HTML
            $contenido = "<html>";
            $contenido .= "<p><strong>Estimado/a {$nombre} {$apellido},</strong></p>";
            $contenido .= "<p>Te confirmamos que tu cita ha sido registrada exitosamente en Barberia.</p>";
            $contenido .= "<p><strong>Fecha de Cita:</strong> {$fecha}</p>";
            $contenido .= "<p><strong>Hora de Cita:</strong> {$hora}</p>";
            $contenido .= "<p><strong>Servicios Seleccionados:</strong> {$servicios}</p>";
            $contenido .= "<p>Si tienes alguna pregunta o deseas reagendar, por favor contáctanos.</p>";
            $contenido .= "<p>Atentamente, Barberia.</p>";
            $contenido .= "</html>";
        
            // Asignar el contenido al cuerpo del correo
            $mail->Body = $contenido;
            $mail->isHTML(true);
        
            // Enviar el correo
            try {
                $mail->send();
            } catch (Exception $e) {
                echo "Error al enviar el correo: {$mail->ErrorInfo}";
            }
        }
        



        public function enviarMensajeContacto($nombre, $correo, $asunto, $mensaje)
        {
            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor SMTP
                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host = $_ENV['EMAIL_HOST'] ;
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['EMAIL_USER'] ; // Correo del administrador
                $mail->Password = $_ENV['EMAIL_PASS'] ; // Contraseña de la cuenta
                $mail->SMTPSecure = 'tls';
                $mail->Port = $_ENV['EMAIL_PORT'] ;
                $mail->CharSet = 'UTF-8';
        
                // Configuración del remitente y destinatario
                $mail->setFrom($correo, $nombre); // El remitente es el usuario
                $mail->addAddress($_ENV['EMAIL_USER'] , 'Administrador'); // Destinatario del mensaje
        
                // Asunto del correo
                $mail->Subject = "Nuevo mensaje de contacto: {$asunto}";
        
                // Contenido del mensaje en HTML
                $contenido = "<html>";
                $contenido .= "<p><strong>Nombre:</strong> {$nombre}</p>";
                $contenido .= "<p><strong>Correo:</strong> {$correo}</p>";
                $contenido .= "<p><strong>Asunto:</strong> {$asunto}</p>";
                $contenido .= "<p><strong>Mensaje:</strong></p>";
                $contenido .= "<p>{$mensaje}</p>";
                $contenido .= "</html>";
        
                // Asignar el contenido al cuerpo del correo
                $mail->Body = $contenido;
                $mail->isHTML(true);
        
                // Enviar el correo
                $mail->send();
            } catch (Exception $e) {
                echo "Error al enviar el correo: {$mail->ErrorInfo}";
            }
        }
        


        public function enviarMensajeContactoCliente($nombre, $correo)
{
    $mail = new PHPMailer(true);
    try {
        // Configuración del servidor SMTP
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER']; // Correo del administrador
        $mail->Password = $_ENV['EMAIL_PASS']; // Contraseña de la cuenta
        $mail->SMTPSecure = 'tls';
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->CharSet = 'UTF-8';
    
        // Configuración del remitente y destinatario
        $mail->setFrom($_ENV['EMAIL_USER'], 'Administrador');
        $mail->addAddress($correo, $nombre); // El cliente recibirá el correo
    
        // Asunto del correo
        $mail->Subject = "Tu consulta ha sido recibida";
    
        // Contenido del mensaje en HTML
        $contenido = "<html>";
        $contenido .= "<p>Hola <strong>{$nombre}</strong>,</p>";
        $contenido .= "<p>Gracias por ponerte en contacto con nosotros. Tu consulta ha sido recibida y será atendida por uno de nuestros administradores.</p>";
        $contenido .= "<p>En breve nos pondremos en contacto contigo para resolver cualquier duda que tengas.</p>";
        $contenido .= "<p>Saludos,</p>";
        $contenido .= "<p>El equipo de barberia.</p>";
        $contenido .= "</html>";
    
        // Asignar el contenido al cuerpo del correo
        $mail->Body = $contenido;
        $mail->isHTML(true);
    
        // Enviar el correo
        $mail->send();
    } catch (Exception $e) {
        echo "Error al enviar el correo: {$mail->ErrorInfo}";
    }
}



        public function enviarConfirmacionSuscripcion($correo)
        {
            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor SMTP
                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host = $_ENV['EMAIL_HOST'] ;
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['EMAIL_USER'] ; // Correo del administrador
                $mail->Password = $_ENV['EMAIL_PASS'] ; // Contraseña de la cuenta
                $mail->SMTPSecure = 'tls';
                $mail->Port = $_ENV['EMAIL_PORT'] ;
                $mail->CharSet = 'UTF-8';
        
                // Configuración del remitente y destinatario
                $mail->setFrom('noreply@barberia.com', 'Barberia'); // El remitente puede ser un correo genérico
                $mail->addAddress($correo); // El destinatario es el correo del usuario
        
                // Asunto del correo
                    $mail->Subject = "🎉 ¡Gracias por suscribirte! 🎉";

                    // Contenido del mensaje en HTML
                    $contenido = "<html>";
                    $contenido .= "<p style='font-size: 18px; color: #333;'>¡Hola <strong>[Nombre]</strong>! 👋</p>";
                    $contenido .= "<p style='font-size: 16px; color: #555;'>¡Gracias por unirte a nuestra comunidad! 🎉 Estamos emocionados de tenerte con nosotros.</p>";
                    $contenido .= "<p style='font-size: 16px; color: #555;'>¿Qué puedes esperar de nuestra suscripción? Aquí te contamos algunos beneficios:</p>";
                    $contenido .= "<ul style='font-size: 16px; color: #555; line-height: 1.6;'>";
                    $contenido .= "<li>✨ Te avisaremos sobre <strong>nuestros nuevos servicios</strong> para que no te pierdas nada.</li>";
                    $contenido .= "<li>🎁 Recibirás <strong>promociones especiales</strong> y ofertas exclusivas solo para suscriptores.</li>";
                    $contenido .= "<li>💸 Obtendrás <strong>descuentos exclusivos</strong> en algunos de nuestros servicios.</li>";
                    $contenido .= "<li>📰 Además, te mantendremos al tanto de las <strong>novedades</strong> y eventos importantes.</li>";
                    $contenido .= "</ul>";
                    $contenido .= "<p style='font-size: 16px; color: #555;'>Nos aseguraremos de que siempre estés informado de lo mejor que tenemos para ofrecerte. 😊</p>";
                    $contenido .= "<p style='font-size: 16px; color: #555;'>¡Gracias por ser parte de nuestra familia! ❤️</p>";
                    $contenido .= "<p style='font-size: 16px; color: #555;'>Con cariño, <br>El equipo de [Tu Empresa]</p>";
                    $contenido .= "</html>";

        
                // Asignar el contenido al cuerpo del correo
                $mail->Body = $contenido;
                $mail->isHTML(true);
        
                // Enviar el correo
                $mail->send();
            } catch (Exception $e) {
                echo "Error al enviar el correo: {$mail->ErrorInfo}";
            }
        }
        


        public function enviarRecordatorioCitaCliente($usuarioId, $nombre, $apellido, $fecha, $hora) {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = $_ENV['EMAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['EMAIL_USER'];
            $mail->Password = $_ENV['EMAIL_PASS'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = $_ENV['EMAIL_PORT'];
            $mail->CharSet = 'UTF-8';
        
            // Remitente y destinatario
            $mail->setFrom('bucicardi05@gmail.com', 'Barberia');
            $mail->addAddress($this->email, "{$nombre} {$apellido}"); // Se envía al cliente
        
            // Asunto del correo
            $mail->Subject = 'Recordatorio de Cita - Barberia';
        
            // Contenido en HTML
            $contenido = "<html>";
            $contenido .= "<p><strong>Hola {$nombre} {$apellido},</strong></p>";
            $contenido .= "<p>Te recordamos que tienes una cita agendada en Barberia.</p>";
            $contenido .= "<p><strong>Fecha:</strong> {$fecha}</p>";
            $contenido .= "<p><strong>Hora:</strong> {$hora}</p>";
            $contenido .= "<p>¡Te esperamos!</p>";
            $contenido .= "<p>Atentamente, Barberia.</p>";
            $contenido .= "</html>";
        
            // Configuración del correo
            $mail->Body = $contenido;
            $mail->isHTML(true);
        
            // Enviar correo
            try {
                $mail->send();
            } catch (Exception $e) {
                echo "Error al enviar el correo: {$mail->ErrorInfo}";
            }
        }
        public function enviarAvisoImportante($usuarioId, $nombre, $apellido, $mensaje) {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $_ENV['EMAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['EMAIL_USER'];
            $mail->Password = $_ENV['EMAIL_PASS'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = $_ENV['EMAIL_PORT'];
            $mail->CharSet = 'UTF-8';
        
            // Verificar si $this->email es válido
            if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
               // error_log("Email inválido para usuario ID {$usuarioId}: {$this->email}");
                return false; // No intenta enviar si el email es inválido
            }
        
            // Configuración del remitente y destinatario
            $mail->setFrom($_ENV['EMAIL_USER'], 'Barberia');
            $mail->addAddress($this->email, "{$nombre} {$apellido}");
        
            // Asunto del correo
            $mail->Subject = 'Novedades de Barberia';
        
            // Contenido del correo
            $contenido = "<html>";
            $contenido .= "<p><strong>Hola {$nombre} {$apellido},</strong></p>";
            $contenido .= "<p>{$mensaje}</p>";
            $contenido .= "<p>¡Esperamos verte pronto en Barberia!</p>";
            $contenido .= "</html>";
        
            $mail->Body = $contenido;
            $mail->isHTML(true);
        
            try {
                return $mail->send();
            } catch (Exception $e) {
                error_log("Error al enviar el correo a {$this->email}: " . $mail->ErrorInfo);
                return false;
            }
        }
        
        

}
    