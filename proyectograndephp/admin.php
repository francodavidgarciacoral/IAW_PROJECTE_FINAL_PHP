<?php
require_once 'config.php';
require_once 'header.php';

// Access Control: Admin Only
// Comprobamos si el usuario ha iniciado sesión Y si su rol guardado en $_SESSION es 'admin'. 
// Si no es admin, lo redirigimos al catálogo general para proteger este panel.
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: catalog.php');
    exit;
}

// --- GAMES LOGIC ---
$game_titulo = '';
$game_sistema_id = '';
$game_genero = '';
$game_desc = '';
$game_edit_id = null;
$game_message = '';

// Handle Game Form
// Comprobamos si nos han enviado un formulario por POST y si la acción a realizar es sobre un juego (CRUD de juegos).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type']) && $_POST['action_type'] === 'game') {
    // Delete Game
    // Aquí empieza la parte de BORRAR (DELETE) del CRUD de videojuegos
    if (isset($_POST['delete_id'])) {
        $id_to_delete = $_POST['delete_id'];
        // Consulta SQL DELETE: Elimina un videojuego de la base de datos coincidiendo con su ID
        $stmt = $conn->prepare("DELETE FROM videojuegos WHERE id = ?");
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            $game_message = "Juego eliminado.";
        }
        $stmt->close();
    }
    // Create/Update Game
    // Aquí empieza la parte de CREAR (INSERT) y ACTUALIZAR (UPDATE) del CRUD de videojuegos
    else {
        $titulo = trim($_POST['titulo']);
        $sistema_id = $_POST['sistema_id'];
        $genero = trim($_POST['genero']);
        $desc = trim($_POST['descripcion']);
        $edit_id = $_POST['edit_id'] ?? null;

        if (!empty($titulo) && !empty($sistema_id)) {
            if ($edit_id) {
                // Consulta SQL UPDATE: Actualiza los campos de un videojuego existente 
                $stmt = $conn->prepare("UPDATE videojuegos SET titulo=?, sistema_id=?, genero=?, descripcion=? WHERE id=?");
                $stmt->bind_param("sissi", $titulo, $sistema_id, $genero, $desc, $edit_id);
            } else {
                // Consulta SQL INSERT: Agrega un nuevo registro de videojuego a la tabla 
                $stmt = $conn->prepare("INSERT INTO videojuegos (titulo, sistema_id, genero, descripcion) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("siss", $titulo, $sistema_id, $genero, $desc);
            }
            if ($stmt->execute()) {
                $game_message = $edit_id ? "Juego actualizado." : "Juego creado.";
                // Reset vars
                if (!$edit_id) {
                    $titulo = $sistema = $genero = $desc = '';
                }
            } else {
                $game_message = "Error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Load Game for Edit
// Comprobamos si se envió la petición por GET para rellenar el formulario antes de editar
if (isset($_GET['edit_game'])) {
    $edit_id = $_GET['edit_game'];
    // Consulta SQL SELECT (READ del CRUD para editar): Trae un juego específico basándose en su ID
    $stmt = $conn->prepare("SELECT * FROM videojuegos WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($g = $res->fetch_assoc()) {
        $game_titulo = $g['titulo'];
        $game_sistema_id = $g['sistema_id'];
        $game_genero = $g['genero'];
        $game_desc = $g['descripcion'];
        $game_edit_id = $g['id'];
    }
    $stmt->close();
}

// --- USERS LOGIC ---
$user_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type']) && $_POST['action_type'] === 'user') {
    // Change Role
    if (isset($_POST['change_role'])) {
        $uid = (int) $_POST['user_id'];
        $role = $_POST['new_role'];
        if (in_array($role, ['admin', 'coleccionista'])) {
            // Consulta SQL UPDATE para Usuarios: Permite al admin ascender cuentas normales o quitar permisos.
            $stmt = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
            $stmt->bind_param("si", $role, $uid);
            $stmt->execute();
            $user_message = "Rol actualizado.";
            $stmt->close();
        }
    }
    // Delete User
    if (isset($_POST['delete_user'])) {
        $uid = (int) $_POST['user_id'];
        if ($uid != $_SESSION['usuario_id']) {
            // Consulta SQL DELETE para Usuarios: Elimina un usuario por completo.
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $user_message = "Usuario eliminado.";
            $stmt->close();
        }
    }
}

// Fetch Data
// Aquí empieza la parte de LEER (READ) del CRUD general para las tablas de la vista
// Consulta SQL SELECT: Devuelve la lista completa de consolas (sistemas) para los desplegables.
$sistemas = $conn->query("SELECT * FROM sistemas ORDER BY nombre ASC");
// Consulta SQL SELECT: Devuelve la lista completa de videojuegos y el nombre de su sistema (JOIN) para armar la tabla principal del admin.
$juegos = $conn->query("SELECT v.*, s.nombre as sistema_nombre FROM videojuegos v JOIN sistemas s ON v.sistema_id = s.id ORDER BY v.id DESC");
// Consulta SQL SELECT: Devuelve todos los usuarios creados en la web.
$usuarios = $conn->query("SELECT * FROM usuarios ORDER BY id ASC");
?>

<div class="container mt-20">
    <h1 class="text-center">Panel de Administración</h1>

    <!-- Tabs Styling -->
    <style>
        .admin-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #334155;
        }

        .tab-btn {
            background: transparent;
            border: none;
            padding: 10px 20px;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .tab-btn.active {
            color: var(--neon-green);
            border-bottom: 2px solid var(--neon-green);
            margin-bottom: -2px;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>

    <div class="admin-tabs">
        <button class="tab-btn active" onclick="openTab('games')">🎮 Videojuegos</button>
        <button class="tab-btn" onclick="openTab('users')">👥 Usuarios</button>
    </div>

    <!-- MESSAGES -->
    <?php if ($game_message || $user_message): ?>
        <div
            style="background: rgba(57, 255, 20, 0.1); border: 1px solid var(--neon-green); padding: 10px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo $game_message . ' ' . $user_message; ?>
        </div>
    <?php endif; ?>

    <!-- GAMES SECTION -->
    <div id="games" class="tab-content active">
        <div class="card mb-20">
            <h3>
                <?php echo $game_edit_id ? '✏️ Editar Juego' : '➕ Añadir Juego'; ?>
            </h3>
            <form method="POST" action="admin.php">
                <input type="hidden" name="action_type" value="game">
                <?php if ($game_edit_id): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $game_edit_id; ?>">
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label>Título</label>
                        <input type="text" name="titulo" value="<?php echo htmlspecialchars($game_titulo); ?>" required>
                    </div>
                    <div>
                        <label>Sistema</label>
                        <select name="sistema_id" required>
                            <option value="">Selecciona sistema...</option>
                            <?php $sistemas->data_seek(0);
                            while ($s = $sistemas->fetch_assoc()): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo $game_sistema_id == $s['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <label>Género</label>
                <input type="text" name="genero" value="<?php echo htmlspecialchars($game_genero); ?>">

                <label>Descripción</label>
                <textarea name="descripcion"
                    style="width:100%; height:80px; background:#0f172a; border:1px solid #334155; color:white; border-radius:8px; padding:10px; margin-bottom:20px;"><?php echo htmlspecialchars($game_desc); ?></textarea>

                <button type="submit" class="btn btn-primary">
                    <?php echo $game_edit_id ? 'Actualizar' : 'Guardar'; ?>
                </button>
                <?php if ($game_edit_id): ?>
                    <a href="admin.php" class="btn btn-danger">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h3>Catálogo Global</h3>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left; border-bottom:1px solid #334155;">
                        <th style="padding:10px;">ID</th>
                        <th>Título</th>
                        <th>Sistema</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $juegos->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #334155;">
                            <td style="padding:10px;">
                                <?php echo $row['id']; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['titulo']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['sistema_nombre']); ?>
                            </td>
                            <td>
                                <a href="admin.php?edit_game=<?php echo $row['id']; ?>" style="margin-right:10px;">✏️</a>
                                <form method="POST" action="admin.php" style="display:inline;"
                                    onsubmit="return confirm('Borrar?');">
                                    <input type="hidden" name="action_type" value="game">
                                    <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" style="background:none; border:none; cursor:pointer;">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- USERS SECTION -->
    <div id="users" class="tab-content">
        <div class="card">
            <h3>Gestión de Usuarios</h3>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left; border-bottom:1px solid #334155;">
                        <th style="padding:10px;">ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = $usuarios->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #334155;">
                            <td style="padding:10px;">
                                <?php echo $u['id']; ?>
                            </td>
                            <td><b>
                                    <?php echo htmlspecialchars($u['nombre']); ?>
                                </b></td>
                            <td>
                                <?php echo htmlspecialchars($u['email']); ?>
                            </td>
                            <td>
                                <form method="POST" action="admin.php" style="display:flex; gap:5px;">
                                    <input type="hidden" name="action_type" value="user">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <select name="new_role" style="padding:2px; width:auto; margin:0;">
                                        <option value="coleccionista" <?php echo $u['rol'] == 'coleccionista' ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?php echo $u['rol'] == 'admin' ? 'selected' : ''; ?>>Admin
                                        </option>
                                    </select>
                                    <button type="submit" name="change_role" class="btn btn-small btn-primary">💾</button>
                                </form>
                            </td>
                            <td>
                                <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                    <form method="POST" action="admin.php" onsubmit="return confirm('Eliminar usuario?');">
                                        <input type="hidden" name="action_type" value="user">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-small btn-danger">🗑️</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function openTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(tabName).classList.add('active');
        event.currentTarget.classList.add('active');
    }
</script>

</body>

</html>