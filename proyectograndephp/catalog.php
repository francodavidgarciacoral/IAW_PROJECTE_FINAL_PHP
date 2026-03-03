<?php
// === ARCHIVO DE CATÁLOGO (catalog.php) ===
// Este archivo sirve para mostrar la lista de todos los videojuegos disponibles en la base de datos.
// También permite buscar juegos por nombre y filtrarlos por plataforma.

require_once 'config.php';
require_once 'header.php';

// Check auth
// Comprobamos si la variable 'usuario_id' existe en la sesión para saber si el usuario hizo login.
// Si no existe, lo redirigimos a la página de login porque esta zona es privada.
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Filter & Search Logic
$search = $_GET['q'] ?? '';
$sistema_filter = $_GET['sistema'] ?? '';

// Consulta SQL SELECT: Obtiene todos los sistemas disponibles para mostrarlos en el menú desplegable de filtros.
$sistemas_result = $conn->query("SELECT * FROM sistemas ORDER BY nombre ASC");

// Build Game Query
// Consulta SQL SELECT: Obtiene los datos de los videojuegos y une (JOIN) la tabla sistemas 
// para obtener el nombre de la consola en lugar de solo su ID numérico.
$query = "
    SELECT v.*, s.nombre as sistema_nombre 
    FROM videojuegos v 
    JOIN sistemas s ON v.sistema_id = s.id 
";

$conditions = [];

// Si el usuario escribió algo en la barra de búsqueda...
if (!empty($search)) {
    $s_term = $conn->real_escape_string($search);
    $conditions[] = "(v.titulo LIKE '%$s_term%' OR s.nombre LIKE '%$s_term%')";
}

// Si el usuario seleccionó un filtro de consola...
if (!empty($sistema_filter)) {
    $conditions[] = "v.sistema_id = " . (int) $sistema_filter;
}

// Si hay condiciones de búsqueda o filtro, las añadimos a la consulta con la cláusula WHERE
if (count($conditions) > 0) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

// Ordenamos alfabéticamente y ejecutamos la consulta
$query .= " ORDER BY v.titulo ASC";
$result = $conn->query($query);
?>

<div class="search-bar">
    <form action="catalog.php" method="GET"
        style="display:flex; width:100%; gap:10px; align-items: center; flex-wrap: wrap;">
        <input type="text" name="q" placeholder="Buscar juegos..." value="<?php echo htmlspecialchars($search); ?>"
            style="flex-grow: 1;">

        <select name="sistema" style="width: auto; min-width: 150px;">
            <option value="">Todas las Consolas</option>
            <?php while ($sys = $sistemas_result->fetch_assoc()): ?>
                <option value="<?php echo $sys['id']; ?>" <?php echo $sistema_filter == $sys['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($sys['nombre']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
    </form>
</div>

<h2>🕹️ Catálogo Completo</h2>

<!-- Updated Grid with better ID for styling reference if needed -->
<div class="grid-container" id="game-catalog">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="card" style="cursor: pointer; position: relative;"
            onclick="window.location='details.php?id=<?php echo $row['id']; ?>';">
            <div class="card-image-container"
                style="display: flex; align-items: center; justify-content: center; background-color: #1e293b; color: var(--neon-blue); font-size: 1.5rem; font-weight: bold; text-align: center; height: 150px;">
                [<?php echo htmlspecialchars($row['sistema_nombre']); ?>]
            </div>

            <div class="game-info">
                <div class="game-title">
                    <?php echo htmlspecialchars($row['titulo']); ?>
                </div>

                <span class="badge badge-console">
                    <?php echo htmlspecialchars($row['sistema_nombre']); ?>
                </span>
                <span class="badge badge-genre">
                    <?php echo htmlspecialchars($row['genero']); ?>
                </span>
            </div>

            <!-- Hidden overlay link for accessibility/SEO logic if needed, but onclick handles main interaction -->
            <a href="details.php?id=<?php echo $row['id']; ?>"
                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;"></a>
        </div>
    <?php endwhile; ?>
</div>

</body>

</html>