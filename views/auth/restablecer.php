<div class="contenedor restablecer">
    <?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Coloca tu nuevo password</p>

        <form class="formulario" method="POST" action="/restablecer">

            <div class="campo">
                <label for="password">Password</label>
                <input type="password" id="password" placeholder="Tu password" name="password" />
            </div>

            <div class="campo">
                <label for="password2">Confirma password</label>
                <input type="password2" id="password2" placeholder="Repite tu password" name="password2" />
            </div>

            <input type="submit" value="Restablecer password" class="boton" />

        </form>

        <div class="acciones">
            <a href="/crear">¿Aún no tienes una cuenta? Crear Cuenta</a>
            <a href="/olvide">Olvidé mi contraseña</a>
        </div>
    </div> <!--contenedor-sm-->
</div>