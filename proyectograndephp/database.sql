DROP DATABASE IF EXISTS retrovault;
CREATE DATABASE retrovault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE retrovault;

-- Tabla de Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'coleccionista') NOT NULL DEFAULT 'coleccionista'
);

-- Tabla de Sistemas (Consolas)
CREATE TABLE sistemas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    fabricante VARCHAR(100) NOT NULL
);

-- Tabla de Videojuegos
CREATE TABLE videojuegos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    sistema_id INT NOT NULL,
    genero VARCHAR(50),
    FOREIGN KEY (sistema_id) REFERENCES sistemas(id) ON DELETE CASCADE
);

-- Tabla de Colecciones (Relación Usuario-Videojuego)
CREATE TABLE colecciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    videojuego_id INT NOT NULL,
    estado ENUM('nuevo', 'usado', 'roto') NOT NULL DEFAULT 'usado',
    nota INT CHECK (nota >= 0 AND nota <= 10),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (videojuego_id) REFERENCES videojuegos(id) ON DELETE CASCADE
);

-- Insertar Datos de Ejemplo

-- Usuarios (Password es '1234')
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Admin User', 'admin@retrovault.com', '$2y$10$8Q6XW6J6XW6J6XW6J6XW6u8q8q8q8q8q8q8q8q8q8q8q8q8q8q8q', 'admin'),
('Coleccionista 1', 'user1@retrovault.com', '$2y$10$8Q6XW6J6XW6J6XW6J6XW6u8q8q8q8q8q8q8q8q8q8q8q8q8q8q8q', 'coleccionista'),
('Coleccionista 2', 'user2@retrovault.com', '$2y$10$8Q6XW6J6XW6J6XW6J6XW6u8q8q8q8q8q8q8q8q8q8q8q8q8q8q8q', 'coleccionista');

-- Sistemas
INSERT INTO sistemas (nombre, fabricante) VALUES 
('NES', 'Nintendo'),
('SNES', 'Nintendo'),
('PlayStation', 'Sony'),
('Mega Drive', 'Sega'),
('N64', 'Nintendo');

-- Videojuegos
INSERT INTO videojuegos (titulo, sistema_id, genero) VALUES 
('Super Mario Bros', 1, 'Plataformas'),
('The Legend of Zelda', 1, 'Aventura'),
('Super Metroid', 2, 'Acción/Aventura'),
('Chrono Trigger', 2, 'RPG'),
('Final Fantasy VII', 3, 'RPG'),
('Metal Gear Solid', 3, 'Acción'),
('Sonic the Hedgehog', 4, 'Plataformas'),
('Streets of Rage 2', 4, 'Beat em up'),
('Super Mario 64', 5, 'Plataformas'),
('Ocarina of Time', 5, 'Aventura');

-- Colecciones
INSERT INTO colecciones (usuario_id, videojuego_id, estado, nota) VALUES 
(2, 1, 'usado', 8),
(2, 3, 'nuevo', 10),
(2, 5, 'usado', 9),
(3, 2, 'roto', 2),
(3, 7, 'usado', 7);
