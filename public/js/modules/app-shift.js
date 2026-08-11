// Shift Schema & Allocation frontend module
let currentShiftSubTab = 'master';
let allShiftSchemes = [];
let allEmployeeShifts = [];

function switchShiftSubTab(tab) {
    currentShiftSubTab = tab;
    document.querySelectorAll('.sub-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.shift-subpanel').forEach(p => p.style.display = 'none');

    if (tab === 'master') {
        document.getElementById('subTabShiftMaster').classList.add('active');
        document.getElementById('subTabShiftMaster').style.borderBottom = '2px solid var(--primary-color)';
        document.getElementById('subTabShiftMaster').style.color = 'var(--primary-color)';
        
        document.getElementById('subTabShiftAllocation').style.borderBottom = '2px solid transparent';
        document.getElementById('subTabShiftAllocation').style.color = '#64748b';

        document.getElementById('panelShiftMaster').style.display = 'block';
        loadShiftSchemes();
    } else {
        document.getElementById('subTabShiftAllocation').classList.add('active');
        document.getElementById('subTabShiftAllocation').style.borderBottom = '2px solid var(--primary-color)';
        document.getElementById('subTabShiftAllocation').style.color = 'var(--primary-color)';
        
        document.getElementById('subTabShiftMaster').style.borderBottom = '2px solid transparent';
        document.getElementById('subTabShiftMaster').style.color = '#64748b';

        document.getElementById('panelShiftAllocation').style.display = 'block';
        loadShiftEmployeesDropdown();
        loadEmployeeShifts();
    }
}

// --- MASTER SKEMA SHIFT ---
function loadShiftSchemes() {
    fetch(`${API_URL}/shift-schemes`)
        .then(res => res.json())
        .then(data => {
            allShiftSchemes = data;
            renderShiftSchemesTable();
            populateShiftSchemeDropdowns();
        })
        .catch(err => {
            console.error('Error loading shift schemes:', err);
            showToast('Gagal memuat skema shift', 'error');
        });
}

