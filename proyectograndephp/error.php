<?php
// === ARCHIVO DE ERRORES (error.php) ===
// Este archivo sirve para mostrar mensajes de error (como contraseñas incorrectas) 
// o mensajes de éxito (como registro completado) al usuario.
// Se incluye en otras páginas (como login.php o register.php) para mostrar las alertas.

// Comprobamos si la sesión ya está iniciada. Si no lo está, la iniciamos.
// Usamos $_SESSION para poder pasar mensajes de error/éxito entre recargas de página.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Comprobamos si existe un array de 'errores' guardado en la sesión y si tiene elementos.
if (isset($_SESSION['errores']) && count($_SESSION['errores']) > 0) {
    echo '<div class="error-container">';
    echo '<h3>Error</h3>';
    echo '<ul>';
    foreach ($_SESSION['errores'] as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul>';
    echo '</div>';

    // Limpiar errores después de mostrarlos para que no vuelvan a aparecer al recargar
    unset($_SESSION['errores']);
}

// Comprobamos si hay un mensaje general de éxito o información guardado en la sesión
if (isset($_SESSION['mensaje'])) {
    echo '<div class="success-container">';
    echo '<p>' . htmlspecialchars($_SESSION['mensaje']) . '</p>';
    echo '</div>';

    // Limpiar mensaje después de mostrarlo
    unset($_SESSION['mensaje']);
}
?>