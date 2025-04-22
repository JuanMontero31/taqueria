<?php

session_start(); // <-- Iniciar sesión
include 'conexion.php';

// Validar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $password = $_POST['password'];

    // Consultar en la base de datos
    $sql = "SELECT * FROM usuarios WHERE nombre = ? LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        
        // Verificar la contraseña
        if ($password == $usuario['password']) {
            $_SESSION['usuario'] = $usuario['nombre'];
            // Si la contraseña es correcta, redirigir al dashboard
            echo "<script>
                    window.location.href = 'dashboard.php';  // Redirigir al dashboard
                  </script>";
        } else {
            // Si la contraseña es incorrecta
            echo "<script>
                    alert('Contraseña incorrecta.');
                    window.location.href = 'index.html';  // Volver al formulario
                  </script>";
        }
    } else {
        // Si el usuario no existe
        echo "<script>
                alert('Usuario no encontrado.');
                window.location.href = 'index.html';  // Volver al formulario
              </script>";
    }
    
    $stmt->close();
}

$conexion->close();
?>
