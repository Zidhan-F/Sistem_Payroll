// === Attendance Module ===

let currentAttendanceLogs = [];
let editingAttendanceLogId = null;

async function loadAttendanceClients() {
    const select = document.getElementById('attendanceClientSelect');
    if (!select) return;
    const currentVal = select.value;
    try {
        const res = await fetch(`${API_URL}/clients`);
        const clients = await res.json();
        select.innerHTML = '<option value="">-- Pilih Client --</option>';
        clients.forEach(c => {
            select.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
        });
        if (currentVal && Array.from(select.options).some(opt => opt.value == currentVal)) {
            select.value = currentVal;
            if (typeof loadAttendanceLogs === 'function') {
                loadAttendanceLogs();
            }
        }
        if (typeof syncCustomClientDropdown === 'function') {
            syncCustomClientDropdown();
        }
    } catch(e) { console.error(e); }
}

async function loadAttendanceLogs() {
    const tbody = document.getElementById('attendanceTableBody');
    if (!tbody) return;
    const clientId = document.getElementById('attendanceClientSelect')?.value;
    const bulan = document.getElementById('attendanceMonthSelect')?.value;
    const tahun = document.getElementById('attendanceYearSelect')?.value;

    const searchInput = document.getElementById('attendanceSearchInput');
    if (searchInput) searchInput.value = '';

    // Hide late upload banners by default
    document.querySelectorAll('#attendanceLateUploadRemark').forEach(banner => banner.style.display = 'none');

    if (!clientId) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-clipboard-check" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
            Pilih client terlebih dahulu.</td></tr>`;
        return;
    }

    // Sinkronisasi selectedClientId secara global
    window.selectedClientId = clientId;

    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:40px;color:#94a3b8;">
        <i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block;"></i>Memuat data...</td></tr>`;

    try {
        const res = await fetch(`${API_URL}/attendance-logs?client_id=${clientId}&bulan=${bulan}&tahun=${tahun}`);
        const data = await res.json();
        currentAttendanceLogs = Array.isArray(data) ? data : (data.data || []);

        // Handle late upload banner
        const lateUploadBanners = document.querySelectorAll('#attendanceLateUploadRemark');
        const cutoffLabels = document.querySelectorAll('#attendanceCutoffDateLabel');
        
        if (data && data.is_late_upload) {
            let formattedDate = data.cutoff_date || '';
            if (data.cutoff_date) {
                const dateParts = data.cutoff_date.split('-');
                if (dateParts.length === 3) {
                    const months = [
                        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];
                    const day = parseInt(dateParts[2]);
                    const monthIdx = parseInt(dateParts[1]) - 1;
                    const year = dateParts[0];
                    if (monthIdx >= 0 && monthIdx < 12) {
                        formattedDate = `${day} ${months[monthIdx]} ${year}`;
                    }
                }
            }
            
            lateUploadBanners.forEach(banner => {
                banner.style.display = 'flex';
            });
            cutoffLabels.forEach(label => {
                label.innerText = formattedDate;
            });
        } else {
            lateUploadBanners.forEach(banner => {
                banner.style.display = 'none';
            });
        }

        if (!currentAttendanceLogs || currentAttendanceLogs.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:40px;color:#94a3b8;">
                <i class="fas fa-clipboard-check" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
                Belum ada data kehadiran untuk periode ini.</td></tr>`;
            return;
        }

        tbody.innerHTML = currentAttendanceLogs.map((a, i) => {
            const dateParts = a.tanggal.split('-');
            const d = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
            const tanggalFormatted = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });
            
            // Build shift status badges
            let shiftBadges = '';
            if (parseInt(a.is_incomplete) === 1) {
                shiftBadges += `<span style="background:#fee2e2;color:#991b1b;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:700;margin-left:5px;">Incomplete</span>`;
            }
            if (parseInt(a.is_rapel) === 1) {
                shiftBadges += `<span style="background:#dbeafe;color:#1e40af;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:700;margin-left:5px;">Rapel (${a.payout_period})</span>`;
            }

            const statusNorm = (a.status || '').toLowerCase().trim();
            let isLibur = false;
            if (statusNorm === 'day off' || statusNorm === 'off') {
                shiftBadges += `<span style="background:#f1f5f9;color:#475569;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:700;margin-left:5px;">Day Off</span>`;
                isLibur = true;
            } else if (statusNorm === 'alfa' || statusNorm === 'absent') {
                shiftBadges += `<span style="background:#fee2e2;color:#b91c1c;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:700;margin-left:5px;">Alfa</span>`;
            } else if (statusNorm === 'sakit' || statusNorm === 'sick') {
                shiftBadges += `<span style="background:#fef3c7;color:#b45309;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:700;margin-left:5px;">Sakit</span>`;
            } else if (statusNorm === 'izin' || statusNorm === 'leave') {
                shiftBadges += `<span style="background:#e0f2fe;color:#0369a1;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:700;margin-left:5px;">Izin</span>`;
            } else if (statusNorm === 'hadir' || statusNorm === 'present') {
                shiftBadges += `<span style="background:#e8fdf0;color:#15803d;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:700;margin-left:5px;">Hadir</span>`;
            }

            const rowStyle = isLibur 
                ? 'border-bottom: 1px solid #f1f5f9; background-color: #f8fafc; color: #94a3b8; opacity: 0.85;'
                : 'border-bottom: 1px solid #f1f5f9;';

            return `<tr data-employee-id="${a.employee_id || ''}" data-employee-name="${(a.employee_name || '').toLowerCase()}" data-shift-name="${(a.shift_name || 'default').toLowerCase()}" style="${rowStyle}">
                <td style="text-align:center;padding:12px;color:#64748b;">${i+1}</td>
                <td style="padding:12px;font-weight:600;color:#1e293b;">${a.employee_name || '-'}</td>
                <td style="text-align:center;padding:12px;color:#475569;">${tanggalFormatted}</td>
                <td style="padding:12px;font-weight:600;color:#475569;">
                    ${a.shift_name || '<span style="color:#94a3b8;font-style:italic;">Default</span>'}
                    ${shiftBadges}
                </td>
                <td style="text-align:center;padding:12px;color:#475569;">${a.jam_masuk || '-'}</td>
                <td style="text-align:center;padding:12px;color:#475569;">${a.jam_keluar || '-'}</td>
                <td style="text-align:center;padding:12px;color:#475569;font-weight:700;">
                    ${parseFloat(a.calculated_work_hours || 0).toFixed(1)}j<br>
                    <small style="color:var(--success);">OT: ${parseFloat(a.calculated_overtime_hours || 0).toFixed(1)}j</small>
                </td>

                <td style="text-align:center;padding:12px;white-space:nowrap;">
                    <button onclick="editAttendanceLog(${a.id})" style="background:#f1f5f9;color:#475569;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;margin-right:4px;" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteAttendanceLog(${a.id})" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:40px;color:#ef4444;">Gagal memuat data: ${e.message}</td></tr>`;
    }
}

async function loadAttendanceEmployees() {
    const select = document.getElementById('attendanceEmployeeSelect');
    if (!select) return;
    const clientId = document.getElementById('attendanceClientSelect')?.value;
    if (!clientId) { select.innerHTML = '<option value="">-- Pilih Karyawan --</option>'; return; }

    try {
        const res = await fetch(`${API_URL}/employees?client_id=${clientId}`);
        const emps = await res.json();
        select.innerHTML = '<option value="">-- Pilih Karyawan --</option>';
        (emps.data || emps).forEach(e => {
            select.innerHTML += `<option value="${e.id}">${e.nama}</option>`;
        });
    } catch(e) { console.error(e); }
}

function bukaModalAttendance() {
    const clientId = document.getElementById('attendanceClientSelect')?.value;
    if (!clientId) { showToast('Pilih client terlebih dahulu!', 'error'); return; }
    document.getElementById('attendanceForm')?.reset();
    document.getElementById('attendanceModalTitle').innerText = 'Input Kehadiran';
    editingAttendanceLogId = null;
    const employeeSelect = document.getElementById('attendanceEmployeeSelect');
    if (employeeSelect) {
        employeeSelect.disabled = false;
    }
    const tanggalInput = document.getElementById('attendanceTanggal');
    if (tanggalInput) {
        tanggalInput.disabled = false;
        if (!tanggalInput.value) {
            const today = new Date().toISOString().split('T')[0];
            tanggalInput.value = today;
        }
    }
    loadAttendanceEmployees();
    openModal('attendanceModal');

    // Bind auto-check listeners setelah modal terbuka
    setTimeout(() => {
        const empSelect = document.getElementById('attendanceEmployeeSelect');
        const dateInput = document.getElementById('attendanceTanggal');
        if (empSelect && !empSelect._autoCheckBound) {
            empSelect.addEventListener('change', checkExistingAttendance);
            empSelect._autoCheckBound = true;
        }
        if (dateInput && !dateInput._autoCheckBound) {
            dateInput.addEventListener('change', checkExistingAttendance);
            dateInput._autoCheckBound = true;
        }
    }, 100);
}

// Auto-deteksi data kehadiran yang sudah ada saat karyawan & tanggal dipilih
async function checkExistingAttendance() {
    const employeeId = document.getElementById('attendanceEmployeeSelect')?.value;
    const tanggal = document.getElementById('attendanceTanggal')?.value;

    // Hanya cek jika keduanya sudah terisi dan bukan sedang mode edit manual
    if (!employeeId || !tanggal || editingAttendanceLogId) return;

    try {
        const res = await fetch(`${API_URL}/attendance-logs?employee_id=${employeeId}&tanggal=${tanggal}`);
        const data = await res.json();
        const logs = Array.isArray(data) ? data : (data.data || []);

        if (logs.length > 0) {
            const existing = logs[0];
            // Auto-fill form dengan data yang sudah ada
            const statusInput = document.getElementById('attendanceStatus');
            const jamMasukInput = document.getElementById('attendanceJamMasuk');
            const jamKeluarInput = document.getElementById('attendanceJamKeluar');
            const keteranganInput = document.getElementById('attendanceKeterangan');

            if (statusInput) statusInput.value = existing.status || 'Hadir';
            if (jamMasukInput) jamMasukInput.value = existing.jam_masuk || '';
            if (jamKeluarInput) jamKeluarInput.value = existing.jam_keluar || '';
            if (keteranganInput) keteranganInput.value = existing.keterangan || '';

            // Beralih ke mode edit
            editingAttendanceLogId = existing.id;
            document.getElementById('attendanceModalTitle').innerText = 'Edit Kehadiran (Data Ditemukan)';
            showToast('Data kehadiran ditemukan, form telah diisi otomatis.', 'info');
        } else {
            // Reset ke mode tambah baru
            editingAttendanceLogId = null;
            document.getElementById('attendanceModalTitle').innerText = 'Input Kehadiran';
        }
    } catch (e) {
        console.error('Auto-check attendance error:', e);
    }
}

async function simpanAttendance(e) {
    e.preventDefault();
    const employeeId = document.getElementById('attendanceEmployeeSelect')?.value;
    const tanggal = document.getElementById('attendanceTanggal')?.value;
    const status = document.getElementById('attendanceStatus')?.value;
    const jamMasuk = document.getElementById('attendanceJamMasuk')?.value;
    const jamKeluar = document.getElementById('attendanceJamKeluar')?.value;
    const keterangan = document.getElementById('attendanceKeterangan')?.value;

    if (!employeeId || !tanggal) {
        showToast('Karyawan dan tanggal wajib diisi!', 'error');
        return;
    }

    try {
        let url = `${API_URL}/attendance-logs`;
        let method = 'POST';
        if (editingAttendanceLogId) {
            url += `/${editingAttendanceLogId}`;
            method = 'PUT';
        }
        
        const res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ employee_id: employeeId, tanggal, status, jam_masuk: jamMasuk, jam_keluar: jamKeluar, keterangan })
        });
        const data = await res.json();
        if (!res.ok) {
            let errorMsg = data.messages?.error || data.message || data.error;
            if (data.messages && typeof data.messages === 'object') {
                errorMsg = Object.values(data.messages).join(', ');
            } else if (data.messages && typeof data.messages === 'string') {
                errorMsg = data.messages;
            }
            showToast(errorMsg || 'Gagal menyimpan kehadiran!', 'error');
            return;
        }
        showToast(data.message || 'Kehadiran berhasil disimpan!', 'success');
        closeModal('attendanceModal');

        // Auto-update filter dropdowns to the month and year of the saved log
        const dateParts = tanggal.split('-');
        if (dateParts.length === 3) {
            const yearVal = parseInt(dateParts[0]);
            const monthVal = parseInt(dateParts[1]);
            const monthSelect = document.getElementById('attendanceMonthSelect');
            const yearSelect = document.getElementById('attendanceYearSelect');
            if (monthSelect) monthSelect.value = monthVal;
            if (yearSelect) yearSelect.value = yearVal;
        }

        loadAttendanceLogs();
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
    }
}

async function deleteAttendanceLog(id) {
    if (!await showConfirm('Yakin ingin menghapus log kehadiran ini?')) return;
    try {
        await fetch(`${API_URL}/attendance-logs/${id}`, { method: 'DELETE' });
        showToast('Log kehadiran berhasil dihapus!', 'success');
        loadAttendanceLogs();
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
    }
}

function downloadMainAbsensiTemplate() {
    try {
        const headers = [
            {
                'Employee ID': '',
                'Nama': '',
                'Tgl dan Hari': '',
                'Shift': '',
                'Jam Masuk': '',
                'Jam Keluar': ''
            }
        ];

        const worksheet = XLSX.utils.json_to_sheet(headers);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Attendance Template");
        
        // Auto-fit column widths
        const max_widths = [15, 25, 20, 15, 12, 12];
        worksheet['!cols'] = max_widths.map(w => ({ wch: w }));

        const filename = `Attendance_Template_Blank.xlsx`;
        XLSX.writeFile(workbook, filename);
        showToast('Template berhasil didownload!', 'success');
    } catch (err) {
        console.error(err);
        showToast('Gagal memuat template: ' + err.message, 'error');
    }
}

window.downloadMainAbsensiTemplate = downloadMainAbsensiTemplate;

function filterAttendanceTable() {
    const input = document.getElementById('attendanceSearchInput');
    if (!input) return;
    const filter = input.value.toLowerCase().trim();
    const tbody = document.getElementById('attendanceTableBody');
    if (!tbody) return;
    const rows = tbody.getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        if (row.querySelector('td[colspan]')) {
            continue;
        }
        
        const empId = (row.getAttribute('data-employee-id') || '').toLowerCase();
        const empName = (row.getAttribute('data-employee-name') || '').toLowerCase();
        const shiftName = (row.getAttribute('data-shift-name') || '').toLowerCase();
        
        if (empName.includes(filter) || empId.includes(filter) || shiftName.includes(filter)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    }
}

window.filterAttendanceTable = filterAttendanceTable;

function syncCustomClientDropdown() {
    const nativeSelect = document.getElementById('attendanceClientSelect');
    const container = document.getElementById('attendanceClientOptionsContainer');
    const selectedTextSpan = document.getElementById('attendanceClientSelectedText');
    if (!nativeSelect || !container) return;

    container.innerHTML = '';

    Array.from(nativeSelect.options).forEach(option => {
        if (!option.value) return; // Skip placeholder option
        const optionDiv = document.createElement('div');
        optionDiv.innerText = option.text;
        optionDiv.setAttribute('data-value', option.value);
        optionDiv.style.padding = '10px 14px';
        optionDiv.style.borderRadius = '6px';
        optionDiv.style.cursor = 'pointer';
        optionDiv.style.fontSize = '13.5px';
        optionDiv.style.color = '#334155';
        optionDiv.style.fontWeight = '600';
        optionDiv.style.transition = 'all 0.15s';
        
        optionDiv.onmouseover = () => {
            optionDiv.style.background = '#f1f5f9';
            optionDiv.style.color = 'var(--primary-color)';
        };
        optionDiv.onmouseout = () => {
            optionDiv.style.background = 'transparent';
            optionDiv.style.color = '#334155';
        };
        optionDiv.onclick = () => {
            nativeSelect.value = option.value;
            if (selectedTextSpan) {
                selectedTextSpan.innerText = option.text;
            }
            
            const event = new Event('change', { bubbles: true });
            nativeSelect.dispatchEvent(event);
            
            closeAttendanceClientDropdown();
        };
        
        container.appendChild(optionDiv);
    });

    const selectedOption = nativeSelect.options[nativeSelect.selectedIndex];
    if (selectedOption && selectedTextSpan) {
        selectedTextSpan.innerText = selectedOption.text;
    }
}

function toggleAttendanceClientDropdown(event) {
    event.stopPropagation();
    const panel = document.getElementById('attendanceClientDropdownPanel');
    const arrow = document.querySelector('#attendanceClientDropdownTrigger i');
    if (!panel) return;

    const isVisible = panel.style.display === 'block';
    
    closeAttendanceClientDropdown();
    
    if (!isVisible) {
        panel.style.display = 'block';
        if (arrow) arrow.style.transform = 'translateY(-50%) rotate(180deg)';
        const searchInput = document.getElementById('attendanceClientSearchInput');
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
            filterClientDropdownOptions();
        }
    }
}

function closeAttendanceClientDropdown() {
    const panel = document.getElementById('attendanceClientDropdownPanel');
    const arrow = document.querySelector('#attendanceClientDropdownTrigger i');
    if (panel) panel.style.display = 'none';
    if (arrow) arrow.style.transform = 'translateY(-50%)';
}

function filterClientDropdownOptions() {
    const searchVal = (document.getElementById('attendanceClientSearchInput')?.value || '').toLowerCase().trim();
    const container = document.getElementById('attendanceClientOptionsContainer');
    if (!container) return;
    const divs = container.getElementsByTagName('div');
    
    for (let i = 0; i < divs.length; i++) {
        const div = divs[i];
        const text = div.innerText.toLowerCase();
        if (text.includes(searchVal)) {
            div.style.display = 'block';
        } else {
            div.style.display = 'none';
        }
    }
}

function editAttendanceLog(id) {
    const log = currentAttendanceLogs.find(x => x.id === id);
    if (!log) return;
    
    editingAttendanceLogId = id;
    document.getElementById('attendanceModalTitle').innerText = 'Edit Kehadiran';
    
    const employeeSelect = document.getElementById('attendanceEmployeeSelect');
    if (employeeSelect) {
        employeeSelect.innerHTML = `<option value="${log.employee_id}">${log.employee_name || 'Karyawan'}</option>`;
        employeeSelect.value = log.employee_id;
        employeeSelect.disabled = true;
    }
    
    const tanggalInput = document.getElementById('attendanceTanggal');
    if (tanggalInput) {
        tanggalInput.value = log.tanggal;
        tanggalInput.disabled = true;
    }
    
    const statusInput = document.getElementById('attendanceStatus');
    if (statusInput) statusInput.value = log.status;
    
    const jamMasukInput = document.getElementById('attendanceJamMasuk');
    if (jamMasukInput) jamMasukInput.value = log.jam_masuk || '';
    
    const jamKeluarInput = document.getElementById('attendanceJamKeluar');
    if (jamKeluarInput) jamKeluarInput.value = log.jam_keluar || '';
    
    const keteranganInput = document.getElementById('attendanceKeterangan');
    if (keteranganInput) keteranganInput.value = log.keterangan || '';
    
    openModal('attendanceModal');
}

document.addEventListener('click', function(e) {
    const wrapper = document.querySelector('.custom-select-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        closeAttendanceClientDropdown();
    }
});

window.syncCustomClientDropdown = syncCustomClientDropdown;
window.toggleAttendanceClientDropdown = toggleAttendanceClientDropdown;
window.closeAttendanceClientDropdown = closeAttendanceClientDropdown;
window.filterClientDropdownOptions = filterClientDropdownOptions;
window.editAttendanceLog = editAttendanceLog;
window.bukaModalAttendance = bukaModalAttendance;
window.simpanAttendance = simpanAttendance;
window.loadAttendanceLogs = loadAttendanceLogs;
window.loadAttendanceClients = loadAttendanceClients;

