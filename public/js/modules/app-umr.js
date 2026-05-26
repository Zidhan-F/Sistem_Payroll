// ===== UMR (UMP/UMK) MODULE =====
// Extracted from app.js for modular monolith architecture

// ===== UMP / UMK MODULE =====

let currentUmrType = 'UMP';
let umrFilteredData = [];
let umrCurrentPage = 1;
const UMR_PER_PAGE = 10;

function formatNominal(val) {
    return new Intl.NumberFormat('id-ID').format(val || 0);
}

async function renderUmrTable() {
    try {
        const tipe = document.getElementById('selectUmrType')?.value || currentUmrType;
        const tahun = document.getElementById('selectUmrYear')?.value || new Date().getFullYear();
        
        const response = await fetch(`${API_URL}/minimum-wages?tipe=${tipe}&tahun=${tahun}`);
        umrAllData = await response.json();
        
        currentUmrType = tipe;

        const tabUmp = document.getElementById('tabUmp');
        const tabUmk = document.getElementById('tabUmk');
        const tabNominal = document.getElementById('tabNominal');
        const tableArea = document.getElementById('umrTableArea');
        const nominalArea = document.getElementById('umrNominalArea');

        // Reset all tabs to inactive style
        const resetTabs = () => {
            [tabUmp, tabUmk, tabNominal].forEach(tab => {
                if (tab) {
                    tab.className = 'umr-tab-btn';
                    tab.style.background = 'transparent';
                    tab.style.border = '1px solid transparent';
                    tab.style.borderBottom = 'none';
                    tab.style.color = '#0d6efd';
                    tab.style.zIndex = '1';
                }
            });
        };

        const setActiveTab = (tab) => {
            if (tab) {
                tab.className = 'umr-tab-btn active';
                tab.style.background = 'white';
                tab.style.border = '1px solid #ddd';
                tab.style.borderBottom = '1px solid white';
                tab.style.zIndex = '2';
            }
        };

        resetTabs();

        if (tipe === 'NOMINAL') {
            setActiveTab(tabNominal);
            if (tableArea) tableArea.style.display = 'none';
            if (nominalArea) nominalArea.style.display = 'block';
            updateNominalDisplay();
        } else {
            if (tableArea) tableArea.style.display = 'block';
            if (nominalArea) nominalArea.style.display = 'none';
            
            if (tipe === 'UMP') {
                setActiveTab(tabUmp);
                const searchEl = document.getElementById('searchUmr');
                if (searchEl) searchEl.placeholder = 'Search Province...';
            } else {
                setActiveTab(tabUmk);
                const searchEl = document.getElementById('searchUmr');
                if (searchEl) searchEl.placeholder = 'Search City/Regency...';
            }

            // Update thead dynamically to match exact formats from UMP/UMK screenshots
            const thead = document.getElementById('tabelUmrBody')?.previousElementSibling;
            if (thead) {
                if (tipe === 'UMP') {
                    thead.innerHTML = `
                        <tr>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">StateId</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">StateCode</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">StateName</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: right; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;" class="col-nominal">UMP Nominal</th>
                        </tr>
                    `;
                } else {
                    thead.innerHTML = `
                        <tr>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">RegencyId</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">RegencyCode</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">RegencyName</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">StateId</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: right; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;" class="col-nominal">UMK Nominal</th>
                        </tr>
                    `;
                }
            }
        }
        
        // If nominal, we don't need to fetch or render the table data
        if (tipe === 'NOMINAL') return;
        
        // Dynamically populate searchUmr select options
        const searchEl = document.getElementById('searchUmr');
        if (searchEl && searchEl.tagName === 'SELECT') {
            const prevVal = searchEl.value;
            let optionsHtml = '';
            
            if (tipe === 'UMP') {
                optionsHtml += '<option value="">-- Select Province --</option>';
                const uniqueProvinces = [...new Set(umrAllData.map(row => row.nama_daerah))].sort();
                uniqueProvinces.forEach(prov => {
                    optionsHtml += `<option value="${prov}" ${prov === prevVal ? 'selected' : ''}>${prov}</option>`;
                });
            } else {
                optionsHtml += '<option value="">-- Select City/Regency --</option>';
                const uniqueRegencies = [...new Set(umrAllData.map(row => row.nama_daerah))].sort();
                uniqueRegencies.forEach(reg => {
                    optionsHtml += `<option value="${reg}" ${reg === prevVal ? 'selected' : ''}>${reg}</option>`;
                });
            }
            searchEl.innerHTML = optionsHtml;
        }

        // Apply search filter
        const q = (document.getElementById('searchUmr')?.value || '').toLowerCase();
        umrFilteredData = q 
            ? umrAllData.filter(row => row.nama_daerah.toLowerCase().includes(q) || row.kode_daerah.toLowerCase().includes(q))
            : [...umrAllData];

        renderUmrPage();
    } catch (err) { console.error(err); }
}

