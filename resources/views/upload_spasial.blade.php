<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Data Spasial - Geocimancis</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 400px; }
        .card h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; font-size: 0.9rem; }
        input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
        .btn { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn:hover { background: #2980b9; }
        .btn:disabled { background: #bdc3c7; cursor: not-allowed; }
        .status { margin-top: 15px; padding: 10px; border-radius: 4px; display: none; font-size: 0.85rem; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #7f8c8d; text-decoration: none; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="card">
    <h2>🚀 Import Data</h2>
    <p style="font-size: 0.8rem; color: #666;">Format: GeoJSON (.json) atau Shapefile (.zip)</p>
    
    <div class="form-group">
        <label>Pilih File Spasial</label>
        <input type="file" id="spatialFile" accept=".json,.zip">
    </div>

    <button class="btn" id="uploadBtn" onclick="processUpload()">MULAI IMPORT</button>
    
    <div id="statusBox" class="status"></div>

    <a href="/" class="back-link">← Kembali ke Peta</a>
</div>

<script>
function processUpload() {
    const fileInput = document.getElementById('spatialFile');
    const btn = document.getElementById('uploadBtn');
    const statusBox = document.getElementById('statusBox');

    if (fileInput.files.length === 0) {
        alert("Pilih file terlebih dahulu!");
        return;
    }

    const formData = new FormData();
    formData.append('spatial_file', fileInput.files[0]);

    // UI Loading State
    btn.disabled = true;
    btn.innerText = "Sedang Memproses...";
    statusBox.style.display = "none";

    fetch("{{ route('spatial.upload') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        statusBox.style.display = "block";
        if(data.status === 'success') {
            statusBox.style.background = "#d4edda";
            statusBox.style.color = "#155724";
            statusBox.innerText = data.message;
            fileInput.value = ""; 
        } else {
            statusBox.style.background = "#f8d7da";
            statusBox.style.color = "#721c24";
            statusBox.innerText = data.message;
        }
    })
    .catch(err => {
        alert("Error: Koneksi server bermasalah.");
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerText = "MULAI IMPORT";
    });
}
</script>

</body>
</html>