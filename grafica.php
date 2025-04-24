<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gráfico Navegable</title>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Adaptador para fechas -->
  <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

  <!-- Chart.js Zoom Plugin -->
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.0.0"></script>

  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      padding: 2rem;
    }

    #chart-container {
      max-width: 900px;
      height: 500px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    button {
      display: block;
      margin: 1rem auto;
      padding: 10px 20px;
      font-size: 16px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    button:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>

  <div id="chart-container">
    <canvas id="myChart"></canvas>
  </div>

  <button onclick="resetZoom()">Resetear Zoom</button>

  <script>
    // Datos de ejemplo
    const data = [
      { x: new Date('2024-01-01'), y: 18.2, almacen: "Almacén: Frío 1" },
      { x: new Date('2024-01-02'), y: 17.5, almacen: "Almacén: Frío 1" },
      { x: new Date('2024-01-03'), y: 19.0, almacen: "Almacén: Frío 2" },
      { x: new Date('2024-01-04'), y: 20.1, almacen: "Almacén: Seco 1" },
      { x: new Date('2024-01-05'), y: 21.3, almacen: "Almacén: Seco 2" },
      { x: new Date('2024-01-06'), y: 22.7, almacen: "Almacén: Seco 2" },
      { x: new Date('2024-01-07'), y: 23.1, almacen: "Almacén: Seco 2" }
    ];

    const ctx = document.getElementById('myChart').getContext('2d');

    const yValues = data.map(d => d.y);
    const yMin = Math.min(...yValues);
    const yMax = Math.max(...yValues);

    const myChart = new Chart(ctx, {
      type: 'line',
      data: {
        datasets: [{
          label: 'Temperatura',
          data: data,
          parsing: {
            xAxisKey: 'x',
            yAxisKey: 'y'
          },
          borderColor: 'rgba(75, 192, 192, 1)',
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          pointRadius: 4,
          pointHoverRadius: 6,
          tension: 0.2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            type: 'time',
            time: {
              unit: 'day',
              tooltipFormat: 'dd/MM/yyyy'
            },
            title: {
              display: true,
              text: 'Fecha'
            }
          },
          y: {
            min: yMin,
            max: yMax,
            title: {
              display: true,
              text: 'Temperatura (°C)'
            }
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              afterLabel: function(context) {
                return context.raw.almacen;
              }
            }
          },
          zoom: {
            pan: {
              enabled: true,
              mode: 'xy', // Permite mover tanto el eje X como el Y
              speed: 10 // Velocidad de movimiento con el mouse
            },
            zoom: {
              enabled: true,
              mode: 'xy', // Permite hacer zoom tanto en el eje X como en el Y
              speed: 0.1, // Velocidad de zoom
              sensitivity: 3, // Sensibilidad del zoom con la rueda del ratón
              wheel: {
                enabled: true, // Habilitar zoom con la rueda del ratón
                speed: 0.1 // Velocidad del zoom con la rueda del ratón
              }
            }
          }
        }
      }
    });

     // Control manual de pan estilo "Google Maps"
     const canvas = document.getElementById('myChart');
    let isDragging = false;
    let startX = 0;
    let startY = 0;

    canvas.addEventListener('mousedown', (e) => {
      isDragging = true;
      startX = e.clientX;
      startY = e.clientY;
    });

    canvas.addEventListener('mousemove', (e) => {
      if (isDragging) {
        const deltaX = e.clientX - startX;
        const deltaY = e.clientY - startY;

        // Ajustar el movimiento en el eje X
        const chartArea = myChart.chartArea;
        const chartWidth = chartArea.right - chartArea.left;
        const dataRangeX = myChart.scales.x.max - myChart.scales.x.min;
        const pixelsPerUnitX = chartWidth / dataRangeX;
        const offsetX = deltaX / pixelsPerUnitX;
        myChart.options.scales.x.min -= offsetX;
        myChart.options.scales.x.max -= offsetX;

        // Ajustar el movimiento en el eje Y
        const chartHeight = chartArea.bottom - chartArea.top;
        const dataRangeY = myChart.scales.y.max - myChart.scales.y.min;
        const pixelsPerUnitY = chartHeight / dataRangeY;
        const offsetY = deltaY / pixelsPerUnitY;
        myChart.options.scales.y.min += offsetY;
        myChart.options.scales.y.max += offsetY;

        myChart.update('none');
        startX = e.clientX;
        startY = e.clientY;
      }
    });

    canvas.addEventListener('mouseup', () => isDragging = false);
    canvas.addEventListener('mouseleave', () => isDragging = false);

    function resetZoom() {
      const allData = myChart.data.datasets[0].data;
      const minDate = allData[0].x;
      const maxDate = allData[allData.length - 1].x;

      myChart.options.scales.x.min = minDate;
      myChart.options.scales.x.max = maxDate;
      myChart.options.scales.y.min = yMin;
      myChart.options.scales.y.max = yMax;
      myChart.update();
    }

    // Inicializar con rangos completos
    resetZoom();
  </script>

</body>
</html>
