        <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
            <div class="section-header" style="margin-bottom: 25px; display: flex; flex-direction: column; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; width: 100%;">
                    <div>
                        <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0; display: inline-flex; align-items: center; gap: 8px;">
                            Overtime Approval & Log
                            <button onclick="bukaModalOvertime()" style="background: var(--primary-color); color: white; border: none; border-radius: 50%; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.1);" onmouseover="this.style.transform='scale(1.15)'; this.style.background='var(--primary-dark)';" onmouseout="this.style.transform='scale(1)'; this.style.background='var(--primary-color)';" title="Input Lembur Manual">
                                <i class="fas fa-plus" style="font-size: 11px;"></i>
                            </button>
                        </h3>
                        <p style="color: #64748b; font-size: 13px; margin: 0;">Manage, verify, approve, or reject daily employee overtime hours.</p>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <input type="hidden" id="overtimeClientSelect" value="">
                        
                        <select id="overtimeMonthSelect" onchange="loadOvertimeLogs()" style="padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer;">
                            <option value="1">January</option><option value="2">February</option><option value="3">March</option>
                            <option value="4">April</option><option value="5">May</option><option value="6">June</option>
                            <option value="7">July</option><option value="8">August</option><option value="9">September</option>
                            <option value="10">October</option><option value="11">November</option><option value="12">December</option>
                        </select>
                        <select id="overtimeYearSelect" onchange="loadOvertimeLogs()" style="padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer;">
                            <?php 
                            $currentYear = intval(date('Y'));
                            for ($y = $currentYear - 2; $y <= $currentYear + 3; $y++) {
                                $selected = ($y === $currentYear) ? 'selected' : '';
                                echo "<option value=\"$y\" $selected>$y</option>";
                            }
                            ?>
                        </select>
                        <button class="btn-add" onclick="bukaModalUploadLembur()" style="background: #27ae60; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                            <i class="fas fa-upload"></i> Upload Overtime
                        </button>
                        <button class="btn-add" onclick="downloadLemburTemplateMain()" style="background: #0284c7; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;" title="Download Template Excel Lembur">
                            <i class="fas fa-download"></i> Download Template
                        </button>
                    </div>
                </div>

                <!-- Bulk actions row -->
                <div id="overtimeBulkActions" style="display: none; align-items: center; gap: 12px; background: #f8fafc; padding: 12px 20px; border-radius: 8px; border: 1px dashed #cbd5e1; width: 100%;">
                    <span style="font-size: 13px; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-check-square" style="color: var(--primary-color);"></i> 
                        <span id="otSelectedCount">0</span> logs selected:
                    </span>
                    <button onclick="approveSelectedOvertime()" style="background: #22c55e; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                        <i class="fas fa-check"></i> Approve All
                    </button>
                    <button onclick="rejectSelectedOvertime()" style="background: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                        <i class="fas fa-times"></i> Reject All
                    </button>
                </div>
            </div>

            <!-- Overtime Table -->
            <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                <table style="width: 100%; border-collapse: collapse; min-width: 1000px;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="width: 50px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0;">
                                <input type="checkbox" id="selectAllOvertime" onchange="toggleSelectAllOvertime(this)" style="cursor: pointer; width: 16px; height: 16px; border-radius: 4px; border: 1px solid #cbd5e1;">
                            </th>
                            <th style="width: 60px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">No</th>
                            <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Employee</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Date</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Hours</th>
                            <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Compensation Type</th>
                            <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Description / Purpose</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Status</th>
                            <th style="width: 150px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="overtimeTableBody">
                        <tr>
                            <td colspan="9" style="text-align:center;padding:40px;color:#94a3b8;">
                                <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
                                Please select a client first to display overtime data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
