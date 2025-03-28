<?php
// Verificar si $alertas está definida y es un array
if (isset($alertas) && is_array($alertas)) :
    foreach($alertas as $key => $mensajes):
        foreach($mensajes as $mensaje):
?>
    <div class="alerta <?php echo $key; ?>">
        <?php echo $mensaje; ?>
    </div>
<?php
        endforeach;
    endforeach;
else :
    // Si no hay alertas, podrías poner un mensaje de depuración
    echo "<!-- No hay alertas para mostrar -->";
endif;
?>

<script>
    // Obtenemos todas las alertas
    const alertas = document.querySelectorAll('.alerta');

    // Recorremos cada alerta y configuramos un temporizador para desaparecerlas
    alertas.forEach(alerta => {
        setTimeout(() => {
            alerta.style.transition = 'opacity 0.5s ease';
            alerta.style.opacity = '0';
            setTimeout(() => alerta.remove(), 500); // Después de que se desvanezca, se elimina del DOM
        }, 4000); // 4000 ms = 4 segundos
    });
</script>
