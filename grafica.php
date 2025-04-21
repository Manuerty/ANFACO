<?php
require_once "src\php\consultas.php";

// Obtener los datos de temperatura y almacenes desde las funciones que ya tienes
$tagPez = '00000003A'; // Debes definir el tagPez adecuado

$temperaturas = get_Temperaturas($tagPez);
$almacenes = get_Almacenes($tagPez);

// Crear el dataset combinando las temperaturas con los almacenes usando el Id
$dataset = [];
foreach ($temperaturas as $temp) {
    // Buscar el almacén correspondiente usando el Id
    foreach ($almacenes as $almacen) {
        // Si los Id coinciden, lo combinamos
        if ($temp['IdLector'] == $almacen['IdAlmacen']) {
            $dataset[] = [
                "x" => strtotime($temp["FechaTemperatura"]) * 1000, // Convertimos la fecha a timestamp en milisegundos
                "y" => $temp["ValorTemperatura"], // Valor de la temperatura
                "almacen" => "Almacén: " . $almacen["NombreTipo"] . " " . $almacen["IdTipo"] // Información adicional del almacén
            ];
            break; // Salimos del ciclo de almacenes una vez que encontramos la coincidencia
        }
    }
}

// Pasamos los datos al frontend en formato JSON
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gráfica Multi-día</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1"></script>
    <style>
        #contenedor-grafica {
            width: 600px;
            height: 300px;
        }

        canvas {
            width: 100% !important;
            height: 100% !important;
        }
    </style>
</head>
<body>

<div id="contenedor-grafica">
    <canvas id="graficaTemperatura"></canvas>
</div>

<script>
    // Obtener el dataset combinado de PHP
    const datos = <?php echo json_encode($dataset); ?>;

    const ctx = document.getElementById('graficaTemperatura').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [{
                label: 'Temperatura (°C)',
                data: datos,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.2)',
                tension: 0, // Líneas rectas
                fill: false,
                pointRadius: 3
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const temp = context.raw.y;
                            const fecha = context.raw.x;
                            const almacen = context.raw.almacen;
                            return [
                                'Temperatura: ' + temp + ' °C',
                                almacen // Mostrar la ubicación del almacén
                            ];
                        }
                    }
                }
            },
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'hour', // Cambiar a 'hour' para tener divisiones horarias
                        stepSize: 1, // 6 horas
                        tooltipFormat: "yyyy-MM-dd HH:mm",
                        displayFormats: {
                            hour: 'MMM dd HH:mm'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Fecha y hora'
                    },
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 6, // Mostrar solo 4 marcas por día
                        min: datos.length ? datos[0].x : new Date().getTime() - 86400000, // Asegura que se muestre un día completo
                        max: datos.length ? datos[datos.length - 1].x : new Date().getTime() // Ajuste dinámico para mostrar todo el día
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Temperatura (°C)'
                    }
                }
            }
        }
    });
</script>

</body>
</html>
