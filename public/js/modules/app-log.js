// ===== LOG AKTIVITAS MODULE =====
// Extracted from app.js for modular monolith architecture

async function renderLogAktivitas() {
    const tableBody = document.getElementById('logAktivitasTableBody');
    if (!tableBody) return;
    
    tableBody.innerHTML = `<tr><td colspan="3" class="text-center">Memuat data...</td></tr>`;
    
    try {
        const res = await fetch(`${API_URL}/logs`);
        if (!res.ok) throw new Error('Gagal mengambil data log');
        const logs = await res.json();
        
        if (logs.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="3" class="text-center" style="font-style: italic; color: #888;">Belum ada log aktivitas.</td></tr>`;
            return;
        }
        
        tableBody.innerHTML = logs.map(log => {
            const dateStr = log.created_at ? new Date(log.created_at).toLocaleString('id-ID', {
                dateStyle: 'medium',
                timeStyle: 'short'
            }) : '-';
            
            return `
                <tr>
                    <td style="font-weight: 500; color: #1e293b;">${log.action || '-'}</td>
                    <td><span class="scheme-badge rutin" style="text-transform: none;">${log.user_action || '-'}</span></td>
                    <td style="color: #64748b;">${dateStr}</td>
                </tr>
            `;
        }).join('');
    } catch (err) {
        console.error(err);
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center" style="color: var(--danger);">Gagal memuat log aktivitas.</td></tr>`;
    }
}

window.renderLogAktivitas = renderLogAktivitas;
