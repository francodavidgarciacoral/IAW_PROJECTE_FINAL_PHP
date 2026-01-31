<?php
// Archivo para mostrar errores de validación
// Se espera que 'errores' sea un array en la sesión o pasado como variable

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['errores']) && count($_SESSION['errores']) > 0) {
    echo '<div class="error-container">';
    echo '<h3>Error</h3>';
    echo '<ul>';
    foreach ($_SESSION['errores'] as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul>';
    echo '</div>';

    // Limpiar errores después de mostrarlos
    unset($_SESSION['errores']);
}

if (isset($_SESSION['mensaje'])) {
    echo '<div class="success-container">';
    echo '<p>' . htmlspecialchars($_SESSION['mensaje']) . '</p>';
    echo '</div>';
    unset($_SESSION['mensaje']);
}
?>