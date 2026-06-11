<!-- Section: Skema Shift Hub (Unified Master Shift & Employee Shift Assignments) -->
<div id="viewSkemaShift" class="view-section">
    <!-- Sub Tabs for Shift Hub -->
    <div class="sub-tabs-container" style="display: flex; gap: 8px; border-bottom: 2px solid #f1f5f9; margin-bottom: 20px; padding-bottom: 2px;">
        <button class="sub-tab-btn active" id="subTabShiftMaster" onclick="switchShiftSubTab('master')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: var(--primary-color); cursor: pointer; border-bottom: 2px solid var(--primary-color); margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Master Skema Shift</button>
        <button class="sub-tab-btn" id="subTabShiftAllocation" onclick="switchShiftSubTab('allocation')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Alokasi Shift Karyawan</button>
    </div>

    <!-- Panel 1: Master Skema Shift -->
    <div id="panelShiftMaster" class="shift-subpanel">
        <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
            <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                <div>
                    <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Master Skema Shift</h3>
                    <p style="color: #64748b; font-size: 13px; margin: 0;">Kelola daftar skema shift kerja standar, toleransi keterlambatan, dan tarif lembur.</p>
                </div>
                <div>
                    <button class="btn-add" onclick="bukaModalShiftScheme('tambah')" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-plus"></i> Tambah Skema Shift
                    </button>
                </div>
            </div>

            <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="width: 60px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">No</th>
                            <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Nama Shift</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Jam Masuk - Keluar</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Durasi Standar</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Toleransi (Late/Early)</th>
                            <th style="width: 150px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="shiftSchemesTableContainer">
                        <!-- Rows will be dynamically rendered here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Panel 2: Alokasi Shift Karyawan -->
    <div id="panelShiftAllocation" class="shift-subpanel" style="display: none;">
        <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
            <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                <div>
                    <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Alokasi Shift Karyawan</h3>
                    <p style="color: #64748b; font-size: 13px; margin: 0;">Tugaskan skema shift ke karyawan tertentu dengan rentang tanggal aktif dan lihat riwayat alokasi.</p>
                </div>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 13px; font-weight: 600; color: #64748b;">Filter Karyawan:</span>
                        <select id="shiftEmployeeFilterSelect" onchange="loadEmployeeShifts(this.value)" style="padding: 10px 16px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer; min-width: 200px;">
                            <option value="">Semua Karyawan</option>
                        </select>
                    </div>
                    <button class="btn-add" onclick="bukaModalAssignShift()" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-plus"></i> Tugaskan Shift
                    </button>
                </div>
            </div>

            <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="width: 60px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">No</th>
                            <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Karyawan</th>
                            <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Skema Shift</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Tanggal Mulai</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Tanggal Selesai</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Status</th>
                            <th style="width: 120px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="employeeShiftsTableContainer">
                        <!-- Rows will be dynamically rendered here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
