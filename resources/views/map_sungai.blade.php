<!DOCTYPE html>
<html>
<head>
    <title>Geocimancis</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pupr.png') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Helvetica, Arial, sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
            background: #1a2a36;
        }

        #sidebar {
            width: 85%;
            max-width: 340px;
            background: #2c3e50;
            color: #f0f3f8;
            padding: 16px 12px;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            transform: translateX(0);
        }

        #sidebar.hidden {
            transform: translateX(-100%);
        }

        #toggle-sidebar {
            position: fixed;
            left: min(85%, 340px);
            top: 16px;
            z-index: 1100;
            background: #2c3e50;
            color: #e2e8f0;
            border: none;
            padding: 10px 12px;
            border-radius: 0 28px 28px 0;
            cursor: pointer;
            font-size: 20px;
            transition: left 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1), background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
            width: 44px;
            height: 44px;
        }

        #toggle-sidebar:active {
            background: #1a4a6e;
        }

        #sidebar.hidden + #toggle-sidebar {
            left: 12px;
            border-radius: 40px;
        }

        #map {
            flex-grow: 1;
            width: 100%;
            height: 100%;
            position: relative;
            z-index: 1;
        }

        #sidebar h2 {
            font-size: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding-bottom: 10px;
            margin-bottom: 18px;
            color: #ffffff;
            text-align: center;
            font-weight: 600;
        }

        .global-search-container {
            background: #2c3e50;
            border-radius: 12px;
            margin-bottom: 20px;
            padding: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            border: 1px solid #3a5a7a;
        }
        .global-search-box {
            display: flex;
            align-items: center;
            background: #ecf0f1;
            border-radius: 40px;
            padding: 6px 12px;
            gap: 8px;
        }
        .global-search-box i {
            color: #3498db;
            font-size: 14px;
        }
        .global-search-box input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 8px 0;
            font-size: 0.85rem;
            outline: none;
            color: #2c3e50;
        }
        .global-search-box input::placeholder {
            color: #7f8c8d;
            font-size: 0.75rem;
        }
        .global-search-box button {
            background: #3498db;
            border: none;
            color: white;
            border-radius: 30px;
            padding: 6px 12px;
            font-size: 0.7rem;
            cursor: pointer;
            font-weight: bold;
        }
        .search-results {
            margin-top: 10px;
            max-height: 200px;
            overflow-y: auto;
            background: #34495e;
            border-radius: 10px;
            display: none;
        }
        .search-results.active {
            display: block;
        }
        .result-item {
            padding: 10px 12px;
            border-bottom: 1px solid #3a5a7a;
            cursor: pointer;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .result-item:hover {
            background: #3a5a7a;
        }
        .result-item i {
            width: 20px;
            color: #3498db;
        }
        .result-type {
            font-size: 0.6rem;
            background: #3498db;
            padding: 2px 6px;
            border-radius: 20px;
            color: white;
            margin-left: auto;
        }
        .no-result {
            padding: 10px;
            text-align: center;
            color: #bdc3c7;
            font-size: 0.7rem;
        }

        .menu-group {
            margin-bottom: 12px;
            border-radius: 12px;
            overflow: hidden;
            background: #34495e;
        }

        .menu-title {
            width: 100%;
            padding: 14px 16px;
            background: #3498db;
            color: white;
            border: none;
            text-align: left;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
        }

        .menu-content {
            display: none;
            padding: 10px 8px;
            background: #34495e;
        }

        .menu-content.active {
            display: block;
        }

        .sub-bab-container {
            margin-top: 8px;
            border-left: 3px solid #3498db;
            margin-left: 4px;
            margin-bottom: 10px;
            background: #2c3e50;
            border-radius: 10px;
            overflow: hidden;
        }

        .sub-bab-title {
            width: 100%;
            background: #2c3e50;
            color: #ecf0f1;
            border: none;
            text-align: left;
            padding: 10px 14px;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sub-bab-content {
            display: none;
            padding: 8px 6px 10px 12px;
        }

        .sub-bab-content.active {
            display: block;
        }

        .menu-item {
            background: #2c3e50;
            border-radius: 10px;
            margin-bottom: 8px;
        }

        .menu-item label {
            font-size: 0.8rem;
            cursor: pointer;
            flex-grow: 1;
            padding: 12px 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            margin: 0;
            color: #ecf0f1;
        }

        .menu-item input {
            margin: 0;
            width: 20px;
            height: 20px;
            cursor: pointer;
            flex-shrink: 0;
            accent-color: #3498db;
        }

        .layer-icon {
            width: 24px;
            height: 24px;
            object-fit: contain;
            flex-shrink: 0;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 6px;
            padding: 2px;
        }

        .arrow {
            font-size: 0.7rem;
            transition: transform 0.2s;
            display: inline-block;
        }

        .legend {
            background: rgba(255, 255, 255, 0.95);
            padding: 0;
            line-height: 1.35;
            color: #333;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            font-size: 10px;
            min-width: 170px;
            max-width: 230px;
            border: 1px solid #ddd;
            overflow: hidden;
        }
        .legend-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            background: #3498db;
            color: white;
            cursor: pointer;
            font-weight: bold;
            font-size: 11px;
        }
        .legend-header i {
            font-size: 12px;
            transition: transform 0.2s;
        }
        .legend-content {
            padding: 10px 12px;
            max-height: 300px;
            overflow-y: auto;
            transition: all 0.2s ease;
        }
        .legend-content.collapsed {
            display: none;
        }

        .legend-cat-title {
            font-weight: bold;
            color: #2980b9;
            margin-top: 8px;
            margin-bottom: 6px;
            border-bottom: 1px solid #eee;
            text-transform: uppercase;
            font-size: 10px;
        }

        .legend-sub-title {
            font-weight: bold;
            color: #555;
            margin-left: 6px;
            font-size: 9px;
            font-style: italic;
        }

        .legend i {
            width: 16px;
            height: 16px;
            float: left;
            margin-right: 8px;
            opacity: 0.8;
            border: 1px solid #999;
            border-radius: 3px;
            margin-top: 2px;
        }

        .legend img {
            width: 20px;
            height: 20px;
            float: left;
            margin-right: 8px;
            object-fit: contain;
        }

        .legend-item {
            margin-bottom: 5px;
            margin-left: 8px;
            display: flex;
            align-items: center;
            clear: both;
            font-size: 9.5px;
            gap: 6px;
        }

        .leaflet-popup-content-wrapper {
            padding: 0;
            overflow: hidden;
            border-radius: 12px;
            max-width: 85vw;
        }

        .leaflet-popup-content {
            margin: 0 !important;
            width: 320px !important;
            max-width: 85vw;
        }

        .popup-header {
            font-weight: bold;
            background: #e74c3c;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 13px;
        }

        .popup-scroll {
            max-height: 350px;
            overflow-y: auto;
            padding: 8px 12px 12px 12px;
            background: white;
        }

        .popup-table {
            font-size: 11px;
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }

        .popup-table td {
            border: 1px solid #eee;
            padding: 6px;
            word-wrap: break-word;
            vertical-align: top;
        }

        .leaflet-control-zoom {
            position: fixed !important;
            top: 90px !important;
            right: 10px !important;
            left: auto !important;
            bottom: auto !important;
            z-index: 400 !important;
        }

        #toast-container {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }
        .toast {
            background: #3498db;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideUp 0.3s ease;
            pointer-events: auto;
            min-width: 200px;
            text-align: center;
        }
        .toast.success { background: #27ae60; }
        .toast.error { background: #e74c3c; }
        .toast.warning { background: #f39c12; }
        
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes slideDown {
            from { transform: translateY(0); opacity: 1; }
            to { transform: translateY(100%); opacity: 0; }
        }

        @media (max-width: 560px) {
            .menu-item label { font-size: 0.73rem; padding: 10px 10px; gap: 8px; }
            .menu-title { font-size: 0.65rem; padding: 12px 14px; }
            .legend { max-width: 190px; font-size: 9px; }
            .legend-content { max-height: 250px; }
            #toggle-sidebar { width: 40px; height: 40px; padding: 8px; font-size: 18px; }
        }

        #sidebar::-webkit-scrollbar { width: 4px; }
        #sidebar::-webkit-scrollbar-track { background: #2c3e50; }
        #sidebar::-webkit-scrollbar-thumb { background: #5d7e9a; border-radius: 8px; }
        
        .search-marker { filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }
        .lokasi-kritis-simona-marker {
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(3); opacity: 0; }
        }
    </style>
</head>
<body>

<div id="sidebar">
    <div style="display: flex; align-items: center; justify-content: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.15); padding: 8px 0 12px 0; margin-bottom: 18px;">
        <img src="{{ asset('images/logo-pupr.png') }}" alt="Logo PUPR" style="width: 32px; height: auto;">
        <h2 style="font-size: 1rem; margin: 0; padding: 0; border-bottom: none; text-align: left;">GIS GEOCIMANCIS</h2>
    </div>
    
    <div class="global-search-container">
        <div class="global-search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="global-search-input" placeholder="Cari layer atau nama lokasi... (contoh: jatigede, situ sari)">
            <button id="global-search-btn">Cari</button>
        </div>
        <div id="global-search-results" class="search-results"></div>
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
        center: [-6.722, 108.556], zoom: 11, zoomAnimation: true, markerZoomAnimation: true, zoomSnap: 0.5, zoomDelta: 0.5, attributionControl: false, zoomControl: false
    });
    L.control.zoom({ position: 'topright' }).addTo(map);
    var googleSat = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'] }).addTo(map);
    var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
    L.control.attribution({ prefix: false }).addAttribution('Google Maps').addTo(map);
    var baseMaps = { "Google Satellite": googleSat, "OpenStreetMap": osm };
    L.control.layers(baseMaps, null, { position: 'topright' }).addTo(map);
    var activeLayers = {};
    var layerInfoForLegend = {}; 
    
    var searchMarker = null;
    var allLocationsCache = [];
    var availableLayersList = [];
    var preloadCompleted = false;

    function showToast(message, type = 'info') {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            document.body.appendChild(toastContainer);
        }
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        
        toastContainer.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function parseCoordinate(coordString) {
        coordString = coordString.trim().replace(/\s+/g, ' ');
        let decimalMatch = coordString.match(/^(-?\d+(?:\.\d+)?)[,\s]+(-?\d+(?:\.\d+)?)$/);
        if (decimalMatch) {
            let lat = parseFloat(decimalMatch[1]);
            let lng = parseFloat(decimalMatch[2]);
            if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                return { lat: lat, lng: lng };
            }
        }
        return null;
    }
    
    function createSearchIcon() {
        return L.divIcon({
            html: `<div style="position: relative;">
                        <i class="fas fa-map-marker-alt" style="color: #e74c3c; font-size: 32px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"></i>
                        <div style="position: absolute; top: -8px; left: 8px; width: 8px; height: 8px; background: #e74c3c; border-radius: 50%; animation: pulse 1.5s infinite;"></div>
                    </div>`,
            className: 'search-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });
    }
    
    function clearSearchMarker() {
        if (searchMarker) {
            map.removeLayer(searchMarker);
            searchMarker = null;
        }
    }

    document.getElementById('toggle-sidebar').addEventListener('click', function() {
        var sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('hidden');
        const icon = this.querySelector('i');
        icon.className = sidebar.classList.contains('hidden') ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
        setTimeout(function() { map.invalidateSize(); }, 350);
    });

    const classificationColors = {
        'baku': '#006400', 'fungsional': '#7CFC00', 'potensial': '#32CD32', 'bangunan': '#754c4c',
        'hutan lindung': '#228B22', 'hutan produksi': '#90EE90', 'hutan produksi terbatas': '#32CD32',
        'hutan produksi tetap': '#006400', 'alur aliran bahan rombakan': '#FFAA00', 'danau': '#0070FF',
        'sangat rendah': '#D3FFBE', 'rendah': '#AAFF00', 'menengah': '#FFFF00', 'tinggi': '#FF0000',
        'landai': '#eedbae', 'datar': '#f1a12f', 'agak curam': '#fc8c8c', 'curam': '#ff1c1c', 'sangat curam': '#b70101',
        'aquifer produktif dengan penyebaran luas': '#E6A1CD', 'daerah air tanah langka': '#7193AE', 
        'setempat aquifer produktif': '#9A73DE', 'aquifer produktif kecil setempat berarti': '#A28E69', 
        'aquifer produktif sedang dengan penyebaran luas': '#B5E697', 'aquifer produktif tinggi dengan penyebaran luas': '#E77978',
        'cat bandung': '#00E6A9', 'cat garut': '#FF0000', 'cat indramayu': '#2892C7', 'cat kuningan': '#95BD9F', 
        'cat majalengka': '#FA8532', 'cat malangbong': '#68A6B2', 'cat sukamantri': '#FFFFBE', 'cat sumber-cirebon': '#E6E600', 
        'cat sumedang': '#BF9556', 'cat tasikmalaya': '#FF73DF', 'cat tegal-brebes': '#BFD48A', 'non cat': '#E1E1E1',
        'tidak terjadi banjir': '#BED2FF', 'kerawanan rendah': '#FFBEBE', 'kerawanan sedang': '#FF7F7F', 'kerawanan tinggi': '#A80000',
        'lokasi kritis': '#800000', 'batuan lempung bermasalah': '#7CB342', 'dataran aluvial': '#3BA0BC', 
        'dataran kaki vulkan': '#B7FF93', 'dataran marine': '#93C3FF', 'kaki vulkan': '#FFBB9C', 'kepundan': '#933B3A', 
        'kerucut parasiter': '#BB673B', 'kerucut vulkan': '#EB3B3B', 'lereng vulkan': '#FF9C9C', 'medan lava': '#FFFF3B', 
        'pegunungan denudasional lereng curam': '#FF7C3C', 'pegunungan denudasional lereng sangat curam': '#BB913B', 
        'pegunungan struktural lereng curam': '#FF3BD1', 'pegunungan struktural lereng sangat curam': '#933B75', 
        'perbukitan denudasional': '#EDB03B', 'aset_tanah_brebes': '#26ec0c', 'aset_tanah_cirebon': '#ec9e0c',
        'aset_tanah_cirebon_kota': '#00FFFF', 'aset_tanah_indramayu': '#FF00FF', 'aset_tanah_majalengka': '#FFA500',
        'aset_tanah_sumedang': '#800080', 'risiko rendah': '#96f13a', 'risiko sedang': '#fffb00', 'risiko tinggi': '#ff9900', 
        'risiko sangat tinggi': '#ff0202', 'saluran induk': '#000000', 'saluran primer': '#ff0000', 'saluran sekunder': '#ffea00',
        'saluran tersier': '#0015f8', 'saluran terrsier': '#0015f8', 'saluran pembuang': '#93f2f2', 'saluran suplesi': '#ea6500', 'waduk': '#0070FF',
    };

    const iconMapping = {
        'bendungan': 'bendungan.png', 'bendung': 'bendung.png', 'sumur': 'sumur.png', 'embung': 'embung.png', 'situ': 'situ.png',
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
        if (lower.includes('waduk_ar')) return "Waduk";
        if (lower.includes('banjir')) return "Rawan Banjir";
        if (lower.includes('sumur_air_tanah_2026')) return "Sumur Air Tanah";
        if (lower.includes('demnas')) return "Digital Elevation Model Nasional (BIG)";
        if (lower.includes('kekeringan')) {
            if (lower.includes('cirebon')) return "Rawan Kekeringan Cirebon";
            if (lower.includes('brebes')) return "Rawan Kekeringan Brebes";
            if (lower.includes('indramayu')) return "Rawan Kekeringan Indramayu";
            if (lower.includes('majalengka')) return "Rawan Kekeringan Majalengka";
            if (lower.includes('kuningan')) return "Rawan Kekeringan Kuningan";
            if (lower.includes('sumedang')) return "Rawan Kekeringan Sumedang";
            if (lower.includes('garut')) return "Rawan Kekeringan Garut";
            return "Rawan Kekeringan";
        }
	if (lower === 'lokasi_kritis_simona') return "Titik Lokasi Kritis";
        if (lower.includes('lokasi_kritis')) return "Lokasi Kritis";
        if (lower === 'aquifer_cimancis_ar') return "Aquifer";
        if (lower === 'geologi_regional_250k') return "Geologi Regional";
        if (lower === 'geomorfologi_cimancis') return "Geomorfologi";
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
                    } else if (info.type === 'wms-legend') {
                        content.innerHTML += `<div style="margin-left:10px; font-weight:600; font-size:10px;">${formatTitle(item.name)}</div><div class="legend-item" style="margin-left:20px;"><img src="${info.legendUrl}" style="width:auto; height:auto; max-width:180px;"></div>`;
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
        div.innerHTML = `
            <div class="legend-header" onclick="toggleLegend()">
                <span><i class="fas fa-list-ul"></i> LEGENDA</span>
                <i id="legend-toggle-icon" class="fas fa-chevron-up"></i>
            </div>
            <div id="legend-content" class="legend-content"></div>
        `;
        L.DomEvent.disableScrollPropagation(div);
        L.DomEvent.disableClickPropagation(div);
        return div;
    };
    legend.addTo(map);
    
    window.toggleLegend = function() {
        var content = document.getElementById('legend-content');
        var icon = document.getElementById('legend-toggle-icon');
        if (content) {
            content.classList.toggle('collapsed');
            if (icon) {
                icon.className = content.classList.contains('collapsed') ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
            }
        }
    };

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
        let color = "#65f176"; 
        let fOpacity = 0.6;
        let lWeight = 1.5;
        let lowerLayer = layerName.toLowerCase();

        if (lowerLayer.includes('jaringan')) {
            lWeight = 3;
            let props = feature.properties || {};
            let selectedVal = props.saluran || props.Saluran || props.SALURAN || 
                              props.n_aset || props.N_Aset || props.N_ASET ||
                              props.nama || props.Nama || props.NAMA;
            if (selectedVal) {
                let val = String(selectedVal).toLowerCase().trim();
                if (val.includes('induk')) {
                    color = classificationColors['saluran induk'];
                    lWeight = 4.5;
                } else if (val.includes('primer')) {
                    color = classificationColors['saluran primer'];
                    lWeight = 3.5;
                } else if (val.includes('sekunder')) {
                    color = classificationColors['saluran sekunder'];
                    lWeight = 2.5;
                } else if (val.includes('tersier')) {
                    color = classificationColors['saluran tersier'];
                    lWeight = 1.5;
                } else if (val.includes('terrsier')) {
                    color = classificationColors['saluran tersier'];
                    lWeight = 1.5;
                }else if (val.includes('pembuang')) {
                    color = classificationColors['saluran pembuang'];
                }else if (val.includes('suplesi')) {
                    color = classificationColors['saluran suplesi'];
                } else {
                    color = "#ee1bce"; 
                }
            }
        }
        if (lowerLayer.includes('sempadan')) {
            color = '#12d0f1';
            lWeight = 3;
            fOpacity = 0.1;
            return { color: color, weight: lWeight, fillOpacity: fOpacity, opacity: 1, fillColor: color };
        }
        if (classificationColors[lowerLayer]) {
            color = classificationColors[lowerLayer];
        } 
        else if (lowerLayer.includes('lokasi_kritis')) { color = '#800000'; fOpacity = 0.7; } 
        else if (lowerLayer.includes('baku')) { color = classificationColors['baku']; fOpacity = 0.3; }
        else if (lowerLayer.includes('fungsional')) { color = classificationColors['fungsional']; fOpacity = 0.3; }
        else if (lowerLayer.includes('potensial')) { color = classificationColors['potensial']; fOpacity = 0.5; }
        else if (lowerLayer.includes('bangunan')) { color = classificationColors['bangunan']; }
        else if (lowerLayer.includes('waduk_ar')) { color = '#0070FF'; fOpacity = 0.6; }
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

    function toggleWMS(checkbox, layerName, category) {
        if (checkbox.checked) {
            var wmsLayer = L.tileLayer.wms("https://geo.sisdacimancis.id/geoserver/geocimancis/wms", {
                layers: 'geocimancis:' + layerName,
                format: 'image/png',
                transparent: true,
                version: '1.1.1',
                srs: 'EPSG:3857',
                tiled: true,
                tilesOrigin: '107.69,-7.42',
                attribution: "GeoCimancis WMS"
            }).addTo(map);
            activeLayers[layerName] = wmsLayer;
            let legendUrl = `https://geo.sisdacimancis.id/geoserver/geocimancis/wms?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&LAYER=geocimancis:${layerName}`;
            layerInfoForLegend[layerName] = {
                category: category,
                type: 'wms-legend',
                legendUrl: legendUrl
            };
            updateLegend();
        } else {
            if (activeLayers[layerName]) {
                map.removeLayer(activeLayers[layerName]);
                delete activeLayers[layerName];
                delete layerInfoForLegend[layerName];
                updateLegend();
            }
        }
    }

    function toggleWFS(checkbox, layerName, category, subCategory = null) {
        if (checkbox.checked) {
            var url = "https://geo.sisdacimancis.id/geoserver/geocimancis/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=geocimancis:" + layerName + "&outputFormat=application/json&srsName=EPSG:4326";
            fetch(url).then(res => res.json()).then(data => {
                if (!data.features || data.features.length === 0) return;
                
                let info = { category: category, subCategory: subCategory };
                let iconPath = getLayerIconPath(layerName);
                if (iconPath) { info.type = 'icon'; info.icon = iconPath; } 
                else {
                    let firstFeature = data.features[0];
                    let style = getFeatureStyle(firstFeature || {}, layerName);
                    info.type = 'single'; info.color = style.color;
                    let classes = {};
                    const isJaringanLayer = layerName.toLowerCase().includes('jaringan') || layerName.toLowerCase().includes('saluran');

                    if (isJaringanLayer) {
                        data.features.forEach(f => {
                            for (let prop in f.properties) {
                                let rawVal = String(f.properties[prop]);
                                let cleanVal = rawVal.toLowerCase().replace(/,/g, '').replace(/\s+/g, ' ').replace(/\(.*\)/g, '').trim();
                                if (cleanVal.includes('induk')) {
                                    classes['saluran induk'] = { color: classificationColors['saluran induk'], label: 'Saluran Induk' };
                                } else if (cleanVal.includes('primer')) {
                                    classes['saluran primer'] = { color: classificationColors['saluran primer'], label: 'Saluran Primer' };
                                } else if (cleanVal.includes('sekunder')) {
                                    classes['saluran sekunder'] = { color: classificationColors['saluran sekunder'], label: 'Saluran Sekunder' };
                                } else if (cleanVal.includes('tersier')) {
                                    classes['saluran tersier'] = { color: classificationColors['saluran tersier'], label: 'Saluran Tersier' };
                                } else if (classificationColors[cleanVal]) { 
                                    classes[cleanVal] = { color: classificationColors[cleanVal], label: rawVal }; 
                                }
                            }
                        });
                    } else {
                        // Untuk layer non-jaringan, klasifikasi hanya berdasarkan classificationColors saja
                        data.features.forEach(f => {
                            for (let prop in f.properties) {
                                let rawVal = String(f.properties[prop]);
                                let cleanVal = rawVal.toLowerCase().replace(/,/g, '').replace(/\s+/g, ' ').replace(/\(.*\)/g, '').trim();
                                if (classificationColors[cleanVal]) { 
                                    classes[cleanVal] = { color: classificationColors[cleanVal], label: rawVal }; 
                                }
                            }
                        });
                    }
                    if (Object.keys(classes).length > 1) { info.type = 'classified'; info.classes = Object.values(classes); }                }
                layerInfoForLegend[layerName] = info;
                var geoJsonLayer = L.geoJson(data, {
                    style: (feature) => getFeatureStyle(feature, layerName),
                    pointToLayer: (feature, latlng) => L.marker(latlng, { icon: createMarkerIcon(getFeatureStyle(feature, layerName).color, layerName) }),
                    onEachFeature: (feature, layer) => {
                        let props = feature.properties;
                        let displayTitle = props.Nama || props.nama || props.N_Aset || props.n_aset;
                        if (!displayTitle || displayTitle === "-" || displayTitle === "0") {
                            displayTitle = props.saluran || props.Saluran || formatTitle(layerName);
                        }
                        let rows = "";
                        for (let key in props) { 
                            if (key.toLowerCase().includes('id')) continue;
                            rows += `<tr><td style="width:100px"><b>${formatTitle(key)}</b></td><td>${props[key] || "-"}</td></tr>`; 
                        }
                        layer.bindPopup(`<div class="popup-header">${displayTitle.toUpperCase()}</div><div class="popup-scroll"><table class="popup-table">${rows}</table></div>`);
                    }
                });
                activeLayers[layerName] = geoJsonLayer;
                geoJsonLayer.addTo(map);
                if (layerName.toLowerCase().includes('jaringan')) {
                    geoJsonLayer.bringToFront();
                }
                updateLegend();
                if (geoJsonLayer.getBounds().isValid()) map.flyToBounds(geoJsonLayer.getBounds(), { padding: [50, 50], duration: 1.5 });
            }).catch(err => console.error("Gagal memuat layer WFS:", err));
        } else if (activeLayers[layerName]) {
            map.removeLayer(activeLayers[layerName]);
            delete activeLayers[layerName];
            delete layerInfoForLegend[layerName];
            updateLegend();
        }
    }

    function preloadAllData(layerNames) {
        let totalLayers = layerNames.length;
        let loadedLayers = 0;
        
        layerNames.forEach(layerName => {
            var url = "https://geo.sisdacimancis.id/geoserver/geocimancis/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=geocimancis:" + layerName + "&outputFormat=application/json&srsName=EPSG:4326";
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.features && data.features.length > 0) {
                        data.features.forEach(f => {
                            let allValues = [];
                            let primaryName = null;
                            
                            for (let key in f.properties) {
                                let val = f.properties[key];
                                if (val && typeof val === 'string' && val !== "-" && val !== "0" && val.length > 2) {
                                    allValues.push(val);
                                    
                                    let keyLower = key.toLowerCase();
                                    if (keyLower === 'nama' || keyLower === 'n_aset' || keyLower === 'saluran' || 
                                        keyLower.includes('waduk') || keyLower.includes('bendung') || keyLower.includes('lokasi') ||
                                        keyLower === 'namobj' || keyLower === 'aset') {
                                        if (!primaryName) primaryName = val;
                                    }
                                }
                            }
                            
                            if (!primaryName && allValues.length > 0) {
                                primaryName = allValues[0];
                            }
                            
                            if (primaryName && allValues.length > 0) {
                                let coords = null;
                                if (f.geometry.type === 'Point') {
                                    coords = [f.geometry.coordinates[1], f.geometry.coordinates[0]];
                                } else if (f.geometry.type === 'Polygon') {
                                    try { coords = [f.geometry.coordinates[0][0][1], f.geometry.coordinates[0][0][0]]; } catch(e){}
                                } else if (f.geometry.type === 'MultiPolygon') {
                                    try { coords = [f.geometry.coordinates[0][0][0][1], f.geometry.coordinates[0][0][0][0]]; } catch(e){}
                                }
                                if (coords) {
                                    allLocationsCache.push({
                                        name: String(primaryName),
                                        allNames: allValues,
                                        layerTitle: formatTitle(layerName),
                                        coords: coords
                                    });
                                }
                            }
                        });
                    }
                    loadedLayers++;
                    if (loadedLayers === totalLayers) {
                        preloadCompleted = true;
                        console.log(`Preload selesai! Total lokasi: ${allLocationsCache.length}`);
                        const searchInput = document.getElementById('global-search-input');
                        if(searchInput) searchInput.placeholder = `Cari (${allLocationsCache.length} lokasi tersedia)...`;
                    }
                })
                .catch(err => console.error(`Gagal preload ${layerName}:`, err));
        });
    }

    // ==================== LOKASI KRITIS SIMONA (Data dari MySQL) ====================
 // ==================== LOKASI KRITIS SIMONA (Data dari MySQL) ====================
