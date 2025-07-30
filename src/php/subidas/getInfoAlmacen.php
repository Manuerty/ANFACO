<?php

include "credenciales.php";

// Verifica la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$idLector = isset($_GET['id']) ? intval($_GET['id']) : 0;


// Consulta
$sql = "SELECT IdTipoAlmacen, Nombre
FROM tiposalmacen
LEFT JOIN lectores ON tiposalmacen.IdUsuario = lectores.IdPropietario
WHERE lectores.IdLector = $idLector";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Mostrar resultados
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["IdTipoAlmacen"] . " - Nombre: " . $row["Nombre"] . "<br>";
    }
} else {
    echo "No se encontraron registros.";
}

$conn->close();

?>
