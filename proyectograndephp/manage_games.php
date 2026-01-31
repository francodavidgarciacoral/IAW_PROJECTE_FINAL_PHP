<?php
require_once 'config.php';
require_once 'header.php';

// Verificar permisos de Admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Inicializar variables
$titulo = '';
$sistema_id = '';
$genero = '';
$edit_id = null;

// Procesar Formulario (Crear o Actualizar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si es eliminar
    if (isset($_POST['delete_id'])) {
        $id_to_delete = $_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM videojuegos WHERE id = ?");
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Juego eliminado correctamente.";
        } else {
            $_SESSION['errores'] = ["Error al eliminar: " . $conn->error];
        }
        $stmt->close();
        header('Location: manage_games.php');
        exit;
    }

    // Si es Crear o Actualizar
    $titulo = trim($_POST['titulo']);
    $sistema_id = $_POST['sistema_id'];
    $genero = trim($_POST['genero']);
    $edit_id = $_POST['edit_id'] ?? null;

    if (empty($titulo) || empty($sistema_id)) {
        $_SESSION['errores'] = ["Título y Sistema son obligatorios."];
    } else {
        if ($edit_id) {
            // Actualizar
            $stmt = $conn->prepare("UPDATE videojuegos SET titulo = ?, sistema_id = ?, genero = ? WHERE id = ?");
            $stmt->bind_param("sisi", $titulo, $sistema_id, $genero, $edit_id);
            $action_msg = "actualizado";
        } else {
            // Crear
            $stmt = $conn->prepare("INSERT INTO videojuegos (titulo, sistema_id, genero) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $titulo, $sistema_id, $genero);
            $action_msg = "creado";
        }

        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Juego $action_msg correctamente.";
            header('Location: manage_games.php');
            exit;
        } else {
            $_SESSION['errores'] = ["Error en la base de datos: " . $conn->error];
        }
        $stmt->close();
    }
}

// Cargar datos para editar si se solicita
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM videojuegos WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($game = $result->fetch_assoc()) {
        $titulo = $game['titulo'];
        $sistema_id = $game['sistema_id'];
        $genero = $game['genero'];
    }
    $stmt->close();
}

// Obtener lista de sistemas para el select
$sistemas = $conn->query("SELECT * FROM sistemas ORDER BY nombre ASC");

// Obtener lista de juegos para la tabla
$juegos = $conn->query("
    SELECT v.*, s.nombre as sistema_nombre 
    FROM videojuegos v 
    JOIN sistemas s ON v.sistema_id = s.id 
    ORDER BY v.id DESC
");
?>

<div class="container">
    <h2>Gestión de Videojuegos (Admin)</h2>

    <div class="card">
        <h3>
            <?php echo $edit_id ? 'Editar Juego' : 'Añadir Nuevo Juego'; ?>
        </h3>
        <form action="manage_games.php" method="POST">
            <?php if ($edit_id): ?>
                <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
            <?php endif; ?>

            <label for="titulo">Título:</label>
            <input type="text" name="titulo" id="titulo" value="<?php echo htmlspecialchars($titulo); ?>" required>

            <label for="sistema_id">Sistema:</label>
            <select name="sistema_id" id="sistema_id" required>
                <option value="">Selecciona una consola...</option>
                <?php
                $sistemas->data_seek(0); // Reiniciar puntero
                while ($sys = $sistemas->fetch_assoc()):
                    ?>
                    <option value="<?php echo $sys['id']; ?>" <?php if ($sistema_id == $sys['id'])
                           echo 'selected'; ?>>
                        <?php echo htmlspecialchars($sys['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="genero">Género:</label>
            <input type="text" name="genero" id="genero" value="<?php echo htmlspecialchars($genero); ?>"
                placeholder="Ej: Plataformas">

            <div style="display:flex; gap:10px;">
                <button type="submit">
                    <?php echo $edit_id ? 'Guardar Cambios' : 'Añadir Juego'; ?>
                </button>
                <?php if ($edit_id): ?>
                    <a href="manage_games.php" class="btn btn-danger">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top:20px;">
        <h3>Biblioteca Global</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Sistema</th>
                    <th>Género</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $juegos->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php echo $row['id']; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['titulo']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['sistema_nombre']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['genero']); ?>
                        </td>
                        <td>
                            <a href="manage_games.php?edit=<?php echo $row['id']; ?>" class="btn btn-small">Editar</a>
                            <form action="manage_games.php" method="POST" style="display:inline;"
                                onsubmit="return confirm('¿Seguro que quieres borrar este juego?');">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-small btn-danger" style="margin-top:0;">Borrar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>

</html>