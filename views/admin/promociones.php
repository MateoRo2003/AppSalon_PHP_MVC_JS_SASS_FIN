<?php 
    include_once __DIR__ . '/../templates/barra.php';
?>

<div class="main-content">
    <h1>Opciones y Avisos a Nuestros Clientes</h1>


    <section class="recordatorio-citas">
        <div class="opcion">
            <div class="explicacion">
                <h3>Recordatorio un día antes de la cita:</h3>
                <p>Esta opción permite enviar un recordatorio a los clientes un día antes de su cita programada. Es útil para evitar olvidos y garantizar que los clientes lleguen a tiempo.</p>
                <p><strong>Ejemplo:</strong> Si un cliente tiene una cita programada para el 15 de marzo, el sistema enviará un mensaje el 14 de marzo para recordarle su cita.</p>
            </div>
            <div class="toggle-boton">
                <label class="switch">
                    <!-- Checkbox para Recordatorio un día antes -->
                    <input type="checkbox" id="recordatorioDiaAntes" <?php echo $configuraciones[0]->recordatorio_dia_antes == '1' ? 'checked' : ''; ?> />
                    <span class="slider round"></span>
                </label>
            </div>
        </div>

        <div class="opcion">
            <div class="explicacion">
                <h3>Avisar a los clientes cuando se agregue un nuevo servicio:</h3>
                <p>Esta opción permite enviar un mensaje automáticamente a todos los clientes cada vez que se agregue un nuevo servicio a la lista. Esto es útil para mantener a los clientes informados sobre los nuevos servicios disponibles.</p>
                <p><strong>Ejemplo:</strong> Si se agrega un nuevo servicio de "Masajes Relajantes", el sistema enviará un mensaje a todos los clientes informándoles de la nueva opción.</p>
            </div>
            <div class="toggle-boton">
                <label class="switch">
                    <!-- Checkbox para Avisar a los clientes sobre nuevos servicios -->
                    <input type="checkbox" id="avisarNuevoServicio" <?php echo $configuraciones[0]->avisar_nuevo_servicio == '1' ? 'checked' : ''; ?> />
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </section>
    
    <!-- Sección de Mensajes para Novedades y Nuevos Servicios -->
    <section class="novedades">
        <h2>Enviar Novedades</h2>

        <!-- Advertencia para el Envío Masivo de Mensajes -->
        <div class="advertencia">
            <p><strong>Importante:</strong> El mensaje que envíes se enviará a **todos tus clientes**. Asegúrate de que el mensaje sea adecuado y profesional, ya que no podrás deshacer esta acción una vez enviada.</p>
            <p><strong>Ejemplo de mensaje:</strong> "¡Nuevo servicio disponible! Ahora ofrecemos masajes relajantes para todos nuestros clientes. ¡Agenda tu cita hoy mismo!"</p>
        </div>

        <div class="mensaje-novedad">
            <h3>Mensaje de Novedades:</h3>
            <textarea id="mensajeNovedades" placeholder="Escribe el mensaje que deseas enviar a todos tus clientes..." rows="5" style="width: 100%; padding: 10px; font-size: 16px;"></textarea>
        </div>

        <div class="enviar-novedad">
            <button id="enviarMensaje" disabled>Enviar Mensaje</button>
        </div>
    </section>
</div>

<!-- Script de JavaScript para habilitar el botón al cambiar el estado de los switches -->
<script>
    // Obtener los elementos de los checkboxes
    const recordatorioDiaAntes = document.getElementById('recordatorioDiaAntes');
    const avisarNuevoServicio = document.getElementById('avisarNuevoServicio');

    // Función para enviar la configuración al backend al cambiar el estado del checkbox
    function enviarConfiguracion() {
        const data = {
            recordatorioDiaAntes: recordatorioDiaAntes.checked ? 1 : 0,
            avisarNuevoServicio: avisarNuevoServicio.checked ? 1 : 0
        };

        fetch('/api/guardar-configuracion', { // Reemplaza con tu endpoint real
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Configuración actualizada correctamente.');
            } else {
                alert('Hubo un error al guardar la configuración.');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Agregar eventos a los checkboxes para enviar los datos automáticamente al cambiar
    recordatorioDiaAntes.addEventListener('change', enviarConfiguracion);
    avisarNuevoServicio.addEventListener('change', enviarConfiguracion);

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const mensajeInput = document.getElementById("mensajeNovedades");
    const enviarBtn = document.getElementById("enviarMensaje");

    // Habilitar o deshabilitar el botón según si hay texto en el textarea
    mensajeInput.addEventListener("input", () => {
        enviarBtn.disabled = mensajeInput.value.trim() === "";
    });

    // Evento al presionar el botón
    enviarBtn.addEventListener("click", () => {
        const mensaje = mensajeInput.value.trim();
        if (mensaje === "") return;

        Swal.fire({
            title: "¿Enviar mensaje?",
            text: "Este mensaje será enviado a todos los clientes.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, enviar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar animación de carga mientras espera la respuesta
                Swal.fire({
                    title: "Enviando...",
                    text: "Por favor, espera.",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch("/api/enviar_novedad", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ mensaje }),
                })
                .then((response) => response.json())
                .then((data) => {
                    Swal.close(); // Cerrar la animación de carga

                    if (data.status === "success") {
                        Swal.fire("¡Enviado!", data.message, "success");
                        mensajeInput.value = ""; // Limpiar el campo
                        enviarBtn.disabled = true;
                    } else {
                        Swal.fire("Error", data.message, "error");
                    }
                })
                .catch(() => {
                    Swal.close();
                    Swal.fire("Error", "No se pudo enviar el mensaje.", "error");
                });
            }
        });
    });
});
</script>



<script>
    // Obtener los elementos del textarea y el botón
    const mensajeNovedades = document.getElementById('mensajeNovedades');
    const enviarMensajeButton = document.getElementById('enviarMensaje');

    // Función para habilitar el botón de enviar mensaje cuando haya texto
    function verificarMensaje() {
        if (mensajeNovedades.value.trim() !== '') {
            enviarMensajeButton.disabled = false; // Habilitar el botón si hay texto
        } else {
            enviarMensajeButton.disabled = true; // Deshabilitar el botón si no hay texto
        }
    }

    // Agregar evento de cambio al textarea para verificar si hay texto
    mensajeNovedades.addEventListener('input', verificarMensaje);
</script>
