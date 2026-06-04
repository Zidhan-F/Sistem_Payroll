<!-- Section: Master Payroll Schedule -->
<div id="viewSchedule" class="view-section">
    <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
        <div class="section-header" style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
            <div>
                <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Master Payroll Schedule & Attendance</h3>
                <p style="color: #64748b; font-size: 13px; margin: 0;">Configure global schedule templates and upload employee attendance excel logs.</p>
            </div>
        </div>

        <!-- Custom Tabs for Schedule & Upload -->
        <div class="workspace-tabs">
            <button class="ws-tab active" id="tabScheduleTemplatesBtn" onclick="switchScheduleTab('templates')">
                <i class="fas fa-calendar-alt" style="margin-right: 6px;"></i> Schedule Templates
            </button>
            <button class="ws-tab" id="tabScheduleUploadBtn" onclick="switchScheduleTab('upload')">
                <i class="fas fa-file-excel" style="margin-right: 6px;"></i> Upload Attendance Excel
            </button>
        </div>

        <!-- Tab Panel 1: Schedule Templates -->
        <div id="scheduleTabTemplates" class="schedule-tab-panel" style="display: block;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 13px; font-weight: 600; color: #64748b;">Tahun:</span>
                        <select id="scheduleYearSelect" onchange="pilihTahunSchedule(this.value)" style="padding: 8px 14px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); min-width: 130px;">
                            <!-- Dynamically rendered or static years -->
                        </select>
                    </div>
                    <button onclick="tambahPeriodeTahunan()" style="background: #eff6ff; border: 1px dashed #3b82f6; color: #1d4ed8; border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                        <i class="fas fa-plus"></i> Tambah Tahun
                    </button>
                </div>
                <button class="btn-add" onclick="bukaModalSchedule('tambah')" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 9px 18px; cursor: pointer; transition: all 0.3s; font-size: 13px;">
                    <i class="fas fa-plus"></i> Add Schedule
                </button>
            </div>

            <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="width: 60px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">No</th>
                            <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Title</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Tanggal</th>
                            <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Deskripsi</th>
                            <th style="width: 150px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="masterSchedulesContainer">
                        <!-- Rows will be dynamically rendered here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Panel 2: Upload Attendance Excel -->
        <div id="scheduleTabUpload" class="schedule-tab-panel" style="display: none;">
            <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; background: white;">
                <!-- Top Toolbar -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: #1e293b;">
                            <i class="fas fa-table" style="color: var(--primary-color); margin-right: 6px;"></i> Uploaded Attendance Table
                        </h4>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="text" id="searchInlineAttendance" placeholder="Search employee..." oninput="filterInlineAttendanceTable()" style="padding: 8px 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 13px; width: 220px; transition: border-color 0.2s;">
                        
                        <button type="button" onclick="bukaModalUploadAbsensi()" style="background: #27ae60; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                            <i class="fas fa-file-excel"></i> Upload Excel
                        </button>
                    </div>
                </div>
                
                <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white; min-height: 350px;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 700px;" id="inlineAttendanceTable">
                        <thead id="inlineAttendanceTableHeader">
                            <tr style="background: #f8fafc;">
                                <th style="width: 50px; text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">No</th>
                                <th style="text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Employee ID</th>
                                <th style="text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Nama</th>
                                <th style="text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Tgl dan Hari</th>
                                <th style="text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Jam Masuk</th>
                                <th style="text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Jam Keluar</th>
                                <th style="text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="inlineAttendanceTableBody">
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #cbd5e1; border: none !important;">
                                    <i class="fas fa-file-excel" style="font-size: 36px; margin-bottom: 12px; display: block; color: #cbd5e1;"></i>
                                    No data. Click "Upload Excel" to preview attendance details.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
