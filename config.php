<?php
// === ARCHIVO DE CONFIGURACIÓN (config.php) ===
// Este archivo sirve para establecer la conexión entre nuestra aplicación PHP y la base de datos MySQL.
// Se incluye (require_once) en casi todos los demás archivos para poder hacer consultas.

// Configuración de la Base de Datos
$host = 'localhost';
$usuario = 'root'; // Usuario por defecto de XAMPP
$password = '';    // Contraseña por defecto de XAMPP (vacía)
$base_datos = 'retrovault';

// Crear conexión usando mysqli
$conn = new mysqli($host, $usuario, $password, $base_datos);

// Verificar la conexión
// Comprobamos si hubo un error al intentar conectarse a la base de datos
if ($conn->connect_error) {
    // Si falla, redirigimos a una página de error genérica o mostramos un mensaje
    // Para producción, no mostrar errores detallados al usuario final
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer el juego de caracteres a utf8mb4 para soportar tildes, ñ y emojis
$conn->set_charset("utf8mb4");
?>