{{-- <!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Geofence - Lokasi Saya</title>

  <link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}" />

  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: sans-serif;
    }

    #app {
      display: flex;
      flex-direction: row;
      height: 100%;
    }

    #sidebar {
      width: 300px;
      background-color: rgba(255, 255, 255, 0.95);
      padding: 20px;
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.3);
      z-index: 10;
    }

    #map {
      flex: 1;
      position: relative;
      z-index: 1;
    }

    #toggleSidebar {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 1000;
      background: white;
      border: 1px solid #ccc;
      padding: 8px 12px;
      border-radius: 5px;
      cursor: pointer;
    }

    .coord-label {
      font-weight: bold;
      margin-top: 10px;
    }

    .coord-value {
      font-size: 16px;
    }

    select {
      width: 100%;
      padding: 6px;
      margin-top: 10px;
      font-size: 16px;
    }

    @media (max-width: 768px) {
      #app {
        flex-direction: column;
      }

      #sidebar {
        width: 100%;
        height: auto;
        box-shadow: none;
      }

      #map {
        height: calc(100% - 230px);
      }

      #toggleSidebar {
        top: 5px;
        right: 5px;
      }
    }
  </style>
</head>
<body>

<div id="app">
  <div id="sidebar">
    <h2>Lokasi Anda</h2>
    <div>
      <div class="coord-label">Latitude:</div>
      <div class="coord-value" id="lat">-</div>

      <div class="coord-label">Longitude:</div>
      <div class="coord-value" id="lng">-</div>

      <div class="coord-label">Pilih Icon Marker:</div>
      <select id="iconSelector">
        <option value="default">Default</option>
        <option value="mobil">Mobil</option>
        <option value="motor">Motor</option>
        <option value="orang">Orang</option>
      </select>
    </div>
  </div>

  <div id="map"></div>
  <button id="toggleSidebar">☰</button>
</div>

<script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>

<script>
  const map = L.map('map').setView([-6.205, 106.825], 14);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
  }).addTo(map);

  // Ikon marker
  const iconOptions = {
    default: L.icon({
      iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
      shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
      iconSize: [25, 41],
      iconAnchor: [12, 41],
      popupAnchor: [0, -41]
    }),
    mobil: L.divIcon({ html: '<div style="font-size:24px;">🚗</div>', iconSize: [30, 30], iconAnchor: [15, 15] }),
    motor: L.divIcon({ html: '<div style="font-size:24px;">🏍️</div>', iconSize: [30, 30], iconAnchor: [15, 15] }),
    orang: L.divIcon({ html: '<div style="font-size:24px;">🧍</div>', iconSize: [30, 30], iconAnchor: [15, 15] })
  };

  let userMarker;
  let currentIcon = iconOptions.default;
  let geofenceCircle;
  let lastInside = true;

  const geofenceCenter = [-6.27593, 106.6887121];
  const geofenceRadius = 500;

  // Tambahkan geofence ke map
  geofenceCircle = L.circle(geofenceCenter, {
    radius: geofenceRadius,
    color: 'red',
    fillColor: '#f03',
    fillOpacity: 0.2
  }).addTo(map).bindPopup("Geofence Area");

  // Toggle sidebar
  document.getElementById('toggleSidebar').addEventListener('click', () => {
    const sidebar = document.getElementById('sidebar');
    sidebar.style.display = (sidebar.style.display === 'none') ? 'block' : 'none';
  });

  // Ganti icon
  document.getElementById('iconSelector').addEventListener('change', function (e) {
    currentIcon = iconOptions[e.target.value];

    if (userMarker) {
      const latlng = userMarker.getLatLng();
      map.removeLayer(userMarker);

      const isInside = geofenceCircle.getBounds().contains(latlng);
      const status = isInside
        ? `<span style="color:green;"><strong>DI DALAM geofence</strong></span>`
        : `<span style="color:red;"><strong>DI LUAR geofence</strong></span>`;

      userMarker = L.marker(latlng, { icon: currentIcon })
        .addTo(map)
        .bindPopup(`Lokasi Anda<br>Lat: ${latlng.lat.toFixed(6)}<br>Lng: ${latlng.lng.toFixed(6)}<br>Status: ${status}`)
        .openPopup();
    }
  });

  function checkGeofenceStatus(lat, lng) {
    const userLatLng = L.latLng(lat, lng);
    const isInside = geofenceCircle.getBounds().contains(userLatLng);

    if (lastInside && !isInside) {
      lastInside = false;
      console.log("Keluar dari geofence");
      // Kirim ke backend jika diperlukan
    } else if (!lastInside && isInside) {
      lastInside = true;
      console.log("Masuk ke dalam geofence");
    }
  }

  // Ambil lokasi user saat halaman dimuat
  window.onload = function () {
    if ('geolocation' in navigator) {
      navigator.geolocation.getCurrentPosition(
        function (position) {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;

          document.getElementById('lat').innerText = lat.toFixed(6);
          document.getElementById('lng').innerText = lng.toFixed(6);

          const userLatLng = L.latLng(lat, lng);
          const distance = userLatLng.distanceTo(L.latLng(...geofenceCenter));
          const isInside = distance <= geofenceRadius;

          const status = isInside
            ? `<span style="color:green;"><strong>DI DALAM geofence</strong></span>`
            : `<span style="color:red;"><strong>DI LUAR geofence</strong></span>`;

          const formattedDistance = (distance >= 1000)
            ? (distance / 1000).toFixed(2) + ' km'
            : distance.toFixed(2) + ' meter';

          const popupContent = `
            <strong>Lokasi Anda Sekarang</strong><br>
            Lat: ${lat.toFixed(6)}<br>
            Lng: ${lng.toFixed(6)}<br>
            Jarak ke geofence: ${formattedDistance}<br>
            Status: ${status}
          `;

          if (userMarker) map.removeLayer(userMarker);

          userMarker = L.marker([lat, lng], { icon: currentIcon })
            .addTo(map)
            .bindPopup(popupContent)
            .openPopup();

          map.setView([lat, lng], 15);
          checkGeofenceStatus(lat, lng);
        },
        function (error) {
          alert("Gagal mengambil lokasi: " + error.message);
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0
        }
      );
    } else {
      alert("Browser tidak mendukung geolocation.");
    }
  };
