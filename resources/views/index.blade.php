<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Geofence - Lokasi Saya</title>

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: sans-serif;
    }

    #app {
      position: relative;
      height: 100%;
    }

    #map {
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
    }

    #sidebar {
      position: absolute;
      top: 0;
      left: 0;
      width: 300px;
      height: 100%;
      background-color: rgba(255, 255, 255, 0.95);
      z-index: 10;
      padding: 20px;
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.3);
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
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

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
    mobil: L.divIcon({ className: '', html: '<div style="font-size:24px;">🚗</div>', iconSize: [30, 30], iconAnchor: [15, 15] }),
    motor: L.divIcon({ className: '', html: '<div style="font-size:24px;">🏍️</div>', iconSize: [30, 30], iconAnchor: [15, 15] }),
    orang: L.divIcon({ className: '', html: '<div style="font-size:24px;">🧍</div>', iconSize: [30, 30], iconAnchor: [15, 15] })
  };

  let userMarker;
  let currentIcon = iconOptions.default;
  let geofenceCircle;

  // Konfigurasi geofence
  const geofenceCenter = [-6.27593, 106.6887121];
  const geofenceRadius = 500; // meter

  // Tambahkan lingkaran geofence ke peta
  geofenceCircle = L.circle(geofenceCenter, {
    radius: geofenceRadius,
    color: 'red',
    fillColor: '#f03',
    fillOpacity: 0.2
  }).addTo(map).bindPopup("Geofence Area");

  // Ambil lokasi user
  window.onload = function () {
    if ('geolocation' in navigator) {

      let lastInside = true;

      function checkGeofenceStatus(lat, lng) {
        const userLatLng = L.latLng(lat, lng);
        const isInside = geofenceCircle.getBounds().contains(userLatLng);

        // Jika status berubah dari dalam ke luar
        if (lastInside && !isInside) {
          lastInside = false;
          console.log(lat);
          console.log(lng);
          
          // Kirim ke backend Laravel
          fetch('/api/geofence-exit', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              latitude: lat,
              longitude: lng,
              timestamp: new Date().toISOString()
            })
          });
        }

        if (isInside) lastInside = true;
      }
      
      navigator.geolocation.getCurrentPosition(
        function (position) {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;

          document.getElementById('lat').innerText = lat.toFixed(6);
          document.getElementById('lng').innerText = lng.toFixed(6);

          const userLatLng = L.latLng(lat, lng);
          const geofenceLatLng = L.latLng(geofenceCenter[0], geofenceCenter[1]);

          const distance = userLatLng.distanceTo(geofenceLatLng); // meter
          const isInside = distance <= geofenceRadius;

          // Format satuan jarak
          let formattedDistance;
          if (distance >= 1000) {
            formattedDistance = (distance / 1000).toFixed(2) + ' km';
          } else {
            formattedDistance = distance.toFixed(2) + ' meter';
          }

          const status = isInside
            ? `<span style="color:green;"><strong>DI DALAM geofence</strong></span>`
            : `<span style="color:red;"><strong>DI LUAR geofence</strong></span>`;

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

  // Ganti icon
  document.getElementById('iconSelector').addEventListener('change', function (e) {
    const selected = e.target.value;
    currentIcon = iconOptions[selected];

    if (userMarker) {
      const latlng = userMarker.getLatLng();
      map.removeLayer(userMarker);

      const isInside = geofenceCircle.getBounds().contains(latlng);
      const status = isInside
        ? `<br><span style="color:green;"><strong>Anda berada DI DALAM geofence</strong></span>`
        : `<br><span style="color:red;"><strong>Anda berada DI LUAR geofence</strong></span>`;

      userMarker = L.marker(latlng, { icon: currentIcon })
        .addTo(map)
        .bindPopup(`Lokasi Anda Sekarang<br>Lat: ${latlng.lat.toFixed(6)}<br>Lng: ${latlng.lng.toFixed(6)}${status}`)
        .openPopup();
    }
  });
</script>

</body>
</html>
