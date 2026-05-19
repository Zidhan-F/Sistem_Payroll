const loginForm = document.getElementById('loginForm');
const API_URL = BASE_URL + 'index.php/api';

function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    toast.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        try {
            const response = await fetch(`${API_URL}/login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                showToast('Login berhasil! Mengalihkan...', 'success');
                localStorage.setItem('user', JSON.stringify(data.user));
                
                // Redirect ke dashboard setelah 1.5 detik
                setTimeout(() => {
                    window.location.href = BASE_URL + 'index.php/dashboard';
                }, 1500);
            } else {
                showToast(data.message || 'Login gagal', 'error');
            }
        } catch (err) {
            console.error('Error:', err);
            showToast('Terjadi kesalahan koneksi ke server', 'error');
        }
    });
}
