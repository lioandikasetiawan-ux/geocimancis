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
        
        .menu-group { margin-bottom: 10px; border-radius: 6px; overflow: hidden; background: #34495e; }
        .menu-title { 
            width: 100%; padding: 12px 15px; background: #3498db; color: white; 
            border: none; text-align: left; cursor: pointer; font-weight: bold; 
            display: flex; justify-content: space-between; align-items: center;
            text-transform: uppercase; font-size: 0.75rem;
        }
        .menu-content { display: none; padding: 10px; }
        .menu-content.active { display: block; }
        
        .sub-group-title { font-size: 0.7rem; color: #bdc3c7; margin: 10px 0 5px 5px; display: block; border-bottom: 1px solid #555; }
        .menu-item { background: #2c3e50; padding: 8px; border-radius: 4px; margin-bottom: 5px; cursor: pointer; display: flex; align-items: center; }
        .menu-item label { font-size: 0.8rem; cursor: pointer; flex-grow: 1; margin-left: 10px; }

        .legend { background: white; padding: 10px; line-height: 18px; color: #333; box-shadow: 0 0 15px rgba(0,0,0,0.2); border-radius: 5px; font-size: 12px; }
        .legend i { width: 18px; height: 18px; float: left; margin-right: 8px; opacity: 0.8; border: 1px solid #999; }
        .legend-item { margin-bottom: 5px; display: flex; align-items: center; }
        
        /* POPUP STYLING */
        .leaflet-popup-content-wrapper { padding: 0; overflow: hidden; border-radius: 8px; }
        .leaflet-popup-content { margin: 0 !important; width: 320px !important; }
        .popup-header { font-weight: bold; background: #3498db; color: white; padding: 10px; text-align: center; font-size: 13px; }
        .popup-scroll { max-height: 300px; overflow-y: auto; padding: 5px 10px 10px 10px; }
        .popup-table { font-size: 11px; border-collapse: collapse; width: 100%; table-layout: fixed; }
        .popup-table td { border: 1px solid #eee; padding: 6px; word-wrap: break-word; vertical-align: top; }
        .popup-table tr:nth-child(even) { background-color: #f9f9f9; }

        /* Custom Marker Style */
        .custom-pin { display: flex; justify-content: center; align-items: center; }
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
        var googleSat = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        });

        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');

        var map = L.map('map', { 
            center: [-6.722, 108.556], 
            zoom: 11, 
            layers: [googleSat],
            attributionControl: false 
        });

        L.control.attribution({ prefix: false }).addAttribution('Google Maps').addTo(map);

        var baseMaps = { "Google Satellite": googleSat, "OpenStreetMap": osm };
        L.control.layers(baseMaps).addTo(map);

        var legend = L.control({ position: 'bottomright' });
        legend.onAdd = function (map) {
            var div = L.DomUtil.create('div', 'legend');
            div.innerHTML = '<strong>LEGENDA</strong><br><div id="legend-content">Pilih layer...</div>';
            return div;
        };
        legend.addTo(map);

        var activeLayers = {};
        var layerColors = {};

        function getRandomColor() {
            var letters = '0123456789ABCDEF';
            var color = '#';
            for (var i = 0; i < 6; i++) { color += letters[Math.floor(Math.random() * 16)]; }
            return color;
        }

        function formatTitle(text) {
            return text.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        function updateLegend() {
            var content = document.getElementById('legend-content');
            content.innerHTML = '';
            var keys = Object.keys(activeLayers);
            if (keys.length === 0) { content.innerHTML = 'Pilih layer...'; return; }
            keys.forEach(key => {
                content.innerHTML += `<div class="legend-item"><i style="background: ${layerColors[key]}"></i> ${formatTitle(key)}</div>`;
            });
        }

        // Fungsi untuk membuat Icon Pin dengan warna dinamis
        function createMarkerIcon(color) {
            const svgIcon = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32">
                    <path fill="${color}" stroke="#fff" stroke-width="1" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>`;
            
            return L.divIcon({
                html: svgIcon,
                className: 'custom-pin',
                iconSize: [32, 32],
                iconAnchor: [16, 32], 
                popupAnchor: [0, -32]
            });
        }

        function loadSidebarLayers() {
            const capabilitiesUrl = "http://localhost:8082/geoserver/geocimancis/ows?service=WFS&version=1.1.0&request=GetCapabilities";
            const mainCategories = ["Infrastruktur", "Daerah Irigasi Pusat", "DAS dan Jaringan Sungai", "Kebencanaan", "Potensi Air Tanah", "Sumber Daya Alam", "Sumur Air Tanah"];

            fetch(capabilitiesUrl)
                .then(res => res.text())
                .then(str => new window.DOMParser().parseFromString(str, "text/xml"))
                .then(data => {
                    const layers = data.getElementsByTagName("FeatureType");
                    const menuContainer = document.getElementById('dynamic-menu');
                    menuContainer.innerHTML = '';

                    let catalog = {};
                    mainCategories.forEach(cat => catalog[cat] = []);

                    Array.from(layers).forEach(layer => {
                        const fullName = layer.getElementsByTagName("Name")[0].textContent.replace('geocimancis:', '');
                        const lowerName = fullName.toLowerCase();
                        let category = "Sumber Daya Alam";

                        if (lowerName.includes('bendung') || lowerName.includes('embung') || lowerName.includes('pengendali') || lowerName.includes('demnas') || lowerName.includes('pengaman') || lowerName.includes('situ')) category = "Infrastruktur";
                        else if (lowerName.startsWith('di_')) category = "Daerah Irigasi Pusat";
                        else if (lowerName.includes('sungai') || lowerName.includes('das')) category = "DAS dan Jaringan Sungai";
                        else if (lowerName.includes('banjir') || lowerName.includes('kekeringan')) category = "Kebencanaan";

                        if (catalog[category]) catalog[category].push({ name: fullName });
                    });

                    for (let catName in catalog) {
                        if (catalog[catName].length === 0) continue;
                        let groupDiv = document.createElement('div');
                        groupDiv.className = 'menu-group';
                        groupDiv.innerHTML = `<button class="menu-title" onclick="this.nextElementSibling.classList.toggle('active')">${catName} <span>▼</span></button><div class="menu-content"><span class="sub-group-title">DATA</span></div>`;
                        const contentDiv = groupDiv.querySelector('.menu-content');

                        catalog[catName].forEach(item => {
                            let itemDiv = document.createElement('div');
                            itemDiv.className = 'menu-item';
                            itemDiv.innerHTML = `<input type="checkbox" onchange="toggleWFS(this, '${item.name}')"><label>${formatTitle(item.name)}</label>`;
                            contentDiv.appendChild(itemDiv);
                        });
                        menuContainer.appendChild(groupDiv);
                    }
                });
        }

        function toggleWFS(checkbox, layerName) {
            if (checkbox.checked) {
                var color = getRandomColor();
                layerColors[layerName] = color;
                var url = "http://localhost:8082/geoserver/geocimancis/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=geocimancis:" + layerName + "&outputFormat=application/json&srsName=EPSG:4326";
                
                fetch(url).then(res => res.json()).then(data => {
                    activeLayers[layerName] = L.geoJSON(data, {
                        style: { color: color, weight: 3, fillOpacity: 0.5 },
                        pointToLayer: (feature, latlng) => {
                            // MENGGUNAKAN SIMBOL MAPS (PIN SVG)
                            return L.marker(latlng, { icon: createMarkerIcon(color) });
                        },
                        onEachFeature: (feature, layer) => {
                            let rows = "";
                            for (let key in feature.properties) { 
                                if (key.toLowerCase().includes('id')) continue;
                                let val = feature.properties[key];
                                if (val === null || val === "null") val = "-";
                                rows += `<tr><td style="width:100px"><b>${formatTitle(key)}</b></td><td>${val}</td></tr>`; 
                            }
                            
                            let popupContent = `
                                <div class="popup-header">${formatTitle(layerName)}</div>
                                <div class="popup-scroll">
                                    <table class="popup-table">${rows}</table>
                                </div>`;
                            layer.bindPopup(popupContent);
                        }
                    }).addTo(map);
                    updateLegend();
                    map.flyToBounds(activeLayers[layerName].getBounds(), { padding: [40, 40] });
                });
            } else {
                if (activeLayers[layerName]) { 
                    map.removeLayer(activeLayers[layerName]); 
                    delete activeLayers[layerName]; 
                    delete layerColors[layerName]; 
                    updateLegend(); 
                }
            }
        }

        loadSidebarLayers();
    </script>
</body>
</html>