// === Holiday Calendar Module ===

let currentHolidayMonth = new Date().getMonth();
let currentHolidayYear = new Date().getFullYear();
let currentHolidayViewMode = 'calendar';
let allHolidaysData = [];

async function loadHolidays() {
    try {
        // Silently sync with Google Calendar in background
        fetch(`${API_URL}/holidays/sync`, { method: 'POST' })
            .then(res => {
                if (res.ok) {
                    fetch(`${API_URL}/holidays?tahun=${currentHolidayYear}`)
                        .then(r => r.json())
                        .then(data => {
                            allHolidaysData = data || [];
                            renderHolidayView();
                        });
                }
            })
            .catch(err => console.error('Silent Google Calendar sync failed:', err));

        const res = await fetch(`${API_URL}/holidays?tahun=${currentHolidayYear}`);
        if (!res.ok) throw new Error('Network response was not ok');
        allHolidaysData = await res.json();
        renderHolidayView();
    } catch (e) {
        console.error('Error fetching holidays:', e);
        showToast('Gagal memuat data hari libur: ' + e.message, 'error');
    }
}

function switchHolidayView(mode) {
    currentHolidayViewMode = mode;
    const btnCal = document.getElementById('btnHolidayViewCalendar');
    const btnList = document.getElementById('btnHolidayViewList');
    const containerCal = document.getElementById('holidayCalendarContainer');
    const containerList = document.getElementById('holidayListContainer');

    if (!btnCal || !btnList || !containerCal || !containerList) return;

    if (mode === 'calendar') {
        btnCal.style.background = 'var(--primary-color)';
        btnCal.style.color = 'white';
        btnList.style.background = 'transparent';
        btnList.style.color = '#64748b';
        containerCal.style.display = 'block';
        containerList.style.display = 'none';
    } else {
        btnList.style.background = 'var(--primary-color)';
        btnList.style.color = 'white';
        btnCal.style.background = 'transparent';
        btnCal.style.color = '#64748b';
        containerCal.style.display = 'none';
        containerList.style.display = 'block';
    }
    renderHolidayView();
}

function renderHolidayView() {
    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const titleEl = document.getElementById('holidayCurrentMonthYear');
    if (titleEl) {
        titleEl.innerText = `${monthNames[currentHolidayMonth]} ${currentHolidayYear}`;
    }

    const monthSelect = document.getElementById('holidayMonthSelect');
    const yearSelect = document.getElementById('holidayYearSelect');
    if (monthSelect) monthSelect.value = currentHolidayMonth;
    if (yearSelect) yearSelect.value = currentHolidayYear;

    const activeMonthStr = String(currentHolidayMonth + 1).padStart(2, '0');
    const monthlyHolidays = allHolidaysData.filter(h => {
        return h.tanggal.startsWith(`${currentHolidayYear}-${activeMonthStr}`);
    });

    if (currentHolidayViewMode === 'calendar') {
        renderCalendarGrid(monthlyHolidays);
        renderSideSummary(monthlyHolidays);
    } else {
        renderListTable(allHolidaysData);
    }
}

