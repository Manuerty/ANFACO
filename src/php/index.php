<?php
// Asegúrate de incluir el archivo de consultas donde tienes la función 'comprueba_usuario'
include 'consultas.php'; 

// Verificar si se recibieron los datos del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos del formulario
    $usuario = $_POST['username'];  // Nombre de usuario
    $contrasena = $_POST['password'];  // Contraseña

    // Llamar a la función para comprobar si el usuario existe
    $result = comprueba_usuario($usuario, $contrasena);

    // Verificar el resultado de la función
    if ($result === 0) {
        // Si el usuario o la contraseña son incorrectos, redirigir con un mensaje de error
        header('Location: ../../index.html?error=1');
        exit();
    } else {
        // Si el usuario es válido, redirigir a un área protegida (ej. dashboard)
        session_start();
        $_SESSION['usuario'] = $result[1];  // Almacenar el nombre de usuario en la sesión
        header('Location: ../php/dashboard.php');  // Redirigir a una página protegida
        exit();
    }
} else {
    // Si no es una solicitud POST, redirigir al formulario de login
    header('Location: ../../index.html');
    exit();
}
?>