function renderShiftSchemesTable() {
    const tbody = document.getElementById('shiftSchemesTableContainer');
    tbody.innerHTML = '';

    if (allShiftSchemes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 30px; color: #64748b;">
                    <i class="fas fa-info-circle" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                    Belum ada skema shift yang terdaftar.
                </td>
            </tr>
        `;
        return;
    }
    allShiftSchemes.forEach((s, idx) => {
        tbody.innerHTML += `
            <tr style="border-bottom: 1px solid #e2e8f0; hover:background:#f8fafc;">
                <td style="text-align: center; padding: 12px; font-weight: 600; color: #475569;">${idx + 1}</td>
                <td style="padding: 12px; font-weight: 700; color: #1e293b;">${s.name}</td>
                <td style="text-align: center; padding: 12px; font-weight: 600; color: #475569;">${s.start_time.substring(0,5)} - ${s.end_time.substring(0,5)}</td>
                <td style="text-align: center; padding: 12px; font-weight: 600; color: #475569;">${s.duration} Jam</td>
                <td style="text-align: center; padding: 12px; font-size: 13px; color: #475569;">
                    Late: ${s.grace_period_late}m<br>Early: ${s.grace_period_early}m
                </td>
                <td style="text-align: center; padding: 12px; display: flex; gap: 8px; justify-content: center;">
                    <button onclick="bukaModalShiftScheme('edit', ${s.id})" style="background:#3b82f6;color:white;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;"><i class="fas fa-edit"></i> Edit</button>
                    <button onclick="hapusShiftScheme(${s.id})" style="background:#ef4444;color:white;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;"><i class="fas fa-trash"></i> Hapus</button>
                </td>
            </tr>
        `;
    });
}

function populateShiftSchemeDropdowns() {
    const select = document.getElementById('assignShiftSchemeId');
    if (!select) return;
    select.innerHTML = '<option value="">-- Pilih Skema Shift --</option>';
    allShiftSchemes.forEach(s => {
        select.innerHTML += `<option value="${s.id}">${s.name} (${s.start_time.substring(0,5)} - ${s.end_time.substring(0,5)})</option>`;
    });
}

function bukaModalShiftScheme(mode, id = null) {
    const modal = document.getElementById('modalShiftScheme');
    const title = document.getElementById('modalShiftSchemeTitle');
    const form = document.getElementById('formShiftScheme');

    form.reset();
    document.getElementById('shiftSchemeId').value = '';

    if (mode === 'tambah') {
        title.innerText = 'Add Shift Scheme';
        modal.style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    } else {
        title.innerText = 'Edit Shift Scheme';
        const s = allShiftSchemes.find(x => x.id == id);
        if (s) {
            document.getElementById('shiftSchemeId').value = s.id;
            document.getElementById('shiftSchemeName').value = s.name;
            document.getElementById('shiftSchemeStartTime').value = s.start_time.substring(0,5);
            document.getElementById('shiftSchemeEndTime').value = s.end_time.substring(0,5);
            document.getElementById('shiftSchemeDuration').value = s.duration;
            document.getElementById('shiftSchemeGraceLate').value = s.grace_period_late;
            document.getElementById('shiftSchemeGraceEarly').value = s.grace_period_early;

            modal.style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }
    }
}

function tutupModalShiftScheme() {
    document.getElementById('modalShiftScheme').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function simpanShiftScheme(event) {
    event.preventDefault();
    const id = document.getElementById('shiftSchemeId').value;
    const body = {
        name: document.getElementById('shiftSchemeName').value,
        start_time: document.getElementById('shiftSchemeStartTime').value,
        end_time: document.getElementById('shiftSchemeEndTime').value,
        duration: parseFloat(document.getElementById('shiftSchemeDuration').value),
        grace_period_late: parseInt(document.getElementById('shiftSchemeGraceLate').value) || 0,
        grace_period_early: parseInt(document.getElementById('shiftSchemeGraceEarly').value) || 0
    };

    const url = id ? `${API_URL}/shift-schemes/${id}` : `${API_URL}/shift-schemes`;
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    })
    .then(res => res.json())
    .then(res => {
        showToast(res.message || 'Sukses menyimpan skema shift', 'success');
        tutupModalShiftScheme();
        loadShiftSchemes();
    })
    .catch(err => {
        console.error(err);
        showToast('Failed to save shift scheme', 'error');
    });
}

async function hapusShiftScheme(id) {
    if (!await showConfirm('Are you sure you want to delete this shift scheme?')) return;
    try {
        const res = await fetch(`${API_URL}/shift-schemes/${id}`, { method: 'DELETE' });
        if (res.ok) {
            showToast('Shift scheme deleted successfully', 'success');
            loadShiftSchemes();
        } else {
            const data = await res.json();
            showToast(data.message || data.messages?.error || 'Failed to delete shift scheme', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Terjadi kesalahan saat menghapus skema shift', 'error');
    }
}

// --- EMPLOYEE SHIFT ALLOCATION ---
function loadShiftEmployeesDropdown() {
    fetch(`${API_URL}/employees`)
        .then(res => res.json())
        .then(data => {
            const filterSelect = document.getElementById('shiftEmployeeFilterSelect');
            const assignSelect = document.getElementById('assignShiftEmployeeId');

            if (filterSelect) {
                const currentVal = filterSelect.value;
                filterSelect.innerHTML = '<option value="">All Employees</option>';
                // Handle different wrapper responses (e.g. data or direct array)
                const employees = data.data || data;
                employees.forEach(e => {
                    filterSelect.innerHTML += `<option value="${e.id}">${e.nama}</option>`;
                });
                filterSelect.value = currentVal;
            }

            if (assignSelect) {
                assignSelect.innerHTML = '<option value="">-- Select Employee --</option>';
                const employees = data.data || data;
                employees.forEach(e => {
                    assignSelect.innerHTML += `<option value="${e.id}">${e.nama}</option>`;
                });
            }
        })
        .catch(err => console.error(err));
}

function loadEmployeeShifts(employeeId = '') {
    const url = employeeId ? `${API_URL}/employee-shifts?employee_id=${employeeId}` : `${API_URL}/employee-shifts`;
    fetch(url)
        .then(res => res.json())
        .then(data => {
            allEmployeeShifts = data;
            renderEmployeeShiftsTable();
        })
        .catch(err => {
            console.error(err);
            showToast('Failed to load shift allocations', 'error');
        });
}

function renderEmployeeShiftsTable() {
    const tbody = document.getElementById('employeeShiftsTableContainer');
    tbody.innerHTML = '';

    if (allEmployeeShifts.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 30px; color: #64748b;">
                    <i class="fas fa-info-circle" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                    No employee shift allocations registered yet.
                </td>
            </tr>
        `;
        return;
    }

    const today = new Date().toISOString().substring(0, 10);

    allEmployeeShifts.forEach((es, idx) => {
        let statusBadge = '';
        if (es.end_date && es.end_date < today) {
            statusBadge = '<span style="background:#fee2e2;color:#991b1b;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600;">Expired</span>';
        } else {
            statusBadge = '<span style="background:#dcfce7;color:#166534;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600;">Active</span>';
        }

        const endDateText = es.end_date ? es.end_date : '<span style="color:#94a3b8;font-style:italic;">Indefinite</span>';

        tbody.innerHTML += `
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="text-align: center; padding: 12px; font-weight: 600; color: #475569;">${idx + 1}</td>
                <td style="padding: 12px; font-weight: 700; color: #1e293b;">${es.employee_name}</td>
                <td style="padding: 12px; font-weight: 600; color: #475569;">${es.shift_name} (${es.start_time.substring(0,5)} - ${es.end_time.substring(0,5)})</td>
                <td style="text-align: center; padding: 12px; font-weight: 600; color: #475569;">${es.start_date}</td>
                <td style="text-align: center; padding: 12px; font-weight: 600; color: #475569;">${endDateText}</td>
                <td style="text-align: center; padding: 12px;">${statusBadge}</td>
                <td style="text-align: center; padding: 12px;">
                    <button onclick="hapusEmployeeShift(${es.id})" style="background:#ef4444;color:white;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;"><i class="fas fa-trash-alt"></i> Delete</button>
                </td>
            </tr>
        `;
    });
}