function renderCalendarGrid(monthlyHolidays) {
    const grid = document.getElementById('holidayCalendarGrid');
    if (!grid) return;

    grid.innerHTML = '';

    const Y = currentHolidayYear;
    const M = currentHolidayMonth;

    const firstDayIndex = new Date(Y, M, 1).getDay();
    const totalDays = new Date(Y, M + 1, 0).getDate();
    const prevTotalDays = new Date(Y, M, 0).getDate();

    // 1. Render preceding month days
    for (let i = firstDayIndex - 1; i >= 0; i--) {
        const day = prevTotalDays - i;
        const cell = document.createElement('div');
        cell.style.cssText = `
            background: #f8fafc;
            color: #cbd5e1;
            padding: 10px;
            min-height: 125px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-end;
            font-size: 13px;
            border: 1px solid #f1f5f9;
            cursor: not-allowed;
            user-select: none;
        `;
        cell.innerHTML = `<span>${day}</span>`;
        grid.appendChild(cell);
    }

    // 2. Render current month days
    for (let day = 1; day <= totalDays; day++) {
        const dateStr = `${Y}-${String(M + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isSunday = new Date(Y, M, day).getDay() === 0;
        const holiday = monthlyHolidays.find(h => h.tanggal === dateStr);

        const cell = document.createElement('div');
        cell.style.cssText = `
            background: white;
            color: #334155;
            padding: 8px;
            min-height: 125px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 13px;
            position: relative;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            border: 1px solid #f1f5f9;
        `;

        cell.onmouseover = () => {
            cell.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)';
            cell.style.zIndex = '5';
            cell.style.transform = 'translateY(-2px)';
        };
        cell.onmouseout = () => {
            cell.style.boxShadow = 'none';
            cell.style.zIndex = '1';
            cell.style.transform = 'none';
        };

        let cellContent = '';

        if (holiday) {
            cell.style.background = '#fef2f2';
            cell.style.color = '#991b1b';
            
            cellContent = `
                <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                    <span style="font-size:10px; color:#ef4444; font-weight:700;"><i class="fas fa-umbrella-beach"></i> Libur</span>
                    <span style="font-weight: 700; font-size: 14px;">${day}</span>
                </div>
                <div class="holiday-tooltip-trigger" style="background:#fee2e2; border-left:3px solid #ef4444; padding:4px 6px; border-radius:4px; font-size:11px; font-weight:600; color:#b91c1c; text-align:left; word-break:break-word; max-height:45px; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;" title="${holiday.deskripsi}">
                    ${holiday.deskripsi}
                </div>
            `;
            cell.onclick = () => confirmDeleteHoliday(holiday.id, holiday.tanggal, holiday.deskripsi);
        } else if (isSunday) {
            cell.style.background = '#fffbeb';
            cell.style.color = '#b45309';
            
            cellContent = `
                <div style="display:flex; justify-content:space-between; width:100%;">
                    <span style="font-size:10px; color:#d97706; font-weight:600;">Minggu</span>
                    <span style="font-weight: 700; font-size: 14px; color:#ef4444;">${day}</span>
                </div>
                <div></div>
            `;
            cell.onclick = () => bukaModalHolidayDenganTanggal(dateStr);
        } else {
            cellContent = `
                <div style="display:flex; justify-content:flex-end; width:100%;">
                    <span style="font-weight: 500; font-size: 14px;">${day}</span>
                </div>
                <div></div>
            `;
            cell.onclick = () => bukaModalHolidayDenganTanggal(dateStr);
        }

        cell.innerHTML = cellContent;
        grid.appendChild(cell);
    }

    // 3. Render succeeding month days
    const totalRendered = firstDayIndex + totalDays;
    const remainingCells = (totalRendered % 7 === 0) ? 0 : 7 - (totalRendered % 7);
    
    for (let day = 1; day <= remainingCells; day++) {
        const cell = document.createElement('div');
        cell.style.cssText = `
            background: #f8fafc;
            color: #cbd5e1;
            padding: 10px;
            min-height: 125px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-end;
            font-size: 13px;
            border: 1px solid #f1f5f9;
            cursor: not-allowed;
            user-select: none;
        `;
        cell.innerHTML = `<span>${day}</span>`;
        grid.appendChild(cell);
    }
}

function renderSideSummary(monthlyHolidays) {
    const summaryEl = document.getElementById('holidaySideSummary');
    if (!summaryEl) return;

    if (monthlyHolidays.length === 0) {
        summaryEl.innerHTML = `
            <div style="text-align:center; padding:30px 10px; color:#94a3b8; background:white; border-radius:8px; border:1px dashed #cbd5e1;">
                <i class="fas fa-umbrella-beach" style="font-size:24px; margin-bottom:8px; display:block; color:#cbd5e1;"></i>
                Tidak ada hari libur bulan ini.
            </div>
        `;
        return;
    }

    summaryEl.innerHTML = monthlyHolidays.map(h => {
        const d = new Date(h.tanggal);
        const dayNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const dayName = dayNames[d.getDay()];
        
        return `
            <div style="background:white; border:1px solid #e2e8f0; border-radius:8px; padding:12px; display:flex; align-items:center; gap:12px; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
                <div style="background:#fee2e2; color:#ef4444; font-weight:700; border-radius:6px; width:45px; height:45px; display:flex; flex-direction:column; align-items:center; justify-content:center; flex-shrink:0;">
                    <span style="font-size:9px; text-transform:uppercase; font-weight:600; line-height:1;">${dayName}</span>
                    <span style="font-size:16px; line-height:1.2; font-weight:700;">${d.getDate()}</span>
                </div>
                <div style="flex-grow:1; min-width:0;">
                    <p style="margin:0; font-weight:600; color:#1e293b; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${h.deskripsi}">${h.deskripsi}</p>
                    <p style="margin:2px 0 0 0; color:#64748b; font-size:11px;">${h.tanggal}</p>
                </div>
                <button onclick="confirmDeleteHoliday(${h.id}, '${h.tanggal}', '${h.deskripsi}')" style="background:transparent; border:none; color:#94a3b8; cursor:pointer; padding:4px 8px; transition:color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;
    }).join('');
}

