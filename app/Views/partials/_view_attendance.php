<!-- Section: Attendance Management -->
<div id="viewAttendance">
    <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
        <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
            <div>
                <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0; display: inline-flex; align-items: center; gap: 8px;">
                    Attendance Log
                    <button onclick="bukaModalAttendance()" style="background: var(--primary-color); color: white; border: none; border-radius: 50%; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.1);" onmouseover="this.style.transform='scale(1.15)'; this.style.background='var(--primary-dark)';" onmouseout="this.style.transform='scale(1)'; this.style.background='var(--primary-color)';" title="Tambah Kehadiran Manual">
                        <i class="fas fa-plus" style="font-size: 11px;"></i>
                    </button>
                </h3>
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
                    <i class="fas fa-file-excel"></i> Upload Attendance Excel
                </button>
                <button class="btn-add" onclick="downloadMainAbsensiTemplate()" style="background: #0284c7; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                    <i class="fas fa-download"></i> Download Template
                </button>
                <div style="position: relative; display: inline-block;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;"></i>
                    <input type="text" id="attendanceSearchInput" onkeyup="filterAttendanceTable()" placeholder="Search name, ID, or shift..." style="padding: 10px 12px 10px 34px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 13px; color: #334155; background: white; font-weight: 600; min-width: 240px; transition: all 0.2s;" onfocus="this.style.borderColor='var(--primary-color)';" onblur="this.style.borderColor='#cbd5e1';">
                </div>
            </div>
        </div>

        <!-- Late Upload Alert Banner -->
        <div id="attendanceLateUploadRemark" style="display: none; background: #fff2e8; border: 1px solid #ffbb96; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; color: #d4380d; font-weight: 600; font-size: 13.5px; align-items: center; gap: 8px;">
            <i class="fas fa-exclamation-circle" style="font-size: 16px;"></i>
            <span>Late Upload: Upload absensi untuk periode ini telah melewati tanggal cut-off (<strong id="attendanceCutoffDateLabel">-</strong>).</span>
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
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Jam Kerja (Lembur)</th>
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Early Arrival</th>
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
