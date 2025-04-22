<?php
include 'conexion.php';
session_start();

$productos_vendidos = [];

$ventas = mysqli_query($conexion, "SELECT detalles_orden FROM ventas");
while ($venta = mysqli_fetch_assoc($ventas)) {
    $detalles = json_decode($venta['detalles_orden'], true);
    foreach ($detalles as $detalle) {
        $nombre = $detalle['producto'];
        $cantidad = $detalle['cantidad'];

        if (isset($productos_vendidos[$nombre])) {
            $productos_vendidos[$nombre] += $cantidad;
        } else {
            $productos_vendidos[$nombre] = $cantidad;
        }
    }
}

// Ordenar los productos por cantidad vendida (de mayor a menor)
arsort($productos_vendidos);
$productos_top = array_slice($productos_vendidos, 0, 3);

$ventasMes = mysqli_query($conexion, "
    SELECT DATE(fecha) AS dia, SUM(total) AS total
    FROM ventas
    WHERE MONTH(fecha) = MONTH(CURRENT_DATE())
    AND YEAR(fecha) = YEAR(CURRENT_DATE())
    GROUP BY DATE(fecha)
    ORDER BY dia
");

$fechas = [];
$totales = [];

while ($fila = mysqli_fetch_assoc($ventasMes)) {
    $fechas[] = $fila['dia'];
    $totales[] = $fila['total'];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/estilos_dashboard.css">
    <title>Dashboard - Taquería</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <li class="user"><img src="recursos/casa.png" alt=""><a href="dashboard.php" >Dashboard</a></li>
                    <li><img src="recursos/cena.png" alt=""><a href="ordenes.php" >Órdenes</a></li>
                    <li><img src="recursos/moneda.png" alt=""><a href="ventas.php" >Ventas</a></li>
                    <li><img src="recursos/bebida.png" alt=""><a href="productos.php" >Productos</a></li>
                    <li><img src="recursos/contacto.png" alt=""><a href="usuarios.php" >Usuarios</a></li>
                    <button onclick="cerrarSesion()" class="logout-btn">Cerrar Sesión</button>
                </ul>
            </nav>
        </div>


<div class="contenedor">
    <h2>Mesas</h2>
    <div class="mesas-container">
        <?php
        $mesas_query = mysqli_query($conexion, "SELECT * FROM mesas");
        while ($mesa = mysqli_fetch_assoc($mesas_query)) {
            $mesa_id = $mesa['id'];
            $orden_check = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM ordenes WHERE mesa_id = $mesa_id");
            $tiene_orden = mysqli_fetch_assoc($orden_check)['total'] > 0;
            $estado = $tiene_orden ? "Ocupada" : "Libre";
            $clase = $tiene_orden ? "mesa-ocupada" : "mesa-libre";
            ?>
            <div class="mesa-box <?php echo $clase; ?>">
                <h4>Mesa <?php echo $mesa['id']; ?></h4>
                <center><p class="estado"><?php echo $estado; ?></p></center>
            </div>
        <?php } ?>
    </div>

    <h2>Ventas</h2>
    <div class="ventas">
        <div class="tarjetas">
            <div class="card_dia">
                <h3>Total del Día</h3>
                <?php
                $ventas_dia = mysqli_query($conexion, "SELECT SUM(total) AS total_dia FROM ventas WHERE DATE(fecha) = CURDATE()");
                $dia = mysqli_fetch_assoc($ventas_dia)['total_dia'] ?? 0;
                echo '<strong>$' . number_format($dia, 2) . '</strong>';
                ?>
            </div>

            <div class="card_mes">
                <h3>Ventas del Mes</h3>
                <?php
                $ventas_mes = mysqli_query($conexion, "SELECT SUM(total) AS total_mes FROM ventas WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())");
                $mes = mysqli_fetch_assoc($ventas_mes)['total_mes'] ?? 0;
                echo '<strong>$' . number_format($mes, 2) . '</strong>';
                ?>
            </div>
        </div>

        <div class="grafica_mes">
            <center><h3>Ventas Diarias</h3></center>
            <canvas id="graficaVentasMes"></canvas>
        </div>

        <div class="card_productos">
        <h3>Productos Más Vendidos</h3>
        <ul>
            <?php foreach ($productos_top as $nombre => $cantidad): ?>
                <li><?php echo $nombre . " (" . $cantidad . ")"; ?></li>
            <?php endforeach; ?>
        </ul>

        </div>
    </div>

        <script>
            const ctxVentasMes = document.getElementById('graficaVentasMes').getContext('2d');

            new Chart(ctxVentasMes, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($fechas); ?>,
                    datasets: [{
                        label: 'Total Vendido ($)',
                        data: <?php echo json_encode($totales); ?>,
                        borderColor: '#A90125',
                        backgroundColor: 'rgba(243, 170, 170, 0.3)',
                        pointBackgroundColor: '#A90125',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: {
                            display: true,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    }
                }
            });

            function cerrarSesion() {
                    window.location.href = "index.html";
            }
        </script>
    </div>
</div>
</body>
</html>
