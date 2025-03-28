let paso = 1;
const pasoInicial = 1;
const pasoFinal = 3;

const cita = {
    id: '',
    nombre: '',
    fecha: '',
    hora: '',
    servicios: []
}
// Verificar si el indicador está presente en sessionStorage
window.addEventListener('load', () => {
    if (sessionStorage.getItem('openModalHistorial') === 'true') {
        // Llamar a la función para abrir el modal
        abrirModalHistorial(); // Asume que esta es la función que abre el modal-historial

        // Eliminar el indicador después de abrir el modal
        sessionStorage.removeItem('openModalHistorial');
    }
});
document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
    
});

function iniciarApp() {

    //consultarAPIEdicion();
    mostrarSeccion(); // Muestra y oculta las secciones
    tabs(); // Cambia la sección cuando se presionen los tabs
    botonesPaginador(); // Agrega o quita los botones del paginador
    paginaSiguiente(); 
    paginaAnterior();
    // mostrarTurnos();
   // mostrarHorarios();
    // mostrarHorariosEdicion();
    confirmarServicioEdicion();
    idCliente();
    nombreCliente(); // Añade el nombre del cliente al objeto de cita
    seleccionarFecha(); // Añade la fecha de la cita en el objeto
    inicializarCalendario(); 
    inicializarCalendarioEdicion();   // inicializarCalendarioEdicion();
    //seleccionarHora(); // Añade la hora de la cita en el objeto
   // mostrarResumen(); // Muestra el resumen de la cita
    configurarModales(); // Agregar configuración de los modales
    // validarContrasenaActual();
     //mostrarTurnosEdicion();
}

function agregarServicio() {
    const modalServicio = document.getElementById('modal-servicio-agregar');
    modalServicio.style.display = 'block'; // Asegúrate de que el estilo se aplique
    modalServicio.classList.remove('hide'); // Remover clase hide
    modalServicio.classList.add('show'); // Agregar clase show
    cerrarModalHistorial();
    consultarAPIAgregar(); // Llamar a la función que consulta los servicios
}

function cerrarModalAgregado() {
    document.getElementById('modal-servicio-agregar').style.display = 'none';
    abrirModalHistorial();
    // Opcionalmente, puedes limpiar el contenido del modal o resetear estados
}


let esTurnoCompleto =false;

function anularCita() {
    const modalAnular = document.getElementById('modal-anular');
    modalAnular.classList.add('show');
    modalAnular.style.display = 'block';
    cerrarModalHistorial();
    cerrarModalEditarHistorial();
    
}

function cerrarModalAnular() {
    const modalAnular = document.getElementById('modal-anular');
    modalAnular.classList.remove('show');
    
    setTimeout(() => {
        modalAnular.style.display = 'none';
    }, 400); // Espera el tiempo de transición antes de ocultar completamente
}

// Modificado para POST en lugar de DELETE
function anularServicio() {
    // Obtenemos el servicioIdAntiguo que ya tienes disponible
    const servicioIdAntiguo = cita.servicioIdAntiguo;
    const citaId = cita.citaId;   // Aquí ya tienes el citaId
    console.log('Servicio a anular:', servicioIdAntiguo);
    console.log('id de la cita: ', citaId);
    // Mostrar el spinner de carga mientras se procesa la solicitud

    if(esTurnoCompleto){
        
        anularTurnoCompleto(citaId);
        cerrarModalEditarHistorial();
        
    }else{
         // Realizar la solicitud POST para anular el servicio
    fetch('/api/anularservicio', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            servicioId: servicioIdAntiguo, // Enviamos el servicioId
            citaId: citaId  // Enviamos también el citaId
        })
    })
    .then(response => response.json())
    .then(data => {
        // Si la eliminación fue exitosa
        if (data.success) {
            // Eliminar la fila del servicio en la interfaz
            const filaServicio = document.querySelector(`[data-servicio-id="${servicioIdAntiguo}"]`);
            if (filaServicio) {
                filaServicio.remove(); // Eliminamos la fila
            }

            // Mostrar un mensaje de éxito
            cerrarModalAnular();
            cerrarModalEditarHistorial();
            // Cerrar el modal de anulación
            abrirModalHistorial();

            // Ocultar el spinner de carga
           
        } else {
            // Si hubo un error al eliminar
            alert(data.message || 'Hubo un error al anular el servicio. Inténtalo de nuevo.');
           
        }
    })
    .catch(error => {
        // Manejo de errores si la solicitud falla
        console.error('Error al intentar anular el servicio:', error);
        alert('Error en la solicitud. Inténtalo nuevamente.');
        document.querySelector('.loading-spinner').style.display = 'none';
    });
    }
   
}



function anularTurnoCompleto(citaId) {
    // Mostrar el spinner de carga mientras se procesa la solicitud
    
    const spinner = document.getElementById("loading-spinner");
    spinner.classList.add("show");
    // Realizar la solicitud POST para anular la cita completa
    fetch('/api/anularturnocompleto', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            citaId: citaId  // Enviamos el citaId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Eliminar la fila del turno en la interfaz (si es necesario)
            // Aquí puedes cerrar el modal y actualizar la interfaz según sea necesario
            spinner.classList.remove("show");
           
            Swal.fire({
                icon: 'success',
                title: 'Turno Cancelado',
                text: 'Tu turno fue cancelado correctamente',
                button: 'OK'
            }).then( () => {
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            })
    
        } else {
            alert(data.message || 'Hubo un error al anular el turno. Inténtalo de nuevo.');
            document.querySelector('.loading-spinner').style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error al intentar anular el turno:', error);
        alert('Error en la solicitud. Inténtalo nuevamente.');
        document.querySelector('.loading-spinner').style.display = 'none';
    });
}








// //Función para cargar el historial de citas y mostrar el modal
// function cargarHistorialCitas() {
//     fetch('/obtener-historial-citas')
//         .then(response => response.json())
//         .then(data => {
//             if (data.success) {
//                 const historial = data.historial;
//                 const contenidoHistorial = document.getElementById('contenido-historial');
//                 contenidoHistorial.innerHTML = '';

//                 historial.forEach(cita => {
//                     const citaElemento = document.createElement('div');
//                     citaElemento.classList.add('cita-item');
//                     citaElemento.innerHTML = `
//                         <p>Fecha: ${cita.cita_fecha}</p>
//                         <p>Servicio: ${cita.servicio_nombre}</p>
//                     `;
//                     contenidoHistorial.appendChild(citaElemento);
//                 });

//                 // Mostrar el modal
//                 document.getElementById('modal-historial').classList.add('modal-activo');
//             } else {
//                 console.error(data.message || 'Error al cargar el historial.');
//             }
//         })
//         .catch(error => console.error('Error:', error));
// }

// // Función para cerrar el modal
// function cerrarModalHistorial() {
//     document.getElementById('modal-historial').classList.remove('modal-activo');
// }









// function validarContrasenaActual() {
//     const botonConfirmar = document.getElementById('boton-confirmar');
    
//     botonConfirmar.addEventListener('click', async function(event) {
//         event.preventDefault(); // Evita que se recargue la página

//         const contraseñaActual = document.getElementById('contraseña-actual').value;
//         const nuevaContraseña = document.getElementById('nueva-contraseña');
//         const confirmarContraseña = document.getElementById('confirmar-contraseña');

//         // Verifica que se haya ingresado la contraseña actual
//         if (contraseñaActual.trim() === '') {
//             alert('Por favor, ingresa la contraseña actual.');
//             return;
//         }

//         const datos = new FormData();
//         datos.append('contraseña_actual', contraseñaActual);

//         try {
//             // Realiza la petición POST para validar la contraseña actual
//             const respuesta = await fetch('/api/actualizar-contra', {
//                 method: 'POST',
//                 body: datos
//             });

//             const data = await respuesta.json();
//             if (data.success) {
//                 // Si la contraseña actual es correcta, habilita los campos de nueva contraseña
//                 nuevaContraseña.disabled = false;
//                 confirmarContraseña.disabled = false;
//             } else {
//                 // Muestra el mensaje de error si la contraseña es incorrecta
//                 const errorContrasena = document.getElementById('error-contrasena');
//                 errorContrasena.textContent = data.message;
//                 errorContrasena.style.display = 'block';
//             }
//         } catch (error) {
//             console.error('Error:', error);
//             alert('Hubo un error al validar la contraseña actual. Inténtalo de nuevo.');
//         }
//     });
// }

function cerrarModalSeleccionEdicion() {
    const modalSeleccion = document.getElementById('modal-seleccion-edicion');
    modalSeleccion.classList.remove('show');
    abrirModalEditarHistorial();
    obtenerDetallesCita();
    document.getElementById('blur-background3').style.display = 'none'
    document.getElementById('blur-background').style.display = 'none'
    setTimeout(() => {
        modalSeleccion.style.display = 'none';
    }, 300); // Espera el tiempo de la transición
    
}

function cerrarModalFecha() {
    const modal = document.getElementById("modal-fecha"); // Asegúrate de que este ID coincida con el de tu modal
    modal.style.display = "none"; // Cierra el modal ocultándolo
    
    document.getElementById('blur-background').style.display = 'none'; // Oculta el fondo de desenfoque si lo tienes
    abrirModalEditarHistorial();

}

// Función para manejar el cambio de filtro
function filtrarServicios() {
    var categoriaSeleccionada = document.getElementById('filtro-servicios').value;
    
    // Actualizar el texto que muestra la categoría seleccionada
    var categoriaTexto = categoriaSeleccionada.charAt(0).toUpperCase() + categoriaSeleccionada.slice(1);
    document.getElementById('categoria-seleccionada').innerText = `Categoria seleccionada: ${categoriaTexto}`;

    // Obtener todos los servicios
    var servicios = document.querySelectorAll('.servicio');

    // Filtrar los servicios según la categoría seleccionada
    servicios.forEach(function(servicio) {
        if (categoriaSeleccionada === 'todos') {
            servicio.style.display = 'block'; // Mostrar todos los servicios
        } else {
            var categoriaServicio = servicio.getAttribute('data-categoria'); // Obtenemos la categoría del servicio
            if (categoriaServicio === categoriaSeleccionada) {
                servicio.style.display = 'block'; // Mostrar el servicio si coincide con la categoría
            } else {
                servicio.style.display = 'none'; // Ocultar el servicio si no coincide
            }
        }
    });
}



