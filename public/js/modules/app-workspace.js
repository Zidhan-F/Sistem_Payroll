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
                        ${c.tipe === 'pendapatan' ? '+' : '-'} ${c.is_persentase == 1 ? parseFloat(c.nilai) + '%' : formatRupiah(c.nilai)}
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
        if(divSelect) {
            divSelect.innerHTML = '<option value="">-- Pilih Divisi --</option>';
            if (Array.isArray(wsClientOrgHierarchy)) {
                wsClientOrgHierarchy.forEach(div => {
                    divSelect.innerHTML += `<option value="${div.id}">${div.nama}</option>`;
                });
            }
        }
    } catch(e) { console.error(e); }
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
    
    if(divSelect) divSelect.onchange = () => {
        const divId = divSelect.value;
        if(deptSelect) deptSelect.innerHTML = '<option value="">-- Pilih Departemen --</option>';
        if(posSelect) posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
        if (divId && Array.isArray(wsClientOrgHierarchy)) {
            const division = wsClientOrgHierarchy.find(d => d.id == divId);
            if (division && Array.isArray(division.departments)) {
                division.departments.forEach(dept => {
                    if(deptSelect) deptSelect.innerHTML += `<option value="${dept.id}">${dept.nama}</option>`;
                });
            }
        }
    };
    
    if(deptSelect) deptSelect.onchange = () => {
        const divId = divSelect.value;
        const deptId = deptSelect.value;
        if(posSelect) posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
        if (divId && deptId && Array.isArray(wsClientOrgHierarchy)) {
            const division = wsClientOrgHierarchy.find(d => d.id == divId);
            if (division && Array.isArray(division.departments)) {
                const dept = division.departments.find(dp => dp.id == deptId);
                if (dept && Array.isArray(dept.positions)) {
                    dept.positions.forEach(pos => {
                        if(posSelect) posSelect.innerHTML += `<option value="${pos.id}">${pos.nama}</option>`;
                    });
                }
            }
        }
    };
}

