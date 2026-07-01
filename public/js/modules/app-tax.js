// ===== TAX & BPJS MODULE =====
// Extracted from app.js for modular monolith architecture

let taxSchemes = [];
let bpjsSchemes = [];

// ===== TER RATES REFERENCE DATA =====
const terRatesA = [
    { range: 's.d. Rp 5.400.000', rate: '0%' },
    { range: 'Rp 5.400.001 – Rp 5.650.000', rate: '0.25%' },
    { range: 'Rp 5.650.001 – Rp 5.950.000', rate: '0.50%' },
    { range: 'Rp 5.950.001 – Rp 6.300.000', rate: '0.75%' },
    { range: 'Rp 6.300.001 – Rp 6.750.000', rate: '1.00%' },
    { range: 'Rp 6.750.001 – Rp 7.500.000', rate: '1.25%' },
    { range: 'Rp 7.500.001 – Rp 8.550.000', rate: '1.50%' },
    { range: 'Rp 8.550.001 – Rp 9.650.000', rate: '1.75%' },
    { range: 'Rp 9.650.001 – Rp 10.050.000', rate: '2.00%' },
    { range: 'Rp 10.050.001 – Rp 10.350.000', rate: '2.25%' },
    { range: 'Rp 10.350.001 – Rp 10.700.000', rate: '2.50%' },
    { range: 'Rp 10.700.001 – Rp 11.050.000', rate: '3.00%' },
    { range: 'Rp 11.050.001 – Rp 11.600.000', rate: '3.50%' },
    { range: 'Rp 11.600.001 – Rp 12.500.000', rate: '4.00%' },
    { range: 'Rp 12.500.001 – Rp 13.750.000', rate: '5.00%' },
    { range: 'Rp 13.750.001 – Rp 15.100.000', rate: '6.00%' },
    { range: 'Rp 15.100.001 – Rp 16.950.000', rate: '7.00%' },
    { range: 'Rp 16.950.001 – Rp 19.750.000', rate: '8.00%' },
    { range: 'Rp 19.750.001 – Rp 24.150.000', rate: '9.00%' },
    { range: 'Rp 24.150.001 – Rp 26.450.000', rate: '10.00%' },
    { range: 'Rp 26.450.001 – Rp 28.000.000', rate: '11.00%' },
    { range: 'Rp 28.000.001 – Rp 30.050.000', rate: '12.00%' },
    { range: 'Rp 30.050.001 – Rp 32.400.000', rate: '13.00%' },
    { range: 'Rp 32.400.001 – Rp 35.400.000', rate: '14.00%' },
    { range: 'Rp 35.400.001 – Rp 39.100.000', rate: '15.00%' },
    { range: 'Rp 39.100.001 – Rp 43.850.000', rate: '16.00%' },
    { range: 'Rp 43.850.001 – Rp 47.800.000', rate: '17.00%' },
    { range: 'Rp 47.800.001 – Rp 51.400.000', rate: '18.00%' },
    { range: 'Rp 51.400.001 – Rp 56.300.000', rate: '19.00%' },
    { range: 'Rp 56.300.001 – Rp 62.200.000', rate: '20.00%' },
    { range: 'Rp 62.200.001 – Rp 68.600.000', rate: '21.00%' },
    { range: 'Rp 68.600.001 – Rp 77.500.000', rate: '22.00%' },
    { range: 'Rp 77.500.001 – Rp 89.000.000', rate: '23.00%' },
    { range: 'Rp 89.000.001 – Rp 103.000.000', rate: '24.00%' },
    { range: 'Rp 103.000.001 – Rp 125.000.000', rate: '25.00%' },
    { range: 'Rp 125.000.001 – Rp 157.000.000', rate: '26.00%' },
    { range: 'Rp 157.000.001 – Rp 206.000.000', rate: '27.00%' },
    { range: 'Rp 206.000.001 – Rp 337.000.000', rate: '28.00%' },
    { range: 'Rp 337.000.001 – Rp 454.000.000', rate: '29.00%' },
    { range: 'Rp 454.000.001 – Rp 550.000.000', rate: '30.00%' },
    { range: 'Rp 550.000.001 – Rp 695.000.000', rate: '31.00%' },
    { range: 'Rp 695.000.001 – Rp 910.000.000', rate: '32.00%' },
    { range: 'Rp 910.000.001 – Rp 1.400.000.000', rate: '33.00%' },
    { range: 'Lebih dari Rp 1.400.000.000', rate: '34.00%' }
];

