<!-- Section: Overtime Management -->
<div id="viewOvertime">
    <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
        <div class="section-header" style="margin-bottom: 25px; display: flex; flex-direction: column; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; width: 100%;">
                <div>
                    <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0; display: inline-flex; align-items: center; gap: 8px;">
                        Approval & Overtime Log
                        <button onclick="bukaModalOvertime()" style="background: var(--primary-color); color: white; border: none; border-radius: 50%; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.1);" onmouseover="this.style.transform='scale(1.15)'; this.style.background='var(--primary-dark)';" onmouseout="this.style.transform='scale(1)'; this.style.background='var(--primary-color)';" title="Manual Overtime Input">
                            <i class="fas fa-plus" style="font-size: 11px; color: white !important;"></i>
                        </button>
                    </h3>
                    <p style="color: #64748b; font-size: 13px; margin: 0;">Manage, verify, approve (Approve), or reject (Reject) daily employee overtime hours.</p>
                </div>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <select id="overtimeClientSelect" onchange="loadOvertimeLogs()" style="padding: 10px 16px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer; min-width: 200px;">
                        <option value="">-- Select Client --</option>
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
                    <button class="btn-add" onclick="bukaModalUploadLembur()" style="background: #27ae60; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-upload"></i> Upload Overtime
                    </button>
                    <button class="btn-add" onclick="downloadLemburTemplateMain()" style="background: #0284c7; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;" title="Download Overtime Excel Template">
                        <i class="fas fa-download"></i> Download Template
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

        <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="width: 40px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0;">
                            <input type="checkbox" id="overtimeSelectAll" onchange="toggleSelectAllOvertime(this)" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--primary-color);">
                        </th>
                        <th style="width: 50px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">No</th>
                        <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Employee</th>
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Tanggal</th>
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Overtime Hours</th>
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Holiday</th>
                        <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Keterangan</th>
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Status</th>
                        <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Verifikator</th>
                        <th style="width: 160px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="overtimeTableBody">
                    <!-- Dynamic rows -->
                </tbody>
            </table>
        </div>
    </div>
</div>
