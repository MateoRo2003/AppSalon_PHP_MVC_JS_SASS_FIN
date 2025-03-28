<?php 
    include_once __DIR__ . "/../templates/barra.php"
?>

<div class="centrar-login">
<h1 class="nombre-pagina">Recuperar Password</h1>
<p class="descripcion-pagina">Coloca tu nuevo password a continuación</p>

<?php 
    include_once __DIR__ . "/../templates/alertas.php";
?>
<?php if($error) return; ?>
<form class="formulario-login" method="POST" id="form-recuperar-password">
    <div class="campo">
        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="Tu Nuevo Password"
        />
    </div>
    <input type="submit" class="botonR" value="Guardar Nuevo Password" id="submit-btn">

</form>

<div class="acciones">
    <a href="/">¿Ya tienes cuenta? Iniciar Sesión</a>
    <a href="/crear-cuenta">¿Aún no tienes cuenta? Obtener una</a>
</div>

</div>
<?php include_once __DIR__ . '/../templates/footer.php'; ?>

<script>
    // Obtener los elementos del formulario
    const form = document.getElementById('form-recuperar-password');
    const passwordInput = document.getElementById('password');
    const submitButton = document.getElementById('submit-btn');

    // Evento de submit para deshabilitar el input y el botón
    form.addEventListener('submit', function() {
        passwordInput.disabled = true;  // Deshabilitar el campo de password
        submitButton.disabled = true;  // Deshabilitar el botón de submit
    });
</script>
