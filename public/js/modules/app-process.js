// ===== PROCESS PAYROLL MODULE =====
// Extracted from app.js for modular monolith architecture

// ===== 7. PROSES PAYROLL BULANAN =====
async function loadActivePeriod() {
    try {
        const url = window.selectedClientId ? `${API_URL}/periods?client_id=${window.selectedClientId}` : `${API_URL}/periods`;
        const response = await fetch(url);
        const periods = await response.json();
        window.loadedPeriods = periods;
        
        // 1. Render dropdown selector on the main page
        const select = document.getElementById('selectPeriodInput');
        if (select) {
            select.innerHTML = '<option value="">-- Select Period --</option>' + periods.map(p => `
                <option value="${p.id}" ${p.id == currentPeriodId ? 'selected' : ''}>${p.nama} (${p.status})</option>
            `).join('');
        }
        
        // 2. Render history list inside the popup modal
        const list = document.getElementById('periodHistoryList');
        if (list) {
            list.innerHTML = periods.map(p => `
                <div class="period-item ${p.id == currentPeriodId ? 'active' : ''}" onclick="selectPeriod(${p.id}, '${p.nama}')" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: ${p.id == currentPeriodId ? '#f0fdf4' : 'transparent'};">
                    <span style="font-weight: 500;">${p.nama}</span>
                    <span class="status-badge ${p.status && (p.status.toLowerCase().includes('open') || p.status.toLowerCase().includes('terbuka') || p.status.toLowerCase().includes('active')) ? 'success' : 'danger'}" style="font-size: 11px;">${p.status}</span>
                </div>
            `).join('');
        }
        
        if (periods.length > 0) {
            if (!currentPeriodId) {
                selectPeriod(periods[0].id, periods[0].nama);
                if (select) select.value = periods[0].id;
            } else {
                selectPeriod(currentPeriodId, periods.find(p => p.id == currentPeriodId)?.nama || '');
                if (select) select.value = currentPeriodId;
            }
        } else {
            document.getElementById('prosesActions').style.display = 'none';
            document.getElementById('prosesEmptyState').style.display = 'block';
        }
    } catch (err) { console.error(err); }
}

function selectPeriod(id, name) {
    currentPeriodId = id;
    if (!id || id === 'null' || id === '') {
        document.getElementById('prosesActions').style.display = 'none';
        document.getElementById('prosesEmptyState').style.display = 'block';
        return;
    }
    document.getElementById('activePeriodName').innerText = name;
    
    // Update active period status badge dynamically
    const statusBadge = document.getElementById('activePeriodStatus');
    if (statusBadge && window.loadedPeriods) {
        const period = window.loadedPeriods.find(p => p.id == id);
        const status = period ? period.status : 'Open';
        statusBadge.innerText = status;
        statusBadge.className = 'status-badge ' + (status.toLowerCase().includes('open') || status.toLowerCase().includes('terbuka') || status.toLowerCase().includes('active') ? 'success' : 'danger');
    }

    document.getElementById('prosesActions').style.display = 'block';
    document.getElementById('prosesEmptyState').style.display = 'none';
    renderCutOffTable();
    renderReviewGajiTable();
    tutupSemuaModal();
}

