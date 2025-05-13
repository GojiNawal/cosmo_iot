<!-- box.php -->
<!DOCTYPE html>
<html>
<head>
  <title>Sensor Data Viewer</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px auto;
      max-width: 960px;
      padding: 20px;
    }
    #map {
      height: 400px;
      width: 100%;
      margin-bottom: 20px;
    }
    #countdown {
      font-weight: bold;
      color: green;
      margin-bottom: 10px;
    }
    .card {
      border: 1px solid #ccc;
      border-radius: 12px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body>
  <?php include 'menu.php'; ?>

  <h1>Sensor Data Viewer</h1>

  <?php
    $country = $_GET['country'] ?? 'default';
    switch (strtolower($country)) {
      case 'london':
        $boxId = '5bd00cd1bb15b70019ceccf2';
        break;
      case 'france':
        $boxId = '5ee7c10ddc1438001bbeaeec';
        break;
      case 'spain':
        $boxId = '626030a418aca4001ca27240'; // your original box
        break;
      default:
        $boxId = '626030a418aca4001ca27240'; // fallback default
    }
  ?>

  <p id="countdown">Refreshing in 15 seconds...</p>
  <div id="map"></div>
  <div id="coordinates"></div>
  <div id="sensorCards"></div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    const boxId = "<?php echo $boxId; ?>";
    let map, marker, countdown = 15;
    const countdownEl = document.getElementById("countdown");

    function startCountdown() {
      countdown = 15;
      const interval = setInterval(() => {
        countdown--;
        countdownEl.textContent = `Refreshing in ${countdown} second${countdown !== 1 ? 's' : ''}...`;
        if (countdown === 0) {
          clearInterval(interval);
          fetchData();
          startCountdown();
        }
      }, 1000);
    }

    async function fetchData() {
      try {
        const res = await fetch(`https://api.opensensemap.org/boxes/${boxId}`);
        const data = await res.json();

        const coords = data.loc[0].geometry.coordinates;
        const lon = coords[0], lat = coords[1];

        document.getElementById("coordinates").innerHTML = `
          <h2>Box: ${data.name}</h2>
          <p><strong>Coordinates:</strong> Latitude: ${lat}, Longitude: ${lon}</p>
        `;

        if (!map) {
          map = L.map('map').setView([lat, lon], 15);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
          }).addTo(map);
          marker = L.marker([lat, lon]).addTo(map).bindPopup("Sensor Box").openPopup();
        } else {
          map.setView([lat, lon]);
          marker.setLatLng([lat, lon]);
        }

        const container = document.getElementById("sensorCards");
        container.innerHTML = "";
        for (const sensor of data.sensors) {
          const card = document.createElement("div");
          card.className = "card";
          const measurement = sensor.lastMeasurement;

          card.innerHTML = `
            <strong>${sensor.title}</strong><br>
            Unit: ${sensor.unit}<br>
            ${measurement ? `
              Value: ${measurement.value}<br>
              Time: ${measurement.createdAt}<br>
            ` : "No recent measurement"}
            <canvas id="chart-${sensor._id}" height="100"></canvas>
          `;
          container.appendChild(card);

          if (measurement) {
            fetchChartData(sensor._id, `chart-${sensor._id}`, sensor.unit);
          }
        }
      } catch (err) {
        console.error("Error fetching data:", err);
        document.getElementById("sensorCards").innerHTML = `<p style="color:red;">Failed to fetch sensor data.</p>`;
      }
    }

    async function fetchChartData(sensorId, canvasId, unit) {
      try {
        const res = await fetch(`https://api.opensensemap.org/boxes/${boxId}/data/${sensorId}?format=json&window=day`);
        const json = await res.json();

        const labels = json.map(m => new Date(m.createdAt).toLocaleTimeString());
        const values = json.map(m => parseFloat(m.value));

        const ctx = document.getElementById(canvasId).getContext('2d');
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: labels,
            datasets: [{
              label: `Value (${unit})`,
              data: values,
              borderColor: 'rgba(75, 192, 192, 1)',
              fill: false,
              tension: 0.3
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: true
              }
            },
            scales: {
              y: {
                beginAtZero: false
              }
            }
          }
        });
      } catch (err) {
        console.error(`Error loading chart for sensor ${sensorId}:`, err);
      }
    }

    fetchData();
    startCountdown();
  </script>
</body>
</html>
