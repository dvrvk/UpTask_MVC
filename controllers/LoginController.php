<?php 

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {
    public static function login(Router $router) {
        
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);

            $alertas = $usuario->validarLogin();

            if(empty($alertas)) {
                // Verificar que el usuario exista
                $usuario = Usuario::where('email', $usuario->email);

                if(!$usuario || $usuario->confirmado === '0') {
                    Usuario::setAlerta('error', 'No existe el usuario o no está confirmada la cuenta');
                } else {
                    // El usuario existe
                    if(password_verify($_POST['password'], $usuario->password)) {
                        // Iniciar la sesión
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->nombre;
                        $_SESSION['login'] = true;
                        
                        // Redireccionar 
                        header('Location: /dashboard');
                    } else {
                        // Contraseña incorrecta
                        Usuario::setAlerta('error', 'Password incorrecto');
                    }
                }
                
            } 

        }

        $alertas = Usuario::getAlertas();
        // Render a la vista
        $router->render('/auth/login', [
            'titulo' => 'Iniciar Sesión', 
            'alertas' => $alertas
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
        
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)) {
                // Buscar el usuario
                $usuario = Usuario::where('email', $usuario->email);

                if($usuario && $usuario->confirmado === "1") {
                    // Generar un nuevo token
                    $usuario->crearToken();
                    unset($usuario->password2);

                    // Actualizar el usuario
                    $usuario->guardar();
                    // Enviar el email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarRecuperacion();

                    // Imprimir la alerta
                    Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');
                    
                } else {
                    // No encontro usuario
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                }
                
            }
        
        }

        $alertas = Usuario::getAlertas();

        // Render a la vista
        $router->render('/auth/olvide', [
            'titulo' => 'Olvidé password',
            'alertas' => $alertas
        ]);
    }

    public static function restablecer(Router $router) {

        $alertas = [];
        $token = s($_GET['token']);
        $mostrar = true;

        if(!$token) {
            header('Location: /');
        }

        // Identificar al usuario
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no válido');
            $mostrar = false;
        } 

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Añadir el nuevo password
            $usuario->sincronizar($_POST);

            // Validar el password
            $alertas = $usuario->validarPassword();
            
            if(empty($alertas)){
                // Hashear password
                $usuario->hashPassword();
                unset($usuario->password2);

                // Eliminar token
                $usuario->token = '';

                // Guardar usuario BD
                $resultado = $usuario->guardar();

                // Redireccionar
                if($resultado) {
                    header('Location: /');
                }
                
            }
        }

        $alertas = Usuario::getAlertas();
        // Render a la vista
        $router->render('/auth/restablecer', [
            'titulo' => 'Restablecer password',
            'alertas' => $alertas,
            'mostrar' => $mostrar
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