</script>

</body>
</html> --}}

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tracking Perjalanan</title>
  <link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}" />
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: sans-serif;
    }
    #app {
      display: flex;
      flex-direction: row;
      height: 100%;
    }
    #sidebar {
      width: 300px;
      background-color: rgba(255, 255, 255, 0.95);
      padding: 20px;
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.3);
      z-index: 10;
    }
    #map {
      flex: 1;
      position: relative;
      z-index: 1;
    }
    #toggleSidebar {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 1000;
      background: white;
      border: 1px solid #ccc;
      padding: 8px 12px;
      border-radius: 5px;
      cursor: pointer;
    }
    .coord-label {
      font-weight: bold;
      margin-top: 10px;
    }
    .coord-value {
      font-size: 16px;
    }
    @media (max-width: 768px) {
      #app {
        flex-direction: column;
      }
      #sidebar {
        width: 100%;
        height: auto;
        box-shadow: none;
      }
      #map {
        height: calc(100% - 230px);
      }
      #toggleSidebar {
        top: 5px;
        right: 5px;
      }
    }
  </style>
</head>
<body>

<div id="app">
  <div id="sidebar">
    <h2>Tracking</h2>
    <div>
      <div class="coord-label">Latitude:</div>
      <div class="coord-value" id="lat">-</div>
      <div class="coord-label">Longitude:</div>
      <div class="coord-value" id="lng">-</div>
    </div>
  </div>
  <div id="map"></div>
  <button id="toggleSidebar">☰</button>
</div>

<script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
<script>
  const map = L.map('map').setView([-6.205, 106.825], 14);
  const DEVICE_ID = 'device_123'; // Ganti sesuai ID perangkat

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
  }).addTo(map);

  let userMarker;
  let polyline;

  function updateMarker(lat, lng) {
    if (userMarker) map.removeLayer(userMarker);
    userMarker = L.marker([lat, lng]).addTo(map)
      .bindPopup(`Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`).openPopup();
  }

  function sendToBackend(lat, lng) {
    fetch('/api/location-history', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        device_id: DEVICE_ID,
        latitude: lat,
        longitude: lng
      })
    });
  }

  function drawHistory() {
    fetch(`/api/location-history/${DEVICE_ID}`)
      .then(res => res.json())
      .then(points => {
        if (points.length > 0) {
          const latlngs = points.map(p => [parseFloat(p.latitude), parseFloat(p.longitude)]);
          if (polyline) map.removeLayer(polyline);
          polyline = L.polyline(latlngs, { color: 'blue' }).addTo(map);
          map.fitBounds(polyline.getBounds());
        }
      });
  }

  function trackUser() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(position => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        document.getElementById('lat').innerText = lat.toFixed(6);
        document.getElementById('lng').innerText = lng.toFixed(6);

        updateMarker(lat, lng);
        sendToBackend(lat, lng);
        drawHistory();
      });
    }
  }

  setInterval(trackUser, 300000);
  trackUser();

  document.getElementById('toggleSidebar').addEventListener('click', () => {
    const sidebar = document.getElementById('sidebar');
    sidebar.style.display = (sidebar.style.display === 'none') ? 'block' : 'none';
  });
</script>

</body>
</html>
