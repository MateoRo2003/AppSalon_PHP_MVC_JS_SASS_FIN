<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<!-- FullCalendar Scripts -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<?php 
    include_once __DIR__ . '/../templates/barra.php';
?><div class="main-content">
<h2>Panel Administrativo</h2>
<div class="busqueda">
    <form class="formulario">
        <div class="campo">
            <input 
                type="date"
                id="fecha"
                name="fecha"
                value="<?php echo htmlspecialchars($fecha); ?>" 
            />
        </div>
    </form> 
</div>
<div id="totalGanadoContainer">
    <p>Total Del Día: <span id="totalGanadoTexto">$0</span></p>
    <div class="icono-container">
        <i id="cerrarCajaIcono" class="fa-solid fa-cash-register" onclick="CerrarCaja()"></i>
        <span class="tooltip-text">Cerrar caja</span>
    </div>
</div>

<!-- Tarjetas de estadísticas -->
<div class="tarjetas">
    <div class="tarjeta">
        <h3>TOTAL DE CLIENTES</h3>
        <p class="numero">0</p> <!-- Este valor será actualizado con JS -->
        <i class="fas fa-user"></i>
    </div>
    <div class="tarjeta">
        <h3>TOTAL DE SERVICIOS</h3>
        <p class="numero">0</p> <!-- Este valor será actualizado con JS -->
        <i class="fas fa-cut"></i>
    </div>
    <div class="tarjeta">
        <h3>SUSCRIPCIONES</h3>
        <p class="numero">0</p> <!-- Este valor será actualizado con JS -->
        <i class="fas fa-envelope"></i>
    </div>
    <div class="tarjeta">
        <h3>CITAS</h3>
        <p class="numero">0</p> <!-- Este valor será actualizado con JS -->
        <i class="fas fa-calendar-alt"></i>
    </div>
</div>

<div class="filtros-citas">
    <button onclick="window.location.href='?filtro=proximas'">Reservas Próximas</button>
    <button onclick="window.location.href='?filtro=todas'">Todas las Citas</button>
    <button onclick="window.location.href='?filtro=canceladas'">Citas Canceladas</button>
    <button onclick="window.location.href='?filtro=realizadas'">Citas Realizadas</button>
</div>

<?php
$filtro = $_GET['filtro'] ?? 'proximas'; 
$fechaSeleccionada = $_GET['fecha'] ?? date('Y-m-d'); 
$fechaActual = date('Y-m-d');
switch ($filtro) {
    case 'proximas':
        $citasFiltradas = array_filter($citas, function($cita) use ($fechaActual) {
            if (!empty($cita->fecha)) {  
                $fechaCita = strtotime($cita->fecha);
                $fechaLimite = strtotime($fechaActual . ' +7 days');
                $fechaHoy = strtotime($fechaActual);
                return $cita->estado == 0 && $fechaCita !== false && $fechaCita >= $fechaHoy && $fechaCita <= $fechaLimite;
            }
            return false;
        });
    
        usort($citasFiltradas, function($a, $b) {
            return strtotime($a->fecha) - strtotime($b->fecha);
        });
        break;
    case 'todas':
        $citasFiltradas = array_filter($citas, fn($cita) => $cita->estado == 0);
        usort($citasFiltradas, function($a, $b) {
            $fechaA = !empty($a->fecha) ? strtotime($a->fecha) : 0;
            $fechaB = !empty($b->fecha) ? strtotime($b->fecha) : 0;
            return $fechaB - $fechaA;
        });
        break;
    case 'canceladas':
        $citasFiltradas = array_filter($citas, fn($cita) => $cita->estado == 2);
        break;
    case 'realizadas':
        $citasFiltradas = array_filter($citas, fn($cita) => $cita->estado == 3 || $cita->estado == 4);
        break;
    default:
        $citasFiltradas = $citas;
}

if (count($citasFiltradas) === 0) {
    echo "<h2>No Hay Citas en esta categoría</h2>";
}
?>

