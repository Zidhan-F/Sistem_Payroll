<!-- Section: Schedule Hub (Unified Master Schedule, Holiday Calendar, Attendance, Overtime) -->
<div id="viewSchedule" class="view-section">
    <!-- Sub Tabs for Schedule Hub -->
    <div class="sub-tabs-container" style="display: flex; gap: 8px; border-bottom: 2px solid #f1f5f9; margin-bottom: 20px; padding-bottom: 2px;">
        <button class="sub-tab-btn" id="subTabScheduleMaster" onclick="switchScheduleSubTab('master')" style="display: none;">Master Schedule</button>
        <button class="sub-tab-btn active" id="subTabScheduleHoliday" onclick="switchScheduleSubTab('holiday')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: var(--primary-color); cursor: pointer; border-bottom: 2px solid var(--primary-color); margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Holiday Calendar</button>
        <button class="sub-tab-btn" id="subTabScheduleAttendance" onclick="switchScheduleSubTab('attendance')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Attendance</button>
        <button class="sub-tab-btn" id="subTabScheduleOvertime" onclick="switchScheduleSubTab('overtime')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Overtime</button>
        <button class="sub-tab-btn" id="subTabScheduleSystemSettings" onclick="switchScheduleSubTab('systemSettings')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s ease; outline: none;">System Settings</button>
    </div>

    <!-- Panel 1: Master Schedule -->
    <div id="panelScheduleMaster" class="schedule-subpanel" style="display: none;">
        <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
            <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                <div>
                    <h3 id="scheduleTableTitle" style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Master Payroll Schedule</h3>
                    <p style="color: #64748b; font-size: 13px; margin: 0;">Configure global templates for pay dates and cut-off ranges.</p>
                </div>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 13px; font-weight: 600; color: #64748b;">Tahun:</span>
                        <select id="scheduleYearSelect" onchange="pilihTahunSchedule(this.value)" style="padding: 10px 16px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); min-width: 130px;">
                            <!-- Dynamically rendered or static years -->
                        </select>
                    </div>
                    <button onclick="tambahPeriodeTahunan()" style="background: #eff6ff; border: 1px dashed #3b82f6; color: #1d4ed8; border-radius: 8px; padding: 10px 16px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                        <i class="fas fa-plus"></i> Tambah Tahun
                    </button>

                    <button class="btn-add" onclick="bukaModalSchedule('tambah')" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                        <img src="https://cdn-icons-png.flaticon.com/512/992/992651.png" style="width: 16px; height: 16px; object-fit: contain; filter: brightness(0) invert(1);" alt="Add Icon"> Add Schedule
                    </button>
                </div>
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
    </div>

    <!-- Panel 2: Holiday Calendar -->
    <div id="panelScheduleHoliday" class="schedule-subpanel">
        <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
            <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                <div>
                    <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Holiday Calendar</h3>
                    <p style="color: #64748b; font-size: 13px; margin: 0;">Kelola hari libur nasional dan tanggal libur khusus.</p>
                </div>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <!-- Toggle View Buttons -->
                    <div style="display: inline-flex; background: #f1f5f9; border-radius: 8px; padding: 4px; border: 1px solid #e2e8f0; margin-right: 8px;">
                        <button type="button" id="btnHolidayViewCalendar" onclick="switchHolidayView('calendar')" style="padding: 6px 14px; border-radius: 6px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; background: var(--primary-color); color: white; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-calendar-alt"></i> Kalender
                        </button>
                        <button type="button" id="btnHolidayViewList" onclick="switchHolidayView('list')" style="padding: 6px 14px; border-radius: 6px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; background: transparent; color: #64748b; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-list"></i> Daftar Tabel
                        </button>
                    </div>
                    <button class="btn-add" id="btnSyncGoogleCalendar" onclick="syncGoogleCalendar()" style="background: #4285f4; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s; margin-right: 4px;">
                        <i class="fab fa-google"></i> Sync Google Calendar
                    </button>
                    
                    <button class="btn-add" onclick="bukaModalHoliday()" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-plus"></i> Tambah Hari Libur
                    </button>
                </div>
            </div>

            <!-- Calendar View Container -->
            <div id="holidayCalendarContainer" style="display: block;">
                <!-- Monthly Navigator -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <button type="button" onclick="navigateHolidayMonth(-1)" style="background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 14px; cursor: pointer; color: #475569; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h4 id="holidayCurrentMonthYear" style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; min-width: 160px; text-align: center;">Juni 2026</h4>
                        <button type="button" onclick="navigateHolidayMonth(1)" style="background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 14px; cursor: pointer; color: #475569; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <select id="holidayMonthSelect" onchange="onHolidayMonthYearChange()" style="padding: 8px 14px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; font-weight: 600; color: #334155; cursor: pointer; background: white;">
                            <option value="0">Januari</option>
                            <option value="1">Februari</option>
                            <option value="2">Maret</option>
                            <option value="3">April</option>
                            <option value="4">Mei</option>
                            <option value="5">Juni</option>
                            <option value="6">Juli</option>
                            <option value="7">Agustus</option>
                            <option value="8">September</option>
                            <option value="9">Oktober</option>
                            <option value="10">November</option>
                            <option value="11">Desember</option>
                        </select>
                        
                        <select id="holidayYearSelect" onchange="onHolidayMonthYearChange()" style="padding: 8px 14px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; font-weight: 600; color: #334155; cursor: pointer; background: white; min-width: 90px;">
                            <option value="2025">2025</option>
                            <option value="2026" selected>2026</option>
                            <option value="2027">2027</option>
                        </select>
                    </div>
                </div>

                <!-- Main Layout: Grid Calendar & Side Summary -->
                <div style="display: grid; grid-template-columns: 4fr 1fr; gap: 20px; align-items: start;">
                    <!-- Calendar Grid Wrapper -->
                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                        <!-- Days of Week Headers (Sunday is first column) -->
                        <div style="display: grid; grid-template-columns: repeat(7, 1fr); background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; text-align: center;">
                            <div style="padding: 14px 8px; font-weight: 700; color: #ef4444; font-size: 13px; text-transform: uppercase;">Min</div>
                            <div style="padding: 14px 8px; font-weight: 600; color: #64748b; font-size: 13px; text-transform: uppercase;">Sen</div>
                            <div style="padding: 14px 8px; font-weight: 600; color: #64748b; font-size: 13px; text-transform: uppercase;">Sel</div>
                            <div style="padding: 14px 8px; font-weight: 600; color: #64748b; font-size: 13px; text-transform: uppercase;">Rab</div>
                            <div style="padding: 14px 8px; font-weight: 600; color: #64748b; font-size: 13px; text-transform: uppercase;">Kam</div>
                            <div style="padding: 14px 8px; font-weight: 600; color: #64748b; font-size: 13px; text-transform: uppercase;">Jum</div>
                            <div style="padding: 14px 8px; font-weight: 600; color: #64748b; font-size: 13px; text-transform: uppercase;">Sab</div>
                        </div>
                        
                        <!-- Days Grid -->
                        <div id="holidayCalendarGrid" style="display: grid; grid-template-columns: repeat(7, 1fr); background: #e2e8f0; gap: 1px;">
                            <!-- Filled dynamically by JS -->
                        </div>
                    </div>

                    <!-- Right Side: Monthly Holiday Summary -->
                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; background: #fafafa; min-height: 250px; border-left: 4px solid var(--primary-color);">
                        <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle" style="color: var(--primary-color);"></i> Daftar Hari Libur Bulan Ini
                        </h4>
                        <div id="holidaySideSummary" style="display: flex; flex-direction: column; gap: 12px;">
                            <!-- Filled dynamically by JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View Container (Hidden by default) -->
            <div id="holidayListContainer" style="display: none;">
                <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="width: 60px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">No</th>
                                <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Tanggal</th>
                                <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Hari</th>
                                <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Deskripsi</th>
                                <th style="width: 120px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="holidayTableBody">
                            <!-- Dynamic rows -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel 3: Attendance -->
    <div id="panelScheduleAttendance" class="schedule-subpanel" style="display: none;">
        <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
            <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                <div>
                    <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Attendance Log</h3>
                    <p style="color: #64748b; font-size: 13px; margin: 0;">Input dan kelola kehadiran harian karyawan.</p>
                </div>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <select id="attendanceClientSelect" onchange="loadAttendanceLogs()" style="display: none;">
                        <option value="">-- Pilih Client --</option>
                    </select>
                    <div class="custom-select-wrapper" style="position: relative; min-width: 200px; display: inline-block; font-family: inherit;">
                        <div id="attendanceClientDropdownTrigger" onclick="toggleAttendanceClientDropdown(event)" style="padding: 10px 32px 10px 16px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: space-between; position: relative; min-height: 42px; box-sizing: border-box; user-select: none;">
                            <span id="attendanceClientSelectedText">-- Pilih Client --</span>
                            <i class="fas fa-chevron-down" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: #64748b; transition: transform 0.2s;"></i>
                        </div>
                        <div id="attendanceClientDropdownPanel" style="display: none; position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: white; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); z-index: 999; max-height: 250px; overflow-y: auto; box-sizing: border-box; padding: 6px;">
                            <div style="position: relative; margin-bottom: 6px;">
                                <i class="fas fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12px;"></i>
                                <input type="text" id="attendanceClientSearchInput" onkeyup="filterClientDropdownOptions()" placeholder="Cari client..." style="width: 100%; padding: 8px 10px 8px 30px; border-radius: 6px; border: 1px solid #cbd5e1; outline: none; font-size: 13px; color: #334155; box-sizing: border-box;" onclick="event.stopPropagation()">
                            </div>
                            <div id="attendanceClientOptionsContainer" style="display: flex; flex-direction: column; gap: 2px;">
                                <!-- Rendered dynamically -->
                            </div>
                        </div>
                    </div>
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

                    <button class="btn-add" onclick="downloadMainAbsensiTemplate()" style="background: #2c3e50; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-download"></i> Download Template
                    </button>
                    <div style="position: relative; display: inline-block;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;"></i>
                        <input type="text" id="attendanceSearchInput" onkeyup="filterAttendanceTable()" placeholder="Search name, ID, or shift..." style="padding: 10px 12px 10px 34px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 13px; color: #334155; background: white; font-weight: 600; min-width: 240px; transition: all 0.2s;" onfocus="this.style.borderColor='var(--primary-color)';" onblur="this.style.borderColor='#cbd5e1';">
                    </div>
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
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Jam Kerja (Lembur)</th>
                            <th style="width: 120px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                        <tr>
                            <td colspan="8" style="text-align:center;padding:40px;color:#94a3b8;">
                                <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
                                Silakan pilih client terlebih dahulu untuk menampilkan data absensi.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Panel 4: Overtime -->
    <div id="panelScheduleOvertime" class="schedule-subpanel" style="display: none;">
        <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
            <div class="section-header" style="margin-bottom: 25px; display: flex; flex-direction: column; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; width: 100%;">
                    <div>
                        <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Persetujuan & Log Lembur</h3>
                        <p style="color: #64748b; font-size: 13px; margin: 0;">Kelola, verifikasi, setujui (Approve), atau tolak (Reject) jam lembur harian karyawan.</p>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <select id="overtimeClientSelect" onchange="loadOvertimeLogs()" style="padding: 10px 16px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer; min-width: 200px;">
                            <option value="">-- Pilih Client --</option>
                        </select>
                        <select id="overtimeMonthSelect" onchange="loadOvertimeLogs()" style="padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer;">
                            <option value="1">Januari</option><option value="2">Februari</option><option value="3">Maret</option>
                            <option value="4">April</option><option value="5">Mei</option><option value="6">Juni</option>
                            <option value="7">Juli</option><option value="8">Agustus</option><option value="9">September</option>
                            <option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option>
                        </select>
                        <select id="overtimeYearSelect" onchange="loadOvertimeLogs()" style="padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer;">
                            <option value="2025">2025</option>
                            <option value="2026" selected>2026</option>
                            <option value="2027">2027</option>
                        </select>
                        <button class="btn-add" onclick="bukaModalOvertime()" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                            <i class="fas fa-plus"></i> Input Lembur
                        </button>
                        <button class="btn-add" onclick="bukaModalUploadLembur()" style="background: #0284c7; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                            <i class="fas fa-upload"></i> Upload Lembur
                        </button>
                    </div>
                </div>

                <!-- Bulk actions row -->
                <div id="overtimeBulkActions" style="display: none; align-items: center; gap: 12px; background: #f8fafc; padding: 12px 20px; border-radius: 8px; border: 1px dashed #cbd5e1; width: 100%;">
                    <span style="font-size: 13px; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-check-square" style="color: var(--primary-color);"></i> 
                        <span id="otSelectedCount">0</span> log terpilih:
                    </span>
                    <button onclick="bulkApproveOvertime()" style="background: #10b981; color: white; border: none; border-radius: 6px; padding: 8px 16px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 13px; transition: all 0.2s;">
                        <i class="fas fa-check"></i> Setujui Masal (Approve)
                    </button>
                    <button onclick="bulkRejectOvertime()" style="background: #ef4444; color: white; border: none; border-radius: 6px; padding: 8px 16px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 13px; transition: all 0.2s;">
                        <i class="fas fa-times"></i> Tolak Masal (Reject)
                    </button>
                </div>
            </div>

            <!-- Overtime Summary Indicators -->
            <div id="otSummaryContainer" style="display: none; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 25px;">
                <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 15px; border-radius: 12px; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.01);">
                    <div style="background: #fef3c7; color: #d97706; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #b45309; font-weight: 600;">Menunggu Persetujuan</div>
                        <div id="otSummaryPending" style="font-size: 16px; font-weight: 700; color: #92400e;">0 Jam (0 logs)</div>
                    </div>
                </div>
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 12px; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.01);">
                    <div style="background: #dcfce7; color: #166534; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #15803d; font-weight: 600;">Telah Disetujui (Paid)</div>
                        <div id="otSummaryApproved" style="font-size: 16px; font-weight: 700; color: #166534;">0 Jam (0 logs)</div>
                    </div>
                </div>
                <div style="background: #fef2f2; border: 1px solid #fca5a5; padding: 15px; border-radius: 12px; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.01);">
                    <div style="background: #fee2e2; color: #991b1b; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #b91c1c; font-weight: 600;">Ditolak (Unpaid)</div>
                        <div id="otSummaryRejected" style="font-size: 16px; font-weight: 700; color: #991b1b;">0 Jam (0 logs)</div>
                    </div>
                </div>
            </div>

            <!-- Sub Tabs inside Overtime -->
            <div class="overtime-tabs-container" style="display: flex; gap: 8px; border-bottom: 2px solid #f1f5f9; margin-bottom: 25px; padding-bottom: 2px;">
                <button class="ot-tab-btn active" id="otTabPending" onclick="switchOvertimeSubTab('pending')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: var(--primary-color); cursor: pointer; border-bottom: 2px solid var(--primary-color); margin-bottom: -2px; transition: all 0.2s ease; outline: none;">
                    Persetujuan Lembur (Pending)
                </button>
                <button class="ot-tab-btn" id="otTabHistory" onclick="switchOvertimeSubTab('history')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s ease; outline: none;">
                    Riwayat Lembur (Approved / Rejected)
                </button>
            </div>

            <!-- Tab Content 1: Persetujuan Lembur (Pending) -->
            <div id="otSubPanelPending" class="ot-subpanel">
                <!-- Search Filter Row -->
                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; margin-bottom: 15px;">
                    <div style="position: relative; width: 260px;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="otPendingSearchInput" onkeyup="filterOvertimePending()" placeholder="Cari nama karyawan..." 
                               style="width: 100%; padding: 8px 12px 8px 32px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 13px; color: #334155; box-sizing: border-box;">
                    </div>
                </div>
                <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white; margin-bottom: 15px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="width: 40px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0;">
                                    <input type="checkbox" id="overtimeSelectAll" onchange="toggleSelectAllOvertime(this)" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--primary-color);">
                                </th>
                                <th style="width: 50px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">No</th>
                                <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Karyawan</th>
                                <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Tanggal</th>
                                <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Jam Lembur</th>
                                <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Hari Libur</th>
                                <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Keterangan</th>
                                <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Status</th>
                                <th style="width: 160px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="overtimePendingTableBody">
                            <tr>
                                <td colspan="9" style="text-align:center;padding:40px;color:#94a3b8;">
                                    <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
                                    Silakan pilih client terlebih dahulu untuk menampilkan data lembur.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content 2: Riwayat Lembur (Approved/Rejected) -->
            <div id="otSubPanelHistory" class="ot-subpanel" style="display: none;">
                <!-- Search & Status Filter Row -->
                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; margin-bottom: 15px;">
                    <div style="position: relative; width: 260px;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="otHistorySearchInput" onkeyup="filterOvertimeHistory()" placeholder="Cari nama karyawan..." 
                               style="width: 100%; padding: 8px 12px 8px 32px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 13px; color: #334155; box-sizing: border-box;">
                    </div>
                    <select id="otHistoryStatusFilter" onchange="filterOvertimeHistory()" 
                            style="padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 13px; color: #334155; background: white; font-weight: 600; cursor: pointer;">
                        <option value="">-- Semua Status --</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="width: 50px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">No</th>
                                <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Karyawan</th>
                                <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Tanggal</th>
                                <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Jam Lembur</th>
                                <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Hari Libur</th>
                                <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Keterangan</th>
                                <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Status</th>
                                <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Verifikator</th>
                                <th style="width: 120px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="overtimeHistoryTableBody">
                            <tr>
                                <td colspan="9" style="text-align:center;padding:40px;color:#94a3b8;">
                                    <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
                                    Silakan pilih client terlebih dahulu untuk menampilkan data lembur.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Panel 5: System Settings -->
    <div id="panelScheduleSystemSettings" class="schedule-subpanel" style="display: none;">
        <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white; max-width: 600px; margin: 0 auto;">
            <div class="section-header" style="margin-bottom: 25px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">
                        <i class="fas fa-sliders-h" style="color: var(--primary-color); margin-right: 8px;"></i>System Configuration Settings
                    </h3>
                    <p style="color: #64748b; font-size: 13px; margin: 0;">Adjust global variables and default values for the payroll calculation engine.</p>
                </div>
            </div>

            <form id="formSystemSettings" onsubmit="saveSystemSettings(event)">
                <div style="margin-bottom: 25px;">
                    <label style="font-weight: 600; color: #475569; display: block; margin-bottom: 8px; font-size: 14px;">
                        Overtime Hours Divisor (Default)
                    </label>
                    <div style="position: relative;">
                        <input type="number" id="settingOvertimeDivisor" required min="1" max="1000" placeholder="160" 
                               style="width: 100%; padding: 12px 14px; border: 1px solid #cbd5e0; border-radius: 8px; outline: none; font-size: 14px; box-sizing: border-box;">
                    </div>
                    <small style="color: #94a3b8; font-size: 12px; display: block; margin-top: 6px; line-height: 1.5;">
                        Standard hourly wage rate is calculated as: <code>Base Salary / Overtime Divisor</code>. 
                        Standard Depnaker regulation specifies 173 hours, but this application is configured to default to 160 hours based on internal company specifications. You can modify this divisor value dynamically here.
                    </small>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                    <button type="submit" class="btn-add" style="padding: 12px 24px; background: var(--primary-color); border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-save"></i> Save Configuration
                    </button>
                </div>
            </form>
        </div>

        <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white; max-width: 800px; margin: 20px auto 0 auto;">
            <div class="section-header" style="margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
                <h4 style="font-size: 16px; color: var(--secondary-color); font-weight: 700; margin: 0;">
                    <i class="fas fa-list" style="color: var(--primary-color); margin-right: 8px;"></i>Active System Settings List
                </h4>
            </div>
            
            <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="width: 60px; text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">No</th>
                            <th style="text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Setting Description</th>
                            <th style="text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Setting Key</th>
                            <th style="text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Value</th>
                            <th style="text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Last Updated</th>
                        </tr>
                    </thead>
                    <tbody id="systemSettingsTableBody">
                        <!-- Dynamic settings rows -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
