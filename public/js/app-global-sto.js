// ===== GLOBAL STO MASTER JS =====
const GLOBAL_STO_API = window.API || '/api';

let globalDivisionsList = [];
let globalDepartmentsList = [];
let globalPositionsList = [];

// Tab switcher for STO Global
function switchStoTab(tab) {
    document.querySelectorAll('#viewSto .ws-tab').forEach(btn => {
        btn.classList.remove('active');
        btn.style.borderBottomColor = 'transparent';
        btn.style.color = '#64748b';
    });
    
    const activeBtn = document.querySelector(`#viewSto .ws-tab[data-stotab="${tab}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
        activeBtn.style.borderBottomColor = 'var(--primary-color)';
        activeBtn.style.color = 'var(--primary-color)';
    }

    document.querySelectorAll('#viewSto .sto-tab-panel').forEach(panel => {
        panel.style.display = 'none';
    });
    
    const activePanel = document.getElementById('stoTab' + tab.charAt(0).toUpperCase() + tab.slice(1));
    if (activePanel) {
        activePanel.style.display = 'block';
    }

    loadGlobalStoData(tab);
}

// Fetch and Render Global STO Data
async function loadGlobalStoData(type) {
    const tableBody = document.getElementById(`tableGlobal${type.charAt(0).toUpperCase() + type.slice(1)}Body`);
    if (!tableBody) return;

    tableBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 20px; color: #94a3b8;">Loading data...</td></tr>';

    try {
        const response = await fetch(`${GLOBAL_STO_API}/global-${type === 'divisi' ? 'divisions' : type === 'departemen' ? 'departments' : 'positions'}`);
        const data = await response.json();

        if (type === 'divisi') globalDivisionsList = data;
        else if (type === 'departemen') globalDepartmentsList = data;
        else if (type === 'posisi') globalPositionsList = data;

        renderGlobalStoTable(type, data);
    } catch (err) {
        console.error(`Error loading global ${type}:`, err);
        tableBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 20px; color: #ef4444;">Failed to load data.</td></tr>';
    }
}

function renderGlobalStoTable(type, list) {
    const tableBody = document.getElementById(`tableGlobal${type.charAt(0).toUpperCase() + type.slice(1)}Body`);
    if (!tableBody) return;

    if (!list || list.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: 30px; color: #94a3b8;">No global ${type} data available. Click Add to create one.</td></tr>`;
        return;
    }

    tableBody.innerHTML = list.map((item, index) => {
        return `
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="text-align: center; padding: 14px; color: #475569; font-weight: 500;">${index + 1}</td>
                <td style="padding: 14px; color: #1e293b; font-weight: 600;">${item.nama}</td>
                <td style="text-align: center; padding: 14px;">
                    <div style="display: flex; gap: 8px; justify-content: center;">
                        <button class="btn-icon btn-edit" onclick="bukaModalGlobalSto('${type}', 'edit', ${item.id}, '${item.nama.replace(/'/g, "\\'")}')" style="padding: 6px; font-size: 12px;"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon btn-delete" onclick="hapusGlobalSto('${type}', ${item.id})" style="padding: 6px; font-size: 12px;"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Local Search Filtering
function filterGlobalSto(type) {
    const q = document.getElementById(`searchGlobal${type.charAt(0).toUpperCase() + type.slice(1)}`).value.toLowerCase();
    let list = [];
    if (type === 'divisi') list = globalDivisionsList;
    else if (type === 'departemen') list = globalDepartmentsList;
    else if (type === 'posisi') list = globalPositionsList;

    if (!q) {
        renderGlobalStoTable(type, list);
        return;
    }

    const filtered = list.filter(item => item.nama && item.nama.toLowerCase().includes(q));
    renderGlobalStoTable(type, filtered);
}

// Modal handling for STO Global CRUD
function bukaModalGlobalSto(type, mode, id = null, name = '') {
    const modal = document.getElementById('modalGlobalSto');
    const overlay = document.getElementById('overlay');
    if (!modal || !overlay) return;

    modal.style.display = 'block';
    overlay.style.display = 'block';

    document.getElementById('globalStoType').value = type;
    document.getElementById('globalStoId').value = id || '';
    document.getElementById('globalStoName').value = name || '';

    const labelType = type === 'divisi' ? 'Division' : type === 'departemen' ? 'Department' : 'Position';
    document.getElementById('globalStoModalTitle').innerText = (mode === 'edit' ? 'Edit Global ' : 'Add Global ') + labelType;
    document.getElementById('globalStoLabelName').innerText = labelType + ' Name';
    document.getElementById('globalStoName').placeholder = 'Enter ' + labelType.toLowerCase() + ' name...';
}

function tutupModalGlobalSto() {
    const modal = document.getElementById('modalGlobalSto');
    const overlay = document.getElementById('overlay');
    if (modal) modal.style.display = 'none';
    if (overlay) overlay.style.display = 'none';
}

// Form Submission for Global STO
async function handleGlobalStoSubmit(event) {
    event.preventDefault();
    const type = document.getElementById('globalStoType').value;
    const id = document.getElementById('globalStoId').value;
    const name = document.getElementById('globalStoName').value.trim();

    if (!name) return;

    const endpoint = `/global-${type === 'divisi' ? 'divisions' : type === 'departemen' ? 'departments' : 'positions'}`;
    const url = id ? `${GLOBAL_STO_API}${endpoint}/${id}` : `${GLOBAL_STO_API}${endpoint}`;
    const method = id ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nama: name })
        });

        if (response.ok) {
            tutupModalGlobalSto();
            showToast('Saved successfully!', 'success');
            loadGlobalStoData(type);
        } else {
            const err = await response.json();
            showToast('Failed: ' + (err.error || 'Server error'), 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Connection error', 'error');
    }
}

// Delete Action
async function hapusGlobalSto(type, id) {
    if (!await showConfirm(`Are you sure you want to delete this global ${type}?`)) return;

    const endpoint = `/global-${type === 'divisi' ? 'divisions' : type === 'departemen' ? 'departments' : 'positions'}`;
    const url = `${GLOBAL_STO_API}${endpoint}/${id}`;

    try {
        const response = await fetch(url, { method: 'DELETE' });
        if (response.ok) {
            showToast('Deleted successfully', 'success');
            loadGlobalStoData(type);
        } else {
            showToast('Failed to delete', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Connection error', 'error');
    }
}

// Integration: Populate client workspace dropdown with TomSelect
async function populateOrgNameSelect(type, selectedValue = '') {
    const select = document.getElementById('orgNameSelect');
    if (!select) return;

    select.innerHTML = '<option value="">-- Select Name --</option>';

    try {
        const endpoint = `/global-${type === 'divisi' ? 'divisions' : type === 'department' ? 'departments' : 'positions'}`;
        const response = await fetch(`${GLOBAL_STO_API}${endpoint}`);
        const data = await response.json();

        data.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.nama;
            opt.innerText = item.nama;
            if (selectedValue && item.nama === selectedValue) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });

        // Initialize or rebuild TomSelect
        if (window.orgNameSelectInstance) {
            window.orgNameSelectInstance.destroy();
        }

        window.orgNameSelectInstance = new TomSelect(select, {
            create: false,
            placeholder: `Search and select ${type === 'divisi' ? 'division' : type === 'department' ? 'department' : 'position'}...`,
            sortField: { field: "text", direction: "asc" }
        });
        
        // If there's an active select value, set it explicitly in TomSelect
        if (selectedValue) {
            window.orgNameSelectInstance.setValue(selectedValue);
        }
    } catch (err) {
        console.error(`Error loading global list for dropdown:`, err);
    }
}

// Expose functions globally
window.switchStoTab = switchStoTab;
window.bukaModalGlobalSto = bukaModalGlobalSto;
window.tutupModalGlobalSto = tutupModalGlobalSto;
window.handleGlobalStoSubmit = handleGlobalStoSubmit;
window.hapusGlobalSto = hapusGlobalSto;
window.filterGlobalSto = filterGlobalSto;
window.populateOrgNameSelect = populateOrgNameSelect;
