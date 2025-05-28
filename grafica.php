<?php
// grafica_tramos_coloridos_segmentos.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gráfica Temperaturas - Tramos Coloridos Segmentados</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
      background: #f0f0f0;
      text-align: center;
    }
    #botones {
      margin-bottom: 20px;
    }
    #botones button {
      margin: 0 6px;
      padding: 8px 14px;
      border: none;
      cursor: pointer;
      font-weight: bold;
      color: white;
      border-radius: 5px;
      transition: opacity 0.3s ease;
    }
    #botones button:focus {
      outline: none;
      box-shadow: 0 0 5px #444;
    }
  </style>
</head>
<body>

  <h1>Gráfica de Temperaturas con Tramos Coloridos y Enfoque</h1>

  <div id="botones">
    <button style="background:#FF6384" data-tramo="0">Tramo 1</button>
    <button style="background:#36A2EB" data-tramo="1">Tramo 2</button>
    <button style="background:#FFCE56" data-tramo="2">Tramo 3</button>
    <button style="background:#4BC0C0" data-tramo="3">Tramo 4</button>
    <button style="background:#9966FF" data-tramo="4">Tramo 5</button>
  </div>

  <canvas id="graficaTemperaturas" width="700" height="400"></canvas>

  <script>
    const ctx = document.getElementById('graficaTemperaturas').getContext('2d');

    const labels = [];
    for(let i=1; i<=35; i++) {
      labels.push('Día ' + i);
    }

    const temperaturas = [
      15,17,16,18,19,20,21,
      22,23,21,20,22,24,25,
      26,27,28,29,27,26,25,
      24,23,22,21,20,19,18,
      17,16,15,14,13,12,11
    ];

    const coloresTramos = [
      '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
    ];

    const grisClaro = '#cccccc';

    let tramoActivo = null;

    function tramoDeIndice(idx) {
      return Math.floor(idx / 7);
    }

    const chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Temperatura',
          data: temperaturas,
          fill: false,
          borderWidth: 4,
          tension: 0.3,
          pointRadius: 5,
          pointHoverRadius: 7,
          pointBorderColor: 'transparent',  // <--- sin borde negro
          pointBackgroundColor: function(context) {
            const idx = context.dataIndex ?? 0;
            const tramo = tramoDeIndice(idx);
            if(tramoActivo === null){
              return coloresTramos[tramo];
            } else {
              return tramo === tramoActivo ? coloresTramos[tramo] : grisClaro;
            }
          },
          segment: {
            borderColor: ctx => {
              const index = ctx.p0DataIndex;
              const tramo = tramoDeIndice(index);
              if(tramoActivo === null) {
                return coloresTramos[tramo];
              } else {
                return tramo === tramoActivo ? coloresTramos[tramo] : grisClaro;
              }
            }
          }
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Temperatura (°C)' }
          },
          x: {
            title: { display: true, text: 'Días' }
          }
        }
      }
    });

    document.querySelectorAll('#botones button').forEach(btn => {
      btn.addEventListener('click', () => {
        const nuevoTramo = Number(btn.dataset.tramo);
        tramoActivo = (tramoActivo === nuevoTramo) ? null : nuevoTramo;
        chart.update();
      });
    });

  </script>

</body>
</html>
