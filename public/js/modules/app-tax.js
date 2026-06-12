// ===== TAX & BPJS MODULE =====
// Extracted from app.js for modular monolith architecture

let taxSchemes = [];
let bpjsSchemes = [];

// ===== 4. TAX & BPJS SCHEMES =====
async function renderTaxSchemes() {
    try {
        const bpjsTableBody = document.getElementById('bpjsSchemesTableBody');
        const pph21TableBody = document.getElementById('pph21SchemesTableBody');
        if (bpjsTableBody) {
            bpjsTableBody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        if (pph21TableBody) {
            pph21TableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        
        const response = await fetch(`${API_URL}/tax-schemes`);
        const allSchemes = await response.json();
        
        // Filter schemes by tipe
        taxSchemes = allSchemes.filter(s => s.tipe === 'pph21');
        bpjsSchemes = allSchemes.filter(s => s.tipe === 'bpjs' && !(s.nama && s.nama.startsWith('Custom BPJS -')));

        // Render using helper functions
        renderBpjsTable(bpjsSchemes);
        renderPph21Table(taxSchemes);
    } catch (err) { console.error(err); }
}

function bukaModalBpjs(mode, id = null) {
    document.getElementById('modalBpjs').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    
    if (mode === 'edit' && id) {
        const scheme = bpjsSchemes.find(s => s.id == id);
        if (scheme) {
            document.getElementById('bpjsId').value = scheme.id;
            document.getElementById('bpjsNama').value = scheme.nama;
            
            document.getElementById('bpjsKesKaryawan').value = scheme.bpjs_kes_karyawan !== undefined && scheme.bpjs_kes_karyawan !== null ? scheme.bpjs_kes_karyawan : "1.00";
            document.getElementById('bpjsKesPerusahaan').value = scheme.bpjs_kes_perusahaan !== undefined && scheme.bpjs_kes_perusahaan !== null ? scheme.bpjs_kes_perusahaan : "4.00";
            document.getElementById('bpjsKesMaxSalary').value = formatRupiah(scheme.bpjs_kes_max_salary !== undefined && scheme.bpjs_kes_max_salary !== null ? parseFloat(scheme.bpjs_kes_max_salary) : 12000000);
            
            document.getElementById('bpjsJhtKaryawan').value = scheme.bpjs_jht_karyawan !== undefined && scheme.bpjs_jht_karyawan !== null ? scheme.bpjs_jht_karyawan : "2.00";
            document.getElementById('bpjsJhtPerusahaan').value = scheme.bpjs_jht_perusahaan !== undefined && scheme.bpjs_jht_perusahaan !== null ? scheme.bpjs_jht_perusahaan : "3.70";
            
            document.getElementById('bpjsJpKaryawan').value = scheme.bpjs_jp_karyawan !== undefined && scheme.bpjs_jp_karyawan !== null ? scheme.bpjs_jp_karyawan : "1.00";
            document.getElementById('bpjsJpPerusahaan').value = scheme.bpjs_jp_perusahaan !== undefined && scheme.bpjs_jp_perusahaan !== null ? scheme.bpjs_jp_perusahaan : "2.00";
            document.getElementById('bpjsJpMaxSalary').value = formatRupiah(scheme.bpjs_jp_max_salary !== undefined && scheme.bpjs_jp_max_salary !== null ? parseFloat(scheme.bpjs_jp_max_salary) : 10024600);
            
            document.getElementById('bpjsJkkPerusahaan').value = scheme.bpjs_jkk_perusahaan !== undefined && scheme.bpjs_jkk_perusahaan !== null ? scheme.bpjs_jkk_perusahaan : "0.24";
            document.getElementById('bpjsJkmPerusahaan').value = scheme.bpjs_jkm_perusahaan !== undefined && scheme.bpjs_jkm_perusahaan !== null ? scheme.bpjs_jkm_perusahaan : "0.30";
            
            document.getElementById('modalBpjsTitle').innerText = 'Edit BPJS Scheme';
        }
    } else {
        document.getElementById('formBpjs').reset();
        document.getElementById('bpjsId').value = '';
        document.getElementById('bpjsKesKaryawan').value = "1.00";
        document.getElementById('bpjsKesPerusahaan').value = "4.00";
        document.getElementById('bpjsKesMaxSalary').value = formatRupiah(12000000);
        document.getElementById('bpjsJhtKaryawan').value = "2.00";
        document.getElementById('bpjsJhtPerusahaan').value = "3.70";
        document.getElementById('bpjsJpKaryawan').value = "1.00";
        document.getElementById('bpjsJpPerusahaan').value = "2.00";
        document.getElementById('bpjsJpMaxSalary').value = formatRupiah(10024600);
        document.getElementById('bpjsJkkPerusahaan').value = "0.24";
        document.getElementById('bpjsJkmPerusahaan').value = "0.30";
        document.getElementById('modalBpjsTitle').innerText = 'Add BPJS Scheme';
    }
}

function tutupModalBpjs() {
    document.getElementById('modalBpjs').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function bukaModalPph21(mode, id = null) {
    document.getElementById('modalPph21').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    
    if (mode === 'edit' && id) {
        const scheme = taxSchemes.find(s => s.id == id);
        if (scheme) {
            document.getElementById('pph21Id').value = scheme.id;
            document.getElementById('pph21Nama').value = scheme.nama;
            document.getElementById('pph21Metode').value = scheme.metode;
            document.getElementById('pph21Ptkp').value = scheme.ptkp_status;
            document.getElementById('pph21Deskripsi').value = scheme.deskripsi;
            
            document.getElementById('modalPph21Title').innerText = 'Edit PPh 21 Scheme';
        }
    } else {
        document.getElementById('formPph21').reset();
        document.getElementById('pph21Id').value = '';
        document.getElementById('modalPph21Title').innerText = 'Add PPh 21 Scheme';
    }
}

function tutupModalPph21() {
    document.getElementById('modalPph21').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

// Form BPJS submit handler
if (document.getElementById('formBpjs')) {
    document.getElementById('formBpjs').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('bpjsId').value;
        const data = {
            nama: document.getElementById('bpjsNama').value,
            tipe: 'bpjs',
            bpjs_kes_karyawan: parseFloat(document.getElementById('bpjsKesKaryawan').value) || 0,
            bpjs_kes_perusahaan: parseFloat(document.getElementById('bpjsKesPerusahaan').value) || 0,
            bpjs_kes_max_salary: parseFormattedNumber(document.getElementById('bpjsKesMaxSalary').value) || 0,
            bpjs_jht_karyawan: parseFloat(document.getElementById('bpjsJhtKaryawan').value) || 0,
            bpjs_jht_perusahaan: parseFloat(document.getElementById('bpjsJhtPerusahaan').value) || 0,
            bpjs_jp_karyawan: parseFloat(document.getElementById('bpjsJpKaryawan').value) || 0,
            bpjs_jp_perusahaan: parseFloat(document.getElementById('bpjsJpPerusahaan').value) || 0,
            bpjs_jp_max_salary: parseFormattedNumber(document.getElementById('bpjsJpMaxSalary').value) || 0,
            bpjs_jkk_perusahaan: parseFloat(document.getElementById('bpjsJkkPerusahaan').value) || 0,
            bpjs_jkm_perusahaan: parseFloat(document.getElementById('bpjsJkmPerusahaan').value) || 0
        };
        const url = id ? `${API_URL}/tax-schemes/${id}` : `${API_URL}/tax-schemes`;
        const res = await fetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (res.ok) {
            tutupSemuaModal();
            renderTaxSchemes();
            showToast(id ? 'BPJS scheme updated successfully!' : 'BPJS scheme added successfully!', 'success');
        } else {
            showToast('Failed to save BPJS scheme!', 'error');
        }
    });
}

// Form PPh 21 submit handler
if (document.getElementById('formPph21')) {
    document.getElementById('formPph21').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('pph21Id').value;
        const data = {
            nama: document.getElementById('pph21Nama').value,
            tipe: 'pph21',
            metode: document.getElementById('pph21Metode').value,
            ptkp_status: document.getElementById('pph21Ptkp').value,
            deskripsi: document.getElementById('pph21Deskripsi').value
        };
        const url = id ? `${API_URL}/tax-schemes/${id}` : `${API_URL}/tax-schemes`;
        const res = await fetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (res.ok) {
            tutupSemuaModal();
            renderTaxSchemes();
            showToast(id ? 'PPh 21 scheme updated successfully!' : 'PPh 21 scheme added successfully!', 'success');
        } else {
            showToast('Failed to save PPh 21 scheme!', 'error');
        }
    });
}

