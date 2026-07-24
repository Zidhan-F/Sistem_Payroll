    <!-- Payslip Modal -->
    <div id="overlay" onclick="tutupSemuaModal()"></div>

    <div id="modalSlip" class="modal-skema" style="width: 600px; max-width: 95%; display: none; z-index: 2000;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3>Pay Slip</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white;" onclick="tutupModalSlip()"></i>
        </div>
        <div class="modal-body" style="padding: 25px; max-height: 70vh; overflow-y: auto;">
            <div id="slipContent">
                <!-- Dynamically populated by JS -->
            </div>
        </div>
        <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <button type="button" class="btn-add" onclick="downloadSlip()" style="background: #28a745; color: white; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; padding: 10px 20px; border: none; cursor: pointer;">
                <i class="fas fa-download"></i> Download PDF
            </button>
            <button type="button" class="btn-cancel" onclick="tutupModalSlip()" style="padding: 10px 24px; border-radius: 8px; margin: 0;">Close</button>
        </div>
    </div>

    <!-- BPJS Calculation Detail Modal -->
    <div id="modalDetailBpjs" class="modal-skema" style="display: none; width: 750px; max-width: 95%; z-index: 2005;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: white;">BPJS Calculation Details</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white;" onclick="tutupDetailBpjsModal()"></i>
        </div>
        <div class="modal-body" style="padding: 25px; max-height: 70vh; overflow-y: auto;">
            <div style="margin-bottom: 20px; font-size: 14px; color: #475569; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div><strong>Employee Name:</strong> <span id="bpjsModalEmployeeName" style="color: #1e293b; font-weight: 600;">-</span></div>
                <div style="text-align: right;"><strong>Period:</strong> <span id="bpjsModalPeriod" style="color: #1e293b; font-weight: 600;">-</span></div>
            </div>
            <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                    <thead>
                        <tr style="background: #e2e8f0; border-bottom: 2px solid #cbd5e1; color: #475569; font-weight: 700;">
                            <th style="padding: 12px 10px;">Program BPJS</th>
                            <th style="padding: 12px 10px; text-align: right;">Basis Wage</th>
                            <th style="padding: 12px 10px; text-align: right;">Employee Contribution</th>
                            <th style="padding: 12px 10px; text-align: right;">Company Contribution</th>
                            <th style="padding: 12px 10px; text-align: right;">Total Contribution</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px 10px; font-weight: 600; color: #1e293b;">BPJS Health</td>
                            <td style="padding: 12px 10px; text-align: right; font-variant-numeric: tabular-nums;" id="bpjsKesBase">Rp 0</td>
                            <td style="padding: 12px 10px; text-align: right; color: #ef4444; font-variant-numeric: tabular-nums;" id="bpjsKesEmp">Rp 0 (1%)</td>
                            <td style="padding: 12px 10px; text-align: right; color: #475569; font-variant-numeric: tabular-nums;" id="bpjsKesCo">Rp 0 (4%)</td>
                            <td style="padding: 12px 10px; text-align: right; font-weight: 600; color: #0f172a; font-variant-numeric: tabular-nums;" id="bpjsKesTotal">Rp 0 (5%)</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px 10px; font-weight: 600; color: #1e293b;">BPJS TK - JHT</td>
                            <td style="padding: 12px 10px; text-align: right; font-variant-numeric: tabular-nums;" id="bpjsJhtBase">Rp 0</td>
                            <td style="padding: 12px 10px; text-align: right; color: #ef4444; font-variant-numeric: tabular-nums;" id="bpjsJhtEmp">Rp 0 (2%)</td>
                            <td style="padding: 12px 10px; text-align: right; color: #475569; font-variant-numeric: tabular-nums;" id="bpjsJhtCo">Rp 0 (3.7%)</td>
                            <td style="padding: 12px 10px; text-align: right; font-weight: 600; color: #0f172a; font-variant-numeric: tabular-nums;" id="bpjsJhtTotal">Rp 0 (5.7%)</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px 10px; font-weight: 600; color: #1e293b;">BPJS TK - JP</td>
                            <td style="padding: 12px 10px; text-align: right; font-variant-numeric: tabular-nums;" id="bpjsJpBase">Rp 0</td>
                            <td style="padding: 12px 10px; text-align: right; color: #ef4444; font-variant-numeric: tabular-nums;" id="bpjsJpEmp">Rp 0 (1%)</td>
                            <td style="padding: 12px 10px; text-align: right; color: #475569; font-variant-numeric: tabular-nums;" id="bpjsJpCo">Rp 0 (2%)</td>
                            <td style="padding: 12px 10px; text-align: right; font-weight: 600; color: #0f172a; font-variant-numeric: tabular-nums;" id="bpjsJpTotal">Rp 0 (3%)</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px 10px; font-weight: 600; color: #1e293b;">BPJS TK - JKK</td>
                            <td style="padding: 12px 10px; text-align: right; font-variant-numeric: tabular-nums;" id="bpjsJkkBase">Rp 0</td>
                            <td style="padding: 12px 10px; text-align: right; color: #94a3b8; font-variant-numeric: tabular-nums;" id="bpjsJkkEmp">Rp 0 (0%)</td>
                            <td style="padding: 12px 10px; text-align: right; color: #475569; font-variant-numeric: tabular-nums;" id="bpjsJkkCo">Rp 0 (0.24%)</td>
                            <td style="padding: 12px 10px; text-align: right; font-weight: 600; color: #0f172a; font-variant-numeric: tabular-nums;" id="bpjsJkkTotal">Rp 0</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px 10px; font-weight: 600; color: #1e293b;">BPJS TK - JKM</td>
                            <td style="padding: 12px 10px; text-align: right; font-variant-numeric: tabular-nums;" id="bpjsJkmBase">Rp 0</td>
                            <td style="padding: 12px 10px; text-align: right; color: #94a3b8; font-variant-numeric: tabular-nums;" id="bpjsJkmEmp">Rp 0 (0%)</td>
                            <td style="padding: 12px 10px; text-align: right; color: #475569; font-variant-numeric: tabular-nums;" id="bpjsJkmCo">Rp 0 (0.30%)</td>
                            <td style="padding: 12px 10px; text-align: right; font-weight: 600; color: #0f172a; font-variant-numeric: tabular-nums;" id="bpjsJkmTotal">Rp 0</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8fafc; font-weight: 700; color: #0f172a; border-top: 2px solid #cbd5e1;">
                            <td style="padding: 12px 10px;">Grand Total</td>
                            <td style="padding: 12px 10px; text-align: right;">-</td>
                            <td style="padding: 12px 10px; text-align: right; color: #ef4444; font-variant-numeric: tabular-nums;" id="bpjsGrandEmp">Rp 0</td>
                            <td style="padding: 12px 10px; text-align: right; color: #3b82f6; font-variant-numeric: tabular-nums;" id="bpjsGrandCo">Rp 0</td>
                            <td style="padding: 12px 10px; text-align: right; color: #10b981; font-variant-numeric: tabular-nums;" id="bpjsGrandTotal">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 15px; font-size: 12px; color: #1e40af; line-height: 1.6;">
                <h5 style="margin: 0 0 8px 0; font-weight: 700; font-size: 13px;"><i class="fas fa-info-circle"></i> BPJS Regulation Notes:</h5>
                <ul style="margin: 0; padding-left: 18px;">
                    <li><strong>BPJS Health:</strong> Maximum wage limit Rp 12,000,000 (total contribution 5%: 4% company, 1% employee).</li>
                    <li><strong>BPJS Employment (JP):</strong> Maximum wage limit Rp 10,024,600 (total contribution 3%: 2% company, 1% employee).</li>
                    <li><strong>BPJS Employment (JKK & JKM):</strong> 100% covered by company (JKK 0.24%, JKM 0.30% of basis wage).</li>
                    <li>Company BPJS contributions are informational and do not deduct from employee Take Home Pay.</li>
                </ul>
            </div>
        </div>
        <div class="modal-footer" style="padding: 15px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <button type="button" class="btn-cancel" onclick="tutupDetailBpjsModal()" style="margin: 0; padding: 10px 20px;">Close</button>
        </div>
    </div>

    <!-- Payroll Scheme Form Modal -->
    <div id="modalSkema" class="modal-skema" style="width: 1100px; max-width: 95%;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="modalSkemaTitle">Add Payroll Scheme</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalSkema()"></i>
        </div>
        <form id="formSkema">
            <div class="modal-body" style="padding: 25px;">
                <input type="hidden" id="skemaId">
                <input type="hidden" id="skemaIsPersentase" value="0">
                <input type="hidden" id="skemaTipe" value="bulanan">

                <!-- Scheme Name (Full Width) -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; font-size: 14px; color: #475569; display: block; margin-bottom: 8px;">Scheme Name</label>
                    <input type="text" id="skemaNama" placeholder="Enter Scheme Name" required style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #ddd; outline: none; font-size: 14px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                </div>

                <!-- Two Column Layout: Left (Allowances), Right (Attendance Scheme & Description) -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 20px;">
                    
                    <!-- Left Column: Allowances -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; gap: 15px;">
                        <h4 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 700; color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Allowances</h4>
                        
                        <!-- Basic Salary -->
                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group" style="margin: 0;">
                                <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Basic Salary Source</label>
                                <select id="skemaSumber" onchange="handlePayrollSchemeSumberNilaiChange()" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white; height: 42px;">
                                    <option value="ump">UMP (Province)</option>
                                    <option value="umk">UMK (City/Regency)</option>
                                    <option value="nominal" selected>Custom Nominal</option>
                    </select>
                </div>
                            <div class="form-group" style="margin: 0;">
                                <label id="labelNilaiSkemaPayroll" style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Basic Salary (IDR)</label>
                                 <input type="text" id="skemaNilai" placeholder="Enter Basic Salary" onkeyup="handleSkemaNilaiInput(this)" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
            </div>
                        </div>

                        <!-- Salary Period -->
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Salary Period</label>
                            <select id="skemaPeriode" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white; height: 42px;">
                                <option value="bulan" selected>Per Month</option>
                                <option value="minggu">Per Week</option>
                                <option value="hari_kerja">Per Working Day</option>
                                <option value="tahun">Per Year</option>
                            </select>
                        </div>

                        <!-- Fixed Allowance Table -->
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label style="font-weight: 600; font-size: 13px; color: #475569; margin: 0;">Fixed Allowance</label>
                                <button type="button" onclick="bukaModalPilihSkema('tetap')" style="background: none; border: none; color: #0d6efd; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: underline; padding: 0;">Select Scheme</button>
                            </div>
                            
                            <div style="max-height: 150px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; background: white;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #0d6efd; color: white;">
                                            <th style="padding: 8px 12px; font-size: 12px; text-align: left; font-weight: 600; width: 60%;">Name</th>
                                            <th style="padding: 8px 12px; font-size: 12px; text-align: right; font-weight: 600; width: 40%;">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelKompensasiTetapBody">
                                        <tr>
                                            <td colspan="2" style="padding: 12px; text-align: center; color: #94a3b8; font-size: 13px;">No allowance scheme selected yet</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Non-Fixed Allowance Table -->
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label style="font-weight: 600; font-size: 13px; color: #475569; margin: 0;">Non-Fixed Allowance</label>
                                <button type="button" onclick="bukaModalPilihSkema('tidak_tetap')" style="background: none; border: none; color: #0d6efd; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: underline; padding: 0;">Select Scheme</button>
                            </div>
                            
                            <div style="max-height: 150px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; background: white;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #0d6efd; color: white;">
                                            <th style="padding: 8px 12px; font-size: 12px; text-align: left; font-weight: 600; width: 60%;">Name</th>
                                            <th style="padding: 8px 12px; font-size: 12px; text-align: right; font-weight: 600; width: 40%;">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelKompensasiTidakTetapBody">
                                        <tr>
                                            <td colspan="2" style="padding: 12px; text-align: center; color: #94a3b8; font-size: 13px;">No allowance scheme selected yet</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>


                    </div>

                    <!-- Right Column: Attendance Scheme & Description -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; gap: 15px;">
                        <h4 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 700; color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Attendance Scheme</h4>
                        
                        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; display: flex; flex-direction: column; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500; cursor: pointer; color: #1e293b; margin: 0;">
                                <input type="radio" name="skemaAbsenRule" value="prorate" onchange="handleSkemaAbsenRuleChange()" style="cursor: pointer; width: 18px; height: 18px;">
                                Prorate
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500; cursor: pointer; color: #1e293b; margin: 0;">
                                <input type="radio" name="skemaAbsenRule" value="tidak_potong" onchange="handleSkemaAbsenRuleChange()" style="cursor: pointer; width: 18px; height: 18px;">
                                Attendance Does Not Deduct Salary
                            </label>
                            
                            <div style="display: flex; align-items: center; gap: 10px; margin: 0;">
                                <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500; cursor: pointer; color: #1e293b; margin: 0; flex-shrink: 0;">
                                    <input type="radio" name="skemaAbsenRule" value="potong_nominal" onchange="handleSkemaAbsenRuleChange()" style="cursor: pointer; width: 18px; height: 18px;">
                                    Attendance Deducts Nominal
                                </label>
                                <input type="text" id="skemaNominalPotongan" placeholder="Example: 100000" onkeyup="formatRupiahInput(this)" onfocus="document.querySelector('input[name=\'skemaAbsenRule\'][value=\'potong_nominal\']').checked = true; handleSkemaAbsenRuleChange();" style="flex-grow: 1; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                            </div>
                            
                            <!-- Grace Periods -->
                            <div style="display: flex; gap: 12px; margin-top: 5px;">
                                <div style="flex: 1;">
                                    <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Toleransi Terlambat (Menit)</label>
                                    <input type="number" id="skemaGraceLate" min="0" value="0" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                                </div>
                                <input type="hidden" id="skemaGraceEarly" value="0">
                            </div>

                            <!-- Denda -->
                            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 15px;">
                                <label style="font-weight: 700; font-size: 13px; color: #1e293b; display: flex; align-items: center; gap: 6px; margin: 0;">
                                    <i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i> Konfigurasi Denda
                                </label>
                                <div class="form-group" style="margin: 0;">
                                    <label style="font-weight: 600; font-size: 12px; color: #475569; display: block; margin-bottom: 4px;">
                                        Denda Terlambat / Jam (Rp)
                                        <span style="font-weight: 400; color: #94a3b8;"> — ceiling per jam (&lt; 1 jam = 1 jam)</span>
                                    </label>
                                    <input type="text" id="skemaDendaTerlambatPerJam" placeholder="0" onkeyup="formatRupiahInput(this)" value="0" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                                </div>
                                <input type="hidden" id="skemaDendaAlfaPerHari" value="0">
                            </div>
                        </div>

                        <!-- Overtime Configuration -->
                        <div style="margin: 0; flex-grow: 1; display: flex; flex-direction: column;">
                            <label style="font-weight: 700; font-size: 13px; color: #1e293b; display: flex; align-items: center; gap: 6px; margin: 0 0 10px 0;">
                                <i class="fas fa-clock" style="color: #3b82f6;"></i> Overtime Configuration
                            </label>
                            <input type="hidden" id="skemaDeskripsi" value="">
                            
                            <!-- Type Selector -->
                            <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                                <label style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 14px; border-radius: 8px; border: 2px solid #e2e8f0; cursor: pointer; font-size: 13px; font-weight: 600; color: #475569; transition: all 0.2s; background: white;" id="labelOvertimeStandard" onclick="setOvertimeType('standard')">
                                    <input type="radio" name="skemaOvertimeType" value="standard" checked onchange="handleOvertimeTypeChange()" style="display: none;">
                                    <i class="fas fa-balance-scale" style="font-size: 14px;"></i> Standard
                                </label>
                                <label style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 14px; border-radius: 8px; border: 2px solid #e2e8f0; cursor: pointer; font-size: 13px; font-weight: 600; color: #475569; transition: all 0.2s; background: white;" id="labelOvertimeLumpsum" onclick="setOvertimeType('lumpsum')">
                                    <input type="radio" name="skemaOvertimeType" value="lumpsum" onchange="handleOvertimeTypeChange()" style="display: none;">
                                    <i class="fas fa-hand-holding-usd" style="font-size: 14px;"></i> Lumpsum
                                </label>
                            </div>

                            <!-- Standard Mode Info -->
                            <div id="overtimeStandardPanel" style="background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%); border: 1px solid #bfdbfe; border-radius: 10px; padding: 14px; flex-grow: 1; display: flex; flex-direction: column; gap: 12px;">
                                <!-- Hari Kerja Section -->
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                        <i class="fas fa-briefcase" style="color: #3b82f6; font-size: 13px;"></i>
                                        <span style="font-weight: 700; font-size: 12px; color: #1e40af;">Hari Kerja (Reguler)</span>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 6px;">
                                        <div style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: white; border-radius: 8px; border: 1px solid #dbeafe;">
                                            <span style="background: #3b82f6; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; min-width: 55px; text-align: center;">1 jam</span>
                                            <span style="font-size: 12px; color: #334155; font-weight: 500;">1.5× Upah / Jam</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: white; border-radius: 8px; border: 1px solid #dbeafe;">
                                            <span style="background: #2563eb; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; min-width: 55px; text-align: center;">2-3 jam</span>
                                            <span style="font-size: 12px; color: #334155; font-weight: 500;">2.0× Upah / Jam</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: #fffbeb; border-radius: 8px; border: 1px solid #fef3c7;">
                                            <i class="fas fa-exclamation-circle" style="color: #d97706; font-size: 11px;"></i>
                                            <span style="font-size: 11px; color: #92400e; font-weight: 500;">Max 3 Overtime Hours / Day</span>
                                        </div>
                                    </div>
                                </div>
 
                                <!-- Divider line -->
                                <div style="border-top: 1px dashed #bfdbfe; margin: 2px 0;"></div>
 
                                <!-- Hari Libur Section -->
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                        <i class="fas fa-calendar-minus" style="color: #ef4444; font-size: 13px;"></i>
                                        <span style="font-weight: 700; font-size: 12px; color: #b91c1c;">Holidays (Saturday, Sunday & Public Holidays)</span>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 6px;">
                                        <div style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: white; border-radius: 8px; border: 1px solid #fee2e2;">
                                            <span style="background: #ef4444; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; min-width: 55px; text-align: center;">1-6 Jam</span>
                                            <span style="font-size: 12px; color: #334155; font-weight: 500;">2.0× Upah / Jam</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: white; border-radius: 8px; border: 1px solid #fee2e2;">
                                            <span style="background: #dc2626; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; min-width: 55px; text-align: center;">7 Jam</span>
                                            <span style="font-size: 12px; color: #334155; font-weight: 500;">3.0× Upah / Jam</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: white; border-radius: 8px; border: 1px solid #fee2e2;">
                                            <span style="background: #991b1b; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; min-width: 55px; text-align: center;">8 jam</span>
                                            <span style="font-size: 12px; color: #334155; font-weight: 500;">4.0× Upah / Jam</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 6px; padding: 4px 6px; font-size: 11px; color: #64748b;">
                                            <i class="fas fa-info-circle" style="color: #64748b;"></i>
                                            <span>Saturday (if 5-day work week), Sunday, & National Holidays</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Divisor Info -->
                                <div style="margin-top: 2px; font-size: 11px; color: #64748b; line-height: 1.4; display: flex; align-items: center; gap: 4px;">
                                    <i class="fas fa-calculator"></i>
                                    <span>Upah/Jam = Gaji Pokok / 173 (PP 35/2021)</span>
                                </div>
                            </div>

                            <!-- Lumpsum Mode Options -->
                            <div id="overtimeLumpsumPanel" style="display: none; flex-direction: column; gap: 10px; flex-grow: 1;">
                                <!-- Lumpsum Sub-type Radio Options -->
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer; font-size: 13px; font-weight: 500; color: #334155; background: white; transition: all 0.2s;" onclick="setLumpsumSubtype('per_jam')" id="labelLumpsumPerJam">
                                        <input type="radio" name="skemaLumpsumSubtype" value="per_jam" checked onchange="handleLumpsumSubtypeChange()" style="cursor: pointer; width: 16px; height: 16px; accent-color: #3b82f6;">
                                        <i class="fas fa-clock" style="color: #3b82f6; width: 16px; text-align: center;"></i> Per Jam
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer; font-size: 13px; font-weight: 500; color: #334155; background: white; transition: all 0.2s;" onclick="setLumpsumSubtype('harian')" id="labelLumpsumHarian">
                                        <input type="radio" name="skemaLumpsumSubtype" value="harian" onchange="handleLumpsumSubtypeChange()" style="cursor: pointer; width: 16px; height: 16px; accent-color: #3b82f6;">
                                        <i class="fas fa-calendar-day" style="color: #10b981; width: 16px; text-align: center;"></i> Harian
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer; font-size: 13px; font-weight: 500; color: #334155; background: white; transition: all 0.2s;" onclick="setLumpsumSubtype('bulanan')" id="labelLumpsumBulanan">
                                        <input type="radio" name="skemaLumpsumSubtype" value="bulanan" onchange="handleLumpsumSubtypeChange()" style="cursor: pointer; width: 16px; height: 16px; accent-color: #3b82f6;">
                                        <i class="fas fa-calendar-alt" style="color: #8b5cf6; width: 16px; text-align: center;"></i> Bulanan
                                    </label>
                                </div>

                                <!-- Nominal Input -->
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                                    <label id="labelLumpsumNominal" style="font-weight: 600; font-size: 12px; color: #475569; display: block; margin-bottom: 6px;">
                                        Hourly Overtime Rate (Rp)
                                    </label>
                                    <div style="position: relative;">
                                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 14px; font-weight: 600; color: #94a3b8;">Rp</span>
                                        <input type="text" id="skemaLumpsumNominal" placeholder="0" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px 10px 10px 36px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px; font-weight: 600;">
                                    </div>
                                </div>
                            </div>

                            <!-- Overtime Limits (Min Overtime & Early Arrival) -->
                            <div style="margin-top: 15px; border-top: 1px dashed #bfdbfe; padding-top: 15px; display: flex; gap: 12px;">
                                <div style="flex: 1;">
                                    <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Min. Overtime (Minutes)</label>
                                    <input type="number" id="skemaMinOvertime" min="0" value="30" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                                </div>
                                <div style="flex: 1;">
                                    <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Max Early Arrival Limit (Minutes)</label>
                                    <input type="number" id="skemaMaxEarlyArrivalMinutes" min="0" value="180" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <button type="button" class="btn-cancel" onclick="tutupModalSkema()" style="padding: 10px 24px; border-radius: 8px;">Cancel</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color); box-shadow: 0 4px 6px rgba(243, 156, 18, 0.2); padding: 10px 24px; border-radius: 8px;">Save</button>
            </div>
        </form>
    </div>

    <!-- Allowance Payroll Form Modal -->
    <div id="modalKomponen" class="modal-skema">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalKomponenTitle">Add Allowance</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalKomponen()"></i>
        </div>
        <form id="formKomponen">
            <div class="modal-body">
                <input type="hidden" id="komponenId">
                <input type="hidden" id="komponenSchemeId">
                <div class="form-group">
                    <label>Category</label>
                    <select id="komponenKategori" required onchange="onKategoriChange()">
                        <option value="gaji">Basic Salary</option>
                        <option value="tunjangan">Allowance</option>
                        <option value="insentif">Incentive</option>
                        <option value="lembur">Overtime</option>
                        <option value="absensi">Attendance Deduction</option>
                        <option value="bpjs_kesehatan">BPJS Health</option>
                        <option value="bpjs_ketenagakerjaan">BPJS Employment</option>
                        <option value="lainnya">Others</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Allowance Name</label>
                    <input type="text" id="komponenNama" placeholder="Example: Basic Salary" required>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select id="komponenTipe" required>
                        <option value="pendapatan">Income (+)</option>
                        <option value="potongan">Deduction (-)</option>
                    </select>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Value (IDR or %)</label>
                         <input type="text" id="komponenNilai" placeholder="0" value="0" onkeyup="handleKomponenNilaiInput(this)">
                    </div>
                    <div class="form-group">
                        <label>Unit</label>
                        <select id="komponenIsPersentase" onchange="handleKomponenNilaiInput(document.getElementById('komponenNilai'))">
                            <option value="false">Rupiah (IDR)</option>
                            <option value="true">Percentage (%)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description (optional)</label>
                    <input type="text" id="komponenKeterangan" placeholder="Additional notes">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalKomponen()">Cancel</button>
                <button type="submit" class="btn-save">Save</button>
            </div>
        </form>
    </div>

    <!-- Client Form Modal -->
    <div id="modalClient">
        <div class="modal-header">
            <h3 id="modalTitle">Add Client Data</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModal()"></i>
        </div>
        <form id="formKlien">
            <div class="modal-body">
                <input type="hidden" id="clientId">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Client Name</label>
                        <input type="text" id="namaKlien" placeholder="Enter client name" required>
                    </div>
                    <div class="form-group">
                        <label>Client Email</label>
                        <input type="email" id="emailKlien" placeholder="client@gmail.com" required>
                    </div>
                    <div class="form-group">
                        <label>Select Client Sector</label>
                        <select id="sektorKlien" required>
                            <option value="">-- Select Sector --</option>
                            <option value="Retail">Retail</option>
                            <option value="Manufaktur">Manufacturing</option>
                            <option value="Jasa">Services</option>
                            <option value="Teknologi">Technology</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Business Registration Number (NIB)</label>
                        <input type="text" id="nib" placeholder="Enter NIB" required>
                    </div>
                    <div class="form-group">
                        <label>Tax ID (NPWP)</label>
                        <input type="text" id="npwp" placeholder="Enter NPWP" required>
                    </div>
                    <div class="form-group">
                        <label>Join Date</label>
                        <input type="date" id="tanggalBergabung" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Address</label>
                        <textarea id="alamat" rows="3" placeholder="Enter complete address" required></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModal()">Cancel</button>
                <button type="submit" id="btnSubmit" class="btn-save">Save</button>
            </div>
        </form>
    </div>

    <!-- Organization Form Modal -->
    <div id="modalOrg" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; width: 450px; border-radius: 12px; z-index: 1100; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <div class="modal-header">
            <h3 id="modalOrgTitle">Add Division</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalOrg()"></i>
        </div>
        <form id="formOrg">
            <div class="modal-body">
                <input type="hidden" id="orgId">
                <input type="hidden" id="orgParentId">
                <input type="hidden" id="orgType">

                <div class="form-group">
                    <label id="labelOrgName">Division Name</label>
                    <input type="hidden" id="orgName">
                    <select id="orgNameSelect" style="width: 100%;" required>
                        <option value="">-- Select Name --</option>
                    </select>
                    <div id="quickBadgeContainer" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px; display: none;"></div>
                </div>
                <!-- Extra Fields (Hanya untuk Posisi) -->
                <div id="posExtraFields" style="display: none;">
                    <div class="form-group">
                        <label>Position Level</label>
                        <select id="posLevel" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none; font-size: 14px;">
                            <option value="">-- Select Level --</option>
                            <option value="Intern">Intern</option>
                            <option value="Junior">Junior</option>
                            <option value="Staff">Staff</option>
                            <option value="Staff Senior">Senior Staff</option>
                            <option value="Assistant Manager">Assistant Manager</option>
                            <option value="Manager">Manager</option>
                            <option value="Lead">Lead</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalOrg()">Cancel</button>
                <button type="submit" class="btn-save">Save</button>
            </div>
        </form>
    </div>

    <!-- BPJS Scheme Form Modal -->
    <div id="modalBpjs" class="modal-skema" style="width: 500px; max-width: 95%;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="modalBpjsTitle">Add BPJS Scheme</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalBpjs()"></i>
        </div>
        <form id="formBpjs">
            <div class="modal-body" style="padding: 25px; display: flex; flex-direction: column; gap: 15px; max-height: 70vh; overflow-y: auto;">
                <input type="hidden" id="bpjsId">
                <input type="hidden" id="bpjsTipe" value="bpjs">
                
                <div class="form-group" style="margin: 0;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">BPJS Scheme Name</label>
                    <input type="text" id="bpjsNama" placeholder="Example: Standard BPJS Scheme" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                </div>

                <!-- BPJS Health -->
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: #f8fafc;">
                    <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #334155;"><i class="fas fa-hand-holding-medical" style="color: var(--primary-color);"></i> BPJS Health</h5>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 600; color: #64748b;">Employee Share (%)</label>
                            <input type="number" step="0.01" id="bpjsKesKaryawan" value="1.00" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; height: 36px;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 600; color: #64748b;">Company Share (%)</label>
                            <input type="number" step="0.01" id="bpjsKesPerusahaan" value="4.00" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; height: 36px;">
                        </div>
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 600; color: #64748b;">BPJS Health Max Salary Limit (IDR)</label>
                        <input type="text" id="bpjsKesMaxSalary" value="12.000.000" onkeyup="formatRupiahInput(this)" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; height: 36px;">
                    </div>
                </div>

                <!-- BPJS Employment (JHT & JP) -->
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: #f8fafc;">
                    <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #334155;"><i class="fas fa-shield-alt" style="color: var(--info);"></i> BPJS Employment</h5>
                    
                    <!-- JHT -->
                    <div style="border-bottom: 1px dashed #e2e8f0; padding-bottom: 8px; margin-bottom: 8px;">
                        <span style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">JHT (Old Age Security)</span>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-size: 11px; font-weight: 600; color: #64748b;">Employee (%)</label>
                                <input type="number" step="0.01" id="bpjsJhtKaryawan" value="2.00" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; height: 36px;">
                            </div>
                            <div>
                                <label style="font-size: 11px; font-weight: 600; color: #64748b;">Company (%)</label>
                                <input type="number" step="0.01" id="bpjsJhtPerusahaan" value="3.70" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; height: 36px;">
                            </div>
                        </div>
                    </div>
                    
                    <!-- JP -->
                    <div style="border-bottom: 1px dashed #e2e8f0; padding-bottom: 8px; margin-bottom: 8px;">
                        <span style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">JP (Pension Security)</span>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 6px;">
                            <div>
                                <label style="font-size: 11px; font-weight: 600; color: #64748b;">Employee (%)</label>
                                <input type="number" step="0.01" id="bpjsJpKaryawan" value="1.00" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; height: 36px;">
                            </div>
                            <div>
                                <label style="font-size: 11px; font-weight: 600; color: #64748b;">Company (%)</label>
                                <input type="number" step="0.01" id="bpjsJpPerusahaan" value="2.00" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; height: 36px;">
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 600; color: #64748b;">BPJS Pension Max Salary Limit (IDR)</label>
                            <input type="text" id="bpjsJpMaxSalary" value="10.024.600" onkeyup="formatRupiahInput(this)" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; height: 36px;">
                        </div>
                    </div>

                    <!-- JKK & JKM -->
                    <div>
                        <span style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">JKK & JKM (Borne by Company)</span>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-size: 11px; font-weight: 600; color: #64748b;">JKK (%)</label>
                                <input type="number" step="0.001" id="bpjsJkkPerusahaan" value="0.24" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; height: 36px;">
                            </div>
                            <div>
                                <label style="font-size: 11px; font-weight: 600; color: #64748b;">JKM (%)</label>
                                <input type="number" step="0.01" id="bpjsJkmPerusahaan" value="0.30" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; height: 36px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <button type="button" class="btn-cancel" onclick="tutupModalBpjs()">Cancel</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Save</button>
            </div>
        </form>
    </div>

       <!-- PPh 21 Scheme Form Modal -->
    <div id="modalPph21" class="modal-skema" style="display: none; width: 500px; max-width: 95%; z-index: 2005; transition: width 0.3s ease;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="modalPph21Title">Add PPh 21 Scheme</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalPph21()"></i>
        </div>
        <form id="formPph21">
            <div id="modalPph21Body" class="modal-body" style="padding: 24px; display: grid; grid-template-columns: 1fr; gap: 24px; background: #f8fafc; max-height: 72vh; overflow-y: auto; transition: all 0.3s ease;">
                
                <!-- Left Column: Inputs -->
                <div style="display: flex; flex-direction: column; gap: 16px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); height: fit-content;">
                    <input type="hidden" id="pph21Id">
                    <input type="hidden" id="pph21Tipe" value="pph21">
                    
                    <div class="form-group" style="margin: 0;">
                        <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">PPh 21 Scheme Name</label>
                        <input type="text" id="pph21Nama" placeholder="Example: Standard Tax Scheme" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; margin: 0;">PPh 21 Tax Method / Category</label>
                            <span id="btnShowFormRef" onclick="toggleFormReference()" style="cursor: pointer; font-size: 12px; color: #3b82f6; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fas fa-eye"></i> Lihat Detail
                            </span>
                        </div>
                        <select id="pph21Metode" required onchange="handlePph21MetodeChange()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white; height: 42px;">
                            <option value="" disabled selected>-- Select PPh 21 Category --</option>
                            <option value="Kategori Penerima">Kategori Penerima Penghasilan</option>
                            <option value="TER">Tarif Efektif Rata-Rata (TER) Bulanan</option>
                            <option value="Progresif">Tarif Progresif Pasal 17 UU PPh</option>
                            <option value="PTKP">Batasan PTKP Setahun</option>
                        </select>
                    </div>

                    <div class="form-group" id="groupPph21SubMetode" style="margin: 0; display: none;">
                        <label id="labelPph21SubMetode" style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Detail Pilihan Kategori</label>
                        <select id="pph21SubMetode" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white; height: 42px;">
                            <!-- Will be dynamically populated -->
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Default PTKP Status</label>
                        <select id="pph21Ptkp" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white; height: 42px;">
                            <option value="TK/0">TK/0 - Tidak Kawin, 0 Tanggungan (Rp 54.000.000/thn)</option>
                            <option value="TK/1">TK/1 - Tidak Kawin, 1 Tanggungan (Rp 58.500.000/thn)</option>
                            <option value="TK/2">TK/2 - Tidak Kawin, 2 Tanggungan (Rp 63.000.000/thn)</option>
                            <option value="TK/3">TK/3 - Tidak Kawin, 3 Tanggungan (Rp 67.500.000/thn)</option>
                            <option value="K/0">K/0 - Kawin, 0 Tanggungan (Rp 58.500.000/thn)</option>
                            <option value="K/1">K/1 - Kawin, 1 Tanggungan (Rp 63.000.000/thn)</option>
                            <option value="K/2">K/2 - Kawin, 2 Tanggungan (Rp 67.500.000/thn)</option>
                            <option value="K/3">K/3 - Kawin, 3 Tanggungan (Rp 72.000.000/thn)</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Description / Notes</label>
                        <textarea id="pph21Deskripsi" rows="3" placeholder="Enter brief description..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; resize: none;"></textarea>
                    </div>
                </div>

                <!-- Right Column: Interactive Reference (dynamic based on dropdown selection) -->
                <div id="pph21FormReferenceContainer" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); height: 100%; max-height: 65vh; overflow-y: auto; display: none;">
                    
                    <!-- Default Placeholder (when nothing selected) -->
                    <div id="pph21FormRefPlaceholder" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 300px; text-align: center; color: #64748b;">
                        <i class="fas fa-file-invoice-dollar" style="font-size: 54px; color: #cbd5e1; margin-bottom: 16px;"></i>
                        <h4 style="margin: 0 0 6px 0; font-size: 15px; font-weight: 700; color: #475569;">Detail Referensi Peraturan</h4>
                        <p style="font-size: 12px; margin: 0; max-width: 320px; line-height: 1.5; color: #94a3b8;">Please select PPh 21 method/category from the left dropdown to view tax regulation details here.</p>
                    </div>

                    <!-- Panel 1: Kategori Penerima -->
                    <div id="pph21FormPanel1" style="display: none;">
                        <h4 style="margin: 0 0 16px 0; font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #dbeafe; padding-bottom: 10px;">
                            <i class="fas fa-users" style="color: #2563eb;"></i> Kategori Penerima Penghasilan PPh 21
                        </h4>
                        <p style="margin: 0 0 14px 0; font-size: 12px; color: #64748b; line-height: 1.5;">PPh 21 tax is deducted from various categories of income recipients in connection with employment, services, or activities:</p>
                        
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <div id="formRec1" style="display: flex; gap: 12px; align-items: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                                <div style="background: #f1f5f9; color: #475569; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-user-tie" style="font-size: 11px;"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 12px; color: #1e293b; margin-bottom: 2px;">Pegawai Tetap</div>
                                    <div style="font-size: 11px; color: #64748b; line-height: 1.4;">Penerima penghasilan teratur (gaji bulanan) dalam jangka waktu tertentu.</div>
                                </div>
                            </div>
                            <div id="formRec2" style="display: flex; gap: 12px; align-items: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                                <div style="background: #f1f5f9; color: #475569; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-user-clock" style="font-size: 11px;"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 12px; color: #1e293b; margin-bottom: 2px;">Penerima Pensiun Berkala</div>
                                    <div style="font-size: 11px; color: #64748b; line-height: 1.4;">Mantan pegawai yang menerima uang pensiun bulanan.</div>
                                </div>
                            </div>
                            <div id="formRec3" style="display: flex; gap: 12px; align-items: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                                <div style="background: #f1f5f9; color: #475569; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-hard-hat" style="font-size: 11px;"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 12px; color: #1e293b; margin-bottom: 2px;">Pegawai Tidak Tetap / Tenaga Kerja Lepas</div>
                                    <div style="font-size: 11px; color: #64748b; line-height: 1.4;">Pegawai yang hanya menerima penghasilan berdasarkan hari kerja, unit, atau borongan.</div>
                                </div>
                            </div>
                            <div id="formRec4" style="display: flex; gap: 12px; align-items: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                                <div style="background: #f1f5f9; color: #475569; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-briefcase-medical" style="font-size: 11px;"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 12px; color: #1e293b; margin-bottom: 2px;">Bukan Pegawai (Tenaga Ahli / Kemitraan)</div>
                                    <div style="font-size: 11px; color: #64748b; line-height: 1.4;">Dokter, pengacara, konsultan, agen asuransi, artis, distributor MLM, dll.</div>
                                </div>
                            </div>
                            <div id="formRec5" style="display: flex; gap: 12px; align-items: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                                <div style="background: #f1f5f9; color: #475569; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-money-check-alt" style="font-size: 11px;"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 12px; color: #1e293b; margin-bottom: 2px;">Penerima Pesangon / Manfaat Pensiun Sekaligus</div>
                                    <div style="font-size: 11px; color: #64748b; line-height: 1.4;">Pembayaran lumpsum (pesangon, THT, JHT) saat masa kerja berakhir.</div>
                                </div>
                            </div>
                            <div id="formRec6" style="display: flex; gap: 12px; align-items: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                                <div style="background: #f1f5f9; color: #475569; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-award" style="font-size: 11px;"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 12px; color: #1e293b; margin-bottom: 2px;">Peserta Kegiatan</div>
                                    <div style="font-size: 11px; color: #64748b; line-height: 1.4;">Penerima honorarium atas partisipasi acara, lomba, rapat, seminar.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 2: TER Bulanan -->
                    <div id="pph21FormPanel2" style="display: none;">
                        <h4 style="margin: 0 0 12px 0; font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #dbeafe; padding-bottom: 8px;">
                            <i class="fas fa-percentage" style="color: #3b82f6;"></i> Tarif Efektif Rata-Rata (TER) Bulanan
                        </h4>
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; font-size: 12px; color: #1e40af; font-weight: 600;">
                            <i class="fas fa-calculator" style="margin-right: 6px;"></i> PPh 21 Bulanan = Penghasilan Bruto Sebulan × % TER
                        </div>

                        <!-- TER Sub-tabs inside Form -->
                        <div style="display: flex; gap: 6px; margin-bottom: 12px;">
                            <button type="button" onclick="selectFormTerCategory('A')" id="formTerTabA" style="flex: 1; padding: 8px; border-radius: 6px; border: 2px solid #dbeafe; background: #eff6ff; color: #1d4ed8; font-weight: 700; font-size: 12px; cursor: pointer;">
                                Kategori A
                            </button>
                            <button type="button" onclick="selectFormTerCategory('B')" id="formTerTabB" style="flex: 1; padding: 8px; border-radius: 6px; border: 2px solid #e2e8f0; background: white; color: #475569; font-weight: 700; font-size: 12px; cursor: pointer;">
                                Kategori B
                            </button>
                            <button type="button" onclick="selectFormTerCategory('C')" id="formTerTabC" style="flex: 1; padding: 8px; border-radius: 6px; border: 2px solid #e2e8f0; background: white; color: #475569; font-weight: 700; font-size: 12px; cursor: pointer;">
                                Kategori C
                            </button>
                        </div>

                        <!-- TER Panel Contents -->
                        <div id="formTerPanelA">
                            <p style="margin: 0 0 8px 0; font-size: 11px; color: #475569;">PTKP: <strong>TK/0</strong>, <strong>TK/1</strong>, <strong>K/0</strong></p>
                            <!-- Table Kategori A -->
                            <div style="overflow-x: auto; border: 1px solid #dbeafe; border-radius: 8px; max-height: 250px; overflow-y: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                                    <thead style="position: sticky; top: 0; z-index: 1; background: #eff6ff;">
                                        <tr>
                                            <th style="padding: 6px 10px; text-align: left; color: #1d4ed8; font-weight: 700; border-bottom: 1px solid #dbeafe;">Penghasilan Bruto Bulanan</th>
                                            <th style="padding: 6px 10px; text-align: right; color: #1d4ed8; font-weight: 700; border-bottom: 1px solid #dbeafe;">Tarif</th>
                                        </tr>
                                    </thead>
                                    <tbody id="formTerTableBodyA">
                                        <!-- Will be dynamically populated by JS to keep modal file size optimized -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="formTerPanelB" style="display: none;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; color: #475569;">PTKP: <strong>TK/2</strong>, <strong>TK/3</strong>, <strong>K/1</strong>, <strong>K/2</strong></p>
                            <div style="overflow-x: auto; border: 1px solid #fef3c7; border-radius: 8px; max-height: 250px; overflow-y: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                                    <thead style="position: sticky; top: 0; z-index: 1; background: #fffbeb;">
                                        <tr>
                                            <th style="padding: 6px 10px; text-align: left; color: #92400e; font-weight: 700; border-bottom: 1px solid #fef3c7;">Penghasilan Bruto Bulanan</th>
                                            <th style="padding: 6px 10px; text-align: right; color: #92400e; font-weight: 700; border-bottom: 1px solid #fef3c7;">Tarif</th>
                                        </tr>
                                    </thead>
                                    <tbody id="formTerTableBodyB">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="formTerPanelC" style="display: none;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; color: #475569;">PTKP: <strong>K/3</strong> (Rp 72 jt)</p>
                            <div style="overflow-x: auto; border: 1px solid #fce7f3; border-radius: 8px; max-height: 250px; overflow-y: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                                    <thead style="position: sticky; top: 0; z-index: 1; background: #fdf2f8;">
                                        <tr>
                                            <th style="padding: 6px 10px; text-align: left; color: #9d174d; font-weight: 700; border-bottom: 1px solid #fce7f3;">Penghasilan Bruto Bulanan</th>
                                            <th style="padding: 6px 10px; text-align: right; color: #9d174d; font-weight: 700; border-bottom: 1px solid #fce7f3;">Tarif</th>
                                        </tr>
                                    </thead>
                                    <tbody id="formTerTableBodyC">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 3: Tarif Progresif -->
                    <div id="pph21FormPanel3" style="display: none;">
                        <h4 style="margin: 0 0 12px 0; font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #fecaca; padding-bottom: 8px;">
                            <i class="fas fa-layer-group" style="color: #dc2626;"></i> Tarif Progresif Pasal 17 UU PPh
                        </h4>
                        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; font-size: 12px; color: #991b1b; font-weight: 600;">
                            <i class="fas fa-calculator" style="margin-right: 6px;"></i> PPh 21 Desember = PPh 21 Terutang Setahun − Total PPh 21 Jan–Nov
                        </div>
                        
                        <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 12px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                                <thead style="background: #fef2f2;">
                                    <tr>
                                        <th style="padding: 8px 10px; text-align: left; color: #991b1b; font-weight: 700; border-bottom: 1px solid #fecaca;">Taxable Income Bracket (PKP)</th>
                                        <th style="padding: 8px 10px; text-align: center; color: #991b1b; font-weight: 700; border-bottom: 1px solid #fecaca; width: 80px;">Tarif</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="formProgRow1" style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 8px 10px;">s.d. Rp 60.000.000</td><td style="padding: 8px 10px; text-align: center;"><span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 4px; font-weight: 700;">5%</span></td></tr>
                                    <tr id="formProgRow2" style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 8px 10px;">Rp 60.000.001 – Rp 250.000.000</td><td style="padding: 8px 10px; text-align: center;"><span style="background: #fef9c3; color: #854d0e; padding: 2px 8px; border-radius: 4px; font-weight: 700;">15%</span></td></tr>
                                    <tr id="formProgRow3" style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 8px 10px;">Rp 250.000.001 – Rp 500.000.000</td><td style="padding: 8px 10px; text-align: center;"><span style="background: #fed7aa; color: #9a3412; padding: 2px 8px; border-radius: 4px; font-weight: 700;">25%</span></td></tr>
                                    <tr id="formProgRow4" style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 8px 10px;">Rp 500.000.001 – Rp 5.000.000.000</td><td style="padding: 8px 10px; text-align: center;"><span style="background: #fecaca; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-weight: 700;">30%</span></td></tr>
                                    <tr id="formProgRow5"><td style="padding: 8px 10px;">Lebih dari Rp 5.000.000.000</td><td style="padding: 8px 10px; text-align: center;"><span style="background: #f87171; color: white; padding: 2px 8px; border-radius: 4px; font-weight: 700;">35%</span></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Panel 4: Batasan PTKP -->
                    <div id="pph21FormPanel4" style="display: none;">
                        <h4 style="margin: 0 0 12px 0; font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #bbf7d0; padding-bottom: 8px;">
                            <i class="fas fa-user-shield" style="color: #16a34a;"></i> Batasan PTKP (Per Tahun)
                        </h4>
                        <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 10px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                                <thead style="background: #f0fdf4;">
                                    <tr>
                                        <th style="padding: 6px 8px; text-align: left; color: #166534; font-weight: 700;">Status</th>
                                        <th style="padding: 6px 8px; text-align: left; color: #166534; font-weight: 700;">Description</th>
                                        <th style="padding: 6px 8px; text-align: right; color: #166534; font-weight: 700;">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="formPtkpRowTK0" style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 6px 8px; font-weight: 700;">TK/0</td><td style="padding: 6px 8px; color: #64748b;">Tidak Kawin, 0 Tanggungan</td><td style="padding: 6px 8px; text-align: right; font-weight: 600;">Rp 54.000.000</td></tr>
                                    <tr id="formPtkpRowTK1" style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 6px 8px; font-weight: 700;">TK/1</td><td style="padding: 6px 8px; color: #64748b;">Tidak Kawin, 1 Tanggungan</td><td style="padding: 6px 8px; text-align: right; font-weight: 600;">Rp 58.500.000</td></tr>
                                    <tr id="formPtkpRowTK2" style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 6px 8px; font-weight: 700;">TK/2</td><td style="padding: 6px 8px; color: #64748b;">Tidak Kawin, 2 Tanggungan</td><td style="padding: 6px 8px; text-align: right; font-weight: 600;">Rp 63.000.000</td></tr>
                                    <tr id="formPtkpRowTK3" style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 6px 8px; font-weight: 700;">TK/3</td><td style="padding: 6px 8px; color: #64748b;">Tidak Kawin, 3 Tanggungan</td><td style="padding: 6px 8px; text-align: right; font-weight: 600;">Rp 67.500.000</td></tr>
                                    <tr id="formPtkpRowK0" style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 6px 8px; font-weight: 700;">K/0</td><td style="padding: 6px 8px; color: #64748b;">Kawin, 0 Tanggungan</td><td style="padding: 6px 8px; text-align: right; font-weight: 600;">Rp 58.500.000</td></tr>
                                    <tr id="formPtkpRowK1" style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 6px 8px; font-weight: 700;">K/1</td><td style="padding: 6px 8px; color: #64748b;">Kawin, 1 Tanggungan</td><td style="padding: 6px 8px; text-align: right; font-weight: 600;">Rp 63.000.000</td></tr>
                                    <tr id="formPtkpRowK2" style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 6px 8px; font-weight: 700;">K/2</td><td style="padding: 6px 8px; color: #64748b;">Kawin, 2 Tanggungan</td><td style="padding: 6px 8px; text-align: right; font-weight: 600;">Rp 67.500.000</td></tr>
                                    <tr id="formPtkpRowK3"><td style="padding: 6px 8px; font-weight: 700;">K/3</td><td style="padding: 6px 8px; color: #64748b;">Kawin, 3 Tanggungan</td><td style="padding: 6px 8px; text-align: right; font-weight: 600;">Rp 72.000.000</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <button type="button" class="btn-cancel" onclick="tutupModalPph21()">Cancel</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Save</button>
            </div>
        </form>
    </div>

    <!-- PPh 21 Detail Info Modal -->
    <div id="modalDetailPph21" class="modal-skema" style="display: none; width: 950px; max-width: 95%; z-index: 2005;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; display: flex; justify-content: space-between; align-items: center; padding: 18px 24px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-file-invoice-dollar" style="font-size: 20px; color: white;"></i>
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: white;" id="detailPph21Title">Referensi Lengkap PPh 21</h3>
                    <p style="margin: 2px 0 0 0; font-size: 12px; color: rgba(255, 255, 255, 0.85); font-weight: 400;">PP No. 58 Year 2023 &amp; PMK No. 168/2023 — Select a topic below to view details</p>
                </div>
            </div>
            <i class="fas fa-times" style="cursor: pointer; color: white; font-size: 16px; padding: 4px;" onclick="tutupDetailPph21Modal()"></i>
        </div>
        <div class="modal-body" style="padding: 24px; max-height: 78vh; overflow-y: auto; background: #f8fafc;">

            <!-- Scheme Info Card (top) -->
            <div id="detailPph21SchemeInfoBar" style="display: none; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; text-align: center;">
                    <div style="font-size: 10px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px;">Scheme Name</div>
                    <div style="font-size: 14px; font-weight: 700; color: #1e293b;" id="detailPph21SchemeName">-</div>
                </div>
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; text-align: center;">
                    <div style="font-size: 10px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px;">Tax Method</div>
                    <div style="font-size: 14px; font-weight: 700; color: #f59e0b;" id="detailPph21Method">-</div>
                </div>
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; text-align: center;">
                    <div style="font-size: 10px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px;">Status PTKP</div>
                    <div style="font-size: 14px; font-weight: 700; color: #3b82f6;" id="detailPph21PTKP">-</div>
                </div>
            </div>

            <!-- 4 Topic Selector Cards (Hidden by default) -->
            <div id="detailPph21TopicSelector" style="display: none; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div id="pph21Topic1" onclick="selectPph21Topic(1)" style="cursor: pointer; background: white; border: 2px solid #e2e8f0; border-radius: 12px; padding: 16px; transition: all 0.25s ease; display: flex; align-items: flex-start; gap: 14px;">
                    <div style="background: linear-gradient(135deg, #dbeafe, #eff6ff); width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-users" style="font-size: 18px; color: #2563eb;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 14px; color: #1e293b; margin-bottom: 3px;">1. Kategori Penerima Penghasilan</div>
                        <div style="font-size: 12px; color: #64748b; line-height: 1.4;">Pegawai Tetap, Tidak Tetap, Tenaga Ahli, Pensiunan, dll.</div>
                    </div>
                </div>
                <div id="pph21Topic2" onclick="selectPph21Topic(2)" style="cursor: pointer; background: white; border: 2px solid #e2e8f0; border-radius: 12px; padding: 16px; transition: all 0.25s ease; display: flex; align-items: flex-start; gap: 14px;">
                    <div style="background: linear-gradient(135deg, #f3e8ff, #faf5ff); width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-percentage" style="font-size: 18px; color: #7c3aed;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 14px; color: #1e293b; margin-bottom: 3px;">2. Tarif Efektif Rata-Rata (TER)</div>
                        <div style="font-size: 12px; color: #64748b; line-height: 1.4;">Tarif bulanan Jan–Nov: Kategori A, B, C berdasarkan PTKP</div>
                    </div>
                </div>
                <div id="pph21Topic3" onclick="selectPph21Topic(3)" style="cursor: pointer; background: white; border: 2px solid #e2e8f0; border-radius: 12px; padding: 16px; transition: all 0.25s ease; display: flex; align-items: flex-start; gap: 14px;">
                    <div style="background: linear-gradient(135deg, #fef2f2, #fff1f2); width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-layer-group" style="font-size: 18px; color: #dc2626;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 14px; color: #1e293b; margin-bottom: 3px;">3. Tarif Progresif Pasal 17</div>
                        <div style="font-size: 12px; color: #64748b; line-height: 1.4;">Tarif tahunan / Desember: 5%, 15%, 25%, 30%, 35%</div>
                    </div>
                </div>
                <div id="pph21Topic4" onclick="selectPph21Topic(4)" style="cursor: pointer; background: white; border: 2px solid #e2e8f0; border-radius: 12px; padding: 16px; transition: all 0.25s ease; display: flex; align-items: flex-start; gap: 14px;">
                    <div style="background: linear-gradient(135deg, #dcfce7, #f0fdf4); width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-user-shield" style="font-size: 18px; color: #16a34a;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 14px; color: #1e293b; margin-bottom: 3px;">4. Batasan PTKP Setahun</div>
                        <div style="font-size: 12px; color: #64748b; line-height: 1.4;">TK/0 to K/3 — Annual Non-Taxable Income (PTKP)</div>
                    </div>
                </div>
            </div>

            <!-- ======== PANEL 1: Kategori Penerima Penghasilan ======== -->
            <div id="pph21Panel1" style="display: none;">
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                    <h4 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #dbeafe; padding-bottom: 12px;">
                        <i class="fas fa-users" style="color: #2563eb;"></i> Kategori Penerima Penghasilan PPh 21
                    </h4>
                    <p style="margin: 0 0 16px 0; font-size: 13px; color: #64748b; line-height: 1.5;">PPh 21 tax is deducted from various categories of income recipients in connection with employment, services, or activities performed by domestic individual tax subjects:</p>

                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <div id="detailRec1" style="display: flex; gap: 14px; align-items: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                            <div style="background: #f1f5f9; color: #475569; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-user-tie" style="font-size: 14px;"></i></div>
                            <div>
                                <div style="font-weight: 700; font-size: 13px; color: #1e293b; margin-bottom: 3px;">Pegawai Tetap</div>
                                <div style="font-size: 12px; color: #64748b; line-height: 1.5;">Penerima penghasilan teratur (gaji bulanan) dalam jangka waktu tertentu berdasarkan perjanjian kerja.</div>
                            </div>
                        </div>
                        <div id="detailRec2" style="display: flex; gap: 14px; align-items: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                            <div style="background: #f1f5f9; color: #475569; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-user-clock" style="font-size: 14px;"></i></div>
                            <div>
                                <div style="font-weight: 700; font-size: 13px; color: #1e293b; margin-bottom: 3px;">Penerima Pensiun Berkala</div>
                                <div style="font-size: 12px; color: #64748b; line-height: 1.5;">Mantan pegawai yang menerima uang pensiun bulanan secara berkala dari dana pensiun.</div>
                            </div>
                        </div>
                        <div id="detailRec3" style="display: flex; gap: 14px; align-items: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                            <div style="background: #f1f5f9; color: #475569; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-hard-hat" style="font-size: 14px;"></i></div>
                            <div>
                                <div style="font-weight: 700; font-size: 13px; color: #1e293b; margin-bottom: 3px;">Pegawai Tidak Tetap / Tenaga Kerja Lepas</div>
                                <div style="font-size: 12px; color: #64748b; line-height: 1.5;">Pegawai yang hanya menerima penghasilan berdasarkan hari kerja, unit pekerjaan yang dihasilkan, atau secara borongan.</div>
                            </div>
                        </div>
                        <div id="detailRec4" style="display: flex; gap: 14px; align-items: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                            <div style="background: #f1f5f9; color: #475569; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-briefcase-medical" style="font-size: 14px;"></i></div>
                            <div>
                                <div style="font-weight: 700; font-size: 13px; color: #1e293b; margin-bottom: 3px;">Bukan Pegawai (Tenaga Ahli / Kemitraan)</div>
                                <div style="font-size: 12px; color: #64748b; line-height: 1.5;">Seperti dokter, pengacara, konsultan, agen asuransi, artis, distributor MLM, dll., yang menerima honorarium atau komisi.</div>
                            </div>
                        </div>
                        <div id="detailRec5" style="display: flex; gap: 14px; align-items: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                            <div style="background: #f1f5f9; color: #475569; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-money-check-alt" style="font-size: 14px;"></i></div>
                            <div>
                                <div style="font-weight: 700; font-size: 13px; color: #1e293b; margin-bottom: 3px;">Penerima Pesangon / Manfaat Pensiun Sekaligus</div>
                                <div style="font-size: 12px; color: #64748b; line-height: 1.5;">Pembayaran lumpsum saat masa kerja berakhir, termasuk uang pesangon, uang manfaat pensiun, THT, dan JHT yang dibayar sekaligus.</div>
                            </div>
                        </div>
                        <div id="detailRec6" style="display: flex; gap: 14px; align-items: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                            <div style="background: #f1f5f9; color: #475569; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-award" style="font-size: 14px;"></i></div>
                            <div>
                                <div style="font-weight: 700; font-size: 13px; color: #1e293b; margin-bottom: 3px;">Peserta Kegiatan</div>
                                <div style="font-size: 12px; color: #64748b; line-height: 1.5;">Penerima honorarium atas partisipasi dalam suatu acara, lomba, rapat, sidang, seminar, workshop, dan kegiatan lainnya.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======== PANEL 2: TER Bulanan ======== -->
            <div id="pph21Panel2" style="display: none;">
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                    <h4 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #dbeafe; padding-bottom: 12px;">
                        <i class="fas fa-percentage" style="color: #3b82f6;"></i> Tarif Efektif Rata-Rata (TER) Bulanan
                    </h4>
                    <p style="margin: 0 0 14px 0; font-size: 13px; color: #64748b; line-height: 1.5;">Used for PPh 21 deductions from <strong>January to November</strong>. DJP classifies taxpayers into 3 TER categories based on PTKP status.</p>
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: #1e40af; font-weight: 600;">
                        <i class="fas fa-calculator" style="margin-right: 6px;"></i> Rumus: PPh 21 Bulanan = Penghasilan Bruto Sebulan × % TER
                    </div>

                    <!-- TER Sub-tabs -->
                    <div id="detailTerTabsWrapper" style="display: flex; gap: 8px; margin-bottom: 14px;">
                        <button onclick="selectTerCategory('A')" id="terTabA" style="flex: 1; padding: 10px 14px; border-radius: 8px; border: 2px solid #dbeafe; background: #eff6ff; color: #1d4ed8; font-weight: 700; font-size: 13px; cursor: pointer; transition: all 0.2s;">
                            <i class="fas fa-tag" style="margin-right: 4px;"></i> Kategori A
                        </button>
                        <button onclick="selectTerCategory('B')" id="terTabB" style="flex: 1; padding: 10px 14px; border-radius: 8px; border: 2px solid #e2e8f0; background: white; color: #475569; font-weight: 700; font-size: 13px; cursor: pointer; transition: all 0.2s;">
                            <i class="fas fa-tag" style="margin-right: 4px;"></i> Kategori B
                        </button>
                        <button onclick="selectTerCategory('C')" id="terTabC" style="flex: 1; padding: 10px 14px; border-radius: 8px; border: 2px solid #e2e8f0; background: white; color: #475569; font-weight: 700; font-size: 13px; cursor: pointer; transition: all 0.2s;">
                            <i class="fas fa-tag" style="margin-right: 4px;"></i> Kategori C
                        </button>
                    </div>

                    <!-- TER A Panel -->
                    <div id="terPanelA">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap;">
                            <span style="background: #f1f5f9; color: #334155; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: 12px;">Kategori A</span>
                            <span style="font-size: 12px; color: #475569; font-weight: 500;">PTKP: <strong>TK/0</strong> (Rp 54 jt), <strong>TK/1</strong> (Rp 58,5 jt), <strong>K/0</strong> (Rp 58,5 jt)</span>
                        </div>
                        <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; max-height: 400px; overflow-y: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                <thead style="position: sticky; top: 0; z-index: 1;">
                                    <tr style="background: #f1f5f9;">
                                        <th style="padding: 8px 12px; text-align: left; font-weight: 700; color: #475569; border-bottom: 1px solid #e2e8f0;">Penghasilan Bruto Bulanan</th>
                                        <th style="padding: 8px 12px; text-align: right; font-weight: 700; color: #475569; border-bottom: 1px solid #e2e8f0;">Tarif (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">s.d. Rp 5.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 5.400.001 – Rp 5.650.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,25%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 5.650.001 – Rp 5.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,50%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 5.950.001 – Rp 6.300.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,75%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.300.001 – Rp 6.750.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.750.001 – Rp 7.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,25%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 7.500.001 – Rp 8.550.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,50%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 8.550.001 – Rp 9.650.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,75%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 9.650.001 – Rp 10.050.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">2,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 10.050.001 – Rp 10.350.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">2,25%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 10.350.001 – Rp 10.700.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">2,50%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 10.700.001 – Rp 11.050.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">3,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 11.050.001 – Rp 11.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">3,50%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 11.600.001 – Rp 12.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">4,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 12.500.001 – Rp 13.750.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">5,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 13.750.001 – Rp 15.100.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">6,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 15.100.001 – Rp 16.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">7,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 16.950.001 – Rp 19.750.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">8,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 19.750.001 – Rp 24.150.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">9,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 24.150.001 – Rp 26.450.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">10,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 26.450.001 – Rp 28.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">11,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 28.000.001 – Rp 30.050.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">12,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 30.050.001 – Rp 32.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">13,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 32.400.001 – Rp 35.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">14,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 35.400.001 – Rp 39.100.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">15,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 39.100.001 – Rp 43.850.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">16,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 43.850.001 – Rp 47.800.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">17,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 47.800.001 – Rp 51.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">18,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 51.400.001 – Rp 56.300.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">19,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 56.300.001 – Rp 62.200.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">20,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 62.200.001 – Rp 68.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">21,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 68.600.001 – Rp 77.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">22,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 77.500.001 – Rp 89.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">23,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 89.000.001 – Rp 103.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">24,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 103.000.001 – Rp 125.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">25,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 125.000.001 – Rp 157.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">26,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 157.000.001 – Rp 206.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">27,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 206.000.001 – Rp 337.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">28,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 337.000.001 – Rp 454.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">29,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 454.000.001 – Rp 550.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">30,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 550.000.001 – Rp 695.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">31,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 695.000.001 – Rp 910.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">32,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 910.000.001 – Rp 1.400.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">33,00%</td></tr>
                                    <tr><td style="padding: 7px 12px;">Lebih dari Rp 1.400.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">34,00%</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TER B Panel -->
                    <div id="terPanelB" style="display: none;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap;">
                            <span style="background: #f1f5f9; color: #334155; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: 12px;">Kategori B</span>
                            <span style="font-size: 12px; color: #475569; font-weight: 500;">PTKP: <strong>TK/2</strong> (Rp 63 jt), <strong>TK/3</strong> (Rp 67,5 jt), <strong>K/1</strong> (Rp 63 jt), <strong>K/2</strong> (Rp 67,5 jt)</span>
                        </div>
                        <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; max-height: 400px; overflow-y: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                <thead style="position: sticky; top: 0; z-index: 1;">
                                    <tr style="background: #f1f5f9;">
                                        <th style="padding: 8px 12px; text-align: left; font-weight: 700; color: #475569; border-bottom: 1px solid #e2e8f0;">Penghasilan Bruto Bulanan</th>
                                        <th style="padding: 8px 12px; text-align: right; font-weight: 700; color: #475569; border-bottom: 1px solid #e2e8f0;">Tarif (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">s.d. Rp 6.200.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.200.001 – Rp 6.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,25%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.500.001 – Rp 6.850.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,50%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.850.001 – Rp 7.300.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,75%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 7.300.001 – Rp 9.200.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 9.200.001 – Rp 10.750.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,50%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 10.750.001 – Rp 11.250.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">2,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 11.250.001 – Rp 11.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">2,50%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 11.600.001 – Rp 12.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">3,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 12.600.001 – Rp 13.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">4,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 13.600.001 – Rp 14.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">5,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 14.950.001 – Rp 16.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">6,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 16.400.001 – Rp 18.450.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">7,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 18.450.001 – Rp 21.850.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">8,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 21.850.001 – Rp 26.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">9,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 26.000.001 – Rp 27.700.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">10,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 27.700.001 – Rp 29.350.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">11,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 29.350.001 – Rp 31.450.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">12,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 31.450.001 – Rp 33.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">13,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 33.950.001 – Rp 37.100.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">14,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 37.100.001 – Rp 41.100.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">15,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 41.100.001 – Rp 45.800.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">16,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 45.800.001 – Rp 49.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">17,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 49.500.001 – Rp 53.800.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">18,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 53.800.001 – Rp 58.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">19,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 58.500.001 – Rp 64.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">20,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 64.000.001 – Rp 71.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">21,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 71.000.001 – Rp 80.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">22,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 80.000.001 – Rp 93.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">23,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 93.000.001 – Rp 109.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">24,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 109.000.001 – Rp 129.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">25,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 129.000.001 – Rp 163.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">26,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 163.000.001 – Rp 211.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">27,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 211.000.001 – Rp 374.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">28,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 374.000.001 – Rp 459.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">29,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 459.000.001 – Rp 555.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">30,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 555.000.001 – Rp 704.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">31,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 704.000.001 – Rp 957.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">32,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 957.000.001 – Rp 1.405.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">33,00%</td></tr>
                                    <tr><td style="padding: 7px 12px;">Lebih dari Rp 1.405.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">34,00%</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TER C Panel -->
                    <div id="terPanelC" style="display: none;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap;">
                            <span style="background: #f1f5f9; color: #334155; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: 12px;">Kategori C</span>
                            <span style="font-size: 12px; color: #475569; font-weight: 500;">PTKP: <strong>K/3</strong> — Kawin, 3 Tanggungan (Rp 72.000.000/thn)</span>
                        </div>
                        <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; max-height: 400px; overflow-y: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                <thead style="position: sticky; top: 0; z-index: 1;">
                                    <tr style="background: #f1f5f9;">
                                        <th style="padding: 8px 12px; text-align: left; font-weight: 700; color: #475569; border-bottom: 1px solid #e2e8f0;">Penghasilan Bruto Bulanan</th>
                                        <th style="padding: 8px 12px; text-align: right; font-weight: 700; color: #475569; border-bottom: 1px solid #e2e8f0;">Tarif (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">s.d. Rp 6.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.600.001 – Rp 6.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,25%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.950.001 – Rp 7.350.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,50%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 7.350.001 – Rp 7.800.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,75%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 7.800.001 – Rp 8.850.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 8.850.001 – Rp 9.800.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,25%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 9.800.001 – Rp 10.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,50%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 10.950.001 – Rp 11.200.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,75%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 11.200.001 – Rp 12.050.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">2,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 12.050.001 – Rp 12.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">3,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 12.950.001 – Rp 14.150.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">4,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 14.150.001 – Rp 15.550.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">5,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 15.550.001 – Rp 17.050.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">6,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 17.050.001 – Rp 19.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">7,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 19.500.001 – Rp 22.700.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">8,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 22.700.001 – Rp 26.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">9,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 26.600.001 – Rp 28.100.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">10,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 28.100.001 – Rp 30.100.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">11,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 30.100.001 – Rp 32.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">12,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 32.600.001 – Rp 35.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">13,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 35.400.001 – Rp 38.900.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">14,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 38.900.001 – Rp 43.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">15,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 43.000.001 – Rp 47.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">16,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 47.400.001 – Rp 51.200.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">17,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 51.200.001 – Rp 55.800.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">18,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 55.800.001 – Rp 60.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">19,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 60.400.001 – Rp 66.700.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">20,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 66.700.001 – Rp 74.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">21,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 74.500.001 – Rp 83.200.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">22,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 83.200.001 – Rp 95.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">23,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 95.600.001 – Rp 110.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">24,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 110.000.001 – Rp 134.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">25,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 134.000.001 – Rp 169.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">26,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 169.000.001 – Rp 221.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">27,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 221.000.001 – Rp 390.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">28,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 390.000.001 – Rp 463.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">29,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 463.000.001 – Rp 561.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">30,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 561.000.001 – Rp 709.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">31,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 709.000.001 – Rp 965.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">32,00%</td></tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 965.000.001 – Rp 1.419.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">33,00%</td></tr>
                                    <tr><td style="padding: 7px 12px;">Lebih dari Rp 1.419.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">34,00%</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======== PANEL 3: Tarif Progresif Pasal 17 ======== -->
            <div id="pph21Panel3" style="display: none;">
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                    <h4 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #dbeafe; padding-bottom: 12px;">
                        <i class="fas fa-layer-group" style="color: #3b82f6;"></i> Tarif Progresif Pasal 17 UU PPh
                    </h4>
                    <p style="margin: 0 0 14px 0; font-size: 13px; color: #64748b; line-height: 1.5;">Used in the <strong>final tax period (December)</strong> or when employees resign/terminate. PPh 21 is recalculated cumulatively using Progressive Rates under Article 17 Paragraph (1) Letter a of PPh Law after deducting the annual PTKP.</p>
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: #1e40af; font-weight: 600;">
                        <i class="fas fa-calculator" style="margin-right: 6px;"></i> Rumus: PKP = Penghasilan Bruto Setahun − Biaya Jabatan − Iuran Pensiun − PTKP
                    </div>

                    <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 10px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <thead>
                                <tr style="background: #f1f5f9;">
                                    <th style="padding: 14px 18px; text-align: left; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0;">Taxable Income Bracket (PKP) per Tahun</th>
                                    <th style="padding: 14px 18px; text-align: center; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0; width: 140px;">Tarif</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="detailProgRow1" style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 14px 18px; color: #334155;">s.d. Rp 60.000.000</td>
                                    <td style="padding: 14px 18px; text-align: center;"><span style="background: #f1f5f9; color: #334155; padding: 5px 18px; border-radius: 8px; font-weight: 700; font-size: 14px;">5%</span></td>
                                </tr>
                                <tr id="detailProgRow2" style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 14px 18px; color: #334155;">Rp 60.000.001 – Rp 250.000.000</td>
                                    <td style="padding: 14px 18px; text-align: center;"><span style="background: #f1f5f9; color: #334155; padding: 5px 18px; border-radius: 8px; font-weight: 700; font-size: 14px;">15%</span></td>
                                </tr>
                                <tr id="detailProgRow3" style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 14px 18px; color: #334155;">Rp 250.000.001 – Rp 500.000.000</td>
                                    <td style="padding: 14px 18px; text-align: center;"><span style="background: #f1f5f9; color: #334155; padding: 5px 18px; border-radius: 8px; font-weight: 700; font-size: 14px;">25%</span></td>
                                </tr>
                                <tr id="detailProgRow4" style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 14px 18px; color: #334155;">Rp 500.000.001 – Rp 5.000.000.000</td>
                                    <td style="padding: 14px 18px; text-align: center;"><span style="background: #f1f5f9; color: #334155; padding: 5px 18px; border-radius: 8px; font-weight: 700; font-size: 14px;">30%</span></td>
                                </tr>
                                <tr id="detailProgRow5">
                                    <td style="padding: 14px 18px; color: #334155;">Lebih dari Rp 5.000.000.000</td>
                                    <td style="padding: 14px 18px; text-align: center;"><span style="background: #f1f5f9; color: #334155; padding: 5px 18px; border-radius: 8px; font-weight: 700; font-size: 14px;">35%</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pengurang -->
                    <div style="margin-top: 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 10px; padding: 14px;">
                            <div style="font-weight: 700; font-size: 13px; color: #92400e; margin-bottom: 6px;"><i class="fas fa-briefcase" style="margin-right: 6px;"></i>Biaya Jabatan</div>
                            <div style="font-size: 12px; color: #78350f; line-height: 1.7;">
                                <div>• Tarif: <strong>5%</strong> dari Penghasilan Bruto</div>
                                <div>• Maks: <strong>Rp 500.000 / bulan</strong></div>
                                <div>• Maks: <strong>Rp 6.000.000 / tahun</strong></div>
                            </div>
                        </div>
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px;">
                            <div style="font-weight: 700; font-size: 13px; color: #166534; margin-bottom: 6px;"><i class="fas fa-piggy-bank" style="margin-right: 6px;"></i>Iuran Pensiun / THT</div>
                            <div style="font-size: 12px; color: #14532d; line-height: 1.7;">
                                <div>• Iuran JP: <strong>1%</strong> employee</div>
                                <div>• Iuran JHT: <strong>2%</strong> employee</div>
                                <div>• Dikurangkan sebelum dihitung PKP</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======== PANEL 4: Batasan PTKP ======== -->
            <div id="pph21Panel4" style="display: none;">
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                    <h4 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #dbeafe; padding-bottom: 12px;">
                        <i class="fas fa-user-shield" style="color: #3b82f6;"></i> Annual Non-Taxable Income (PTKP) Limits
                    </h4>
                    <p style="margin: 0 0 16px 0; font-size: 13px; color: #64748b; line-height: 1.5;">Amount of annual income exempted from PPh 21 tax. Maximum dependents of 3 people (family/relatives in direct lineage, one degree).</p>

                    <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 16px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <thead>
                                <tr style="background: #f1f5f9;">
                                    <th style="padding: 12px 14px; text-align: left; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0;">Status PTKP</th>
                                    <th style="padding: 12px 14px; text-align: left; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0;">Description</th>
                                    <th style="padding: 12px 14px; text-align: right; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0;">Jumlah (per Tahun)</th>
                                    <th style="padding: 12px 14px; text-align: center; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0;">Kategori TER</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="detailPtkpRowTK0" style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 14px; font-weight: 700; color: #1e293b;">TK/0</td>
                                    <td style="padding: 12px 14px; color: #475569;">Tidak Kawin, 0 Tanggungan</td>
                                    <td style="padding: 12px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 54.000.000</td>
                                    <td style="padding: 12px 14px; text-align: center;"><span style="background: #f1f5f9; color: #334155; padding: 3px 12px; border-radius: 6px; font-weight: 700; font-size: 12px;">A</span></td>
                                </tr>
                                <tr id="detailPtkpRowTK1" style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 14px; font-weight: 700; color: #1e293b;">TK/1</td>
                                    <td style="padding: 12px 14px; color: #475569;">Tidak Kawin, 1 Tanggungan</td>
                                    <td style="padding: 12px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 58.500.000</td>
                                    <td style="padding: 12px 14px; text-align: center;"><span style="background: #f1f5f9; color: #334155; padding: 3px 12px; border-radius: 6px; font-weight: 700; font-size: 12px;">A</span></td>
                                </tr>
                                <tr id="detailPtkpRowTK2" style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 14px; font-weight: 700; color: #1e293b;">TK/2</td>
                                    <td style="padding: 12px 14px; color: #475569;">Tidak Kawin, 2 Tanggungan</td>
                                    <td style="padding: 12px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 63.000.000</td>
                                    <td style="padding: 12px 14px; text-align: center;"><span style="background: #f1f5f9; color: #334155; padding: 3px 12px; border-radius: 6px; font-weight: 700; font-size: 12px;">B</span></td>
                                </tr>
                                <tr id="detailPtkpRowTK3" style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 14px; font-weight: 700; color: #1e293b;">TK/3</td>
                                    <td style="padding: 12px 14px; color: #475569;">Tidak Kawin, 3 Tanggungan</td>
                                    <td style="padding: 12px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 67.500.000</td>
                                    <td style="padding: 12px 14px; text-align: center;"><span style="background: #f1f5f9; color: #334155; padding: 3px 12px; border-radius: 6px; font-weight: 700; font-size: 12px;">B</span></td>
                                </tr>
                                <tr id="detailPtkpRowK0" style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 14px; font-weight: 700; color: #1e293b;">K/0</td>
                                    <td style="padding: 12px 14px; color: #475569;">Kawin, 0 Tanggungan</td>
                                    <td style="padding: 12px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 58.500.000</td>
                                    <td style="padding: 12px 14px; text-align: center;"><span style="background: #f1f5f9; color: #334155; padding: 3px 12px; border-radius: 6px; font-weight: 700; font-size: 12px;">A</span></td>
                                </tr>
                                <tr id="detailPtkpRowK1" style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 14px; font-weight: 700; color: #1e293b;">K/1</td>
                                    <td style="padding: 12px 14px; color: #475569;">Kawin, 1 Tanggungan</td>
                                    <td style="padding: 12px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 63.000.000</td>
                                    <td style="padding: 12px 14px; text-align: center;"><span style="background: #f1f5f9; color: #334155; padding: 3px 12px; border-radius: 6px; font-weight: 700; font-size: 12px;">B</span></td>
                                </tr>
                                <tr id="detailPtkpRowK2" style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 14px; font-weight: 700; color: #1e293b;">K/2</td>
                                    <td style="padding: 12px 14px; color: #475569;">Kawin, 2 Tanggungan</td>
                                    <td style="padding: 12px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 67.500.000</td>
                                    <td style="padding: 12px 14px; text-align: center;"><span style="background: #f1f5f9; color: #334155; padding: 3px 12px; border-radius: 6px; font-weight: 700; font-size: 12px;">B</span></td>
                                </tr>
                                <tr id="detailPtkpRowK3">
                                    <td style="padding: 12px 14px; font-weight: 700; color: #1e293b;">K/3</td>
                                    <td style="padding: 12px 14px; color: #475569;">Kawin, 3 Tanggungan</td>
                                    <td style="padding: 12px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 72.000.000</td>
                                    <td style="padding: 12px 14px; text-align: center;"><span style="background: #f1f5f9; color: #334155; padding: 3px 12px; border-radius: 6px; font-weight: 700; font-size: 12px;">C</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Cara Hitung PTKP -->
                    <div style="background: linear-gradient(135deg, #eef2ff, #f0f9ff); border: 1px solid #c7d2fe; border-radius: 10px; padding: 16px;">
                        <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #1e293b;"><i class="fas fa-calculator" style="color: #6366f1; margin-right: 6px;"></i>Cara Menghitung PTKP</h5>
                        <div style="display: flex; flex-direction: column; gap: 6px; font-size: 12px; color: #334155; line-height: 1.6;">
                            <div>• <strong>Taxpayer Self:</strong> Rp 54,000,000</div>
                            <div>• <strong>Tambahan Status Kawin:</strong> + Rp 4.500.000</div>
                            <div>• <strong>Tambahan per Tanggungan:</strong> + Rp 4.500.000 (maks 3 orang)</div>
                            <div style="margin-top: 6px; background: white; border: 1px solid #c7d2fe; border-radius: 8px; padding: 10px 14px;">
                                <strong style="color: #4f46e5;">Contoh K/3:</strong> Rp 54.000.000 + Rp 4.500.000 <span style="color: #94a3b8;">(kawin)</span> + Rp 13.500.000 <span style="color: #94a3b8;">(3 tanggungan)</span> = <strong style="color: #166534;">Rp 72.000.000</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer" style="padding: 15px 24px; background: white; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <button type="button" class="btn-cancel" onclick="tutupDetailPph21Modal()" style="margin: 0; padding: 10px 24px;">Close</button>
        </div>
    </div>
    <div id="modalDetailPph21" class="modal-skema" style="display: none; width: 900px; max-width: 95%; z-index: 2005;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; display: flex; justify-content: space-between; align-items: center; padding: 18px 24px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-file-invoice-dollar" style="font-size: 20px; color: white;"></i>
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: white;" id="detailPph21Title">Detail Informasi PPh 21</h3>
                    <p style="margin: 2px 0 0 0; font-size: 12px; color: rgba(255, 255, 255, 0.85); font-weight: 400;" id="detailPph21Subtitle">PP No. 58 Tahun 2023 & PMK No. 168/2023</p>
                </div>
            </div>
            <i class="fas fa-times" style="cursor: pointer; color: white; font-size: 16px; padding: 4px;" onclick="tutupDetailPph21Modal()"></i>
        </div>
        <div class="modal-body" style="padding: 24px; max-height: 75vh; overflow-y: auto; background: #f8fafc;">

            <!-- Scheme Info Card -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Scheme Name</div>
                    <div style="font-size: 15px; font-weight: 700; color: #1e293b;" id="detailPph21SchemeName">-</div>
                </div>
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Tax Method</div>
                    <div style="font-size: 15px; font-weight: 700; color: #f59e0b;" id="detailPph21Method">-</div>
                </div>
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Status PTKP</div>
                    <div style="font-size: 15px; font-weight: 700; color: #3b82f6;" id="detailPph21PTKP">-</div>
                </div>
            </div>

            <!-- 1. Tax Method Explanation -->
            <div id="detailPph21MethodExplanation" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-bottom: 16px;">
                <h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-info-circle" style="color: #3b82f6;"></i> Tax Method Explanation
                </h4>
                <div id="detailPph21MethodDesc" style="font-size: 13px; color: #475569; line-height: 1.7;"></div>
            </div>

            <!-- 2. PTKP (Penghasilan Tidak Kena Pajak) -->
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-bottom: 16px;">
                <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-user-shield" style="color: #10b981;"></i> Annual PTKP (Non-Taxable Income)
                </h4>
                <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: #f1f5f9;">
                                <th style="padding: 10px 14px; text-align: left; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0;">PTKP Status</th>
                                <th style="padding: 10px 14px; text-align: left; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0;">Description</th>
                                <th style="padding: 10px 14px; text-align: right; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0;">Amount (per Year)</th>
                                <th style="padding: 10px 14px; text-align: center; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0;">TER Category</th>
                            </tr>
                        </thead>
                        <tbody id="detailPtkpTableBody">
                            <tr style="border-bottom: 1px solid #f1f5f9;" id="ptkpRowTK0">
                                <td style="padding: 10px 14px; font-weight: 700; color: #1e293b;">TK/0</td>
                                <td style="padding: 10px 14px; color: #475569;">Tidak Kawin, 0 Tanggungan</td>
                                <td style="padding: 10px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 54.000.000</td>
                                <td style="padding: 10px 14px; text-align: center;"><span style="background: #dbeafe; color: #1d4ed8; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 12px;">A</span></td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;" id="ptkpRowTK1">
                                <td style="padding: 10px 14px; font-weight: 700; color: #1e293b;">TK/1</td>
                                <td style="padding: 10px 14px; color: #475569;">Tidak Kawin, 1 Tanggungan</td>
                                <td style="padding: 10px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 58.500.000</td>
                                <td style="padding: 10px 14px; text-align: center;"><span style="background: #dbeafe; color: #1d4ed8; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 12px;">A</span></td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;" id="ptkpRowTK2">
                                <td style="padding: 10px 14px; font-weight: 700; color: #1e293b;">TK/2</td>
                                <td style="padding: 10px 14px; color: #475569;">Tidak Kawin, 2 Tanggungan</td>
                                <td style="padding: 10px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 63.000.000</td>
                                <td style="padding: 10px 14px; text-align: center;"><span style="background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 12px;">B</span></td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;" id="ptkpRowTK3">
                                <td style="padding: 10px 14px; font-weight: 700; color: #1e293b;">TK/3</td>
                                <td style="padding: 10px 14px; color: #475569;">Tidak Kawin, 3 Tanggungan</td>
                                <td style="padding: 10px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 67.500.000</td>
                                <td style="padding: 10px 14px; text-align: center;"><span style="background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 12px;">B</span></td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;" id="ptkpRowK0">
                                <td style="padding: 10px 14px; font-weight: 700; color: #1e293b;">K/0</td>
                                <td style="padding: 10px 14px; color: #475569;">Kawin, 0 Tanggungan</td>
                                <td style="padding: 10px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 58.500.000</td>
                                <td style="padding: 10px 14px; text-align: center;"><span style="background: #dbeafe; color: #1d4ed8; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 12px;">A</span></td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;" id="ptkpRowK1">
                                <td style="padding: 10px 14px; font-weight: 700; color: #1e293b;">K/1</td>
                                <td style="padding: 10px 14px; color: #475569;">Kawin, 1 Tanggungan</td>
                                <td style="padding: 10px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 63.000.000</td>
                                <td style="padding: 10px 14px; text-align: center;"><span style="background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 12px;">B</span></td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;" id="ptkpRowK2">
                                <td style="padding: 10px 14px; font-weight: 700; color: #1e293b;">K/2</td>
                                <td style="padding: 10px 14px; color: #475569;">Kawin, 2 Tanggungan</td>
                                <td style="padding: 10px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 67.500.000</td>
                                <td style="padding: 10px 14px; text-align: center;"><span style="background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 12px;">B</span></td>
                            </tr>
                            <tr id="ptkpRowK3">
                                <td style="padding: 10px 14px; font-weight: 700; color: #1e293b;">K/3</td>
                                <td style="padding: 10px 14px; color: #475569;">Kawin, 3 Tanggungan</td>
                                <td style="padding: 10px 14px; text-align: right; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums;">Rp 72.000.000</td>
                                <td style="padding: 10px 14px; text-align: center;"><span style="background: #fce7f3; color: #9d174d; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 12px;">C</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 10px; font-size: 11px; color: #64748b; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-info-circle"></i>
                    <span>PTKP = Income exempted from tax. Maximum 3 dependents (blood relations/in-laws in direct lineage).</span>
                </div>
            </div>

            <!-- 3. TER (Tarif Efektif Rata-rata) Bulanan -->
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-bottom: 16px;">
                <h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-percentage" style="color: #8b5cf6;"></i> Monthly Average Effective Tax Rate (TER)
                </h4>
                <p style="margin: 0 0 14px 0; font-size: 12px; color: #64748b;">Digunakan untuk pemotongan PPh 21 masa Januari s.d. November. Rumus: <strong>PPh 21 = Penghasilan Bruto × % TER</strong></p>

                <!-- TER Category A -->
                <div style="margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span style="background: #dbeafe; color: #1d4ed8; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 12px;">Kategori A</span>
                        <span style="font-size: 12px; color: #475569; font-weight: 500;">PTKP: TK/0, TK/1, K/0</span>
                    </div>
                    <div class="table-container" style="overflow-x: auto; border: 1px solid #dbeafe; border-radius: 8px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                            <thead>
                                <tr style="background: #eff6ff;">
                                    <th style="padding: 8px 12px; text-align: left; font-weight: 700; color: #1d4ed8; border-bottom: 1px solid #dbeafe;">Penghasilan Bruto Bulanan</th>
                                    <th style="padding: 8px 12px; text-align: right; font-weight: 700; color: #1d4ed8; border-bottom: 1px solid #dbeafe;">Tarif Efektif (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">s.d. Rp 5.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 5.400.001 – Rp 5.650.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,25%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 5.650.001 – Rp 5.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,50%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 5.950.001 – Rp 6.300.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,75%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.300.001 – Rp 6.750.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.750.001 – Rp 7.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,25%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 7.500.001 – Rp 8.550.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,50%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 8.550.001 – Rp 9.650.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,75%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 9.650.001 – Rp 10.050.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">2,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 10.050.001 – Rp 10.350.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">2,25%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 10.350.001 – Rp 10.700.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">2,50%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 10.700.001 – Rp 11.050.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">3,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 11.050.001 – Rp 11.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">3,50%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 11.600.001 – Rp 12.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">4,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 12.500.001 – Rp 13.750.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">5,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 13.750.001 – Rp 15.100.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">6,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 15.100.001 – Rp 16.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">7,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 16.950.001 – Rp 19.750.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">8,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 19.750.001 – Rp 24.150.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">9,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 24.150.001 – Rp 26.450.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">10,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 26.450.001 – Rp 28.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">11,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 28.000.001 – Rp 30.050.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">12,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 30.050.001 – Rp 32.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">13,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 32.400.001 – Rp 35.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">14,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 35.400.001 – Rp 39.100.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">15,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 39.100.001 – Rp 43.850.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">16,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 43.850.001 – Rp 47.800.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">17,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 47.800.001 – Rp 51.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">18,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 51.400.001 – Rp 56.300.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">19,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 56.300.001 – Rp 62.200.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">20,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 62.200.001 – Rp 68.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">21,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 68.600.001 – Rp 77.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">22,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 77.500.001 – Rp 89.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">23,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 89.000.001 – Rp 103.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">24,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 103.000.001 – Rp 125.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">25,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 125.000.001 – Rp 157.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">26,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 157.000.001 – Rp 206.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">27,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 206.000.001 – Rp 337.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">28,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 337.000.001 – Rp 454.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">29,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 454.000.001 – Rp 550.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">30,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 550.000.001 – Rp 695.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">31,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 695.000.001 – Rp 910.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">32,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 910.000.001 – Rp 1.400.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">33,00%</td></tr>
                                <tr><td style="padding: 7px 12px;">Lebih dari Rp 1.400.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">34,00%</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TER Category B -->
                <div style="margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span style="background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 12px;">Kategori B</span>
                        <span style="font-size: 12px; color: #475569; font-weight: 500;">PTKP: TK/2, TK/3, K/1, K/2</span>
                    </div>
                    <div class="table-container" style="overflow-x: auto; border: 1px solid #fef3c7; border-radius: 8px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                            <thead>
                                <tr style="background: #fffbeb;">
                                    <th style="padding: 8px 12px; text-align: left; font-weight: 700; color: #92400e; border-bottom: 1px solid #fef3c7;">Penghasilan Bruto Bulanan</th>
                                    <th style="padding: 8px 12px; text-align: right; font-weight: 700; color: #92400e; border-bottom: 1px solid #fef3c7;">Tarif Efektif (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">s.d. Rp 6.200.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.200.001 – Rp 6.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,25%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.500.001 – Rp 6.850.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,50%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.850.001 – Rp 7.300.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,75%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 7.300.001 – Rp 9.200.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 9.200.001 – Rp 10.750.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,50%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 10.750.001 – Rp 11.250.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">2,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 11.250.001 – Rp 11.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">2,50%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 11.600.001 – Rp 12.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">3,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 12.600.001 – Rp 13.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">4,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 13.600.001 – Rp 14.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">5,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 14.950.001 – Rp 16.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">6,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 16.400.001 – Rp 18.450.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">7,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 18.450.001 – Rp 21.850.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">8,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 21.850.001 – Rp 26.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">9,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 26.000.001 – Rp 27.700.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">10,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 27.700.001 – Rp 29.350.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">11,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 29.350.001 – Rp 31.450.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">12,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 31.450.001 – Rp 33.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">13,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 33.950.001 – Rp 37.100.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">14,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 37.100.001 – Rp 41.100.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">15,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 41.100.001 – Rp 45.800.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">16,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 45.800.001 – Rp 49.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">17,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 49.500.001 – Rp 53.800.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">18,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 53.800.001 – Rp 58.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">19,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 58.500.001 – Rp 64.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">20,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 64.000.001 – Rp 71.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">21,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 71.000.001 – Rp 80.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">22,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 80.000.001 – Rp 93.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">23,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 93.000.001 – Rp 109.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">24,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 109.000.001 – Rp 129.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">25,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 129.000.001 – Rp 163.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">26,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 163.000.001 – Rp 211.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">27,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 211.000.001 – Rp 374.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">28,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 374.000.001 – Rp 459.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">29,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 459.000.001 – Rp 555.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">30,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 555.000.001 – Rp 704.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">31,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 704.000.001 – Rp 957.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">32,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 957.000.001 – Rp 1.405.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">33,00%</td></tr>
                                <tr><td style="padding: 7px 12px;">Lebih dari Rp 1.405.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">34,00%</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TER Category C -->
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span style="background: #fce7f3; color: #9d174d; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 12px;">Kategori C</span>
                        <span style="font-size: 12px; color: #475569; font-weight: 500;">PTKP: K/3</span>
                    </div>
                    <div class="table-container" style="overflow-x: auto; border: 1px solid #fce7f3; border-radius: 8px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                            <thead>
                                <tr style="background: #fdf2f8;">
                                    <th style="padding: 8px 12px; text-align: left; font-weight: 700; color: #9d174d; border-bottom: 1px solid #fce7f3;">Penghasilan Bruto Bulanan</th>
                                    <th style="padding: 8px 12px; text-align: right; font-weight: 700; color: #9d174d; border-bottom: 1px solid #fce7f3;">Tarif Efektif (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">s.d. Rp 6.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.600.001 – Rp 6.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,25%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 6.950.001 – Rp 7.350.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,50%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 7.350.001 – Rp 7.800.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">0,75%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 7.800.001 – Rp 8.850.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 8.850.001 – Rp 9.800.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,25%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 9.800.001 – Rp 10.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,50%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 10.950.001 – Rp 11.200.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">1,75%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 11.200.001 – Rp 12.050.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">2,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 12.050.001 – Rp 12.950.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">3,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 12.950.001 – Rp 14.150.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">4,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 14.150.001 – Rp 15.550.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">5,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 15.550.001 – Rp 17.050.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">6,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 17.050.001 – Rp 19.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">7,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 19.500.001 – Rp 22.700.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">8,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 22.700.001 – Rp 26.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">9,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 26.600.001 – Rp 28.100.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">10,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 28.100.001 – Rp 30.100.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">11,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 30.100.001 – Rp 32.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">12,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 32.600.001 – Rp 35.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">13,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 35.400.001 – Rp 38.900.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">14,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 38.900.001 – Rp 43.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">15,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 43.000.001 – Rp 47.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">16,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 47.400.001 – Rp 51.200.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">17,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 51.200.001 – Rp 55.800.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">18,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 55.800.001 – Rp 60.400.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">19,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 60.400.001 – Rp 66.700.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">20,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 66.700.001 – Rp 74.500.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">21,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 74.500.001 – Rp 83.200.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">22,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 83.200.001 – Rp 95.600.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">23,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 95.600.001 – Rp 110.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">24,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 110.000.001 – Rp 134.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">25,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 134.000.001 – Rp 169.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">26,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 169.000.001 – Rp 221.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">27,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 221.000.001 – Rp 390.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">28,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 390.000.001 – Rp 463.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">29,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 463.000.001 – Rp 561.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">30,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 561.000.001 – Rp 709.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">31,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 709.000.001 – Rp 965.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">32,00%</td></tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;"><td style="padding: 7px 12px;">Rp 965.000.001 – Rp 1.419.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">33,00%</td></tr>
                                <tr><td style="padding: 7px 12px;">Lebih dari Rp 1.419.000.000</td><td style="padding: 7px 12px; text-align: right; font-weight: 600;">34,00%</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 4. Article 17 Progressive Rates (December / Annual) -->
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-bottom: 16px;">
                <h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-layer-group" style="color: #ef4444;"></i> Article 17 Progressive Rates of PPh Law (December Tax Period / Annual)
                </h4>
                <p style="margin: 0 0 12px 0; font-size: 12px; color: #64748b;">Used to recalculate PPh 21 in the final tax period (December) or when employees resign. <strong>Formula: PKP = (Annual Gross Income - Position Fee - Pension Contribution - PTKP)</strong></p>

                <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);">
                                <th style="padding: 12px 14px; text-align: left; font-weight: 700; color: #991b1b; border-bottom: 2px solid #fecaca;">Taxable Income Bracket (PKP) per Tahun</th>
                                <th style="padding: 12px 14px; text-align: center; font-weight: 700; color: #991b1b; border-bottom: 2px solid #fecaca; width: 120px;">Tarif</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 14px; color: #334155;">s.d. Rp 60.000.000</td>
                                <td style="padding: 12px 14px; text-align: center;">
                                    <span style="background: #dcfce7; color: #166534; padding: 4px 14px; border-radius: 6px; font-weight: 700; font-size: 14px;">5%</span>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 14px; color: #334155;">Rp 60.000.001 – Rp 250.000.000</td>
                                <td style="padding: 12px 14px; text-align: center;">
                                    <span style="background: #fef9c3; color: #854d0e; padding: 4px 14px; border-radius: 6px; font-weight: 700; font-size: 14px;">15%</span>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 14px; color: #334155;">Rp 250.000.001 – Rp 500.000.000</td>
                                <td style="padding: 12px 14px; text-align: center;">
                                    <span style="background: #fed7aa; color: #9a3412; padding: 4px 14px; border-radius: 6px; font-weight: 700; font-size: 14px;">25%</span>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 14px; color: #334155;">Rp 500.000.001 – Rp 5.000.000.000</td>
                                <td style="padding: 12px 14px; text-align: center;">
                                    <span style="background: #fecaca; color: #991b1b; padding: 4px 14px; border-radius: 6px; font-weight: 700; font-size: 14px;">30%</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 14px; color: #334155;">Lebih dari Rp 5.000.000.000</td>
                                <td style="padding: 12px 14px; text-align: center;">
                                    <span style="background: #f87171; color: white; padding: 4px 14px; border-radius: 6px; font-weight: 700; font-size: 14px;">35%</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 5. Biaya Jabatan & Iuran Pensiun -->
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-bottom: 16px;">
                <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-receipt" style="color: #f59e0b;"></i> Pengurang Penghasilan Bruto
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 10px; padding: 14px;">
                        <div style="font-weight: 700; font-size: 13px; color: #92400e; margin-bottom: 6px;"><i class="fas fa-briefcase" style="margin-right: 6px;"></i>Biaya Jabatan</div>
                        <div style="font-size: 12px; color: #78350f; line-height: 1.7;">
                            <div>• Tarif: <strong>5%</strong> dari Penghasilan Bruto</div>
                            <div>• Maks: <strong>Rp 500.000 / bulan</strong></div>
                            <div>• Maks: <strong>Rp 6.000.000 / tahun</strong></div>
                        </div>
                    </div>
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px;">
                        <div style="font-weight: 700; font-size: 13px; color: #166534; margin-bottom: 6px;"><i class="fas fa-piggy-bank" style="margin-right: 6px;"></i>Iuran Pensiun / THT</div>
                        <div style="font-size: 12px; color: #14532d; line-height: 1.7;">
                            <div>• Iuran Jaminan Pensiun (JP): <strong>1%</strong> employee</div>
                            <div>• Iuran JHT: <strong>2%</strong> employee</div>
                            <div>• Dikurangkan dari Penghasilan Bruto sebelum dihitung PKP</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. Alur Perhitungan PPh 21 -->
            <div style="background: linear-gradient(135deg, #eef2ff 0%, #f0f9ff 100%); border: 1px solid #c7d2fe; border-radius: 12px; padding: 18px;">
                <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-project-diagram" style="color: #6366f1;"></i> Alur Perhitungan PPh 21 (TER Bulanan + Progresif Desember)
                </h4>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="background: #6366f1; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">1</span>
                        <span style="font-size: 13px; color: #334155;"><strong>Jan – Nov:</strong> PPh 21 Bulanan = Penghasilan Bruto × Tarif TER (berdasarkan kategori PTKP)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="background: #6366f1; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">2</span>
                        <span style="font-size: 13px; color: #334155;"><strong>Desember:</strong> Hitung Penghasilan Bruto setahun (akumulasi Jan–Des)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="background: #6366f1; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">3</span>
                        <span style="font-size: 13px; color: #334155;"><strong>Kurangi:</strong> Biaya Jabatan (5%, maks Rp 6 jt/thn) + Iuran Pensiun/JHT employee</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="background: #6366f1; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">4</span>
                        <span style="font-size: 13px; color: #334155;"><strong>Kurangi PTKP:</strong> PKP = Penghasilan Neto − PTKP (sesuai status)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="background: #6366f1; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">5</span>
                        <span style="font-size: 13px; color: #334155;"><strong>Hitung PPh 21 Terutang Setahun:</strong> PKP × Tarif Progresif Pasal 17</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="background: #10b981; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">6</span>
                        <span style="font-size: 13px; color: #334155;"><strong>PPh 21 Desember:</strong> PPh 21 Terutang Setahun − Total PPh 21 yg sudah dipotong Jan–Nov</span>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer" style="padding: 15px 24px; background: white; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <button type="button" class="btn-cancel" onclick="tutupDetailPph21Modal()" style="margin: 0; padding: 10px 24px;">Close</button>
        </div>
    </div>



    <!-- Custom Toast Container -->
    <div id="toastContainer"></div>

    <!-- Modal Add Client Scheme Option -->
    <div id="modalPilihanSkema" class="modal-skema" style="width: 800px; max-width: 95%;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="modalPilihanSkemaTitle">Add Client Scheme</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalPilihanSkema()"></i>
        </div>
        <form id="formPilihanSkema" onsubmit="event.preventDefault(); simpanPilihanSkema();">
            <div class="modal-body" style="padding: 25px;">
                <input type="hidden" id="pilihanSkemaSetupId">
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <!-- Client Name (Read-only) -->
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
                        <span style="font-weight: 600; color: #475569;"><i class="fas fa-building" style="margin-right: 8px;"></i>Client Name</span>
                        <input type="text" id="modalPilihanSkemaNamaKlien" readonly style="width: 50%; padding: 8px 12px; border-radius: 8px; border: 1px solid #ddd; background-color: #f1f5f9; font-weight: 600;">
                    </div>
                    
                    <!-- Organizational Structure: Divisi, Departemen, Posisi -->
                    <div style="display: flex; flex-direction: column; gap: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
                        <span style="font-weight: 600; color: #475569; margin-bottom: 5px;"><i class="fas fa-sitemap" style="margin-right: 8px;"></i>Organizational Structure (Optional)</span>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                            <select id="modalPilihanSkemaDivisi" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ddd; background: white;">
                                <option value="">-- Select Division --</option>
                            </select>
                            <select id="modalPilihanSkemaDepartemen" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ddd; background: white;">
                                <option value="">-- Select Department --</option>
                            </select>
                            <select id="modalPilihanSkemaPosisi" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ddd; background: white;">
                                <option value="">-- Select Position --</option>
                            </select>
                        </div>
                        <small style="color: #64748b; font-size: 11px;">Leave blank to apply the scheme globally/client-wide.</small>
                    </div>

                    <!-- Payroll Scheme -->
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
                        <div style="display: flex; flex-direction: column;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-weight: 600; color: #475569;"><i class="fas fa-wallet" style="margin-right: 8px;"></i>Payroll Scheme</span>
                                <button type="button" id="modalBtnDetailSkemaPayroll" class="btn-detail-payroll" onclick="lihatDetailSkemaPayrollModal()" style="background: none; border: none; color: #f39c12; cursor: pointer; display: none; align-items: center; gap: 4px; font-size: 13px; font-weight: 600;">
                                    <i class="fas fa-eye"></i> Scheme Detail
                                </button>
                            </div>
                            <small style="color: #64748b; font-size: 11px;">Select the payroll template scheme that applies for salary calculation.</small>
                        </div>
                        <select id="modalPilihanSkemaPayroll" onchange="handleModalPilihanSkemaPayrollChange(this.value)" required style="width: 50%; padding: 8px 12px; border-radius: 8px; border: 1px solid #ddd; background: white;">
                            <option value="">-- Select Payroll Scheme --</option>
                        </select>
                    </div>

                    <!-- Cut Off & Pay Day Configuration -->
                    <div style="display: flex; flex-direction: column; gap: 15px; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; background: #f8fafc; margin-top: -5px;">
                        <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b;"><i class="fas fa-calendar-alt" style="color: var(--primary-color); margin-right: 6px;"></i>Cut Off & Pay Day Configuration</h4>
                        
                        <div style="display: flex; gap: 24px; align-items: flex-start;">
                            <!-- Cut Off -->
                            <div style="flex: 1; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                                <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Tanggal Cut Off</label>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="number" id="modalPilihanSkemaCutoffStart" min="1" max="31" placeholder="-" required style="width: 60px; height: 42px; text-align: center; font-size: 16px; font-weight: 700; border: 1px solid #ddd; border-radius: 8px; outline: none; color: #1e293b; background: white; -moz-appearance: textfield;" oninput="if(this.value>31)this.value=31;if(this.value<1&&this.value!=='')this.value=1;">
                                    <small style="color: #94a3b8; font-size: 11px; line-height: 1.3;"><i class="fas fa-info-circle" style="margin-right: 3px;"></i>Tanggal mulai perhitungan<br>periode payroll.</small>
                                </div>
                            </div>

                            <!-- Pay Day -->
                            <div style="flex: 1; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                                <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;"><i class="fas fa-money-check-alt" style="color: #16a34a; margin-right: 4px;"></i>Pay Day / Tanggal Gajian</label>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="number" id="modalPilihanSkemaPayDate" min="1" max="31" placeholder="-" required style="width: 60px; height: 42px; text-align: center; font-size: 16px; font-weight: 700; border: 1px solid #ddd; border-radius: 8px; outline: none; color: #1e293b; background: white; -moz-appearance: textfield;" oninput="if(this.value>31)this.value=31;if(this.value<1&&this.value!=='')this.value=1;">
                                    <small style="color: #94a3b8; font-size: 11px; line-height: 1.3;"><i class="fas fa-info-circle" style="margin-right: 3px;"></i>Tanggal pembayaran gaji<br>setiap bulan.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BPJS Scheme (Hidden from user, defaults to tambah_skema) -->
                    <div style="display: none;">
                        <select id="modalPilihanSkemaBpjs" onchange="handleModalPilihanSkemaBpjsChange(this.value)" required>
                            <option value="tambah_skema">Add Scheme</option>
                        </select>
                    </div>


                    <!-- BPJS Configuration Inputs (cloned/copied from modalBpjs details) -->
                    <div id="modalClientBpjsOverrideFields" style="display: flex; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; background: #f8fafc; flex-direction: column; gap: 12px; margin-top: 10px; margin-bottom: 10px;">
                        <h4 style="margin: 0 0 5px 0; font-size: 14px; font-weight: 700; color: #1e293b;"><i class="fas fa-cog" style="color: var(--primary-color);"></i> BPJS Programs Activation</h4>
                        <p style="margin: 0; font-size: 12px; color: #64748b;">
                            Select which BPJS programs are active for this client workspace. Inactive programs will be calculated as 0.
                        </p>
                        
                        <div style="display: grid; grid-template-columns: 1fr; gap: 10px; margin-top: 5px;">
                            <!-- BPJS Health -->
                            <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; cursor: pointer; transition: all 0.2s ease;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7;">
                                        <i class="fas fa-hand-holding-medical" style="font-size: 16px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #1e293b; font-size: 13px;">BPJS Health</div>
                                        <div style="font-size: 11px; color: #64748b;" id="mClientBpjsKesDesc">Default: Karyawan 1%, Perusahaan 4%</div>
                                    </div>
                                </div>
                                <input type="checkbox" id="mClientBpjsKesActive" style="width: 18px; height: 18px; accent-color: var(--primary-color); cursor: pointer;">
                            </label>

                            <!-- JHT -->
                            <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; cursor: pointer; transition: all 0.2s ease;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 6px; background: #dcfce7; display: flex; align-items: center; justify-content: center; color: #16a34a;">
                                        <i class="fas fa-coins" style="font-size: 16px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #1e293b; font-size: 13px;">Jaminan Hari Tua (JHT)</div>
                                        <div style="font-size: 11px; color: #64748b;" id="mClientBpjsJhtDesc">Default: Karyawan 2%, Perusahaan 3.7%</div>
                                    </div>
                                </div>
                                <input type="checkbox" id="mClientBpjsJhtActive" style="width: 18px; height: 18px; accent-color: var(--primary-color); cursor: pointer;">
                            </label>

                            <!-- JP -->
                            <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; cursor: pointer; transition: all 0.2s ease;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 6px; background: #fef9c3; display: flex; align-items: center; justify-content: center; color: #ca8a04;">
                                        <i class="fas fa-piggy-bank" style="font-size: 16px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #1e293b; font-size: 13px;">Jaminan Pensiun (JP)</div>
                                        <div style="font-size: 11px; color: #64748b;" id="mClientBpjsJpDesc">Default: Karyawan 1%, Perusahaan 2%</div>
                                    </div>
                                </div>
                                <input type="checkbox" id="mClientBpjsJpActive" style="width: 18px; height: 18px; accent-color: var(--primary-color); cursor: pointer;">
                            </label>

                            <!-- JKK -->
                            <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; cursor: pointer; transition: all 0.2s ease;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 6px; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626;">
                                        <i class="fas fa-user-shield" style="font-size: 16px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #1e293b; font-size: 13px;">Jaminan Kecelakaan Kerja (JKK)</div>
                                        <div style="font-size: 11px; color: #64748b;" id="mClientBpjsJkkDesc">Default: Perusahaan 0.24%</div>
                                    </div>
                                </div>
                                <input type="checkbox" id="mClientBpjsJkkActive" style="width: 18px; height: 18px; accent-color: var(--primary-color); cursor: pointer;">
                            </label>

                            <!-- JKM -->
                            <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; cursor: pointer; transition: all 0.2s ease;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 6px; background: #f3e8ff; display: flex; align-items: center; justify-content: center; color: #9333ea;">
                                        <i class="fas fa-heartbeat" style="font-size: 16px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #1e293b; font-size: 13px;">Jaminan Kematian (JKM)</div>
                                        <div style="font-size: 11px; color: #64748b;" id="mClientBpjsJkmDesc">Default: Perusahaan 0.3%</div>
                                    </div>
                                </div>
                                <input type="checkbox" id="mClientBpjsJkmActive" style="width: 18px; height: 18px; accent-color: var(--primary-color); cursor: pointer;">
                            </label>
                        </div>
                    </div>

                    <!-- Tax Scheme Hidden -->
                    <div style="display: none; align-items: center; justify-content: space-between;">
                        <div style="display: flex; flex-direction: column;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-weight: 600; color: #475569;"><i class="fas fa-percent" style="margin-right: 8px;"></i>PPh 21 Scheme</span>
                                <button type="button" id="modalBtnDetailSkemaPajak" class="btn-detail-pajak" onclick="lihatDetailSkemaPajakModal()" style="background: none; border: none; color: #f39c12; cursor: pointer; display: none; align-items: center; gap: 4px; font-size: 13px; font-weight: 600;">
                                    <i class="fas fa-eye"></i> Scheme Detail
                                </button>
                            </div>
                            <small style="color: #64748b; font-size: 11px;">Calculation method for PPh 21 tax deductions.</small>
                        </div>
                        <select id="modalPilihanSkemaPajak" onchange="handleModalPilihanSkemaPajakChange(this.value)" style="width: 50%; padding: 8px 12px; border-radius: 8px; border: 1px solid #ddd; background: white;">
                            <option value="">-- Select Tax Scheme --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-cancel" onclick="tutupModalPilihanSkema()" style="padding: 10px 24px; border-radius: 8px; border: 1px solid #ddd; background: white; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color); color: white; border: none; padding: 10px 24px; border-radius: 8px; cursor: pointer; font-weight: 600;">Save Scheme Selection</button>
            </div>
        </form>
    </div>

    <!-- Custom Confirm Dialog -->
    <div id="confirmOverlay" class="confirm-overlay"></div>
    <div id="confirmDialog" class="confirm-dialog">
        <div class="confirm-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 id="confirmTitle" class="confirm-title">Confirmation</h3>
        <p id="confirmMessage" class="confirm-message">Are you sure?</p>
        <div class="confirm-actions">
            <button id="confirmCancel" class="confirm-btn confirm-btn-cancel">Cancel</button>
            <button id="confirmOk" class="confirm-btn confirm-btn-ok">Yes, Delete</button>
        </div>
    </div>

    </div>

    <!-- PKWT Form Modal -->
    <div id="modalPKWT" class="modal-skema">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalPKWTTitle">Create PKWT Contract</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalPKWT()"></i>
        </div>
        <form id="formPKWT">
            <div class="modal-body">
                <input type="hidden" id="pkwtId">
                <div class="form-group">
                    <label>Employee Name</label>
                    <input type="text" id="pkwtEmployeeName" placeholder="Enter full name" required>
                </div>
                <div class="form-group">
                    <label>Client</label>
                    <select id="pkwtClientId" required onchange="updatePKWTSchemeInfo()">
                        <option value="">-- Select Client --</option>
                        <!-- Injected by app.js -->
                    </select>
                </div>
                <div id="pkwtSchemeInfo" style="margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; font-size: 12px; display: none;">
                    <i class="fas fa-info-circle"></i> <span id="pkwtSchemeText">Scheme: -</span>
                </div>
                <div class="form-group">
                    <label>Position / Title</label>
                    <input type="text" id="pkwtPositionName" placeholder="Example: Admin Staff" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" id="pkwtStartDate" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" id="pkwtEndDate" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Basic Salary (IDR)</label>
                    <input type="number" id="pkwtBasicSalary" placeholder="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalPKWT()">Cancel</button>
                <button type="submit" class="btn-save">Generate & Save PKWT</button>
            </div>
        </form>
    </div>

    </div>

    <!-- Manage Period Modal -->
    <div id="modalPeriode" class="modal-skema">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3>Payroll Period Management</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalPeriode()"></i>
        </div>
        <div class="modal-body">
            <form id="formPeriode" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Month</label>
                        <select id="periodMonth" required>
                            <option value="1">January</option><option value="2">February</option><option value="3">March</option>
                            <option value="4">April</option><option value="5">May</option><option value="6">June</option>
                            <option value="7">July</option><option value="8">August</option><option value="9">September</option>
                            <option value="10">October</option><option value="11">November</option><option value="12">December</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Year</label>
                        <input type="number" id="periodYear" value="2024" required>
                    </div>
                </div>
                <button type="submit" class="btn-save" style="width: 100%; background: var(--primary-color);">Open New Period</button>
            </form>
            
            <h4 style="margin-bottom: 10px; font-size: 14px;">Period History</h4>
            <div id="periodHistoryList" style="max-height: 200px; overflow-y: auto;">
                <!-- List of periods injected by app.js -->
            </div>
        </div>
    </div>

    <!-- Cut-Off Input Modal -->
    <div id="modalCutOff" class="modal-skema">
        <div class="modal-header" style="background: var(--secondary-color);">
            <h3 id="modalCutOffTitle">Input Cut-Off Data</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalCutOff()"></i>
        </div>
        <form id="formCutOff">
            <div class="modal-body">
                <input type="hidden" id="cutoffPkwtId">
                <div class="form-group">
                    <label>Employee Name</label>
                    <input type="text" id="cutoffEmployeeName" readonly style="background: #f0f0f0;">
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Working Days</label>
                        <input type="number" id="cutoffHariKerja" value="22" required>
                    </div>
                    <div class="form-group">
                        <label>Overtime Hours (Hours)</label>
                        <input type="number" id="cutoffJamLembur" step="0.5" value="0" required>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Attendance Deduction (IDR)</label>
                        <input type="number" id="cutoffPotongan" value="0" required>
                    </div>
                    <div class="form-group">
                        <label>Bonus/Others (IDR)</label>
                        <input type="number" id="cutoffBonus" value="0" required>
                    </div>
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label>Early Arrival (Hours)</label>
                    <input type="number" id="cutoffEarlyArrival" value="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalCutOff()">Cancel</button>
                <button type="submit" class="btn-save" style="background: var(--secondary-color);">Save Data</button>
            </div>
        </form>
    </div>

    <!-- Upload UMP/UMK CSV Modal -->
    <div id="modalUploadUmr" class="modal-skema" style="display: none;">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3>Upload UMP/UMK Data</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalUploadUmr()"></i>
        </div>
        <form id="formUploadUmr">
            <div class="modal-body" style="padding: 25px;">
                <input type="hidden" id="uploadUmrTipe" value="UMP">
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Minimum Wage Year</label>
                    <select id="uploadUmrTahun" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Excel File</label>
                    <!-- Drag & Drop Uploader Area -->
                    <div id="umrDropZone" style="border: 2px dashed #ddd; border-radius: 12px; padding: 35px 20px; text-align: center; background: #fafafa; cursor: pointer; transition: all 0.3s ease;">
                        <i class="fas fa-file-excel" style="font-size: 48px; color: var(--primary-color); margin-bottom: 15px;"></i>
                        <h4 style="font-size: 14px; font-weight: 600; color: #333; margin-bottom: 6px;">Drag & Drop file here</h4>
                        <p style="font-size: 12px; color: #7f8c8d; margin-bottom: 15px;">or click to browse files from your computer</p>
                        <span id="umrFileName" style="font-size: 13px; font-weight: 600; color: var(--info); display: block; word-break: break-all;">No file selected yet</span>
                        <input type="file" id="fileUmr" accept=".xlsx, .xls" style="display: none;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalUploadUmr()">Cancel</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Upload Now</button>
            </div>
        </form>
    </div>

    <!-- Manual Input UMP/UMK Modal -->
    <div id="modalManualUmr" class="modal-skema" style="display: none;">
        <div class="modal-header" style="background: #2c3e50;">
            <h3 id="modalManualTitle">Add Minimum Wage Data</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalManualUmr()"></i>
        </div>
        <form id="formManualUmr">
            <div class="modal-body" style="padding: 25px;">
                <input type="hidden" id="manualUmrId">
                <input type="hidden" id="manualUmrTipe" value="UMP">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Region Code (Province / City / Regency)</label>
                    <input type="text" id="manualUmrKode" placeholder="Example: ID 31 or 31.71" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Region Name</label>
                    <input type="text" id="manualUmrNama" placeholder="Example: DKI JAKARTA or SOUTH JAKARTA" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Parent Province (UMK Only)</label>
                    <input type="text" id="manualUmrProvinsi" placeholder="Example: WEST JAVA (leave blank if UMP)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                </div>

                <input type="hidden" id="manualUmrNominal" value="0">
                <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Year</label>
                        <input type="number" id="manualUmrTahun" placeholder="2026" required value="2026" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                    </div>
                </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalManualUmr()">Cancel</button>
                <button type="submit" class="btn-save" style="background: #2c3e50;">Save Data</button>
            </div>
        </form>
    </div>

    <!-- Employee Data Modal -->
    <div id="modalKaryawan" class="modal-skema" style="display: none; z-index: 1000; width: 780px; max-width: 95vw; max-height: 85vh; overflow: hidden; flex-direction: column; box-sizing: border-box;">
        <div class="modal-header" style="background: var(--primary-color); flex-shrink: 0; padding: 18px 25px;">
            <h3 id="modalKaryawanTitle">Add Employee Data</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalKaryawan()"></i>
        </div>
        <form id="formKaryawan" novalidate style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            <div class="modal-body" style="padding: 25px; overflow-y: auto; flex: 1; min-height: 0;">
                <input type="hidden" id="employeeId">
                
                <div class="form-group" id="empClientIdContainer" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Select Company / Client</label>
                    <select id="empClientId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background-color: white;">
                        <option value="">-- Select Client --</option>
                    </select>
                </div>

                <div class="form-grid" id="empNikNamaGrid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" id="empEmployIdContainer">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">NIK (Employee Identification Number)</label>
                        <input type="text" id="empEmployId" readonly placeholder="Auto-filled" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: #f1f5f9; color: #475569; font-weight: 700; letter-spacing: 1px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Full Name</label>
                        <input type="text" id="empNama" placeholder="Employee Name" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Email Address</label>
                    <input type="email" id="empEmail" placeholder="employee@example.com" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">User Account Role (System Access)</label>
                    <select id="empUserRole" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background-color: white;">
                        <option value="staff">Staff</option>
                        <option value="recruiter">Recruiter</option>
                        <option value="hc_ops">HC Ops</option>
                        <option value="payroll">Payroll</option>
                        <option value="business_development">Business Development</option>
                        <option value="client_superior">Client / Superior</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Place of Birth</label>
                        <input type="text" id="empTempatLahir" placeholder="Example: Jakarta" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Date of Birth</label>
                        <input type="date" id="empTanggalLahir" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">NPWP</label>
                        <input type="text" id="empNpwp" placeholder="Example: 00.000.000.0-000.000" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Marital Status</label>
                        <select id="empStatusPernikahan" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="">-- Select Status --</option>
                            <option value="Belum">Single</option>
                            <option value="Sudah">Married</option>
                            <option value="Cerai">Divorced</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">PTKP Status</label>
                        <select id="empPtkp" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background-color: white;">
                            <option value="TK/0">TK/0</option>
                            <option value="TK/1">TK/1</option>
                            <option value="TK/2">TK/2</option>
                            <option value="TK/3">TK/3</option>
                            <option value="K/0">K/0</option>
                            <option value="K/1">K/1</option>
                            <option value="K/2">K/2</option>
                            <option value="K/3">K/3</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="empJumlahAnakContainer" style="margin-bottom: 15px; display: none;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Number of Children</label>
                    <input type="number" id="empJumlahAnak" min="0" placeholder="0" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Contract Start</label>
                        <input type="date" id="empStartContract" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Contract End</label>
                        <input type="date" id="empEndContract" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Agreement Type</label>
                        <select id="empTipePerjanjian" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="">-- Select Type --</option>
                            <option value="PKWT">PKWT</option>
                            <option value="PKWTT">PKWTT</option>
                            <option value="PKHL">PKHL</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Division</label>
                        <select id="empDivisionId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="">-- Select Division --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Department</label>
                        <select id="empDepartmentId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="">-- Select Department --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Position / Role</label>
                        <select id="empPositionId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="">-- Select Position --</option>
                        </select>
                    </div>
                </div>
                <div id="schemaInfoContainer" style="margin-top: -5px; margin-bottom: 15px; font-size: 12px; font-weight: 500; display: none; padding: 10px 14px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; color: #475569;"></div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Work Location</label>
                    <select id="empWorkLocationId" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="">-- Select Work Location --</option>
                    </select>
                </div>



                <div class="form-group" id="empMinimumWageContainer" style="margin-bottom: 15px; display: none;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Location Minimum Wage (UMP / UMK)</label>
                    <div style="position: relative;">
                        <input type="text" id="empMinimumWage" readonly style="width: 100%; padding: 10px 10px 10px 40px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: #f8fafc; color: #475569; font-weight: 600;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 600; color: #64748b; font-size: 14px;">Rp</span>
                    </div>
                    <small id="empMinimumWageInfo" style="margin-top: 4px; display: block; font-size: 12px; color: #64748b; font-weight: 500;"></small>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Working Days</label>
                    <select id="empHariKerja" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="5">5 Days</option>
                        <option value="6">6 Days</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 15px; display: none;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Custom Standard Days for Prorata (Optional)</label>
                    <input type="number" id="empCustomStandardDays" placeholder="Default global: 20 days" min="1" max="31" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    <small style="color: #94a3b8; font-size: 12px;">Leave empty to use global default of 20 standard days.</small>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Skema Shift (Opsional)</label>
                    <select id="empShiftSchemeId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background-color: white;">
                        <option value="">-- Tanpa Shift / Default --</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="flex-shrink: 0; padding: 15px 25px;">
                <button type="button" class="btn-cancel" onclick="tutupModalKaryawan()">Cancel</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Save Data</button>
            </div>
        </form>
    </div>

    <!-- Work Location Modal -->
    <div id="modalLokasiKerja" class="modal-skema" style="display: none; z-index: 1000; width: 650px; max-width: 95vw; max-height: 85vh; overflow: hidden; flex-direction: column; box-sizing: border-box;">
        <div class="modal-header" style="background: var(--primary-color); flex-shrink: 0; padding: 18px 25px;">
            <h3 id="modalLokasiKerjaTitle">Add Work Location</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalLokasiKerja()"></i>
        </div>
        <form id="formLokasiKerja" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0;">
            <div class="modal-body" style="padding: 25px; overflow-y: auto; flex: 1; min-height: 0;">
                <input type="hidden" id="workLocationId">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Select Company / Client</label>
                    <select id="locClientId" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="">-- Select Client --</option>
                    </select>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Work Location</label>
                        <input type="text" id="locName" placeholder="Example: Bandung Branch Office" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <input type="hidden" id="locCode">
                </div>



                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Province</label>
                        <input type="text" id="locProvinsi" list="provinsiList" placeholder="Type or select Province..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <datalist id="provinsiList"></datalist>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">City/Regency</label>
                        <input type="text" id="locKotaKabupaten" list="kotaList" placeholder="Type or select City/Regency..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <datalist id="kotaList"></datalist>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="flex-shrink: 0; padding: 15px 25px;">
                <button type="button" class="btn-cancel" onclick="tutupModalLokasiKerja()">Cancel</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Save Data</button>
            </div>
        </form>
    </div>

    <!-- Allowance Scheme Modal (Master) -->
    <div id="modalSkemaKompensasi" class="modal-skema" style="display: none;">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalSkemaKompensasiTitle">Add Allowance Scheme</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalSkemaKompensasi()"></i>
        </div>
        <form id="formSkemaKompensasi">
            <div class="modal-body">
                <input type="hidden" id="skemaKompensasiId">
                <input type="hidden" id="skemaKompensasiDeskripsi" value="">
                <input type="hidden" id="skemaKompensasiIsPersentase" value="0">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Allowance Scheme Name</label>
                    <input type="text" id="skemaKompensasiNama" placeholder="Example: Meal Allowance" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Allowance Type</label>
                    <select id="skemaKompensasiSifat" onchange="handleSchemeSifatChange()" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white;">
                        <option value="tetap">Fixed Allowance</option>
                        <option value="tidak_tetap">Variable Allowance</option>
                    </select>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label>Value Source</label>
                        <select id="skemaKompensasiSumber" onchange="handleSchemeSumberNilaiChange()" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white;">
                            <option value="ump">UMP (Province)</option>
                            <option value="umk">UMK (City/Regency)</option>
                            <option value="nominal" selected>Custom Nominal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Period</label>
                        <select id="skemaKompensasiPeriode" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white;">
                            <option value="bulan" selected>Per Month</option>
                            <option value="minggu">Per Week</option>
                            <option value="hari_kerja">Per Working Day</option>
                            <option value="tahun">Per Year</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label id="labelNilaiSkema">Custom Nominal (IDR)</label>
                    <input type="text" id="skemaKompensasiNilai" placeholder="Example: 200000" onkeyup="handleSchemeNilaiInput(this)" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalSkemaKompensasi()">Cancel</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Save Scheme</button>
            </div>
        </form>
    </div>
    <!-- Allowance Component Modal (Master) -->
    <div id="modalKomponenKompensasi" class="modal-skema" style="display: none;">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalKomponenKompensasiTitle">Add Allowance</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalKomponenKompensasi()"></i>
        </div>
        <form id="formKomponenKompensasi">
            <div class="modal-body">
                <input type="hidden" id="komponenKompensasiId">
                <input type="hidden" id="komponenKompensasiSchemeId">
                
                <div class="form-group">
                    <label>Allowance Type</label>
                    <select id="komponenKompensasiJenis" onchange="handleJenisKomponenChange()" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="basic_salary">Basic Salary</option>
                        <option value="kompensasi" selected>Fixed / Variable Allowance</option>
                    </select>
                </div>

                <!-- Show Allowance Type if type is Allowance -->
                <div class="form-group" id="containerSifatKompensasi" style="display: block; margin-bottom: 15px;">
                    <label>Allowance Type</label>
                    <select id="komponenKompensasiSifat" onchange="handleKomponenSifatChange()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="tetap">Fixed Allowance</option>
                        <option value="tidak_tetap">Variable Allowance</option>
                    </select>
                </div>


                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label>Value Source</label>
                        <select id="komponenKompensasiSumber" onchange="handleSumberNilaiChange()" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="ump">UMP (Province)</option>
                            <option value="umk">UMK (City/Regency)</option>
                            <option value="nominal" selected>Custom Nominal</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="containerFormatNilai" style="display: none;">
                        <label>Value Format</label>
                            <select id="komponenKompensasiIsPersentase" onchange="handleKomponenKompensasiNilaiInput(document.getElementById('komponenKompensasiNilai'))" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                             <option value="0" selected>Rupiah (IDR)</option>
                             <option value="1">Percentage (%)</option>
                          </select>
                    </div>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label id="labelNilaiKompensasi">Custom Nominal (IDR)</label>
                        <input type="text" id="komponenKompensasiNilai" placeholder="Example: 5000000" value="0" onkeyup="handleKomponenKompensasiNilaiInput(this)" required>
                    </div>

                    <div class="form-group">
                        <label>Period / Cycle</label>
                        <select id="komponenKompensasiPeriode" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="hari">Day</option>
                            <option value="minggu">Week</option>
                            <option value="bulan" selected>Month</option>
                            <option value="tahun">Year</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalKomponenKompensasi()">Cancel</button>
                <button type="submit" class="btn-save" style="background: #10b981;">Save Allowance</button>
            </div>
        </form>
    </div>

    <!-- Select Scheme Modal (New Pop-up) -->
    <div id="overlayPilihSkema" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000;" onclick="tutupModalPilihSkema()"></div>
    <div id="modalPilihSkema" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 700px; max-width: 90%; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 2001; overflow: hidden; font-family: 'Inter', sans-serif;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
            <h3 id="modalPilihSkemaTitle" style="margin: 0; font-size: 18px; font-weight: 600; color: white;">Select Scheme</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white;" onclick="tutupModalPilihSkema()"></i>
        </div>
        <div class="modal-body" style="padding: 20px; max-height: 400px; overflow-y: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 2px solid #cbd5e1; color: #475569; background: #e2e8f0;">
                        <th style="padding: 10px 8px; text-align: center; width: 60px;">Select</th>
                        <th style="padding: 10px 8px; text-align: left; width: 35%;">Scheme Name</th>
                        <th style="padding: 10px 8px; text-align: left; width: 60%;">Allowance</th>
                    </tr>
                </thead>
                <tbody id="modalPilihSkemaBody">
                    <!-- Dynamically populated -->
                </tbody>
            </table>
        </div>
        <div class="modal-footer" style="padding: 15px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <button type="button" class="btn-cancel" onclick="tutupModalPilihSkema()" style="margin: 0; padding: 10px 20px;">Cancel</button>
            <button type="button" class="btn-save" onclick="terapkanPilihanSkema()" style="margin: 0; padding: 10px 20px; background: #0d6efd; color: white;">Apply</button>
        </div>
    </div>

    <!-- Payroll Scheme Detail Modal -->
    <div id="modalDetailSkemaPayroll" class="modal-skema" style="width: 600px; max-width: 95%;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3>Payroll Scheme Detail</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="document.getElementById('modalDetailSkemaPayroll').style.display='none'; document.getElementById('overlay').style.display='none';"></i>
        </div>
        <div class="modal-body" style="padding: 25px; max-height: 75vh; overflow-y: auto;">
            <div id="detailSkemaPayrollContent">
                <h4 style="margin: 0 0 5px 0; font-size: 18px; font-weight: 700; color: #1e293b;" id="dtlNama">Scheme Name</h4>
                <p style="margin: 0 0 20px 0; font-size: 14px; color: #64748b;" id="dtlDeskripsi">Scheme description...</p>

                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <!-- Info Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <div>
                            <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">Salary Type</span>
                            <strong style="font-size: 14px; color: #334155;" id="dtlTipe">Monthly</strong>
                        </div>
                        <div>
                            <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">Salary Proration</span>
                            <strong style="font-size: 14px; color: #334155;" id="dtlProrate">Yes</strong>
                        </div>
                        <div>
                            <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">Absence No Salary Deduction</span>
                            <strong style="font-size: 14px; color: #334155;" id="dtlAbsenTidakPotong">No</strong>
                        </div>
                        <div>
                            <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">Absence Deduction Nominal</span>
                            <strong style="font-size: 14px; color: #dc2626;" id="dtlNominalPotongan">Rp 0</strong>
                        </div>
                    </div>

                    <!-- Components Section -->
                    <div>
                        <h5 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Allowance List</h5>
                        <div style="display: flex; flex-direction: column; gap: 8px;" id="dtlComponentsList">
                            <!-- Dynamic Components -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="padding: 15px 25px; display: flex; justify-content: flex-end; background: #f8fafc; border-top: 1px solid #e2e8f0;">
            <button type="button" onclick="document.getElementById('modalDetailSkemaPayroll').style.display='none'; document.getElementById('overlay').style.display='none';" class="btn-cancel" style="padding: 8px 20px; font-size: 14px; font-weight: 600; margin: 0;">Close</button>
        </div>
    </div>

    <!-- BPJS & Tax Scheme Detail Modal -->
    <div id="modalDetailSkemaPajak" class="modal-skema" style="width: 600px; max-width: 95%;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3>BPJS & Tax Scheme Detail</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="document.getElementById('modalDetailSkemaPajak').style.display='none'; document.getElementById('overlay').style.display='none';"></i>
        </div>
        <div class="modal-body" style="padding: 25px; max-height: 75vh; overflow-y: auto;">
            <div id="detailSkemaPajakContent">
                <h4 style="margin: 0 0 5px 0; font-size: 18px; font-weight: 700; color: #1e293b;" id="dtlPajakNama">Scheme Name</h4>
                <p style="margin: 0 0 20px 0; font-size: 14px; color: #64748b;" id="dtlPajakDeskripsi">Scheme description...</p>

                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <!-- Info Grid PPh 21 -->
                    <div style="background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Tax (PPh 21)</h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">Tax Method</span>
                                <strong style="font-size: 14px; color: #334155;" id="dtlPajakMetode">Gross</strong>
                            </div>
                            <div>
                                <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">Default PTKP Status</span>
                                <strong style="font-size: 14px; color: #334155;" id="dtlPajakPtkp">TK/0</strong>
                            </div>
                        </div>
                    </div>

                    <!-- BPJS Health -->
                    <div style="background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">BPJS Health</h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px;">
                            <div>
                                <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">Employee Portion</span>
                                <strong style="font-size: 14px; color: #334155;" id="dtlPajakBpjsKesKaryawan">1%</strong>
                            </div>
                            <div>
                                <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">Company Portion</span>
                                <strong style="font-size: 14px; color: #334155;" id="dtlPajakBpjsKesPerusahaan">4%</strong>
                            </div>
                        </div>
                        <div>
                            <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">BPJS Health Max Salary Limit</span>
                            <strong style="font-size: 14px; color: #334155;" id="dtlPajakBpjsKesMaxSalary">Rp 12.000.000</strong>
                        </div>
                    </div>

                    <!-- BPJS Employment -->
                    <div style="background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">BPJS Employment</h5>
                        <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 15px; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #e2e8f0;">
                            <div>
                                <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">JHT Employee / Company</span>
                                <strong style="font-size: 14px; color: #334155;" id="dtlPajakBpjsJht">2% / 3.7%</strong>
                            </div>
                            <div>
                                <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">JP Employee / Company</span>
                                <strong style="font-size: 14px; color: #334155;" id="dtlPajakBpjsJp">1% / 2%</strong>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px;">
                            <div>
                                <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">JKK Company</span>
                                <strong style="font-size: 14px; color: #334155;" id="dtlPajakBpjsJkk">0.24%</strong>
                            </div>
                            <div>
                                <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">JKM Company</span>
                                <strong style="font-size: 14px; color: #334155;" id="dtlPajakBpjsJkm">0.3%</strong>
                            </div>
                        </div>
                        <div>
                            <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">JP Max Salary Limit</span>
                            <strong style="font-size: 14px; color: #334155;" id="dtlPajakBpjsJpMaxSalary">Rp 10.024.600</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="padding: 15px 25px; display: flex; justify-content: flex-end; background: #f8fafc; border-top: 1px solid #e2e8f0;">
            <button type="button" onclick="document.getElementById('modalDetailSkemaPajak').style.display='none'; document.getElementById('overlay').style.display='none';" class="btn-cancel" style="padding: 8px 20px; font-size: 14px; font-weight: 600; margin: 0;">Close</button>
        </div>
    </div>

    <!-- Custom Confirm Dialog -->
    <div id="confirmOverlay" class="confirm-overlay"></div>
    <div id="confirmDialog" class="confirm-dialog">
        <div class="confirm-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h3 id="confirmTitle">Confirmation</h3>
        <p id="confirmMessage">Are you sure?</p>
        <div class="confirm-actions">
            <button id="confirmCancel" class="btn-cancel">Cancel</button>
            <button id="confirmOk" class="btn-save" style="background: var(--danger); color: white; border: none; border-radius: 8px; padding: 10px 20px; font-weight: 600; cursor: pointer;">Yes, Continue</button>
        </div>
    </div>


    <!-- Payroll Scheme Template Form Modal (Multiple Schemes per Org Structure) -->
    <div id="modalSchemeTemplate" class="modal-skema" style="width: 1200px; max-width: 95%; display: none;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="modalSchemeTemplateTitle">Add Payroll Scheme</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white;" onclick="closeSchemeTemplateModal()"></i>
        </div>
        <form id="formSchemeTemplate" onsubmit="event.preventDefault(); saveSchemeTemplate();">
            <div class="modal-body" style="padding: 25px; max-height: 70vh; overflow-y: auto;">
                
                <!-- Scheme Information & Organizational Structure -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 700; color: #1e293b;">Scheme Information</h4>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Scheme Name</label>
                        <input type="text" id="schemeTemplateNama" placeholder="Example: IT Manager Scheme" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Division</label>
                            <select id="schemeTemplateDivisionId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                                <option value="">-- All Divisions --</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Department</label>
                            <select id="schemeTemplateDepartmentId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                                <option value="">-- All Departments --</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Position</label>
                            <select id="schemeTemplatePositionId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                                <option value="">-- All Positions --</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Description</label>
                        <textarea id="schemeTemplateDeskripsi" rows="2" placeholder="Brief description of this scheme..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; resize: none;"></textarea>
                    </div>
                </div>
                
                <!-- Basic Salary -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 700; color: #1e293b;">Basic Salary</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px;">
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Salary Source</label>
                            <select id="schemeTemplateSumberGaji" onchange="handleSumberGajiChange()" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                                <option value="nominal">Custom Nominal</option>
                                <option value="ump">UMP (Province)</option>
                                <option value="umk">UMK (City/Regency)</option>
                            </select>
                        </div>
                        <div id="schemeTemplateNominalContainer" class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Basic Salary Value (IDR)</label>
                            <input type="text" id="schemeTemplateNilaiGaji" placeholder="0" onkeyup="formatRupiahInput(this)" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        </div>
                        <div id="schemeTemplateUmkContainer" class="form-group" style="margin: 0; display: none;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Select UMP/UMK</label>
                            <select id="schemeTemplateMinimumWageId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                                <option value="">-- Select UMP/UMK --</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Allowance -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 700; color: #1e293b;">Allowance</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Transport (IDR)</label>
                            <input type="text" id="schemeTunjanganTransport" placeholder="0" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; margin-bottom: 6px;">
                            <div style="display: flex; gap: 12px; font-size: 12px; color: #64748b;">
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="schemeBpjsIncTransport"> BPJS
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="schemePphIncTransport" checked> PPh 21
                                </label>
                            </div>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Meal (IDR)</label>
                            <input type="text" id="schemeTunjanganMakan" placeholder="0" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; margin-bottom: 6px;">
                            <div style="display: flex; gap: 12px; font-size: 12px; color: #64748b;">
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="schemeBpjsIncMakan"> BPJS
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="schemePphIncMakan" checked> PPh 21
                                </label>
                            </div>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Communication (IDR)</label>
                            <input type="text" id="schemeTunjanganKomunikasi" placeholder="0" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; margin-bottom: 6px;">
                            <div style="display: flex; gap: 12px; font-size: 12px; color: #64748b;">
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="schemeBpjsIncKomunikasi" checked> BPJS
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="schemePphIncKomunikasi" checked> PPh 21
                                </label>
                            </div>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Position (IDR)</label>
                            <input type="text" id="schemeTunjanganJabatan" placeholder="0" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; margin-bottom: 6px;">
                            <div style="display: flex; gap: 12px; font-size: 12px; color: #64748b;">
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="schemeBpjsIncJabatan" checked> BPJS
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="schemePphIncJabatan" checked> PPh 21
                                </label>
                            </div>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Attendance (IDR)</label>
                            <input type="text" id="schemeTunjanganKehadiran" placeholder="0" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; margin-bottom: 6px;">
                            <div style="display: flex; gap: 12px; font-size: 12px; color: #64748b;">
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="schemeBpjsIncKehadiran"> BPJS
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="schemePphIncKehadiran" checked> PPh 21
                                </label>
                            </div>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Performance (IDR)</label>
                            <input type="text" id="schemeTunjanganKinerja" placeholder="0" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; margin-bottom: 6px;">
                            <div style="display: flex; gap: 12px; font-size: 12px; color: #64748b;">
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="schemeBpjsIncKinerja"> BPJS
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="schemePphIncKinerja" checked> PPh 21
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Deduction -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 700; color: #1e293b;">Deduction</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Loan (IDR)</label>
                            <input type="text" id="schemePotonganPinjaman" placeholder="0" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Cash Advance (IDR)</label>
                            <input type="text" id="schemePotonganKasbon" placeholder="0" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Others (IDR)</label>
                            <input type="text" id="schemePotonganLainnya" placeholder="0" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        </div>
                    </div>
                </div>
                
                <!-- Attendance & Overtime -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 700; color: #1e293b;">Attendance & Overtime</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Deduction per Absence (IDR)</label>
                            <input type="text" id="schemePotonganPerAlpa" placeholder="0 = auto salary/22" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Bonus per Attendance (IDR)</label>
                            <input type="text" id="schemeBonusPerHadir" placeholder="0" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Overtime Rate/Hour (IDR)</label>
                            <input type="text" id="schemeRateLembur" placeholder="0 = auto (salary/173)x1.5" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        </div>
                    </div>

                    <!-- Grace Periods & Min Overtime -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Toleransi Terlambat (Menit)</label>
                            <input type="number" id="schemeGraceLate" min="0" value="0" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                        </div>
                        <div class="form-group" style="margin: 0; display: none;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Toleransi Early Leave (Menit)</label>
                            <input type="number" id="schemeGraceEarly" min="0" value="0" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Min. Lembur (Menit)</label>
                            <input type="number" id="schemeMinOvertime" min="0" value="30" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                        </div>
                    </div>

                    <!-- Denda -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                        <div class="form-group" style="margin: 0; grid-column: 1 / span 3;">
                            <label style="font-weight: 700; font-size: 13px; color: #1e293b; display: flex; align-items: center; gap: 6px; margin-bottom: 5px;">
                                <i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i> Konfigurasi Denda
                            </label>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 12px; color: #475569; display: block; margin-bottom: 4px;">
                                Denda Terlambat / Jam (Rp)
                                <span style="font-weight: 400; color: #94a3b8;"> — ceiling per jam (&lt; 1 jam = 1 jam)</span>
                            </label>
                            <input type="text" id="schemeDendaTerlambatPerJam" placeholder="0" onkeyup="formatRupiahInput(this)" value="0" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 12px; color: #475569; display: block; margin-bottom: 4px;">
                                Denda Alfa / Hari (Rp)
                                <span style="font-weight: 400; color: #94a3b8;"> — berlaku untuk Alfa &amp; Early Leave melebihi toleransi</span>
                            </label>
                            <input type="text" id="schemeDendaAlfaPerHari" placeholder="0" onkeyup="formatRupiahInput(this)" value="0" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                        </div>
                    </div>
                </div>
                
                <!-- BPJS -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 700; color: #1e293b;">BPJS</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: white;">
                            <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #334155;">BPJS Health (%)</h5>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: #64748b;">Employee</label>
                                    <input type="number" step="0.01" id="schemeBpjsKesKaryawan" value="1.00" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: #64748b;">Company</label>
                                    <input type="number" step="0.01" id="schemeBpjsKesPerusahaan" value="4.00" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px;">
                                </div>
                            </div>
                        </div>
                        
                        <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: white;">
                            <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #334155;">BPJS JHT (%)</h5>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: #64748b;">Employee</label>
                                    <input type="number" step="0.01" id="schemeBpjsJhtKaryawan" value="2.00" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: #64748b;">Company</label>
                                    <input type="number" step="0.01" id="schemeBpjsJhtPerusahaan" value="3.70" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px;">
                                </div>
                            </div>
                        </div>
                        
                        <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: white;">
                            <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #334155;">BPJS JP (%)</h5>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: #64748b;">Employee</label>
                                    <input type="number" step="0.01" id="schemeBpjsJpKaryawan" value="1.00" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: #64748b;">Company</label>
                                    <input type="number" step="0.01" id="schemeBpjsJpPerusahaan" value="2.00" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px;">
                                </div>
                            </div>
                        </div>
                        
                        <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: white;">
                            <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #334155;">BPJS JKK & JKM (%) - Company</h5>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: #64748b;">JKK</label>
                                    <input type="number" step="0.01" id="schemeBpjsJkkPerusahaan" value="0.24" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: #64748b;">JKM</label>
                                    <input type="number" step="0.01" id="schemeBpjsJkmPerusahaan" value="0.30" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tax -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                    <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 700; color: #1e293b;">Tax PPh 21</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Tax Method</label>
                            <select id="schemeMetodePajak" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                                <option value="Gross" selected>Gross (Pajak Ditanggung Karyawan)</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Default PTKP Status</label>
                            <select id="schemePtkpStatus" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                                <option value="TK/0">TK/0 (Single)</option>
                                <option value="K/0">K/0 (Married)</option>
                                <option value="K/1">K/1 (Married with 1 Child)</option>
                                <option value="K/2">K/2 (Married with 2 Children)</option>
                                <option value="K/3">K/3 (Married with 3 Children)</option>
                            </select>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <button type="button" class="btn-cancel" onclick="closeSchemeTemplateModal()" style="padding: 10px 24px; border-radius: 8px;">Cancel</button>
                <button type="submit" class="btn-save" style="background: #0d6efd; padding: 10px 24px; border-radius: 8px;">Save Scheme</button>
            </div>
        </form>
    </div>

    <!-- Master Payroll Schedule Modal -->
    <div id="modalSchedule" class="modal-skema" style="display: none; width: 600px; max-width: 95%;">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalScheduleTitle">Add Payroll Schedule</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalSchedule()"></i>
        </div>
        <form id="formSchedule">
            <div class="modal-body" style="padding: 20px;">
                <input type="hidden" id="scheduleId">
                <input type="hidden" id="scheduleCutoffStart" value="21">
                <input type="hidden" id="scheduleCutoffEnd" value="20">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block; color: #475569;">Title</label>
                    <input type="text" id="scheduleNama" placeholder="Example: Standard Office Schedule" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block; color: #475569;">Tanggal</label>
                    <input type="number" id="schedulePayDate" min="1" max="31" placeholder="25" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block; color: #475569;">Description</label>
                    <textarea id="scheduleDeskripsi" placeholder="Describe this schedule..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 80px; resize: vertical; font-family: inherit;"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <button type="button" class="btn-cancel" onclick="tutupModalSchedule()" style="padding: 10px 24px; border-radius: 8px;">Cancel</button>
                <button type="submit" class="btn-save" style="background: #0d6efd; padding: 10px 24px; border-radius: 8px; color: white; border: none; font-weight: 600; cursor: pointer;">Save Schedule</button>
            </div>
        </form>
    </div>

    <!-- Upload Attendance Excel Modal -->
    <div id="modalUploadAbsensi" class="modal-skema" style="display: none; width: 650px; max-width: 95%;">
        <div class="modal-header" style="background: #f39c12;">
            <h3>Upload Attendance Log</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalUploadAbsensi()"></i>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block; color: #475569;">Client</label>
                    <select id="modalUploadAbsensiClient" onchange="onAbsensiClientChanged()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="">-- Select Client --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block; color: #475569;">Bulan</label>
                    <select id="modalUploadAbsensiBulan" onchange="onAbsensiClientChanged()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
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
                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block; color: #475569;">Tahun</label>
                    <select id="modalUploadAbsensiTahun" onchange="onAbsensiClientChanged()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </select>
                </div>
            </div>

            <div id="dropzoneAbsensiExcel" ondragover="handleAbsensiDragOver(event)" ondragleave="handleAbsensiDragLeave(event)" ondrop="handleAbsensiDrop(event)" style="background: rgba(243, 156, 18, 0.08); border: 1px dashed #f39c12; padding: 15px; border-radius: 8px; margin-bottom: 15px; text-align: center; transition: all 0.2s ease;">
                <i class="fas fa-file-excel" style="font-size: 36px; color: #f39c12; margin-bottom: 10px; display: block;"></i>
                <span style="font-size: 14px; font-weight: 600; color: #2c3e50; display: block; margin-bottom: 5px;">Select Excel Attendance File</span>
                <span style="font-size: 12px; color: #64748b; display: block; margin-bottom: 12px;">Required columns: Employee ID, Nama, Tgl dan Hari, Jam Masuk, Jam Keluar, Status</span>
                
                <div style="display: flex; justify-content: center; gap: 10px; align-items: center; margin-bottom: 10px;">
                    <input type="file" id="fileAbsensiExcel" accept=".xlsx, .xls" style="display: none;" onchange="handleAbsensiFileSelect(event)">
                    <button type="button" class="btn-add" onclick="document.getElementById('fileAbsensiExcel').click()" style="background: #f39c12; padding: 8px 20px; font-weight: 600;">
                        Choose File
                    </button>
                    <button type="button" class="btn-cancel" onclick="downloadAbsensiTemplate()" style="padding: 8px 16px; border: 1px solid #cbd5e0; background: white; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-download"></i> Download Template
                    </button>
                </div>
                <span id="labelAbsensiFilename" style="font-size: 13px; font-weight: 600; color: #f39c12; display: block; margin-top: 5px;">No file chosen</span>
            </div>

            <div style="margin-bottom: 15px; display: none;">
                <label style="font-weight: 600; margin-bottom: 6px; display: block; color: #475569;">Parsing & Calculation Summary</label>
                <div id="uploadAbsensiLogs" style="background: #1e293b; color: #38bdf8; font-family: monospace; font-size: 12px; padding: 12px; border-radius: 8px; height: 180px; overflow-y: auto; white-space: pre-wrap; line-height: 1.5;">
                    Waiting for file...
                </div>
            </div>
        </div>
        <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            <button type="button" class="btn-cancel" onclick="tutupModalUploadAbsensi()" style="padding: 10px 24px; border-radius: 8px;">Close</button>
            <button type="button" id="btnSaveUploadedAbsensi" disabled onclick="saveUploadedAbsensi()" style="background: #f39c12; padding: 10px 24px; border-radius: 8px; color: white; border: none; font-weight: 600; cursor: not-allowed; opacity: 0.5;">Apply & Save Attendance</button>
        </div>
    </div>

    <!-- Upload Manual Salary Excel Modal -->
    <div id="modalUploadManualSalary" class="modal-skema" style="display: none; width: 650px; max-width: 95%; z-index: 2010;">
        <div class="modal-header" style="background: linear-gradient(135deg, #2980b9 0%, #1f618d 100%);">
            <h3>Upload Gaji Manual</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalUploadManualSalary()"></i>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div id="dropzoneManualSalaryExcel" ondragover="handleManualSalaryDragOver(event)" ondragleave="handleManualSalaryDragLeave(event)" ondrop="handleManualSalaryDrop(event)" style="background: rgba(41, 128, 185, 0.08); border: 1px dashed #2980b9; padding: 25px 15px; border-radius: 8px; margin-bottom: 15px; text-align: center; transition: all 0.2s ease;">
                <i class="fas fa-file-excel" style="font-size: 48px; color: #2980b9; margin-bottom: 15px; display: block;"></i>
                <span style="font-size: 16px; font-weight: 700; color: #2c3e50; display: block; margin-bottom: 5px;">Select Excel Manual Salary File</span>
                <span style="font-size: 12px; color: #64748b; display: block; margin-bottom: 20px;">Required columns: PKWT ID, Employee Name, Employee ID (NIK), Working Days, Overtime Hours, Early Arrival Hours, Basic Salary (Gaji Pokok), Rapel, Bonus / Lainnya, Absence Deduction (Potongan Absen), Potongan Lain</span>
                
                <div style="display: flex; justify-content: center; gap: 10px; align-items: center; margin-bottom: 15px;">
                    <input type="file" id="fileManualSalaryExcel" accept=".xlsx, .xls" style="display: none;" onchange="handleManualSalaryFileSelect(event)">
                    <button type="button" class="btn-save" onclick="document.getElementById('fileManualSalaryExcel').click()" style="background: #2980b9; padding: 10px 24px; font-weight: 600; border-radius: 8px; border: none; color: white; cursor: pointer;">
                        Choose File
                    </button>
                    <button type="button" class="btn-cancel" onclick="downloadSalaryTemplate()" style="padding: 10px 20px; border: 1px solid #cbd5e0; background: white; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 6px; border-radius: 8px;">
                        <i class="fas fa-download"></i> Download Template
                    </button>
                </div>
                <span id="labelManualSalaryFilename" style="font-size: 13px; font-weight: 600; color: #2980b9; display: block; margin-top: 5px;">No file chosen</span>
            </div>
        </div>
        <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: right; gap: 8px;">
            <button type="button" class="btn-cancel" onclick="tutupModalUploadManualSalary()" style="padding: 10px 24px; border-radius: 8px;">Close</button>
            <button type="button" id="btnSaveManualSalary" disabled onclick="saveManualSalary()" style="background: #2980b9; padding: 10px 24px; border-radius: 8px; color: white; border: none; font-weight: 600; cursor: not-allowed; opacity: 0.5; transition: all 0.2s;">Apply & Save Salary</button>
        </div>
    </div>

    <!-- Upload Overtime Excel Modal -->
    <div id="modalUploadLembur" class="modal-skema" style="display: none; width: 650px; max-width: 95%;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3>Upload Lembur Log</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalUploadLembur()"></i>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block; color: #475569;">Client</label>
                    <select id="modalUploadLemburClient" onchange="onLemburClientChanged()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="">-- Select Client --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block; color: #475569;">Period</label>
                    <select id="modalUploadLemburPeriod" onchange="onLemburPeriodChanged()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;" disabled>
                        <option value="">-- Select Client First --</option>
                    </select>
                </div>
            </div>

            <div id="dropzoneLemburExcel" ondragover="handleLemburDragOver(event)" ondragleave="handleLemburDragLeave(event)" ondrop="handleLemburDrop(event)" style="background: rgba(243, 156, 18, 0.08); border: 1px dashed var(--primary-color); padding: 15px; border-radius: 8px; margin-bottom: 15px; text-align: center; transition: all 0.2s ease;">
                <i class="fas fa-file-excel" style="font-size: 36px; color: var(--primary-color); margin-bottom: 10px; display: block;"></i>
                <span id="dropzoneLemburText1" style="font-size: 14px; font-weight: 600; color: #2c3e50; display: block; margin-bottom: 5px;">Pilih File Excel Lembur</span>
                <span id="dropzoneLemburText2" style="font-size: 12px; color: #64748b; display: block; margin-bottom: 12px;">Kolom wajib: NIK, Nama, Tanggal (YYYY-MM-DD), Nominal</span>
                
                <div style="display: flex; justify-content: center; gap: 10px; align-items: center; margin-bottom: 10px;">
                    <input type="file" id="fileLemburExcel" accept=".xlsx, .xls" style="display: none;" onchange="handleLemburFileSelect(event)">
                    <button type="button" class="btn-add" onclick="document.getElementById('fileLemburExcel').click()" style="background: var(--primary-color); padding: 8px 20px; font-weight: 600;">
                        Choose File
                    </button>
                    <button type="button" class="btn-cancel" onclick="downloadLemburTemplate()" style="padding: 8px 16px; border: 1px solid #cbd5e0; background: white; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-download"></i> Download Template
                    </button>
                </div>
                <span id="labelLemburFilename" style="font-size: 13px; font-weight: 600; color: var(--primary-color); display: block; margin-top: 5px;">No file selected</span>
            </div>

            <div style="margin-bottom: 15px; display: none;">
                <label style="font-weight: 600; margin-bottom: 6px; display: block; color: #475569;">Parsing & Calculation Summary</label>
                <div id="uploadLemburLogs" style="background: #1e293b; color: #38bdf8; font-family: monospace; font-size: 12px; padding: 12px; border-radius: 8px; height: 180px; overflow-y: auto; white-space: pre-wrap; line-height: 1.5;">
                    Waiting for file...
                </div>
            </div>
        </div>
        <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            <button type="button" class="btn-cancel" onclick="tutupModalUploadLembur()" style="padding: 10px 24px; border-radius: 8px;">Close</button>
            <button type="button" id="btnSaveUploadedLembur" disabled onclick="saveUploadedLembur()" style="background: var(--primary-color); padding: 10px 24px; border-radius: 8px; color: white; border: none; font-weight: 600; cursor: not-allowed; opacity: 0.5;">Apply & Save Overtime</button>
        </div>
    </div>

    <!-- Holiday Modal -->
    <div id="holidayModal" class="modal-skema" style="width: 480px; max-width: 95%; display: none; z-index: 2000;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="holidayModalTitle">Add Holiday</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white;" onclick="closeModal('holidayModal')"></i>
        </div>
        <div class="modal-body" style="padding: 25px;">
            <form id="holidayForm" onsubmit="simpanHoliday(event)">
                <input type="hidden" id="holidayId">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Tanggal <span style="color: #ef4444;">*</span></label>
                    <input type="date" id="holidayTanggal" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                </div>
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Deskripsi <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="holidayDeskripsi" placeholder="Contoh: Hari Raya Idul Fitri" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                    <button type="button" class="btn-cancel" onclick="closeModal('holidayModal')" style="padding: 10px 20px; border-radius: 8px;">Cancel</button>
                    <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Modal -->
    <div id="attendanceModal" class="modal-skema" style="width: 520px; max-width: 95%; display: none; z-index: 2000;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="attendanceModalTitle">Input Kehadiran</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white;" onclick="closeModal('attendanceModal')"></i>
        </div>
        <div class="modal-body" style="padding: 25px;">
            <form id="attendanceForm" onsubmit="simpanAttendance(event)">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Karyawan <span style="color: #ef4444;">*</span></label>
                    <select id="attendanceEmployeeSelect" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                        <option value="">-- Select Employee --</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Tanggal <span style="color: #ef4444;">*</span></label>
                    <input type="date" id="attendanceTanggal" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                </div>
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Status</label>
                    <select id="attendanceStatus" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                        <option value="Hadir">Hadir</option>
                        <option value="Absen">Absen</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Izin">Izin</option>
                        <option value="Cuti">Cuti</option>
                    </select>
                </div>
                <div style="display: flex; gap: 12px; margin-bottom: 18px;">
                    <div style="flex: 1;">
                        <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Clock In</label>
                        <input type="time" id="attendanceJamMasuk" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Clock Out</label>
                        <input type="time" id="attendanceJamKeluar" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Description</label>
                    <input type="text" id="attendanceKeterangan" placeholder="Opsional" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                    <button type="button" class="btn-cancel" onclick="closeModal('attendanceModal')" style="padding: 10px 20px; border-radius: 8px;">Cancel</button>
                    <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Overtime Modal -->
    <div id="overtimeModal" class="modal-skema" style="width: 520px; max-width: 95%; display: none; z-index: 2000;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="overtimeModalTitle">Input Lembur</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white;" onclick="closeModal('overtimeModal')"></i>
        </div>
        <div class="modal-body" style="padding: 25px;">
            <form id="overtimeForm" onsubmit="simpanOvertime(event)">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Karyawan <span style="color: #ef4444;">*</span></label>
                    <select id="overtimeEmployeeSelect" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                        <option value="">-- Select Employee --</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Tanggal <span style="color: #ef4444;">*</span></label>
                    <input type="date" id="overtimeTanggal" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                </div>
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Jam Lembur <span style="color: #ef4444;">*</span></label>
                    <input type="number" id="overtimeJamLembur" step="0.5" min="0.5" max="12" placeholder="Contoh: 2" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                    <small style="color: #94a3b8; font-size: 12px;">Hari kerja: maks 3 jam. Hari libur: unlimited.</small>
                </div>
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" id="overtimeIsHoliday" style="width: 18px; height: 18px; accent-color: var(--primary-color);">
                        <span style="font-weight: 600; color: #334155; font-size: 14px;">Hari Libur / Weekend</span>
                    </label>
                    <small style="color: #94a3b8; font-size: 12px; margin-left: 28px;">Otomatis terdeteksi dari Holiday Calendar & weekend.</small>
                </div>
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Description</label>
                    <input type="text" id="overtimeKeterangan" placeholder="Opsional" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                    <button type="button" class="btn-cancel" onclick="closeModal('overtimeModal')" style="padding: 10px 20px; border-radius: 8px;">Cancel</button>
                    <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Tambah/Edit Master Skema Shift -->
    <div id="modalShiftScheme" class="modal-skema" style="width: 550px; max-width: 95%; display: none; z-index: 2000;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="modalShiftSchemeTitle">Tambah Skema Shift</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white;" onclick="tutupModalShiftScheme()"></i>
        </div>
        <div class="modal-body" style="padding: 25px;">
            <form id="formShiftScheme" onsubmit="simpanShiftScheme(event)">
                <input type="hidden" id="shiftSchemeId">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Nama Shift <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="shiftSchemeName" placeholder="Contoh: Shift Pagi, Shift Malam, Lembur Khusus" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                </div>
                <div style="display: flex; gap: 12px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Jam Mulai <span style="color: #ef4444;">*</span></label>
                        <input type="time" id="shiftSchemeStartTime" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Jam Selesai <span style="color: #ef4444;">*</span></label>
                        <input type="time" id="shiftSchemeEndTime" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                    </div>
                </div>
                <!-- Hidden fields to maintain JavaScript compatibility without displaying in the form -->
                <div style="display: none;">
                    <input type="number" id="shiftSchemeDuration" value="8">
                    <input type="number" id="shiftSchemeGraceLate" value="0">
                    <input type="number" id="shiftSchemeGraceEarly" value="0">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" class="btn-cancel" onclick="tutupModalShiftScheme()" style="padding: 10px 20px; border-radius: 8px;">Cancel</button>
                    <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Alokasi Shift Karyawan -->
    <div id="modalAssignShift" class="modal-skema" style="width: 500px; max-width: 95%; display: none; z-index: 2000;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="modalAssignShiftTitle">Assign Employee Shift</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white;" onclick="tutupModalAssignShift()"></i>
        </div>
        <div class="modal-body" style="padding: 25px;">
            <form id="formAssignShift" onsubmit="simpanAssignShift(event)">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Pilih Karyawan <span style="color: #ef4444;">*</span></label>
                    <select id="assignShiftEmployeeId" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none; background: white;">
                        <option value="">-- Select Employee --</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Pilih Skema Shift <span style="color: #ef4444;">*</span></label>
                    <select id="assignShiftSchemeId" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none; background: white;">
                        <option value="">-- Pilih Skema Shift --</option>
                    </select>
                </div>
                <div style="display: flex; gap: 12px; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Tanggal Mulai <span style="color: #ef4444;">*</span></label>
                        <input type="date" id="assignShiftStartDate" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Tanggal Selesai (Opsional)</label>
                        <input type="date" id="assignShiftEndDate" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" class="btn-cancel" onclick="tutupModalAssignShift()" style="padding: 10px 20px; border-radius: 8px;">Cancel</button>
                    <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Tugaskan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hitung Kompensasi Kontrak -->
    <div id="modalHitungKompensasi" class="modal-skema" style="width: 550px; max-width: 95%; display: none; z-index: 2000; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); padding: 15px 20px; color: white; border-top-left-radius: 16px; border-top-right-radius: 16px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600;"><i class="fas fa-calculator" style="margin-right: 8px;"></i>Hitung Kompensasi Kontrak</h3>
            <i class="fas fa-times" style="cursor: pointer; font-size: 18px;" onclick="tutupModalHitungKompensasi()"></i>
        </div>
        <form id="formHitungKompensasi" onsubmit="hitungKompensasiKontrak(event)">
            <div class="modal-body" style="padding: 20px;">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Karyawan PKWT <span style="color: #ef4444;">*</span></label>
                    <select id="hkEmployeeId" required onchange="onEmployeeChangeKompensasi()" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none; background: white;">
                        <option value="">-- Pilih Karyawan PKWT --</option>
                    </select>
                </div>
                <div style="display: flex; gap: 12px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Tanggal Mulai <span style="color: #ef4444;">*</span></label>
                        <input type="date" id="hkStartDate" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Tanggal Selesai <span style="color: #ef4444;">*</span></label>
                        <input type="date" id="hkEndDate" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                    </div>
                </div>
                <div style="display: flex; gap: 12px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Basic Salary <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="hkBasicSalary" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Hari Kerja Aktual (Opsional)</label>
                        <input type="number" id="hkActualDays" placeholder="Jika kontrak < 1 bulan" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                    </div>
                </div>
                
                <div id="hkPreviewContainer" style="display: none; background: #f8fafc; border: 1px dashed var(--primary-color); border-radius: 8px; padding: 15px; margin-bottom: 20px; font-size: 13px;">
                    <h4 style="margin: 0 0 10px 0; color: var(--secondary-color); font-weight: 600;">Preview Perhitungan:</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                        <div>Masa Kerja: <strong id="hkPreviewMasaKerja">-</strong></div>
                        <div>Multiplier: <strong id="hkPreviewMultiplier">-</strong></div>
                    </div>
                    <div style="border-top: 1px solid #e2e8f0; padding-top: 10px; font-size: 14px;">
                        Estimasi Kompensasi: <strong id="hkPreviewNilai" style="color: var(--primary-color); font-size: 16px;">-</strong>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" class="btn-cancel" onclick="tutupModalHitungKompensasi()" style="padding: 10px 20px; border-radius: 8px;">Cancel</button>
                    <button type="button" onclick="previewKompensasiKontrak()" style="background: #64748b; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer;">Preview</button>
                    <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Simpan Draft</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Tetapkan Nilai Kompensasi -->
    <div id="modalTetapkanKompensasi" class="modal-skema" style="width: 500px; max-width: 95%; display: none; z-index: 2000; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 15px 20px; color: white; border-top-left-radius: 16px; border-top-right-radius: 16px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600;"><i class="fas fa-check-double" style="margin-right: 8px;"></i>Tetapkan Nilai Kompensasi (HCOPS)</h3>
            <i class="fas fa-times" style="cursor: pointer; font-size: 18px;" onclick="tutupModalTetapkanKompensasi()"></i>
        </div>
        <form id="formTetapkanKompensasi" onsubmit="tetapkanKompensasiKontrak(event)">
            <div class="modal-body" style="padding: 20px;">
                <input type="hidden" id="tkId">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Nilai Kalkulasi Sistem</label>
                    <input type="text" id="tkKalkulasiSistem" disabled style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none; background: #f1f5f9;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Nilai Final Kompensasi <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="tkNilaiFinal" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Catatan Penyesuaian</label>
                    <textarea id="tkCatatan" placeholder="Tambahkan alasan jika nilai final berbeda dengan kalkulasi sistem" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; height: 80px; outline: none; resize: none;"></textarea>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" class="btn-cancel" onclick="tutupModalTetapkanKompensasi()" style="padding: 10px 20px; border-radius: 8px;">Cancel</button>
                    <button type="submit" style="background: #f59e0b; color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Tetapkan & Kirim</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Detail Kompensasi Kontrak -->
    <div id="modalDetailKompensasi" class="modal-skema" style="width: 600px; max-width: 95%; display: none; z-index: 2000; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #1e293b 100%); padding: 15px 20px; color: white; border-top-left-radius: 16px; border-top-right-radius: 16px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600;"><i class="fas fa-file-invoice-dollar" style="margin-right: 8px;"></i>Detail Kompensasi Kontrak</h3>
            <i class="fas fa-times" style="cursor: pointer; font-size: 18px;" onclick="tutupModalDetailKompensasi()"></i>
        </div>
        <div class="modal-body" style="padding: 20px; font-size: 14px;">
            <input type="hidden" id="dkId">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="color: #64748b; font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">NAMA KARYAWAN</label>
                    <strong id="dkNamaKaryawan" style="color: #334155; font-size: 15px;">-</strong>
                </div>
                <div>
                    <label style="color: #64748b; font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">NIK</label>
                    <strong id="dkNik" style="color: #334155; font-size: 15px;">-</strong>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                <div>
                    <label style="color: #64748b; font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">TANGGAL MULAI</label>
                    <span id="dkTglMulai">-</span>
                </div>
                <div>
                    <label style="color: #64748b; font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">TANGGAL BERAKHIR</label>
                    <span id="dkTglAkhir">-</span>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                <div>
                    <label style="color: #64748b; font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">MASA KERJA AKTUAL</label>
                    <span id="dkMasaKerja">-</span>
                </div>
                <div>
                    <label style="color: #64748b; font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">GAJI POKOK (BASIC)</label>
                    <span id="dkGajiPokok">-</span>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                <div>
                    <label style="color: #64748b; font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">MULTIPLIER</label>
                    <span id="dkMultiplier">-</span>
                </div>
                <div>
                    <label style="color: #64748b; font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">STATUS</label>
                    <span id="dkStatusBadge">-</span>
                </div>
            </div>
            
            <div style="background: #f8fafc; border-radius: 8px; padding: 15px; margin-top: 15px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Kompensasi Kalkulasi:</span>
                        <strong id="dkKompensasiSistem" style="color: #334155;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 1px solid #e2e8f0; padding-top: 10px; font-size: 15px;">
                        <span>Nilai Final Kompensasi:</span>
                        <strong id="dkKompensasiFinal" style="color: var(--primary-color); font-size: 16px;">-</strong>
                    </div>
                </div>
            </div>

            <div id="dkCatatanBox" style="display: none; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 12px; margin-bottom: 20px; font-size: 13px;">
                <label style="color: #b45309; font-weight: 600; display: block; margin-bottom: 4px;">CATATAN / OVERRIDE REASON</label>
                <span id="dkCatatan" style="color: #78350f;">-</span>
            </div>

            <div id="dkWorkflowBox" style="display: none; font-size: 12px; color: #64748b; margin-bottom: 20px; border-top: 1px solid #f1f5f9; padding-top: 10px;">
                <div id="dkFlowDitetapkan" style="display: none;">Ditetapkan oleh: <strong id="dkDitetapkanOleh">-</strong> pada <span id="dkDitetapkanPada">-</span></div>
                <div id="dkFlowDisetujui" style="display: none; margin-top: 4px;">Disetujui oleh: <strong id="dkDisetujuiOleh">-</strong> pada <span id="dkDisetujuiPada">-</span></div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn-cancel" onclick="tutupModalDetailKompensasi()" style="padding: 10px 20px; border-radius: 8px; margin: 0;">Close</button>
                <?php if (($_COOKIE['user_role'] ?? '') === 'client_superior' || ($_COOKIE['user_role'] ?? '') === 'admin'): ?>
                <button type="button" id="btnRejectKompensasi" onclick="tolakKompensasiKontrakWorkflow()" style="background: #ef4444; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: none;">Reject</button>
                <button type="button" id="btnApproveKompensasi" onclick="setujuiKompensasiKontrakWorkflow()" style="background: #22c55e; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: none;">Approve</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal: Tambah/Edit FPK Master -->
    <div id="modalFpkMaster" class="modal-skema" style="display: none; position: fixed; z-index: 1050; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 450px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); box-sizing: border-box; overflow: hidden;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
            <h3 id="fpkMasterTitle" style="margin: 0; font-size: 16px; font-weight: 700; color: white;">Tambah FPK Baru</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white; font-size: 18px;" onclick="tutupModalFpkMaster()"></i>
        </div>
        <div class="modal-body" style="padding: 20px 25px; box-sizing: border-box;">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #475569; font-size: 13px;">Nomor FPK <span style="color:#ef4444;">*</span></label>
                <input type="text" id="fpkNomor" placeholder="Contoh: FPK/2026/001" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #475569; font-size: 13px;">Nama FPK <span style="color:#ef4444;">*</span></label>
                <input type="text" id="fpkNama" placeholder="Contoh: Rekrutmen Staff IT" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #475569; font-size: 13px;">Provinsi FPK <span style="color:#ef4444;">*</span></label>
                <input type="text" id="fpkProvinsi" placeholder="Contoh: Jawa Barat" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #475569; font-size: 13px;">Kota / Kabupaten <span style="color:#ef4444;">*</span></label>
                <input type="text" id="fpkCity" placeholder="Contoh: Bandung" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-cancel" onclick="tutupModalFpkMaster()" style="padding: 10px 18px; border-radius: 8px; border: 1px solid #cbd5e1; background: #f8fafc; cursor: pointer; font-size: 14px; font-weight: 600;">Batal</button>
                <button type="button" onclick="saveFpkMaster()" style="padding: 10px 22px; border-radius: 8px; background: var(--primary-color); color: white; border: none; cursor: pointer; font-size: 14px; font-weight: 600;">Simpan</button>
            </div>
        </div>
    </div>

    <!-- Modal: Penempelan Karyawan ke FPK -->
    <div id="modalFpkAssign" class="modal-skema" style="display: none; position: fixed; z-index: 1050; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 500px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); box-sizing: border-box; overflow: hidden;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: white;">Penempelan Karyawan ke FPK</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white; font-size: 18px;" onclick="tutupModalFpkAssign()"></i>
        </div>
        <div class="modal-body" style="padding: 20px 25px; box-sizing: border-box;">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #475569; font-size: 13px;">Filter Client <span style="color:#ef4444;">*</span></label>
                <select id="fpkAssignClient" style="width: 100%;">
                    <option value="">-- Pilih Client --</option>
                </select>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #475569; font-size: 13px;">Pilih Karyawan <span style="color:#ef4444;">*</span></label>
                <select id="fpkAssignEmployee" style="width: 100%;">
                    <option value="">-- Pilih Karyawan (Pilih Client terlebih dahulu) --</option>
                </select>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #475569; font-size: 13px;">Pilih FPK (Status: Open) <span style="color:#ef4444;">*</span></label>
                <select id="fpkAssignFpk" style="width: 100%;">
                    <option value="">-- Pilih FPK --</option>
                </select>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #475569; font-size: 13px;">Provinsi FPK</label>
                <input type="text" id="fpkAssignProvinsi" readonly style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; background: #f1f5f9; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box; color: #64748b;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #475569; font-size: 13px;">Kota / Kabupaten FPK</label>
                <input type="text" id="fpkAssignCity" readonly style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; background: #f1f5f9; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box; color: #64748b;">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-cancel" onclick="tutupModalFpkAssign()" style="padding: 10px 18px; border-radius: 8px; border: 1px solid #cbd5e1; background: #f8fafc; cursor: pointer; font-size: 14px; font-weight: 600;">Batal</button>
                <button type="button" id="btnSubmitFpkAssign" onclick="submitFpkAssignment()" style="padding: 10px 22px; border-radius: 8px; background: var(--primary-color); color: white; border: none; cursor: pointer; font-size: 14px; font-weight: 600;"><i class="fas fa-check"></i> Submit</button>
            </div>
        </div>
    </div>

    <!-- Modal KPI Report Detail (Total THP, Total Headcount, Rata-Rata Gaji, Tren MoM) -->
    <div id="modalReportKpiDetail" class="modal-skema" style="display: none; width: 920px; max-width: 95%; z-index: 2010; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; display: flex; justify-content: space-between; align-items: center; padding: 18px 24px;">
            <h3 id="modalKpiDetailTitle" style="margin: 0; font-size: 18px; font-weight: 700; color: white; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-chart-pie" id="modalKpiDetailIcon" style="color: white;"></i> Detail Metrik Summary Gaji
            </h3>
            <i class="fas fa-times" style="cursor: pointer; color: white; font-size: 18px; opacity: 0.9; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'" onclick="tutupModalKpiDetail()"></i>
        </div>
        <div class="modal-body" style="padding: 24px; max-height: 75vh; overflow-y: auto; background: #ffffff;">
            <div id="modalKpiDetailSummary" style="margin-bottom: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
                <!-- Dynamically populated by JS -->
            </div>

            <div class="table-container" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: white;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead id="modalKpiDetailThead">
                        <!-- Dynamically populated by JS -->
                    </thead>
                    <tbody id="modalKpiDetailTbody">
                        <!-- Dynamically populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer" style="padding: 16px 24px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
            <button type="button" class="btn-cancel" onclick="tutupModalKpiDetail()" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; background: #cbd5e1; color: #334155; border: none; font-size: 13px;">Tutup</button>
        </div>
    </div>