function renderListTable(allHolidaysData) {
    const tbody = document.getElementById('holidayTableBody');
    if (!tbody) return;

    if (!allHolidaysData || allHolidaysData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fas fa-umbrella-beach" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
            Belum ada hari libur untuk tahun ini.</td></tr>`;
        return;
    }

    const dayNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    tbody.innerHTML = allHolidaysData.map((h, i) => {
        const d = new Date(h.tanggal);
        const dayName = dayNames[d.getDay()];
        const tanggalFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        return `<tr style="border-bottom: 1px solid #f1f5f9;">
            <td style="text-align:center;padding:14px;color:#64748b;">${i+1}</td>
            <td style="padding:14px;font-weight:600;color:#1e293b;">${tanggalFormatted}</td>
            <td style="padding:14px;color:#475569;">${dayName}</td>
            <td style="padding:14px;color:#475569;">${h.deskripsi || '-'}</td>
            <td style="text-align:center;padding:14px;">
                <button onclick="confirmDeleteHoliday(${h.id}, '${h.tanggal}', '${h.deskripsi}')" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
    }).join('');
}

function navigateHolidayMonth(dir) {
    currentHolidayMonth += dir;
    if (currentHolidayMonth < 0) {
        currentHolidayMonth = 11;
        currentHolidayYear--;
        loadHolidays();
        return;
    } else if (currentHolidayMonth > 11) {
        currentHolidayMonth = 0;
        currentHolidayYear++;
        loadHolidays();
        return;
    }
    renderHolidayView();
}

function onHolidayMonthYearChange() {
    const monthSelect = document.getElementById('holidayMonthSelect');
    const yearSelect = document.getElementById('holidayYearSelect');
    
    const newMonth = monthSelect ? parseInt(monthSelect.value) : currentHolidayMonth;
    const newYear = yearSelect ? parseInt(yearSelect.value) : currentHolidayYear;

    const yearChanged = (newYear !== currentHolidayYear);

    currentHolidayMonth = newMonth;
    currentHolidayYear = newYear;

    if (yearChanged) {
        loadHolidays();
    } else {
        renderHolidayView();
    }
}

function bukaModalHoliday() {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    bukaModalHolidayDenganTanggal(`${yyyy}-${mm}-${dd}`);
}

function bukaModalHolidayDenganTanggal(dateStr) {
    document.getElementById('holidayForm')?.reset();
    document.getElementById('holidayModalTitle').innerText = 'Tambah Hari Libur';
    document.getElementById('holidayId').value = '';
    
    const dateInput = document.getElementById('holidayTanggal');
    if (dateInput) {
        dateInput.value = dateStr;
    }
    openModal('holidayModal');
}

async function simpanHoliday(e) {
    e.preventDefault();
    const id = document.getElementById('holidayId').value;
    const tanggal = document.getElementById('holidayTanggal').value;
    const deskripsi = document.getElementById('holidayDeskripsi').value;

    if (!tanggal || !deskripsi) {
        showToast('Tanggal dan deskripsi wajib diisi!', 'error');
        return;
    }

    const url = id ? `${API_URL}/holidays/${id}` : `${API_URL}/holidays`;
    const method = id ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tanggal, deskripsi })
        });
        const data = await res.json();
        if (!res.ok) {
            showToast(data.messages?.error || 'Gagal menyimpan!', 'error');
            return;
        }
        showToast(data.message || 'Hari libur berhasil disimpan!', 'success');
        closeModal('holidayModal');
        loadHolidays();
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
    }
}

async function deleteHoliday(id) {
    await confirmDeleteHoliday(id, '', 'hari libur ini');
}

async function confirmDeleteHoliday(id, tanggal, deskripsi) {
    const dateInfo = tanggal ? ` pada tanggal ${tanggal}` : '';
    if (!await showConfirm(`Yakin ingin menghapus hari libur "${deskripsi}"${dateInfo}?`)) return;
    try {
        await fetch(`${API_URL}/holidays/${id}`, { method: 'DELETE' });
        showToast('Hari libur berhasil dihapus!', 'success');
        loadHolidays();
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
    }
}

async function syncGoogleCalendar() {
    const btn = document.getElementById('btnSyncGoogleCalendar');
    const originalHtml = btn ? btn.innerHTML : '';
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
    }

    try {
        const res = await fetch(`${API_URL}/holidays/sync`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        
        if (!res.ok) {
            throw new Error(data.message || 'Gagal melakukan sinkronisasi.');
        }

        showToast(data.message || 'Sinkronisasi berhasil!', 'success');
        loadHolidays();
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
}

// Expose functions globally
Object.assign(window, {
    loadHolidays,
    bukaModalHoliday,
    bukaModalHolidayDenganTanggal,
    simpanHoliday,
    deleteHoliday,
    confirmDeleteHoliday,
    switchHolidayView,
    navigateHolidayMonth,
    onHolidayMonthYearChange,
    syncGoogleCalendar
});
