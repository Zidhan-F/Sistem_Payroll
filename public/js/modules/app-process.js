// ===== PROCESS PAYROLL MODULE =====
// Extracted from app.js for modular monolith architecture

// ===== 7. PROSES PAYROLL BULANAN =====
async function autoCreatePeriod(month, year) {
    if (!window.selectedClientId) return;
    const data = {
        client_id: parseInt(window.selectedClientId),
        bulan: parseInt(month),
        tahun: parseInt(year)
    };
    try {
        const res = await fetch(`${API_URL}/periods`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (res.ok) {
            const url = `${API_URL}/periods?client_id=${window.selectedClientId}`;
            const response = await fetch(url);
            const periods = await response.json();
            window.loadedPeriods = periods;
            
            const activePeriod = periods.find(p => parseInt(p.bulan) === month && parseInt(p.tahun) === year);
            if (activePeriod) {
                currentPeriodId = activePeriod.id;
                window.currentPeriodId = activePeriod.id;
                selectPeriod(currentPeriodId, activePeriod.nama);
            }
        } else {
            console.error('Failed to auto-create period');
        }
    } catch (err) {
        console.error(err);
    }
}

async function loadActivePeriod() {
    try {
        const monthSelect = document.getElementById('processMonthSelect');
        const yearSelect = document.getElementById('processYearSelect');
        
        const d = new Date();
        const currentMonth = d.getMonth() + 1;
        const currentYear = d.getFullYear();
        
        if (monthSelect && !monthSelect.value) {
            monthSelect.value = currentMonth;
        }
        if (yearSelect && !yearSelect.value) {
            yearSelect.value = currentYear;
        }

        const selMonth = monthSelect ? parseInt(monthSelect.value) : currentMonth;
        const selYear = yearSelect ? parseInt(yearSelect.value) : currentYear;

        const url = window.selectedClientId ? `${API_URL}/periods?client_id=${window.selectedClientId}` : `${API_URL}/periods`;
        const response = await fetch(url);
        const periods = await response.json();
        window.loadedPeriods = periods;
        
        const list = document.getElementById('periodHistoryList');
        if (list) {
            list.innerHTML = periods.map(p => `
                <div class="period-item ${p.id == currentPeriodId ? 'active' : ''}" onclick="selectPeriod(${p.id}, '${p.nama}')" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: ${p.id == currentPeriodId ? '#f0fdf4' : 'transparent'};">
                    <span style="font-weight: 500;">${p.nama}</span>
                    <span class="status-badge ${p.status && (p.status.toLowerCase().includes('open') || p.status.toLowerCase().includes('terbuka') || p.status.toLowerCase().includes('active')) ? 'success' : 'danger'}" style="font-size: 11px;">${p.status}</span>
                </div>
            `).join('');
        }
        
        let activePeriod = periods.find(p => parseInt(p.bulan) === selMonth && parseInt(p.tahun) === selYear);
        
        if (activePeriod) {
            currentPeriodId = activePeriod.id;
            window.currentPeriodId = activePeriod.id;
            
            if (monthSelect) monthSelect.value = activePeriod.bulan;
            if (yearSelect) yearSelect.value = activePeriod.tahun;
            
            selectPeriod(currentPeriodId, activePeriod.nama);
        } else {
            if (window.selectedClientId) {
                await autoCreatePeriod(selMonth, selYear);
            } else {
                currentPeriodId = null;
                window.currentPeriodId = null;
                selectPeriod(null, '');
            }
        }
    } catch (err) { console.error(err); }
}

function selectPeriod(id, name) {
    currentPeriodId = id;
    window.currentPeriodId = id;
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

async function onProcessPeriodChange() {
    const monthSelect = document.getElementById('processMonthSelect');
    const yearSelect = document.getElementById('processYearSelect');
    if (!monthSelect || !yearSelect || !window.loadedPeriods) return;
    
    const selectedMonth = parseInt(monthSelect.value);
    const selectedYear = parseInt(yearSelect.value);
    
    const matchedPeriod = window.loadedPeriods.find(p => parseInt(p.bulan) === selectedMonth && parseInt(p.tahun) === selectedYear);
    
    if (matchedPeriod) {
        selectPeriod(matchedPeriod.id, matchedPeriod.nama);
    } else {
        if (window.selectedClientId) {
            showToast('Membuka periode baru secara otomatis...', 'info');
            await autoCreatePeriod(selectedMonth, selectedYear);
        } else {
            currentPeriodId = null;
            window.currentPeriodId = null;
            document.getElementById('prosesActions').style.display = 'none';
            document.getElementById('prosesEmptyState').style.display = 'block';
            
            if (document.getElementById('resultSection')) document.getElementById('resultSection').style.display = 'none';
            if (document.getElementById('resultsEmptyState')) document.getElementById('resultsEmptyState').style.display = 'block';
        }
    }
}
window.onProcessPeriodChange = onProcessPeriodChange;

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
            tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
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
            tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 20px; color: #64748b;"><i class="fas fa-info-circle" style="margin-right: 8px;"></i>Tidak ada data karyawan aktif untuk periode ini.</td></tr>`;
            return;
        }
        tbody.innerHTML = data.map(row => {
            const hariKerja = parseFloat(row.hari_kerja) || 0;
            const rapelHari = parseInt(row.rapel_hari_kerja) || 0;
            const displayHari = hariKerja > 0 ? `${hariKerja} Days` : (rapelHari > 0 ? `${rapelHari} Days (Rapel)` : '0 Days');
            const jamLembur = parseFloat(row.jam_lembur) || 0;
            const earlyArrival = parseInt(row.early_arrival_minutes) || 0;
            const earlyArrivalHours = earlyArrival > 0 ? Math.round(earlyArrival / 60) : 0;
            const potongan = parseFloat(row.potongan_absensi) || 0;
            const bonus = parseFloat(row.bonus_tambahan) || 0;
            return `
            <tr>
                <td>${row.employee_name} <span class="status-badge info" style="font-size:10px; margin-left:5px; padding:2px 6px;">${row.tipe_perjanjian || 'PKWT'}</span></td>
                <td>${displayHari}</td>
                <td>${jamLembur > 0 ? jamLembur + ' Hours' : '-'}</td>
                <td>${earlyArrivalHours > 0 ? earlyArrivalHours + ' Hours' : '-'}</td>
                <td>${formatRupiah(potongan)}</td>
                <td>${formatRupiah(bonus)}</td>
                <td><button class="btn-icon btn-edit" onclick="bukaModalCutOff(${row.pkwt_id}, '${row.employee_name}', ${hariKerja}, ${jamLembur}, ${potongan}, ${bonus}, ${earlyArrivalHours})"><i class="fas fa-edit"></i></button></td>
            </tr>`;
        }).join('');
    } catch (err) { 
        console.error(err); 
        const tbody = document.getElementById('tabelCutOffBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 20px; color: #ef4444;"><i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>Failed to load data cut-off: ${err.message || err}</td></tr>`;
        }
    }
}

