// ===== TAX MODULE =====
// Extracted from app.js for modular monolith architecture

// ===== 4. TAX SCHEMES (PPh 21) =====
async function renderTaxSchemes() {
    try {
        const response = await fetch(`${API_URL}/tax-schemes`);
        taxSchemes = await response.json();
        const container = document.getElementById('taxSchemesContainer');
        if (!container) return;
        container.innerHTML = taxSchemes.map(scheme => `
            <div class="scheme-card" style="border-left: 4px solid var(--danger);">
                <div class="scheme-card-header">
                    <div class="scheme-card-info">
                        <h4><i class="fas fa-percent" style="color: var(--danger);"></i> ${scheme.nama}</h4>
                        <div class="scheme-card-desc">Method: <b>${scheme.metode}</b> | PTKP: <b>${scheme.ptkp_status}</b></div>
                        <div class="scheme-card-desc" style="margin-top: 5px; font-size: 11px; color: #64748b;">
                            <span style="margin-right: 10px;"><i class="fas fa-hand-holding-medical"></i> Kes: <b>${scheme.bpjs_kes_karyawan || 1}% / ${scheme.bpjs_kes_perusahaan || 4}%</b></span>
                            <span style="margin-right: 10px;"><i class="fas fa-shield-alt"></i> JHT: <b>${scheme.bpjs_jht_karyawan || 2}% / ${scheme.bpjs_jht_perusahaan || 3.7}%</b></span>
                            <span style="margin-right: 10px;"><i class="fas fa-history"></i> JP: <b>${scheme.bpjs_jp_karyawan || 1}% / ${scheme.bpjs_jp_perusahaan || 2}%</b></span>
                        </div>
                    </div>
                    <div class="scheme-card-actions">
                        <button class="btn-icon btn-edit" onclick="bukaModalPajak('edit', ${scheme.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon btn-delete" onclick="hapusPajak(${scheme.id})"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (err) { console.error(err); }
}

function bukaModalPajak(mode, id = null) {
    const modal = document.getElementById('modalPajak');
    document.getElementById('modalPajak').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    if (mode === 'edit' && id) {
        const scheme = taxSchemes.find(s => s.id == id);
        if (scheme) {
            document.getElementById('pajakId').value = scheme.id;
            document.getElementById('pajakNama').value = scheme.nama;
            document.getElementById('pajakMetode').value = scheme.metode;
            document.getElementById('pajakPtkp').value = scheme.ptkp_status;
            document.getElementById('pajakDeskripsi').value = scheme.deskripsi;
            
            document.getElementById('pajakBpjsKesKaryawan').value = scheme.bpjs_kes_karyawan !== undefined && scheme.bpjs_kes_karyawan !== null ? scheme.bpjs_kes_karyawan : "1.00";
            document.getElementById('pajakBpjsKesPerusahaan').value = scheme.bpjs_kes_perusahaan !== undefined && scheme.bpjs_kes_perusahaan !== null ? scheme.bpjs_kes_perusahaan : "4.00";
            document.getElementById('pajakBpjsKesMaxSalary').value = formatRupiah(scheme.bpjs_kes_max_salary !== undefined && scheme.bpjs_kes_max_salary !== null ? parseFloat(scheme.bpjs_kes_max_salary) : 12000000);
            
            document.getElementById('pajakBpjsJhtKaryawan').value = scheme.bpjs_jht_karyawan !== undefined && scheme.bpjs_jht_karyawan !== null ? scheme.bpjs_jht_karyawan : "2.00";
            document.getElementById('pajakBpjsJhtPerusahaan').value = scheme.bpjs_jht_perusahaan !== undefined && scheme.bpjs_jht_perusahaan !== null ? scheme.bpjs_jht_perusahaan : "3.70";
            
            document.getElementById('pajakBpjsJpKaryawan').value = scheme.bpjs_jp_karyawan !== undefined && scheme.bpjs_jp_karyawan !== null ? scheme.bpjs_jp_karyawan : "1.00";
            document.getElementById('pajakBpjsJpPerusahaan').value = scheme.bpjs_jp_perusahaan !== undefined && scheme.bpjs_jp_perusahaan !== null ? scheme.bpjs_jp_perusahaan : "2.00";
            document.getElementById('pajakBpjsJpMaxSalary').value = formatRupiah(scheme.bpjs_jp_max_salary !== undefined && scheme.bpjs_jp_max_salary !== null ? parseFloat(scheme.bpjs_jp_max_salary) : 10024600);
            
            document.getElementById('pajakBpjsJkkPerusahaan').value = scheme.bpjs_jkk_perusahaan !== undefined && scheme.bpjs_jkk_perusahaan !== null ? scheme.bpjs_jkk_perusahaan : "0.24";
            document.getElementById('pajakBpjsJkmPerusahaan').value = scheme.bpjs_jkm_perusahaan !== undefined && scheme.bpjs_jkm_perusahaan !== null ? scheme.bpjs_jkm_perusahaan : "0.30";
        }
    } else {
        document.getElementById('formPajak').reset();
        document.getElementById('pajakId').value = '';
        document.getElementById('pajakBpjsKesKaryawan').value = "1.00";
        document.getElementById('pajakBpjsKesPerusahaan').value = "4.00";
        document.getElementById('pajakBpjsKesMaxSalary').value = formatRupiah(12000000);
        document.getElementById('pajakBpjsJhtKaryawan').value = "2.00";
        document.getElementById('pajakBpjsJhtPerusahaan').value = "3.70";
        document.getElementById('pajakBpjsJpKaryawan').value = "1.00";
        document.getElementById('pajakBpjsJpPerusahaan').value = "2.00";
        document.getElementById('pajakBpjsJpMaxSalary').value = formatRupiah(10024600);
        document.getElementById('pajakBpjsJkkPerusahaan').value = "0.24";
        document.getElementById('pajakBpjsJkmPerusahaan').value = "0.30";
    }
}

// Form Skema Pajak submit handler
if (document.getElementById('formPajak')) {
    document.getElementById('formPajak').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('pajakId').value;
        const data = {
            nama: document.getElementById('pajakNama').value,
            metode: document.getElementById('pajakMetode').value,
            ptkp_status: document.getElementById('pajakPtkp').value,
            deskripsi: document.getElementById('pajakDeskripsi').value,
            bpjs_kes_karyawan: parseFloat(document.getElementById('pajakBpjsKesKaryawan').value) || 0,
            bpjs_kes_perusahaan: parseFloat(document.getElementById('pajakBpjsKesPerusahaan').value) || 0,
            bpjs_kes_max_salary: parseFormattedNumber(document.getElementById('pajakBpjsKesMaxSalary').value) || 0,
            bpjs_jht_karyawan: parseFloat(document.getElementById('pajakBpjsJhtKaryawan').value) || 0,
            bpjs_jht_perusahaan: parseFloat(document.getElementById('pajakBpjsJhtPerusahaan').value) || 0,
            bpjs_jp_karyawan: parseFloat(document.getElementById('pajakBpjsJpKaryawan').value) || 0,
            bpjs_jp_perusahaan: parseFloat(document.getElementById('pajakBpjsJpPerusahaan').value) || 0,
            bpjs_jp_max_salary: parseFormattedNumber(document.getElementById('pajakBpjsJpMaxSalary').value) || 0,
            bpjs_jkk_perusahaan: parseFloat(document.getElementById('pajakBpjsJkkPerusahaan').value) || 0,
            bpjs_jkm_perusahaan: parseFloat(document.getElementById('pajakBpjsJkmPerusahaan').value) || 0
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
            showToast(id ? 'Tax scheme updated successfully!' : 'Tax scheme added successfully!', 'success');
        } else {
            showToast('Failed to save tax scheme!', 'error');
        }
    });
}


async function hapusPajak(id) {
    if (!await showConfirm('Are you sure you want to delete this tax scheme?')) return;
    try {
        const res = await fetch(`${API_URL}/tax-schemes/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderTaxSchemes();
            showToast('Tax scheme deleted successfully!', 'success');
        } else {
            showToast('Failed to delete tax scheme!', 'error');
        }
    } catch (err) { console.error(err); }
}
