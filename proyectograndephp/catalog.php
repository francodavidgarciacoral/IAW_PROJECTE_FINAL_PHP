<?php
require_once 'config.php';
require_once 'header.php';

// Check auth
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Search Logic
$search = $_GET['q'] ?? '';
$query = "
    SELECT v.*, s.nombre as sistema_nombre 
    FROM videojuegos v 
    JOIN sistemas s ON v.sistema_id = s.id 
";

if (!empty($search)) {
    $city = $conn->real_escape_string($search);
    $query .= " WHERE v.titulo LIKE '%$search%' OR s.nombre LIKE '%$search%'";
}

$query .= " ORDER BY v.titulo ASC";
$result = $conn->query($query);
?>

<div class="search-bar">
    <form action="catalog.php" method="GET" style="display:flex; width:100%; gap:10px;">
        <input type="text" name="q" placeholder="Buscar juegos por título o sistema..."
            value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary">🔍 Buscar</button>
    </form>
</div>

<h2>🕹️ Catálogo Completo</h2>

<div class="grid-container">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="card">
            <!-- Placeholder Image logic could go here -->
            <div class="placeholder-img">🎮</div>

            <div class="game-title">
                <?php echo htmlspecialchars($row['titulo']); ?>
            </div>

            <div>
                <span class="badge badge-console">
                    <?php echo htmlspecialchars($row['sistema_nombre']); ?>
                </span>
                <span class="badge badge-genre">
                    <?php echo htmlspecialchars($row['genero']); ?>
                </span>
            </div>

            <div class="card-footer">
                <?php if ($_SESSION['usuario_rol'] === 'coleccionista'): ?>
                    <a href="my_collection.php?add_game_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-small">
                        ➕ Añadir
                    </a>
                <?php else: ?>
                    <span style="font-size:0.8rem; color:#aaa;">Solo Coleccionistas</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</body>

</html>