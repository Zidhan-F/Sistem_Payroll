// ===== PKWT MODULE =====
// Extracted from app.js for modular monolith architecture

// ===== 6. PKWT (KONTRAK KERJA) =====
async function renderPKWTTable() {
    try {
        const tbody = document.getElementById('tabelPKWTBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        const url = window.selectedClientId ? `${API_URL}/pkwt?client_id=${window.selectedClientId}` : `${API_URL}/pkwt`;
        const response = await fetch(url);
        pkwtData = await response.json();
        if (!tbody) return;
        tbody.innerHTML = pkwtData.map(row => {
            const basicComp = (row.components || []).find(c => c.nama.toLowerCase().includes('gaji pokok') || c.nama.toLowerCase().includes('basic salary'));
            return `
                <tr>
                    <td style="font-weight: 600; color: var(--primary-color);">${row.employee_name}</td>
                    <td>${row.client_name}</td>
                    <td>${row.position_name}</td>
                    <td>${new Date(row.start_date).toLocaleDateString()}</td>
                    <td>${row.end_date ? new Date(row.end_date).toLocaleDateString() : '-'}</td>
                    <td>${
                        basicComp && (basicComp.sumber_nilai === 'ump' || basicComp.sumber_nilai === 'umk')
                            ? `${basicComp.nilai}% ${basicComp.sumber_nilai.toUpperCase()} (${formatRupiah(basicComp.nilai_nominal)})`
                            : formatRupiah(basicComp ? (basicComp.nilai_nominal || basicComp.nilai) : 0)
                    }</td>
                    <td><button class="btn-icon btn-delete" onclick="hapusPKWT(${row.id})"><i class="fas fa-trash"></i></button></td>
                </tr>
            `;
        }).join('');
    } catch (err) { console.error(err); }
}

async function hapusPKWT(id) {
    if (!await showConfirm('Are you sure you want to delete this PKWT?')) return;
    try {
        const res = await fetch(`${API_URL}/pkwt/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderPKWTTable();
            showToast('PKWT deleted successfully!', 'success');
        } else {
            showToast('Failed to delete PKWT!', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Failed to delete PKWT!', 'error');
    }
}

function bukaModalPKWT() {
    document.getElementById('modalPKWT').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    fetch(`${API_URL}/clients`).then(r => r.json()).then(data => {
        const select = document.getElementById('pkwtClientId');
        if (select.tomselect) {
            select.tomselect.destroy();
        }
        select.innerHTML = '<option value="">-- Select Client --</option>' + data.map(c => `<option value="${c.id}">${c.nama}</option>`).join('');
        
        new TomSelect(select, {
            create: false,
            sortField: { field: "text", direction: "asc" }
        });

        select.tomselect.on('change', () => {
            if (typeof window.updatePKWTSchemeInfo === 'function') {
                window.updatePKWTSchemeInfo();
            }
        });

        if (window.selectedClientId) {
            select.tomselect.setValue(window.selectedClientId);
            if (typeof window.updatePKWTSchemeInfo === 'function') {
                window.updatePKWTSchemeInfo();
            }
        }
    });
}

    // Modal PKWT update info
    window.updatePKWTSchemeInfo = async () => {
        const clientId = document.getElementById('pkwtClientId').value;
        if(!clientId) return;
        const res = await fetch(`${API_URL}/client-configs`);
        const configs = await res.json();
        const conf = configs.find(c => c.client_id == clientId);
        const box = document.getElementById('pkwtSchemeInfo');
        box.style.display = 'block';
        document.getElementById('pkwtSchemeText').innerText = conf ? `Scheme: ${conf.payroll_scheme_name}` : 'The client scheme has not been set up yet.';
    };

    // Form PKWT submit handler
    if (document.getElementById('formPKWT')) {
        document.getElementById('formPKWT').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = {
                employee_name: document.getElementById('pkwtEmployeeName').value,
                client_id: parseInt(document.getElementById('pkwtClientId').value),
                position_name: document.getElementById('pkwtPositionName').value,
                start_date: document.getElementById('pkwtStartDate').value,
                end_date: document.getElementById('pkwtEndDate').value,
                basic_salary: parseFloat(document.getElementById('pkwtBasicSalary').value) || 0,
                status: 'Active'
            };
            try {
                const res = await fetch(`${API_URL}/pkwt`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                if (res.ok) {
                    tutupModalPKWT();
                    renderPKWTTable();
                    showToast('PKWT created successfully and salary has been generated', 'success');
                } else {
                    showToast('Failed to create PKWT', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Failed to create PKWT', 'error');
            }
        });
    }
