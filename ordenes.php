<?php
session_start();
include 'conexion.php';

// Manejar creación de mesas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['num_mesas'])) {
    $num_mesas = intval($_POST['num_mesas']);

    // Obtener el número actual de mesas
    $result = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM mesas");
    $total_mesas = mysqli_fetch_assoc($result)['total'];

    if ($num_mesas > $total_mesas) {
        // Agregar nuevas mesas
        for ($i = $total_mesas + 1; $i <= $num_mesas; $i++) {
            mysqli_query($conexion, "INSERT INTO mesas (numero) VALUES ($i)");
        }
    } else if ($num_mesas < $total_mesas) {
        $no_se_pudo_eliminar = false;

        for ($i = $total_mesas; $i > $num_mesas; $i--) {
            // Obtener el ID de la mesa con ese número
            $res = mysqli_query($conexion, "SELECT id FROM mesas WHERE numero = $i");
            $fila = mysqli_fetch_assoc($res);
            $mesa_id = $fila['id'];

            // Verificar si tiene órdenes asociadas
            $check_ordenes = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM ordenes WHERE mesa_id = $mesa_id");
            $ordenes = mysqli_fetch_assoc($check_ordenes)['total'];

            // Verificar si tiene ventas asociadas (opcional, puedes omitirlo si no es necesario)
            $check_ventas = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM ventas WHERE mesa_id = $mesa_id");
            $ventas = mysqli_fetch_assoc($check_ventas)['total'];

            if ($ordenes == 0 && $ventas == 0) {
                mysqli_query($conexion, "DELETE FROM mesas WHERE id = $mesa_id");
            } else {
                $no_se_pudo_eliminar = true;
                break; // detenemos el bucle si una mesa no puede eliminarse
            }
        }

        if ($no_se_pudo_eliminar) {
            echo "<script>alert('No se pueden eliminar mesas que ya tienen órdenes o ventas registradas.'); window.location.href='ordenes.php';</script>";
            exit();
        }
    }

    header("Location: ordenes.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_orden'])) {
    $mesa_id = intval($_POST['mesa_id']);
    $productos = json_decode($_POST['productos'], true);
    $detalles_extras = isset($_POST['detalles_extras']) ? $_POST['detalles_extras'] : ''; // Obtener detalles extra
 
    if ($mesa_id > 0 && is_array($productos)) {
        foreach ($productos as $producto) {
            $id_producto = intval($producto['id']);
            $cantidad = intval($producto['cantidad']);
            $detalles_extras = $producto['detalles_extras']; // Asegúrate de obtener los detalles desde el JSON
 
            // Obtener el precio del producto
            $result = mysqli_query($conexion, "SELECT precio FROM productos WHERE id = $id_producto");
            $producto_data = mysqli_fetch_assoc($result);
            $precio = $producto_data['precio'];
 
            // Calcular subtotal
            $subtotal = $precio * $cantidad;
 
            // Insertar orden en la base de datos
            $stmt = $conexion->prepare("INSERT INTO ordenes (mesa_id, producto_id, cantidad, subtotal, detalles_extras) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiis", $mesa_id, $id_producto, $cantidad, $subtotal, $detalles_extras);
            $stmt->execute();
        }
 
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
 }
 

// Obtener mesas y productos (ya lo tienes)
$mesas = mysqli_query($conexion, "SELECT * FROM mesas ORDER BY numero ASC");
$productos = mysqli_query($conexion, "SELECT * FROM productos ORDER BY tipo ASC, nombre ASC");
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Órdenes</title>
    <link rel="stylesheet" href="estilos/estilos_ordenes.css">
    <script defer src="ordenes.js"></script>
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
                    <li class="user"><img src="recursos/cena.png" alt=""><a href="ordenes.php" >Órdenes</a></li>
                    <li><img src="recursos/moneda.png" alt=""><a href="ventas.php" >Ventas</a></li>
                    <li><img src="recursos/bebida.png" alt=""><a href="productos.php" >Productos</a></li>
                    <li><img src="recursos/contacto.png" alt=""><a href="usuarios.php" >Usuarios</a></li>
                    <button onclick="cerrarSesion()" class="logout-btn">Cerrar Sesión</button>
                </ul>
            </nav>
        </div>

        <div class="contenedor">
            <div class="encabezado">
                <h2>Órdenes</h2>
                    <form method="POST" action="ordenes.php">
                        <input type="number" name="num_mesas" min="1" placeholder="No. de Mesas" required>
                        <button type="submit">Actualizar Mesas</button>
                    </form>
            </div>
            <div class="mesas">
            <?php while ($mesa = mysqli_fetch_assoc($mesas)): ?>
                <div class="mesa" id="mesa-<?php echo $mesa['id']; ?>">
                    <h3>Mesa <?php echo $mesa['numero']; ?></h3>
                    
            <!-- Mostrar resumen de la orden -->
            <div class="resumen-orden" id="resumen-<?php echo $mesa['id']; ?>">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                        </tr>
                        
                    </thead>
                    <tbody>
                        <?php
                            $orden_resumen = mysqli_query($conexion, "SELECT o.*, o.detalles_extras, p.nombre, p.precio FROM ordenes o JOIN productos p ON o.producto_id = p.id WHERE o.mesa_id = {$mesa['id']}");
                            $total = 0;
                            while ($orden = mysqli_fetch_assoc($orden_resumen)) {
                                $total += $orden['subtotal'];
                                echo "<tr>";
                                echo "<td>{$orden['nombre']}</td>";
                                echo "<td>\${$orden['precio']}</td>";
                                echo "<td>{$orden['cantidad']}</td>";
                                echo "<td>\${$orden['subtotal']}</td>";
                                echo "</tr>";
                        ?>
                                <tr>
                                    <td>Detalles</td>
                                <?php
                                        echo "<td colspan='3'>{$orden['detalles_extras']}</td>";
                                    }
                                ?>
                                </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan='3' style='text-align: right;'><strong>Total:</strong></td>
                            <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

                    <?php
                        $orden_resumen = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM ordenes WHERE mesa_id = ".$mesa['id']);
                        $tiene_orden = mysqli_fetch_assoc($orden_resumen)['total'] > 0;
                        ?>
                    <?php if (!$tiene_orden): ?>
                        <button class="btn-ordenar" data-mesa-id="<?php echo $mesa['id']; ?>">Ocupar</button>
                    <?php else: ?>
                        <button class="btn-ordenar" data-mesa-id="<?php echo $mesa['id']; ?>" style="display: none;">Ocupar</button>
                        <button class="btn-cobrar" data-mesa-id="<?php echo $mesa['id']; ?>">Cobrar</button>
                    <?php endif; ?>
    
                </div>
            <?php endwhile; ?>
            </div>
        </div>

        <!-- Modal para cobrar -->
        <div id="modalCobrar" class="modal">
            <div class="modal-contenidopago">
                <input type="hidden" id="mesa_id_cobro">
                <span id="cerrarModalCobrar" class="cerrar">&times;</span>
                <center><h2>Cobrar Orden</h2></center>
                <label for="monto_cliente">Monto del Cliente: $</label>
                <input type="number" id="monto_cliente" placeholder="Monto" required>
                <br>
                <button class="pago" id="procesarCobro">Procesar Cobro</button>
            </div>
        </div>

        <!-- Modal para realizar órdenes -->
        <div id="modalOrden" class="modal">
            <div class="modal-contenido">
                <span id="cerrarModal" class="cerrar">&times;</span>
                <center><h2>Realizar Orden</h2></center>
                <div id="productos-lista">
                    <?php while ($producto = mysqli_fetch_assoc($productos)): ?>
                        <div class="producto" onclick='agregarProducto(<?php echo $producto["id"]; ?>, "<?php echo $producto["nombre"]; ?>", <?php echo $producto["precio"]; ?>)'>
                            <?php echo $producto['nombre']; ?><br>
                            <p>$<?php echo $producto['precio']; ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
                <table id="tabla-orden" class="tab">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Detalles</th>
                            <th>Subtotal</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <h4>Total: $<span id="totalOrden">0.00</span></h4>
                <button id="confirmarOrden">Confirmar Orden</button>

            </div>
        </div>

        <script>
            function cerrarSesion() {
                    window.location.href = "index.html";
            }
        </script>
</body>
</html>

