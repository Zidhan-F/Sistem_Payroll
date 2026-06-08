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
            const hasCurrentPeriod = periods.some(p => p.id == currentPeriodId);
            if (!currentPeriodId || !hasCurrentPeriod) {
                selectPeriod(periods[0].id, periods[0].nama);
                if (select) select.value = periods[0].id;
            } else {
                selectPeriod(currentPeriodId, periods.find(p => p.id == currentPeriodId)?.nama || '');
                if (select) select.value = currentPeriodId;
            }
        } else {
            document.getElementById('prosesActions').style.display = 'none';
            document.getElementById('prosesEmptyState').style.display = 'block';
            if (document.getElementById('resultSection')) document.getElementById('resultSection').style.display = 'none';
            if (document.getElementById('resultsEmptyState')) document.getElementById('resultsEmptyState').style.display = 'block';
        }
    } catch (err) { console.error(err); }
}

function selectPeriod(id, name) {
    currentPeriodId = id;
    if (!id || id === 'null' || id === '') {
        document.getElementById('prosesActions').style.display = 'none';
        document.getElementById('prosesEmptyState').style.display = 'block';
        if (document.getElementById('resultSection')) document.getElementById('resultSection').style.display = 'none';
        if (document.getElementById('resultsEmptyState')) document.getElementById('resultsEmptyState').style.display = 'block';
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
    if (document.getElementById('resultsEmptyState')) document.getElementById('resultsEmptyState').style.display = 'none';
    if (document.getElementById('resultSection')) document.getElementById('resultSection').style.display = 'block';
    renderCutOffTable();
    renderReviewGajiTable();
    tutupSemuaModal();
}

function switchPayrollProcessSubTab(tab) {
    document.querySelectorAll('.client-proses-subpanel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('#viewProses .sub-tab-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.color = '#64748b';
        btn.style.borderBottomColor = 'transparent';
    });

    if (tab === 'processing') {
        const panel = document.getElementById('panelSalaryProcessing');
        if (panel) panel.style.display = 'block';
        const activeBtn = document.getElementById('subTabSalaryProcessing');
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.color = 'var(--primary-color)';
            activeBtn.style.borderBottomColor = 'var(--primary-color)';
        }
    } else if (tab === 'results') {
        const panel = document.getElementById('panelCalculationResults');
        if (panel) panel.style.display = 'block';
        const activeBtn = document.getElementById('subTabCalculationResults');
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.color = 'var(--primary-color)';
            activeBtn.style.borderBottomColor = 'var(--primary-color)';
        }
    }
}
window.switchPayrollProcessSubTab = switchPayrollProcessSubTab;

async function renderCutOffTable() {
    if(!currentPeriodId) return;
    try {
        const tbody = document.getElementById('tabelCutOffBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        const url = window.selectedClientId ? `${API_URL}/attendance/${currentPeriodId}?client_id=${window.selectedClientId}` : `${API_URL}/attendance/${currentPeriodId}`;
        const res = await fetch(url);
        if (!res.ok) {
            throw new Error('Failed to load attendance data (HTTP ' + res.status + ')');
        }
        const data = await res.json();
        window.currentPeriodAttendance = data;
        if (!tbody) return;
        if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px; color: #64748b;"><i class="fas fa-info-circle" style="margin-right: 8px;"></i>Tidak ada data karyawan aktif untuk periode ini.</td></tr>`;
            return;
        }
        tbody.innerHTML = data.map(row => {
            const hariKerja = parseFloat(row.hari_kerja) || 0;
            const jamLembur = parseFloat(row.jam_lembur) || 0;
            const potongan = parseFloat(row.potongan_absensi) || 0;
            const bonus = parseFloat(row.bonus_tambahan) || 0;
            return `
            <tr>
                <td>${row.employee_name} <span class="status-badge info" style="font-size:10px; margin-left:5px; padding:2px 6px;">${row.tipe_perjanjian || 'PKWT'}</span></td>
                <td>${hariKerja} Days</td>
                <td>${jamLembur} Hours</td>
                <td>${formatRupiah(potongan)}</td>
                <td>${formatRupiah(bonus)}</td>
                <td><button class="btn-icon btn-edit" onclick="bukaModalCutOff(${row.pkwt_id}, '${row.employee_name}', ${hariKerja || 22}, ${jamLembur}, ${potongan}, ${bonus})"><i class="fas fa-edit"></i></button></td>
            </tr>`;
        }).join('');
    } catch (err) { 
        console.error(err); 
        const tbody = document.getElementById('tabelCutOffBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px; color: #ef4444;"><i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>Gagal memuat data cut-off: ${err.message || err}</td></tr>`;
        }
    }
}

