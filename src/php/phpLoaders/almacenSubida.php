<?php

include "credenciales.php";

date_default_timezone_set('Europe/Madrid');

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

file_put_contents(__DIR__ . "/a.txt", $xmlContent);


// Cargar el XML desde el contenido
$xml = simplexml_load_string($xmlContent);
if (!$xml) {
    exit("Error: No se puede cargar el fichero XML.\n");
}

// Guardar el XML entero recibido (sobrescribe cada vez)
file_put_contents(__DIR__ . "/a.txt", "=== XML recibido ===\n" . $xmlContent . "\n\n");

// Cargar el XML desde el contenido
$xml = simplexml_load_string($xmlContent);
if (!$xml) {
    exit("Error: No se puede cargar el fichero XML.\n");
}

foreach ($xml->registro as $fila) {
    $tag = (string) $fila['tag'];
    $idLector = (string) $fila['idLector'];
    $idAlmacen = (int) $fila['idAlmacen'];
    $fechaActualRaw = trim((string) $fila['fechaActual']);

    $fechaDateTime = DateTime::createFromFormat('d/m/Y H:i:s', $fechaActualRaw, new DateTimeZone('Europe/Madrid'));

    if ($fechaDateTime && $fechaDateTime->getLastErrors()['warning_count'] == 0 && $fechaDateTime->getLastErrors()['error_count'] == 0) {
        $fechaActualMySQL = $fechaDateTime->format('Y-m-d H:i:s');
    } else {
        file_put_contents(__DIR__ . "/a.txt", "Error en fecha -> $fechaActualRaw\n", FILE_APPEND);
        continue;
    }

    // 🔎 Log de depuración
    file_put_contents(__DIR__ . "/a.txt", 
        "Raw: $fechaActualRaw | Parsed: " . $fechaDateTime->format('Y-m-d H:i:s e') . " | MySQL: $fechaActualMySQL\n", 
        FILE_APPEND
    );

    $data_content = (string) $fila->data;
    $user = (string) $fila['user'];
    $data_compressed = gzcompress($data_content, 9);
    $data_encoded = base64_encode($data_compressed);

    $sql = "INSERT INTO almacen (
                Id, Fecha, IdLector, TagPez, DatosTemp, IdTipoAlmacen, IdPropietario, TempMin, TempMax, DatosProcesados
            ) VALUES (
                NULL, '$fechaActualMySQL', '$idLector', '$tag', '$data_encoded', $idAlmacen, '$user', NULL, NULL, 0
            )";

    if ($conn->query($sql) === TRUE) {
        file_put_contents(__DIR__ . "/a.txt", "Insert OK con Fecha: $fechaActualMySQL\n", FILE_APPEND);
    } else {
        file_put_contents(__DIR__ . "/a.txt", "Error SQL: " . $conn->error . "\n", FILE_APPEND);
    }
}

$conn->close();

?>
