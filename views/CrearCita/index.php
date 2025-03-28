<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<!-- FullCalendar Scripts -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<?php 
    include_once __DIR__ . '/../templates/barra.php';
    include_once __DIR__ . '/../templates/alertas.php';
    $usuario = $_SESSION['usuario'] ?? null; // Asegúrate de que $usuario esté definido
?>
<div class="main-content">
    <h1 class="nombre-pagina">Crear Cita</h1>
    <p class="descripcion-pagina">Ingresa los datos que correspondan</p>

    <form class="formulario-crear" method="POST" id="formularioCrearCita">

    <div class="campo-crear">
    <label for="servicios">Elige tus servicios</label>
    <div class="custom-combobox">
        <div class="combobox-selected" onclick="toggleDropdown()">
            Selecciona uno o más servicios
        </div>
        <div class="combobox-options">
            <?php foreach ($servicios as $servicio): ?>
                <label class="checkbox-option">
                    <span class="servicio-nombre"><?php echo $servicio->nombre; ?> - $<?php echo $servicio->precio; ?></span>
                    <input type="checkbox" value="<?php echo $servicio->id; ?>" name="servicios[]"
                           data-duration="<?php echo $servicio->duracion; ?>"
                           data-price="<?php echo $servicio->precio; ?>"
                           onchange="updateSelectedOptions()" />
                </label>
            <?php endforeach; ?>
        </div>
    </div>
</div>




        <div class="campo-crear">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" placeholder="Tu Nombre" value="<?php echo $usuario ? htmlspecialchars($usuario->nombre) : ''; ?>" required />
        </div>

        <div class="campo-crear">
            <label for="apellido">Apellido</label>
            <input type="text" id="apellido" name="apellido" placeholder="Tu Apellido" value="<?php echo $usuario ? htmlspecialchars($usuario->apellido) : ''; ?>" required />
        </div>

        <div class="campo-crear">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" placeholder="Tu E-mail (Obligatorio)" value="<?php echo $usuario ? htmlspecialchars($usuario->email) : ''; ?>" required />
        </div>

        <div class="campo-crear">
            <button type="button" class="btn-ver-fechas" onclick="VerCrearCita()">Ver Fechas Disponibles</button>
        </div>
        <div id="calendar-note" class="note-admin">
                    <i class="fas fa-info-circle"></i> <!-- Ícono Font Awesome -->
                    <span>Nota Importante: Seleciona el boton Fechas disponibles para elegir tu fecha y horarios.</span>
        </div>

        <div class="campo-crear">
            <label for="fecha">Fecha de la cita</label>
            <input type="text" id="fecha-nueva" name="fecha" readonly />
        </div>

        <div class="campo-crear">
            <label for="hora">Hora de la cita</label>
            <input type="text" id="hora-nueva" name="hora" readonly />
        </div>

        <div class="campo-crear">
            <label for="duracionTotal">Duración Total</label>
            <input type="number" id="duracionTotal" name="duracionTotal" value="0" readonly />
        </div>

        <div class="campo-crear">
            <label for="precioTotal">Precio Total</label>
            <input type="number" id="precioTotal" name="precioTotal" value="0" readonly />
        </div>

        <div class="campo-crear">
            <label for="Finalizacion">Finalizacion Prevista</label>
            <input type="text" id="hora-prevista" name="hora-prevista" value="0" readonly />
        </div>

        <input type="submit" class="btn-confirmar" value="Crear Cita" id="crearCitaBtn"  />

    </form>

    <div id="spinner-overlay" class="spinner-overlay">
        <div class="spinner"></div>
    </div>

   
</div>