var lokasiKritisSimona = null;
var lokasiKritisSimonaMarkers = [];

// Fungsi untuk mendapatkan warna berdasarkan tingkat kerusakan
function getColorByTingkatKerusakan(tingkat) {
    if (!tingkat) return '#3498db'; // default biru
    
    tingkat = tingkat.toLowerCase();
    if (tingkat.includes('berat')) return '#e74c3c'; // merah
    if (tingkat.includes('sedang')) return '#f1c40f'; // kuning
    if (tingkat.includes('ringan')) return '#3498db'; // biru
    return '#3498db'; // default
}

// Fungsi untuk membuat icon berdasarkan tingkat kerusakan
function getSimonaIcon(tingkatKerusakan) {
    var color = getColorByTingkatKerusakan(tingkatKerusakan);
    
    // Warna untuk overlay (lebih terang/transparan)
    var overlayColor = color === '#e74c3c' ? 'rgba(231, 76, 60, 0.5)' : 
                       (color === '#f1c40f' ? 'rgba(241, 196, 15, 0.5)' : 
                       'rgba(52, 152, 219, 0.5)');
    
    return L.divIcon({
        html: `<div style="position: relative;">
                    <div style="width: 14px; height: 14px; background: ${color}; border: 2px solid white; border-radius: 50%; box-shadow: 0 0 4px rgba(0,0,0,0.3); z-index: 2;"></div>
                    <div style="position: absolute; top: -2px; left: -2px; width: 18px; height: 18px; background: ${overlayColor}; border-radius: 50%; animation: pulse 1.5s infinite; z-index: 1;"></div>
                </div>`,
        className: 'lokasi-kritis-simona-marker',
        iconSize: [14, 14],
        iconAnchor: [7, 7],
        popupAnchor: [0, -7]
    });
}