async function loadSchemaMappingTable() {
    if (!window.selectedClientId) return;
    try {
        const tbody = document.getElementById('tabelPilihanSkemaKlien');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        const res = await fetch(`${API_URL}/client-configs-mapping/${window.selectedClientId}`);
        const mappings = await res.json();
        
        if (!tbody) return;
        
        if (!mappings || mappings.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 40px; color: #94a3b8;"><i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>No payroll schemes registered yet. Click the "Add Scheme" button to configure.</td></tr>';
            return;
        }
        
        tbody.innerHTML = mappings.map(m => {
            return `
                <tr>
                    <td style="padding:12px 15px; border-bottom:1px solid #e2e8f0; color:#1e293b;">${m.division_name || 'Global'}</td>
                    <td style="padding:12px 15px; border-bottom:1px solid #e2e8f0; color:#1e293b;">${m.department_name || 'Global'}</td>
                    <td style="padding:12px 15px; border-bottom:1px solid #e2e8f0; color:#1e293b;">${m.position_name || 'Global'}</td>
                    <td style="padding:12px 15px; border-bottom:1px solid #e2e8f0; color:#1e293b;">${m.payroll_scheme_name || m.payroll_type || '-'}</td>
                    <td style="padding:12px 15px; border-bottom:1px solid #e2e8f0; color:#1e293b;">${m.bpjs_scheme_name || '-'}</td>
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
    } catch (err) {
        console.error('Error loading mapping table:', err);
    }
}

async function editSchemaMapping(id) {
    try {
        window.editSchemaMappingId = id;
        await window.openModalPilihanSkema(true);
        
        const res = await fetch(`${API_URL}/client-configs-mapping/${window.selectedClientId}`);
        const mappings = await res.json();
        const conf = mappings.find(m => m.id == id);
        if (!conf) return;
        
        document.getElementById('modalPilihanSkemaTitle').innerText = 'Edit Client Scheme';
        
        // Use TomSelect setValue for org structure dropdowns
        const divEl = document.getElementById('modalPilihanSkemaDivisi');
        const deptEl = document.getElementById('modalPilihanSkemaDepartemen');
        const posEl = document.getElementById('modalPilihanSkemaPosisi');
        const psEl = document.getElementById('modalPilihanSkemaPayroll');
        const bpjsEl = document.getElementById('modalPilihanSkemaBpjs');
        const tsEl = document.getElementById('modalPilihanSkemaPajak');

        if (conf.division_id && divEl && divEl.tomselect) {
            divEl.tomselect.setValue(conf.division_id);
        }
        if (conf.department_id && deptEl && deptEl.tomselect) {
            deptEl.tomselect.setValue(conf.department_id);
        }
        if (conf.position_id && posEl && posEl.tomselect) {
            posEl.tomselect.setValue(conf.position_id);
        }
        if (conf.payroll_scheme_id && psEl && psEl.tomselect) {
            psEl.tomselect.setValue(conf.payroll_scheme_id);
        }
        if (conf.bpjs_scheme_id && bpjsEl) {
            bpjsEl.value = conf.bpjs_scheme_id;
            window.handleModalPilihanSkemaBpjsChange(conf.bpjs_scheme_id);
        }
        if (conf.tax_scheme_id && tsEl && tsEl.tomselect) {
            tsEl.tomselect.setValue(conf.tax_scheme_id);
        }
    } catch(e) { console.error('Error in editSchemaMapping:', e); }
}

async function hapusSchemaMapping(id) {
    if (!await showConfirm('Apakah Anda yakin ingin menghapus mapping skema ini?')) return;
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
        
        const bpjsOpts = taxSchemes.filter(s => s.tipe === 'bpjs');
        const taxOpts = taxSchemes.filter(s => s.tipe === 'pph21');

        const bpjsSelect = document.getElementById('pilihanSkemaBpjs');
        if (bpjsSelect) {
            bpjsSelect.innerHTML = '<option value="">-- Pilih Skema BPJS --</option>' +
                bpjsOpts.map(s => `<option value="${s.id}">${s.nama}</option>`).join('');
        }

        const tsSelect = document.getElementById('pilihanSkemaPajak');
        if (tsSelect) {
            tsSelect.innerHTML = '<option value="">-- Pilih Skema Pajak --</option>' +
                taxOpts.map(s => `<option value="${s.id}">${s.nama} (${s.metode || '-'})</option>`).join('');
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
            if (bpjsSelect && conf.bpjs_scheme_id) bpjsSelect.value = conf.bpjs_scheme_id;
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
        try { await loadAbsenConfig(); } catch(e) { console.warn('loadAbsenConfig skipped:', e); }
        
        try {
            await loadSchemaMappingOrgDropdowns();
            initCascadingSkemaDropdowns();
            handlePilihanSkemaLevelChange();
        } catch(e) { console.warn('Org dropdowns init skipped:', e); }
    } catch (err) {
        console.error('Error loading pilihan skema:', err);
    }
    // Always load the mapping table, even if form setup fails
    await loadSchemaMappingTable();
}

window.simpanPilihanSkema = async function() {
    if (!window.selectedClientId) {
        showToast('Pilih klien terlebih dahulu!', 'error');
        return;
    }
    
    const isModal = document.getElementById('modalPilihanSkema') && document.getElementById('modalPilihanSkema').style.display === 'block';
    
    let payrollType, minimumWageId, customNominal, payrollSchemeId, bpjsSchemeId, taxSchemeId, compSchemeId;
    let level, divId, deptId, posId;
    
    if (isModal) {
        payrollType = 'Template';
        payrollSchemeId = document.getElementById('modalPilihanSkemaPayroll').value;
        bpjsSchemeId = document.getElementById('modalPilihanSkemaBpjs').value;
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
        bpjsSchemeId = document.getElementById('pilihanSkemaBpjs')?.value || null;
        taxSchemeId = document.getElementById('pilihanSkemaPajak').value;
        compSchemeId = document.getElementById('pilihanSkemaKompensasi').value;
        
        level = document.getElementById('pilihanSkemaLevel').value;
        divId = document.getElementById('pilihanSkemaDivisiId')?.value;
        deptId = document.getElementById('pilihanSkemaDeptId')?.value;
        posId = document.getElementById('pilihanSkemaPosisiId')?.value;
    }

    if (!payrollType && !bpjsSchemeId && !taxSchemeId && !compSchemeId) {
        showToast('Pilih minimal satu skema!', 'error');
        return;
    }

    try {
        if (isModal && bpjsSchemeId) {
            // Read active states and fallback to base rates or 0
            const base = window.modalClientBpjsBaseRates || {};
            const currentValues = {
                bpjs_kes_karyawan: document.getElementById('mClientBpjsKesActive').checked ? (base.bpjs_kes_karyawan || 0) : 0,
                bpjs_kes_perusahaan: document.getElementById('mClientBpjsKesActive').checked ? (base.bpjs_kes_perusahaan || 0) : 0,
                bpjs_kes_max_salary: document.getElementById('mClientBpjsKesActive').checked ? (base.bpjs_kes_max_salary || 0) : 0,
                bpjs_jht_karyawan: document.getElementById('mClientBpjsJhtActive').checked ? (base.bpjs_jht_karyawan || 0) : 0,
                bpjs_jht_perusahaan: document.getElementById('mClientBpjsJhtActive').checked ? (base.bpjs_jht_perusahaan || 0) : 0,
                bpjs_jp_karyawan: document.getElementById('mClientBpjsJpActive').checked ? (base.bpjs_jp_karyawan || 0) : 0,
                bpjs_jp_perusahaan: document.getElementById('mClientBpjsJpActive').checked ? (base.bpjs_jp_perusahaan || 0) : 0,
                bpjs_jp_max_salary: document.getElementById('mClientBpjsJpActive').checked ? (base.bpjs_jp_max_salary || 0) : 0,
                bpjs_jkk_perusahaan: document.getElementById('mClientBpjsJkkActive').checked ? (base.bpjs_jkk_perusahaan || 0) : 0,
                bpjs_jkm_perusahaan: document.getElementById('mClientBpjsJkmActive').checked ? (base.bpjs_jkm_perusahaan || 0) : 0
            };

            const original = window.modalClientBpjsOriginalValues;
            const isModified = !original ||
                original.bpjs_kes_karyawan !== currentValues.bpjs_kes_karyawan ||
                original.bpjs_kes_perusahaan !== currentValues.bpjs_kes_perusahaan ||
                original.bpjs_kes_max_salary !== currentValues.bpjs_kes_max_salary ||
                original.bpjs_jht_karyawan !== currentValues.bpjs_jht_karyawan ||
                original.bpjs_jht_perusahaan !== currentValues.bpjs_jht_perusahaan ||
                original.bpjs_jp_karyawan !== currentValues.bpjs_jp_karyawan ||
                original.bpjs_jp_perusahaan !== currentValues.bpjs_jp_perusahaan ||
                original.bpjs_jp_max_salary !== currentValues.bpjs_jp_max_salary ||
                original.bpjs_jkk_perusahaan !== currentValues.bpjs_jkk_perusahaan ||
                original.bpjs_jkm_perusahaan !== currentValues.bpjs_jkm_perusahaan;

            if (bpjsSchemeId === 'tambah_skema' || isModified) {
                // Find current scheme
                const selectedScheme = (window.workspaceBpjsSchemes || []).find(s => s.id == bpjsSchemeId);
                const isAlreadyCustom = selectedScheme && selectedScheme.nama && selectedScheme.nama.startsWith('Custom BPJS -');
                
                if (isAlreadyCustom && bpjsSchemeId !== 'tambah_skema') {
                    // Update in place
                    try {
                        const putRes = await fetch(`${API_URL}/tax-schemes/${bpjsSchemeId}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                nama: selectedScheme.nama,
                                tipe: 'bpjs',
                                ...currentValues
                            })
                        });
                        if (!putRes.ok) {
                            console.error('Failed to update custom BPJS scheme');
                        }
                    } catch (err) {
                        console.error('Error updating custom BPJS scheme:', err);
                    }
                } else {
                    // Create a new custom BPJS scheme
                    const clientName = document.getElementById('modalPilihanSkemaNamaKlien').value || 'Client';
                    const divEl = document.getElementById('modalPilihanSkemaDivisi');
                    const deptEl = document.getElementById('modalPilihanSkemaDepartemen');
                    const posEl = document.getElementById('modalPilihanSkemaPosisi');
                    const divText = divEl && divEl.tomselect ? divEl.tomselect.getItem(divEl.value)?.textContent?.trim() : '';
                    const deptText = deptEl && deptEl.tomselect ? deptEl.tomselect.getItem(deptEl.value)?.textContent?.trim() : '';
                    const posText = posEl && posEl.tomselect ? posEl.tomselect.getItem(posEl.value)?.textContent?.trim() : '';

                    let orgInfo = 'General';
                    if (posText) orgInfo = `Posisi: ${posText}`;
                    else if (deptText) orgInfo = `Departemen: ${deptText}`;
                    else if (divText) orgInfo = `Divisi: ${divText}`;

                    const customName = `Custom BPJS - ${clientName} (${orgInfo})`;
                    
                    try {
                        const postRes = await fetch(`${API_URL}/tax-schemes`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                nama: customName,
                                tipe: 'bpjs',
                                ...currentValues
                            })
                        });
                        if (postRes.ok) {
                            const resData = await postRes.json();
                            if (resData && resData.id) {
                                bpjsSchemeId = resData.id;
                            }
                        } else {
                            console.error('Failed to create custom BPJS scheme');
                        }
                    } catch (err) {
                        console.error('Error creating custom BPJS scheme:', err);
                    }
                }
            }
        }

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
            bpjs_scheme_id: bpjsSchemeId || (generalExisting ? generalExisting.bpjs_scheme_id : null),
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

        } else {
            const d = await res.json();
            let msg = 'Gagal menyimpan pilihan skema!';
            if (d.messages && d.messages.error) msg = d.messages.error;
            else if (d.message) msg = d.message;
            showToast(msg, 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Gagal menyimpan pilihan skema!', 'error');
    }
}

