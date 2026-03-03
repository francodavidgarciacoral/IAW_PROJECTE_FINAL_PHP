<?php
// === ARCHIVO DE LOGIN (login.php) ===
// Permite a los usuarios acceder a su cuenta comprobando que sus credenciales son correctas.

// Incluir configuración
require_once 'config.php';

// Iniciar sesión
session_start();

// Si ya está logueado, redirigir al listado de juegos
// Verificamos con $_SESSION si la variable 'usuario_id' existe, lo que significa que el usuario ya realizó login.
if (isset($_SESSION['usuario_id'])) {
    header('Location: catalog.php');
    exit;
}


// Procesar el formulario
// Comprobamos si el formulario de inicio de sesión fue enviado usando el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validación básica
    if (empty($email) || empty($password)) {
        $_SESSION['errores'] = ['Por favor complete todos los campos'];
    } else {
        // Buscar usuario por email usando Prepared Statement
        // Consulta SQL SELECT: Busca al usuario en la base de datos usando únicamente su email para obtener su contraseña encriptada (hash)
        $stmt = $conn->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($usuario = $resultado->fetch_assoc()) {
            // Verificar contraseña ingresada contra la contraseña hasheada de la base de datos
            if (password_verify($password, $usuario['password'])) {
                // Login correcto
                // GUARDAMOS datos importantes en variables de $_SESSION. 
                // Esto nos servirá para "recordar" al usuario en todas las demás páginas del sistema hasta que cierre sesión.
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol'];

                // Si por $_SESSION comprobamos que el rol es 'admin', lo llevamos al panel de control, de lo contrario al catálogo general.
                if ($_SESSION['usuario_rol'] === 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: catalog.php');
                }
                exit;
            } else {
                $_SESSION['errores'] = ['Credenciales incorrectas'];
            }
        } else {
            $_SESSION['errores'] = ['Credenciales incorrectas'];
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RetroVault</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <div class="logo">
            <h1><a href="index.html" style="color:white; text-decoration:none;">RetroVault</a></h1>
        </div>
        <nav>
            <a href="index.html">Volver</a>
        </nav>
    </header>

    <div class="container">
        <div class="welcome-container">
            <div class="card" style="max-width: 400px; margin: 0 auto;">
                <h2>Iniciar Sesión</h2>

                <?php include 'error.php'; ?>

                <form action="login.php" method="POST">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required placeholder="ejemplo@retrovault.com">

                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>

                    <button type="submit">Entrar</button>

                    <p style="margin-top: 20px; text-align: center; font-size: 0.9em;">
                        ¿No tienes cuenta? <a href="register.php" style="color: var(--secondary-color);">Regístrate
                            aquí</a>
                    </p>

                    <div style="margin-top: 20px; font-size: 0.8em; border-top: 1px solid #444; padding-top: 10px;">
                        <p><strong>Demo Users:</strong></p>
                        <p>Admin: admin@retrovault.com / 1234</p>
                        <p>User: user1@retrovault.com / 1234</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>