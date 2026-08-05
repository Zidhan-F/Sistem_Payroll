// ===== PAYROLL SCHEME MODULE =====
// Extracted from app.js for modular monolith architecture

// ===== 3. PAYROLL SCHEMES =====
let payrollSchemes = [];

async function renderPayrollSchemes() {
    try {
        const container = document.getElementById('payrollSchemesContainer');
        if (container) {
            container.innerHTML = `<div style="text-align: center; padding: 40px; color: #94a3b8; width: 100%;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</div>`;
        }

        if (!window.compensationSchemes) {
            const compRes = await fetch(`${API_URL}/compensation-schemes`);
            window.compensationSchemes = await compRes.json();
        }
        const res = await fetch(`${API_URL}/payroll-schemes`);
        payrollSchemes = await res.json();
        if(!container) return;
        container.innerHTML = payrollSchemes.map(scheme => {
            const basic = scheme.components ? scheme.components.find(c => c.jenis_komponen === 'basic_salary' || c.nama.includes('Gaji Pokok') || c.nama.includes('Basic Salary')) : null;
            let basicDetails = 'Not configured';
            if (basic) {
                if (basic.sumber_nilai === 'ump') {
                    basicDetails = `UMP (${parseFloat(basic.nilai)}%)`;
                } else if (basic.sumber_nilai === 'umk') {
                    basicDetails = `UMK (${parseFloat(basic.nilai)}%)`;
                } else if (basic.sumber_nilai === 'kompensasi') {
                    basicDetails = `Take from Allowance (${parseFloat(basic.nilai)}%)`;
                } else {
                    basicDetails = formatRupiah(basic.nilai);
                }
            }

            const linkedComps = (scheme.components || []).filter(c => c.jenis_komponen !== 'basic_salary');
            const compName = linkedComps.length > 0 
                ? linkedComps.map(c => c.nama).join(', ') 
                : 'No allowance';

            const absenceDetails = `Prorate: ${scheme.prorate == 1 ? 'Yes' : 'No'} | Absen No Salary Cut: ${scheme.absen_tidak_potong == 1 ? 'Yes' : 'No'} | Cut: ${formatRupiah(scheme.nominal_potongan || 0)}/day`;

            return `
            <div class="scheme-card">
                <div class="scheme-card-header">
                    <div class="scheme-card-info">
                        <h4><i class="fas fa-file-invoice-dollar"></i> ${scheme.nama}</h4>
                            <div class="scheme-card-desc" style="margin-bottom: 8px;">${scheme.deskripsi || 'No description'}</div>
                            <div style="font-size: 12px; color: #475569; display: grid; gap: 4px; border-top: 1px solid #f1f5f9; padding-top: 8px;">
                                <div><strong>Basic Salary:</strong> ${basicDetails}</div>
                                <div><strong>Allowance Scheme:</strong> ${compName}</div>
                                <div><strong>Absence Scheme:</strong> ${absenceDetails}</div>
                            </div>
                    </div>
                    <div class="scheme-card-actions">
                        <button class="btn-icon btn-edit" onclick="window.bukaModalSkema('edit', ${scheme.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon btn-delete" onclick="window.hapusSkema(${scheme.id})"><i class="fas fa-trash"></i></button>
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
                        sifat_kompensasi: comp.sifat_kompensasi || 'tetap',
                        is_bpjs: (comp.is_bpjs === true || comp.is_bpjs == 1) ? 1 : 0,
                        is_pph21: (comp.is_pph21 === false || comp.is_pph21 == 0) ? 0 : 1
                    });
                } catch (err) {
                    console.error('Error parsing checkbox data-comp:', err);
                }
            });

            const overtimeType = document.querySelector('input[name="skemaOvertimeType"]:checked')?.value || 'standard';
            const lumpsumSubtype = document.querySelector('input[name="skemaLumpsumSubtype"]:checked')?.value || 'per_jam';
            const lumpsumNominal = parseFormattedNumber(document.getElementById('skemaLumpsumNominal').value) || 0;

            const data = {
                nama: document.getElementById('skemaNama').value,
                deskripsi: document.getElementById('skemaDeskripsi').value,
                tipe: document.getElementById('skemaTipe').value,
                compensation_scheme_id: null,
                components: selectedComponents,
                prorate: (document.querySelector('input[name="skemaAbsenRule"]:checked')?.value === 'prorate') ? 1 : 0,
                absen_tidak_potong: (document.querySelector('input[name="skemaAbsenRule"]:checked')?.value === 'tidak_potong') ? 1 : 0,
                nominal_potongan: parseFormattedNumber(document.getElementById('skemaNominalPotongan').value) || 0,
                sumber_nilai: document.getElementById('skemaSumber').value,
                periode: document.getElementById('skemaPeriode').value,
                nilai: parseFormattedNumber(document.getElementById('skemaNilai').value) || 0,
                is_persentase: parseInt(document.getElementById('skemaIsPersentase').value) || 0,
                grace_period_late: parseInt(document.getElementById('skemaGraceLate').value) || 0,
                grace_period_early: parseInt(document.getElementById('skemaGraceEarly').value) || 0,
                min_overtime: parseInt(document.getElementById('skemaMinOvertime').value) || 30,
                max_early_arrival_minutes: parseInt(document.getElementById('skemaMaxEarlyArrivalMinutes').value) || 180,
                denda_terlambat_per_jam: parseFormattedNumber(document.getElementById('skemaDendaTerlambatPerJam').value) || 0,
                denda_alfa_per_hari: parseFormattedNumber(document.getElementById('skemaDendaAlfaPerHari').value) || 0,
                overtime_type: overtimeType,
                lumpsum_subtype: overtimeType === 'lumpsum' ? lumpsumSubtype : null,
                lumpsum_nominal: overtimeType === 'lumpsum' ? lumpsumNominal : 0,
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
                showToast(id ? 'Payroll scheme updated successfully!' : 'Payroll scheme added successfully!', 'success');
            } else {
                showToast('Failed to save payroll scheme!', 'error');
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
    if (tetapBody) tetapBody.innerHTML = `<tr><td colspan="2" style="padding: 12px; text-align: center; color: #94a3b8; font-size: 13px;">No allowance scheme selected yet</td></tr>`;
    if (tidakTetapBody) tidakTetapBody.innerHTML = `<tr><td colspan="2" style="padding: 12px; text-align: center; color: #94a3b8; font-size: 13px;">No allowance scheme selected yet</td></tr>`;

    if(mode === 'edit' && id) {
        const s = payrollSchemes.find(x => x.id == id);
        if(s) {
            document.getElementById('modalSkemaTitle').innerText = 'Edit Payroll Scheme';
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

            if (s.prorate == 1) {
                if (radioProrate) radioProrate.checked = true;
            } else if (s.absen_tidak_potong == 1) {
                if (radioTidakPotong) radioTidakPotong.checked = true;
            } else if (s.nominal_potongan > 0) {
                if (radioPotongNominal) radioPotongNominal.checked = true;
            }
            document.getElementById('skemaNominalPotongan').value = s.nominal_potongan ? formatRupiah(Math.round(s.nominal_potongan)) : '';
            handleSkemaAbsenRuleChange();
            
            document.getElementById('skemaGraceLate').value = s.grace_period_late || 0;
            document.getElementById('skemaGraceEarly').value = s.grace_period_early || 0;
            document.getElementById('skemaMinOvertime').value = s.min_overtime || 30;
            document.getElementById('skemaMaxEarlyArrivalMinutes').value = s.max_early_arrival_minutes !== undefined && s.max_early_arrival_minutes !== null ? s.max_early_arrival_minutes : 180;
            document.getElementById('skemaDendaTerlambatPerJam').value = formatRupiah(s.denda_terlambat_per_jam || 0);
            document.getElementById('skemaDendaAlfaPerHari').value = formatRupiah(s.denda_alfa_per_hari || 0);

            // Load overtime configuration
            const savedOvertimeType = s.overtime_type || 'standard';
            setOvertimeType(savedOvertimeType);
            if (savedOvertimeType === 'lumpsum') {
                const savedSubtype = s.lumpsum_subtype || 'per_jam';
                setLumpsumSubtype(savedSubtype);
                const nomEl = document.getElementById('skemaLumpsumNominal');
                if (nomEl) {
                    nomEl.value = s.lumpsum_nominal ? formatRupiah(Math.round(s.lumpsum_nominal)) : '';
                }
            }

            // Find basic salary component
            const basic = s.components ? s.components.find(c => c.jenis_komponen === 'basic_salary' || c.nama.includes('Gaji Pokok') || c.nama.includes('Basic Salary')) : null;
            if (basic) {
                document.getElementById('skemaSumber').value = basic.sumber_nilai || 'nominal';
                document.getElementById('skemaPeriode').value = basic.periode || 'bulan';
                const elNilai = document.getElementById('skemaNilai');
                if (basic.sumber_nilai === 'ump' || basic.sumber_nilai === 'umk') {
                    elNilai.value = parseFloat(basic.nilai) || 0;
                } else {
                    elNilai.value = Math.round(parseFloat(basic.nilai) || 0);
                    formatRupiahInput(elNilai);
                }
                document.getElementById('skemaIsPersentase').value = basic.is_persentase || '0';
            } else {
                document.getElementById('skemaSumber').value = 'nominal';
                document.getElementById('skemaPeriode').value = 'bulan';
                const elNilai = document.getElementById('skemaNilai');
                elNilai.value = 0;
                formatRupiahInput(elNilai);
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
                        valStr = `${parseFloat(c.nilai)}% UMP`;
                    } else if (c.sumber_nilai === 'umk') {
                        valStr = `${parseFloat(c.nilai)}% UMK`;
                    } else if (c.sumber_nilai === 'ump_umk') {
                        valStr = `${parseFloat(c.nilai)}% UMP/UMK`;
                    } else {
                        valStr = c.is_persentase == 1 ? `${parseFloat(c.nilai)}%` : formatRupiahVal(c.nilai);
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
                        valStr = `${parseFloat(c.nilai)}% UMP`;
                    } else if (c.sumber_nilai === 'umk') {
                        valStr = `${parseFloat(c.nilai)}% UMK`;
                    } else if (c.sumber_nilai === 'ump_umk') {
                        valStr = `${parseFloat(c.nilai)}% UMP/UMK`;
                    } else {
                        valStr = c.is_persentase == 1 ? `${parseFloat(c.nilai)}%` : formatRupiahVal(c.nilai);
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
        document.getElementById('modalSkemaTitle').innerText = 'Add Payroll Scheme';
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
        
        document.getElementById('skemaGraceLate').value = 0;
        document.getElementById('skemaGraceEarly').value = 0;
        document.getElementById('skemaMinOvertime').value = 30;
        document.getElementById('skemaMaxEarlyArrivalMinutes').value = 180;
        document.getElementById('skemaDendaTerlambatPerJam').value = '0';
        document.getElementById('skemaDendaAlfaPerHari').value = '0';
        document.getElementById('skemaSumber').value = 'nominal';
        document.getElementById('skemaPeriode').value = 'bulan';
        document.getElementById('skemaNilai').value = '';
        document.getElementById('skemaIsPersentase').value = '0';
        handlePayrollSchemeSumberNilaiChange();

        // Reset overtime configuration
        setOvertimeType('standard');
        setLumpsumSubtype('per_jam');
        document.getElementById('skemaLumpsumNominal').value = '';
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

    titleEl.innerText = sifat === 'tetap' ? 'Select Fixed Allowance Scheme' : 'Select Variable Allowance Scheme';

    const filteredSchemes = (window.compensationSchemes || []).filter(s => 
        (s.components || []).some(c => c.sifat_kompensasi === sifat)
    );

    if (filteredSchemes.length === 0) {
        bodyEl.innerHTML = `<tr><td colspan="3" style="padding: 15px; text-align: center; color: #64748b;">No allowance schemes available</td></tr>`;
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
                    valStr = `${parseFloat(c.nilai)}% UMP`;
                } else if (c.sumber_nilai === 'umk') {
                    valStr = `${parseFloat(c.nilai)}% UMK`;
                } else if (c.sumber_nilai === 'ump_umk') {
                    valStr = `${parseFloat(c.nilai)}% UMP/UMK`;
                } else {
                    valStr = c.is_persentase == 1 ? `${parseFloat(c.nilai)}%` : formatRupiahVal(c.nilai);
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
        body.innerHTML = `<tr><td colspan="2" style="padding: 12px; text-align: center; color: #94a3b8; font-size: 13px;">No allowance scheme selected yet</td></tr>`;
        tutupModalPilihSkema();
        return;
    }

    body.innerHTML = componentsToRender.map(item => {
        const c = item.data;
        let valStr = '';
        if (c.sumber_nilai === 'ump') {
            valStr = `${parseFloat(c.nilai)}% UMP`;
        } else if (c.sumber_nilai === 'umk') {
            valStr = `${parseFloat(c.nilai)}% UMK`;
        } else if (c.sumber_nilai === 'ump_umk') {
            valStr = `${parseFloat(c.nilai)}% UMP/UMK`;
        } else {
            valStr = c.is_persentase == 1 ? `${parseFloat(c.nilai)}%` : formatRupiahVal(c.nilai);
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
    const isNominal = document.querySelector('input[name="skemaAbsenRule"][value="potong_nominal"]')?.checked || false;
    const inputNominal = document.getElementById('skemaNominalPotongan');
    if (inputNominal) {
        inputNominal.disabled = !isNominal;
        if (!isNominal) {
            inputNominal.value = '';
        }
    }
}
window.handleSkemaAbsenRuleChange = handleSkemaAbsenRuleChange;

window.bukaModalSkema = bukaModalSkema;

async function hapusSkema(id) {
    if (!await showConfirm('Are you sure you want to delete this payroll scheme?')) return;
    try {
        const res = await fetch(`${API_URL}/payroll-schemes/${id}`, {
            method: 'DELETE'
        });
        if (res.ok) {
            renderPayrollSchemes();
            showToast('Payroll scheme deleted successfully!', 'success');
        } else {
            showToast('Failed to delete payroll scheme!', 'error');
        }
    } catch (err) {
        console.error('Error deleting payroll scheme:', err);
        showToast('Failed to delete payroll scheme!', 'error');
    }
}
window.hapusSkema = hapusSkema;
window.renderPayrollSchemes = renderPayrollSchemes;

// ===== OVERTIME CONFIGURATION FUNCTIONS =====
function setOvertimeType(type) {
    const radioStandard = document.querySelector('input[name="skemaOvertimeType"][value="standard"]');
    const radioLumpsum = document.querySelector('input[name="skemaOvertimeType"][value="lumpsum"]');
    const standardPanel = document.getElementById('overtimeStandardPanel');
    const lumpsumPanel = document.getElementById('overtimeLumpsumPanel');
    const labelStandard = document.getElementById('labelOvertimeStandard');
    const labelLumpsum = document.getElementById('labelOvertimeLumpsum');

    if (type === 'standard') {
        if (radioStandard) radioStandard.checked = true;
        if (radioLumpsum) radioLumpsum.checked = false;
        if (standardPanel) standardPanel.style.display = 'block';
        if (lumpsumPanel) lumpsumPanel.style.display = 'none';
        if (labelStandard) {
            labelStandard.style.borderColor = '#3b82f6';
            labelStandard.style.background = '#eff6ff';
            labelStandard.style.color = '#1e40af';
        }
        if (labelLumpsum) {
            labelLumpsum.style.borderColor = '#e2e8f0';
            labelLumpsum.style.background = 'white';
            labelLumpsum.style.color = '#475569';
        }
    } else {
        if (radioStandard) radioStandard.checked = false;
        if (radioLumpsum) radioLumpsum.checked = true;
        if (standardPanel) standardPanel.style.display = 'none';
        if (lumpsumPanel) lumpsumPanel.style.display = 'flex';
        if (labelLumpsum) {
            labelLumpsum.style.borderColor = '#3b82f6';
            labelLumpsum.style.background = '#eff6ff';
            labelLumpsum.style.color = '#1e40af';
        }
        if (labelStandard) {
            labelStandard.style.borderColor = '#e2e8f0';
            labelStandard.style.background = 'white';
            labelStandard.style.color = '#475569';
        }
        handleLumpsumSubtypeChange();
    }
}
window.setOvertimeType = setOvertimeType;

function handleOvertimeTypeChange() {
    const val = document.querySelector('input[name="skemaOvertimeType"]:checked')?.value || 'standard';
    setOvertimeType(val);
}
window.handleOvertimeTypeChange = handleOvertimeTypeChange;

function setLumpsumSubtype(subtype) {
    const radios = document.querySelectorAll('input[name="skemaLumpsumSubtype"]');
    radios.forEach(r => {
        r.checked = (r.value === subtype);
    });

    // Update label styles
    const labels = {
        'per_jam': document.getElementById('labelLumpsumPerJam'),
        'harian': document.getElementById('labelLumpsumHarian'),
        'bulanan': document.getElementById('labelLumpsumBulanan')
    };
    Object.keys(labels).forEach(key => {
        const el = labels[key];
        if (!el) return;
        if (key === subtype) {
            el.style.borderColor = '#3b82f6';
            el.style.background = '#eff6ff';
        } else {
            el.style.borderColor = '#e2e8f0';
            el.style.background = 'white';
        }
    });

    // Update nominal label
    const nomLabel = document.getElementById('labelLumpsumNominal');
    if (nomLabel) {
        const subtypeLabels = {
            'per_jam': 'Nominal Upah Lembur Per Jam (Rp)',
            'harian': 'Nominal Upah Lembur Per Hari (Rp)',
            'bulanan': 'Nominal Upah Lembur Per Bulan (Rp)'
        };
        nomLabel.textContent = subtypeLabels[subtype] || subtypeLabels['per_jam'];
    }
}
window.setLumpsumSubtype = setLumpsumSubtype;

function handleLumpsumSubtypeChange() {
    const val = document.querySelector('input[name="skemaLumpsumSubtype"]:checked')?.value || 'per_jam';
    setLumpsumSubtype(val);
}
window.handleLumpsumSubtypeChange = handleLumpsumSubtypeChange;

// ===== EXCEL TEMPLATE DOWNLOAD & UPLOAD FITUR SKEMA PAYROLL =====

function downloadTemplateSkemaPayroll() {
    try {
        // Form template excel kosong (hanya header kolom tanpa data contoh)
        const headers = [[
            'Nama Skema',
            'Deskripsi',
            'Tipe Skema (bulanan/harian)',
            'Gaji Pokok Nominal (Rp)',
            'Sumber Gaji (nominal/ump/umk)',
            'Prorate (1=Ya, 0=Tidak)',
            'Absen Tidak Potong (1=Ya, 0=Tidak)',
            'Nominal Potongan Alpa (Rp/hari)',
            'Toleransi Terlambat (Menit)',
            'Toleransi Pulang Cepat (Menit)',
            'Minimal Lembur (Menit)',
            'Tipe Lembur (standard/lumpsum)',
            'Nominal Lembur Lumpsum (Rp)',
            'Komponen 1 Nama',
            'Komponen 1 Tipe (pendapatan/potongan)',
            'Komponen 1 Nilai',
            'Komponen 1 Persentase (1=Ya, 0=Tidak)',
            'Komponen 2 Nama',
            'Komponen 2 Tipe (pendapatan/potongan)',
            'Komponen 2 Nilai',
            'Komponen 2 Persentase (1=Ya, 0=Tidak)',
            'Komponen 3 Nama',
            'Komponen 3 Tipe (pendapatan/potongan)',
            'Komponen 3 Nilai',
            'Komponen 3 Persentase (1=Ya, 0=Tidak)'
        ]];

        const ws = XLSX.utils.aoa_to_sheet(headers);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Template Skema Payroll');

        // Adjust column widths
        ws['!cols'] = [
            { wch: 25 }, // Nama Skema
            { wch: 25 }, // Deskripsi
            { wch: 22 }, // Tipe Skema
            { wch: 22 }, // Gaji Pokok Nominal
            { wch: 22 }, // Sumber Gaji
            { wch: 20 }, // Prorate
            { wch: 22 }, // Absen Tidak Potong
            { wch: 24 }, // Nominal Potongan Alpa
            { wch: 22 }, // Toleransi Terlambat
            { wch: 24 }, // Toleransi Pulang Cepat
            { wch: 20 }, // Minimal Lembur
            { wch: 24 }, // Tipe Lembur
            { wch: 24 }, // Nominal Lembur Lumpsum
            { wch: 20 }, // Komponen 1 Nama
            { wch: 25 }, // Komponen 1 Tipe
            { wch: 18 }, // Komponen 1 Nilai
            { wch: 24 }, // Komponen 1 Persentase
            { wch: 20 }, // Komponen 2 Nama
            { wch: 25 }, // Komponen 2 Tipe
            { wch: 18 }, // Komponen 2 Nilai
            { wch: 24 }, // Komponen 2 Persentase
            { wch: 20 }, // Komponen 3 Nama
            { wch: 25 }, // Komponen 3 Tipe
            { wch: 18 }, // Komponen 3 Nilai
            { wch: 24 }  // Komponen 3 Persentase
        ];

        XLSX.writeFile(wb, 'template_master_payroll_scheme.xlsx');
        if (typeof showToast === 'function') {
            showToast('Template Excel kosong berhasil diunduh!', 'success');
        } else {
            alert('Template Excel kosong berhasil diunduh!');
        }
    } catch (err) {
        console.error('Error downloading template:', err);
        if (typeof showToast === 'function') {
            showToast('Gagal mendownload template Excel', 'error');
        }
    }
}
window.downloadTemplateSkemaPayroll = downloadTemplateSkemaPayroll;

function bukaModalUploadSkemaPayroll() {
    const modal = document.getElementById('modalUploadSkemaPayroll');
    const overlay = document.getElementById('overlay');
    if (modal) modal.style.display = 'block';
    if (overlay) overlay.style.display = 'block';

    // Reset file input label
    const fileInput = document.getElementById('fileSkemaPayrollExcel');
    const fileLabel = document.getElementById('labelSkemaPayrollFilename');
    if (fileInput) fileInput.value = '';
    if (fileLabel) {
        fileLabel.textContent = 'Tidak ada file yang dipilih';
        fileLabel.style.color = '#059669';
    }
}
window.bukaModalUploadSkemaPayroll = bukaModalUploadSkemaPayroll;

function tutupModalUploadSkemaPayroll() {
    const modal = document.getElementById('modalUploadSkemaPayroll');
    const overlay = document.getElementById('overlay');
    if (modal) modal.style.display = 'none';
    if (overlay) overlay.style.display = 'none';
}
window.tutupModalUploadSkemaPayroll = tutupModalUploadSkemaPayroll;

function handleSkemaPayrollFileSelect(e) {
    const file = e && e.target && e.target.files ? e.target.files[0] : null;
    const fileLabel = document.getElementById('labelSkemaPayrollFilename');
    if (file && fileLabel) {
        fileLabel.textContent = `📌 File Terpilih: ${file.name}`;
        fileLabel.style.color = '#059669';
    } else if (fileLabel) {
        fileLabel.textContent = 'Tidak ada file yang dipilih';
    }
}
window.handleSkemaPayrollFileSelect = handleSkemaPayrollFileSelect;

async function importSkemaPayrollExcel() {
    const fileInput = document.getElementById('fileSkemaPayrollExcel');
    const file = fileInput && fileInput.files ? fileInput.files[0] : null;

    if (!file) {
        if (typeof showToast === 'function') {
            showToast('Silakan pilih file Excel terlebih dahulu!', 'warning');
        } else {
            alert('Silakan pilih file Excel terlebih dahulu!');
        }
        return;
    }

    const reader = new FileReader();
    reader.onload = async (e) => {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];

            const rows = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
            if (rows.length < 2) {
                if (typeof showToast === 'function') {
                    showToast('File Excel tidak berisi baris data!', 'error');
                } else {
                    alert('File Excel tidak berisi baris data!');
                }
                return;
            }

            const headerRow = rows[0].map(h => h ? h.toString().trim().toLowerCase() : '');
            
            // Helper to get value by matching header keyword
            const getVal = (rowArr, keywords, defaultVal = '') => {
                for (const kw of keywords) {
                    const idx = headerRow.findIndex(h => h.includes(kw));
                    if (idx !== -1 && rowArr[idx] !== undefined && rowArr[idx] !== null) {
                        return rowArr[idx].toString().trim();
                    }
                }
                return defaultVal;
            };

            const parsedSchemes = [];

            for (let i = 1; i < rows.length; i++) {
                const r = rows[i];
                if (!r || r.length === 0) continue;
                const isRowEmpty = r.every(cell => cell === null || cell === undefined || cell.toString().trim() === '');
                if (isRowEmpty) continue;

                // Read Nama Skema (first column or header search)
                let namaSkema = r[0] ? r[0].toString().trim() : '';
                if (!namaSkema) {
                    namaSkema = getVal(r, ['nama skema', 'nama', 'skema']);
                }
                if (!namaSkema) continue;

                const deskripsi = getVal(r, ['deskripsi', 'catatan', 'keterangan']);
                const tipe = getVal(r, ['tipe skema', 'tipe'], 'bulanan').toLowerCase();
                const gajiPokok = parseFloat(getVal(r, ['gaji pokok', 'gajipokok', 'basic salary', 'nominal gaji'], 0)) || 0;
                const sumberGaji = getVal(r, ['sumber gaji', 'sumbergaji'], 'nominal').toLowerCase();
                const prorate = parseInt(getVal(r, ['prorate'], 0)) || 0;
                const absenTidakPotong = parseInt(getVal(r, ['absen tidak potong', 'tidak potong'], 0)) || 0;
                const nominalPotongan = parseFloat(getVal(r, ['nominal potongan', 'potongan alpa'], 0)) || 0;
                const graceLate = parseInt(getVal(r, ['toleransi terlambat', 'late'], 0)) || 0;
                const graceEarly = parseInt(getVal(r, ['toleransi pulang cepat', 'early'], 0)) || 0;
                const minOvertime = parseInt(getVal(r, ['minimal lembur', 'min overtime'], 30)) || 30;
                const tipeLembur = getVal(r, ['tipe lembur', 'overtime type'], 'standard').toLowerCase();
                const lumpsumNominal = parseFloat(getVal(r, ['nominal lembur lumpsum', 'lumpsum'], 0)) || 0;

                // Extract components dynamically if present
                const components = [];
                for (let compIdx = 1; compIdx <= 5; compIdx++) {
                    const compNama = getVal(r, [`komponen ${compIdx} nama`, `komponen${compIdx}nama`, `tunjangan ${compIdx} nama`, `potongan ${compIdx} nama`]);
                    if (compNama) {
                        const compTipe = getVal(r, [`komponen ${compIdx} tipe`, `tunjangan ${compIdx} tipe`, `potongan ${compIdx} tipe`], 'pendapatan').toLowerCase();
                        const compNilai = parseFloat(getVal(r, [`komponen ${compIdx} nilai`, `tunjangan ${compIdx} nominal`, `potongan ${compIdx} nominal`], 0)) || 0;
                        const compPersen = parseInt(getVal(r, [`komponen ${compIdx} persentase`], 0)) || 0;
                        components.push({
                            nama: compNama,
                            tipe: compTipe,
                            nilai: compNilai,
                            is_persentase: compPersen
                        });
                    }
                }

                parsedSchemes.push({
                    nama: namaSkema,
                    deskripsi: deskripsi,
                    tipe: tipe,
                    gaji_pokok: gajiPokok,
                    sumber_gaji: sumberGaji,
                    prorate: prorate,
                    absen_tidak_potong: absenTidakPotong,
                    nominal_potongan: nominalPotongan,
                    grace_period_late: graceLate,
                    grace_period_early: graceEarly,
                    min_overtime: minOvertime,
                    overtime_type: tipeLembur,
                    lumpsum_nominal: lumpsumNominal,
                    components: components
                });
            }

            if (parsedSchemes.length === 0) {
                if (typeof showToast === 'function') {
                    showToast('Tidak ada skema valid yang ditemukan pada file Excel!', 'warning');
                } else {
                    alert('Tidak ada skema valid yang ditemukan pada file Excel!');
                }
                return;
            }

            const btnSubmit = document.getElementById('btnSubmitUploadedSkemaPayroll');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Mengimpor...`;
            }

            const response = await fetch(`${API_URL}/payroll-schemes/upload-excel`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ schemes: parsedSchemes })
            });

            const result = await response.json();

            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<i class="fas fa-upload"></i> Upload & Import`;
            }

            if (response.ok) {
                tutupModalUploadSkemaPayroll();
                if (typeof showToast === 'function') {
                    showToast(result.message || 'Berhasil mengimpor skema payroll!', 'success');
                } else {
                    alert(result.message || 'Berhasil mengimpor skema payroll!');
                }
                if (typeof renderPayrollSchemes === 'function') {
                    renderPayrollSchemes();
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast(result.message || 'Gagal mengimpor skema payroll.', 'error');
                } else {
                    alert(result.message || 'Gagal mengimpor skema payroll.');
                }
            }
        } catch (err) {
            console.error('Error importing Excel:', err);
            if (typeof showToast === 'function') {
                showToast('Gagal memproses file Excel: ' + err.message, 'error');
            } else {
                alert('Gagal memproses file Excel: ' + err.message);
            }
        }
    };
    reader.readAsArrayBuffer(file);
}
window.importSkemaPayrollExcel = importSkemaPayrollExcel;
