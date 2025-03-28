<?php

namespace Model;

class Usuario extends ActiveRecord {
    // Base de datos
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'apellido', 'email', 'password', 'telefono', 'admin', 'confirmado', 'token', 'suscripcion'];

    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $password;
    public $telefono;
    public $admin;
    public $confirmado;
    public $token;
    public $suscripcion;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->telefono = $args['telefono'] ?? null ;
        $this->admin = $args['admin'] ?? '0';
        $this->confirmado = $args['confirmado'] ?? '0';
        $this->token = $args['token'] ?? '';
        $this->suscripcion = $args['suscripcion'] ?? '0';

        
    }



    public static function contarClientes() {
        $consulta = "SELECT COUNT(*) AS totalClientes FROM usuarios WHERE admin = 1";
        $resultado = self::consultarSQL($consulta);
    
        // Verificamos si el resultado es válido
        if ($resultado && isset($resultado[0]->totalClientes)) {
            return $resultado[0]->totalClientes;
        } else {
            // Si no hay resultados o no se puede acceder a 'totalClientes', devolvemos 0
            return 0;
        }
    }
    
    
    public static function contarSuscripciones() {
        $consulta = "SELECT COUNT(*) AS totalSuscripciones FROM usuarios WHERE suscripcion = 1";
        $resultado = self::consultarSQL($consulta);
        return $resultado ? $resultado[0]->totalSuscripciones : 0;
    }
    
    public function validarUsuario() {
        self::$alertas = [];
    
        if (!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio. Recibido: ' . var_export($this->nombre, true);
        }
        if (!$this->apellido) {
            self::$alertas['error'][] = 'El apellido es obligatorio. Recibido: ' . var_export($this->apellido, true);
        }
        if (!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio. Recibido: ' . var_export($this->email, true);;
        } 
        
    
        return self::$alertas;
    }
    public static function findByEmail($email) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE email = ? LIMIT 1";  // Usa el símbolo de pregunta "?" para los parámetros
    
        // Prepara la consulta
        $stmt = self::$db->prepare($query);
    
        // Enlaza el parámetro
        $stmt->bind_param('s', $email);  // 's' para indicar que es una cadena de texto
    
        // Ejecuta la consulta
        $stmt->execute();
    
        // Obtiene el resultado
        $resultado = $stmt->get_result()->fetch_assoc();
    
        if ($resultado) {
            return new self($resultado);  // Devuelve el objeto Usuario
        }
    
        return null;  // Si no hay usuario, retorna null
    }
    

    // Método para obtener todos los usuarios suscritos
    public static function ALLSuscripciones() {
        $consulta = "SELECT * FROM usuarios WHERE suscripcion = 1"; // Usuarios suscritos
        return self::SQL($consulta);
    }


    public function validar() {
        if (!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }
        if (!$this->apellido) {
            self::$alertas['error'][] = 'El apellido es obligatorio';
        }
        return self::$alertas;
    }


    // Mensajes de validación para la creación de una cuenta
    public function validarNuevaCuenta() {
      
        return self::$alertas;
    }

    public function validarLogin() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es Obligatorio';
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'La contraseña es Obligatoria';
        }

        return self::$alertas;
    }
    public function validarEmail() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es Obligatorio';
        }
        return self::$alertas;
    }

    public function validarPassword() {
        if(!$this->password) {
            self::$alertas['error'][] = 'La contraseña es obligatoria';
        }
        if(strlen($this->password) < 6) {
            self::$alertas['error'][] = 'La contraseña debe tener al menos 6 caracteres';
        }

        return self::$alertas;
    }

    public function existeUsuario() {
        // Revisar si el usuario existe con el mismo email
        $query = "SELECT * FROM " . self::$tabla . " WHERE email = '" . $this->email . "' LIMIT 1";
    
        $resultado = self::$db->query($query);
    
        // Si el resultado tiene registros
        if ($resultado->num_rows) {
            // Obtener los datos del usuario
            $usuario = $resultado->fetch_object();
    
            // Si el usuario existe, retornar el objeto con todos los datos
            return $usuario;
        }
    
        // Si no existe, retornar null
        return null;
    }
    
    public function hashPassword() {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function crearToken() {
        $this->token = uniqid();
    }

    public function comprobarPasswordAndVerificado($password) {
        if (!$this->confirmado) {
            self::$alertas['error'][] = 'Tu cuenta no ha sido confirmada';
            return false;
        }
    
        if (!password_verify($password, $this->password)) {
            self::$alertas['error'][] = 'Contraseña incorrecta';
            return false;
        }
    
        return true;
    }
    




    
}