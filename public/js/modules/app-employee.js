// ===== EMPLOYEE MODULE =====
// Extracted from app.js for modular monolith architecture

function rowHtmlForEmployee(emp) {
    return `
        <tr>
            <td style="font-weight: 600;">${emp.nama_klien || '-'}</td>
            <td style="font-weight: 600;">${emp.nik || '-'}</td>
            <td style="font-weight: 600;">
                <i class="fas fa-user" style="margin-right: 8px; opacity: 0.6;"></i>${emp.nama}
            </td>
            <td>
                <div style="font-size: 13px;">${emp.tempat_lahir || '-'}</div>
                <div style="font-size: 11px;">${emp.tanggal_lahir || '-'}</div>
            </td>
            <td>${emp.npwp || '-'}</td>
            <td>${emp.nama_divisi || '-'}</td>
            <td>${emp.nama_dept || '-'}</td>
            <td>${emp.nama_posisi || '-'}</td>
            <td>
                <div style="font-weight:600;">
                    <i class="fas fa-map-marker-alt" style="margin-right: 4px; opacity: 0.7;"></i>${emp.nama_lokasi || '-'}
                </div>
            </td>
            <td>
                <div style="font-weight: 600; color: #16a34a;">
                    Rp ${emp.umr_nominal ? parseFloat(emp.umr_nominal).toLocaleString('id-ID') : '0'}
                </div>
                <div style="font-size: 11px; color: #64748b;">
                    ${emp.umr_tipe || '-'}
                </div>
            </td>
            <td>
                <div style="color: #ef4444; font-weight: 600; font-size: 12px;">Rp ${emp.denda_absen ? parseFloat(emp.denda_absen).toLocaleString('id-ID') : '0'}</div>
            </td>
            <td>
                <div style="font-weight: 600;">${emp.tipe_perjanjian || '-'}</div>
                <div style="font-size: 11px;">${emp.start_contract || '-'} to ${emp.end_contract || '-'}</div>
            </td>

            <td>
                <div style="display: flex; gap: 8px;">
                    <button class="btn-icon btn-edit" onclick="bukaModalKaryawanGlobalEdit(${emp.id}, ${emp.client_id})" title="Edit Employee" style="color: var(--primary-color); background: rgba(41, 128, 185, 0.1); width: 30px; height: 30px; border-radius: 6px;"><i class="fas fa-user-edit"></i></button>
                    <button class="btn-icon btn-delete" onclick="hapusKaryawanGlobal(${emp.id})" title="Delete Employee" style="color: var(--danger); background: rgba(231, 76, 60, 0.1); width: 30px; height: 30px; border-radius: 6px;"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>
    `;
}

async function renderAllEmployees() {
    try {
        const url = window.selectedClientId ? `${API_URL}/employees?client_id=${window.selectedClientId}` : `${API_URL}/employees`;
        const res = await fetch(url);
        const employees = await res.json();
        window.employees = employees; // Expose globally for app-org.js
        const tbody = document.getElementById('tabelKaryawanBody');
        if (!tbody) return;
        tbody.innerHTML = employees.map(emp => rowHtmlForEmployee(emp)).join('');
    } catch (err) { console.error(err); }
}

function filterKaryawan() {
    const q = document.getElementById('searchKaryawan').value.toLowerCase();
    const tbody = document.getElementById('tabelKaryawanBody');
    if (!tbody || !window.employees) return;

    const filtered = window.employees.filter(emp => {
        return (emp.nama && emp.nama.toLowerCase().includes(q)) ||
            (emp.nik && emp.nik.toLowerCase().includes(q)) ||
            (emp.nama_posisi && emp.nama_posisi.toLowerCase().includes(q)) ||
            (emp.nama_dept && emp.nama_dept.toLowerCase().includes(q)) ||
            (emp.nama_divisi && emp.nama_divisi.toLowerCase().includes(q)) ||
            (emp.email && emp.email.toLowerCase().includes(q)) ||
            (emp.nama_lokasi && emp.nama_lokasi.toLowerCase().includes(q));
    });

    tbody.innerHTML = filtered.map(emp => rowHtmlForEmployee(emp)).join('');
}
window.filterKaryawan = filterKaryawan;

// ===== 3. GLOBAL MANAJEMEN KARYAWAN =====
let allEmployeesGlobal = [];

