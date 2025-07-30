<?php

include "credenciales.php";

// Verifica la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$idLector = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consulta
$sql = "SELECT usuarios.IdUsuario as IdUsuario, usuarios.Usuario as Usuario, lectores.IdLector as IdLector
FROM usuarios Join lectores ON lectores.IdPropietario = usuarios.IdUsuario";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Mostrar resultados
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["IdUsuario"] . " - Nombre: " . $row["Usuario"] . " - Lector: " . $row["IdLector"] ."<br>";
    }
} else {
    echo "No se encontraron registros.";
}

$conn->close();

?>
