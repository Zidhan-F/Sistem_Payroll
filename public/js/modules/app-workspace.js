// ===== WORKSPACE MODULE =====
// Extracted from app.js for modular monolith architecture

async function populateMinimumWageDropdown(tipe, selectElementId) {
    try {
        const res = await fetch(`${API_URL}/minimum-wages?tipe=${tipe}`);
        const data = await res.json();
        const select = document.getElementById(selectElementId);
        if (select) {
            select.innerHTML = '<option value="">-- Pilih Wilayah --</option>' +
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

async function loadWorkspaceSetup() {
    if (!window.selectedClientId) return;
    try {
        const response = await fetch(`${API_URL}/client-configs`);
        const configs = await response.json();
        const conf = configs.find(c => c.client_id == window.selectedClientId);

        document.getElementById('wSetupClientName').innerText = window.selectedClientName || '-';

        if (conf) {
            let payrollSchemeText = 'Belum Set';
            if (conf.payroll_type === 'UMP') {
                payrollSchemeText = `UMP: ${conf.minimum_wage_region || 'Belum Set'} (Rp ${conf.minimum_wage_nominal ? parseFloat(conf.minimum_wage_nominal).toLocaleString('id-ID') : '-'})`;
            } else if (conf.payroll_type === 'UMK') {
                payrollSchemeText = `UMK: ${conf.minimum_wage_region || 'Belum Set'} (Rp ${conf.minimum_wage_nominal ? parseFloat(conf.minimum_wage_nominal).toLocaleString('id-ID') : '-'})`;
            } else if (conf.payroll_type === 'Nominal') {
                payrollSchemeText = `Nominal: Rp ${conf.custom_nominal ? parseFloat(conf.custom_nominal).toLocaleString('id-ID') : '-'}`;
            } else if (conf.payroll_type === 'Template') {
                payrollSchemeText = conf.payroll_scheme_name || 'Belum Set';
            }
            document.getElementById('wSetupPayrollScheme').innerText = payrollSchemeText;
            document.getElementById('wSetupTaxScheme').innerText = conf.tax_scheme_name || 'Belum Set';
            document.getElementById('wSetupPayDate').innerText = conf.pay_date ? `Tgl ${conf.pay_date}` : 'Belum Set';
            document.getElementById('wSetupCutoff').innerText = conf.cutoff_start ? `${conf.cutoff_start} s/d ${(conf.cutoff_start - 1)}` : 'Belum Set';
        } else {
            document.getElementById('wSetupPayrollScheme').innerText = 'Belum Set';
            document.getElementById('wSetupTaxScheme').innerText = 'Belum Set';
            document.getElementById('wSetupPayDate').innerText = 'Belum Set';
            document.getElementById('wSetupCutoff').innerText = 'Belum Set';
        }
    } catch (err) {
        console.error(err);
    }
}

// ===== 5. SETUP PAYROLL KLIEN =====
async function renderClientSetup() {
    try {
        const response = await fetch(`${API_URL}/client-configs`);
        clientConfigs = await response.json();
        const tbody = document.getElementById('tabelSetupBody');
        if (!tbody) return;
        tbody.innerHTML = clientConfigs.map(conf => {
            let payrollSchemeText = 'Belum Set';
            if (conf.payroll_type === 'UMP') {
                payrollSchemeText = `UMP: ${conf.minimum_wage_region || 'Belum Set'} (Rp ${conf.minimum_wage_nominal ? parseFloat(conf.minimum_wage_nominal).toLocaleString('id-ID') : '-'})`;
            } else if (conf.payroll_type === 'UMK') {
                payrollSchemeText = `UMK: ${conf.minimum_wage_region || 'Belum Set'} (Rp ${conf.minimum_wage_nominal ? parseFloat(conf.minimum_wage_nominal).toLocaleString('id-ID') : '-'})`;
            } else if (conf.payroll_type === 'Nominal') {
                payrollSchemeText = `Nominal: Rp ${conf.custom_nominal ? parseFloat(conf.custom_nominal).toLocaleString('id-ID') : '-'}`;
            } else if (conf.payroll_type === 'Template') {
                payrollSchemeText = conf.payroll_scheme_name || 'Belum Set';
            }
            return `
                <tr>
                    <td style="font-weight: 600;">${conf.client_name}</td>
                    <td><span class="scheme-badge bulanan">${payrollSchemeText}</span></td>
                    <td><span class="scheme-badge" style="background:#e74c3c;">${conf.tax_scheme_name || 'Belum Set'}</span></td>
                    <td>Tgl ${conf.pay_date || '-'}</td>
                    <td>${conf.cutoff_start || '-'}-${(conf.cutoff_start - 1) || '-'}</td>
                    <td>
                        <button class="btn-icon btn-edit" onclick="bukaModalSetup(${conf.client_id}, '${conf.client_name}')"><i class="fas fa-cog"></i></button>
                    </td>
                </tr>
            `;
        }).join('');
    } catch (err) { console.error(err); }
}

async function bukaModalSetup(clientId, clientName) {
    document.getElementById('modalSetup').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('setupClientId').value = clientId;
    document.getElementById('setupClientNama').value = clientName;

    const pRes = await fetch(`${API_URL}/payroll-schemes`);
    const pSchemes = await pRes.json();
    const tRes = await fetch(`${API_URL}/tax-schemes`);
    const tSchemes = await tRes.json();

    document.getElementById('setupPayrollScheme').innerHTML = '<option value="">-- Pilih Skema --</option>' + pSchemes.map(s => `<option value="${s.id}">${s.nama}</option>`).join('');
    document.getElementById('setupTaxScheme').innerHTML = '<option value="">-- Pilih Skema --</option>' + tSchemes.map(s => `<option value="${s.id}">${s.nama}</option>`).join('');

    const current = clientConfigs.find(c => c.client_id == clientId);
    if (current) {
        document.getElementById('setupPayDate').value = current.pay_date || 25;
        document.getElementById('setupCutoffStart').value = current.cutoff_start || 21;

        const tipeSelect = document.getElementById('setupPayrollSchemeTipe');
        if (tipeSelect) {
            tipeSelect.value = current.payroll_type || '';
            await handleSetupPayrollSchemeTipeChange();

            if (current.payroll_type === 'UMP' || current.payroll_type === 'UMK') {
                const wilSelect = document.getElementById('setupPayrollSchemeWilayah');
                if (wilSelect && current.minimum_wage_id) {
                    wilSelect.value = current.minimum_wage_id;
                }
            } else if (current.payroll_type === 'Nominal') {
                const nomInput = document.getElementById('setupPayrollSchemeNominal');
                if (nomInput && current.custom_nominal) {
                    nomInput.value = current.custom_nominal;
                }
            } else if (current.payroll_type === 'Template') {
                const tplSelect = document.getElementById('setupPayrollScheme');
                if (tplSelect && current.payroll_scheme_id) {
                    tplSelect.value = current.payroll_scheme_id;
                }
            }
        }
    } else {
        const tipeSelect = document.getElementById('setupPayrollSchemeTipe');
        if (tipeSelect) {
            tipeSelect.value = '';
            handleSetupPayrollSchemeTipeChange();
        }
    }
}

// formSetup submit handler
if (document.getElementById('formSetup')) {
    document.getElementById('formSetup').addEventListener('submit', async (e) => {
        e.preventDefault();
        const payrollType = document.getElementById('setupPayrollSchemeTipe').value;
        const minimumWageId = document.getElementById('setupPayrollSchemeWilayah').value;
        const customNominal = document.getElementById('setupPayrollSchemeNominal').value;
        const payrollSchemeId = document.getElementById('setupPayrollScheme').value;

        const data = {
            client_id: document.getElementById('setupClientId').value,
            payroll_type: payrollType || null,
            minimum_wage_id: (payrollType === 'UMP' || payrollType === 'UMK') ? (minimumWageId || null) : null,
            custom_nominal: (payrollType === 'Nominal') ? (customNominal || null) : null,
            payroll_scheme_id: (payrollType === 'Template') ? (payrollSchemeId || null) : null,
            tax_scheme_id: document.getElementById('setupTaxScheme').value,
            pay_date: parseInt(document.getElementById('setupPayDate').value),
            cutoff_start: parseInt(document.getElementById('setupCutoffStart').value)
        };
        const res = await fetch(`${API_URL}/client-configs`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (res.ok) {
            tutupSemuaModal();
            showToast('Setup Payroll berhasil disimpan!', 'success');
            if (window.selectedClientId) {
                loadWorkspaceSetup();
            } else {
                renderClientSetup();
            }
        } else {
            showToast('Gagal menyimpan Setup Payroll!', 'error');
        }
    });
}

// ===== PILIHAN SKEMA (Client Workspace Tab) =====
async function renderPilihanKompensasiSummary(schemeId) {
    const summaryDiv = document.getElementById('pilihanKompensasiSummary');
    if (!summaryDiv) return;
    if (!schemeId) {
        summaryDiv.innerHTML = `<p style="text-align: center; color: #94a3b8; font-size: 13px; margin: 0;">Pilih skema komponen di atas untuk melihat detailnya.</p>`;
        return;
    }
    try {
        const res = await fetch(`${API_URL}/compensation-schemes`);
        const schemes = await res.json();
        const scheme = schemes.find(s => s.id == schemeId);
        if (!scheme) {
            summaryDiv.innerHTML = `<p style="text-align: center; color: #94a3b8; font-size: 13px; margin: 0;">Skema tidak ditemukan.</p>`;
            return;
        }

        const comps = scheme.components || [];
        if (comps.length === 0) {
            summaryDiv.innerHTML = `
                <div style="text-align: center; padding: 10px;">
                    <i class="fas fa-info-circle" style="color: #94a3b8; margin-right: 6px;"></i>
                    <span style="color: #94a3b8; font-size: 13px;">Belum ada komponen dalam skema ini.</span>
                </div>`;
            return;
        }

        const pendapatan = comps.filter(c => c.tipe === 'pendapatan');
        const potongan = comps.filter(c => c.tipe === 'potongan');
        const totalPendapatan = pendapatan.reduce((sum, c) => sum + parseFloat(c.nilai || 0), 0);
        const totalPotongan = potongan.reduce((sum, c) => sum + parseFloat(c.nilai || 0), 0);

        summaryDiv.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <span style="font-weight: 600; color: #1e293b; font-size: 14px;"><i class="fas fa-list" style="margin-right: 6px; color: var(--primary-color);"></i> ${comps.length} Komponen</span>
                <div style="display: flex; gap: 12px;">
                    <span style="font-size: 12px; background: #dcfce7; color: #16a34a; padding: 4px 10px; border-radius: 20px; font-weight: 600;">+ ${formatRupiah(totalPendapatan)}</span>
                    <span style="font-size: 12px; background: #fee2e2; color: #dc2626; padding: 4px 10px; border-radius: 20px; font-weight: 600;">- ${formatRupiah(totalPotongan)}</span>
                </div>
            </div>
            ${comps.map(c => `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; font-size: 13px;">
                    <span style="color: #475569;">${c.nama}</span>
                    <span style="font-weight: 600; color: ${c.tipe === 'pendapatan' ? '#16a34a' : '#dc2626'};">
                        ${c.tipe === 'pendapatan' ? '+' : '-'} ${c.is_persentase == 1 ? c.nilai + '%' : formatRupiah(c.nilai)}
                    </span>
                </div>
            `).join('')}
        `;
    } catch (err) {
        console.error(err);
    }
}

let wsClientOrgHierarchy = [];
window.editSchemaMappingId = null;

async function loadSchemaMappingOrgDropdowns() {
    if (!window.selectedClientId) return;
    try {
        const r = await fetch(`${API_URL}/org?client_id=${window.selectedClientId}`);
        wsClientOrgHierarchy = await r.json();

        const divSelect = document.getElementById('pilihanSkemaDivisiId');
        if (divSelect) {
            divSelect.innerHTML = '<option value="">-- Pilih Divisi --</option>';
            if (Array.isArray(wsClientOrgHierarchy)) {
                wsClientOrgHierarchy.forEach(div => {
                    divSelect.innerHTML += `<option value="${div.id}">${div.nama}</option>`;
                });
            }
        }
    } catch (e) { console.error(e); }
}

function handlePilihanSkemaLevelChange() {
    const level = document.getElementById('pilihanSkemaLevel').value;
    document.getElementById('pilihanSkemaDivisiContainer').style.display = (level !== 'general') ? 'block' : 'none';
    document.getElementById('pilihanSkemaDeptContainer').style.display = (level === 'departemen' || level === 'posisi') ? 'block' : 'none';
    document.getElementById('pilihanSkemaPosisiContainer').style.display = (level === 'posisi') ? 'block' : 'none';
}

function initCascadingSkemaDropdowns() {
    const divSelect = document.getElementById('pilihanSkemaDivisiId');
    const deptSelect = document.getElementById('pilihanSkemaDeptId');
    const posSelect = document.getElementById('pilihanSkemaPosisiId');

    if (divSelect) divSelect.onchange = () => {
        const divId = divSelect.value;
        if (deptSelect) deptSelect.innerHTML = '<option value="">-- Pilih Departemen --</option>';
        if (posSelect) posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
        if (divId && Array.isArray(wsClientOrgHierarchy)) {
            const division = wsClientOrgHierarchy.find(d => d.id == divId);
            if (division && Array.isArray(division.departments)) {
                division.departments.forEach(dept => {
                    if (deptSelect) deptSelect.innerHTML += `<option value="${dept.id}">${dept.nama}</option>`;
                });
            }
        }
    };

    if (deptSelect) deptSelect.onchange = () => {
        const divId = divSelect.value;
        const deptId = deptSelect.value;
        if (posSelect) posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
        if (divId && deptId && Array.isArray(wsClientOrgHierarchy)) {
            const division = wsClientOrgHierarchy.find(d => d.id == divId);
            if (division && Array.isArray(division.departments)) {
                const dept = division.departments.find(dp => dp.id == deptId);
                if (dept && Array.isArray(dept.positions)) {
                    dept.positions.forEach(pos => {
                        if (posSelect) posSelect.innerHTML += `<option value="${pos.id}">${pos.nama}</option>`;
                    });
                }
            }
        }
    };
}

async function loadSchemaMappingTable() {
    if (!window.selectedClientId) return;
    try {
        const res = await fetch(`${API_URL}/client-configs-mapping/${window.selectedClientId}`);
        const mappings = await res.json();

        const tbody = document.getElementById('tabelSchemaMappingBody');
        const tbodyKlien = document.getElementById('tabelPilihanSkemaKlien');

        if (mappings && mappings.length > 0) {
            if (tbody) {
                tbody.innerHTML = mappings.map(m => {
                    let levelLabel = '<span class="status-badge warning" style="background:#f1f5f9;color:#475569;">General Klien</span>';
                    if (m.position_id) {
                        levelLabel = `<span class="status-badge" style="background:#e0e7ff;color:#4338ca;">Posisi: ${m.position_name}</span>`;
                    } else if (m.department_id) {
                        levelLabel = `<span class="status-badge" style="background:#fce7f3;color:#be185d;">Dept: ${m.department_name}</span>`;
                    } else if (m.division_id) {
                        levelLabel = `<span class="status-badge" style="background:#dcfce7;color:#15803d;">Divisi: ${m.division_name}</span>`;
                    }

                    return `
                        <tr>
                            <td>${levelLabel}</td>
                            <td>${m.payroll_type || '-'}</td>
                            <td>${m.tax_scheme_name || '-'}</td>
                            <td>${m.compensation_scheme_name || '-'}</td>
                            <td>
                                <button onclick="editSchemaMapping(${m.id})" class="btn-icon" title="Edit" style="color:#3498db;background:transparent;border:none;cursor:pointer;"><i class="fas fa-edit"></i></button>
                                <button onclick="hapusSchemaMapping(${m.id})" class="btn-icon" title="Hapus" style="color:#e74c3c;background:transparent;border:none;cursor:pointer;margin-left:8px;"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            if (tbodyKlien) {
                tbodyKlien.innerHTML = mappings.map(m => {
                    return `
                        <tr>
                            <td style="padding:12px 15px; border-bottom:1px solid #e2e8f0; color:#1e293b;">${m.division_name || '<span style="color:#94a3b8;font-style:italic;">(General)</span>'}</td>
                            <td style="padding:12px 15px; border-bottom:1px solid #e2e8f0; color:#1e293b;">${m.department_name || '<span style="color:#94a3b8;font-style:italic;">(Semua)</span>'}</td>
                            <td style="padding:12px 15px; border-bottom:1px solid #e2e8f0; color:#1e293b;">${m.position_name || '<span style="color:#94a3b8;font-style:italic;">(Semua)</span>'}</td>
                            <td style="padding:12px 15px; border-bottom:1px solid #e2e8f0; color:#1e293b;">${m.payroll_scheme_name || m.payroll_type || '-'}</td>
                            <td style="padding:12px 15px; border-bottom:1px solid #e2e8f0; color:#1e293b;">${m.tax_scheme_name || '-'}</td>
                            <td style="padding:12px 15px; border-bottom:1px solid #e2e8f0;">
                                <div style="display: flex; justify-content: center; align-items: center; gap: 12px;">
                                    <button onclick="editSchemaMapping(${m.id})" class="btn-icon" title="Edit" style="color:#3498db; background:transparent; border:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; box-shadow:none; width:auto; height:auto; padding:4px;"><i class="fas fa-edit" style="font-size:16px;"></i></button>
                                    <button onclick="hapusSchemaMapping(${m.id})" class="btn-icon" title="Delete" style="color:#e74c3c; background:transparent; border:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; box-shadow:none; width:auto; height:auto; padding:4px;"><i class="fas fa-trash" style="font-size:16px;"></i></button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            }
        } else {
            if (tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Belum ada skema yang diatur</td></tr>';
            if (tbodyKlien) tbodyKlien.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align:center; padding: 40px; color: #94a3b8;">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                        Belum ada skema payroll yang terdaftar. Klik tombol "Tambah Skema" untuk mengkonfigurasi.
                    </td>
                </tr>
            `;
        }
    } catch (err) {
        console.error('Error loading mapping table:', err);
    }
}

async function editSchemaMapping(id) {
    try {
        await window.openModalPilihanSkema();

        const res = await fetch(`${API_URL}/client-configs-mapping/${window.selectedClientId}`);
        const mappings = await res.json();
        const conf = mappings.find(m => m.id == id);
        if (!conf) return;

        window.editSchemaMappingId = id;
        document.getElementById('modalPilihanSkemaTitle').innerText = 'Edit Client Scheme';

        if (conf.division_id) {
            const divSelect = document.getElementById('modalPilihanSkemaDivisi');
            divSelect.value = conf.division_id;
            divSelect.dispatchEvent(new Event('change'));

            if (conf.department_id) {
                setTimeout(() => {
                    const deptSelect = document.getElementById('modalPilihanSkemaDepartemen');
                    deptSelect.value = conf.department_id;
                    deptSelect.dispatchEvent(new Event('change'));

                    if (conf.position_id) {
                        setTimeout(() => {
                            const posSelect = document.getElementById('modalPilihanSkemaPosisi');
                            posSelect.value = conf.position_id;
                        }, 100);
                    }
                }, 100);
            }
        }

        if (conf.payroll_scheme_id) {
            document.getElementById('modalPilihanSkemaPayroll').value = conf.payroll_scheme_id;
        }
        if (conf.tax_scheme_id) {
            document.getElementById('modalPilihanSkemaPajak').value = conf.tax_scheme_id;
        }
    } catch (e) { console.error('Error in editSchemaMapping:', e); }
}

async function hapusSchemaMapping(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus mapping skema ini?')) return;
    try {
        const res = await fetch(`${API_URL}/client-configs/${id}`, { method: 'DELETE' });
        if (res.ok) {
            showToast('Mapping skema dihapus', 'success');
            loadSchemaMappingTable();
        } else {
            showToast('Gagal menghapus mapping', 'error');
        }
    } catch (err) {
        console.error(err);
    }
}


async function loadPilihanSkema() {
    if (!window.selectedClientId) return;
    try {
        // Load payroll schemes for dropdown
        const psRes = await fetch(`${API_URL}/payroll-schemes`);
        const payrollSchemes = await psRes.json();
        const psSelect = document.getElementById('pilihanSkemaPayroll');
        if (psSelect) {
            psSelect.innerHTML = '<option value="">-- Pilih Skema Payroll --</option>' +
                payrollSchemes.map(s => `<option value="${s.id}">${s.nama} (${s.tipe || 'Umum'})</option>`).join('');
        }

        // Load tax schemes for dropdown
        const tsRes = await fetch(`${API_URL}/tax-schemes`);
        const taxSchemes = await tsRes.json();
        const tsSelect = document.getElementById('pilihanSkemaPajak');
        if (tsSelect) {
            tsSelect.innerHTML = '<option value="">-- Pilih Skema Pajak --</option>' +
                taxSchemes.map(s => `<option value="${s.id}">${s.nama} (${s.metode || '-'})</option>`).join('');
        }

        // Load global compensation schemes for dropdown
        const compRes = await fetch(`${API_URL}/compensation-schemes`);
        const compensationSchemes = await compRes.json();
        const compSelect = document.getElementById('pilihanSkemaKompensasi');
        if (compSelect) {
            compSelect.innerHTML = '<option value="">-- Pilih Skema Komponen --</option>' +
                compensationSchemes.map(s => `<option value="${s.id}">${s.nama}</option>`).join('');
        }

        // Load current client config to pre-select values
        const cfgRes = await fetch(`${API_URL}/client-configs-mapping/${window.selectedClientId}`);
        const configs = await cfgRes.json();
        const conf = configs.find(c => c.division_id === null && c.department_id === null && c.position_id === null); // Load General as default form
        window.editSchemaMappingId = conf ? conf.id : null;

        const summaryDiv = document.getElementById('pilihanKompensasiSummary');
        if (summaryDiv) {
            summaryDiv.innerHTML = `<p style="text-align: center; color: #94a3b8; font-size: 13px; margin: 0;">Pilih skema komponen di atas untuk melihat detailnya.</p>`;
        }

        if (conf) {
            const tipeSelect = document.getElementById('pilihanSkemaPayrollTipe');
            if (tipeSelect) {
                tipeSelect.value = conf.payroll_type || '';
                await handlePilihanSkemaPayrollTipeChange();

                if (conf.payroll_type === 'UMP' || conf.payroll_type === 'UMK') {
                    const wilSelect = document.getElementById('pilihanSkemaPayrollWilayah');
                    if (wilSelect && conf.minimum_wage_id) {
                        wilSelect.value = conf.minimum_wage_id;
                    }
                } else if (conf.payroll_type === 'Nominal') {
                    const nomInput = document.getElementById('pilihanSkemaPayrollNominal');
                    if (nomInput && conf.custom_nominal) {
                        nomInput.value = conf.custom_nominal;
                    }
                } else if (conf.payroll_type === 'Template') {
                    if (psSelect && conf.payroll_scheme_id) {
                        psSelect.value = conf.payroll_scheme_id;
                    }
                }
            }
            if (tsSelect && conf.tax_scheme_id) tsSelect.value = conf.tax_scheme_id;
            if (compSelect && conf.compensation_scheme_id) {
                compSelect.value = conf.compensation_scheme_id;
                renderPilihanKompensasiSummary(conf.compensation_scheme_id);
            }
        } else {
            const tipeSelect = document.getElementById('pilihanSkemaPayrollTipe');
            if (tipeSelect) {
                tipeSelect.value = '';
                handlePilihanSkemaPayrollTipeChange();
            }
        }
        await loadAbsenConfig();

        await loadSchemaMappingOrgDropdowns();
        initCascadingSkemaDropdowns();
        handlePilihanSkemaLevelChange();
        await loadSchemaMappingTable();
    } catch (err) {
        console.error('Error loading pilihan skema:', err);
    }
}

async function simpanPilihanSkema() {
    if (!window.selectedClientId) {
        showToast('Pilih klien terlebih dahulu!', 'error');
        return;
    }

    const isModal = document.getElementById('modalPilihanSkema') && document.getElementById('modalPilihanSkema').style.display === 'block';

    let payrollType, minimumWageId, customNominal, payrollSchemeId, taxSchemeId, compSchemeId;
    let level, divId, deptId, posId;

    if (isModal) {
        payrollType = 'Template';
        payrollSchemeId = document.getElementById('modalPilihanSkemaPayroll').value;
        taxSchemeId = document.getElementById('modalPilihanSkemaPajak').value;
        compSchemeId = '';
        divId = document.getElementById('modalPilihanSkemaDivisi').value;
        deptId = document.getElementById('modalPilihanSkemaDepartemen').value;
        posId = document.getElementById('modalPilihanSkemaPosisi').value;

        if (posId) level = 'posisi';
        else if (deptId) level = 'departemen';
        else if (divId) level = 'divisi';
        else level = 'general';

    } else {
        payrollType = document.getElementById('pilihanSkemaPayrollTipe').value;
        minimumWageId = document.getElementById('pilihanSkemaPayrollWilayah').value;
        customNominal = document.getElementById('pilihanSkemaPayrollNominal').value;
        payrollSchemeId = document.getElementById('pilihanSkemaPayroll').value;
        taxSchemeId = document.getElementById('pilihanSkemaPajak').value;
        compSchemeId = document.getElementById('pilihanSkemaKompensasi').value;

        level = document.getElementById('pilihanSkemaLevel').value;
        divId = document.getElementById('pilihanSkemaDivisiId')?.value;
        deptId = document.getElementById('pilihanSkemaDeptId')?.value;
        posId = document.getElementById('pilihanSkemaPosisiId')?.value;
    }

    if (!payrollType && !taxSchemeId && !compSchemeId) {
        showToast('Pilih minimal satu skema!', 'error');
        return;
    }

    try {
        // Load existing general config to preserve pay_date and cutoff
        const cfgRes = await fetch(`${API_URL}/client-configs-mapping/${window.selectedClientId}`);
        const configs = await cfgRes.json();
        const generalExisting = configs.find(c => c.division_id === null && c.department_id === null && c.position_id === null);

        const data = {
            client_id: window.selectedClientId,
            payroll_type: payrollType || null,
            minimum_wage_id: (payrollType === 'UMP' || payrollType === 'UMK') ? (minimumWageId || null) : null,
            custom_nominal: (payrollType === 'Nominal') ? (customNominal || null) : null,
            payroll_scheme_id: (payrollType === 'Template') ? (payrollSchemeId || null) : null,
            tax_scheme_id: taxSchemeId || (generalExisting ? generalExisting.tax_scheme_id : null),
            compensation_scheme_id: compSchemeId || (generalExisting ? generalExisting.compensation_scheme_id : null),
            pay_date: generalExisting ? generalExisting.pay_date : 25,
            cutoff_start: generalExisting ? generalExisting.cutoff_start : 21,
            cutoff_end: generalExisting ? generalExisting.cutoff_end : 20,

            // Add org hierarchy based on selected level
            division_id: (level !== 'general' && divId) ? divId : null,
            department_id: ((level === 'departemen' || level === 'posisi') && deptId) ? deptId : null,
            position_id: (level === 'posisi' && posId) ? posId : null
        };

        if (window.editSchemaMappingId) {
            data.id = window.editSchemaMappingId;
        }

        const res = await fetch(`${API_URL}/client-configs`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (res.ok) {
            if (!isModal) {
                await simpanKonfigAbsen(false);
            }
            showToast('Pilihan skema berhasil disimpan!', 'success');
            window.editSchemaMappingId = null;
            if (isModal) {
                tutupModalPilihanSkema();
            } else {
                document.getElementById('pilihanSkemaLevel').value = 'general';
                handlePilihanSkemaLevelChange();
            }
            if (typeof loadPilihanSkemaKlienTable === 'function') {
                loadPilihanSkemaKlienTable(); // reload friend's UI
            }
            await loadSchemaMappingTable();
            // Also update the Setup Payroll tab data
            loadWorkspaceSetup();
        } else {
            let errorMsg = 'Gagal menyimpan pilihan skema!';
            try {
                const d = await res.json();
                if (d.messages && d.messages.error) {
                    errorMsg = d.messages.error;
                } else if (d.message) {
                    errorMsg = d.message;
                }
            } catch (e) {
                // Not JSON, fallback to generic error
            }
            showToast(errorMsg, 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Gagal menyimpan pilihan skema!', 'error');
    }
}

window.openModalPilihanSkema = async function () {
    if (!window.selectedClientId) {
        showToast('Pilih klien terlebih dahulu!', 'error');
        return;
    }

    // Reset any previous edit state
    window.editSchemaMappingId = null;

    document.getElementById('modalPilihanSkemaTitle').innerText = 'Tambah Skema Client';
    document.getElementById('formPilihanSkema').reset();

    // Set client name
    const titleEl = document.getElementById('clientWorkspaceTitle');
    if (titleEl) {
        const clientName = titleEl.innerText.replace('🏢 ', '');
        document.getElementById('modalPilihanSkemaNamaKlien').value = clientName;
    }

    // Populate divisions
    const divSelect = document.getElementById('modalPilihanSkemaDivisi');
    try {
        const orgRes = await fetch(`${API_URL}/org?client_id=${window.selectedClientId}`);
        const orgData = await orgRes.json();
        window.clientOrgData = orgData;

        divSelect.innerHTML = '<option value="">-- Pilih Divisi --</option>' +
            orgData.map(d => `<option value="${d.id}">${d.nama}</option>`).join('');

        divSelect.onchange = function () {
            const deptSelect = document.getElementById('modalPilihanSkemaDepartemen');
            const posSelect = document.getElementById('modalPilihanSkemaPosisi');
            deptSelect.innerHTML = '<option value="">-- Pilih Departemen --</option>';
            posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
            if (!this.value) return;
            const div = window.clientOrgData.find(d => d.id == this.value);
            if (div && div.departments) {
                deptSelect.innerHTML += div.departments.map(dep => `<option value="${dep.id}">${dep.nama}</option>`).join('');
            }
        };

        const deptSelect = document.getElementById('modalPilihanSkemaDepartemen');
        deptSelect.onchange = function () {
            const posSelect = document.getElementById('modalPilihanSkemaPosisi');
            posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
            if (!this.value) return;
            const div = window.clientOrgData.find(d => d.id == document.getElementById('modalPilihanSkemaDivisi').value);
            if (div && div.departments) {
                const dep = div.departments.find(dp => dp.id == this.value);
                if (dep && dep.positions) {
                    posSelect.innerHTML += dep.positions.map(p => `<option value="${p.id}">${p.nama}</option>`).join('');
                }
            }
        };
    } catch (e) {
        console.error('Error fetching org data', e);
    }

    // Check if we need to copy to modal dropdowns
    try {
        const psRes = await fetch(`${API_URL}/payroll-schemes`);
        const payrollSchemes = await psRes.json();
        const psSelect = document.getElementById('modalPilihanSkemaPayroll');
        if (psSelect) {
            psSelect.innerHTML = '<option value="">-- Pilih Skema Payroll --</option>' +
                payrollSchemes.map(s => `<option value="${s.id}">${s.nama} (${s.tipe || 'Umum'})</option>`).join('');
        }

        const tsRes = await fetch(`${API_URL}/tax-schemes`);
        const taxSchemes = await tsRes.json();
        const tsSelect = document.getElementById('modalPilihanSkemaPajak');
        if (tsSelect) {
            tsSelect.innerHTML = '<option value="">-- Pilih Skema Pajak --</option>' +
                taxSchemes.map(s => `<option value="${s.id}">${s.nama}</option>`).join('');
        }
    } catch (e) {
        console.error('Error fetching schemes for modal', e);
    }

    document.getElementById('modalPilihanSkema').style.display = 'block';
};

window.tutupModalPilihanSkema = function () {
    document.getElementById('modalPilihanSkema').style.display = 'none';
};

function goToMasterKompensasi() {
    switchView('masterKompensasi');
    renderMasterKompensasi();
}

// ===== 9. KONFIGURASI ABSEN =====
async function loadAbsenConfig() {
    if (!window.selectedClientId) return;
    try {
        const res = await fetch(`${API_URL}/client-absence-config/${window.selectedClientId}`);
        const data = await res.json();
        if (data && data.id) {
            document.getElementById('cfgProrate').checked = data.prorate == 1;
            document.getElementById('cfgAbsenTidakPotong').checked = data.absen_tidak_potong == 1;
            const nominalInput = document.getElementById('cfgNominalPotongan');
            if (nominalInput) {
                const val = data.nominal_potongan ? parseFloat(data.nominal_potongan) : 0;
                nominalInput.value = val > 0 ? new Intl.NumberFormat('id-ID').format(val) : '';
            }
        } else {
            document.getElementById('cfgProrate').checked = false;
            document.getElementById('cfgAbsenTidakPotong').checked = false;
            const nominalInput = document.getElementById('cfgNominalPotongan');
            if (nominalInput) nominalInput.value = '';
        }
    } catch (err) { console.error(err); }
}

async function simpanKonfigAbsen(showToastOnSuccess = true) {
    if (!window.selectedClientId) return;
    const nominalRaw = document.getElementById('cfgNominalPotongan').value;
    const nominalClean = parseFloat(nominalRaw.replace(/\./g, '').replace(/,/g, '.')) || 0;

    const data = {
        client_id: window.selectedClientId,
        prorate: document.getElementById('cfgProrate').checked ? 1 : 0,
        absen_tidak_potong: document.getElementById('cfgAbsenTidakPotong').checked ? 1 : 0,
        nominal_potongan: nominalClean
    };
    try {
        const res = await fetch(`${API_URL}/client-absence-config`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (res.ok && showToastOnSuccess) { showToast('Konfigurasi absen berhasil disimpan!', 'success'); }
    } catch (err) { console.error(err); if (showToastOnSuccess) showToast('Gagal menyimpan konfigurasi', 'error'); }
}
