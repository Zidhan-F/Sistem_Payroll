// ===== SCHEDULE MODULE =====
// Handles payroll schedule templates CRUD operations

let masterSchedules = [];
let selectedScheduleYear = 2026;
let scheduleYears = [2024, 2025, 2026, 2027];

function renderScheduleYears() {
    const selectEl = document.getElementById('scheduleYearSelect');
    if (!selectEl) return;

    selectEl.innerHTML = scheduleYears.map(year => {
        const isActive = year === selectedScheduleYear;
        return `<option value="${year}" ${isActive ? 'selected' : ''}>Tahun ${year}</option>`;
    }).join('');
    
    // Update the table title to reflect selected year
    const titleEl = document.getElementById('scheduleTableTitle');
    if (titleEl) {
        titleEl.innerText = `Master Payroll Schedule - Tahun ${selectedScheduleYear}`;
    }
}

function showPrompt(message, defaultValue = '', title = 'Input') {
    return new Promise((resolve) => {
        let overlay = document.getElementById('customPromptOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'customPromptOverlay';
            overlay.style = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(15, 23, 42, 0.6);
                backdrop-filter: blur(4px);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            
            const dialog = document.createElement('div');
            dialog.id = 'customPromptDialog';
            dialog.style = `
                background: white;
                border-radius: 16px;
                width: 400px;
                max-width: 95%;
                padding: 24px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                transform: scale(0.9);
                transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                display: flex;
                flex-direction: column;
                gap: 16px;
            `;
            
            dialog.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #2563eb; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div>
                        <h4 id="customPromptTitle" style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b;">${title}</h4>
                    </div>
                </div>
                <div id="customPromptMessage" style="font-size: 14px; color: #475569; line-height: 1.5;">${message}</div>
                <div>
                    <input type="text" id="customPromptInput" style="width: 100%; padding: 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; outline: none; font-size: 14px; color: #1e293b; transition: border-color 0.2s;" />
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px;">
                    <button id="customPromptCancel" style="padding: 10px 16px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; color: #475569; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;">Batal</button>
                    <button id="customPromptOk" style="padding: 10px 20px; border-radius: 8px; border: none; background: #e67e22; color: white; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(230, 126, 34, 0.2);">Simpan</button>
                </div>
            `;
            
            overlay.appendChild(dialog);
            document.body.appendChild(overlay);
        }
        
        // Setup values
        const dialog = document.getElementById('customPromptDialog');
        const titleEl = document.getElementById('customPromptTitle');
        const messageEl = document.getElementById('customPromptMessage');
        const inputEl = document.getElementById('customPromptInput');
        const okBtn = document.getElementById('customPromptOk');
        const cancelBtn = document.getElementById('customPromptCancel');
        
        titleEl.innerText = title;
        messageEl.innerText = message;
        inputEl.value = defaultValue;
        
        // Show with transition
        setTimeout(() => {
            overlay.style.opacity = '1';
            dialog.style.transform = 'scale(1)';
        }, 10);
        
        const close = (value) => {
            overlay.style.opacity = '0';
            dialog.style.transform = 'scale(0.9)';
            setTimeout(() => {
                overlay.remove();
            }, 300);
            resolve(value);
        };
        
        // Add events
        okBtn.onclick = () => {
            const val = inputEl.value.trim();
            close(val);
        };
        
        cancelBtn.onclick = () => {
            close(null);
        };
        
        inputEl.onkeydown = (e) => {
            if (e.key === 'Enter') {
                okBtn.click();
            } else if (e.key === 'Escape') {
                cancelBtn.click();
            }
        };
        
        // Focus input
        setTimeout(() => inputEl.focus(), 100);
    });
}

function pilihTahunSchedule(year) {
    selectedScheduleYear = parseInt(year);
    renderScheduleYears();
    renderMasterSchedule();
}

async function tambahPeriodeTahunan() {
    const defaultNewYear = Math.max(...scheduleYears) + 1;
    const yearStr = await showPrompt("Masukkan tahun baru:", defaultNewYear, "Tambah Periode Tahunan");
    if (!yearStr) return;
    const year = parseInt(yearStr);
    if (isNaN(year) || year < 2000 || year > 2100) {
        showToast("Input tahun tidak valid!", "error");
        return;
    }
    if (scheduleYears.includes(year)) {
        showToast("Tahun sudah ada!", "error");
        return;
    }
    scheduleYears.push(year);
    scheduleYears.sort((a, b) => b - a); // Sort descending
    selectedScheduleYear = year;
    renderScheduleYears();
    renderMasterSchedule();
}

async function renderMasterSchedule() {
    // Always render years first to keep UI sync
    renderScheduleYears();

    try {
        const tbody = document.getElementById('masterSchedulesContainer');
        if (!tbody) return;
        
        tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;

        const res = await fetch(`${API_URL}/schedule-templates?tahun=${selectedScheduleYear}`);
        if (!res.ok) throw new Error('Failed to fetch schedule templates');
        masterSchedules = await res.json();

        if (masterSchedules.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 40px; color: #94a3b8;"><i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>No payroll schedules configured for year ${selectedScheduleYear}. Click the "Add Schedule" button to configure.</td></tr>`;
            return;
        }

        tbody.innerHTML = masterSchedules.map((s, idx) => {
            return `
                <tr>
                    <td style="text-align: center; padding: 16px; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 14px;">${idx + 1}</td>
                    <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; color: #1e293b; font-weight: 600; font-size: 14px;">${s.nama}</td>
                    <td style="text-align: center; padding: 16px; border-bottom: 1px solid #e2e8f0; color: #1e293b; font-weight: 600; font-size: 14px;">Tgl ${s.pay_date}</td>
                    <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 13px; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${s.deskripsi || '-'}">${s.deskripsi || '-'}</td>
                    <td style="padding: 16px; border-bottom: 1px solid #e2e8f0;">
                        <div style="display: flex; justify-content: center; align-items: center; gap: 12px;">
                            <button onclick="bukaModalSchedule('edit', ${s.id})" class="btn-icon" title="Edit" style="color:#3498db; background:transparent; border:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; box-shadow:none; width:auto; height:auto; padding:4px;"><i class="fas fa-edit" style="font-size:16px;"></i></button>
                            <button onclick="hapusScheduleTemplate(${s.id})" class="btn-icon" title="Delete" style="color:#e74c3c; background:transparent; border:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; box-shadow:none; width:auto; height:auto; padding:4px;"><i class="fas fa-trash" style="font-size:16px;"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    } catch (err) {
        console.error(err);
        const tbody = document.getElementById('masterSchedulesContainer');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 20px; color: #ef4444;"><i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>Failed to load schedules.</td></tr>';
        }
    }
}

function bukaModalSchedule(mode, id = null) {
    tutupSemuaModal(true); // Close any active modals, keep sidebar open
    
    document.getElementById('modalSchedule').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';

    const titleEl = document.getElementById('modalScheduleTitle');
    const form = document.getElementById('formSchedule');
    
    form.reset();
    document.getElementById('scheduleId').value = '';

    if (mode === 'tambah') {
        titleEl.innerText = 'Add Payroll Schedule';
    } else if (mode === 'edit' && id !== null) {
        titleEl.innerText = 'Edit Payroll Schedule';
        const s = masterSchedules.find(item => item.id == id);
        if (s) {
            document.getElementById('scheduleId').value = s.id;
            document.getElementById('scheduleNama').value = s.nama;
            document.getElementById('schedulePayDate').value = s.pay_date;
            document.getElementById('scheduleCutoffStart').value = s.cutoff_start;
            document.getElementById('scheduleCutoffEnd').value = s.cutoff_end;
            document.getElementById('scheduleDeskripsi').value = s.deskripsi || '';
        }
    }
}

function tutupModalSchedule() {
    document.getElementById('modalSchedule').style.display = 'none';
    if (!document.querySelector('.sidebar').classList.contains('active')) {
        document.getElementById('overlay').style.display = 'none';
    }
}

async function hapusScheduleTemplate(id) {
    if (!await showConfirm('Apakah Anda yakin ingin menghapus schedule template ini?')) return;
    try {
        const res = await fetch(`${API_URL}/schedule-templates/${id}`, {
            method: 'DELETE'
        });
        if (res.ok) {
            showToast('Schedule template berhasil dihapus', 'success');
            renderMasterSchedule();
        } else {
            showToast('Gagal menghapus schedule template', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Terjadi kesalahan saat menghapus data', 'error');
    }
}

// Register submit listener
document.addEventListener('DOMContentLoaded', () => {
    // Initial renders
    renderScheduleYears();
    renderMasterSchedule();

    const form = document.getElementById('formSchedule');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = document.getElementById('scheduleId').value;
            const nama = document.getElementById('scheduleNama').value;
            const payDate = parseInt(document.getElementById('schedulePayDate').value);
            const cutoffStart = parseInt(document.getElementById('scheduleCutoffStart').value);
            const cutoffEnd = parseInt(document.getElementById('scheduleCutoffEnd').value);
            const deskripsi = document.getElementById('scheduleDeskripsi').value;

            // Simple validation
            if (payDate < 1 || payDate > 31 || cutoffStart < 1 || cutoffStart > 31 || cutoffEnd < 1 || cutoffEnd > 31) {
                showToast('Tanggal harus berada dalam rentang 1-31!', 'error');
                return;
            }

            const data = { 
                nama, 
                pay_date: payDate, 
                cutoff_start: cutoffStart, 
                cutoff_end: cutoffEnd, 
                deskripsi,
                tahun: selectedScheduleYear 
            };
            
            try {
                let url = `${API_URL}/schedule-templates`;
                let method = 'POST';

                if (id) {
                    url += `/${id}`;
                    method = 'PUT';
                }

                const res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    showToast(id ? 'Schedule template berhasil diupdate!' : 'Schedule template berhasil disimpan!', 'success');
                    tutupModalSchedule();
                    renderMasterSchedule();
                } else {
                    showToast('Gagal menyimpan schedule template!', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Terjadi kesalahan koneksi!', 'error');
            }
        });
    }
});

// Expose functions globally
window.renderMasterSchedule = renderMasterSchedule;
window.bukaModalSchedule = bukaModalSchedule;
window.tutupModalSchedule = tutupModalSchedule;
window.hapusScheduleTemplate = hapusScheduleTemplate;
window.pilihTahunSchedule = pilihTahunSchedule;
window.tambahPeriodeTahunan = tambahPeriodeTahunan;
