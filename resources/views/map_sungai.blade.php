<!DOCTYPE html>
<html>
<head>
    <title>Peta Sungai & Presensi Cirebon</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    
    <style>
        #map { height: 600px; width: 100%; border-radius: 8px; }
        .popup-container { text-align: center; min-width: 160px; font-family: sans-serif; }
        .popup-container img { width: 100%; border-radius: 5px; margin: 8px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .info-table { width: 100%; font-size: 11px; text-align: left; border-top: 1px solid #eee; margin-top: 5px; }
    </style>
</head>
<body>
    <h1>Peta Jaringan Sungai & Presensi Karyawan</h1>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    
    <script>
    // Inisialisasi Peta
    var map = L.map('map').setView([-6.722, 108.556], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // 1. LOAD DATA SUNGAI (WFS GeoServer)
    var wfsUrl = "http://localhost:8082/geoserver/geocimancis/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=geocimancis:sungai_kota_cirebon&outputFormat=application/json";
    
    fetch(wfsUrl)
        .then(res => res.json())
        .then(data => {
            L.geoJSON(data, {
                style: { color: 'blue', weight: 3, opacity: 0.8 },
                onEachFeature: function(feature, layer) {
                    if (feature.properties && feature.properties.nama_sunga) {
                        layer.bindPopup("Nama Sungai: " + feature.properties.nama_sunga);
                    }
                }
            }).addTo(map);
        });

    // 2. LOAD DATA ABSENSI (PostgreSQL) dengan fitur Anti-Penumpukan
    var markerClusterGroup = L.markerClusterGroup(); // Grup untuk marker yang menumpuk

    function loadAbsensiMarkers() {
        fetch('/get-absensi')
            .then(response => response.json())
            .then(data => {
                data.forEach(absen => {
                    if (absen.lat && absen.lng) {
                        // Konten popup lengkap sesuai permintaan
                        let popupContent = `
                            <div class="popup-container">
                                <h3 style="margin:0;">${absen.nama}</h3>
                                <small style="color:gray;">${absen.waktu_absen}</small><br>
                                <img src="${absen.foto || 'https://via.placeholder.com/150'}">
                                <table class="info-table">
                                    <tr><td><b>📍 Wilayah</b></td><td>: ${absen.wilayah_tugas}</td></tr>
                                    <tr><td><b>🌐 Koordinat</b></td><td>: ${parseFloat(absen.lat).toFixed(5)}, ${parseFloat(absen.lng).toFixed(5)}</td></tr>
                                </table>
                            </div>
                        `;

                        // Buat marker dan tambahkan ke grup cluster
                        let marker = L.marker([absen.lat, absen.lng]).bindPopup(popupContent);
                        markerClusterGroup.addLayer(marker);
                    }
                });
                
                // Tambahkan semua marker ke peta melalui grup cluster
                map.addLayer(markerClusterGroup);
            })
            .catch(err => console.error('Gagal memuat data absen:', err));
    }

    // Jalankan fungsi
    loadAbsensiMarkers();
    </script>
</body>
</html>