const terRatesB = [
    { range: 's.d. Rp 6.200.000', rate: '0%' },
    { range: 'Rp 6.200.001 – Rp 6.500.000', rate: '0.25%' },
    { range: 'Rp 6.500.001 – Rp 6.850.000', rate: '0.50%' },
    { range: 'Rp 6.850.001 – Rp 7.300.000', rate: '0.75%' },
    { range: 'Rp 7.300.001 – Rp 9.200.000', rate: '1.00%' },
    { range: 'Rp 9.200.001 – Rp 10.750.000', rate: '1.50%' },
    { range: 'Rp 10.750.001 – Rp 11.250.000', rate: '2.00%' },
    { range: 'Rp 11.250.001 – Rp 11.600.000', rate: '2.50%' },
    { range: 'Rp 11.600.001 – Rp 12.600.000', rate: '3.00%' },
    { range: 'Rp 12.600.001 – Rp 13.600.000', rate: '4.00%' },
    { range: 'Rp 13.600.001 – Rp 14.950.000', rate: '5.00%' },
    { range: 'Rp 14.950.001 – Rp 16.400.000', rate: '6.00%' },
    { range: 'Rp 16.400.001 – Rp 18.450.000', rate: '7.00%' },
    { range: 'Rp 18.450.001 – Rp 21.850.000', rate: '8.00%' },
    { range: 'Rp 21.850.001 – Rp 26.000.000', rate: '9.00%' },
    { range: 'Rp 26.000.001 – Rp 27.700.000', rate: '10.00%' },
    { range: 'Rp 27.700.001 – Rp 29.350.000', rate: '11.00%' },
    { range: 'Rp 29.350.001 – Rp 31.450.000', rate: '12.00%' },
    { range: 'Rp 31.450.001 – Rp 33.950.000', rate: '13.00%' },
    { range: 'Rp 33.950.001 – Rp 37.100.000', rate: '14.00%' },
    { range: 'Rp 37.100.001 – Rp 41.100.000', rate: '15.00%' },
    { range: 'Rp 41.100.001 – Rp 45.800.000', rate: '16.00%' },
    { range: 'Rp 45.800.001 – Rp 49.500.000', rate: '17.00%' },
    { range: 'Rp 49.500.001 – Rp 53.800.000', rate: '18.00%' },
    { range: 'Rp 53.800.001 – Rp 58.500.000', rate: '19.00%' },
    { range: 'Rp 58.500.001 – Rp 64.000.000', rate: '20.00%' },
    { range: 'Rp 64.000.001 – Rp 71.000.000', rate: '21.00%' },
    { range: 'Rp 71.000.001 – Rp 80.000.000', rate: '22.00%' },
    { range: 'Rp 80.000.001 – Rp 93.000.000', rate: '23.00%' },
    { range: 'Rp 93.000.001 – Rp 109.000.000', rate: '24.00%' },
    { range: 'Rp 109.000.001 – Rp 129.000.000', rate: '25.00%' },
    { range: 'Rp 129.000.001 – Rp 163.000.000', rate: '26.00%' },
    { range: 'Rp 163.000.001 – Rp 211.000.000', rate: '27.00%' },
    { range: 'Rp 211.000.001 – Rp 374.000.000', rate: '28.00%' },
    { range: 'Rp 374.000.001 – Rp 459.000.000', rate: '29.00%' },
    { range: 'Rp 459.000.001 – Rp 555.000.000', rate: '30.00%' },
    { range: 'Rp 555.000.001 – Rp 704.000.000', rate: '31.00%' },
    { range: 'Rp 704.000.001 – Rp 957.000.000', rate: '32.00%' },
    { range: 'Rp 957.000.001 – Rp 1.405.000.000', rate: '33.00%' },
    { range: 'Lebih dari Rp 1.405.000.000', rate: '34.00%' }
];

const terRatesC = [
    { range: 's.d. Rp 6.600.000', rate: '0%' },
    { range: 'Rp 6.600.001 – Rp 6.950.000', rate: '0.25%' },
    { range: 'Rp 6.950.001 – Rp 7.350.000', rate: '0.50%' },
    { range: 'Rp 7.350.001 – Rp 7.800.000', rate: '0.75%' },
    { range: 'Rp 7.800.001 – Rp 8.850.000', rate: '1.00%' },
    { range: 'Rp 8.850.001 – Rp 9.800.000', rate: '1.25%' },
    { range: 'Rp 9.800.001 – Rp 10.950.000', rate: '1.50%' },
    { range: 'Rp 10.950.001 – Rp 11.200.000', rate: '1.75%' },
    { range: 'Rp 11.200.001 – Rp 12.050.000', rate: '2.00%' },
    { range: 'Rp 12.050.001 – Rp 12.950.000', rate: '3.00%' },
    { range: 'Rp 12.950.001 – Rp 14.150.000', rate: '4.00%' },
    { range: 'Rp 14.150.001 – Rp 15.550.000', rate: '5.00%' },
    { range: 'Rp 15.550.001 – Rp 17.050.000', rate: '6.00%' },
    { range: 'Rp 17.050.001 – Rp 19.500.000', rate: '7.00%' },
    { range: 'Rp 19.500.001 – Rp 22.700.000', rate: '8.00%' },
    { range: 'Rp 22.700.001 – Rp 26.600.000', rate: '9.00%' },
    { range: 'Rp 26.600.001 – Rp 28.100.000', rate: '10.00%' },
    { range: 'Rp 28.100.001 – Rp 30.100.000', rate: '11.00%' },
    { range: 'Rp 30.100.001 – Rp 32.600.000', rate: '12.00%' },
    { range: 'Rp 32.600.001 – Rp 35.400.000', rate: '13.00%' },
    { range: 'Rp 35.400.001 – Rp 38.900.000', rate: '14.00%' },
    { range: 'Rp 38.900.001 – Rp 43.000.000', rate: '15.00%' },
    { range: 'Rp 43.000.001 – Rp 47.400.000', rate: '16.00%' },
    { range: 'Rp 47.400.001 – Rp 51.200.000', rate: '17.00%' },
    { range: 'Rp 51.200.001 – Rp 55.800.000', rate: '18.00%' },
    { range: 'Rp 55.800.001 – Rp 60.400.000', rate: '19.00%' },
    { range: 'Rp 60.400.001 – Rp 66.700.000', rate: '20.00%' },
    { range: 'Rp 66.700.001 – Rp 74.500.000', rate: '21.00%' },
    { range: 'Rp 74.500.001 – Rp 83.200.000', rate: '22.00%' },
    { range: 'Rp 83.200.001 – Rp 95.600.000', rate: '23.00%' },
    { range: 'Rp 95.600.001 – Rp 110.000.000', rate: '24.00%' },
    { range: 'Rp 110.000.001 – Rp 134.000.000', rate: '25.00%' },
    { range: 'Rp 134.000.001 – Rp 169.000.000', rate: '26.00%' },
    { range: 'Rp 169.000.001 – Rp 221.000.000', rate: '27.00%' },
    { range: 'Rp 221.000.001 – Rp 390.000.000', rate: '28.00%' },
    { range: 'Rp 390.000.001 – Rp 463.000.000', rate: '29.00%' },
    { range: 'Rp 463.000.001 – Rp 561.000.000', rate: '30.00%' },
    { range: 'Rp 561.000.001 – Rp 709.000.000', rate: '31.00%' },
    { range: 'Rp 709.000.001 – Rp 965.000.000', rate: '32.00%' },
    { range: 'Rp 965.000.001 – Rp 1.419.000.000', rate: '33.00%' },
    { range: 'Lebih dari Rp 1.419.000.000', rate: '34.00%' }
];

