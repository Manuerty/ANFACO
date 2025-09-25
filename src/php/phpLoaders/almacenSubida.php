<?php

include "credenciales.php";

const ALLOWED_EXTENSIONS = ['xml'];
$file_type = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

if (!in_array($file_type, ALLOWED_EXTENSIONS)) {
    exit("Error: Tipo de archivo no permitido. Solo se permiten: " . implode(", ", ALLOWED_EXTENSIONS) . "\n");
}

// Leer el contenido directamente desde el archivo temporal
$xmlContent = file_get_contents($_FILES['file']['tmp_name']);
if ($xmlContent === false) {
    exit("Error: No se pudo leer el archivo XML.\n");
}

// Guardar el XML completo en la tabla log_registros
$nombreArchivo = $_FILES['file']['name'];
$archivoComprimido = base64_encode(gzcompress($xmlContent, 9));
$fechaRegistro = date('Y-m-d H:i:s');

$sql_log = "INSERT INTO log_registros (nombre_archivo, fecha_registro, archivo_comprimido)
            VALUES ('$nombreArchivo', '$fechaRegistro', '$archivoComprimido')";
$conn->query($sql_log); // No necesitamos validar éxito para continuar con la lógica principal

// Cargar el XML
$xml = simplexml_load_string($xmlContent);
if (!$xml) {
    exit("Error: No se puede cargar el fichero XML.\n");
}

foreach ($xml->registro as $fila) {
    $tag        = (string) $fila['tag'];
    $idLector   = (string) $fila['idLector'];
    $idAlmacen  = (int)    $fila['idAlmacen'];
    $fechaActualRaw = trim((string) $fila['fechaActual']);

    $fechaDateTime = DateTime::createFromFormat('d/m/Y H:i:s', $fechaActualRaw);

    if ($fechaDateTime && $fechaDateTime->getLastErrors()['warning_count'] == 0
        && $fechaDateTime->getLastErrors()['error_count'] == 0) {

        $fechaActualMySQL = $fechaDateTime->format('Y-m-d H:i:s');

    } else {
        continue;
    }

    $data_content   = (string) $fila->data;
    $user           = (string) $fila['user'];
    $data_compressed = gzcompress($data_content, 9);
    $data_encoded   = base64_encode($data_compressed);

    $sql = "INSERT INTO almacen (
                Id, Fecha, IdLector, TagPez, DatosTemp,
                IdTipoAlmacen, IdPropietario, TempMin, TempMax, DatosProcesados
            ) VALUES (
                NULL, '$fechaActualMySQL', '$idLector', '$tag', '$data_encoded',
                $idAlmacen, '$user', NULL, NULL, 0
            )";

    $conn->query($sql);
}

$conn->close();

?>
