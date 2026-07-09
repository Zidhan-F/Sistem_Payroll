        <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
            <!-- Header -->
            <div class="section-header" style="margin-bottom: 25px; display: flex; flex-direction: column; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; width: 100%;">
                    <div>
                        <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Persetujuan & Log Early Arrival</h3>
                        <p style="color: #64748b; font-size: 13px; margin: 0;">Manage, verify, approve, or reject employee early arrival hours.</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px; margin-bottom: 25px; align-items: end;">
                <?php if (($_COOKIE['user_role'] ?? '') === 'admin'): ?>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Client</label>
                    <select id="eaClientFilter" onchange="onEaClientChanged()" style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; font-size: 13px; outline: none; font-weight: 600; color: #334155; cursor: pointer;">
                        <option value="">-- Select Client --</option>
                    </select>
                </div>
                <?php else: ?>
                <input type="hidden" id="eaClientFilter" value="">
                <?php endif; ?>
                
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Month</label>
                    <select id="eaMonthFilter" onchange="loadEarlyArrivalLogs()" style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; font-size: 13px; outline: none; font-weight: 600; color: #334155; cursor: pointer;">
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Year</label>
                    <select id="eaYearFilter" onchange="loadEarlyArrivalLogs()" style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; font-size: 13px; outline: none; font-weight: 600; color: #334155; cursor: pointer;">
                        <option value="2025">2025</option>
                        <option value="2026" selected>2026</option>
                        <option value="2027">2027</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Status</label>
                    <select id="eaStatusFilter" onchange="loadEarlyArrivalLogs()" style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; font-size: 13px; outline: none; font-weight: 600; color: #334155; cursor: pointer;">
                        <option value="">Semua Status</option>
                        <option value="Pending" selected>Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                <div style="grid-column: span 1; display: flex; gap: 8px;">
                    <button class="btn-add" onclick="bukaModalUploadEarlyArrival()" style="background: #27ae60; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 14px; cursor: pointer; transition: all 0.3s; width: 100%; font-size: 13px; min-height: 40px; box-sizing: border-box;">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                    <button class="btn-add" onclick="downloadEarlyArrivalTemplateMain()" style="background: #0284c7; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 14px; cursor: pointer; transition: all 0.3s; width: 100%; font-size: 13px; min-height: 40px; box-sizing: border-box;" title="Download Template Excel Early Arrival">
                        <i class="fas fa-download"></i> Template
                    </button>
                </div>
            </div>

            <!-- Bulk actions row -->
            <div id="eaBulkActions" style="display: none; align-items: center; gap: 12px; background: #f8fafc; padding: 12px 20px; border-radius: 8px; border: 1px dashed #cbd5e1; margin-bottom: 20px;">
                <span style="font-size: 13px; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check-square" style="color: var(--primary-color);"></i> 
                    <span id="eaSelectedCount">0</span> log terpilih:
                </span>
                <button onclick="approveSelectedEarlyArrival()" style="background: #22c55e; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                    <i class="fas fa-check"></i> Setujui Semua
                </button>
                <button onclick="rejectSelectedEarlyArrival()" style="background: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                    <i class="fas fa-times"></i> Tolak Semua
                </button>
            </div>

            <!-- Table -->
            <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                <table style="width: 100%; border-collapse: collapse; min-width: 1100px;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="width: 50px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0;">
                                <input type="checkbox" id="selectAllEa" onchange="toggleSelectAllEa(this)" style="cursor: pointer; width: 16px; height: 16px; border-radius: 4px; border: 1px solid #cbd5e1;">
                            </th>
                            <th style="width: 60px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">No</th>
                            <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Employee</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Date</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Shift / Check In</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Menit Awal</th>
                            <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Alasan / Deskripsi</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Status</th>
                            <th style="width: 160px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="eaTableBody">
                        <tr>
                            <td colspan="9" style="text-align:center;padding:40px;color:#94a3b8;">
                                <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
                                Please select a client first to display history data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
