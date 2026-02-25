<!DOCTYPE html>
<html>
<head>
    <title>Sistem Absensi Real-time - Geocimancis</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background-color: #f0f2f5; }
        .container { display: flex; gap: 20px; }
        #map { height: 600px; flex: 2; border-radius: 12px; border: 2px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .sidebar { flex: 1; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-absen { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .btn-absen:hover { background: #0056b3; }
        #status { font-size: 0.9em; margin-top: 10px; color: #666; }
    </style>
</head>
<body>

    <h2 style="text-align: center;">📍 Monitoring Absensi Karyawan Cirebon</h2>

    <div class="container">
        <div class="sidebar">
            <h3>Input Kehadiran</h3>
            <div class="form-group">
                <label>Nama Karyawan</label>
                <input type="text" id="nama" placeholder="Contoh: Budi Santoso">
            </div>
            <div class="form-group">
                <label>Wilayah Tugas</label>
                <select id="wilayah">
                    <option value="Cirebon Kota">Cirebon Kota</option>
                    <option value="Sumber">Sumber</option>
                    <option value="Palimanan">Palimanan</option>
                </select>
            </div>
            <div class="form-group">
                <label>Waktu & Tanggal</label>
                <input type="text" id="waktu_info" disabled value="{{ date('Y-m-d H:i') }}">
            </div>
            <button class="btn-absen" onclick="ambilLokasi()">KIRIM ABSEN SEKARANG</button>
            <div id="status">Menunggu perintah...</div>
        </div>

        <div id="map"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Inisialisasi Peta Cirebon
        var map = L.map('map').setView([-6.722, 108.556], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // 1. Load Data Sungai dari GeoServer (WFS)
        var urlSungai = "http://localhost:8082/geoserver/geocimancis/ows?service=WFS&version=1.1.0&request=GetFeature&typeName=geocimancis:sungai_kota_cirebon&outputFormat=application/json";
        fetch(urlSungai).then(res => res.json()).then(data => {
            L.geoJSON(data, { style: { color: 'blue', weight: 2 } }).addTo(map);
        });

        // 2. Load Data Absen dari GeoServer agar muncul titik-titik karyawan
        var urlAbsen = "http://localhost:8082/geoserver/geocimancis/ows?service=WFS&version=1.1.0&request=GetFeature&typeName=geocimancis:data_absens&outputFormat=application/json";
        fetch(urlAbsen).then(res => res.json()).then(data => {
            L.geoJSON(data, {
                pointToLayer: (feature, latlng) => L.marker(latlng),
                onEachFeature: (feature, layer) => {
                    layer.bindPopup(`<b>${feature.properties.nama}</b><br>${feature.properties.waktu_absen}<br>Wilayah: ${feature.properties.wilayah_tugas}`);
                }
            }).addTo(map);
        });

        // 3. Logika Ambil Lokasi GPS & Kirim ke Controller
        function ambilLokasi() {
            const nama = document.getElementById('nama').value;
            const wilayah = document.getElementById('wilayah').value;
            const status = document.getElementById('status');

            if (!nama) return alert("Nama tidak boleh kosong!");

            status.innerHTML = "🛰️ Sedang mencari sinyal GPS...";

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    status.innerHTML = "📤 Mengirim data ke server...";

                    // Kirim ke Controller AbsenController@simpan
                    fetch('/simpan-absen', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ nama, wilayah, lat, lng })
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                        location.reload(); // Refresh untuk update titik di peta
                    })
                    .catch(err => {
                        status.innerHTML = "❌ Gagal mengirim data.";
                    });

                }, function(error) {
                    status.innerHTML = "❌ GPS Gagal: " + error.message;
                });
            } else {
                status.innerHTML = "❌ Browser tidak mendukung GPS.";
            }
        }
    </script>
</body>
</html>