function bukaModalAssignShift() {
    const modal = document.getElementById('modalAssignShift');
    document.getElementById('formAssignShift').reset();
    
    // Default start date is today
    document.getElementById('assignShiftStartDate').value = new Date().toISOString().substring(0, 10);

    modal.style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function tutupModalAssignShift() {
    document.getElementById('modalAssignShift').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function simpanAssignShift(event) {
    event.preventDefault();
    const body = {
        employee_id: parseInt(document.getElementById('assignShiftEmployeeId').value),
        shift_scheme_id: parseInt(document.getElementById('assignShiftSchemeId').value),
        start_date: document.getElementById('assignShiftStartDate').value,
        end_date: document.getElementById('assignShiftEndDate').value || null
    };

    fetch(`${API_URL}/employee-shifts`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    })
    .then(res => res.json())
    .then(res => {
        showToast(res.message || 'Shift assigned successfully', 'success');
        tutupModalAssignShift();
        loadEmployeeShifts(document.getElementById('shiftEmployeeFilterSelect').value);
    })
    .catch(err => {
        console.error(err);
        showToast('Failed to assign shift', 'error');
    });
}

async function hapusEmployeeShift(id) {
    if (!await showConfirm('Are you sure you want to delete this shift allocation?')) return;
    try {
        const res = await fetch(`${API_URL}/employee-shifts/${id}`, { method: 'DELETE' });
        if (res.ok) {
            showToast('Shift allocation deleted successfully', 'success');
            loadEmployeeShifts(document.getElementById('shiftEmployeeFilterSelect')?.value);
        } else {
            const data = await res.json();
            showToast(data.message || data.messages?.error || 'Failed to delete shift allocation', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Terjadi kesalahan saat menghapus alokasi shift', 'error');
    }
}

let parsedShiftSchemeData = [];

// --- EXCEL TEMPLATE DOWNLOAD FOR SHIFT SCHEME ---
function downloadShiftSchemeTemplate() {
    showToast('Downloading Shift Scheme template...', 'info');
    try {
        let sampleRows = [
            ['Shift Name', 'Start Time (HH:MM)', 'End Time (HH:MM)', 'Duration (Hours)', 'Late Tolerance (Minutes)', 'Early Leave Tolerance (Minutes)'],
            ['Shift Pagi', '08:00', '17:00', 8.0, 15, 15],
            ['Shift Siang', '13:00', '21:00', 8.0, 15, 15],
            ['Shift Malam', '21:00', '06:00', 8.0, 0, 0],
            ['Shift Halfday', '08:00', '13:00', 5.0, 10, 10]
        ];

        const worksheet = XLSX.utils.aoa_to_sheet(sampleRows);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Shift Scheme Template");

        const max_widths = [22, 22, 22, 18, 25, 30];
        worksheet['!cols'] = max_widths.map(w => ({ wch: w }));

        const filename = 'Shift_Scheme_Template.xlsx';
        XLSX.writeFile(workbook, filename);
        showToast('Template Skema Shift berhasil diunduh!', 'success');
    } catch (e) {
        console.error(e);
        showToast('Gagal mengunduh template: ' + (e.message || e), 'error');
    }
}

// --- EXCEL UPLOAD FOR SHIFT SCHEME ---
function bukaModalUploadShiftScheme() {
    const logsEl = document.getElementById('uploadShiftSchemeLogs');
    if (logsEl) logsEl.innerHTML = "Waiting for file...";
    
    const labelFilename = document.getElementById('labelShiftSchemeFilename');
    if (labelFilename) labelFilename.innerText = "No file selected";
    
    const fileInput = document.getElementById('fileShiftSchemeExcel');
    if (fileInput) fileInput.value = "";

    const text1 = document.getElementById('dropzoneShiftSchemeText1');
    const text2 = document.getElementById('dropzoneShiftSchemeText2');
    if (text1) text1.innerText = 'Pilih File Excel Master Skema Shift';
    if (text2) text2.innerText = 'Format kolom: Shift Name, Start Time (HH:MM), End Time (HH:MM), Late Tolerance, Early Tolerance';

    const saveBtn = document.getElementById('btnSaveUploadedShiftScheme');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.style.opacity = "0.5";
        saveBtn.style.cursor = "not-allowed";
    }

    parsedShiftSchemeData = [];

    const modal = document.getElementById('modalUploadShiftScheme');
    const overlay = document.getElementById('overlay');
    if (modal) modal.style.display = 'block';
    if (overlay) overlay.style.display = 'block';
}

function tutupModalUploadShiftScheme() {
    const modal = document.getElementById('modalUploadShiftScheme');
    const overlay = document.getElementById('overlay');
    if (modal) modal.style.display = 'none';
    if (overlay) overlay.style.display = 'none';
}

function handleShiftSchemeFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    processShiftSchemeFile(file);
}

function processShiftSchemeFile(file) {
    if (!file) return;

    const text1 = document.getElementById('dropzoneShiftSchemeText1');
    const text2 = document.getElementById('dropzoneShiftSchemeText2');
    if (text1) text1.innerText = file.name;
    if (text2) text2.innerText = 'File selected. Click or drag another file to replace.';

    const labelFilename = document.getElementById('labelShiftSchemeFilename');
    if (labelFilename) labelFilename.innerText = file.name;

    const logsDiv = document.getElementById('uploadShiftSchemeLogs');
    if (logsDiv) logsDiv.innerHTML = "Reading file...\n";

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array', cellDates: false });
            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];
            const json = XLSX.utils.sheet_to_json(worksheet, { raw: false });

            if (json.length === 0) {
                if (logsDiv) logsDiv.innerHTML += "Error: File Excel kosong.\n";
                return;
            }

            if (logsDiv) logsDiv.innerHTML += `Parsed ${json.length} baris dari sheet "${sheetName}".\n`;
            processParsedShiftScheme(json);
        } catch (err) {
            console.error(err);
            if (logsDiv) logsDiv.innerHTML += `Error parsing file: ${err.message || err}\n`;
        }
    };
    reader.readAsArrayBuffer(file);
}

