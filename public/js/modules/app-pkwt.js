// ===== PKWT MODULE =====
// Extracted from app.js for modular monolith architecture

// ===== 6. PKWT (KONTRAK KERJA) =====
async function renderPKWTTable() {
    try {
        const url = window.selectedClientId ? `${API_URL}/pkwt?client_id=${window.selectedClientId}` : `${API_URL}/pkwt`;
        const response = await fetch(url);
        pkwtData = await response.json();
        const tbody = document.getElementById('tabelPKWTBody');
        if (!tbody) return;
        tbody.innerHTML = pkwtData.map(row => {
            const basicComp = (row.components || []).find(c => c.nama.toLowerCase().includes('gaji pokok'));
            return `
                <tr>
                    <td style="font-weight: 600; color: var(--primary-color);">${row.employee_name}</td>
                    <td>${row.client_name}</td>
                    <td>${row.position_name}</td>
                    <td>${new Date(row.start_date).toLocaleDateString()}</td>
                    <td><span class="status-badge ${row.status && row.status.toLowerCase() === 'aktif' ? 'success' : 'danger'}">${row.status}</span></td>
                    <td>${formatRupiah(basicComp ? basicComp.nilai : 0)}</td>
                    <td><button class="btn-icon btn-delete" onclick="hapusPKWT(${row.id})"><i class="fas fa-trash"></i></button></td>
                </tr>
            `;
        }).join('');
    } catch (err) { console.error(err); }
}

async function hapusPKWT(id) {
    if (!await showConfirm('Apakah Anda yakin ingin menghapus PKWT ini?')) return;
    try {
        const res = await fetch(`${API_URL}/pkwt/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderPKWTTable();
            showToast('PKWT berhasil dihapus!', 'success');
        } else {
            showToast('Gagal menghapus PKWT!', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Gagal menghapus PKWT!', 'error');
    }
}

function bukaModalPKWT() {
    document.getElementById('modalPKWT').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    fetch(`${API_URL}/clients`).then(r => r.json()).then(data => {
        const select = document.getElementById('pkwtClientId');
        select.innerHTML = '<option value="">-- Pilih Klien --</option>' + data.map(c => `<option value="${c.id}">${c.nama}</option>`).join('');
        if (window.selectedClientId) {
            select.value = window.selectedClientId;
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
        document.getElementById('pkwtSchemeText').innerText = conf ? `Skema: ${conf.payroll_scheme_name}` : 'Klien belum di-setup skemanya.';
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
                status: 'Aktif'
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
                    showToast('PKWT berhasil dibuat dan gaji telah tergenerate', 'success');
                } else {
                    showToast('Gagal membuat PKWT', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Gagal membuat PKWT', 'error');
            }
        });
    }