function loadLokasiKritisSimona() {
    showToast('Memuat data Lokasi Kritis SIMONA...', 'info');
    
    fetch('/api.php/api/lokasi-kritis-mysql')
        .then(response => response.json())
        .then(data => {
            console.log('Data SIMONA:', data);
            
            let features = [];
            if (data.type === 'FeatureCollection' && data.features) {
                features = data.features;
            } else if (data.data && Array.isArray(data.data)) {
                features = data.data.map(item => ({
                    type: 'Feature',
                    geometry: {
                        type: 'Point',
                        coordinates: [parseFloat(item.koordinat_y), parseFloat(item.koordinat_x)]
                    },
                    properties: item
                }));
            } else if (Array.isArray(data)) {
                features = data.map(item => ({
                    type: 'Feature',
                    geometry: {
                        type: 'Point',
                        coordinates: [parseFloat(item.koordinat_y), parseFloat(item.koordinat_x)]
                    },
                    properties: item
                }));
            }
            
            if (features.length > 0) {
                if (lokasiKritisSimona) {
                    map.removeLayer(lokasiKritisSimona);
                    lokasiKritisSimonaMarkers = [];
                }
                
                lokasiKritisSimona = L.geoJson({
                    type: 'FeatureCollection',
                    features: features
                }, {
                    pointToLayer: function(feature, latlng) {
                        var tingkatKerusakan = feature.properties.tingkat_kerusakan || feature.properties.tingkat_kerusakan;
                        var icon = getSimonaIcon(tingkatKerusakan);
                        return L.marker(latlng, { icon: icon });
                    },
                    onEachFeature: function(feature, layer) {
                        let props = feature.properties;
                        let rows = '';
                        let fields = [
                            {label: 'Kode', key: 'kode'},
                            {label: 'Perihal', key: 'perihal'},
                            {label: 'DAS', key: 'das'},
                            {label: 'Sungai', key: 'sungai'},
                            {label: 'Desa', key: 'desa'},
                            {label: 'Kecamatan', key: 'kecamatan'},
                            {label: 'Kab/Kota', key: 'kab_kota'},
                            {label: 'Tingkat Kerusakan', key: 'tingkat_kerusakan'},
                            {label: 'Prioritas', key: 'prioritas_penanganan'},
                            {label: 'Status', key: 'status'},
                            {label: 'Keterangan', key: 'keterangan'}
                        ];
                        for (let field of fields) {
                            if (props[field.key] && props[field.key] !== '' && props[field.key] !== '-') {
                                rows += `<tr><td style="width:100px"><b>${field.label}</b></td><td>${props[field.key]}</td></tr>`;
                            }
                        }
                        let title = props.kode || props.perihal || 'Lokasi Kritis SIMONA';
                        layer.bindPopup(`
                            <div class="popup-header" style="background:#f39c12;">
                                <i class="fas fa-chart-line"></i> ${title}
                            </div>
                            <div class="popup-scroll">
                                <table class="popup-table">${rows}</tr>
                            </div>
                        `);
                        lokasiKritisSimonaMarkers.push(layer);
                    }
                });
                
                lokasiKritisSimona.addTo(map);
                activeLayers['lokasi_kritis_simona'] = lokasiKritisSimona;
                layerInfoForLegend['lokasi_kritis_simona'] = { 
                    category: 'LOKASI KRITIS SIMONA', 
                    type: 'classified',
                    classes: [
                        { color: '#e74c3c', label: 'Tingkat Kerusakan Berat' },
                        { color: '#f1c40f', label: 'Tingkat Kerusakan Sedang' },
                        { color: '#3498db', label: 'Tingkat Kerusakan Ringan' }
                    ]
                };
                updateLegend();
                showToast(`Berhasil memuat ${features.length} titik Lokasi Kritis SIMONA`, 'success');
            } else {
                showToast('Tidak ada data Lokasi Kritis SIMONA', 'warning');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Gagal memuat data Lokasi Kritis SIMONA', 'error');
        });
}

    function toggleLokasiKritisSimona() {
        const checkbox = document.getElementById('lokasi-kritis-simona-checkbox');
        if (lokasiKritisSimona && map.hasLayer(lokasiKritisSimona)) {
            map.removeLayer(lokasiKritisSimona);
            if (checkbox) checkbox.checked = false;
            delete activeLayers['lokasi_kritis_simona'];
            delete layerInfoForLegend['lokasi_kritis_simona'];
            updateLegend();
            showToast('Layer Lokasi Kritis SIMONA disembunyikan', 'info');
        } else {
            loadLokasiKritisSimona();
            if (checkbox) checkbox.checked = true;
        }
    }

    function zoomToLokasiKritisSimona() {
        if (lokasiKritisSimonaMarkers.length > 0) {
            var group = L.featureGroup(lokasiKritisSimonaMarkers);
            map.fitBounds(group.getBounds(), { padding: [50, 50] });
            showToast(`Menampilkan ${lokasiKritisSimonaMarkers.length} titik Lokasi Kritis SIMONA`, 'info');
        } else {
            showToast('Tidak ada marker Lokasi Kritis SIMONA. Aktifkan layer terlebih dahulu.', 'warning');
        }
    }

    // ==================== LOAD SIDEBAR LAYERS ====================
    function loadSidebarLayers() {
        const capabilitiesUrl = "https://geo.sisdacimancis.id/geoserver/geocimancis/ows?service=WFS&version=1.1.0&request=GetCapabilities";
        const mainCategories = ["DAS dan Jaringan Sungai","Batas Wilayah Administrasi", "Infrastruktur", "Daerah Kewenangan Kabupaten dan Provinsi", "Daerah Irigasi Pusat", "Kebencanaan", "Aset Tanah", "Sumber Daya Alam", "Sumber Daya Air"];
        
        fetch(capabilitiesUrl).then(res => res.text()).then(str => new window.DOMParser().parseFromString(str, "text/xml")).then(data => {
            const layers = data.getElementsByTagName("FeatureType");
            const menuContainer = document.getElementById('dynamic-menu');
            menuContainer.innerHTML = '';
            let catalog = {};
            mainCategories.forEach(cat => catalog[cat] = []);
            availableLayersList = [];
            let allLayerNames = [];
            
            Array.from(layers).forEach(layer => {
                const fullName = layer.getElementsByTagName("Name")[0].textContent.replace('geocimancis:', '');
                availableLayersList.push({ name: fullName, title: formatTitle(fullName) });
                allLayerNames.push(fullName);
                assignToCategory(fullName, catalog);
            });

            const manualRasterLayers = ["DEMNAS_Cimancis_UTM"]; 
            manualRasterLayers.forEach(name => {
                assignToCategory(name, catalog);
                availableLayersList.push({ name: name, title: formatTitle(name) });
                allLayerNames.push(name);
            });

            preloadAllData(allLayerNames);

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
                            createMenuItem(item, catName, subContent, `DI ${region}`);
                        });
                        contentDiv.appendChild(subDiv);
                    }
                } 
                else if (catName === "Kebencanaan") {
                    let banjirItems = [];
                    let kekeringanItems = [];
                    let otherItems = [];
                    const kabupatenKekeringan = /cirebon|brebes|indramayu|majalengka|kuningan|sumedang|garut/;
                    catalog[catName].forEach(item => {
                        let name = item.name.toLowerCase();
                        if (name.includes('mh')) {
                            banjirItems.push(item);
                        } else if (name.includes('kekeringan') && kabupatenKekeringan.test(name)) {
                            kekeringanItems.push(item);
                        } else {
                            otherItems.push(item);
                        }
                    });
                    otherItems.forEach(item => {
                        createMenuItem(item, catName, contentDiv);
                    });
                    if (banjirItems.length > 0) {
                        let mhSubDiv = document.createElement('div');
                        mhSubDiv.className = 'sub-bab-container';
                        mhSubDiv.innerHTML = `<button class="sub-bab-title" onclick="toggleMenu(this)">Lokasi Kejadian Banjir <span class="arrow">&#9660;</span></button><div class="sub-bab-content"></div>`;
                        const mhSubContent = mhSubDiv.querySelector('.sub-bab-content');
                        banjirItems.forEach(item => {
                            createMenuItem(item, catName, mhSubContent, 'Kejadian Banjir');
                        });
                        contentDiv.appendChild(mhSubDiv);
                    }
                    if (kekeringanItems.length > 0) {
                        let keringSubDiv = document.createElement('div');
                        keringSubDiv.className = 'sub-bab-container';
                        keringSubDiv.innerHTML = `<button class="sub-bab-title" onclick="toggleMenu(this)">Daerah Rawan Kekeringan <span class="arrow">&#9660;</span></button><div class="sub-bab-content"></div>`;
                        const keringSubContent = keringSubDiv.querySelector('.sub-bab-content');
                        kekeringanItems.forEach(item => {
                            createMenuItem(item, catName, keringSubContent, 'Kekeringan');
                        });
                        contentDiv.appendChild(keringSubDiv);
                    }
                }
                else {
                    catalog[catName].forEach(item => {
                        let isChecked = (catName === "DAS dan Jaringan Sungai") ? "checked" : "";
                        createMenuItem(item, catName, contentDiv, null, isChecked);
                    });
                }
                menuContainer.appendChild(groupDiv);
            }
            
            // ==================== MENU LOKASI KRITIS SIMONA ====================
            const lokasiKritisSimonaGroup = document.createElement('div');
            lokasiKritisSimonaGroup.className = 'menu-group';
            lokasiKritisSimonaGroup.innerHTML = `
                <button class="menu-title" onclick="toggleMenu(this)" style="background: #f39c12;">
                    LOKASI KRITIS SIMONA <span class="arrow">&#9660;</span>
                </button>
                <div class="menu-content">
                    <div class="menu-item">
                        <label>
                            <input type="checkbox" id="lokasi-kritis-simona-checkbox" onchange="toggleLokasiKritisSimona()">
                            <i class="fas fa-chart-line" style="color: #f39c12; width: 24px; text-align: center;"></i>
                            <span>Tampilkan Lokasi Kritis SIMONA</span>
                        </label>
                    </div>
                    <div class="menu-item">
                        <label style="justify-content: center;">
                            <button onclick="zoomToLokasiKritisSimona()" style="background: #3498db; border: none; color: white; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 12px; width: 100%;">
                                <i class="fas fa-search-location"></i> Zoom ke Semua Lokasi
                            </button>
                        </label>
                    </div>
                </div>
            `;
            menuContainer.appendChild(lokasiKritisSimonaGroup);
            
        }).catch(err => console.error("Gagal load sidebar:", err));
    }

    function assignToCategory(fullName, catalog) {
        const lowerName = fullName.toLowerCase();
        let category = "Sumber Daya Alam";
        if (lowerName.includes('aset_tanah')) category = "Aset Tanah";
        else if (lowerName.startsWith('di_')) category = "Daerah Irigasi Pusat";
        else if (lowerName.includes('pemboran')|| lowerName.includes('pemanfaatan') || lowerName.includes('resapan')|| lowerName.includes('aquifer') || lowerName.includes('cat') || lowerName.includes('sempadan')  ) category = "Sumber Daya Air";
        else if (lowerName.includes('bendung') || lowerName.includes('embung') || lowerName.includes('pengendali') || lowerName.includes('pengaman') || lowerName.includes('mata') || lowerName.includes('situ') || lowerName.includes('waduk') || lowerName.includes('sumur')) category = "Infrastruktur";
        else if (lowerName.includes('kewenangan')) category = "Daerah Kewenangan Kabupaten dan Provinsi";
        else if (lowerName.includes('sungai') || lowerName.includes('das')) category = "DAS dan Jaringan Sungai";
        else if (lowerName.startsWith('batas')) category = "Batas Wilayah Administrasi";
        else if (lowerName.includes('banjir') || lowerName.includes('kekeringan')|| lowerName.includes('mh') || lowerName.includes('kritis') || lowerName.includes('gerakan')) category = "Kebencanaan";
        catalog[category].push({ name: fullName });
    }

    function createMenuItem(item, catName, container, subCat = null, isChecked = "") {
        let itemDiv = document.createElement('div');
        itemDiv.className = 'menu-item';
        let isWMS = item.name.toLowerCase().includes('demnas');
        let funcName = isWMS ? 'toggleWMS' : 'toggleWFS';
        let subParam = subCat ? `, '${subCat}'` : '';
        itemDiv.innerHTML = `
            <label>
                <input type="checkbox" ${isChecked} onchange="${funcName}(this, '${item.name}', '${catName}'${subParam})">
                ${getLayerIcon(item.name)}
                <span>${formatTitle(item.name)}</span>
            </label>`;
        container.appendChild(itemDiv);
        if (isChecked === "checked") {
            setTimeout(() => {
                const input = itemDiv.querySelector('input');
                window[funcName](input, item.name, catName);
            }, 200);
        }
    }

    function performGlobalSearch(query) {
        if (!query || query.trim() === "") { 
            document.getElementById('global-search-results').innerHTML = ''; 
            document.getElementById('global-search-results').classList.remove('active'); 
            return; 
        }
        query = query.toLowerCase().trim();
        let results = [];
        
        availableLayersList.forEach(layer => {
            if (layer.title.toLowerCase().includes(query) || layer.name.toLowerCase().includes(query)) {
                if (!results.some(r => r.type === 'layer' && r.name === layer.name)) {
                    results.push({ type: 'layer', title: layer.title, name: layer.name });
                }
            }
        });
        
        allLocationsCache.forEach(loc => {
            let matched = false;
            let matchedName = loc.name;
            
            if (loc.name.toLowerCase().includes(query)) {
                matched = true;
            }
            else if (loc.allNames && loc.allNames.some(val => val.toLowerCase().includes(query))) {
                matched = true;
                let found = loc.allNames.find(val => val.toLowerCase().includes(query));
                if (found) matchedName = found;
            }
            
            if (matched) {
                if (!results.some(r => r.type === 'location' && r.name === matchedName)) {
                    results.push({ type: 'location', title: loc.layerTitle, name: matchedName, coords: loc.coords });
                }
            }
        });
        
        let coord = parseCoordinate(query);
        if (coord) results.push({ type: 'coordinate', title: 'Koordinat', name: `${coord.lat.toFixed(6)}, ${coord.lng.toFixed(6)}`, lat: coord.lat, lng: coord.lng });
        
        let resultDiv = document.getElementById('global-search-results');
        resultDiv.innerHTML = '';
        if (results.length === 0) { 
            resultDiv.innerHTML = '<div class="no-result"><i class="fas fa-info-circle"></i> Tidak ditemukan</div>'; 
        } else {
            results.forEach(res => {
                let item = document.createElement('div'); item.className = 'result-item';
                let icon = res.type === 'layer' ? '<i class="fas fa-layer-group"></i>' : (res.type === 'location' ? '<i class="fas fa-map-marker-alt"></i>' : '<i class="fas fa-globe"></i>');
                item.innerHTML = `${icon}<span style="flex:1;"><b>${res.title}</b> ${res.name ? `<span style="font-size:0.65rem;">- ${res.name}</span>` : ''}</span><span class="result-type">${res.type === 'layer' ? 'Layer' : (res.type === 'location' ? 'Lokasi' : 'Koordinat')}</span>`;
                item.onclick = () => {
                    if (res.type === 'layer') {
                        let allCheckboxes = document.querySelectorAll('#dynamic-menu input[type="checkbox"]');
                        for (let cb of allCheckboxes) {
                            let layerName = cb.getAttribute('onchange')?.match(/'([^']+)'/)?.[1];
                            if (layerName === res.name) { if (!cb.checked) cb.click(); cb.scrollIntoView({ behavior: 'smooth', block: 'center' }); break; }
                        }
                    } else if (res.type === 'location' && res.coords) {
                        clearSearchMarker();
                        searchMarker = L.marker(res.coords, { icon: createSearchIcon() }).addTo(map);
                        searchMarker.bindPopup(`<div style="text-align:center;"><strong>${res.name}</strong><br>${res.title}<br><button onclick="clearSearchMarker()" style="margin-top:8px; padding:4px 12px; background:#e74c3c; color:white; border:none; border-radius:5px; cursor:pointer;">Tutup</button></div>`).openPopup();
                        map.flyTo(res.coords, 14, { duration: 1 });
                    } else if (res.type === 'coordinate') {
                        clearSearchMarker();
                        searchMarker = L.marker([res.lat, res.lng], { icon: createSearchIcon() }).addTo(map);
                        searchMarker.bindPopup(`<div style="text-align:center;"><strong>Koordinat</strong><br>Lat: ${res.lat}<br>Lng: ${res.lng}<br><button onclick="clearSearchMarker()" style="margin-top:8px; padding:4px 12px; background:#e74c3c; color:white; border:none; border-radius:5px; cursor:pointer;">Tutup</button></div>`).openPopup();
                        map.flyTo([res.lat, res.lng], 15, { duration: 1 });
                    }
                };
                resultDiv.appendChild(item);
            });
        }
        resultDiv.classList.add('active');
    }
    
    window.clearSearchMarker = clearSearchMarker;
    window.toggleLokasiKritisSimona = toggleLokasiKritisSimona;
    window.zoomToLokasiKritisSimona = zoomToLokasiKritisSimona;
    
    document.getElementById('global-search-btn').addEventListener('click', () => performGlobalSearch(document.getElementById('global-search-input').value));
    document.getElementById('global-search-input').addEventListener('keypress', (e) => { if (e.key === 'Enter') performGlobalSearch(e.target.value); });
    
    var userMarker, userAccuracy;
    function onLocationFound(e) {
        var radius = e.accuracy / 2;
        if (!userMarker) {
            userMarker = L.marker(e.latlng).addTo(map).bindPopup("Anda berada di sini");
            userAccuracy = L.circle(e.latlng, radius).addTo(map);
        } else {
            userMarker.setLatLng(e.latlng);
            userAccuracy.setLatLng(e.latlng);
            userAccuracy.setRadius(radius);
        }
    }
    map.on('locationfound', onLocationFound);
    map.locate({setView: false, watch: true, enableHighAccuracy: true}); 
    loadSidebarLayers();
</script>
</body>
</html>