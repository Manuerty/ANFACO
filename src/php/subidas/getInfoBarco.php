<?php

include "credenciales.php";

// Verifica la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$idLector = isset($_GET['id']) ? intval($_GET['id']) : 0;

$idPropietario = 0;

// 1. Obtener IdPropietario desde lectores
$sqlPropietario = "SELECT IdPropietario FROM lectores WHERE IdLector = $idLector";
$resultPropietario = $conn->query($sqlPropietario);

if ($resultPropietario && $resultPropietario->num_rows > 0) {
    $rowPropietario = $resultPropietario->fetch_assoc();
    $idPropietario = intval($rowPropietario['IdPropietario']);
} else {
    echo "No se encontraron registros.";
    exit;
}


// Consulta con JOIN
$sql = "
    SELECT
        barcos.IdBarco,
        barcos.Nombre AS NombreBarco,
        tiposalmacen.IdTipoAlmacen,
        tiposalmacen.Nombre AS NombreBodega
    FROM barcos
    LEFT JOIN tiposalmacen ON barcos.IdBarco = tiposalmacen.IdBarco
    WHERE barcos.IdUsuario = $idPropietario
    ORDER BY barcos.IdBarco, tiposalmacen.IdTipoAlmacen
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $barcos = [];

    while ($row = $result->fetch_assoc()) {
        $idBarco = $row["IdBarco"];

        if (!isset($barcos[$idBarco])) {
            $barcos[$idBarco] = [
                "NombreBarco" => $row["NombreBarco"],
                "Bodegas" => []
            ];
        }

        if ($row["IdTipoAlmacen"] !== null) {
            $barcos[$idBarco]["Bodegas"][] = $row["NombreBodega"] . " - " . $row["IdTipoAlmacen"];
        }
    }

    // Mostrar resultados
    foreach ($barcos as $id => $barco) {
        echo "ID Barco: $id - Nombre: " . $barco["NombreBarco"];

        if (!empty($barco["Bodegas"])) {
            echo " - Bodegas: " . implode(" , ", $barco["Bodegas"]);
        } else {
            echo " - Bodegas: Ninguna";
        }

        echo "<br>";
    }

} else {
    echo "No se encontraron registros.";
}


$conn->close();

?>
