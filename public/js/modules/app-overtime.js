// === Overtime Module ===

async function loadOvertimeClients() {
    const select = document.getElementById('overtimeClientSelect');
    if (!select) return;
    try {
        const res = await fetch(`${API_URL}/clients`);
        const clients = await res.json();
        select.innerHTML = '<option value="">-- Pilih Client --</option>';
        clients.forEach(c => {
            select.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
        });
        loadOvertimeLogs();
    } catch(e) { console.error(e); }
}

async function loadOvertimeLogs() {
    const tbody = document.getElementById('overtimeTableBody');
    if (!tbody) return;
    const clientId = document.getElementById('overtimeClientSelect')?.value;
    const bulan = document.getElementById('overtimeMonthSelect')?.value;
    const tahun = document.getElementById('overtimeYearSelect')?.value;

    const selectAllCheckbox = document.getElementById('overtimeSelectAll');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    document.getElementById('overtimeBulkActions').style.display = 'none';

    if (!clientId) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
            Silakan pilih client terlebih dahulu untuk menampilkan data lembur.</td></tr>`;
        document.getElementById('otSummaryContainer').style.display = 'none';
        return;
    }

    tbody.innerHTML = `<tr><td colspan="10" style="text-align:center;padding:40px;color:#94a3b8;">
        <i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block;"></i>Memuat data...</td></tr>`;

    try {
        const res = await fetch(`${API_URL}/overtime-logs?client_id=${clientId}&bulan=${bulan}&tahun=${tahun}`);
        const data = await res.json();

        if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" style="text-align:center;padding:40px;color:#94a3b8;">
                <i class="fas fa-clock" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
                Belum ada data lembur untuk periode ini.</td></tr>`;
            document.getElementById('otSummaryContainer').style.display = 'none';
            return;
        }

        // Calculate and update summaries
        let pendingHrs = 0, pendingLogs = 0;
        let approvedHrs = 0, approvedLogs = 0;
        let rejectedHrs = 0, rejectedLogs = 0;

        data.forEach(o => {
            const hrs = parseFloat(o.jam_lembur) || 0;
            const status = String(o.status || 'Pending').toLowerCase().trim();
            if (status === 'approved' || status === 'setuju') {
                approvedHrs += hrs;
                approvedLogs++;
            } else if (status === 'rejected' || status === 'tolak') {
                rejectedHrs += hrs;
                rejectedLogs++;
            } else {
                pendingHrs += hrs;
                pendingLogs++;
            }
        });

        document.getElementById('otSummaryPending').innerText = `${pendingHrs.toFixed(1)} Jam (${pendingLogs} data)`;
        document.getElementById('otSummaryApproved').innerText = `${approvedHrs.toFixed(1)} Jam (${approvedLogs} data)`;
        document.getElementById('otSummaryRejected').innerText = `${rejectedHrs.toFixed(1)} Jam (${rejectedLogs} data)`;
        document.getElementById('otSummaryContainer').style.display = 'grid';

        tbody.innerHTML = data.map((o, i) => {
            const d = new Date(o.tanggal);
            const tanggalFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            const isHoliday = parseInt(o.is_holiday);
            const tipeLabel = isHoliday ? 'Hari Libur' : 'Hari Kerja';
            const tipeStyle = isHoliday
                ? 'background:#fef3c7;color:#92400e;'
                : 'background:#dcfce7;color:#166534;';

            // Status label & badge
            const statusVal = String(o.status || 'Pending');
            let statusBadge = '';
            if (statusVal === 'Approved') {
                statusBadge = `<span style="background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-check-circle"></i> Approved</span>`;
            } else if (statusVal === 'Rejected') {
                statusBadge = `<span style="background:#fee2e2;color:#b91c1c;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-times-circle"></i> Rejected</span>`;
            } else {
                statusBadge = `<span style="background:#fffbeb;color:#d97706;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-clock"></i> Pending</span>`;
            }

            // Approver details
            let approverDetails = '-';
            if (o.approved_by) {
                const appDate = o.approved_at ? new Date(o.approved_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'}) : '';
                approverDetails = `<span style="font-size:12px;font-weight:600;color:#334155;">${o.approved_by}</span><br><span style="font-size:10px;color:#94a3b8;">${appDate}</span>`;
            }

            // Action buttons based on status
            let actionButtons = '';
            if (statusVal === 'Pending') {
                actionButtons = `
                    <button onclick="approveOvertimeLog(${o.id})" style="background:#dcfce7;color:#166534;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;margin-right:4px;" title="Setujui (Approve)">
                        <i class="fas fa-check"></i>
                    </button>
                    <button onclick="rejectOvertimeLog(${o.id})" style="background:#fee2e2;color:#991b1b;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;margin-right:4px;" title="Tolak (Reject)">
                        <i class="fas fa-times"></i>
                    </button>
                `;
            } else {
                actionButtons = `
                    <button onclick="resetOvertimeLog(${o.id})" style="background:#f1f5f9;color:#475569;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;margin-right:4px;" title="Kembalikan ke Pending">
                        <i class="fas fa-undo"></i>
                    </button>
                `;
            }
            actionButtons += `
                <button onclick="deleteOvertimeLog(${o.id})" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            `;

            return `<tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="text-align:center;padding:12px;">
                    <input type="checkbox" class="overtime-row-checkbox" value="${o.id}" onchange="onOvertimeCheckboxChange()" style="width:16px;height:16px;cursor:pointer;accent-color:var(--primary-color);">
                </td>
                <td style="text-align:center;padding:12px;color:#64748b;">${i+1}</td>
                <td style="padding:12px;font-weight:600;color:#1e293b;">${o.employee_name || '-'}</td>
                <td style="text-align:center;padding:12px;color:#475569;">${tanggalFormatted}</td>
                <td style="text-align:center;padding:12px;font-weight:700;color:#1e293b;">${parseFloat(o.jam_lembur)} jam</td>
                <td style="text-align:center;padding:12px;">
                    <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;${tipeStyle}">${tipeLabel}</span>
                </td>
                <td style="padding:12px;color:#475569;max-width:200px;overflow:hidden;text-overflow:ellipsis;" title="${o.keterangan || ''}">${o.keterangan || '-'}</td>
                <td style="text-align:center;padding:12px;">${statusBadge}</td>
                <td style="padding:12px;">${approverDetails}</td>
                <td style="text-align:center;padding:12px;">
                    <div style="display:inline-flex;align-items:center;">${actionButtons}</div>
                </td>
            </tr>`;
        }).join('');
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="10" style="text-align:center;padding:40px;color:#ef4444;">Gagal memuat data: ${e.message}</td></tr>`;
    }
}

