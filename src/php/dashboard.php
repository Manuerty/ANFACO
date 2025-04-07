<?php
session_start();  // Iniciar sesión

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    // Si no hay sesión activa, redirigir al login (index.html)
    header('Location: index.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="dashboard-container">
    <h2>Bienvenido, <?php echo $_SESSION['usuario']; ?></h2>
    <p>Esta es tu página de inicio después de iniciar sesión.</p>
    
    <!-- Enlace para cerrar sesión -->
    <a href="logout.php">Cerrar sesión</a>
</div>

</body>
</html>
