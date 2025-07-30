<?php

include "credenciales.php";

const ALLOWED_EXTENSIONS = ['xml'];
$file_type = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

if (!in_array($file_type, ALLOWED_EXTENSIONS)) {
    echo "Tipos permitidos: " . implode(", ", ALLOWED_EXTENSIONS) . "\n";
    exit("Error: Tipo de archivo no permitido.\n");
}

// Leer el contenido directamente desde el archivo temporal
$xmlContent = file_get_contents($_FILES['file']['tmp_name']);
if ($xmlContent === false) {
    exit("Error: No se pudo leer el archivo XML.\n");
}

// Cargar el XML desde el contenido
$xml = simplexml_load_string($xmlContent);
if (!$xml) {
    exit("Error: No se puede cargar el fichero XML.\n");
}

foreach ($xml->captura as $fila) {
    // Extraer y limpiar los datos
    $tag = trim((string) $fila['tag']);
    $idBarco = (int) $fila['idBarco'];
    $especie = trim((string) $fila['idPez']);
    $fechaCapturaRaw = trim((string) $fila['fechaDeCaptura']);
    $zona = trim((string) $fila['zona']);
    $idTipoAlmacen = trim((string) $fila['idBodega']);

    // Procesar la fecha
    $fechaDateTime = DateTime::createFromFormat('d/m/Y H:i:s', $fechaCapturaRaw);
    if ($fechaDateTime && $fechaDateTime->getLastErrors()['warning_count'] == 0 && $fechaDateTime->getLastErrors()['error_count'] == 0) {
        $fechaCapturaMySQL = $fechaDateTime->format('Y-m-d H:i:s');
    } else {
        echo "Error: Formato de fecha no válido -> $fechaCapturaRaw\n";
        continue; // Salta este registro y sigue con el siguiente
    }


    $sql = "INSERT INTO capturas (Zona, Especie, FechaCaptura, TagPez, IdBarco, IdTipoAlmacen)
            VALUES ('" . $conn->real_escape_string($zona) . "',
                    '" . $conn->real_escape_string($especie) . "',
                    '" . $conn->real_escape_string($fechaCapturaMySQL) . "',
                    '" . $conn->real_escape_string($tag) . "',
                    $idBarco,
                    $idTipoAlmacen)";

    if ($conn->query($sql) === TRUE) {
        echo "Nuevo registro creado correctamente\n";
    } else {
        echo "Error: " . $sql . "\n" . $conn->error . "\n";
    }
}


$conn->close();

?>
