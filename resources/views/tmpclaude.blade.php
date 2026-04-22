<!DOCTYPE html>
<html>
<head>
    <title>Geocimancis</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <!-- Preconnect untuk domain eksternal agar DNS lebih cepat -->
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://geo.sisdacimancis.id">
    <!-- Leaflet CSS (critical, langsung load) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Font Awesome dimuat async agar tidak memblokir render -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" media="print" onload="this.media='all'" />
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></noscript>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pupr.png') }}">
    <style>
        /* ===== RESET & BASE ===== */
        *{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
        body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;display:flex;height:100vh;overflow:hidden;background:#1a2a36}

        /* ===== SIDEBAR ===== */
        #sidebar{
            width:85%;max-width:340px;background:#2c3e50;color:#f0f3f8;
            padding:16px 12px;box-shadow:4px 0 15px rgba(0,0,0,.3);
            z-index:1000;overflow-y:auto;
            /* Gunakan translate3d agar pakai GPU, lebih ringan dari translateX */
            transform:translate3d(0,0,0);
            transition:transform .3s ease;
            position:fixed;left:0;top:0;bottom:0;
            /* Percepat scroll di iOS */
            -webkit-overflow-scrolling:touch;
            will-change:transform;
        }
        #sidebar.hidden{transform:translate3d(-100%,0,0)}

        /* ===== TOGGLE BUTTON ===== */
        #toggle-sidebar{
            position:fixed;left:min(85%,340px);top:16px;z-index:1100;
            background:#2c3e50;color:#e2e8f0;border:none;
            padding:10px 12px;border-radius:0 28px 28px 0;cursor:pointer;
            font-size:20px;transition:left .3s ease,background .2s;
            display:flex;align-items:center;justify-content:center;
            box-shadow:2px 2px 10px rgba(0,0,0,.2);width:44px;height:44px;
        }
        #toggle-sidebar:active{background:#1a4a6e}
        #sidebar.hidden+#toggle-sidebar{left:12px;border-radius:40px}

        /* ===== MAP ===== */
        #map{flex-grow:1;width:100%;height:100%;position:relative;z-index:1}

        /* ===== SIDEBAR HEADER ===== */
        .sidebar-header{
            display:flex;align-items:center;justify-content:center;gap:10px;
            border-bottom:1px solid rgba(255,255,255,.15);padding:8px 0 12px;
            margin-bottom:18px;
        }
        #sidebar h2{
            font-size:1rem;border-bottom:1px solid rgba(255,255,255,.15);
            padding-bottom:10px;margin-bottom:18px;color:#fff;
            text-align:center;font-weight:600;
        }

        /* ===== SEARCH ===== */
        .global-search-container{
            background:#2c3e50;border-radius:12px;margin-bottom:20px;
            padding:10px;box-shadow:0 2px 8px rgba(0,0,0,.2);border:1px solid #3a5a7a;
        }
        .global-search-box{
            display:flex;align-items:center;background:#ecf0f1;
            border-radius:40px;padding:6px 12px;gap:8px;
        }
        .global-search-box i{color:#3498db;font-size:14px}
        .global-search-box input{
            flex:1;border:none;background:transparent;padding:8px 0;
            font-size:.85rem;outline:none;color:#2c3e50;
        }
        .global-search-box input::placeholder{color:#7f8c8d;font-size:.75rem}
        .global-search-box button{
            background:#3498db;border:none;color:#fff;border-radius:30px;
            padding:6px 12px;font-size:.7rem;cursor:pointer;font-weight:700;
        }
        .search-results{
            margin-top:10px;max-height:200px;overflow-y:auto;
            background:#34495e;border-radius:10px;display:none;
        }
        .search-results.active{display:block}
        .result-item{
            padding:10px 12px;border-bottom:1px solid #3a5a7a;cursor:pointer;
            font-size:.75rem;display:flex;align-items:center;gap:10px;
        }
        .result-item:hover,.result-item:active{background:#3a5a7a}
        .result-item i{width:20px;color:#3498db}
        .result-type{
            font-size:.6rem;background:#3498db;padding:2px 6px;
            border-radius:20px;color:#fff;margin-left:auto;
        }
        .no-result{padding:10px;text-align:center;color:#bdc3c7;font-size:.7rem}

        /* ===== MENU ===== */
        .menu-group{margin-bottom:12px;border-radius:12px;overflow:hidden;background:#34495e}
        .menu-title{
            width:100%;padding:14px 16px;background:#3498db;color:#fff;
            border:none;text-align:left;cursor:pointer;font-weight:600;
            display:flex;justify-content:space-between;align-items:center;
            text-transform:uppercase;font-size:.7rem;letter-spacing:.5px;
        }
        .menu-content{display:none;padding:10px 8px;background:#34495e}
        .menu-content.active{display:block}

        .sub-bab-container{
            margin-top:8px;border-left:3px solid #3498db;margin-left:4px;
            margin-bottom:10px;background:#2c3e50;border-radius:10px;overflow:hidden;
        }
        .sub-bab-title{
            width:100%;background:#2c3e50;color:#ecf0f1;border:none;
            text-align:left;padding:10px 14px;font-size:.7rem;font-weight:600;
            cursor:pointer;display:flex;justify-content:space-between;align-items:center;
        }
        .sub-bab-content{display:none;padding:8px 6px 10px 12px}
        .sub-bab-content.active{display:block}

        .menu-item{background:#2c3e50;border-radius:10px;margin-bottom:8px}
        .menu-item label{
            font-size:.8rem;cursor:pointer;flex-grow:1;
            padding:12px;display:flex;align-items:center;gap:12px;
            width:100%;margin:0;color:#ecf0f1;
        }
        .menu-item input{
            margin:0;width:20px;height:20px;cursor:pointer;
            flex-shrink:0;accent-color:#3498db;
        }
        .layer-icon{
            width:24px;height:24px;object-fit:contain;flex-shrink:0;
            background:rgba(0,0,0,.2);border-radius:6px;padding:2px;
        }
        .arrow{font-size:.7rem;transition:transform .2s;display:inline-block}

        /* ===== LEGEND ===== */
        .legend{
            background:rgba(255,255,255,.95);padding:0;line-height:1.35;
            color:#333;box-shadow:0 2px 12px rgba(0,0,0,.2);border-radius:12px;
            font-size:10px;min-width:170px;max-width:230px;
            border:1px solid #ddd;overflow:hidden;
        }
        .legend-header{
            display:flex;justify-content:space-between;align-items:center;
            padding:10px 12px;background:#3498db;color:#fff;cursor:pointer;
            font-weight:700;font-size:11px;
        }
        .legend-header i{font-size:12px;transition:transform .2s}
        .legend-content{
            padding:10px 12px;max-height:300px;overflow-y:auto;
            transition:all .2s ease;
        }
        .legend-content.collapsed{display:none}
        .legend-cat-title{
            font-weight:700;color:#2980b9;margin-top:8px;margin-bottom:6px;
            border-bottom:1px solid #eee;text-transform:uppercase;font-size:10px;
        }
        .legend-sub-title{
            font-weight:700;color:#555;margin-left:6px;
            font-size:9px;font-style:italic;
        }
        .legend i{
            width:16px;height:16px;float:left;margin-right:8px;
            opacity:.8;border:1px solid #999;border-radius:3px;margin-top:2px;
        }
        .legend img{
            width:20px;height:20px;float:left;
            margin-right:8px;object-fit:contain;
        }
        .legend-item{
            margin-bottom:5px;margin-left:8px;display:flex;
            align-items:center;clear:both;font-size:9.5px;gap:6px;
        }

        /* ===== POPUP ===== */
        .leaflet-popup-content-wrapper{padding:0;overflow:hidden;border-radius:12px;max-width:85vw}
        .leaflet-popup-content{margin:0!important;width:320px!important;max-width:85vw}
        .popup-header{
            font-weight:700;background:#e74c3c;color:#fff;
            padding:10px;text-align:center;font-size:13px;
        }
        .popup-scroll{
            max-height:300px;overflow-y:auto;padding:8px 12px 12px;
            background:#fff;-webkit-overflow-scrolling:touch;
        }
        .popup-table{font-size:11px;border-collapse:collapse;width:100%;table-layout:fixed}
        .popup-table td{border:1px solid #eee;padding:6px;word-wrap:break-word;vertical-align:top}

        /* ===== ZOOM CONTROL ===== */
        .leaflet-control-zoom{
            position:fixed!important;top:90px!important;right:10px!important;
            left:auto!important;bottom:auto!important;z-index:400!important;
        }

        /* ===== TOAST ===== */
        #toast-container{
            position:fixed;bottom:20px;left:50%;transform:translateX(-50%);
            z-index:10000;display:flex;flex-direction:column;gap:10px;pointer-events:none;
        }
        .toast{
            background:#3498db;color:#fff;padding:12px 20px;border-radius:8px;
            font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.15);
            pointer-events:auto;min-width:200px;text-align:center;
            /* Hapus animasi slideUp untuk performa - ganti opacity sederhana */
            animation:fadeIn .2s ease;
        }
        .toast.success{background:#27ae60}
        .toast.error{background:#e74c3c}
        .toast.warning{background:#f39c12}
        @keyframes fadeIn{from{opacity:0}to{opacity:1}}

        /* ===== SEARCH MARKER PULSE (lebih ringan) ===== */
        @keyframes pulse{
            0%{transform:scale(1);opacity:.7}
            100%{transform:scale(2.5);opacity:0}
        }

        /* ===== RESPONSIVE ===== */
        @media(max-width:560px){
            .menu-item label{font-size:.73rem;padding:10px;gap:8px}
            .menu-title{font-size:.65rem;padding:12px 14px}
            .legend{max-width:190px;font-size:9px}
            .legend-content{max-height:250px}
            #toggle-sidebar{width:40px;height:40px;padding:8px;font-size:18px}
        }

        /* ===== SCROLLBAR ===== */
        #sidebar::-webkit-scrollbar{width:4px}
        #sidebar::-webkit-scrollbar-track{background:#2c3e50}
        #sidebar::-webkit-scrollbar-thumb{background:#5d7e9a;border-radius:8px}
    </style>
</head>
<body>

<div id="sidebar">
    <div class="sidebar-header">
        <img src="{{ asset('images/logo-pupr.png') }}" alt="Logo PUPR" style="width:32px;height:auto" loading="lazy">
        <h2 style="font-size:1rem;margin:0;padding:0;border-bottom:none;text-align:left">GIS GEOCIMANCIS</h2>
    </div>
    <div class="global-search-container">
        <div class="global-search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="global-search-input" placeholder="Cari layer atau lokasi...">
            <button id="global-search-btn">Cari</button>
        </div>
        <div id="global-search-results" class="search-results"></div>
    </div>
    <div id="dynamic-menu">
        <p style="font-size:.8rem;text-align:center;color:#bdc3c7">Memuat daftar data...</p>
    </div>
</div>

<button id="toggle-sidebar"><i class="fas fa-chevron-left"></i></button>
<div id="map"></div>

<!-- Leaflet JS dimuat async -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
<script>
// Tunggu Leaflet selesai load sebelum init
document.querySelector('script[src*="leaflet"]').addEventListener('load', initApp);

function initApp() {
    // ===== MAP INIT =====
    var map = L.map('map', {
        center:[-6.722,108.556], zoom:11,
        zoomAnimation:true, markerZoomAnimation:true,
        zoomSnap:.5, zoomDelta:.5,
        attributionControl:false, zoomControl:false,
        // Optimalkan tile loading di mobile
        preferCanvas:true  // Render dengan Canvas, lebih ringan dari SVG untuk banyak feature
    });

    L.control.zoom({position:'topright'}).addTo(map);

    // Tile dengan maxNativeZoom agar tidak download tile resolusi berlebihan
    var googleSat = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{
        maxZoom:20, maxNativeZoom:18,
        subdomains:['mt0','mt1','mt2','mt3'],
        keepBuffer:1,       // Kurangi buffer tile agar hemat memori
        updateWhenIdle:true // Hanya update tile saat panning berhenti
    }).addTo(map);

    var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
        maxZoom:19, maxNativeZoom:18,
        keepBuffer:1, updateWhenIdle:true
    });

    L.control.attribution({prefix:false}).addAttribution('Google Maps').addTo(map);
    var baseMaps = {"Google Satellite":googleSat,"OpenStreetMap":osm};
    L.control.layers(baseMaps, null, {position:'topright'}).addTo(map);

    // ===== STATE =====
    var activeLayers = {};
    var layerInfoForLegend = {};
    var searchMarker = null;
    var allLocationsCache = [];
    var availableLayersList = [];
    var preloadCompleted = false;
    // Cache WFS yang sudah pernah diload agar tidak fetch ulang
    var wfsDataCache = {};

    // ===== TOAST =====
    // Singleton container
    var toastContainer = document.createElement('div');
    toastContainer.id = 'toast-container';
    document.body.appendChild(toastContainer);

    function showToast(message, type) {
        type = type || 'info';
        var toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.textContent = message;
        toastContainer.appendChild(toast);
        setTimeout(function(){
            toast.style.opacity = '0';
            toast.style.transition = 'opacity .3s';
            setTimeout(function(){ if(toast.parentNode) toast.parentNode.removeChild(toast); }, 300);
        }, 3000);
    }

    // ===== KOORDINAT PARSER =====
    function parseCoordinate(s) {
        s = s.trim().replace(/\s+/g,' ');
        var m = s.match(/^(-?\d+(?:\.\d+)?)[,\s]+(-?\d+(?:\.\d+)?)$/);
        if(m){
            var lat = parseFloat(m[1]), lng = parseFloat(m[2]);
            if(!isNaN(lat)&&!isNaN(lng)&&lat>=-90&&lat<=90&&lng>=-180&&lng<=180)
                return {lat:lat,lng:lng};
        }
        return null;
    }

    // ===== ICONS =====
    function createSearchIcon() {
        // Gunakan divIcon sederhana tanpa animasi berat
        return L.divIcon({
            html:'<i class="fas fa-map-marker-alt" style="color:#e74c3c;font-size:32px;text-shadow:0 2px 4px rgba(0,0,0,.3)"></i>',
            className:'search-marker',
            iconSize:[32,32], iconAnchor:[16,32], popupAnchor:[0,-32]
        });
    }

    function clearSearchMarker() {
        if(searchMarker){ map.removeLayer(searchMarker); searchMarker = null; }
    }

    // ===== SIDEBAR TOGGLE =====
    document.getElementById('toggle-sidebar').addEventListener('click', function(){
        var sb = document.getElementById('sidebar');
        sb.classList.toggle('hidden');
        var icon = this.querySelector('i');
        icon.className = sb.classList.contains('hidden') ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
        setTimeout(function(){ map.invalidateSize(); }, 350);
    });

    // ===== WARNA & ICON MAPPING =====
    var classificationColors = {
        'baku':'#006400','fungsional':'#7CFC00','potensial':'#32CD32','bangunan':'#754c4c',
        'hutan lindung':'#228B22','hutan produksi':'#90EE90','hutan produksi terbatas':'#32CD32',
        'hutan produksi tetap':'#006400','alur aliran bahan rombakan':'#FFAA00','danau':'#0070FF',
        'sangat rendah':'#D3FFBE','rendah':'#AAFF00','menengah':'#FFFF00','tinggi':'#FF0000',
        'landai':'#eedbae','datar':'#f1a12f','agak curam':'#fc8c8c','curam':'#ff1c1c','sangat curam':'#b70101',
        'aquifer produktif dengan penyebaran luas':'#E6A1CD','daerah air tanah langka':'#7193AE',
        'setempat aquifer produktif':'#9A73DE','aquifer produktif kecil setempat berarti':'#A28E69',
        'aquifer produktif sedang dengan penyebaran luas':'#B5E697','aquifer produktif tinggi dengan penyebaran luas':'#E77978',
        'cat bandung':'#00E6A9','cat garut':'#FF0000','cat indramayu':'#2892C7','cat kuningan':'#95BD9F',
        'cat majalengka':'#FA8532','cat malangbong':'#68A6B2','cat sukamantri':'#FFFFBE',
        'cat sumber-cirebon':'#E6E600','cat sumedang':'#BF9556','cat tasikmalaya':'#FF73DF',
        'cat tegal-brebes':'#BFD48A','non cat':'#E1E1E1',
        'tidak terjadi banjir':'#BED2FF','kerawanan rendah':'#FFBEBE',
        'kerawanan sedang':'#FF7F7F','kerawanan tinggi':'#A80000',
        'lokasi kritis':'#800000','batuan lempung bermasalah':'#7CB342',
        'dataran aluvial':'#3BA0BC','dataran kaki vulkan':'#B7FF93','dataran marine':'#93C3FF',
        'kaki vulkan':'#FFBB9C','kepundan':'#933B3A','kerucut parasiter':'#BB673B',
        'kerucut vulkan':'#EB3B3B','lereng vulkan':'#FF9C9C','medan lava':'#FFFF3B',
        'pegunungan denudasional lereng curam':'#FF7C3C',
        'pegunungan denudasional lereng sangat curam':'#BB913B',
        'pegunungan struktural lereng curam':'#FF3BD1',
        'pegunungan struktural lereng sangat curam':'#933B75',
        'perbukitan denudasional':'#EDB03B',
        'aset_tanah_brebes':'#26ec0c','aset_tanah_cirebon':'#ec9e0c',
        'aset_tanah_cirebon_kota':'#00FFFF','aset_tanah_indramayu':'#FF00FF',
        'aset_tanah_majalengka':'#FFA500','aset_tanah_sumedang':'#800080',
        'risiko rendah':'#96f13a','risiko sedang':'#fffb00',
        'risiko tinggi':'#ff9900','risiko sangat tinggi':'#ff0202',
        'saluran induk':'#000000','saluran primer':'#ff0000',
        'saluran sekunder':'#ffea00','saluran tersier':'#0015f8',
        'saluran terrsier':'#0015f8','saluran pembuang':'#93f2f2',
        'saluran suplesi':'#ea6500','waduk':'#0070FF'
    };

    var iconMapping = {
        'bendungan':'bendungan.png','bendung':'bendung.png','sumur':'sumur.png',
        'embung':'embung.png','situ':'situ.png',
        'pengendali_sedimen':'pengendalisedimen.png',
        'pengendali_banjir':'pengendali%20banjir.png',
        'irigasi':'bangunan_irigasi.png','pantai':'bangunanpantai.png',
        'mata_air':'mata_air.png','klimatologi':'pos_klimatologi.png',
        'duga_air':'pos_duga_air.png','curah_hujan':'pos_curah_hujan.png'
    };

    function getLayerIconPath(layerName) {
        var name = layerName.toLowerCase();
        for(var key in iconMapping){ if(name.includes(key)) return iconMapping[key]; }
        return null;
    }
    function getLayerIcon(layerName) {
        var path = getLayerIconPath(layerName);
        return path ? '<img src="icon/'+path+'" class="layer-icon" loading="lazy" onerror="this.style.display=\'none\'">' : '';
    }

    function formatTitle(text) {
        var lower = text.toLowerCase();
        if(lower==='das_cimanggis_ar') return "Batas 25 Das Cimanuk Cisanggarung";
        if(lower.includes('sungai_orde_ln')) return "Jaringan Sungai";
        if(lower.includes('waduk_ar')) return "Waduk";
        if(lower.includes('banjir')) return "Rawan Banjir";
        if(lower.includes('sumur_air_tanah_2026')) return "Sumur Air Tanah";
        if(lower.includes('demnas')) return "Digital Elevation Model Nasional (BIG)";
        if(lower.includes('kekeringan')){
            var kabMap = {cirebon:'Cirebon',brebes:'Brebes',indramayu:'Indramayu',
                         majalengka:'Majalengka',kuningan:'Kuningan',sumedang:'Sumedang',garut:'Garut'};
            for(var k in kabMap){ if(lower.includes(k)) return "Rawan Kekeringan "+kabMap[k]; }
            return "Rawan Kekeringan";
        }
        if(lower==='lokasi_kritis_simona') return "Titik Lokasi Kritis";
        if(lower.includes('lokasi_kritis')) return "Lokasi Kritis";
        if(lower==='aquifer_cimancis_ar') return "Aquifer";
        if(lower==='geologi_regional_250k') return "Geologi Regional";
        if(lower==='geomorfologi_cimancis') return "Geomorfologi";
        return text.replace(/di_/gi,'').replace(/_/g,' ').replace(/\b\w/g,function(l){return l.toUpperCase();});
    }

    // ===== LEGEND =====
    function updateLegend() {
        var content = document.getElementById('legend-content');
        if(!content) return;
        var layerKeys = Object.keys(activeLayers);
        if(layerKeys.length===0){
            content.innerHTML = '<span style="color:#888;font-style:italic">Pilih layer...</span>';
            return;
        }
        var structured = {};
        layerKeys.forEach(function(key){
            var info = layerInfoForLegend[key];
            if(!info) return;
            if(!structured[info.category]) structured[info.category]={};
            var sub = info.subCategory||"General";
            if(!structured[info.category][sub]) structured[info.category][sub]=[];
            structured[info.category][sub].push({name:key,details:info});
        });
        var html = '';
        for(var cat in structured){
            html += '<div class="legend-cat-title">'+cat+'</div>';
            for(var sub in structured[cat]){
                if(sub!=="General") html += '<div class="legend-sub-title">'+sub+'</div>';
                structured[cat][sub].forEach(function(item){
                    var info = item.details;
                    if(info.type==='icon'){
                        html += '<div class="legend-item"><img src="icon/'+info.icon+'" loading="lazy"><span>'+formatTitle(item.name)+'</span></div>';
                    } else if(info.type==='classified'){
                        html += '<div style="margin-left:10px;font-weight:600;font-size:10px">'+formatTitle(item.name)+'</div>';
                        info.classes.forEach(function(cls){
                            html += '<div class="legend-item" style="margin-left:20px"><i style="background:'+cls.color+'"></i><span>'+cls.label+'</span></div>';
                        });
                    } else if(info.type==='wms-legend'){
                        html += '<div style="margin-left:10px;font-weight:600;font-size:10px">'+formatTitle(item.name)+'</div>';
                        html += '<div class="legend-item" style="margin-left:20px"><img src="'+info.legendUrl+'" style="width:auto;height:auto;max-width:180px" loading="lazy"></div>';
                    } else {
                        html += '<div class="legend-item"><i style="background:'+info.color+'"></i><span>'+formatTitle(item.name)+'</span></div>';
                    }
                });
            }
        }
        content.innerHTML = html;
    }

    var legend = L.control({position:'bottomright'});
    legend.onAdd = function(){
        var div = L.DomUtil.create('div','legend');
        div.innerHTML = '<div class="legend-header" onclick="toggleLegend()"><span><i class="fas fa-list-ul"></i> LEGENDA</span><i id="legend-toggle-icon" class="fas fa-chevron-up"></i></div><div id="legend-content" class="legend-content"></div>';
        L.DomEvent.disableScrollPropagation(div);
        L.DomEvent.disableClickPropagation(div);
        return div;
    };
    legend.addTo(map);

    window.toggleLegend = function(){
        var content = document.getElementById('legend-content');
        var icon = document.getElementById('legend-toggle-icon');
        if(content){
            content.classList.toggle('collapsed');
            if(icon) icon.className = content.classList.contains('collapsed') ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
        }
    };

    function toggleMenu(btn) {
        var content = btn.nextElementSibling;
        var arrow = btn.querySelector('.arrow');
        content.classList.toggle('active');
        if(arrow) arrow.style.transform = content.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0deg)';
    }

    // ===== MARKER =====
    function createMarkerIcon(color, layerName) {
        var iconFile = getLayerIconPath(layerName);
        if(iconFile) return L.icon({iconUrl:'icon/'+iconFile,iconSize:[28,28],iconAnchor:[14,14],popupAnchor:[0,-14]});
        // Gunakan CircleMarker via divIcon yang lebih ringan dari SVG pin penuh
        return L.divIcon({
            html:'<div style="width:14px;height:14px;background:'+color+';border:2px solid #fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,.4)"></div>',
            className:'',
            iconSize:[14,14], iconAnchor:[7,7], popupAnchor:[0,-7]
        });
    }

    // ===== FEATURE STYLE =====
    function getFeatureStyle(feature, layerName) {
        var color = "#65f176";
        var fOpacity = 0.6, lWeight = 1.5;
        var ll = layerName.toLowerCase();

    if(ll.includes('jaringan') || ll.includes('saluran') || ll.includes('sungai')){
        lWeight = 3;
        var props = feature.properties || {};
        
        // BACA DARI BERBAGAI KEMUNGKINAN NAMA PROPERTI
        var saluranType = props.saluran || props.Saluran || props.SALURAN || 
                         props.n_aset || props.N_Aset || props.N_ASET || 
                         props.nama || props.Nama || props.NAMA ||
                         props.jenis || props.Jenis || props.JENIS ||
                         props.tipe || props.Tipe || props.TIPE ||
                         props.kategori || props.Kategori || props.KATEGORI ||
                         '';
        
        var typeStr = String(saluranType).toLowerCase().trim();
        
        // DEBUG: Lihat di console apa yang terbaca
        console.log('Layer:', layerName, 'Tipe saluran:', typeStr, 'Properties:', Object.keys(props));
        
        if(typeStr.includes('induk')) { 
            color = classificationColors['saluran induk'];   
            lWeight = 4.5; 
        }
        else if(typeStr.includes('primer')) { 
            color = classificationColors['saluran primer'];  
            lWeight = 3.5; 
        }
        else if(typeStr.includes('sekunder')) { 
            color = classificationColors['saluran sekunder'];
            lWeight = 2.5; 
        }
        else if(typeStr.includes('tersier') || typeStr.includes('terrsier')) { 
            color = classificationColors['saluran tersier']; 
            lWeight = 1.5; 
        }
        else if(typeStr.includes('pembuang')) { 
            color = classificationColors['saluran pembuang']; 
        }
        else if(typeStr.includes('suplesi')) { 
            color = classificationColors['saluran suplesi'];  
        }
        else if(typeStr.includes('induk') === false && typeStr !== '') {
            // Jika ada nilai tapi tidak dikenali, tampilkan warning di console
            console.warn('Tipe saluran tidak dikenali:', typeStr);
        }
    }
        if(ll.includes('sempadan')){
            return {color:'#12d0f1',weight:3,fillOpacity:.1,opacity:1,fillColor:'#12d0f1'};
        }
        if(classificationColors[ll]){ color=classificationColors[ll]; }
        else if(ll.includes('lokasi_kritis')){ color='#800000'; fOpacity=.7; }
        else if(ll.includes('baku'))        { color=classificationColors['baku'];     fOpacity=.3; }
        else if(ll.includes('fungsional'))  { color=classificationColors['fungsional'];fOpacity=.3; }
        else if(ll.includes('potensial'))   { color=classificationColors['potensial']; fOpacity=.5; }
        else if(ll.includes('bangunan'))    { color=classificationColors['bangunan']; }
        else if(ll.includes('waduk_ar'))    { color='#0070FF'; fOpacity=.6; }
        else {
            for(var prop in feature.properties){
                var val = String(feature.properties[prop]).toLowerCase().replace(/,/g,'').replace(/\s+/g,' ').replace(/\(.*\)/g,'').trim();
                if(classificationColors[val]){ color=classificationColors[val]; break; }
            }
        }
        if(ll==='das_cimanggis_ar'){ color="#FF7F7F"; fOpacity=0; lWeight=3; }
        else if(ll.includes('sungai')){ color="#3498db"; lWeight=2; }
        return {color:color,weight:lWeight,fillOpacity:fOpacity,opacity:1,fillColor:color};
    }

    // ===== WMS =====
    function toggleWMS(checkbox, layerName, category) {
        if(checkbox.checked){
            var wmsLayer = L.tileLayer.wms("https://geo.sisdacimancis.id/geoserver/geocimancis/wms",{
                layers:'geocimancis:'+layerName,
                format:'image/png',transparent:true,
                version:'1.1.1',srs:'EPSG:3857',
                tiled:true,tilesOrigin:'107.69,-7.42',
                attribution:"GeoCimancis WMS",
                updateWhenIdle:true,   // Hemat bandwidth di mobile
                keepBuffer:1
            }).addTo(map);
            activeLayers[layerName] = wmsLayer;
            layerInfoForLegend[layerName] = {
                category:category, type:'wms-legend',
                legendUrl:'https://geo.sisdacimancis.id/geoserver/geocimancis/wms?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&LAYER=geocimancis:'+layerName
            };
            updateLegend();
        } else {
            if(activeLayers[layerName]){ map.removeLayer(activeLayers[layerName]); delete activeLayers[layerName]; delete layerInfoForLegend[layerName]; updateLegend(); }
        }
    }

    // ===== WFS (dengan cache) =====
    function toggleWFS(checkbox, layerName, category, subCategory) {
        subCategory = subCategory || null;
        if(checkbox.checked){
            loadWFSLayer(layerName, category, subCategory);
        } else if(activeLayers[layerName]){
            map.removeLayer(activeLayers[layerName]);
            delete activeLayers[layerName];
            delete layerInfoForLegend[layerName];
            updateLegend();
        }
    }

    function loadWFSLayer(layerName, category, subCategory) {
        // Jika sudah di-cache, langsung render ulang tanpa fetch
        if(wfsDataCache[layerName]){
            renderWFSData(wfsDataCache[layerName], layerName, category, subCategory);
            return;
        }
        var url = "https://geo.sisdacimancis.id/geoserver/geocimancis/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=geocimancis:"+layerName+"&outputFormat=application/json&srsName=EPSG:4326";
        fetch(url)
            .then(function(res){ return res.json(); })
            .then(function(data){
                if(!data.features||data.features.length===0) return;
                wfsDataCache[layerName] = data; // simpan ke cache
                renderWFSData(data, layerName, category, subCategory);
            })
            .catch(function(err){ console.error("Gagal memuat WFS:", layerName, err); });
    }

    function renderWFSData(data, layerName, category, subCategory) {
        var info = {category:category, subCategory:subCategory};
        var iconPath = getLayerIconPath(layerName);
        if(iconPath){ info.type='icon'; info.icon=iconPath; }
        else {
            var firstStyle = getFeatureStyle(data.features[0]||{}, layerName);
            info.type='single'; info.color=firstStyle.color;
            var classes = {};
            var isJaringan = layerName.toLowerCase().includes('jaringan')||layerName.toLowerCase().includes('saluran');
            data.features.forEach(function(f){
                for(var prop in f.properties){
                    var rawVal = String(f.properties[prop]);
                    var cleanVal = rawVal.toLowerCase().replace(/,/g,'').replace(/\s+/g,' ').replace(/\(.*\)/g,'').trim();
                    if(isJaringan){
                        if(cleanVal.includes('induk'))   classes['saluran induk']  ={color:classificationColors['saluran induk'],  label:'Saluran Induk'};
                        else if(cleanVal.includes('primer'))  classes['saluran primer'] ={color:classificationColors['saluran primer'], label:'Saluran Primer'};
                        else if(cleanVal.includes('sekunder'))classes['saluran sekunder']={color:classificationColors['saluran sekunder'],label:'Saluran Sekunder'};
                        else if(cleanVal.includes('tersier')) classes['saluran tersier'] ={color:classificationColors['saluran tersier'], label:'Saluran Tersier'};
                        else if(classificationColors[cleanVal]) classes[cleanVal]={color:classificationColors[cleanVal],label:rawVal};
                    } else {
                        if(classificationColors[cleanVal]) classes[cleanVal]={color:classificationColors[cleanVal],label:rawVal};
                    }
                }
            });
            if(Object.keys(classes).length>1){ info.type='classified'; info.classes=Object.values(classes); }
        }
        layerInfoForLegend[layerName] = info;

        var geoJsonLayer = L.geoJson(data, {
            // preferCanvas diset di map init, tidak perlu per-layer
            style:function(f){ return getFeatureStyle(f,layerName); },
            pointToLayer:function(f,latlng){
                return L.marker(latlng,{icon:createMarkerIcon(getFeatureStyle(f,layerName).color, layerName)});
            },
            onEachFeature:function(f,layer){
                var props = f.properties;
                var displayTitle = props.Nama||props.nama||props.N_Aset||props.n_aset;
                if(!displayTitle||displayTitle==="-"||displayTitle==="0"){
                    displayTitle = props.saluran||props.Saluran||formatTitle(layerName);
                }
                var rows = "";
                for(var key in props){
                    if(key.toLowerCase().includes('id')) continue;
                    rows += '<tr><td style="width:100px"><b>'+formatTitle(key)+'</b></td><td>'+(props[key]||"-")+'</td></tr>';
                }
                layer.bindPopup('<div class="popup-header">'+displayTitle.toUpperCase()+'</div><div class="popup-scroll"><table class="popup-table">'+rows+'</table></div>');
            }
        });
        activeLayers[layerName] = geoJsonLayer;
        geoJsonLayer.addTo(map);
        if(layerName.toLowerCase().includes('jaringan')) geoJsonLayer.bringToFront();
        updateLegend();
        if(geoJsonLayer.getBounds().isValid())
            map.flyToBounds(geoJsonLayer.getBounds(),{padding:[50,50],duration:1.5});
    }

    // ===== PRELOAD DATA (throttled agar tidak banjir request) =====
    function preloadAllData(layerNames) {
        var total = layerNames.length;
        var loaded = 0;
        var concurrencyLimit = 3; // Maks 3 request sekaligus di mobile
        var queue = layerNames.slice();

        function processNext() {
            if(queue.length===0) return;
            var layerName = queue.shift();
            var url = "https://geo.sisdacimancis.id/geoserver/geocimancis/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=geocimancis:"+layerName+"&outputFormat=application/json&srsName=EPSG:4326";
            fetch(url)
                .then(function(r){ return r.json(); })
                .then(function(data){
                    if(data.features&&data.features.length>0){
                        // Simpan ke cache sekalian
                        wfsDataCache[layerName] = data;
                        data.features.forEach(function(f){
                            var allValues = [], primaryName = null;
                            for(var key in f.properties){
                                var val = f.properties[key];
                                if(val&&typeof val==='string'&&val!=="-"&&val!=="0"&&val.length>2){
                                    allValues.push(val);
                                    var kl = key.toLowerCase();
                                    if((kl==='nama'||kl==='n_aset'||kl==='saluran'||kl.includes('waduk')||kl.includes('bendung')||kl.includes('lokasi')||kl==='namobj'||kl==='aset')&&!primaryName)
                                        primaryName = val;
                                }
                            }
                            if(!primaryName&&allValues.length>0) primaryName=allValues[0];
                            if(primaryName&&allValues.length>0){
                                var coords=null, g=f.geometry;
                                if(g.type==='Point') coords=[g.coordinates[1],g.coordinates[0]];
                                else if(g.type==='Polygon'){ try{coords=[g.coordinates[0][0][1],g.coordinates[0][0][0]];}catch(e){} }
                                else if(g.type==='MultiPolygon'){ try{coords=[g.coordinates[0][0][0][1],g.coordinates[0][0][0][0]];}catch(e){} }
                                if(coords) allLocationsCache.push({name:String(primaryName),allNames:allValues,layerTitle:formatTitle(layerName),coords:coords});
                            }
                        });
                    }
                })
                .catch(function(){})
                .finally(function(){
                    loaded++;
                    if(loaded===total){
                        preloadCompleted=true;
                        var si = document.getElementById('global-search-input');
                        if(si) si.placeholder='Cari ('+allLocationsCache.length+' lokasi tersedia)...';
                    }
                    processNext();
                });
        }
        // Mulai sejumlah concurrencyLimit workers
        for(var i=0;i<Math.min(concurrencyLimit,queue.length);i++) processNext();
    }

    // ===== LOKASI KRITIS SIMONA =====
    var lokasiKritisSimona = null;
    var lokasiKritisSimonaMarkers = [];

    function getColorByTingkatKerusakan(t) {
        if(!t) return '#3498db';
        t = t.toLowerCase();
        if(t.includes('berat')) return '#e74c3c';
        if(t.includes('sedang')) return '#f1c40f';
        return '#3498db';
    }

    function getSimonaIcon(tk) {
        var color = getColorByTingkatKerusakan(tk);
        var oc = color==='#e74c3c'?'rgba(231,76,60,.5)':(color==='#f1c40f'?'rgba(241,196,15,.5)':'rgba(52,152,219,.5)');
        return L.divIcon({
            html:'<div style="position:relative"><div style="width:14px;height:14px;background:'+color+';border:2px solid #fff;border-radius:50%;box-shadow:0 0 4px rgba(0,0,0,.3);z-index:2"></div><div style="position:absolute;top:-2px;left:-2px;width:18px;height:18px;background:'+oc+';border-radius:50%;animation:pulse 1.5s infinite;z-index:1"></div></div>',
            className:'lokasi-kritis-simona-marker',
            iconSize:[14,14], iconAnchor:[7,7], popupAnchor:[0,-7]
        });
    }

    function loadLokasiKritisSimona() {
        showToast('Memuat data Lokasi Kritis SIMONA...','info');
        fetch('/api.php/api/lokasi-kritis-mysql')
            .then(function(r){ return r.json(); })
            .then(function(data){
                var features = [];
                if(data.type==='FeatureCollection'&&data.features) features=data.features;
                else if(data.data&&Array.isArray(data.data)){
                    features=data.data.map(function(i){
                        return {type:'Feature',geometry:{type:'Point',coordinates:[parseFloat(i.koordinat_y),parseFloat(i.koordinat_x)]},properties:i};
                    });
                } else if(Array.isArray(data)){
                    features=data.map(function(i){
                        return {type:'Feature',geometry:{type:'Point',coordinates:[parseFloat(i.koordinat_y),parseFloat(i.koordinat_x)]},properties:i};
                    });
                }
                if(features.length>0){
                    if(lokasiKritisSimona){ map.removeLayer(lokasiKritisSimona); lokasiKritisSimonaMarkers=[]; }
                    lokasiKritisSimona = L.geoJson({type:'FeatureCollection',features:features},{
                        pointToLayer:function(f,latlng){
                            return L.marker(latlng,{icon:getSimonaIcon(f.properties.tingkat_kerusakan)});
                        },
                        onEachFeature:function(f,layer){
                            var props=f.properties, rows='';
                            var fields=[
                                {label:'Kode',key:'kode'},{label:'Perihal',key:'perihal'},
                                {label:'DAS',key:'das'},{label:'Sungai',key:'sungai'},
                                {label:'Desa',key:'desa'},{label:'Kecamatan',key:'kecamatan'},
                                {label:'Kab/Kota',key:'kab_kota'},{label:'Tingkat Kerusakan',key:'tingkat_kerusakan'},
                                {label:'Prioritas',key:'prioritas_penanganan'},{label:'Status',key:'status'},
                                {label:'Keterangan',key:'keterangan'}
                            ];
                            fields.forEach(function(field){
                                if(props[field.key]&&props[field.key]!==''&&props[field.key]!=='-')
                                    rows+='<tr><td style="width:100px"><b>'+field.label+'</b></td><td>'+props[field.key]+'</td></tr>';
                            });
                            var title=props.kode||props.perihal||'Lokasi Kritis SIMONA';
                            layer.bindPopup('<div class="popup-header" style="background:#f39c12"><i class="fas fa-chart-line"></i> '+title+'</div><div class="popup-scroll"><table class="popup-table">'+rows+'</table></div>');
                            lokasiKritisSimonaMarkers.push(layer);
                        }
                    });
                    lokasiKritisSimona.addTo(map);
                    activeLayers['lokasi_kritis_simona']=lokasiKritisSimona;
                    layerInfoForLegend['lokasi_kritis_simona']={
                        category:'LOKASI KRITIS SIMONA',type:'classified',
                        classes:[
                            {color:'#e74c3c',label:'Tingkat Kerusakan Berat'},
                            {color:'#f1c40f',label:'Tingkat Kerusakan Sedang'},
                            {color:'#3498db',label:'Tingkat Kerusakan Ringan'}
                        ]
                    };
                    updateLegend();
                    showToast('Berhasil memuat '+features.length+' titik Lokasi Kritis SIMONA','success');
                } else {
                    showToast('Tidak ada data Lokasi Kritis SIMONA','warning');
                }
            })
            .catch(function(e){
                console.error(e);
                showToast('Gagal memuat data Lokasi Kritis SIMONA','error');
            });
    }

    function toggleLokasiKritisSimona() {
        var cb = document.getElementById('lokasi-kritis-simona-checkbox');
        if(lokasiKritisSimona&&map.hasLayer(lokasiKritisSimona)){
            map.removeLayer(lokasiKritisSimona);
            if(cb) cb.checked=false;
            delete activeLayers['lokasi_kritis_simona'];
            delete layerInfoForLegend['lokasi_kritis_simona'];
            updateLegend();
            showToast('Layer Lokasi Kritis SIMONA disembunyikan','info');
        } else {
            loadLokasiKritisSimona();
            if(cb) cb.checked=true;
        }
    }

    function zoomToLokasiKritisSimona() {
        if(lokasiKritisSimonaMarkers.length>0){
            var group = L.featureGroup(lokasiKritisSimonaMarkers);
            map.fitBounds(group.getBounds(),{padding:[50,50]});
            showToast('Menampilkan '+lokasiKritisSimonaMarkers.length+' titik Lokasi Kritis SIMONA','info');
        } else {
            showToast('Tidak ada marker Lokasi Kritis SIMONA. Aktifkan layer terlebih dahulu.','warning');
        }
    }

    // ===== SIDEBAR LOADER =====
    function loadSidebarLayers() {
        var capabilitiesUrl = "https://geo.sisdacimancis.id/geoserver/geocimancis/ows?service=WFS&version=1.1.0&request=GetCapabilities";
        var mainCategories = ["DAS dan Jaringan Sungai","Batas Wilayah Administrasi","Infrastruktur",
            "Daerah Kewenangan Kabupaten dan Provinsi","Daerah Irigasi Pusat","Kebencanaan",
            "Aset Tanah","Sumber Daya Alam","Sumber Daya Air"];

        fetch(capabilitiesUrl)
            .then(function(r){ return r.text(); })
            .then(function(str){ return new DOMParser().parseFromString(str,"text/xml"); })
            .then(function(data){
                var layers = data.getElementsByTagName("FeatureType");
                var menuContainer = document.getElementById('dynamic-menu');
                menuContainer.innerHTML='';
                var catalog = {};
                mainCategories.forEach(function(c){ catalog[c]=[]; });
                availableLayersList = [];
                var allLayerNames = [];

                Array.from(layers).forEach(function(layer){
                    var fullName = layer.getElementsByTagName("Name")[0].textContent.replace('geocimancis:','');
                    availableLayersList.push({name:fullName,title:formatTitle(fullName)});
                    allLayerNames.push(fullName);
                    assignToCategory(fullName,catalog);
                });

                var manualRasterLayers = ["DEMNAS_Cimancis_UTM"];
                manualRasterLayers.forEach(function(name){
                    assignToCategory(name,catalog);
                    availableLayersList.push({name:name,title:formatTitle(name)});
                    allLayerNames.push(name);
                });

                preloadAllData(allLayerNames);

                for(var catName in catalog){
                    if(catalog[catName].length===0) continue;
                    var groupDiv = document.createElement('div');
                    groupDiv.className='menu-group';
                    var isOpen = (catName==="DAS dan Jaringan Sungai")?"active":"";
                    groupDiv.innerHTML='<button class="menu-title" onclick="toggleMenu(this)">'+catName+' <span class="arrow">&#9660;</span></button><div class="menu-content '+isOpen+'"></div>';
                    var contentDiv = groupDiv.querySelector('.menu-content');

                    if(catName==="Daerah Irigasi Pusat"){
                        var subGroups={};
                        catalog[catName].forEach(function(item){
                            var parts=item.name.split('_');
                            var region=parts[1]?parts[1].toUpperCase():"LAINNYA";
                            if(!subGroups[region]) subGroups[region]=[];
                            subGroups[region].push(item);
                        });
                        for(var region in subGroups){
                            var subDiv=document.createElement('div');
                            subDiv.className='sub-bab-container';
                            subDiv.innerHTML='<button class="sub-bab-title" onclick="toggleMenu(this)">DI '+region+' <span class="arrow">&#9660;</span></button><div class="sub-bab-content"></div>';
                            var subContent=subDiv.querySelector('.sub-bab-content');
                            subGroups[region].forEach(function(item){ createMenuItem(item,catName,subContent,'DI '+region); });
                            contentDiv.appendChild(subDiv);
                        }
                    } else if(catName==="Kebencanaan"){
                        var banjirItems=[],kekeringanItems=[],otherItems=[];
                        var kabRe=/cirebon|brebes|indramayu|majalengka|kuningan|sumedang|garut/;
                        catalog[catName].forEach(function(item){
                            var n=item.name.toLowerCase();
                            if(n.includes('mh')) banjirItems.push(item);
                            else if(n.includes('kekeringan')&&kabRe.test(n)) kekeringanItems.push(item);
                            else otherItems.push(item);
                        });
                        otherItems.forEach(function(item){ createMenuItem(item,catName,contentDiv); });
                        if(banjirItems.length>0){
                            var mhDiv=document.createElement('div');
                            mhDiv.className='sub-bab-container';
                            mhDiv.innerHTML='<button class="sub-bab-title" onclick="toggleMenu(this)">Lokasi Kejadian Banjir <span class="arrow">&#9660;</span></button><div class="sub-bab-content"></div>';
                            var mhContent=mhDiv.querySelector('.sub-bab-content');
                            banjirItems.forEach(function(item){ createMenuItem(item,catName,mhContent,'Kejadian Banjir'); });
                            contentDiv.appendChild(mhDiv);
                        }
                        if(kekeringanItems.length>0){
                            var keringDiv=document.createElement('div');
                            keringDiv.className='sub-bab-container';
                            keringDiv.innerHTML='<button class="sub-bab-title" onclick="toggleMenu(this)">Daerah Rawan Kekeringan <span class="arrow">&#9660;</span></button><div class="sub-bab-content"></div>';
                            var keringContent=keringDiv.querySelector('.sub-bab-content');
                            kekeringanItems.forEach(function(item){ createMenuItem(item,catName,keringContent,'Kekeringan'); });
                            contentDiv.appendChild(keringDiv);
                        }
                    } else {
                        catalog[catName].forEach(function(item){
                            var isChecked=(catName==="DAS dan Jaringan Sungai")?"checked":"";
                            createMenuItem(item,catName,contentDiv,null,isChecked);
                        });
                    }
                    menuContainer.appendChild(groupDiv);
                }

                // Menu SIMONA
                var simonaGroup=document.createElement('div');
                simonaGroup.className='menu-group';
                simonaGroup.innerHTML=
                    '<button class="menu-title" onclick="toggleMenu(this)" style="background:#f39c12">LOKASI KRITIS SIMONA <span class="arrow">&#9660;</span></button>'+
                    '<div class="menu-content">'+
                    '<div class="menu-item"><label>'+
                    '<input type="checkbox" id="lokasi-kritis-simona-checkbox" onchange="toggleLokasiKritisSimona()">'+
                    '<i class="fas fa-chart-line" style="color:#f39c12;width:24px;text-align:center"></i>'+
                    '<span>Tampilkan Lokasi Kritis SIMONA</span></label></div>'+
                    '<div class="menu-item"><label style="justify-content:center">'+
                    '<button onclick="zoomToLokasiKritisSimona()" style="background:#3498db;border:none;color:#fff;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:12px;width:100%">'+
                    '<i class="fas fa-search-location"></i> Zoom ke Semua Lokasi</button></label></div></div>';
                menuContainer.appendChild(simonaGroup);
            })
            .catch(function(e){ console.error("Gagal load sidebar:",e); });
    }

    function assignToCategory(fullName, catalog) {
        var l = fullName.toLowerCase();
        var cat = "Sumber Daya Alam";
        if(l.includes('aset_tanah')) cat="Aset Tanah";
        else if(l.startsWith('di_')) cat="Daerah Irigasi Pusat";
        else if(l.includes('pemboran')||l.includes('pemanfaatan')||l.includes('resapan')||l.includes('aquifer')||l.includes('cat')||l.includes('sempadan')) cat="Sumber Daya Air";
        else if(l.includes('bendung')||l.includes('embung')||l.includes('pengendali')||l.includes('pengaman')||l.includes('mata')||l.includes('situ')||l.includes('waduk')||l.includes('sumur')) cat="Infrastruktur";
        else if(l.includes('kewenangan')) cat="Daerah Kewenangan Kabupaten dan Provinsi";
        else if(l.includes('sungai')||l.includes('das')) cat="DAS dan Jaringan Sungai";
        else if(l.startsWith('batas')) cat="Batas Wilayah Administrasi";
        else if(l.includes('banjir')||l.includes('kekeringan')||l.includes('mh')||l.includes('kritis')||l.includes('gerakan')) cat="Kebencanaan";
        catalog[cat].push({name:fullName});
    }

    function createMenuItem(item, catName, container, subCat, isChecked) {
        isChecked = isChecked||"";
        subCat = subCat||null;
        var itemDiv=document.createElement('div');
        itemDiv.className='menu-item';
        var isWMS=item.name.toLowerCase().includes('demnas');
        var funcName=isWMS?'toggleWMS':'toggleWFS';
        var subParam=subCat?", '"+subCat+"'":'';
        itemDiv.innerHTML=
            '<label><input type="checkbox" '+(isChecked||'')+
            ' onchange="'+funcName+'(this, \''+item.name+'\', \''+catName+'\''+subParam+')">'+
            getLayerIcon(item.name)+
            '<span>'+formatTitle(item.name)+'</span></label>';
        container.appendChild(itemDiv);
        if(isChecked==="checked"){
            setTimeout(function(){
                var input=itemDiv.querySelector('input');
                window[funcName](input,item.name,catName);
            },200);
        }
    }

    // ===== SEARCH =====
    // Debounce agar tidak search setiap keystroke
    var searchDebounce = null;
    function performGlobalSearch(query) {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(function(){
            _doSearch(query);
        }, 300);
    }

    function _doSearch(query) {
        var resultDiv = document.getElementById('global-search-results');
        if(!query||query.trim()===""){
            resultDiv.innerHTML=''; resultDiv.classList.remove('active'); return;
        }
        query = query.toLowerCase().trim();
        var results = [];

        availableLayersList.forEach(function(layer){
            if(layer.title.toLowerCase().includes(query)||layer.name.toLowerCase().includes(query)){
                if(!results.some(function(r){ return r.type==='layer'&&r.name===layer.name; }))
                    results.push({type:'layer',title:layer.title,name:layer.name});
            }
        });

        allLocationsCache.forEach(function(loc){
            var matched=false, matchedName=loc.name;
            if(loc.name.toLowerCase().includes(query)) matched=true;
            else if(loc.allNames&&loc.allNames.some(function(v){ return v.toLowerCase().includes(query); })){
                matched=true;
                var found=loc.allNames.find(function(v){ return v.toLowerCase().includes(query); });
                if(found) matchedName=found;
            }
            if(matched&&!results.some(function(r){ return r.type==='location'&&r.name===matchedName; }))
                results.push({type:'location',title:loc.layerTitle,name:matchedName,coords:loc.coords});
        });

        var coord=parseCoordinate(query);
        if(coord) results.push({type:'coordinate',title:'Koordinat',name:coord.lat.toFixed(6)+', '+coord.lng.toFixed(6),lat:coord.lat,lng:coord.lng});

        resultDiv.innerHTML='';
        if(results.length===0){
            resultDiv.innerHTML='<div class="no-result"><i class="fas fa-info-circle"></i> Tidak ditemukan</div>';
        } else {
            var frag=document.createDocumentFragment();
            results.forEach(function(res){
                var item=document.createElement('div');
                item.className='result-item';
                var icon=res.type==='layer'?'<i class="fas fa-layer-group"></i>':(res.type==='location'?'<i class="fas fa-map-marker-alt"></i>':'<i class="fas fa-globe"></i>');
                var typeLabel=res.type==='layer'?'Layer':(res.type==='location'?'Lokasi':'Koordinat');
                item.innerHTML=icon+'<span style="flex:1"><b>'+res.title+'</b> '+(res.name?'<span style="font-size:.65rem">- '+res.name+'</span>':'')+'</span><span class="result-type">'+typeLabel+'</span>';
                item.onclick=function(){
                    if(res.type==='layer'){
                        var cbs=document.querySelectorAll('#dynamic-menu input[type="checkbox"]');
                        for(var i=0;i<cbs.length;i++){
                            var cb=cbs[i];
                            var match=cb.getAttribute('onchange')&&cb.getAttribute('onchange').match(/'([^']+)'/);
                            if(match&&match[1]===res.name){ if(!cb.checked) cb.click(); cb.scrollIntoView({behavior:'smooth',block:'center'}); break; }
                        }
                    } else if(res.type==='location'&&res.coords){
                        clearSearchMarker();
                        searchMarker=L.marker(res.coords,{icon:createSearchIcon()}).addTo(map);
                        searchMarker.bindPopup('<div style="text-align:center"><strong>'+res.name+'</strong><br>'+res.title+'<br><button onclick="clearSearchMarker()" style="margin-top:8px;padding:4px 12px;background:#e74c3c;color:#fff;border:none;border-radius:5px;cursor:pointer">Tutup</button></div>').openPopup();
                        map.flyTo(res.coords,14,{duration:1});
                    } else if(res.type==='coordinate'){
                        clearSearchMarker();
                        searchMarker=L.marker([res.lat,res.lng],{icon:createSearchIcon()}).addTo(map);
                        searchMarker.bindPopup('<div style="text-align:center"><strong>Koordinat</strong><br>Lat: '+res.lat+'<br>Lng: '+res.lng+'<br><button onclick="clearSearchMarker()" style="margin-top:8px;padding:4px 12px;background:#e74c3c;color:#fff;border:none;border-radius:5px;cursor:pointer">Tutup</button></div>').openPopup();
                        map.flyTo([res.lat,res.lng],15,{duration:1});
                    }
                };
                frag.appendChild(item);
            });
            resultDiv.appendChild(frag);
        }
        resultDiv.classList.add('active');
    }

    // ===== GPS LOCATOR =====
    var userMarker, userAccuracy;
    map.on('locationfound',function(e){
        var r=e.accuracy/2;
        if(!userMarker){
            userMarker=L.marker(e.latlng).addTo(map).bindPopup("Anda berada di sini");
            userAccuracy=L.circle(e.latlng,r).addTo(map);
        } else {
            userMarker.setLatLng(e.latlng);
            userAccuracy.setLatLng(e.latlng);
            userAccuracy.setRadius(r);
        }
    });
    map.locate({setView:false,watch:true,enableHighAccuracy:true});

    // ===== EVENT LISTENERS =====
    document.getElementById('global-search-btn').addEventListener('click',function(){
        performGlobalSearch(document.getElementById('global-search-input').value);
    });
    document.getElementById('global-search-input').addEventListener('keypress',function(e){
        if(e.key==='Enter') performGlobalSearch(e.target.value);
    });

    // ===== EXPOSE GLOBALS =====
    window.clearSearchMarker = clearSearchMarker;
    window.toggleLokasiKritisSimona = toggleLokasiKritisSimona;
    window.zoomToLokasiKritisSimona = zoomToLokasiKritisSimona;
    window.toggleMenu = toggleMenu;
    window.toggleWMS = toggleWMS;
    window.toggleWFS = toggleWFS;
    window.toggleLegend = toggleLegend;

    // ===== START =====
    loadSidebarLayers();
} // end initApp
</script>
</body>
</html>
