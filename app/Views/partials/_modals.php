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

    <!-- Modal Detail Perhitungan BPJS -->
    <div id="modalDetailBpjs" class="modal-skema" style="display: none; width: 750px; max-width: 95%; z-index: 2005;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: white;">Rincian Perhitungan BPJS</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white;" onclick="tutupDetailBpjsModal()"></i>
        </div>
        <div class="modal-body" style="padding: 25px; max-height: 70vh; overflow-y: auto;">
            <div style="margin-bottom: 20px; font-size: 14px; color: #475569; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div><strong>Nama Karyawan:</strong> <span id="bpjsModalEmployeeName" style="color: #1e293b; font-weight: 600;">-</span></div>
                <div style="text-align: right;"><strong>Periode:</strong> <span id="bpjsModalPeriod" style="color: #1e293b; font-weight: 600;">-</span></div>
            </div>
            <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                    <thead>
                        <tr style="background: #e2e8f0; border-bottom: 2px solid #cbd5e1; color: #475569; font-weight: 700;">
                            <th style="padding: 12px 10px;">Program BPJS</th>
                            <th style="padding: 12px 10px; text-align: right;">Upah Basis</th>
                            <th style="padding: 12px 10px; text-align: right;">Beban Karyawan</th>
                            <th style="padding: 12px 10px; text-align: right;">Beban Perusahaan</th>
                            <th style="padding: 12px 10px; text-align: right;">Total Iuran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px 10px; font-weight: 600; color: #1e293b;">BPJS Kesehatan</td>
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
                            <td style="padding: 12px 10px;">Total Keseluruhan</td>
                            <td style="padding: 12px 10px; text-align: right;">-</td>
                            <td style="padding: 12px 10px; text-align: right; color: #ef4444; font-variant-numeric: tabular-nums;" id="bpjsGrandEmp">Rp 0</td>
                            <td style="padding: 12px 10px; text-align: right; color: #3b82f6; font-variant-numeric: tabular-nums;" id="bpjsGrandCo">Rp 0</td>
                            <td style="padding: 12px 10px; text-align: right; color: #10b981; font-variant-numeric: tabular-nums;" id="bpjsGrandTotal">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 15px; font-size: 12px; color: #1e40af; line-height: 1.6;">
                <h5 style="margin: 0 0 8px 0; font-weight: 700; font-size: 13px;"><i class="fas fa-info-circle"></i> Catatan Peraturan BPJS:</h5>
                <ul style="margin: 0; padding-left: 18px;">
                    <li><strong>BPJS Kesehatan:</strong> Batas upah maksimum Rp 12.000.000 (total iuran 5%: 4% perusahaan, 1% karyawan).</li>
                    <li><strong>BPJS Ketenagakerjaan (JP):</strong> Batas upah maksimum Rp 10.024.600 (total iuran 3%: 2% perusahaan, 1% karyawan).</li>
                    <li><strong>BPJS Ketenagakerjaan (JKK & JKM):</strong> 100% ditanggung oleh perusahaan (JKK 0.24%, JKM 0.30% dari upah basis).</li>
                    <li>Beban BPJS Perusahaan bersifat sebagai informasi tambahan dan tidak memotong upah bersih (Take Home Pay) karyawan.</li>
                </ul>
            </div>
        </div>
        <div class="modal-footer" style="padding: 15px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <button type="button" class="btn-cancel" onclick="tutupDetailBpjsModal()" style="margin: 0; padding: 10px 20px;">Tutup</button>
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
                            
                            <!-- Grace Periods & Min Overtime -->
                            <div style="display: flex; gap: 12px; margin-top: 5px;">
                                <div style="flex: 1;">
                                    <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Toleransi Terlambat (Menit)</label>
                                    <input type="number" id="skemaGraceLate" min="0" value="0" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                                </div>
                                <div style="flex: 1; display: none;">
                                    <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Toleransi Early Leave (Menit)</label>
                                    <input type="number" id="skemaGraceEarly" min="0" value="0" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                                </div>
                                <div style="flex: 1;">
                                    <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Min. Lembur (Menit)</label>
                                    <input type="number" id="skemaMinOvertime" min="0" value="30" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                                </div>
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

                        <!-- Description -->
                        <div class="form-group" style="margin: 0; flex-grow: 1; display: flex; flex-direction: column;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Description / Notes</label>
                            <textarea id="skemaDeskripsi" rows="8" placeholder="Enter a brief description of the payroll scheme or additional notes here..." style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; outline: none; font-size: 14px; resize: none; font-family: inherit; flex-grow: 1; min-height: 180px;"></textarea>
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
                        <option value="bpjs_kesehatan">BPJS Kesehatan</option>
                        <option value="bpjs_ketenagakerjaan">BPJS Ketenagakerjaan</option>
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

                <!-- BPJS Kesehatan -->
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: #f8fafc;">
                    <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #334155;"><i class="fas fa-hand-holding-medical" style="color: var(--primary-color);"></i> BPJS Kesehatan</h5>
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
                        <label style="font-size: 11px; font-weight: 600; color: #64748b;">BPJS Kesehatan Max Salary Limit (IDR)</label>
                        <input type="text" id="bpjsKesMaxSalary" value="12.000.000" onkeyup="formatRupiahInput(this)" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; height: 36px;">
                    </div>
                </div>

                <!-- BPJS Ketenagakerjaan (JHT & JP) -->
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: #f8fafc;">
                    <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #334155;"><i class="fas fa-shield-alt" style="color: var(--info);"></i> BPJS Ketenagakerjaan</h5>
                    
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
    <div id="modalPph21" class="modal-skema" style="width: 500px; max-width: 95%;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="modalPph21Title">Add PPh 21 Scheme</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalPph21()"></i>
        </div>
        <form id="formPph21">
            <div class="modal-body" style="padding: 25px; display: flex; flex-direction: column; gap: 15px;">
                <input type="hidden" id="pph21Id">
                <input type="hidden" id="pph21Tipe" value="pph21">
                
                <div class="form-group" style="margin: 0;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">PPh 21 Scheme Name</label>
                    <input type="text" id="pph21Nama" placeholder="Example: Standard Tax Scheme" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Tax Method</label>
                    <select id="pph21Metode" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white; height: 42px;">
                        <option value="Gross">Gross (Tax borne by Employee)</option>
                        <option value="Gross Up">Gross Up (Tax Allowance)</option>
                        <option value="Nett">Nett (Tax borne by Company)</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Default PTKP Status</label>
                    <select id="pph21Ptkp" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white; height: 42px;">
                        <option value="TK/0">TK/0 (Single)</option>
                        <option value="K/0">K/0 (Married)</option>
                        <option value="K/1">K/1 (Married with 1 Child)</option>
                        <option value="K/2">K/2 (Married with 2 Children)</option>
                        <option value="K/3">K/3 (Married with 3 Children)</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Description / Notes</label>
                    <textarea id="pph21Deskripsi" rows="4" placeholder="Enter brief description..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; resize: none;"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <button type="button" class="btn-cancel" onclick="tutupModalPph21()">Cancel</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Save</button>
            </div>
        </form>
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
                    <div style="display: flex; flex-direction: column; gap: 12px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; background: #f8fafc; margin-top: -5px;">
                        <h4 style="margin: 0 0 2px 0; font-size: 14px; font-weight: 700; color: #1e293b;"><i class="fas fa-calendar-alt" style="color: var(--primary-color); margin-right: 6px;"></i>Cut Off & Pay Day</h4>
                        <p style="margin: 0; font-size: 12px; color: #64748b;">
                            Konfigurasi tanggal cut off dan tanggal gajian untuk skema ini. Wajib diisi.
                        </p>

                        <div style="display: flex; gap: 24px; align-items: flex-start;">
                            <!-- Cut Off -->
                            <div style="flex: 1;">
                                <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Tanggal Cut Off</label>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="number" id="modalPilihanSkemaCutoffStart" min="1" max="30" placeholder="-" required style="width: 60px; height: 42px; text-align: center; font-size: 16px; font-weight: 700; border: 1px solid #ddd; border-radius: 8px; outline: none; color: #1e293b; background: white; -moz-appearance: textfield;" oninput="if(this.value>30)this.value=30;if(this.value<1&&this.value!=='')this.value=1;">
                                    <small style="color: #94a3b8; font-size: 11px; line-height: 1.3;"><i class="fas fa-info-circle" style="margin-right: 3px;"></i>Tanggal mulai perhitungan<br>periode payroll (rapel).</small>
                                </div>
                            </div>

                            <!-- Pay Day -->
                            <div style="flex: 1;">
                                <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;"><i class="fas fa-money-check-alt" style="color: #16a34a; margin-right: 4px;"></i>Pay Day / Tanggal Gajian</label>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="number" id="modalPilihanSkemaPayDate" min="1" max="30" placeholder="-" required style="width: 60px; height: 42px; text-align: center; font-size: 16px; font-weight: 700; border: 1px solid #ddd; border-radius: 8px; outline: none; color: #1e293b; background: white; -moz-appearance: textfield;" oninput="if(this.value>30)this.value=30;if(this.value<1&&this.value!=='')this.value=1;">
                                    <small style="color: #94a3b8; font-size: 11px; line-height: 1.3;"><i class="fas fa-info-circle" style="margin-right: 3px;"></i>Tanggal pembayaran gaji<br>setiap bulan.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BPJS Scheme (Hidden from user, defaults to tambah_skema) -->
                    <div style="display: none;">
                        <select id="modalPilihanSkemaBpjs" onchange="handleModalPilihanSkemaBpjsChange(this.value)" required>
                            <option value="tambah_skema">Tambah Skema</option>
                        </select>
                    </div>

                    <!-- BPJS Configuration Inputs (cloned/copied from modalBpjs details) -->
                    <div id="modalClientBpjsOverrideFields" style="display: flex; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; background: #f8fafc; flex-direction: column; gap: 12px; margin-top: 10px; margin-bottom: 10px;">
                        <h4 style="margin: 0 0 5px 0; font-size: 14px; font-weight: 700; color: #1e293b;"><i class="fas fa-cog" style="color: var(--primary-color);"></i> BPJS Programs Activation</h4>
                        <p style="margin: 0; font-size: 12px; color: #64748b;">
                            Select which BPJS programs are active for this client workspace. Inactive programs will be calculated as 0.
                        </p>
                        
                        <div style="display: grid; grid-template-columns: 1fr; gap: 10px; margin-top: 5px;">
                            <!-- BPJS Kesehatan -->
                            <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; cursor: pointer; transition: all 0.2s ease;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7;">
                                        <i class="fas fa-hand-holding-medical" style="font-size: 16px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #1e293b; font-size: 13px;">BPJS Kesehatan</div>
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

                    <!-- Tax Scheme -->
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; flex-direction: column;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-weight: 600; color: #475569;"><i class="fas fa-percent" style="margin-right: 8px;"></i>PPh 21 Scheme</span>
                                <button type="button" id="modalBtnDetailSkemaPajak" class="btn-detail-pajak" onclick="lihatDetailSkemaPajakModal()" style="background: none; border: none; color: #f39c12; cursor: pointer; display: none; align-items: center; gap: 4px; font-size: 13px; font-weight: 600;">
                                    <i class="fas fa-eye"></i> Scheme Detail
                                </button>
                            </div>
                            <small style="color: #64748b; font-size: 11px;">Calculation method for PPh 21 tax deductions.</small>
                        </div>
                        <select id="modalPilihanSkemaPajak" onchange="handleModalPilihanSkemaPajakChange(this.value)" required style="width: 50%; padding: 8px 12px; border-radius: 8px; border: 1px solid #ddd; background: white;">
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
    <div id="modalKaryawan" class="modal-skema" style="display: none; z-index: 1000;">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalKaryawanTitle">Add Employee Data</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalKaryawan()"></i>
        </div>
        <form id="formKaryawan" novalidate>
            <div class="modal-body" style="padding: 25px; max-height: 70vh; overflow-y: auto;">
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

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
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
                        <option value="7">7 Days</option>
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
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalKaryawan()">Cancel</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Save Data</button>
            </div>
        </form>
    </div>

    <!-- Work Location Modal -->
    <div id="modalLokasiKerja" class="modal-skema" style="display: none; z-index: 1000;">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalLokasiKerjaTitle">Add Work Location</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalLokasiKerja()"></i>
        </div>
        <form id="formLokasiKerja">
            <div class="modal-body" style="padding: 25px; max-height: 70vh; overflow-y: auto;">
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
            <div class="modal-footer">
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
                    <select id="skemaKompensasiSifat" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white;">
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
                    <select id="komponenKompensasiSifat" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
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

                    <!-- BPJS Kesehatan -->
                    <div style="background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">BPJS Kesehatan</h5>
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
                            <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 4px;">BPJS Kesehatan Max Salary Limit</span>
                            <strong style="font-size: 14px; color: #334155;" id="dtlPajakBpjsKesMaxSalary">Rp 12.000.000</strong>
                        </div>
                    </div>

                    <!-- BPJS Ketenagakerjaan -->
                    <div style="background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">BPJS Ketenagakerjaan</h5>
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
                            <h5 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #334155;">BPJS Kesehatan (%)</h5>
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
                                <option value="Gross">Gross (Tax borne by Employee)</option>
                                <option value="Gross Up">Gross Up (Tax Allowance)</option>
                                <option value="Net">Net (Tax borne by Company)</option>
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
                    <label style="font-weight: 600; margin-bottom: 6px; display: block; color: #475569;">Deskripsi</label>
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
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block; color: #475569;">Client</label>
                    <select id="modalUploadAbsensiClient" onchange="onAbsensiClientChanged()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="">-- Select Client --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block; color: #475569;">Period</label>
                    <select id="modalUploadAbsensiPeriod" onchange="onAbsensiPeriodChanged()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;" disabled>
                        <option value="">-- Select Client First --</option>
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

    <!-- Holiday Modal -->
    <div id="holidayModal" class="modal-skema" style="width: 480px; max-width: 95%; display: none; z-index: 2000;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="holidayModalTitle">Tambah Hari Libur</h3>
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
                    <button type="button" class="btn-cancel" onclick="closeModal('holidayModal')" style="padding: 10px 20px; border-radius: 8px;">Batal</button>
                    <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Simpan</button>
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
                        <option value="">-- Pilih Karyawan --</option>
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
                        <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Jam Masuk</label>
                        <input type="time" id="attendanceJamMasuk" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Jam Keluar</label>
                        <input type="time" id="attendanceJamKeluar" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Keterangan</label>
                    <input type="text" id="attendanceKeterangan" placeholder="Opsional" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                    <button type="button" class="btn-cancel" onclick="closeModal('attendanceModal')" style="padding: 10px 20px; border-radius: 8px;">Batal</button>
                    <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Simpan</button>
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
                        <option value="">-- Pilih Karyawan --</option>
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
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Keterangan</label>
                    <input type="text" id="overtimeKeterangan" placeholder="Opsional" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                    <button type="button" class="btn-cancel" onclick="closeModal('overtimeModal')" style="padding: 10px 20px; border-radius: 8px;">Batal</button>
                    <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Simpan</button>
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
                    <button type="button" class="btn-cancel" onclick="tutupModalShiftScheme()" style="padding: 10px 20px; border-radius: 8px;">Batal</button>
                    <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Alokasi Shift Karyawan -->
    <div id="modalAssignShift" class="modal-skema" style="width: 500px; max-width: 95%; display: none; z-index: 2000;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="modalAssignShiftTitle">Tugaskan Shift Karyawan</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white;" onclick="tutupModalAssignShift()"></i>
        </div>
        <div class="modal-body" style="padding: 25px;">
            <form id="formAssignShift" onsubmit="simpanAssignShift(event)">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-size: 14px;">Pilih Karyawan <span style="color: #ef4444;">*</span></label>
                    <select id="assignShiftEmployeeId" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 14px; outline: none; background: white;">
                        <option value="">-- Pilih Karyawan --</option>
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
                    <button type="button" class="btn-cancel" onclick="tutupModalAssignShift()" style="padding: 10px 20px; border-radius: 8px;">Batal</button>
                    <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">Tugaskan</button>
                </div>
            </form>
        </div>
    </div>

