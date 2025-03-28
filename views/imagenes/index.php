<?php 
    include_once __DIR__ . '/../templates/barra.php';
    include_once __DIR__ . '/../templates/alertas.php';
    $usuario = $_SESSION['usuario'] ?? null; // Asegúrate de que $usuario esté definido
?>
<div class="main-content">
    <h1>Editar estilos destacados</h1>

    <!-- Botón para abrir el modal de agregar imagen -->
    <div class="contenedor-boton">
        <button id="abrirModal" class="boton-agregar">
            <span class="icono-mas">+</span>
            <span>Agregar Imagen</span>
        </button>
    </div>

    <!-- Contenedor para mostrar las imágenes -->
    <div class="contenedor-imagenes">
        <?php foreach ($imagenes as $imagen): ?>
            <div class="imagen-item">
                <!-- Mostrar imagen -->
                <div class="imagen-placeholder">
                    <?php if (!empty($imagen->imagen)): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($imagen->imagen); ?>" alt="Imagen de Tendencia" class="imagen-admin">
                    <?php else: ?>
                        <p>No hay imagen disponible</p>
                    <?php endif; ?>
                </div>

                <!-- Mostrar nombre y descripción -->
                <div class="imagen-info">
                    <h3><?php echo $imagen->nombre; ?></h3>
                    <p><?php echo $imagen->descripcion; ?></p>
                </div>

                <!-- Botones de editar o eliminar -->
                <div class="imagen-acciones">
                    <button class="editar-imagen" onclick="document.getElementById('modalEditar<?php echo $imagen->id; ?>').style.display='block'">
                        Editar
                    </button>
                    <form action="/admin/imagenes/eliminar" method="POST" onsubmit="return confirmarEliminar(event, <?php echo $imagen->id; ?>)">
                        <input type="hidden" name="id" value="<?php echo $imagen->id; ?>">
                    <button type="submit" class="eliminar-imagen">Eliminar</button>
                </form>
                </div>
            </div>

            <!-- Modal de Edición (Uno por cada imagen) -->
            <div id="modalEditar<?php echo $imagen->id; ?>" class="modal-agregar">
                <div class="modal-contenido">
                    <span class="cerrar" onclick="document.getElementById('modalEditar<?php echo $imagen->id; ?>').style.display='none'">&times;</span>
                    <h2>Editar Imagen</h2>
                    <form method="POST" action="/admin/imagenes/editar?id=<?php echo $imagen->id; ?>" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $imagen->id; ?>" />
                        <div class="campo-agregar">
                            <label for="nombre<?php echo $imagen->id; ?>">Nombre:</label>
                            <input type="text" id="nombre<?php echo $imagen->id; ?>" name="nombre" value="<?php echo $imagen->nombre; ?>" required />
                        </div>
                        <div class="campo-agregar">
                            <label for="descripcion<?php echo $imagen->id; ?>">Descripción:</label>
                            <textarea id="descripcion<?php echo $imagen->id; ?>" name="descripcion" required><?php echo $imagen->descripcion; ?></textarea>
                        </div>
                        <div class="acciones-agregar">
                            <button class="btn-guardar-agregar" type="submit">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>


<!-- Script para cerrar el modal al hacer clic fuera -->
<script>
window.onclick = function(event) {
    let modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
};
</script>




<!-- Modal -->
<div id="modalAgregar" class="modal-agregar">
 
  <div class="modal-contenido-agregar">
     <span class="cerrar-agregar" id="cerrarModal">&times;</span>
    <h2>Agregar Nueva Imagen</h2>
    <div class="contenido-principal">
      <!-- Contenedor izquierdo: formulario -->
      <form id="formAgregarImagen" class="formulario" action="/admin/imagenes/crear" method="POST" enctype="multipart/form-data">
        <div class="campo-agregar">
          <label for="nombre">Nombre:</label>
          <input type="text" id="nombre" name="nombre" required>
        </div>
        <div class="campo-agregar">
          <label for="descripcion">Descripción:</label>
          <textarea id="descripcion" name="descripcion" required></textarea>
        </div>
        <div class="campo-agregar">
          <label for="archivo">Seleccionar Imagen:</label>
          <input type="file" id="archivo" name="archivo" accept="image/*" required>
        </div>
        <div class="acciones-agregar">
          <button type="submit" class="btn-guardar-agregar">Guardar</button>
        </div>
      </form>
      <!-- Contenedor derecho: vista previa -->
      <div class="vista-previa">
        <img id="preview" src="" alt="Vista previa de la imagen" style="display:none;">
      </div>
    </div>
  </div>
</div>



</div>



<!-- Script para abrir y cerrar el modal -->
<script>
document.getElementById("abrirModal").addEventListener("click", function() {
    let modal = document.getElementById("modalAgregar");
    modal.style.display = "flex";  // Mostrar el modal
    
    // Deshabilitar la interacción fuera del modal
    document.body.style.pointerEvents = "none";  // Deshabilitar interacciones en todo el body
    document.getElementById("modalAgregar").style.pointerEvents = "auto";  // Permitir interacciones dentro del modal
    
    document.body.style.overflow = "hidden";  // Deshabilitar el scroll mientras el modal está abierto
});

document.getElementById("cerrarModal").addEventListener("click", function() {
    cerrarModalYLimpiar();
});

window.addEventListener("click", function(event) {
    if (event.target === document.getElementById("modalAgregar")) {
        cerrarModalYLimpiar();
    }
});

function cerrarModalYLimpiar() {
    let modal = document.getElementById("modalAgregar");
    modal.style.display = "none";  // Ocultar el modal

    // Restablecer las interacciones
    document.body.style.pointerEvents = "auto";  // Restaurar interacciones del body
    document.body.style.overflow = "auto";  // Restaurar el scroll

    // Limpiar los campos del formulario dentro del modal
    let formulario = modal.querySelector("form");
    if (formulario) {
        formulario.reset();
    }

    // Restablecer la previsualización de la imagen
    let preview = document.getElementById("preview");
    preview.src = "";
    preview.style.display = "none";
}

// Actualizar la previsualización cuando se selecciona un archivo
document.getElementById("archivo").addEventListener("change", function(event) {
    let preview = document.getElementById("preview");
    let file = event.target.files[0];

    if (file) {
        let reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);
    } else {
        preview.src = "";
        preview.style.display = "none";
    }
});

</script>


<!-- modal-agregar -->

<script>
document.getElementById("archivo").addEventListener("change", function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById("preview");
            preview.src = e.target.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }
});

</script>