async function renderReviewGajiTable() {
    if(!currentPeriodId) return;
    try {
        const tbody = document.getElementById('tabelReviewGajiBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="11" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        const url = window.selectedClientId ? `${API_URL}/payroll-results/${currentPeriodId}?client_id=${window.selectedClientId}` : `${API_URL}/payroll-results/${currentPeriodId}`;
        const res = await fetch(url);
        if (!res.ok) {
            throw new Error('Failed to load payroll results (HTTP ' + res.status + ')');
        }
        const data = await res.json();
        const section = document.getElementById('resultSection');
        if (!tbody) return;
        if (data.length > 0) {
            section.style.display = 'block';
            tbody.innerHTML = data.map(row => `
                <tr>
                    <td style="text-align: center; vertical-align: middle;">
                        <input type="checkbox" class="review-gaji-checkbox" data-id="${row.id}" data-employee-name="${row.employee_name}" data-scheme="${row.scheme_name || ''}" data-net-salary="${row.take_home_pay}" ${row.status_approval === 'Approved' ? 'disabled checked' : ''} style="transform: scale(1.2); cursor: pointer;">
                    </td>
                    <td>${row.employee_name} <span class="status-badge info" style="font-size:10px; margin-left:5px; padding:2px 6px;">${row.tipe_perjanjian || 'PKWT'}</span></td>
                    <td>${row.division_name || '-'}</td>
                    <td>${row.department_name || '-'}</td>
                    <td>${row.position_name || '-'}</td>
                    <td>${row.scheme_name || '-'}</td>
                    <td>${formatRupiah(row.total_pendapatan)}</td>
                    <td>${formatRupiah(row.total_potongan)}</td>
                    <td style="font-weight:700;">${formatRupiah(row.take_home_pay)}</td>
                    <td><span class="status-badge ${row.status_approval === 'Approved' ? 'success' : 'warning'}">${row.status_approval}</span></td>
                    <td>
                        <div style="display:flex; gap:6px; align-items:center;">
                            <button class="btn-icon btn-neutral" onclick="bukaSlipGaji(${row.id})" title="View Pay Slip / Details" style="width:30px; height:30px; border-radius:6px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-eye"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
        } else { 
            section.style.display = 'block';
            tbody.innerHTML = `<tr><td colspan="11" style="text-align:center; padding: 20px; color:#7f8c8d;"><i class="fas fa-info-circle" style="margin-right: 8px;"></i>Belum ada data gaji yang di-generate untuk periode ini.</td></tr>`;
        }
    } catch (err) { 
        console.error(err); 
        const tbody = document.getElementById('tabelReviewGajiBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="11" style="text-align: center; padding: 20px; color: #ef4444;"><i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>Gagal memuat hasil kalkulasi: ${err.message || err}</td></tr>`;
        }
    }
}

// Add event listener for select all checkbox using event delegation
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'selectAllReviewGaji') {
        const checkboxes = document.querySelectorAll('.review-gaji-checkbox:not(:disabled)');
        checkboxes.forEach(cb => {
            cb.checked = e.target.checked;
        });
    }
});

