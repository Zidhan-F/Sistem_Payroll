// === Early Arrival Module ===

const formatMinsToHours = (minutes) => {
    const mins = parseInt(minutes) || 0;
    const hrs = mins / 60;
    return (hrs % 1 === 0 ? hrs : hrs.toFixed(1)) + ' jam';
};

async function loadEarlyArrivalClients() {
    const select = document.getElementById('eaClientFilter');
    if (select && window.selectedClientId) {
        select.value = window.selectedClientId;
    }
    try {
        if (select && select.tagName === 'SELECT') {
            const res = await fetch(`${API_URL}/clients`);
            const clients = await res.json();
            select.innerHTML = '<option value="">-- Select Client --</option>';
            clients.forEach(c => {
                select.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
            });

            if (window.selectedClientId && clients.some(c => c.id == window.selectedClientId)) {
                select.value = window.selectedClientId;
            }
        }

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
    const clientId = window.selectedClientId || document.getElementById('eaClientFilter')?.value;
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
    const mainTbody = document.getElementById('eaTableBody');
    if (!pendingTbody && !historyTbody && !mainTbody) return;

    const clientId = window.selectedClientId || document.getElementById('eaClientFilter')?.value;
    const bulan = document.getElementById('eaMonthFilter')?.value;
    const tahun = document.getElementById('eaYearFilter')?.value;
    const employeeId = document.getElementById('eaEmployeeFilter')?.value;

    const selectAllCheckbox = document.getElementById('chkEaSelectAll') || document.getElementById('selectAllEa');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    
    const bulkActions = document.getElementById('earlyArrivalBulkActions') || document.getElementById('eaBulkActions');
    if (bulkActions) bulkActions.style.display = 'none';

    if (!clientId) {
        const noClientHtml = `<tr><td colspan="11" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
            Please select a client first untuk menampilkan data.</td></tr>`;
        if (pendingTbody) pendingTbody.innerHTML = noClientHtml;
        if (historyTbody) historyTbody.innerHTML = noClientHtml;
        if (mainTbody) {
            mainTbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:40px;color:#94a3b8;">
                <i class="fas fa-info-circle" style="font-size:32px;margin-bottom:8px;display:block;color:#f39c12;"></i>
                Please select a client first to display history data.</td></tr>`;
        }
        
        const summaryContainer = document.getElementById('eaSummaryContainer');
        if (summaryContainer) summaryContainer.style.display = 'none';
        
        const otPending = document.getElementById('eaSummaryPending');
        if (otPending) otPending.innerText = '0 Menit (0 requests)';
        const otApproved = document.getElementById('eaSummaryApproved');
        if (otApproved) otApproved.innerText = '0 Menit (0 logs)';
        const otRejected = document.getElementById('eaSummaryRejected');
        if (otRejected) otRejected.innerText = '0 Menit (0 logs)';
        const otProcessed = document.getElementById('eaSummaryProcessed');
        if (otProcessed) otProcessed.innerText = '0 Menit (0 logs)';
        return;
    }

    const loadingHtml = `<tr><td colspan="11" style="text-align:center;padding:40px;color:#94a3b8;">
        <i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block;"></i>Loading data...</td></tr>`;
    if (pendingTbody) pendingTbody.innerHTML = loadingHtml;
    if (historyTbody) historyTbody.innerHTML = loadingHtml;
    if (mainTbody) {
        mainTbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block;"></i>Loading data...</td></tr>`;
    }

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
            if (pendingTbody) pendingTbody.innerHTML = noDataHtml;
            if (historyTbody) historyTbody.innerHTML = noDataHtml;
            if (mainTbody) {
                mainTbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:40px;color:#94a3b8;">
                    <i class="fas fa-clock" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
                    Tidak ada data Early Arrival untuk periode ini.</td></tr>`;
            }
            
            const otPending = document.getElementById('eaSummaryPending');
            if (otPending) otPending.innerText = '0 Menit (0 requests)';
            const otApproved = document.getElementById('eaSummaryApproved');
            if (otApproved) otApproved.innerText = '0 Menit (0 logs)';
            const otRejected = document.getElementById('eaSummaryRejected');
            if (otRejected) otRejected.innerText = '0 Menit (0 logs)';
            const otProcessed = document.getElementById('eaSummaryProcessed');
            if (otProcessed) otProcessed.innerText = '0 Menit (0 logs)';
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

        const otPending = document.getElementById('eaSummaryPending');
        if (otPending) otPending.innerText = `${pendingMins} Menit (${pendingCount} requests)`;
        const otApproved = document.getElementById('eaSummaryApproved');
        if (otApproved) otApproved.innerText = `${approvedMins} Menit (${approvedCount} logs)`;
        const otRejected = document.getElementById('eaSummaryRejected');
        if (otRejected) otRejected.innerText = `${rejectedMins} Menit (${rejectedCount} logs)`;
        const otProcessed = document.getElementById('eaSummaryProcessed');
        if (otProcessed) otProcessed.innerText = `${processedMins} Menit (${processedCount} logs)`;

        // Render panels
        if (pendingTbody || historyTbody) {
            filterEaPending();
            filterEaHistory();
        }

        if (mainTbody) {
            const activeStatusFilter = document.getElementById('eaStatusFilter')?.value || '';
            const filtered = data.filter(item => {
                const status = String(item.status).toUpperCase();
                if (activeStatusFilter && status !== activeStatusFilter.toUpperCase()) return false;
                return true;
            });

            if (filtered.length === 0) {
                mainTbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:40px;color:#94a3b8;">
                    <i class="fas fa-clock" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
                    Tidak ada data Early Arrival untuk kriteria ini.</td></tr>`;
            } else {
                let html = '';
                let index = 1;
                filtered.forEach(item => {
                    const d = new Date(item.date);
                    const dateFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

                    const status = String(item.status).toUpperCase();
                    let statusBadge = '';
                    let actionButtons = '';

                    if (status === 'PENDING') {
                        statusBadge = `<span style="background:#fffbeb;color:#d97706;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-clock"></i> Pending</span>`;
                        actionButtons = `
                            <button onclick="approveEarlyArrivalLog(${item.id})" style="background:#dcfce7;color:#166534;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; margin-right: 4px;" title="Setujui">
                                <i class="fas fa-check"></i>
                            </button>
                            <button onclick="rejectEarlyArrivalLog(${item.id})" style="background:#fee2e2;color:#991b1b;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px;" title="Tolak">
                                <i class="fas fa-times"></i>
                            </button>`;
                    } else if (status === 'APPROVED') {
                        statusBadge = `<span style="background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-check-circle"></i> Approved</span>`;
                        actionButtons = `<button onclick="resetEarlyArrivalLog(${item.id})" style="background:#f1f5f9;color:#475569;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;display:inline-flex;align-items:center;gap:4px;transition: all 0.2s;" title="Kembalikan ke pending"><i class="fas fa-undo"></i> Reset</button>`;
                    } else if (status === 'REJECTED') {
                        statusBadge = `<span style="background:#fee2e2;color:#b91c1c;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-times-circle"></i> Rejected</span>`;
                        actionButtons = `<button onclick="resetEarlyArrivalLog(${item.id})" style="background:#f1f5f9;color:#475569;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;display:inline-flex;align-items:center;gap:4px;transition: all 0.2s;" title="Kembalikan ke pending"><i class="fas fa-undo"></i> Reset</button>`;
                    } else if (status === 'PROCESSED') {
                        statusBadge = `<span style="background:#eff6ff;color:#1d4ed8;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-file-invoice-dollar"></i> Processed</span>`;
                        actionButtons = `<span style="font-size:12px;color:#94a3b8;font-style:italic;"><i class="fas fa-lock"></i> Terkunci (Payroll)</span>`;
                    }

                    html += `<tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="text-align:center;padding:16px;">
                            <input type="checkbox" class="ea-row-checkbox" value="${item.id}" onchange="onEaCheckboxChange()" style="width:16px;height:16px;cursor:pointer;accent-color:var(--primary-color);">
                        </td>
                        <td style="text-align:center;padding:16px;color:#64748b;">${index++}</td>
                        <td style="padding:16px;font-weight:600;color:#1e293b;">
                            <i class="fas fa-user-clock" style="margin-right: 8px; opacity: 0.6; color: var(--primary-color);"></i>${item.employee_name || '-'} (${item.employee_nik || '-'})
                        </td>
                        <td style="text-align:center;padding:16px;color:#475569;">${dateFormatted}</td>
                        <td style="text-align:center;padding:16px;color:#475569;font-weight:600;">${item.shift_start_time || '-'} / ${item.check_in_time || '-'}</td>
                        <td style="text-align:center;padding:16px;color:#475569;">${formatMinsToHours(item.early_minutes)} (Eligible: ${formatMinsToHours(item.eligible_minutes)})</td>
                        <td style="padding:16px;color:#475569;">${item.keterangan || '-'}</td>
                        <td style="text-align:center;padding:16px;">${statusBadge}</td>
                        <td style="text-align:center;padding:16px;">${actionButtons}</td>
                    </tr>`;
                });
                mainTbody.innerHTML = html;
            }
        }

    } catch (e) {
        console.error('Error loading early arrival logs:', e);
        const errorHtml = `<tr><td colspan="11" style="text-align:center;padding:40px;color:#ef4444;">Failed to load data: ${e.message}</td></tr>`;
        if (pendingTbody) pendingTbody.innerHTML = errorHtml;
        if (historyTbody) historyTbody.innerHTML = errorHtml;
        if (mainTbody) mainTbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:40px;color:#ef4444;">Failed to load data: ${e.message}</td></tr>`;
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
            <td style="text-align:center;padding:16px;color:#475569;">${formatMinsToHours(item.early_minutes)}</td>
            <td style="text-align:center;padding:16px;font-weight:700;color:#1e293b;">${formatMinsToHours(item.eligible_minutes)}</td>
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
            <td style="text-align:center;padding:16px;color:#475569;">${formatMinsToHours(item.early_minutes)}</td>
            <td style="text-align:center;padding:16px;font-weight:700;color:#1e293b;">${formatMinsToHours(item.eligible_minutes)}</td>
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
    
    const selectAllCheckbox = document.getElementById('chkEaSelectAll') || document.getElementById('selectAllEa');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
    }

    const bulkActions = document.getElementById('earlyArrivalBulkActions') || document.getElementById('eaBulkActions');
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
        showToast('Select at least one Early Arrival log to approve.', 'error');
        return;
    }

    const confirmMsg = `Apakah Anda yakin ingin menyetujui ${ids.length} log Early Arrival terpilih?`;
    if (typeof showConfirm === 'function') {
        const approved = await showConfirm(confirmMsg, 'Bulk Approval', 'Approve', 'Cancel', 'success');
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
            showToast(data.message || 'Early Arrival log approved successfully.', 'success');
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
        showToast('Select at least one Early Arrival log to reject.', 'error');
        return;
    }

    const confirmMsg = `Apakah Anda yakin ingin menolak ${ids.length} log Early Arrival terpilih?`;
    if (typeof showConfirm === 'function') {
        const rejected = await showConfirm(confirmMsg, 'Bulk Rejection', 'Reject', 'Cancel', 'danger');
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
            showToast(data.message || 'Early Arrival log rejected successfully.', 'success');
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
        const approved = await showConfirm(confirmMsg, 'Approve Request', 'Approve', 'Cancel', 'success');
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
        const rejected = await showConfirm(confirmMsg, 'Reject Request', 'Reject', 'Cancel', 'danger');
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
        const reset = await showConfirm(confirmMsg, 'Reset Status', 'Reset to Pending', 'Cancel', 'primary');
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

function toggleSelectAllEa(checkbox) {
    toggleEaSelectAll(checkbox);
}

function approveSelectedEarlyArrival() {
    bulkApproveEarlyArrival();
}

function rejectSelectedEarlyArrival() {
    bulkRejectEarlyArrival();
}

let parsedEarlyArrivalData = [];
let earlyArrivalUploadPeriods = [];

async function downloadEarlyArrivalTemplate() {
    showToast('Downloading Early Arrival template...', 'info');
    try {
        const clientId = window.selectedClientId || document.getElementById('eaClientFilter')?.value;
        let sampleRows = [
            ['NIK', 'Nama', 'Tanggal', 'Jam Shift Masuk', 'Jam Check In', 'Menit Early Arrival', 'Alasan / Keterangan']
        ];

        if (clientId) {
            try {
                const res = await fetch(`${API_URL}/employees?client_id=${clientId}`);
                const emps = await res.json();
                const list = emps.data || emps;
                if (Array.isArray(list) && list.length > 0) {
                    const todayStr = new Date().toISOString().slice(0, 10);
                    list.slice(0, 10).forEach(emp => {
                        sampleRows.push([
                            emp.nik || '',
                            emp.nama || '',
                            todayStr,
                            '08:00',
                            '07:30',
                            30,
                            'Penugasan operasional buka toko lebih awal'
                        ]);
                    });
                }
            } catch (err) {
                console.warn('Could not fetch employees for sample template:', err);
            }
        }

        if (sampleRows.length === 1) {
            sampleRows.push(
                ['EMP001', 'Budi Santoso', '2026-01-15', '08:00', '07:30', 30, 'Penugasan operasional buka toko lebih awal'],
                ['EMP002', 'Siti Rahma', '2026-01-15', '08:00', '07:15', 45, 'Briefing pagi shift 1']
            );
        }

        const worksheet = XLSX.utils.aoa_to_sheet(sampleRows);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Early Arrival Template");

        const max_widths = [15, 25, 15, 18, 16, 20, 40];
        worksheet['!cols'] = max_widths.map(w => ({ wch: w }));

        const filename = 'Early_Arrival_Template.xlsx';
        XLSX.writeFile(workbook, filename);
        showToast('Template Early Arrival berhasil diunduh!', 'success');
    } catch (e) {
        console.error(e);
        showToast('Gagal mengunduh template: ' + (e.message || e), 'error');
    }
}

async function downloadEarlyArrivalTemplateMain() {
    await downloadEarlyArrivalTemplate();
}

async function bukaModalUploadEarlyArrival() {
    const logsEl = document.getElementById('uploadEarlyArrivalLogs');
    if (logsEl) logsEl.innerHTML = "Pilih Client dan Periode untuk memulai.";
    
    const labelFilename = document.getElementById('labelEarlyArrivalFilename');
    if (labelFilename) labelFilename.innerText = "No file selected";
    
    const fileInput = document.getElementById('fileEarlyArrivalExcel');
    if (fileInput) fileInput.value = "";

    const text1 = document.getElementById('dropzoneEarlyArrivalText1');
    const text2 = document.getElementById('dropzoneEarlyArrivalText2');
    if (text1) text1.innerText = 'Pilih File Excel Early Arrival';
    if (text2) text2.innerText = 'Kolom: NIK, Nama, Tanggal (YYYY-MM-DD), Jam Check In';

    const saveBtn = document.getElementById('btnSaveUploadedEarlyArrival');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.style.opacity = "0.5";
        saveBtn.style.cursor = "not-allowed";
    }

    parsedEarlyArrivalData = [];

    // Populate Clients & Periods
    const select = document.getElementById('modalUploadEarlyArrivalClient');
    const periodSelect = document.getElementById('modalUploadEarlyArrivalPeriod');
    if (select) select.innerHTML = '<option value="">-- Select Client --</option>';
    if (periodSelect) {
        periodSelect.innerHTML = '<option value="">-- Select Client First --</option>';
        periodSelect.disabled = true;
    }

    try {
        const res = await fetch(`${API_URL}/clients`);
        const clients = await res.json();
        if (select && Array.isArray(clients)) {
            clients.forEach(c => {
                select.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
            });
        }

        const activeClientId = window.selectedClientId || document.getElementById('eaClientFilter')?.value;
        if (select && activeClientId) {
            select.value = activeClientId;
            await onEarlyArrivalUploadClientChanged();
        }
    } catch(e) {
        console.error(e);
        showToast('Failed to load client list', 'error');
    }

    const modal = document.getElementById('modalUploadEarlyArrival');
    const overlay = document.getElementById('overlay');
    if (modal) modal.style.display = 'block';
    if (overlay) overlay.style.display = 'block';
}

function tutupModalUploadEarlyArrival() {
    const modal = document.getElementById('modalUploadEarlyArrival');
    const overlay = document.getElementById('overlay');
    if (modal) modal.style.display = 'none';
    if (overlay) overlay.style.display = 'none';
}

async function onEarlyArrivalUploadClientChanged() {
    const clientId = document.getElementById('modalUploadEarlyArrivalClient')?.value;
    const periodSelect = document.getElementById('modalUploadEarlyArrivalPeriod');
    if (!periodSelect) return;

    if (!clientId) {
        periodSelect.innerHTML = '<option value="">-- Select Client First --</option>';
        periodSelect.disabled = true;
        return;
    }

    periodSelect.innerHTML = '<option value="">-- Loading Periods... --</option>';
    periodSelect.disabled = true;

    try {
        const res = await fetch(`${API_URL}/periods?client_id=${clientId}`);
        const periods = await res.json();
        earlyArrivalUploadPeriods = Array.isArray(periods) ? periods : [];

        if (earlyArrivalUploadPeriods.length === 0) {
            periodSelect.innerHTML = '<option value="">-- No open payroll periods found --</option>';
            periodSelect.disabled = true;
            return;
        }

        periodSelect.innerHTML = '<option value="">-- Select Payroll Period --</option>';
        earlyArrivalUploadPeriods.forEach(p => {
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            const mName = monthNames[parseInt(p.bulan) - 1] || p.bulan;
            periodSelect.innerHTML += `<option value="${p.id}">${mName} ${p.tahun}</option>`;
        });

        const currentMonth = document.getElementById('eaMonthFilter')?.value || (new Date().getMonth() + 1);
        const currentYear = document.getElementById('eaYearFilter')?.value || new Date().getFullYear();
        const match = earlyArrivalUploadPeriods.find(p => p.bulan == currentMonth && p.tahun == currentYear);
        if (match) {
            periodSelect.value = match.id;
        } else if (earlyArrivalUploadPeriods.length > 0) {
            periodSelect.value = earlyArrivalUploadPeriods[0].id;
        }

        periodSelect.disabled = false;
    } catch (e) {
        console.error(e);
        periodSelect.innerHTML = '<option value="">-- Failed to load periods --</option>';
        periodSelect.disabled = true;
    }
}

function handleEarlyArrivalFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    processEarlyArrivalFile(file);
}

