<div class="contenedor olvide">
    <?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Restablece tu password</p>

        <form class="formulario" method="POST" action="/olvide">

            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="Tu email" name="email" />
            </div>

            <input type="submit" value="Enivar Instrucciones" class="boton" />

        </form>

        <div class="acciones">
            <a href="/">¿Ya tienes cuenta? Inicia sesión</a>
            <a href="/crear">¿No tienes cuenta? Crear cuenta</a>
        </div>
    </div> <!--contenedor-sm-->
</div>