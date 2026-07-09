// === Overtime Module ===

async function loadOvertimeClients() {
    const select = document.getElementById('overtimeClientSelect');
    if (!select) return;
    try {
        const res = await fetch(`${API_URL}/clients`);
        const clients = await res.json();
        select.innerHTML = '<option value="">-- Select Client --</option>';
        clients.forEach(c => {
            select.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
        });
        loadOvertimeLogs();
    } catch(e) { console.error(e); }
}

async function loadOvertimeLogs() {
    const pendingTbody = document.getElementById('overtimePendingTableBody');
    const historyTbody = document.getElementById('overtimeHistoryTableBody');
    const mainTbody = document.getElementById('overtimeTableBody');
    if (!pendingTbody && !historyTbody && !mainTbody) return;

    const clientId = document.getElementById('overtimeClientSelect')?.value;
    const bulan = document.getElementById('overtimeMonthSelect')?.value;
    const tahun = document.getElementById('overtimeYearSelect')?.value;

    const selectAllCheckbox = document.getElementById('overtimeSelectAll');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    const bulkActions = document.getElementById('overtimeBulkActions');
    if (bulkActions) bulkActions.style.display = 'none';

    if (!clientId) {
        const noClientHtml = `<tr><td colspan="11" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
            Please select a client first to display overtime data.</td></tr>`;
        if (pendingTbody) pendingTbody.innerHTML = noClientHtml;
        if (historyTbody) historyTbody.innerHTML = noClientHtml;
        if (mainTbody) {
            const cols = mainTbody.closest('table')?.querySelectorAll('thead th')?.length || 9;
            mainTbody.innerHTML = `<tr><td colspan="${cols}" style="text-align:center;padding:40px;color:#94a3b8;">
                <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
                Please select a client first to display overtime data.</td></tr>`;
        }
        const otSummary = document.getElementById('otSummaryContainer');
        if (otSummary) otSummary.style.display = 'none';
        return;
    }

    const loadingHtml = `<tr><td colspan="11" style="text-align:center;padding:40px;color:#94a3b8;">
        <i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block;"></i>Loading data...</td></tr>`;
    if (pendingTbody) pendingTbody.innerHTML = loadingHtml;
    if (historyTbody) historyTbody.innerHTML = loadingHtml;
    if (mainTbody) {
        const cols = mainTbody.closest('table')?.querySelectorAll('thead th')?.length || 9;
        mainTbody.innerHTML = `<tr><td colspan="${cols}" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block;"></i>Loading data...</td></tr>`;
    }

    try {
        const res = await fetch(`${API_URL}/overtime-logs?client_id=${clientId}&bulan=${bulan}&tahun=${tahun}`);
        const data = await res.json();
        window.currentOvertimeLogs = data || [];

        // Reset search inputs on reload
        const pendingSearchInput = document.getElementById('otPendingSearchInput');
        if (pendingSearchInput) pendingSearchInput.value = '';
        const searchInput = document.getElementById('otHistorySearchInput');
        if (searchInput) searchInput.value = '';
        const statusFilter = document.getElementById('otHistoryStatusFilter');
        if (statusFilter) statusFilter.value = '';

        if (!data || data.length === 0) {
            const noDataHtml = `<tr><td colspan="11" style="text-align:center;padding:40px;color:#94a3b8;">
                <i class="fas fa-clock" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
                Belum ada data lembur untuk periode ini.</td></tr>`;
            if (pendingTbody) pendingTbody.innerHTML = noDataHtml;
            if (historyTbody) historyTbody.innerHTML = noDataHtml;
            if (mainTbody) {
                const cols = mainTbody.closest('table')?.querySelectorAll('thead th')?.length || 9;
                mainTbody.innerHTML = `<tr><td colspan="${cols}" style="text-align:center;padding:40px;color:#94a3b8;">
                    <i class="fas fa-clock" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
                    Belum ada data lembur untuk periode ini.</td></tr>`;
            }
            const otSummary = document.getElementById('otSummaryContainer');
            if (otSummary) otSummary.style.display = 'none';
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

        const otPending = document.getElementById('otSummaryPending');
        if (otPending) otPending.innerText = `${pendingHrs.toFixed(1)} Jam (${pendingLogs} data)`;
        const otApproved = document.getElementById('otSummaryApproved');
        if (otApproved) otApproved.innerText = `${approvedHrs.toFixed(1)} Jam (${approvedLogs} data)`;
        const otRejected = document.getElementById('otSummaryRejected');
        if (otRejected) otRejected.innerText = `${rejectedHrs.toFixed(1)} Jam (${rejectedLogs} data)`;
        const otSummary = document.getElementById('otSummaryContainer');
        if (otSummary) otSummary.style.display = 'grid';

        let pendingIndex = 1;
        let historyIndex = 1;
        let pendingHtml = '';
        let historyHtml = '';
        let mainHtml = '';

        const hasVerifierCol = mainTbody ? mainTbody.closest('table').querySelectorAll('thead th').length >= 10 : false;

        data.forEach((o, i) => {
            const d = new Date(o.tanggal);
            const tanggalFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            const isHoliday = parseInt(o.is_holiday);
            const tipeLabel = isHoliday ? 'Hari Libur' : 'Hari Kerja';
            const tipeStyle = isHoliday
                ? 'background:#fef3c7;color:#92400e;'
                : 'background:#dcfce7;color:#166534;';

            const statusVal = String(o.status || 'Pending');
            let statusBadge = '';
            let actionButtons = '';

            if (statusVal === 'Pending') {
                statusBadge = `<span style="background:#fffbeb;color:#d97706;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-clock"></i> Pending</span>`;
                actionButtons = `
                    <button onclick="approveOvertimeLog(${o.id})" style="background:#dcfce7;color:#166534;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;margin-right:4px;" title="Approve">
                        <i class="fas fa-check"></i>
                    </button>
                    <button onclick="rejectOvertimeLog(${o.id})" style="background:#fee2e2;color:#991b1b;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;margin-right:4px;" title="Reject">
                        <i class="fas fa-times"></i>
                    </button>
                    <button onclick="deleteOvertimeLog(${o.id})" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                `;

                if (pendingTbody) {
                    pendingHtml += `<tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="text-align:center;padding:12px;">
                            <input type="checkbox" class="overtime-row-checkbox" value="${o.id}" onchange="onOvertimeCheckboxChange()" style="width:16px;height:16px;cursor:pointer;accent-color:var(--primary-color);">
                        </td>
                        <td style="text-align:center;padding:12px;color:#64748b;">${pendingIndex++}</td>
                        <td style="padding:12px;font-weight:600;color:#1e293b;">${o.employee_name || '-'}</td>
                        <td style="text-align:center;padding:12px;color:#475569;font-size:12px;">${o.employee_nik || '-'}</td>
                        <td style="text-align:center;padding:12px;color:#475569;">${tanggalFormatted}</td>
                        <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${formatTimeHM(o.jam_masuk)}</td>
                        <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${formatTimeHM(o.jam_keluar)}</td>
                        <td style="text-align:center;padding:12px;font-weight:700;color:#1e293b;">${parseFloat(o.jam_lembur)} jam</td>
                        <td style="text-align:center;padding:12px;">
                            <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;${tipeStyle}">${tipeLabel}</span>
                        </td>
                        <td style="text-align:center;padding:12px;">${statusBadge}</td>
                        <td style="text-align:center;padding:12px;">
                            <div style="display:inline-flex;align-items:center;">${actionButtons}</div>
                        </td>
                    </tr>`;
                }
            } else {
                if (statusVal === 'Approved') {
                    statusBadge = `<span style="background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-check-circle"></i> Approved</span>`;
                } else if (statusVal === 'Rejected') {
                    statusBadge = `<span style="background:#fee2e2;color:#b91c1c;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-times-circle"></i> Rejected</span>`;
                }

                let approverDetails = '-';
                if (o.approved_by) {
                    const appDate = o.approved_at ? new Date(o.approved_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'}) : '';
                    approverDetails = `<span style="font-size:12px;font-weight:600;color:#334155;">${o.approved_by}</span><br><span style="font-size:10px;color:#94a3b8;">${appDate}</span>`;
                }

                actionButtons = `
                    <button onclick="resetOvertimeLog(${o.id})" style="background:#f1f5f9;color:#475569;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;margin-right:4px;" title="Kembalikan ke Pending">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button onclick="deleteOvertimeLog(${o.id})" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                `;

                if (historyTbody) {
                    historyHtml += `<tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="text-align:center;padding:12px;color:#64748b;">${historyIndex++}</td>
                        <td style="padding:12px;font-weight:600;color:#1e293b;">${o.employee_name || '-'}</td>
                        <td style="text-align:center;padding:12px;color:#475569;font-size:12px;">${o.employee_nik || '-'}</td>
                        <td style="text-align:center;padding:12px;color:#475569;">${tanggalFormatted}</td>
                        <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${formatTimeHM(o.jam_masuk)}</td>
                        <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${formatTimeHM(o.jam_keluar)}</td>
                        <td style="text-align:center;padding:12px;font-weight:700;color:#1e293b;">${parseFloat(o.jam_lembur)} jam</td>
                        <td style="text-align:center;padding:12px;">
                            <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;${tipeStyle}">${tipeLabel}</span>
                        </td>
                        <td style="text-align:center;padding:12px;">${statusBadge}</td>
                        <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${o.payout_period || '-'}</td>
                        <td style="padding:12px;">${approverDetails}</td>
                        <td style="text-align:center;padding:12px;">
                            <div style="display:inline-flex;align-items:center;">${actionButtons}</div>
                        </td>
                    </tr>`;
                }
            }

            if (mainTbody) {
                let approverDetails = '-';
                if (o.approved_by) {
                    const appDate = o.approved_at ? new Date(o.approved_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'}) : '';
                    approverDetails = `<span style="font-size:12px;font-weight:600;color:#334155;">${o.approved_by}</span><br><span style="font-size:10px;color:#94a3b8;">${appDate}</span>`;
                }

                mainHtml += `<tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="text-align:center;padding:12px;">
                        <input type="checkbox" class="overtime-row-checkbox" value="${o.id}" onchange="onOvertimeCheckboxChange()" style="width:16px;height:16px;cursor:pointer;accent-color:var(--primary-color);">
                    </td>
                    <td style="text-align:center;padding:12px;color:#64748b;">${i + 1}</td>
                    <td style="padding:12px;font-weight:600;color:#1e293b;">${o.employee_name || '-'}</td>
                    <td style="text-align:center;padding:12px;color:#475569;">${tanggalFormatted}</td>
                    <td style="text-align:center;padding:12px;font-weight:700;color:#1e293b;">${parseFloat(o.jam_lembur)} jam</td>
                    <td style="text-align:center;padding:12px;">
                        <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;${tipeStyle}">${tipeLabel}</span>
                    </td>
                    <td style="padding:12px;color:#475569;max-width:200px;overflow:hidden;text-overflow:ellipsis;" title="${o.keterangan || ''}">${o.keterangan || '-'}</td>
                    <td style="text-align:center;padding:12px;">${statusBadge}</td>
                    ${hasVerifierCol ? `<td style="padding:12px;">${approverDetails}</td>` : ''}
                    <td style="text-align:center;padding:12px;">
                        <div style="display:inline-flex;align-items:center;">${actionButtons}</div>
                    </td>
                </tr>`;
            }
        });

        if (pendingTbody) {
            pendingTbody.innerHTML = pendingHtml || `<tr><td colspan="11" style="text-align:center;padding:30px;color:#94a3b8;">Tidak ada data lembur pending.</td></tr>`;
        }
        if (historyTbody) {
            historyTbody.innerHTML = historyHtml || `<tr><td colspan="12" style="text-align:center;padding:30px;color:#94a3b8;">Tidak ada riwayat lembur.</td></tr>`;
        }
        if (mainTbody) {
            mainTbody.innerHTML = mainHtml || `<tr><td colspan="${hasVerifierCol ? 10 : 9}" style="text-align:center;padding:30px;color:#94a3b8;">Belum ada data lembur untuk periode ini.</td></tr>`;
        }
    } catch (e) {
        if (pendingTbody) pendingTbody.innerHTML = `<tr><td colspan="11" style="text-align:center;padding:40px;color:#ef4444;">Failed to load data: ${e.message}</td></tr>`;
        if (historyTbody) historyTbody.innerHTML = `<tr><td colspan="12" style="text-align:center;padding:40px;color:#ef4444;">Failed to load data: ${e.message}</td></tr>`;
        if (mainTbody) {
            const cols = mainTbody.closest('table')?.querySelectorAll('thead th')?.length || 9;
            mainTbody.innerHTML = `<tr><td colspan="${cols}" style="text-align:center;padding:40px;color:#ef4444;">Failed to load data: ${e.message}</td></tr>`;
        }
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

    if (!await showConfirm(`Are you sure you want to approve ${ids.length} selected overtime records?`, 'Confirm Approval', 'Yes, Approve', 'Cancel', 'success')) return;

    try {
        const res = await fetch(`${API_URL}/overtime-logs/bulk-approve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids })
        });
        const data = await res.json();
        if (res.ok) {
            showToast(data.message || 'Overtime approved successfully!', 'success');
            loadOvertimeLogs();
        } else {
            showToast(data.message || 'Failed to approve overtime', 'error');
        }
    } catch(e) {
        showToast('Error: ' + e.message, 'error');
    }
}

async function bulkRejectOvertime() {
    const checkboxes = document.querySelectorAll('.overtime-row-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));
    if (ids.length === 0) return;

    if (!await showConfirm(`Are you sure you want to reject ${ids.length} selected overtime records?`, 'Confirm Rejection', 'Yes, Reject', 'Cancel', 'danger')) return;

    try {
        const res = await fetch(`${API_URL}/overtime-logs/bulk-reject`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids })
        });
        const data = await res.json();
        if (res.ok) {
            showToast(data.message || 'Overtime rejected successfully!', 'success');
            loadOvertimeLogs();
        } else {
            showToast(data.message || 'Failed to reject overtime', 'error');
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
            showToast('Overtime approved', 'success');
            loadOvertimeLogs();
        } else {
            showToast(data.message || 'Failed to approve overtime', 'error');
        }
    } catch(e) { console.error(e); }
}

async function rejectOvertimeLog(id) {
    try {
        const res = await fetch(`${API_URL}/overtime-logs/reject/${id}`, { method: 'POST' });
        const data = await res.json();
        if (res.ok) {
            showToast('Overtime rejected', 'success');
            loadOvertimeLogs();
        } else {
            showToast(data.message || 'Failed to reject overtime', 'error');
        }
    } catch(e) { console.error(e); }
}

async function resetOvertimeLog(id) {
    if (!await showConfirm('Are you sure you want to reset this overtime status to Pending?', 'Reset Status', 'Yes, Reset', 'Cancel', 'primary')) return;
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
            showToast(data.message || 'Failed to reset status', 'error');
        }
    } catch(e) { console.error(e); }
}

async function loadOvertimeEmployees() {
    const select = document.getElementById('overtimeEmployeeSelect');
    if (!select) return;
    const clientId = document.getElementById('overtimeClientSelect')?.value;
    if (!clientId) { select.innerHTML = '<option value="">-- Select Employee --</option>'; return; }

    try {
        const res = await fetch(`${API_URL}/employees?client_id=${clientId}`);
        const emps = await res.json();
        select.innerHTML = '<option value="">-- Select Employee --</option>';
        (emps.data || emps).forEach(e => {
            select.innerHTML += `<option value="${e.id}">${e.nama}</option>`;
        });
    } catch(e) { console.error(e); }
}

function bukaModalOvertime() {
    const clientId = document.getElementById('overtimeClientSelect')?.value;
    if (!clientId) { showToast('Please select a client first!', 'error'); return; }
    document.getElementById('overtimeForm')?.reset();
    document.getElementById('overtimeModalTitle').innerText = 'Input Lembur';
    
    const tanggalInput = document.getElementById('overtimeTanggal');
    if (tanggalInput && !tanggalInput.value) {
        const today = new Date().toISOString().split('T')[0];
        tanggalInput.value = today;
    }
    
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
            showToast(data.messages?.error || 'Failed to save!', 'error');
            return;
        }
        showToast(data.message || 'Overtime saved successfully!', 'success');
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
    if (!await showConfirm('Are you sure you want to delete this overtime log?', 'Delete Overtime Log', 'Yes, Delete', 'Cancel', 'danger')) return;
    try {
        await fetch(`${API_URL}/overtime-logs/${id}`, { method: 'DELETE' });
        showToast('Overtime log deleted successfully!', 'success');
        loadOvertimeLogs();
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
    }
}

// === Overtime Upload Logic ===
let parsedLemburData = [];

function parseOvertimeExcelDate(val) {
    if (!val) return null;
    if (val instanceof Date) return val;
    
    // Check if it's a number (Excel serial date)
    if (!isNaN(val)) {
        const date = new Date((val - 25569) * 24 * 60 * 60 * 1000);
        return date;
    }

    const s = String(val).trim();
    if (/^\d{4}-\d{1,2}-\d{1,2}$/.test(s)) {
        const parts = s.split('-');
        return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
    }
    if (/^\d{1,2}[-/]\d{1,2}[-/]\d{4}$/.test(s)) {
        const separator = s.includes('/') ? '/' : '-';
        const parts = s.split(separator);
        return new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
    }
    const d = new Date(s);
    if (!isNaN(d.getTime())) return d;
    return null;
}

async function bukaModalUploadLembur() {
    document.getElementById('uploadLemburLogs').innerHTML = "Select Client and Period to start.";
    document.getElementById('labelLemburFilename').innerText = "No file selected";
    document.getElementById('fileLemburExcel').value = "";

    const text1 = document.getElementById('dropzoneLemburText1');
    const text2 = document.getElementById('dropzoneLemburText2');
    if (text1) text1.innerText = 'Drag & Drop file here';
    if (text2) text2.innerText = 'or click to browse files from your computer';

    document.getElementById('btnSaveUploadedLembur').disabled = true;
    document.getElementById('btnSaveUploadedLembur').style.opacity = "0.5";
    document.getElementById('btnSaveUploadedLembur').style.cursor = "not-allowed";

    parsedLemburData = [];

    // Populate Clients
    const select = document.getElementById('modalUploadLemburClient');
    const periodSelect = document.getElementById('modalUploadLemburPeriod');
    select.innerHTML = '<option value="">-- Select Client --</option>';
    periodSelect.innerHTML = '<option value="">-- Select Client First --</option>';
    periodSelect.disabled = true;

    try {
        const res = await fetch(`${API_URL}/clients`);
        const clients = await res.json();
        clients.forEach(c => {
            select.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
        });

        // Auto-select current selected client in the main page
        const mainClientId = document.getElementById('overtimeClientSelect')?.value;
        if (mainClientId) {
            select.value = mainClientId;
            await onLemburClientChanged();
        }
    } catch(e) {
        console.error(e);
        showToast('Failed to load client list', 'error');
    }

    document.getElementById('modalUploadLembur').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function tutupModalUploadLembur() {
    document.getElementById('modalUploadLembur').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

async function onLemburClientChanged() {
    const clientId = document.getElementById('modalUploadLemburClient').value;
    const periodSelect = document.getElementById('modalUploadLemburPeriod');
    if (!clientId) {
        periodSelect.innerHTML = '<option value="">-- Select Client First --</option>';
        periodSelect.disabled = true;
        return;
    }

    periodSelect.innerHTML = '<option value="">Loading periods...</option>';
    periodSelect.disabled = true;

    try {
        const res = await fetch(`${API_URL}/periods?client_id=${clientId}`);
        let periods = res.ok ? await res.json() : [];

        // Check if the main page's selected month & year exists in the periods
        const mainMonth = document.getElementById('overtimeMonthSelect')?.value;
        const mainYear = document.getElementById('overtimeYearSelect')?.value;
        
        if (mainMonth && mainYear) {
            const hasMatchedPeriod = periods.some(p => parseInt(p.bulan) == parseInt(mainMonth) && parseInt(p.tahun) == parseInt(mainYear));
            if (!hasMatchedPeriod) {
                // Period does not exist yet. Let's create it automatically!
                const createRes = await fetch(`${API_URL}/periods`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        client_id: parseInt(clientId),
                        bulan: parseInt(mainMonth),
                        tahun: parseInt(mainYear)
                    })
                });
                if (createRes.ok) {
                    // Refetch the periods list
                    const refetchRes = await fetch(`${API_URL}/periods?client_id=${clientId}`);
                    periods = refetchRes.ok ? await refetchRes.json() : [];
                }
            }
        }

        window.lemburUploadPeriods = periods;

        if (periods.length === 0) {
            periodSelect.innerHTML = '<option value="">No periods available</option>';
            return;
        }

        periodSelect.innerHTML = '<option value="" disabled selected hidden>-- Select Period --</option>' + periods.map(p => `
            <option value="${p.id}">${p.nama} (${p.status})</option>
        `).join('');
        periodSelect.disabled = false;

        // Auto-select based on main page selection
        if (mainMonth && mainYear) {
            const matchedPeriod = periods.find(p => parseInt(p.bulan) == parseInt(mainMonth) && parseInt(p.tahun) == parseInt(mainYear));
            if (matchedPeriod) {
                periodSelect.value = matchedPeriod.id;
                onLemburPeriodChanged();
            }
        }
    } catch (e) {
        console.error(e);
        periodSelect.innerHTML = '<option value="">Error loading periods</option>';
    }
}

async function onLemburPeriodChanged() {
    const logsDiv = document.getElementById('uploadLemburLogs');
    logsDiv.innerHTML = "Ready to upload Overtime Excel file.";
}

async function downloadLemburTemplate() {
    showToast('Downloading template...', 'info');
    try {
        const worksheet = XLSX.utils.aoa_to_sheet([
            ['NIK', 'Nama', 'Tanggal', 'Nominal', 'Keterangan']
        ]);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Overtime Template");

        const max_widths = [15, 25, 15, 15, 20];
        worksheet['!cols'] = max_widths.map(w => ({ wch: w }));

        const filename = 'Overtime_Template.xlsx';
        XLSX.writeFile(workbook, filename);
        showToast('Template downloaded successfully!', 'success');
    } catch (e) {
        console.error(e);
        showToast('Failed to download template: ' + e.message, 'error');
    }
}

function handleLemburFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    processLemburFile(file);
}

function processLemburFile(file) {
    if (!file) return;

    const clientId = document.getElementById('modalUploadLemburClient').value;
    const periodId = document.getElementById('modalUploadLemburPeriod').value;
    if (!clientId || !periodId) {
        showToast('Please select Client and Period first before choosing file.', 'warning');
        return;
    }

    const text1 = document.getElementById('dropzoneLemburText1');
    const text2 = document.getElementById('dropzoneLemburText2');
    if (text1) text1.innerText = file.name;
    if (text2) text2.innerText = 'File selected. Click or drag another file to replace.';

    document.getElementById('labelLemburFilename').innerText = file.name;
    const logsDiv = document.getElementById('uploadLemburLogs');
    logsDiv.innerHTML = "Reading file...\n";

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array', cellDates: false });
            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];
            const json = XLSX.utils.sheet_to_json(worksheet, { raw: false });

            if (json.length === 0) {
                logsDiv.innerHTML += "Error: Excel file is empty.\n";
                return;
            }

            logsDiv.innerHTML += `Parsed ${json.length} rows from sheet "${sheetName}".\n`;
            processParsedLembur(json);
        } catch (err) {
            console.error(err);
            logsDiv.innerHTML += `Error parsing file: ${err.message || err}\n`;
        }
    };
    reader.readAsArrayBuffer(file);
}

function handleLemburDragOver(event) {
    event.preventDefault();
    const zone = document.getElementById('dropzoneLemburExcel');
    if (zone) {
        zone.style.borderColor = 'var(--primary-dark)';
        zone.style.backgroundColor = 'rgba(243, 156, 18, 0.18)';
    }
}

function handleLemburDragLeave(event) {
    event.preventDefault();
    const zone = document.getElementById('dropzoneLemburExcel');
    if (zone) {
        zone.style.borderColor = 'var(--primary-color)';
        zone.style.backgroundColor = 'rgba(243, 156, 18, 0.08)';
    }
}

function handleLemburDrop(event) {
    event.preventDefault();
    const zone = document.getElementById('dropzoneLemburExcel');
    if (zone) {
        zone.style.borderColor = 'var(--primary-color)';
        zone.style.backgroundColor = 'rgba(243, 156, 18, 0.08)';
    }
    
    if (event.dataTransfer.files && event.dataTransfer.files.length > 0) {
        const file = event.dataTransfer.files[0];
        const fileInput = document.getElementById('fileLemburExcel');
        if (fileInput) {
            fileInput.files = event.dataTransfer.files;
        }
        processLemburFile(file);
    }
}

async function downloadLemburTemplateMain() {
    await downloadLemburTemplate();
}


function processParsedLembur(rows) {
    const logsDiv = document.getElementById('uploadLemburLogs');
    const finalData = [];
    let logText = "";
    let validCount = 0;

    rows.forEach((row, index) => {
        const keys = Object.keys(row);
        const nikKey = keys.find(k => k.toLowerCase() === 'nik' || k.toLowerCase().replace(/\s+/g, '') === 'employeeid');
        const nameKey = keys.find(k => k.toLowerCase() === 'nama' || k.toLowerCase() === 'name');
        const tglKey = keys.find(k => k.toLowerCase() === 'tanggal' || k.toLowerCase() === 'date');
        const nominalKey = keys.find(k => k.toLowerCase() === 'nominal' || k.toLowerCase() === 'amount');
        const ketKey = keys.find(k => k.toLowerCase() === 'keterangan' || k.toLowerCase() === 'description' || k.toLowerCase() === 'notes');

        const nikVal = String(row[nikKey] || '').trim();
        const nameVal = String(row[nameKey] || '').trim();
        const tglVal = row[tglKey];
        const nominalVal = parseFloat(String(row[nominalKey] || '').replace(/[^0-9.-]+/g, '')) || 0;
        const ketVal = ketKey ? String(row[ketKey] || '').trim() : '';

        if (!tglVal || nominalVal <= 0 || (!nikVal && !nameVal)) {
            logText += `⚠️ Row ${index + 1}: Skipped (Missing date/nominal/name).\n`;
            return;
        }

        const dateObj = parseOvertimeExcelDate(tglVal);
        if (!dateObj) {
            logText += `⚠️ Row ${index + 1}: Invalid date format "${tglVal}".\n`;
            return;
        }

        const yyyy = dateObj.getFullYear();
        const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
        const dd = String(dateObj.getDate()).padStart(2, '0');
        const formattedDate = `${yyyy}-${mm}-${dd}`;

        finalData.push({
            nik: nikVal,
            nama: nameVal,
            tanggal: formattedDate,
            nominal: nominalVal,
            keterangan: ketVal
        });

        validCount++;
    });

    parsedLemburData = finalData;
    logsDiv.innerHTML += logText;
    logsDiv.innerHTML += `\nSuccess: Loaded ${validCount} valid overtime records.\nClick 'Apply & Save Overtime' to submit.`;

    if (validCount > 0) {
        const btn = document.getElementById('btnSaveUploadedLembur');
        btn.disabled = false;
        btn.style.cursor = 'pointer';
        btn.style.opacity = '1';
    }
}

async function saveUploadedLembur() {
    if (parsedLemburData.length === 0) {
        showToast('No parsed data to apply.', 'warning');
        return;
    }

    const btn = document.getElementById('btnSaveUploadedLembur');
    btn.disabled = true;
    btn.style.opacity = '0.5';
    btn.style.cursor = 'not-allowed';

    const periodId = document.getElementById('modalUploadLemburPeriod').value;
    const activePeriod = (window.lemburUploadPeriods || []).find(p => p.id == periodId);
    const payoutPeriodStr = activePeriod ? `${activePeriod.bulan}-${activePeriod.tahun}` : '';

    showToast('Saving overtime logs...', 'info');

    try {
        const res = await fetch(`${API_URL}/overtime-logs/import`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                logs: parsedLemburData,
                payout_period: payoutPeriodStr
            })
        });

        const result = await res.json();
        if (res.ok && result.success) {
            showToast(`Successfully imported ${result.imported_count} records!`, 'success');
            tutupModalUploadLembur();

            // Always refresh the table after import
            loadOvertimeLogs();
        } else {
            showToast(result.message || 'Failed to import overtime logs.', 'error');
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
        }
    } catch (err) {
        console.error(err);
        showToast('Error saving: ' + err.message, 'error');
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    }
}

function switchOvertimeSubTab(tab) {
    const panels = document.querySelectorAll('.ot-subpanel');
    panels.forEach(p => p.style.display = 'none');

    const btns = document.querySelectorAll('.ot-tab-btn');
    btns.forEach(b => {
        b.classList.remove('active');
        b.style.color = '#64748b';
        b.style.borderBottomColor = 'transparent';
    });

    if (tab === 'pending') {
        const panel = document.getElementById('otSubPanelPending');
        if (panel) panel.style.display = 'block';
        const btn = document.getElementById('otTabPending');
        if (btn) {
            btn.classList.add('active');
            btn.style.color = 'var(--primary-color)';
            btn.style.borderBottomColor = 'var(--primary-color)';
        }
    } else if (tab === 'history') {
        const panel = document.getElementById('otSubPanelHistory');
        if (panel) panel.style.display = 'block';
        const btn = document.getElementById('otTabHistory');
        if (btn) {
            btn.classList.add('active');
            btn.style.color = 'var(--primary-color)';
            btn.style.borderBottomColor = 'var(--primary-color)';
        }
    }
}

function filterOvertimeHistory() {
    const searchVal = (document.getElementById('otHistorySearchInput')?.value || '').toLowerCase().trim();
    const statusVal = (document.getElementById('otHistoryStatusFilter')?.value || '').toLowerCase().trim();
    const historyTbody = document.getElementById('overtimeHistoryTableBody');
    if (!historyTbody || !window.currentOvertimeLogs) return;

    // Filter only historical (non-Pending) logs
    const filtered = window.currentOvertimeLogs.filter(o => {
        const status = String(o.status || 'Pending').toLowerCase().trim();
        if (status === 'pending') return false; // Exclude pending from history

        // Apply status filter (Approved/Rejected)
        if (statusVal) {
            const mappedStatus = (status === 'approved' || status === 'setuju') ? 'approved' : 'rejected';
            if (mappedStatus !== statusVal) return false;
        }

        // Apply search name filter
        if (searchVal) {
            const empName = String(o.employee_name || '').toLowerCase();
            if (!empName.includes(searchVal)) return false;
        }

        return true;
    });

    // Re-render history table rows
    let historyIndex = 1;
    let historyHtml = '';

    filtered.forEach(o => {
        const d = new Date(o.tanggal);
        const tanggalFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        const isHoliday = parseInt(o.is_holiday);
        const tipeLabel = isHoliday ? 'Hari Libur' : 'Hari Kerja';
        const tipeStyle = isHoliday ? 'background:#fef3c7;color:#92400e;' : 'background:#dcfce7;color:#166534;';

        const rawStatus = String(o.status || 'Pending');
        let statusBadge = '';
        if (rawStatus === 'Approved') {
            statusBadge = `<span style="background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-check-circle"></i> Approved</span>`;
        } else {
            statusBadge = `<span style="background:#fee2e2;color:#b91c1c;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-times-circle"></i> Rejected</span>`;
        }

        let approverDetails = '-';
        if (o.approved_by) {
            const appDate = o.approved_at ? new Date(o.approved_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'}) : '';
            approverDetails = `<span style="font-size:12px;font-weight:600;color:#334155;">${o.approved_by}</span><br><span style="font-size:10px;color:#94a3b8;">${appDate}</span>`;
        }

        const actionButtons = `
            <button onclick="resetOvertimeLog(${o.id})" style="background:#f1f5f9;color:#475569;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;margin-right:4px;" title="Kembalikan ke Pending">
                <i class="fas fa-undo"></i>
            </button>
            <button onclick="deleteOvertimeLog(${o.id})" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        `;

        historyHtml += `<tr style="border-bottom: 1px solid #f1f5f9;">
            <td style="text-align:center;padding:12px;color:#64748b;">${historyIndex++}</td>
            <td style="padding:12px;font-weight:600;color:#1e293b;">${o.employee_name || '-'}</td>
            <td style="text-align:center;padding:12px;color:#475569;font-size:12px;">${o.employee_nik || '-'}</td>
            <td style="text-align:center;padding:12px;color:#475569;">${tanggalFormatted}</td>
            <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${formatTimeHM(o.jam_masuk)}</td>
            <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${formatTimeHM(o.jam_keluar)}</td>
            <td style="text-align:center;padding:12px;font-weight:700;color:#1e293b;">${parseFloat(o.jam_lembur)} jam</td>
            <td style="text-align:center;padding:12px;">
                <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;${tipeStyle}">${tipeLabel}</span>
            </td>
            <td style="text-align:center;padding:12px;">${statusBadge}</td>
            <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${o.payout_period || '-'}</td>
            <td style="padding:12px;">${approverDetails}</td>
            <td style="text-align:center;padding:12px;">
                <div style="display:inline-flex;align-items:center;">${actionButtons}</div>
            </td>
        </tr>`;
    });

    historyTbody.innerHTML = historyHtml || `<tr><td colspan="12" style="text-align:center;padding:30px;color:#94a3b8;">Tidak ada riwayat lembur yang cocok dengan pencarian.</td></tr>`;
}

function filterOvertimePending() {
    const searchVal = (document.getElementById('otPendingSearchInput')?.value || '').toLowerCase().trim();
    const pendingTbody = document.getElementById('overtimePendingTableBody');
    if (!pendingTbody || !window.currentOvertimeLogs) return;

    // Filter only pending logs matching the search term
    const filtered = window.currentOvertimeLogs.filter(o => {
        const status = String(o.status || 'Pending').toLowerCase().trim();
        if (status !== 'pending') return false;

        if (searchVal) {
            const empName = String(o.employee_name || '').toLowerCase();
            if (!empName.includes(searchVal)) return false;
        }

        return true;
    });

    let pendingIndex = 1;
    let pendingHtml = '';

    filtered.forEach(o => {
        const d = new Date(o.tanggal);
        const tanggalFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        const isHoliday = parseInt(o.is_holiday);
        const tipeLabel = isHoliday ? 'Hari Libur' : 'Hari Kerja';
        const tipeStyle = isHoliday ? 'background:#fef3c7;color:#92400e;' : 'background:#dcfce7;color:#166534;';

        const statusBadge = `<span style="background:#fffbeb;color:#d97706;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-clock"></i> Pending</span>`;
        
        const actionButtons = `
            <button onclick="approveOvertimeLog(${o.id})" style="background:#dcfce7;color:#166534;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;margin-right:4px;" title="Approve">
                <i class="fas fa-check"></i>
            </button>
            <button onclick="rejectOvertimeLog(${o.id})" style="background:#fee2e2;color:#991b1b;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;margin-right:4px;" title="Reject">
                <i class="fas fa-times"></i>
            </button>
            <button onclick="deleteOvertimeLog(${o.id})" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        `;

        pendingHtml += `<tr style="border-bottom: 1px solid #f1f5f9;">
            <td style="text-align:center;padding:12px;">
                <input type="checkbox" class="overtime-row-checkbox" value="${o.id}" onchange="onOvertimeCheckboxChange()" style="width:16px;height:16px;cursor:pointer;accent-color:var(--primary-color);">
            </td>
            <td style="text-align:center;padding:12px;color:#64748b;">${pendingIndex++}</td>
            <td style="padding:12px;font-weight:600;color:#1e293b;">${o.employee_name || '-'}</td>
            <td style="text-align:center;padding:12px;color:#475569;font-size:12px;">${o.employee_nik || '-'}</td>
            <td style="text-align:center;padding:12px;color:#475569;">${tanggalFormatted}</td>
            <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${formatTimeHM(o.jam_masuk)}</td>
            <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${formatTimeHM(o.jam_keluar)}</td>
            <td style="text-align:center;padding:12px;font-weight:700;color:#1e293b;">${parseFloat(o.jam_lembur)} jam</td>
            <td style="text-align:center;padding:12px;">
                <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;${tipeStyle}">${tipeLabel}</span>
            </td>
            <td style="text-align:center;padding:12px;">${statusBadge}</td>
            <td style="text-align:center;padding:12px;">
                <div style="display:inline-flex;align-items:center;">${actionButtons}</div>
            </td>
        </tr>`;
    });

    pendingTbody.innerHTML = pendingHtml || `<tr><td colspan="11" style="text-align:center;padding:30px;color:#94a3b8;">Tidak ada data lembur pending yang cocok dengan pencarian.</td></tr>`;
}

function approveSelectedOvertime() {
    bulkApproveOvertime();
}

function rejectSelectedOvertime() {
    bulkRejectOvertime();
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
    bulkRejectOvertime,
    approveSelectedOvertime,
    rejectSelectedOvertime,
    bukaModalUploadLembur,
    tutupModalUploadLembur,
    onLemburClientChanged,
    onLemburPeriodChanged,
    downloadLemburTemplate,
    handleLemburFileSelect,
    saveUploadedLembur,
    switchOvertimeSubTab,
    filterOvertimeHistory,
    filterOvertimePending,
    handleLemburDragOver,
    handleLemburDragLeave,
    handleLemburDrop,
    downloadLemburTemplateMain
});
