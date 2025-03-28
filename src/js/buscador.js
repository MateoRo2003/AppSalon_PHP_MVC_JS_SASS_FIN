const citaAdmin = {
    id: '',
    nombre: '',
    fecha: '',
    hora: '',
    servicios: []
}


document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

function iniciarApp() {
    // buscarPorFecha();
    
    generarHorarios();
    
    gestionarModalObservaciones();
     mostrarHorariosCrear();
    //mostrarHorariosPosponer();
}


function ConfirmarHoraCrear(){
    calcularFinalizacionPrevista();
     // Asegúrate de que las variables citaAdmin.fecha y citaAdmin.hora contienen los valores seleccionados
     const fechaInput = document.getElementById('fecha-nueva');
     const horaInput = document.getElementById('hora-nueva');
     const horaPrevista = document.getElementById('hora-prevista');
 
     // Rellenar los inputs con las variables citaAdmin.fecha y citaAdmin.hora
     if (citaAdmin.fecha && citaAdmin.hora) {
         fechaInput.value = citaAdmin.fecha;  // Asignar la fecha al campo de fecha
         horaInput.value = citaAdmin.hora;
         horaPrevista.value = citaAdmin.finalizacionPrevista;    // Asignar la hora al campo de hora
     }
 
     CerrarHoraCrear();
     cerrarCrearCita();
}








function cerrarModalAnular(){
    document.querySelector('#modal-observaciones-anular').style.display = 'none';
}
function eliminarCita(button){
    const citaId = button.getAttribute('data-id');
    const usuarioId= button.getAttribute('data-usuario-id');
    const email = button.getAttribute('data-email');
    console.log('el email', email)
    citaAdmin.usuarioId = usuarioId
    citaAdmin.email=email;
    console.log(citaAdmin.usuarioId);
    console.log(citaAdmin.email);
    citaAdmin.id=citaId;
    console.log("ID de la cita a posponer: " + citaAdmin.id);
    const modalObservaciones = document.querySelector('#modal-observaciones-anular');
    modalObservaciones.style.display = 'block';
    modalObservaciones.classList.add('show');
    
    setTimeout(() => {
        modalObservaciones.style.animation = 'slideIn 0.5s forwards';
    }, 10);
}
// Validar que el botón "Enviar" se habilite solo si hay más de 15 caracteres
document.querySelector('#observacion-anular').addEventListener('input', function() {
    const textarea = document.querySelector('#observacion-anular');
    const enviarButton = document.querySelector('#enviar-observacion-anular');
    
    if (textarea.value.length > 15) {
        enviarButton.disabled = false; // Habilitar el botón
    } else {
        enviarButton.disabled = true; // Deshabilitar el botón
    }
});

// Función que se llama cuando se hace clic en "Enviar"
document.querySelector('#enviar-observacion-anular').addEventListener('click', function() {
    const observacion = document.querySelector('#observacion-anular').value;

    // Llamar a la función que anula la cita
    AnularTurno(observacion);
});