async function renderReviewGajiTable() {
    if(!currentPeriodId) return;
    try {
        const tbody = document.getElementById('tabelReviewGajiBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="22" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        const url = window.selectedClientId ? `${API_URL}/payroll-results/${currentPeriodId}?client_id=${window.selectedClientId}` : `${API_URL}/payroll-results/${currentPeriodId}`;
        const res = await fetch(url);
        if (!res.ok) {
            throw new Error('Failed to load payroll results (HTTP ' + res.status + ')');
        }
        const data = await res.json();
        const section = document.getElementById('resultSection');
        if (!tbody) return;

        let displayData = data;
        const role = typeof getCurrentRole === 'function' ? getCurrentRole() : 'admin';
        if (role === 'staff') {
            displayData = data.filter(row => {
                const currentFullName = (currentUser && currentUser.full_name) ? currentUser.full_name.toLowerCase().trim() : '';
                const currentUsername = (currentUser && currentUser.username) ? currentUser.username.toLowerCase().trim() : '';
                const empName = (row.employee_name || '').toLowerCase().trim();
                return empName === currentFullName || empName === currentUsername;
            });
            // Staff can view payslip status whether Approved, Pending, or Hold
            displayData = displayData.filter(row => ['Approved', 'Pending', 'Hold'].includes(row.status_approval));
        }

        document.querySelectorAll('#selectAllReviewGaji').forEach(input => {
            const th = input.closest('th');
            if (th) th.style.display = (role === 'staff') ? 'none' : '';
        });
        document.querySelectorAll('#btnApproveSelectedGaji').forEach(btn => {
            btn.style.display = (role === 'staff') ? 'none' : '';
        });

        if (displayData.length > 0) {
            section.style.display = 'block';
            tbody.innerHTML = displayData.map(row => {
                const gp = parseFloat(row.gaji_pokok || 0);
                const ot = parseFloat(row.lembur_pay || 0);
                const ea = parseFloat(row.early_arrival_pay || 0);
                
                // Parse raw_components for Rapel component
                let rapelVal = 0;
                if (row.raw_components) {
                    try {
                        const comps = JSON.parse(row.raw_components);
                        comps.forEach(c => {
                            if (c.nama && c.nama.toLowerCase().includes('rapel')) {
                                rapelVal += parseFloat(c.nilai || 0);
                            }
                        });
                    } catch(e) {
                        console.error('Error parsing raw_components:', e);
                    }
                }

                const totalPendapatan = parseFloat(row.total_pendapatan || 0);
                const lainBonus = Math.max(0, totalPendapatan - gp - ot - ea - rapelVal);

                const potAbsen = parseFloat(row.potongan_absen || 0);
                const bpjsKes = parseFloat(row.bpjs_kes_karyawan || 0);
                const bpjsJht = parseFloat(row.bpjs_jht_karyawan || 0);
                const bpjsJp = parseFloat(row.bpjs_jp_karyawan || 0);
                const bpjsKaryawan = bpjsKes + bpjsJht + bpjsJp;
                const pph21 = parseFloat(row.pph21 || 0);
                const totalPotongan = parseFloat(row.total_potongan || 0);
                const potLain = Math.max(0, totalPotongan - potAbsen - bpjsKaryawan - pph21);

                return `
                    <tr>
                        <td style="text-align: center; vertical-align: middle;">
                            <input type="checkbox" class="review-gaji-checkbox" data-id="${row.id}" data-employee-name="${row.employee_name}" data-scheme="${row.scheme_name || ''}" data-net-salary="${row.take_home_pay}" data-is-rapel="${row.is_new_hire_rapel ? 'true' : 'false'}" ${(row.status_approval === 'Approved' || row.status_approval === 'Hold') ? 'disabled' : ''} ${row.status_approval === 'Approved' ? 'checked' : ''} style="transform: scale(1.2); cursor: pointer;">
                        </td>
                        <td>${row.employee_name} <span class="status-badge info" style="font-size:10px; margin-left:5px; padding:2px 6px;">${row.tipe_perjanjian || 'PKWT'}</span>${row.status_approval === 'Hold' ? (row.is_new_hire_rapel ? '<br><span style="font-size:10px; color:#ef4444; font-weight:600;"><i class="fas fa-exclamation-circle"></i> Ditunda (Gaji dirapel ke bulan depan)</span>' : '<br><span style="font-size:10px; color:#ef4444; font-weight:600;"><i class="fas fa-exclamation-circle"></i> Ditunda (Absen setelah cut-off)</span>') : ''}${row.is_new_hire_rapel ? ` <span class="status-badge warning" style="font-size:10px; margin-left:5px; padding:2px 6px; background-color:#fff3cd; color:#856404; border:1px solid #ffeeba; font-weight:600; border-radius:4px;">Dirapel ke ${row.rapel_payout_period || ''}</span>` : ''}</td>
                        <td>${row.client_name || '-'}</td>
                        <td>${row.division_name || '-'}</td>
                        <td>${row.department_name || '-'}</td>
                        <td>${row.position_name || '-'}</td>
                        <td>${row.scheme_name || '-'}</td>
                        <td>${formatRupiah(gp)}</td>
                        <td>${formatRupiah(ot)}</td>
                        <td>${formatRupiah(ea)}</td>
                        <td>${formatRupiah(rapelVal)}</td>
                        <td>${formatRupiah(lainBonus)}</td>
                        <td style="font-weight:600;">${formatRupiah(totalPendapatan)}</td>
                        <td>${formatRupiah(potAbsen)}</td>
                        <td>${formatRupiah(bpjsKes)}</td>
                        <td>${formatRupiah(bpjsJht)}</td>
                        <td>${formatRupiah(bpjsJp)}</td>
                        <td>${formatRupiah(pph21)}</td>
                        <td>${formatRupiah(potLain)}</td>
                        <td style="font-weight:600;">${formatRupiah(totalPotongan)}</td>
                        <td style="font-weight:700; color:#2e7d32;">${formatRupiah(row.take_home_pay)}</td>
                        <td><span class="status-badge ${row.status_approval === 'Approved' ? 'success' : (row.status_approval === 'Hold' ? 'danger' : 'warning')}">${row.status_approval}</span></td>
                        <td>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <button class="btn-icon btn-neutral" onclick="bukaSlipGaji(${row.id})" title="View Salary Slip / Details" style="width:30px; height:30px; border-radius:6px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-eye"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            // Hide the checkbox cells for staff role to avoid column shifting
            if (role === 'staff') {
                tbody.querySelectorAll('.review-gaji-checkbox').forEach(input => {
                    const td = input.closest('td');
                    if (td) td.style.display = 'none';
                });
            }
        } else { 
            section.style.display = 'block';
            tbody.innerHTML = `<tr><td colspan="22" style="text-align:center; padding: 20px; color:#7f8c8d;"><i class="fas fa-info-circle" style="margin-right: 8px;"></i>No data yet gaji yang di-generate untuk periode ini.</td></tr>`;
        }
    } catch (err) { 
        console.error(err); 
        const tbody = document.getElementById('tabelReviewGajiBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="22" style="text-align: center; padding: 20px; color: #ef4444;"><i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>Gagal memuat hasil kalkulasi: ${err.message || err}</td></tr>`;
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
        showToast('Select at least one salary record with Pending status!', 'warning');
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
        confirmed = await showConfirm(confirmMsg, 'Confirm Approval', 'Yes, Approve', 'Cancel', 'primary');
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
            showToast('All selected salary data approved successfully!', 'success');
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

async function downloadSalaryTemplate() {
    if (!currentPeriodId) {
        showToast('Please select a period first.', 'warning');
        return;
    }
    if (!window.selectedClientId) {
        showToast('Please select a client first.', 'warning');
        return;
    }

    showToast('Generating manual salary template...', 'info');

    try {
        const attUrl = `${API_URL}/attendance/${currentPeriodId}?client_id=${window.selectedClientId}`;
        const resultsUrl = `${API_URL}/payroll-results/${currentPeriodId}?client_id=${window.selectedClientId}`;
        
        const [attRes, resultsRes] = await Promise.all([
            fetch(attUrl),
            fetch(resultsUrl)
        ]);
        
        if (!attRes.ok) {
            throw new Error('Failed to load employee list');
        }
        const attData = await attRes.json();
        const resultsData = resultsRes.ok ? await resultsRes.json() : [];

        if (!attData || attData.length === 0) {
            showToast('No active employees found for this client.', 'warning');
            return;
        }

        // Map the employee list to template rows
        const formatted = attData.map((row) => {
            // Find matched payroll result for pre-fill
            const matched = resultsData.find(r => r.pkwt_id == row.pkwt_id);
            
            let gp = parseFloat(row.gaji_pokok) || 0;
            let otPay = 0;
            let eaPay = 0;
            let rapel = 0;
            let bonus = parseFloat(row.bonus_tambahan) || 0;
            let potAbsen = parseFloat(row.potongan_absensi) || 0;
            let potLain = 0;

            if (matched) {
                gp = parseFloat(matched.gaji_pokok || 0);
                otPay = parseFloat(matched.lembur_pay || 0);
                eaPay = parseFloat(matched.early_arrival_pay || 0);
                
                // Parse rapel from raw_components
                if (matched.raw_components) {
                    try {
                        const comps = JSON.parse(matched.raw_components);
                        comps.forEach(c => {
                            if (c.nama && c.nama.toLowerCase().includes('rapel')) {
                                rapel += parseFloat(c.nilai || 0);
                            }
                        });
                    } catch(e) {}
                }
                
                const totalPendapatan = parseFloat(matched.total_pendapatan || 0);
                bonus = Math.max(0, totalPendapatan - gp - otPay - eaPay - rapel);

                potAbsen = parseFloat(matched.potongan_absen || 0);
                const bpjsKaryawan = parseFloat(matched.bpjs_kes_karyawan || 0) + parseFloat(matched.bpjs_jht_karyawan || 0) + parseFloat(matched.bpjs_jp_karyawan || 0);
                const totalPotongan = parseFloat(matched.total_potongan || 0);
                potLain = Math.max(0, totalPotongan - potAbsen - bpjsKaryawan - parseFloat(matched.pph21 || 0));
            }

            return {
                'PKWT ID': row.pkwt_id,
                'Employee Name': row.employee_name,
                'Employee ID (NIK)': row.employ_id || '',
                'Working Days': parseFloat(row.hari_kerja) || 0,
                'Overtime Hours': parseFloat(row.jam_lembur) || 0,
                'Early Arrival Hours': row.early_arrival_minutes ? Math.round(row.early_arrival_minutes / 60) : 0,
                'Basic Salary (Gaji Pokok)': gp,
                'Rapel': rapel,
                'Bonus / Lainnya': bonus,
                'Absence Deduction (Potongan Absen)': potAbsen,
                'Potongan Lain': potLain
            };
        });

        const ws = XLSX.utils.json_to_sheet(formatted);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Salary Upload Template");

        // Set column widths
        const wscols = [
            { wch: 10 }, // PKWT ID
            { wch: 25 }, // Employee Name
            { wch: 18 }, // Employee ID (NIK)
            { wch: 15 }, // Working Days
            { wch: 15 }, // Overtime Hours
            { wch: 18 }, // Early Arrival Hours
            { wch: 25 }, // Basic Salary (Gaji Pokok)
            { wch: 15 }, // Rapel
            { wch: 20 }, // Bonus / Lainnya
            { wch: 30 }, // Absence Deduction (Potongan Absen)
            { wch: 15 }  // Potongan Lain
        ];
        ws['!cols'] = wscols;

        // Save file
        const monthSelect = document.getElementById('processMonthSelect');
        const yearSelect = document.getElementById('processYearSelect');
        const periodStr = monthSelect && yearSelect ? `${monthSelect.options[monthSelect.selectedIndex].text}_${yearSelect.value}` : currentPeriodId;
        const filename = `manual_salary_template_${periodStr}.xlsx`;
        XLSX.writeFile(wb, filename);

        showToast('Template downloaded successfully!', 'success');
    } catch (err) {
        console.error(err);
        showToast('Failed to generate template: ' + err.message, 'error');
    }
}

function bukaModalUploadManualSalary() {
    window.uploadedManualSalaryData = null;
    const fileInput = document.getElementById('fileManualSalaryExcel');
    if (fileInput) fileInput.value = '';
    
    const labelFilename = document.getElementById('labelManualSalaryFilename');
    if (labelFilename) labelFilename.innerText = 'No file chosen';
    
    const btnSave = document.getElementById('btnSaveManualSalary');
    if (btnSave) {
        btnSave.disabled = true;
        btnSave.style.cursor = 'not-allowed';
        btnSave.style.opacity = '0.5';
    }

    const dropzone = document.getElementById('dropzoneManualSalaryExcel');
    if (dropzone) {
        dropzone.style.background = 'rgba(41, 128, 185, 0.08)';
    }
    
    document.getElementById('modalUploadManualSalary').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function tutupModalUploadManualSalary() {
    document.getElementById('modalUploadManualSalary').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function handleManualSalaryDragOver(e) {
    e.preventDefault();
    const dropzone = document.getElementById('dropzoneManualSalaryExcel');
    if (dropzone) {
        dropzone.style.background = 'rgba(41, 128, 185, 0.15)';
    }
}

function handleManualSalaryDragLeave(e) {
    e.preventDefault();
    const dropzone = document.getElementById('dropzoneManualSalaryExcel');
    if (dropzone) {
        dropzone.style.background = 'rgba(41, 128, 185, 0.08)';
    }
}

function handleManualSalaryDrop(e) {
    e.preventDefault();
    const dropzone = document.getElementById('dropzoneManualSalaryExcel');
    if (dropzone) {
        dropzone.style.background = 'rgba(41, 128, 185, 0.08)';
    }
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        processManualSalaryFile(files[0]);
    }
}

function handleManualSalaryFileSelect(e) {
    const file = e.target.files[0];
    if (file) {
        processManualSalaryFile(file);
    }
}

function processManualSalaryFile(file) {
    showToast('Reading Excel file...', 'info');
    
    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            
            const jsonRows = XLSX.utils.sheet_to_json(worksheet);
            if (!jsonRows || jsonRows.length === 0) {
                showToast('The uploaded sheet is empty.', 'error');
                return;
            }

            const firstRow = jsonRows[0];
            if (!firstRow.hasOwnProperty('PKWT ID')) {
                showToast('Invalid template: Column "PKWT ID" is missing.', 'error');
                return;
            }

            // Map rows
            const payload = jsonRows.map(row => {
                return {
                    pkwt_id: parseInt(row['PKWT ID']),
                    employee_name: row['Employee Name'],
                    working_days: parseFloat(row['Working Days']) || 0,
                    overtime_hours: parseFloat(row['Overtime Hours']) || 0,
                    early_arrival_hours: parseFloat(row['Early Arrival Hours']) || 0,
                    gaji_pokok: parseFloat(row['Basic Salary (Gaji Pokok)']) || 0,
                    rapel: parseFloat(row['Rapel']) || 0,
                    bonus_tambahan: parseFloat(row['Bonus / Lainnya']) || 0,
                    potongan_absen: parseFloat(row['Absence Deduction (Potongan Absen)']) || 0,
                    potongan_lain: parseFloat(row['Potongan Lain']) || 0
                };
            });

            window.uploadedManualSalaryData = payload;
            
            const labelFilename = document.getElementById('labelManualSalaryFilename');
            if (labelFilename) {
                labelFilename.innerText = file.name;
            }
            
            const btnSave = document.getElementById('btnSaveManualSalary');
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.style.cursor = 'pointer';
                btnSave.style.opacity = '1';
            }
            
            showToast('File loaded. Click "Apply & Save Salary" to finish.', 'success');
        } catch (err) {
            console.error(err);
            showToast('Failed to process Excel: ' + err.message, 'error');
        }
    };
    reader.readAsArrayBuffer(file);
}

async function saveManualSalary() {
    if (!window.uploadedManualSalaryData) {
        showToast('Please select or drop a file first.', 'warning');
        return;
    }

    showToast('Uploading and calculating salaries...', 'info');
    
    try {
        const res = await fetch(`${API_URL}/upload-manual-payroll`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-User-Action': typeof currentUser !== 'undefined' && currentUser ? currentUser.username : 'Admin'
            },
            body: JSON.stringify({
                period_id: currentPeriodId,
                client_id: window.selectedClientId,
                rows: window.uploadedManualSalaryData
            })
        });

        if (res.ok) {
            showToast('Salaries uploaded and calculated successfully!', 'success');
            tutupModalUploadManualSalary();
            renderCutOffTable();
            renderReviewGajiTable();
        } else {
            const errData = await res.json();
            const errMsg = errData.messages?.error || errData.message || 'Failed to upload manual salaries.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Gagal',
                    text: errMsg,
                    confirmButtonColor: '#3498db'
                });
            } else {
                showToast(errMsg, 'error');
            }
        }
    } catch(err) {
        console.error(err);
        showToast('Failed to save salaries: ' + err.message, 'error');
    }
}