async function approveSelectedGaji() {
    const checkboxes = document.querySelectorAll('.review-gaji-checkbox:checked:not(:disabled)');
    if (checkboxes.length === 0) {
        showToast('Pilih minimal satu data gaji yang berstatus Pending!', 'warning');
        return;
    }

    const ids = [];
    let validationFailed = false;
    let failMessage = '';

    for (let cb of checkboxes) {
        const id = cb.getAttribute('data-id');
        const name = cb.getAttribute('data-employee-name');
        const scheme = cb.getAttribute('data-scheme');
        const netSalary = parseFloat(cb.getAttribute('data-net-salary') || 0);

        // Validation 1: Must have a scheme
        if (!scheme || scheme === '-' || scheme.trim() === '') {
            validationFailed = true;
            failMessage = `Gaji untuk karyawan "${name}" tidak dapat disetujui karena belum memiliki skema payroll.`;
            break;
        }

        // Validation 2: Net salary must be > 0
        if (netSalary <= 0) {
            validationFailed = true;
            failMessage = `Gaji untuk karyawan "${name}" tidak dapat disetujui karena Net Salary bernilai Rp 0 atau kurang.`;
            break;
        }

        ids.push(id);
    }

    if (validationFailed) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: failMessage,
                confirmButtonColor: '#3498db'
            });
        } else {
            showToast(failMessage, 'error');
        }
        return;
    }

    const confirmMsg = `Apakah Anda yakin ingin menyetujui ${ids.length} data gaji yang dipilih?`;
    let confirmed = false;
    if (typeof showConfirm === 'function') {
        confirmed = await showConfirm(confirmMsg, 'Konfirmasi Persetujuan', 'Ya, Setujui', 'Batal', 'primary');
    } else {
        confirmed = confirm(confirmMsg);
    }

    if (!confirmed) return;

    showToast('Approving selected salaries...', 'info');

    try {
        const res = await fetch(`${API_URL}/approve-payroll-bulk`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: ids })
        });

        if (res.ok) {
            showToast('Semua data gaji terpilih berhasil disetujui!', 'success');
            const selectAll = document.getElementById('selectAllReviewGaji');
            if (selectAll) selectAll.checked = false;
            renderReviewGajiTable();
        } else {
            const errData = await res.json();
            const errMsg = errData.messages?.error || errData.message || 'Gagal menyetujui data gaji terpilih!';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Persetujuan Gagal',
                    text: errMsg,
                    confirmButtonColor: '#3498db'
                });
            } else {
                showToast(errMsg, 'error');
            }
        }
    } catch (err) {
        console.error(err);
        showToast('Terjadi kesalahan saat memproses persetujuan.', 'error');
    }
}

async function generateGaji() {
    if(!currentPeriodId) return;
    showToast('Calculating salary...', 'info');
    const url = window.selectedClientId ? `${API_URL}/generate-payroll/${currentPeriodId}?client_id=${window.selectedClientId}` : `${API_URL}/generate-payroll/${currentPeriodId}`;
    const res = await fetch(url, { method: 'POST' });
    if (res.ok) { showToast('Salary generated successfully!', 'success'); renderReviewGajiTable(); }
}

