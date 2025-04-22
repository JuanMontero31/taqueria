<?php
include 'conexion.php';
header('Content-Type: application/json');

//file_put_contents("debug.txt", print_r($_GET, true));
$condiciones = [];
$params = [];

if (!empty($_GET['fecha'])) {
    $condiciones[] = "DATE(fecha) = ?";
    $params[] = $_GET['fecha'];
}
if (!empty($_GET['mes'])) {
    $condiciones[] = "MONTH(fecha) = ?";
    $params[] = $_GET['mes'];
}
if (!empty($_GET['anio'])) {
    $condiciones[] = "YEAR(fecha) = ?";
    $params[] = $_GET['anio'];
}

$sql = "SELECT * FROM ventas";
if (!empty($condiciones)) {
    $sql .= " WHERE " . implode(" AND ", $condiciones);
}
$sql .= " ORDER BY fecha DESC";

$stmt = $conexion->prepare($sql);
if (!empty($params)) {
    $tipos = str_repeat("s", count($params)); // asume strings
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

$ventas = [];
while ($row = $resultado->fetch_assoc()) {
    $ventas[] = $row;
}

echo json_encode($ventas);
?>