function AnularTurno(observacion) {
    const usuarioId = citaAdmin.usuarioId;
    const citaid = citaAdmin.id;   // Obtener ID de la cita
    const email = citaAdmin.email;  // Obtener el email del cliente
    console.log("Longitud de la observación: ", observacion.length);
    console.log('email: ',email);
    console.log('cita: ',citaid);
    console.log('usuario: ',usuarioId);
    // Mostrar el spinner de carga mientras se procesan los datos
    const spinner = document.getElementById("loading-spinner");
    spinner.classList.add("show");

    // Verificar que la observación tenga al menos 15 caracteres y los datos estén presentes
    if (observacion.length >= 15 && citaid && email) {
        fetch('/api/anularCita', {  // Aquí usamos el endpoint de anulación
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                citaid: citaid,
                email: email,
                usuarioId: usuarioId,
                observacion: observacion
            }),
        })
        .then(response => response.text())
        .then(data => {
            console.log('Respuesta cruda del servidor:', data);
            try {
                const jsonResponse = JSON.parse(data);
                if (jsonResponse.resultado) {  // Verificamos si la respuesta es exitosa
                    spinner.classList.remove("show");

                    Swal.fire({
                        icon: 'success',
                        title: 'Cita Anulada',
                        text: 'La cita fue anulada correctamente.',
                        button: 'OK'
                    }).then(() => {
                        setTimeout(() => {
                            window.location.reload();  // Recargar la página después de la anulación
                        }, 1000);
                    });
                } else {
                    alert('Hubo un problema al anular la cita: ' + (jsonResponse.error || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error al parsear JSON:', error);
                alert('Hubo un error al procesar la respuesta del servidor');
            }
        })
        .catch(error => {
            console.error('Error al enviar los datos:', error);
            alert('Hubo un error al enviar los datos al servidor');
        });
    } else {
        alert('Por favor, ingresa una observación válida de al menos 15 caracteres.');
    }
}






var calendarVer; // Variable global para el calendario del modal

function inicializarCalendarioVer() {
    var calendarEl = document.getElementById('calendar-ver');
    var fechaInput = document.getElementById('fecha');
    var modalSeleccionarTurno = document.getElementById('modal-seleccionar-turno'); // Modal secundario
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
    calendar = new FullCalendar.calendarVer(calendarEl, {
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
                
                ConsultarHorariosAdmin(cita.fecha);

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

    calendarVer.render();
}


// function buscarPorFecha() {
//     const fechaInput = document.querySelector('#fecha');
//     fechaInput.addEventListener('input', function(e) {
//         const fechaSeleccionada = e.target.value;

//         window.location = `?fecha=${fechaSeleccionada}`;
//     });
// }


function cerrarhorarioscrear(){
    // Función para cerrar el modal secundario con animación
    const modalSecundario = document.getElementById('modal-seleccionar-turno');
        modalSecundario.style.animation = 'slideOut 0.5s forwards'; // Animación de salida
        setTimeout(() => {
            modalSecundario.style.display = 'none'; // Oculta el modal después de la animación
            modalSecundario.classList.remove('mostrar'); // Limpia las clases
            
            modal.style.pointerEvents = 'auto';
            modalSecundario.style.pointerEvents = 'none'; // Desactivar interacciones cuando el modal esté cerrado
        }, 500);
}

// Función para cerrar el modal de posponer
function cerrarCrearCita() {
    const modal = document.getElementById("modal-crearcita"); // Asegúrate de que este ID coincida con el de tu modal
    modal.style.display = "none"; // Cierra el modal ocultándolo  
    document.getElementById('blur-background').style.display = 'none'; // Oculta el fondo de desenfoque si lo tienes
    
}
function VerCrearCita(){
    // Mostrar el modal
    const modalFecha = document.getElementById('modal-crearcita');
    modalFecha.classList.add('show');
    modalFecha.style.display = 'block';
    inicializarFechas();

    // Verifica si el calendario está inicializado
    setTimeout(function () {
        if (calendarDisponible) {
            console.log('hola'); // Asegurarse de que el calendario está inicializado antes de actualizar el tamaño
            calendarDisponible.updateSize();  
        } else {
            console.error("calendarAdmin no está inicializado.");
        }
    }, 300); 
}
var calendarDisponible; // Variable global para el segundo calendario

function inicializarFechas() {
    var calendarEl = document.getElementById('calendar-crear');
    var fechaInput = document.getElementById('fechacrear');
    var modalSeleccionarTurno = document.getElementById('modal-seleccionar-turno'); // Modal secundario
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
    calendarDisponible = new FullCalendar.Calendar(calendarEl, {
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
                citaAdmin.fecha = info.dateStr; // Actualizar la fecha en la variable 'cita'
                
                ConsultarHorariosAdmin(citaAdmin.fecha);

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

        }
        
        
        
    });

    calendarDisponible.render();
}
function posponerCita(button) {
    // Obtener los datos del botón de posponer
    const email = button.getAttribute('data-email');
    const usuarioId = button.getAttribute('data-usuario-id');
    const citaId = button.getAttribute('data-id');
    const hora = button.getAttribute('data-hora');
    const finalizacion = button.getAttribute('data-finalizacion');
    const duracionTotal = parseInt(button.getAttribute('data-duracion-total'), 10); // Convertir la duración total a entero

    console.log("ID de la cita a posponer: " + citaId);
    console.log('Hora de la cita:', hora); // Verifica que aquí salga la hora correcta
    console.log('Finalización prevista:', finalizacion);
    console.log('Duración Total en minutos:', duracionTotal); // Verifica la duración total

    // Guardar en citaAdmin
    citaAdmin.email = email;
    citaAdmin.usuarioId = usuarioId;
    citaAdmin.id = citaId;
    citaAdmin.HoraOriginal = hora !== 'No disponible' ? moment(hora, 'HH:mm').format('HH:mm') : null;
    citaAdmin.finalizacion = finalizacion !== 'No disponible' ? moment(finalizacion, 'HH:mm').format('HH:mm') : null;
    citaAdmin.duracionTotal = duracionTotal; // Guardar la duración total en citaAdmin como un entero
    
    console.log("Hora original guardada:", citaAdmin.HoraOriginal);  // Verifica que aquí salga correctamente
    console.log('Hora de finalización guardada:', citaAdmin.finalizacion);
    console.log('Duración Total guardada (en minutos):', citaAdmin.duracionTotal);  // Verifica la duración total

    // Mostrar el modal
    const modalFecha = document.getElementById('modal-posponer');
    modalFecha.classList.add('show');
    modalFecha.style.display = 'block';

    // Mostrar la hora y finalización en el modal
    const horaSpan = modalFecha.querySelector('#hora-posponer');
    const finalizacionSpan = modalFecha.querySelector('#finalizacion-posponer');
    
    if (horaSpan && finalizacionSpan) {
        horaSpan.textContent = citaAdmin.HoraOriginal ? citaAdmin.HoraOriginal : 'No disponible';
        finalizacionSpan.textContent = citaAdmin.finalizacion ? citaAdmin.finalizacion : 'No disponible';
    }

    // Inicializar el calendario
    inicializarCalendarioPosponer(citaId);

    // Verifica si el calendario está inicializado
    setTimeout(function () {
        if (calendar3) {
            console.log('Hola'); // Asegurarse de que el calendario está inicializado antes de actualizar el tamaño
            calendar3.updateSize();  
        } else {
            console.error("calendarAdmin no está inicializado.");
        }
    }, 300);
}



// Función para cerrar el modal de posponer
function cerrarModalFecha() {
    const modal = document.getElementById("modal-posponer"); // Asegúrate de que este ID coincida con el de tu modal
    modal.style.display = "none"; // Cierra el modal ocultándolo
    
    document.getElementById('blur-background').style.display = 'none'; // Oculta el fondo de desenfoque si lo tienes

}

function cerrarModalHora() {
    const modal = document.getElementById("modal-seleccionar-turno"); // Asegúrate de que este ID coincida con el de tu modal
    modal.style.display = "none"; // Cierra el modal ocultándolo
    
    document.getElementById('blur-background').style.display = 'none'; // Oculta el fondo de desenfoque si lo tienes

}
var calendarPosponer; // Variable global para el segundo calendario
function inicializarCalendarioPosponer() {
    var calendarEl = document.getElementById('calendar-posponer');
    var fechaInput = document.getElementById('fechaPosponer');
    var modalSeleccionarTurno = document.getElementById('modal-seleccionar-turno'); // Modal secundario
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
    calendarPosponer = new FullCalendar.Calendar(calendarEl, {
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
                citaAdmin.fecha = info.dateStr; // Actualizar la fecha en la variable 'cita'
                
                ConsultarHorariosAdmin(citaAdmin.fecha);

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

    calendarPosponer.render();
}


function ConsultarHorariosAdmin(fechaSeleccionada) {
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




function CerrarHoraCrear(){
    const modal = document.querySelector('#modal-seleccionar-turno');
    modal.style.animation = 'slideOut 0.5s forwards';
    setTimeout(() => {
        modal.style.display = 'none'; // Oculta el modal
        modal.classList.remove('show');
    }, 500);
}

function cerrarmodalhorarios(){
    // Función para cerrar el modal secundario con animación
    const modalSecundario = document.getElementById('modal-seleccionar-turno');
        modalSecundario.style.animation = 'slideOut 0.5s forwards'; // Animación de salida
        setTimeout(() => {
            modalSecundario.style.display = 'none'; // Oculta el modal después de la animación
            modalSecundario.classList.remove('mostrar'); // Limpia las clases
            
            modal.style.pointerEvents = 'auto';
            modalSecundario.style.pointerEvents = 'none'; // Desactivar interacciones cuando el modal esté cerrado
        }, 500);
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



const duracionTotal = document.getElementById('duracionTotal').value;
citaAdmin.duracionTotal = duracionTotal;
// Mostrar el valor en consola
console.log(citaAdmin.duracionTotal);


function mostrarHorariosCrear(turno) {
    const contenedorHorarios = document.querySelector('#horarios-crear');
    contenedorHorarios.innerHTML = '';  // Limpiar horarios anteriores
    contenedorHorarios.style.display = 'block';  // Mostrar los horarios

    let horarios = [];
    const botonManana = document.querySelector('#turno-manana');
    const botonTarde = document.querySelector('#turno-tarde');

    // Hora de cierre (dependiendo del turno)
    let horaCierre;
    if (turno === 'manana') {
        horarios = generarHorarios('08:00', '11:30', 30);  // Horarios de mañana
        botonManana.classList.add('seleccionado');
        botonTarde.classList.remove('seleccionado');
        horaCierre = moment('12:00', 'HH:mm');  // Cierre a las 12:00
    } else {
        horarios = generarHorarios('14:00', '18:30', 30);  // Horarios de tarde
        botonTarde.classList.add('seleccionado');
        botonManana.classList.remove('seleccionado');
        horaCierre = moment('19:00', 'HH:mm');  // Cierre a las 19:00
    }

    // Obtener la duración total de la cita
    const duracionTotal = citaAdmin ? citaAdmin.duracionTotal : 30;  // Se usa un valor por defecto si no existe citaAdmin
    console.log(duracionTotal, 'duración total');

    horarios.forEach((hora, index) => {
        const botonHora = document.createElement('button');
        botonHora.classList.add('botones', 'horario', 'botones-horarios');
        botonHora.textContent = hora;

        // Convertir la hora seleccionada y calcular su hora de fin según la duración
        const horaInicioSeleccionada = moment(hora, 'HH:mm');
        const horaFinSeleccionada = moment(hora, 'HH:mm').add(duracionTotal, 'minutes');  // Fin de la cita

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

            // Validar solapamientos
            return (
                // La cita seleccionada comienza dentro de una cita ocupada
                horaInicioSeleccionada.isBetween(horaInicioOcupada, horaFinOcupada, null, '[)') ||
                // La cita seleccionada termina dentro de una cita ocupada
                horaFinSeleccionada.isBetween(horaInicioOcupada, horaFinOcupada, null, '(]') ||
                // La cita seleccionada cubre completamente una cita ocupada
                (horaInicioSeleccionada.isBefore(horaInicioOcupada) && horaFinSeleccionada.isAfter(horaFinOcupada))
            );
        });

        // Si el horario está ocupado, deshabilitar la casilla y cambiar su color
        if (!estaDisponible) {
            botonHora.setAttribute('disabled', 'true');
            botonHora.style.backgroundColor = '#ccc';  // Estilo para un horario ocupado
        } else {
            botonHora.addEventListener('click', () => seleccionarHoraCrear(hora));
            botonHora.style.backgroundColor = turno === 'manana' ? '#28a745' : '#ff9800';  // Verde para mañana, naranja para tarde
        }

        // Añadir animación escalonada para los botones
        setTimeout(() => {
            botonHora.classList.add('visible');
        }, index * 100);

        contenedorHorarios.appendChild(botonHora);
    });
}








function seleccionarHoraCrear(hora) {
    const botonesHorario = document.querySelectorAll('.horario');
    const botonConfirmar = document.querySelector('#confirmar-modal-crear'); // El botón confirmar

    // Buscar el botón seleccionado
    botonesHorario.forEach(boton => boton.classList.remove('seleccionado')); // Deselecciona todos
    const botonSeleccionado = Array.from(botonesHorario).find(boton => boton.textContent === hora);

    if (botonSeleccionado) {
        botonSeleccionado.classList.add('seleccionado'); // Marca como seleccionado
        citaAdmin.hora = hora; // Actualiza la hora en citaAdmin
        console.log(citaAdmin.hora);
        botonConfirmar.disabled = false; // Habilitar el botón
    } else {
        citaAdmin.hora = null; // No hay hora seleccionada
        botonConfirmar.disabled = true; // Deshabilitar el botón
    }

    calcularFinalizacionPrevista();
}


function calcularFinalizacionPrevista() {
    console.log("Calculando la finalización prevista...: ", citaAdmin.duracionTotal);
    if (!citaAdmin.hora || !citaAdmin.duracionTotal) {
        console.warn("Faltan datos para calcular la finalización prevista.");
        return;
    }

    // Construir la fecha y hora inicial
    let [hora, minutos] = citaAdmin.hora.split(":").map(Number);
    let fechaHoraInicio = new Date(citaAdmin.fecha + "T" + citaAdmin.hora + ":00");

    // Sumar la duración total en minutos
    fechaHoraInicio.setMinutes(fechaHoraInicio.getMinutes() + citaAdmin.duracionTotal);

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
    citaAdmin.finalizacionPrevista = `${horaFinalStr}:${minutosFinalStr}`;
    
    console.log("Finalización prevista redondeada:", citaAdmin.finalizacionPrevista);
}
function mostrarHorariosPosponer(turno) {
    const contenedorHorarios = document.querySelector('#horarios-posponer');
    contenedorHorarios.innerHTML = '';  // Limpiar horarios anteriores
    contenedorHorarios.style.display = 'block';  // Mostrar los horarios

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
    const duracionTotal = citaAdmin.duracionTotal;  // Usando citaAdmin como en el código anterior
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
            botonHora.addEventListener('click', () => seleccionarHoraPosponer(hora));
            botonHora.style.backgroundColor = turno === 'manana' ? '#4caf50' : '#e67e22';
        }

        setTimeout(() => {
            botonHora.classList.add('visible');
        }, index * 100);

        contenedorHorarios.appendChild(botonHora);
    });

    citaAdmin.hora = null;  // Reemplazado cita.hora por citaAdmin.hora
    toggleConfirmButton();
}



// Función para seleccionar un horario y habilitar el botón "Confirmar"
function seleccionarHoraPosponer(hora) {
    const botonesHorario = document.querySelectorAll('.horario');
    const botonConfirmar = document.querySelector('#btn-confirmar');

    // Quitar selección previa y asignar la nueva
    botonesHorario.forEach(boton => boton.classList.remove('seleccionado'));
    const botonSeleccionado = Array.from(botonesHorario).find(boton => boton.textContent === hora);

    if (botonSeleccionado) {
        botonSeleccionado.classList.add('seleccionado');
        citaAdmin.hora = hora; // Guardar la hora seleccionada

        // Verificar si la hora original y la finalización existen
        if (citaAdmin.HoraOriginal && citaAdmin.finalizacion) {
            // Convertir las horas en objetos moment
            const horaInicioOriginal = moment(citaAdmin.HoraOriginal, 'HH:mm'); // Corrección aquí
            const horaFinOriginal = moment(citaAdmin.finalizacion, 'HH:mm');

            // Calcular la duración original de la cita
            const duracionCitaOriginal = moment.duration(horaFinOriginal.diff(horaInicioOriginal));

            // Calcular la nueva hora de finalización con la nueva hora de inicio
            const horaInicioNueva = moment(hora, 'HH:mm'); // Convertir la hora seleccionada en un objeto moment
            const horaFinNueva = horaInicioNueva.clone().add(duracionCitaOriginal); // Clonar y sumar la duración

            // Guardar la nueva hora de finalización
            citaAdmin.horaFinal = horaFinNueva.format('HH:mm');
        } else {
            citaAdmin.horaFinal = null; // Si faltan datos, borrar la hora final
        }

        console.log('Nueva hora de inicio:', citaAdmin.hora);
        console.log('Nueva hora de finalización:', citaAdmin.horaFinal);
    } else {
        citaAdmin.hora = null;
        citaAdmin.horaFinal = null; // Borrar la hora de finalización si no se selecciona ninguna
    }

    // Habilitar/deshabilitar el botón "Confirmar" según la selección
    botonConfirmar.disabled = !citaAdmin.hora;

    console.log('Hora seleccionada:', citaAdmin.hora); // Depuración
    console.log('Hora de finalización:', citaAdmin.horaFinal); // Depuración
}





// Validar la longitud de la observación
function validarObservacion() {
    const textarea = document.querySelector('#observacion-textarea');
    const botonEnviar = document.querySelector('#enviar-observacion');
    const textoObservacion = textarea.value.trim();

    // Habilitar el botón si el texto tiene al menos 15 caracteres
    botonEnviar.disabled = textoObservacion.length < 15;
}

// Abrir modal de observaciones
function abrirModalObservaciones() {
    if (!citaAdmin.hora) {
        console.error('No se puede abrir el modal: No hay hora seleccionada.');
        return;
    }

    const modalObservaciones = document.querySelector('#modal-observaciones');
    modalObservaciones.style.display = 'block';
    modalObservaciones.classList.add('show');

    // Reiniciar estado del textarea y botón "Enviar"
    const textarea = document.querySelector('#observacion-textarea');
    const botonEnviar = document.querySelector('#enviar-observacion');
    textarea.value = '';
    botonEnviar.disabled = true;
}

// Cerrar modal de observaciones
function cerrarModalObservaciones() {
    const modalObservaciones = document.querySelector('#modal-observaciones');
    modalObservaciones.style.display = 'none';
    modalObservaciones.classList.remove('show');
}
// Enviar datos de la nueva fecha
function enviarFecha() {
    const observacion = document.querySelector('#observacion-textarea').value.trim();
    const fecha = citaAdmin.fecha;
    const hora = citaAdmin.hora;
    const horaFinal = citaAdmin.horaFinal; // <-- Se agrega la nueva hora de finalización
    const citaid = citaAdmin.id;
    const email = citaAdmin.email;
    const usuarioId = citaAdmin.usuarioId;
    const spinner = document.getElementById('loading-spinner');

    if (observacion.length >= 15 && fecha && hora && horaFinal && citaid) { // <-- Se verifica también horaFinal
        spinner.classList.add('show');

        fetch('/api/posponerFechaAdmin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                observacion: observacion,
                fecha: fecha,
                hora: hora,
                horaFinal: horaFinal, // <-- Se envía al backend
                citaid: citaid,
                usuarioId: usuarioId,
                email: email,
            }),
        })
            .then(response => response.text())
            .then(data => {
                console.log('Respuesta cruda del servidor:', data);

                try {
                    const jsonResponse = JSON.parse(data);
                    console.log('Respuesta JSON:', jsonResponse);

                    if (jsonResponse.resultado) {
                        spinner.classList.remove('show');

                        Swal.fire({
                            icon: 'success',
                            title: 'Turno Pospuesto',
                            text: 'El turno fue pospuesto correctamente.',
                            button: 'OK',
                        }).then(() => {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        });
                    } else {
                        alert('Hubo un problema al posponer el turno: ' + (jsonResponse.error || 'Error desconocido'));
                    }
                } catch (error) {
                    console.error('Error al parsear JSON:', error);
                    alert('Hubo un error al procesar la respuesta del servidor.');
                }
            })
            .catch(error => {
                console.error('Error al enviar los datos:', error);
                alert('Hubo un error al enviar los datos al servidor.');
            });
    } else {
        alert('Por favor, completa todos los campos correctamente.');
    }
}




