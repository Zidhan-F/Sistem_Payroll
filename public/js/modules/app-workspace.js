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

async function loadPilihanSkema() {
    if (!window.selectedClientId) return;
    try {
        // Fetch payroll schemes for dropdown inside modal later
        const psRes = await fetch(`${API_URL}/payroll-schemes`);
        window.payrollSchemes = await psRes.json();

        // Fetch tax schemes for dropdown inside modal later
        const tsRes = await fetch(`${API_URL}/tax-schemes`);
        window.taxSchemes = await tsRes.json();

        // Load current client configs to render in the table
        const cfgRes = await fetch(`${API_URL}/client-configs`);
        const configs = await cfgRes.json();
        
        renderPilihanSkemaTable(configs);
    } catch (err) {
        console.error('Error loading pilihan skema:', err);
    }
}

function renderPilihanSkemaTable(configs) {
    const tbody = document.getElementById('tabelPilihanSkemaKlien');
    if (!tbody) return;

    // Filter configs for active client and having a valid setup_id
    const clientConfigs = configs.filter(c => c.client_id == window.selectedClientId && c.setup_id);

    if (clientConfigs.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                    Belum ada skema payroll yang terdaftar. Klik tombol "Tambah Skema" untuk mengkonfigurasi.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = clientConfigs.map(c => {
        return `
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 12px 15px; font-weight: 500; color: #334155;">${c.division_name || '<span style="color: #94a3b8; font-style: italic;">Global (Semua)</span>'}</td>
                <td style="padding: 12px 15px; font-weight: 500; color: #334155;">${c.department_name || '-'}</td>
                <td style="padding: 12px 15px; font-weight: 500; color: #334155;">${c.position_name || '-'}</td>
                <td style="padding: 12px 15px; font-weight: 600; color: var(--primary-color);">${c.payroll_scheme_name || '-'}</td>
                <td style="padding: 12px 15px; font-weight: 600; color: #0f766e;">${c.tax_scheme_name || '-'}</td>
                <td style="padding: 12px 15px; text-align: center;">
                    <div style="display: flex; justify-content: center; gap: 8px;">
                        <button class="btn-action-edit" onclick="editPilihanSkema(${c.setup_id})" style="background: #e0f2fe; color: #0369a1; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;" title="Edit">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn-action-delete" onclick="deletePilihanSkema(${c.setup_id})" style="background: #fee2e2; color: #b91c1c; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;" title="Hapus">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

window.openModalPilihanSkema = async function(conf = null) {
    if (!window.selectedClientId) {
        showToast('Pilih klien terlebih dahulu!', 'error');
        return;
    }
    
    const modal = document.getElementById('modalPilihanSkema');
    if (!modal) return;

    // Title & Reset
    document.getElementById('modalPilihanSkemaTitle').innerText = conf ? 'Edit Skema Client' : 'Tambah Skema Client';
    document.getElementById('pilihanSkemaSetupId').value = conf ? (conf.setup_id || '') : '';
    document.getElementById('modalPilihanSkemaNamaKlien').value = window.selectedClientName || '';

    // Load hierarchy
    const divSelect = document.getElementById('modalPilihanSkemaDivisi');
    const deptSelect = document.getElementById('modalPilihanSkemaDepartemen');
    const posSelect = document.getElementById('modalPilihanSkemaPosisi');
    const hkSelect = document.getElementById('modalPilihanSkemaHariKerja');
    const psSelect = document.getElementById('modalPilihanSkemaPayroll');
    const tsSelect = document.getElementById('modalPilihanSkemaPajak');

    divSelect.innerHTML = '<option value="">-- Pilih Divisi --</option>';
    deptSelect.innerHTML = '<option value="">-- Pilih Departemen --</option>';
    posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
    hkSelect.value = '';
    psSelect.value = '';
    tsSelect.value = '';

    // Hide detail buttons
    handleModalPilihanSkemaPajakChange('');
    handleModalPilihanSkemaPayrollChange('');

    // Populate dropdown values
    if (window.payrollSchemes) {
        psSelect.innerHTML = '<option value="">-- Pilih Skema Payroll --</option>' +
            window.payrollSchemes.map(s => `<option value="${s.id}">${s.nama} (${s.tipe || 'Umum'})</option>`).join('');
    }
    if (window.taxSchemes) {
        tsSelect.innerHTML = '<option value="">-- Pilih Skema Pajak --</option>' +
            window.taxSchemes.map(s => `<option value="${s.id}">${s.nama} (${s.metode || '-'})</option>`).join('');
    }

    try {
        const r = await fetch(`${API_URL}/org?client_id=${window.selectedClientId}`);
        const orgHierarchy = await r.json();

        if (Array.isArray(orgHierarchy)) {
            orgHierarchy.forEach(div => {
                divSelect.innerHTML += `<option value="${div.id}">${div.nama}</option>`;
            });
        }

        // Setup cascading triggers inside modal
        divSelect.onchange = () => {
            const divId = divSelect.value;
            deptSelect.innerHTML = '<option value="">-- Pilih Departemen --</option>';
            posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
            hkSelect.value = '';
            if (divId && Array.isArray(orgHierarchy)) {
                const division = orgHierarchy.find(d => d.id == divId);
                if (division && Array.isArray(division.departments)) {
                    division.departments.forEach(dept => {
                        deptSelect.innerHTML += `<option value="${dept.id}">${dept.nama}</option>`;
                    });
                }
            }
        };

        deptSelect.onchange = () => {
            const divId = divSelect.value;
            const deptId = deptSelect.value;
            posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
            hkSelect.value = '';
            if (divId && deptId && Array.isArray(orgHierarchy)) {
                const division = orgHierarchy.find(d => d.id == divId);
                if (division && Array.isArray(division.departments)) {
                    const dept = division.departments.find(dp => dp.id == deptId);
                    if (dept && Array.isArray(dept.positions)) {
                        dept.positions.forEach(pos => {
                            posSelect.innerHTML += `<option value="${pos.id}" data-hari-kerja="${pos.hari_kerja || 5}">${pos.nama}</option>`;
                        });
                    }
                }
            }
        };

        posSelect.onchange = () => {
            const selectedOpt = posSelect.options[posSelect.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const hk = selectedOpt.getAttribute('data-hari-kerja') || '5';
                hkSelect.value = hk;
            } else {
                hkSelect.value = '';
            }
        };

        // If editing (conf is provided), pre-fill values
        if (conf) {
            if (conf.division_id) {
                divSelect.value = conf.division_id;
                divSelect.onchange(); // trigger cascade
            }
            if (conf.department_id) {
                deptSelect.value = conf.department_id;
                deptSelect.onchange(); // trigger cascade
            }
            if (conf.position_id) {
                posSelect.value = conf.position_id;
                posSelect.onchange(); // trigger cascade
            }
            // Set payroll scheme
            if (conf.payroll_scheme_id) {
                psSelect.value = conf.payroll_scheme_id;
                handleModalPilihanSkemaPayrollChange(conf.payroll_scheme_id);
            }
            // Set tax scheme
            if (conf.tax_scheme_id) {
                tsSelect.value = conf.tax_scheme_id;
                handleModalPilihanSkemaPajakChange(conf.tax_scheme_id);
            }
            // Set working days
            if (conf.position_id) {
                setTimeout(() => {
                    const posOpt = posSelect.querySelector(`option[value="${conf.position_id}"]`);
                    if (posOpt) {
                        hkSelect.value = posOpt.getAttribute('data-hari-kerja') || '5';
                    }
                }, 100);
            }
        }

    } catch (err) {
        console.error('Error in modal setup:', err);
    }

    modal.style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
};

window.tutupModalPilihanSkema = function() {
    const modal = document.getElementById('modalPilihanSkema');
    if (modal) modal.style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
};

window.editPilihanSkema = async function(setupId) {
    if (!window.selectedClientId) return;
    try {
        const res = await fetch(`${API_URL}/client-configs`);
        const configs = await res.json();
        const conf = configs.find(c => c.setup_id == setupId);
        if (!conf) return;

        openModalPilihanSkema(conf);
    } catch (err) {
        console.error('Error opening edit modal:', err);
        showToast('Gagal memuat detail konfigurasi', 'error');
    }
};

window.deletePilihanSkema = async function(setupId) {
    const confirmed = await showConfirm('Apakah Anda yakin ingin menghapus konfigurasi skema ini?');
    if (confirmed) {
        try {
            const res = await fetch(`${API_URL}/client-configs/${setupId}`, {
                method: 'DELETE'
            });
            if (res.ok) {
                showToast('Konfigurasi skema berhasil dihapus!', 'success');
                loadPilihanSkema();
            } else {
                showToast('Gagal menghapus konfigurasi skema!', 'error');
            }
        } catch (err) {
            console.error('Error deleting client config:', err);
            showToast('Gagal menghapus konfigurasi skema!', 'error');
        }
    }
};

window.handleModalPilihanSkemaPajakChange = function(val) {
    const btn = document.getElementById('modalBtnDetailSkemaPajak');
    if (btn) {
        btn.style.display = val ? 'inline-flex' : 'none';
    }
};

window.lihatDetailSkemaPajakModal = function() {
    const val = document.getElementById('modalPilihanSkemaPajak').value;
    if (!val || !window.taxSchemes) return;
    
    const scheme = window.taxSchemes.find(s => s.id == val);
    if (!scheme) return;
    
    // Populate modal contents
    document.getElementById('dtlPajakNama').innerText = scheme.nama || '-';
    document.getElementById('dtlPajakDeskripsi').innerText = scheme.deskripsi || 'Tidak ada deskripsi.';
    document.getElementById('dtlPajakMetode').innerText = scheme.metode ? scheme.metode.toUpperCase() : '-';
    document.getElementById('dtlPajakPtkp').innerText = scheme.ptkp_default || '-';
    
    document.getElementById('dtlPajakBpjsKesKaryawan').innerText = (scheme.bpjs_kes_karyawan !== undefined ? scheme.bpjs_kes_karyawan : '1.00') + '%';
    document.getElementById('dtlPajakBpjsKesPerusahaan').innerText = (scheme.bpjs_kes_perusahaan !== undefined ? scheme.bpjs_kes_perusahaan : '4.00') + '%';
    document.getElementById('dtlPajakBpjsKesMaxSalary').innerText = formatRupiah(scheme.bpjs_kes_max_salary || 12000000);
    
    document.getElementById('dtlPajakBpjsJht').innerText = `${scheme.bpjs_jht_karyawan !== undefined ? scheme.bpjs_jht_karyawan : '2.00'}% / ${scheme.bpjs_jht_perusahaan !== undefined ? scheme.bpjs_jht_perusahaan : '3.70'}%`;
    document.getElementById('dtlPajakBpjsJp').innerText = `${scheme.bpjs_jp_karyawan !== undefined ? scheme.bpjs_jp_karyawan : '1.00'}% / ${scheme.bpjs_jp_perusahaan !== undefined ? scheme.bpjs_jp_perusahaan : '2.00'}%`;
    document.getElementById('dtlPajakBpjsJpMaxSalary').innerText = formatRupiah(scheme.bpjs_jp_max_salary || 10024600);
    
    document.getElementById('dtlPajakBpjsJkk').innerText = (scheme.bpjs_jkk_perusahaan !== undefined ? scheme.bpjs_jkk_perusahaan : '0.24') + '%';
    document.getElementById('dtlPajakBpjsJkm').innerText = (scheme.bpjs_jkm_perusahaan !== undefined ? scheme.bpjs_jkm_perusahaan : '0.30') + '%';
    
    // Open modal
    document.getElementById('modalDetailSkemaPajak').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
};

window.handleModalPilihanSkemaPayrollChange = function(val) {
    const btn = document.getElementById('modalBtnDetailSkemaPayroll');
    if (btn) {
        btn.style.display = val ? 'inline-flex' : 'none';
    }
};

window.lihatDetailSkemaPayrollModal = function() {
    const val = document.getElementById('modalPilihanSkemaPayroll').value;
    if (!val || !window.payrollSchemes) return;
    
    const scheme = window.payrollSchemes.find(s => s.id == val);
    if (!scheme) return;
    
    // Populate modal contents
    document.getElementById('dtlNama').innerText = scheme.nama || '-';
    document.getElementById('dtlDeskripsi').innerText = scheme.deskripsi || 'Tidak ada deskripsi.';
    document.getElementById('dtlTipe').innerText = scheme.tipe ? scheme.tipe.toUpperCase() : 'BULANAN';
    document.getElementById('dtlProrate').innerText = scheme.prorate == 1 ? 'YA' : 'TIDAK';
    document.getElementById('dtlAbsenTidakPotong').innerText = scheme.absen_tidak_potong == 1 ? 'YA' : 'TIDAK';
    document.getElementById('dtlNominalPotongan').innerText = formatRupiah(scheme.nominal_potongan || 0);
    
    const listCont = document.getElementById('dtlComponentsList');
    if (listCont) {
        if (scheme.components && scheme.components.length > 0) {
            listCont.innerHTML = scheme.components.map(c => {
                const isPendapatan = c.tipe === 'pendapatan';
                const badgeClass = isPendapatan ? 'pendapatan' : 'potongan';
                const valStr = c.is_persentase == 1 ? `${c.nilai}%` : formatRupiah(c.nilai || 0);
                
                // Determine icon and color based on jenis_komponen
                let iconClass = 'fa-coins';
                let iconBg = '#f1f5f9';
                let iconColor = '#64748b';
                
                if (c.jenis_komponen === 'basic_salary') {
                    iconClass = 'fa-wallet'; iconBg = '#fef3c7'; iconColor = '#d97706';
                } else if (isPendapatan) {
                    iconClass = 'fa-plus-circle'; iconBg = '#d1fae5'; iconColor = '#059669';
                } else {
                    iconClass = 'fa-minus-circle'; iconBg = '#fee2e2'; iconColor = '#dc2626';
                }
                
                return `
                    <div class="component-item" style="padding: 10px 14px; margin-bottom: 8px;">
                        <div class="component-item-left" style="display: flex; align-items: center; gap: 12px; flex: 1;">
                            <div class="component-kategori-icon" style="width: 30px; height: 30px; background: ${iconBg}; color: ${iconColor}; font-size: 12px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas ${iconClass}"></i>
                            </div>
                            <div class="component-info" style="display: flex; flex-direction: column;">
                                <span class="comp-name" style="font-size: 13px; font-weight: 600; color: #1e293b;">${c.nama}</span>
                                <span class="comp-kategori" style="font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.3px;">${c.sifat_kompensasi || 'komponen'} • ${c.tipe}</span>
                            </div>
                        </div>
                        <div class="component-item-right" style="display: flex; align-items: center; gap: 12px;">
                            <span class="component-value ${badgeClass}" style="font-size: 13px; font-weight: 600; padding: 2px 8px; border-radius: 6px;">${valStr}</span>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            listCont.innerHTML = `
                <div style="text-align: center; padding: 20px; color: #94a3b8; font-size: 13px; background: #f8fafc; border-radius: 8px; border: 1px dashed #e2e8f0;">
                    Tidak ada komponen tambahan pada skema ini.
                </div>
            `;
        }
    }
    
    // Open modal
    document.getElementById('modalDetailSkemaPayroll').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
};

async function simpanPilihanSkema() {
    if (!window.selectedClientId) {
        showToast('Pilih klien terlebih dahulu!', 'error');
        return;
    }
    const setupId = document.getElementById('pilihanSkemaSetupId').value;
    const payrollSchemeId = document.getElementById('modalPilihanSkemaPayroll').value;
    const taxSchemeId = document.getElementById('modalPilihanSkemaPajak').value;
    const divisionId = document.getElementById('modalPilihanSkemaDivisi').value;
    const departmentId = document.getElementById('modalPilihanSkemaDepartemen').value;
    const positionId = document.getElementById('modalPilihanSkemaPosisi').value;
    const hariKerja = document.getElementById('modalPilihanSkemaHariKerja').value;

    if (!payrollSchemeId) {
        showToast('Pilih Skema Payroll!', 'error');
        return;
    }
    if (!taxSchemeId) {
        showToast('Pilih Skema BPJS & Pajak!', 'error');
        return;
    }

    try {
        // If position is selected, save working days setting to this position
        if (positionId && hariKerja) {
            const posRes = await fetch(`${API_URL}/org/posisi/${positionId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ hari_kerja: parseInt(hariKerja) })
            });
            if (!posRes.ok) {
                showToast('Gagal menyimpan hari kerja posisi!', 'error');
                return;
            }
        }

        // Load existing config to preserve pay_date and cutoff if editing, or use defaults
        let payDate = 25;
        let cutoffStart = 21;
        
        if (setupId) {
            const cfgRes = await fetch(`${API_URL}/client-configs`);
            const configs = await cfgRes.json();
            const existing = configs.find(c => c.setup_id == setupId);
            if (existing) {
                payDate = existing.pay_date || 25;
                cutoffStart = existing.cutoff_start || 21;
            }
        } else {
            // Check if there is already a global config or any config to reuse payDate/cutoffStart
            const cfgRes = await fetch(`${API_URL}/client-configs`);
            const configs = await cfgRes.json();
            const firstExist = configs.find(c => c.client_id == window.selectedClientId);
            if (firstExist) {
                payDate = firstExist.pay_date || 25;
                cutoffStart = firstExist.cutoff_start || 21;
            }
        }

        const data = {
            id: setupId ? parseInt(setupId) : undefined,
            client_id: window.selectedClientId,
            payroll_type: 'Template',
            payroll_scheme_id: parseInt(payrollSchemeId),
            tax_scheme_id: parseInt(taxSchemeId),
            compensation_scheme_id: null,
            division_id: divisionId ? parseInt(divisionId) : null,
            department_id: departmentId ? parseInt(departmentId) : null,
            position_id: positionId ? parseInt(positionId) : null,
            pay_date: payDate,
            cutoff_start: cutoffStart
        };

        const res = await fetch(`${API_URL}/client-configs`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (res.ok) {
            showToast('Pilihan skema berhasil disimpan!', 'success');
            tutupModalPilihanSkema();
            loadPilihanSkema(); // reload configurations table
            loadWorkspaceSetup(); // reload workspace setup tab
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

window.handlePilihanSkemaPayrollChange = function(val) {
    const btn = document.getElementById('btnDetailSkemaPayroll');
    if (btn) {
        btn.style.display = val ? 'inline-flex' : 'none';
    }
};

window.lihatDetailSkemaPayroll = function() {
    const val = document.getElementById('pilihanSkemaPayroll').value;
    if (!val || !window.payrollSchemes) return;
    
    const scheme = window.payrollSchemes.find(s => s.id == val);
    if (!scheme) return;
    
    // Populate modal contents
    document.getElementById('dtlNama').innerText = scheme.nama || '-';
    document.getElementById('dtlDeskripsi').innerText = scheme.deskripsi || 'Tidak ada deskripsi.';
    document.getElementById('dtlTipe').innerText = scheme.tipe ? scheme.tipe.toUpperCase() : 'BULANAN';
    document.getElementById('dtlProrate').innerText = scheme.prorate == 1 ? 'YA' : 'TIDAK';
    document.getElementById('dtlAbsenTidakPotong').innerText = scheme.absen_tidak_potong == 1 ? 'YA' : 'TIDAK';
    document.getElementById('dtlNominalPotongan').innerText = formatRupiah(scheme.nominal_potongan || 0);
    
    const listCont = document.getElementById('dtlComponentsList');
    if (listCont) {
        if (scheme.components && scheme.components.length > 0) {
            listCont.innerHTML = scheme.components.map(c => {
                const isPendapatan = c.tipe === 'pendapatan';
                const badgeClass = isPendapatan ? 'pendapatan' : 'potongan';
                const valStr = c.is_persentase == 1 ? `${c.nilai}%` : formatRupiah(c.nilai || 0);
                
                // Determine icon and color based on jenis_komponen
                let iconClass = 'fa-coins';
                let iconBg = '#f1f5f9';
                let iconColor = '#64748b';
                
                if (c.jenis_komponen === 'basic_salary') {
                    iconClass = 'fa-wallet'; iconBg = '#fef3c7'; iconColor = '#d97706';
                } else if (isPendapatan) {
                    iconClass = 'fa-plus-circle'; iconBg = '#d1fae5'; iconColor = '#059669';
                } else {
                    iconClass = 'fa-minus-circle'; iconBg = '#fee2e2'; iconColor = '#dc2626';
                }
                
                return `
                    <div class="component-item" style="padding: 10px 14px; margin-bottom: 8px;">
                        <div class="component-item-left" style="display: flex; align-items: center; gap: 12px; flex: 1;">
                            <div class="component-kategori-icon" style="width: 30px; height: 30px; background: ${iconBg}; color: ${iconColor}; font-size: 12px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas ${iconClass}"></i>
                            </div>
                            <div class="component-info" style="display: flex; flex-direction: column;">
                                <span class="comp-name" style="font-size: 13px; font-weight: 600; color: #1e293b;">${c.nama}</span>
                                <span class="comp-kategori" style="font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.3px;">${c.sifat_kompensasi || 'komponen'} • ${c.tipe}</span>
                            </div>
                        </div>
                        <div class="component-item-right" style="display: flex; align-items: center; gap: 12px;">
                            <span class="component-value ${badgeClass}" style="font-size: 13px; font-weight: 600; padding: 2px 8px; border-radius: 6px;">${valStr}</span>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            listCont.innerHTML = `
                <div style="text-align: center; padding: 20px; color: #94a3b8; font-size: 13px; background: #f8fafc; border-radius: 8px; border: 1px dashed #e2e8f0;">
                    Tidak ada komponen tambahan pada skema ini.
                </div>
            `;
        }
    }
    
    // Open modal
    document.getElementById('modalDetailSkemaPayroll').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
};

window.handlePilihanSkemaPajakChange = function(val) {
    const btn = document.getElementById('btnDetailSkemaPajak');
    if (btn) {
        btn.style.display = val ? 'inline-flex' : 'none';
    }
};

window.lihatDetailSkemaPajak = function() {
    const val = document.getElementById('pilihanSkemaPajak').value;
    if (!val || !window.taxSchemes) return;
    
    const scheme = window.taxSchemes.find(s => s.id == val);
    if (!scheme) return;
    
    // Populate modal contents
    document.getElementById('dtlPajakNama').innerText = scheme.nama || '-';
    document.getElementById('dtlPajakDeskripsi').innerText = scheme.deskripsi || 'Tidak ada deskripsi.';
    document.getElementById('dtlPajakMetode').innerText = scheme.metode ? scheme.metode.toUpperCase() : '-';
    document.getElementById('dtlPajakPtkp').innerText = scheme.ptkp_default || '-';
    
    document.getElementById('dtlPajakBpjsKesKaryawan').innerText = (scheme.bpjs_kes_karyawan !== undefined ? scheme.bpjs_kes_karyawan : '1.00') + '%';
    document.getElementById('dtlPajakBpjsKesPerusahaan').innerText = (scheme.bpjs_kes_perusahaan !== undefined ? scheme.bpjs_kes_perusahaan : '4.00') + '%';
    document.getElementById('dtlPajakBpjsKesMaxSalary').innerText = formatRupiah(scheme.bpjs_kes_max_salary || 12000000);
    
    document.getElementById('dtlPajakBpjsJht').innerText = `${scheme.bpjs_jht_karyawan !== undefined ? scheme.bpjs_jht_karyawan : '2.00'}% / ${scheme.bpjs_jht_perusahaan !== undefined ? scheme.bpjs_jht_perusahaan : '3.70'}%`;
    document.getElementById('dtlPajakBpjsJp').innerText = `${scheme.bpjs_jp_karyawan !== undefined ? scheme.bpjs_jp_karyawan : '1.00'}% / ${scheme.bpjs_jp_perusahaan !== undefined ? scheme.bpjs_jp_perusahaan : '2.00'}%`;
    document.getElementById('dtlPajakBpjsJpMaxSalary').innerText = formatRupiah(scheme.bpjs_jp_max_salary || 10024600);
    
    document.getElementById('dtlPajakBpjsJkk').innerText = (scheme.bpjs_jkk_perusahaan !== undefined ? scheme.bpjs_jkk_perusahaan : '0.24') + '%';
    document.getElementById('dtlPajakBpjsJkm').innerText = (scheme.bpjs_jkm_perusahaan !== undefined ? scheme.bpjs_jkm_perusahaan : '0.30') + '%';
    
    // Open modal
    document.getElementById('modalDetailSkemaPajak').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
};



