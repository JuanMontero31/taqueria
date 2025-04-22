<?php
include 'conexion.php';

if (isset($_GET['mesa_id'])) {
    $mesa_id = intval($_GET['mesa_id']);

    $query = "SELECT SUM(p.precio * o.cantidad) AS total
              FROM ordenes o
              JOIN productos p ON o.producto_id = p.id
              WHERE o.mesa_id = $mesa_id";

    $resultado = mysqli_query($conexion, $query);
    $fila = mysqli_fetch_assoc($resultado);

    echo json_encode([
        'total' => floatval($fila['total'])
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mesa_id = $_POST['mesa_id'];
    $pago = $_POST['pago'];

    // Obtener la orden actual de la mesa
    $query = "SELECT o.*, p.nombre, p.precio FROM ordenes o 
              JOIN productos p ON o.producto_id = p.id 
              WHERE o.mesa_id = $mesa_id";
    $resultado = mysqli_query($conexion, $query);

    $total = 0;
    $detalles = [];

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $subtotal = $fila['precio'] * $fila['cantidad'];
        $total += $subtotal;
        $detalles[] = [
            'producto' => $fila['nombre'],
            'cantidad' => $fila['cantidad'],
            'precio' => $fila['precio'],
            'subtotal' => $subtotal,
            'detalles_extras' => $fila['detalles_extras'] // Añadimos los detalles extras
        ];
    }

    $cambio = $pago - $total;
    $json_detalles = json_encode($detalles, JSON_UNESCAPED_UNICODE); // Soporta acentos y caracteres especiales

    // Insertar en la tabla ventas
    $stmt = $conexion->prepare("INSERT INTO ventas (mesa_id, total, pago, cambio, detalles_orden) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iddds", $mesa_id, $total, $pago, $cambio, $json_detalles);
    $stmt->execute();

    // Limpiar órdenes de la mesa
    mysqli_query($conexion, "DELETE FROM ordenes WHERE mesa_id = $mesa_id");

    echo json_encode([
        'success' => true,
        'cambio' => $cambio,
        'ticket' => [
            'mesa' => $mesa_id,
            'total' => $total,
            'pago' => $pago,
            'cambio' => $cambio,
            'detalles' => $detalles
        ]
    ]);
    
}
?>
