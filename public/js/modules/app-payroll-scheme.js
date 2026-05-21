// ===== PAYROLL SCHEME MODULE =====
// Extracted from app.js for modular monolith architecture

// ===== 3. PAYROLL SCHEMES =====
async function renderPayrollSchemes() {
    try {
        if (!window.compensationSchemes) {
            const compRes = await fetch(`${API_URL}/compensation-schemes`);
            window.compensationSchemes = await compRes.json();
        }
        const res = await fetch(`${API_URL}/payroll-schemes`);
        payrollSchemes = await res.json();
        const container = document.getElementById('payrollSchemesContainer');
        if(!container) return;
        container.innerHTML = payrollSchemes.map(scheme => {
            const basic = scheme.components ? scheme.components.find(c => c.jenis_komponen === 'basic_salary' || c.nama.includes('Gaji Pokok')) : null;
            let basicDetails = 'Belum dikonfigurasi';
            if (basic) {
                if (basic.sumber_nilai === 'ump') {
                    basicDetails = `UMP (${basic.nilai}%)`;
                } else if (basic.sumber_nilai === 'umk') {
                    basicDetails = `UMK (${basic.nilai}%)`;
                } else if (basic.sumber_nilai === 'kompensasi') {
                    basicDetails = `Ambil dari Kompensasi (${basic.nilai}%)`;
                } else {
                    basicDetails = formatRupiah(basic.nilai);
                }
            }

            const compScheme = window.compensationSchemes ? window.compensationSchemes.find(cs => cs.id == scheme.compensation_scheme_id) : null;
            const compName = compScheme ? compScheme.nama : 'Tidak terhubung';

            const absenceDetails = `Prorate: ${scheme.prorate == 1 ? 'Ya' : 'Tidak'} | Absen Tidak Potong Gaji: ${scheme.absen_tidak_potong == 1 ? 'Ya' : 'Tidak'} | Potongan: ${formatRupiah(scheme.nominal_potongan || 0)}/hari`;

            return `
            <div class="scheme-card">
                <div class="scheme-card-header">
                    <div class="scheme-card-info">
                        <h4><i class="fas fa-file-invoice-dollar"></i> ${scheme.nama}</h4>
                            <div class="scheme-card-desc" style="margin-bottom: 8px;">${scheme.deskripsi || 'Tidak ada deskripsi'}</div>
                            <div style="font-size: 12px; color: #475569; display: grid; gap: 4px; border-top: 1px solid #f1f5f9; padding-top: 8px;">
                                <div><strong>Gaji Pokok:</strong> ${basicDetails}</div>
                                <div><strong>Skema Kompensasi:</strong> ${compName}</div>
                                <div><strong>Skema Absen:</strong> ${absenceDetails}</div>
                            </div>
                    </div>
                    <div class="scheme-card-actions">
                        <button class="btn-icon btn-edit" onclick="bukaModalSkema('edit', ${scheme.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon btn-delete" onclick="hapusSkema(${scheme.id})"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
            `;
        }).join('');
    } catch (err) { console.error(err); }
}

    // Form Skema Payroll submit handler
    if (document.getElementById('formSkema')) {
        document.getElementById('formSkema').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('skemaId').value;
            
            // Gather selected compensation components
            const selectedComponents = [];
            document.querySelectorAll('.skema-comp-checkbox:checked').forEach(cb => {
                try {
                    const comp = JSON.parse(decodeURIComponent(cb.getAttribute('data-comp')));
                    selectedComponents.push({
                        nama: comp.nama,
                        tipe: comp.tipe,
                        nilai: parseFloat(comp.nilai) || 0,
                        is_persentase: comp.is_persentase,
                        jenis_komponen: 'kompensasi',
                        sumber_nilai: comp.sumber_nilai || 'nominal',
                        periode: comp.periode || 'bulan',
                        sifat_kompensasi: comp.sifat_kompensasi || 'tetap'
                    });
                } catch (err) {
                    console.error('Error parsing checkbox data-comp:', err);
                }
            });

            const data = {
                nama: document.getElementById('skemaNama').value,
                deskripsi: document.getElementById('skemaDeskripsi').value,
                tipe: document.getElementById('skemaTipe').value,
                compensation_scheme_id: null,
                components: selectedComponents,
                prorate: (document.querySelector('input[name="skemaAbsenRule"]:checked')?.value === 'prorate') ? 1 : 0,
                absen_tidak_potong: (document.querySelector('input[name="skemaAbsenRule"]:checked')?.value === 'tidak_potong') ? 1 : 0,
                nominal_potongan: (document.querySelector('input[name="skemaAbsenRule"]:checked')?.value === 'potong_nominal') ? (parseFloat(document.getElementById('skemaNominalPotongan').value) || 0) : 0,
                sumber_nilai: document.getElementById('skemaSumber').value,
                periode: document.getElementById('skemaPeriode').value,
                nilai: parseFloat(document.getElementById('skemaNilai').value) || 0,
                is_persentase: parseInt(document.getElementById('skemaIsPersentase').value) || 0
            };
            const url = id ? `${API_URL}/payroll-schemes/${id}` : `${API_URL}/payroll-schemes`;
            const res = await fetch(url, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (res.ok) {
                tutupSemuaModal();
                renderPayrollSchemes();
                showToast(id ? 'Skema payroll berhasil diupdate!' : 'Skema payroll berhasil ditambahkan!', 'success');
            } else {
                showToast('Gagal menyimpan skema payroll!', 'error');
            }
        });
    }

