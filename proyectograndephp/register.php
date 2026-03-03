<?php
// === ARCHIVO DE REGISTRO (register.php) ===
// Permite que un visitante pueda crear una cuenta nueva en la base de datos y unirse a la web.

// Incluir configuración
require_once 'config.php';

session_start();

// Verificamos con $_SESSION si ya tiene una cuenta abierta. Si la tiene, es un sinsentido que se registre y lo echamos al catálogo.
if (isset($_SESSION['usuario_id'])) {
    header('Location: catalog.php');
    exit;
}

// Si la petición a la página es tipo POST, significa que el usuario ha enviado el formulario de registro.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errores = [];

    if (empty($nombre) || empty($email) || empty($password)) {
        $errores[] = "Todos los campos son obligatorios";
    }

    if ($password !== $confirm_password) {
        $errores[] = "Las contraseñas no coinciden";
    }

    if (empty($errores)) {
        // Verificar si email existe
        // Consulta SQL SELECT: Buscamos si ya hay alguien registrado con ese correo en la tabla 'usuarios' para evitar duplicados.
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->num_rows > 0) {
            $errores[] = "El email ya está registrado";
        }
        $stmt->close();
    }

    if (empty($errores)) {
        // Insertar usuario
        // Protegemos la contraseña del usuario antes de meterla a la BDD convirtiéndola a un Hash encriptado.
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $rol = 'coleccionista'; // Por defecto los nuevos usuarios son normales (no administradores)

        // Consulta SQL INSERT: Creamos el registro final del usuario insertando su nombre, email, contraseña protegida y rol en la tabla correspondiente.
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $password_hash, $rol);

        if ($stmt->execute()) {
            // Guardamos un texto en la variable $_SESSION['mensaje'] temporalmente.
            // Esto permitirá que error.php lo muestre en la página de login.php con un globo verde de "Registro Exitoso".
            $_SESSION['mensaje'] = "Registro exitoso. ¡Inicia sesión!";
            header('Location: login.php');
            exit;
        } else {
            $errores[] = "Error al registrar usuario: " . $conn->error;
        }
        $stmt->close();
    }

    $_SESSION['errores'] = $errores;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - RetroVault</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <div class="logo">
            <h1><a href="index.html" style="color:white; text-decoration:none;">RetroVault</a></h1>
        </div>
        <nav>
            <a href="login.php">Iniciar Sesión</a>
        </nav>
    </header>

    <div class="container">
        <div class="welcome-container">
            <div class="card" style="max-width: 400px; margin: 0 auto;">
                <h2>Unirse a la Resistencia</h2>

                <?php include 'error.php'; ?>

                <form action="register.php" method="POST">
                    <label for="nombre">Nombre de Usuario:</label>
                    <input type="text" id="nombre" name="nombre" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>

                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>

                    <label for="confirm_password">Confirmar Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>

                    <button type="submit">Registrarse</button>

                    <p style="margin-top: 20px; text-align: center; font-size: 0.9em;">
                        ¿Ya tienes cuenta? <a href="login.php" style="color: var(--secondary-color);">Inicia sesión
                            aquí</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>

</html>