<!DOCTYPE html>
<html>
<head>
    <title>Geocimancis</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pupr.png') }}">
    <style>
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; height: 100vh; overflow: hidden; }
        #sidebar { width: 320px; background: #2c3e50; color: white; padding: 20px; box-shadow: 2px 0 5px rgba(0,0,0,0.2); z-index: 1000; overflow-y: auto; transition: all 0.3s ease; flex-shrink: 0; position: relative; }
        #sidebar.hidden { margin-left: -360px; box-shadow: none; }
        #toggle-sidebar { position: fixed; left: 320px; top: 20px; z-index: 1100; background: #2c3e50; color: #d1d1d1; border: none; padding: 10px 12px; border-radius: 0 5px 5px 0; cursor: pointer; font-size: 22px; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        #toggle-sidebar:hover { color: #ffffff; }
        #sidebar.hidden + #toggle-sidebar { left: 0px; }
        #sidebar.hidden ~ #map .leaflet-left .leaflet-control-zoom { margin-left: 50px; transition: margin-left 0.3s ease; }
        .leaflet-left .leaflet-control-zoom { transition: margin-left 0.3s ease; }
        #sidebar h2 { font-size: 1.2rem; border-bottom: 1px solid #555; padding-bottom: 10px; margin-bottom: 20px; color: #ecf0f1; text-align: center; }
        #map { flex-grow: 1; height: 100%; position: relative; }
        .menu-group { margin-bottom: 10px; border-radius: 6px; overflow: hidden; background: #34495e; }
        .menu-title { width: 100%; padding: 12px 15px; background: #3498db; color: white; border: none; text-align: left; cursor: pointer; font-weight: bold; display: flex; justify-content: space-between; align-items: center; text-transform: uppercase; font-size: 0.75rem; }
        .menu-content { display: none; padding: 10px; }
        .menu-content.active { display: block; }
        .sub-bab-container { margin-top: 5px; border-left: 2px solid #3498db; margin-left: 5px; margin-bottom: 5px; }
        .sub-bab-title { width: 100%; background: #2c3e50; color: #ecf0f1; border: none; text-align: left; padding: 8px 10px; font-size: 0.75rem; font-weight: bold; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #3e4f5f; }
        .sub-bab-content { display: none; padding: 5px 0 5px 10px; }
        .sub-bab-content.active { display: block; }
        .menu-item { background: #2c3e50; border-radius: 4px; margin-bottom: 5px; cursor: pointer; display: flex; align-items: center; transition: background 0.2s; }
        .menu-item:hover { background: #3e4f5f; }
        .menu-item label { font-size: 0.8rem; cursor: pointer; flex-grow: 1; padding: 8px; display: flex; align-items: center; width: 100%; margin: 0; }
        .menu-item input { margin-right: 10px; cursor: pointer; }
        .layer-icon { width: 20px; height: 20px; margin-right: 10px; object-fit: contain; flex-shrink: 0; }
        .arrow { font-size: 0.6rem; transition: 0.3s; display: inline-block; }
        
        .legend { background: white; padding: 12px; line-height: 18px; color: #333; box-shadow: 0 0 15px rgba(0,0,0,0.2); border-radius: 8px; font-size: 11px; min-width: 200px; max-height: 450px; overflow-y: auto; }
        .legend-cat-title { font-weight: bold; color: #2980b9; margin-top: 8px; margin-bottom: 4px; border-bottom: 1px solid #eee; text-transform: uppercase; font-size: 10px; }
        .legend-sub-title { font-weight: bold; color: #555; margin-left: 5px; font-size: 10px; font-style: italic; }
        .legend i { width: 14px; height: 14px; float: left; margin-right: 8px; opacity: 0.8; border: 1px solid #999; border-radius: 2px; margin-top: 2px; }
        .legend img { width: 16px; height: 16px; float: left; margin-right: 8px; object-fit: contain; }
        .legend-item { margin-bottom: 4px; margin-left: 10px; display: flex; align-items: center; clear: both; }
        
        .leaflet-popup-content-wrapper { padding: 0; overflow: hidden; border-radius: 8px; }
        .leaflet-popup-content { margin: 0 !important; width: 320px !important; }
        .popup-header { font-weight: bold; background: #3498db; color: white; padding: 10px; text-align: center; font-size: 13px; }
        .popup-scroll { max-height: 300px; overflow-y: auto; padding: 5px 10px 10px 10px; }
        .popup-table { font-size: 11px; border-collapse: collapse; width: 100%; table-layout: fixed; }
        .popup-table td { border: 1px solid #eee; padding: 6px; word-wrap: break-word; vertical-align: top; }
        .popup-table tr:nth-child(even) { background-color: #f9f9f9; }
        .custom-pin { display: flex; justify-content: center; align-items: center; }

        /* Style marker lokasi user */
        .user-location-marker {
            background: #2196F3;
            border: 2px solid white;
            border-radius: 50%;
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.4);
        }
    </style>
</head>
<body>

<div id="sidebar">
    <div style="display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #555; padding: 10px 0; margin-bottom: 20px;">
        <img src="{{ asset('images/logo-pupr.png') }}" alt="Logo PUPR" style="width: 30px; height: auto; margin-right: 10px;">
        <h2 style="font-size: 1.1rem; color: #ecf0f1; margin: 0; padding: 0; border-bottom: none; text-align: left;">GIS GEOCIMANCIS</h2>
    </div>
    <div id="dynamic-menu">
        <p style="font-size: 0.8rem; text-align: center; color: #bdc3c7;">Memuat daftar data...</p>
    </div>
</div>

<button id="toggle-sidebar"><i class="fas fa-chevron-left"></i></button>
<div id="map"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('map', { 
        center: [-6.722, 108.556], zoom: 11, zoomAnimation: true, markerZoomAnimation: true, zoomSnap: 0.5, zoomDelta: 0.5, attributionControl: false 
    });

    var googleSat = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'] }).addTo(map);
    var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
    L.control.attribution({ prefix: false }).addAttribution('Google Maps').addTo(map);
    var baseMaps = { "Google Satellite": googleSat, "OpenStreetMap": osm };
    L.control.layers(baseMaps).addTo(map);

    var activeLayers = {};
    var layerInfoForLegend = {}; 

    document.getElementById('toggle-sidebar').addEventListener('click', function() {
        var sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('hidden');
        const icon = this.querySelector('i');
        icon.className = sidebar.classList.contains('hidden') ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
        setTimeout(function() { map.invalidateSize(); }, 350);
    });

    const classificationColors = {
        'baku': '#006400',      
        'fungsional': '#7CFC00',
        'potensial': '#32CD32',  
        'jaringan': '#FFFF00',  
        'bangunan': '#754c4c',
        'hutan lindung': '#228B22',
        'hutan produksi': '#90EE90',
        'hutan produksi terbatas': '#32CD32',
        'hutan produksi tetap': '#006400',
        'alur aliran bahan rombakan': '#FFAA00',
        'danau': '#0070FF',
        'sangat rendah': '#D3FFBE',
        'rendah': '#AAFF00', 
        'menengah': '#FFFF00', 
        'tinggi': '#FF0000',
        'aquifer produktif dengan penyebaran luas': '#E6A1CD', 
        'daerah air tanah langka': '#7193AE', 
        'setempat aquifer produktif': '#9A73DE', 
        'aquifer produktif kecil setempat berarti': '#A28E69', 
        'aquifer produktif sedang dengan penyebaran luas': '#B5E697', 
        'aquifer produktif tinggi dengan penyebaran luas': '#E77978',
        'cat bandung': '#00E6A9', 
        'cat garut': '#FF0000', 
        'cat indramayu': '#2892C7', 
        'cat kuningan': '#95BD9F', 
        'cat majalengka': '#FA8532', 
        'cat malangbong': '#68A6B2', 
        'cat sukamantri': '#FFFFBE', 
        'cat sumber-cirebon': '#E6E600', 
        'cat sumedang': '#BF9556', 
        'cat tasikmalaya': '#FF73DF', 
        'cat tegal-brebes': '#BFD48A', 
        'non cat': '#E1E1E1',
        'tidak terjadi banjir': '#BED2FF', 
        'kerawanan rendah': '#FFBEBE', 
        'kerawanan sedang': '#FF7F7F', 
        'kerawanan tinggi': '#A80000',
        'batuan lempung bermasalah': '#7CB342',
        'dataran aluvial': '#3BA0BC', 
        'dataran kaki vulkan': '#B7FF93', 
        'dataran marine': '#93C3FF', 
        'kaki vulkan': '#FFBB9C', 
        'kepundan': '#933B3A', 
        'kerucut parasiter': '#BB673B', 
        'kerucut vulkan': '#EB3B3B', 
        'lereng vulkan': '#FF9C9C', 
        'medan lava': '#FFFF3B', 
        'pegunungan denudasional lereng curam': '#FF7C3C', 
        'pegunungan denudasional lereng sangat curam': '#BB913B', 
        'pegunungan struktural lereng curam': '#FF3BD1', 
        'pegunungan struktural lereng sangat curam': '#933B75', 
        'perbukitan denudasional': '#EDB03B'
    };

    const iconMapping = {
        'bendungan': 'bendungan.png', 'bendung': 'bendung.png', 'embung': 'embung.png', 'situ': 'situ.png',
        'pengendali_sedimen': 'pengendalisedimen.png', 'pengendali_banjir': 'pengendali%20banjir.png',
        'irigasi': 'bangunan_irigasi.png', 'pantai': 'bangunanpantai.png', 'mata_air': 'mata_air.png',
        'sumur': 'sumur.png', 'klimatologi': 'pos_klimatologi.png', 'duga_air': 'pos_duga_air.png', 'curah_hujan': 'pos_curah_hujan.png'
    };

    function getLayerIconPath(layerName) {
        let name = layerName.toLowerCase();
        for (let key in iconMapping) { if (name.includes(key)) return iconMapping[key]; }
        return null;
    }

    function getLayerIcon(layerName) {
        let path = getLayerIconPath(layerName);
        return path ? `<img src="icon/${path}" class="layer-icon" onerror="this.style.display='none'">` : ''; 
    }

    function formatTitle(text) {
        let lower = text.toLowerCase();
        if (lower === 'das_cimanggis_ar') return "Batas 25 Das Cimanuk Cisanggarung";
        if (lower.includes('sungai_orde_ln')) return "Jaringan Sungai";
        return text.replace(/di_/gi, '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    function updateLegend() {
        var content = document.getElementById('legend-content');
        if(!content) return;
        content.innerHTML = '';
        var layerKeys = Object.keys(activeLayers);
        if (layerKeys.length === 0) { 
            content.innerHTML = '<span style="color:#888; font-style:italic">Pilih layer...</span>'; 
            return; 
        }
        let structured = {};
        layerKeys.forEach(key => {
            let info = layerInfoForLegend[key];
            if (!info) return;
            if (!structured[info.category]) structured[info.category] = {};
            let sub = info.subCategory || "General";
            if (!structured[info.category][sub]) structured[info.category][sub] = [];
            structured[info.category][sub].push({ name: key, details: info });
        });
        for (let cat in structured) {
            content.innerHTML += `<div class="legend-cat-title">${cat}</div>`;
            for (let sub in structured[cat]) {
                if (sub !== "General") content.innerHTML += `<div class="legend-sub-title">${sub}</div>`;
                structured[cat][sub].forEach(item => {
                    let info = item.details;
                    if (info.type === 'icon') {
                        content.innerHTML += `<div class="legend-item"><img src="icon/${info.icon}"> <span>${formatTitle(item.name)}</span></div>`;
                    } else if (info.type === 'classified') {
                        content.innerHTML += `<div style="margin-left:10px; font-weight:600; font-size:10px;">${formatTitle(item.name)}</div>`;
                        info.classes.forEach(cls => {
                            content.innerHTML += `<div class="legend-item" style="margin-left:20px;"><i style="background: ${cls.color}"></i> <span>${cls.label}</span></div>`;
                        });
                    } else {
                        content.innerHTML += `<div class="legend-item"><i style="background: ${info.color}"></i> <span>${formatTitle(item.name)}</span></div>`;
                    }
                });
            }
        }
    }

    var legend = L.control({ position: 'bottomright' });
    legend.onAdd = function (map) {
        var div = L.DomUtil.create('div', 'legend');
        div.innerHTML = '<strong style="display:block;margin-bottom:8px;border-bottom:1px solid #eee; font-size:12px">LEGENDA</strong><div id="legend-content">Pilih layer...</div>';
        L.DomEvent.disableScrollPropagation(div);
        L.DomEvent.disableClickPropagation(div);
        return div;
    };
    legend.addTo(map);

    function toggleMenu(btn) {
        const content = btn.nextElementSibling;
        const arrow = btn.querySelector('.arrow');
        content.classList.toggle('active');
        arrow.style.transform = content.classList.contains('active') ? "rotate(180deg)" : "rotate(0deg)";
    }

    function createMarkerIcon(color, layerName) {
        let iconFile = getLayerIconPath(layerName);
        if (iconFile) return L.icon({ iconUrl: `icon/${iconFile}`, iconSize: [28, 28], iconAnchor: [14, 14], popupAnchor: [0, -14] });
        const svgIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path fill="${color}" stroke="#fff" stroke-width="1" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>`;
        return L.divIcon({ html: svgIcon, className: 'custom-pin', iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -32] });
    }

    function getFeatureStyle(feature, layerName) {
        let color = "#3388ff"; 
        let fOpacity = 0.6;
        let lWeight = 1;
        let lowerLayer = layerName.toLowerCase();

        if (lowerLayer.includes('baku')) { color = classificationColors['baku']; fOpacity = 0.3; }
        else if (lowerLayer.includes('fungsional')) { color = classificationColors['fungsional']; fOpacity = 0.3; }
        else if (lowerLayer.includes('potensial')) { color = classificationColors['potensial']; fOpacity = 0.5; }
        else if (lowerLayer.includes('jaringan')) { color = classificationColors['jaringan']; fOpacity = 0.7; }
        else if (lowerLayer.includes('bangunan')) { color = classificationColors['bangunan']; }
        else {
            for (let prop in feature.properties) {
                let val = String(feature.properties[prop]).toLowerCase().replace(/,/g, '').replace(/\s+/g, ' ').replace(/\(.*\)/g, '').trim();
                if (classificationColors[val]) { color = classificationColors[val]; break; }
            }
        }
        if (lowerLayer === 'das_cimanggis_ar') { color = "#FF7F7F"; fOpacity = 0; lWeight = 3; }
        else if (lowerLayer.includes('sungai')) { color = "#3498db"; lWeight = 2; }
        return { color: color, weight: lWeight, fillOpacity: fOpacity, opacity: 1, fillColor: color };
    }

    function toggleWFS(checkbox, layerName, category, subCategory = null) {
        if (checkbox.checked) {
            var url = "http://103.144.231.38:8082/geoserver/geocimancis/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=geocimancis:" + layerName + "&outputFormat=application/json&srsName=EPSG:4326";
            fetch(url).then(res => res.json()).then(data => {
                let info = { category: category, subCategory: subCategory };
                let iconPath = getLayerIconPath(layerName);
                if (iconPath) { info.type = 'icon'; info.icon = iconPath; } 
                else {
                    let style = getFeatureStyle(data.features[0], layerName);
                    info.type = 'single'; info.color = style.color;
                    let classes = {};
                    data.features.forEach(f => {
                        for (let prop in f.properties) {
                            let rawVal = String(f.properties[prop]);
                            let cleanVal = rawVal.toLowerCase().replace(/,/g, '').replace(/\s+/g, ' ').replace(/\(.*\)/g, '').trim();
                            if (classificationColors[cleanVal]) { classes[cleanVal] = { color: classificationColors[cleanVal], label: rawVal }; }
                        }
                    });
                    if (Object.keys(classes).length > 1) { info.type = 'classified'; info.classes = Object.values(classes); }
                }
                layerInfoForLegend[layerName] = info;
                var geoJsonLayer = L.geoJSON(data, {
                    style: (feature) => getFeatureStyle(feature, layerName),
                    pointToLayer: (feature, latlng) => L.marker(latlng, { icon: createMarkerIcon(getFeatureStyle(feature, layerName).color, layerName) }),
                    onEachFeature: (feature, layer) => {
                        let rows = "";
                        for (let key in feature.properties) { 
                            if (key.toLowerCase().includes('id')) continue;
                            rows += `<tr><td style="width:100px"><b>${formatTitle(key)}</b></td><td>${feature.properties[key] || "-"}</td></tr>`; 
                        }
                        layer.bindPopup(`<div class="popup-header">${formatTitle(layerName)}</div><div class="popup-scroll"><table class="popup-table">${rows}</table></div>`);
                    }
                });
                activeLayers[layerName] = geoJsonLayer;
                geoJsonLayer.addTo(map);
                updateLegend();
                if (geoJsonLayer.getBounds().isValid()) map.flyToBounds(geoJsonLayer.getBounds(), { padding: [50, 50], duration: 1.5 });
            });
        } else if (activeLayers[layerName]) {
            map.removeLayer(activeLayers[layerName]);
            delete activeLayers[layerName];
            delete layerInfoForLegend[layerName];
            updateLegend();
        }
    }

    function loadSidebarLayers() {
        const capabilitiesUrl = "http://103.144.231.38:8082/geoserver/geocimancis/ows?service=WFS&version=1.1.0&request=GetCapabilities";
        const mainCategories = ["DAS dan Jaringan Sungai", "Infrastruktur", "Daerah Irigasi Pusat", "Kebencanaan", "Sumber Daya Alam"];
        fetch(capabilitiesUrl).then(res => res.text()).then(str => new window.DOMParser().parseFromString(str, "text/xml")).then(data => {
            const layers = data.getElementsByTagName("FeatureType");
            const menuContainer = document.getElementById('dynamic-menu');
            menuContainer.innerHTML = '';
            let catalog = {};
            mainCategories.forEach(cat => catalog[cat] = []);
            Array.from(layers).forEach(layer => {
                const fullName = layer.getElementsByTagName("Name")[0].textContent.replace('geocimancis:', '');
                const lowerName = fullName.toLowerCase();
                let category = "Sumber Daya Alam";
                if (lowerName.includes('bendung') || lowerName.includes('embung') || lowerName.includes('pengendali') || lowerName.includes('pengaman') || lowerName.includes('mata') || lowerName.includes('situ')) category = "Infrastruktur";
                else if (lowerName.startsWith('di_')) category = "Daerah Irigasi Pusat";
                else if (lowerName.includes('sungai') || lowerName.includes('das')) category = "DAS dan Jaringan Sungai";
                else if (lowerName.includes('banjir') || lowerName.includes('kekeringan') || lowerName.includes('gerakan')) category = "Kebencanaan";
                catalog[category].push({ name: fullName });
            });
            for (let catName in catalog) {
                if (catalog[catName].length === 0) continue;
                let groupDiv = document.createElement('div');
                groupDiv.className = 'menu-group';
                let isOpen = (catName === "DAS dan Jaringan Sungai") ? "active" : "";
                groupDiv.innerHTML = `<button class="menu-title" onclick="toggleMenu(this)">${catName} <span class="arrow">&#9660;</span></button><div class="menu-content ${isOpen}"></div>`;
                const contentDiv = groupDiv.querySelector('.menu-content');
                if (catName === "Daerah Irigasi Pusat") {
                    let subGroups = {};
                    catalog[catName].forEach(item => {
                        let parts = item.name.split('_');
                        let region = parts[1] ? parts[1].toUpperCase() : "LAINNYA";
                        if (!subGroups[region]) subGroups[region] = [];
                        subGroups[region].push(item);
                    });
                    for (let region in subGroups) {
                        let subDiv = document.createElement('div');
                        subDiv.className = 'sub-bab-container';
                        subDiv.innerHTML = `<button class="sub-bab-title" onclick="toggleMenu(this)">DI ${region} <span class="arrow">&#9660;</span></button><div class="sub-bab-content"></div>`;
                        const subContent = subDiv.querySelector('.sub-bab-content');
                        subGroups[region].forEach(item => {
                            let itemDiv = document.createElement('div');
                            itemDiv.className = 'menu-item';
                            itemDiv.innerHTML = `<label><input type="checkbox" onchange="toggleWFS(this, '${item.name}', '${catName}', 'DI ${region}')">${getLayerIcon(item.name)}<span>${formatTitle(item.name)}</span></label>`;
                            subContent.appendChild(itemDiv);
                        });
                        contentDiv.appendChild(subDiv);
                    }
                } else {
                    catalog[catName].forEach(item => {
                        let itemDiv = document.createElement('div');
                        itemDiv.className = 'menu-item';
                        let isChecked = (catName === "DAS dan Jaringan Sungai") ? "checked" : "";
                        itemDiv.innerHTML = `<label><input type="checkbox" ${isChecked} onchange="toggleWFS(this, '${item.name}', '${catName}')">${getLayerIcon(item.name)}<span>${formatTitle(item.name)}</span></label>`;
                        contentDiv.appendChild(itemDiv);
                        if (isChecked) setTimeout(() => toggleWFS(itemDiv.querySelector('input'), item.name, catName), 200);
                    });
                }
                menuContainer.appendChild(groupDiv);
            }
        });
    }

    // --- FITUR LOKASI SAYA (GEOLOCATION) ---
    var userMarker, userCircle;

    function onLocationFound(e) {
        var radius = e.accuracy / 2;
        if (!userMarker) {
            userMarker = L.marker(e.latlng, {
                icon: L.divIcon({
                    className: 'user-location-marker',
                    iconSize: [12, 12]
                })
            }).addTo(map).bindPopup("Lokasi Anda saat ini");
            userCircle = L.circle(e.latlng, radius, {
                color: '#2196F3',
                fillColor: '#2196F3',
                fillOpacity: 0.15,
                weight: 1
            }).addTo(map);
        } else {
            userMarker.setLatLng(e.latlng);
            userCircle.setLatLng(e.latlng);
            userCircle.setRadius(radius);
        }
    }

    function onLocationError(e) {
        console.warn("Gagal mendapatkan lokasi: " + e.message);
    }

    map.on('locationfound', onLocationFound);
    map.on('locationerror', onLocationError);

    // Memulai tracking lokasi
    map.locate({setView: false, watch: true, enableHighAccuracy: true});

    loadSidebarLayers();
</script>
</body>
</html>