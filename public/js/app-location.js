let workLocations = [];
let nextWorkLocationId = 1;

async function fetchNextWorkLocationId() {
    try {
        const r = await fetch('/api/work-locations');
        const data = await r.json();
        if (data && data.length > 0) {
            const maxId = Math.max(...data.map(l => parseInt(l.id) || 0));
            nextWorkLocationId = maxId + 1;
        } else {
            nextWorkLocationId = 1;
        }
    } catch (e) {
        console.error(e);
        nextWorkLocationId = 1;
    }
}

function switchClientKaryawanSubTab(tab) {
    document.querySelectorAll('.client-karyawan-subpanel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.sub-tab-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.color = '#64748b';
        btn.style.borderBottomColor = 'transparent';
    });

    if (tab === 'lokasi_kerja') {
        document.getElementById('panelLokasiKerja').style.display = 'block';
        const activeBtn = document.getElementById('subTabLokasiKerja');
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.color = 'var(--primary-color)';
            activeBtn.style.borderBottomColor = 'var(--primary-color)';
        }
        loadWorkLocationsForClient();
    } else if (tab === 'karyawan_data') {
        document.getElementById('panelKaryawanData').style.display = 'block';
        const activeBtn = document.getElementById('subTabKaryawanData');
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.color = 'var(--primary-color)';
            activeBtn.style.borderBottomColor = 'var(--primary-color)';
        }
        if (typeof renderAllEmployees === 'function') {
            renderAllEmployees();
        }
    }
}

async function loadWorkLocations() {
    try {
        const r = await fetch('/api/work-locations');
        workLocations = await r.json();
        renderWorkLocationsTable();
    } catch (e) {
        console.error(e);
    }
}

let allWorkLocationsGlobal = [];

