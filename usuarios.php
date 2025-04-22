<?php
session_start();

include "conexion.php"; // Conexión a la base de datos

// Agregar usuario con contraseña
if (isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    $nombre = $_POST['nombre'];
    $password = $_POST['password']; // Cambio de 'contraseña' a 'password'

    // Encriptar la contraseña usando bcrypt
    //$password_hash = password_hash($password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO usuarios (nombre, password) VALUES ('$nombre', '$password')";
    if (mysqli_query($conexion, $sql)) {
        echo "<script>alert('Usuario agregado correctamente'); window.location.href='usuarios.php';</script>";
    } else {
        echo "<script>alert('Error al agregar usuario');</script>";
    }
}

// Eliminar usuario
if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar') {
    $id = $_GET['id'];
    $sql = "DELETE FROM usuarios WHERE id=$id";

    if (mysqli_query($conexion, $sql)) {
        echo "<script>alert('Usuario eliminado correctamente'); window.location.href='usuarios.php';</script>";
    } else {
        echo "<script>alert('Error al eliminar usuario');</script>";
    }
}

// Obtener usuarios
$usuarios = mysqli_query($conexion, "SELECT * FROM usuarios");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
    <link rel="stylesheet" href="estilos/estilos_usuarios.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
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
                <li><img src="recursos/moneda.png" alt=""><a href="ventas.php" >Ventas</a></li>
                <li><img src="recursos/bebida.png" alt=""><a href="productos.php" >Productos</a></li>
                <li class="user"><img src="recursos/contacto.png" alt=""><a href="usuarios.php" >Usuarios</a></li>
                <button onclick="cerrarSesion()" class="logout-btn">Cerrar Sesión</button>
            </ul>
        </nav>
    </div>

    <div class="contenedor">
        <!-- Formulario para agregar usuario -->
        <div class="formulario-usuario">
            <h2>Agregar Usuarios</h2>
            <form action="usuarios.php" method="POST">
                <center>
                <input type="text" id="nombre" name="nombre" placeholder="Nombre" required >
                <input type="password" id="password" name="password" placeholder="Contraseña" required><br><br>
                </center>
                <center>
                <button type="submit" name="accion" value="agregar">Agregar</button>
                </center>
            </form>
        </div><br><br>

        <!-- Tabla de usuarios -->
         <center>
        <div class="tabla-usuarios">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Mostrar usuarios
                    if (mysqli_num_rows($usuarios) > 0) {
                        while ($usuario = mysqli_fetch_assoc($usuarios)) {
                            echo "<tr>";
                            echo "<td>" . $usuario['nombre'] . "</td>";
                            echo "<td><a href='usuarios.php?accion=eliminar&id=" . $usuario['id'] . "'>Eliminar</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>No hay usuarios registrados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    </center>

    <script>
        function cerrarSesion() {
                window.location.href = "index.html";
        }
    </script>
</body>
</html>