<div id="citas-admin">
    <ul class="citas">   
        <?php 
        $idCita = null; // Asegurar que no arranca con un valor incorrecto
        foreach ($citasFiltradas as $key => $cita) {
            if ($idCita !== $cita->id) {
                $total = 0;
                $claseEstado = $cita->estado == 2 ? 'anulada' : ($cita->estado == 3 || $cita->estado == 4 ? 'realizado' : '');
        ?>
            <li class="<?php echo $claseEstado; ?>">
                <?php if ($cita->estado == 2): ?>
                    <p class="anulado-texto">Anulado</p>
                <?php elseif ($cita->estado == 3 || $cita->estado == 4): ?>
                    <p class="realizado-texto">Realizado</p>
                <?php endif; ?>
            
                <p>ID: <span><?php echo $cita->id; ?></span></p>
                <p>Fecha: <span><?php echo !empty($cita->fecha) ? date("d/m/Y", strtotime($cita->fecha)) : 'No disponible'; ?></span></p>
                <p>Hora: <span><?php echo !empty($cita->hora) ? date("H:i", strtotime($cita->hora)) : 'No disponible'; ?></span></p>
                <p>Cliente: <span><?php echo $cita->cliente; ?></span></p>
                <p>Email: <span><?php echo $cita->email; ?></span></p>
                <p>Teléfono: <span><?php echo $cita->telefono; ?></span></p>
                <p>Finalizacion Prevista: <span><?php echo !empty($cita->finalizacion) ? date("H:i", strtotime($cita->finalizacion)) : 'No disponible'; ?></span></p>
                <p>Duracion Total:  <span><?php echo $cita->duracionTotal; ?></span></p>
                <!-- Mostrar observaciones solo si tiene contenido -->  
                <?php if (!empty($cita->observaciones)): ?>
                    <p>Observaciones: <span><?php echo $cita->observaciones; ?></span></p>
                <?php endif; ?>

                <h3>Servicios</h3>
        <?php 
                $idCita = $cita->id;
            } 
            $total += $cita->precio;
        ?>
                <p class="servicio"><?php echo $cita->servicio . " " . $cita->precio; ?></p>
                
                
        

                <?php 
                // Verificar si es la última vez que aparece esta cita
                if (!isset($citasFiltradas[$key + 1]) || $citasFiltradas[$key + 1]->id !== $cita->id) { ?>
                    <p class="total">Total: <span>$ <?php echo $total; ?></span></p>
                    <div class="botones-admin">
                    <form style="display: inline;">
                        <input type="hidden" name="id" value="<?php echo $cita->id; ?>">
                        <input 
                            type="button" 
                            class="boton-posponer" 
                            value="Posponer" 
                            onclick="posponerCita(this)" 
                            data-id="<?php echo $cita->id; ?>" 
                            data-email="<?php echo $cita->email; ?>" 
                            data-usuario-id="<?php echo $cita->usuarioId; ?>" 
                            data-hora="<?php echo !empty($cita->hora) ? date('H:i', strtotime($cita->hora)) : 'No disponible'; ?>"
                            data-finalizacion="<?php echo !empty($cita->finalizacion) ? date('H:i', strtotime($cita->finalizacion)) : 'No disponible'; ?>"
                            data-duracion-total="<?php echo $cita->duracionTotal; ?>" 
                            <?php echo ($cita->estado == 2 || $cita->estado == 3 || $cita->estado == 4) ? 'disabled' : ''; ?> 
                        />
                    </form>
                        <form style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $cita->id; ?>">
                            <input 
                                type="button" 
                                class="boton-eliminar" 
                                value="Anular" 
                                onclick="eliminarCita(this)" 
                                data-id="<?php echo $cita->id; ?>" 
                                data-email="<?php echo $cita->email; ?>" 
                                data-usuario-id="<?php echo $cita->usuarioId; ?>" 
                                <?php echo ($cita->estado == 2 || $cita->estado == 3 || $cita->estado == 4) ? 'disabled' : ''; ?> 
                            />
                        </form>
                        <form style="display: inline;" action="/api/realizado" method="POST">
                            <input type="hidden" name="id" value="<?php echo $cita->id; ?>">
                            <input 
                                type="submit" 
                                class="boton-realizado" 
                                value="Realizado" 
                                <?php echo ($cita->estado == 2 || $cita->estado == 3 || $cita->estado == 4 ) ? 'disabled' : ''; ?> 
                            />
                        </form>
                    </div>
                <?php } 
            } ?>
    </ul>
