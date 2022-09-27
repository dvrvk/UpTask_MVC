(function(){ // Sirve para proteger las variables y que no se mezclen con otros archivos
    // Boton para mostrar el Modal de Agregar tarea
    const nuevaTareaBtn = document.querySelector('#agregar-tarea');
    nuevaTareaBtn.addEventListener('click', mostrarFormulario);

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

        agregarTarea();
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
    function agregarTarea(tarea) {

    }


})();