function processEarlyArrivalFile(file) {
    if (!file) return;

    const clientId = document.getElementById('modalUploadEarlyArrivalClient')?.value;
    const periodId = document.getElementById('modalUploadEarlyArrivalPeriod')?.value;
    if (!clientId || !periodId) {
        showToast('Pilih Client dan Periode terlebih dahulu sebelum memilih file.', 'warning');
        return;
    }

    const text1 = document.getElementById('dropzoneEarlyArrivalText1');
    const text2 = document.getElementById('dropzoneEarlyArrivalText2');
    if (text1) text1.innerText = file.name;
    if (text2) text2.innerText = 'File selected. Click or drag another file to replace.';

    const labelFilename = document.getElementById('labelEarlyArrivalFilename');
    if (labelFilename) labelFilename.innerText = file.name;

    const logsDiv = document.getElementById('uploadEarlyArrivalLogs');
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
            processParsedEarlyArrival(json);
        } catch (err) {
            console.error(err);
            if (logsDiv) logsDiv.innerHTML += `Error parsing file: ${err.message || err}\n`;
        }
    };
    reader.readAsArrayBuffer(file);
}

function handleEarlyArrivalDragOver(event) {
    event.preventDefault();
    const zone = document.getElementById('dropzoneEarlyArrivalExcel');
    if (zone) {
        zone.style.borderColor = 'var(--primary-dark)';
        zone.style.backgroundColor = 'rgba(39, 174, 96, 0.18)';
    }
}