window.openModalPilihanSkema = async function(isEdit = false) {
    if (!window.selectedClientId) {
        showToast('Pilih klien terlebih dahulu!', 'error');
        return;
    }
    if (!isEdit) {
        window.editSchemaMappingId = null;
    }
    
    document.getElementById('modalPilihanSkemaTitle').innerText = isEdit ? 'Edit Client Scheme' : 'Tambah Skema Client';
    document.getElementById('formPilihanSkema').reset();
    
    // Set client name
    const titleEl = document.getElementById('clientWorkspaceTitle');
    if (titleEl) {
        const clientName = titleEl.innerText.replace('🏢 ', '');
        document.getElementById('modalPilihanSkemaNamaKlien').value = clientName;
    }
    
    // Show modal IMMEDIATELY
    document.getElementById('modalPilihanSkema').style.display = 'block';
    
    // Get all select elements
    const divSelect = document.getElementById('modalPilihanSkemaDivisi');
    const deptSelect = document.getElementById('modalPilihanSkemaDepartemen');
    const posSelect = document.getElementById('modalPilihanSkemaPosisi');
    const psSelect = document.getElementById('modalPilihanSkemaPayroll');
    const bpjsSelect = document.getElementById('modalPilihanSkemaBpjs');
    const tsSelect = document.getElementById('modalPilihanSkemaPajak');

    // Immediately show default BPJS fields and values if we are not editing
    if (!isEdit) {
        if (bpjsSelect) {
            bpjsSelect.value = 'tambah_skema';
        }
        window.handleModalPilihanSkemaBpjsChange('tambah_skema');
    }

    // Destroy existing TomSelect instances before repopulating (excluding bpjsSelect)
    [divSelect, deptSelect, posSelect, psSelect, tsSelect].forEach(el => {
        if (el && el.tomselect) el.tomselect.destroy();
    });

    try {
        // Fetch Global STO data and schemes in parallel
        const [divRes, deptRes, posRes, psRes, tsRes] = await Promise.all([
            fetch(`${API_URL}/global-divisions`),
            fetch(`${API_URL}/global-departments`),
            fetch(`${API_URL}/global-positions`),
            fetch(`${API_URL}/payroll-schemes`),
            fetch(`${API_URL}/tax-schemes`)
        ]);
        const globalDivisions = await divRes.json();
        const globalDepartments = await deptRes.json();
        const globalPositions = await posRes.json();
        const payrollSchemes = await psRes.json();
        const taxSchemes = await tsRes.json();

        // Populate payroll schemes
        if (psSelect) {
            psSelect.innerHTML = '<option value="">-- Pilih Skema Payroll --</option>' +
                payrollSchemes.map(s => `<option value="${s.id}">${s.nama} (${s.tipe || 'Umum'})</option>`).join('');
        }
        
        // Filter tax schemes
        const bpjsOpts = taxSchemes.filter(s => s.tipe === 'bpjs');
        const taxOpts = taxSchemes.filter(s => s.tipe === 'pph21');

        window.workspaceBpjsSchemes = bpjsOpts;

        // Populate bpjs schemes
        if (bpjsSelect) {
            const currentVal = bpjsSelect.value;
            bpjsSelect.innerHTML = '<option value="tambah_skema">Tambah Skema</option>' +
                bpjsOpts.map(s => `<option value="${s.id}">${s.nama}</option>`).join('');
            bpjsSelect.value = currentVal;
        }
        // Populate tax schemes
        if (tsSelect) {
            tsSelect.innerHTML = '<option value="">-- Pilih Skema Pajak --</option>' +
                taxOpts.map(s => `<option value="${s.id}">${s.nama}</option>`).join('');
        }

        // Populate divisions from Global STO
        if (divSelect) {
            divSelect.innerHTML = '<option value="">-- Pilih Divisi --</option>' +
                globalDivisions.map(d => `<option value="${d.id}">${d.nama}</option>`).join('');
        }
        // Populate departments from Global STO
        if (deptSelect) {
            deptSelect.innerHTML = '<option value="">-- Pilih Departemen --</option>' +
                globalDepartments.map(d => `<option value="${d.id}">${d.nama}</option>`).join('');
        }
        // Populate positions from Global STO
        if (posSelect) {
            posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>' +
                globalPositions.map(p => `<option value="${p.id}">${p.nama}</option>`).join('');
        }

        // Initialize all TomSelects (excluding bpjsSelect)
        [psSelect, tsSelect, divSelect, deptSelect, posSelect].forEach(el => {
            if (el) {
                new TomSelect(el, { create: false, sortField: { field: 'text', direction: 'asc' } });
            }
        });

        // Trigger change handler if not editing to ensure fields are fresh
        if (!isEdit && bpjsSelect && bpjsSelect.value) {
            window.handleModalPilihanSkemaBpjsChange(bpjsSelect.value);
        }

    } catch (e) {
        console.error('Error loading modal data:', e);
    }
};window.handleModalPilihanSkemaBpjsChange = function(value) {
    const fieldsDiv = document.getElementById('modalClientBpjsOverrideFields');
    if (!fieldsDiv) return;

    if (!value) {
        fieldsDiv.style.display = 'none';
        window.modalClientBpjsOriginalValues = null;
        window.modalClientBpjsBaseRates = null;
        return;
    }

    const schemes = window.workspaceBpjsSchemes || [];
    let baseScheme = null;

    if (value !== 'tambah_skema') {
        const selected = schemes.find(s => s.id == value);
        if (selected) {
            const isAlreadyCustom = selected.nama && selected.nama.startsWith('Custom BPJS -');
            if (isAlreadyCustom) {
                // Find a non-custom scheme to use as base rates reference
                baseScheme = schemes.find(s => !s.nama || !s.nama.startsWith('Custom BPJS -'));
            } else {
                baseScheme = selected;
            }
        }
    }

    // Resolve base rates (defaulting to standard BPJS rates if not found)
    const baseRates = {
        bpjs_kes_karyawan: baseScheme ? parseFloat(baseScheme.bpjs_kes_karyawan) : 1.00,
        bpjs_kes_perusahaan: baseScheme ? parseFloat(baseScheme.bpjs_kes_perusahaan) : 4.00,
        bpjs_kes_max_salary: baseScheme ? parseFloat(baseScheme.bpjs_kes_max_salary) : 12000000,
        bpjs_jht_karyawan: baseScheme ? parseFloat(baseScheme.bpjs_jht_karyawan) : 2.00,
        bpjs_jht_perusahaan: baseScheme ? parseFloat(baseScheme.bpjs_jht_perusahaan) : 3.70,
        bpjs_jp_karyawan: baseScheme ? parseFloat(baseScheme.bpjs_jp_karyawan) : 1.00,
        bpjs_jp_perusahaan: baseScheme ? parseFloat(baseScheme.bpjs_jp_perusahaan) : 2.00,
        bpjs_jp_max_salary: baseScheme ? parseFloat(baseScheme.bpjs_jp_max_salary) : 10024600,
        bpjs_jkk_perusahaan: baseScheme ? parseFloat(baseScheme.bpjs_jkk_perusahaan) : 0.24,
        bpjs_jkm_perusahaan: baseScheme ? parseFloat(baseScheme.bpjs_jkm_perusahaan) : 0.30
    };
    window.modalClientBpjsBaseRates = baseRates;

    // Format helper
    const formatRupiah = (val) => {
        if (!val) return 'Rp 0';
        return 'Rp ' + parseFloat(val).toLocaleString('id-ID');
    };

    // Update label descriptions
    document.getElementById('mClientBpjsKesDesc').innerText = `Rate: Karyawan ${parseFloat(baseRates.bpjs_kes_karyawan)}%, Perusahaan ${parseFloat(baseRates.bpjs_kes_perusahaan)}% (Max: ${formatRupiah(baseRates.bpjs_kes_max_salary)})`;
    document.getElementById('mClientBpjsJhtDesc').innerText = `Rate: Karyawan ${parseFloat(baseRates.bpjs_jht_karyawan)}%, Perusahaan ${parseFloat(baseRates.bpjs_jht_perusahaan)}%`;
    document.getElementById('mClientBpjsJpDesc').innerText = `Rate: Karyawan ${parseFloat(baseRates.bpjs_jp_karyawan)}%, Perusahaan ${parseFloat(baseRates.bpjs_jp_perusahaan)}% (Max: ${formatRupiah(baseRates.bpjs_jp_max_salary)})`;
    document.getElementById('mClientBpjsJkkDesc').innerText = `Rate: Perusahaan ${parseFloat(baseRates.bpjs_jkk_perusahaan)}%`;
    document.getElementById('mClientBpjsJkmDesc').innerText = `Rate: Perusahaan ${parseFloat(baseRates.bpjs_jkm_perusahaan)}%`;

    // Show override fields
    fieldsDiv.style.display = 'flex';

    // Populate active states
    const selectedScheme = schemes.find(s => s.id == value);
    if (selectedScheme) {
        document.getElementById('mClientBpjsKesActive').checked = parseFloat(selectedScheme.bpjs_kes_karyawan) > 0 || parseFloat(selectedScheme.bpjs_kes_perusahaan) > 0;
        document.getElementById('mClientBpjsJhtActive').checked = parseFloat(selectedScheme.bpjs_jht_karyawan) > 0 || parseFloat(selectedScheme.bpjs_jht_perusahaan) > 0;
        document.getElementById('mClientBpjsJpActive').checked = parseFloat(selectedScheme.bpjs_jp_karyawan) > 0 || parseFloat(selectedScheme.bpjs_jp_perusahaan) > 0;
        document.getElementById('mClientBpjsJkkActive').checked = parseFloat(selectedScheme.bpjs_jkk_perusahaan) > 0;
        document.getElementById('mClientBpjsJkmActive').checked = parseFloat(selectedScheme.bpjs_jkm_perusahaan) > 0;

        // Store original values matching database state
        window.modalClientBpjsOriginalValues = {
            bpjs_kes_karyawan: (parseFloat(selectedScheme.bpjs_kes_karyawan) > 0 || parseFloat(selectedScheme.bpjs_kes_perusahaan) > 0) ? parseFloat(selectedScheme.bpjs_kes_karyawan) : 0,
            bpjs_kes_perusahaan: (parseFloat(selectedScheme.bpjs_kes_karyawan) > 0 || parseFloat(selectedScheme.bpjs_kes_perusahaan) > 0) ? parseFloat(selectedScheme.bpjs_kes_perusahaan) : 0,
            bpjs_kes_max_salary: (parseFloat(selectedScheme.bpjs_kes_karyawan) > 0 || parseFloat(selectedScheme.bpjs_kes_perusahaan) > 0) ? parseFloat(selectedScheme.bpjs_kes_max_salary) : 0,
            bpjs_jht_karyawan: (parseFloat(selectedScheme.bpjs_jht_karyawan) > 0 || parseFloat(selectedScheme.bpjs_jht_perusahaan) > 0) ? parseFloat(selectedScheme.bpjs_jht_karyawan) : 0,
            bpjs_jht_perusahaan: (parseFloat(selectedScheme.bpjs_jht_karyawan) > 0 || parseFloat(selectedScheme.bpjs_jht_perusahaan) > 0) ? parseFloat(selectedScheme.bpjs_jht_perusahaan) : 0,
            bpjs_jp_karyawan: (parseFloat(selectedScheme.bpjs_jp_karyawan) > 0 || parseFloat(selectedScheme.bpjs_jp_perusahaan) > 0) ? parseFloat(selectedScheme.bpjs_jp_karyawan) : 0,
            bpjs_jp_perusahaan: (parseFloat(selectedScheme.bpjs_jp_karyawan) > 0 || parseFloat(selectedScheme.bpjs_jp_perusahaan) > 0) ? parseFloat(selectedScheme.bpjs_jp_perusahaan) : 0,
            bpjs_jp_max_salary: (parseFloat(selectedScheme.bpjs_jp_karyawan) > 0 || parseFloat(selectedScheme.bpjs_jp_perusahaan) > 0) ? parseFloat(selectedScheme.bpjs_jp_max_salary) : 0,
            bpjs_jkk_perusahaan: parseFloat(selectedScheme.bpjs_jkk_perusahaan) > 0 ? parseFloat(selectedScheme.bpjs_jkk_perusahaan) : 0,
            bpjs_jkm_perusahaan: parseFloat(selectedScheme.bpjs_jkm_perusahaan) > 0 ? parseFloat(selectedScheme.bpjs_jkm_perusahaan) : 0
        };
    } else {
        // If tambah_skema or nothing found, all checked by default
        document.getElementById('mClientBpjsKesActive').checked = true;
        document.getElementById('mClientBpjsJhtActive').checked = true;
        document.getElementById('mClientBpjsJpActive').checked = true;
        document.getElementById('mClientBpjsJkkActive').checked = true;
        document.getElementById('mClientBpjsJkmActive').checked = true;

        window.modalClientBpjsOriginalValues = {
            is_tambah_skema: true,
            bpjs_kes_karyawan: baseRates.bpjs_kes_karyawan,
            bpjs_kes_perusahaan: baseRates.bpjs_kes_perusahaan,
            bpjs_kes_max_salary: baseRates.bpjs_kes_max_salary,
            bpjs_jht_karyawan: baseRates.bpjs_jht_karyawan,
            bpjs_jht_perusahaan: baseRates.bpjs_jht_perusahaan,
            bpjs_jp_karyawan: baseRates.bpjs_jp_karyawan,
            bpjs_jp_perusahaan: baseRates.bpjs_jp_perusahaan,
            bpjs_jp_max_salary: baseRates.bpjs_jp_max_salary,
            bpjs_jkk_perusahaan: baseRates.bpjs_jkk_perusahaan,
            bpjs_jkm_perusahaan: baseRates.bpjs_jkm_perusahaan
        };
    }
};