window.selectFormTerCategory = function(category) {
    const cats = ['A', 'B', 'C'];
    cats.forEach(c => {
        const panel = document.getElementById(`formTerPanel${c}`);
        const tab = document.getElementById(`formTerTab${c}`);
        if (panel) {
            panel.style.display = (c === category) ? 'block' : 'none';
        }
        if (tab) {
            if (c === category) {
                tab.style.background = '#eff6ff';
                tab.style.borderColor = '#dbeafe';
                tab.style.color = '#1d4ed8';
            } else {
                tab.style.background = 'white';
                tab.style.borderColor = '#e2e8f0';
                tab.style.color = '#475569';
            }
        }
    });
}

function populateFormTerTables() {
    const renderTable = (tbodyId, data) => {
        const tbody = document.getElementById(tbodyId);
        if (tbody) {
            tbody.innerHTML = data.map(item => `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 6px 10px; color: #334155;">${item.range}</td>
                    <td style="padding: 6px 10px; text-align: right; font-weight: 600; color: #0f172a;">${item.rate}</td>
                </tr>
            `).join('');
        }
    };
    renderTable('formTerTableBodyA', terRatesA);
    renderTable('formTerTableBodyB', terRatesB);
    renderTable('formTerTableBodyC', terRatesC);
}


// ===== 4. TAX & BPJS SCHEMES =====
async function renderTaxSchemes() {
    try {
        const bpjsTableBody = document.getElementById('bpjsSchemesTableBody');
        const pph21TableBody = document.getElementById('pph21SchemesTableBody');
        if (bpjsTableBody) {
            bpjsTableBody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        if (pph21TableBody) {
            pph21TableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;
        }
        
        const response = await fetch(`${API_URL}/tax-schemes`);
        const allSchemes = await response.json();
        
        // Filter schemes by tipe
        taxSchemes = allSchemes.filter(s => s.tipe === 'pph21');
        bpjsSchemes = allSchemes.filter(s => s.tipe === 'bpjs' && !(s.nama && s.nama.startsWith('Custom BPJS -')));

        // Render using helper functions
        renderBpjsTable(bpjsSchemes);
        renderPph21Table(taxSchemes);
    } catch (err) { console.error(err); }
}

function bukaModalBpjs(mode, id = null) {
    document.getElementById('modalBpjs').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    
    if (mode === 'edit' && id) {
        const scheme = bpjsSchemes.find(s => s.id == id);
        if (scheme) {
            document.getElementById('bpjsId').value = scheme.id;
            document.getElementById('bpjsNama').value = scheme.nama;
            
            document.getElementById('bpjsKesKaryawan').value = scheme.bpjs_kes_karyawan !== undefined && scheme.bpjs_kes_karyawan !== null ? scheme.bpjs_kes_karyawan : "1.00";
            document.getElementById('bpjsKesPerusahaan').value = scheme.bpjs_kes_perusahaan !== undefined && scheme.bpjs_kes_perusahaan !== null ? scheme.bpjs_kes_perusahaan : "4.00";
            document.getElementById('bpjsKesMaxSalary').value = formatRupiah(scheme.bpjs_kes_max_salary !== undefined && scheme.bpjs_kes_max_salary !== null ? parseFloat(scheme.bpjs_kes_max_salary) : 12000000);
            
            document.getElementById('bpjsJhtKaryawan').value = scheme.bpjs_jht_karyawan !== undefined && scheme.bpjs_jht_karyawan !== null ? scheme.bpjs_jht_karyawan : "2.00";
            document.getElementById('bpjsJhtPerusahaan').value = scheme.bpjs_jht_perusahaan !== undefined && scheme.bpjs_jht_perusahaan !== null ? scheme.bpjs_jht_perusahaan : "3.70";
            
            document.getElementById('bpjsJpKaryawan').value = scheme.bpjs_jp_karyawan !== undefined && scheme.bpjs_jp_karyawan !== null ? scheme.bpjs_jp_karyawan : "1.00";
            document.getElementById('bpjsJpPerusahaan').value = scheme.bpjs_jp_perusahaan !== undefined && scheme.bpjs_jp_perusahaan !== null ? scheme.bpjs_jp_perusahaan : "2.00";
            document.getElementById('bpjsJpMaxSalary').value = formatRupiah(scheme.bpjs_jp_max_salary !== undefined && scheme.bpjs_jp_max_salary !== null ? parseFloat(scheme.bpjs_jp_max_salary) : 10024600);
            
            document.getElementById('bpjsJkkPerusahaan').value = scheme.bpjs_jkk_perusahaan !== undefined && scheme.bpjs_jkk_perusahaan !== null ? scheme.bpjs_jkk_perusahaan : "0.24";
            document.getElementById('bpjsJkmPerusahaan').value = scheme.bpjs_jkm_perusahaan !== undefined && scheme.bpjs_jkm_perusahaan !== null ? scheme.bpjs_jkm_perusahaan : "0.30";
            
            document.getElementById('modalBpjsTitle').innerText = 'Edit BPJS Scheme';
        }
    } else {
        document.getElementById('formBpjs').reset();
        document.getElementById('bpjsId').value = '';
        document.getElementById('bpjsKesKaryawan').value = "1.00";
        document.getElementById('bpjsKesPerusahaan').value = "4.00";
        document.getElementById('bpjsKesMaxSalary').value = formatRupiah(12000000);
        document.getElementById('bpjsJhtKaryawan').value = "2.00";
        document.getElementById('bpjsJhtPerusahaan').value = "3.70";
        document.getElementById('bpjsJpKaryawan').value = "1.00";
        document.getElementById('bpjsJpPerusahaan').value = "2.00";
        document.getElementById('bpjsJpMaxSalary').value = formatRupiah(10024600);
        document.getElementById('bpjsJkkPerusahaan').value = "0.24";
        document.getElementById('bpjsJkmPerusahaan').value = "0.30";
        document.getElementById('modalBpjsTitle').innerText = 'Add BPJS Scheme';
    }
}

function tutupModalBpjs() {
    document.getElementById('modalBpjs').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

// Global function to update sub-metode options in PPh 21 modal
window.handlePph21MetodeChange = function() {
    const mainMetode = document.getElementById('pph21Metode').value;
    const subMetodeGroup = document.getElementById('groupPph21SubMetode');
    const subMetodeSelect = document.getElementById('pph21SubMetode');
    
    // Hide all reference panels
    const refPanels = ['pph21FormPanel1', 'pph21FormPanel2', 'pph21FormPanel3', 'pph21FormPanel4', 'pph21FormRefPlaceholder'];
    refPanels.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
    
    if (!mainMetode) {
        subMetodeGroup.style.display = 'none';
        const placeholder = document.getElementById('pph21FormRefPlaceholder');
        if (placeholder) placeholder.style.display = 'flex';
        return;
    }
    
    // Show correct reference panel
    let activePanelId = '';
    if (mainMetode === 'Kategori Penerima') activePanelId = 'pph21FormPanel1';
    else if (mainMetode === 'TER') activePanelId = 'pph21FormPanel2';
    else if (mainMetode === 'Progresif') activePanelId = 'pph21FormPanel3';
    else if (mainMetode === 'PTKP') activePanelId = 'pph21FormPanel4';
    
    const activePanel = document.getElementById(activePanelId);
    if (activePanel) activePanel.style.display = 'block';
    
    subMetodeGroup.style.display = 'block';
    subMetodeSelect.innerHTML = '';
    
    let options = [];
    if (mainMetode === 'Kategori Penerima') {
        options = [
            'Pegawai Tetap',
            'Penerima Pensiun Berkala',
            'Pegawai Tidak Tetap / Tenaga Kerja Lepas',
            'Bukan Pegawai',
            'Penerima Pesangon',
            'Peserta Kegiatan'
        ];
    } else if (mainMetode === 'TER') {
        options = [
            'TER Kategori A',
            'TER Kategori B',
            'TER Kategori C'
        ];
    } else if (mainMetode === 'Progresif') {
        options = [
            'Progresif Lapisan 1 (5%)',
            'Progresif Lapisan 2 (15%)',
            'Progresif Lapisan 3 (25%)',
            'Progresif Lapisan 4 (30%)',
            'Progresif Lapisan 5 (35%)',
            'Progresif Kumulatif'
        ];
    } else if (mainMetode === 'PTKP') {
        options = [
            'PTKP TK/0',
            'PTKP TK/1',
            'PTKP TK/2',
            'PTKP TK/3',
            'PTKP K/0',
            'PTKP K/1',
            'PTKP K/2',
            'PTKP K/3'
        ];
    }
    
    options.forEach(opt => {
        const el = document.createElement('option');
        el.value = opt;
        el.innerText = opt;
        subMetodeSelect.appendChild(el);
    });
    
    // Attach event handler to sub-metode selection change if not already attached
    if (!subMetodeSelect.dataset.hasListener) {
        subMetodeSelect.addEventListener('change', window.highlightFormSubMetodeRow);
        subMetodeSelect.dataset.hasListener = 'true';
    }
    
    // Trigger highlight immediately
    window.highlightFormSubMetodeRow();
}

window.highlightFormSubMetodeRow = function() {
    const mainMetode = document.getElementById('pph21Metode').value;
    const subMetode = document.getElementById('pph21SubMetode').value;
    
    // Reset highlights for Kategori Penerima
    for (let i = 1; i <= 6; i++) {
        const row = document.getElementById(`formRec${i}`);
        if (row) {
            row.style.background = '#f8fafc';
            row.style.borderColor = '#e2e8f0';
            row.style.boxShadow = 'none';
            const iconDiv = row.querySelector('div');
            if (iconDiv) {
                iconDiv.style.background = '#f1f5f9';
                iconDiv.style.color = '#475569';
            }
        }
    }
    
    // Reset highlights for Progresif
    for (let i = 1; i <= 5; i++) {
        const row = document.getElementById(`formProgRow${i}`);
        if (row) {
            row.style.background = '';
            row.style.fontWeight = 'normal';
            row.style.boxShadow = '';
        }
    }
    
    // Reset highlights for PTKP
    const ptkpStatuses = ['TK0', 'TK1', 'TK2', 'TK3', 'K0', 'K1', 'K2', 'K3'];
    ptkpStatuses.forEach(st => {
        const row = document.getElementById(`formPtkpRow${st}`);
        if (row) {
            row.style.background = '';
            row.style.boxShadow = '';
        }
    });
    
    if (mainMetode === 'Kategori Penerima') {
        const recMap = {
            'Pegawai Tetap': 'formRec1',
            'Penerima Pensiun Berkala': 'formRec2',
            'Pegawai Tidak Tetap / Tenaga Kerja Lepas': 'formRec3',
            'Bukan Pegawai': 'formRec4',
            'Penerima Pesangon': 'formRec5',
            'Peserta Kegiatan': 'formRec6'
        };
        const activeId = recMap[subMetode];
        if (activeId) {
            const el = document.getElementById(activeId);
            if (el) {
                el.style.background = '#eff6ff';
                el.style.borderColor = '#3b82f6';
                el.style.boxShadow = '0 2px 8px rgba(59, 130, 246, 0.05)';
                const iconDiv = el.querySelector('div');
                if (iconDiv) {
                    iconDiv.style.background = '#3b82f6';
                    iconDiv.style.color = '#ffffff';
                }
            }
        }
    } else if (mainMetode === 'TER') {
        if (subMetode === 'TER Kategori A') {
            window.selectFormTerCategory('A');
        } else if (subMetode === 'TER Kategori B') {
            window.selectFormTerCategory('B');
        } else if (subMetode === 'TER Kategori C') {
            window.selectFormTerCategory('C');
        }
    } else if (mainMetode === 'Progresif') {
        const progMap = {
            'Progresif Lapisan 1 (5%)': 'formProgRow1',
            'Progresif Lapisan 2 (15%)': 'formProgRow2',
            'Progresif Lapisan 3 (25%)': 'formProgRow3',
            'Progresif Lapisan 4 (30%)': 'formProgRow4',
            'Progresif Lapisan 5 (35%)': 'formProgRow5'
        };
        const activeId = progMap[subMetode];
        if (activeId) {
            const el = document.getElementById(activeId);
            if (el) {
                el.style.background = '#eff6ff';
                el.style.fontWeight = 'bold';
                el.style.boxShadow = 'inset 4px 0 0 #3b82f6';
            }
        } else if (subMetode === 'Progresif Kumulatif') {
            // Highlight all rows in progresif
            for (let i = 1; i <= 5; i++) {
                const el = document.getElementById(`formProgRow${i}`);
                if (el) {
                    el.style.background = '#eff6ff';
                    el.style.boxShadow = 'inset 4px 0 0 #3b82f6';
                }
            }
        }
    } else if (mainMetode === 'PTKP') {
        const cleanStatus = subMetode.replace('PTKP ', '').replace('/', ''); // "TK/0" -> "TK0"
        const el = document.getElementById(`formPtkpRow${cleanStatus}`);
        if (el) {
            el.style.background = '#eff6ff';
            el.style.boxShadow = 'inset 4px 0 0 #3b82f6';
        }
    }
}

window.toggleFormReference = function(forceShow = null) {
    const modal = document.getElementById('modalPph21');
    const modalBody = document.getElementById('modalPph21Body');
    const refContainer = document.getElementById('pph21FormReferenceContainer');
    const btn = document.getElementById('btnShowFormRef');
    
    if (!modal || !modalBody || !refContainer || !btn) return;
    
    const isCurrentlyHidden = refContainer.style.display === 'none';
    const shouldShow = forceShow !== null ? forceShow : isCurrentlyHidden;
    
    if (shouldShow) {
        modal.style.width = '1100px';
        modalBody.style.gridTemplateColumns = '380px 1fr';
        refContainer.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-eye-slash"></i> Sembunyikan Detail';
        
        // Trigger calculation highlighting to match dropdown choice
        window.highlightFormSubMetodeRow();
    } else {
        modal.style.width = '500px';
        modalBody.style.gridTemplateColumns = '1fr';
        refContainer.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-eye"></i> Lihat Detail';
    }
}

function bukaModalPph21(mode, id = null) {
    document.getElementById('modalPph21').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    
    // Reset reference panel to collapsed state by default
    window.toggleFormReference(false);
    
    // Populate TER rates tables inside form reference panel
    populateFormTerTables();
    
    // Hide submetode group by default
    document.getElementById('groupPph21SubMetode').style.display = 'none';
    
    if (mode === 'edit' && id) {
        const scheme = taxSchemes.find(s => s.id == id);
        if (scheme) {
            document.getElementById('pph21Id').value = scheme.id;
            document.getElementById('pph21Nama').value = scheme.nama;
            
            // Map metode to main and sub options
            let mainMetode = '';
            let subMetode = scheme.metode || '';
            
            const pph21MetodeMap = {
                'Pegawai Tetap': 'Kategori Penerima',
                'Penerima Pensiun Berkala': 'Kategori Penerima',
                'Pegawai Tidak Tetap / Tenaga Kerja Lepas': 'Kategori Penerima',
                'Bukan Pegawai': 'Kategori Penerima',
                'Penerima Pesangon': 'Kategori Penerima',
                'Peserta Kegiatan': 'Kategori Penerima',
                
                'TER Kategori A': 'TER',
                'TER Kategori B': 'TER',
                'TER Kategori C': 'TER',
                
                'Progresif Lapisan 1 (5%)': 'Progresif',
                'Progresif Lapisan 2 (15%)': 'Progresif',
                'Progresif Lapisan 3 (25%)': 'Progresif',
                'Progresif Lapisan 4 (30%)': 'Progresif',
                'Progresif Lapisan 5 (35%)': 'Progresif',
                'Progresif Kumulatif': 'Progresif',
                
                'PTKP TK/0': 'PTKP',
                'PTKP TK/1': 'PTKP',
                'PTKP TK/2': 'PTKP',
                'PTKP TK/3': 'PTKP',
                'PTKP K/0': 'PTKP',
                'PTKP K/1': 'PTKP',
                'PTKP K/2': 'PTKP',
                'PTKP K/3': 'PTKP'
            };
            
            mainMetode = pph21MetodeMap[subMetode];
            
            if (mainMetode) {
                document.getElementById('pph21Metode').value = mainMetode;
                window.handlePph21MetodeChange();
                document.getElementById('pph21SubMetode').value = subMetode;
            } else {
                // Compatibility for legacy (Gross, Gross Up, Nett)
                document.getElementById('pph21Metode').value = 'Kategori Penerima';
                window.handlePph21MetodeChange();
                document.getElementById('pph21SubMetode').value = 'Pegawai Tetap';
            }
            
            document.getElementById('pph21Ptkp').value = scheme.ptkp_status;
            document.getElementById('pph21Deskripsi').value = scheme.deskripsi || '';
            
            document.getElementById('modalPph21Title').innerText = 'Edit PPh 21 Scheme';
        }
    } else {
        document.getElementById('formPph21').reset();
        document.getElementById('pph21Id').value = '';
        document.getElementById('pph21Metode').value = '';
        window.handlePph21MetodeChange(); // Trigger display reset to placeholder
        document.getElementById('modalPph21Title').innerText = 'Add PPh 21 Scheme';
    }
}

function tutupModalPph21() {
    document.getElementById('modalPph21').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

// Form BPJS submit handler
if (document.getElementById('formBpjs')) {
    document.getElementById('formBpjs').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('bpjsId').value;
        const data = {
            nama: document.getElementById('bpjsNama').value,
            tipe: 'bpjs',
            bpjs_kes_karyawan: parseFloat(document.getElementById('bpjsKesKaryawan').value) || 0,
            bpjs_kes_perusahaan: parseFloat(document.getElementById('bpjsKesPerusahaan').value) || 0,
            bpjs_kes_max_salary: parseFormattedNumber(document.getElementById('bpjsKesMaxSalary').value) || 0,
            bpjs_jht_karyawan: parseFloat(document.getElementById('bpjsJhtKaryawan').value) || 0,
            bpjs_jht_perusahaan: parseFloat(document.getElementById('bpjsJhtPerusahaan').value) || 0,
            bpjs_jp_karyawan: parseFloat(document.getElementById('bpjsJpKaryawan').value) || 0,
            bpjs_jp_perusahaan: parseFloat(document.getElementById('bpjsJpPerusahaan').value) || 0,
            bpjs_jp_max_salary: parseFormattedNumber(document.getElementById('bpjsJpMaxSalary').value) || 0,
            bpjs_jkk_perusahaan: parseFloat(document.getElementById('bpjsJkkPerusahaan').value) || 0,
            bpjs_jkm_perusahaan: parseFloat(document.getElementById('bpjsJkmPerusahaan').value) || 0
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
            showToast(id ? 'BPJS scheme updated successfully!' : 'BPJS scheme added successfully!', 'success');
        } else {
            showToast('Failed to save BPJS scheme!', 'error');
        }
    });
}

// Form PPh 21 submit handler
if (document.getElementById('formPph21')) {
    document.getElementById('formPph21').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('pph21Id').value;
        const mainMetode = document.getElementById('pph21Metode').value;
        const subMetode = document.getElementById('pph21SubMetode').value;
        
        const data = {
            nama: document.getElementById('pph21Nama').value,
            tipe: 'pph21',
            metode: subMetode || mainMetode,
            ptkp_status: document.getElementById('pph21Ptkp').value,
            deskripsi: document.getElementById('pph21Deskripsi').value
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
            showToast(id ? 'PPh 21 scheme updated successfully!' : 'PPh 21 scheme added successfully!', 'success');
        } else {
            showToast('Failed to save PPh 21 scheme!', 'error');
        }
    });
}

