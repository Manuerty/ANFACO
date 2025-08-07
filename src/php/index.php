<?php

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $error_message = "Error [$errno] en $errfile:$errline: $errstr";
    echo $error_message;
});

require_once "clases/controlador.php";
require_once 'clases/estado.php';
session_start();

try {
    if (!isset($_SESSION["Controlador"])) {
        $_SESSION["Controlador"] = new Controlador;
    }

    // ✅ NUEVO: interceptar acción específica para actualizar solo la gráfica
        if (isset($_POST['accion']) && $_POST['accion'] === 'actualizarGrafica') {
        $ids = json_decode($_POST['ids'] ?? '[]');
        $colores = json_decode($_POST['colores'] ?? '[]');
        $idBoton = $_POST['idBoton'] ?? 0;

        // 🔎 Aquí debes obtener los datos necesarios para pasarle al método
        // Por ejemplo, si ya están en el estado
        $temperaturas = $_SESSION["Controlador"]->miEstado->temperaturas ?? [];
        $almacenes = array_filter(
            $_SESSION["Controlador"]->miEstado->almacenes ?? [],
            fn($almacen) => in_array($almacen['IdAlmacen'], $ids)
        );

        // 🔁 Generar datos y devolver
        $datosGrafica = $_SESSION["Controlador"]->generarDatosGrafica($temperaturas, $almacenes);

        echo json_encode([
            'datos' => [
                'graficaTemperatura' => $datosGrafica
            ],
            'color' => $colores
        ]);
        return;
    }
    // Función auxiliar para cargar la página normalmente
    function devuelveContenido() {
        if (!isset($_POST['arrayDatos'])) {
            return $_SESSION["Controlador"]->generarContenido();
        } else {
            return $_SESSION["Controlador"]->generarContenido($_POST['arrayDatos']);
        }
    }

    // Si no es una acción especial, cargar la página normalmente
    $respuestaJSON = json_encode(devuelveContenido());

} catch (Exception $e) {
    $respuestaJSON = json_encode(array("Ha ocurrido un error inesperado", null, 0));
}

echo $respuestaJSON;
?>
