// ===== CLIENT MODULE =====
// Extracted from app.js for modular monolith architecture

// ===== 1. KLIEN MODULE =====
async function renderTable() {
    try {
        const tbody = document.getElementById('tabelKlienBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        const response = await fetch(`${API_URL}/clients`);
        clients = await response.json();
        renderClientTableData(clients);
    } catch (err) { console.error(err); }
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderClientTableData(data) {
    const tbody = document.getElementById('tabelKlienBody');
    if (!tbody) return;
    tbody.innerHTML = data.map(client => {
        const dateJoined = client.tgl_gabung ? new Date(client.tgl_gabung).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '-';
        return `
            <tr style="cursor: pointer;" title="Click to open workspace for ${escapeHtml(client.nama)}" onclick="event.target.closest('button') ? null : selectClient(${client.id})">
                <td class="client-name-td">${escapeHtml(client.nama)} <i class="fas fa-chevron-right client-arrow-icon"></i></td>
                <td>${escapeHtml(client.sektor)}</td>
                <td>${client.npwp ? `'${escapeHtml(client.npwp)}'` : '-'}</td>
                <td>${escapeHtml(client.nib) || '-'}</td>
                <td>${dateJoined}</td>
                <td>${escapeHtml(client.alamat)}</td>
                <td>
                    <div class="action-btns">
                        <button class="btn-icon btn-edit" onclick="bukaModal('edit', ${client.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon btn-delete" onclick="hapusKlien(${client.id})"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

window.cariKlienAktif = function () {
    const q = document.getElementById('cariKlienGlobal').value.toLowerCase();
    if (!clients) return;
    if (!q) {
        renderClientTableData(clients);
        return;
    }
    const filtered = clients.filter(c =>
        (c.nama && c.nama.toLowerCase().includes(q)) ||
        (c.sektor && c.sektor.toLowerCase().includes(q)) ||
        (c.npwp && String(c.npwp).toLowerCase().includes(q)) ||
        (c.nib && String(c.nib).toLowerCase().includes(q))
    );
    renderClientTableData(filtered);
}

// ===== CLIENT WORKSPACE WORKFLOW =====
window.selectedClientId = null;
window.selectedClientName = null;
window.selectedClientSektor = null;

function selectClient(id, name, sektor) {
    if (!name || !sektor) {
        const client = (window.clients || clients || []).find(c => c.id == id);
        if (client) {
            name = client.nama;
            sektor = client.sektor;
        }
    }
    window.selectedClientId = id;
    window.selectedClientName = name;
    window.selectedClientSektor = sektor;
    window.currentPeriodId = null; // Reset period selection when switching clients

    localStorage.setItem('selectedClientId', id);
    localStorage.setItem('selectedClientName', name || '');
    localStorage.setItem('selectedClientSektor', sektor || '');

    document.getElementById('clientWorkspaceTitle').innerText = name || '-';
    document.getElementById('clientWorkspaceSektor').innerText = sektor || '-';

    switchView('clientWorkspace');
    switchWorkspaceTab('karyawan');
}

function backToClientList() {
    window.selectedClientId = null;
    window.selectedClientName = null;
    window.selectedClientSektor = null;
    localStorage.removeItem('selectedClientId');
    localStorage.removeItem('selectedClientName');
    localStorage.removeItem('selectedClientSektor');
    localStorage.removeItem('activeWorkspaceTab');
    switchView('klien');
}

function switchWorkspaceTab(tab) {
    // Auto-close any open modals when switching workspace tabs
    if (typeof tutupSemuaModal === 'function') tutupSemuaModal(true);

    localStorage.setItem('activeWorkspaceTab', tab);

    document.querySelectorAll('.ws-tab').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.querySelector(`.ws-tab[data-wtab="${tab}"]`);
    if (activeBtn) activeBtn.classList.add('active');

    document.querySelectorAll('.w-tab-panel').forEach(panel => panel.classList.remove('active'));
    const activePanel = document.getElementById('view' + tab.charAt(0).toUpperCase() + tab.slice(1));
    if (activePanel) activePanel.classList.add('active');

    if (tab === 'karyawan') {
        if (typeof switchClientKaryawanSubTab === 'function') {
            switchClientKaryawanSubTab('lokasi_kerja');
        } else {
            renderAllEmployees();
        }
    } else if (tab === 'struktur') {
        if (typeof renderClientOrg === 'function') {
            renderClientOrg(window.selectedClientId);
        }
    } else if (tab === 'kompensasi') {
        loadPilihanSkema();
        // Load scheme templates for the client
        if (typeof loadSchemeTemplates === 'function' && window.selectedClientId) {
            loadSchemeTemplates(window.selectedClientId);
        }
    } else if (tab === 'pkwt') {
        renderPKWTTable();
    } else if (tab === 'proses') {
        loadActivePeriod();
        if (typeof switchPayrollProcessSubTab === 'function') {
            switchPayrollProcessSubTab('processing');
        }
    }
}

async function populateMinimumWageDropdown(tipe, selectElementId) {
    try {
        const res = await fetch(`${API_URL}/minimum-wages?tipe=${tipe}`);
        const data = await res.json();
        const select = document.getElementById(selectElementId);
        if (select) {
            select.innerHTML = '<option value="">-- Select Region --</option>' +
                data.map(item => `<option value="${item.id}">${item.nama_daerah} (Rp ${parseFloat(item.nominal).toLocaleString('id-ID')})</option>`).join('');
        }
    } catch (err) {
        console.error('Error fetching minimum wages:', err);
    }
}

async function handlePilihanSkemaPayrollTipeChange() {
    const tipe = document.getElementById('pilihanSkemaPayrollTipe').value;
    const wilContainer = document.getElementById('pilihanSkemaPayrollWilayahContainer');
    const nomContainer = document.getElementById('pilihanSkemaPayrollNominalContainer');
    const tplContainer = document.getElementById('pilihanSkemaPayrollTemplateContainer');

    wilContainer.style.display = 'none';
    nomContainer.style.display = 'none';
    tplContainer.style.display = 'none';

    if (tipe === 'UMP' || tipe === 'UMK') {
        wilContainer.style.display = 'block';
        await populateMinimumWageDropdown(tipe, 'pilihanSkemaPayrollWilayah');
    } else if (tipe === 'Nominal') {
        nomContainer.style.display = 'block';
    } else if (tipe === 'Template') {
        tplContainer.style.display = 'block';
    }
}

async function handleSetupPayrollSchemeTipeChange() {
    const tipe = document.getElementById('setupPayrollSchemeTipe').value;
    const wilContainer = document.getElementById('setupPayrollSchemeWilayahContainer');
    const nomContainer = document.getElementById('setupPayrollSchemeNominalContainer');
    const tplContainer = document.getElementById('setupPayrollSchemeTemplateContainer');

    wilContainer.style.display = 'none';
    nomContainer.style.display = 'none';
    tplContainer.style.display = 'none';

    if (tipe === 'UMP' || tipe === 'UMK') {
        wilContainer.style.display = 'block';
        await populateMinimumWageDropdown(tipe, 'setupPayrollSchemeWilayah');
    } else if (tipe === 'Nominal') {
        nomContainer.style.display = 'block';
    } else if (tipe === 'Template') {
        tplContainer.style.display = 'block';
    }
}


function bukaModal(mode, id = null) {
    const modal = document.getElementById('modalClient');
    const overlay = document.getElementById('overlay');
    modal.style.display = 'block';
    overlay.style.display = 'block';
    if (mode === 'edit' && id) {
        const client = clients.find(c => c.id == id);
        if (client) {
            document.getElementById('modalTitle').innerText = 'Edit Client Data';
            document.getElementById('clientId').value = client.id;
            document.getElementById('namaKlien').value = client.nama;
            document.getElementById('emailKlien').value = client.email;
            const sektorSel = document.getElementById('sektorKlien');
            if (sektorSel && client.sektor) {
                let found = false;
                for (let i = 0; i < sektorSel.options.length; i++) {
                    if (sektorSel.options[i].value === client.sektor) {
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    const opt = document.createElement('option');
                    opt.value = client.sektor;
                    opt.innerText = client.sektor;
                    sektorSel.appendChild(opt);
                }
            }
            if (sektorSel) {
                sektorSel.value = client.sektor || '';
            }
            document.getElementById('nib').value = client.nib;
            document.getElementById('npwp').value = client.npwp ? String(client.npwp) : '';
            document.getElementById('tanggalBergabung').value = client.tgl_gabung ? client.tgl_gabung.split('T')[0] : '';
            document.getElementById('alamat').value = client.alamat;
        }
    } else {
        document.getElementById('modalTitle').innerText = 'Add Client Data';
        document.getElementById('formKlien').reset();
        document.getElementById('clientId').value = '';
    }
}

async function hapusKlien(id) {
    if (!await showConfirm('Are you sure you want to delete this client?')) return;
    try {
        const res = await fetch(`${API_URL}/clients/${id}`, { method: 'DELETE' });
        if (res.ok) { renderTable(); showToast('Client deleted successfully', 'success'); }
    } catch (err) { console.error(err); }
}

document.getElementById('formKlien')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('clientId').value;
    const data = {
        nama: document.getElementById('namaKlien').value,
        email: document.getElementById('emailKlien').value,
        sektor: document.getElementById('sektorKlien').value,
        nib: document.getElementById('nib').value,
        npwp: document.getElementById('npwp').value,
        tgl_gabung: document.getElementById('tanggalBergabung').value,
        alamat: document.getElementById('alamat').value,
        status: 'Aktif'
    };

    try {
        let url = `${API_URL}/clients`;
        let method = 'POST';
        if (id) {
            url += `/${id}`;
            method = 'PUT';
        }
        const res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (res.ok) {
            if (typeof tutupModal === 'function') tutupModal();
            else if (document.getElementById('modalClient')) document.getElementById('modalClient').style.display = 'none';
            if (document.getElementById('overlay')) document.getElementById('overlay').style.display = 'none';
            renderTable();
            showToast(id ? 'Client data updated successfully!' : 'Client data added successfully!', 'success');
        } else {
            showToast('Failed to save client data', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('A server error occurred', 'error');
    }
});