async function hapusPajak(id) {
    if (!await showConfirm('Are you sure you want to delete this scheme?')) return;
    try {
        const res = await fetch(`${API_URL}/tax-schemes/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderTaxSchemes();
            showToast('Scheme deleted successfully!', 'success');
        } else {
            showToast('Failed to delete scheme!', 'error');
        }
    } catch (err) { console.error(err); }
}

function switchTaxTab(tab) {
    // Update tab button styles
    document.querySelectorAll('#viewPajak .ws-tab').forEach(btn => {
        btn.classList.remove('active');
    });

    const activeBtn = document.querySelector(`#viewPajak .ws-tab[data-taxtab="${tab}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }

    // Show/hide tab panels
    document.querySelectorAll('#viewPajak .tax-tab-panel').forEach(panel => {
        panel.style.display = 'none';
    });

    const panelId = 'taxTab' + (tab === 'bpjs' ? 'Bpjs' : 'Pph21');
    const activePanel = document.getElementById(panelId);
    if (activePanel) {
        activePanel.style.display = 'block';
    }
}

// Search/filter for tax schemes
function filterTaxScheme(type) {
    if (type === 'bpjs') {
        const q = (document.getElementById('searchBpjsScheme')?.value || '').toLowerCase();
        const filtered = q ? bpjsSchemes.filter(s => s.nama && s.nama.toLowerCase().includes(q)) : bpjsSchemes;
        renderBpjsTable(filtered);
    } else {
        const q = (document.getElementById('searchPph21Scheme')?.value || '').toLowerCase();
        const filtered = q ? taxSchemes.filter(s => s.nama && s.nama.toLowerCase().includes(q)) : taxSchemes;
        renderPph21Table(filtered);
    }
}

// Helper: render BPJS table body
function renderBpjsTable(schemes) {
    const bpjsTableBody = document.getElementById('bpjsSchemesTableBody');
    if (!bpjsTableBody) return;

    if (!schemes || schemes.length === 0) {
        bpjsTableBody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; color: var(--text-muted); font-style: italic; padding: 30px;">
                    <i class="fas fa-inbox" style="font-size: 36px; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                    No BPJS schemes found. Click "Add BPJS Scheme" to create one.
                </td>
            </tr>
        `;
        return;
    }

    bpjsTableBody.innerHTML = schemes.map(scheme => `
        <tr style="border-bottom: 1px solid #f1f5f9;">
            <td style="padding: 14px; font-weight: 600; color: var(--secondary-color);">
                <i class="fas fa-shield-alt" style="color: var(--primary-color); margin-right: 6px;"></i> ${scheme.nama}
            </td>
            <td style="padding: 14px;">
                <div style="margin-bottom: 2px;">Karyawan: <b>${scheme.bpjs_kes_karyawan !== undefined && scheme.bpjs_kes_karyawan !== null ? parseFloat(scheme.bpjs_kes_karyawan) : 1}%</b></div>
                <div style="margin-bottom: 2px;">Perusahaan: <b>${scheme.bpjs_kes_perusahaan !== undefined && scheme.bpjs_kes_perusahaan !== null ? parseFloat(scheme.bpjs_kes_perusahaan) : 4}%</b></div>
                <div style="font-size: 11px; color: #64748b;">Max: IDR ${formatRupiah(parseFloat(scheme.bpjs_kes_max_salary || 12000000))}</div>
            </td>
            <td style="padding: 14px;">
                <div style="margin-bottom: 2px;">Karyawan: <b>${scheme.bpjs_jht_karyawan !== undefined && scheme.bpjs_jht_karyawan !== null ? parseFloat(scheme.bpjs_jht_karyawan) : 2}%</b></div>
                <div>Perusahaan: <b>${scheme.bpjs_jht_perusahaan !== undefined && scheme.bpjs_jht_perusahaan !== null ? parseFloat(scheme.bpjs_jht_perusahaan) : 3.7}%</b></div>
            </td>
            <td style="padding: 14px;">
                <div style="margin-bottom: 2px;">Karyawan: <b>${scheme.bpjs_jp_karyawan !== undefined && scheme.bpjs_jp_karyawan !== null ? parseFloat(scheme.bpjs_jp_karyawan) : 1}%</b></div>
                <div style="margin-bottom: 2px;">Perusahaan: <b>${scheme.bpjs_jp_perusahaan !== undefined && scheme.bpjs_jp_perusahaan !== null ? parseFloat(scheme.bpjs_jp_perusahaan) : 2}%</b></div>
                <div style="font-size: 11px; color: #64748b;">Max: IDR ${formatRupiah(parseFloat(scheme.bpjs_jp_max_salary || 10024600))}</div>
            </td>
            <td style="padding: 14px;">
                <div style="margin-bottom: 2px;">JKK (Perush.): <b>${scheme.bpjs_jkk_perusahaan !== undefined && scheme.bpjs_jkk_perusahaan !== null ? parseFloat(scheme.bpjs_jkk_perusahaan) : 0.24}%</b></div>
                <div>JKM (Perush.): <b>${scheme.bpjs_jkm_perusahaan !== undefined && scheme.bpjs_jkm_perusahaan !== null ? parseFloat(scheme.bpjs_jkm_perusahaan) : 0.3}%</b></div>
            </td>
            <td style="text-align: center; padding: 14px;">
                <div style="display: flex; gap: 8px; justify-content: center;">
                    <button class="btn-icon btn-edit" onclick="bukaModalBpjs('edit', ${scheme.id})" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn-icon btn-delete" onclick="hapusPajak(${scheme.id})" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Helper: render PPh 21 table body
function renderPph21Table(schemes) {
    const pph21TableBody = document.getElementById('pph21SchemesTableBody');
    if (!pph21TableBody) return;

    if (!schemes || schemes.length === 0) {
        pph21TableBody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; color: var(--text-muted); font-style: italic; padding: 30px;">
                    <i class="fas fa-inbox" style="font-size: 36px; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                    No PPh 21 schemes found. Click "Add PPh 21 Scheme" to create one.
                </td>
            </tr>
        `;
        return;
    }

    pph21TableBody.innerHTML = schemes.map(scheme => `
        <tr style="border-bottom: 1px solid #f1f5f9;">
            <td style="padding: 14px; font-weight: 600; color: var(--secondary-color);">
                <i class="fas fa-calculator" style="color: var(--danger); margin-right: 6px;"></i> ${scheme.nama}
            </td>
            <td style="padding: 14px;">
                <span style="background-color: rgba(243, 156, 18, 0.15); color: var(--primary-dark); padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 12px; border: 1px solid rgba(243, 156, 18, 0.25);">
                    ${scheme.metode}
                </span>
            </td>
            <td style="padding: 14px; font-weight: 600;">
                ${scheme.ptkp_status}
            </td>
            <td style="padding: 14px; color: #64748b; font-style: italic;">
                ${scheme.deskripsi || '-'}
            </td>
            <td style="text-align: center; padding: 14px;">
                <div style="display: flex; gap: 8px; justify-content: center;">
                    <button class="btn-icon" onclick="bukaDetailPph21(${scheme.id})" title="Lihat Detail PPh 21" style="background: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe; border-radius: 8px; width: 36px; height: 36px; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fas fa-eye"></i></button>
                    <button class="btn-icon btn-edit" onclick="bukaModalPph21('edit', ${scheme.id})" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn-icon btn-delete" onclick="hapusPajak(${scheme.id})" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ===== PPh 21 DETAIL MODAL =====
window.selectPph21Topic = function(topicId) {
    // Show active panel, hide others
    for (let i = 1; i <= 4; i++) {
        const panel = document.getElementById(`pph21Panel${i}`);
        const card = document.getElementById(`pph21Topic${i}`);
        if (panel) {
            panel.style.display = (i === topicId) ? 'block' : 'none';
        }
        if (card) {
            if (i === topicId) {
                card.style.borderColor = '#3b82f6';
                card.style.background = '#f8fafc';
                card.style.boxShadow = '0 4px 12px rgba(59, 130, 246, 0.08)';
            } else {
                card.style.borderColor = '#e2e8f0';
                card.style.background = 'white';
                card.style.boxShadow = 'none';
            }
        }
    }
}

window.selectTerCategory = function(category) {
    const cats = ['A', 'B', 'C'];
    cats.forEach(c => {
        const panel = document.getElementById(`terPanel${c}`);
        const tab = document.getElementById(`terTab${c}`);
        if (panel) {
            panel.style.display = (c === category) ? 'block' : 'none';
        }
        if (tab) {
            if (c === category) {
                tab.style.background = '#eff6ff';
                tab.style.borderColor = '#dbeafe';
                tab.style.color = '#1d4ed8';
            } else {
                tab.style.background = 'white';
                tab.style.borderColor = '#e2e8f0';
                tab.style.color = '#475569';
            }
        }
    });
}

function bukaDetailPph21(schemeId) {
    const scheme = taxSchemes.find(s => s.id == schemeId);
    if (!scheme) return;

    // Fill scheme info cards
    document.getElementById('detailPph21SchemeInfoBar').style.display = 'grid';
    document.getElementById('detailPph21SchemeName').innerText = scheme.nama || '-';
    document.getElementById('detailPph21Method').innerText = scheme.metode || '-';
    document.getElementById('detailPph21PTKP').innerText = scheme.ptkp_status || '-';

    // Highlight active PTKP row
    highlightPtkpRow(scheme.ptkp_status);
    // First, reset all detail items' display state to flex/block/table-row before making decisions
    // 1. Reset Recipient cards
    for (let i = 1; i <= 6; i++) {
        const el = document.getElementById(`detailRec${i}`);
        if (el) el.style.display = 'flex';
    }
    // 2. Reset TER tabs wrapper
    const tabWrapper = document.getElementById('detailTerTabsWrapper');
    if (tabWrapper) tabWrapper.style.display = 'flex';
    
    // 3. Reset Progresif rows
    for (let i = 1; i <= 5; i++) {
        const el = document.getElementById(`detailProgRow${i}`);
        if (el) el.style.display = 'table-row';
    }
    // 4. Reset PTKP rows
    const ptkpKeys = ['TK0', 'TK1', 'TK2', 'TK3', 'K0', 'K1', 'K2', 'K3'];
    ptkpKeys.forEach(k => {
        const el = document.getElementById(`detailPtkpRow${k}`);
        if (el) el.style.display = 'table-row';
    });

    // Auto-select topic based on the scheme's metode/sub-metode
    const metode = scheme.metode || '';
    
    // Kategori Penerima mapping
    const recMap = {
        'Pegawai Tetap': 1,
        'Penerima Pensiun Berkala': 2,
        'Pegawai Tidak Tetap / Tenaga Kerja Lepas': 3,
        'Bukan Pegawai': 4,
        'Penerima Pesangon / Manfaat Pensiun Sekaligus': 5,
        'Peserta Kegiatan': 6
    };
    const activeRecId = recMap[metode];

    if (activeRecId) {
        window.selectPph21Topic(1);
        for (let i = 1; i <= 6; i++) {
            const el = document.getElementById(`detailRec${i}`);
            if (el) el.style.display = (i === activeRecId) ? 'flex' : 'none';
        }
    } else if (metode.startsWith('TER')) {
        window.selectPph21Topic(2);
        if (tabWrapper) tabWrapper.style.display = 'none'; // Hide category selection tabs
        
        if (metode.includes('Kategori A')) {
            window.selectTerCategory('A');
        } else if (metode.includes('Kategori B')) {
            window.selectTerCategory('B');
        } else if (metode.includes('Kategori C')) {
            window.selectTerCategory('C');
        } else {
            window.selectTerCategory('A');
        }
    } else if (metode.startsWith('Progresif')) {
        window.selectPph21Topic(3);
        const progMap = {
            'Progresif Lapisan 1 (5%)': 1,
            'Progresif Lapisan 2 (15%)': 2,
            'Progresif Lapisan 3 (25%)': 3,
            'Progresif Lapisan 4 (30%)': 4,
            'Progresif Lapisan 5 (35%)': 5
        };
        const activeProgId = progMap[metode];
        if (activeProgId) {
            for (let i = 1; i <= 5; i++) {
                const el = document.getElementById(`detailProgRow${i}`);
                if (el) el.style.display = (i === activeProgId) ? 'table-row' : 'none';
            }
        }
    } else if (metode.startsWith('PTKP')) {
        window.selectPph21Topic(4);
        const activePtkpKey = metode.replace('PTKP ', '').replace('/', ''); // "PTKP TK/0" -> "TK0"
        ptkpKeys.forEach(k => {
            const el = document.getElementById(`detailPtkpRow${k}`);
            if (el) el.style.display = (k === activePtkpKey) ? 'table-row' : 'none';
        });
    } else {
        // Fallback default to topic 1
        window.selectPph21Topic(1);
    }

    // Show the modal
    document.getElementById('modalDetailPph21').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function tutupDetailPph21Modal() {
    document.getElementById('modalDetailPph21').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function highlightPtkpRow(ptkpStatus) {
    // Map PTKP status to row element IDs
    const ptkpRowMap = {
        'TK/0': 'ptkpRowTK0',
        'TK/1': 'ptkpRowTK1',
        'TK/2': 'ptkpRowTK2',
        'TK/3': 'ptkpRowTK3',
        'K/0': 'ptkpRowK0',
        'K/1': 'ptkpRowK1',
        'K/2': 'ptkpRowK2',
        'K/3': 'ptkpRowK3'
    };

    // Reset all rows
    Object.values(ptkpRowMap).forEach(rowId => {
        const row = document.getElementById(rowId);
        if (row) {
            row.style.background = '';
            row.style.boxShadow = '';
        }
    });

    // Highlight active row
    const activeRowId = ptkpRowMap[ptkpStatus];
    if (activeRowId) {
        const row = document.getElementById(activeRowId);
        if (row) {
            row.style.background = 'linear-gradient(90deg, #eff6ff 0%, #dbeafe 100%)';
            row.style.boxShadow = 'inset 4px 0 0 #3b82f6';
        }
    }
}