async function renderCutOffTable() {
    if(!currentPeriodId) return;
    try {
        const tbody = document.getElementById('tabelCutOffBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        const url = window.selectedClientId ? `${API_URL}/attendance/${currentPeriodId}?client_id=${window.selectedClientId}` : `${API_URL}/attendance/${currentPeriodId}`;
        const res = await fetch(url);
        const data = await res.json();
        if (!tbody) return;
        tbody.innerHTML = data.map(row => `
            <tr>
                <td>${row.employee_name}</td>
                <td>${row.hari_kerja || 0} Days</td>
                <td>${row.jam_lembur || 0} Hours</td>
                <td>${formatRupiah(row.potongan_absensi)}</td>
                <td>${formatRupiah(row.bonus_tambahan)}</td>
                <td><button class="btn-icon btn-edit" onclick="bukaModalCutOff(${row.pkwt_id}, '${row.employee_name}', ${row.hari_kerja || 22}, ${row.jam_lembur || 0}, ${row.potongan_absensi || 0}, ${row.bonus_tambahan || 0})"><i class="fas fa-edit"></i></button></td>
            </tr>
        `).join('');
    } catch (err) { console.error(err); }
}

async function renderReviewGajiTable() {
    if(!currentPeriodId) return;
    try {
        const tbody = document.getElementById('tabelReviewGajiBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        const url = window.selectedClientId ? `${API_URL}/payroll-results/${currentPeriodId}?client_id=${window.selectedClientId}` : `${API_URL}/payroll-results/${currentPeriodId}`;
        const res = await fetch(url);
        const data = await res.json();
        const section = document.getElementById('resultSection');
        if (!tbody) return;
        if (data.length > 0) {
            section.style.display = 'block';
            tbody.innerHTML = data.map(row => `
                <tr>
                    <td>${row.employee_name}</td>
                    <td style="color:var(--success);">${formatRupiah(row.total_pendapatan)}</td>
                    <td style="color:var(--danger);">${formatRupiah(row.total_potongan)}</td>
                    <td style="font-weight:700;">${formatRupiah(row.take_home_pay)}</td>
                    <td><span class="status-badge ${row.status_approval === 'Approved' ? 'success' : 'warning'}">${row.status_approval}</span></td>
                    <td>
                        ${row.status_approval === 'Pending' ? 
                            `<button class="btn-save" onclick="approveGaji(${row.id})" style="padding:5px 10px; font-size:11px;">Approve</button>` : 
                            `<button class="btn-icon" onclick="bukaSlipGaji(${row.id})" title="View Pay Slip" style="background:var(--primary-color); color:white; width:30px; height:30px;"><i class="fas fa-eye"></i></button>`
                        }
                    </td>
                </tr>
            `).join('');
        } else { 
            section.style.display = 'block';
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding: 20px; color:#7f8c8d;">No salary data generated for this period.</td></tr>`;
        }
    } catch (err) { console.error(err); }
}

async function generateGaji() {
    if(!currentPeriodId) return;
    showToast('Calculating salary...', 'info');
    const url = window.selectedClientId ? `${API_URL}/generate-payroll/${currentPeriodId}?client_id=${window.selectedClientId}` : `${API_URL}/generate-payroll/${currentPeriodId}`;
    const res = await fetch(url, { method: 'POST' });
    if (res.ok) { showToast('Salary generated successfully!', 'success'); renderReviewGajiTable(); }
}

async function approveGaji(id) {
    const res = await fetch(`${API_URL}/approve-payroll/${id}`, { method: 'POST' });
    if (res.ok) { showToast('Salary approved!', 'success'); renderReviewGajiTable(); }
}

// ===== EXPORT =====
async function exportGajiToExcel() {
    if(!currentPeriodId) {
        showToast('Please select a period first.', 'warning');
        return;
    }
    
    showToast('Exporting to Excel...', 'info');
    
    try {
        let url = `${API_URL}/export-payroll/${currentPeriodId}?format=json`;
        if (window.selectedClientId) {
            url += `&client_id=${window.selectedClientId}`;
        }
        
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('Failed to fetch export data');
        }
        
        const data = await response.json();
        if (!data || data.length === 0) {
            showToast('No payroll data to export.', 'warning');
            return;
        }
        
        // Format the rows for Excel
        const formatted = data.map((row, index) => {
            const bpjsTK = parseFloat(row.bpjs_jht_karyawan || 0) + parseFloat(row.bpjs_jp_karyawan || 0);
            const bpjsKes = parseFloat(row.bpjs_kes_karyawan || 0);
            const dob = [row.tempat_lahir, row.tanggal_lahir].filter(Boolean).join(', ') || '-';
            
            return {
                'No': index + 1,
                'Company / Client': row.client_name || '-',
                'Employee ID (NIK)': row.employ_id || '-',
                'Employee Name': row.employee_name || '-',
                'Place & Date of Birth': dob,
                'NPWP': row.npwp || '-',
                'Division': row.division_name || '-',
                'Department': row.department_name || '-',
                'Position / Role': row.position_name || '-',
                'Work Location': row.location_name || '-',
                'Min. Wage (UMP/UMK)': row.min_wage ? parseFloat(row.min_wage) : 0,
                'Basic Salary': parseFloat(row.gaji_pokok || 0),
                'Overtime Pay': parseFloat(row.lembur_pay || 0),
                'Bonus/Lainnya': parseFloat(row.bonus_tambahan || 0),
                'Total Income (Pendapatan)': parseFloat(row.total_pendapatan || 0),
                'Absence Deduction': parseFloat(row.potongan_absen || 0),
                'BPJS Ketenagakerjaan (Karyawan)': bpjsTK,
                'BPJS Kesehatan (Karyawan)': bpjsKes,
                'Tax (PPh21)': parseFloat(row.pph21 || 0),
                'Total Deductions (Potongan)': parseFloat(row.total_potongan || 0),
                'Take Home Pay': parseFloat(row.take_home_pay || 0),
                'Status': row.status_approval || 'Pending'
            };
        });
        
        // Create SheetJS workbook and worksheet
        const ws = XLSX.utils.json_to_sheet(formatted);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Payroll Report");
        
        // Adjust column widths for clean readability
        const wscols = [
            {wch: 5},   // No
            {wch: 25},  // Company / Client
            {wch: 18},  // Employee ID (NIK)
            {wch: 20},  // Employee Name
            {wch: 22},  // Place & Date of Birth
            {wch: 20},  // NPWP
            {wch: 15},  // Division
            {wch: 15},  // Department
            {wch: 18},  // Position / Role
            {wch: 25},  // Work Location
            {wch: 15},  // Min. Wage (UMP/UMK)
            {wch: 15},  // Basic Salary
            {wch: 12},  // Overtime Pay
            {wch: 15},  // Bonus/Lainnya
            {wch: 20},  // Total Income (Pendapatan)
            {wch: 15},  // Absence Deduction
            {wch: 28},  // BPJS Ketenagakerjaan (Karyawan)
            {wch: 25},  // BPJS Kesehatan (Karyawan)
            {wch: 12},  // Tax (PPh21)
            {wch: 25},  // Total Deductions (Potongan)
            {wch: 20},  // Take Home Pay
            {wch: 12}   // Status
        ];
        ws['!cols'] = wscols;
        
        // Get period name from dropdown selection
        const select = document.getElementById('selectPeriodInput');
        let periodText = 'Report';
        if (select && select.selectedIndex >= 0) {
            periodText = select.options[select.selectedIndex].text.split('(')[0].trim().replace(/\s+/g, '_');
        }
        
        // Download real .xlsx file
        XLSX.writeFile(wb, `Payroll_Report_${periodText}.xlsx`);
        showToast('Export successful!', 'success');
    } catch (error) {
        console.error(error);
        showToast('Export failed: ' + error.message, 'danger');
    }
}

// ===== UTILS & MODAL CLOSING =====

// ===== SLIP GAJI =====

async function bukaSlipGaji(id) {
    try {
        const response = await fetch(`${API_URL}/slip-details/${id}`);
        const data = await response.json();

        const info = data.info;
        
        // Build company burdens array for PKWT
        const companyBurdens = [];
        if (parseFloat(info.bpjs_kes_perusahaan) > 0) companyBurdens.push({ name: 'BPJS Kesehatan (4% Beban Perusahaan)', value: parseFloat(info.bpjs_kes_perusahaan) });
        if (parseFloat(info.bpjs_jht_perusahaan) > 0) companyBurdens.push({ name: 'BPJS TK JHT (3.7% Beban Perusahaan)', value: parseFloat(info.bpjs_jht_perusahaan) });
        if (parseFloat(info.bpjs_jp_perusahaan) > 0) companyBurdens.push({ name: 'BPJS TK JP (2% Beban Perusahaan)', value: parseFloat(info.bpjs_jp_perusahaan) });
        if (parseFloat(info.bpjs_jkk_perusahaan) > 0) companyBurdens.push({ name: 'BPJS TK JKK (Beban Perusahaan)', value: parseFloat(info.bpjs_jkk_perusahaan) });
        if (parseFloat(info.bpjs_jkm_perusahaan) > 0) companyBurdens.push({ name: 'BPJS TK JKM (Beban Perusahaan)', value: parseFloat(info.bpjs_jkm_perusahaan) });

        const hasBpjs = companyBurdens.length > 0 || parseFloat(info.bpjs_kes_karyawan) > 0 || parseFloat(info.bpjs_jht_karyawan) > 0 || parseFloat(info.bpjs_jp_karyawan) > 0;

        document.getElementById('slipContent').innerHTML = `
        <div style="text-align:center;border-bottom:2px solid #eee;padding-bottom:15px;margin-bottom:20px;">
            <h2 style="color:var(--primary-color); margin: 0;">PAYSLIP</h2>
            <p style="font-size:13px;color:#666; margin: 5px 0 0 0;">Period: ${info.period_name || info.periode}</p>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:20px;font-size:14px;">
            <div>
                <p style="margin: 4px 0;"><strong>Name:</strong> ${info.employee_name}</p>
                <p style="margin: 4px 0;"><strong>NIK:</strong> ${info.employ_id || info.nik_karyawan || '-'}</p>
                <p style="margin: 4px 0;"><strong>Position:</strong> ${info.position_name || '-'}</p>
            </div>
            <div style="text-align:right;">
                <p style="margin: 4px 0;"><strong>Status:</strong> ${info.status_approval || 'Pending'}</p>
                <p style="margin: 4px 0;"><strong>Client:</strong> ${info.client_name || '-'}</p>
            </div>
        </div>
        <table style="width:100%;font-size:14px;border-collapse:collapse;margin-bottom:20px;">
            <thead>
                <tr style="background:#f8f9fa;"><th colspan="2" style="text-align:left;padding:8px 10px;border-bottom:1px solid #eee;">Earnings</th></tr>
            </thead>
            <tbody>
                ${data.earnings.map(e => `<tr><td style="padding:8px 10px;">${e.nama}</td><td style="text-align:right;padding:8px 10px;">${formatRupiah(e.nilai)}</td></tr>`).join('')}
            </tbody>
            <thead>
                <tr style="background:#f8f9fa;"><th colspan="2" style="text-align:left;padding:8px 10px;border-bottom:1px solid #eee;">Deductions</th></tr>
            </thead>
            <tbody>
                ${data.deductions.map(d => `<tr><td style="padding:8px 10px;">${d.nama}</td><td style="text-align:right;color:#e74c3c;padding:8px 10px;">- ${formatRupiah(d.nilai)}</td></tr>`).join('')}
            </tbody>
            <tfoot>
                <tr style="border-top:2px solid #eee;">
                    <th style="padding:15px 10px;font-size:16px;text-align:left;">TAKE HOME PAY</th>
                    <th style="padding:15px 10px;font-size:16px;text-align:right;color:var(--success);">${formatRupiah(info.take_home_pay)}</th>
                </tr>
            </tfoot>
        </table>
        
        ${companyBurdens.length > 0 ? `
        <div style="margin-top:20px;border-top:2px dashed #eee;padding-top:15px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <h4 style="margin:0;color:#475569;font-size:14px;font-weight:700;text-transform:uppercase;">Beban Perusahaan (Informasi)</h4>
                ${hasBpjs ? `<a href="javascript:void(0)" onclick="bukaDetailBpjsModal('pkwt', ${id})" style="font-size:12px;color:var(--primary-color);font-weight:600;text-decoration:none;"><i class="fas fa-calculator"></i> Detail Perhitungan BPJS</a>` : ''}
            </div>
            <table style="width:100%;font-size:13px;color:#64748b;">
                ${companyBurdens.map(d=>`<tr><td style="padding:4px 0;">${d.name}</td><td style="text-align:right;padding:4px 0;">${formatRupiah(d.value)}</td></tr>`).join('')}
            </table>
        </div>
        ` : ''}
        `;

        document.getElementById('modalSlip').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    } catch (err) {
        console.error('Error loading slip details:', err);
    }
}

function tutupModalSlip() {
    document.getElementById('modalSlip').style.display = 'none';
    if(document.getElementById('modalDetailBpjs')) {
        document.getElementById('modalDetailBpjs').style.display = 'none';
    }
    document.getElementById('overlay').style.display = 'none';
}

function cetakSlip() {
    const content = document.getElementById('slipContent').innerHTML;
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print Pay Slip</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">');
    printWindow.document.write('<style>body{font-family: "Inter", sans-serif; padding: 40px;} .primary-color{color: #2980b9;} .success{color: #27ae60;} .danger{color: #e74c3c;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    setTimeout(() => {
        printWindow.print();
    }, 500);
}

    // formPeriode submit handler
    if(document.getElementById('formPeriode')) {
        document.getElementById('formPeriode').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = {
                client_id: window.selectedClientId ? parseInt(window.selectedClientId) : null,
                bulan: parseInt(document.getElementById('periodMonth').value),
                tahun: parseInt(document.getElementById('periodYear').value)
            };
            const res = await fetch(`${API_URL}/periods`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (res.ok) {
                tutupSemuaModal();
                showToast('New period opened successfully!', 'success');
                loadActivePeriod();
            } else {
                showToast('Failed to open new period!', 'error');
            }
        });
    }

    // Form Cut-Off submit handler
    if (document.getElementById('formCutOff')) {
        document.getElementById('formCutOff').addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!currentPeriodId) {
                showToast('Active period not found!', 'error');
                return;
            }
            const data = {
                period_id: parseInt(currentPeriodId),
                pkwt_id: parseInt(document.getElementById('cutoffPkwtId').value),
                hari_kerja: parseInt(document.getElementById('cutoffHariKerja').value) || 0,
                jam_lembur: parseFloat(document.getElementById('cutoffJamLembur').value) || 0,
                potongan_absensi: parseFloat(document.getElementById('cutoffPotongan').value) || 0,
                bonus_tambahan: parseFloat(document.getElementById('cutoffBonus').value) || 0
            };
            try {
                const res = await fetch(`${API_URL}/attendance`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                if (res.ok) {
                    tutupSemuaModal();
                    renderCutOffTable();
                    showToast('Cut-off data saved successfully!', 'success');
                } else {
                    showToast('Failed to save cut-off data!', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Failed to save cut-off data!', 'error');
            }
        });
    }

    // Dynamic recalculation of CutOff Potongan based on hari_kerja
    const cutoffHariKerjaInput = document.getElementById('cutoffHariKerja');
    if (cutoffHariKerjaInput) {
        cutoffHariKerjaInput.addEventListener('input', () => {
            if (window.currentAbsenceConfig && window.currentAbsenceConfig.nominal_potongan > 0) {
                const nominal = parseFloat(window.currentAbsenceConfig.nominal_potongan) || 0;
                const hariKerja = parseInt(cutoffHariKerjaInput.value) || 0;
                const missingDays = 21 - hariKerja;
                const potonganInput = document.getElementById('cutoffPotongan');
                if (potonganInput) {
                    potonganInput.value = missingDays > 0 ? (missingDays * nominal) : 0;
                }
            }
        });
    }

