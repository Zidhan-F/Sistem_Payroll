// === Early Arrival Module ===

async function loadEarlyArrivalClients() {
    const select = document.getElementById('eaClientFilter');
    if (!select) return;
    try {
        const res = await fetch(`${API_URL}/clients`);
        const clients = await res.json();
        select.innerHTML = '<option value="">-- Pilih Client --</option>';
        clients.forEach(c => {
            select.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
        });

        // Set current month and year as default filter values if not set
        const currentMonth = new Date().getMonth() + 1;
        const currentYear = new Date().getFullYear();

        const monthFilter = document.getElementById('eaMonthFilter');
        if (monthFilter && !monthFilter.value) {
            monthFilter.value = currentMonth;
        }

        const yearFilter = document.getElementById('eaYearFilter');
        if (yearFilter && !yearFilter.value) {
            yearFilter.value = currentYear;
        }

        onEaClientChanged();
    } catch (e) {
        console.error('Error loading early arrival clients:', e);
    }
}

async function onEaClientChanged() {
    const clientId = document.getElementById('eaClientFilter')?.value;
    const empSelect = document.getElementById('eaEmployeeFilter');

    if (!clientId) {
        if (empSelect) empSelect.innerHTML = '<option value="">-- All Employees --</option>';
        loadEarlyArrivalLogs();
        return;
    }

    // Load employees
    try {
        const resEmp = await fetch(`${API_URL}/employees?client_id=${clientId}`);
        const emps = await resEmp.json();
        if (empSelect) {
            empSelect.innerHTML = '<option value="">-- All Employees --</option>';
            const employeeList = emps.data || emps;
            if (Array.isArray(employeeList)) {
                employeeList.forEach(emp => {
                    empSelect.innerHTML += `<option value="${emp.id}">${emp.nama} (${emp.nik})</option>`;
                });
            }
        }
    } catch (e) {
        console.error('Error loading early arrival employees:', e);
    }

    loadEarlyArrivalLogs();
}

