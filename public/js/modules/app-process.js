// ===== PROCESS PAYROLL MODULE =====
// Extracted from app.js for modular monolith architecture

// ===== 7. PROSES PAYROLL BULANAN =====
async function loadActivePeriod() {
    try {
        const response = await fetch(`${API_URL}/periods`);
        const periods = await response.json();
        window.loadedPeriods = periods;
        
        // 1. Render dropdown selector on the main page
        const select = document.getElementById('selectPeriodInput');
        if (select) {
            select.innerHTML = '<option value="">-- Pilih Periode --</option>' + periods.map(p => `
                <option value="${p.id}" ${p.id == currentPeriodId ? 'selected' : ''}>${p.nama} (${p.status})</option>
            `).join('');
        }
        
        // 2. Render history list inside the popup modal
        const list = document.getElementById('periodHistoryList');
        if (list) {
            list.innerHTML = periods.map(p => `
                <div class="period-item ${p.id == currentPeriodId ? 'active' : ''}" onclick="selectPeriod(${p.id}, '${p.nama}')" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: ${p.id == currentPeriodId ? '#f0fdf4' : 'transparent'};">
                    <span style="font-weight: 500;">${p.nama}</span>
                    <span class="status-badge ${p.status && (p.status.toLowerCase().includes('open') || p.status.toLowerCase().includes('terbuka')) ? 'success' : 'danger'}" style="font-size: 11px;">${p.status}</span>
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
        statusBadge.className = 'status-badge ' + (status.toLowerCase().includes('open') || status.toLowerCase().includes('terbuka') ? 'success' : 'danger');
    }

    document.getElementById('prosesActions').style.display = 'block';
    document.getElementById('prosesEmptyState').style.display = 'none';
    renderCutOffTable();
    renderReviewGajiTable();
    tutupSemuaModal();
}

async function renderCutOffTable() {
    if(!currentPeriodId) return;
    const url = window.selectedClientId ? `${API_URL}/attendance/${currentPeriodId}?client_id=${window.selectedClientId}` : `${API_URL}/attendance/${currentPeriodId}`;
    const res = await fetch(url);
    const data = await res.json();
    document.getElementById('tabelCutOffBody').innerHTML = data.map(row => `
        <tr>
            <td>${row.employee_name}</td>
            <td>${row.hari_kerja || 0} Hari</td>
            <td>${row.jam_lembur || 0} Jam</td>
            <td>${formatRupiah(row.potongan_absensi)}</td>
            <td>${formatRupiah(row.bonus_tambahan)}</td>
            <td><button class="btn-icon btn-edit" onclick="bukaModalCutOff(${row.pkwt_id}, '${row.employee_name}', ${row.hari_kerja || 22}, ${row.jam_lembur || 0}, ${row.potongan_absensi || 0}, ${row.bonus_tambahan || 0})"><i class="fas fa-edit"></i></button></td>
        </tr>
    `).join('');
}

async function renderReviewGajiTable() {
    if(!currentPeriodId) return;
    const url = window.selectedClientId ? `${API_URL}/payroll-results/${currentPeriodId}?client_id=${window.selectedClientId}` : `${API_URL}/payroll-results/${currentPeriodId}`;
    const res = await fetch(url);
    const data = await res.json();
    const section = document.getElementById('resultSection');
    const tbody = document.getElementById('tabelReviewGajiBody');
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
                        `<button class="btn-icon" onclick="bukaSlipGaji(${row.id})" title="Lihat Slip Gaji" style="background:var(--primary-color); color:white; width:30px; height:30px;"><i class="fas fa-eye"></i></button>`
                    }
                </td>
            </tr>
        `).join('');
    } else { section.style.display = 'none'; }
}

async function generateGaji() {
    if(!currentPeriodId) return;
    showToast('Menghitung gaji...', 'info');
    const res = await fetch(`${API_URL}/generate-payroll/${currentPeriodId}`, { method: 'POST' });
    if (res.ok) { showToast('Gaji berhasil di-generate!', 'success'); renderReviewGajiTable(); }
}

async function approveGaji(id) {
    const res = await fetch(`${API_URL}/approve-payroll/${id}`, { method: 'POST' });
    if (res.ok) { showToast('Gaji disetujui!', 'success'); renderReviewGajiTable(); }
}

// ===== UTILS & MODAL CLOSING =====

// ===== SLIP GAJI =====

async function bukaSlipGaji(id) {
    try {
        const response = await fetch(`${API_URL}/slip-details/${id}`);
        const data = await response.json();

        document.getElementById('slipClientName').innerText = data.info.client_name;
        document.getElementById('slipPeriod').innerText = data.info.period_name;
        document.getElementById('slipEmployeeName').innerText = data.info.employee_name;
        document.getElementById('slipPosition').innerText = data.info.position_name;
        document.getElementById('slipTHP').innerText = formatRupiah(data.info.take_home_pay);

        const eList = document.getElementById('slipEarningsList');
        const dList = document.getElementById('slipDeductionsList');

        eList.innerHTML = data.earnings.map(e => `
            <div style="display:flex; justify-content:space-between;">
                <span>${e.nama}</span>
                <span>${formatRupiah(e.nilai)}</span>
            </div>
        `).join('');

        dList.innerHTML = data.deductions.map(d => `
            <div style="display:flex; justify-content:space-between;">
                <span>${d.nama}</span>
                <span>${formatRupiah(d.nilai)}</span>
            </div>
        `).join('');

        document.getElementById('modalSlip').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    } catch (err) {
        console.error('Error loading slip details:', err);
    }
}

function tutupModalSlip() {
    document.getElementById('modalSlip').style.display = 'none';
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
                showToast('Periode baru berhasil dibuka!', 'success');
                loadActivePeriod();
            } else {
                showToast('Gagal membuka periode baru!', 'error');
            }
        });
    }

    // Form Cut-Off submit handler
    if (document.getElementById('formCutOff')) {
        document.getElementById('formCutOff').addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!currentPeriodId) {
                showToast('Periode aktif tidak ditemukan!', 'error');
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
                    showToast('Data cut-off berhasil disimpan!', 'success');
                } else {
                    showToast('Gagal menyimpan data cut-off!', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Gagal menyimpan data cut-off!', 'error');
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

// Global Closing Handlers
function tutupModal() { tutupSemuaModal(); }
function tutupModalSkema() { tutupSemuaModal(); }
function tutupModalKomponen() { tutupSemuaModal(); }
function tutupModalPajak() { tutupSemuaModal(); }
function tutupModalSetup() { tutupSemuaModal(); }
function tutupModalPKWT() { tutupSemuaModal(); }
function tutupModalPeriode() { tutupSemuaModal(); }
function tutupModalCutOff() { tutupSemuaModal(); }