function handleShiftSchemeDragOver(event) {
    event.preventDefault();
    const zone = document.getElementById('dropzoneShiftSchemeExcel');
    if (zone) {
        zone.style.borderColor = 'var(--primary-dark)';
        zone.style.backgroundColor = 'rgba(39, 174, 96, 0.18)';
    }
}

function handleShiftSchemeDragLeave(event) {
    event.preventDefault();
    const zone = document.getElementById('dropzoneShiftSchemeExcel');
    if (zone) {
        zone.style.borderColor = '#27ae60';
        zone.style.backgroundColor = 'rgba(39, 174, 96, 0.08)';
    }
}

function handleShiftSchemeDrop(event) {
    event.preventDefault();
    const zone = document.getElementById('dropzoneShiftSchemeExcel');
    if (zone) {
        zone.style.borderColor = '#27ae60';
        zone.style.backgroundColor = 'rgba(39, 174, 96, 0.08)';
    }
    
    if (event.dataTransfer.files && event.dataTransfer.files.length > 0) {
        const file = event.dataTransfer.files[0];
        const fileInput = document.getElementById('fileShiftSchemeExcel');
        if (fileInput) {
            fileInput.files = event.dataTransfer.files;
        }
        processShiftSchemeFile(file);
    }
}

function formatTimeToHHMM(timeStr) {
    if (!timeStr) return '';
    let s = timeStr.toString().trim();
    if (!isNaN(s) && parseFloat(s) < 1 && parseFloat(s) > 0) {
        const totalMinutes = Math.round(parseFloat(s) * 24 * 60);
        const hours = Math.floor(totalMinutes / 60);
        const mins = totalMinutes % 60;
        return `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}`;
    }
    if (s.includes(':')) {
        const parts = s.split(':');
        return `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}`;
    }
    return s;
}