async function approveGaji(id) {
    // Keep this individual helper but delegate to bulk logic for safety or perform verification
    showToast('Approving salary...', 'info');
    try {
        const res = await fetch(`${API_URL}/approve-payroll/${id}`, { method: 'POST' });
        if (res.ok) { 
            showToast('Salary approved!', 'success'); 
            renderReviewGajiTable(); 
        } else {
            const errData = await res.json();
            const errMsg = errData.messages?.error || errData.message || 'Gagal menyetujui gaji!';
            showToast(errMsg, 'error');
        }
    } catch(err) {
        console.error(err);
        showToast('Gagal memproses persetujuan.', 'error');
    }
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
                'Contract Type': row.tipe_perjanjian || 'PKWT',
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
            {wch: 15},  // Contract Type
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
        <table style="width:100%; border:none; margin-bottom:20px; font-size:14px; line-height:1.5; border-collapse:collapse;">
            <tr>
                <td style="width:60%; vertical-align:top; padding:0; border:none;">
                    <p style="margin: 4px 0;"><strong>Name:</strong> ${info.employee_name}</p>
                    <p style="margin: 4px 0;"><strong>NIK:</strong> ${info.employ_id || info.nik_karyawan || '-'}</p>
                    <p style="margin: 4px 0;"><strong>Position:</strong> ${info.position_name || '-'}</p>
                </td>
                <td style="width:40%; text-align:right; vertical-align:top; padding:0; border:none;">
                    <p style="margin: 4px 0;"><strong>Status:</strong> ${info.status_approval || 'Pending'}</p>
                    <p style="margin: 4px 0;"><strong>Client:</strong> ${info.client_name || '-'}</p>
                </td>
            </tr>
        </table>
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
                    <th style="padding:15px 10px;font-size:16px;text-align:left;">NET SALARY</th>
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

function downloadSlip() {
    const element = document.getElementById('slipContent');
    if (!element) return;
    
    let filename = 'payslip.pdf';
    try {
        const htmlText = element.innerHTML;
        // Parse the name and period from the DOM structure
        const nameMatch = htmlText.match(/Name:<\/strong>\s*([^<]+)/i);
        const periodMatch = htmlText.match(/Period:\s*([^<|]+)/i);
        
        let nameStr = 'employee';
        let periodStr = '';
        
        if (nameMatch && nameMatch[1]) {
            nameStr = nameMatch[1].trim().toLowerCase().replace(/[^a-z0-9]/g, '_');
        }
        if (periodMatch && periodMatch[1]) {
            periodStr = '_' + periodMatch[1].trim().toLowerCase().replace(/[^a-z0-9]/g, '_');
        }
        
        filename = `payslip_${nameStr}${periodStr}.pdf`;
    } catch(e) {
        console.error('Error generating filename:', e);
    }
    
    // Show loading toast
    showToast('Generating PDF, please wait...', 'info');
    
    // Custom print/download optimizations for html2pdf (A5 Landscape for half-page payslip)
    const opt = {
        margin:       [8, 12, 8, 12], // top, left, bottom, right margins in mm
        filename:     filename,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
            scale: 2.5, 
            useCORS: true, 
            letterRendering: true,
            scrollY: 0,
            scrollX: 0
        },
        jsPDF:        { unit: 'mm', format: 'a5', orientation: 'landscape' }
    };
    
    html2pdf().set(opt).from(element).save().then(() => {
        showToast('Payslip downloaded successfully!', 'success');
    }).catch(err => {
        console.error('PDF Generation Error:', err);
        showToast('Failed to download PDF', 'error');
    });
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
    document.getElementById('cutoffHariKerja').value = parseFloat(hariKerja) || 0;
    document.getElementById('cutoffJamLembur').value = parseFloat(jamLembur) || 0;
    
    // Calculate default potongan if nominal_potongan is set and no manual potongan exists yet
    let finalPotongan = parseFloat(potongan) || 0;
    if (finalPotongan == 0 && window.currentAbsenceConfig && window.currentAbsenceConfig.nominal_potongan > 0) {
        const nominal = parseFloat(window.currentAbsenceConfig.nominal_potongan) || 0;
        const missingDays = 21 - (parseFloat(hariKerja) || 0);
        if (missingDays > 0) {
            finalPotongan = missingDays * nominal;
        }
    }
    document.getElementById('cutoffPotongan').value = finalPotongan;
    document.getElementById('cutoffBonus').value = parseFloat(bonus) || 0;
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

function tutupModalPKWT() { tutupSemuaModal(); }
function tutupModalPeriode() { tutupSemuaModal(); }
function tutupModalCutOff() { tutupSemuaModal(); }

// ===== EXCEL ATTENDANCE UPLOAD =====
let parsedAttendanceData = [];

async function bukaModalUploadAbsensi() {
    document.getElementById('modalUploadAbsensi').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    
    // Reset file input & UI
    document.getElementById('fileAbsensiExcel').value = '';
    document.getElementById('labelAbsensiFilename').innerText = 'No file chosen';
    document.getElementById('uploadAbsensiLogs').innerHTML = 'Waiting for file...';
    parsedAttendanceData = [];
    
    const saveBtn = document.getElementById('btnSaveUploadedAbsensi');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.style.cursor = 'not-allowed';
        saveBtn.style.opacity = '0.5';
    }

    // Load clients
    try {
        const res = await fetch(`${API_URL}/clients`);
        const configs = res.ok ? await res.json() : [];
        const clientSelect = document.getElementById('modalUploadAbsensiClient');
        
        clientSelect.innerHTML = '<option value="">-- Select Client --</option>' + configs.map(c => `
            <option value="${c.id}">${c.nama}</option>
        `).join('');

        // If client is already active in workspace, auto-select it!
        if (window.selectedClientId && configs.some(c => c.id == window.selectedClientId)) {
            clientSelect.value = window.selectedClientId;
            onAbsensiClientChanged();
        } else {
            document.getElementById('modalUploadAbsensiPeriod').innerHTML = '<option value="">-- Select Client First --</option>';
            document.getElementById('modalUploadAbsensiPeriod').disabled = true;
        }
    } catch (e) {
        console.error(e);
        showToast('Failed to load clients list', 'error');
    }
}