window.downloadSalaryTemplate = downloadSalaryTemplate;
window.bukaModalUploadManualSalary = bukaModalUploadManualSalary;
window.tutupModalUploadManualSalary = tutupModalUploadManualSalary;
window.handleManualSalaryDragOver = handleManualSalaryDragOver;
window.handleManualSalaryDragLeave = handleManualSalaryDragLeave;
window.handleManualSalaryDrop = handleManualSalaryDrop;
window.handleManualSalaryFileSelect = handleManualSalaryFileSelect;
window.saveManualSalary = saveManualSalary;

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
        
        const formatMoney = (val) => {
            const num = parseFloat(val || 0);
            return 'Rp ' + Math.round(num).toLocaleString('id-ID');
        };

        // Format the rows for Excel
        const formatted = data.map((row, index) => {
            const dob = [row.tempat_lahir, row.tanggal_lahir].filter(Boolean).join(', ') || '-';
            
            const gp = parseFloat(row.gaji_pokok || 0);
            const ot = parseFloat(row.lembur_pay || 0);
            const ea = parseFloat(row.early_arrival_pay || 0);

            // Parse raw_components for Rapel component
            let rapelVal = 0;
            if (row.raw_components) {
                try {
                    const comps = JSON.parse(row.raw_components);
                    comps.forEach(c => {
                        if (c.nama && c.nama.toLowerCase().includes('rapel')) {
                            rapelVal += parseFloat(c.nilai || 0);
                        }
                    });
                } catch(e) {
                    console.error('Error parsing raw_components:', e);
                }
            }

            const totalPendapatan = parseFloat(row.total_pendapatan || 0);
            const lainBonus = Math.max(0, totalPendapatan - gp - ot - ea - rapelVal);

            const potAbsen = parseFloat(row.potongan_absen || 0);
            const bpjsKes = parseFloat(row.bpjs_kes_karyawan || 0);
            const bpjsJht = parseFloat(row.bpjs_jht_karyawan || 0);
            const bpjsJp = parseFloat(row.bpjs_jp_karyawan || 0);
            const bpjsKaryawan = bpjsKes + bpjsJht + bpjsJp;
            const pph21 = parseFloat(row.pph21 || 0);
            const totalPotongan = parseFloat(row.total_potongan || 0);
            const potLain = Math.max(0, totalPotongan - potAbsen - bpjsKaryawan - pph21);

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
                'Min. Wage (UMP/UMK)': row.min_wage ? formatMoney(row.min_wage) : '0',
                'Basic Salary (Gaji Pokok)': formatMoney(gp),
                'Overtime Pay (Lembur)': formatMoney(ot),
                'Early Arrival Pay': formatMoney(ea),
                'Rapel': formatMoney(rapelVal),
                'Bonus / Lainnya': formatMoney(lainBonus),
                'Total Income (Pendapatan)': formatMoney(totalPendapatan),
                'Absence Deduction (Potongan Absen)': formatMoney(potAbsen),
                'BPJS Kes (Karyawan)': formatMoney(bpjsKes),
                'BPJS JHT (Karyawan)': formatMoney(bpjsJht),
                'BPJS JP (Karyawan)': formatMoney(bpjsJp),
                'Tax (PPh21)': formatMoney(pph21),
                'Potongan Lain': formatMoney(potLain),
                'Total Deductions (Potongan)': formatMoney(totalPotongan),
                'Net Salary (Take Home Pay)': formatMoney(row.take_home_pay),
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
            {wch: 20},  // Min. Wage (UMP/UMK)
            {wch: 25},  // Basic Salary (Gaji Pokok)
            {wch: 20},  // Overtime Pay (Lembur)
            {wch: 20},  // Early Arrival Pay
            {wch: 20},  // Rapel
            {wch: 20},  // Bonus / Lainnya
            {wch: 25},  // Total Income (Pendapatan)
            {wch: 28},  // Absence Deduction (Potongan Absen)
            {wch: 20},  // BPJS Kes (Karyawan)
            {wch: 20},  // BPJS JHT (Karyawan)
            {wch: 20},  // BPJS JP (Karyawan)
            {wch: 15},  // Tax (PPh21)
            {wch: 18},  // Potongan Lain
            {wch: 25},  // Total Deductions (Potongan)
            {wch: 25},  // Net Salary (Take Home Pay)
            {wch: 12}   // Status
        ];
        ws['!cols'] = wscols;
        
        // Get period name from current period ID
        let periodText = 'Report';
        if (currentPeriodId && window.loadedPeriods) {
            const periodObj = window.loadedPeriods.find(p => p.id == currentPeriodId);
            if (periodObj) {
                periodText = periodObj.nama.trim().replace(/\s+/g, '_');
            }
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
        
        // Extract values
        let basicSalary = parseFloat(info.gaji_pokok) || 0;
        let transportAlw = 0;
        let specialAlw = 0;
        let overtime = 0;
        let earlyArrival = 0;
        let taxAllowance = 0;
        let taxAllowanceLabel = '';
        let rapelComponents = [];
        
        if (data.earnings && data.earnings.length > 0) {
            data.earnings.forEach(e => {
                const nameLower = e.nama.toLowerCase();
                if (nameLower.includes('gaji pokok') || nameLower.includes('basic salary')) {
                    basicSalary = parseFloat(e.nilai) || 0;
                } else if (nameLower.includes('transport')) {
                    transportAlw += parseFloat(e.nilai) || 0;
                } else if (nameLower.includes('lembur') || nameLower.includes('overtime')) {
                    overtime += parseFloat(e.nilai) || 0;
                } else if (nameLower.includes('early arrival')) {
                    earlyArrival += parseFloat(e.nilai) || 0;
                } else if (nameLower.includes('tunjangan pajak') || nameLower.includes('tax allowance')) {
                    taxAllowance = parseFloat(e.nilai) || 0;
                    taxAllowanceLabel = e.nama;
                } else if (nameLower.includes('rapel')) {
                    rapelComponents.push({
                        nama: e.nama,
                        nilai: parseFloat(e.nilai) || 0
                    });
                } else {
                    specialAlw += parseFloat(e.nilai) || 0;
                }
            });
        }
        
        const jkk = parseFloat(info.bpjs_jkk_perusahaan) || 0;
        const jkm = parseFloat(info.bpjs_jkm_perusahaan) || 0;
        const jhtc = parseFloat(info.bpjs_jht_perusahaan) || 0;
        const bpjsCompany = parseFloat(info.bpjs_kes_perusahaan) || 0;
        const jpCompany = parseFloat(info.bpjs_jp_perusahaan) || 0;
        
        const tax = parseFloat(info.pph21) || 0;
        const jhte = parseFloat(info.bpjs_jht_karyawan) || 0;
        const bpjsEmployee = parseFloat(info.bpjs_kes_karyawan) || 0;
        const jpEmployee = parseFloat(info.bpjs_jp_karyawan) || 0;
        
        let iuranWajib = 0;
        let shopDeduction = 0;
        let absenceDeduction = 0;
        
        if (data.deductions && data.deductions.length > 0) {
            data.deductions.forEach(d => {
                const nameLower = d.nama.toLowerCase();
                if (nameLower.includes('jht') || nameLower.includes('kesehatan') || nameLower.includes('bpjs') || nameLower.includes('pajak') || nameLower.includes('pph')) {
                    return;
                }
                if (nameLower.includes('absen') || nameLower.includes('absence')) {
                    absenceDeduction += parseFloat(d.nilai) || 0;
                } else if (nameLower.includes('wajib') || nameLower.includes('iuran')) {
                    iuranWajib += parseFloat(d.nilai) || 0;
                } else {
                    shopDeduction += parseFloat(d.nilai) || 0;
                }
            });
        }

        let taxLabel = 'TAX (PPH 21)';
        if (data.deductions && data.deductions.length > 0) {
            const taxItem = data.deductions.find(d => {
                const nameLower = d.nama.toLowerCase();
                return nameLower.includes('pajak') || nameLower.includes('pph') || nameLower.includes('tax');
            });
            if (taxItem) {
                taxLabel = taxItem.nama;
            }
        }

        const totalRapel = rapelComponents.reduce((sum, r) => sum + r.nilai, 0);
        const totalIncome = basicSalary + transportAlw + specialAlw + overtime + earlyArrival + taxAllowance + totalRapel;
        const totalDeduction = iuranWajib + shopDeduction + absenceDeduction + tax + jhte + bpjsEmployee + jpEmployee;
        const totalCompanyBpjs = jkk + jkm + jhtc + bpjsCompany + jpCompany;
        const hasBpjs = jkk > 0 || jkm > 0 || jhtc > 0 || bpjsCompany > 0 || jpCompany > 0 || bpjsEmployee > 0 || jhte > 0 || jpEmployee > 0;
        const brutoPajak = totalIncome - absenceDeduction + bpjsCompany + jkk + jkm;

        document.getElementById('slipContent').innerHTML = `
        <style>
            #slipContent table tbody tr:hover {
                background: transparent !important;
            }
            #slipContent table tbody tr:hover td:first-child {
                box-shadow: none !important;
            }
        </style>
        <div style="font-family: Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.4; padding: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px;">
                <span style="font-size: 16px; font-weight: bold; text-transform: uppercase;">
                    ${info.client_name || info.nama_klien || 'PT Duta Karya Sukses Nusantara'}
                </span>
                ${hasBpjs ? `<a href="javascript:void(0)" onclick="bukaDetailBpjsModal('pkwt', ${id})" class="no-pdf" style="font-size: 12px; color: #f39c12; font-weight: bold; text-decoration: none;"><i class="fas fa-calculator"></i> Detail Perhitungan BPJS</a>` : ''}
            </div>
            
            <table style="width: 100%; border: none; margin-bottom: 20px; font-size: 12px; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                        <table style="border: none; border-collapse: collapse;">
                            <tr><td style="padding: 2px 0; font-weight: bold; width: 80px;">PERIOD</td><td style="padding: 2px 0; width: 10px;">:</td><td style="padding: 2px 0;">${info.period_name || info.periode || '-'}</td></tr>
                            <tr><td style="padding: 2px 0; font-weight: bold;">NAME</td><td style="padding: 2px 0;">:</td><td style="padding: 2px 0;">${info.employee_name || '-'}</td></tr>
                            <tr><td style="padding: 2px 0; font-weight: bold;">NIK</td><td style="padding: 2px 0;">:</td><td style="padding: 2px 0;">${info.employ_id || info.nik_karyawan || '-'}</td></tr>
                            <tr><td style="padding: 2px 0; font-weight: bold;">PTKP</td><td style="padding: 2px 0;">:</td><td style="padding: 2px 0;">${info.ptkp_status || '-'}</td></tr>
                        </table>
                    </td>
                    <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                        <table style="border: none; border-collapse: collapse; margin-left: auto;">
                            <tr><td style="padding: 2px 0; font-weight: bold; width: 80px;">POSITION</td><td style="padding: 2px 0; width: 10px;">:</td><td style="padding: 2px 0;">${info.position_name || '-'}</td></tr>
                            <tr><td style="padding: 2px 0; font-weight: bold;">DEPT</td><td style="padding: 2px 0;">:</td><td style="padding: 2px 0;">${info.department_name || '-'}</td></tr>
                            <tr><td style="padding: 2px 0; font-weight: bold;">NPWP</td><td style="padding: 2px 0;">:</td><td style="padding: 2px 0;">${info.npwp || '-'}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <hr style="border: none; border-top: 1px solid #000; margin-bottom: 15px;">
            
            <table style="width: 100%; border: none; border-collapse: collapse; font-size: 11px; margin-bottom: 15px;">
                <tr>
                    <!-- Left Column: INCOME -->
                    <td style="width: 50%; vertical-align: top; border: none; padding: 0 15px 0 0;">
                        <div style="font-weight: bold; font-size: 12px; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px;">EARNINGS (PENDAPATAN)</div>
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr><td style="padding: 4px 0; font-weight: bold; width: 60%; text-transform: uppercase;">BASIC SALARY</td><td style="padding: 4px 0; text-align: right; width: 40%;">${formatRupiah(basicSalary)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">TRANSPORT ALW</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(transportAlw)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">SPECIAL ALW</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(specialAlw)}</td></tr>
                            ${rapelComponents.map(r => `<tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">${r.nama}</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(r.nilai)}</td></tr>`).join('')}
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">OVERTIME</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(overtime)}</td></tr>
                            ${earlyArrival > 0 ? `<tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">EARLY ARRIVAL</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(earlyArrival)}</td></tr>` : ''}
                            ${taxAllowance > 0 ? `<tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">${taxAllowanceLabel}</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(taxAllowance)}</td></tr>` : ''}
                        </table>
                    </td>
                    <!-- Vertical Divider -->
                    <td style="width: 1px; border-left: 1px solid #000; padding: 0;"></td>
                    <!-- Right Column: DEDUCTION -->
                    <td style="width: 50%; vertical-align: top; border: none; padding: 0 0 0 15px;">
                        <div style="font-weight: bold; font-size: 12px; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px; color: #e74c3c;">DEDUCTIONS (POTONGAN)</div>
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr><td style="padding: 4px 0; font-weight: bold; width: 60%; text-transform: uppercase;">IURAN WAJIB</td><td style="padding: 4px 0; text-align: right; color: #e74c3c; width: 40%;">${formatRupiah(iuranWajib)}</td></tr>
                            ${absenceDeduction > 0 ? `<tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">POTONGAN ABSEN</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(absenceDeduction)}</td></tr>` : ''}
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">SHOP DEDUCTION</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(shopDeduction)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">${taxLabel}</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(tax)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">BPJS JHT (JHTE)</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(jhte)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">BPJS KESEHATAN (EMP)</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(bpjsEmployee)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">BPJS JP (EMP)</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(jpEmployee)}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <hr style="border: none; border-top: 1px solid #000; margin: 15px 0;">
            
            <table style="width: 100%; border: none; border-collapse: collapse; font-size: 11px; margin-bottom: 15px;">
                <tr>
                    <td style="width: 50%; vertical-align: middle; border: none; padding: 0 15px 0 0;">
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr><td style="padding: 4px 0; font-weight: bold; width: 60%; text-transform: uppercase;">TOTAL CASH INCOME</td><td style="padding: 4px 0; text-align: right; font-weight: bold; width: 40%;">${formatRupiah(totalIncome)}</td></tr>
                        </table>
                    </td>
                    <td style="width: 1px; border-left: 1px solid #000; padding: 0;"></td>
                    <td style="width: 50%; vertical-align: middle; border: none; padding: 0 0 0 15px;">
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr><td style="padding: 4px 0; font-weight: bold; width: 60%; text-transform: uppercase;">TOTAL CASH DEDUCTION</td><td style="padding: 4px 0; text-align: right; font-weight: bold; color: #e74c3c; width: 40%;">${formatRupiah(totalDeduction)}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <hr style="border: none; border-top: 1px solid #000; margin: 15px 0;">
            
            <table style="width: 100%; border: none; border-collapse: collapse; font-size: 12px; font-weight: bold; background-color: #fafafa; border: 1px solid #eee; margin-bottom: 15px;">
                <tr>
                    <td style="padding: 12px 15px; text-transform: uppercase; width: 50%;">TAKE HOME PAY (THP)</td>
                    <td style="padding: 12px 15px; text-align: right; color: var(--success); font-size: 14px; width: 50%;">${formatRupiah(info.take_home_pay)}</td>
                </tr>
            </table>

            ${totalCompanyBpjs > 0 ? `
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; font-size: 11px;">
                <div style="font-weight: bold; font-size: 12px; margin-bottom: 6px; color: #475569; border-bottom: 1px dashed #cbd5e1; padding-bottom: 4px;">COMPANY BENEFITS (BPJS DIBAYAR PERUSAHAAN)</div>
                <table style="width: 100%; border: none; border-collapse: collapse; color: #64748b;">
                    <tr><td style="padding: 3px 0; width: 60%;">BPJS Kesehatan (4%)</td><td style="padding: 3px 0; text-align: right; width: 40%; font-weight: 500;">${formatRupiah(bpjsCompany)}</td></tr>
                    <tr><td style="padding: 3px 0;">BPJS JHT (JHTC) (3.7%)</td><td style="padding: 3px 0; text-align: right; font-weight: 500;">${formatRupiah(jhtc)}</td></tr>
                    <tr><td style="padding: 3px 0;">BPJS JP (Company) (2%)</td><td style="padding: 3px 0; text-align: right; font-weight: 500;">${formatRupiah(jpCompany)}</td></tr>
                    <tr><td style="padding: 3px 0;">BPJS JKK (0.24%)</td><td style="padding: 3px 0; text-align: right; font-weight: 500;">${formatRupiah(jkk)}</td></tr>
                    <tr><td style="padding: 3px 0;">BPJS JKM (0.3%)</td><td style="padding: 3px 0; text-align: right; font-weight: 500;">${formatRupiah(jkm)}</td></tr>
                    <tr style="border-top: 1px dashed #cbd5e1; font-weight: bold; color: #475569;"><td style="padding: 4px 0; padding-top: 6px;">Total Tunjangan BPJS Perusahaan</td><td style="padding: 4px 0; padding-top: 6px; text-align: right;">${formatRupiah(totalCompanyBpjs)}</td></tr>
                </table>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 6px; line-height: 1.3;"><i class="fas fa-info-circle"></i> Komponen di atas dibayarkan oleh perusahaan langsung ke BPJS Kesehatan dan BPJS Ketenagakerjaan (tidak memotong atau menambah jumlah uang tunai yang Anda terima).</div>
            </div>
            ` : ''}

            ${brutoPajak > 0 ? `
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; font-size: 11px; margin-top: 10px;">
                <div style="font-weight: bold; font-size: 12px; margin-bottom: 6px; color: #475569; border-bottom: 1px dashed #cbd5e1; padding-bottom: 4px;">DETAIL PERHITUNGAN PAJAK (TAX DETAILS)</div>
                <table style="width: 100%; border: none; border-collapse: collapse; color: #64748b;">
                    <tr><td style="padding: 3px 0; width: 60%;">Total Gaji Bruto (Basis PPh 21)</td><td style="padding: 3px 0; text-align: right; width: 40%; font-weight: 500;">${formatRupiah(brutoPajak)}</td></tr>
                    <tr>
                        <td style="padding: 3px 0;">Potongan Pajak PPh 21</td>
                        <td style="padding: 3px 0; text-align: right; font-weight: 500; color: ${tax >= 0 ? '#e74c3c' : 'var(--success)'};">
                            ${tax >= 0 ? '-' : '+'}${formatRupiah(Math.abs(tax))}
                        </td>
                    </tr>
                    <tr style="border-top: 1px dashed #cbd5e1; font-weight: bold; color: #475569;"><td style="padding: 4px 0; padding-top: 6px;">Gaji Bruto Setelah Pajak</td><td style="padding: 4px 0; padding-top: 6px; text-align: right;">${formatRupiah(brutoPajak - tax)}</td></tr>
                </table>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 6px; line-height: 1.3;"><i class="fas fa-info-circle"></i> Total Gaji Bruto di atas merupakan dasar pengenaan pajak (DPP) PPh 21, yang terdiri dari seluruh pendapatan tunai dikurangi denda/potongan absen, ditambah premi BPJS Kesehatan (4%), JKK (0.24%), dan JKM (0.3%) yang ditanggung perusahaan.</div>
            </div>
            ` : ''}
        </div>
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
    
    // Physically remove no-pdf elements from DOM temporarily so html2pdf cannot render them
    const noPdfElements = Array.from(element.querySelectorAll('.no-pdf'));
    const placeholders = noPdfElements.map(el => {
        const parent = el.parentNode;
        const next = el.nextSibling;
        parent.removeChild(el);
        return { parent, next, el };
    });
    
    let filename = 'salary_slip.pdf';
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
        
        filename = `salary_slip_${nameStr}${periodStr}.pdf`;
    } catch(e) {
        console.error('Error generating filename:', e);
    }
    
    // Show loading toast
    showToast('Generating PDF, please wait...', 'info');
    
    // Custom print/download optimizations for html2pdf (A4 Portrait for full-page payslip)
    const opt = {
        margin:       [10, 10, 10, 10], // top, left, bottom, right margins in mm
        filename:     filename,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
            scale: 2, 
            useCORS: true, 
            letterRendering: true,
            scrollY: 0,
            scrollX: 0
        },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
    };
    
    html2pdf().set(opt).from(element).save().then(() => {
        // Restore no-pdf elements to the exact DOM position
        placeholders.forEach(({ parent, next, el }) => {
            if (next && next.parentNode === parent) {
                parent.insertBefore(el, next);
            } else {
                parent.appendChild(el);
            }
        });
        showToast('Salary slip downloaded successfully!', 'success');
    }).catch(err => {
        // Restore no-pdf elements to the exact DOM position
        placeholders.forEach(({ parent, next, el }) => {
            if (next && next.parentNode === parent) {
                parent.insertBefore(el, next);
            } else {
                parent.appendChild(el);
            }
        });
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
                bonus_tambahan: parseFloat(document.getElementById('cutoffBonus').value) || 0,
                early_arrival_minutes: (parseInt(document.getElementById('cutoffEarlyArrival').value) || 0) * 60
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
    // Sinkronkan selectedClientId dari select attendance jika window.selectedClientId belum terisi
    if (!window.selectedClientId) {
        const attClientId = document.getElementById('attendanceClientSelect')?.value;
        if (attClientId) {
            window.selectedClientId = attClientId;
        }
    }

    if (!window.selectedClientId) {
        showToast('Please select a client first!', 'warning');
        return;
    }

    document.getElementById('modalPeriode').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    
    // Set default month and year in the form based on current process selection if available, otherwise current date
    const d = new Date();
    let currentMonth = d.getMonth() + 1; // 1-12
    let currentYear = d.getFullYear();

    const processMonthSelect = document.getElementById('processMonthSelect');
    const processYearSelect = document.getElementById('processYearSelect');
    if (processMonthSelect && processMonthSelect.value) {
        currentMonth = parseInt(processMonthSelect.value);
    }
    if (processYearSelect && processYearSelect.value) {
        currentYear = parseInt(processYearSelect.value);
    }
    
    const monthSelect = document.getElementById('periodMonth');
    const yearInput = document.getElementById('periodYear');
    if (monthSelect) monthSelect.value = currentMonth;
    if (yearInput) yearInput.value = currentYear;

    loadActivePeriod();
}
window.bukaModalPeriode = bukaModalPeriode;

function bukaModalCutOff(pkwtId, empName, hariKerja = 22, jamLembur = 0, potongan = 0, bonus = 0, earlyArrival = 0) {
    document.getElementById('modalCutOff').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('cutoffPkwtId').value = pkwtId;
    document.getElementById('cutoffEmployeeName').value = empName;
    document.getElementById('cutoffHariKerja').value = parseFloat(hariKerja) || 0;
    document.getElementById('cutoffJamLembur').value = parseFloat(jamLembur) || 0;
    document.getElementById('cutoffEarlyArrival').value = parseInt(earlyArrival) || 0;
    
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
let parsedDailyLogs = [];

async function bukaModalUploadAbsensi() {
    document.getElementById('modalUploadAbsensi').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    
    // Reset file input & UI
    document.getElementById('fileAbsensiExcel').value = '';
    const labelAbs = document.getElementById('labelAbsensiFilename');
    if (labelAbs) labelAbs.innerText = 'No file chosen';
    
    const text1 = document.getElementById('dropzoneAbsensiText1');
    const text2 = document.getElementById('dropzoneAbsensiText2');
    if (text1) text1.innerText = 'Drag & Drop file here';
    if (text2) text2.innerText = 'or click to browse files from your computer';
    
    const zone = document.getElementById('dropzoneAbsensiExcel');
    if (zone) {
        zone.style.borderColor = '#cbd5e1';
        zone.style.backgroundColor = '#ffffff';
    }

    document.getElementById('uploadAbsensiLogs').innerHTML = 'Waiting for file...';
    parsedAttendanceData = [];
    parsedDailyLogs = [];
    window.modalUploadResolvedPeriodId = null;
    
    const saveBtn = document.getElementById('btnSaveUploadedAbsensi');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.style.cursor = 'not-allowed';
        saveBtn.style.opacity = '0.5';
    }

    // Populate Year dropdown
    const yearSelect = document.getElementById('modalUploadAbsensiTahun');
    const currentYear = new Date().getFullYear();
    yearSelect.innerHTML = '';
    for (let y = currentYear - 2; y <= currentYear + 2; y++) {
        yearSelect.innerHTML += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
    }

    // Set current month
    const monthSelect = document.getElementById('modalUploadAbsensiBulan');
    const currentMonth = new Date().getMonth() + 1;
    monthSelect.value = currentMonth;

    // Check main page selectors for pre-selected month/year
    const mainMonth = document.getElementById('attendanceMonthSelect')?.value;
    const mainYear = document.getElementById('attendanceYearSelect')?.value;
    if (mainMonth) monthSelect.value = parseInt(mainMonth);
    if (mainYear) yearSelect.value = parseInt(mainYear);

    // Load clients
    try {
        const res = await fetch(`${API_URL}/clients`);
        const configs = res.ok ? await res.json() : [];
        const clientSelect = document.getElementById('modalUploadAbsensiClient');
        
        if ($.fn.select2 && $(clientSelect).data('select2')) {
            $(clientSelect).select2('destroy');
        }
        
        clientSelect.innerHTML = '<option value=""></option>' + configs.map(c => `
            <option value="${c.id}">${c.nama}</option>
        `).join('');

        if ($.fn.select2) {
            $(clientSelect).select2({
                width: '100%',
                placeholder: "-- Select Client --",
                dropdownParent: $('#modalUploadAbsensi')
            }).off('change.absensiClient').on('change.absensiClient', function() {
                onAbsensiClientChanged();
            });
        }

        // If client is already active in workspace or main filter, auto-select it!
        let activeClientId = null;
        if (window.selectedClientId) {
            activeClientId = window.selectedClientId;
        } else {
            const mainClientSelect = document.getElementById('attendanceClientSelect');
            if (mainClientSelect && mainClientSelect.value) {
                activeClientId = mainClientSelect.value;
            }
        }

        if (activeClientId && configs.some(c => c.id == activeClientId)) {
            if ($.fn.select2) {
                $(clientSelect).prop('disabled', true).val(activeClientId).trigger('change');
            } else {
                clientSelect.disabled = true;
                clientSelect.value = activeClientId;
                onAbsensiClientChanged();
            }
        } else {
            if ($.fn.select2) {
                $(clientSelect).prop('disabled', false).val('').trigger('change');
            } else {
                clientSelect.disabled = false;
                clientSelect.value = '';
            }
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
    const bulan = document.getElementById('modalUploadAbsensiBulan').value;
    const tahun = document.getElementById('modalUploadAbsensiTahun').value;
    
    if (!clientId || !bulan || !tahun) {
        window.currentPeriodAttendance = [];
        window.modalUploadResolvedPeriodId = null;
        return;
    }

    if (window.isCheckingPeriods) {
        return;
    }
    window.isCheckingPeriods = true;

    const logsDiv = document.getElementById('uploadAbsensiLogs');
    if (logsDiv && logsDiv.parentElement) {
        logsDiv.parentElement.style.display = 'none';
    }
    logsDiv.innerHTML = 'Loading employees...\n';

    try {
        // Auto-create period if it doesn't exist
        const res = await fetch(`${API_URL}/periods?client_id=${clientId}`);
        let periods = res.ok ? await res.json() : [];

        let matchedPeriod = periods.find(p => parseInt(p.bulan) == parseInt(bulan) && parseInt(p.tahun) == parseInt(tahun));
        if (!matchedPeriod) {
            const createRes = await fetch(`${API_URL}/periods`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    client_id: parseInt(clientId),
                    bulan: parseInt(bulan),
                    tahun: parseInt(tahun)
                })
            });
            if (createRes.ok) {
                const refetchRes = await fetch(`${API_URL}/periods?client_id=${clientId}`);
                periods = refetchRes.ok ? await refetchRes.json() : [];
                matchedPeriod = periods.find(p => parseInt(p.bulan) == parseInt(bulan) && parseInt(p.tahun) == parseInt(tahun));
            }
        }

        window.modalUploadPeriods = periods;
        window.modalUploadResolvedPeriodId = matchedPeriod ? matchedPeriod.id : null;

        if (!matchedPeriod) {
            logsDiv.innerHTML += 'Warning: Could not find or create period.\n';
            window.isCheckingPeriods = false;
            return;
        }

        // Load employees for this period
        const periodId = matchedPeriod.id;
        logsDiv.innerHTML += `Period resolved (ID: ${periodId}). Fetching employees...\n`;
        const empRes = await fetch(`${API_URL}/attendance/${periodId}?client_id=${clientId}`);
        if (!empRes.ok) throw new Error('HTTP ' + empRes.status);
        const data = await empRes.json();
        window.currentPeriodAttendance = data;
        logsDiv.innerHTML += `Loaded ${data.length} active employees.\nReady to select attendance Excel file.\n`;
    } catch (e) {
        console.error(e);
        logsDiv.innerHTML += `Error: ${e.message || e}\n`;
    } finally {
        window.isCheckingPeriods = false;
    }
}

