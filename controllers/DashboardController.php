<?php

namespace Controllers;

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
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarPerfil();

            if(empty($alertas)){
                // Verificar que el email no exista
                $existeusuario = Usuario::where('email', $usuario->email);
                if($existeusuario && $existeusuario->id !== $usuario->id) {
                    // Mensaje de error
                    Usuario::setAlerta('error', 'Email no valido, la cuenta ya existe');

                } else {
                    // Guardar el usuario
                    $usuario->guardar();

                    // Alerta exito
                    Usuario::setAlerta('exito', 'Guardado Correctamente');

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
}