async function renderManajemenKaryawan(list = null) {
    try {
        if (!list) {
            const res = await fetch(`${API_URL}/employees`);
            allEmployeesGlobal = await res.json();
            window.allEmployeesGlobal = allEmployeesGlobal;
            list = allEmployeesGlobal;
        }
        const tbody = document.getElementById('tabelKaryawanGlobalBody');
        if (!tbody) return;

        tbody.innerHTML = list.map(emp => rowHtmlForEmployee(emp)).join('');
    } catch (err) {
        console.error(err);
    }
}

function cariKaryawanGlobalAktif() {
    const q = document.getElementById('cariKaryawanGlobal').value.toLowerCase();
    if (!q) {
        renderManajemenKaryawan(allEmployeesGlobal);
        return;
    }
    const filtered = allEmployeesGlobal.filter(emp => {
        return (emp.nama && emp.nama.toLowerCase().includes(q)) ||
            (emp.nik && emp.nik.toLowerCase().includes(q)) ||
            (emp.nama_klien && emp.nama_klien.toLowerCase().includes(q)) ||
            (emp.nama_posisi && emp.nama_posisi.toLowerCase().includes(q)) ||
            (emp.nama_dept && emp.nama_dept.toLowerCase().includes(q)) ||
            (emp.nama_divisi && emp.nama_divisi.toLowerCase().includes(q)) ||
            (emp.nama_lokasi && emp.nama_lokasi.toLowerCase().includes(q));
    });
    renderManajemenKaryawan(filtered);
}

function bukaModalKaryawanGlobal() {
    window.selectedClientId = null;
    if (typeof bukaModalKaryawan === 'function') {
        bukaModalKaryawan('tambah');
    }
}

async function bukaModalKaryawanGlobalEdit(id, clientId) {
    window.selectedClientId = clientId;
    if (typeof bukaModalKaryawan === 'function') {
        await bukaModalKaryawan('edit', id);
    }
}

async function hapusKaryawanGlobal(id) {
    if (!await showConfirm('Are you sure you want to delete this employee?')) return;
    try {
        const res = await fetch(`${API_URL}/employees/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderManajemenKaryawan();
            showToast('Employee deleted successfully', 'success');
        }
    } catch (err) {
        console.error(err);
    }
}

window.renderManajemenKaryawan = renderManajemenKaryawan;
window.renderAllEmployees = renderAllEmployees;
window.filterKaryawan = filterKaryawan;
window.cariKaryawanGlobalAktif = cariKaryawanGlobalAktif;
window.bukaModalKaryawanGlobal = bukaModalKaryawanGlobal;
window.bukaModalKaryawanGlobalEdit = bukaModalKaryawanGlobalEdit;
window.hapusKaryawanGlobal = hapusKaryawanGlobal;

function toggleSubmenu(event, id) {
    if (event) event.stopPropagation();
    const submenu = document.getElementById(id);
    if (!submenu) return;

    const isVisible = submenu.style.display === 'block';

    // Hide all other submenus first (if there are any)
    document.querySelectorAll('.sidebar-submenu').forEach(sub => {
        if (sub.id !== id) {
            sub.style.display = 'none';
            const arr = sub.parentElement.querySelector('.submenu-arrow');
            if (arr) arr.style.transform = 'rotate(0deg)';
        }
    });

    submenu.style.display = isVisible ? 'none' : 'block';

    const arrow = event.currentTarget.querySelector('.submenu-arrow');
    if (arrow) {
        arrow.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
    }
}

function switchKaryawanSubMenu(action, event) {
    if (event) event.stopPropagation();

    document.querySelectorAll('.sidebar-submenu li').forEach(l => l.classList.remove('active'));

    if (action === 'lokasi_kerja') {
        switchView('globalLokasiKerja');
        if (typeof loadGlobalWorkLocations === 'function') {
            loadGlobalWorkLocations();
        }
        const subItem = document.getElementById('submenuLokasiKerja');
        if (subItem) subItem.classList.add('active');
    } else if (action === 'tambah_karyawan') {
        switchView('manajemenKaryawan');
        const subItem = document.getElementById('submenuTambahKaryawan');
        if (subItem) subItem.classList.add('active');
    }
}

window.toggleSubmenu = toggleSubmenu;
window.switchKaryawanSubMenu = switchKaryawanSubMenu;
window.togglePayrollSubmenu = togglePayrollSubmenu;
window.switchPayrollSub = switchPayrollSub;