// Keep as stub for backward compatibility
function onAbsensiPeriodChanged() {
    // No-op: period is now auto-resolved from month/year
}

async function downloadAbsensiTemplate() {
    const clientId = document.getElementById('modalUploadAbsensiClient').value;
    const bulan = document.getElementById('modalUploadAbsensiBulan').value;
    const tahun = document.getElementById('modalUploadAbsensiTahun').value;

    if (!clientId || !bulan || !tahun) {
        showToast('Pilih Client, Bulan dan Tahun terlebih dahulu.', 'warning');
        return;
    }

    const employees = window.currentPeriodAttendance || [];
    if (employees.length === 0) {
        showToast('No active employees found to generate template.', 'warning');
        return;
    }

    const month = parseInt(bulan);
    const year = parseInt(tahun);
    const daysInMonth = new Date(year, month, 0).getDate();

    // Fetch holidays for the year from Holiday Calendar
    let holidayMap = {};
    try {
        const resHolidays = await fetch(`${API_URL}/holidays?tahun=${year}`);
        if (resHolidays.ok) {
            const holidayList = await resHolidays.json();
            (Array.isArray(holidayList) ? holidayList : []).forEach(h => {
                const hDate = (h.tanggal || '').substring(0, 10);
                if (hDate) holidayMap[hDate] = h.deskripsi || 'Hari Libur';
            });
        }
    } catch(e) { console.warn('Could not load holidays for template:', e); }
    
    const dayNames = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
    const bulanNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const templateData = [];

    employees.forEach(emp => {
        const empId = emp.employ_id || emp.nik || '';
        const empName = emp.employee_name || '';
        const workDaysConfig = parseInt(emp.employee_hari_kerja || emp.position_hari_kerja || 5);
        const joinDate = (emp.tgl_masuk || emp.start_contract || '').substring(0, 10);

        for (let d = 1; d <= daysInMonth; d++) {
            const dateObj = new Date(year, month - 1, d);
            const dayOfWeek = dateObj.getDay();
            const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;

            // Skip dates prior to employee's join date
            if (joinDate && dateStr < joinDate) {
                continue;
            }

            const dayName = dayNames[dayOfWeek];
            const tglHariStr = `${dateStr} ${dayName}`;

            let jamMasuk = '08:00';
            let jamKeluar = '17:00';

            // Weekend rest day check
            let isRestDay = false;
            if (workDaysConfig === 5) {
                isRestDay = (dayOfWeek === 0 || dayOfWeek === 6);
            } else if (workDaysConfig === 6) {
                isRestDay = (dayOfWeek === 0);
            }

            // Public holiday check from holiday_calendar
            const isPublicHoliday = !!holidayMap[dateStr];

            if (isRestDay || isPublicHoliday) {
                jamMasuk = '';
                jamKeluar = '';
            }

            let status = 'Hadir';
            if (isPublicHoliday) {
                status = 'Off';
            } else if (isRestDay) {
                status = 'Off';
            }

            templateData.push({
                'Employee ID': empId,
                'Nama': empName,
                'Tgl dan Hari': tglHariStr,
                'Shift': '',
                'Jam Masuk': jamMasuk,
                'Jam Keluar': jamKeluar,
                'Status': status
            });
        }
    });

    const worksheet = XLSX.utils.json_to_sheet(templateData);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Attendance Template");
    
    const max_widths = [15, 25, 20, 12, 12, 12, 12];
    worksheet['!cols'] = max_widths.map(w => ({ wch: w }));

    const filename = `Attendance_Template_${bulanNames[month]}_${year}.xlsx`;
    XLSX.writeFile(workbook, filename);
    showToast('Template downloaded successfully!', 'success');
}