function configurarModales() {
    const modal = document.getElementById('modal');
    const btnReservar = document.getElementById('reservar-turno');
    const btnCerrar = document.getElementById('cerrar-modal');
    const modalSecundario = document.getElementById('modal-seleccionar-turno');
    const btnCerrarSecundario = document.getElementById('cerrar-modal-seleccionar');
    const blurBackground = document.getElementById('blur-background'); 
    const modalBlurBackground = document.getElementById('modal-blur-background');
    const app = document.getElementById('app');
    const barra = document.querySelector('.barra');
    const darkBackground = document.getElementById('dark-background'); // Fondo oscuro
    let originalWidth = window.innerWidth;

    // Función para abrir el modal principal
    function abrirModal() {
        //inicializarCalendario(); 
        // Consulta la API en el backend de PHP
        consultarAPI();
        originalWidth = window.innerWidth; 
        const reducedWidth = originalWidth - 500; 
        // Verifica si el calendario está inicializado
        setTimeout(function () {
            if (calendar) {
                console.log('hola'); // Asegurarse de que el calendario está inicializado antes de actualizar el tamaño
                calendar.updateSize();  
            } else {
                console.error("calendarAdmin no está inicializado.");
            }
        }, 300); 
        app.style.width = `${reducedWidth}px`;
        app.style.transition = 'width 0.5s'; 
        modal.style.display = 'block';
        blurBackground.style.display = 'block'; 
        
        barra.classList.add('modal-abierto');
        document.body.style.overflow = 'hidden';
        modal.classList.add('mostrar');

        setTimeout(() => {
            modal.style.animation = 'slideIn 0.5s forwards';
        }, 10);
    }

    // Función para cerrar el modal principal
    function cerrarModal() {
        modal.classList.remove('mostrar');
        modal.style.animation = 'slideOut 0.5s forwards';
        blurBackground.style.display = 'none'; 

        setTimeout(() => {
            modal.style.display = 'none';
            app.style.width = `${originalWidth}px`; 
            barra.classList.remove('modal-abierto');
            document.body.style.overflow = '';
        }, 500); 
    }


    

    // Eventos de los botones
    btnReservar.addEventListener('click', abrirModal);
    btnCerrar.addEventListener('click', cerrarModal);
    
}


function mostrarSeccion() {
    // Ocultar la sección que tenga la clase de mostrar
    const seccionAnterior = document.querySelector('.seccion.mostrar');
    if (seccionAnterior) {
        seccionAnterior.classList.remove('mostrar');
    }

    // Seleccionar la sección con el paso actual
    const pasoSelector = `#paso-${paso}`;
    const seccion = document.querySelector(pasoSelector);
    seccion.classList.add('mostrar');


    
    // Quitar la clase de actual al tab anterior
    const tabAnterior = document.querySelector('.actual');
    if (tabAnterior) {
        tabAnterior.classList.remove('actual');
    }

    // Resaltar el tab actual
    const tab = document.querySelector(`[data-paso="${paso}"]`);
    tab.classList.add('actual');

    // Deshabilitar las pestañas que no se pueden seleccionar
    const botones = document.querySelectorAll('.tabs button');
    botones.forEach((boton, index) => {
        if (index + 1 === paso) {
            boton.classList.remove('inactivo'); // Habilita la pestaña actual
        } else {
            boton.classList.add('inactivo'); // Deshabilita las demás pestañas
        }
    });

    // **Forzar redimensionamiento del calendario en el paso 2**
    if (paso === 2) {
        setTimeout(function () {
            console.log('Actualizando tamaño del calendario');
            if (calendar) {
                calendar.updateSize();  // Actualiza el tamaño con un retraso
            }
        }, 300); // Ajusta el tiempo según sea necesario
    }
}


function tabs() {
    const botones = document.querySelectorAll('.tabs button');
    botones.forEach((boton, index) => {
        boton.classList.add('inactivo'); // Marca todas como inactivas

        // Habilitar solo la pestaña correspondiente al paso actual
        if (index + 1 === paso) {
            boton.classList.remove('inactivo'); // Habilita la pestaña activa
        }

        // Previene la selección de pestañas manualmente
        boton.addEventListener('click', function (e) {
            e.preventDefault();
            // No hacer nada aquí para evitar la selección de pestañas
        });
    });
}


function botonesPaginador() {
    const paginaAnterior = document.querySelector('#anterior');
    const paginaSiguiente = document.querySelector('#siguiente');

    if (paso === pasoInicial) {
        paginaAnterior.classList.add('ocultar');
        paginaSiguiente.classList.remove('ocultar');
              // Si estamos en el paso 1 (selección de servicios)
              if (cita.servicios.length === 0) {
                // Deshabilitar el botón "Siguiente" si no hay servicios seleccionados
                paginaSiguiente.disabled = true;
            } else {
                // Habilitar si hay servicios seleccionados
                paginaSiguiente.disabled = false;
            }
    } else if (paso === pasoFinal) {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.add('ocultar');
        mostrarResumen();
    } else if (paso === 2) {
        // Si estamos en el paso 2
        paginaAnterior.classList.remove('ocultar'); // Mostrar "Anterior"
        paginaSiguiente.classList.add('ocultar');   // Ocultar "Siguiente"
    } else {
        // Para cualquier otro paso
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.remove('ocultar');
    }

    mostrarSeccion();
}

function paginaAnterior() {
    const paginaAnterior = document.querySelector('#anterior');
    paginaAnterior.addEventListener('click', function () {
        if (paso <= pasoInicial) return;
        paso--;
        botonesPaginador();
    });
}

function paginaSiguiente() {
    const paginaSiguiente = document.querySelector('#siguiente');
    paginaSiguiente.addEventListener('click', function () {
        if (paso >= pasoFinal) return;
        paso++;
        botonesPaginador();
    });
}


function mostrarServiciosAgregar(servicios) {
    console.log('buenas', cita.servicioIdAntiguo);  // Para ver el valor de servicioIdAntiguo

    const serviciosDiv = document.querySelector('#servicios-edicion-agregar');
    serviciosDiv.innerHTML = ''; // Limpiar el contenido previo

    // Filtrar servicios para no mostrar los que están en cita.servicios
    const serviciosDisponibles = servicios.filter(servicio => {
        return !cita.serviciosSacar.includes(servicio.id);  // Solo mostrar servicios que no están en cita.servicios
    });

    serviciosDisponibles.forEach(servicio => {
        const { id, nombre, precio } = servicio;

        const nombreServicio = document.createElement('P');
        nombreServicio.classList.add('nombre-servicio');
        nombreServicio.textContent = nombre;

        const precioServicio = document.createElement('P');
        precioServicio.classList.add('precio-servicio');
        precioServicio.textContent = `$${precio}`;

        const servicioDiv = document.createElement('DIV');
        servicioDiv.classList.add('servicio3');
        servicioDiv.dataset.idServicio = id; // Asegúrate de que este sea el id correcto
        servicioDiv.onclick = function() {
            seleccionarServicioAgregar(servicio);
        };

        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(precioServicio);

        serviciosDiv.appendChild(servicioDiv);
    });
}



function seleccionarServicioAgregar(servicio) {
    const { id } = servicio;
    const { servicios } = cita;

    // Buscar solo dentro del contenedor de servicios del modal de creación
    const divServicio = document.querySelector(`#servicios-edicion-agregar [data-id-servicio="${id}"]`);

    // Verificar si el servicio ya está seleccionado
    if (servicios.some(agregado => agregado.id === id)) {
        // Desmarcar el servicio
        cita.servicios = servicios.filter(agregado => agregado.id !== id);
        divServicio.classList.remove('seleccionado3');
    } else {
        // Marcar el servicio
        cita.servicios = [...servicios, servicio];
        divServicio.classList.add('seleccionado3');
    }

    // Controlar el estado del botón "Siguiente"
    const paginaSiguiente = document.querySelector('#btn-confirmar-servicio-agregar');
    // Habilitar el botón si hay al menos un servicio en la lista
    paginaSiguiente.disabled = cita.servicios.length === 0;
}






function cerrarModalServicioAgregar() {
    document.getElementById('modal-servicio-agregar').style.display = 'none';
    abrirModalHistorial();
    // Opcionalmente, puedes limpiar el contenido del modal o resetear estados
}
async function confirmarServicioAgregar() {
    const servicios = cita.servicios; // Obtener los servicios seleccionados
    const citaId = cita.citaid;
    console.log('listado', servicios);
    console.log('citaid',citaId);

    const spinner = document.getElementById("loading-spinner");
    spinner.classList.add("show");

    if (servicios.length > 0) { // Verificar si hay al menos un servicio seleccionado
        try {
            const url = '/api/agregarServicio';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    serviciosIds: servicios.map(servicio => servicio.id), // Extraer los IDs de los servicios
                    citaId: citaId
                }),
            });
            console.log(response);
            if (response.ok) {
                console.log(response.ok);

                // Verificar si el contenido de la respuesta es JSON antes de parsearlo
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    const result = await response.json();
                    console.log('Servicios actualizados:', result);

                    if (result.resultado) {
                        // Mostrar mensaje de éxito con Swal
                        Swal.fire({
                            icon: 'success',
                            title: 'Servicio Agregado',
                            text: 'Tu servicio fue agregado correctamente',
                            button: 'OK'
                        }).then(() => {
                            // Establecer el indicador en sessionStorage
                            sessionStorage.setItem('openModalHistorial', 'true');

                            // Recargar la página después de un pequeño retraso
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        });
                    } else {
                        console.error('Error en la actualización:', result.error);
                    }
                } else {
                    console.error('Error: Respuesta no es JSON');
                }
            } else {
                console.error('Error al actualizar el servicio:', response.status);
            }
        } catch (error) {
            console.error('Error al enviar la solicitud:', error);
        } finally {
            spinner.classList.remove("show");
        }
    } else {
        console.error('No hay servicios seleccionados.');
        spinner.classList.remove("show");
    }
}


function mostrarServiciosEdicion(servicios) {
    const serviciosDiv = document.querySelector('#servicios-edicion');
    serviciosDiv.innerHTML = ''; // Limpiar el contenido previo

    // Filtrar los servicios que tengan la misma duración que el servicio actual
    const serviciosFiltrados = servicios.filter(servicio => servicio.duracion === cita.servicioEditable.duracion);

    // Mostrar los servicios filtrados, pero evitar que se muestre el servicio actual seleccionado
    serviciosFiltrados.forEach(servicio => {
        const { id, nombre, precio } = servicio;

        // Si el servicio es el actual seleccionado, no lo mostrar
        if (cita.servicioEditable && cita.servicioEditable.nombre === nombre) {
            return; // Saltar este servicio
        }

        const nombreServicio = document.createElement('P');
        nombreServicio.classList.add('nombre-servicio');
        nombreServicio.textContent = nombre;

        const precioServicio = document.createElement('P');
        precioServicio.classList.add('precio-servicio');
        precioServicio.textContent = `$${precio}`;

        const servicioDiv = document.createElement('DIV');
        servicioDiv.classList.add('servicio2');
        servicioDiv.dataset.idServicio = id; // Asegúrate de que este sea el id correcto

        servicioDiv.onclick = function() {
            seleccionarServicioEdicion(servicio);
        };

        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(precioServicio);

        serviciosDiv.appendChild(servicioDiv);
    });
}


function seleccionarServicioEdicion(servicio) {
    const { id, nombre } = servicio;

    // Buscar el elemento visual del servicio seleccionado en el modal de edición
    const divServicio = document.querySelector(`#servicios-edicion [data-id-servicio="${id}"]`);

    // Si hay un servicio previamente seleccionado, desmarcarlo
    if (cita.servicioEditable) {
        const divServicioAnterior = document.querySelector(`#servicios-edicion [data-id-servicio="${cita.servicioEditable.id}"]`);
        if (divServicioAnterior) {
            divServicioAnterior.classList.remove('seleccionado');
        }
    }

    // Marcar el nuevo servicio como seleccionado y actualizar cita.servicioEditable
    cita.servicioEditable = { id, nombre };
    divServicio.classList.add('seleccionado');

    // Actualizar el estado del botón "Confirmar"
    const paginaSiguiente = document.querySelector('#btn-confirmar-servicio');
    paginaSiguiente.disabled = !cita.servicioEditable;
}