function bukaModalKomponen(schemeId) {
    document.getElementById('modalKomponen').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('formKomponen').reset();
    document.getElementById('komponenSchemeId').value = schemeId;
}

function bukaModalOrg(type, mode, id = null) {
    document.getElementById('modalOrg').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('orgType').value = type;
}

function bukaModalPeriode() {
    document.getElementById('modalPeriode').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function bukaModalCutOff(pkwtId, empName, hariKerja = 22, jamLembur = 0, potongan = 0, bonus = 0) {
    document.getElementById('modalCutOff').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('cutoffPkwtId').value = pkwtId;
    document.getElementById('cutoffEmployeeName').value = empName;
    document.getElementById('cutoffHariKerja').value = hariKerja;
    document.getElementById('cutoffJamLembur').value = jamLembur;
    
    // Calculate default potongan if nominal_potongan is set and no manual potongan exists yet
    let finalPotongan = potongan;
    if (potongan == 0 && window.currentAbsenceConfig && window.currentAbsenceConfig.nominal_potongan > 0) {
        const nominal = parseFloat(window.currentAbsenceConfig.nominal_potongan) || 0;
        const missingDays = 21 - hariKerja;
        if (missingDays > 0) {
            finalPotongan = missingDays * nominal;
        }
    }
    document.getElementById('cutoffPotongan').value = finalPotongan;
    document.getElementById('cutoffBonus').value = bonus;
}

function exportPayrollExcel() {
    if (!currentPeriodId) {
        showToast('Please select a period first!', 'error');
        return;
    }
    const clientId = window.selectedClientId || '';
    if (!clientId) {
        showToast('Please select a client first!', 'error');
        return;
    }
    
    // Redirect to download endpoint
    window.location.href = `${API_URL}/payroll-export/${currentPeriodId}?client_id=${clientId}`;
}

// Global Closing Handlers
function tutupModal() { tutupSemuaModal(); }
function tutupModalSkema() { tutupSemuaModal(); }
function tutupModalKomponen() { tutupSemuaModal(); }
function tutupModalPajak() { tutupSemuaModal(); }
function tutupModalSetup() { tutupSemuaModal(); }
function tutupModalPKWT() { tutupSemuaModal(); }
function tutupModalPeriode() { tutupSemuaModal(); }
function tutupModalCutOff() { tutupSemuaModal(); }

