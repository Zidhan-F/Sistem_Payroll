// === Overtime Module ===

async function loadOvertimeClients() {
    const select = document.getElementById('overtimeClientSelect');
    if (!select) return;
    try {
        const res = await fetch(`${API_URL}/clients`);
        const clients = await res.json();
        select.innerHTML = '<option value="">-- Pilih Client --</option>';
        clients.forEach(c => {
            select.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
        });
    } catch(e) { console.error(e); }
}

async function loadOvertimeLogs() {
    const tbody = document.getElementById('overtimeTableBody');
    if (!tbody) return;
    const clientId = document.getElementById('overtimeClientSelect')?.value;
    const bulan = document.getElementById('overtimeMonthSelect')?.value;
    const tahun = document.getElementById('overtimeYearSelect')?.value;

    if (!clientId) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-clock" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
            Pilih client terlebih dahulu.</td></tr>`;
        return;
    }

    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:#94a3b8;">
        <i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block;"></i>Memuat data...</td></tr>`;

    try {
        const res = await fetch(`${API_URL}/overtime-logs?client_id=${clientId}&bulan=${bulan}&tahun=${tahun}`);
        const data = await res.json();

        if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:#94a3b8;">
                <i class="fas fa-clock" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
                Belum ada data lembur untuk periode ini.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.map((o, i) => {
            const d = new Date(o.tanggal);
            const tanggalFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            const isHoliday = parseInt(o.is_holiday);
            const tipeLabel = isHoliday ? 'Hari Libur' : 'Hari Kerja';
            const tipeStyle = isHoliday
                ? 'background:#fef3c7;color:#92400e;'
                : 'background:#dcfce7;color:#166534;';
            return `<tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="text-align:center;padding:12px;color:#64748b;">${i+1}</td>
                <td style="padding:12px;font-weight:600;color:#1e293b;">${o.employee_name || '-'}</td>
                <td style="text-align:center;padding:12px;color:#475569;">${tanggalFormatted}</td>
                <td style="text-align:center;padding:12px;font-weight:700;color:#1e293b;">${parseFloat(o.jam_lembur)} jam</td>
                <td style="text-align:center;padding:12px;">
                    <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;${tipeStyle}">${tipeLabel}</span>
                </td>
                <td style="padding:12px;color:#475569;max-width:200px;overflow:hidden;text-overflow:ellipsis;">${o.keterangan || '-'}</td>
                <td style="text-align:center;padding:12px;">
                    <button onclick="deleteOvertimeLog(${o.id})" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:#ef4444;">Gagal memuat data: ${e.message}</td></tr>`;
    }
}

async function loadOvertimeEmployees() {
    const select = document.getElementById('overtimeEmployeeSelect');
    if (!select) return;
    const clientId = document.getElementById('overtimeClientSelect')?.value;
    if (!clientId) { select.innerHTML = '<option value="">-- Pilih Karyawan --</option>'; return; }

    try {
        const res = await fetch(`${API_URL}/employees?client_id=${clientId}`);
        const emps = await res.json();
        select.innerHTML = '<option value="">-- Pilih Karyawan --</option>';
        (emps.data || emps).forEach(e => {
            select.innerHTML += `<option value="${e.id}">${e.nama}</option>`;
        });
    } catch(e) { console.error(e); }
}

function bukaModalOvertime() {
    const clientId = document.getElementById('overtimeClientSelect')?.value;
    if (!clientId) { showToast('Pilih client terlebih dahulu!', 'error'); return; }
    document.getElementById('overtimeForm')?.reset();
    document.getElementById('overtimeModalTitle').innerText = 'Input Lembur';
    loadOvertimeEmployees();
    openModal('overtimeModal');
}

async function simpanOvertime(e) {
    e.preventDefault();
    const employeeId = document.getElementById('overtimeEmployeeSelect')?.value;
    const tanggal = document.getElementById('overtimeTanggal')?.value;
    const jamLembur = document.getElementById('overtimeJamLembur')?.value;
    const isHoliday = document.getElementById('overtimeIsHoliday')?.checked ? 1 : 0;
    const keterangan = document.getElementById('overtimeKeterangan')?.value;

    if (!employeeId || !tanggal || !jamLembur) {
        showToast('Karyawan, tanggal, dan jam lembur wajib diisi!', 'error');
        return;
    }

    // Client-side validation: max 3 jam untuk hari kerja
    if (!isHoliday && parseFloat(jamLembur) > 3) {
        showToast('Lembur hari kerja maksimal 3 jam per hari!', 'error');
        return;
    }

    try {
        const res = await fetch(`${API_URL}/overtime-logs`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ employee_id: employeeId, tanggal, jam_lembur: jamLembur, is_holiday: isHoliday, keterangan })
        });
        const data = await res.json();
        if (!res.ok) {
            showToast(data.messages?.error || 'Gagal menyimpan!', 'error');
            return;
        }
        showToast(data.message || 'Lembur berhasil disimpan!', 'success');
        closeModal('overtimeModal');
        loadOvertimeLogs();
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
    }
}

async function deleteOvertimeLog(id) {
    if (!await showConfirm('Yakin ingin menghapus log lembur ini?')) return;
    try {
        await fetch(`${API_URL}/overtime-logs/${id}`, { method: 'DELETE' });
        showToast('Log lembur berhasil dihapus!', 'success');
        loadOvertimeLogs();
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
    }
}
