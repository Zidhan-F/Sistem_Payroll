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
        const jenisList = [];
        if (parseInt(s.is_holiday_shift) === 1) jenisList.push('<span style="background:#fef08a;color:#854d0e;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600;">Hari Libur</span>');
        if (parseInt(s.is_overtime_shift) === 1) jenisList.push('<span style="background:#dbeafe;color:#1e40af;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600;">Lembur Penuh</span>');
        if (jenisList.length === 0) jenisList.push('<span style="background:#f1f5f9;color:#475569;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600;">Standar</span>');

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
        title.innerText = 'Tambah Skema Shift';
        modal.style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    } else {
        title.innerText = 'Edit Skema Shift';
        const s = allShiftSchemes.find(x => x.id == id);
        if (s) {
            document.getElementById('shiftSchemeId').value = s.id;
            document.getElementById('shiftSchemeName').value = s.name;
            document.getElementById('shiftSchemeStartTime').value = s.start_time.substring(0,5);
            document.getElementById('shiftSchemeEndTime').value = s.end_time.substring(0,5);
            document.getElementById('shiftSchemeDuration').value = s.duration;
            document.getElementById('shiftSchemeGraceLate').value = s.grace_period_late;
            document.getElementById('shiftSchemeGraceEarly').value = s.grace_period_early;
            document.getElementById('shiftSchemeIsHoliday').checked = parseInt(s.is_holiday_shift) === 1;
            document.getElementById('shiftSchemeIsOvertime').checked = parseInt(s.is_overtime_shift) === 1;

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
        grace_period_early: parseInt(document.getElementById('shiftSchemeGraceEarly').value) || 0,
        is_holiday_shift: document.getElementById('shiftSchemeIsHoliday').checked ? 1 : 0,
        is_overtime_shift: document.getElementById('shiftSchemeIsOvertime').checked ? 1 : 0
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
        showToast('Gagal menyimpan skema shift', 'error');
    });
}

function hapusShiftScheme(id) {
    if (confirm('Apakah Anda yakin ingin menghapus skema shift ini?')) {
        fetch(`${API_URL}/shift-schemes/${id}`, { method: 'DELETE' })
            .then(res => {
                if (res.ok) {
                    showToast('Skema shift berhasil dihapus', 'success');
                    loadShiftSchemes();
                } else {
                    res.json().then(data => {
                        showToast(data.messages?.error || 'Gagal menghapus skema shift', 'error');
                    });
                }
            })
            .catch(err => console.error(err));
    }
}

// --- ALOKASI SHIFT KARYAWAN ---
function loadShiftEmployeesDropdown() {
    fetch(`${API_URL}/employees`)
        .then(res => res.json())
        .then(data => {
            const filterSelect = document.getElementById('shiftEmployeeFilterSelect');
            const assignSelect = document.getElementById('assignShiftEmployeeId');

            if (filterSelect) {
                const currentVal = filterSelect.value;
                filterSelect.innerHTML = '<option value="">Semua Karyawan</option>';
                // Handle different wrapper responses (e.g. data or direct array)
                const employees = data.data || data;
                employees.forEach(e => {
                    filterSelect.innerHTML += `<option value="${e.id}">${e.nama}</option>`;
                });
                filterSelect.value = currentVal;
            }

            if (assignSelect) {
                assignSelect.innerHTML = '<option value="">-- Pilih Karyawan --</option>';
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
            showToast('Gagal memuat alokasi shift', 'error');
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
                    Belum ada alokasi shift karyawan yang terdaftar.
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

        const endDateText = es.end_date ? es.end_date : '<span style="color:#94a3b8;font-style:italic;">Seterusnya</span>';

        tbody.innerHTML += `
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="text-align: center; padding: 12px; font-weight: 600; color: #475569;">${idx + 1}</td>
                <td style="padding: 12px; font-weight: 700; color: #1e293b;">${es.employee_name}</td>
                <td style="padding: 12px; font-weight: 600; color: #475569;">${es.shift_name} (${es.start_time.substring(0,5)} - ${es.end_time.substring(0,5)})</td>
                <td style="text-align: center; padding: 12px; font-weight: 600; color: #475569;">${es.start_date}</td>
                <td style="text-align: center; padding: 12px; font-weight: 600; color: #475569;">${endDateText}</td>
                <td style="text-align: center; padding: 12px;">${statusBadge}</td>
                <td style="text-align: center; padding: 12px;">
                    <button onclick="hapusEmployeeShift(${es.id})" style="background:#ef4444;color:white;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;"><i class="fas fa-trash-alt"></i> Hapus</button>
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
        showToast(res.message || 'Sukses menugaskan shift', 'success');
        tutupModalAssignShift();
        loadEmployeeShifts(document.getElementById('shiftEmployeeFilterSelect').value);
    })
    .catch(err => {
        console.error(err);
        showToast('Gagal menugaskan shift', 'error');
    });
}

function hapusEmployeeShift(id) {
    if (confirm('Apakah Anda yakin ingin menghapus alokasi shift ini?')) {
        fetch(`${API_URL}/employee-shifts/${id}`, { method: 'DELETE' })
            .then(res => {
                if (res.ok) {
                    showToast('Alokasi shift berhasil dihapus', 'success');
                    loadEmployeeShifts(document.getElementById('shiftEmployeeFilterSelect').value);
                } else {
                    showToast('Gagal menghapus alokasi shift', 'error');
                }
            })
            .catch(err => console.error(err));
    }
}

// Global functions exposes
window.switchShiftSubTab = switchShiftSubTab;
window.loadShiftSchemes = loadShiftSchemes;
window.bukaModalShiftScheme = bukaModalShiftScheme;
window.tutupModalShiftScheme = tutupModalShiftScheme;
window.simpanShiftScheme = simpanShiftScheme;
window.hapusShiftScheme = hapusShiftScheme;

window.loadShiftEmployeesDropdown = loadShiftEmployeesDropdown;
window.loadEmployeeShifts = loadEmployeeShifts;
window.bukaModalAssignShift = bukaModalAssignShift;
window.tutupModalAssignShift = tutupModalAssignShift;
window.simpanAssignShift = simpanAssignShift;
window.hapusEmployeeShift = hapusEmployeeShift;
