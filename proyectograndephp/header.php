<?php
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
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <header>
            <div class="logo">
                <h1><a href="dashboard.php" style="color:var(--neon-green); text-decoration:none;">RETROVAULT</a></h1>
            </div>
            <nav>
                <a href="dashboard.php">🏠 Inicio</a>
                <a href="catalog.php">🔍 Ver Catálogo</a>

                <?php if ($_SESSION['usuario_rol'] === 'coleccionista'): ?>
                    <a href="my_collection.php">📦 Mi Colección</a>
                <?php endif; ?>

                <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                    <a href="manage_games.php">⚙️ Gestión</a>
                <?php endif; ?>

                <span class="user-info"
                    style="align-self: center; border-left: 1px solid #334155; padding-left: 20px; margin-left: 10px;">
                    <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                </span>
                <a href="logout.php" style="color: var(--danger);">🚪 Salir</a>
            </nav>
        </header>
    <?php endif; ?>

    <div class="container">
        <?php include_once 'error.php'; ?>