async function loadEarlyArrivalLogs() {
    const pendingTbody = document.getElementById('eaPendingTableBody');
    const historyTbody = document.getElementById('eaHistoryTableBody');
    if (!pendingTbody || !historyTbody) return;

    const clientId = document.getElementById('eaClientFilter')?.value;
    const bulan = document.getElementById('eaMonthFilter')?.value;
    const tahun = document.getElementById('eaYearFilter')?.value;
    const employeeId = document.getElementById('eaEmployeeFilter')?.value;

    const selectAllCheckbox = document.getElementById('chkEaSelectAll');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    
    const bulkActions = document.getElementById('earlyArrivalBulkActions');
    if (bulkActions) bulkActions.style.display = 'none';

    if (!clientId) {
        const noClientHtml = `<tr><td colspan="11" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
            Silakan pilih client terlebih dahulu untuk menampilkan data persetujuan.</td></tr>`;
        pendingTbody.innerHTML = noClientHtml;
        historyTbody.innerHTML = `<tr><td colspan="12" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
            Silakan pilih client terlebih dahulu untuk menampilkan data riwayat.</td></tr>`;
        
        const summaryContainer = document.getElementById('eaSummaryContainer');
        if (summaryContainer) summaryContainer.style.display = 'none';
        
        // Reset summary elements
        document.getElementById('eaSummaryPending').innerText = '0 Menit (0 requests)';
        document.getElementById('eaSummaryApproved').innerText = '0 Menit (0 logs)';
        document.getElementById('eaSummaryRejected').innerText = '0 Menit (0 logs)';
        document.getElementById('eaSummaryProcessed').innerText = '0 Menit (0 logs)';
        return;
    }

    const loadingHtml = `<tr><td colspan="11" style="text-align:center;padding:40px;color:#94a3b8;">
        <i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block;"></i>Memuat data...</td></tr>`;
    pendingTbody.innerHTML = loadingHtml;
    historyTbody.innerHTML = `<tr><td colspan="12" style="text-align:center;padding:40px;color:#94a3b8;">
        <i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block;"></i>Memuat data...</td></tr>`;

    try {
        let url = `${API_URL}/early-arrival?client_id=${clientId}&bulan=${bulan}&tahun=${tahun}`;
        if (employeeId) url += `&employee_id=${employeeId}`;

        const res = await fetch(url);
        const data = await res.json();
        window.currentEarlyArrivalLogs = data || [];

        // Reset search inputs on reload
        const pendingSearchInput = document.getElementById('eaPendingSearchInput');
        if (pendingSearchInput) pendingSearchInput.value = '';
        const historySearchInput = document.getElementById('eaHistorySearchInput');
        if (historySearchInput) historySearchInput.value = '';
        const historyStatusFilter = document.getElementById('eaHistoryStatusFilter');
        if (historyStatusFilter) historyStatusFilter.value = '';

        const summaryContainer = document.getElementById('eaSummaryContainer');
        if (summaryContainer) summaryContainer.style.display = 'grid';

        if (!data || data.length === 0) {
            const noDataHtml = `<tr><td colspan="11" style="text-align:center;padding:40px;color:#94a3b8;">
                <i class="fas fa-clock" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
                Tidak ada data Early Arrival untuk periode ini.</td></tr>`;
            pendingTbody.innerHTML = noDataHtml;
            historyTbody.innerHTML = `<tr><td colspan="12" style="text-align:center;padding:40px;color:#94a3b8;">
                <i class="fas fa-clock" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
                Tidak ada data Early Arrival untuk periode ini.</td></tr>`;
            
            document.getElementById('eaSummaryPending').innerText = '0 Menit (0 requests)';
            document.getElementById('eaSummaryApproved').innerText = '0 Menit (0 logs)';
            document.getElementById('eaSummaryRejected').innerText = '0 Menit (0 logs)';
            document.getElementById('eaSummaryProcessed').innerText = '0 Menit (0 logs)';
            return;
        }

        // Calculate summaries
        let pendingCount = 0, pendingMins = 0;
        let approvedCount = 0, approvedMins = 0;
        let rejectedCount = 0, rejectedMins = 0;
        let processedCount = 0, processedMins = 0;

        data.forEach(item => {
            const mins = parseInt(item.eligible_minutes) || 0;
            const status = String(item.status).toUpperCase();
            
            if (status === 'PENDING') {
                pendingCount++;
                pendingMins += mins;
            } else if (status === 'APPROVED') {
                approvedCount++;
                approvedMins += mins;
            } else if (status === 'REJECTED') {
                rejectedCount++;
                rejectedMins += mins;
            } else if (status === 'PROCESSED') {
                processedCount++;
                processedMins += mins;
            }
        });

        document.getElementById('eaSummaryPending').innerText = `${pendingMins} Menit (${pendingCount} requests)`;
        document.getElementById('eaSummaryApproved').innerText = `${approvedMins} Menit (${approvedCount} logs)`;
        document.getElementById('eaSummaryRejected').innerText = `${rejectedMins} Menit (${rejectedCount} logs)`;
        document.getElementById('eaSummaryProcessed').innerText = `${processedMins} Menit (${processedCount} logs)`;

        // Render both panels
        filterEaPending();
        filterEaHistory();

    } catch (e) {
        console.error('Error loading early arrival logs:', e);
        const errorHtml = `<tr><td colspan="11" style="text-align:center;padding:40px;color:#ef4444;">Gagal memuat data: ${e.message}</td></tr>`;
        pendingTbody.innerHTML = errorHtml;
        historyTbody.innerHTML = `<tr><td colspan="12" style="text-align:center;padding:40px;color:#ef4444;">Gagal memuat data: ${e.message}</td></tr>`;
    }
}

