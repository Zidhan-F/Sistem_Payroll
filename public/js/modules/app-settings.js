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