async function loadGlobalWorkLocations() {
    try {
        const tbody = document.getElementById('tabelGlobalLokasiKerjaBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        const r = await fetch('/api/work-locations');
        workLocations = await r.json();
        allWorkLocationsGlobal = [...workLocations];
        renderGlobalWorkLocationsTable(workLocations);
    } catch (e) {
        console.error(e);
    }
}

function renderGlobalWorkLocationsTable(list = null) {
    const tbody = document.getElementById('tabelGlobalLokasiKerjaBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    
    const items = list !== null ? list : workLocations;
    
    if (items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding: 20px; color: var(--text-muted);">No work location data found.</td></tr>`;
        return;
    }
    
    items.forEach(loc => {
        tbody.innerHTML += `
            <tr>
                <td style="font-weight:600;">${loc.lokasi_kerja}</td>
                <td style="font-weight:600;">${loc.location_code || '-'}</td>
                <td>${loc.nama_klien || '-'}</td>

                <td>${loc.provinsi || '-'}</td>
                <td>${loc.kota_kabupaten || '-'}</td>
                <td>
                    <div style="display:flex; justify-content:center; align-items:center; gap:12px;">
                        <button onclick="editLokasiKerja(${loc.id})" class="btn-icon" title="Edit" style="color:#94a3b8;background:transparent;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;box-shadow:none;width:auto;height:auto;padding:4px;"><i class="fas fa-edit" style="font-size:16px;"></i></button>
                        <button onclick="hapusLokasiKerja(${loc.id})" class="btn-icon" title="Hapus" style="color:#e74c3c;background:transparent;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;box-shadow:none;width:auto;height:auto;padding:4px;"><i class="fas fa-trash" style="font-size:16px;"></i></button>
                    </div>
                </td>
            </tr>
        `;
    });
}

function cariLokasiKerjaGlobalAktif() {
    const q = document.getElementById('cariLokasiKerjaGlobal').value.toLowerCase();
    if (!q) {
        renderGlobalWorkLocationsTable(allWorkLocationsGlobal);
        return;
    }
    const filtered = allWorkLocationsGlobal.filter(loc => {
        return (loc.lokasi_kerja && loc.lokasi_kerja.toLowerCase().includes(q)) ||
            (loc.location_code && loc.location_code.toLowerCase().includes(q)) ||
            (loc.nama_klien && loc.nama_klien.toLowerCase().includes(q)) ||
            (loc.provinsi && loc.provinsi.toLowerCase().includes(q)) ||
            (loc.kota_kabupaten && loc.kota_kabupaten.toLowerCase().includes(q));
    });
    renderGlobalWorkLocationsTable(filtered);
}

async function loadWorkLocationsForClient() {
    if (!window.selectedClientId) return;
    try {
        const tbody = document.getElementById('tabelLokasiKerjaBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        const r = await fetch(`/api/work-locations?client_id=${window.selectedClientId}`);
        workLocations = await r.json();
        renderWorkLocationsTable();
    } catch (e) {
        console.error(e);
    }
}

function renderWorkLocationsTable() {
    const tbody = document.getElementById('tabelLokasiKerjaBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    
    if (workLocations.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding: 20px; color: var(--text-muted);">Belum ada data lokasi kerja.</td></tr>`;
        return;
    }
    
    workLocations.forEach(loc => {
        tbody.innerHTML += `
            <tr>
                <td style="font-weight:600;">${loc.lokasi_kerja}</td>
                <td style="font-weight:600;">${loc.location_code || '-'}</td>
                <td>${loc.provinsi || '-'}</td>
                <td>${loc.kota_kabupaten || '-'}</td>
            </tr>
        `;
    });
}

async function bukaModalLokasiKerja() {
    document.getElementById('modalLokasiKerja').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    
    document.getElementById('formLokasiKerja').reset();
    document.getElementById('workLocationId').value = '';
    document.getElementById('modalLokasiKerjaTitle').innerText = 'Add Work Location';
    

    fetchNextWorkLocationId();

    const clientSelect = document.getElementById('locClientId');
    if (clientSelect) {
        clientSelect.disabled = false;
    }
    if (window.locClientSelectInstance) {
        window.locClientSelectInstance.enable();
    }
    
    await populateLocClients();
    await populateProvinsiKotaLists();
}

function tutupModalLokasiKerja() {
    document.getElementById('modalLokasiKerja').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

let umrDataCached = null;

function updateKotaListBasedOnProvinsi(e) {
    const provInput = document.getElementById('locProvinsi');
    const kotaInput = document.getElementById('locKotaKabupaten');
    const kotaList = document.getElementById('kotaList');
    if (!provInput || !kotaList || !umrDataCached) return;

    const selectedProvName = provInput.value.trim().toUpperCase();

    // Clear city input value if this update is triggered by user typing or selecting a province,
    // and the current city value doesn't belong to the newly selected province.
    if (e && (e.type === 'input' || e.type === 'change') && kotaInput && kotaInput.value) {
        const currentCity = kotaInput.value.trim().toUpperCase();
        const matchedCityObj = umrDataCached.find(u => u.tipe === 'UMK' && u.nama_daerah.toUpperCase() === currentCity);
        const matchedProvObj = umrDataCached.find(u => u.tipe === 'UMP' && u.nama_daerah.toUpperCase() === selectedProvName);
        
        if (matchedProvObj && matchedCityObj) {
            const provCode = matchedProvObj.kode_daerah.trim();
            if (!matchedCityObj.kode_daerah || !matchedCityObj.kode_daerah.trim().startsWith(provCode)) {
                kotaInput.value = '';
            }
        } else if (selectedProvName !== '') {
            kotaInput.value = '';
        }
    }

    kotaList.innerHTML = '';

    // Find the UMP province record to get its code
    const matchedProv = umrDataCached.find(u => u.tipe === 'UMP' && u.nama_daerah.toUpperCase() === selectedProvName);
    
    let filteredKotas = [];
    if (matchedProv && matchedProv.kode_daerah) {
        // If province is selected, filter UMK cities where code starts with province code
        const provCode = matchedProv.kode_daerah.trim(); // e.g. "ID 12"
        filteredKotas = umrDataCached.filter(u => 
            u.tipe === 'UMK' && 
            u.kode_daerah && 
            u.kode_daerah.trim().startsWith(provCode)
        );
    } else {
        // If no province is selected (or empty), show all UMK cities
        filteredKotas = umrDataCached.filter(u => u.tipe === 'UMK');
    }

    // Populate the datalist
    const kotas = [...new Set(filteredKotas.map(u => u.nama_daerah))];
    kotas.forEach(k => {
        if (k) kotaList.innerHTML += `<option value="${k}"></option>`;
    });
}

async function populateProvinsiKotaLists() {
    try {
        if (!umrDataCached) {
            const r = await fetch('/api/minimum-wages?tipe=all');
            umrDataCached = await r.json();
        }
        
        const provList = document.getElementById('provinsiList');
        if (!provList) return;
        
        provList.innerHTML = '';
        
        // Populate Provinsi (UMP)
        const provs = [...new Set(umrDataCached.filter(u => u.tipe === 'UMP').map(u => u.nama_daerah))];
        provs.forEach(p => {
            if (p) provList.innerHTML += `<option value="${p}"></option>`;
        });
        
        // Populate/Filter Kota (UMK) based on current province input value
        updateKotaListBasedOnProvinsi();
    } catch (e) {
        console.error('Error fetching minimum wages for datalist', e);
    }
}

async function populateLocClients() {
    const clientSelect = document.getElementById('locClientId');
    if (!clientSelect) return;
    
    // Save current selection value
    const currentVal = clientSelect.value;
    
    if (window.locClientSelectInstance) {
        window.locClientSelectInstance.destroy();
    }
    
    clientSelect.innerHTML = '<option value="">-- Select Client --</option>';
    
    try {
        const r = await fetch('/api/clients');
        const clientsData = await r.json();
        clientsData.forEach(c => {
            clientSelect.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
        });
        
        if (window.selectedClientId) {
            clientSelect.value = window.selectedClientId;
            clientSelect.dispatchEvent(new Event('change'));
            clientSelect.parentElement.style.display = 'none';
        } else {
            clientSelect.parentElement.style.display = 'block';
            if (currentVal) clientSelect.value = currentVal;
            
            // Initialize TomSelect for "select search"
            window.locClientSelectInstance = new TomSelect(clientSelect, {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        }
    } catch (e) {
        console.error(e);
    }
}





document.getElementById('locName')?.addEventListener('input', (e) => {
    const val = e.target.value;
    if (!val) {
        document.getElementById('locCode').value = '';
        return;
    }
    const words = val.trim().split(/\s+/);
    const initials = words
        .map(w => w.replace(/[^a-zA-Z0-9]/g, '').charAt(0).toUpperCase())
        .filter(c => c !== '')
        .join('');
    
    let id = document.getElementById('workLocationId').value;
    if (!id) {
        if (nextWorkLocationId) {
            id = nextWorkLocationId;
        } else if (workLocations && workLocations.length > 0) {
            id = Math.max(...workLocations.map(l => parseInt(l.id) || 0)) + 1;
        } else {
            id = 1;
        }
    }
    document.getElementById('locCode').value = initials + id;
});

document.getElementById('locProvinsi')?.addEventListener('input', updateKotaListBasedOnProvinsi);
document.getElementById('locProvinsi')?.addEventListener('change', updateKotaListBasedOnProvinsi);

async function editLokasiKerja(id) {
    await bukaModalLokasiKerja();
    const loc = workLocations.find(l => l.id == id);
    
    if (!loc) return;
    
    document.getElementById('modalLokasiKerjaTitle').innerText = 'Edit Work Location';
    document.getElementById('workLocationId').value = loc.id;
    document.getElementById('locName').value = loc.lokasi_kerja;
    document.getElementById('locCode').value = loc.location_code || '';
    document.getElementById('locProvinsi').value = loc.provinsi || '';
    
    // Filter city datalist options based on province before setting city value
    updateKotaListBasedOnProvinsi();
    
    document.getElementById('locKotaKabupaten').value = loc.kota_kabupaten || '';
    
    const clientSelect = document.getElementById('locClientId');
    if (clientSelect) {
        clientSelect.value = loc.client_id;
        clientSelect.disabled = true;
        
        if (window.locClientSelectInstance) {
            window.locClientSelectInstance.setValue(loc.client_id);
            window.locClientSelectInstance.disable();
        }
        
        if (window.selectedClientId) {
            clientSelect.parentElement.style.display = 'none';
        } else {
            clientSelect.parentElement.style.display = 'block';
        }
    }
    
}

document.getElementById('formLokasiKerja')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('workLocationId').value;
    const data = {
        client_id: document.getElementById('locClientId').value,
        lokasi_kerja: document.getElementById('locName').value,
        location_code: document.getElementById('locCode').value,
        division_id: null,
        department_id: null,
        position_id: null,
        provinsi: document.getElementById('locProvinsi').value,
        kota_kabupaten: document.getElementById('locKotaKabupaten').value
    };
    
    try {
        const r = await fetch(id ? `/api/work-locations/${id}` : '/api/work-locations', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        if (r.ok) {
            tutupModalLokasiKerja();
            if (window.selectedClientId) {
                loadWorkLocationsForClient();
            } else {
                loadGlobalWorkLocations();
            }
            showToast('Work location data saved successfully');
        } else {
            const err = await r.json().catch(() => ({}));
            let msg = 'Failed to save work location data';
            if (err.messages) msg = Object.values(err.messages).join(', ');
            else if (err.message) msg = err.message;
            showToast(msg, 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('A connection error occurred', 'error');
    }
});

async function hapusLokasiKerja(id) {
    if (!await showConfirm('Are you sure you want to delete this work location?')) return;
    try {
        const r = await fetch(`/api/work-locations/${id}`, { method: 'DELETE' });
        if (r.ok) {
            if (window.selectedClientId) {
                loadWorkLocationsForClient();
            } else {
                loadGlobalWorkLocations();
            }
            showToast('Work location deleted successfully');
        } else {
            showToast('Failed to delete work location', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Terjadi kesalahan koneksi', 'error');
    }
}

window.loadGlobalWorkLocations = loadGlobalWorkLocations;
window.switchClientKaryawanSubTab = switchClientKaryawanSubTab;
window.bukaModalLokasiKerja = bukaModalLokasiKerja;
window.tutupModalLokasiKerja = tutupModalLokasiKerja;
window.editLokasiKerja = editLokasiKerja;
window.hapusLokasiKerja = hapusLokasiKerja;
window.cariLokasiKerjaGlobalAktif = cariLokasiKerjaGlobalAktif;