async function confirmarServicioEdicion() {
    const servicio = cita.servicioEditable;
    const servicioIdAntiguo = cita.servicioIdAntiguo;
    const citaId = cita.citaId; 
    console.log(cita.servicioIdAntiguo);
    const spinner = document.getElementById("loading-spinner");
    spinner.classList.add("show");

    if (servicio) {
        try {
            const url = '/api/actualizarservicioeditable';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    servicioIdAntiguo: servicioIdAntiguo,
                    servicioIdNuevo: servicio.id,
                    citaId: citaId
                }),
            });

            if (response.ok) {
                // Verificar si el contenido de la respuesta es JSON antes de parsearlo
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    const result = await response.json();
                    console.log('Servicio actualizado:', result);

                    if (result.resultado) {
                        cerrarModalServicio();
                        cerrarModalEditarHistorial();
                        cerrarModalSeleccion();
                    } else {
                        console.error('Error en la actualización:', result.error);
                    }
                } else {
                    console.error('Error: Respuesta no es JSON');
                }
            } else {
                console.error('Error al actualizar el servicio:', response.status);
            }
        } catch (error) {
            console.error('Error al enviar la solicitud:', error);
        } finally {
            spinner.classList.remove("show");
        }
    } else {
        spinner.classList.remove("show");
    }
}


function cerrarModalSeleccion() {
    const modalSeleccion = document.getElementById('modal-seleccion-edicion');
    modalSeleccion.classList.remove('show');
    setTimeout(() => {
        modalSeleccion.style.display = 'none';
    }, 300); // Espera el tiempo de la transición
}


async function consultarAPIAgregar() {

    try {
        const url = '/api/servicios-agregar';
        const resultado = await fetch(url);
        const servicios = await resultado.json();
        mostrarServiciosAgregar(servicios);
        
        
        
    } catch (error) {
        console.log(error);
    }
}




async function consultarAPIEdicion() {

    try {
        const url = '/api/servicios-edicion';
        const resultado = await fetch(url);
        const servicios = await resultado.json();
        mostrarServiciosEdicion(servicios);
        
    } catch (error) {
        console.log(error);
    }
}

// async function consultarAPI() {

//     try {
//         const url = 'http://localhost:3000/api/servicios';
//         const resultado = await fetch(url);
//         const servicios = await resultado.json();
//         mostrarServicios(servicios);
//     } catch (error) {
//         console.log(error);
//     }
// }

// function mostrarServicios(servicios) {
//     servicios.forEach( servicio => {
//         const { id, nombre, precio } = servicio;

//         const nombreServicio = document.createElement('P');
//         nombreServicio.classList.add('nombre-servicio');
//         nombreServicio.textContent = nombre;

//         const precioServicio = document.createElement('P');
//         precioServicio.classList.add('precio-servicio');
//         precioServicio.textContent = `$${precio}`;

//         const servicioDiv = document.createElement('DIV');
//         servicioDiv.classList.add('servicio');
//         servicioDiv.dataset.idServicio = id;
//         servicioDiv.onclick = function() {
//             seleccionarServicio(servicio);
//         }

//         servicioDiv.appendChild(nombreServicio);
//         servicioDiv.appendChild(precioServicio);

//         document.querySelector('#servicios').appendChild(servicioDiv);

//     });
// }

const categorias = {
    cabello: ['Full Diseño', 'Corte Clasico', 'Corte Moderno', 'Corte Infante'],
    facial: ['Black Mask', 'Limpieza Facial Express', 'Tratamiento Anti-Edad'],
    combo: ['Corte + Barba'],
    barba: ['Recorte de Barba']
};


function seleccionarServicio(servicio) {
    const { id, nombre } = servicio;
    let { servicios } = cita;

    const divServicio = document.querySelector(`#servicios [data-id-servicio="${id}"]`);

    // Si el servicio ya está seleccionado, lo deseleccionamos
    if (servicios.some(agregado => agregado.id === id)) {
        cita.servicios = servicios.filter(agregado => agregado.id !== id);
        divServicio.classList.remove('seleccionado');

        if (nombre.toLowerCase().includes('corte infante')) {
            cita.infanteSeleccionado = 0;
            cita.cantidadInfantes = 1; // Resetear cantidad de infantes
            cita.observacionesInfante = "";
        }
    } else {
        // Aplicar reglas según la categoría del servicio
        if (categorias.combo.includes(nombre)) {
            cita.servicios = [servicio];
            cita.infanteSeleccionado = 0;
        } else if (nombre.toLowerCase().includes('corte infante')) {
            cita.servicios.push(servicio);
            cita.infanteSeleccionado = 1;
            abrirModalInfante(); // Aquí el usuario elige la cantidad de infantes
        } else if (categorias.cabello.includes(nombre)) {
            if (!cita.infanteSeleccionado) {
                cita.servicios = servicios.filter(serv => !categorias.cabello.includes(serv.nombre));
            }
            cita.servicios.push(servicio);
        } else if (categorias.facial.includes(nombre)) {
            cita.servicios = servicios.filter(serv => !categorias.facial.includes(serv.nombre));
            cita.servicios.push(servicio);
        } else if (categorias.barba.includes(nombre)) {
            cita.servicios.push(servicio);
        } else {
            cita.servicios.push(servicio);
        }

        // Recalcular duración y precio
        calcularDuracionServicios();
        calcularPrecioTotal();

        // Verificar si se superó el límite de 120 minutos
        if (cita.duracionTotal > 120) {
            Swal.fire({
                icon: 'warning',
                title: 'Límite alcanzado',
                text: 'No puedes seleccionar más servicios, el tiempo máximo es de 2 horas.',
                confirmButtonColor: '#d33',
            });

            // Remover el servicio que causó la superación
            cita.servicios = cita.servicios.filter(agregado => agregado.id !== id);
            divServicio.classList.remove('seleccionado');

            // Recalcular la duración después de quitar el servicio
            calcularDuracionServicios();
            calcularPrecioTotal();
            return;
        }

        // Agregar la clase de seleccionado al nuevo servicio
        divServicio.classList.add('seleccionado');
    }

    actualizarSeleccionVisual();

    // Controlar el estado del botón "Siguiente"
    const paginaSiguiente = document.querySelector('#siguiente');
    paginaSiguiente.disabled = cita.servicios.length === 0;

    // Mostrar en consola para depuración
    console.log("Datos guardados en cita:", cita);
}

// Esta función la llamas después de que el usuario elija la cantidad de infantes.
function actualizarPrecioPorCantidadInfantes() {
    // Recalcular precio total después de elegir cantidad de infantes
    calcularPrecioTotal();
    calcularDuracionServicios();
}

function calcularPrecioTotal() {
    let precioTotal = 0;

    cita.servicios.forEach(servicio => {
        if (servicio.nombre.toLowerCase().includes('corte infante')) {
            // Asegúrate de convertir precio a número y multiplicar por cantidad de infantes
            precioTotal += parseFloat(servicio.precio) * (cita.cantidadInfantes || 1);
        } else {
            precioTotal += parseFloat(servicio.precio); // Asegúrate de que precio sea un número
        }
    });

    cita.precioTotal = precioTotal;
    console.log("Precio total de la cita:", cita.precioTotal);
}

function calcularDuracionServicios() {
    let duracionTotal = 0;

    cita.servicios.forEach(servicio => {
        let duracion = parseInt(servicio.duracion, 10); // Convertir a número
        if (!isNaN(duracion)) { 
            if (servicio.nombre.toLowerCase().includes('corte infante')) {
                // Usar la cantidad de infantes almacenada en `cita`
                const cantidadInfantes = cita.cantidadInfantes || 1;
                duracionTotal += duracion * cantidadInfantes; // Multiplicar por la cantidad de infantes
            } else {
                duracionTotal += duracion;
            }
        }
    });

    // Actualizar el valor en la cita
    cita.duracionTotal = duracionTotal;
    console.log("Duración total de la cita:", cita.duracionTotal);
}

function abrirModalInfante() {
    console.log("Abriendo modal de infante...");

    // Crear el modal
    const modal = document.createElement('div');
    modal.classList.add('modal-infante');
    modal.innerHTML = `
        <div class="modal-contenido">
            <div class="modal-header">
                <h2>Seleccionar Corte Infante</h2>
                 <span  class="cerrar" onclick="cerrarModalInfante()">X</span>
            </div>
            <div class="modal-body">
                <label for="cantidadInfantes">Cantidad de infantes:</label>
                <input id="cantidadInfantes" type="number" min="1" max="4" value="1" class="input-estilizado">

                <label for="detallesInfante">Detalles del infante (opcional):</label>
                <textarea id="detallesInfante" maxlength="200" placeholder="Ejemplo: Tiene alergia a productos específicos..." class="textarea-estilizado"></textarea>
                
                <button id="confirmarCorteInfante" class="btn-confirmar">Confirmar</button>
                <button class="btn-cancelar" onclick="cerrarModalInfante()">Cancelar</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    console.log("Modal de infante añadido al DOM");

    // Capturar inputs
    const cantidadInfantesInput = document.getElementById('cantidadInfantes');
    const detallesInfanteInput = document.getElementById('detallesInfante');

    // Evento para actualizar valores
    cantidadInfantesInput.addEventListener('input', () => {
        let cantidad = parseInt(cantidadInfantesInput.value) || 1;
        cantidad = Math.max(1, Math.min(4, cantidad)); // Asegurar el rango 1-4
        cantidadInfantesInput.value = cantidad;

        // Actualizar la cantidad en cita
        cita.cantidadInfantes = cantidad;

        // Buscar el servicio 'Corte Infante' en cita.servicios
        const servicioInfante = cita.servicios.find(serv => serv.nombre.toLowerCase().includes('corte infante'));

        if (servicioInfante) {
            const precioBase = parseInt(servicioInfante.precio, 10) || 0;
            cita.precioTotal = precioBase * cantidad; // Multiplicar por la cantidad de infantes
            console.log("Precio total recalculado: $", cita.precioTotal);
        }

        // Llamar a la función para recalcular la duración
        calcularDuracionServicios();
        console.log("Duración total recalculada:", cita.duracionTotal);

        console.log("Cantidad de infantes seleccionada:", cantidad);
    });

    detallesInfanteInput.addEventListener('input', () => {
        // Solo actualizar la observación si se ha ingresado algo
        if (detallesInfanteInput.value.trim() !== '') {
            cita.observacionesInfante = detallesInfanteInput.value;
            console.log("Detalles del infante ingresados:", detallesInfanteInput.value);
        } else {
            // Si el campo está vacío, podemos opcionalmente borrar la observación
            cita.observacionesInfante = '';
            console.log("No se ingresaron detalles del infante.");
        }
    });

    // Botón de confirmación
    document.getElementById('confirmarCorteInfante').addEventListener('click', () => {
        console.log("Confirmando selección de Corte Infante...");
        cita.observacionesInfante = detallesInfanteInput.value;
        cita.cantidadInfantes = parseInt(cantidadInfantesInput.value) || 1;

        // Recalcular la duración y el precio con los nuevos valores
        actualizarPrecioPorCantidadInfantes();

        console.log("Datos guardados en cita:", cita);
        cerrarModalInfante();
    });

    modal.style.display = 'block';
    console.log("Modal de infante mostrado en pantalla.");
}

// Función para actualizar el resumen de duración y precio
function actualizarResumen() {
    console.log("Actualizando resumen...");

    const duracionCita = document.querySelector('.contenido-resumen p:nth-of-type(4)');
    const totalPrecioCita = document.querySelector('.contenido-resumen p:nth-of-type(5)');

    let totalPrecio = 0;
    cita.servicios.forEach(servicio => {
        let precioServicio = parseFloat(servicio.precio);

        if (servicio.nombre.toLowerCase().includes('corte infante')) {
            const cantidadInfantes = cita.cantidadInfantes;
            if (cantidadInfantes) {
                precioServicio *= parseInt(cantidadInfantes, 10);
            }
        }

        totalPrecio += precioServicio;
    });

    if (duracionCita) {
        duracionCita.innerHTML = `<span>Duración total:</span> ${cita.duracionTotal} minutos`;
        console.log("Duración total actualizada en el resumen:", cita.duracionTotal);
    }

    if (totalPrecioCita) {
        totalPrecioCita.innerHTML = `<span>Total precio:</span> $${totalPrecio.toFixed(2)}`;
        console.log("Precio total actualizado en el resumen: $", totalPrecio.toFixed(2));
    }
}


function cerrarModalInfante() {
    const modal = document.querySelector('.modal-infante');
    if (modal) {
        modal.style.display = 'none';
        modal.remove();
    }
}





// Función para actualizar la visualización de servicios seleccionados
function actualizarSeleccionVisual() {
    document.querySelectorAll('#servicios .seleccionado').forEach(div => {
        div.classList.remove('seleccionado');
    });

    cita.servicios.forEach(serv => {
        const divServicio = document.querySelector(`#servicios [data-id-servicio="${serv.id}"]`);
        if (divServicio) divServicio.classList.add('seleccionado');
    });
}


