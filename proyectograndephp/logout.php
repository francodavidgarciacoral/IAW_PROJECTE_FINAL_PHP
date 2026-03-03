<?php
// === ARCHIVO DE CIERRE DE SESIÓN (logout.php) ===
// Este archivo sirve para cerrar la sesión actual del usuario y destruir sus datos almacenados.

// Iniciar o recuperar la sesión actual para poder destruirla
session_start();

// Destruir toda la información registrada de una sesión (elimina las variables $_SESSION)
session_destroy();

// Redirigir al usuario de vuelta a la página principal después de cerrar sesión
header('Location: index.html');
exit;
?>