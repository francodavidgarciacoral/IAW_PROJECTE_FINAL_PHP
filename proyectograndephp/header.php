<?php
// === ARCHIVO HEADER (header.php) ===
// Este archivo sirve como la cabecera (menú de navegación) común para todas las páginas logueadas.
// Contiene la estructura básica en HTML y los enlaces navegacionales del proyecto.

// Si la sesión no ha arrancado (no existe la cookie de sesión en PHP), la iniciamos.
// Esto es necesario para leer de $_SESSION el rol, nombre, etc.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroVault</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Comprobamos si el usuario ha iniciado sesión. Si es así, mostramos el menú de navegación -->
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <header>
            <div class="logo">
                <h1><a href="index.html" style="color:var(--neon-green); text-decoration:none;">RETROVAULT</a></h1>
            </div>
            <nav>
                <a href="index.html">🏠 Inicio</a>
                <a href="catalog.php">🔍 Catálogo</a>

                <!-- Comprobamos si el usuario es coleccionista o admin para mostrar el enlace "Mi Colección" -->
                <?php if ($_SESSION['usuario_rol'] === 'coleccionista' || $_SESSION['usuario_rol'] === 'admin'): ?>
                    <a href="my_collection.php">📦 Mi Colección</a>
                <?php endif; ?>

                <!-- Comprobamos de nuevo si es administrador para mostrarle el botón al Panel de Admin -->
                <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                    <a href="admin.php" style="color:var(--neon-purple);">⚙️ Panel Admin</a>
                <?php endif; ?>

                <span class="user-info"
                    style="align-self: center; border-left: 1px solid #334155; padding-left: 20px; margin-left: 10px;">
                    <!-- Mostramos el nombre del usuario recuperado desde la variable de sesión -->
                    <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                </span>
                <a href="logout.php" style="color: var(--danger);">🚪 Salir</a>
            </nav>
        </header>
    <?php endif; ?>

    <div class="container">
        <!-- Incluimos error.php en todas nuestras páginas públicas y privadas para manejar alertas -->
        <?php include_once 'error.php'; ?>