function idCliente() {
    const clienteId = document.querySelector('#id');
    console.log('Campo #id:', clienteId); // Verifica si el campo #id está presente en el DOM

    if (clienteId) {
        cita.id = clienteId.value;
        console.log('holaaaaaa', cita.id); // Esto debería mostrar el valor del ID
    } else {
        console.error("El campo #id no fue encontrado.");
    }
}


function nombreCliente() {
    cita.nombre = document.querySelector('#nombre').value;
}



var calendar2; // Variable global para el segundo calendario
function inicializarCalendarioEdicion() {
    var calendarEl = document.getElementById('calendar2');
    var fechaInput = document.getElementById('fecha');
    var modalSeleccionarTurno = document.getElementById('modal-seleccionar-turno-editar'); // Modal secundario
    const modalPrincipal = document.querySelector('#modal');

    // Obtener la fecha actual
    var today = new Date();
    today.setHours(0, 0, 0, 0); // Resetear las horas de la fecha actual

    // Fecha límite: 30 días a partir de hoy
    var thirtyDaysFromNow = new Date(today);
    thirtyDaysFromNow.setDate(today.getDate() + 30);

    // Fecha mínima: hoy (no permitir seleccionar fechas pasadas)
    var minDate = new Date(today);

    // Fecha máxima: 30 días desde hoy
    var maxDate = new Date(today);
    maxDate.setDate(today.getDate() + 30);

    // Definir el siguiente mes permitido
    var nextMonth = new Date(today);
    nextMonth.setMonth(today.getMonth() + 1);
    nextMonth.setDate(1); // Primer día del siguiente mes

    // Inicializar el calendario
    calendar2 = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        initialDate: today,
        headerToolbar: {
            left: 'prev',
            center: 'title',
            right: 'next'
        },
        selectable: true,
        selectMirror: true,
        events: [],
        validRange: {
            start: minDate,
            end: maxDate
        },
        dateClick: function(info) {
            var selectedDate = new Date(info.dateStr);
            selectedDate.setHours(0, 0, 0, 0); // Asegurarse de comparar solo la fecha, no la hora

            // Verificar si la fecha seleccionada está dentro del rango permitido
            if (selectedDate < minDate || selectedDate > maxDate) {
                return; // No permitir selección si la fecha es inválida
            }

            // Eliminar la clase 'selected' de cualquier otra fecha previamente seleccionada
            document.querySelectorAll('.fc-daygrid-day').forEach(function(dayCell) {
                dayCell.classList.remove('selected');
            });

            const dia = selectedDate.getUTCDay();
            if (![6].includes(dia)) { // Suponiendo que 6 representa el sábado
                // Añadir la clase 'selected' a la fecha seleccionada
                info.dayEl.classList.add('selected');

                // Actualizar el input con la fecha seleccionada
                fechaInput.value = info.dateStr;
                cita.fecha = info.dateStr; // Actualizar la fecha en la variable 'cita'
                
                ConsultarHorarios(cita.fecha);

                // Abrir el modal secundario
                modalSeleccionarTurno.style.display = 'block';
                modalSeleccionarTurno.classList.add('mostrar'); // Muestra el modal de selección de turno
                setTimeout(() => {
                    modalSeleccionarTurno.style.animation = 'slideIn 0.5s forwards'; // Animación de entrada
                }, 10);
                modalSeleccionarTurno.style.pointerEvents = 'auto'; // Asegura que se puedan interactuar los elementos
                
            }

            if ([6].includes(dia)) { // Si es sábado
                fechaInput.value = ''; // Limpiar el input si es fin de semana
                return;
            }
        },
        dayCellDidMount: function(info) {
            var currentDay = new Date(info.date);
            currentDay.setHours(0, 0, 0, 0); // Resetear las horas para que solo compare la fecha

            // Comparar con la fecha mínima y máxima para deshabilitar días
            if (currentDay < minDate || currentDay > maxDate) {
                info.el.classList.add('disabled-day'); // Añadir clase para deshabilitar el día
            }

            // Aplicar borde al día actual sin fondo
            if (currentDay.getTime() === today.getTime()) {
                info.el.classList.add('current-day'); // Añadir una clase especial al día actual
            }
        },
        datesSet: function(dateInfo) {
            const displayedMonth = dateInfo.start.getMonth(); // Mes mostrado
            const displayedYear = dateInfo.start.getFullYear(); // Año mostrado

            // Calcular el mes siguiente permitido
            const allowedNextMonth = new Date(today);
            allowedNextMonth.setMonth(today.getMonth() + 1);
            allowedNextMonth.setFullYear(today.getFullYear());

            // Calcular el primer día del mes siguiente mostrado
            const firstDayOfDisplayedMonth = new Date(displayedYear, displayedMonth, 1);

            // Determinar si estamos en el mes actual o el siguiente permitido
            const isCurrentMonth = (displayedMonth === today.getMonth() && displayedYear === today.getFullYear());
            const isNextAllowedMonth = (displayedMonth === allowedNextMonth.getMonth() && displayedYear === allowedNextMonth.getFullYear());

            // Desactivar el botón de mes anterior si estamos en el mes actual
            const prevButton = document.querySelector('.fc-prev-button');
            prevButton.disabled = isCurrentMonth;

            // Desactivar el botón de mes siguiente si estamos en el siguiente mes permitido
            const nextButton = document.querySelector('.fc-next-button');
            nextButton.disabled = isNextAllowedMonth;
        }
    });

    calendar2.render();
}



function editarFecha() {
    // Función para manejar la edición de fecha
    // console.log("Editar fecha seleccionado");
    cerrarModalSeleccion();
    
    const modalFecha = document.getElementById('modal-fecha');
    modalFecha.classList.add('show');
    modalFecha.style.display = 'block';
    setTimeout(function () {
        // console.log('Actualizando tamaño del calendario');
        if (calendar2) {
            calendar2.updateSize();  // Actualiza el tamaño con un retraso
        }
    }, 300); // Ajusta el tiempo según sea necesario
    // Mostrar el modal de selección de fecha
}

// Mostrar horarios según el turno seleccionado
function mostrarHorariosPosponer(turno) {
    const contenedorHorarios = document.querySelector('#horarios-posponer');
    contenedorHorarios.innerHTML = ''; // Limpiar horarios anteriores
    contenedorHorarios.style.display = 'flex';

    let horarios = [];
    const botonManana = document.querySelector('#turno-manana');
    const botonTarde = document.querySelector('#turno-tarde');

    // Definir horarios
    if (turno === 'manana') {
        horarios = generarHorarios('08:00', '11:30', 30); // Horarios de mañana
        botonManana.classList.add('seleccionado');
        botonTarde.classList.remove('seleccionado');
    } else {
        horarios = generarHorarios('14:00', '18:30', 30); // Horarios de tarde
        botonTarde.classList.add('seleccionado');
        botonManana.classList.remove('seleccionado');
    }

    // Generar botones de horarios
    horarios.forEach((hora, index) => {
        const botonHora = document.createElement('button');
        botonHora.classList.add('botones', 'horario', 'botones-horarios', 'oculto');
        botonHora.textContent = hora;
        botonHora.style.backgroundColor = turno === 'manana' ? '#28a745' : '#ff9800';

        botonHora.addEventListener('click', () => seleccionarHoraPosponer(hora));

        setTimeout(() => {
            botonHora.classList.remove('oculto');
            botonHora.classList.add('visible');
        }, index * 100);

        contenedorHorarios.appendChild(botonHora);
    });
}
function mostrarHorariosEdicion(turno) {
    const contenedorHorarios = document.querySelector('#horarios2');
    contenedorHorarios.innerHTML = '';  
    contenedorHorarios.style.display = 'block';

    let horarios = [];
    const botonManana = document.querySelector('#turno-manana');
    const botonTarde = document.querySelector('#turno-tarde');

    // Hora de cierre (dependiendo del turno)
    let horaCierre;
    if (turno === 'manana') {
        horarios = generarHorarios('08:00', '11:30', 30);
        botonManana.classList.add('seleccionado');
        botonTarde.classList.remove('seleccionado');
        horaCierre = moment('12:00', 'HH:mm');  // Cierre a las 12:00
    } else {
        horarios = generarHorarios('14:00', '18:30', 30);
        botonTarde.classList.add('seleccionado');
        botonManana.classList.remove('seleccionado');
        horaCierre = moment('19:00', 'HH:mm');  // Cierre a las 19:00
    }

    // Obtenemos la duración total de la cita
    const duracionTotal = cita.duracionTotal;
    console.log('Duración total de la cita:', duracionTotal);

    horarios.forEach((hora, index) => {
        const botonHora = document.createElement('BUTTON');
        botonHora.classList.add('botones', 'horario', 'botones-horarios');
        botonHora.textContent = hora;

        // Convertir la hora seleccionada y calcular su hora de fin según la duración
        const horaInicioSeleccionada = moment(hora, 'HH:mm');
        const horaFinSeleccionada = moment(hora, 'HH:mm').add(duracionTotal, 'minutes'); // Fin de la cita

        // Verificar si la hora de fin supera la hora de cierre para el turno correspondiente
        if (horaFinSeleccionada.isAfter(horaCierre)) {
            botonHora.setAttribute('disabled', 'true');
            botonHora.style.backgroundColor = '#ccc';  // Fondo gris para deshabilitarlo
            return; // No permitir seleccionar este horario
        }

        // Verificar si esta cita se superpone con horarios ocupados
        const estaDisponible = !horariosOcupados.some(({ horaInicio, horaFin, estado }) => {
            if (estado === 2) return false;  // Ignorar horarios con estado 2 (anulado)
        
            // Convertir horarios ocupados a formato 'moment.js'
            const horaInicioOcupada = moment(horaInicio, 'HH:mm');
            const horaFinOcupada = moment(horaFin, 'HH:mm');
        
            return (
                // La nueva cita comienza dentro de una cita ocupada
                horaInicioSeleccionada.isBetween(horaInicioOcupada, horaFinOcupada, null, '[)') ||
                // La nueva cita termina dentro de una cita ocupada
                horaFinSeleccionada.isBetween(horaInicioOcupada, horaFinOcupada, null, '(]') ||
                // La nueva cita cubre completamente una cita ocupada
                (horaInicioSeleccionada.isBefore(horaInicioOcupada) && horaFinSeleccionada.isAfter(horaFinOcupada)) ||
                // La nueva cita está completamente dentro de una cita ocupada
                (horaInicioSeleccionada.isSameOrAfter(horaInicioOcupada) && horaFinSeleccionada.isSameOrBefore(horaFinOcupada))
            );
        });

        // Si el horario está ocupado o no se puede utilizar, deshabilitar la casilla
        if (!estaDisponible) {
            botonHora.setAttribute('disabled', 'true');
            botonHora.style.backgroundColor = '#ccc';  // Fondo gris para deshabilitarlo
        } else {
            botonHora.addEventListener('click', () => seleccionarHoraEdicion(hora));
            botonHora.style.backgroundColor = turno === 'manana' ? '#4caf50' : '#e67e22';
        }

        setTimeout(() => {
            botonHora.classList.add('visible');
        }, index * 100);

        contenedorHorarios.appendChild(botonHora);
    });

    cita.hora = null;
    toggleConfirmButton();
}