function filterEaPending() {
    const q = document.getElementById('eaPendingSearchInput')?.value.toLowerCase().trim() || '';
    const tbody = document.getElementById('eaPendingTableBody');
    if (!tbody || !window.currentEarlyArrivalLogs) return;

    const filtered = window.currentEarlyArrivalLogs.filter(item => {
        const status = String(item.status).toUpperCase();
        if (status !== 'PENDING') return false;

        const matchesQuery = !q ||
            (item.employee_name && item.employee_name.toLowerCase().includes(q)) ||
            (item.employee_nik && item.employee_nik.toLowerCase().includes(q));

        return matchesQuery;
    });

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="11" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-search" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
            Tidak ada data Early Arrival pending yang cocok.</td></tr>`;
        return;
    }

    let html = '';
    let index = 1;
    filtered.forEach(item => {
        const d = new Date(item.date);
        const dateFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

        const statusBadge = `<span style="background:#fffbeb;color:#d97706;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-clock"></i> Pending</span>`;

        const actionButtons = `
            <button onclick="approveEarlyArrivalLog(${item.id})" style="background:#dcfce7;color:#166534;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; margin-right: 4px;" title="Setujui">
                <i class="fas fa-check"></i>
            </button>
            <button onclick="rejectEarlyArrivalLog(${item.id})" style="background:#fee2e2;color:#991b1b;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px;" title="Tolak">
                <i class="fas fa-times"></i>
            </button>`;

        html += `<tr style="border-bottom:1px solid #f1f5f9; hover { background: #f8fafc; }">
            <td style="text-align:center;padding:16px;">
                <input type="checkbox" class="ea-row-checkbox" value="${item.id}" onchange="onEaCheckboxChange()" style="width:16px;height:16px;cursor:pointer;accent-color:var(--primary-color);">
            </td>
            <td style="text-align:center;padding:16px;color:#64748b;">${index++}</td>
            <td style="padding:16px;font-weight:600;color:#1e293b;">
                <i class="fas fa-user-clock" style="margin-right: 8px; opacity: 0.6; color: var(--primary-color);"></i>${item.employee_name || '-'}
            </td>
            <td style="text-align:center;padding:16px;color:#475569;font-size:12px;">${item.employee_nik || '-'}</td>
            <td style="text-align:center;padding:16px;color:#475569;">${dateFormatted}</td>
            <td style="text-align:center;padding:16px;color:#475569;font-weight:600;">${item.shift_start_time || '-'}</td>
            <td style="text-align:center;padding:16px;color:#475569;font-weight:600;">${item.check_in_time || '-'}</td>
            <td style="text-align:center;padding:16px;color:#475569;">${item.early_minutes} mnt</td>
            <td style="text-align:center;padding:16px;font-weight:700;color:#1e293b;">${item.eligible_minutes} mnt</td>
            <td style="text-align:center;padding:16px;">${statusBadge}</td>
            <td style="text-align:center;padding:16px;">${actionButtons}</td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function filterEaHistory() {
    const q = document.getElementById('eaHistorySearchInput')?.value.toLowerCase().trim() || '';
    const statusFilter = document.getElementById('eaHistoryStatusFilter')?.value || '';
    const tbody = document.getElementById('eaHistoryTableBody');
    if (!tbody || !window.currentEarlyArrivalLogs) return;

    const filtered = window.currentEarlyArrivalLogs.filter(item => {
        const status = String(item.status).toUpperCase();
        if (status === 'PENDING') return false; // Exclude pending from history

        const matchesQuery = !q ||
            (item.employee_name && item.employee_name.toLowerCase().includes(q)) ||
            (item.employee_nik && item.employee_nik.toLowerCase().includes(q));

        const matchesStatus = !statusFilter || status === statusFilter.toUpperCase();

        return matchesQuery && matchesStatus;
    });

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="12" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-search" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
            Tidak ada riwayat Early Arrival yang cocok dengan pencarian.</td></tr>`;
        return;
    }

    let html = '';
    let index = 1;
    filtered.forEach(item => {
        const d = new Date(item.date);
        const dateFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

        let statusBadge = '';
        const status = String(item.status).toUpperCase();
        if (status === 'APPROVED') {
            statusBadge = `<span style="background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-check-circle"></i> Approved</span>`;
        } else if (status === 'REJECTED') {
            statusBadge = `<span style="background:#fee2e2;color:#b91c1c;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-times-circle"></i> Rejected</span>`;
        } else if (status === 'PROCESSED') {
            statusBadge = `<span style="background:#eff6ff;color:#1d4ed8;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-file-invoice-dollar"></i> Processed</span>`;
        }

        const appDate = item.approved_at ? new Date(item.approved_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '';
        const verifier = item.approved_by ? `<span style="font-size:12px;font-weight:600;color:#334155;">${item.approved_by}</span><br><span style="font-size:10px;color:#94a3b8;">${appDate}</span>` : '-';

        let actionButtons = '';
        if (status !== 'PROCESSED') {
            actionButtons = `<button onclick="resetEarlyArrivalLog(${item.id})" style="background:#f1f5f9;color:#475569;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;display:inline-flex;align-items:center;gap:4px;transition: all 0.2s;" title="Kembalikan ke pending"><i class="fas fa-undo"></i> Reset</button>`;
        } else {
            actionButtons = `<span style="font-size:12px;color:#94a3b8;font-style:italic;"><i class="fas fa-lock"></i> Terkunci (Payroll)</span>`;
        }

        html += `<tr style="border-bottom:1px solid #f1f5f9; hover { background: #f8fafc; }">
            <td style="text-align:center;padding:16px;color:#64748b;">${index++}</td>
            <td style="padding:16px;font-weight:600;color:#1e293b;">
                <i class="fas fa-user-clock" style="margin-right: 8px; opacity: 0.6; color: var(--primary-color);"></i>${item.employee_name || '-'}
            </td>
            <td style="text-align:center;padding:16px;color:#475569;font-size:12px;">${item.employee_nik || '-'}</td>
            <td style="text-align:center;padding:16px;color:#475569;">${dateFormatted}</td>
            <td style="text-align:center;padding:16px;color:#475569;font-weight:600;">${item.shift_start_time || '-'}</td>
            <td style="text-align:center;padding:16px;color:#475569;font-weight:600;">${item.check_in_time || '-'}</td>
            <td style="text-align:center;padding:16px;color:#475569;">${item.early_minutes} mnt</td>
            <td style="text-align:center;padding:16px;font-weight:700;color:#1e293b;">${item.eligible_minutes} mnt</td>
            <td style="text-align:center;padding:16px;">${statusBadge}</td>
            <td style="text-align:center;padding:16px;color:#475569;font-weight:600;">${item.payroll_period || '-'}</td>
            <td style="padding:16px;">${verifier}</td>
            <td style="text-align:center;padding:16px;">${actionButtons}</td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function switchEaSubPanel(panel) {
    const panels = document.querySelectorAll('.ea-subpanel');
    panels.forEach(p => p.style.display = 'none');

    const btnPending = document.getElementById('btnEaSubPanelPending');
    const btnHistory = document.getElementById('btnEaSubPanelHistory');

    if (btnPending && btnHistory) {
        // Reset both buttons to normal state
        btnPending.style.color = '#64748b';
        btnPending.style.borderBottomColor = 'transparent';
        btnPending.style.fontWeight = '600';

        btnHistory.style.color = '#64748b';
        btnHistory.style.borderBottomColor = 'transparent';
        btnHistory.style.fontWeight = '600';
    }

    if (panel === 'pending') {
        const p = document.getElementById('eaSubPanelPending');
        if (p) p.style.display = 'block';
        if (btnPending) {
            btnPending.style.color = 'var(--primary-color)';
            btnPending.style.borderBottomColor = 'var(--primary-color)';
            btnPending.style.fontWeight = '700';
        }
    } else if (panel === 'history') {
        const p = document.getElementById('eaSubPanelHistory');
        if (p) p.style.display = 'block';
        if (btnHistory) {
            btnHistory.style.color = 'var(--primary-color)';
            btnHistory.style.borderBottomColor = 'var(--primary-color)';
            btnHistory.style.fontWeight = '700';
        }
    }
}

function toggleEaSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.ea-row-checkbox:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    onEaCheckboxChange();
}

function onEaCheckboxChange() {
    const checkboxes = document.querySelectorAll('.ea-row-checkbox:not(:disabled)');
    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    
    const selectAllCheckbox = document.getElementById('chkEaSelectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
    }

    const bulkActions = document.getElementById('earlyArrivalBulkActions');
    const selectedCountSpan = document.getElementById('eaSelectedCount');
    
    if (bulkActions) {
        if (checkedCount > 0) {
            bulkActions.style.display = 'flex';
            if (selectedCountSpan) selectedCountSpan.innerText = checkedCount;
        } else {
            bulkActions.style.display = 'none';
        }
    }
}

async function bulkApproveEarlyArrival() {
    const checkedBoxes = document.querySelectorAll('.ea-row-checkbox:checked:not(:disabled)');
    const ids = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
    
    if (ids.length === 0) {
        showToast('Pilih setidaknya satu log Early Arrival untuk disetujui.', 'error');
        return;
    }

    const confirmMsg = `Apakah Anda yakin ingin menyetujui ${ids.length} log Early Arrival terpilih?`;
    if (typeof showConfirm === 'function') {
        const approved = await showConfirm(confirmMsg, 'Persetujuan Massal', 'Setujui', 'Batal', 'success');
        if (!approved) return;
    } else {
        if (!confirm(confirmMsg)) return;
    }

    try {
        const res = await fetch(`${API_URL}/early-arrival/bulk-approve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids })
        });
        const data = await res.json();
        if (res.ok) {
            showToast(data.message || 'Log Early Arrival berhasil disetujui.', 'success');
            loadEarlyArrivalLogs();
        } else {
            showToast(data.message || 'Gagal menyetujui log Early Arrival.', 'error');
        }
    } catch (e) {
        console.error('Error in bulkApproveEarlyArrival:', e);
        showToast('Koneksi bermasalah, silakan coba lagi.', 'error');
    }
}

async function bulkRejectEarlyArrival() {
    const checkedBoxes = document.querySelectorAll('.ea-row-checkbox:checked:not(:disabled)');
    const ids = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
    
    if (ids.length === 0) {
        showToast('Pilih setidaknya satu log Early Arrival untuk ditolak.', 'error');
        return;
    }

    const confirmMsg = `Apakah Anda yakin ingin menolak ${ids.length} log Early Arrival terpilih?`;
    if (typeof showConfirm === 'function') {
        const rejected = await showConfirm(confirmMsg, 'Penolakan Massal', 'Tolak', 'Batal', 'danger');
        if (!rejected) return;
    } else {
        if (!confirm(confirmMsg)) return;
    }

    try {
        const res = await fetch(`${API_URL}/early-arrival/bulk-reject`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids })
        });
        const data = await res.json();
        if (res.ok) {
            showToast(data.message || 'Log Early Arrival berhasil ditolak.', 'success');
            loadEarlyArrivalLogs();
        } else {
            showToast(data.message || 'Gagal menolak log Early Arrival.', 'error');
        }
    } catch (e) {
        console.error('Error in bulkRejectEarlyArrival:', e);
        showToast('Koneksi bermasalah, silakan coba lagi.', 'error');
    }
}

async function approveEarlyArrivalLog(id) {
    const confirmMsg = 'Apakah Anda yakin ingin menyetujui kedatangan awal ini?';
    if (typeof showConfirm === 'function') {
        const approved = await showConfirm(confirmMsg, 'Setujui Pengajuan', 'Setujui', 'Batal', 'success');
        if (!approved) return;
    } else {
        if (!confirm(confirmMsg)) return;
    }

    try {
        const res = await fetch(`${API_URL}/early-arrival/approve/${id}`, { method: 'POST' });
        const data = await res.json();
        if (res.ok) {
            showToast(data.message || 'Kedatangan awal disetujui.', 'success');
            loadEarlyArrivalLogs();
        } else {
            showToast(data.message || 'Gagal menyetujui pengajuan.', 'error');
        }
    } catch (e) {
        console.error('Error in approveEarlyArrivalLog:', e);
    }
}

async function rejectEarlyArrivalLog(id) {
    const confirmMsg = 'Apakah Anda yakin ingin menolak kedatangan awal ini?';
    if (typeof showConfirm === 'function') {
        const rejected = await showConfirm(confirmMsg, 'Tolak Pengajuan', 'Tolak', 'Batal', 'danger');
        if (!rejected) return;
    } else {
        if (!confirm(confirmMsg)) return;
    }

    try {
        const res = await fetch(`${API_URL}/early-arrival/reject/${id}`, { method: 'POST' });
        const data = await res.json();
        if (res.ok) {
            showToast(data.message || 'Kedatangan awal ditolak.', 'success');
            loadEarlyArrivalLogs();
        } else {
            showToast(data.message || 'Gagal menolak pengajuan.', 'error');
        }
    } catch (e) {
        console.error('Error in rejectEarlyArrivalLog:', e);
    }
}

async function resetEarlyArrivalLog(id) {
    const confirmMsg = 'Apakah Anda yakin ingin mengembalikan log kedatangan awal ini ke status Pending?';
    if (typeof showConfirm === 'function') {
        const reset = await showConfirm(confirmMsg, 'Reset Status', 'Kembalikan ke Pending', 'Batal', 'primary');
        if (!reset) return;
    } else {
        if (!confirm(confirmMsg)) return;
    }

    try {
        const res = await fetch(`${API_URL}/early-arrival/reset/${id}`, { method: 'POST' });
        const data = await res.json();
        if (res.ok) {
            showToast(data.message || 'Status dikembalikan ke pending.', 'success');
            loadEarlyArrivalLogs();
        } else {
            showToast(data.message || 'Gagal mereset status.', 'error');
        }
    } catch (e) {
        console.error('Error in resetEarlyArrivalLog:', e);
    }
}

Object.assign(window, {
    loadEarlyArrivalClients,
    onEaClientChanged,
    loadEarlyArrivalLogs,
    toggleEaSelectAll,
    onEaCheckboxChange,
    bulkApproveEarlyArrival,
    bulkRejectEarlyArrival,
    approveEarlyArrivalLog,
    rejectEarlyArrivalLog,
    resetEarlyArrivalLog,
    switchEaSubPanel,
    filterEaPending,
    filterEaHistory
});