function handleEarlyArrivalDragLeave(event) {
    event.preventDefault();
    const zone = document.getElementById('dropzoneEarlyArrivalExcel');
    if (zone) {
        zone.style.borderColor = '#27ae60';
        zone.style.backgroundColor = 'rgba(39, 174, 96, 0.08)';
    }
}

function handleEarlyArrivalDrop(event) {
    event.preventDefault();
    const zone = document.getElementById('dropzoneEarlyArrivalExcel');
    if (zone) {
        zone.style.borderColor = '#27ae60';
        zone.style.backgroundColor = 'rgba(39, 174, 96, 0.08)';
    }
    
    if (event.dataTransfer.files && event.dataTransfer.files.length > 0) {
        const file = event.dataTransfer.files[0];
        const fileInput = document.getElementById('fileEarlyArrivalExcel');
        if (fileInput) {
            fileInput.files = event.dataTransfer.files;
        }
        processEarlyArrivalFile(file);
    }
}

function processParsedEarlyArrival(json) {
    parsedEarlyArrivalData = [];
    const logsDiv = document.getElementById('uploadEarlyArrivalLogs');

    let validCount = 0;
    json.forEach((row, idx) => {
        const nik = (row['NIK'] || row['nik'] || '').toString().trim();
        const nama = (row['Nama'] || row['nama'] || row['Nama Karyawan'] || '').toString().trim();
        const tanggal = (row['Tanggal'] || row['tanggal'] || row['Date'] || '').toString().trim();
        const shiftStart = (row['Jam Shift Masuk'] || row['Shift Masuk'] || row['shift_start_time'] || '08:00').toString().trim();
        const checkIn = (row['Jam Check In'] || row['Check In'] || row['check_in_time'] || '').toString().trim();
        const menit = parseInt(row['Menit Early Arrival'] || row['Menit'] || row['early_minutes'] || 0);
        const keterangan = (row['Alasan / Keterangan'] || row['Keterangan'] || row['Alasan'] || row['alasan'] || 'Upload Excel Early Arrival').toString().trim();

        if ((nik || nama) && tanggal) {
            parsedEarlyArrivalData.push({
                nik,
                nama,
                tanggal,
                shift_start_time: shiftStart,
                check_in_time: checkIn,
                early_minutes: menit,
                keterangan
            });
            validCount++;
        }
    });

    if (logsDiv) {
        logsDiv.innerHTML += `\nSukses: Berhasil memuat ${validCount} data Early Arrival yang valid.\nKlik 'Apply & Save Early Arrival' untuk menyimpan.`;
    }

    if (validCount > 0) {
        const btn = document.getElementById('btnSaveUploadedEarlyArrival');
        if (btn) {
            btn.disabled = false;
            btn.style.cursor = 'pointer';
            btn.style.opacity = '1';
        }
    }
}