function seleccionarHoraEdicion(hora) {
    // Evitar el reinicio de la página
    event.preventDefault();
    const modalPrincipal = document.querySelector('#modal');
    const botonesHorario = document.querySelectorAll('.horario');
    let botonCerrar = document.querySelector('#cerrar-modal-seleccionar');

    // Buscar el botón seleccionado
    const botonSeleccionado = Array.from(botonesHorario).find(boton => boton.textContent === hora);

    if (botonSeleccionado) {
        if (botonSeleccionado.classList.contains('seleccionado')) {
            console.log(`Deseleccionando la hora: ${hora}`);
            botonSeleccionado.classList.remove('seleccionado');
            cita.hora = null;
            cita.horaFinal = null; // Resetear la hora final

            const algunaSeleccionada = Array.from(botonesHorario).some(boton => boton.classList.contains('seleccionado'));
            if (!algunaSeleccionada) {
                botonCerrar.textContent = 'Cerrar';
                botonCerrar.classList.remove('confirmar-seleccion');
            }
        } else {
            console.log(`Seleccionando la hora: ${hora}`);
            botonesHorario.forEach(boton => boton.classList.remove('seleccionado'));
            botonSeleccionado.classList.add('seleccionado');
            cita.hora = hora; // Guardar la nueva hora seleccionada

            // Verificar si la hora original y la finalización existen
            if (cita.horaOriginal && cita.finalizacion) {
                // Convertir las horas en objetos moment
                const horaInicioOriginal = moment(cita.horaOriginal, 'HH:mm');
                const horaFinOriginal = moment(cita.finalizacion, 'HH:mm');

                // Calcular la duración original de la cita
                const duracionCitaOriginal = moment.duration(horaFinOriginal.diff(horaInicioOriginal));

                // Calcular la nueva hora de finalización con la nueva hora de inicio
                const horaInicioNueva = moment(hora, 'HH:mm');
                const horaFinNueva = horaInicioNueva.clone().add(duracionCitaOriginal); // Clonar y sumar la duración

                // Guardar la nueva hora de finalización
                cita.horaFinal = horaFinNueva.format('HH:mm');
            } else {
                cita.horaFinal = null; // Si faltan datos, borrar la hora final
            }

            console.log('Nueva hora de inicio:', cita.hora);
            console.log('Nueva hora de finalización:', cita.horaFinal);
            botonCerrar.textContent = 'Confirmar';
            botonCerrar.classList.add('confirmar-seleccion');
            console.log("Asignando evento click a botonCerrar");
            // Evento para confirmar selección y enviar datos
            botonCerrar.addEventListener('click', function () {
                console.log("Evento click ejecutado");
                const spinner = document.getElementById("loading-spinner");
                spinner.classList.add("show");
                if (cita.hora && cita.fecha) {
                    
                    console.log('Confirmando selección y enviando datos a la API...');

                    const citaid = cita.citaid;
                    const usuarioId = cita.usuarioId;
                    console.log(`Enviando Fecha: ${cita.fecha} y Hora: ${cita.hora}`, 'cita: ',citaid, 'usuario: ',usuarioId, 'enviando finalizacion nueva:', cita.horaFinal);
                    console.log('finalizacionnnn neuvaaaa: ', cita.horaFinal);
                    fetch('/api/actualizarturno', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ fecha: cita.fecha, hora: cita.hora, horaFinal: cita.horaFinal, citaid: citaid, usuarioId: usuarioId })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Respuesta del servidor:', data);
                        if (data.resultado) {
                            console.log('Cita actualizada exitosamente.');
                            cerrarModalSecun();
                            // Cerrar modales después de confirmar
                            document.querySelector('#modal-fecha').style.display = 'none';
                            document.querySelector('#modal-seleccionar-turno-editar').style.display = 'none';
                            document.querySelector('#blur-background').style.display = 'none';
                            document.querySelector('#blur-background3').style.display = 'none';
                            spinner.classList.remove("show");
                            abrirModalHistorial();
                            // Opcional: Resetear la selección
                            cita.fecha = null;
                            cita.hora = null;
                            cita.horaFinal = null;
                        } else {
                            console.error('Error en la actualización:', data.error);
                            alert('Ocurrió un error: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error al actualizar cita:', error.message);
                        alert('Ocurrió un error al actualizar la cita: ' + error.message);
                    });
                } else {
                    console.warn('Fecha o hora no seleccionadas. No se puede confirmar la cita.');
                    alert('Por favor selecciona una fecha y una hora antes de confirmar.');
                }

            }, { once: true }); // Agregar `once: true` para que el evento solo se ejecute una vez
        }
    } else {
        console.warn(`Hora ${hora} no encontrada en los botones de horario.`);
    }
}

function abrirModalHistorial() {
    fetch(`/api/historial-citas`)
        .then(response => {
            if (!response.ok) {
                throw new Error("Error en la solicitud al servidor");
            }
            return response.json();
        })
        .then(data => {

            const historialContent = document.getElementById("historial-content");
            historialContent.innerHTML = ""; // Limpiar contenido antes de agregar
            document.getElementById('blur-background').style.display = 'block';

            if (data.success) {
                // Filtrar las citas para excluir las anuladas (estado 2)
                const citasActivas = data.historial.filter(item => parseInt(item.estado) !== 2);
                
                // Si no hay citas activas, mostrar un mensaje
                if (citasActivas.length === 0) {
                    historialContent.innerText = "No hay citas disponibles.";
                } else {

                    // Agrupar las citas por fecha y hora
                    const citasAgrupadas = {};
                    citasActivas.forEach((item) => {
                        const fechaHora = `${item.fecha} ${item.hora.slice(0, 5)}`;
                        const finalizacion = item.finalizacion.slice(0, 5);  // Formateamos finalización a HH:MM

                        if (!citasAgrupadas[fechaHora]) {
                            citasAgrupadas[fechaHora] = [];
                        }
                        citasAgrupadas[fechaHora].push(item);
                    });

                    // Iterar sobre las citas agrupadas y crear los elementos
                    Object.keys(citasAgrupadas).forEach((fechaHora) => {
                        const grupoDiv = document.createElement("div");
                        grupoDiv.className = "grupo-cita";
                        const [fecha, hora] = fechaHora.split(" ");  // Extraemos fecha y hora
                        const finalizacion = citasAgrupadas[fechaHora][0].finalizacion.slice(0, 5);  // Formateamos finalización a HH:MM

                        // Crear el encabezado con la fecha, hora y finalización
                        grupoDiv.innerHTML = `
                        <div class="info-fecha-hora">
                            <div class="fecha-hora">
                                <div><strong>Fecha:</strong> <span class="item">${fecha}</span></div>
                                <div><strong>Hora:</strong> <span class="item">${hora}</span></div>
                                <div><strong>Finalización:</strong> <span class="item">${finalizacion}</span></div>
                            </div>
                            <div class="iconos-historial">
                            <i class="fas fa-pencil-alt icono-lapiz" title="Posponer fecha" data-id="${fechaHora}"></i>
                            <i class="fas fa-trash-alt icono-basura" title="Anular turno completo"></i>
                            </div>
                        </div>
                    `;
                    const iconoLapiz = grupoDiv.querySelector('.icono-lapiz');
                    iconoLapiz.addEventListener('click', function() {
                        const citaSeleccionada = citasAgrupadas[fechaHora][0]; // Obtener la primera cita del grupo
                    
                        console.log("Cita seleccionada:", citaSeleccionada); // Ver todos los datos que trae
                    
                        // Guardamos la información en la variable 'cita'
                        cita.citaid = citaSeleccionada.citaId;
                        cita.usuarioId = citaSeleccionada.usuarioId;
                        cita.horaOriginal = citaSeleccionada.hora.slice(0, 5); // Guardar la hora correctamente
                        cita.finalizacion = citaSeleccionada.finalizacion.slice(0, 5); // Formateamos HH:MM
                        cita.duracionTotal = citaSeleccionada.duracionTotal ?? "No disponible"; // Prevención de undefined
                    
                        console.log('La cita es:', cita.citaid, 
                                    'El usuario es:', cita.usuarioId, 
                                    'La hora es:', cita.horaOriginal, 
                                    'La finalización es:', cita.finalizacion, 
                                    'La duración total es:', cita.duracionTotal);
                    
                        editarFecha(); // Llamamos la función para modificar la fecha
                    });
                    
                    

                    const iconoBasura = grupoDiv.querySelector('.icono-basura');
                    iconoBasura.addEventListener('click', function() {
                        // Obtener el citaId desde el grupo de citas
                        const citaId = citasAgrupadas[fechaHora][0].citaId;
                        anularTurnoCompleto(citaId); // Llamar a la función de anulación
                    });

                    // Crear los servicios dentro de este grupo
                    citasAgrupadas[fechaHora].forEach((item) => {
                        const precioFormateado = parseInt(item.servicioPrecio).toLocaleString('es-AR', { style: 'currency', currency: 'ARS', minimumFractionDigits: 0 });

                        // Crear el elemento de servicio
                        const servicioDiv = document.createElement("div");
                        servicioDiv.className = "cita";
                        servicioDiv.setAttribute("data-id", item.servicioId);
                        servicioDiv.setAttribute("data-cita-id", item.citaId);
                        servicioDiv.innerHTML = `
                            <div class="info">
                                <div class="etiqueta">
                                    <i class="fas fa-concierge-bell"></i> <strong>Servicio:</strong> <span class="item">${item.servicioNombre || 'No especificado'}</span>
                                </div>
                            </div>
                            <div class="precio-container">
                                <span class="precio">${precioFormateado}</span>
                            </div>
                        `;

                        // Agregar servicio al grupo
                        grupoDiv.appendChild(servicioDiv);
                    });

                    // Verificar si el grupo tiene solo un servicio
                    if (citasAgrupadas[fechaHora].length === 1) {
                        esTurnoCompleto = true; // Si solo hay un servicio, actualizar la variable global
                        console.log(esTurnoCompleto);
                    }

                    // Agregar el grupo de citas al contenedor principal
                    historialContent.appendChild(grupoDiv);
                    });
                }
            } else {
                historialContent.innerText = "No se pudo cargar el historial de citas.";
            }
        })
        .catch(error => {
            document.getElementById("historial-content").innerText = "Ocurrió un error al obtener el historial de citas.";
            console.error("Error:", error);
        });
    
    const modal = document.getElementById("modal-historial");
    modal.style.display = "block";

    // Timeout para aplicar la clase de transición después de que esté visible
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}