function parseAttendanceExcelDate(val) {
    if (val instanceof Date) return val;
    if (typeof val === 'number') {
        return new Date((val - 25569) * 86400 * 1000);
    }
    if (typeof val === 'string') {
        const num = parseFloat(val);
        if (!isNaN(num) && num > 40000 && num < 60000) {
            return new Date((num - 25569) * 86400 * 1000);
        }
        // Handle Indonesian date format: "Rabu 1 Juli 2026", "Senin 6 Juli 2026", etc.
        const bulanID = {
            'januari': 0, 'februari': 1, 'maret': 2, 'april': 3,
            'mei': 4, 'juni': 5, 'juli': 6, 'agustus': 7,
            'september': 8, 'oktober': 9, 'november': 10, 'desember': 11
        };
        const hariID = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];

        const lower = val.toLowerCase().trim();

        // Try "Hari Tgl Bulan Tahun" - e.g. "Rabu 1 Juli 2026"
        const matchID = lower.match(/(?:senin|selasa|rabu|kamis|jumat|sabtu|minggu)?\s*(\d{1,2})\s+([a-z]+)\s+(\d{4})/);
        if (matchID) {
            const tgl = parseInt(matchID[1]);
            const bulan = bulanID[matchID[2]];
            const thn = parseInt(matchID[3]);
            if (bulan !== undefined) {
                return new Date(thn, bulan, tgl);
            }
        }

        // Try standard formats: remove day names then parse
        let clean = val.replace(/senin|selasa|rabu|kamis|jumat|sabtu|minggu/gi, '').trim();
        // Replace Indonesian month names
        Object.entries(bulanID).forEach(([name, idx]) => {
            clean = clean.replace(new RegExp(name, 'gi'), String(idx + 1).padStart(2, '0'));
        });
        clean = clean.replace(/[^0-9\/\-]/g, ' ').trim().replace(/\s+/g, '-');

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

function processAbsensiFile(file) {
    const clientId = document.getElementById('modalUploadAbsensiClient').value;
    const bulan = document.getElementById('modalUploadAbsensiBulan').value;
    const tahun = document.getElementById('modalUploadAbsensiTahun').value;
    if (!clientId || !bulan || !tahun) {
        showToast('Pilih Client, Bulan dan Tahun terlebih dahulu.', 'warning');
        return;
    }

    const text1 = document.getElementById('dropzoneAbsensiText1');
    const text2 = document.getElementById('dropzoneAbsensiText2');
    if (text1) text1.innerText = file.name;
    if (text2) text2.innerText = 'File selected. Click or drag another file to replace.';

    const labelAbs = document.getElementById('labelAbsensiFilename');
    if (labelAbs) labelAbs.innerText = file.name;

    const logsDiv = document.getElementById('uploadAbsensiLogs');
    if (logsDiv && logsDiv.parentElement) {
        logsDiv.parentElement.style.display = 'none';
    }
    logsDiv.innerHTML = "Reading file...\n";

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array', cellDates: false });
            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];
            const json = XLSX.utils.sheet_to_json(worksheet, { raw: false, dateNF: 'yyyy-mm-dd' });

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

function handleAbsensiFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    processAbsensiFile(file);
}