</div>
</div>


<!-- Contenedor oculto para almacenar IDs de citas realizadas -->
<div id="citasRealizadasData" style="display: none;">
    <?php foreach ($citasFiltradas as $cita): ?>
        <?php if ($cita->estado == 3): ?>
            <span data-id="<?php echo $cita->id; ?>" data-precio="<?php echo $cita->precio; ?>" data-fecha="<?php echo $cita->fecha; ?>"></span>
        <?php endif; ?>
    <?php endforeach; ?>
</div>



<div id="loading-spinner" class="loading-spinner">
    <div class="razor"></div>
</div>

<div id="modal-posponer" class="modal-seleccion" style="display: none;">
    <div class="modal-content-seleccion">
        <h2>Selecciona una fecha</h2>
        <div id="calendar-container">
            <div id="calendar-posponer"></div>
        </div>
        <button class="btn-cancelar" onclick="cerrarModalFecha()">Cancelar</button>
    </div>
</div>

<div id="modal-seleccionar-turno" class="modal-seleccionar-turno">
    <div class="modal-contenido-seleccionar">
        <h3 class="text-center">Selecciona tu Horario</h3>
        <p class="text-center">¿Qué parte del día prefieres?</p>
        <div class="campo">
            <input id="fechaPosponer" type="date" />
        </div>
        <div class="campo-hora">
            <div class="botones-turno">
                <button id="turno-manana" type="button" class="button-hora" onclick="mostrarHorariosPosponer('manana')">Mañana</button>
                <button id="turno-tarde" type="button" class="button-hora" onclick="mostrarHorariosPosponer('tarde')">Tarde</button>
            </div>
            <div id="horarios-posponer" class="horarios-container"></div>
        </div>
        <div class="modal-buttons">
            <button class="btn" onclick="cerrarModalHora()">Cerrar</button>
            <button id="btn-confirmar" class="btn-confirmar" disabled onclick="abrirModalObservaciones()">Confirmar</button>
        </div>
    </div>
</div>

<!-- Modal de observaciones -->
<div id="modal-observaciones" class="modal-observacion" style="display: none;">
    <div class="modal-content-observacion">
        <h2>Ingresa tu observación</h2>
        <textarea id="observacion-textarea" placeholder="Escribe la razón para posponer la cita..." required oninput="validarObservacion()"></textarea>
        <button id="enviar-observacion" class="btn-enviar" onclick="enviarFecha()" disabled>Enviar</button>
        <button class="btn-cancelar" onclick="cerrarModalObservaciones()">Cancelar</button>
    </div>
</div>




<!-- Modal de observaciones -->
<div id="modal-observaciones-anular" class="modal-observacion" style="display: none;">
    <div class="modal-content-observacion">
        <h2>Ingresa tu observación</h2>
        <textarea id="observacion-anular" placeholder="Escribe la razón para anular esta cita..." required></textarea>
        <button id="enviar-observacion-anular" class="btn-enviar" disabled>Enviar</button>
        <button class="btn-cancelar" onclick="cerrarModalAnular()">Cancelar</button>
    </div>
</div>





<?php 
    $script = "
        <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script src='build/js/buscador.js'></script>
        <script src='build/js/admin.js'></script>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js'></script> <!-- Agrega Moment.js -->
    ";
?>





<script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
















