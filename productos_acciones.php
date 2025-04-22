<?php
include "conexion.php"; // Conexión a la base de datos

// Función para agregar producto
if (isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    $tipo = $_POST['tipo'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];

    $sql = "INSERT INTO productos (tipo, nombre, descripcion, precio) 
            VALUES ('$tipo', '$nombre', '$descripcion', '$precio')";

    if (mysqli_query($conexion, $sql)) {
        echo "<script>alert('Producto agregado correctamente'); window.location.href='productos.php';</script>";
    } else {
        echo "<script>alert('Error al agregar producto');</script>";
    }
}

// Función para actualizar producto
if (isset($_POST['accion']) && $_POST['accion'] == 'actualizar') {
    $id = $_POST['id'];
    $tipo = $_POST['tipo'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];

    $sql = "UPDATE productos SET tipo='$tipo', nombre='$nombre', descripcion='$descripcion', precio='$precio' WHERE id=$id";

    if (mysqli_query($conexion, $sql)) {
        echo "<script>alert('Producto actualizado correctamente'); window.location.href='productos.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar producto');</script>";
    }
}

// Función para eliminar producto
if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar') {
    $id = $_GET['id'];
    $sql = "DELETE FROM productos WHERE id=$id";

    if (mysqli_query($conexion, $sql)) {
        echo "<script>alert('Producto eliminado correctamente'); window.location.href='productos.php';</script>";
    } else {
        echo "<script>alert('Error al eliminar producto');</script>";
    }
}

// Función para obtener todos los productos
function obtener_productos($conexion) {
    $resultado = mysqli_query($conexion, "SELECT * FROM productos ORDER BY tipo ASC, nombre ASC");
    return $resultado;
}

?>
