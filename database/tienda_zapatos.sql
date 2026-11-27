CREATE DATABASE IF NOT EXISTS tienda_zapatos
CHARACTER SET utf8mb4
COLLATE utf8mb4_spanish2_ci;

USE tienda_zapatos;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(150)
);

CREATE TABLE marcas (
    id_marca INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    cantidad INT NOT NULL DEFAULT 0,
    id_categoria INT NOT NULL,
    id_marca INT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria)
        REFERENCES categorias(id_categoria),
    FOREIGN KEY (id_marca)
        REFERENCES marcas(id_marca)
);

INSERT INTO categorias (nombre, descripcion) VALUES
('Deportivos', 'Calzado deportivo y para entrenamiento'),
('Casuales', 'Calzado para uso cotidiano'),
('Formales', 'Zapatos para ocasiones formales'),
('Sandalias', 'Calzado abierto y ligero'),
('Botas', 'Botas para diferentes usos');

INSERT INTO marcas (nombre) VALUES
('Nike'),
('Adidas'),
('Puma'),
('Reebok'),
('Converse');

INSERT INTO productos
(nombre, descripcion, precio, cantidad, id_categoria, id_marca)
VALUES
('Nike Air Max', 'Zapatos deportivos para hombre', 350000, 10, 1, 1),
('Adidas Run', 'Tenis deportivos para correr', 280000, 8, 1, 2),
('Zapato Oxford', 'Zapato formal de cuero negro', 190000, 5, 3, 3),
('Sandalia Casual', 'Sandalia para uso diario', 75000, 15, 4, 4),
('Bota Urbana', 'Bota casual de color cafe', 220000, 6, 5, 10);