function processParsedAttendance(rows) {
    const bulan = document.getElementById('modalUploadAbsensiBulan').value;
    const tahun = document.getElementById('modalUploadAbsensiTahun').value;
    const periodId = window.modalUploadResolvedPeriodId;
    const activePeriod = periodId ? (window.modalUploadPeriods || []).find(p => p.id == periodId) : null;
    const payoutPeriodStr = bulan && tahun ? `${bulan}-${tahun}` : '';
    const logsDiv = document.getElementById('uploadAbsensiLogs');
    const employees = window.currentPeriodAttendance || [];
    if (employees.length === 0) {
        const errMsg = "Gaji/Absensi tidak bisa diproses karena tidak ada data karyawan aktif untuk Client ini di database. Daftarkan karyawan terlebih dahulu di menu Employee Management.";
        logsDiv.innerHTML += `Error: ${errMsg}\n`;
        showToast(errMsg, 'warning');
        return;
    }

    // Group the Excel rows by Employee ID or Name
    const excelByEmp = {};
    rows.forEach(row => {
        const keys = Object.keys(row);
        const empIdKey = keys.find(k => {
            const clean = k.toLowerCase().replace(/[^a-z0-9]/g, '');
            return clean === 'employeeid' || clean === 'nik' || clean === 'idkaryawan' || clean.includes('employeeid') || clean.includes('idkaryawan');
        });
        const nameKey = keys.find(k => {
            const clean = k.toLowerCase().replace(/[^a-z0-9]/g, '');
            return clean === 'nama' || clean === 'name' || clean === 'employeename' || clean === 'namakaryawan' || clean.includes('name') || clean.includes('nama');
        });
        const tglKey = keys.find(k => {
            const clean = k.toLowerCase().replace(/[^a-z0-9]/g, '');
            return clean === 'tgldanhari' || clean === 'tanggal' || clean === 'date' || clean === 'tgl' || clean.includes('date') || clean.includes('tanggal') || clean.includes('tgl');
        });
        const checkinKey = keys.find(k => {
            const clean = k.toLowerCase().replace(/[^a-z0-9]/g, '');
            return clean === 'jammasuk' || clean === 'checkin' || clean === 'timein' || clean === 'masuk' || clean.includes('masuk') || clean.includes('checkin') || clean.includes('timein');
        });
        const checkoutKey = keys.find(k => {
            const clean = k.toLowerCase().replace(/[^a-z0-9]/g, '');
            return clean === 'jamkeluar' || clean === 'checkout' || clean === 'timeout' || clean === 'keluar' || clean.includes('keluar') || clean.includes('checkout') || clean.includes('timeout');
        });
        const statusKey = keys.find(k => {
            const clean = k.toLowerCase().replace(/[^a-z0-9]/g, '');
            return clean === 'status' || clean.includes('status');
        });
        const shiftKey = keys.find(k => {
            const clean = k.toLowerCase().replace(/[^a-z0-9]/g, '');
            return clean === 'shift' || clean.includes('shift');
        });

        const empId = empIdKey ? String(row[empIdKey] || '').trim() : '';
        const empName = nameKey ? String(row[nameKey] || '').trim() : '';
        const tglVal = tglKey ? row[tglKey] : '';
        
        let checkin = '';
        if (checkinKey) {
            const rawVal = row[checkinKey];
            if (rawVal !== undefined && rawVal !== null) {
                const strVal = String(rawVal).trim();
                const num = parseFloat(strVal);
                if (!isNaN(num) && num >= 0 && num < 1) {
                    const totalMinutes = Math.round(num * 24 * 60);
                    const hours = Math.floor(totalMinutes / 60);
                    const minutes = totalMinutes % 60;
                    checkin = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
                } else {
                    checkin = strVal;
                }
            }
        }
        
        let checkout = '';
        if (checkoutKey) {
            const rawVal = row[checkoutKey];
            if (rawVal !== undefined && rawVal !== null) {
                const strVal = String(rawVal).trim();
                const num = parseFloat(strVal);
                if (!isNaN(num) && num >= 0 && num < 1) {
                    const totalMinutes = Math.round(num * 24 * 60);
                    const hours = Math.floor(totalMinutes / 60);
                    const minutes = totalMinutes % 60;
                    checkout = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
                } else {
                    checkout = strVal;
                }
            }
        }
        
        const status = statusKey ? String(row[statusKey] || '').trim() : '';
        const shift = shiftKey ? String(row[shiftKey] || '').trim() : '';

        if (!empId && !empName) return;
        const rowData = {
            dateVal: tglVal,
            checkin: checkin,
            checkout: checkout,
            status: status,
            shift: shift
        };

        if (empId) {
            if (!excelByEmp[empId]) {
                excelByEmp[empId] = [];
            }
            excelByEmp[empId].push(rowData);
        }
        if (empName) {
            const nameKey = empName.toLowerCase();
            if (!excelByEmp[nameKey]) {
                excelByEmp[nameKey] = [];
            }
            excelByEmp[nameKey].push(rowData);
        }
    });

    const finalAttendance = [];
    const dailyLogs = [];
    let logText = "";

    employees.forEach(emp => {
        const empId = String(emp.employ_id || emp.nik || '').trim();
        const empName = String(emp.employee_name || '').trim();
        
        let matchedRows = excelByEmp[empId] || excelByEmp[empName.toLowerCase()];
        if (!matchedRows) {
            const matchingKey = Object.keys(excelByEmp).find(k => 
                k.toLowerCase() === empName.toLowerCase() || 
                empName.toLowerCase().includes(k.toLowerCase()) ||
                k.toLowerCase().includes(empName.toLowerCase())
            );
            if (matchingKey) {
                matchedRows = excelByEmp[matchingKey];
            }
        }

        if (!matchedRows) {
            logText += `⚠️ Employee not found in Excel: "${empName}" (ID: ${empId || 'N/A'}). Will use default/current values.\n`;
            return;
        }

        const workDaysConfig = parseInt(emp.employee_hari_kerja || emp.position_hari_kerja || 5);
        let totalHadir = 0;
        let totalLembur = 0;
        let totalAlfa = 0;
        
        matchedRows.forEach(row => {
            const dateObj = parseAttendanceExcelDate(row.dateVal);
            if (!dateObj) return;

            const yyyy = dateObj.getFullYear();
            const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
            const dd = String(dateObj.getDate()).padStart(2, '0');
            const formattedDate = `${yyyy}-${mm}-${dd}`;

            // Filter: skip rows outside the selected calendar month
            if (bulan && tahun) {
                const filterMonth = parseInt(bulan);
                const filterYear = parseInt(tahun);
                if (dateObj.getMonth() + 1 !== filterMonth || dateObj.getFullYear() !== filterYear) {
                    return; // Skip this row - not in the selected calendar month
                }
            }

            // Determine final status - auto-detect Day Off for weekends without data
            const hasCheckin = row.checkin && row.checkin !== 'null';
            const hasCheckout = row.checkout && row.checkout !== 'null';
            let finalStatus = row.status || 'Hadir';

            if (!hasCheckin && !hasCheckout && (!row.status || row.status.toLowerCase().trim() === 'hadir')) {
                const dayOfWeekCheck = dateObj.getDay();
                let isRestDayCheck = false;
                if (workDaysConfig === 5) {
                    isRestDayCheck = (dayOfWeekCheck === 0 || dayOfWeekCheck === 6);
                } else if (workDaysConfig === 6) {
                    isRestDayCheck = (dayOfWeekCheck === 0);
                }
                if (isRestDayCheck) {
                    finalStatus = 'Day Off';
                }
            }

            dailyLogs.push({
                employee_id: emp.employee_id,
                tanggal: formattedDate,
                jam_masuk: hasCheckin ? row.checkin : '',
                jam_keluar: hasCheckout ? row.checkout : '',
                status: finalStatus,
                keterangan: '',
                shift_name: row.shift,
                payout_period: payoutPeriodStr
            });

            const dayOfWeek = dateObj.getDay();
            const statusNorm = finalStatus.toLowerCase().trim();

            let isRestDay = false;
            if (workDaysConfig === 5) {
                isRestDay = (dayOfWeek === 0 || dayOfWeek === 6);
            } else if (workDaysConfig === 6) {
                isRestDay = (dayOfWeek === 0);
            }

            let hasTimes = false;
            let checkinTime = null;
            let checkoutTime = null;

            if (row.checkin && row.checkout && row.checkin !== 'null' && row.checkout !== 'null') {
                const ciParts = row.checkin.split(':');
                const coParts = row.checkout.split(':');
                if (ciParts.length >= 2 && coParts.length >= 2) {
                    hasTimes = true;
                    checkinTime = new Date(2000, 0, 1, parseInt(ciParts[0]), parseInt(ciParts[1]), 0);
                    checkoutTime = new Date(2000, 0, 1, parseInt(coParts[0]), parseInt(coParts[1]), 0);
                    if (checkoutTime < checkinTime) {
                        checkoutTime.setDate(checkoutTime.getDate() + 1);
                    }
                }
            }

            const isPresent = (statusNorm === 'hadir' || statusNorm === 'present' || (statusNorm === '' && hasTimes));
            if (isPresent) {
                totalHadir++;
            }

            // Overtime will be calculated on the backend from daily shift attendance logs


            const isAbsent = (statusNorm === 'alfa' || statusNorm === 'absent' || statusNorm === 'missing' || statusNorm === 'absen');
            if (isAbsent && !isRestDay) {
                totalAlfa++;
            }
        });

        const gajiPokok = parseFloat(emp.gaji_pokok || 0);
        let divider = (workDaysConfig === 5) ? 22 : ((workDaysConfig === 6) ? 26 : 30);
        if (bulan && tahun) {
            const filterMonth = parseInt(bulan);
            const filterYear = parseInt(tahun);
            const daysInMonth = new Date(filterYear, filterMonth, 0).getDate();
            let stdDays = 0;
            for (let d = 1; d <= daysInMonth; d++) {
                const dateObj = new Date(filterYear, filterMonth - 1, d);
                const dayOfWeek = dateObj.getDay();
                if (workDaysConfig === 5) {
                    if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                        stdDays++;
                    }
                } else if (workDaysConfig === 6) {
                    if (dayOfWeek !== 0) {
                        stdDays++;
                    }
                } else {
                    stdDays++;
                }
            }
            if (stdDays > 0) {
                divider = stdDays;
            }
        }
        const dendaAbsenPerDay = gajiPokok / divider;
        const totalPotongan = totalAlfa * dendaAbsenPerDay;

        logText += `✅ Parsed "${empName}":\n`;
        logText += `   - Work week config: ${workDaysConfig} days (${workDaysConfig === 5 ? 'Sat/Sun off' : workDaysConfig === 6 ? 'Sun off' : 'No off'})\n`;
        logText += `   - Attended: ${totalHadir} Days, Overtime: ${totalLembur.toFixed(1)} Hours\n`;
        logText += `   - Absent (Alfa): ${totalAlfa} Days => Deduction: ${formatRupiah(totalPotongan)}\n`;

        finalAttendance.push({
            period_id: periodId,
            pkwt_id: emp.pkwt_id,
            hari_kerja: totalHadir,
            jam_lembur: parseFloat(totalLembur.toFixed(1)),
            potongan_absensi: parseFloat(totalPotongan.toFixed(2)),
            bonus_tambahan: parseFloat(emp.bonus_tambahan || 0)
        });
    });

    parsedAttendanceData = finalAttendance;
    parsedDailyLogs = dailyLogs;
    logsDiv.innerHTML += "\n" + logText;
    logsDiv.innerHTML += `\nSuccess: Ready to apply ${finalAttendance.length} records.`;
    
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

    showToast('Applying daily attendance logs...', 'info');
    try {
        // 1. Save daily logs
        const resLogs = await fetch(`${API_URL}/attendance-logs/bulk`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ logs: parsedDailyLogs })
        });

        if (!resLogs.ok) {
            const err = await resLogs.json();
            throw new Error(err.message || 'Gagal menyimpan log absensi harian');
        }

        // 2. Save cut-off summary
        showToast('Applying summary attendance records...', 'info');
        const resSummary = await fetch(`${API_URL}/attendance-bulk`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(parsedAttendanceData)
        });

        if (resSummary.ok) {
            showToast('Attendance logs successfully imported!', 'success');
            
            const uploadedClientId = document.getElementById('modalUploadAbsensiClient')?.value;
            const uploadedBulan = document.getElementById('modalUploadAbsensiBulan')?.value;
            const uploadedTahun = document.getElementById('modalUploadAbsensiTahun')?.value;

            tutupModalUploadAbsensi();

            // Sync main dashboard attendance selectors
            const mainClientSelect = document.getElementById('attendanceClientSelect');
            const mainMonthSelect = document.getElementById('attendanceMonthSelect');
            const mainYearSelect = document.getElementById('attendanceYearSelect');

            if (mainClientSelect && uploadedClientId) {
                mainClientSelect.value = uploadedClientId;
                if (typeof window.syncCustomClientDropdown === 'function') {
                    window.syncCustomClientDropdown();
                }
            }
            if (uploadedBulan && mainMonthSelect) mainMonthSelect.value = parseInt(uploadedBulan);
            if (uploadedTahun && mainYearSelect) mainYearSelect.value = parseInt(uploadedTahun);

            // Refresh table
            const periodId = window.modalUploadResolvedPeriodId;
            if (typeof currentPeriodId !== 'undefined' && window.currentPeriodId == periodId) {
                renderCutOffTable();
            }
            
            // Refresh daily attendance logs table if the function exists
            if (typeof loadAttendanceLogs === 'function') {
                loadAttendanceLogs();
            }
        } else {
            const err = await resSummary.json();
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
window.parseExcelDate = parseAttendanceExcelDate;
window.renderCutOffTable = renderCutOffTable;

function handleAbsensiDragOver(event) {
    event.preventDefault();
    const zone = document.getElementById('dropzoneAbsensiExcel');
    if (zone) {
        zone.style.borderColor = '#e67e22';
        zone.style.backgroundColor = 'rgba(243, 156, 18, 0.18)';
    }
}

function handleAbsensiDragLeave(event) {
    event.preventDefault();
    const zone = document.getElementById('dropzoneAbsensiExcel');
    if (zone) {
        zone.style.borderColor = '#f39c12';
        zone.style.backgroundColor = 'rgba(243, 156, 18, 0.08)';
    }
}

function handleAbsensiDrop(event) {
    event.preventDefault();
    const zone = document.getElementById('dropzoneAbsensiExcel');
    if (zone) {
        zone.style.borderColor = '#f39c12';
        zone.style.backgroundColor = 'rgba(243, 156, 18, 0.08)';
    }
    
    if (event.dataTransfer.files && event.dataTransfer.files.length > 0) {
        const file = event.dataTransfer.files[0];
        const fileInput = document.getElementById('fileAbsensiExcel');
        if (fileInput) {
            fileInput.files = event.dataTransfer.files;
        }
        processAbsensiFile(file);
    }
}

// Prevent default drag/drop behavior on window to avoid browser navigating away
window.addEventListener("dragover", function(e) {
    e.preventDefault();
}, false);
window.addEventListener("drop", function(e) {
    e.preventDefault();
}, false);

window.handleAbsensiDragOver = handleAbsensiDragOver;
window.handleAbsensiDragLeave = handleAbsensiDragLeave;
window.handleAbsensiDrop = handleAbsensiDrop;