<script>
document.getElementById("crearCitaBtn").addEventListener("click", function(event) {
    event.preventDefault(); // Evita el envío tradicional del formulario

    const form = document.querySelector(".formulario-crear");

    // Mostrar el loading con Swal
    Swal.fire({
        title: 'Creando cita...',
        text: 'Por favor espera mientras procesamos tu solicitud.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Enviar datos del formulario con fetch
    fetch("/crear-cuentaAdmin", {
        method: "POST",
        body: new FormData(form)
    })
    .then(response => response.text()) // Obtener la respuesta como texto primero
    .then(data => {
        console.log("Respuesta del servidor:", data);

        try {
            data = JSON.parse(data); // Intentar convertir la respuesta en JSON
        } catch (e) {
           
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "Ocurrió un problema, por favor completa todos los campos requiridos."
            });
            return;
        }

        Swal.close();

        if (data.error) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.error
            });
        } else if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.success
            }).then(() => {
                window.location.href = "/admin"; // Redirigir después de éxito
            });
        }
    })
    .catch(error => {
        console.error("Error en la solicitud:", error);
        Swal.fire({
            icon: 'error',
            title: 'Hubo un error',
            text: "Ocurrió un problema, por favor intenta de nuevo."
        });
    });
});
</script>






<script>
  function toggleDropdown() {
    document.querySelector(".combobox-options").classList.toggle("show");
}

function updateSelectedOptions() {
    let selectedOptions = [];
    document.querySelectorAll(".combobox-options input:checked").forEach(checkbox => {
        selectedOptions.push(checkbox.parentElement.querySelector(".servicio-nombre").textContent.trim());
    });

    let selectedText = selectedOptions.length > 0 ? selectedOptions.join(", ") : "Selecciona uno o más servicios";
    document.querySelector(".combobox-selected").textContent = selectedText;
}

// Cierra el dropdown si se hace clic fuera
document.addEventListener("click", function (event) {
    let combobox = document.querySelector(".custom-combobox");
    if (!combobox.contains(event.target)) {
        document.querySelector(".combobox-options").classList.remove("show");
    }
});

</script>



<script>
function actualizarTotales() {
    let duracionTotal = 0;
    let precioTotal = 0;

    document.querySelectorAll('input[name="servicios[]"]:checked').forEach(checkbox => {
        duracionTotal += parseInt(checkbox.getAttribute('data-duration'));
        precioTotal += parseFloat(checkbox.getAttribute('data-price'));
    });

    // Actualizar los campos de duración y precio
    document.getElementById('duracionTotal').value = duracionTotal;
    document.getElementById('precioTotal').value = precioTotal.toFixed(0);

    // Guardar el valor de duracionTotal en citaAdmin
    citaAdmin.duracionTotal = duracionTotal;
    console.log(citaAdmin.duracionTotal,'laduracionnn');

}

// Agregar event listeners a los checkboxes
document.querySelectorAll('input[name="servicios[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', actualizarTotales);
});

// Inicializar los totales al cargar la página
actualizarTotales();




</script> 

<script>
    function mostrarSpinner() {
    document.getElementById('spinner-overlay').style.display = 'flex';
}

</script>

<div id="modal-crearcita" class="modal-seleccion" style="display: none;">
    <div class="modal-content-seleccion">
        <h2>Selecciona una fecha</h2>
        <div id="calendar-container">
            <div id="calendar-crear"></div>
        </div>
        <button class="btn-cancelar" onclick="cerrarCrearCita()">Cancelar</button>
    </div>
</div>


<div id="modal-seleccionar-turno" class="modal-seleccionar-turno">
    <div class="modal-contenido-seleccionar">
        <h3 class="text-center">Selecciona tu Horario</h3>
        <p class="text-center">¿Qué parte del día prefieres?</p>
        <div class="campo">
            <input id="fechacrear" type="date" />
        </div>


        <div class="campo-hora">
            <div class="botones-turno">
                <button id="turno-manana" type="button" class="button-hora" onclick="mostrarHorariosCrear('manana')">Mañana</button>
                <button id="turno-tarde" type="button" class="button-hora" onclick="mostrarHorariosCrear('tarde')">Tarde</button>
            </div>
            <div id="horarios-crear" class="horarios-container"></div>
        </div>
        <button id="confirmar-modal-crear" class="btn-confirmar " onclick=ConfirmarHoraCrear() >Confirmar</button>
        <button id="cerrar-modal-seleccionar" class="btn" onclick=CerrarHoraCrear()>Cerrar</button>
        </div>
</div>


<?php
    $script = "<script src='build/js/buscador.js'></script>"
    
?>


    


