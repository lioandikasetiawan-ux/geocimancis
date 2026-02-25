<!DOCTYPE html>
<html>
<head>
    <title>Peta Sungai Cirebon</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* Mengatur ukuran wadah peta, jika tidak ada height peta tidak akan muncul */
        #map { height: 600px; width: 100%; }
    </style>
</head>
<body>
    <h1>Peta Jaringan Sungai Kota Cirebon</h1>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    var map = L.map('map').setView([-6.722, 108.556], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);
    // URL WFS GeoServer untuk mengambil data dalam format GeoJSON
    var wfsUrl = "http://localhost:8082/geoserver/geocimancis/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=geocimancis:sungai_kota_cirebon&outputFormat=application/json";
    // Mengambil data menggunakan Fetch API
    fetch(wfsUrl)
        .then(function(response) {
            return response.json(); // Mengubah response menjadi format JSON
        })
        .then(function(data) {
            // Memasukkan data vektor ke peta
            L.geoJSON(data, {
                style: function(feature) {
                    return {
                        color: 'blue',      // Warna garis sungai
                        weight: 3,          // Ketebalan garis
                        opacity: 0.8        // Transparansi
                    };
                },
                onEachFeature: function(feature, layer) {
                    // Menampilkan popup saat sungai diklik (asumsi kolom namanya 'nama')
                    if (feature.properties && feature.properties.nama_sunga) {
                        layer.bindPopup("Nama Sungai: " + feature.properties.nama_sunga);
                    }
                }
            }).addTo(map);
        });
</script>
</body>
</html>