// function inicializarCalendarioEdicion() {
//     var calendarEl = document.getElementById('calendar2');
//     var fechaInput = document.getElementById('fecha'); // Puedes cambiar esto si necesitas otro input
//     var modalSeleccionarTurno = document.getElementById('modal-seleccionar-turno-editar'); // Modal secundario
//     const modalPrincipal = document.querySelector('#modal');

//     // Obtener la fecha actual
//     var today = new Date();
//     today.setHours(0, 0, 0, 0); // Resetear las horas de la fecha actual

//     // Fecha de ayer
//     var yesterday = new Date(today);
//     yesterday.setDate(today.getDate()-1); // Restar un día para obtener ayer

//     // Inicializar el segundo calendario
//     calendar2 = new FullCalendar.Calendar(calendarEl, {
//         locale: 'es',
//         initialView: 'dayGridMonth',
//         headerToolbar: {
//             left: 'prev',
//             center: 'title',
//             right: 'next'
//         },
//         events: [],
//         dateClick: function(info) {
//             var selectedDate = new Date(info.dateStr);
//             selectedDate.setHours(0, 0, 0, 0); // Asegurarse de comparar solo la fecha, no la hora
//             var thirtyDaysFromNow = new Date(today);
//             thirtyDaysFromNow.setDate(today.getDate() + 30); // Fecha 30 días adelante

