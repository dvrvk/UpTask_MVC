<?php 

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {
    public static function login(Router $router) {
        

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

        }

        // Render a la vista
        $router->render('/auth/login', [
            'titulo' => 'Iniciar Sesión'
        ]);
    }

    public static function logout() {
        echo "Desde logout";
    }

    public static function crear(Router $router) {

        $usuario = new Usuario();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();
            
            
            if(empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario) {
                    Usuario::setAlerta('error', 'Este usuario ya está registrado');
                } else {
                    // Hasear el password
                    $usuario->hashPassword();

                    // Eliminar password2
                    unset($usuario->password2); //Elimina el elemento - no necesario;

                    // Generar token
                    $usuario->crearToken();

                    // Crear un nuevo usuario
                    $resultado = $usuario->guardar();

                    // Enviar Email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();
                    
                    if($resultado) {
                        header('Location: /mensaje');
                    }

                    
                }

            }
            
        }

        $alertas = Usuario::getAlertas();
        // Render a la vista
        $router->render('/auth/crear', [
            'titulo' => 'Crear tu cuenta', 
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router) {
        

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

        }

        // Render a la vista
        $router->render('/auth/olvide', [
            'titulo' => 'Olvidé password'
        ]);
    }

    public static function restablecer(Router $router) {

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

        }

        // Render a la vista
        $router->render('/auth/restablecer', [
            'titulo' => 'Restablecer password'
        ]);
    }

    public static function mensaje(Router $router) {
        
        // Render a la vista
        $router->render('/auth/mensaje', [
            'titulo' => 'Cuenta Creada'
        ]);
    }

    public static function confirmar(Router $router) {
        $alertas = [];

        $token = s($_GET['token']);
        if(!$token || $token === 0 || $token === null) {
            header('Location: /');
        }

        //Encontrar al usuario con este token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            //No se encontro un usuario con ese token
            Usuario::setAlerta('error', 'Token no valido');
        } else {
            // Confirmar la cuenta
            $usuario->confirmado = 1;
            unset($usuario->password2);
            $usuario->token = '';

            // Guardar en la base de datos
            $usuario->guardar();

            // Mensaje exito
            Usuario::setAlerta('exito', 'Tu cuenta ha sido confirmada correctamente');
        }

        $alertas = Usuario::getAlertas();

        // Render a la vista
        $router->render('/auth/confirmar', [
            'titulo' => 'Confirmar cuenta', 
            'alertas' => $alertas
        ]);
    }
}