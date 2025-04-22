CREATE DATABASE taqueria;
USE taqueria;

-- Tabla de Usuarios (Admin)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Tabla de Mesas
CREATE TABLE mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE
);

-- Tabla de Productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255),
    precio DECIMAL(10,2) NOT NULL
);

-- Tabla de Órdenes (Cada pedido vincula una mesa con productos y cantidad)
CREATE TABLE ordenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mesa_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    detalles_extras VARCHAR(255),
    FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Tabla de Ventas (Registra cada venta con los detalles de la orden)
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mesa_id INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    pago DECIMAL(10,2) NOT NULL,
    cambio DECIMAL(10,2) NOT NULL,
    detalles_orden TEXT NOT NULL, -- Se almacenarán los productos en JSON o texto plano
    FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE CASCADE
);
