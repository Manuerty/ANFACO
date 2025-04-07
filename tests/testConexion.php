<?php
// Include the file where the ConexionBD function is defined
include '../src/php/Conexion.php'; 

// Call the ConexionBD function
$conn = ConexionBD("localhost", "prueba_1", "root", "");

// Check if the connection was successful
if ($conn) {
    echo "La conexión a la base de datos fue exitosa.";
} else {
    echo "Hubo un problema al conectar con la base de datos.";
}
?>