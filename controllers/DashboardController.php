<?php

namespace Controllers;

use Classes\Email;
use Model\Proyecto;
use Model\Usuario;
use MVC\Router;

class DashboardController {
    public static function index(Router $router){
        session_start();
        isAuth();

        $id = $_SESSION['id'];

        $proyectos = Proyecto::belongsTo('propietarioId', $id);
        

        $router->render('/dashboard/index', [
            'titulo' => 'Proyectos', 
            'proyectos' => $proyectos
        ]);

    }

    public static function crear_proyecto(Router $router){
        session_start();
        isAuth();

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyecto = new Proyecto($_POST);

            //Validación
            $alertas = $proyecto->validarProyecto();
            if(empty($alertas)) {
                // Generar una URL única
                $proyecto->url = md5(uniqid());

                // Almacenar el creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];
                
                // Guardar el proyecto
                $proyecto->guardar();

                // Redireccionar
                header('Location: /proyecto?id=' . $proyecto->url);
            }
        }

        $router->render('/dashboard/crear-proyecto', [
            'titulo' => 'Crear Proyecto', 
            'alertas' => $alertas
        ]);

    }
    
    public static function proyecto(Router $router) {
        session_start();
        isAuth();

        // Revisar que la persona que visita el proyecto es quien lo creo
        $token = s($_GET['id']);
        if(!$token) {
            header('Location: /dashboard');
        }

        $proyecto = Proyecto::where('url', $token);
        if($proyecto->propietarioId !== $_SESSION['id']) {
            header('Location: /dashboard');
        }
        


        $router->render('/dashboard/proyecto', [
            'titulo' => $proyecto->proyecto
        ]);
    }

    public static function perfil(Router $router){
        session_start();
        isAuth();

        $alertas = [];
        $usuario = Usuario::find($_SESSION['id']);
        

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Compruebo si existe un email por confirmar
            $PreEmailTemp = $usuario->emailTemp;

            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarPerfil();

            if(empty($alertas)){
                // Verificar que el email no exista
                $existeusuario = Usuario::where('email', $usuario->emailTemp);
                if($existeusuario && $existeusuario->id !== $usuario->id) {
                    // Mensaje de error
                    Usuario::setAlerta('error', 'Email no valido, la cuenta ya existe');
                     
                } else {
                    if($usuario->email === $usuario->emailTemp && empty($PreEmailTemp)) {
                        // No he cambiado el email
                        $usuario->emailTemp = '';
                        $usuario->guardar();
                        Usuario::setAlerta('exito', 'Guardado Correctamente');
                        
                    }  elseif($usuario->email === $usuario->emailTemp) {
                        // He cambiado el email anteriormente
                        $usuario->emailTemp = $PreEmailTemp;
                        $usuario->guardar();
                        Usuario::setAlerta('exito', "Guardado correctamente, el email $PreEmailTemp está pendiente de confirmar");
                    } else {
                        // He cambiado el email

                        // Crear token
                        $usuario->crearToken();

                        // Guardo Usuario
                        $usuario->guardar();
                        Usuario::setAlerta('exito', "Te hemos enviado las intrucciones a: " . $_POST['emailTemp'] . " para que confirmes tu nuevo email, hasta entonces debes usar el anterior");
                        
                        $email = new Email($usuario->emailTemp, $usuario->nombre, $usuario->token);
                        $email->enviarCambioEmail();
                    }

                    // Asginar los datos a la sesión
                    $_SESSION['nombre'] = $usuario->nombre;        
  
                }

                
            }

        }

        $alertas = Usuario::getAlertas();

        $router->render('/dashboard/perfil', [
            'titulo' => 'Perfil', 
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);

    }

    public static function cambioEmail(Router $router){
        $alertas = [];
        $token = s($_GET['token']);
        if(!$token){
            header('Location: /');
        }

        $usuario = Usuario::where('token', $token);
        if(!$usuario) {
            Usuario::setAlerta('error', 'El token es incorrecto o no existe');
        } else {
            $usuario->email = $usuario->emailTemp;
            $usuario->emailTemp = '';
            $usuario->token = '';
            
            $resultado = $usuario->guardar();

            if($resultado) {
                Usuario::setAlerta('exito', 'Tu nuevo email ' . $usuario->email . ' ha sido confirmado');
            } else {
                Usuario::setAlerta('error', 'Error al guardar pruebe más tarde');
            }
        }



        $alertas = Usuario::getAlertas();
        $router->render('/dashboard/cambio-email', [
            'titulo' => 'Confirmar cambio email',
            'alertas' => $alertas
        ]);
    }

    public static function cambiar_password(Router $router){
        session_start();
        isAuth();

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = Usuario::find($_SESSION['id']);
            //Sincronizarlo con el objeto
            $usuario->sincronizar($_POST);

            $alertas = $usuario->nuevo_password();

            if(empty($alertas)) {
                $resultado = $usuario->comprobar_password();
                if($resultado){

                    $usuario->password = $usuario->password_nuevo;
                    // Eliminar propiedades no necesarias
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);
                    
                    // Hashear el nuevo password
                    $usuario->hashpassword();

                    // Actualizar
                    $resultado = $usuario->guardar();

                    if($resultado){
                        Usuario::setAlerta('exito', 'Contraseña actualizada correctamente');
                        $alertas = Usuario::getAlertas();
                    }                    

                } else {
                    Usuario::setAlerta('error', 'Contraseña incorrecta');
                    $alertas = Usuario::getAlertas();
                }
            }

        }

        

        $router->render('dashboard/cambiar-password', [
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas
        ]);
    }

}