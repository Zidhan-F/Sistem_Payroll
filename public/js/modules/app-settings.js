// ===== SYSTEM SETTINGS MODULE =====

async function fetchSystemSettings() {
    try {
        const res = await fetch(`${API_URL}/settings`);
        if (!res.ok) throw new Error('Failed to fetch settings');
        const settings = await res.json();
        
        // Find overtime_divisor
        const divisorObj = settings.find(s => s.setting_key === 'overtime_divisor');
        const divisorInput = document.getElementById('settingOvertimeDivisor');
        if (divisorInput) {
            divisorInput.value = divisorObj ? divisorObj.setting_value : '160';
        }

        // Render settings list table
        const tbody = document.getElementById('systemSettingsTableBody');
        if (tbody) {
            if (!settings || settings.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:20px;color:#94a3b8;">No configuration settings found.</td></tr>`;
            } else {
                tbody.innerHTML = settings.map((s, idx) => {
                    let keyLabel = s.setting_key;
                    let desc = 'System configuration parameter.';
                    if (s.setting_key === 'overtime_divisor') {
                        keyLabel = 'Overtime Divisor';
                        desc = 'Default hours divisor used to calculate employee hourly rate for overtime payments.';
                    }
                    
                    const updatedAt = s.updated_at 
                        ? new Date(s.updated_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
                        : '-';
                    
                    return `<tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="text-align:center;padding:12px;color:#64748b;">${idx + 1}</td>
                        <td style="padding:12px;font-weight:600;color:#1e293b;">
                            <div>${keyLabel}</div>
                            <small style="font-weight:normal;color:#64748b;font-size:12px;">${desc}</small>
                        </td>
                        <td style="padding:12px;color:#475569;"><code>${s.setting_key}</code></td>
                        <td style="text-align:center;padding:12px;font-weight:700;color:#1e293b;">${s.setting_value}</td>
                        <td style="text-align:center;padding:12px;color:#64748b;font-size:13px;">${updatedAt}</td>
                    </tr>`;
                }).join('');
            }
        }
    } catch (err) {
        console.error('Error fetching system settings:', err);
    }
}

async function saveSystemSettings(event) {
    if (event) event.preventDefault();
    
    const divisorInput = document.getElementById('settingOvertimeDivisor');
    if (!divisorInput) return;
    
    const divisorValue = divisorInput.value.trim();
    if (!divisorValue || isNaN(divisorValue) || parseFloat(divisorValue) <= 0) {
        showToast('Please enter a valid overtime divisor greater than 0', 'error');
        return;
    }
    
    try {
        const res = await fetch(`${API_URL}/settings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                overtime_divisor: divisorValue
            })
        });
        
        if (res.ok) {
            showToast('System configuration settings saved successfully!', 'success');
            await fetchSystemSettings();
        } else {
            const err = await res.json().catch(() => ({}));
            showToast(err.message || 'Failed to save system settings', 'error');
        }
    } catch (err) {
        console.error('Error saving system settings:', err);
        showToast('Connection error occurred while saving settings', 'error');
    }
}

// Initialize on load and register functions globally
document.addEventListener('DOMContentLoaded', () => {
    fetchSystemSettings();
});

window.fetchSystemSettings = fetchSystemSettings;
window.saveSystemSettings = saveSystemSettings;
