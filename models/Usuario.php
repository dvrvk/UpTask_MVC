<?php 

namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'email', 'password', 'token', 'confirmado', 'emailTemp'];

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->password_actual = $args['password_actual'] ?? '';
        $this->password_nuevo = $args['password_nuevo'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
        $this->emailTemp = $args['emailTemp'] ?? '';
    }

    // Validar el login de usuarios
    public function validarLogin() {
        
        self::validarEmail();

        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }

        return self::$alertas;
    }

    // Validación para cuentas nuevas
    public function validarNuevaCuenta() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }

        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }

        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        } elseif (strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
        } elseif($this->password !== $this->password2){
            self::$alertas['error'][] = 'Los password son diferentes';
        }

        return self::$alertas;
    }

    // Valida un email
    public function validarEmail() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El campo email es obligatorio';
        } elseif(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no valido';
        }

        return self::$alertas;
    }

    // Valida el password 
    public function validarPassword() {
        
        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        } elseif (strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
        } elseif($this->password !== $this->password2){
            self::$alertas['error'][] = 'Los password no pueden ser diferentes';
        }

        return self::$alertas;
    }

    // Validar perfil usuario
    public function validarPerfil() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }

        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }

        return self::$alertas;
    }

    // Validar nuevo password
    public function nuevo_password() : array {
        if(!$this->password_actual) {
            self::$alertas['error'][] = 'El password actual es obligatorio'; 
        }

        if(!$this->password_nuevo) {
            self::$alertas['error'][] = 'El password nuevo es obligatorio'; 
        } elseif(strlen($this->password_nuevo) < 6) {
            self::$alertas['error'][] = 'El password nuevo debe tener al menos 6 caracteres'; 
        }

        return self::$alertas;
    }

    // Comprobar password
    public function comprobar_password() : bool {
        return password_verify($this->password_actual, $this->password);
    }


    // Hashea el password
    public function hashPassword() : void {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    // Generer un token
    public function crearToken() : void{
        $this->token = md5(uniqid()); //uniqid();
    }

    


    
 }