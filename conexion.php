<?php
$servidor = "mysql_db";  // Cambia esto si tu servidor no está en localhost
$usuario = "root";        // Cambia esto si tu usuario de MySQL no es 'root'
$contraseña = "1234";         // Cambia esto si tienes contraseña
$base_de_datos = "taqueria";

$conexion = new mysqli($servidor, $usuario, $contraseña, $base_de_datos);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>
