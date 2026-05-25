/**
 * Module: Payroll Scheme Templates Management
 * Mengelola multiple skema payroll per divisi/departemen/posisi
 */

let currentClientIdForSchemes = null;
let allSchemeTemplates = [];
let currentSchemeTemplate = null;

/**
 * Load all scheme templates for a client
 */
async function loadSchemeTemplates(clientId) {
    currentClientIdForSchemes = clientId;
    
    try {
        const response = await fetch(`/api/payroll-schemes?client_id=${clientId}`);
        const schemes = await response.json();
        
        allSchemeTemplates = schemes;
        renderSchemeTemplatesTable(schemes);
        populateSchemeDropdowns(schemes);
        
        return schemes;
    } catch (error) {
        console.error('Error loading scheme templates:', error);
        showNotification('Gagal memuat skema payroll', 'error');
        return [];
    }
}

/**
 * Render table of scheme templates
 */
function renderSchemeTemplatesTable(schemes) {
    const tbody = document.getElementById('tabelSkemaTemplates');
    if (!tbody) return;
    
    if (schemes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                    Belum ada skema payroll. Klik tombol "Tambah Skema" untuk membuat skema baru.
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = schemes.map(scheme => {
        const orgInfo = [];
        if (scheme.division_name) orgInfo.push(`<span class="badge badge-info">${scheme.division_name}</span>`);
        if (scheme.department_name) orgInfo.push(`<span class="badge badge-warning">${scheme.department_name}</span>`);
        if (scheme.position_name) orgInfo.push(`<span class="badge badge-success">${scheme.position_name}</span>`);
        
        const orgDisplay = orgInfo.length > 0 ? orgInfo.join(' ') : '<span class="badge badge-secondary">Semua</span>';
        
        const gajiDisplay = scheme.sumber_gaji === 'nominal' 
            ? formatRupiah(scheme.nilai_gaji_pokok)
            : (scheme.minimum_wage_name || '-');
        
        const statusBadge = scheme.is_active == 1 
            ? '<span class="badge badge-success">Aktif</span>'
            : '<span class="badge badge-secondary">Nonaktif</span>';
        
        return `
            <tr>
                <td>${scheme.nama_skema}</td>
                <td>${orgDisplay}</td>
                <td>${scheme.sumber_gaji.toUpperCase()}</td>
                <td style="text-align: right;">${gajiDisplay}</td>
                <td style="text-align: center;">${statusBadge}</td>
                <td style="text-align: center;">
                    <button class="btn-icon btn-info" onclick="viewSchemeTemplateDetail(${scheme.id})" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon btn-warning" onclick="editSchemeTemplate(${scheme.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon ${scheme.is_active == 1 ? 'btn-secondary' : 'btn-success'}" 
                            onclick="toggleSchemeTemplateActive(${scheme.id})" 
                            title="${scheme.is_active == 1 ? 'Nonaktifkan' : 'Aktifkan'}">
                        <i class="fas fa-${scheme.is_active == 1 ? 'toggle-off' : 'toggle-on'}"></i>
                    </button>
                    <button class="btn-icon btn-danger" onclick="deleteSchemeTemplate(${scheme.id})" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

/**
 * Populate scheme dropdowns for filtering
 */
function populateSchemeDropdowns(schemes) {
    const selectElement = document.getElementById('pilihanSkemaPayroll');
    if (!selectElement) return;
    
    // Group schemes by org structure
    const grouped = {};
    schemes.forEach(scheme => {
        if (scheme.is_active != 1) return;
        
        const key = `${scheme.division_id || 'all'}_${scheme.department_id || 'all'}_${scheme.position_id || 'all'}`;
        if (!grouped[key]) grouped[key] = [];
        grouped[key].push(scheme);
    });
    
    selectElement.innerHTML = '<option value="">-- Pilih Skema Payroll --</option>';
    
    schemes.filter(s => s.is_active == 1).forEach(scheme => {
        const orgLabel = [];
        if (scheme.division_name) orgLabel.push(scheme.division_name);
        if (scheme.department_name) orgLabel.push(scheme.department_name);
        if (scheme.position_name) orgLabel.push(scheme.position_name);
        
        const label = orgLabel.length > 0 
            ? `${scheme.nama_skema} (${orgLabel.join(' > ')})`
            : `${scheme.nama_skema} (Default)`;
        
        selectElement.innerHTML += `<option value="${scheme.id}">${label}</option>`;
    });
}

/**
 * Open modal to create new scheme template
 */
function openNewSchemeTemplateModal() {
    console.log('openNewSchemeTemplateModal called');
    console.log('currentClientIdForSchemes:', currentClientIdForSchemes);
    
    if (!currentClientIdForSchemes) {
        alert('Pilih klien terlebih dahulu');
        return;
    }
    
    currentSchemeTemplate = null;
    document.getElementById('modalSchemeTemplateTitle').textContent = 'Tambah Skema Payroll Baru';
    document.getElementById('formSchemeTemplate').reset();
    
    // Load org structure dropdowns
    loadOrgStructureForScheme(currentClientIdForSchemes);
    
    // Show modal
    document.getElementById('modalSchemeTemplate').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    
    console.log('Modal should be visible now');
}

/**
 * Load organization structure for scheme modal
 */
async function loadOrgStructureForScheme(clientId) {
    try {
        const response = await fetch(`/api/org?client_id=${clientId}`);
        const data = await response.json();
        
        // Populate divisions
        const divSelect = document.getElementById('schemeTemplateDivisionId');
        divSelect.innerHTML = '<option value="">-- Semua Divisi --</option>';
        data.divisions.forEach(div => {
            divSelect.innerHTML += `<option value="${div.id}">${div.nama}</option>`;
        });
        
        // Populate departments
        const deptSelect = document.getElementById('schemeTemplateDepartmentId');
        deptSelect.innerHTML = '<option value="">-- Semua Departemen --</option>';
        data.departments.forEach(dept => {
            deptSelect.innerHTML += `<option value="${dept.id}">${dept.nama}</option>`;
        });
        
        // Populate positions
        const posSelect = document.getElementById('schemeTemplatePositionId');
        posSelect.innerHTML = '<option value="">-- Semua Posisi --</option>';
        data.positions.forEach(pos => {
            posSelect.innerHTML += `<option value="${pos.id}">${pos.nama}</option>`;
        });
        
    } catch (error) {
        console.error('Error loading org structure:', error);
    }
}

/**
 * Handle sumber gaji change
 */
function handleSumberGajiChange() {
    const sumber = document.getElementById('schemeTemplateSumberGaji').value;
    const nominalContainer = document.getElementById('schemeTemplateNominalContainer');
    const umkContainer = document.getElementById('schemeTemplateUmkContainer');
    
    if (sumber === 'nominal') {
        nominalContainer.style.display = 'block';
        umkContainer.style.display = 'none';
    } else if (sumber === 'ump' || sumber === 'umk') {
        nominalContainer.style.display = 'none';
        umkContainer.style.display = 'block';
        loadMinimumWagesForScheme(sumber);
    } else {
        nominalContainer.style.display = 'none';
        umkContainer.style.display = 'none';
    }
}

/**
 * Load minimum wages for scheme
 */
async function loadMinimumWagesForScheme(type) {
    try {
        const response = await fetch(`/api/minimum-wages?type=${type}`);
        const wages = await response.json();
        
        const select = document.getElementById('schemeTemplateMinimumWageId');
        select.innerHTML = '<option value="">-- Pilih UMP/UMK --</option>';
        
        wages.forEach(wage => {
            select.innerHTML += `<option value="${wage.id}">${wage.nama} - ${formatRupiah(wage.nominal)}</option>`;
        });
    } catch (error) {
        console.error('Error loading minimum wages:', error);
    }
}

/**
 * Save scheme template
 */
async function saveSchemeTemplate() {
    const formData = {
        client_id: currentClientIdForSchemes,
        division_id: document.getElementById('schemeTemplateDivisionId').value || null,
        department_id: document.getElementById('schemeTemplateDepartmentId').value || null,
        position_id: document.getElementById('schemeTemplatePositionId').value || null,
        nama_skema: document.getElementById('schemeTemplateNama').value,
        deskripsi: document.getElementById('schemeTemplateDeskripsi').value,
        sumber_gaji: document.getElementById('schemeTemplateSumberGaji').value,
        nilai_gaji_pokok: parseFloat(document.getElementById('schemeTemplateNilaiGaji').value.replace(/[^0-9]/g, '')) || 0,
        minimum_wage_id: document.getElementById('schemeTemplateMinimumWageId').value || null,
        
        // Tunjangan
        tunjangan_transport: parseFloat(document.getElementById('schemeTunjanganTransport').value.replace(/[^0-9]/g, '')) || 0,
        tunjangan_makan: parseFloat(document.getElementById('schemeTunjanganMakan').value.replace(/[^0-9]/g, '')) || 0,
        tunjangan_komunikasi: parseFloat(document.getElementById('schemeTunjanganKomunikasi').value.replace(/[^0-9]/g, '')) || 0,
        tunjangan_jabatan: parseFloat(document.getElementById('schemeTunjanganJabatan').value.replace(/[^0-9]/g, '')) || 0,
        tunjangan_kehadiran: parseFloat(document.getElementById('schemeTunjanganKehadiran').value.replace(/[^0-9]/g, '')) || 0,
        tunjangan_kinerja: parseFloat(document.getElementById('schemeTunjanganKinerja').value.replace(/[^0-9]/g, '')) || 0,
        
        // Potongan
        potongan_pinjaman: parseFloat(document.getElementById('schemePotonganPinjaman').value.replace(/[^0-9]/g, '')) || 0,
        potongan_kasbon: parseFloat(document.getElementById('schemePotonganKasbon').value.replace(/[^0-9]/g, '')) || 0,
        potongan_lainnya: parseFloat(document.getElementById('schemePotonganLainnya').value.replace(/[^0-9]/g, '')) || 0,
        
        // Absensi & Lembur
        potongan_per_alpa: parseFloat(document.getElementById('schemePotonganPerAlpa').value.replace(/[^0-9]/g, '')) || 0,
        bonus_per_hadir: parseFloat(document.getElementById('schemeBonusPerHadir').value.replace(/[^0-9]/g, '')) || 0,
        rate_lembur_per_jam: parseFloat(document.getElementById('schemeRateLembur').value.replace(/[^0-9]/g, '')) || 0,
        
        // BPJS
        bpjs_kes_karyawan: parseFloat(document.getElementById('schemeBpjsKesKaryawan').value) || 1.0,
        bpjs_kes_perusahaan: parseFloat(document.getElementById('schemeBpjsKesPerusahaan').value) || 4.0,
        bpjs_jht_karyawan: parseFloat(document.getElementById('schemeBpjsJhtKaryawan').value) || 2.0,
        bpjs_jht_perusahaan: parseFloat(document.getElementById('schemeBpjsJhtPerusahaan').value) || 3.7,
        bpjs_jp_karyawan: parseFloat(document.getElementById('schemeBpjsJpKaryawan').value) || 1.0,
        bpjs_jp_perusahaan: parseFloat(document.getElementById('schemeBpjsJpPerusahaan').value) || 2.0,
        bpjs_jkk_perusahaan: parseFloat(document.getElementById('schemeBpjsJkkPerusahaan').value) || 0.24,
        bpjs_jkm_perusahaan: parseFloat(document.getElementById('schemeBpjsJkmPerusahaan').value) || 0.30,
        
        // Pajak
        metode_pajak: document.getElementById('schemeMetodePajak').value,
        ptkp_status: document.getElementById('schemePtkpStatus').value,
        
        is_active: 1
    };
    
    try {
        const url = currentSchemeTemplate 
            ? `/api/payroll-schemes/${currentSchemeTemplate.id}`
            : '/api/payroll-schemes';
        
        const method = currentSchemeTemplate ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        
        if (response.ok) {
            showNotification(
                currentSchemeTemplate ? 'Skema berhasil diperbarui' : 'Skema berhasil ditambahkan',
                'success'
            );
            closeSchemeTemplateModal();
            loadSchemeTemplates(currentClientIdForSchemes);
        } else {
            const error = await response.json();
            showNotification('Gagal menyimpan skema: ' + (error.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Error saving scheme:', error);
        showNotification('Terjadi kesalahan saat menyimpan skema', 'error');
    }
}

/**
 * Edit scheme template
 */
async function editSchemeTemplate(id) {
    try {
        const response = await fetch(`/api/payroll-schemes/${id}`);
        const scheme = await response.json();
        
        currentSchemeTemplate = scheme;
        document.getElementById('modalSchemeTemplateTitle').textContent = 'Edit Skema Payroll';
        
        // Load org structure first
        await loadOrgStructureForScheme(scheme.client_id);
        
        // Fill form
        document.getElementById('schemeTemplateDivisionId').value = scheme.division_id || '';
        document.getElementById('schemeTemplateDepartmentId').value = scheme.department_id || '';
        document.getElementById('schemeTemplatePositionId').value = scheme.position_id || '';
        document.getElementById('schemeTemplateNama').value = scheme.nama_skema;
        document.getElementById('schemeTemplateDeskripsi').value = scheme.deskripsi || '';
        document.getElementById('schemeTemplateSumberGaji').value = scheme.sumber_gaji;
        
        handleSumberGajiChange();
        
        if (scheme.sumber_gaji === 'nominal') {
            document.getElementById('schemeTemplateNilaiGaji').value = formatRupiah(scheme.nilai_gaji_pokok);
        } else {
            setTimeout(() => {
                document.getElementById('schemeTemplateMinimumWageId').value = scheme.minimum_wage_id || '';
            }, 500);
        }
        
        // Fill other fields
        document.getElementById('schemeTunjanganTransport').value = formatRupiah(scheme.tunjangan_transport);
        document.getElementById('schemeTunjanganMakan').value = formatRupiah(scheme.tunjangan_makan);
        document.getElementById('schemeTunjanganKomunikasi').value = formatRupiah(scheme.tunjangan_komunikasi);
        document.getElementById('schemeTunjanganJabatan').value = formatRupiah(scheme.tunjangan_jabatan);
        document.getElementById('schemeTunjanganKehadiran').value = formatRupiah(scheme.tunjangan_kehadiran);
        document.getElementById('schemeTunjanganKinerja').value = formatRupiah(scheme.tunjangan_kinerja);
        
        document.getElementById('schemePotonganPinjaman').value = formatRupiah(scheme.potongan_pinjaman);
        document.getElementById('schemePotonganKasbon').value = formatRupiah(scheme.potongan_kasbon);
        document.getElementById('schemePotonganLainnya').value = formatRupiah(scheme.potongan_lainnya);
        
        document.getElementById('schemePotonganPerAlpa').value = formatRupiah(scheme.potongan_per_alpa);
        document.getElementById('schemeBonusPerHadir').value = formatRupiah(scheme.bonus_per_hadir);
        document.getElementById('schemeRateLembur').value = formatRupiah(scheme.rate_lembur_per_jam);
        
        document.getElementById('schemeBpjsKesKaryawan').value = scheme.bpjs_kes_karyawan;
        document.getElementById('schemeBpjsKesPerusahaan').value = scheme.bpjs_kes_perusahaan;
        document.getElementById('schemeBpjsJhtKaryawan').value = scheme.bpjs_jht_karyawan;
        document.getElementById('schemeBpjsJhtPerusahaan').value = scheme.bpjs_jht_perusahaan;
        document.getElementById('schemeBpjsJpKaryawan').value = scheme.bpjs_jp_karyawan;
        document.getElementById('schemeBpjsJpPerusahaan').value = scheme.bpjs_jp_perusahaan;
        document.getElementById('schemeBpjsJkkPerusahaan').value = scheme.bpjs_jkk_perusahaan;
        document.getElementById('schemeBpjsJkmPerusahaan').value = scheme.bpjs_jkm_perusahaan;
        
        document.getElementById('schemeMetodePajak').value = scheme.metode_pajak;
        document.getElementById('schemePtkpStatus').value = scheme.ptkp_status;
        
        // Show modal
        document.getElementById('modalSchemeTemplate').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
        
    } catch (error) {
        console.error('Error loading scheme:', error);
        showNotification('Gagal memuat data skema', 'error');
    }
}

/**
 * View scheme template detail
 */
async function viewSchemeTemplateDetail(id) {
    try {
        const response = await fetch(`/api/payroll-schemes/${id}`);
        const scheme = await response.json();
        
        // Build detail HTML
        const orgInfo = [];
        if (scheme.division_name) orgInfo.push(`Divisi: ${scheme.division_name}`);
        if (scheme.department_name) orgInfo.push(`Departemen: ${scheme.department_name}`);
        if (scheme.position_name) orgInfo.push(`Posisi: ${scheme.position_name}`);
        
        const detailHTML = `
            <div style="padding: 20px;">
                <h3 style="margin-bottom: 20px; color: var(--primary-color);">${scheme.nama_skema}</h3>
                
                <div style="margin-bottom: 20px;">
                    <strong>Struktur Organisasi:</strong><br>
                    ${orgInfo.length > 0 ? orgInfo.join('<br>') : 'Berlaku untuk semua struktur organisasi'}
                </div>
                
                <div style="margin-bottom: 20px;">
                    <strong>Deskripsi:</strong><br>
                    ${scheme.deskripsi || '-'}
                </div>
                
                <hr>
                
                <h4>Gaji Pokok</h4>
                <p>Sumber: ${scheme.sumber_gaji.toUpperCase()}</p>
                <p>Nilai: ${scheme.sumber_gaji === 'nominal' ? formatRupiah(scheme.nilai_gaji_pokok) : (scheme.minimum_wage_name + ' - ' + formatRupiah(scheme.minimum_wage_nominal))}</p>
                
                <hr>
                
                <h4>Tunjangan</h4>
                <ul>
                    <li>Transport: ${formatRupiah(scheme.tunjangan_transport)}</li>
                    <li>Makan: ${formatRupiah(scheme.tunjangan_makan)}</li>
                    <li>Komunikasi: ${formatRupiah(scheme.tunjangan_komunikasi)}</li>
                    <li>Jabatan: ${formatRupiah(scheme.tunjangan_jabatan)}</li>
                    <li>Kehadiran: ${formatRupiah(scheme.tunjangan_kehadiran)}</li>
                    <li>Kinerja: ${formatRupiah(scheme.tunjangan_kinerja)}</li>
                </ul>
                
                <hr>
                
                <h4>BPJS & Pajak</h4>
                <p>Metode Pajak: ${scheme.metode_pajak}</p>
                <p>Status PTKP: ${scheme.ptkp_status}</p>
                <p>BPJS Kesehatan Karyawan: ${scheme.bpjs_kes_karyawan}%</p>
                <p>BPJS JHT Karyawan: ${scheme.bpjs_jht_karyawan}%</p>
            </div>
        `;
        
        // Show in modal or alert
        alert(detailHTML.replace(/<[^>]*>/g, '\n'));
        
    } catch (error) {
        console.error('Error loading scheme detail:', error);
        showNotification('Gagal memuat detail skema', 'error');
    }
}

/**
 * Toggle scheme active status
 */
async function toggleSchemeTemplateActive(id) {
    if (!confirm('Apakah Anda yakin ingin mengubah status skema ini?')) return;
    
    try {
        const response = await fetch(`/api/payroll-schemes/toggle-active/${id}`, {
            method: 'POST'
        });
        
        if (response.ok) {
            const result = await response.json();
            showNotification(result.message, 'success');
            loadSchemeTemplates(currentClientIdForSchemes);
        } else {
            showNotification('Gagal mengubah status skema', 'error');
        }
    } catch (error) {
        console.error('Error toggling scheme status:', error);
        showNotification('Terjadi kesalahan', 'error');
    }
}

/**
 * Delete scheme template
 */
async function deleteSchemeTemplate(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus skema ini? Tindakan ini tidak dapat dibatalkan.')) return;
    
    try {
        const response = await fetch(`/api/payroll-schemes/${id}`, {
            method: 'DELETE'
        });
        
        if (response.ok) {
            showNotification('Skema berhasil dihapus', 'success');
            loadSchemeTemplates(currentClientIdForSchemes);
        } else {
            showNotification('Gagal menghapus skema', 'error');
        }
    } catch (error) {
        console.error('Error deleting scheme:', error);
        showNotification('Terjadi kesalahan', 'error');
    }
}

/**
 * Close scheme template modal
 */
function closeSchemeTemplateModal() {
    document.getElementById('modalSchemeTemplate').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
    currentSchemeTemplate = null;
}

/**
 * Get schemes filtered by org structure
 */
async function getSchemesByOrgStructure(clientId, divisionId, departmentId, positionId) {
    try {
        let url = `/api/payroll-schemes/by-org?client_id=${clientId}`;
        if (divisionId) url += `&division_id=${divisionId}`;
        if (departmentId) url += `&department_id=${departmentId}`;
        if (positionId) url += `&position_id=${positionId}`;
        
        const response = await fetch(url);
        return await response.json();
    } catch (error) {
        console.error('Error getting schemes by org structure:', error);
        return [];
    }
}
