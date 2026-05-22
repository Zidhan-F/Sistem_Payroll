// ===== COMPENSATION MODULE =====
// Extracted from app.js for modular monolith architecture

    // Form Skema Kompensasi submit handler
    if (document.getElementById('formSkemaKompensasi')) {
        document.getElementById('formSkemaKompensasi').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('skemaKompensasiId').value;
            const data = {
                nama: document.getElementById('skemaKompensasiNama').value,
                deskripsi: document.getElementById('skemaKompensasiDeskripsi').value || '',
                tipe: 'pendapatan',
                sumber_nilai: document.getElementById('skemaKompensasiSumber').value,
                nilai: parseFormattedNumber(document.getElementById('skemaKompensasiNilai').value) || 0,
                periode: document.getElementById('skemaKompensasiPeriode').value,
                is_persentase: parseInt(document.getElementById('skemaKompensasiIsPersentase').value) || 0,
                sifat_kompensasi: document.getElementById('skemaKompensasiSifat').value
            };
            const url = id ? `${API_URL}/compensation-schemes/${id}` : `${API_URL}/compensation-schemes`;
            const res = await fetch(url, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (res.ok) {
                tutupSemuaModal();
                renderMasterKompensasi();
                showToast(id ? 'Skema komponen berhasil diupdate!' : 'Skema komponen berhasil ditambahkan!', 'success');
            } else {
                showToast('Gagal menyimpan skema!', 'error');
            }
        });
    }

    // Form Komponen Kompensasi submit handler
    if (document.getElementById('formKomponenKompensasi')) {
        document.getElementById('formKomponenKompensasi').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('komponenKompensasiId').value;
            const schemeId = document.getElementById('komponenKompensasiSchemeId').value;
            const jenis = document.getElementById('komponenKompensasiJenis').value;
            const sifat = document.getElementById('komponenKompensasiSifat').value;
            let nama = '';
            if (jenis === 'basic_salary') {
                nama = 'Gaji Pokok';
            } else {
                nama = sifat === 'tetap' ? 'Komponen Tetap' : 'Komponen Tidak Tetap';
            }

            const data = {
                scheme_id: parseInt(schemeId),
                nama: nama,
                tipe: 'pendapatan',
                nilai: parseFormattedNumber(document.getElementById('komponenKompensasiNilai').value) || 0,
                is_persentase: parseInt(document.getElementById('komponenKompensasiIsPersentase').value) || 0,
                jenis_komponen: jenis,
                sifat_kompensasi: sifat,
                sumber_nilai: document.getElementById('komponenKompensasiSumber').value,
                periode: document.getElementById('komponenKompensasiPeriode').value
            };
            const url = id ? `${API_URL}/compensation-components/${id}` : `${API_URL}/compensation-components`;
            const res = await fetch(url, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (res.ok) {
                tutupSemuaModal();
                renderMasterKompensasi();
                showToast(id ? 'Komponen berhasil diupdate!' : 'Komponen berhasil ditambahkan!', 'success');
            } else {
                showToast('Gagal menyimpan komponen!', 'error');
            }
        });
    }

