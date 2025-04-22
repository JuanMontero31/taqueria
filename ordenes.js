document.addEventListener('DOMContentLoaded', () => {
    // Abrir el modal al hacer clic en el botón de ordenar
    document.querySelectorAll('.btn-ordenar').forEach(boton => {
        boton.addEventListener('click', () => {
            const mesaId = boton.getAttribute('data-mesa-id');
            abrirModal(mesaId);
        });
    });

    // Botón para cerrar el modal
    document.getElementById('cerrarModal').addEventListener('click', cerrarModal);

    // Botón para confirmar orden
    document.getElementById('confirmarOrden').addEventListener('click', enviarOrden);
});

document.addEventListener('DOMContentLoaded', () => {
    // Mostrar el modal de cobro cuando se haga clic en "Cobrar"
    document.querySelectorAll('.btn-cobrar').forEach(boton => {
        boton.addEventListener('click', () => {
            const mesaId = boton.getAttribute('data-mesa-id');
            abrirModalCobrar(mesaId);
        });
    });

    // Botón para cerrar el modal de cobro
    document.getElementById('cerrarModalCobrar').addEventListener('click', cerrarModalCobrar);

    // Botón para procesar el cobro
    document.getElementById('procesarCobro').addEventListener('click', procesarCobro);
});

document.getElementById("monto_cliente").addEventListener("keypress", function (event) {
    if (event.key === "Enter") {
        event.preventDefault();
        procesarCobro();
    }
});

function abrirModalCobrar(mesaId) {
    document.getElementById('mesa_id_cobro').value = mesaId;
    document.getElementById('modalCobrar').style.display = 'block';

}

function cerrarModalCobrar() {
    document.getElementById('modalCobrar').style.display = 'none';
}

function procesarCobro() {
    const mesaId = document.getElementById('mesa_id_cobro').value;
    const montoCliente = parseFloat(document.getElementById('monto_cliente').value);

    if (montoCliente <= 0) {
        alert('El monto debe ser mayor que 0.');
        return;
    }

    // Obtener el total actual de la cuenta desde el servidor
    fetch(`guardar_venta.php?mesa_id=${mesaId}`)
        .then(res => res.json())
        .then(data => {
            const totalCuenta = parseFloat(data.total);

            if (isNaN(totalCuenta)) {
                alert('No se pudo obtener el total de la cuenta.');
                return;
            }

            if (montoCliente < totalCuenta) {
                alert(`El monto ingresado ($${montoCliente.toFixed(2)}) es menor al total de la cuenta ($${totalCuenta.toFixed(2)}).`);
                return;
            }

            // El monto es suficiente, continuar con el cobro
            fetch('guardar_venta.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `mesa_id=${mesaId}&pago=${montoCliente}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(`Venta registrada correctamente.\nCambio: $${data.cambio.toFixed(2)}`);
                    
                        const ticket = data.ticket;
                        let contenidoTicket = `
                        <style>
                            body {
                                font-family: 'Courier New', monospace;
                                font-size: 12px;
                                width: 230px;
                                margin: 0 auto;
                            }
                            h2, p {
                                text-align: center;
                                margin: 0;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 10px;
                            }
                            td {
                                padding: 2px 0;
                            }
                            .total {
                                margin-top: 10px;
                            }
                            .bold {
                                font-weight: bold;
                            }
                            .center {
                                text-align: center;
                            }
                            .right {
                                text-align: right;
                            }
                            .line {
                                border-top: 1px dashed #000;
                                margin: 5px 0;
                            }
                        </style>
                        <h2>Taqueria Dany</h2>
                        <p>Gracias por su compra</p>
                        <p>Mesa: ${ticket.mesa}</p>
                        <div class="line"></div>
                        <table>
                            <tbody>`;
                    
                        ticket.detalles.forEach(item => {
                            contenidoTicket += `
                                <tr>
                                    <td colspan="2">${item.producto}</td>
                                </tr>
                                <tr>
                                    <td>${item.cantidad} x $${parseFloat(item.precio).toFixed(2)}</td>
                                    <td class="right">$${parseFloat(item.subtotal).toFixed(2)}</td>
                                </tr>`;
                        });
                    
                        contenidoTicket += `</tbody></table>
                            <div class="line"></div>
                            <table class="total">
                                <tr><td class="bold">Total:</td><td class="right bold">$${parseFloat(ticket.total).toFixed(2)}</td></tr>
                                <tr><td>Pago:</td><td class="right">$${parseFloat(ticket.pago).toFixed(2)}</td></tr>
                                <tr><td>Cambio:</td><td class="right">$${parseFloat(ticket.cambio).toFixed(2)}</td></tr>
                            </table>
                            <div class="line"></div>
                            <p class="center">¡Vuelva pronto!</p>
                            <p class="center">${new Date().toLocaleString()}</p>
                        `;
                    
                        const ventana = window.open('', '_blank', 'width=1000,height=600');
                        ventana.document.open();
                        ventana.document.write(`
                        <html>
                        <head>
                            <title>Ticket</title>
                        </head>
                        <body>
                            ${contenidoTicket}
                            <script>
                                window.onload = () => {
                                    window.print();
                                    setTimeout(() => window.close(), 100);
                                };
                            </script>
                        </body>
                        </html>`);
                        ventana.document.close();

                    
                        location.reload();
                    } else {
                        alert(data.error || 'Hubo un error al registrar la venta.');
                    }
                    
                })
                .catch(err => {
                    console.error(err);
                    alert('Error de conexión.');
                });

            cerrarModalCobrar();
        })
        .catch(err => {
            console.error('Error al obtener el total:', err);
            alert('No se pudo verificar el total de la cuenta.');
        });
}