function tutupModalUploadAbsensi() {
    document.getElementById('modalUploadAbsensi').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

async function onAbsensiClientChanged() {
    const clientId = document.getElementById('modalUploadAbsensiClient').value;
    const periodSelect = document.getElementById('modalUploadAbsensiPeriod');
    
    if (!clientId) {
        periodSelect.innerHTML = '<option value="">-- Select Client First --</option>';
        periodSelect.disabled = true;
        return;
    }

    periodSelect.innerHTML = '<option value="">Loading periods...</option>';
    periodSelect.disabled = true;

    try {
        const res = await fetch(`${API_URL}/periods?client_id=${clientId}`);
        const periods = res.ok ? await res.json() : [];
        window.modalUploadPeriods = periods;

        if (periods.length === 0) {
            periodSelect.innerHTML = '<option value="">No periods available</option>';
            return;
        }

        periodSelect.innerHTML = '<option value="">-- Select Period --</option>' + periods.map(p => `
            <option value="${p.id}">${p.nama} (${p.status})</option>
        `).join('');
        periodSelect.disabled = false;

        // Auto-select active period if it matches
        if (typeof currentPeriodId !== 'undefined' && currentPeriodId && periods.some(p => p.id == currentPeriodId)) {
            periodSelect.value = currentPeriodId;
            onAbsensiPeriodChanged();
        }
    } catch (e) {
        console.error(e);
        periodSelect.innerHTML = '<option value="">Error loading periods</option>';
    }
}

async function onAbsensiPeriodChanged() {
    const clientId = document.getElementById('modalUploadAbsensiClient').value;
    const periodId = document.getElementById('modalUploadAbsensiPeriod').value;
    const logsDiv = document.getElementById('uploadAbsensiLogs');
    
    if (!clientId || !periodId) {
        window.currentPeriodAttendance = [];
        return;
    }

    logsDiv.innerHTML = "Fetching active employees for this period...\n";
    try {
        const res = await fetch(`${API_URL}/attendance/${periodId}?client_id=${clientId}`);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        window.currentPeriodAttendance = data;
        logsDiv.innerHTML += `Loaded ${data.length} active employees from database.\nReady to select attendance Excel file.\n`;
    } catch (e) {
        console.error(e);
        logsDiv.innerHTML += `Error loading employee roster: ${e.message || e}\n`;
    }
}

function downloadAbsensiTemplate() {
    const clientId = document.getElementById('modalUploadAbsensiClient').value;
    const periodId = document.getElementById('modalUploadAbsensiPeriod').value;

    if (!clientId || !periodId) {
        showToast('Please select Client and Period first.', 'warning');
        return;
    }

    const activePeriod = window.modalUploadPeriods?.find(p => p.id == periodId);
    if (!activePeriod) {
        showToast('Period details not found.', 'error');
        return;
    }

    const employees = window.currentPeriodAttendance || [];
    if (employees.length === 0) {
        showToast('No active employees found to generate template.', 'warning');
        return;
    }

    const month = parseInt(activePeriod.bulan);
    const year = parseInt(activePeriod.tahun);
    const daysInMonth = new Date(year, month, 0).getDate();
    
    const dayNames = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
    const templateData = [];

    employees.forEach(emp => {
        const empId = emp.employ_id || emp.nik || '';
        const empName = emp.employee_name || '';
        const workDaysConfig = parseInt(emp.employee_hari_kerja || emp.position_hari_kerja || 5);

        for (let d = 1; d <= daysInMonth; d++) {
            const dateObj = new Date(year, month - 1, d);
            const dayOfWeek = dateObj.getDay();
            const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const dayName = dayNames[dayOfWeek];
            const tglHariStr = `${dateStr} ${dayName}`;

            let jamMasuk = '08:00';
            let jamKeluar = '17:00';
            let status = 'Hadir';

            // Determine if it is a rest day
            let isRestDay = false;
            if (workDaysConfig === 5) {
                isRestDay = (dayOfWeek === 0 || dayOfWeek === 6); // Sat, Sun
            } else if (workDaysConfig === 6) {
                isRestDay = (dayOfWeek === 0); // Sun
            }

            if (isRestDay) {
                jamMasuk = '';
                jamKeluar = '';
                status = 'Off';
            }

            templateData.push({
                'Employee ID': empId,
                'Nama': empName,
                'Tgl dan Hari': tglHariStr,
                'Shift': '', // Added Shift column
                'Jam Masuk': jamMasuk,
                'Jam Keluar': jamKeluar,
                'Status': status
            });
        }
    });

    const worksheet = XLSX.utils.json_to_sheet(templateData);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Attendance Template");
    
    // Auto-fit column widths
    const max_widths = [15, 25, 20, 12, 12, 12];
    worksheet['!cols'] = max_widths.map(w => ({ wch: w }));

    const filename = `Attendance_Template_${activePeriod.nama.replace(/\s+/g, '_')}.xlsx`;
    XLSX.writeFile(workbook, filename);
    showToast('Template downloaded successfully!', 'success');
}

function parseExcelDate(val) {
    if (val instanceof Date) return val;
    if (typeof val === 'number') {
        return new Date((val - 25569) * 86400 * 1000);
    }
    if (typeof val === 'string') {
        let clean = val.replace(/[a-zA-Z]/g, '').trim();
        let parts = clean.split(/[-/]/);
        if (parts.length === 3) {
            if (parts[0].length === 4) {
                return new Date(parts[0], parts[1] - 1, parts[2]);
            } else {
                return new Date(parts[2], parts[1] - 1, parts[0]);
            }
        }
        let parsed = Date.parse(clean);
        if (!isNaN(parsed)) return new Date(parsed);
    }
    return null;
}

function handleAbsensiFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    const clientId = document.getElementById('modalUploadAbsensiClient').value;
    const periodId = document.getElementById('modalUploadAbsensiPeriod').value;
    if (!clientId || !periodId) {
        showToast('Please select Client and Period first before selecting file.', 'warning');
        return;
    }

    document.getElementById('labelAbsensiFilename').innerText = file.name;
    const logsDiv = document.getElementById('uploadAbsensiLogs');
    logsDiv.innerHTML = "Reading file...\n";

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array', cellDates: true });
            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];
            const json = XLSX.utils.sheet_to_json(worksheet);

            if (json.length === 0) {
                logsDiv.innerHTML += "Error: Excel file is empty.\n";
                return;
            }

            logsDiv.innerHTML += `Parsed ${json.length} rows from sheet "${sheetName}".\n`;
            processParsedAttendance(json);
        } catch (err) {
            console.error(err);
            logsDiv.innerHTML += `Error parsing file: ${err.message || err}\n`;
        }
    };
    reader.readAsArrayBuffer(file);
}

