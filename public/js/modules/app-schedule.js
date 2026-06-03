// ===== SCHEDULE MODULE =====
// Handles payroll schedule templates CRUD operations

let masterSchedules = [];

async function renderMasterSchedule() {
    try {
        const tbody = document.getElementById('masterSchedulesContainer');
        if (!tbody) return;
        
        tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</td></tr>`;

        const res = await fetch(`${API_URL}/schedule-templates`);
        if (!res.ok) throw new Error('Failed to fetch schedule templates');
        masterSchedules = await res.json();

        if (masterSchedules.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 40px; color: #94a3b8;"><i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>No payroll schedules configured. Click the "Add Schedule" button to configure.</td></tr>';
            return;
        }

        tbody.innerHTML = masterSchedules.map((s, idx) => {
            return `
                <tr>
                    <td style="text-align: center; padding: 16px; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 14px;">${idx + 1}</td>
                    <td style="padding: 16px; border-bottom: 1px solid #e2e8f0; color: #1e293b; font-weight: 600; font-size: 14px;">${s.nama}</td>
                    <td style="text-align: center; padding: 16px; border-bottom: 1px solid #e2e8f0; color: #1e293b; font-weight: 600; font-size: 14px;">Tgl ${s.pay_date}</td>
                    <td style="text-align: center; padding: 16px; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 14px;">
                        <span class="scheme-badge bulanan" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd; padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 12px;">
                            ${s.cutoff_start} s/d ${s.cutoff_end}
                        </span>
                    </td>
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
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px; color: #ef4444;"><i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>Failed to load schedules.</td></tr>';
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

            const data = { nama, pay_date: payDate, cutoff_start: cutoffStart, cutoff_end: cutoffEnd, deskripsi };
            
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
