<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Gaji - Payroll App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .calc-container {
            width: 100%;
            max-width: 500px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.3);
        }
        .calc-header { text-align: center; margin-bottom: 30px; }
        .calc-header i { font-size: 40px; color: #2c3e50; margin-bottom: 15px; }
        .calc-header h2 { font-size: 24px; color: #2c3e50; }
        .calc-header p { color: #7f8c8d; font-size: 14px; }
        
        .result-card {
            background: #2c3e50;
            color: white;
            padding: 25px;
            border-radius: 18px;
            margin-top: 30px;
            display: none;
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        .result-item { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; opacity: 0.9; }
        .result-total { border-top: 1px solid rgba(255,255,255,0.2); margin-top: 15px; padding-top: 15px; display: flex; justify-content: space-between; font-weight: 700; font-size: 18px; }
        
        .btn-check {
            width: 100%;
            padding: 14px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
        }
        .btn-check:hover { background: #34495e; transform: translateY(-2px); }
    </style>
</head>
<body>

    <div class="calc-container">
        <div class="calc-header">
            <i class="fas fa-calculator"></i>
            <h2>Simulasi Gaji Kamu</h2>
            <p>Pilih daerah kerja kamu untuk melihat estimasi gaji</p>
        </div>

        <div class="form-group">
            <label>Tipe Daerah</label>
            <select id="calcType" onchange="loadRegions()" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd;">
                <option value="UMP">Provinsi (UMP)</option>
                <option value="UMK">Kota/Kabupaten (UMK)</option>
            </select>
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <label>Pilih Daerah</label>
            <select id="calcRegion" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd;">
                <option value="">-- Loading Daerah --</option>
            </select>
        </div>

        <button class="btn-check" onclick="calculateSalary()">Cek Estimasi Gaji</button>

        <div id="resultCard" class="result-card">
            <div class="result-item">
                <span>Daerah:</span>
                <span id="resRegion">-</span>
            </div>
            <div class="result-item">
                <span>Gaji Pokok (UMP/UMK):</span>
                <span id="resBasic">-</span>
            </div>
            <div class="result-item">
                <span>Tunjangan (Estimasi 10%):</span>
                <span id="resAllowance">-</span>
            </div>
            <div class="result-total">
                <span>Total Estimasi THP:</span>
                <span id="resTotal">-</span>
            </div>
            <p style="font-size: 10px; margin-top: 15px; opacity: 0.7; font-weight: 400; text-align: center;">
                *Hasil ini adalah simulasi. Gaji riil ditentukan oleh kebijakan perusahaan dan kontrak kerja.
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="<?= base_url('index.php/login') ?>" style="color: #2c3e50; text-decoration: none; font-size: 13px; font-weight: 500;">
                <i class="fas fa-lock" style="margin-right: 5px;"></i> Login Admin
            </a>
        </div>
    </div>

    <script>
        const BASE_URL = "<?= base_url() ?>";
        const API_URL = "<?= base_url('index.php/api') ?>";
        let allRegions = [];

        async function loadRegions() {
            const type = document.getElementById('calcType').value;
            const res = await fetch(`${API_URL}/minimum-wages?tipe=${type}`);
            allRegions = await res.json();
            
            const select = document.getElementById('calcRegion');
            select.innerHTML = allRegions.map(r => `<option value="${r.id}">${r.nama_daerah}</option>`).join('');
        }

        function formatRupiah(val) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
        }

        function calculateSalary() {
            const regionId = document.getElementById('calcRegion').value;
            const region = allRegions.find(r => r.id == regionId);
            
            if (!region) return;

            const basic = parseFloat(region.nominal);
            const allowance = basic * 0.1; // Estimasi tunjangan 10%
            const total = basic + allowance;

            document.getElementById('resRegion').innerText = region.nama_daerah;
            document.getElementById('resBasic').innerText = formatRupiah(basic);
            document.getElementById('resAllowance').innerText = formatRupiah(allowance);
            document.getElementById('resTotal').innerText = formatRupiah(total);

            document.getElementById('resultCard').style.display = 'block';
        }

        // Init
        loadRegions();
    </script>
</body>
</html>
