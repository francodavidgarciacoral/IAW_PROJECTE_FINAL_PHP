<?php
require_once 'config.php';
require_once 'header.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Stats Logic
$result = $conn->query("SELECT COUNT(*) as total FROM videojuegos");
$total_juegos = $result->fetch_assoc()['total'];

$mi_coleccion_count = 0;
if ($_SESSION['usuario_rol'] === 'coleccionista') {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM colecciones WHERE usuario_id = ?");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $mi_coleccion_count = $stmt->get_result()->fetch_assoc()['total'];
}
?>

<div class="text-center" style="margin-bottom:40px;">
    <h2>¡Bienvenido al Búnker, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>!</h2>
    <p style="color:var(--text-muted);">Tu centro de operaciones para el coleccionismo retro.</p>
</div>

<div class="dashboard-stats">
    <div class="card stat-card">
        <div class="stat-number"><?php echo $total_juegos; ?></div>
        <div class="stat-label">Juegos en la Base de Datos Global</div>
        <a href="catalog.php" class="btn btn-primary btn-small mt-20">Explorar Catálogo</a>
    </div>

    <?php if ($_SESSION['usuario_rol'] === 'coleccionista'): ?>
        <div class="card stat-card">
            <div class="stat-number" style="color:var(--neon-purple);"><?php echo $mi_coleccion_count; ?></div>
            <div class="stat-label">Juegos en tu Colección</div>
            <a href="my_collection.php" class="btn btn-primary btn-small mt-20"
                style="background-color:var(--neon-purple);">Ver Mi Colección</a>
        </div>
    <?php endif; ?>

    <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
        <div class="card stat-card">
            <div class="stat-number" style="color:var(--danger);">ADMIN</div>
            <div class="stat-label">Panel de Administración</div>
            <a href="manage_games.php" class="btn btn-small mt-20" style="border:1px solid white;">Gestionar</a>
        </div>
    <?php endif; ?>
</div>

</body>

</html>