async function bukaModalSkema(mode, id = null) {
    document.getElementById('modalSkema').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';

    try {
        const compRes = await fetch(`${API_URL}/compensation-schemes`);
        window.compensationSchemes = await compRes.json();
    } catch (err) {
        console.error('Error fetching compensation schemes in bukaModalSkema:', err);
    }

    const tetapBody = document.getElementById('tabelKompensasiTetapBody');
    const tidakTetapBody = document.getElementById('tabelKompensasiTidakTetapBody');
    if (tetapBody) tetapBody.innerHTML = `<tr><td colspan="2" style="padding: 12px; text-align: center; color: #94a3b8; font-size: 13px;">Belum ada skema kompensasi terpilih</td></tr>`;
    if (tidakTetapBody) tidakTetapBody.innerHTML = `<tr><td colspan="2" style="padding: 12px; text-align: center; color: #94a3b8; font-size: 13px;">Belum ada skema kompensasi terpilih</td></tr>`;

    if(mode === 'edit' && id) {
        const s = payrollSchemes.find(x => x.id == id);
        if(s) {
            document.getElementById('modalSkemaTitle').innerText = 'Edit Skema Payroll';
            document.getElementById('skemaId').value = s.id;
            document.getElementById('skemaNama').value = s.nama;
            document.getElementById('skemaDeskripsi').value = s.deskripsi;
            document.getElementById('skemaTipe').value = s.tipe;
            
            // Set absence radio buttons and nominal
            const radioProrate = document.querySelector('input[name="skemaAbsenRule"][value="prorate"]');
            const radioTidakPotong = document.querySelector('input[name="skemaAbsenRule"][value="tidak_potong"]');
            const radioPotongNominal = document.querySelector('input[name="skemaAbsenRule"][value="potong_nominal"]');

            if (radioProrate) radioProrate.checked = false;
            if (radioTidakPotong) radioTidakPotong.checked = false;
            if (radioPotongNominal) radioPotongNominal.checked = false;
            document.getElementById('skemaNominalPotongan').value = '';

            if (s.prorate == 1) {
                if (radioProrate) radioProrate.checked = true;
            } else if (s.absen_tidak_potong == 1) {
                if (radioTidakPotong) radioTidakPotong.checked = true;
            } else if (s.nominal_potongan > 0) {
                if (radioPotongNominal) radioPotongNominal.checked = true;
                document.getElementById('skemaNominalPotongan').value = s.nominal_potongan;
            }
            handleSkemaAbsenRuleChange();

            // Find basic salary component
            const basic = s.components ? s.components.find(c => c.jenis_komponen === 'basic_salary' || c.nama.includes('Gaji Pokok')) : null;
            if (basic) {
                document.getElementById('skemaSumber').value = basic.sumber_nilai || 'nominal';
                document.getElementById('skemaPeriode').value = basic.periode || 'bulan';
                document.getElementById('skemaNilai').value = basic.nilai || 0;
                document.getElementById('skemaIsPersentase').value = basic.is_persentase || '0';
            } else {
                document.getElementById('skemaSumber').value = 'nominal';
                document.getElementById('skemaPeriode').value = 'bulan';
                document.getElementById('skemaNilai').value = 0;
                document.getElementById('skemaIsPersentase').value = '0';
            }
            handlePayrollSchemeSumberNilaiChange();

            const savedComponents = s.components || [];

            const fixedSaved = savedComponents.filter(c => c.jenis_komponen !== 'basic_salary' && c.sifat_kompensasi === 'tetap');
            const variableSaved = savedComponents.filter(c => c.jenis_komponen !== 'basic_salary' && c.sifat_kompensasi === 'tidak_tetap');

            if (fixedSaved.length > 0 && tetapBody) {
                tetapBody.innerHTML = fixedSaved.map(c => {
                    let valStr = '';
                    if (c.sumber_nilai === 'ump') {
                        valStr = `${c.nilai}% UMP`;
                    } else if (c.sumber_nilai === 'umk') {
                        valStr = `${c.nilai}% UMK`;
                    } else if (c.sumber_nilai === 'ump_umk') {
                        valStr = `${c.nilai}% UMP/UMK`;
                    } else {
                        valStr = c.is_persentase == 1 ? `${c.nilai}%` : formatRupiahVal(c.nilai);
                    }
                    const dataAttr = encodeURIComponent(JSON.stringify(c));
                    return `
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 10px 12px; font-size: 13px; color: #334155; text-align: left; display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" class="skema-comp-checkbox" checked data-comp="${dataAttr}" style="cursor: pointer; width: 16px; height: 16px;">
                                <div><span style="font-weight: 500;">${c.nama}</span></div>
                            </td>
                            <td style="padding: 10px 12px; font-size: 13px; color: #1e293b; font-weight: 600; text-align: right; vertical-align: middle;">${valStr}</td>
                        </tr>
                    `;
                }).join('');
            }

            if (variableSaved.length > 0 && tidakTetapBody) {
                tidakTetapBody.innerHTML = variableSaved.map(c => {
                    let valStr = '';
                    if (c.sumber_nilai === 'ump') {
                        valStr = `${c.nilai}% UMP`;
                    } else if (c.sumber_nilai === 'umk') {
                        valStr = `${c.nilai}% UMK`;
                    } else if (c.sumber_nilai === 'ump_umk') {
                        valStr = `${c.nilai}% UMP/UMK`;
                    } else {
                        valStr = c.is_persentase == 1 ? `${c.nilai}%` : formatRupiahVal(c.nilai);
                    }
                    const dataAttr = encodeURIComponent(JSON.stringify(c));
                    return `
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 10px 12px; font-size: 13px; color: #334155; text-align: left; display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" class="skema-comp-checkbox" checked data-comp="${dataAttr}" style="cursor: pointer; width: 16px; height: 16px;">
                                <div><span style="font-weight: 500;">${c.nama}</span></div>
                            </td>
                            <td style="padding: 10px 12px; font-size: 13px; color: #1e293b; font-weight: 600; text-align: right; vertical-align: middle;">${valStr}</td>
                        </tr>
                    `;
                }).join('');
            }
        }
    } else {
        document.getElementById('modalSkemaTitle').innerText = 'Tambah Skema Payroll';
        document.getElementById('formSkema').reset();
        document.getElementById('skemaId').value = '';
        const radioProrate = document.querySelector('input[name="skemaAbsenRule"][value="prorate"]');
        const radioTidakPotong = document.querySelector('input[name="skemaAbsenRule"][value="tidak_potong"]');
        const radioPotongNominal = document.querySelector('input[name="skemaAbsenRule"][value="potong_nominal"]');
        if (radioProrate) radioProrate.checked = false;
        if (radioTidakPotong) radioTidakPotong.checked = false;
        if (radioPotongNominal) radioPotongNominal.checked = false;
        document.getElementById('skemaNominalPotongan').value = '';
        handleSkemaAbsenRuleChange();
        document.getElementById('skemaSumber').value = 'nominal';
        document.getElementById('skemaPeriode').value = 'bulan';
        document.getElementById('skemaNilai').value = '';
        document.getElementById('skemaIsPersentase').value = '0';
        handlePayrollSchemeSumberNilaiChange();
    }
}

const rupiahFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
});

function formatRupiahVal(val) {
    return rupiahFormatter.format(val);
}

function bukaModalPilihSkema(sifat) {
    window.activePilihSkemaSifat = sifat;
    
    const titleEl = document.getElementById('modalPilihSkemaTitle');
    const bodyEl = document.getElementById('modalPilihSkemaBody');
    if (!titleEl || !bodyEl) return;

    titleEl.innerText = sifat === 'tetap' ? 'Pilih Skema Kompensasi Tetap' : 'Pilih Skema Kompensasi Tidak Tetap';

    const filteredSchemes = (window.compensationSchemes || []).filter(s => 
        (s.components || []).some(c => c.sifat_kompensasi === sifat)
    );

    if (filteredSchemes.length === 0) {
        bodyEl.innerHTML = `<tr><td colspan="3" style="padding: 15px; text-align: center; color: #64748b;">Tidak ada skema kompensasi tersedia</td></tr>`;
    } else {
        const mainCompNames = Array.from(document.querySelectorAll(`#tabelKompensasi${sifat === 'tetap' ? 'Tetap' : 'TidakTetap'}Body .skema-comp-checkbox`))
            .map(cb => {
                try {
                    return JSON.parse(decodeURIComponent(cb.getAttribute('data-comp'))).nama;
                } catch (e) {
                    return null;
                }
            }).filter(Boolean);

        bodyEl.innerHTML = filteredSchemes.map(s => {
            const compsList = (s.components || []).filter(c => c.sifat_kompensasi === sifat).map(c => {
                let valStr = '';
                if (c.sumber_nilai === 'ump') {
                    valStr = `${c.nilai}% UMP`;
                } else if (c.sumber_nilai === 'umk') {
                    valStr = `${c.nilai}% UMK`;
                } else if (c.sumber_nilai === 'ump_umk') {
                    valStr = `${c.nilai}% UMP/UMK`;
                } else {
                    valStr = c.is_persentase == 1 ? `${c.nilai}%` : formatRupiahVal(c.nilai);
                }
                return `<span style="display: inline-block; background: #e2e8f0; color: #334155; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-right: 4px; margin-bottom: 4px;">${c.nama}: ${valStr}</span>`;
            }).join('');

            const isChecked = (s.components || []).some(c => c.sifat_kompensasi === sifat && mainCompNames.includes(c.nama));
            const checkedAttr = isChecked ? 'checked' : '';

            return `
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px 8px; text-align: center; vertical-align: middle;">
                        <input type="checkbox" class="modal-choice-scheme-checkbox" value="${s.id}" ${checkedAttr} style="cursor: pointer; width: 16px; height: 16px;">
                    </td>
                    <td style="padding: 10px 8px; font-weight: 600; color: #1e293b; vertical-align: middle;">${s.nama}</td>
                    <td style="padding: 10px 8px; vertical-align: middle; line-height: 1.5;">${compsList}</td>
                </tr>
            `;
        }).join('');
    }

    document.getElementById('overlayPilihSkema').style.display = 'block';
    document.getElementById('modalPilihSkema').style.display = 'block';
}