function toggleSelectAllOvertime(source) {
    const checkboxes = document.querySelectorAll('.overtime-row-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
    onOvertimeCheckboxChange();
}

function onOvertimeCheckboxChange() {
    const checkboxes = document.querySelectorAll('.overtime-row-checkbox:checked');
    const panel = document.getElementById('overtimeBulkActions');
    const countSpan = document.getElementById('otSelectedCount');
    if (panel) {
        if (checkboxes.length > 0) {
            if (countSpan) countSpan.innerText = checkboxes.length;
            panel.style.display = 'flex';
        } else {
            panel.style.display = 'none';
        }
    }
}

async function bulkApproveOvertime() {
    const checkboxes = document.querySelectorAll('.overtime-row-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));
    if (ids.length === 0) return;

    if (!await showConfirm(`Yakin ingin menyetujui (Approve) ${ids.length} data lembur terpilih?`)) return;

    try {
        const res = await fetch(`${API_URL}/overtime-logs/bulk-approve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids })
        });
        const data = await res.json();
        if (res.ok) {
            showToast(data.message || 'Lembur berhasil disetujui!', 'success');
            loadOvertimeLogs();
        } else {
            showToast(data.message || 'Gagal menyetujui lembur', 'error');
        }
    } catch(e) {
        showToast('Error: ' + e.message, 'error');
    }
}

async function bulkRejectOvertime() {
    const checkboxes = document.querySelectorAll('.overtime-row-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));
    if (ids.length === 0) return;

    if (!await showConfirm(`Yakin ingin menolak (Reject) ${ids.length} data lembur terpilih?`)) return;

    try {
        const res = await fetch(`${API_URL}/overtime-logs/bulk-reject`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids })
        });
        const data = await res.json();
        if (res.ok) {
            showToast(data.message || 'Lembur berhasil ditolak!', 'success');
            loadOvertimeLogs();
        } else {
            showToast(data.message || 'Gagal menolak lembur', 'error');
        }
    } catch(e) {
        showToast('Error: ' + e.message, 'error');
    }
}

async function approveOvertimeLog(id) {
    try {
        const res = await fetch(`${API_URL}/overtime-logs/approve/${id}`, { method: 'POST' });
        const data = await res.json();
        if (res.ok) {
            showToast('Lembur disetujui', 'success');
            loadOvertimeLogs();
        } else {
            showToast(data.message || 'Gagal menyetujui lembur', 'error');
        }
    } catch(e) { console.error(e); }
}

async function rejectOvertimeLog(id) {
    try {
        const res = await fetch(`${API_URL}/overtime-logs/reject/${id}`, { method: 'POST' });
        const data = await res.json();
        if (res.ok) {
            showToast('Lembur ditolak', 'success');
            loadOvertimeLogs();
        } else {
            showToast(data.message || 'Gagal menolak lembur', 'error');
        }
    } catch(e) { console.error(e); }
}

async function resetOvertimeLog(id) {
    if (!await showConfirm('Yakin ingin mengembalikan status lembur ini ke Pending?')) return;
    try {
        const res = await fetch(`${API_URL}/overtime-logs/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        if (res.ok) {
            showToast('Status lembur dikembalikan ke Pending', 'success');
            loadOvertimeLogs();
        } else {
            showToast(data.message || 'Gagal mereset status', 'error');
        }
    } catch(e) { console.error(e); }
}

async function loadOvertimeEmployees() {
    const select = document.getElementById('overtimeEmployeeSelect');
    if (!select) return;
    const clientId = document.getElementById('overtimeClientSelect')?.value;
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

function bukaModalOvertime() {
    const clientId = document.getElementById('overtimeClientSelect')?.value;
    if (!clientId) { showToast('Pilih client terlebih dahulu!', 'error'); return; }
    document.getElementById('overtimeForm')?.reset();
    document.getElementById('overtimeModalTitle').innerText = 'Input Lembur';
    loadOvertimeEmployees();
    openModal('overtimeModal');
}

async function simpanOvertime(e) {
    e.preventDefault();
    const employeeId = document.getElementById('overtimeEmployeeSelect')?.value;
    const tanggal = document.getElementById('overtimeTanggal')?.value;
    const jamLembur = document.getElementById('overtimeJamLembur')?.value;
    const isHoliday = document.getElementById('overtimeIsHoliday')?.checked ? 1 : 0;
    const keterangan = document.getElementById('overtimeKeterangan')?.value;

    if (!employeeId || !tanggal || !jamLembur) {
        showToast('Karyawan, tanggal, dan jam lembur wajib diisi!', 'error');
        return;
    }

    // Client-side validation: max 3 jam untuk hari kerja
    if (!isHoliday && parseFloat(jamLembur) > 3) {
        showToast('Lembur hari kerja maksimal 3 jam per hari!', 'error');
        return;
    }

    try {
        const res = await fetch(`${API_URL}/overtime-logs`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ employee_id: employeeId, tanggal, jam_lembur: jamLembur, is_holiday: isHoliday, keterangan })
        });
        const data = await res.json();
        if (!res.ok) {
            showToast(data.messages?.error || 'Gagal menyimpan!', 'error');
            return;
        }
        showToast(data.message || 'Lembur berhasil disimpan!', 'success');
        closeModal('overtimeModal');

        // Auto-update filter dropdowns to the month and year of the saved log
        const dateParts = tanggal.split('-');
        if (dateParts.length === 3) {
            const yearVal = parseInt(dateParts[0]);
            const monthVal = parseInt(dateParts[1]);
            const monthSelect = document.getElementById('overtimeMonthSelect');
            const yearSelect = document.getElementById('overtimeYearSelect');
            if (monthSelect) monthSelect.value = monthVal;
            if (yearSelect) yearSelect.value = yearVal;
        }

        loadOvertimeLogs();
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
    }
}

async function deleteOvertimeLog(id) {
    if (!await showConfirm('Yakin ingin menghapus log lembur ini?')) return;
    try {
        await fetch(`${API_URL}/overtime-logs/${id}`, { method: 'DELETE' });
        showToast('Log lembur berhasil dihapus!', 'success');
        loadOvertimeLogs();
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
    }
}

Object.assign(window, {
    loadOvertimeClients,
    loadOvertimeLogs,
    bukaModalOvertime,
    simpanOvertime,
    deleteOvertimeLog,
    approveOvertimeLog,
    rejectOvertimeLog,
    resetOvertimeLog,
    toggleSelectAllOvertime,
    onOvertimeCheckboxChange,
    bulkApproveOvertime,
    bulkRejectOvertime
});
