// === Attendance Module ===

async function loadAttendanceClients() {
    const select = document.getElementById('attendanceClientSelect');
    if (!select) return;
    try {
        const res = await fetch(`${API_URL}/clients`);
        const clients = await res.json();
        select.innerHTML = '<option value="">-- Pilih Client --</option>';
        clients.forEach(c => {
            select.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
        });
    } catch(e) { console.error(e); }
}

async function loadAttendanceLogs() {
    const tbody = document.getElementById('attendanceTableBody');
    if (!tbody) return;
    const clientId = document.getElementById('attendanceClientSelect')?.value;
    const bulan = document.getElementById('attendanceMonthSelect')?.value;
    const tahun = document.getElementById('attendanceYearSelect')?.value;

    if (!clientId) {
        tbody.innerHTML = `<tr><td colspan="10" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-clipboard-check" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
            Pilih client terlebih dahulu.</td></tr>`;
        return;
    }

    tbody.innerHTML = `<tr><td colspan="10" style="text-align:center;padding:40px;color:#94a3b8;">
        <i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block;"></i>Memuat data...</td></tr>`;

    try {
        const res = await fetch(`${API_URL}/attendance-logs?client_id=${clientId}&bulan=${bulan}&tahun=${tahun}`);
        const data = await res.json();

        if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" style="text-align:center;padding:40px;color:#94a3b8;">
                <i class="fas fa-clipboard-check" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
                Belum ada data kehadiran untuk periode ini.</td></tr>`;
            return;
        }

        const statusColors = {
            'Hadir': 'background:#dcfce7;color:#166534;',
            'Absen': 'background:#fee2e2;color:#991b1b;',
            'Sakit': 'background:#fef3c7;color:#92400e;',
            'Izin': 'background:#dbeafe;color:#1e40af;',
            'Cuti': 'background:#f3e8ff;color:#6b21a8;',
        };

        tbody.innerHTML = data.map((a, i) => {
            const d = new Date(a.tanggal);
            const tanggalFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            const statusStyle = statusColors[a.status] || 'background:#f1f5f9;color:#475569;';
            
            // Build shift status badges
            let shiftBadges = '';
            if (parseInt(a.is_incomplete) === 1) {
                shiftBadges += `<span style="background:#fee2e2;color:#991b1b;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:700;margin-left:5px;">Incomplete</span>`;
            }
            if (parseInt(a.is_rapel) === 1) {
                shiftBadges += `<span style="background:#dbeafe;color:#1e40af;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:700;margin-left:5px;">Rapel (${a.payout_period})</span>`;
            }

            return `<tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="text-align:center;padding:12px;color:#64748b;">${i+1}</td>
                <td style="padding:12px;font-weight:600;color:#1e293b;">${a.employee_name || '-'}</td>
                <td style="text-align:center;padding:12px;color:#475569;">${tanggalFormatted}</td>
                <td style="padding:12px;font-weight:600;color:#475569;">${a.shift_name || '<span style="color:#94a3b8;font-style:italic;">Default</span>'}</td>
                <td style="text-align:center;padding:12px;">
                    <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;${statusStyle}">${a.status}</span>
                </td>
                <td style="text-align:center;padding:12px;color:#475569;">${a.jam_masuk || '-'}</td>
                <td style="text-align:center;padding:12px;color:#475569;">${a.jam_keluar || '-'}</td>
                <td style="text-align:center;padding:12px;color:#475569;font-weight:700;">
                    ${parseFloat(a.calculated_work_hours || 0).toFixed(1)}j<br>
                    <small style="color:var(--success);">OT: ${parseFloat(a.calculated_overtime_hours || 0).toFixed(1)}j</small>
                </td>
                <td style="text-align:center;padding:12px;color:#ef4444;font-weight:600;">
                    ${parseFloat(a.late_hours || 0) > 0 ? parseFloat(a.late_hours).toFixed(1) + 'j' : '-'}
                </td>
                <td style="text-align:center;padding:12px;color:#f59e0b;font-weight:600;">
                    ${parseFloat(a.early_leave_hours || 0) > 0 ? parseFloat(a.early_leave_hours).toFixed(1) + 'j' : '-'}
                </td>
                <td style="padding:12px;color:#475569;max-width:220px;overflow:hidden;text-overflow:ellipsis;">
                    <span>${a.keterangan || '-'}</span>
                    ${shiftBadges}
                </td>
                <td style="text-align:center;padding:12px;">
                    <button onclick="deleteAttendanceLog(${a.id})" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="10" style="text-align:center;padding:40px;color:#ef4444;">Gagal memuat data: ${e.message}</td></tr>`;
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
    loadAttendanceEmployees();
    openModal('attendanceModal');
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
        const res = await fetch(`${API_URL}/attendance-logs`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ employee_id: employeeId, tanggal, status, jam_masuk: jamMasuk, jam_keluar: jamKeluar, keterangan })
        });
        const data = await res.json();
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