async function saveUploadedEarlyArrival() {
    if (parsedEarlyArrivalData.length === 0) {
        showToast('Tidak ada data Early Arrival untuk disimpan.', 'warning');
        return;
    }

    const btn = document.getElementById('btnSaveUploadedEarlyArrival');
    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
    }

    const periodId = document.getElementById('modalUploadEarlyArrivalPeriod')?.value;
    const activePeriod = (earlyArrivalUploadPeriods || []).find(p => p.id == periodId);
    const payoutPeriodStr = activePeriod ? `${activePeriod.bulan}-${activePeriod.tahun}` : '';
    const clientId = document.getElementById('modalUploadEarlyArrivalClient')?.value || window.selectedClientId;

    showToast('Menyimpan data Early Arrival...', 'info');

    try {
        const res = await fetch(`${API_URL}/early-arrival/import`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                client_id: parseInt(clientId),
                logs: parsedEarlyArrivalData,
                payout_period: payoutPeriodStr
            })
        });

        const result = await res.json();
        if (res.ok && (result.success || result.status === 200 || result.imported_count >= 0)) {
            showToast(`Berhasil mengimpor ${result.imported_count || parsedEarlyArrivalData.length} data Early Arrival!`, 'success');
            tutupModalUploadEarlyArrival();
            loadEarlyArrivalLogs();
        } else {
            showToast(result.message || 'Gagal mengimpor data Early Arrival.', 'error');
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
    filterEaHistory,
    toggleSelectAllEa,
    approveSelectedEarlyArrival,
    rejectSelectedEarlyArrival,
    downloadEarlyArrivalTemplate,
    downloadEarlyArrivalTemplateMain,
    bukaModalUploadEarlyArrival,
    tutupModalUploadEarlyArrival,
    onEarlyArrivalUploadClientChanged,
    handleEarlyArrivalFileSelect,
    processEarlyArrivalFile,
    handleEarlyArrivalDragOver,
    handleEarlyArrivalDragLeave,
    handleEarlyArrivalDrop,
    processParsedEarlyArrival,
    saveUploadedEarlyArrival
});