function editarCita() {
    // Oculta el modal actual de edición de cita
   
//    document.getElementById('blur-background2').style.display = 'block';
    // Muestra el nuevo modal con las opciones de edición
    const modalSeleccion = document.getElementById('modal-seleccion-edicion');
    modalSeleccion.classList.add('show');
    modalSeleccion.style.display = 'block';

    document.getElementById('blur-background3').style.display = 'none'
    cerrarModalEditarHistorial();
   cerrarModalHistorial();
}

function cerrarModalHistorial() {
    const modal = document.getElementById('modal-historial');
    modal.classList.remove('show'); // Elimina la clase show
    modal.classList.add('hide'); // Agrega la clase hide
    document.getElementById('blur-background').style.display = 'none';
    // Espera a que la animación termine antes de ocultar el modal
    setTimeout(() => {
        modal.style.display = 'none'; // Oculta el modal
        
        modal.classList.remove('hide'); // Limpia la clase hide
    }, 250); // Tiempo de espera igual al tiempo de la transición
}

function abrirModalEditarHistorial(servicioId) {
    console.log('ID del servicio:', servicioId);  // Verificar el servicioId
    cita.servicioIdAntiguo = servicioId;
    fetch('/api/guardar-servicio-en-sesion', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ servicioId: servicioId })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("Error en la solicitud al servidor");
        }
        return response.json();
    })
    .then(data => {
        console.log(data);  // Verificar qué datos recibimos
        if (data.success) {
            obtenerDetallesServicio();
        } else {
            console.error(data.message || "No se pudo guardar el ID del servicio en la sesión");
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}
function obtenerDetallesServicio() {
    fetch('/api/historial-citas/detalles-servicio')
        .then(response => {
            if (!response.ok) {
                throw new Error("Error en la solicitud al servidor");
            }
            return response.json();
        })
        .then(data => {
            console.log(data);
            
            const editarContent = document.getElementById("editar-content");
            editarContent.innerHTML = ""; // Limpiar contenido antes de agregar

            if (data.success) {
                const item = data.servicio;
                const precioFormateado = parseInt(item.precio).toLocaleString('es-AR', { style: 'currency', currency: 'ARS', minimumFractionDigits: 0 });

                // Almacenar solo el nombre del servicio
                cita.servicioEditable = {
                    nombre: item.nombre,
                    duracion: item.duracion
                };

                editarContent.innerHTML = `
                    <div class="etiqueta"><strong>Servicio:</strong> <span class="item">${item.nombre || 'No especificado'}</span></div>
                    <div class="etiqueta"><strong>Precio:</strong> <span class="item">${precioFormateado}</span></div>
                `;

                const modalEditar = document.getElementById("modal-editar-historial");
                modalEditar.classList.remove('hide');
                modalEditar.classList.add('show');
                modalEditar.style.display = "block";
                cerrarModalHistorial();
                document.getElementById('blur-background3').style.display = 'block';
            } else {
                editarContent.innerText = "No se pudo cargar los detalles del servicio.";
            }
        })
        .catch(error => {
            console.error("Error:", error);
            document.getElementById("editar-content").innerText = "Ocurrió un error al obtener los detalles del servicio.";
        });
}



function obtenerDetallesCita() {
    fetch('/api/historial-citas/detalles')
        .then(response => {
            if (!response.ok) {
                throw new Error("Error en la solicitud al servidor");
            }
            return response.json();
        })
        .then(data => {
            const editarContent = document.getElementById("editar-content");
            editarContent.innerHTML = ""; // Limpiar contenido antes de agregar

            if (data.success) {
                const item = data.cita;
                const precioFormateado = parseInt(item.servicioPrecio).toLocaleString('es-AR', { style: 'currency', currency: 'ARS', minimumFractionDigits: 0 });
                const horaFormateada = item.hora.slice(0, 5);
                cita.servicioIdAntiguo = item.servicioId;
                cita.fechaAntigua= item.fecha;
                cita.id = item.citaId
                console.log(cita.id);
                // Mueve aquí la lógica para mostrar el modal después de que el contenido haya sido agregado
            } else {
                editarContent.innerText = "No se pudo cargar los detalles de la cita.";
            }
        })
        .catch(error => {
            console.error("Error:", error);
            document.getElementById("editar-content").innerText = "Ocurrió un error al obtener los detalles de la cita.";
        });
}

function cerrarModalEditarHistorial() {
    const modalEditar = document.getElementById('modal-editar-historial');
    document.getElementById('blur-background3').style.display = 'none';
    modalEditar.classList.remove('show');
    modalEditar.classList.add('hide');
    abrirModalHistorial();
    setTimeout(() => {
        modalEditar.style.display = 'none';
    }, 300); // Tiempo de espera igual al tiempo de la transición

    // Limpiar la selección de los servicios
    const serviciosDiv = document.querySelector('#servicios-edicion');
    const serviciosSeleccionados = serviciosDiv.querySelectorAll('.seleccionado');
    serviciosSeleccionados.forEach(servicio => {
        servicio.classList.remove('seleccionado'); // Quitar la clase de seleccionado
    });

    // Limpiar el objeto cita.servicioEditable
    cita.servicioEditable = null;

    // Deshabilitar el botón de "Confirmar" ya que no hay servicio seleccionado
    const paginaSiguiente = document.querySelector('#btn-confirmar-servicio');
    paginaSiguiente.disabled = true;
}



let calendar;


function inicializarCalendario() {
    var calendarEl = document.getElementById('calendar');
    var fechaInput = document.getElementById('fechaprincipal');
    var modalSeleccionarTurno = document.getElementById('modal-seleccionar-turno'); // Modal secundario
    const modalPrincipal = document.querySelector('#modal');

    // Obtener la fecha actual
    var today = new Date();
    today.setHours(0, 0, 0, 0); // Resetear las horas de la fecha actual

    // Fecha límite: 30 días a partir de hoy
    var thirtyDaysFromNow = new Date(today);
    thirtyDaysFromNow.setDate(today.getDate() + 30);

    // Fecha mínima: hoy (permitir seleccionar la fecha actual)
    var minDate = new Date(today);

    // Fecha máxima: 30 días desde hoy
    var maxDate = new Date(today);
    maxDate.setDate(today.getDate() + 30);

    // Inicializar el calendario
    calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        initialDate: today,
        headerToolbar: {
            left: 'prev',
            center: 'title',
            right: 'next'
        },
        selectable: true,
        selectMirror: true,
        events: [],
        validRange: {
            start: minDate, // Permitir seleccionar desde hoy
            end: maxDate
        },
        dateClick: function(info) {
            var selectedDate = new Date(info.dateStr);
            selectedDate.setHours(0, 0, 0, 0); // Asegurarse de comparar solo la fecha, no la hora

            // Verificar si la fecha seleccionada está dentro del rango permitido
            if (selectedDate < minDate || selectedDate > maxDate) {
                return; // No permitir selección si la fecha es inválida
            }

            // Eliminar la clase 'selected' de cualquier otra fecha previamente seleccionada
            document.querySelectorAll('.fc-daygrid-day').forEach(function(dayCell) {
                dayCell.classList.remove('selected');
            });

            const dia = selectedDate.getUTCDay();
            if (![6].includes(dia)) { // Suponiendo que 6 representa el sábado
                // Añadir la clase 'selected' a la fecha seleccionada
                info.dayEl.classList.add('selected');

                // Actualizar el input con la fecha seleccionada
                fechaInput.value = info.dateStr;
                cita.fecha = info.dateStr; // Actualizar la fecha en la variable 'cita'

                ConsultarHorarios(cita.fecha);

                // Abrir el modal secundario
                modalSeleccionarTurno.style.display = 'block';
                modalSeleccionarTurno.classList.add('mostrar'); // Muestra el modal de selección de turno
                setTimeout(() => {
                    modalSeleccionarTurno.style.animation = 'slideIn 0.5s forwards'; // Animación de entrada
                }, 10);
                modalSeleccionarTurno.style.pointerEvents = 'auto'; // Asegura que se puedan interactuar los elementos
            }

            if ([6].includes(dia)) { // Si es sábado
                fechaInput.value = ''; // Limpiar el input si es fin de semana
                return;
            }
        },
        dayCellDidMount: function(info) {
            var currentDay = new Date(info.date);
            currentDay.setHours(0, 0, 0, 0); // Resetear las horas para que solo compare la fecha

            // Comparar con la fecha mínima y máxima para deshabilitar días
            if (currentDay < minDate || currentDay > maxDate) {
                info.el.classList.add('disabled-day'); // Añadir clase para deshabilitar el día
            }

            // Aplicar borde al día actual sin fondo
            if (currentDay.getTime() === today.getTime()) {
                info.el.classList.add('current-day'); // Añadir una clase especial al día actual
            }
        },
        datesSet: function(dateInfo) {
            const displayedMonth = dateInfo.start.getMonth(); // Mes mostrado
            const displayedYear = dateInfo.start.getFullYear(); // Año mostrado

            // Calcular el mes siguiente permitido
            const allowedNextMonth = new Date(today);
            allowedNextMonth.setMonth(today.getMonth() + 1);
            allowedNextMonth.setFullYear(today.getFullYear());

            // Calcular el primer día del mes siguiente mostrado
            const firstDayOfDisplayedMonth = new Date(displayedYear, displayedMonth, 1);

            // Determinar si estamos en el mes actual o el siguiente permitido
            const isCurrentMonth = (displayedMonth === today.getMonth() && displayedYear === today.getFullYear());
            const isNextAllowedMonth = (displayedMonth === allowedNextMonth.getMonth() && displayedYear === allowedNextMonth.getFullYear());

            // Desactivar el botón de mes anterior si estamos en el mes actual
            const prevButton = document.querySelector('.fc-prev-button');
            prevButton.disabled = isCurrentMonth;

            // Desactivar el botón de mes siguiente si estamos en el siguiente mes permitido
            const nextButton = document.querySelector('.fc-next-button');
            nextButton.disabled = isNextAllowedMonth;
        }
    });

    calendar.render();
}



let horariosOcupados = [];  // Almacenar horarios ocupados con inicio y fin

function ConsultarHorarios(fechaSeleccionada) {
    if (!fechaSeleccionada) {
        console.error('No se ha seleccionado una fecha válida.');
        return;
    }

    fetch('/api/consultarHorarios', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ fecha: fechaSeleccionada })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }

        // Guardar horarios ocupados con hora de inicio y finalización prevista
        horariosOcupados = data.map(item => {
            // Verificar si las propiedades 'hora' y 'finalizacion' están presentes y no son null o undefined
            const horaInicio = item.hora ? item.hora.slice(0, 5) : '';  // Hora de inicio en formato HH:mm
            const horaFin = item.finalizacion ? item.finalizacion.slice(0, 5) : '';  // Hora de finalización en formato HH:mm (ajustado para "finalizacion")

            return {
                horaInicio,
                horaFin,
                estado: parseInt(item.estado, 10)
            };
        });
        console.log('Horarios ocupados:', horariosOcupados);
    })
    .catch(error => {
        console.error('Error al obtener los horarios:', error);
    });
}