//             // Verificar si la fecha seleccionada es anterior a hoy o posterior a los 30 días
//             if (selectedDate < yesterday || selectedDate > thirtyDaysFromNow) {
//                 return; // No permitir selección si la fecha es inválida
//             }

//             // Actualizar el input con la fecha seleccionada
//             fechaInput.value = info.dateStr;
        
//             // Guardar la fecha seleccionada en la variable cita.fecha
//             cita.fecha = info.dateStr;
//             ConsultarHorarios(cita.fecha);
//             // Mostrar el modal de selección de turno
//             modalSeleccionarTurno.style.display = 'block';
//             modalSeleccionarTurno.classList.add('mostrar');
        
//             setTimeout(() => {
//                 modalSeleccionarTurno.style.animation = 'slideIn 0.5s forwards';
//             }, 10);
//             modalSeleccionarTurno.style.pointerEvents = 'auto';
//         },
//         dayCellDidMount: function(info) {
//             var currentDay = new Date(info.date);
//             currentDay.setHours(0, 0, 0, 0); // Resetear las horas para que solo compare la fecha

//             // Comparar con la fecha de ayer y añadir una clase a los días pasados
//             if (currentDay <= yesterday) {
//                 info.el.classList.add('disabled-day'); // Añadir clase para deshabilitar el día
//             }

