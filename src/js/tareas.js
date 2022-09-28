(function(){ // Sirve para proteger las variables y que no se mezclen con otros archivos

    obtenerTareas();
    let tareas = [];

    // Boton para mostrar el Modal de Agregar tarea
    const nuevaTareaBtn = document.querySelector('#agregar-tarea');
    nuevaTareaBtn.addEventListener('click', mostrarFormulario);

    // Consultar los proyectos de la API
    async function obtenerTareas() {
        try {
            const id = obtenerProyecto();
            const url = `/api/tareas?id=${id}`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();
            
            tareas = resultado.tareas;
            mostrarTareas();

        } catch (error) {
            console.log(error);   
        }
    }

    // Función para mostrar las tareas del proyecto
    function mostrarTareas() {
        limpiarTareas();
        const contenedorTareas = document.querySelector('#listado-tareas');
        if(tareas.length === 0) {
            
            const textoNoTareas = document.createElement('LI');
            textoNoTareas.textContent = 'No hay tareas';
            textoNoTareas.classList.add('no-tareas');

            contenedorTareas.appendChild(textoNoTareas);
            return;
        }

        const estados = {
            0: 'Pendiente',
            1: 'Completa'
        }

        tareas.forEach(tarea => {
            const contenedorTarea = document.createElement('LI');
            contenedorTarea.dataset.tareaId = tarea.id;
            contenedorTarea.classList.add('tarea');

            const nombreTarea = document.createElement('P');
            nombreTarea.textContent = tarea.nombre;

            const opcionesDiv = document.createElement('DIV');
            opcionesDiv.classList.add('opciones');

            // Botón estado
            const btnEstadoTarea = document.createElement('BUTTON');
            btnEstadoTarea.classList.add('estado-tarea');
            btnEstadoTarea.classList.add(`${estados[tarea.estado].toLowerCase()}`)
            btnEstadoTarea.textContent = estados[tarea.estado];
            btnEstadoTarea.dataset.estadoTarea = tarea.estado;
            btnEstadoTarea.ondblclick = function() {
                cambiarEstadoTarea({...tarea});
            }

            // Boton eliminar
            const btnEliminarTarea = document.createElement('BUTTON');
            btnEliminarTarea.classList.add('eliminar-tarea');
            btnEliminarTarea.dataset.idTarea = tarea.id;
            btnEliminarTarea.textContent = 'Eliminar';

            // 
            opcionesDiv.appendChild(btnEstadoTarea);
            opcionesDiv.appendChild(btnEliminarTarea);

            contenedorTarea.appendChild(nombreTarea);
            contenedorTarea.appendChild(opcionesDiv);

            contenedorTareas.appendChild(contenedorTarea);
            
        });

    }

    function mostrarFormulario() {
        const modal = document.createElement('DIV');
        modal.classList.add('modal');
        modal.innerHTML = `
            <form class="formulario nueva-tarea">
                <legend>Añade una nueva tarea</legend>
                <div class="campo">
                    <label>Tarea</label>
                    <input 
                        type="text"
                        name="tarea"
                        placeholder="Añadir Tarea"
                        id="tarea"
                    />
                </div>
                <div class="opciones">
                    <input type="submit" value="Añadir Tarea" class="submit-nueva-tarea" />
                    <button type="button" class="cerrar-modal">Cancelar</button>
                </div>
            </form>
        `;

        setTimeout(() => {
            const formulario = document.querySelector('.formulario');
            formulario.classList.add('animar');
        }, 0);

        const btnCerrarModal = document.querySelector('.cerrar-modal');

        modal.addEventListener('click', function(e) {
            e.preventDefault();

            if(e.target.classList.contains('cerrar-modal') || e.target.classList.contains('modal')){  //Identifica si el elemento html contiene una clase
                
                const formulario = document.querySelector('.formulario');
                formulario.classList.add('cerrar');
                setTimeout(() => {
                    modal.remove();
                }, 500);
                
            } 

            if(e.target.classList.contains('submit-nueva-tarea')){
                SubmitFormularioNuevaTarea();
            }
            
        })

        document.querySelector('.dashboard').appendChild(modal);
    }

    function SubmitFormularioNuevaTarea() {
        const tarea = document.querySelector('#tarea').value.trim();
        if(tarea === '') {
            // Alerta de error
            mostrarAlerta('El nombre de la tarea es obligatorio', 'error', document.querySelector('.formulario legend'));
            return;

        }

        agregarTarea(tarea);
    }

    // Muestra un mensaje en la interfaz
    function mostrarAlerta(mensaje, tipo, referencia) {
        // Previene repetición de alertas
        const aletaPrevia = document.querySelector('.alerta'); 
        
        if(aletaPrevia) {
            aletaPrevia.remove();
        }
        const alerta = document.createElement('DIV');
        alerta.classList.add('alerta', tipo);
        alerta.textContent = mensaje;

        // Inserta la alerta antes del legend
        referencia.parentElement.insertBefore(alerta, referencia.nextElementSibling);

        // Elimnar la alerta tras x segundos
        setTimeout(() => {
            alerta.remove();
        }, 5000);
        
    }

    // Consultar el servidor para añadir una nueva tarea al proyecto actual
    async function agregarTarea(tarea) {
        // Construir la petición
        const datos = new FormData();
        datos.append('nombre', tarea);
        datos.append('proyectoId', obtenerProyecto());
        

        try {
            const url = 'http://localhost:3000/api/tarea';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
            const resultado = await respuesta.json();
            

            // Alerta de error (petición incorrecta)
            mostrarAlerta(resultado.mensaje, resultado.tipo , document.querySelector('.formulario legend'));

            // Si es exitosa cerrar el modal
            if(resultado.tipo === 'exito') {
                const modal = document.querySelector('.modal');
                const btnSubmit = document.querySelector('.submit-nueva-tarea');
                const btnCancelar = document.querySelector('.cerrar-modal');
                const texto = document.querySelector('#tarea');
                btnSubmit.remove();
                btnCancelar.remove();
                texto.setAttribute("disabled", "");

                setTimeout(() => {
                    modal.remove();
                }, 3000);
                
                // Agregar el objeto de tarea al global de tareas
                const tareaObj = {
                    id: String(resultado.id), 
                    nombre: tarea,
                    estado: "0",
                    proyectoId: resultado.proyectoId
                }

                tareas = [...tareas, tareaObj];
                mostrarTareas();

            }

        } catch (error) {
            console.log(error);
            // Alerta de error (petición incorrecta)
            mostrarAlerta('No es posible conectar', 'error', document.querySelector('.formulario legend'));
            return;
        }
    }

    function cambiarEstadoTarea(tarea) {
        
        const nuevoEstado = tarea.estado ==="1" ? "0" : "1";
        tarea.estado = nuevoEstado;
        actualizarTarea(tarea);

    }

    async function actualizarTarea(tarea) {
        const {estado, id, nombre, proyectoId} = tarea;

        const datos = new FormData();
        datos.append('id', id);
        datos.append('nombre', nombre);
        datos.append('estado', estado);
        datos.append('proyectoId', obtenerProyecto());

        try {
            const url = 'http://localhost:3000/api/tarea/actualizar';
            const respuesta = await fetch(url, {
                method: 'POST', 
                body: datos
            });
            const resultado = await respuesta.json();

            if(resultado.tipo === 'exito') {
                mostrarAlerta(resultado.mensaje, resultado.tipo, document.querySelector('.contenedor-nueva-tarea'));

                tareas = tareas.map(tareaMemoria => { // NO modifica el original, crea uno nuevo y luego lo asigno
                    if(tareaMemoria.id === id) {
                        tareaMemoria.estado = estado;
                    }

                    return tareaMemoria;
                });

                mostrarTareas();
            }

        } catch (error) {
            console.log(error);
        }




    }

    function obtenerProyecto() {
        const proyectoParams = new URLSearchParams(window.location.search);
        const proyecto = Object.fromEntries(proyectoParams.entries()); // Para acceder a la informacion
        return proyecto.id;
    }

    function limpiarTareas() {
        const listadoTareas = document.querySelector('.listado-tareas');

        while(listadoTareas.firstChild) { //Evalua si hay algún elemento y lo borra
            listadoTareas.removeChild(listadoTareas.firstChild);
        }
    }

    


})();