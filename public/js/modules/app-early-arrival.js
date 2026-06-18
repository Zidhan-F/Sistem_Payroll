// === Early Arrival Module ===

async function loadEarlyArrivalClients() {
    const select = document.getElementById('eaClientFilter');
    if (!select) return;
    try {
        const res = await fetch(`${API_URL}/clients`);
        const clients = await res.json();
        select.innerHTML = '<option value="">-- Select Client --</option>';
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
    const deptSelect = document.getElementById('eaDeptFilter');
    const empSelect = document.getElementById('eaEmployeeFilter');

    if (!clientId) {
        if (deptSelect) deptSelect.innerHTML = '<option value="">-- All Departments --</option>';
        if (empSelect) empSelect.innerHTML = '<option value="">-- All Employees --</option>';
        loadEarlyArrivalLogs();
        return;
    }

    // Load departments
    try {
        const resDept = await fetch(`${API_URL}/org?client_id=${clientId}`);
        const orgData = await resDept.json();
        if (deptSelect) {
            deptSelect.innerHTML = '<option value="">-- All Departments --</option>';
            if (orgData && orgData.departments) {
                orgData.departments.forEach(dept => {
                    deptSelect.innerHTML += `<option value="${dept.id}">${dept.nama}</option>`;
                });
            }
        }
    } catch (e) {
        console.error('Error loading early arrival departments:', e);
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
    const deptId = document.getElementById('eaDeptFilter')?.value;
    const employeeId = document.getElementById('eaEmployeeFilter')?.value;

    const selectAllCheckbox = document.getElementById('chkEaSelectAll');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;

    if (!clientId) {
        const noClientHtml = `<tr><td colspan="10" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
            Please select a client first to display early arrival requests.</td></tr>`;
        pendingTbody.innerHTML = noClientHtml;
        historyTbody.innerHTML = `<tr><td colspan="12" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
            Please select a client first to display history.</td></tr>`;
        
        // Reset summary elements
        document.getElementById('eaSummaryPending').innerText = '0 Requests (0 min)';
        document.getElementById('eaSummaryApproved').innerText = '0 Logs (0 min)';
        document.getElementById('eaSummaryRejected').innerText = '0 Logs (0 min)';
        document.getElementById('eaSummaryProcessed').innerText = '0 Logs (0 min)';
        return;
    }

    const loadingHtml = `<tr><td colspan="12" style="text-align:center;padding:40px;color:#94a3b8;">
        <i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block;"></i>Loading early arrival data...</td></tr>`;
    pendingTbody.innerHTML = loadingHtml;
    historyTbody.innerHTML = loadingHtml;

    try {
        let url = `${API_URL}/early-arrival?client_id=${clientId}&bulan=${bulan}&tahun=${tahun}`;
        if (deptId) url += `&department_id=${deptId}`;
        if (employeeId) url += `&employee_id=${employeeId}`;

        const res = await fetch(url);
        const data = await res.json();
        window.currentEarlyArrivalLogs = data || [];

        // Clear local search inputs
        const searchInput = document.getElementById('eaHistorySearchInput');
        if (searchInput) searchInput.value = '';
        const statusFilter = document.getElementById('eaHistoryStatusFilter');
        if (statusFilter) statusFilter.value = '';

        if (!data || data.length === 0) {
            const noDataHtml = `<tr><td colspan="10" style="text-align:center;padding:40px;color:#94a3b8;">
                <i class="fas fa-clock" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
                No early arrival data for this period.</td></tr>`;
            pendingTbody.innerHTML = noDataHtml;
            historyTbody.innerHTML = `<tr><td colspan="12" style="text-align:center;padding:40px;color:#94a3b8;">
                <i class="fas fa-clock" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
                No early arrival history.</td></tr>`;
            
            document.getElementById('eaSummaryPending').innerText = '0 Requests (0 min)';
            document.getElementById('eaSummaryApproved').innerText = '0 Logs (0 min)';
            document.getElementById('eaSummaryRejected').innerText = '0 Logs (0 min)';
            document.getElementById('eaSummaryProcessed').innerText = '0 Logs (0 min)';
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

        document.getElementById('eaSummaryPending').innerText = `${pendingCount} Requests (${pendingMins} min)`;
        document.getElementById('eaSummaryApproved').innerText = `${approvedCount} Logs (${approvedMins} min)`;
        document.getElementById('eaSummaryRejected').innerText = `${rejectedCount} Logs (${rejectedMins} min)`;
        document.getElementById('eaSummaryProcessed').innerText = `${processedCount} Logs (${processedMins} min)`;

        // Render Pending Requests
        let pendingHtml = '';
        let pendingIndex = 1;
        
        data.forEach(item => {
            if (item.status !== 'PENDING') return;

            const d = new Date(item.date);
            const dateFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            
            pendingHtml += `<tr style="border-bottom: 1px solid #f1f5f9; hover { background: #f8fafc; }">
                <td style="text-align:center;padding:12px;">
                    <input type="checkbox" class="ea-row-checkbox" value="${item.id}" onchange="onEaCheckboxChange()" style="width:16px;height:16px;cursor:pointer;accent-color:var(--primary-color);">
                </td>
                <td style="padding:12px;font-weight:600;color:#1e293b;">
                    <i class="fas fa-user-clock" style="margin-right: 8px; opacity: 0.6; color: var(--primary-color);"></i>${item.employee_name || '-'}
                </td>
                <td style="text-align:center;padding:12px;color:#475569;font-size:12px;">${item.employee_nik || '-'}</td>
                <td style="text-align:center;padding:12px;color:#475569;">${dateFormatted}</td>
                <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${item.shift_start_time || '-'}</td>
                <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${item.check_in_time || '-'}</td>
                <td style="text-align:center;padding:12px;color:#475569;">${item.early_minutes} min</td>
                <td style="text-align:center;padding:12px;font-weight:700;color:#1e293b;">${item.eligible_minutes} min</td>
                <td style="text-align:center;padding:12px;">
                    <span style="background:#fffbeb;color:#d97706;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-clock"></i> Pending</span>
                </td>
                <td style="text-align:center;padding:12px;">
                    <div style="display:inline-flex;align-items:center;gap:6px;">
                        <button onclick="approveEarlyArrivalLog(${item.id})" style="background:#dcfce7;color:#166534;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px;" title="Approve">
                            <i class="fas fa-check"></i>
                        </button>
                        <button onclick="rejectEarlyArrivalLog(${item.id})" style="background:#fee2e2;color:#991b1b;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px;" title="Reject">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        });

        pendingTbody.innerHTML = pendingHtml || `<tr><td colspan="10" style="text-align:center;padding:30px;color:#94a3b8;">No pending requests found.</td></tr>`;

        // Render History Logs
        filterEaHistory();

    } catch (e) {
        console.error('Error loading early arrival logs:', e);
        pendingTbody.innerHTML = `<tr><td colspan="10" style="text-align:center;padding:40px;color:#ef4444;">Failed to load data: ${e.message}</td></tr>`;
        historyTbody.innerHTML = `<tr><td colspan="12" style="text-align:center;padding:40px;color:#ef4444;">Failed to load data: ${e.message}</td></tr>`;
    }
}

function switchEaSubPanel(panel) {
    const pendingPanel = document.getElementById('eaSubPanelPending');
    const historyPanel = document.getElementById('eaSubPanelHistory');
    const pendingBtn = document.getElementById('btnEaSubPanelPending');
    const historyBtn = document.getElementById('btnEaSubPanelHistory');

    if (panel === 'pending') {
        if (pendingPanel) pendingPanel.style.display = 'block';
        if (historyPanel) historyPanel.style.display = 'none';

        if (pendingBtn) {
            pendingBtn.style.color = 'var(--primary-color)';
            pendingBtn.style.borderBottomColor = 'var(--primary-color)';
            pendingBtn.style.fontWeight = '700';
        }
        if (historyBtn) {
            historyBtn.style.color = '#64748b';
            historyBtn.style.borderBottomColor = 'transparent';
            historyBtn.style.fontWeight = '600';
        }
    } else {
        if (pendingPanel) pendingPanel.style.display = 'none';
        if (historyPanel) historyPanel.style.display = 'block';

        if (pendingBtn) {
            pendingBtn.style.color = '#64748b';
            pendingBtn.style.borderBottomColor = 'transparent';
            pendingBtn.style.fontWeight = '600';
        }
        if (historyBtn) {
            historyBtn.style.color = 'var(--primary-color)';
            historyBtn.style.borderBottomColor = 'var(--primary-color)';
            historyBtn.style.fontWeight = '700';
        }
        filterEaHistory();
    }
}

function filterEaHistory() {
    const q = document.getElementById('eaHistorySearchInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('eaHistoryStatusFilter')?.value || '';
    const tbody = document.getElementById('eaHistoryTableBody');
    if (!tbody || !window.currentEarlyArrivalLogs) return;

    const filtered = window.currentEarlyArrivalLogs.filter(item => {
        if (item.status === 'PENDING') return false; // History doesn't show pending

        const matchesQuery = !q ||
            (item.employee_name && item.employee_name.toLowerCase().includes(q)) ||
            (item.employee_nik && item.employee_nik.toLowerCase().includes(q));

        const matchesStatus = !statusFilter || item.status === statusFilter;

        return matchesQuery && matchesStatus;
    });

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="12" style="text-align:center;padding:30px;color:#94a3b8;">No matching history logs found.</td></tr>`;
        return;
    }

    let historyHtml = '';
    let index = 1;
    filtered.forEach(item => {
        const d = new Date(item.date);
        const dateFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

        let statusBadge = '';
        if (item.status === 'APPROVED') {
            statusBadge = `<span style="background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-check-circle"></i> Approved</span>`;
        } else if (item.status === 'REJECTED') {
            statusBadge = `<span style="background:#fee2e2;color:#b91c1c;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-times-circle"></i> Rejected</span>`;
        } else if (item.status === 'PROCESSED') {
            statusBadge = `<span style="background:#eff6ff;color:#1d4ed8;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-file-invoice-dollar"></i> Processed</span>`;
        }

        const appDate = item.approved_at ? new Date(item.approved_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '';
        const verifier = item.approved_by ? `<span style="font-size:12px;font-weight:600;color:#334155;">${item.approved_by}</span><br><span style="font-size:10px;color:#94a3b8;">${appDate}</span>` : '-';

        let actionButtons = '';
        if (item.status !== 'PROCESSED') {
            actionButtons = `<button onclick="resetEarlyArrivalLog(${item.id})" style="background:#f1f5f9;color:#475569;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;display:inline-flex;align-items:center;gap:4px;transition: all 0.2s;" title="Reset back to pending"><i class="fas fa-undo"></i> Reset</button>`;
        } else {
            actionButtons = `<span style="font-size:12px;color:#94a3b8;font-style:italic;"><i class="fas fa-lock"></i> Locked</span>`;
        }

        historyHtml += `<tr style="border-bottom:1px solid #f1f5f9; hover { background: #f8fafc; }">
            <td style="text-align:center;padding:12px;color:#64748b;">${index++}</td>
            <td style="padding:12px;font-weight:600;color:#1e293b;">${item.employee_name || '-'}</td>
            <td style="text-align:center;padding:12px;color:#475569;font-size:12px;">${item.employee_nik || '-'}</td>
            <td style="text-align:center;padding:12px;color:#475569;">${dateFormatted}</td>
            <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${item.shift_start_time || '-'}</td>
            <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${item.check_in_time || '-'}</td>
            <td style="text-align:center;padding:12px;color:#475569;">${item.early_minutes} min</td>
            <td style="text-align:center;padding:12px;font-weight:700;color:#1e293b;">${item.eligible_minutes} min</td>
            <td style="text-align:center;padding:12px;">${statusBadge}</td>
            <td style="text-align:center;padding:12px;color:#475569;font-weight:600;">${item.payroll_period || '-'}</td>
            <td style="padding:12px;">${verifier}</td>
            <td style="text-align:center;padding:12px;">${actionButtons}</td>
        </tr>`;
    });
    tbody.innerHTML = historyHtml;
}

function toggleEaSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.ea-row-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function onEaCheckboxChange() {
    // If not all rows are selected, uncheck the select all checkbox
    const checkboxes = document.querySelectorAll('.ea-row-checkbox');
    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    const selectAllCheckbox = document.getElementById('chkEaSelectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
    }
}

async function bulkApproveEarlyArrival() {
    const checkedBoxes = document.querySelectorAll('.ea-row-checkbox:checked');
    const ids = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
    
    if (ids.length === 0) {
        showToast('Please select at least one early arrival request to approve.', 'error');
        return;
    }

    const confirmMsg = `Are you sure you want to approve ${ids.length} selected early arrival requests?`;
    if (typeof showConfirm === 'function') {
        const approved = await showConfirm(confirmMsg, 'Bulk Approve', 'Approve', 'Cancel', 'success');
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
            showToast(data.message || 'Early arrival logs approved successfully.', 'success');
            loadEarlyArrivalLogs();
        } else {
            showToast(data.message || 'Failed to approve early arrival logs.', 'error');
        }
    } catch (e) {
        console.error('Error in bulkApproveEarlyArrival:', e);
        showToast('Connection error, please try again.', 'error');
    }
}

async function bulkRejectEarlyArrival() {
    const checkedBoxes = document.querySelectorAll('.ea-row-checkbox:checked');
    const ids = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
    
    if (ids.length === 0) {
        showToast('Please select at least one early arrival request to reject.', 'error');
        return;
    }

    const confirmMsg = `Are you sure you want to reject ${ids.length} selected early arrival requests?`;
    if (typeof showConfirm === 'function') {
        const rejected = await showConfirm(confirmMsg, 'Bulk Reject', 'Reject', 'Cancel', 'danger');
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
            showToast(data.message || 'Early arrival logs rejected successfully.', 'success');
            loadEarlyArrivalLogs();
        } else {
            showToast(data.message || 'Failed to reject early arrival logs.', 'error');
        }
    } catch (e) {
        console.error('Error in bulkRejectEarlyArrival:', e);
        showToast('Connection error, please try again.', 'error');
    }
}

async function approveEarlyArrivalLog(id) {
    const confirmMsg = 'Are you sure you want to approve this early arrival request?';
    if (typeof showConfirm === 'function') {
        const approved = await showConfirm(confirmMsg, 'Approve Request', 'Approve', 'Cancel', 'success');
        if (!approved) return;
    } else {
        if (!confirm(confirmMsg)) return;
    }

    try {
        const res = await fetch(`${API_URL}/early-arrival/approve/${id}`, { method: 'POST' });
        const data = await res.json();
        if (res.ok) {
            showToast(data.message || 'Early arrival approved.', 'success');
            loadEarlyArrivalLogs();
        } else {
            showToast(data.message || 'Failed to approve request.', 'error');
        }
    } catch (e) {
        console.error('Error in approveEarlyArrivalLog:', e);
    }
}

async function rejectEarlyArrivalLog(id) {
    const confirmMsg = 'Are you sure you want to reject this early arrival request?';
    if (typeof showConfirm === 'function') {
        const rejected = await showConfirm(confirmMsg, 'Reject Request', 'Reject', 'Cancel', 'danger');
        if (!rejected) return;
    } else {
        if (!confirm(confirmMsg)) return;
    }

    try {
        const res = await fetch(`${API_URL}/early-arrival/reject/${id}`, { method: 'POST' });
        const data = await res.json();
        if (res.ok) {
            showToast(data.message || 'Early arrival rejected.', 'success');
            loadEarlyArrivalLogs();
        } else {
            showToast(data.message || 'Failed to reject request.', 'error');
        }
    } catch (e) {
        console.error('Error in rejectEarlyArrivalLog:', e);
    }
}

async function resetEarlyArrivalLog(id) {
    const confirmMsg = 'Are you sure you want to return this early arrival log to Pending status?';
    if (typeof showConfirm === 'function') {
        const reset = await showConfirm(confirmMsg, 'Reset Status', 'Reset to Pending', 'Cancel', 'primary');
        if (!reset) return;
    } else {
        if (!confirm(confirmMsg)) return;
    }

    try {
        const res = await fetch(`${API_URL}/early-arrival/reset/${id}`, { method: 'POST' });
        const data = await res.json();
        if (res.ok) {
            showToast(data.message || 'Status returned to pending.', 'success');
            loadEarlyArrivalLogs();
        } else {
            showToast(data.message || 'Failed to reset status.', 'error');
        }
    } catch (e) {
        console.error('Error in resetEarlyArrivalLog:', e);
    }
}

Object.assign(window, {
    loadEarlyArrivalClients,
    onEaClientChanged,
    loadEarlyArrivalLogs,
    switchEaSubPanel,
    toggleEaSelectAll,
    onEaCheckboxChange,
    bulkApproveEarlyArrival,
    bulkRejectEarlyArrival,
    approveEarlyArrivalLog,
    rejectEarlyArrivalLog,
    resetEarlyArrivalLog,
    filterEaHistory
});
