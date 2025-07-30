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

foreach ($xml->registro as $fila) {
    $tag = (string) $fila['tag'];
    $idLector = (string) $fila['idLector'];
    $idAlmacen = (int) $fila['idAlmacen'];
    // $fechaActual = (string) $fila['fechaActual'];
    $fechaActualRaw = (string) $fila['fechaActual'];
    $fechaActualRaw = trim($fechaActualRaw); // Eliminamos espacios

    // Convertimos la fecha al formato correcto
    $fechaDateTime = DateTime::createFromFormat('d/m/Y H:i:s', $fechaActualRaw);

    if ($fechaDateTime && $fechaDateTime->getLastErrors()['warning_count'] == 0 && $fechaDateTime->getLastErrors()['error_count'] == 0) {
        $fechaActualMySQL = $fechaDateTime->format('Y-m-d H:i:s');
    } else {
        echo "Error: Formato de fecha no válido -> $fechaActualRaw\n";
        continue; // Salta este registro y sigue con el siguiente
    }

    $data_content = (string) $fila->data;
    $user = (string) $fila['user'];
    // Comprimir y codificar
    $data_compressed = gzcompress($data_content, 9);
    $data_encoded = base64_encode($data_compressed);

    // Insertar en DB
    $sql = "INSERT INTO almacen (
                Id, Fecha, IdLector, TagPez, DatosTemp, IdTipoAlmacen, IdPropietario, TempMin, TempMax, DatosProcesados
            ) VALUES (
                NULL, '$fechaActualMySQL', '$idLector', '$tag', '$data_encoded', $idAlmacen, $user, NULL, NULL, 0
            )";

    if ($conn->query($sql) === TRUE) {
        echo "Nuevo registro creado correctamente\n";
    } else {
        echo "Error: " . $sql . "\n" . $conn->error;
    }
}

$conn->close();

?>