function renderUmrPage() {
    const tbody = document.getElementById('tabelUmrBody');
    if (!tbody) return;

    const totalData = umrFilteredData.length;
    const totalPages = Math.max(1, Math.ceil(totalData / UMR_PER_PAGE));
    
    // Clamp page
    if (umrCurrentPage > totalPages) umrCurrentPage = totalPages;
    if (umrCurrentPage < 1) umrCurrentPage = 1;

    const start = (umrCurrentPage - 1) * UMR_PER_PAGE;
    const end = Math.min(start + UMR_PER_PAGE, totalData);
    const pageData = umrFilteredData.slice(start, end);

    const stateIdMap = {
        'ID 11': 5, 'ID 12': 6, 'ID 17': 7, 'ID 15': 8, 'ID 14': 9, 'ID 13': 10, 'ID 16': 11,
        'ID 18': 12, 'ID 19': 13, 'ID 21': 14, 'ID 36': 15, 'ID 32': 16, 'ID 31': 17, 'ID 33': 18,
        'ID 35': 19, 'ID 34': 20, 'ID 51': 21, 'ID 52': 22, 'ID 53': 23, 'ID 61': 24, 'ID 63': 25,
        'ID 62': 26, 'ID 64': 27, 'ID 75': 28, 'ID 73': 29, 'ID 74': 30, 'ID 72': 31, 'ID 71': 32,
        'ID 76': 33, 'ID 81': 34, 'ID 82': 35, 'ID 91': 36, 'ID 92': 37, 'ID 65': 45
    };

    if (pageData.length > 0) {
        let index = start + 1;
        tbody.innerHTML = pageData.map(row => {
            if (currentUmrType === 'UMP') {
                const stateId = stateIdMap[row.kode_daerah] || (row.provinsi || index++);
                return `
                    <tr>
                        <td class="td-code">${stateId}</td>
                        <td class="td-code">${row.kode_daerah}</td>
                        <td class="td-name">${row.nama_daerah}</td>
                        <td class="td-nominal">${formatRupiah(row.nominal)}</td>
                    </tr>
                `;
            } else {
                const regencyId = index++;
                const prefix = row.kode_daerah.split('.')[0] || '';
                const stateId = stateIdMap[prefix] || (row.provinsi || 17);
                return `
                    <tr>
                        <td class="td-code">${regencyId}</td>
                        <td class="td-code">${row.kode_daerah}</td>
                        <td class="td-name">${row.nama_daerah}</td>
                        <td class="td-code">${stateId}</td>
                        <td class="td-nominal">${formatRupiah(row.nominal)}</td>
                    </tr>
                `;
            }
        }).join('');
    } else {
        const colSpan = currentUmrType === 'UMP' ? 4 : 5;
        tbody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center; padding:40px; color:#aaa;">
                <i class="fas fa-database" style="font-size:28px; margin-bottom:10px; display:block;"></i>
                No ${currentUmrType} data available. Click <b>Upload</b> to add data.
           </td></tr>`;
    }

    // Update pagination info
    const infoEl = document.getElementById('umrPaginationInfo');
    if (infoEl) {
        infoEl.innerText = totalData > 0
            ? `Showing ${start + 1} - ${end} of ${totalData} entries`
            : 'No data';
    }

    // Render pagination controls
    const controls = document.getElementById('umrPaginationControls');
    if (controls) {
        let html = '';
        // Prev button
        html += `<button ${umrCurrentPage <= 1 ? 'disabled' : ''} onclick="goUmrPage(${umrCurrentPage - 1})"><i class="fas fa-chevron-left"></i></button>`;
        
        // Page numbers
        const maxVisible = 4;
        let pageStart = Math.max(1, umrCurrentPage - Math.floor(maxVisible / 2));
        let pageEnd = Math.min(totalPages, pageStart + maxVisible - 1);
        if (pageEnd - pageStart < maxVisible - 1) pageStart = Math.max(1, pageEnd - maxVisible + 1);

        for (let i = pageStart; i <= pageEnd; i++) {
            html += `<button class="${i === umrCurrentPage ? 'active' : ''}" onclick="goUmrPage(${i})">${i}</button>`;
        }
        
        // Next button
        html += `<button ${umrCurrentPage >= totalPages ? 'disabled' : ''} onclick="goUmrPage(${umrCurrentPage + 1})"><i class="fas fa-chevron-right"></i></button>`;
        
        controls.innerHTML = html;
    }
}

function goUmrPage(page) {
    umrCurrentPage = page;
    renderUmrPage();
}

function switchUmrTab(tipe) {
    currentUmrType = tipe;
    umrCurrentPage = 1;

    const selectType = document.getElementById('selectUmrType');
    if (selectType) selectType.value = tipe;

    // Reset search
    const search = document.getElementById('searchUmr');
    if (search) search.value = '';

    renderUmrTable();
}

function filterUmrTable() {
    umrCurrentPage = 1;
    const q = (document.getElementById('searchUmr')?.value || '').toLowerCase();
    umrFilteredData = q
        ? umrAllData.filter(row => row.nama_daerah.toLowerCase().includes(q) || row.kode_daerah.toLowerCase().includes(q))
        : [...umrAllData];
    renderUmrPage();
}

function bukaModalUploadUmr() {
    document.getElementById('modalUploadUmr').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('uploadUmrTipe').value = currentUmrType;
    
    // Change modal title based on type (UMP or UMK)
    const titleEl = document.querySelector('#modalUploadUmr .modal-header h3');
    if (titleEl) {
        titleEl.textContent = `Upload ${currentUmrType} Data`;
    }
    
    // Reset file input
    const fileInput = document.getElementById('fileUmr');
    if (fileInput) fileInput.value = '';
    const fileNameEl = document.getElementById('umrFileName');
    if (fileNameEl) fileNameEl.style.display = 'none';
}

function tutupModalUploadUmr() {
    document.getElementById('modalUploadUmr').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function handleUmrSelectChange(val) {
    if (val === 'MANUAL') {
        // Balikkan dropdown ke pilihan sebelumnya agar filter tidak rusak
        document.getElementById('selectUmrType').value = currentUmrType;
        bukaModalManualUmr();
    } else {
        switchUmrTab(val);
    }
}

function bukaModalManualUmr() {
    document.getElementById('modalManualUmr').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('manualUmrTipe').value = currentUmrType;
    document.getElementById('formManualUmr').reset();
    document.getElementById('manualUmrTipe').value = currentUmrType;
}

function tutupModalManualUmr() {
    document.getElementById('modalManualUmr').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

// CSV Download Template
function downloadTemplateUmr() {
    const tipe = currentUmrType;
    let csvContent = '';
    
    if (tipe === 'UMP') {
        csvContent = 'StateId,StateCode,StateName,UMP\n';
    } else {
        csvContent = 'RegencyId,RegencyCode,RegencyName,StateId,UMK\n';
    }

    const stateIdMap = {
        'ID 11': 5, 'ID 12': 6, 'ID 17': 7, 'ID 15': 8, 'ID 14': 9, 'ID 13': 10, 'ID 16': 11,
        'ID 18': 12, 'ID 19': 13, 'ID 21': 14, 'ID 36': 15, 'ID 32': 16, 'ID 31': 17, 'ID 33': 18,
        'ID 35': 19, 'ID 34': 20, 'ID 51': 21, 'ID 52': 22, 'ID 53': 23, 'ID 61': 24, 'ID 63': 25,
        'ID 62': 26, 'ID 64': 27, 'ID 75': 28, 'ID 73': 29, 'ID 74': 30, 'ID 72': 31, 'ID 71': 32,
        'ID 76': 33, 'ID 81': 34, 'ID 82': 35, 'ID 91': 36, 'ID 92': 37, 'ID 65': 45
    };

    // Jika ada data di tabel, masukkan data tersebut ke CSV
    if (umrAllData && umrAllData.length > 0) {
        let idCounter = 1;
        umrAllData.forEach(row => {
            if (tipe === 'UMP') {
                const stateId = stateIdMap[row.kode_daerah] || (row.provinsi || idCounter++);
                csvContent += `${stateId},${row.kode_daerah},${row.nama_daerah},${row.nominal || 0}\n`;
            } else {
                const regencyId = idCounter++;
                const prefix = row.kode_daerah.split('.')[0] || '';
                const stateId = stateIdMap[prefix] || (row.provinsi || 17);
                csvContent += `${regencyId},${row.kode_daerah},${row.nama_daerah},${stateId},${row.nominal || 0}\n`;
            }
        });
    } else {
        // Fallback ke data contoh jika tabel kosong
        if (tipe === 'UMP') {
            const defaultUmpData = [
                { code: 'ID 11', name: 'ACEH' },
                { code: 'ID 12', name: 'SUMATERA UTARA' },
                { code: 'ID 17', name: 'BENGKULU' },
                { code: 'ID 15', name: 'JAMBI' },
                { code: 'ID 14', name: 'RIAU' },
                { code: 'ID 13', name: 'SUMATERA BARAT' },
                { code: 'ID 16', name: 'SUMATERA SELATAN' },
                { code: 'ID 18', name: 'LAMPUNG' },
                { code: 'ID 19', name: 'KEP. BANGKA BELITUNG' },
                { code: 'ID 21', name: 'KEP. RIAU' },
                { code: 'ID 36', name: 'BANTEN' },
                { code: 'ID 32', name: 'JAWA BARAT' },
                { code: 'ID 31', name: 'DKI JAKARTA' },
                { code: 'ID 33', name: 'JAWA TENGAH' },
                { code: 'ID 35', name: 'JAWA TIMUR' },
                { code: 'ID 34', name: 'DI YOGYAKARTA' },
                { code: 'ID 51', name: 'BALI' },
                { code: 'ID 52', name: 'NUSA TENGGARA BARAT' },
                { code: 'ID 53', name: 'NUSA TENGGARA TIMUR' },
                { code: 'ID 61', name: 'KALIMANTAN BARAT' },
                { code: 'ID 63', name: 'KALIMANTAN SELATAN' },
                { code: 'ID 62', name: 'KALIMANTAN TENGAH' },
                { code: 'ID 64', name: 'KALIMANTAN TIMUR' },
                { code: 'ID 75', name: 'GORONTALO' },
                { code: 'ID 73', name: 'SULAWESI SELATAN' },
                { code: 'ID 74', name: 'SULAWESI TENGGARA' },
                { code: 'ID 72', name: 'SULAWESI TENGAH' },
                { code: 'ID 71', name: 'SULAWESI UTARA' },
                { code: 'ID 76', name: 'SULAWESI BARAT' },
                { code: 'ID 81', name: 'MALUKU' },
                { code: 'ID 82', name: 'MALUKU UTARA' },
                { code: 'ID 91', name: 'PAPUA' },
                { code: 'ID 92', name: 'PAPUA BARAT' },
                { code: 'ID 65', name: 'KALIMANTAN UTARA' }
            ];
            
            defaultUmpData.forEach(row => {
                const stateId = stateIdMap[row.code] || 17;
                csvContent += `${stateId},${row.code},${row.name},0\n`;
            });
        } else {
            const defaultUmkData = [
                { code: 'ID 11.01', name: 'KAB. ACEH BARAT' },
                { code: 'ID 11.02', name: 'KAB. ACEH BARAT DAYA' },
                { code: 'ID 11.03', name: 'KAB. ACEH BESAR' },
                { code: 'ID 31.71', name: 'KOTA JAKARTA PUSAT' },
                { code: 'ID 32.71', name: 'KOTA BOGOR' },
                { code: 'ID 32.73', name: 'KOTA BANDUNG' }
            ];
            
            let regId = 1;
            defaultUmkData.forEach(row => {
                const prefix = row.code.split('.')[0] || '';
                const stateId = stateIdMap[prefix] || 17;
                csvContent += `${regId++},${row.code},${row.name},${stateId},0\n`;
            });
        }
    }

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `template_${tipe.toLowerCase()}_${new Date().getFullYear()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    showToast(`${tipe} template downloaded successfully!`, 'success');
}

// Drag & Drop + File Input Handling
document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.getElementById('umrDropZone');
    const fileInput = document.getElementById('fileUmr');
    
    if (fileInput) {
        // Prevent click bubble to dropZone
        fileInput.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const fileNameEl = document.getElementById('umrFileName');
                if (fileNameEl) {
                    fileNameEl.innerText = `📌 ${file.name}`;
                    fileNameEl.style.display = 'block';
                }
            }
        });
    }

    if (dropZone) {
        // Trigger file dialog on click
        dropZone.addEventListener('click', () => {
            fileInput.click();
        });

        ['dragenter', 'dragover'].forEach(evt => {
            dropZone.addEventListener(evt, (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropZone.addEventListener(evt, (e) => { e.preventDefault(); dropZone.classList.remove('drag-over'); });
        });
        dropZone.addEventListener('drop', (e) => {
            const file = e.dataTransfer.files[0];
            if (file && file.name.endsWith('.csv')) {
                fileInput.files = e.dataTransfer.files;
                const fileNameEl = document.getElementById('umrFileName');
                if (fileNameEl) {
                    fileNameEl.innerText = `📌 ${file.name}`;
                    fileNameEl.style.display = 'block';
                }
            } else {
                showToast('Only CSV files are allowed!', 'error');
            }
        });
    }
});

