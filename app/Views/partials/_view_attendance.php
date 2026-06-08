<!-- Section: Attendance Management -->
<div id="viewAttendance" class="view-section">
    <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
        <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
            <div>
                <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Attendance Log</h3>
                <p style="color: #64748b; font-size: 13px; margin: 0;">Input dan kelola kehadiran harian karyawan.</p>
            </div>
            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <select id="attendanceClientSelect" onchange="loadAttendanceLogs()" style="padding: 10px 16px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer; min-width: 200px;">
                    <option value="">-- Pilih Client --</option>
                </select>
                <select id="attendanceMonthSelect" onchange="loadAttendanceLogs()" style="padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer;">
                    <option value="1">Januari</option><option value="2">Februari</option><option value="3">Maret</option>
                    <option value="4">April</option><option value="5">Mei</option><option value="6">Juni</option>
                    <option value="7">Juli</option><option value="8">Agustus</option><option value="9">September</option>
                    <option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option>
                </select>
                <select id="attendanceYearSelect" onchange="loadAttendanceLogs()" style="padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer;">
                    <option value="2025">2025</option>
                    <option value="2026" selected>2026</option>
                    <option value="2027">2027</option>
                </select>
                <button class="btn-add" onclick="bukaModalUploadAbsensi()" style="background: #27ae60; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                    <img src="https://cdn-icons-png.flaticon.com/512/337/337947.png" style="width: 16px; height: 16px; object-fit: contain; filter: brightness(0) invert(1);" alt="Upload Icon"> Upload Attendance Excel
                </button>
            </div>
        </div>

        <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="width: 60px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">No</th>
                        <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Karyawan</th>
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Tanggal</th>
                        <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Shift</th>
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Jam Masuk</th>
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Jam Keluar</th>
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Jam Kerja</th>
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Terlambat</th>
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Pulang Cepat</th>
                        <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Keterangan & Status Shift</th>
                        <th style="width: 100px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    <!-- Dynamic rows -->
                </tbody>
            </table>
        </div>
    </div>
</div>