function mostrarHorarios(turno) {
    const contenedorHorarios = document.querySelector('#horarios');
    contenedorHorarios.innerHTML = '';
    contenedorHorarios.style.display = 'block';

    let horarios = [];
    const botonManana = document.querySelector('#turno-manana');
    const botonTarde = document.querySelector('#turno-tarde');

    // Hora de cierre (dependiendo del turno)
    let horaCierre;
    if (turno === 'manana') {
        horarios = generarHorarios('08:00', '11:30', 30);
        botonManana.classList.add('seleccionado');
        botonTarde.classList.remove('seleccionado');
        horaCierre = moment('12:00', 'HH:mm');  // Cierre a las 12:00
    } else {
        horarios = generarHorarios('14:00', '18:30', 30);
        botonTarde.classList.add('seleccionado');
        botonManana.classList.remove('seleccionado');
        horaCierre = moment('19:00', 'HH:mm');  // Cierre a las 19:00
    }

    // Obtenemos la duración total de la cita
    const duracionTotal = cita.duracionTotal;
    console.log('Duración total de la cita:', duracionTotal);

    horarios.forEach((hora, index) => {
        const botonHora = document.createElement('BUTTON');
        botonHora.classList.add('botones', 'horario', 'botones-horarios');
        botonHora.textContent = hora;

        // Convertir la hora seleccionada y calcular su hora de fin según la duración
        const horaInicioSeleccionada = moment(hora, 'HH:mm');
        const horaFinSeleccionada = moment(hora, 'HH:mm').add(duracionTotal, 'minutes'); // Fin de la cita

        // Verificar si la hora de fin supera la hora de cierre para el turno correspondiente
        if (horaFinSeleccionada.isAfter(horaCierre)) {
            botonHora.setAttribute('disabled', 'true');
            botonHora.style.backgroundColor = '#ccc';  // Fondo gris para deshabilitarlo
            return; // No permitir seleccionar este horario
        }

        // Verificar si esta cita se superpone con horarios ocupados
        const estaDisponible = !horariosOcupados.some(({ horaInicio, horaFin, estado }) => {
            if (estado === 2) return false;  // Ignorar horarios con estado 2 (anulado)
        
            // Convertir horarios ocupados a formato 'moment.js'
            const horaInicioOcupada = moment(horaInicio, 'HH:mm');
            const horaFinOcupada = moment(horaFin, 'HH:mm');
        
            return (
                // La nueva cita comienza dentro de una cita ocupada
                horaInicioSeleccionada.isBetween(horaInicioOcupada, horaFinOcupada, null, '[)') ||
                // La nueva cita termina dentro de una cita ocupada
                horaFinSeleccionada.isBetween(horaInicioOcupada, horaFinOcupada, null, '(]') ||
                // La nueva cita cubre completamente una cita ocupada
                (horaInicioSeleccionada.isBefore(horaInicioOcupada) && horaFinSeleccionada.isAfter(horaFinOcupada)) ||
                // La nueva cita está completamente dentro de una cita ocupada
                (horaInicioSeleccionada.isSameOrAfter(horaInicioOcupada) && horaFinSeleccionada.isSameOrBefore(horaFinOcupada))
            );
        });
        

        // Si el horario está ocupado o no se puede utilizar, deshabilitar la casilla
        if (!estaDisponible) {
            botonHora.setAttribute('disabled', 'true');
            botonHora.style.backgroundColor = '#ccc';  // Fondo gris para deshabilitarlo
        } else {
            botonHora.addEventListener('click', () => seleccionarHora(hora));
            botonHora.style.backgroundColor = turno === 'manana' ? '#4caf50' : '#ff9800';
        }

        setTimeout(() => {
            botonHora.classList.add('visible');
        }, index * 100);

        contenedorHorarios.appendChild(botonHora);
    });

    cita.hora = null;
    toggleConfirmButton();
}




function generarHorarios(inicio, fin, intervalo) {
    let horarios = [];
    let horaActual = moment(inicio, 'HH:mm');
    let horaFinal = moment(fin, 'HH:mm');
    
    while (horaActual <= horaFinal) {
        horarios.push(horaActual.format('HH:mm'));
        horaActual.add(intervalo, 'minutes');
    }
    
    return horarios;
}
function toggleConfirmButton() {
    const confirmarButton = document.getElementById('confirmar-modal-seleccionar');
    if (cita.hora) {
        confirmarButton.removeAttribute('disabled');  // Habilitar el botón
    } else {
        confirmarButton.setAttribute('disabled', 'true');  // Deshabilitar el botón
    }
}

// Función para seleccionar la hora
function seleccionarHora(hora) {
    const botonesHorario = document.querySelectorAll('.horario');
    const botonCerrar = document.querySelector('#cerrar-modal-seleccionar'); // Botón "Cerrar"
    
    // Buscar el botón seleccionado
    const botonSeleccionado = Array.from(botonesHorario).find(boton => boton.textContent === hora);

    if (botonSeleccionado) {
        // Si el botón ya está seleccionado, deseleccionarlo
        if (botonSeleccionado.classList.contains('seleccionado')) {
            botonSeleccionado.classList.remove('seleccionado');
            cita.hora = null; // Limpiar la hora seleccionada
        } else {
            // Si no está seleccionado, seleccionarlo
            botonesHorario.forEach(boton => boton.classList.remove('seleccionado')); // Deseleccionar otros botones
            botonSeleccionado.classList.add('seleccionado'); // Seleccionar el botón actual
            cita.hora = hora; // Guardar la hora seleccionada
            console.log(cita.hora); // Verificar que cita.hora tiene el valor correcto
        }
        calcularFinalizacionPrevista();
        // Llamamos a la función para habilitar o deshabilitar el botón "Confirmar"
        toggleConfirmButton();
    }
}

function confirmar() {
    if (cita.hora && cita.fecha) {
        if (cita.servicios && cita.servicios.length > 0) {
            // Nueva cita: se seleccionaron servicios
            console.log('Creando nueva cita');
            paso = 3;
            console.log('Cita confirmada con servicios:', cita.servicios);
            botonesPaginador(); // Llamar a la función de navegación para actualizar botones
        } else {
            const spinner = document.getElementById("loading-spinner");
            spinner.classList.add("show");
            // Posponiendo cita: solo se editó fecha y hora
            console.log('Posponiendo cita');
            console.log(cita.fecha, cita.hora);
            const citaid = cita.citaid;
            const usuarioId = cita.usuarioId;
            console.log(`Enviando Fecha: ${cita.fecha} y Hora: ${cita.hora}`, 'cita: ', citaid, 'usuario: ', usuarioId, 'nueva finalizacion: ', cita.horaFinal);
            
            fetch('/api/actualizarturno', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    fecha: cita.fecha, 
                    hora: cita.hora, 
                    horaFinal: cita.horaFinal, 
                    citaid: citaid, 
                    usuarioId: usuarioId 
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                console.log('Respuesta del servidor:', data);
                if (data.resultado) {
                    console.log('Cita actualizada exitosamente.');

                    // Cerrar modales después de confirmar
                    document.querySelector('#modal-fecha').style.display = 'none';
                    document.querySelector('#modal-seleccion-edicion').style.display = 'none';
                    document.querySelector('#blur-background').style.display = 'none';
                    document.querySelector('#blur-background3').style.display = 'none';
                    spinner.classList.remove("show");

                    // Mostrar alerta con SweetAlert2 y redirigir
                    Swal.fire({
                        title: "¡Cita Actualizada!",
                        text: "Tu cita ha sido reprogramada exitosamente.",
                        icon: "success",
                        confirmButtonText: "Aceptar",
                        timer: 3000, // La alerta se cerrará automáticamente en 3 segundos
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = "/cita"; // Redirigir a la página de citas
                    });

                    // Opcional: Resetear la selección
                    cita.fecha = null;
                    cita.hora = null;
                } else {
                    console.error('Error en la actualización:', data.error);
                    Swal.fire({
                        title: "Error",
                        text: "Ocurrió un problema al actualizar la cita.",
                        icon: "error",
                        confirmButtonText: "Aceptar"
                    });
                }
            })
            .catch(error => {
                console.error('Error al actualizar cita:', error.message);
                Swal.fire({
                    title: "Error",
                    text: "Ocurrió un error al actualizar la cita: " + error.message,
                    icon: "error",
                    confirmButtonText: "Aceptar"
                });
            });
        }

        cerrarModalSecundario(); // Cerrar el modal secundario
    } else {
        console.log('Faltan datos: Hora y fecha son necesarios');
        Swal.fire({
            title: "Atención",
            text: "Debes seleccionar una fecha y una hora para confirmar la cita.",
            icon: "warning",
            confirmButtonText: "Aceptar"
        });
    }
}


function cerrarModalSecun() {
    const modalPrincipal = document.getElementById('modal');
    const modalSecundario = document.getElementById('modal-seleccionar-turno-editar');
    const darkBackground = document.querySelector('.dark-background');
    const blurBackground = document.getElementById('blur-background');  // Fondo oscuro

    modalSecundario.style.animation = 'slideOut 0.5s forwards'; // Animación de salida

    // Desactivar interacciones con el modal principal mientras el secundario se cierra
    modalSecundario.style.pointerEvents = 'none';
    darkBackground.style.display = "none";  // Asegura que el fondo oscuro desaparezca

    // Después de la animación, restablecer la visibilidad y la interacción
    setTimeout(() => {
        modalSecundario.style.display = 'none';  // Oculta el modal secundario
        modalSecundario.classList.remove('mostrar');  // Elimina la clase de visibilidad
        modalSecundario.style.pointerEvents = 'none';  // Asegura que no haya interacciones mientras está cerrado

        // Restaurar el modal principal
        modalPrincipal.style.pointerEvents = 'auto';  // Habilitar interacciones en el modal principal
        blurBackground.style.display = 'block';  // Mostrar el fondo desenfocado

    }, 500);  // Tiempo coincidente con la duración de la animación
}

function cerrarModalSecundario() {
    const modalPrincipal = document.getElementById('modal');
    const modalSecundario = document.getElementById('modal-seleccionar-turno');
    const darkBackground = document.querySelector('.dark-background');
    const blurBackground = document.getElementById('blur-background');  // Fondo oscuro

    modalSecundario.style.animation = 'slideOut 0.5s forwards'; // Animación de salida
   
    // Desactivar interacciones con el modal principal mientras el secundario se cierra
    modalSecundario.style.pointerEvents = 'none';
    darkBackground.style.display = "none";  // Asegura que el fondo oscuro desaparezca

    // Después de la animación, restablecer la visibilidad y la interacción
    setTimeout(() => {
        modalSecundario.style.display = 'none';  // Oculta el modal secundario
        modalSecundario.classList.remove('mostrar');  // Elimina la clase de visibilidad
        modalSecundario.style.pointerEvents = 'none';  // Asegura que no haya interacciones mientras está cerrado
 // Solo llamar a mostrarHorarios si hay una fecha seleccionada
 if (cita.fecha) {
    mostrarHorarios(cita.turno || 'manana');
}
        // Restaurar el modal principal
        modalPrincipal.style.pointerEvents = 'auto';  // Habilitar interacciones en el modal principal
        blurBackground.style.display = 'block';  // Mostrar el fondo desenfocado

        // *** Restablecer el estado del modal ***
      

    }, 500);  // Tiempo coincidente con la duración de la animación
}




