// ===== SALARY RAPEL ADJUSTMENTS MODULE =====
// UI logic, AJAX operations, manual forms, and approvals

let rapelAllData = [];
window.rapelEmployees = [];

async function loadRapelAdjustments() {
    if (!window.selectedClientId) return;

    const tbody = document.getElementById('tabelRapelBody');
    if (tbody) {
        tbody.innerHTML = `<tr><td colspan="11" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading rapel adjustments...</td></tr>`;
    }

    const month = document.getElementById('rapelMonthSelect').value;
    const year = document.getElementById('rapelYearSelect').value;
    const paymentPeriod = `${month}-${year}`;

    try {
        const response = await fetch(`${API_URL}/rapel-adjustments?client_id=${window.selectedClientId}&payment_period=${paymentPeriod}`);
        rapelAllData = await response.json();

        renderRapelAdjustmentsTable(rapelAllData);
    } catch (err) {
        console.error('Error loading rapel adjustments:', err);
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="11" style="text-align: center; padding: 20px; color: #ef4444;"><i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>Failed to load data.</td></tr>`;
        }
    }
}

function renderRapelAdjustmentsTable(data) {
    const tbody = document.getElementById('tabelRapelBody');
    if (!tbody) return;

    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="11" style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fas fa-info-circle" style="font-size: 36px; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                    No rapel adjustments found for this period. Click "Add Adjustment" or "Scan Differences".
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = data.map(row => {
        let statusBadge = '';
        let actionButtons = '';

        // Status styling matching premium HSL tailored design
        switch (row.status) {
            case 'Pending Approval':
                statusBadge = `<span class="status-badge warning" style="background-color: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">Pending Approval</span>`;
                actionButtons = `
                    <button class="btn-icon" onclick="updateRapelStatus(${row.id}, 'Approved')" title="Approve" style="background: #10b981; color: white; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; margin-right: 4px;"><i class="fas fa-check"></i></button>
                    <button class="btn-icon" onclick="updateRapelStatus(${row.id}, 'Rejected')" title="Reject" style="background: #ef4444; color: white; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; margin-right: 4px;"><i class="fas fa-times"></i></button>
                    <button class="btn-icon" onclick="deleteRapelAdjustment(${row.id})" title="Delete" style="background: #64748b; color: white; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer;"><i class="fas fa-trash"></i></button>
                `;
                break;
            case 'Approved':
                statusBadge = `<span class="status-badge success" style="background-color: #d1fae5; color: #059669; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">Approved</span>`;
                actionButtons = `
                    <button class="btn-icon" onclick="updateRapelStatus(${row.id}, 'Cancelled')" title="Cancel Approval" style="background: #6b7280; color: white; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer;"><i class="fas fa-ban"></i></button>
                `;
                break;
            case 'Paid':
                statusBadge = `<span class="status-badge info" style="background-color: #dbeafe; color: #2563eb; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">Paid</span>`;
                actionButtons = `<span style="font-size: 12px; color: #94a3b8; font-weight: 600;">None</span>`;
                break;
            case 'Rejected':
                statusBadge = `<span class="status-badge danger" style="background-color: #fee2e2; color: #dc2626; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">Rejected</span>`;
                actionButtons = `
                    <button class="btn-icon" onclick="deleteRapelAdjustment(${row.id})" title="Delete" style="background: #64748b; color: white; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer;"><i class="fas fa-trash"></i></button>
                `;
                break;
            case 'Cancelled':
                statusBadge = `<span class="status-badge" style="background-color: #f1f5f9; color: #64748b; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">Cancelled</span>`;
                actionButtons = `
                    <button class="btn-icon" onclick="updateRapelStatus(${row.id}, 'Pending Approval')" title="Re-submit" style="background: #3b82f6; color: white; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; margin-right: 4px;"><i class="fas fa-redo"></i></button>
                    <button class="btn-icon" onclick="deleteRapelAdjustment(${row.id})" title="Delete" style="background: #64748b; color: white; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer;"><i class="fas fa-trash"></i></button>
                `;
                break;
            default:
                statusBadge = `<span class="status-badge" style="background-color: #f1f5f9; color: #64748b; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">${row.status}</span>`;
                actionButtons = `<button class="btn-icon" onclick="deleteRapelAdjustment(${row.id})" title="Delete" style="background: #64748b; color: white; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer;"><i class="fas fa-trash"></i></button>`;
        }

        return `
            <tr>
                <td style="padding: 12px 15px; font-weight: 600; color: #1e293b;">${escapeHtml(row.employee_name)} <br><small style="color:#64748b; font-weight: normal;">NIK: ${escapeHtml(row.employee_nik || '-')}</small></td>
                <td style="padding: 12px 15px; font-size: 13px; color: #475569;">${escapeHtml(row.adjustment_type)}</td>
                <td style="padding: 12px 15px; font-size: 13px; font-weight: 600; color: #0f172a;">${escapeHtml(row.component_name)}</td>
                <td style="padding: 12px 15px; font-size: 13px; text-align: center;">${escapeHtml(row.reference_period)}</td>
                <td style="padding: 12px 15px; font-size: 13px; text-align: center;">${escapeHtml(row.payment_period)}</td>
                <td style="padding: 12px 15px; text-align: right; font-size: 13px; font-family: monospace;">${formatRupiah(row.previous_amount)}</td>
                <td style="padding: 12px 15px; text-align: right; font-size: 13px; font-family: monospace;">${formatRupiah(row.correct_amount)}</td>
                <td style="padding: 12px 15px; text-align: right; font-size: 13px; font-family: monospace; font-weight: 700; color: ${parseFloat(row.difference_amount) >= 0 ? '#10b981' : '#ef4444'}">${formatRupiah(row.difference_amount)}</td>
                <td style="padding: 12px 15px; font-size: 12px; color: #64748b; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(row.reason)}">${escapeHtml(row.reason || '-')}</td>
                <td style="padding: 12px 15px; text-align: center;">${statusBadge}</td>
                <td style="padding: 12px 15px; text-align: center; white-space: nowrap;">
                    <div style="display: flex; justify-content: center; gap: 4px;">
                        ${actionButtons}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

async function openModalAddRapel() {
    if (!window.selectedClientId) {
        showToast('Please select a client first!', 'error');
        return;
    }

    const modal = document.getElementById('modalRapel');
    const overlay = document.getElementById('overlay');
    if (!modal) return;

    modal.style.display = 'block';
    overlay.style.display = 'block';

    document.getElementById('modalRapelTitle').innerText = 'Add Rapel Adjustment';
    document.getElementById('formRapel').reset();
    document.getElementById('rapelAdjustmentId').value = '';
    
    // Set components defaults
    document.getElementById('rapelComponentNameInput').value = '';
    document.getElementById('rapelComponentNameInput').style.display = 'block';
    document.getElementById('rapelComponentNameSelect').style.display = 'none';

    // Populate years options (2 years back to 3 years forward)
    const currentYear = new Date().getFullYear();
    const populateYearDropdown = (elId) => {
        const el = document.getElementById(elId);
        if (el) {
            el.innerHTML = '';
            for (let y = currentYear - 2; y <= currentYear + 3; y++) {
                const opt = document.createElement('option');
                opt.value = y;
                opt.innerText = y;
                if (y === currentYear) opt.selected = true;
                el.appendChild(opt);
            }
        }
    };
    populateYearDropdown('rapelRefYear');
    populateYearDropdown('rapelPayYear');

    // Sync payment period selects with workspace page selects
    const wsMonth = document.getElementById('rapelMonthSelect').value;
    const wsYear = document.getElementById('rapelYearSelect').value;
    document.getElementById('rapelPayMonth').value = wsMonth;
    document.getElementById('rapelPayYear').value = wsYear;

    // Load active employees list for the client
    const empSelect = document.getElementById('rapelEmployeeSelect');
    empSelect.innerHTML = '<option value="">-- Loading Employees... --</option>';

    try {
        const res = await fetch(`${API_URL}/employees?client_id=${window.selectedClientId}`);
        window.rapelEmployees = await res.json();

        empSelect.innerHTML = '<option value="">-- Select Employee --</option>' +
            window.rapelEmployees.map(emp => `<option value="${emp.id}">${escapeHtml(emp.nama)} (NIK: ${escapeHtml(emp.employ_id || '-')})</option>`).join('');
    } catch (err) {
        console.error('Error loading employees:', err);
        empSelect.innerHTML = '<option value="">-- Failed to load employees --</option>';
    }

    calculateRapelDiff();
}

function onRapelEmployeeChange() {
    const empId = document.getElementById('rapelEmployeeSelect').value;
    const emp = (window.rapelEmployees || []).find(e => e.id == empId);
    
    if (emp) {
        const type = document.getElementById('rapelAdjustmentType').value;
        if (type === 'Salary Adjustment') {
            document.getElementById('rapelPreviousAmount').value = parseFloat(emp.gaji_pokok) || 0;
            document.getElementById('rapelComponentNameInput').value = 'Basic Salary';
            document.getElementById('rapelComponentNameInput').readOnly = true;
        } else {
            document.getElementById('rapelPreviousAmount').value = 0;
            document.getElementById('rapelComponentNameInput').readOnly = false;
        }
        calculateRapelDiff();
    }
}

function onRapelTypeChange() {
    const type = document.getElementById('rapelAdjustmentType').value;
    const inputField = document.getElementById('rapelComponentNameInput');
    const selectField = document.getElementById('rapelComponentNameSelect');
    const empId = document.getElementById('rapelEmployeeSelect').value;
    const emp = (window.rapelEmployees || []).find(e => e.id == empId);

    if (type === 'Salary Adjustment') {
        inputField.style.display = 'block';
        selectField.style.display = 'none';
        inputField.value = 'Basic Salary';
        inputField.readOnly = true;

        if (emp) {
            document.getElementById('rapelPreviousAmount').value = parseFloat(emp.gaji_pokok) || 0;
        }
    } else {
        inputField.style.display = 'block';
        selectField.style.display = 'none';
        inputField.readOnly = false;
        inputField.value = '';
        document.getElementById('rapelPreviousAmount').value = 0;
    }
    calculateRapelDiff();
}

function calculateRapelDiff() {
    const prev = parseFloat(document.getElementById('rapelPreviousAmount').value) || 0;
    const corr = parseFloat(document.getElementById('rapelCorrectAmount').value) || 0;
    const diff = corr - prev;
    document.getElementById('rapelDifferenceAmount').value = formatRupiah(diff);
}

async function saveRapelAdjustment(event) {
    event.preventDefault();

    const empId = document.getElementById('rapelEmployeeSelect').value;
    const refMonth = document.getElementById('rapelRefMonth').value;
    const refYear = document.getElementById('rapelRefYear').value;
    const payMonth = document.getElementById('rapelPayMonth').value;
    const payYear = document.getElementById('rapelPayYear').value;
    const adjType = document.getElementById('rapelAdjustmentType').value;
    const compName = document.getElementById('rapelComponentNameInput').value;
    const prevAmt = parseFloat(document.getElementById('rapelPreviousAmount').value) || 0.0;
    const corrAmt = parseFloat(document.getElementById('rapelCorrectAmount').value) || 0.0;
    const reason = document.getElementById('rapelReason').value;

    const payload = {
        employee_id: parseInt(empId),
        reference_period: `${refMonth}-${refYear}`,
        payment_period: `${payMonth}-${payYear}`,
        adjustment_type: adjType,
        component_name: compName,
        previous_amount: prevAmt,
        correct_amount: corrAmt,
        reason: reason
    };

    try {
        const res = await fetch(`${API_URL}/rapel-adjustments/create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            closeModal('modalRapel');
            loadRapelAdjustments();
            showToast('Rapel adjustment successfully saved!', 'success');
        } else {
            showToast('Failed to save rapel adjustment!', 'error');
        }
    } catch (err) {
        console.error('Error saving rapel adjustment:', err);
        showToast('System error occurred!', 'error');
    }
}

async function updateRapelStatus(id, status) {
    let confirmMsg = `Are you sure you want to change this adjustment's status to ${status}?`;
    if (status === 'Approved') confirmMsg = 'Confirm this adjustment details and approve for payment?';
    if (status === 'Rejected') confirmMsg = 'Reject this adjustment request?';
    if (status === 'Cancelled') confirmMsg = 'Cancel approval? The adjustment will not be paid.';
    
    if (!await showConfirm(confirmMsg, 'Adjustment Status Update', 'Yes, Update', 'Cancel', status === 'Approved' ? 'success' : 'danger')) return;

    try {
        const res = await fetch(`${API_URL}/rapel-adjustments/update-status/${id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: status })
        });

        if (res.ok) {
            loadRapelAdjustments();
            showToast(`Status updated to ${status}!`, 'success');
        } else {
            showToast('Failed to update status!', 'error');
        }
    } catch (err) {
        console.error('Error updating status:', err);
        showToast('System error occurred!', 'error');
    }
}

async function deleteRapelAdjustment(id) {
    if (!await showConfirm('Are you sure you want to delete this rapel adjustment?', 'Delete Adjustment', 'Yes, Delete', 'Cancel', 'danger')) return;

    try {
        const res = await fetch(`${API_URL}/rapel-adjustments/${id}`, {
            method: 'DELETE'
        });

        if (res.ok) {
            loadRapelAdjustments();
            showToast('Rapel adjustment deleted!', 'success');
        } else {
            showToast('Failed to delete rapel adjustment!', 'error');
        }
    } catch (err) {
        console.error('Error deleting adjustment:', err);
        showToast('System error occurred!', 'error');
    }
}

async function scanRetroactiveDifferences() {
    if (!window.selectedClientId) return;

    const month = document.getElementById('rapelMonthSelect').value;
    const year = document.getElementById('rapelYearSelect').value;
    const paymentPeriod = `${month}-${year}`;

    showToast('Scanning employee contracts and historical payroll records...', 'info');

    try {
        const res = await fetch(`${API_URL}/rapel-adjustments/auto-generate?client_id=${window.selectedClientId}&payment_period=${paymentPeriod}`, {
            method: 'POST'
        });
        const data = await res.json();

        if (res.ok) {
            loadRapelAdjustments();
            showToast(data.message || 'Scanning completed successfully!', 'success');
        } else {
            showToast('Failed to scan retroactive differences!', 'error');
        }
    } catch (err) {
        console.error('Error scanning differences:', err);
        showToast('System error occurred during scanning!', 'error');
    }
}

// Expose functions globally
Object.assign(window, {
    loadRapelAdjustments,
    openModalAddRapel,
    onRapelEmployeeChange,
    onRapelTypeChange,
    calculateRapelDiff,
    saveRapelAdjustment,
    updateRapelStatus,
    deleteRapelAdjustment,
    scanRetroactiveDifferences
});