// CSV Upload Handler
const formUploadUmr = document.getElementById('formUploadUmr');
if (formUploadUmr) {
    formUploadUmr.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const fileInput = document.getElementById('fileUmr');
        const file = fileInput.files[0];
        
        if (!file) {
            showToast('Please select a CSV file first!', 'error');
            return;
        }

        showToast('Reading and uploading data...', 'info');

        const reader = new FileReader();
        reader.onload = async (event) => {
            try {
                const csvText = event.target.result;
                const lines = csvText.split(/\r?\n/).filter(line => line.trim() !== '');
                
                // Skip header row
                const dataLines = lines.slice(1);
                
                if (dataLines.length === 0) {
                    showToast('CSV file is empty!', 'error');
                    return;
                }

                const tipe = document.getElementById('uploadUmrTipe').value;
                const tahun = document.getElementById('uploadUmrTahun').value;
                
                // Helper to split CSV line respecting double quotes
                const parseCsvLine = (text) => {
                    const cols = [];
                    let inQuote = false;
                    let cell = '';
                    for (let i = 0; i < text.length; i++) {
                        const char = text[i];
                        if (char === '"') {
                            inQuote = !inQuote;
                        } else if (char === ',' && !inQuote) {
                            cols.push(cell.trim().replace(/^"|"$/g, ''));
                            cell = '';
                        } else {
                            cell += char;
                        }
                    }
                    cols.push(cell.trim().replace(/^"|"$/g, ''));
                    return cols;
                };

                // Dynamically detect column indices based on header names for extreme robustness
                const headerLine = lines[0];
                const headers = parseCsvLine(headerLine);
                
                let codeIdx = -1;
                let nameIdx = -1;
                let nominalIdx = -1;
                let stateIdIdx = -1;

                headers.forEach((h, idx) => {
                    const cleanH = h.toLowerCase();
                    if (cleanH.includes('code') || cleanH.includes('kode')) {
                        codeIdx = idx;
                    } else if (cleanH.includes('name') || cleanH.includes('daerah') || cleanH.includes('kabupaten') || cleanH.includes('provinsi')) {
                        if (!cleanH.includes('kode')) {
                            nameIdx = idx;
                        }
                    } else if (cleanH.includes('amount') || cleanH.includes('nominal') || cleanH.includes('gaji') || cleanH === 'ump' || cleanH === 'umk') {
                        nominalIdx = idx;
                    } else if (cleanH === 'stateid' || cleanH === 'provinsi_id') {
                        stateIdIdx = idx;
                    }
                });

                // Fallbacks if not auto-detected by headers
                if (codeIdx === -1) {
                    if (tipe === 'UMP') {
                        codeIdx = headers.length >= 4 ? 1 : 0;
                        nameIdx = headers.length >= 4 ? 2 : 1;
                        nominalIdx = headers.length >= 4 ? 3 : 2;
                        stateIdIdx = headers.length >= 4 ? 0 : -1;
                    } else {
                        codeIdx = headers.length >= 5 ? 1 : 0;
                        nameIdx = headers.length >= 5 ? 2 : 1;
                        stateIdIdx = headers.length >= 5 ? 3 : -1;
                        nominalIdx = headers.length >= 5 ? 4 : 2;
                    }
                }
                
                const items = dataLines.map(line => {
                    // Handle CSV with commas inside quotes
                    const cols = parseCsvLine(line);
                    let rawNominal = cols[nominalIdx] || '0';
                    rawNominal = rawNominal.trim();
                    
                    let nominalVal = 0;
                    if (rawNominal.includes('.') && rawNominal.includes(',')) {
                        if (rawNominal.lastIndexOf('.') > rawNominal.lastIndexOf(',')) {
                            nominalVal = parseFloat(rawNominal.replace(/,/g, '')) || 0;
                        } else {
                            nominalVal = parseFloat(rawNominal.replace(/\./g, '').replace(/,/g, '.')) || 0;
                        }
                    } else if (rawNominal.includes(',')) {
                        const parts = rawNominal.split(',');
                        if (parts.length === 2 && parts[1].length === 2) {
                            nominalVal = parseFloat(rawNominal.replace(/,/g, '.')) || 0;
                        } else {
                            nominalVal = parseFloat(rawNominal.replace(/,/g, '')) || 0;
                        }
                    } else if (rawNominal.includes('.')) {
                        const parts = rawNominal.split('.');
                        if (parts.length > 2 || (parts.length === 2 && parts[1].length === 3)) {
                            nominalVal = parseFloat(rawNominal.replace(/\./g, '')) || 0;
                        } else {
                            nominalVal = parseFloat(rawNominal) || 0;
                        }
                    } else {
                        nominalVal = parseFloat(rawNominal) || 0;
                    }

                    const provinceVal = stateIdIdx !== -1 ? (cols[stateIdIdx] || '') : '';
                    
                    return {
                        tipe: tipe,
                        kode_daerah: cols[codeIdx] || '',
                        nama_daerah: cols[nameIdx] || '',
                        provinsi: provinceVal,
                        nominal: nominalVal,
                        tahun: parseInt(tahun)
                    };
                }).filter(item => item.kode_daerah && item.nama_daerah);

                const res = await fetch(`${API_URL}/minimum-wages`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items: items })
                });

                if (res.ok) {
                    tutupModalUploadUmr();
                    umrCurrentPage = 1;
                    renderUmrTable();
                    showToast(`${items.length} ${tipe} data uploaded successfully!`, 'success');
                } else {
                    showToast('Failed to upload data!', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Error processing CSV file!', 'error');
            }
        };
        reader.readAsText(file);
    });
}

