<?php
include 'conexion.php';
session_start();

$ventas_por_pagina = 10; // Cambia este valor si quieres mostrar más/menos
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $ventas_por_pagina;

// Filtros
$where = "";

if (!empty($_GET['fecha'])) {
    $fecha = $_GET['fecha'];
    $where = "WHERE DATE(fecha) = '$fecha'";
} elseif (!empty($_GET['mes']) && !empty($_GET['anio'])) {
    $mes = $_GET['mes'];
    $anio = $_GET['anio'];
    $where = "WHERE MONTH(fecha) = $mes AND YEAR(fecha) = $anio";
}

// Total de ventas para paginación
$total_query = "SELECT COUNT(*) AS total FROM ventas $where";
$total_resultado = mysqli_query($conexion, $total_query);
$total_ventas = mysqli_fetch_assoc($total_resultado)['total'];
$total_paginas = ceil($total_ventas / $ventas_por_pagina);

// Consulta con paginación
$query = "SELECT id, mesa_id, DATE_FORMAT(fecha, '%d-%m-%Y %H:%i:%s') as fecha, total, pago, cambio, detalles_orden FROM ventas $where ORDER BY fecha DESC LIMIT $inicio, $ventas_por_pagina";
$resultado = mysqli_query($conexion, $query);



?>

<!DOCTYPE html>
<html lang="es">
<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="estilos/estilos_ventas.css">
    <title>Ventas</title>
    
</head>
<body>

        <div class="menu">
            <nav>
                
                <ul>
                <center>
                    <div class="avatar">
                        <img src="recursos/avatar.png" alt=""><br>
                        <label><?php echo $_SESSION['usuario']; ?></label>
                    </div>
                </center>
                    <li><img src="recursos/casa.png" alt=""><a href="dashboard.php" >Dashboard</a></li>
                    <li><img src="recursos/cena.png" alt=""><a href="ordenes.php" >Órdenes</a></li>
                    <li  class="user"><img src="recursos/moneda.png" alt=""><a href="ventas.php" >Ventas</a></li>
                    <li><img src="recursos/bebida.png" alt=""><a href="productos.php" >Productos</a></li>
                    <li><img src="recursos/contacto.png" alt=""><a href="usuarios.php" >Usuarios</a></li>
                    <button onclick="cerrarSesion()" class="logout-btn">Cerrar Sesión</button>
                </ul>
            </nav>
        </div>

    <div class="contenedor">
        <h2>Ventas</h2>

    <!-- Filtros -->
    <form method="GET">
    <label for="fecha">Fecha:</label>
    <input type="date" name="fecha" id="filtroFecha" value="<?= $_GET['fecha'] ?? '' ?>">

    <label for="mes">Mes:</label>
    <select name="mes" id="filtroMes">
        <option value="">--</option>
        <?php for ($i = 1; $i <= 12; $i++): ?>
            <option value="<?= $i ?>" <?= (isset($_GET['mes']) && $_GET['mes'] == $i) ? 'selected' : '' ?>>
                <?= $i ?>
            </option>
        <?php endfor; ?>
    </select>

    <label for="anio">Año:</label>
    <input type="number" name="anio" id="filtroAnio" min="2020" max="<?= date('Y') ?>" value="<?= $_GET['anio'] ?? date('Y') ?>">

    <button type="submit">Filtrar</button>
    <button type="button" onclick="generarPDF()">Imprimir</button>
</form>


    <!-- Tabla de ventas -->
    <table>
        <thead>
            <tr>
                <th>Mesa</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Pago</th>
                <th>Cambio</th>
                <th>Detalles</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($venta = mysqli_fetch_assoc($resultado)): ?>
                <tr>
                    <td><?= $venta['mesa_id'] ?></td>
                    <?php
                        $partes = explode(' ', $venta['fecha']);
                    ?>
                    <td><?= $partes[0] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $partes[1] ?></td>

                    <td>$<?= number_format($venta['total'], 2) ?></td>
                    <td>$<?= number_format($venta['pago'], 2) ?></td>
                    <td>$<?= number_format($venta['cambio'], 2) ?></td>
                    <td class="detalles">
                    <?php
                        $detalles = json_decode($venta['detalles_orden'], true);
                        foreach ($detalles as $item) {
                            echo "{$item['producto']} x{$item['cantidad']} \${$item['precio']} = \${$item['subtotal']}";
                            if (!empty($item['detalles_extras'])) {
                                echo " (Extras: {$item['detalles_extras']})";
                            }
                            echo "<br>";
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Paginación -->
    <div class="paginacion">
        <?php if ($pagina_actual > 1): ?>
            <a href="?pagina=<?= $pagina_actual - 1 ?>&<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])) ?>">&laquo; Anterior</a>
        <?php endif; ?>

        <?php if ($pagina_actual < $total_paginas): ?>
            <a href="?pagina=<?= $pagina_actual + 1 ?>&<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])) ?>">Siguiente &raquo;</a>
        <?php endif; ?>
    </div>
    </div>

    <script>
        function cerrarSesion() {
                window.location.href = "index.html";
        }

        async function generarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Obtener valores de los inputs
    const fecha = document.getElementById('filtroFecha')?.value;
    const mes = document.getElementById('filtroMes')?.value;
    const anio = document.getElementById('filtroAnio')?.value;

    // Crear URL con los filtros seleccionados
    const queryParams = [];
    if (fecha) queryParams.push(`fecha=${fecha}`);
    if (mes) queryParams.push(`mes=${mes}`);
    if (anio) queryParams.push(`anio=${anio}`);

    let url = 'ventas_pdf.php';
    if (queryParams.length > 0) {
        url += '?' + queryParams.join('&');
    }

    console.log("URL enviada:", url); // DEBUG opcional

    // Obtener los datos con los filtros aplicados
    const response = await fetch(url);
    const data = await response.json();

    doc.text("Reporte de Ventas", 14, 15);

    const rows = data.map(venta => [
        venta.mesa_id,
        venta.fecha,
        `$${parseFloat(venta.total).toFixed(2)}`,
        `$${parseFloat(venta.pago).toFixed(2)}`,
        `$${parseFloat(venta.cambio).toFixed(2)}`,
        venta.detalles_orden
    ]);

    doc.autoTable({
        head: [["Mesa", "Fecha", "Total", "Pago", "Cambio", "Detalles"]],
        body: rows,
        startY: 20,
        styles: { fontSize: 7 },
        headStyles: { fillColor: [220, 220, 220] }
    });

    doc.save("reporte.pdf");
}


    </script>

</body>
</html>
