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
    descripcion TEXT,
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
('Coleccionista 1', 'user1@retrovault.com', '$2y$10$8Q6XW6J6XW6J6XW6J6XW6u8q8q8q8q8q8q8q8q8q8q8q8q8q8q8q', 'coleccionista');

-- Sistemas
INSERT INTO sistemas (nombre, fabricante) VALUES 
('NES', 'Nintendo'),
('SNES', 'Nintendo'),
('PlayStation', 'Sony'),
('Mega Drive', 'Sega'),
('Nintendo 64', 'Nintendo');

-- Videojuegos
INSERT INTO videojuegos (titulo, sistema_id, genero, descripcion) VALUES 
-- NES (id 1)
('Super Mario Bros', 1, 'Plataformas', 'El clásico que salvó la industria en 1985. Controla a Mario en su misión para rescatar a la Princesa Peach.'),
('The Legend of Zelda', 1, 'Aventura', 'Una aventura épica en Hyrule donde Link debe reunir los fragmentos de la Trifuerza para vencer a Ganon.'),
('Metroid', 1, 'Acción/Aventura', 'Explora el planeta Zebes con Samus Aran y lucha contra los Piratas Espaciales en esta aventura no lineal.'),
('Duck Hunt', 1, 'Shooter', 'Usa la Zapper para cazar patos en este clásico arcade de puntería.'),
-- SNES (id 2)
('Super Mario World', 2, 'Plataformas', 'Mario viaja a Dinosaur Land para salvar a los dinosaurios en uno de los mejores plataformas de la historia.'),
('The Legend of Zelda: A Link to the Past', 2, 'Aventura', 'Link viaja entre el Mundo de la Luz y el Mundo de la Oscuridad en esta obra maestra de 16 bits.'),
('Super Metroid', 2, 'Acción/Aventura', 'La definición del género Metroidvania. Atmósfera densa y exploración profunda en el planeta Zebes.'),
('Chrono Trigger', 2, 'RPG', 'Un RPG de viajes en el tiempo con múltiples finales y un diseño de personajes de Akira Toriyama.'),
('Donkey Kong Country', 2, 'Plataformas', 'Revolucionarios gráficos prerrenderizados y jugabilidad desafiante con Donkey y Diddy Kong.'),
-- PlayStation (id 3)
('Final Fantasy VII', 3, 'RPG', 'La historia de Cloud Strife y AVALANCHE contra Shinra y Sephiroth que popularizó los JRPGs en occidente.'),
('Metal Gear Solid', 3, 'Acción/Sigilo', 'Espionaje táctico cinematográfico. Solid Snake debe infiltrarse en Shadow Moses para detener una amenaza nuclear.'),
('Tekken 3', 3, 'Lucha', 'Considerado uno de los mejores juegos de lucha de todos los tiempos, introdujo el movimiento en 3D real.'),
('Resident Evil 2', 3, 'Terror', 'Survival horror en Raccoon City. Leon y Claire luchan por sobrevivir al brote del Virus-G.'),
('Castlevania: Symphony of the Night', 3, 'Metroidvania', 'Alucard explora el castillo de Drácula en este título que redefinió la franquicia con elementos RPG.'),
-- Mega Drive (id 4)
('Sonic the Hedgehog 2', 4, 'Plataformas', 'Sonic y Tails corren a velocidad supersónica para detener al Dr. Robotnik y su Death Egg.'),
('Streets of Rage 2', 4, 'Beat em up', 'Lucha callejera con una banda sonora legendaria de Yuzo Koshiro.'),
('Golden Axe', 4, 'Beat em up', 'Fantasía épica beat em up donde eliges entre un bárbaro, una amazona o un enano.'),
('Gunstar Heroes', 4, 'Run and Gun', 'Acción frenética y jefes gigantes en este clásico de Treasure.'),
('Shinobi III', 4, 'Acción', 'Joe Musashi regresa con movimientos ninja fluidos y acción rápida.'),
-- Nintendo 64 (id 5)
('Super Mario 64', 5, 'Plataformas', 'El juego que definió cómo moverse en un espacio 3D. Mario recolecta estrellas en el castillo de Peach.'),
('The Legend of Zelda: Ocarina of Time', 5, 'Aventura', 'Una leyenda atemporal. Link viaja por el tiempo para detener a Ganondorf en el primer Zelda en 3D.'),
('GoldenEye 007', 5, 'Shooter', 'El shooter que demostró que el género podía funcionar en consolas, con un modo multijugador legendario.'),
('Mario Kart 64', 5, 'Carreras', 'Carreras locas con objetos en 3D que se convirtieron en el estándar de las fiestas multijugador.'),
('Star Fox 64', 5, 'Shooter', 'Combate aéreo cinematográfico con voces reales y el innovador Rumble Pak.');

-- Colecciones (Corregido: Todos los juegos para el usuario 2 que sí existe)
INSERT INTO colecciones (usuario_id, videojuego_id, estado, nota) VALUES 
(2, 1, 'usado', 8),
(2, 6, 'nuevo', 10),
(2, 11, 'usado', 9),
(2, 16, 'roto', 2),
(2, 21, 'usado', 7);