// Manual UMR Form Handler
const formManualUmr = document.getElementById('formManualUmr');
if (formManualUmr) {
    formManualUmr.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = {
            items: [{
                tipe: document.getElementById('manualUmrTipe').value,
                kode_daerah: document.getElementById('manualUmrKode').value,
                nama_daerah: document.getElementById('manualUmrNama').value,
                nominal: parseFloat(document.getElementById('manualUmrNominal').value) || 0,
                tahun: parseInt(document.getElementById('manualUmrTahun').value)
            }]
        };

        try {
            const res = await fetch(`${API_URL}/minimum-wages`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (res.ok) {
                tutupModalManualUmr();
                renderUmrTable();
                showToast('Data saved successfully!', 'success');
            } else {
                showToast('Failed to save data!', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('System error occurred!', 'error');
        }
    });
}

// ===== SIMULASI GAJI MODULE =====

async function loadSimulasiRegions() {
    const type = document.getElementById('simulasiType').value;
    if (type === 'NOMINAL') {
        const savedNominal = localStorage.getItem('simulasi_nominal');
        const select = document.getElementById('simulasiRegion');
        if (savedNominal) {
            const formatted = new Intl.NumberFormat('id-ID').format(savedNominal);
            select.innerHTML = `<option value="NOMINAL">Agreed Nominal (Rp ${formatted})</option>`;
            simulasiAllData = [{id: 'NOMINAL', nama_daerah: 'Agreed Nominal', nominal: savedNominal}];
        } else {
            select.innerHTML = `<option value="">No nominal saved yet</option>`;
            simulasiAllData = [];
        }
        return;
    }
    try {
        const res = await fetch(`${API_URL}/minimum-wages?tipe=${type}`);
        let dbData = await res.json();
        
        // Data Default 38 Provinsi (Update 2026 Projection)
        const defaultUmp = [
            {id: 'ID 11', nama_daerah: 'ACEH', nominal: 3000000},
            {id: 'ID 12', nama_daerah: 'SUMATERA UTARA', nominal: 4000000},
            {id: 'ID 17', nama_daerah: 'BENGKULU', nominal: 2000000},
            {id: 'ID 15', nama_daerah: 'JAMBI', nominal: 2000000},
            {id: 'ID 14', nama_daerah: 'RIAU', nominal: 2000000},
            {id: 'ID 13', nama_daerah: 'SUMATERA BARAT', nominal: 2000000},
            {id: 'ID 16', nama_daerah: 'SUMATERA SELATAN', nominal: 2000000},
            {id: 'ID 18', nama_daerah: 'LAMPUNG', nominal: 2000000},
            {id: 'ID 19', nama_daerah: 'KEP. BANGKA BELITUNG', nominal: 2000000},
            {id: 'ID 21', nama_daerah: 'KEP. RIAU', nominal: 2000000},
            {id: 'ID 36', nama_daerah: 'BANTEN', nominal: 2000000},
            {id: 'ID 32', nama_daerah: 'JAWA BARAT', nominal: 2000000},
            {id: 'ID 31', nama_daerah: 'DKI JAKARTA', nominal: 2000000},
            {id: 'ID 33', nama_daerah: 'JAWA TENGAH', nominal: 2000000},
            {id: 'ID 35', nama_daerah: 'JAWA TIMUR', nominal: 2000000},
            {id: 'ID 34', nama_daerah: 'DI YOGYAKARTA', nominal: 2000000},
            {id: 'ID 51', nama_daerah: 'BALI', nominal: 2000000},
            {id: 'ID 52', nama_daerah: 'NUSA TENGGARA BARAT', nominal: 2000000},
            {id: 'ID 53', nama_daerah: 'NUSA TENGGARA TIMUR', nominal: 2000000},
            {id: 'ID 61', nama_daerah: 'KALIMANTAN BARAT', nominal: 2000000},
            {id: 'ID 63', nama_daerah: 'KALIMANTAN SELATAN', nominal: 2000000},
            {id: 'ID 62', nama_daerah: 'KALIMANTAN TENGAH', nominal: 2000000},
            {id: 'ID 64', nama_daerah: 'KALIMANTAN TIMUR', nominal: 2000000},
            {id: 'ID 75', nama_daerah: 'GORONTALO', nominal: 2000000},
            {id: 'ID 73', nama_daerah: 'SULAWESI SELATAN', nominal: 2000000},
            {id: 'ID 74', nama_daerah: 'SULAWESI TENGGARA', nominal: 2000000},
            {id: 'ID 72', nama_daerah: 'SULAWESI TENGAH', nominal: 2000000},
            {id: 'ID 71', nama_daerah: 'SULAWESI UTARA', nominal: 2000000},
            {id: 'ID 76', nama_daerah: 'SULAWESI BARAT', nominal: 2000000},
            {id: 'ID 81', nama_daerah: 'MALUKU', nominal: 2000000},
            {id: 'ID 82', nama_daerah: 'MALUKU UTARA', nominal: 2000000},
            {id: 'ID 91', nama_daerah: 'PAPUA', nominal: 1000000},
            {id: 'ID 92', nama_daerah: 'PAPUA BARAT', nominal: 2000000},
            {id: 'ID 65', nama_daerah: 'KALIMANTAN UTARA', nominal: 2000000}
        ];

        // Data Default Kota Besar (UMK 2024 - Full Indonesia Eksak)
        const defaultUmk = [
            {id: '1300000', nama_daerah: 'Jakarta', nominal: 7000000},
            {id: 'ID 11.16', nama_daerah: 'KAB. ACEH TAMIANG', nominal: 1000000},
            {id: 'ID 11.04', nama_daerah: 'KAB. ACEH TENGAH', nominal: 1000000},
            {id: 'ID 11.03', nama_daerah: 'KAB. ACEH TIMUR', nominal: 1000000},
            {id: 'ID 12.09', nama_daerah: 'KAB. ASAHAN', nominal: 1000000},
            {id: 'ID 51.03', nama_daerah: 'KAB. BADUNG', nominal: 5000000},
            {id: 'ID 32.04', nama_daerah: 'KAB. BANDUNG', nominal: 1000000},
            {id: 'ID 32.17', nama_daerah: 'KAB. BANDUNG BARAT', nominal: 1000000},
            {id: 'ID 72.01', nama_daerah: 'KAB. BANGGAI', nominal: 1000000},
            {id: 'ID 35.26', nama_daerah: 'KAB. BANGKALAN', nominal: 1000000},
            {id: 'ID 33.04', nama_daerah: 'KAB. BANJARNEGARA', nominal: 1000000},
            {id: 'ID 73.03', nama_daerah: 'KAB. BANTAENG', nominal: 1000000},
            {id: 'ID 34.02', nama_daerah: 'KAB. BANTUL', nominal: 1000000},
            {id: 'ID 16.07', nama_daerah: 'KAB. BANYUASIN', nominal: 1000000},
            {id: 'ID 33.02', nama_daerah: 'KAB. BANYUMAS', nominal: 1000000},
            {id: 'ID 35.10', nama_daerah: 'KAB. BANYUWANGI', nominal: 1000000},
            {id: 'ID 73.11', nama_daerah: 'KAB. BARRU', nominal: 1000000},
            {id: 'ID 33.25', nama_daerah: 'KAB. BATANG', nominal: 1000000},
            {id: 'ID 12.19', nama_daerah: 'KAB. BATU BARA', nominal: 1000000},
            {id: 'ID 32.16', nama_daerah: 'KAB. BEKASI', nominal: 1000000},
            {id: 'ID 19.02', nama_daerah: 'KAB. BELITUNG', nominal: 1000000},
            {id: 'ID 14.03', nama_daerah: 'KAB. BENGKALIS', nominal: 1000000},
            {id: 'ID 17.01', nama_daerah: 'KAB. BENGKULU SELATAN', nominal: 1000000},
            {id: 'ID 64.03', nama_daerah: 'KAB. BERAU', nominal: 1000000},
            {id: 'ID 11.11', nama_daerah: 'KAB. BIREUEN', nominal: 1000000},
            {id: 'ID 33.16', nama_daerah: 'KAB. BLORA', nominal: 1000000},
            {id: 'ID 32.01', nama_daerah: 'KAB. BOGOR', nominal: 1000000},
            {id: 'ID 35.22', nama_daerah: 'KAB. BOJONEGORO', nominal: 1000000},
            {id: 'ID 35.11', nama_daerah: 'KAB. BONDOWOSO', nominal: 1000000},
            {id: 'ID 73.08', nama_daerah: 'KAB. BONE', nominal: 1000000},
            {id: 'ID 33.09', nama_daerah: 'KAB. BOYOLALI', nominal: 1000000},
            {id: 'ID 33.29', nama_daerah: 'KAB. BREBES', nominal: 1000000},
            {id: 'ID 51.08', nama_daerah: 'KAB. BULELENG', nominal: 1000000},
            {id: 'ID 73.02', nama_daerah: 'KAB. BULUKUMBA', nominal: 1000000},
            {id: 'ID 65.01', nama_daerah: 'KAB. BULUNGAN', nominal: 1000000},
            {id: 'ID 15.08', nama_daerah: 'KAB. BUNGO', nominal: 1000000},
            {id: 'ID 32.07', nama_daerah: 'KAB. CIAMIS', nominal: 1000000},
            {id: 'ID 32.03', nama_daerah: 'KAB. CIANJUR', nominal: 1000000},
            {id: 'ID 33.01', nama_daerah: 'KAB. CILACAP', nominal: 1000000},
            {id: 'ID 32.09', nama_daerah: 'KAB. CIREBON', nominal: 1000000},
            {id: 'ID 12.07', nama_daerah: 'KAB. DELI SERDANG', nominal: 1000000},
            {id: 'ID 33.21', nama_daerah: 'KAB. DEMAK', nominal: 1000000},
            {id: 'ID 13.10', nama_daerah: 'KAB. DHARMASRAYA', nominal: 1000000},
            {id: 'ID 53.08', nama_daerah: 'KAB. ENDE', nominal: 1000000},
            {id: 'ID 73.16', nama_daerah: 'KAB. ENREKANG', nominal: 1000000},
            {id: 'ID 32.05', nama_daerah: 'KAB. GARUT', nominal: 1000000},
            {id: 'ID 51.04', nama_daerah: 'KAB. GIANYAR', nominal: 1000000},
            {id: 'ID 75.01', nama_daerah: 'KAB. GORONTALO', nominal: 1000000},
            {id: 'ID 73.06', nama_daerah: 'KAB. GOWA', nominal: 1000000},
            {id: 'ID 35.25', nama_daerah: 'KAB. GRESIK', nominal: 1000000},
            {id: 'ID 33.15', nama_daerah: 'KAB. GROBOGAN', nominal: 1000000},
            {id: 'ID 63.07', nama_daerah: 'KAB. HULU SUNGAI TENGAH', nominal: 1000000},
            {id: 'ID 63.08', nama_daerah: 'KAB. HULU SUNGAI UTARA', nominal: 1000000},
            {id: 'ID 14.04', nama_daerah: 'KAB. INDRAGIRI HILIR', nominal: 1000000},
            {id: 'ID 14.02', nama_daerah: 'KAB. INDRAGIRI HULU', nominal: 1000000},
            {id: 'ID 32.12', nama_daerah: 'KAB. INDRAMAYU', nominal: 1000000},
            {id: 'ID 91.03', nama_daerah: 'KAB. JAYAPURA', nominal: 1000000},
            {id: 'ID 35.09', nama_daerah: 'KAB. JEMBER', nominal: 1000000},
            {id: 'ID 51.01', nama_daerah: 'KAB. JEMBRANA', nominal: 1000000},
            {id: 'ID 73.04', nama_daerah: 'KAB. JENEPONTO', nominal: 1000000},
            {id: 'ID 33.20', nama_daerah: 'KAB. JEPARA', nominal: 1000000},
            {id: 'ID 35.17', nama_daerah: 'KAB. JOMBANG', nominal: 1000000},
            {id: 'ID 14.01', nama_daerah: 'KAB. KAMPAR', nominal: 1000000},
            {id: 'ID 62.03', nama_daerah: 'KAB. KAPUAS', nominal: 1000000},
            {id: 'ID 61.06', nama_daerah: 'KAB. KAPUAS HULU', nominal: 1000000},
            {id: 'ID 33.13', nama_daerah: 'KAB. KARANGANYAR', nominal: 1000000},
            {id: 'ID 32.15', nama_daerah: 'KAB. KARAWANG', nominal: 1000000},
            {id: 'ID 12.06', nama_daerah: 'KAB. KARO', nominal: 1000000},
            {id: 'ID 33.05', nama_daerah: 'KAB. KEBUMEN', nominal: 1000000},
            {id: 'ID 33.24', nama_daerah: 'KAB. KENDAL', nominal: 1000000},
            {id: 'ID 17.08', nama_daerah: 'KAB. KEPAHIANG', nominal: 1000000},
            {id: 'ID 61.04', nama_daerah: 'KAB. KETAPANG', nominal: 1000000},
            {id: 'ID 33.10', nama_daerah: 'KAB. KLATEN', nominal: 1000000},
            {id: 'ID 74.01', nama_daerah: 'KAB. KOLAKA', nominal: 1000000},
            {id: 'ID 63.02', nama_daerah: 'KAB. KOTABARU', nominal: 1000000},
            {id: 'ID 62.01', nama_daerah: 'KAB. KOTAWARINGIN BARAT', nominal: 1000000},
            {id: 'ID 62.02', nama_daerah: 'KAB. KOTAWARINGIN TIMUR', nominal: 1000000},
            {id: 'ID 61.12', nama_daerah: 'KAB. KUBU RAYA', nominal: 1000000},
            {id: 'ID 33.19', nama_daerah: 'KAB. KUDUS', nominal: 1000000},
            {id: 'ID 34.01', nama_daerah: 'KAB. KULON PROGO', nominal: 1000000},
            {id: 'ID 32.08', nama_daerah: 'KAB. KUNINGAN', nominal: 1000000},
            {id: 'ID 64.07', nama_daerah: 'KAB. KUTAI BARAT', nominal: 1000000},
            {id: 'ID 64.02', nama_daerah: 'KAB. KUTAI KARTANEGARA', nominal: 1000000},
            {id: 'ID 64.08', nama_daerah: 'KAB. KUTAI TIMUR', nominal: 1000000},
            {id: 'ID 12.10', nama_daerah: 'KAB. LABUHANBATU', nominal: 1000000},
            {id: 'ID 12.23', nama_daerah: 'KAB. LABUHANBATU UTARA', nominal: 1000000},
            {id: 'ID 16.04', nama_daerah: 'KAB. LAHAT', nominal: 1000000},
            {id: 'ID 35.24', nama_daerah: 'KAB. LAMONGAN', nominal: 1000000},
            {id: 'ID 18.04', nama_daerah: 'KAB. LAMPUNG BARAT', nominal: 1000000},
            {id: 'ID 18.01', nama_daerah: 'KAB. LAMPUNG SELATAN', nominal: 1000000},
            {id: 'ID 18.02', nama_daerah: 'KAB. LAMPUNG TENGAH', nominal: 1000000},
            {id: 'ID 18.07', nama_daerah: 'KAB. LAMPUNG TIMUR', nominal: 1000000},
            {id: 'ID 18.03', nama_daerah: 'KAB. LAMPUNG UTARA', nominal: 1000000},
            {id: 'ID 61.08', nama_daerah: 'KAB. LANDAK', nominal: 1000000},
            {id: 'ID 12.05', nama_daerah: 'KAB. LANGKAT', nominal: 1000000},
            {id: 'ID 36.02', nama_daerah: 'KAB. LEBAK', nominal: 1000000},
            {id: 'ID 52.01', nama_daerah: 'KAB. LOMBOK BARAT', nominal: 1000000},
            {id: 'ID 52.02', nama_daerah: 'KAB. LOMBOK TENGAH', nominal: 1000000},
            {id: 'ID 52.03', nama_daerah: 'KAB. LOMBOK TIMUR', nominal: 1000000},
            {id: 'ID 35.08', nama_daerah: 'KAB. LUMAJANG', nominal: 1000000},
            {id: 'ID 73.17', nama_daerah: 'KAB. LUWU', nominal: 1000000},
            {id: 'ID 73.24', nama_daerah: 'KAB. LUWU TIMUR', nominal: 1000000},
            {id: 'ID 73.22', nama_daerah: 'KAB. LUWU UTARA', nominal: 1000000},
            {id: 'ID 35.19', nama_daerah: 'KAB. MADIUN', nominal: 1000000},
            {id: 'ID 33.08', nama_daerah: 'KAB. MAGELANG', nominal: 1000000},
            {id: 'ID 35.20', nama_daerah: 'KAB. MAGETAN', nominal: 1000000},
            {id: 'ID 32.10', nama_daerah: 'KAB. MAJALENGKA', nominal: 1000000},
            {id: 'ID 76.05', nama_daerah: 'KAB. MAJENE', nominal: 1000000},
            {id: 'ID 35.07', nama_daerah: 'KAB. MALANG', nominal: 1000000},
            {id: 'ID 76.02', nama_daerah: 'KAB. MAMUJU', nominal: 1000000},
            {id: 'ID 12.13', nama_daerah: 'KAB. MANDAILING NATAL', nominal: 1000000},
            {id: 'ID 53.15', nama_daerah: 'KAB. MANGGARAI BARAT', nominal: 1000000},
            {id: 'ID 92.02', nama_daerah: 'KAB. MANOKWARI', nominal: 1000000},
            {id: 'ID 73.09', nama_daerah: 'KAB. MAROS', nominal: 1000000},
            {id: 'ID 61.10', nama_daerah: 'KAB. MELAWI', nominal: 1000000},
            {id: 'ID 15.02', nama_daerah: 'KAB. MERANGIN', nominal: 1000000},
            {id: 'ID 91.01', nama_daerah: 'KAB. MERAUKE', nominal: 1000000},
            {id: 'ID 91.09', nama_daerah: 'KAB. MIMIKA', nominal: 1000000},
            {id: 'ID 71.05', nama_daerah: 'KAB. MINAHASA SELATAN', nominal: 1000000},
            {id: 'ID 71.06', nama_daerah: 'KAB. MINAHASA UTARA', nominal: 1000000},
            {id: 'ID 35.16', nama_daerah: 'KAB. MOJOKERTO', nominal: 1000000},
            {id: 'ID 72.06', nama_daerah: 'KAB. MOROWALI', nominal: 1000000},
            {id: 'ID 16.03', nama_daerah: 'KAB. MUARA ENIM', nominal: 1000000},
            {id: 'ID 16.06', nama_daerah: 'KAB. MUSI BANYUASIN', nominal: 1000000},
            {id: 'ID 91.04', nama_daerah: 'KAB. NABIRE', nominal: 1000000},
            {id: 'ID 35.18', nama_daerah: 'KAB. NGANJUK', nominal: 1000000},
            {id: 'ID 35.21', nama_daerah: 'KAB. NGAWI', nominal: 1000000},
            {id: 'ID 16.10', nama_daerah: 'KAB. OGAN ILIR', nominal: 1000000},
            {id: 'ID 16.02', nama_daerah: 'KAB. OGAN KOMERING ILIR', nominal: 1000000},
            {id: 'ID 16.01', nama_daerah: 'KAB. OGAN KOMERING ULU', nominal: 1000000},
            {id: 'ID 16.09', nama_daerah: 'KAB. OGAN KOMERING ULU SELATAN', nominal: 1000000},
            {id: 'ID 16.08', nama_daerah: 'KAB. OGAN KOMERING ULU TIMUR', nominal: 1000000},
            {id: 'ID 35.01', nama_daerah: 'KAB. PACITAN', nominal: 1000000},
            {id: 'ID 35.28', nama_daerah: 'KAB. PAMEKASAN', nominal: 1000000},
            {id: 'ID 36.01', nama_daerah: 'KAB. PANDEGLANG', nominal: 1000000},
            {id: 'ID 32.18', nama_daerah: 'KAB. PANGANDARAN', nominal: 1000000},
            {id: 'ID 73.10', nama_daerah: 'KAB. PANGKAJENE KEPULAUAN', nominal: 1000000},
            {id: 'ID 13.12', nama_daerah: 'KAB. PASAMAN BARAT', nominal: 1000000},
            {id: 'ID 35.14', nama_daerah: 'KAB. PASURUAN', nominal: 1000000},
            {id: 'ID 33.18', nama_daerah: 'KAB. PATI', nominal: 1000000},
            {id: 'ID 33.26', nama_daerah: 'KAB. PEKALONGAN', nominal: 1000000},
            {id: 'ID 14.05', nama_daerah: 'KAB. PELALAWAN', nominal: 1000000},
            {id: 'ID 33.27', nama_daerah: 'KAB. PEMALANG', nominal: 1000000},
            {id: 'ID 64.09', nama_daerah: 'KAB. PENAJAM PASER UTARA', nominal: 1000000},
            {id: 'ID 18.09', nama_daerah: 'KAB. PESAWARAN', nominal: 1000000},
            {id: 'ID 13.01', nama_daerah: 'KAB. PESISIR SELATAN', nominal: 1000000},
            {id: 'ID 11.07', nama_daerah: 'KAB. PIDIE', nominal: 1000000},
            {id: 'ID 73.15', nama_daerah: 'KAB. PINRANG', nominal: 1000000},
            {id: 'ID 76.04', nama_daerah: 'KAB. POLEWALI MANDAR', nominal: 1000000},
            {id: 'ID 35.02', nama_daerah: 'KAB. PONOROGO', nominal: 1000000},
            {id: 'ID 18.10', nama_daerah: 'KAB. PRINGSEWU', nominal: 1000000},
            {id: 'ID 35.13', nama_daerah: 'KAB. PROBOLINGGO', nominal: 1000000},
            {id: 'ID 33.03', nama_daerah: 'KAB. PURBALINGGA', nominal: 1000000},
            {id: 'ID 32.14', nama_daerah: 'KAB. PURWAKARTA', nominal: 1000000},
            {id: 'ID 33.06', nama_daerah: 'KAB. PURWOREJO', nominal: 1000000},
            {id: 'ID 17.02', nama_daerah: 'KAB. REJANG LEBONG', nominal: 1000000},
            {id: 'ID 33.17', nama_daerah: 'KAB. REMBANG', nominal: 1000000},
            {id: 'ID 14.07', nama_daerah: 'KAB. ROKAN HILIR', nominal: 1000000},
            {id: 'ID 14.06', nama_daerah: 'KAB. ROKAN HULU', nominal: 1000000},
            {id: 'ID 61.03', nama_daerah: 'KAB. SANGGAU', nominal: 1000000},
            {id: 'ID 15.03', nama_daerah: 'KAB. SAROLANGUN', nominal: 1000000},
            {id: 'ID 33.22', nama_daerah: 'KAB. SEMARANG', nominal: 1000000},
            {id: 'ID 36.04', nama_daerah: 'KAB. SERANG', nominal: 1000000},
            {id: 'ID 12.18', nama_daerah: 'KAB. SERDANG BEDAGAI', nominal: 1000000},
            {id: 'ID 14.08', nama_daerah: 'KAB. SIAK', nominal: 1000000},
            {id: 'ID 73.14', nama_daerah: 'KAB. SIDENRENG RAPPANG', nominal: 1000000},
            {id: 'ID 35.15', nama_daerah: 'KAB. SIDOARJO', nominal: 1000000},
            {id: 'ID 72.10', nama_daerah: 'KAB. SIGI', nominal: 1000000},
            {id: 'ID 73.07', nama_daerah: 'KAB. SINJAI', nominal: 1000000},
            {id: 'ID 61.05', nama_daerah: 'KAB. SINTANG', nominal: 1000000},
            {id: 'ID 35.12', nama_daerah: 'KAB. SITUBONDO', nominal: 1000000},
            {id: 'ID 34.04', nama_daerah: 'KAB. SLEMAN', nominal: 1000000},
            {id: 'ID 73.12', nama_daerah: 'KAB. SOPPENG', nominal: 1000000},
            {id: 'ID 92.01', nama_daerah: 'KAB. SORONG', nominal: 1000000},
            {id: 'ID 33.14', nama_daerah: 'KAB. SRAGEN', nominal: 1000000},
            {id: 'ID 32.13', nama_daerah: 'KAB. SUBANG', nominal: 1000000},
            {id: 'ID 32.02', nama_daerah: 'KAB. SUKABUMI', nominal: 1000000},
            {id: 'ID 33.11', nama_daerah: 'KAB. SUKOHARJO', nominal: 1000000},
            {id: 'ID 52.04', nama_daerah: 'KAB. SUMBAWA', nominal: 1000000},
            {id: 'ID 32.11', nama_daerah: 'KAB. SUMEDANG', nominal: 1000000},
            {id: 'ID 35.29', nama_daerah: 'KAB. SUMENEP', nominal: 1000000},
            {id: 'ID 63.09', nama_daerah: 'KAB. TABALONG', nominal: 1000000},
            {id: 'ID 51.02', nama_daerah: 'KAB. TABANAN', nominal: 1000000},
            {id: 'ID 73.05', nama_daerah: 'KAB. TAKALAR', nominal: 1000000},
            {id: 'ID 73.18', nama_daerah: 'KAB. TANA TORAJA', nominal: 1000000},
            {id: 'ID 63.10', nama_daerah: 'KAB. TANAH BUMBU', nominal: 1000000},
            {id: 'ID 63.01', nama_daerah: 'KAB. TANAH LAUT', nominal: 1000000},
            {id: 'ID 36.03', nama_daerah: 'KAB. TANGERANG', nominal: 1000000},
            {id: 'ID 18.06', nama_daerah: 'KAB. TANGGAMUS', nominal: 1000000},
            {id: 'ID 12.02', nama_daerah: 'KAB. TAPANULI UTARA', nominal: 1000000},
            {id: 'ID 63.05', nama_daerah: 'KAB. TAPIN', nominal: 1000000},
            {id: 'ID 32.06', nama_daerah: 'KAB. TASIKMALAYA', nominal: 1000000},
            {id: 'ID 15.09', nama_daerah: 'KAB. TEBO', nominal: 1000000},
            {id: 'ID 33.28', nama_daerah: 'KAB. TEGAL', nominal: 1000000},
            {id: 'ID 33.23', nama_daerah: 'KAB. TEMANGGUNG', nominal: 1000000},
            {id: 'ID 12.12', nama_daerah: 'KAB. TOBA SAMOSIR', nominal: 1000000},
            {id: 'ID 73.26', nama_daerah: 'KAB. TORAJA UTARA', nominal: 1000000},
            {id: 'ID 35.23', nama_daerah: 'KAB. TUBAN', nominal: 1000000},
            {id: 'ID 18.05', nama_daerah: 'KAB. TULANG BAWANG', nominal: 1000000},
            {id: 'ID 35.04', nama_daerah: 'KAB. TULUNGAGUNG', nominal: 1000000},
            {id: 'ID 73.13', nama_daerah: 'KAB. WAJO', nominal: 1000000},
            {id: 'ID 33.12', nama_daerah: 'KAB. WONOGIRI', nominal: 1000000},
            {id: 'ID 33.07', nama_daerah: 'KAB. WONOSOBO', nominal: 1000000},
            {id: 'ID 31.73', nama_daerah: 'KOTA ADM. JAKARTA BARAT', nominal: 1000000},
            {id: 'ID 31.71', nama_daerah: 'KOTA ADM. JAKARTA PUSAT', nominal: 1000000},
            {id: 'ID 31.74', nama_daerah: 'KOTA ADM. JAKARTA SELATAN', nominal: 1000000},
            {id: 'ID 31.75', nama_daerah: 'KOTA ADM. JAKARTA TIMUR', nominal: 1000000},
            {id: 'ID 31.72', nama_daerah: 'KOTA ADM. JAKARTA UTARA', nominal: 1000000},
            {id: 'ID 81.71', nama_daerah: 'KOTA AMBON', nominal: 1000000},
            {id: 'ID 64.71', nama_daerah: 'KOTA BALIKPAPAN', nominal: 1000000},
            {id: 'ID 11.71', nama_daerah: 'KOTA BANDA ACEH', nominal: 1000000},
            {id: 'ID 18.71', nama_daerah: 'KOTA BANDAR LAMPUNG', nominal: 1000000},
            {id: 'ID 32.73', nama_daerah: 'KOTA BANDUNG', nominal: 1000000},
            {id: 'ID 32.79', nama_daerah: 'KOTA BANJAR', nominal: 1000000},
            {id: 'ID 63.72', nama_daerah: 'KOTA BANJARBARU', nominal: 1000000},
            {id: 'ID 63.71', nama_daerah: 'KOTA BANJARMASIN', nominal: 1000000},
            {id: 'ID 21.71', nama_daerah: 'KOTA BATAM', nominal: 1000000},
            {id: 'ID 35.79', nama_daerah: 'KOTA BATU', nominal: 1000000},
            {id: 'ID 74.72', nama_daerah: 'KOTA BAU BAU', nominal: 1000000},
            {id: 'ID 32.75', nama_daerah: 'KOTA BEKASI', nominal: 1000000},
            {id: 'ID 17.71', nama_daerah: 'KOTA BENGKULU', nominal: 1000000},
            {id: 'ID 12.75', nama_daerah: 'KOTA BINJAI', nominal: 1000000},
            {id: 'ID 71.72', nama_daerah: 'KOTA BITUNG', nominal: 1000000},
            {id: 'ID 35.72', nama_daerah: 'KOTA BLITAR', nominal: 1000000},
            {id: 'ID 32.71', nama_daerah: 'KOTA BOGOR', nominal: 1000000},
            {id: 'ID 64.74', nama_daerah: 'KOTA BONTANG', nominal: 1000000},
            {id: 'ID 13.75', nama_daerah: 'KOTA BUKITTINGGI', nominal: 1000000},
            {id: 'ID 36.72', nama_daerah: 'KOTA CILEGON', nominal: 1000000},
            {id: 'ID 32.77', nama_daerah: 'KOTA CIMAHI', nominal: 1000000},
            {id: 'ID 32.74', nama_daerah: 'KOTA CIREBON', nominal: 1000000},
            {id: 'ID 51.71', nama_daerah: 'KOTA DENPASAR', nominal: 1000000},
            {id: 'ID 32.76', nama_daerah: 'KOTA DEPOK', nominal: 1000000},
            {id: 'ID 14.72', nama_daerah: 'KOTA DUMAI', nominal: 1000000},
            {id: 'ID 75.71', nama_daerah: 'KOTA GORONTALO', nominal: 1000000},
            {id: 'ID 15.71', nama_daerah: 'KOTA JAMBI', nominal: 1000000},
            {id: 'ID 91.71', nama_daerah: 'KOTA JAYAPURA', nominal: 1000000},
            {id: 'ID 35.71', nama_daerah: 'KOTA KEDIRI', nominal: 1000000},
            {id: 'ID 74.71', nama_daerah: 'KOTA KENDARI', nominal: 1000000},
            {id: 'ID 71.74', nama_daerah: 'KOTA KOTAMOBAGU', nominal: 1000000},
            {id: 'ID 53.71', nama_daerah: 'KOTA KUPANG', nominal: 1000000},
            {id: 'ID 11.74', nama_daerah: 'KOTA LANGSA', nominal: 1000000},
            {id: 'ID 11.73', nama_daerah: 'KOTA LHOKSEUMAWE', nominal: 1000000},
            {id: 'ID 16.73', nama_daerah: 'KOTA LUBUK LINGGAU', nominal: 1000000},
            {id: 'ID 35.77', nama_daerah: 'KOTA MADIUN', nominal: 1000000},
            {id: 'ID 33.71', nama_daerah: 'KOTA MAGELANG', nominal: 1000000},
            {id: 'ID 73.71', nama_daerah: 'KOTA MAKASSAR', nominal: 1000000},
            {id: 'ID 35.73', nama_daerah: 'KOTA MALANG', nominal: 1000000},
            {id: 'ID 71.71', nama_daerah: 'KOTA MANADO', nominal: 1000000},
            {id: 'ID 52.71', nama_daerah: 'KOTA MATARAM', nominal: 1000000},
            {id: 'ID 12.71', nama_daerah: 'KOTA MEDAN', nominal: 1000000},
            {id: 'ID 18.72', nama_daerah: 'KOTA METRO', nominal: 1000000},
            {id: 'ID 35.76', nama_daerah: 'KOTA MOJOKERTO', nominal: 1000000},
            {id: 'ID 13.71', nama_daerah: 'KOTA PADANG', nominal: 1000000},
            {id: 'ID 13.74', nama_daerah: 'KOTA PADANG PANJANG', nominal: 1000000},
            {id: 'ID 12.77', nama_daerah: 'KOTA PADANG SIDEMPUAN', nominal: 1000000},
            {id: 'ID 16.72', nama_daerah: 'KOTA PAGAR ALAM', nominal: 1000000},
            {id: 'ID 62.71', nama_daerah: 'KOTA PALANGKARAYA', nominal: 1000000},
            {id: 'ID 16.71', nama_daerah: 'KOTA PALEMBANG', nominal: 1000000},
            {id: 'ID 73.73', nama_daerah: 'KOTA PALOPO', nominal: 1000000},
            {id: 'ID 72.71', nama_daerah: 'KOTA PALU', nominal: 1000000},
            {id: 'ID 19.71', nama_daerah: 'KOTA PANGKAL PINANG', nominal: 1000000},
            {id: 'ID 73.72', nama_daerah: 'KOTA PARE PARE', nominal: 1000000},
            {id: 'ID 13.77', nama_daerah: 'KOTA PARIAMAN', nominal: 1000000},
            {id: 'ID 35.75', nama_daerah: 'KOTA PASURUAN', nominal: 1000000},
            {id: 'ID 13.76', nama_daerah: 'KOTA PAYAKUMBUH', nominal: 1000000},
            {id: 'ID 33.75', nama_daerah: 'KOTA PEKALONGAN', nominal: 1000000},
            {id: 'ID 14.71', nama_daerah: 'KOTA PEKANBARU', nominal: 1000000},
            {id: 'ID 12.72', nama_daerah: 'KOTA PEMATANGSIANTAR', nominal: 1000000},
            {id: 'ID 61.71', nama_daerah: 'KOTA PONTIANAK', nominal: 1000000},
            {id: 'ID 16.74', nama_daerah: 'KOTA PRABUMULIH', nominal: 1000000},
            {id: 'ID 35.74', nama_daerah: 'KOTA PROBOLINGGO', nominal: 1000000},
            {id: 'ID 33.73', nama_daerah: 'KOTA SALATIGA', nominal: 1000000},
            {id: 'ID 64.72', nama_daerah: 'KOTA SAMARINDA', nominal: 1000000},
            {id: 'ID 33.74', nama_daerah: 'KOTA SEMARANG', nominal: 1000000},
            {id: 'ID 36.73', nama_daerah: 'KOTA SERANG', nominal: 1000000},
            {id: 'ID 12.73', nama_daerah: 'KOTA SIBOLGA', nominal: 1000000},
            {id: 'ID 61.72', nama_daerah: 'KOTA SINGKAWANG', nominal: 1000000},
            {id: 'ID 13.72', nama_daerah: 'KOTA SOLOK', nominal: 1000000},
            {id: 'ID 92.71', nama_daerah: 'KOTA SORONG', nominal: 1000000},
            {id: 'ID 32.72', nama_daerah: 'KOTA SUKABUMI', nominal: 1000000},
            {id: 'ID 15.72', nama_daerah: 'KOTA SUNGAI PENUH', nominal: 1000000},
            {id: 'ID 35.78', nama_daerah: 'KOTA SURABAYA', nominal: 1000000},
            {id: 'ID 33.72', nama_daerah: 'KOTA SURAKARTA', nominal: 1000000},
            {id: 'ID 36.71', nama_daerah: 'KOTA TANGERANG', nominal: 1000000},
            {id: 'ID 36.74', nama_daerah: 'KOTA TANGERANG SELATAN', nominal: 1000000},
            {id: 'ID 12.74', nama_daerah: 'KOTA TANJUNG BALAI', nominal: 1000000},
            {id: 'ID 21.72', nama_daerah: 'KOTA TANJUNG PINANG', nominal: 1000000},
            {id: 'ID 65.71', nama_daerah: 'KOTA TARAKAN', nominal: 1000000},
            {id: 'ID 32.78', nama_daerah: 'KOTA TASIKMALAYA', nominal: 1000000},
            {id: 'ID 12.76', nama_daerah: 'KOTA TEBING TINGGI', nominal: 1000000},
            {id: 'ID 33.76', nama_daerah: 'KOTA TEGAL', nominal: 1000000},
            {id: 'ID 82.71', nama_daerah: 'KOTA TERNATE', nominal: 1000000},
            {id: 'ID 71.73', nama_daerah: 'KOTA TOMOHON', nominal: 1000000},
            {id: 'ID 34.71', nama_daerah: 'KOTA YOGYAKARTA', nominal: 1000000},
            {id: 'MYS-00001', nama_daerah: 'Malaysia', nominal: 1000000},
            {id: 'SIN-01001', nama_daerah: 'Singapore', nominal: 1000000}
        ];

        // Ambil data dari DB (jika ada) atau gunakan default
        if (type === 'UMP') {
            simulasiAllData = dbData.length > 0 ? dbData : defaultUmp;
        } else {
            simulasiAllData = dbData.length > 0 ? dbData : defaultUmk;
        }

        const select = document.getElementById('simulasiRegion');
        const placeholderText = type === 'UMP' ? '-- Select Province --' : '-- Select City/Regency --';
        select.innerHTML = `<option value="">${placeholderText}</option>` + 
            simulasiAllData.map(r => `<option value="${r.id}">${r.nama_daerah}</option>`).join('');
    } catch (err) { console.error(err); }
}

function hitungSimulasiGaji() {
    const regionId = document.getElementById('simulasiRegion').value;
    const region = simulasiAllData.find(r => r.id == regionId);
    if (!region) return;
    const basic = parseFloat(region.nominal);
    const allowance = basic * 0.1; // Estimasi 10%
    const total = basic + allowance;

    document.getElementById('simBasic').innerText = formatNominal(basic);
    document.getElementById('simAllowance').innerText = formatNominal(allowance);
    document.getElementById('simTotal').innerText = formatNominal(total);

    document.getElementById('simulasiResult').style.display = 'block';
    document.getElementById('simulasiResult').scrollIntoView({ behavior: 'smooth' });
}

// Function to format Rupiah as the user types
function formatRupiahInput(element) {
    let value = element.value.replace(/[^,\d]/g, '').toString();
    let split = value.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    element.value = rupiah ? rupiah : '';
}

// Function to handle save Nominal
function simpanNominalManual() {
    const inputVal = document.getElementById('inputUmrNominal').value;
    const nominal = parseInt(inputVal.replace(/\./g, ''));
    if (!nominal || nominal <= 0) {
        showToast('Please enter a valid salary nominal!', 'error');
        return;
    }
    
    localStorage.setItem('simulasi_nominal', nominal);
    showToast('Nominal Rp ' + inputVal + ' saved successfully for simulation.', 'success');
    document.getElementById('inputUmrNominal').value = '';
    updateNominalDisplay();
}

function updateNominalDisplay() {
    const savedNominal = localStorage.getItem('simulasi_nominal');
    const displayDiv = document.getElementById('displayNominalTersimpan');
    const valSpan = document.getElementById('valNominalTersimpan');
    if (displayDiv && valSpan) {
        if (savedNominal) {
            valSpan.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(parseInt(savedNominal));
            displayDiv.style.display = 'block';
        } else {
            displayDiv.style.display = 'none';
        }
    }
}
