<!DOCTYPE html>
<html>
<head>
    <title>Absensi Geocimancis</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f4f4f4; }
        #map { height: 500px; width: 100%; border-radius: 10px; margin-top: 20px; }
        .form-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }
        .preview-area { margin: 10px 0; width: 100%; height: 240px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 8px; }
        video, img { width: 100%; height: 100%; object-fit: cover; }
        input, select, button { padding: 10px; margin: 5px 0; border-radius: 5px; border: 1px solid #ccc; width: 100%; box-sizing: border-box; }
        .btn-main { background: #28a745; color: white; border: none; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .popup-container { text-align: center; min-width: 150px; }
        .popup-container img { width: 100%; border-radius: 5px; margin-top: 5px; }
    </style>
</head>
<body>

    <div class="form-box">
        <h2>📍 Presensi Karyawan</h2>
        <input type="text" id="nama" placeholder="Nama Lengkap">
        <select id="wilayah">
            <option value="Cirebon Kota">Cirebon Kota</option>
            <option value="Sumber">Sumber</option>
            <option value="Palimanan">Palimanan</option>
        </select>
        
        <div style="display: flex; gap: 10px;">
            <button onclick="startCamera()" style="background: #6c757d; color: white;">📷 Kamera</button>
            <button onclick="document.getElementById('fileInput').click()" style="background: #6c757d; color: white;">📁 Galeri</button>
            <input type="file" id="fileInput" accept="image/*" style="display:none" onchange="previewFile()">
        </div>

        <div class="preview-area">
            <video id="video" autoplay playsinline style="display:none"></video>
            <img id="preview-img" style="display:none">
            <span id="txt-p">Belum ada foto</span>
        </div>
        
        <button id="cap-btn" style="display:none; background:#007bff; color:white;" onclick="takeSnapshot()">Ambil Gambar</button>
        <button class="btn-main" onclick="prosesAbsen()">SIMPAN ABSEN SEKARANG</button>
    </div>

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <script>
        // Inisialisasi Peta
        var map = L.map('map').setView([-6.722, 108.556], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        const video = document.getElementById('video');
        const previewImg = document.getElementById('preview-img');
        const capBtn = document.getElementById('cap-btn');
        let currentFoto = null;

        // Fungsi Kamera
        function startCamera() {
            video.style.display = 'block'; previewImg.style.display = 'none'; capBtn.style.display = 'block';
            document.getElementById('txt-p').style.display = 'none';
            navigator.mediaDevices.getUserMedia({ video: true }).then(s => video.srcObject = s);
        }

        function takeSnapshot() {
            const canvas = document.createElement('canvas');
            canvas.width = 640; canvas.height = 480;
            canvas.getContext('2d').drawImage(video, 0, 0, 640, 480);
            currentFoto = canvas.toDataURL('image/jpeg');
            previewImg.src = currentFoto; previewImg.style.display = 'block'; video.style.display = 'none';
            capBtn.style.display = 'none';
        }

        function previewFile() {
            const file = document.getElementById('fileInput').files[0];
            const reader = new FileReader();
            reader.onloadend = () => { 
                currentFoto = reader.result; 
                previewImg.src = currentFoto; 
                previewImg.style.display = 'block'; 
                video.style.display = 'none';
                document.getElementById('txt-p').style.display = 'none';
            };
            if (file) reader.readAsDataURL(file);
        }

        // Simpan Absen
        function prosesAbsen() {
            if (!currentFoto || !document.getElementById('nama').value) return alert("Nama & Foto wajib!");
            navigator.geolocation.getCurrentPosition(pos => {
                fetch('/simpan-absen', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        nama: document.getElementById('nama').value,
                        wilayah: document.getElementById('wilayah').value,
                        foto: currentFoto,
                        lat: pos.coords.latitude, lng: pos.coords.longitude
                    })
                }).then(() => {
                    alert("Absen Berhasil!");
                    location.reload();
                });
            }, () => alert("Gagal mengambil lokasi. Pastikan GPS aktif."));
        }

        // Tampilkan SEMUA titik absen dengan Fitur Cluster (Anti-Tumpuk)
        var markerClusterGroup = L.markerClusterGroup();

        fetch('/get-absensi').then(res => res.json()).then(data => {
            data.forEach(d => {
                if (d.lat && d.lng) {
                    let popupContent = `
                        <div class="popup-container">
                            <b>${d.nama}</b><br>
                            <small>${d.waktu_absen}</small><br>
                            <img src="${d.foto}"><br>
                            <small>Wilayah: ${d.wilayah_tugas}</small><br>
                            <small>Koord: ${parseFloat(d.lat).toFixed(5)}, ${parseFloat(d.lng).toFixed(5)}</small>
                        </div>
                    `;
                    let marker = L.marker([d.lat, d.lng]).bindPopup(popupContent);
                    markerClusterGroup.addLayer(marker);
                }
            });
            map.addLayer(markerClusterGroup);
        });
    </script>
</body>
</html>