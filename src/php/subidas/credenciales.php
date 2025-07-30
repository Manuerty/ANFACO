<?php

// Credenciales de la base de datos
define('DB_HOST', '81.169.200.39'); // Cambiar si es necesario
define('DB_PORT', '23458');
//define('DB_HOST', '172.19.50.103'); // Cambiar si es necesario
define('DB_USER', 'Anfaco');      // Usuario de la base de datos
//define('DB_USER', 'me');      // Usuario de la base de datos
define('DB_PASS', 'Rodrigo-01*');  // Contraseña del usuario
define('DB_NAME', 'anfaco'); // Nombre de la base de datos

// Conexión a la base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
// Verificar la conexión
/*
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} else {
    echo "Conexión exitosa a la base de datos.\n";
}
*/
?>