document.addEventListener('DOMContentLoaded', () => {
    // Botón para agregar más productos
    document.querySelectorAll('.btn-agregar-producto').forEach(boton => {
        boton.addEventListener('click', () => {
            const mesaId = boton.getAttribute('data-mesa-id');
            abrirModal(mesaId); // Abrir el modal de agregar productos
        });
    });
});


let ordenActual = [];
let mesaActual = null;

function abrirModal(mesaId) {
    mesaActual = mesaId;
    ordenActual = [];

    document.getElementById('modalOrden').style.display = 'block';
    document.getElementById('tabla-orden').innerHTML = '';
    document.getElementById('totalOrden').innerText = '0.00';
}

function cerrarModal() {
    document.getElementById('modalOrden').style.display = 'none';
}

function agregarProducto(id, nombre, precio) {
    let producto = ordenActual.find(p => p.id === id);
    if (producto) {
        producto.cantidad += 1;
        producto.subtotal = (producto.cantidad * producto.precio).toFixed(2);
    } else {
        ordenActual.push({
            id,
            nombre,
            precio,
            cantidad: 1,
            subtotal: precio.toFixed(2),
            detalles_extras: ''  // Añadir detalles vacíos inicialmente
        });
    }
    actualizarTabla();
}

function actualizarTabla() {
    const tabla = document.getElementById('tabla-orden');
    tabla.innerHTML = '';

    let total = 0;
    ordenActual.forEach((p, index) => {
        total += parseFloat(p.subtotal);
        const row = `
        <tr>
        <td>${p.nombre}</td>
        <td> x ${p.cantidad}</td>
        <td><textarea class="detalles-extras" data-index="${index}" rows="2" cols="40">${p.detalles_extras}</textarea></td>
        <td>$${p.subtotal}</td>
        <td><button class="tache" onclick="eliminarProducto(${index})">❌</button></td>
        </tr>
        `;
        tabla.innerHTML += row;
    });

    document.getElementById('totalOrden').innerText = total.toFixed(2);
    // Asignar el evento de actualización de detalles
    document.querySelectorAll('.detalles-extras').forEach(textarea => {
        textarea.addEventListener('input', function () {
            const index = this.getAttribute('data-index');
            ordenActual[index].detalles_extras = this.value;
        });
    });
}

function eliminarProducto(index) {
    ordenActual.splice(index, 1);
    actualizarTabla();
}

document.querySelectorAll('.btn-ordenar').forEach(boton => {
    boton.addEventListener('click', () => {
        const mesaId = boton.getAttribute('data-mesa-id');
        abrirModal(mesaId);
        cargarOrden(mesaId);
    });
});

function cargarOrden(mesaId) {
    fetch(`ordenes.php?mesa_id=${mesaId}`)
        .then(response => response.json())
        .then(data => {
            ordenActual = data; // Los productos existentes para esa mesa
            mesaActual = mesaId;
            actualizarTabla();
        })
        .catch(err => console.error('Error al cargar la orden: ', err));
}


function enviarOrden() {
    if (!mesaActual || ordenActual.length === 0) {
        alert('Debe seleccionar al menos un producto.');
        return;
    }

    const formData = new FormData();
    formData.append('guardar_orden', '1');
    formData.append('mesa_id', mesaActual);
    formData.append('productos', JSON.stringify(ordenActual)); // productos como texto plano

    console.log(ordenActual);  // Para verificar los detalles antes de enviarlos

    fetch('ordenes.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Orden guardada exitosamente.');
                window.location.href = 'ordenes.php'
                // Puedes volver a cargar la orden actual si deseas mantenerla visible
                cargarOrden(mesaActual); // Volver a traer lo que tiene la mesa
            } else {
                alert('Error al guardar la orden.');
            }
        })

        .catch(err => {
            console.error(err);
            alert('Error en la conexión con el servidor.');
        });

    cerrarModal();
}