function processParsedShiftScheme(json) {
    parsedShiftSchemeData = [];
    const logsDiv = document.getElementById('uploadShiftSchemeLogs');

    let validCount = 0;
    json.forEach((row, idx) => {
        const name = (row['Shift Name'] || row['Nama Shift'] || row['name'] || row['Name'] || '').toString().trim();
        const rawStartTime = row['Start Time (HH:MM)'] || row['Start Time'] || row['Jam Masuk'] || row['start_time'] || '';
        const rawEndTime = row['End Time (HH:MM)'] || row['End Time'] || row['Jam Keluar'] || row['end_time'] || '';
        const duration = parseFloat(row['Duration (Hours)'] || row['Duration'] || row['Durasi'] || row['Durasi (Jam)'] || row['duration'] || 0);
        const graceLate = parseInt(row['Late Tolerance (Minutes)'] || row['Late Tolerance'] || row['Toleransi Terlambat'] || row['grace_period_late'] || 0);
        const graceEarly = parseInt(row['Early Leave Tolerance (Minutes)'] || row['Early Tolerance'] || row['Toleransi Pulang Awal'] || row['grace_period_early'] || 0);

        const startTime = formatTimeToHHMM(rawStartTime);
        const endTime = formatTimeToHHMM(rawEndTime);

        if (name && startTime && endTime) {
            parsedShiftSchemeData.push({
                name,
                start_time: startTime,
                end_time: endTime,
                duration: duration,
                grace_period_late: graceLate,
                grace_period_early: graceEarly
            });
            validCount++;
        }
    });

    if (logsDiv) {
        logsDiv.innerHTML += `\nSukses: Berhasil memuat ${validCount} skema shift yang valid.\nKlik 'Apply & Save Shift Schemes' untuk menyimpan ke database.`;
    }

    if (validCount > 0) {
        const btn = document.getElementById('btnSaveUploadedShiftScheme');
        if (btn) {
            btn.disabled = false;
            btn.style.cursor = 'pointer';
            btn.style.opacity = '1';
        }
    }
}

async function saveUploadedShiftScheme() {
    if (parsedShiftSchemeData.length === 0) {
        showToast('Tidak ada data skema shift untuk disimpan.', 'warning');
        return;
    }

    const btn = document.getElementById('btnSaveUploadedShiftScheme');
    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
    }

    showToast('Menyimpan data skema shift...', 'info');

    try {
        const res = await fetch(`${API_URL}/shift-schemes/import`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                schemes: parsedShiftSchemeData
            })
        });

        const result = await res.json();
        if (res.ok && (result.success || result.status === 200 || result.imported_count >= 0)) {
            showToast(`Berhasil mengimpor ${result.imported_count || parsedShiftSchemeData.length} skema shift!`, 'success');
            tutupModalUploadShiftScheme();
            loadShiftSchemes();
        } else {
            showToast(result.message || 'Gagal mengimpor skema shift.', 'error');
            if (btn) {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
            }
        }
    } catch (err) {
        console.error(err);
        showToast('Error saving: ' + err.message, 'error');
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
        }
    }
}

// Global functions exposes
window.switchShiftSubTab = switchShiftSubTab;
window.loadShiftSchemes = loadShiftSchemes;
window.bukaModalShiftScheme = bukaModalShiftScheme;
window.tutupModalShiftScheme = tutupModalShiftScheme;
window.simpanShiftScheme = simpanShiftScheme;
window.hapusShiftScheme = hapusShiftScheme;
window.downloadShiftSchemeTemplate = downloadShiftSchemeTemplate;
window.bukaModalUploadShiftScheme = bukaModalUploadShiftScheme;
window.tutupModalUploadShiftScheme = tutupModalUploadShiftScheme;
window.handleShiftSchemeFileSelect = handleShiftSchemeFileSelect;
window.handleShiftSchemeDragOver = handleShiftSchemeDragOver;
window.handleShiftSchemeDragLeave = handleShiftSchemeDragLeave;
window.handleShiftSchemeDrop = handleShiftSchemeDrop;
window.processParsedShiftScheme = processParsedShiftScheme;
window.saveUploadedShiftScheme = saveUploadedShiftScheme;

window.loadShiftEmployeesDropdown = loadShiftEmployeesDropdown;
window.loadEmployeeShifts = loadEmployeeShifts;
window.bukaModalAssignShift = bukaModalAssignShift;
window.tutupModalAssignShift = tutupModalAssignShift;
window.simpanAssignShift = simpanAssignShift;
window.hapusEmployeeShift = hapusEmployeeShift;