function tutupModalPilihSkema() {
    document.getElementById('overlayPilihSkema').style.display = 'none';
    document.getElementById('modalPilihSkema').style.display = 'none';
}

function terapkanPilihanSkema() {
    const sifat = window.activePilihSkemaSifat;
    const body = sifat === 'tetap' ? document.getElementById('tabelKompensasiTetapBody') : document.getElementById('tabelKompensasiTidakTetapBody');
    if (!body) return;

    const checkedCheckboxes = document.querySelectorAll('.modal-choice-scheme-checkbox:checked');
    const checkedSchemeIds = Array.from(checkedCheckboxes).map(cb => cb.value);

    const existingComps = [];
    body.querySelectorAll('.skema-comp-checkbox').forEach(cb => {
        try {
            const data = JSON.parse(decodeURIComponent(cb.getAttribute('data-comp')));
            existingComps.push({
                data: data,
                checked: cb.checked
            });
        } catch (e) {}
    });

    const componentsToRender = [];
    const addedNames = new Set();

    checkedSchemeIds.forEach(sid => {
        const cs = (window.compensationSchemes || []).find(s => s.id == sid);
        if (cs && cs.components) {
            cs.components.forEach(c => {
                if (c.sifat_kompensasi === sifat && c.jenis_komponen !== 'basic_salary') {
                    if (!addedNames.has(c.nama)) {
                        addedNames.add(c.nama);
                        componentsToRender.push({
                            data: c,
                            checked: true
                        });
                    }
                }
            });
        }
    });

    existingComps.forEach(ec => {
        if (!addedNames.has(ec.data.nama)) {
            addedNames.add(ec.data.nama);
            componentsToRender.push(ec);
        } else {
            const idx = componentsToRender.findIndex(x => x.data.nama === ec.data.nama);
            if (idx !== -1) {
                componentsToRender[idx].checked = ec.checked;
            }
        }
    });

    if (componentsToRender.length === 0) {
        body.innerHTML = `<tr><td colspan="2" style="padding: 12px; text-align: center; color: #94a3b8; font-size: 13px;">Belum ada skema kompensasi terpilih</td></tr>`;
        tutupModalPilihSkema();
        return;
    }

    body.innerHTML = componentsToRender.map(item => {
        const c = item.data;
        let valStr = '';
        if (c.sumber_nilai === 'ump') {
            valStr = `${c.nilai}% UMP`;
        } else if (c.sumber_nilai === 'umk') {
            valStr = `${c.nilai}% UMK`;
        } else if (c.sumber_nilai === 'ump_umk') {
            valStr = `${c.nilai}% UMP/UMK`;
        } else {
            valStr = c.is_persentase == 1 ? `${c.nilai}%` : formatRupiahVal(c.nilai);
        }

        const checkedAttr = item.checked ? 'checked' : '';
        const dataAttr = encodeURIComponent(JSON.stringify(c));

        return `
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 12px; font-size: 13px; color: #334155; text-align: left; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" class="skema-comp-checkbox" ${checkedAttr} data-comp="${dataAttr}" style="cursor: pointer; width: 16px; height: 16px;">
                    <div>
                        <span style="font-weight: 500;">${c.nama}</span>
                    </div>
                </td>
                <td style="padding: 10px 12px; font-size: 13px; color: #1e293b; font-weight: 600; text-align: right; vertical-align: middle;">${valStr}</td>
            </tr>
        `;
    }).join('');

    tutupModalPilihSkema();
}

window.bukaModalPilihSkema = bukaModalPilihSkema;
window.tutupModalPilihSkema = tutupModalPilihSkema;
window.terapkanPilihanSkema = terapkanPilihanSkema;

function handleSkemaAbsenRuleChange() {
    const selectedRule = document.querySelector('input[name="skemaAbsenRule"]:checked')?.value;
    const container = document.getElementById('containerNominalPotonganSkema');
    if (!container) return;
    if (selectedRule === 'potong_nominal') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
        document.getElementById('skemaNominalPotongan').value = '';
    }
}
window.handleSkemaAbsenRuleChange = handleSkemaAbsenRuleChange;
