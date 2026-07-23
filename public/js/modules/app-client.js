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
    const role = typeof getCurrentRole === 'function' ? getCurrentRole() : 'admin';
    const canOpenWorkspace = typeof hasPermission === 'function' ? hasPermission('clientWorkspace') : true;
    const canEditDelete = (role !== 'staff' && role !== 'client_superior');

    tbody.innerHTML = data.map(client => {
        const dateJoined = client.tgl_gabung ? new Date(client.tgl_gabung).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '-';
        
        const rowStyle = canOpenWorkspace ? 'cursor: pointer;' : 'cursor: default;';
        const rowTitle = canOpenWorkspace ? `Click to open workspace for ${escapeHtml(client.nama)}` : '';
        const rowClick = canOpenWorkspace ? `onclick="event.target.closest('button') ? null : selectClient(${client.id})"` : '';
        const chevron = canOpenWorkspace ? ' <i class="fas fa-chevron-right client-arrow-icon"></i>' : '';
        
        const actionHtml = canEditDelete ? `
            <div class="action-btns">
                <button class="btn-icon btn-edit" onclick="bukaModal('edit', ${client.id})"><i class="fas fa-edit"></i></button>
                <button class="btn-icon btn-delete" onclick="hapusKlien(${client.id})"><i class="fas fa-trash"></i></button>
            </div>
        ` : '-';

        return `
            <tr style="${rowStyle}" title="${rowTitle}" ${rowClick}>
                <td class="client-name-td">${escapeHtml(client.nama)}${chevron}</td>
                <td>${escapeHtml(client.sektor)}</td>
                <td>${client.npwp ? `'${escapeHtml(client.npwp)}'` : '-'}</td>
                <td>${escapeHtml(client.nib) || '-'}</td>
                <td>${dateJoined}</td>
                <td>${escapeHtml(client.alamat)}</td>
                <td>${actionHtml}</td>
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
    
    // Pilih tab default berdasarkan role untuk alur proses kerja
    let defaultTab = 'karyawan';
    const role = typeof getCurrentRole === 'function' ? getCurrentRole() : 'admin';
    if (role === 'business_development') {
        defaultTab = 'struktur';
    } else if (role === 'hc_ops') {
        defaultTab = 'attendance';
    } else if (['payroll', 'client_superior', 'staff'].includes(role)) {
        defaultTab = 'proses';
    }
    switchWorkspaceTab(defaultTab);
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
        if (typeof switchClientPKWTSubTab === 'function') {
            switchClientPKWTSubTab('pkwt_data');
        } else {
            renderPKWTTable();
        }
    } else if (tab === 'proses') {
        loadActivePeriod();
        if (typeof switchPayrollProcessSubTab === 'function') {
            const role = typeof getCurrentRole === 'function' ? getCurrentRole() : 'admin';
            // Client / Superior & Staff langsung melihat hasil kalkulasi (4.1 & 4.2), Payroll melakukan proses (3.1)
            if (role === 'client_superior' || role === 'staff') {
                switchPayrollProcessSubTab('results');
            } else {
                switchPayrollProcessSubTab('processing');
            }
        }
    } else if (tab === 'laporan') {
        if (typeof loadPayrollReport === 'function') {
            loadPayrollReport(window.selectedClientId);
        }
    } else if (tab === 'attendance') {
        const selectEl = document.getElementById('attendanceClientSelect');
        if (selectEl) {
            selectEl.value = window.selectedClientId;
            if (typeof syncCustomClientDropdown === 'function') {
                syncCustomClientDropdown();
            }
        }
        if (typeof loadAttendanceLogs === 'function') {
            loadAttendanceLogs();
        }
    } else if (tab === 'overtime') {
        const selectEl = document.getElementById('overtimeClientSelect');
        if (selectEl) {
            selectEl.value = window.selectedClientId;
        }
        if (typeof loadOvertimeLogs === 'function') {
            loadOvertimeLogs();
        }
    } else if (tab === 'earlyArrival') {
        const filterEl = document.getElementById('eaClientFilter');
        if (filterEl) {
            filterEl.value = window.selectedClientId;
        }
        if (typeof loadEarlyArrivalLogs === 'function') {
            loadEarlyArrivalLogs();
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

function downloadClientExcelTemplate() {
    try {
        const headers = [['Client Name', 'Client Sector', 'Email', 'Phone', 'Join Date', 'Address', 'NIB', 'NPWP']];
        const dummyRow = [['Example Client Ltd', 'Tech', 'info@exampleclient.com', '021-1234567', '2026-07-01', 'Jl. Jendral Sudirman Kav. 21', '1234567890123', '123456789012345']];
        const data = headers.concat(dummyRow);

        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Clients Template');

        // Set column widths
        ws['!cols'] = [
            { wch: 25 }, // Client Name
            { wch: 15 }, // Client Sector
            { wch: 25 }, // Email
            { wch: 15 }, // Phone
            { wch: 15 }, // Join Date
            { wch: 35 }, // Address
            { wch: 20 }, // NIB
            { wch: 20 }  // NPWP
        ];

        XLSX.writeFile(wb, 'client_upload_template.xlsx');
        showToast('Template downloaded successfully', 'success');
    } catch (err) {
        console.error(err);
        showToast('Failed to download template', 'error');
    }
}

function triggerClientExcelUpload() {
    const input = document.getElementById('clientExcelInput');
    if (input) {
        input.value = ''; // Reset to allow uploading same file
        input.click();
    }
}

function handleClientExcelUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = async (e) => {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];
            
            // sheet_to_json with raw options
            const rows = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
            if (rows.length < 2) {
                showToast('The uploaded Excel file contains no data rows.', 'error');
                return;
            }

            // Map columns based on headers in the first row
            const headers = rows[0].map(h => h ? h.toString().trim().toLowerCase().replace(/\s+/g, '') : '');
            
            // Map header names to keys
            const nameIndex = headers.indexOf('clientname');
            const sectorIndex = headers.indexOf('clientsector');
            const emailIndex = headers.indexOf('email');
            const phoneIndex = headers.indexOf('phone');
            const joinDateIndex = headers.indexOf('joindate');
            const addressIndex = headers.indexOf('address');
            const nibIndex = headers.indexOf('nib');
            const npwpIndex = headers.indexOf('npwp');

            if (nameIndex === -1 || sectorIndex === -1 || emailIndex === -1 || joinDateIndex === -1) {
                showToast('Invalid template format! Missing required columns: Client Name, Client Sector, Email, or Join Date.', 'error');
                return;
            }

            const clients = [];
            for (let i = 1; i < rows.length; i++) {
                const r = rows[i];
                if (!r || r.length === 0) continue;

                // Check if the row is entirely empty
                const isRowEmpty = r.every(cell => cell === null || cell === undefined || cell.toString().trim() === '');
                if (isRowEmpty) continue;

                // Parse Join Date. Excel sometimes reads dates as numeric values
                let rawJoinDate = r[joinDateIndex];
                let formattedJoinDate = '';
                if (rawJoinDate) {
                    if (typeof rawJoinDate === 'number') {
                        // Excel serial date to JS Date
                        const dateObj = new Date((rawJoinDate - 25569) * 86400 * 1000);
                        formattedJoinDate = dateObj.toISOString().split('T')[0];
                    } else {
                        // Attempt to parse string
                        const dateStr = rawJoinDate.toString().trim();
                        if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                            formattedJoinDate = dateStr;
                        } else if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(dateStr)) {
                            // MM/DD/YYYY
                            const parts = dateStr.split('/');
                            const mm = parts[0].padStart(2, '0');
                            const dd = parts[1].padStart(2, '0');
                            const yyyy = parts[2];
                            formattedJoinDate = `${yyyy}-${mm}-${dd}`;
                        } else {
                            formattedJoinDate = dateStr;
                        }
                    }
                }

                // NPWP & NIB cleanup: force string format and strip any single quotes if Excel added them for text formatting
                const npwpVal = npwpIndex !== -1 && r[npwpIndex] ? r[npwpIndex].toString().replace(/^'|'$/g, '').trim() : '';
                const nibVal = nibIndex !== -1 && r[nibIndex] ? r[nibIndex].toString().replace(/^'|'$/g, '').trim() : '';

                const client = {
                    nama: nameIndex !== -1 && r[nameIndex] ? r[nameIndex].toString().trim() : '',
                    sektor: sectorIndex !== -1 && r[sectorIndex] ? r[sectorIndex].toString().trim() : '',
                    email: emailIndex !== -1 && r[emailIndex] ? r[emailIndex].toString().trim() : '',
                    telepon: phoneIndex !== -1 && r[phoneIndex] ? r[phoneIndex].toString().trim() : '',
                    tgl_gabung: formattedJoinDate,
                    alamat: addressIndex !== -1 && r[addressIndex] ? r[addressIndex].toString().trim() : '',
                    nib: nibVal,
                    npwp: npwpVal
                };

                clients.push(client);
            }

            if (clients.length === 0) {
                showToast('No valid data rows found in the uploaded file.', 'error');
                return;
            }

            // Post to backend
            const userObj = JSON.parse(localStorage.getItem('user'));
            const username = userObj ? userObj.username : 'admin';

            const response = await fetch(`${API_URL}/clients/bulk`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-User-Action': username
                },
                body: JSON.stringify(clients)
            });

            const resData = await response.json();
            if (response.ok) {
                showToast(resData.message || 'Bulk clients uploaded successfully!', 'success');
                renderTable();
            } else {
                if (resData.errors && Array.isArray(resData.errors)) {
                    alert('Failed to upload clients:\n\n' + resData.errors.join('\n'));
                } else {
                    showToast(resData.message || resData.error || 'Failed to import clients.', 'error');
                }
            }
        } catch (err) {
            console.error(err);
            showToast('Error parsing Excel file.', 'error');
        }
    };
    reader.readAsArrayBuffer(file);
}
