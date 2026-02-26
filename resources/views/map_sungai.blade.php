<!DOCTYPE html>
<html>
<head>
    <title>Sistem Informasi Geografis - Geocimancis</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; height: 100vh; }
        
        /* Sidebar Styling */
        #sidebar { width: 300px; background: #2c3e50; color: white; padding: 20px; box-shadow: 2px 0 5px rgba(0,0,0,0.2); z-index: 1000; overflow-y: auto; }
        #sidebar h2 { font-size: 1.2rem; border-bottom: 1px solid #555; padding-bottom: 10px; margin-bottom: 20px; color: #ecf0f1; }
        
        /* Map Styling */
        #map { flex-grow: 1; height: 100%; }

        /* Menu Item Styling */
        .menu-group { margin-bottom: 25px; }
        .menu-title { font-weight: bold; margin-bottom: 10px; display: block; color: #3498db; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
        .menu-item { background: #34495e; padding: 12px; border-radius: 6px; margin-bottom: 8px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; }
        .menu-item:hover { background: #3e5871; border-left: 4px solid #3498db; }
        .menu-item input { margin-right: 12px; cursor: pointer; }
        .menu-item label { cursor: pointer; flex-grow: 1; font-size: 0.9rem; }

        /* Popup Styling */
        .leaflet-popup-content-wrapper { border-radius: 8px; }
        .popup-header { font-weight: bold; color: #2c3e50; border-bottom: 1px solid #ddd; margin-bottom: 5px; padding-bottom: 3px; }
    </style>
</head>
<body>

    <div id="sidebar">
        <h2>🌐 GIS GEOCIMANCIS</h2>
        
        <div class="menu-group">
            <span class="menu-title">Data Hidrologi</span>
            <div class="menu-item">
                <input type="checkbox" id="layerSungai" onchange="toggleLayer(this, 'sungai')">
                <label for="layerSungai">Jaringan Sungai Kota</label>
            </div>
            <div class="menu-item">
                <input type="checkbox" id="layerLain" disabled>
                <label for="layerLain">Batas Administrasi (Segera)</label>
            </div>
        </div>

        <div class="menu-group" style="margin-top: 50px;">
            <span class="menu-title">Legenda</span>
            <div style="font-size: 0.8rem; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 5px;">
                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                    <div style="width: 20px; height: 3px; background: blue; margin-right: 10px;"></div>
                    <span>Aliran Sungai</span>
                </div>
            </div>
        </div>
    </div>

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // --- 1. INISIALISASI PETA ---
        var map = L.map('map').setView([-6.722, 108.556], 13);
        
        // Basemap (OSM)
        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Objek untuk menampung layer spasial
        var layers = {
            sungai: null
        };

        // --- 2. FUNGSI LOAD DATA SPASIAL (WFS) ---
        function loadSungai() {
            var wfsUrl = "http://localhost:8082/geoserver/geocimancis/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=geocimancis:sungai_kota_cirebon&outputFormat=application/json";
            
            return fetch(wfsUrl)
                .then(res => res.json())
                .then(data => {
                    layers.sungai = L.geoJSON(data, {
                        style: {
                            color: 'blue',
                            weight: 3,
                            opacity: 0.8
                        },
                        onEachFeature: function(feature, layer) {
                            if (feature.properties && feature.properties.nama_sunga) {
                                let content = `
                                    <div class="popup-header">Detail Sungai</div>
                                    <b>Nama:</b> ${feature.properties.nama_sunga}<br>
                                    <b>Wilayah:</b> Kota Cirebon
                                `;
                                layer.bindPopup(content);
                            }
                        }
                    });
                    return layers.sungai;
                })
                .catch(err => {
                    console.error("Gagal mengambil data GeoServer:", err);
                    alert("Koneksi ke GeoServer gagal. Pastikan GeoServer aktif.");
                });
        }

        // --- 3. KONTROL LAYER DARI SIDEBAR ---
        function toggleLayer(checkbox, layerKey) {
            if (checkbox.checked) {
                // Jika data belum pernah di-load, load dulu
                if (!layers[layerKey]) {
                    loadSungai().then(layer => {
                        if (layer) {
                            layer.addTo(map);
                            map.fitBounds(layer.getBounds());
                        }
                    });
                } else {
                    layers[layerKey].addTo(map);
                }
            } else {
                // Jika checkbox tidak dicentang, hapus dari peta
                if (layers[layerKey]) {
                    map.removeLayer(layers[layerKey]);
                }
            }
        }
    </script>
</body>
</html>