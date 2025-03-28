
document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();

    
});

function iniciarApp() {
    consultarTotales();
    obtenerTotalGanado();
   
}
// Función para actualizar la duracionTotal en citaAdmin
function updateDuracionTotal() {
    const checkboxes = document.querySelectorAll('input[name="servicios[]"]:checked');
    let duracionTotal = 0;

    checkboxes.forEach(checkbox => {
        const duracion = parseInt(checkbox.getAttribute('data-duration'));
        duracionTotal += duracion;
    });

    // Actualizar la variable citaAdmin
    citaAdmin.duracionTotal = duracionTotal;

    // Mostrar el valor actualizado de duracionTotal en el formulario
    document.getElementById('duracionTotal').value = citaAdmin.duracionTotal;
}

// Añadir el evento a los checkboxes para actualizar la duración total cuando se seleccione/deseleccione un servicio
document.querySelectorAll('input[name="servicios[]"]').forEach(input => {
    input.addEventListener('change', updateDuracionTotal);
});


async function CerrarCaja() {
    // Obtener el total ganado
    const totalGanado = document.getElementById("totalGanadoTexto").innerText.replace("$", "").replace(",", "").trim();

    console.log(totalGanado, 'total ganado');
    // Obtener la fecha actual en formato YYYY-MM-DD
    const fechaActual = new Date().toISOString().split("T")[0];

    // Obtener IDs, precios y fechas de citas realizadas
    const citasRealizadas = [];
    document.querySelectorAll("#citasRealizadasData span").forEach(span => {
        const precio = parseFloat(span.dataset.precio);
        console.log(precio, 'precio de la cita');
        citasRealizadas.push({
            id: span.dataset.id,
           precio: precio.toFixed(2),
            fecha: span.dataset.fecha
        });
    });

    // Si no hay citas realizadas, mostrar un mensaje de error
    if (citasRealizadas.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No hay citas realizadas",
            text: "Debes estar en la categoria de Citas Realizadas para cerrar la caja.",
        });
        return;
    }

    // Mostrar alerta de carga
    Swal.fire({
        title: "Cerrando caja...",
        text: "Por favor espera mientras se procesa la caja.",
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        // Enviar petición al backend
        const response = await fetch("/api/cierreCaja", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                total: parseInt(totalGanado),
                fecha: fechaActual,
                citas: citasRealizadas // Enviar todas las citas con estado 3
            })
        });

        const data = await response.json();

        // Verificar respuesta
        if (response.ok) {
            Swal.fire({
                icon: "success",
                title: "Caja cerrada",
                text: "La caja se ha cerrado correctamente.",
                timer: 2000,
                showConfirmButton: false
            });

            // Limpiar el total ganado
            document.getElementById("totalGanadoTexto").innerText = "$0";

            // Opcional: recargar la página para actualizar el estado de las citas
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            throw new Error(data.message || "Error al cerrar caja");
        }
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.message || "Hubo un problema al cerrar la caja.",
        });
    }
}







function obtenerTotalGanado() {
    fetch('/api/TotalGanado')
        .then(response => response.json())
        .then(datos => {
            console.log(datos); // Para ver los datos en la consola

            if (Array.isArray(datos) && datos.length > 0) {
                actualizarTotalGanado(datos);
            } else {
                console.warn('No se encontraron registros de total ganado.');
            }
        })
        .catch(error => console.error('Error al obtener los totales:', error));
}

function actualizarTotalGanado(datos) {
    const totalTexto = document.getElementById('totalGanadoTexto');

    // Sumar todos los totales de las fechas
    const totalGanado = datos.reduce((acumulador, item) => acumulador + parseFloat(item.total_ganado), 0);

    // Mostrar en el HTML
    totalTexto.textContent = `$${totalGanado.toLocaleString()}`;
}





async function consultarTotales() {
    try {
        const url = '/api/totales'; // URL del endpoint creado en el backend
        const resultado = await fetch(url);

        // Verificar si la respuesta es exitosa
        if (!resultado.ok) {
            throw new Error('Error al obtener los datos');
        }

        // Convertir la respuesta en formato JSON
        const data = await resultado.json();
        console.log(data);
        // Llamamos a la función para actualizar las tarjetas
        actualizarTarjetas(data);

    } catch (error) {
        console.error('Error al consultar la API:', error);
    }
}

// Función para actualizar las tarjetas con los totales
function actualizarTarjetas(data) {
    // Actualizar el total de clientes
    document.querySelector('.tarjeta .numero').textContent = data.totalClientes;

    // Actualizar el total de servicios
    document.querySelectorAll('.tarjeta')[1].querySelector('.numero').textContent = data.totalServicios;

    // Actualizar el total de suscripciones
    document.querySelectorAll('.tarjeta')[2].querySelector('.numero').textContent = data.totalSuscripciones;

    // Actualizar el total de citas
    document.querySelectorAll('.tarjeta')[3].querySelector('.numero').textContent = data.totalCitas;
}