        <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
            <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                <div>
                    <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0; display: inline-flex; align-items: center; gap: 8px;">
                        Attendance Log
                        <button onclick="bukaModalAttendance()" style="background: var(--primary-color); color: white; border: none; border-radius: 50%; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.1);" onmouseover="this.style.transform='scale(1.15)'; this.style.background='var(--primary-dark)';" onmouseout="this.style.transform='scale(1)'; this.style.background='var(--primary-color)';" title="Manual Attendance Input">
                            <i class="fas fa-plus" style="font-size: 11px; color: white !important;"></i>
                        </button>
                    </h3>
                    <p style="color: #64748b; font-size: 13px; margin: 0;">Input and manage daily employee attendance.</p>
                </div>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <input type="hidden" id="attendanceClientSelect" value="">
                    
                    <select id="attendanceMonthSelect" onchange="loadAttendanceLogs()" style="padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer;">
                        <option value="1">January</option><option value="2">February</option><option value="3">March</option>
                        <option value="4">April</option><option value="5">May</option><option value="6">June</option>
                        <option value="7">July</option><option value="8">August</option><option value="9">September</option>
                        <option value="10">October</option><option value="11">November</option><option value="12">December</option>
                    </select>
                    <select id="attendanceYearSelect" onchange="loadAttendanceLogs()" style="padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer;">
                        <?php 
                        $currentYear = intval(date('Y'));
                        for ($y = $currentYear - 2; $y <= $currentYear + 3; $y++) {
                            $selected = ($y === $currentYear) ? 'selected' : '';
                            echo "<option value=\"$y\" $selected>$y</option>";
                        }
                        ?>
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

            <!-- Attendance Summary Cards (Hours Worked, Expected Hours, Masa Kerja) -->
            <div id="attendanceSummaryCardsPanel" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 20px;">
                <div onclick="showAttendanceSummaryDetail('actual')" title="Klik untuk melihat rincian Jam Kerja Aktual" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; border-radius: 10px; padding: 16px; display: flex; align-items: center; gap: 14px; box-shadow: 0 2px 6px rgba(37,99,235,0.06); cursor: pointer; transition: all 0.25s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 20px -5px rgba(37,99,235,0.25)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 6px rgba(37,99,235,0.06)';">
                    <div style="width: 46px; height: 46px; border-radius: 10px; background: #2563eb; color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(37,99,235,0.25);">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <span style="font-size: 11.5px; color: #1e40af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Jam Kerja Aktual</span>
                        <div class="summaryActualHoursVal" style="font-size: 22px; font-weight: 800; color: #1e3a8a; margin-top: 2px;">0 <span style="font-size: 13px; font-weight: 600;">Jam</span></div>
                        <span style="font-size: 11.5px; color: #3b82f6;">Total jam bekerja di bulan ini <i class="fas fa-arrow-right" style="font-size: 10px; margin-left: 2px;"></i></span>
                    </div>
                </div>

                <div onclick="showAttendanceSummaryDetail('expected')" title="Klik untuk melihat rincian Jam Kerja Seharusnya" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0; border-radius: 10px; padding: 16px; display: flex; align-items: center; gap: 14px; box-shadow: 0 2px 6px rgba(22,163,74,0.06); cursor: pointer; transition: all 0.25s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 20px -5px rgba(22,163,74,0.25)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 6px rgba(22,163,74,0.06)';">
                    <div style="width: 46px; height: 46px; border-radius: 10px; background: #16a34a; color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(22,163,74,0.25);">
                        <i class="fas fa-business-time"></i>
                    </div>
                    <div>
                        <span style="font-size: 11.5px; color: #166534; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Jam Kerja Seharusnya</span>
                        <div class="summaryExpectedHoursVal" style="font-size: 22px; font-weight: 800; color: #14532d; margin-top: 2px;">0 <span style="font-size: 13px; font-weight: 600;">Jam</span></div>
                        <span style="font-size: 11.5px; color: #22c55e;">Target standar hari kerja bulan ini <i class="fas fa-arrow-right" style="font-size: 10px; margin-left: 2px;"></i></span>
                    </div>
                </div>

                <div onclick="showAttendanceSummaryDetail('tenure')" title="Klik untuk melihat rincian Masa Kerja Karyawan" style="background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); border: 1px solid #e9d5ff; border-radius: 10px; padding: 16px; display: flex; align-items: center; gap: 14px; box-shadow: 0 2px 6px rgba(147,51,234,0.06); cursor: pointer; transition: all 0.25s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 20px -5px rgba(147,51,234,0.25)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 6px rgba(147,51,234,0.06)';">
                    <div style="width: 46px; height: 46px; border-radius: 10px; background: #9333ea; color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(147,51,234,0.25);">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <span style="font-size: 11.5px; color: #6b21a8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Masa Kerja Karyawan</span>
                        <div class="summaryTenureMonthsVal" style="font-size: 22px; font-weight: 800; color: #581c87; margin-top: 2px;">0 <span style="font-size: 13px; font-weight: 600;">Bulan</span></div>
                        <span style="font-size: 11.5px; color: #a855f7;">Rata-rata/Total akumulasi masa kerja <i class="fas fa-arrow-right" style="font-size: 10px; margin-left: 2px;"></i></span>
                    </div>
                </div>
            </div>

            <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="width: 60px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">No</th>
                            <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Employee</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Date</th>
                            <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Shift</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Clock In</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Clock Out</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Work Hours (Overtime)</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Early Arrival</th>
                            <th style="width: 120px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                        <tr>
                            <td colspan="9" style="text-align:center;padding:40px;color:#94a3b8;">
                                <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
                                Please select a client first to display attendance data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
