<!DOCTYPE html>
<html>
<head>
    <title>Sistem Informasi Geografis - Geocimancis</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; height: 100vh; }
        #sidebar { width: 320px; background: #2c3e50; color: white; padding: 20px; box-shadow: 2px 0 5px rgba(0,0,0,0.2); z-index: 1000; overflow-y: auto; }
        #sidebar h2 { font-size: 1.2rem; border-bottom: 1px solid #555; padding-bottom: 10px; margin-bottom: 20px; color: #ecf0f1; text-align: center; }
        #map { flex-grow: 1; height: 100%; }
        .menu-group { margin-bottom: 20px; }
        .menu-title { font-weight: bold; margin-bottom: 10px; display: block; color: #3498db; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; border-left: 3px solid #3498db; padding-left: 8px; }
        .menu-item { background: #34495e; padding: 10px; border-radius: 6px; margin-bottom: 5px; cursor: pointer; transition: 0.2s; display: flex; align-items: center; }
        .menu-item:hover { background: #3e5871; }
        .menu-item input { margin-right: 10px; cursor: pointer; transform: scale(1.2); }
        .menu-item label { cursor: pointer; flex-grow: 1; font-size: 0.85rem; }
        .popup-table { font-size: 11px; border-collapse: collapse; width: 100%; }
        .popup-table td { border: 1px solid #ddd; padding: 4px; }
        .popup-header { font-weight: bold; background: #3498db; color: white; padding: 5px; border-radius: 4px 4px 0 0; text-align: center; margin-bottom: 5px; }
    </style>
</head>
<body>

    <div id="sidebar">
        <h2>🌐 GIS GEOCIMANCIS</h2>
        <div id="dynamic-menu">
            <p style="font-size: 0.8rem; text-align: center; color: #bdc3c7;">Memuat daftar data...</p>
        </div>
    </div>

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([-6.722, 108.556], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

        var activeLayers = {};

        // --- 1. FUNGSI AMBIL DAFTAR LAYER DARI GEOSERVER ---
        function loadSidebarLayers() {
            const capabilitiesUrl = "http://localhost:8082/geoserver/geocimancis/ows?service=WFS&version=1.1.0&request=GetCapabilities";

            fetch(capabilitiesUrl)
                .then(res => res.text())
                .then(str => new window.DOMParser().parseFromString(str, "text/xml"))
                .then(data => {
                    const layers = data.getElementsByTagName("FeatureType");
                    const menuContainer = document.getElementById('dynamic-menu');
                    menuContainer.innerHTML = ''; // Kosongkan loading text

                    // Objek untuk grouping berdasarkan nama depan (misal: di_cikeusik)
                    let groups = {};

                    Array.from(layers).forEach(layer => {
                        const fullName = layer.getElementsByTagName("Name")[0].textContent.replace('geocimancis:', '');
                        const title = layer.getElementsByTagName("Title")[0].textContent;
                        
                        // Logika grouping: ambil kata pertama dan kedua (contoh: di_cikeusik)
                        const parts = fullName.split('_');
                        const groupKey = parts.length > 1 ? parts[0] + "_" + parts[1] : "Lainnya";
                        
                        if (!groups[groupKey]) groups[groupKey] = [];
                        groups[groupKey].push({ name: fullName, title: title });
                    });

                    // Buat elemen HTML untuk setiap grup
                    for (let group in groups) {
                        let groupDiv = document.createElement('div');
                        groupDiv.className = 'menu-group';
                        groupDiv.innerHTML = `<span class="menu-title">${group.toUpperCase().replace('_', ' ')}</span>`;

                        groups[group].forEach(item => {
                            // Tentukan warna otomatis berdasarkan tipe (baku=hijau, bangunan=merah, lainnya=biru)
                            let color = '#3498db';
                            if (item.name.includes('baku')) color = '#2ecc71';
                            if (item.name.includes('bangunan')) color = '#e74c3c';
                            if (item.name.includes('fungsional')) color = '#f1c40f';

                            let itemDiv = document.createElement('div');
                            itemDiv.className = 'menu-item';
                            itemDiv.innerHTML = `
                                <input type="checkbox" onchange="toggleWFS(this, '${item.name}', '${color}')">
                                <label>${item.title}</label>
                            `;
                            groupDiv.appendChild(itemDiv);
                        });
                        menuContainer.appendChild(groupDiv);
                    }
                })
                .catch(err => {
                    console.error("Gagal load daftar layer:", err);
                    document.getElementById('dynamic-menu').innerHTML = '<p style="color:red">Gagal memuat data. Cek GeoServer.</p>';
                });
        }

        // Jalankan fungsi saat halaman dibuka
        loadSidebarLayers();

     // --- 2. FUNGSI TOGGLE WFS (DIPERBARUI) ---
function toggleWFS(checkbox, layerName, color) {
    if (checkbox.checked) {
        var url = "http://localhost:8082/geoserver/geocimancis/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=geocimancis:" + layerName + "&outputFormat=application/json";
        
        fetch(url).then(res => res.json()).then(data => {
            activeLayers[layerName] = L.geoJSON(data, {
                style: { color: color, fillColor: color, weight: 2, fillOpacity: 0.5 },
                pointToLayer: (feature, latlng) => L.circleMarker(latlng, { radius: 6, fillColor: color, color: "#000", weight: 1, fillOpacity: 0.8 }),
                onEachFeature: (feature, layer) => {
                    let tableRows = "";
                    for (let key in feature.properties) {
                        tableRows += `<tr><td><b>${key}</b></td><td>${feature.properties[key]}</td></tr>`;
                    }
                    layer.bindPopup(`<div class="popup-header">Info ${layerName}</div><table class="popup-table">${tableRows}</table>`);
                }
            }).addTo(map);

            // MODIFIKASI DISINI: Batasi zoom maksimal agar tidak terlalu dekat
            map.fitBounds(activeLayers[layerName].getBounds(), {
                padding: [50, 50], // Memberi ruang di tepi layar
                maxZoom: 12        // Angka ini mengatur kejauhan. Coba 12 atau 13 jika 14 masih terlalu dekat.
            });
        });
    } else {
        if (activeLayers[layerName]) {
            map.removeLayer(activeLayers[layerName]);
            delete activeLayers[layerName];
        }
    }
}
    </script>
</body>
</html>