// ===== MASTER SKEMA KOMPENSASI =====
async function renderMasterKompensasi() {
    try {
        const res = await fetch(`${API_URL}/compensation-schemes`);
        window.compensationSchemes = await res.json();
        const container = document.getElementById('compensationSchemesContainer');
        if (!container) return;
        
        if (window.compensationSchemes.length === 0) {
            container.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px;">
                        <div class="empty-schemes" style="border: 2px dashed #cbd5e1; border-radius: 12px; background: white; padding: 30px; margin: 0 auto; max-width: 500px;">
                            <i class="fas fa-coins" style="font-size: 40px; color: #94a3b8; margin-bottom: 12px; display: block;"></i>
                            <p style="color: #64748b; font-weight: 600; margin-bottom: 15px;">Belum ada skema kompensasi global.</p>
                            <button class="btn-add" onclick="bukaModalSkemaKompensasi('tambah')" style="margin: 0 auto; background: var(--primary-color);">
                                <i class="fas fa-plus"></i> Tambah Skema Pertama
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        container.innerHTML = window.compensationSchemes.map((scheme, index) => {
            const comp = (scheme.components && scheme.components.length > 0) ? scheme.components[0] : null;

            let nilaiDisplay = '-';
            if (comp) {
                if (comp.sumber_nilai === 'ump') {
                    nilaiDisplay = `${comp.nilai}% UMP`;
                } else if (comp.sumber_nilai === 'umk') {
                    nilaiDisplay = `${comp.nilai}% UMK`;
                } else if (comp.sumber_nilai === 'ump_umk') {
                    nilaiDisplay = `${comp.nilai}% UMP/UMK`;
                } else {
                    nilaiDisplay = comp.is_persentase == 1 ? `${comp.nilai}%` : formatRupiah(comp.nilai);
                }
            }

            let periodeDisplay = '-';
            if (comp) {
                if (comp.periode === 'hari_kerja') {
                    periodeDisplay = 'Per Hari Kerja';
                } else if (comp.periode === 'minggu') {
                    periodeDisplay = 'Per Minggu';
                } else if (comp.periode === 'tahun') {
                    periodeDisplay = 'Per Tahun';
                } else {
                    periodeDisplay = 'Per Bulan';
                }
            }

            let sifatDisplay = '';
            if (comp) {
                sifatDisplay = comp.sifat_kompensasi === 'tidak_tetap' ? 'Komponen Tidak Tetap' : 'Komponen Tetap';
            }

            return `
                <tr id="comp-scheme-row-${scheme.id}" style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;">
                    <td style="text-align: center; padding: 16px; color: #475569;">${index + 1}</td>
                    <td style="padding: 16px; font-weight: 600; color: #1e293b;">
                        <div style="display: flex; flex-direction: column;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-coins" style="color: #10b981;"></i>
                            ${scheme.nama}
                        </div>
                            <span style="font-size: 11px; color: #64748b; font-weight: normal; margin-left: 22px; margin-top: 4px;">
                                ${sifatDisplay}
                            </span>
                        </div>
                    </td>
                    <td style="text-align: center; padding: 16px; font-weight: 600; color: #1e293b;">${nilaiDisplay}</td>
                    <td style="text-align: center; padding: 16px; color: #475569;">${periodeDisplay}</td>
                    <td style="text-align: center; padding: 16px;">
                        <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                            <button class="btn-icon btn-edit" onclick="bukaModalSkemaKompensasi('edit', ${scheme.id})" title="Edit Skema"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon btn-delete" onclick="hapusSkemaKompensasi(${scheme.id})" title="Hapus Skema"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    } catch (err) {
        console.error(err);
    }
}

function toggleSchemeCardBody(schemeId) {
    const body = document.getElementById(`comp-scheme-body-${schemeId}`);
    const toggle = document.getElementById(`comp-scheme-toggle-${schemeId}`);
    if (body) {
        body.classList.toggle('expanded');
        if (body.classList.contains('expanded')) {
            body.style.display = 'table-row';
            if (toggle) toggle.classList.add('expanded');
        } else {
            body.style.display = 'none';
            if (toggle) toggle.classList.remove('expanded');
        }
    }
}

function handleSchemeSumberNilaiChange() {
    const sumber = document.getElementById('skemaKompensasiSumber').value;
    const labelNilai = document.getElementById('labelNilaiSkema');
    const inputNilai = document.getElementById('skemaKompensasiNilai');
    const isPersentase = document.getElementById('skemaKompensasiIsPersentase');
    const selectPeriode = document.getElementById('skemaKompensasiPeriode');

    if (sumber === 'ump' || sumber === 'umk') {
        if (isPersentase) isPersentase.value = '1';
        if (labelNilai) labelNilai.innerText = `Nilai Persentase (%) dari ${sumber.toUpperCase()}`;
        if (inputNilai) inputNilai.placeholder = 'Contoh: 100';
        if (selectPeriode) {
            selectPeriode.value = 'bulan';
            selectPeriode.disabled = true;
        }
    } else {
        if (isPersentase) isPersentase.value = '0';
        if (labelNilai) labelNilai.innerText = 'Nominal Custom (Rp)';
        if (inputNilai) inputNilai.placeholder = 'Contoh: 200000';
        if (selectPeriode) {
            selectPeriode.disabled = false;
        }
    }
}

window.handlePayrollSchemeSumberNilaiChange = function() {
    const sumber = document.getElementById('skemaSumber').value;
    const labelNilai = document.getElementById('labelNilaiSkemaPayroll');
    const inputNilai = document.getElementById('skemaNilai');
    const isPersentase = document.getElementById('skemaIsPersentase');
    const selectPeriode = document.getElementById('skemaPeriode');

    if (sumber === 'ump' || sumber === 'umk') {
        if (isPersentase) isPersentase.value = '1';
        if (labelNilai) labelNilai.innerText = `Nilai Persentase (%) dari ${sumber.toUpperCase()}`;
        if (inputNilai) {
            inputNilai.placeholder = 'Contoh: 100';
            if (inputNilai.value === '') inputNilai.value = '100';
        }
        if (selectPeriode) {
            selectPeriode.value = 'bulan';
            selectPeriode.disabled = true;
        }
    } else {
        if (isPersentase) isPersentase.value = '0';
        if (labelNilai) labelNilai.innerText = 'Nominal Custom (Rp)';
        if (inputNilai) {
            inputNilai.placeholder = 'Contoh: 5000000';
            if (inputNilai.value === '100') inputNilai.value = '';
        }
        if (selectPeriode) {
            selectPeriode.disabled = false;
        }
    }
}

function bukaModalSkemaKompensasi(mode, id = null) {
    document.getElementById('modalSkemaKompensasi').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    if (mode === 'edit' && id) {
        const scheme = window.compensationSchemes.find(s => s.id == id);
        if (scheme) {
            const comp = (scheme.components && scheme.components.length > 0) ? scheme.components[0] : null;
            document.getElementById('modalSkemaKompensasiTitle').innerText = 'Edit Skema Komponen';
            document.getElementById('skemaKompensasiId').value = scheme.id;
            document.getElementById('skemaKompensasiNama').value = scheme.nama;
            document.getElementById('skemaKompensasiDeskripsi').value = '';
            
            if (comp) {
                document.getElementById('skemaKompensasiSumber').value = comp.sumber_nilai || 'nominal';
                document.getElementById('skemaKompensasiPeriode').value = comp.periode || 'bulan';
                const elNilai = document.getElementById('skemaKompensasiNilai');
                elNilai.value = comp.nilai || '0';
                formatRupiahInput(elNilai);
                document.getElementById('skemaKompensasiIsPersentase').value = comp.is_persentase || '0';
                document.getElementById('skemaKompensasiSifat').value = comp.sifat_kompensasi || 'tetap';
            } else {
                document.getElementById('skemaKompensasiSumber').value = 'nominal';
                document.getElementById('skemaKompensasiPeriode').value = 'bulan';
                const elNilai = document.getElementById('skemaKompensasiNilai');
                elNilai.value = '0';
                formatRupiahInput(elNilai);
                document.getElementById('skemaKompensasiIsPersentase').value = '0';
                document.getElementById('skemaKompensasiSifat').value = 'tetap';
            }
            handleSchemeSumberNilaiChange();
        }
    } else {
        document.getElementById('modalSkemaKompensasiTitle').innerText = 'Tambah Skema Komponen';
        document.getElementById('formSkemaKompensasi').reset();
        document.getElementById('skemaKompensasiId').value = '';
        document.getElementById('skemaKompensasiNama').value = '';
        document.getElementById('skemaKompensasiDeskripsi').value = '';
        document.getElementById('skemaKompensasiSumber').value = 'nominal';
        document.getElementById('skemaKompensasiPeriode').value = 'bulan';
        document.getElementById('skemaKompensasiNilai').value = '';
        document.getElementById('skemaKompensasiIsPersentase').value = '0';
        document.getElementById('skemaKompensasiSifat').value = 'tetap';
        handleSchemeSumberNilaiChange();
    }
}

function tutupModalSkemaKompensasi() {
    tutupSemuaModal();
}

async function hapusSkemaKompensasi(id) {
    if (!await showConfirm('Apakah Anda yakin ingin menghapus skema komponen ini beserta seluruh komponen di dalamnya?')) return;
    try {
        const res = await fetch(`${API_URL}/compensation-schemes/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderMasterKompensasi();
            showToast('Skema komponen berhasil dihapus!', 'success');
        } else {
            showToast('Gagal menghapus skema!', 'error');
        }
    } catch (err) {
        console.error(err);
    }
}

function handleJenisKomponenChange() {
    const jenis = document.getElementById('komponenKompensasiJenis').value;
    const sifatContainer = document.getElementById('containerSifatKompensasi');
    if (jenis === 'kompensasi') {
        if (sifatContainer) sifatContainer.style.display = 'block';
    } else {
        if (sifatContainer) sifatContainer.style.display = 'none';
    }
}

function handleSumberNilaiChange() {
    const sumber = document.getElementById('komponenKompensasiSumber').value;
    const formatContainer = document.getElementById('containerFormatNilai');
    const labelNilai = document.getElementById('labelNilaiKompensasi');
    const inputNilai = document.getElementById('komponenKompensasiNilai');
    const selectIsPersentase = document.getElementById('komponenKompensasiIsPersentase');

    if (sumber === 'ump') {
        if (formatContainer) formatContainer.style.display = 'none';
        if (selectIsPersentase) selectIsPersentase.value = '1';
        if (labelNilai) labelNilai.innerText = 'Nilai Persentase (%) dari UMP';
        if (inputNilai) inputNilai.placeholder = 'Contoh: 100';
    } else if (sumber === 'umk') {
        if (formatContainer) formatContainer.style.display = 'none';
        if (selectIsPersentase) selectIsPersentase.value = '1';
        if (labelNilai) labelNilai.innerText = 'Nilai Persentase (%) dari UMK';
        if (inputNilai) inputNilai.placeholder = 'Contoh: 100';
    } else if (sumber === 'ump_umk') {
        if (formatContainer) formatContainer.style.display = 'none';
        if (selectIsPersentase) selectIsPersentase.value = '1';
        if (labelNilai) labelNilai.innerText = 'Nilai Persentase (%) dari UMP/UMK';
        if (inputNilai) inputNilai.placeholder = 'Contoh: 100';
    } else {
        if (formatContainer) formatContainer.style.display = 'none';
        if (selectIsPersentase) selectIsPersentase.value = '0';
        if (labelNilai) labelNilai.innerText = 'Nominal Custom (Rp)';
        if (inputNilai) inputNilai.placeholder = 'Contoh: 5000000';
    }
}

function bukaModalKomponenKompensasi(schemeId, mode, id = null) {
    document.getElementById('modalKomponenKompensasi').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('komponenKompensasiSchemeId').value = schemeId;
    if (mode === 'edit' && id) {
        const scheme = window.compensationSchemes.find(s => s.id == schemeId);
        const k = scheme ? (scheme.components || []).find(comp => comp.id == id) : null;
        if (k) {
            document.getElementById('modalKomponenKompensasiTitle').innerText = 'Edit Komponen Kompensasi';
            document.getElementById('komponenKompensasiId').value = k.id;
            const nameEl = document.getElementById('komponenKompensasiNama');
            if (nameEl) nameEl.value = k.nama;
            const tipeEl = document.getElementById('komponenKompensasiTipe');
            if (tipeEl) tipeEl.value = k.tipe;
            const elNilai = document.getElementById('komponenKompensasiNilai');
            elNilai.value = k.nilai;
            handleKomponenKompensasiNilaiInput(elNilai);
            document.getElementById('komponenKompensasiIsPersentase').value = (k.is_persentase == 1 || k.is_persentase === true || k.is_persentase === '1') ? '1' : '0';
            
            // New fields
            document.getElementById('komponenKompensasiJenis').value = k.jenis_komponen || 'kompensasi';
            document.getElementById('komponenKompensasiSifat').value = k.sifat_kompensasi || 'tetap';
            document.getElementById('komponenKompensasiSumber').value = k.sumber_nilai || 'nominal';
            document.getElementById('komponenKompensasiPeriode').value = k.periode || 'bulan';
        }
    } else {
        document.getElementById('modalKomponenKompensasiTitle').innerText = 'Tambah Komponen Kompensasi';
        document.getElementById('formKomponenKompensasi').reset();
        document.getElementById('komponenKompensasiId').value = '';
        document.getElementById('komponenKompensasiSchemeId').value = schemeId;
        
        // Defaults
        document.getElementById('komponenKompensasiJenis').value = 'kompensasi';
        document.getElementById('komponenKompensasiSifat').value = 'tetap';
        document.getElementById('komponenKompensasiSumber').value = 'nominal';
        document.getElementById('komponenKompensasiPeriode').value = 'bulan';
        document.getElementById('komponenKompensasiIsPersentase').value = '0';
    }
    
    // Trigger handlers to update visibility/labels
    handleJenisKomponenChange();
    handleSumberNilaiChange();
}

function tutupModalKomponenKompensasi() {
    tutupSemuaModal();
}

async function hapusKomponenKompensasi(id) {
    if (!await showConfirm('Apakah Anda yakin ingin menghapus komponen kompensasi ini?')) return;
    try {
        const res = await fetch(`${API_URL}/compensation-components/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderMasterKompensasi();
            showToast('Komponen kompensasi berhasil dihapus!', 'success');
        } else {
            showToast('Gagal menghapus komponen!', 'error');
        }
    } catch (err) {
        console.error(err);
    }
}