function processParsedAttendance(rows) {
    const logsDiv = document.getElementById('uploadAbsensiLogs');
    const employees = window.currentPeriodAttendance || [];
    if (employees.length === 0) {
        logsDiv.innerHTML += "Error: No active employees loaded in context.\n";
        return;
    }

    const finalAttendance = [];
    let logText = "";
    let validCount = 0;

    rows.forEach(row => {
        const keys = Object.keys(row);
        const empIdKey = keys.find(k => k.toLowerCase().replace(/\s+/g, '') === 'employeeid');
        const nameKey = keys.find(k => k.toLowerCase().replace(/\s+/g, '') === 'nama' || k.toLowerCase().replace(/\s+/g, '') === 'name');
        const tglKey = keys.find(k => k.toLowerCase().replace(/\s+/g, '') === 'tgldanhari' || k.toLowerCase().replace(/\s+/g, '') === 'tanggal' || k.toLowerCase().replace(/\s+/g, '') === 'date');
        const checkinKey = keys.find(k => k.toLowerCase().replace(/\s+/g, '') === 'jammasuk' || k.toLowerCase().replace(/\s+/g, '') === 'checkin');
        const checkoutKey = keys.find(k => k.toLowerCase().replace(/\s+/g, '') === 'jamkeluar' || k.toLowerCase().replace(/\s+/g, '') === 'checkout');
        const statusKey = keys.find(k => k.toLowerCase().replace(/\s+/g, '') === 'status');

        const empIdStr = String(row[empIdKey] || '').trim();
        const empNameStr = String(row[nameKey] || '').trim();
        const tglVal = row[tglKey];
        const checkin = String(row[checkinKey] || '').trim();
        const checkout = String(row[checkoutKey] || '').trim();
        const status = String(row[statusKey] || '').trim();

        if (!tglVal) return;
        const dateObj = parseExcelDate(tglVal);
        if (!dateObj) return;

        const y = dateObj.getFullYear();
        const m = String(dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dateObj.getDate()).padStart(2, '0');
        const formattedDate = `${y}-${m}-${d}`;

        // Find the internal employee ID
        let matchedEmp = employees.find(e => 
            (e.employ_id && e.employ_id === empIdStr) || 
            (e.nik && e.nik === empIdStr) || 
            (e.employee_name && e.employee_name.toLowerCase() === empNameStr.toLowerCase())
        );

        if (!matchedEmp) {
            matchedEmp = employees.find(e => e.employee_name && e.employee_name.toLowerCase().includes(empNameStr.toLowerCase()));
        }

        if (matchedEmp) {
            finalAttendance.push({
                employee_id: matchedEmp.employee_id,
                tanggal: formattedDate,
                jam_masuk: (checkin && checkin !== 'null') ? checkin : null,
                jam_keluar: (checkout && checkout !== 'null') ? checkout : null,
                status: status || 'Hadir'
            });
            validCount++;
        }
    });

    parsedAttendanceData = finalAttendance;
    logsDiv.innerHTML += `\nSuccess: Ready to apply ${validCount} daily records.`;
    
    if (typeof window.renderInlineAttendanceTable === 'function') {
        window.renderInlineAttendanceTable(rows, false);
    }

    const btn = document.getElementById('btnSaveUploadedAbsensi');
    if (btn) {
        btn.disabled = false;
        btn.style.cursor = 'pointer';
        btn.style.opacity = '1';
    }
}