//             // Comparar si el día está fuera del rango de +30 días
//             var thirtyDaysFromNow = new Date(today);
//             thirtyDaysFromNow.setDate(today.getDate() + 30);
//             if (currentDay > thirtyDaysFromNow) {
//                 info.el.classList.add('disabled-day'); // Añadir clase para días fuera del rango
//             }

//             // Aplicar borde al día actual sin fondo
//             if (currentDay.getTime() === today.getTime()) {
//                 info.el.classList.add('current-day'); // Añadir una clase especial al día actual
//             }

//             // Deshabilitar el domingo visualmente
//             if (currentDay.getDay() === 0) { // 0 representa el domingo
//                 info.el.classList.add('disabled-day'); // Añadir clase para deshabilitar visualmente el domingo
//             }
//         },
//         datesSet: function(dateInfo) {
//             const displayedMonth = dateInfo.start.getMonth();
//             const displayedYear = dateInfo.start.getFullYear();

//             const lastMonth = new Date(today);
//             lastMonth.setMonth(today.getMonth() - 1);

//             const prevButton = document.querySelector('.fc-prev-button');
//             prevButton.disabled = (displayedMonth === lastMonth.getMonth() && displayedYear === lastMonth.getFullYear());

//             const nextButton = document.querySelector('.fc-next-button');
//             nextButton.disabled = (displayedMonth === today.getMonth() && displayedYear === today.getFullYear());
//         }
//     });

//     calendar2.render();
// }



