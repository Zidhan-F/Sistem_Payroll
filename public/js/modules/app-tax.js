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
                        <div class="scheme-card-desc">Metode: <b>${scheme.metode}</b> | PTKP: <b>${scheme.ptkp_status}</b></div>
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
        }
    } else {
        document.getElementById('formPajak').reset();
        document.getElementById('pajakId').value = '';
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
                deskripsi: document.getElementById('pajakDeskripsi').value
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
                showToast(id ? 'Skema pajak berhasil diupdate!' : 'Skema pajak berhasil ditambahkan!', 'success');
            } else {
                showToast('Gagal menyimpan skema pajak!', 'error');
            }
        });

async function hapusPajak(id) {
    if (!await showConfirm('Apakah Anda yakin ingin menghapus skema pajak ini?')) return;
    try {
        const res = await fetch(`${API_URL}/tax-schemes/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderTaxSchemes();
            showToast('Skema pajak berhasil dihapus!', 'success');
        } else {
            showToast('Gagal menghapus skema pajak!', 'error');
        }
    } catch (err) { console.error(err); }
}
