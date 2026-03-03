<?php
// === ARCHIVO DE DETALLES (details.php) ===
// Muestra la información extendida de un juego en particular.
// Permite añadir el juego a la colección personal si eres un usuario registrado.
require_once 'config.php';
require_once 'header.php';

// Validate ID
// Comprobamos si nos han pasado un ID por la URL (GET) y si es un número válido. Si no, redirigimos al catálogo.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: catalog.php');
    exit;
}

$game_id = (int) $_GET['id'];

// Fetch Game Details
// Consulta SQL SELECT: (READ del CRUD) Obtiene todos los detalles de este juego en específico
// basándose en el ID que recibió por la URL. Hace JOIN con 'sistemas' para mostrar el nombre de su plataforma.
$stmt = $conn->prepare("
    SELECT v.*, s.nombre as sistema_nombre 
    FROM videojuegos v 
    JOIN sistemas s ON v.sistema_id = s.id 
    WHERE v.id = ?
");
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='container'><h2 class='text-center'>Juego no encontrado</h2><center><a href='catalog.php' class='btn btn-primary'>Volver</a></center></div>";
    exit;
}

$game = $result->fetch_assoc();
$stmt->close();
?>

<!-- Details View -->
<div class="container mt-20">
    <a href="catalog.php" style="color:var(--text-muted); margin-bottom:20px; display:inline-block;">&larr; Volver al
        Catálogo</a>

    <div class="card"
        style="display: flex; gap: 40px; flex-wrap: wrap; align-items: flex-start; padding: 40px; border: 1px solid var(--neon-blue);">

        <!-- Big Cover Image -->
        <div
            style="flex: 0 0 300px; max-width: 100%; display: flex; align-items: center; justify-content: center; background-color: #1e293b; height: 300px; border-radius: 12px; box-shadow: 0 0 20px rgba(0,0,0,0.5); border: 2px solid #334155;">
            <span
                style="color: var(--neon-blue); font-size: 2rem; font-weight: bold; text-align: center;">[<?php echo htmlspecialchars($game['sistema_nombre']); ?>]</span>
        </div>

        <!-- Info & History -->
        <div style="flex: 1; min-width: 300px;">
            <h1 style="font-size: 2.5rem; color: var(--neon-green); margin-bottom: 5px;">
                <?php echo htmlspecialchars($game['titulo']); ?>
            </h1>

            <div style="margin-bottom: 20px;">
                <span class="badge badge-console" style="font-size: 1rem; padding: 5px 15px;">
                    <?php echo htmlspecialchars($game['sistema_nombre']); ?>
                </span>
                <span class="badge badge-genre" style="font-size: 1rem; padding: 5px 15px;">
                    <?php echo htmlspecialchars($game['genero']); ?>
                </span>
            </div>

            <h3
                style="color: var(--neon-blue); border-bottom: 1px solid #334155; padding-bottom: 10px; margin-top: 30px;">
                📖 Historia
            </h3>
            <p style="font-size: 1.1rem; line-height: 1.8; color: var(--text-main); margin-bottom: 30px;">
                <?php echo nl2br(htmlspecialchars($game['descripcion'] ?? 'Sin descripción histórica disponible.')); ?>
            </p>

            <!-- Actions -->
            <div style="display: flex; gap: 20px; align-items: center;">
                <!-- Comprobamos si el usuario es un coleccionista o admin usando los permisos guardados en $_SESSION. -->
                <!-- Esto decide si mostramos el botón para poder añadir el juego a su colección personal. -->
                <?php if (isset($_SESSION['usuario_rol']) && ($_SESSION['usuario_rol'] == 'coleccionista' || $_SESSION['usuario_rol'] == 'admin')): ?>
                    <a href="my_collection.php?add_game_id=<?php echo $game['id']; ?>" class="btn btn-primary"
                        style="font-size: 1.2rem; padding: 15px 30px;">
                        ➕ Añadir a mi Colección
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-small">Inicia sesión para coleccionar</a>
                <?php endif; ?>

                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'admin'): ?>
                    <a href="admin.php?edit_game=<?php echo $game['id']; ?>" class="btn btn-danger">✏️ Editar (Admin)</a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

</body>

</html>