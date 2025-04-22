<?php
include 'conexion.php';
session_start();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
    <link rel="stylesheet" href="estilos/estilos_productos.css">
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
                <li class="user"><img src="recursos/bebida.png" alt=""><a href="productos.php" >Productos</a></li>
                <li><img src="recursos/contacto.png" alt=""><a href="usuarios.php" >Usuarios</a></li>
                <button onclick="cerrarSesion()" class="logout-btn">Cerrar Sesión</button>
            </ul>
        </nav>
    </div>
    
    <div class="contenedor">
        <h2>Productos</h2>
        <div class="cuadros">
        <div class="tabla-productos">
            
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="lista-productos">
                    <?php
                    include "productos_acciones.php";
                    $productos = obtener_productos($conexion);

                    // Verificar si existen productos
                    if (mysqli_num_rows($productos) > 0) {
                        // Recorrer y mostrar cada producto en la tabla
                        while ($producto = mysqli_fetch_assoc($productos)) {
                            echo "<tr>";
                            echo "<td>" . $producto['tipo'] . "</td>";
                            echo "<td>" . $producto['nombre'] . "</td>";
                            echo "<td>" . $producto['descripcion'] . "</td>";
                            echo "<td>$" . $producto['precio'] . "</td>";
                            echo "<td>
                                    <div>
                                    <button type='button' onclick='editarProducto(" . json_encode($producto) . ")'>Editar</button>
                                    <button onclick=\"window.location.href='productos_acciones.php?accion=eliminar&id=" . $producto['id'] . "'\">Eliminar</button>
                                    </div>
                                 </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No hay productos disponibles.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Formulario para agregar productos -->
        <div class="formulario-producto">
            <h2>Agregar Producto</h2>
            <form id="form-producto" action="productos_acciones.php" method="POST">

                <input type="hidden" id="id" name="id">
                <input type="text" id="tipo" name="tipo" placeholder="Tipo" required>
                <input type="text" id="nombre" name="nombre" placeholder="Nombre" required>
                <input type="text" id="descripcion" name="descripcion" placeholder="Descripción" required>
                <input type="number" step="0.01" id="precio" name="precio" placeholder="Precio" required>

                <button id="btn-submit" type="submit" name="accion" value="agregar">Agregar</button>
                <button type="button" class="cancelar" id="btn-cancelar" onclick="cancelarEdicion()" style="display:none;">Cancelar</button>


            </form>
        </div>
        </div>
    </div>

    <script>
        function cerrarSesion() {
                window.location.href = "index.html";
        }

        function editarProducto(producto) {
            document.getElementById("id").value = producto.id;
            document.getElementById("tipo").value = producto.tipo;
            document.getElementById("nombre").value = producto.nombre;
            document.getElementById("descripcion").value = producto.descripcion;
            document.getElementById("precio").value = producto.precio;

            const btn = document.getElementById("btn-submit");
            btn.textContent = "Actualizar";
            btn.value = "actualizar";

            // Mostrar botón cancelar
            document.getElementById("btn-cancelar").style.display = "inline-block";
        }

        function cancelarEdicion() {
            document.getElementById("form-producto").reset(); // Limpia el formulario
            document.getElementById("id").value = "";         // Limpia el ID oculto

            const btn = document.getElementById("btn-submit");
            btn.textContent = "Agregar";
            btn.value = "agregar";

            // Oculta el botón cancelar
            document.getElementById("btn-cancelar").style.display = "none";
        }


    </script>


</body>
</html>