async function hapusPajak(id) {
    if (!await showConfirm('Are you sure you want to delete this scheme?')) return;
    try {
        const res = await fetch(`${API_URL}/tax-schemes/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderTaxSchemes();
            showToast('Scheme deleted successfully!', 'success');
        } else {
            showToast('Failed to delete scheme!', 'error');
        }
    } catch (err) { console.error(err); }
}

function switchTaxTab(tab) {
    // Update tab button styles
    document.querySelectorAll('#viewPajak .ws-tab').forEach(btn => {
        btn.classList.remove('active');
    });

    const activeBtn = document.querySelector(`#viewPajak .ws-tab[data-taxtab="${tab}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }

    // Show/hide tab panels
    document.querySelectorAll('#viewPajak .tax-tab-panel').forEach(panel => {
        panel.style.display = 'none';
    });

    const panelId = 'taxTab' + (tab === 'bpjs' ? 'Bpjs' : 'Pph21');
    const activePanel = document.getElementById(panelId);
    if (activePanel) {
        activePanel.style.display = 'block';
    }
}

// Search/filter for tax schemes
function filterTaxScheme(type) {
    if (type === 'bpjs') {
        const q = (document.getElementById('searchBpjsScheme')?.value || '').toLowerCase();
        const filtered = q ? bpjsSchemes.filter(s => s.nama && s.nama.toLowerCase().includes(q)) : bpjsSchemes;
        renderBpjsTable(filtered);
    } else {
        const q = (document.getElementById('searchPph21Scheme')?.value || '').toLowerCase();
        const filtered = q ? taxSchemes.filter(s => s.nama && s.nama.toLowerCase().includes(q)) : taxSchemes;
        renderPph21Table(filtered);
    }
}

// Helper: render BPJS table body
function renderBpjsTable(schemes) {
    const bpjsTableBody = document.getElementById('bpjsSchemesTableBody');
    if (!bpjsTableBody) return;

    if (!schemes || schemes.length === 0) {
        bpjsTableBody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; color: var(--text-muted); font-style: italic; padding: 30px;">
                    <i class="fas fa-inbox" style="font-size: 36px; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                    No BPJS schemes found. Click "Add BPJS Scheme" to create one.
                </td>
            </tr>
        `;
        return;
    }

    bpjsTableBody.innerHTML = schemes.map(scheme => `
        <tr style="border-bottom: 1px solid #f1f5f9;">
            <td style="padding: 14px; font-weight: 600; color: var(--secondary-color);">
                <i class="fas fa-shield-alt" style="color: var(--primary-color); margin-right: 6px;"></i> ${scheme.nama}
            </td>
            <td style="padding: 14px;">
                <div style="margin-bottom: 2px;">Karyawan: <b>${scheme.bpjs_kes_karyawan !== undefined && scheme.bpjs_kes_karyawan !== null ? parseFloat(scheme.bpjs_kes_karyawan) : 1}%</b></div>
                <div style="margin-bottom: 2px;">Perusahaan: <b>${scheme.bpjs_kes_perusahaan !== undefined && scheme.bpjs_kes_perusahaan !== null ? parseFloat(scheme.bpjs_kes_perusahaan) : 4}%</b></div>
                <div style="font-size: 11px; color: #64748b;">Max: IDR ${formatRupiah(parseFloat(scheme.bpjs_kes_max_salary || 12000000))}</div>
            </td>
            <td style="padding: 14px;">
                <div style="margin-bottom: 2px;">Karyawan: <b>${scheme.bpjs_jht_karyawan !== undefined && scheme.bpjs_jht_karyawan !== null ? parseFloat(scheme.bpjs_jht_karyawan) : 2}%</b></div>
                <div>Perusahaan: <b>${scheme.bpjs_jht_perusahaan !== undefined && scheme.bpjs_jht_perusahaan !== null ? parseFloat(scheme.bpjs_jht_perusahaan) : 3.7}%</b></div>
            </td>
            <td style="padding: 14px;">
                <div style="margin-bottom: 2px;">Karyawan: <b>${scheme.bpjs_jp_karyawan !== undefined && scheme.bpjs_jp_karyawan !== null ? parseFloat(scheme.bpjs_jp_karyawan) : 1}%</b></div>
                <div style="margin-bottom: 2px;">Perusahaan: <b>${scheme.bpjs_jp_perusahaan !== undefined && scheme.bpjs_jp_perusahaan !== null ? parseFloat(scheme.bpjs_jp_perusahaan) : 2}%</b></div>
                <div style="font-size: 11px; color: #64748b;">Max: IDR ${formatRupiah(parseFloat(scheme.bpjs_jp_max_salary || 10024600))}</div>
            </td>
            <td style="padding: 14px;">
                <div style="margin-bottom: 2px;">JKK (Perush.): <b>${scheme.bpjs_jkk_perusahaan !== undefined && scheme.bpjs_jkk_perusahaan !== null ? parseFloat(scheme.bpjs_jkk_perusahaan) : 0.24}%</b></div>
                <div>JKM (Perush.): <b>${scheme.bpjs_jkm_perusahaan !== undefined && scheme.bpjs_jkm_perusahaan !== null ? parseFloat(scheme.bpjs_jkm_perusahaan) : 0.3}%</b></div>
            </td>
            <td style="text-align: center; padding: 14px;">
                <div style="display: flex; gap: 8px; justify-content: center;">
                    <button class="btn-icon btn-edit" onclick="bukaModalBpjs('edit', ${scheme.id})" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn-icon btn-delete" onclick="hapusPajak(${scheme.id})" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Helper: render PPh 21 table body
function renderPph21Table(schemes) {
    const pph21TableBody = document.getElementById('pph21SchemesTableBody');
    if (!pph21TableBody) return;

    if (!schemes || schemes.length === 0) {
        pph21TableBody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; color: var(--text-muted); font-style: italic; padding: 30px;">
                    <i class="fas fa-inbox" style="font-size: 36px; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                    No PPh 21 schemes found. Click "Add PPh 21 Scheme" to create one.
                </td>
            </tr>
        `;
        return;
    }

    pph21TableBody.innerHTML = schemes.map(scheme => `
        <tr style="border-bottom: 1px solid #f1f5f9;">
            <td style="padding: 14px; font-weight: 600; color: var(--secondary-color);">
                <i class="fas fa-calculator" style="color: var(--danger); margin-right: 6px;"></i> ${scheme.nama}
            </td>
            <td style="padding: 14px;">
                <span style="background-color: rgba(243, 156, 18, 0.15); color: var(--primary-dark); padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 12px; border: 1px solid rgba(243, 156, 18, 0.25);">
                    ${scheme.metode}
                </span>
            </td>
            <td style="padding: 14px; font-weight: 600;">
                ${scheme.ptkp_status}
            </td>
            <td style="padding: 14px; color: #64748b; font-style: italic;">
                ${scheme.deskripsi || '-'}
            </td>
            <td style="text-align: center; padding: 14px;">
                <div style="display: flex; gap: 8px; justify-content: center;">
                    <button class="btn-icon btn-edit" onclick="bukaModalPph21('edit', ${scheme.id})" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn-icon btn-delete" onclick="hapusPajak(${scheme.id})" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>
    `).join('');
}

