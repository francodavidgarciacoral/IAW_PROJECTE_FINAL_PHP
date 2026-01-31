<?php
require_once 'config.php';
require_once 'header.php';

// Auth Check
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'coleccionista') {
    echo "<div class='container'><p>Acceso denegado.</p></div>";
    exit;
}

$user_id = $_SESSION['usuario_id'];
$edit_mode = false;
$edit_data = null;
$pre_selected_game_id = $_GET['add_game_id'] ?? null;

// --- HANDLE POST REQUESTS (CRUD) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DELETE
    if (isset($_POST['delete_id'])) {
        $stmt = $conn->prepare("DELETE FROM colecciones WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $_POST['delete_id'], $user_id);
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Juego eliminado.";
        }
        $stmt->close();
        header('Location: my_collection.php');
        exit;
    }

    // ADD / EDIT
    $videojuego_id = $_POST['videojuego_id'] ?? null;
    $estado = $_POST['estado'] ?? 'usado';
    $nota = $_POST['nota'] ?? 0;
    $collection_id = $_POST['collection_id'] ?? null;

    if ($collection_id) {
        $stmt = $conn->prepare("UPDATE colecciones SET estado = ?, nota = ? WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("siii", $estado, $nota, $collection_id, $user_id);
    } else {
        // Check duplicate
        $check = $conn->prepare("SELECT id FROM colecciones WHERE usuario_id = ? AND videojuego_id = ?");
        $check->bind_param("ii", $user_id, $videojuego_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $_SESSION['errores'] = ["¡Ya tienes este juego!"];
            $check->close();
            header('Location: my_collection.php');
            exit;
        }
        $check->close();

        $stmt = $conn->prepare("INSERT INTO colecciones (usuario_id, videojuego_id, estado, nota) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $user_id, $videojuego_id, $estado, $nota);
    }

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Colección actualizada.";
        header('Location: my_collection.php');
        exit;
    } else {
        $_SESSION['errores'] = ["Error: " . $conn->error];
    }
}

// --- PREPARE DATA ---

// Edit Mode
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM colecciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $_GET['edit'], $user_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    $edit_mode = true;
    $stmt->close();
}

// Fetch User Collection
$query = "
    SELECT c.id as collection_id, c.estado, c.nota, c.fecha_registro,
           v.titulo, v.genero, s.nombre as sistema
    FROM colecciones c
    JOIN videojuegos v ON c.videojuego_id = v.id
    JOIN sistemas s ON v.sistema_id = s.id
    WHERE c.usuario_id = ?
    ORDER BY c.fecha_registro DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$mi_coleccion = $stmt->get_result();

// Data for Dropdown
$all_games = $conn->query("SELECT v.id, v.titulo, s.nombre as sistema FROM videojuegos v JOIN sistemas s ON v.sistema_id = s.id ORDER BY v.titulo ASC");
?>

<!-- === FORM SECTION === -->
<div class="card form-card">
    <h3 style="color:var(--neon-green); text-align:center;">
        <?php echo $edit_mode ? '✏️ Editar Juego' : '📦 Añadir a Colección'; ?>
    </h3>

    <form action="my_collection.php" method="POST">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="collection_id" value="<?php echo $edit_data['id']; ?>">
            <p class="text-center" style="font-size:1.2rem; font-weight:bold;">
                Editando: <?php echo htmlspecialchars($mi_coleccion->fetch_assoc()['titulo'] ?? 'Juego');
                // Note: resetting pointer for list below would be needed properly, but simplified here
                ?>
            </p>
        <?php else: ?>
            <label>Selecciona Juego:</label>
            <select name="videojuego_id" required>
                <option value="">-- Busca el juego --</option>
                <?php while ($g = $all_games->fetch_assoc()): ?>
                    <option value="<?php echo $g['id']; ?>" <?php echo ($pre_selected_game_id == $g['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($g['titulo']) . ' (' . htmlspecialchars($g['sistema']) . ')'; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
            <div>
                <label>Estado:</label>
                <select name="estado">
                    <option value="nuevo" <?php echo ($edit_mode && $edit_data['estado'] == 'nuevo') ? 'selected' : ''; ?>>✨ Nuevo / Precintado</option>
                    <option value="usado" <?php echo ($edit_mode && $edit_data['estado'] == 'usado') ? 'selected' : 'selected'; ?>>💿 Usado</option>
                    <option value="roto" <?php echo ($edit_mode && $edit_data['estado'] == 'roto') ? 'selected' : ''; ?>>
                        🛠️ Roto / Piezas</option>
                </select>
            </div>
            <div>
                <label>Valoración (0-10):</label>
                <input type="number" name="nota" min="0" max="10"
                    value="<?php echo $edit_mode ? $edit_data['nota'] : '8'; ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;">
            <?php echo $edit_mode ? '💾 Guardar Cambios' : '✅ Añadir a Colección'; ?>
        </button>

        <?php if ($edit_mode): ?>
            <a href="my_collection.php" class="btn btn-danger"
                style="display:block; text-align:center; margin-top:10px;">Cancelar</a>
        <?php endif; ?>
    </form>
</div>

<!-- === LIST SECTION === -->
<h2 class="mt-20">📚 Mi Biblioteca Personal</h2>

<div class="grid-container">
    <?php
    // Reset pointer if we used it in edit mode logic (simple fix: re-execute or just store in array first. 
    // For safety, re-execute or just iterate if we didn't consume it.
    // In the form above I fetched one row if edit_mode, so I should actually re-fetch or careful. 
    // Re-fetching for simplicity of this script generation.
    $stmt->execute();
    $mi_coleccion = $stmt->get_result();

    while ($row = $mi_coleccion->fetch_assoc()):
        ?>
        <div class="card">
            <div class="placeholder-img" style="height:100px; font-size:2rem;">💿</div>

            <div class="game-title"><?php echo htmlspecialchars($row['titulo']); ?></div>

            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span class="badge badge-console"><?php echo htmlspecialchars($row['sistema']); ?></span>
                <span class="badge" style="background:#334155;">⭐ <?php echo $row['nota']; ?>/10</span>
            </div>

            <div style="font-size:0.9rem; color:#aaa; margin-bottom:15px;">
                Estado: <span style="color:white;"><?php echo ucfirst($row['estado']); ?></span>
            </div>

            <div class="card-footer" style="display:flex; gap:10px; justify-content:flex-end;">
                <a href="my_collection.php?edit=<?php echo $row['collection_id']; ?>" class="btn btn-small"
                    style="background:#3b82f6;">✏️</a>

                <form action="my_collection.php" method="POST" onsubmit="return confirm('¿Borrar juego?');">
                    <input type="hidden" name="delete_id" value="<?php echo $row['collection_id']; ?>">
                    <button type="submit" class="btn btn-small btn-danger">🗑️</button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</body>

</html>