async function consultarHorariosOcupados(fecha) {
    const url = `/api/horarios-ocupados?fecha=${fecha}`;
    const resultado = await fetch(url);
    const horariosOcupados = await resultado.json();
    
    // Filtrar los horarios disponibles
    const horariosDisponibles = horarios.filter(hora => !horariosOcupados.includes(hora));
    mostrarHorariosDisponibles(horariosDisponibles);
}





function seleccionarFecha() {
    const inputFecha = document.querySelector('#fecha');
    inputFecha.addEventListener('input', function(e) {

        const dia = new Date(e.target.value).getUTCDay();

        if( [6, 0].includes(dia) ) {
            e.target.value = '';
            mostrarAlerta('Fines de semana no permitidos', 'error', '.formulario');
        } else {
            cita.fecha = e.target.value;
        }
        
    });
}



function mostrarAlerta(mensaje, tipo, elemento, desaparece = true) {

    // Previene que se generen más de 1 alerta
    const alertaPrevia = document.querySelector('.alerta');
    if(alertaPrevia) {
        alertaPrevia.remove();
    }

    // Scripting para crear la alerta
    const alerta = document.createElement('DIV');
    alerta.textContent = mensaje;
    alerta.classList.add('alerta');
    alerta.classList.add(tipo);

    const referencia = document.querySelector(elemento);
    referencia.appendChild(alerta);

    if(desaparece) {
        // Eliminar la alerta
        setTimeout(() => {
            alerta.remove();
        }, 3000);
    }
  
}

function mostrarResumen() {
    const resumen = document.querySelector('.contenido-resumen');
    console.log('Cita actual:', cita);

    // Limpiar el Contenido de Resumen
    while (resumen.firstChild) {
        resumen.removeChild(resumen.firstChild);
    }

    if (!cita.nombre || !cita.fecha || !cita.hora || cita.servicios.length === 0) {
        mostrarAlerta('Faltan datos de Servicios, Fecha u Hora', 'error', '.contenido-resumen', false);
        return;
    }

    // Calcular finalización prevista
    calcularFinalizacionPrevista();

    // Sumar los precios de los servicios (teniendo en cuenta la cantidad de infantes)
    let totalPrecio = 0;
    cita.servicios.forEach(servicio => {
        if (servicio.nombre.toLowerCase().includes('corte infante')) {
            // Si es un "Corte Infante", multiplicamos el precio por la cantidad de infantes
            totalPrecio += parseFloat(servicio.precio) * cita.cantidadInfantes;
        } else {
            // Si no es un "Corte Infante", sumamos el precio normalmente
            totalPrecio += parseFloat(servicio.precio);
        }
    });

    const { nombre, fecha, hora, servicios } = cita;

    // Heading Servicios
    const headingServicios = document.createElement('H3');
    headingServicios.textContent = 'Resumen de Servicios';
    resumen.appendChild(headingServicios);

    let tieneCorteInfante = false;

    servicios.forEach(servicio => {
        const { precio, nombre } = servicio;
        const contenedorServicio = document.createElement('DIV');
        contenedorServicio.classList.add('contenedor-servicio');

        const textoServicio = document.createElement('P');
        textoServicio.textContent = nombre;

        const precioServicio = document.createElement('P');
        let precioMostrar = precio;

        if (nombre.toLowerCase().includes('corte infante')) {
            // Si es un corte infante, mostramos el precio por infante
            precioMostrar = (parseFloat(precio) * cita.cantidadInfantes).toFixed(2);
        }

        precioServicio.innerHTML = `<span>Precio:</span> $${precioMostrar}`;

        contenedorServicio.appendChild(textoServicio);
        contenedorServicio.appendChild(precioServicio);
        resumen.appendChild(contenedorServicio);

        // Verificar si es un "Corte Infante"
        if (nombre.toLowerCase().includes('corte infante')) {
            tieneCorteInfante = true;
        }
    });

    // Mostrar cantidad de infantes y observaciones si se seleccionó "Corte Infante"
    if (tieneCorteInfante) {
        if (cita.cantidadInfantes > 0) {
            const cantidadInfantesTexto = document.createElement('P');
            cantidadInfantesTexto.innerHTML = `<span>Cantidad de infantes:</span> ${cita.cantidadInfantes}`;
            resumen.appendChild(cantidadInfantesTexto);
        }

        if (cita.observacionesInfante && cita.observacionesInfante.trim() !== '') {
            const observacionesInfante = document.createElement('P');
            observacionesInfante.innerHTML = `<span>Observaciones del infante:</span> ${cita.observacionesInfante}`;
            resumen.appendChild(observacionesInfante);
        }
    }

    // Heading Cita
    const headingCita = document.createElement('H3');
    headingCita.textContent = 'Resumen de Cita';
    resumen.appendChild(headingCita);

    const nombreCliente = document.createElement('P');
    nombreCliente.innerHTML = `<span>Nombre:</span> ${nombre}`;

    const fechaObj = new Date(fecha);
    const mes = fechaObj.getMonth();
    const dia = fechaObj.getDate() + 2;
    const year = fechaObj.getFullYear();

    const fechaUTC = new Date(Date.UTC(year, mes, dia));
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const fechaFormateada = fechaUTC.toLocaleDateString('es-AR', opciones);

    const fechaCita = document.createElement('P');
    fechaCita.innerHTML = `<span>Fecha:</span> ${fechaFormateada}`;

    const horaCita = document.createElement('P');
    horaCita.innerHTML = `<span>Hora:</span> ${hora} Horas`;

    const duracionCita = document.createElement('P');
    duracionCita.innerHTML = `<span>Duración total:</span> ${cita.duracionTotal} minutos`;

    const finalizacionCita = document.createElement('P');
    finalizacionCita.innerHTML = `<span>Finalización prevista:</span> ${cita.finalizacionPrevista}`;

    const totalPrecioCita = document.createElement('P');
    totalPrecioCita.innerHTML = `<span>Total precio:</span> $${totalPrecio.toFixed(2)}`;

    // Botón Reservar
    const botonReservar = document.createElement('BUTTON');
    botonReservar.classList.add('boton');
    botonReservar.textContent = 'Reservar Cita';
    botonReservar.onclick = reservarCita;

    resumen.appendChild(nombreCliente);
    resumen.appendChild(fechaCita);
    resumen.appendChild(horaCita);
    resumen.appendChild(duracionCita);
    resumen.appendChild(finalizacionCita); // Aquí agregas la finalización prevista
    resumen.appendChild(totalPrecioCita);
    resumen.appendChild(botonReservar);
}



// function actualizarResumen() {
//     const duracionCita = document.querySelector('.contenido-resumen p:nth-of-type(4)');
//     const totalPrecioCita = document.querySelector('.contenido-resumen p:nth-of-type(5)'); // Seleccionar el precio total

//     // Calcular nuevamente el precio total sumando los precios de los servicios
//     let totalPrecio = 0;
//     cita.servicios.forEach(servicio => {
//         let precioServicio = parseFloat(servicio.precio);

//         // Si es un "Corte Infante", multiplicar por la cantidad de infantes
//         if (servicio.nombre.toLowerCase().includes('corte infante')) {
//             const cantidadInfantes = document.getElementById('cantidadInfantes');
//             if (cantidadInfantes) {
//                 precioServicio *= parseInt(cantidadInfantes.value, 10);
//             }
//         }

//         totalPrecio += precioServicio;
//     });

//     // Actualizar duración
//     if (duracionCita) {
//         duracionCita.innerHTML = `<span>Duración total:</span> ${cita.duracionTotal} minutos`;
//     }

//     // Actualizar precio total
//     if (totalPrecioCita) {
//         totalPrecioCita.innerHTML = `<span>Total precio:</span> $${totalPrecio.toFixed(2)}`;
//     }
// }

function calcularFinalizacionPrevista() {
    if (!cita.hora || !cita.duracionTotal) {
        console.warn("Faltan datos para calcular la finalización prevista.");
        return;
    }

    // Construir la fecha y hora inicial
    let [hora, minutos] = cita.hora.split(":").map(Number);
    let fechaHoraInicio = new Date(cita.fecha + "T" + cita.hora + ":00");

    // Sumar la duración total en minutos
    fechaHoraInicio.setMinutes(fechaHoraInicio.getMinutes() + cita.duracionTotal);

    // Obtener la hora y minutos finales
    let horaFin = fechaHoraInicio.getHours();
    let minutosFin = fechaHoraInicio.getMinutes();

    // 🔹 Redondear los minutos al siguiente múltiplo de 30
    if (minutosFin % 30 !== 0) {
        minutosFin = Math.ceil(minutosFin / 30) * 30; // Redondea hacia arriba

        // Si se pasa de 60 minutos, sumar una hora y ajustar minutos a 00
        if (minutosFin === 60) {
            minutosFin = 0;
            horaFin += 1;
        }
    }

    // Convertir a formato HH:MM
    let horaFinalStr = horaFin.toString().padStart(2, '0');
    let minutosFinalStr = minutosFin.toString().padStart(2, '0');

    // Guardar la finalización prevista
    cita.finalizacionPrevista = `${horaFinalStr}:${minutosFinalStr}`;
    
    console.log("Finalización prevista redondeada:", cita.finalizacionPrevista);
}







async function reservarCita() {
    // Asegurarnos de que la finalización prevista se haya calculado antes de proceder
    calcularFinalizacionPrevista();

    const { nombre, fecha, hora, servicios, id, duracionTotal, observacionesInfante, finalizacionPrevista } = cita;

    if (!fecha || !hora || servicios.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "Atención",
            text: "Debes seleccionar una fecha, una hora y al menos un servicio antes de reservar.",
            confirmButtonText: "Aceptar"
        });
        return;
    }

    const idServicios = servicios.map(servicio => servicio.id);
    const spinner = document.getElementById("loading-spinner");
    spinner.classList.add("show");

    const datos = new FormData();
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('usuarioId', id);
    datos.append('servicios', idServicios);
    datos.append('duracionTotal', duracionTotal);
    datos.append('observacionesInfante', observacionesInfante || ''); // 🔹 Agregamos la observación
    datos.append('finalizacionPrevista', finalizacionPrevista); // 🔹 Agregar la finalización prevista

    console.log([...datos]);

    try {
        const url = '/api/citas';
        const respuesta = await fetch(url, {
            method: 'POST',
            body: datos
        });

        const resultado = await respuesta.json();
        console.log(resultado);
       // spinner.classList.remove("show");

        if (resultado.resultado) {
            Swal.fire({
                icon: "success",
                title: "Cita Creada",
                text: "Tu cita fue creada correctamente.",
                confirmButtonText: "Aceptar",
                timer: 3000,
                timerProgressBar: true
            }).then(() => {
                window.location.href = "/cita"; // Redirige a la página de citas
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo crear la cita. Inténtalo de nuevo."
            });
        }
    } catch (error) {
        console.error("Error al reservar cita:", error);
        spinner.classList.remove("show");
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Hubo un error al guardar la cita. Por favor, inténtalo de nuevo."
        });
    }
}