async function saveUploadedAbsensi() {
    if (parsedAttendanceData.length === 0) {
        showToast('No parsed data to apply.', 'warning');
        return;
    }

    showToast('Applying attendance records...', 'info');
    try {
        const res = await fetch(`${API_URL}/attendance-logs/bulk`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ logs: parsedAttendanceData })
        });

        if (res.ok) {
            showToast('Attendance logs successfully imported!', 'success');
            tutupModalUploadAbsensi();
            // Refresh table if we are currently looking at the same period inside workspace
            const periodId = document.getElementById('modalUploadAbsensiPeriod').value;
            if (typeof currentPeriodId !== 'undefined' && window.currentPeriodId == periodId) {
                renderCutOffTable();
            }
        } else {
            const err = await res.json();
            showToast(`Failed: ${err.message || 'Error occurred'}`, 'error');
        }
    } catch (err) {
        console.error(err);
        showToast(`Error saving: ${err.message || err}`, 'error');
    }
}

window.bukaModalUploadAbsensi = bukaModalUploadAbsensi;
window.tutupModalUploadAbsensi = tutupModalUploadAbsensi;
window.onAbsensiClientChanged = onAbsensiClientChanged;
window.onAbsensiPeriodChanged = onAbsensiPeriodChanged;
window.downloadAbsensiTemplate = downloadAbsensiTemplate;
window.handleAbsensiFileSelect = handleAbsensiFileSelect;
window.saveUploadedAbsensi = saveUploadedAbsensi;
window.parseExcelDate = parseExcelDate;
window.renderCutOffTable = renderCutOffTable;