window.tutupModalPilihanSkema = function() {
    document.getElementById('modalPilihanSkema').style.display = 'none';
    window.editSchemaMappingId = null;
    
    const overrideFields = document.getElementById('modalClientBpjsOverrideFields');
    if (overrideFields) overrideFields.style.display = 'none';
    window.modalClientBpjsOriginalValues = null;
    window.modalClientBpjsBaseRates = null;

    // Destroy TomSelect instances (excluding bpjs select which is native)
    ['modalPilihanSkemaDivisi', 'modalPilihanSkemaDepartemen', 'modalPilihanSkemaPosisi', 'modalPilihanSkemaPayroll', 'modalPilihanSkemaPajak'].forEach(id => {
        const el = document.getElementById(id);
        if (el && el.tomselect) el.tomselect.destroy();
    });
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
        const elProrate = document.getElementById('cfgProrate');
        const elAbsen = document.getElementById('cfgAbsenTidakPotong');
        const nominalInput = document.getElementById('cfgNominalPotongan');
        if (data && data.id) {
            if (elProrate) elProrate.checked = data.prorate == 1;
            if (elAbsen) elAbsen.checked = data.absen_tidak_potong == 1;
            if (nominalInput) {
                const val = data.nominal_potongan ? parseFloat(data.nominal_potongan) : 0;
                nominalInput.value = val > 0 ? new Intl.NumberFormat('id-ID').format(val) : '';
            }
        } else {
            if (elProrate) elProrate.checked = false;
            if (elAbsen) elAbsen.checked = false;
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
