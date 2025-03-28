<?php 
    include_once __DIR__ . '/../templates/barra.php';
?>

<div class="main-content">
    <h1 >Rendimientos</h1>

    <div class="tabla-container">
        <table class="tabla-rendimientos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Total Ganado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cierres as $cierre): ?>
                    <tr>
                        <td><?= htmlspecialchars($cierre->id) ?></td>
                        <td><?= htmlspecialchars($cierre->fecha) ?></td>
                        <td>$<?= number_format($cierre->total) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
