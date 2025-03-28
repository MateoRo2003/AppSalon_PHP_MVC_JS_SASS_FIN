
    <?php include_once __DIR__ . '/../templates/barra.php'; ?>

    <div class="main-content">
        <h1>Clientes</h1>
        
        <!-- Filtros para ver administradores o clientes -->
        <div class="filtros">
            <a href="/clientes?tipo=admin" class="filtro <?php echo ($tipo === 'admin') ? 'activo' : ''; ?>">Administradores</a>
            <a href="/clientes?tipo=cliente" class="filtro <?php echo ($tipo === 'cliente') ? 'activo' : ''; ?>">Clientes Normales</a>
        </div>

        <table class="clientes-table">
            <thead>
                <tr>
                    <th>ID#</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?php echo $cliente->id; ?></td>
                        <td><?php echo $cliente->nombre; ?></td>
                        <td><?php echo $cliente->apellido; ?></td>
                        <td><?php echo $cliente->telefono ? $cliente->telefono : 'No Obligatorio'; ?></td>
                        <td><?php echo $cliente->email; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

