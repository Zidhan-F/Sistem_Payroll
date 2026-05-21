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
    if(current) {
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
    if(document.getElementById('formSetup')) {
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
        summaryDiv.innerHTML = `<p style="text-align: center; color: #94a3b8; font-size: 13px; margin: 0;">Pilih skema kompensasi di atas untuk melihat detailnya.</p>`;
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
                    <span style="color: #94a3b8; font-size: 13px;">Belum ada komponen kompensasi dalam skema ini.</span>
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
            compSelect.innerHTML = '<option value="">-- Pilih Skema Kompensasi --</option>' +
                compensationSchemes.map(s => `<option value="${s.id}">${s.nama}</option>`).join('');
        }

        // Load current client config to pre-select values
        const cfgRes = await fetch(`${API_URL}/client-configs`);
        const configs = await cfgRes.json();
        const conf = configs.find(c => c.client_id == window.selectedClientId);
        
        const summaryDiv = document.getElementById('pilihanKompensasiSummary');
        if (summaryDiv) {
            summaryDiv.innerHTML = `<p style="text-align: center; color: #94a3b8; font-size: 13px; margin: 0;">Pilih skema kompensasi di atas untuk melihat detailnya.</p>`;
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
    } catch (err) {
        console.error('Error loading pilihan skema:', err);
    }
}

async function simpanPilihanSkema() {
    if (!window.selectedClientId) {
        showToast('Pilih klien terlebih dahulu!', 'error');
        return;
    }
    const payrollType = document.getElementById('pilihanSkemaPayrollTipe').value;
    const minimumWageId = document.getElementById('pilihanSkemaPayrollWilayah').value;
    const customNominal = document.getElementById('pilihanSkemaPayrollNominal').value;
    const payrollSchemeId = document.getElementById('pilihanSkemaPayroll').value;
    const taxSchemeId = document.getElementById('pilihanSkemaPajak').value;
    const compSchemeId = document.getElementById('pilihanSkemaKompensasi').value;

    if (!payrollType && !taxSchemeId && !compSchemeId) {
        showToast('Pilih minimal satu skema!', 'error');
        return;
    }

    try {
        // Load existing config to preserve pay_date and cutoff
        const cfgRes = await fetch(`${API_URL}/client-configs`);
        const configs = await cfgRes.json();
        const existing = configs.find(c => c.client_id == window.selectedClientId);

        const data = {
            client_id: window.selectedClientId,
            payroll_type: payrollType || null,
            minimum_wage_id: (payrollType === 'UMP' || payrollType === 'UMK') ? (minimumWageId || null) : null,
            custom_nominal: (payrollType === 'Nominal') ? (customNominal || null) : null,
            payroll_scheme_id: (payrollType === 'Template') ? (payrollSchemeId || null) : null,
            tax_scheme_id: taxSchemeId || (existing ? existing.tax_scheme_id : null),
            compensation_scheme_id: compSchemeId || (existing ? existing.compensation_scheme_id : null),
            pay_date: existing ? existing.pay_date : 25,
            cutoff_start: existing ? existing.cutoff_start : 21
        };

        const res = await fetch(`${API_URL}/client-configs`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (res.ok) {
            await simpanKonfigAbsen(false);
            showToast('Pilihan skema berhasil disimpan!', 'success');
            // Also update the Setup Payroll tab data
            loadWorkspaceSetup();
        } else {
            showToast('Gagal menyimpan pilihan skema!', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Gagal menyimpan pilihan skema!', 'error');
    }
}

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
