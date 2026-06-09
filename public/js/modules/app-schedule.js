// ===== SCHEDULE MODULE =====
// Handles payroll schedule templates CRUD operations

let masterSchedules = [];
let selectedScheduleYear = 2026;
let scheduleYears = [2024, 2025, 2026, 2027];

function renderScheduleYears() {
    const selectEl = document.getElementById('scheduleYearSelect');
    if (!selectEl) return;

    selectEl.innerHTML = scheduleYears.map(year => {
        const isActive = year === selectedScheduleYear;
        return `<option value="${year}" ${isActive ? 'selected' : ''}>Tahun ${year}</option>`;
    }).join('');
    
    // Update the table title to reflect selected year
    const titleEl = document.getElementById('scheduleTableTitle');
    if (titleEl) {
        titleEl.innerText = `Master Payroll Schedule - Tahun ${selectedScheduleYear}`;
    }
}

function showPrompt(message, defaultValue = '', title = 'Input') {
    return new Promise((resolve) => {
        let overlay = document.getElementById('customPromptOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'customPromptOverlay';
            overlay.style = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(15, 23, 42, 0.6);
                backdrop-filter: blur(4px);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            
            const dialog = document.createElement('div');
            dialog.id = 'customPromptDialog';
            dialog.style = `
                background: white;
                border-radius: 16px;
                width: 400px;
                max-width: 95%;
                padding: 24px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                transform: scale(0.9);
                transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                display: flex;
                flex-direction: column;
                gap: 16px;
            `;
            
            dialog.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #2563eb; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div>
                        <h4 id="customPromptTitle" style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b;">${title}</h4>
                    </div>
                </div>
                <div id="customPromptMessage" style="font-size: 14px; color: #475569; line-height: 1.5;">${message}</div>
                <div>
                    <input type="text" id="customPromptInput" style="width: 100%; padding: 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; outline: none; font-size: 14px; color: #1e293b; transition: border-color 0.2s;" />
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px;">
                    <button id="customPromptCancel" style="padding: 10px 16px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; color: #475569; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;">Batal</button>
                    <button id="customPromptOk" style="padding: 10px 20px; border-radius: 8px; border: none; background: #e67e22; color: white; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(230, 126, 34, 0.2);">Simpan</button>
                </div>
            `;
            
            overlay.appendChild(dialog);
            document.body.appendChild(overlay);
        }
        
        // Setup values
        const dialog = document.getElementById('customPromptDialog');
        const titleEl = document.getElementById('customPromptTitle');
        const messageEl = document.getElementById('customPromptMessage');
        const inputEl = document.getElementById('customPromptInput');
        const okBtn = document.getElementById('customPromptOk');
        const cancelBtn = document.getElementById('customPromptCancel');
        
        titleEl.innerText = title;
        messageEl.innerText = message;
        inputEl.value = defaultValue;
        
        // Show with transition
        setTimeout(() => {
            overlay.style.opacity = '1';
            dialog.style.transform = 'scale(1)';
        }, 10);
        
        const close = (value) => {
            overlay.style.opacity = '0';
            dialog.style.transform = 'scale(0.9)';
            setTimeout(() => {
                overlay.remove();
            }, 300);
            resolve(value);
        };
        
        // Add events
        okBtn.onclick = () => {
            const val = inputEl.value.trim();
            close(val);
        };
        
        cancelBtn.onclick = () => {
            close(null);
        };
        
        inputEl.onkeydown = (e) => {
            if (e.key === 'Enter') {
                okBtn.click();
            } else if (e.key === 'Escape') {
                cancelBtn.click();
            }
        };
        
        // Focus input
        setTimeout(() => inputEl.focus(), 100);
    });
}

function pilihTahunSchedule(year) {
    selectedScheduleYear = parseInt(year);
    renderScheduleYears();
    renderMasterSchedule();
}

async function tambahPeriodeTahunan() {
    const defaultNewYear = Math.max(...scheduleYears) + 1;
    const yearStr = await showPrompt("Masukkan tahun baru:", defaultNewYear, "Tambah Periode Tahunan");
    if (!yearStr) return;
    const year = parseInt(yearStr);
    if (isNaN(year) || year < 2000 || year > 2100) {
        showToast("Input tahun tidak valid!", "error");
        return;
    }
    if (scheduleYears.includes(year)) {
        showToast("Tahun sudah ada!", "error");
        return;
    }
    scheduleYears.push(year);
    scheduleYears.sort((a, b) => b - a); // Sort descending
    selectedScheduleYear = year;
    renderScheduleYears();
    renderMasterSchedule();
}

async function renderMasterSchedule() {
    // Always render years first to keep UI sync
    renderScheduleYears();

    try {
        const tbody = document.getElementById('masterSchedulesContainer');
        if (!tbody) return;
        
        tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;

        const res = await fetch(`${API_URL}/schedule-templates?tahun=${selectedScheduleYear}`);
        if (!res.ok) throw new Error('Failed to fetch schedule templates');
        masterSchedules = await res.json();

        if (masterSchedules.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 40px; color: #94a3b8;"><i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>No payroll schedules configured for year ${selectedScheduleYear}. Click the "Add Schedule" button to configure.</td></tr>`;
            return;
        }

        tbody.innerHTML = masterSchedules.map((s, idx) => {
            return `
                <tr>
                    <td style="text-align: center; padding: 16px; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 14px;">${idx + 1}</td>
                    <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; color: #1e293b; font-weight: 600; font-size: 14px;">${s.nama}</td>
                    <td style="text-align: center; padding: 16px; border-bottom: 1px solid #e2e8f0; color: #1e293b; font-weight: 600; font-size: 14px;">Tgl ${s.pay_date}</td>
                    <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 13px; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${s.deskripsi || '-'}">${s.deskripsi || '-'}</td>
                    <td style="padding: 16px; border-bottom: 1px solid #e2e8f0;">
                        <div style="display: flex; justify-content: center; align-items: center; gap: 12px;">
                            <button onclick="bukaModalSchedule('edit', ${s.id})" class="btn-icon" title="Edit" style="color:#94a3b8; background:transparent; border:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; box-shadow:none; width:auto; height:auto; padding:4px;"><i class="fas fa-edit" style="font-size:16px;"></i></button>
                            <button onclick="hapusScheduleTemplate(${s.id})" class="btn-icon" title="Delete" style="color:#e74c3c; background:transparent; border:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; box-shadow:none; width:auto; height:auto; padding:4px;"><i class="fas fa-trash" style="font-size:16px;"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    } catch (err) {
        console.error(err);
        const tbody = document.getElementById('masterSchedulesContainer');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 20px; color: #ef4444;"><i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>Failed to load schedules.</td></tr>';
        }
    }
}

function bukaModalSchedule(mode, id = null) {
    tutupSemuaModal(true); // Close any active modals, keep sidebar open
    
    document.getElementById('modalSchedule').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';

    const titleEl = document.getElementById('modalScheduleTitle');
    const form = document.getElementById('formSchedule');
    
    form.reset();
    document.getElementById('scheduleId').value = '';

    if (mode === 'tambah') {
        titleEl.innerText = 'Add Payroll Schedule';
    } else if (mode === 'edit' && id !== null) {
        titleEl.innerText = 'Edit Payroll Schedule';
        const s = masterSchedules.find(item => item.id == id);
        if (s) {
            document.getElementById('scheduleId').value = s.id;
            document.getElementById('scheduleNama').value = s.nama;
            document.getElementById('schedulePayDate').value = s.pay_date;
            document.getElementById('scheduleCutoffStart').value = s.cutoff_start;
            document.getElementById('scheduleCutoffEnd').value = s.cutoff_end;
            document.getElementById('scheduleDeskripsi').value = s.deskripsi || '';
        }
    }
}

function tutupModalSchedule() {
    document.getElementById('modalSchedule').style.display = 'none';
    if (!document.querySelector('.sidebar').classList.contains('active')) {
        document.getElementById('overlay').style.display = 'none';
    }
}

async function hapusScheduleTemplate(id) {
    if (!await showConfirm('Apakah Anda yakin ingin menghapus schedule template ini?')) return;
    try {
        const res = await fetch(`${API_URL}/schedule-templates/${id}`, {
            method: 'DELETE'
        });
        if (res.ok) {
            showToast('Schedule template berhasil dihapus', 'success');
            renderMasterSchedule();
        } else {
            showToast('Gagal menghapus schedule template', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Terjadi kesalahan saat menghapus data', 'error');
    }
}

// Register submit listener
document.addEventListener('DOMContentLoaded', () => {
    // Initial renders
    renderScheduleYears();
    renderMasterSchedule();

    const form = document.getElementById('formSchedule');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = document.getElementById('scheduleId').value;
            const nama = document.getElementById('scheduleNama').value;
            const payDate = parseInt(document.getElementById('schedulePayDate').value);
            const cutoffStart = parseInt(document.getElementById('scheduleCutoffStart').value);
            const cutoffEnd = parseInt(document.getElementById('scheduleCutoffEnd').value);
            const deskripsi = document.getElementById('scheduleDeskripsi').value;

            // Simple validation
            if (payDate < 1 || payDate > 31 || cutoffStart < 1 || cutoffStart > 31 || cutoffEnd < 1 || cutoffEnd > 31) {
                showToast('Tanggal harus berada dalam rentang 1-31!', 'error');
                return;
            }

            const data = { 
                nama, 
                pay_date: payDate, 
                cutoff_start: cutoffStart, 
                cutoff_end: cutoffEnd, 
                deskripsi,
                tahun: selectedScheduleYear 
            };
            
            try {
                let url = `${API_URL}/schedule-templates`;
                let method = 'POST';

                if (id) {
                    url += `/${id}`;
                    method = 'PUT';
                }

                const res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    showToast(id ? 'Schedule template berhasil diupdate!' : 'Schedule template berhasil disimpan!', 'success');
                    tutupModalSchedule();
                    renderMasterSchedule();
                } else {
                    showToast('Gagal menyimpan schedule template!', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Terjadi kesalahan koneksi!', 'error');
            }
        });
    }
});

// Expose functions globally
window.renderMasterSchedule = renderMasterSchedule;
window.bukaModalSchedule = bukaModalSchedule;
window.tutupModalSchedule = tutupModalSchedule;
window.hapusScheduleTemplate = hapusScheduleTemplate;
window.pilihTahunSchedule = pilihTahunSchedule;
window.tambahPeriodeTahunan = tambahPeriodeTahunan;

// ===== INLINE ATTENDANCE UPLOAD & TABS =====
async function switchScheduleTab(tab) {
    const tabTemplatesBtn = document.getElementById('tabScheduleTemplatesBtn');
    const tabUploadBtn = document.getElementById('tabScheduleUploadBtn');
    const templatesPanel = document.getElementById('scheduleTabTemplates');
    const uploadPanel = document.getElementById('scheduleTabUpload');

    if (!tabTemplatesBtn || !tabUploadBtn || !templatesPanel || !uploadPanel) return;

    if (tab === 'templates') {
        tabTemplatesBtn.classList.add('active');
        tabUploadBtn.classList.remove('active');

        templatesPanel.style.display = 'block';
        uploadPanel.style.display = 'none';
        
        renderMasterSchedule();
    } else if (tab === 'upload') {
        tabUploadBtn.classList.add('active');
        tabTemplatesBtn.classList.remove('active');

        templatesPanel.style.display = 'none';
        uploadPanel.style.display = 'block';
        
        // Load clients for the inline form
        clearInlineAttendanceTable();
    }
}

async function initInlineUploadClients() {
    const clientSelect = document.getElementById('inlineUploadAbsensiClient');
    if (!clientSelect) return;
    
    clientSelect.innerHTML = '<option value="">Loading clients...</option>';
    
    try {
        const res = await fetch(`${API_URL}/clients`);
        const configs = res.ok ? await res.json() : [];
        
        clientSelect.innerHTML = '<option value="">-- Select Client --</option>' + configs.map(c => `
            <option value="${c.id}">${c.nama}</option>
        `).join('');

        // If client is already active in workspace, auto-select it!
        if (window.selectedClientId && configs.some(c => c.id == window.selectedClientId)) {
            clientSelect.value = window.selectedClientId;
            onInlineAbsensiClientChanged();
        } else {
            const periodSelect = document.getElementById('inlineUploadAbsensiPeriod');
            if (periodSelect) {
                periodSelect.innerHTML = '<option value="">-- Select Client First --</option>';
                periodSelect.disabled = true;
            }
        }
    } catch (e) {
        console.error(e);
        clientSelect.innerHTML = '<option value="">Failed to load clients</option>';
        showToast('Failed to load clients list', 'error');
    }
}

async function onInlineAbsensiClientChanged() {
    const clientId = document.getElementById('inlineUploadAbsensiClient').value;
    const periodSelect = document.getElementById('inlineUploadAbsensiPeriod');
    
    if (!clientId) {
        periodSelect.innerHTML = '<option value="">-- Select Client First --</option>';
        periodSelect.disabled = true;
        clearInlineAttendanceTable();
        return;
    }

    periodSelect.innerHTML = '<option value="">Loading periods...</option>';
    periodSelect.disabled = true;
    clearInlineAttendanceTable();

    try {
        const res = await fetch(`${API_URL}/periods?client_id=${clientId}`);
        const periods = res.ok ? await res.json() : [];
        window.inlineUploadPeriods = periods;

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
            onInlineAbsensiPeriodChanged();
        }
    } catch (e) {
        console.error(e);
        periodSelect.innerHTML = '<option value="">Error loading periods</option>';
    }
}

let inlinePeriodAttendance = [];

async function onInlineAbsensiPeriodChanged() {
    const clientId = document.getElementById('inlineUploadAbsensiClient').value;
    const periodId = document.getElementById('inlineUploadAbsensiPeriod').value;
    const logsDiv = document.getElementById('inlineUploadAbsensiLogs');
    
    // Hide left column and restore 1-column layout when period changes
    const leftCol = document.getElementById('inlineUploadFormCol');
    const container = document.getElementById('inlineUploadContainer');
    if (leftCol && container) {
        leftCol.style.display = 'none';
        container.style.gridTemplateColumns = '1fr';
    }

    clearInlineAttendanceTable();
    
    if (!clientId || !periodId) {
        inlinePeriodAttendance = [];
        return;
    }

    const tbody = document.getElementById('inlineAttendanceTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8; border: none !important; height: 290px; vertical-align: middle;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 36px; margin-bottom: 12px; display: block; color: var(--primary-color);"></i>
                    Loading data...
                </td>
            </tr>
        `;
    }

    logsDiv.innerHTML = "Fetching active employees for this period...\n";
    try {
        const res = await fetch(`${API_URL}/attendance/${periodId}?client_id=${clientId}`);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        inlinePeriodAttendance = data;
        logsDiv.innerHTML += `Loaded ${data.length} active employees from database.\n`;
        
        // Render database summaries in the table
        renderInlineAttendanceTable(data, true);
    } catch (e) {
        console.error(e);
        logsDiv.innerHTML += `Error loading employee roster: ${e.message || e}\n`;
    }
}

function renderInlineAttendanceTable(rows, isDbSummary = false) {
    const thead = document.getElementById('inlineAttendanceTableHeader');
    const tbody = document.getElementById('inlineAttendanceTableBody');
    if (!thead || !tbody) return;

    // Render Headers
    if (isDbSummary) {
        thead.innerHTML = `
            <tr style="background: #f8fafc;">
                <th style="width: 50px; text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">No</th>
                <th style="text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Employee ID</th>
                <th style="text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Employee Name</th>
                <th style="text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Contract</th>
                <th style="text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Hari Kerja</th>
                <th style="text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Lembur (Hr)</th>
                <th style="text-align: right; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Pot. Absen</th>
                <th style="text-align: right; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Bonus Tambahan</th>
            </tr>
        `;
    } else {
        thead.innerHTML = `
            <tr style="background: #f8fafc;">
                <th style="width: 50px; text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">No</th>
                <th style="text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Employee ID</th>
                <th style="text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Nama</th>
                <th style="text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Tgl dan Hari</th>
                <th style="text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Jam Masuk</th>
                <th style="text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Jam Keluar</th>
                <th style="text-align: center; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 13px;">Status</th>
            </tr>
        `;
    }

    if (!rows || rows.length === 0) {
        const colSpan = isDbSummary ? 8 : 7;
        tbody.innerHTML = `
            <tr>
                <td colspan="${colSpan}" style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fas fa-file-excel" style="font-size: 36px; margin-bottom: 12px; display: block; color: #cbd5e1;"></i>
                    No attendance data found.
                </td>
            </tr>
        `;
        return;
    }

    if (isDbSummary) {
        tbody.innerHTML = rows.map((emp, idx) => {
            const empId = emp.employ_id || emp.nik || '-';
            const empName = emp.employee_name || '-';
            const contract = emp.tipe_perjanjian || '-';
            const workingDays = emp.hari_kerja !== null ? emp.hari_kerja : '0';
            const overtime = emp.jam_lembur !== null ? parseFloat(emp.jam_lembur) : '0';
            const deduction = emp.potongan_absensi !== null ? formatRupiah(parseFloat(emp.potongan_absensi)) : 'Rp 0';
            const bonus = emp.bonus_tambahan !== null ? formatRupiah(parseFloat(emp.bonus_tambahan)) : 'Rp 0';

            return `
                <tr class="attendance-row">
                    <td style="text-align: center; padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b;">${idx + 1}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #475569; font-weight: 500;">${empId}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #1e293b; font-weight: 600;">${empName}</td>
                    <td style="text-align: center; padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 12px;"><span style="background: #f1f5f9; padding: 2px 8px; border-radius: 4px; color: #475569; font-weight: 600;">${contract}</span></td>
                    <td style="text-align: center; padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #1e293b; font-weight: 600;">${workingDays}</td>
                    <td style="text-align: center; padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #1e293b; font-weight: 600;">${overtime}</td>
                    <td style="text-align: right; padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #e74c3c; font-weight: 600;">${deduction}</td>
                    <td style="text-align: right; padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #2ecc71; font-weight: 600;">${bonus}</td>
                </tr>
            `;
        }).join('');
    } else {
        tbody.innerHTML = rows.map((row, idx) => {
            const keys = Object.keys(row);
            const empIdKey = keys.find(k => k.toLowerCase().replace(/\s+/g, '') === 'employeeid');
            const nameKey = keys.find(k => k.toLowerCase().replace(/\s+/g, '') === 'nama' || k.toLowerCase().replace(/\s+/g, '') === 'name');
            const tglKey = keys.find(k => k.toLowerCase().replace(/\s+/g, '') === 'tgldanhari' || k.toLowerCase().replace(/\s+/g, '') === 'tanggal' || k.toLowerCase().replace(/\s+/g, '') === 'date');
            const checkinKey = keys.find(k => k.toLowerCase().replace(/\s+/g, '') === 'jammasuk' || k.toLowerCase().replace(/\s+/g, '') === 'checkin');
            const checkoutKey = keys.find(k => k.toLowerCase().replace(/\s+/g, '') === 'jamkeluar' || k.toLowerCase().replace(/\s+/g, '') === 'checkout');
            const statusKey = keys.find(k => k.toLowerCase().replace(/\s+/g, '') === 'status');

            const empId = String(row[empIdKey] || '').trim();
            const empName = String(row[nameKey] || '').trim();
            
            let tglVal = row[tglKey];
            if (tglVal instanceof Date) {
                const y = tglVal.getFullYear();
                const m = String(tglVal.getMonth() + 1).padStart(2, '0');
                const d = String(tglVal.getDate()).padStart(2, '0');
                tglVal = `${y}-${m}-${d}`;
            }
            
            const checkin = String(row[checkinKey] !== undefined ? row[checkinKey] : '').trim();
            const checkout = String(row[checkoutKey] !== undefined ? row[checkoutKey] : '').trim();
            const status = String(row[statusKey] !== undefined ? row[statusKey] : '').trim();

            // Color badge for status
            let statusBadge = '';
            const statusNorm = status.toLowerCase();
            if (statusNorm === 'hadir' || statusNorm === 'present') {
                statusBadge = `<span style="background: #e8fdf0; padding: 2px 8px; border-radius: 4px; color: #2ecc71; font-weight: 600; font-size: 11px;">${status}</span>`;
            } else if (statusNorm === 'alfa' || statusNorm === 'absent') {
                statusBadge = `<span style="background: #fdeded; padding: 2px 8px; border-radius: 4px; color: #e74c3c; font-weight: 600; font-size: 11px;">${status}</span>`;
            } else if (statusNorm === 'off') {
                statusBadge = `<span style="background: #f1f5f9; padding: 2px 8px; border-radius: 4px; color: #64748b; font-weight: 600; font-size: 11px;">${status}</span>`;
            } else {
                statusBadge = `<span style="background: #eff6ff; padding: 2px 8px; border-radius: 4px; color: #3b82f6; font-weight: 600; font-size: 11px;">${status || '-'}</span>`;
            }

            return `
                <tr class="attendance-row">
                    <td style="text-align: center; padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b;">${idx + 1}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #475569; font-weight: 500;">${empId || '-'}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #1e293b; font-weight: 600;">${empName || '-'}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #475569;">${tglVal || '-'}</td>
                    <td style="text-align: center; padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #1e293b;">${checkin || '-'}</td>
                    <td style="text-align: center; padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #1e293b;">${checkout || '-'}</td>
                    <td style="text-align: center; padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px;">${statusBadge}</td>
                </tr>
            `;
        }).join('');
    }
}

function clearInlineAttendanceTable() {
    const tbody = document.getElementById('inlineAttendanceTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: #cbd5e1; border: none !important; height: 290px; vertical-align: middle;">
                    <i class="fas fa-file-excel" style="font-size: 36px; margin-bottom: 12px; display: block; color: #cbd5e1;"></i>
                    No data. Click "Upload Excel" to preview attendance details.
                </td>
            </tr>
        `;
    }
}

function triggerInlineBrowseExcel() {
    const clientVal = document.getElementById('inlineUploadAbsensiClient').value;
    const periodVal = document.getElementById('inlineUploadAbsensiPeriod').value;
    if (!clientVal || !periodVal) {
        showToast('Please select Client and Period first before uploading.', 'warning');
        return;
    }
    document.getElementById('inlineFileAbsensiExcel').click();
}

function cancelInlineUpload() {
    // Hide left column and restore 1-column layout
    const leftCol = document.getElementById('inlineUploadFormCol');
    const container = document.getElementById('inlineUploadContainer');
    if (leftCol && container) {
        leftCol.style.display = 'none';
        container.style.gridTemplateColumns = '1fr';
    }

    // Reset file input & filename label
    document.getElementById('inlineFileAbsensiExcel').value = '';
    document.getElementById('inlineLabelAbsensiFilename').innerText = 'No file chosen';

    // Reload the database summary table
    onInlineAbsensiPeriodChanged();
}

function filterInlineAttendanceTable() {
    const searchVal = document.getElementById('searchInlineAttendance').value.toLowerCase();
    const rows = document.querySelectorAll('#inlineAttendanceTableBody .attendance-row');
    
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if (text.includes(searchVal)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function downloadInlineAbsensiTemplate() {
    const clientId = document.getElementById('inlineUploadAbsensiClient').value;
    const periodId = document.getElementById('inlineUploadAbsensiPeriod').value;

    if (!clientId || !periodId) {
        showToast('Please select Client and Period first.', 'warning');
        return;
    }

    const activePeriod = window.inlineUploadPeriods?.find(p => p.id == periodId);
    if (!activePeriod) {
        showToast('Period details not found.', 'error');
        return;
    }

    const employees = inlinePeriodAttendance || [];
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

            let isRestDay = false;
            if (workDaysConfig === 5) {
                isRestDay = (dayOfWeek === 0 || dayOfWeek === 6);
            } else if (workDaysConfig === 6) {
                isRestDay = (dayOfWeek === 0);
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
    
    const max_widths = [15, 25, 20, 12, 12, 12];
    worksheet['!cols'] = max_widths.map(w => ({ wch: w }));

    const filename = `Attendance_Template_${activePeriod.nama.replace(/\s+/g, '_')}.xlsx`;
    XLSX.writeFile(workbook, filename);
    showToast('Template downloaded successfully!', 'success');
}

let inlineParsedAttendanceData = [];

function handleInlineAbsensiFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    const clientId = document.getElementById('inlineUploadAbsensiClient').value;
    const periodId = document.getElementById('inlineUploadAbsensiPeriod').value;
    if (!clientId || !periodId) {
        showToast('Please select Client and Period first before selecting file.', 'warning');
        return;
    }

    document.getElementById('inlineLabelAbsensiFilename').innerText = file.name;
    const logsDiv = document.getElementById('inlineUploadAbsensiLogs');
    logsDiv.innerHTML = "Reading file...\n";

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            // Jangan pakai cellDates: true karena kolom jam akan terbaca sebagai Date object
            const workbook = XLSX.read(data, { type: 'array', cellDates: false });
            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];
            const json = XLSX.utils.sheet_to_json(worksheet, { raw: false, dateNF: 'yyyy-mm-dd' });

            if (json.length === 0) {
                logsDiv.innerHTML += "Error: Excel file is empty.\n";
                return;
            }

            logsDiv.innerHTML += `Parsed ${json.length} rows from sheet "${sheetName}".\n`;
            processInlineParsedAttendance(json);
        } catch (err) {
            console.error(err);
            logsDiv.innerHTML += `Error parsing file: ${err.message || err}\n`;
        }
    };
    reader.readAsArrayBuffer(file);
}

function processInlineParsedAttendance(rows) {
    const logsDiv = document.getElementById('inlineUploadAbsensiLogs');
    const employees = inlinePeriodAttendance || [];
    if (employees.length === 0) {
        logsDiv.innerHTML += "Error: No active employees loaded in context.\n";
        return;
    }

    // Toggle open the Left panel (Import Control) and make right column flex 1
    const leftCol = document.getElementById('inlineUploadFormCol');
    const container = document.getElementById('inlineUploadContainer');
    if (leftCol && container) {
        leftCol.style.display = 'block';
        container.style.gridTemplateColumns = '350px 1fr';
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
        const checkinRaw = row[checkinKey];
        const checkoutRaw = row[checkoutKey];
        const status = String(row[statusKey] || '').trim();

        // Konversi jam: jika angka (misal 8, 13, 17) jadikan "08:00", "13:00", "17:00"
        const toTimeStr = (val) => {
            if (!val && val !== 0) return null;
            
            // Jika Date object (Excel serial time dibaca sebagai Date)
            if (val instanceof Date) {
                const h = String(val.getHours()).padStart(2, '0');
                const m = String(val.getMinutes()).padStart(2, '0');
                return `${h}:${m}`;
            }
            
            // Jika angka desimal Excel (0.333 = 08:00, 0.708 = 17:00)
            if (typeof val === 'number') {
                // Jika < 1 berarti ini time fraction (0.0 - 1.0)
                if (val < 1) {
                    const totalMinutes = Math.round(val * 24 * 60);
                    const h = Math.floor(totalMinutes / 60);
                    const m = totalMinutes % 60;
                    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
                }
                // Jika >= 1 berarti jam polos (8, 13, 17)
                const h = Math.floor(val);
                const m = Math.round((val - h) * 60);
                return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
            }
            
            const s = String(val).trim();
            if (!s) return null;
            
            // Jika sudah format "HH:MM"
            if (/^\d{1,2}:\d{2}/.test(s)) return s.substring(0, 5);
            
            // Jika string angka saja ("8", "17")
            if (/^\d+$/.test(s)) return s.padStart(2, '0') + ':00';
            
            // Jika string tanggal (fallback dari Excel Date) — ambil jam saja
            const dateObj = new Date(s);
            if (!isNaN(dateObj.getTime())) {
                const h = String(dateObj.getHours()).padStart(2, '0');
                const m = String(dateObj.getMinutes()).padStart(2, '0');
                return `${h}:${m}`;
            }
            
            return s.substring(0, 5); // crop max 5 char "HH:MM"
        };

        const checkin = toTimeStr(checkinRaw);
        const checkout = toTimeStr(checkoutRaw);

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
                shift_name: row[keys.find(k => k.toLowerCase().replace(/\s+/g,'') === 'shift')] || null,
                status: status || 'Hadir'
            });
            validCount++;
        }
    });

    inlineParsedAttendanceData = finalAttendance;
    logsDiv.innerHTML += `\nSuccess: Ready to apply ${validCount} daily records.`;
    
    renderInlineAttendanceTable(rows, false);

    const btn = document.getElementById('btnSaveInlineUploadedAbsensi');
    if (btn) {
        btn.disabled = false;
        btn.style.cursor = 'pointer';
        btn.style.opacity = '1';
    }
}

async function saveInlineUploadedAbsensi() {
    if (inlineParsedAttendanceData.length === 0) {
        showToast('No parsed data to apply.', 'warning');
        return;
    }

    showToast('Applying attendance records...', 'info');
    
    const saveBtn = document.getElementById('btnSaveInlineUploadedAbsensi');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.style.opacity = '0.5';
    }

    try {
        const res = await fetch(`${API_URL}/attendance-logs/bulk`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ logs: inlineParsedAttendanceData })
        });

        if (res.ok) {
            showToast('Attendance logs successfully imported!', 'success');
            
            // Hide left column and restore 1-column layout
            const leftCol = document.getElementById('inlineUploadFormCol');
            const container = document.getElementById('inlineUploadContainer');
            if (leftCol && container) {
                leftCol.style.display = 'none';
                container.style.gridTemplateColumns = '1fr';
            }

            document.getElementById('inlineFileAbsensiExcel').value = '';
            document.getElementById('inlineLabelAbsensiFilename').innerText = 'No file chosen';
            
            const logsDiv = document.getElementById('inlineUploadAbsensiLogs');
            logsDiv.innerHTML += `\n\n🎉 All data successfully saved to database!`;
            
            onInlineAbsensiPeriodChanged();
            if (typeof window.renderCutOffTable === 'function') {
                window.renderCutOffTable();
            }
        } else {
            const err = await res.json();
            showToast(`Failed: ${err.message || 'Error occurred'}`, 'error');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.style.opacity = '1';
            }
        }
    } catch (err) {
        console.error(err);
        showToast(`Error saving: ${err.message || err}`, 'error');
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.style.opacity = '1';
        }
    }
}

Object.assign(window, {
    switchScheduleTab,
    onInlineAbsensiClientChanged,
    onInlineAbsensiPeriodChanged,
    downloadInlineAbsensiTemplate,
    handleInlineAbsensiFileSelect,
    saveInlineUploadedAbsensi,
    filterInlineAttendanceTable,
    triggerInlineBrowseExcel,
    cancelInlineUpload,
    renderInlineAttendanceTable
});

// End of app-schedule.js
