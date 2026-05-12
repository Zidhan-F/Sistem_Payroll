const loginForm = document.getElementById('loginForm');
const loginMessage = document.getElementById('loginMessage');
const API_URL = 'http://localhost:5000/api'; // Sesuaikan dengan port server Anda

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
                loginMessage.innerText = 'Login berhasil! Mengalihkan...';
                loginMessage.className = 'login-message success';
                
                // Simpan data user ke localStorage
                localStorage.setItem('user', JSON.stringify(data.user));
                
                // Redirect ke halaman utama setelah 1.5 detik
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 1500);
            } else {
                loginMessage.innerText = data.message || 'Login gagal';
                loginMessage.className = 'login-message error';
            }
        } catch (err) {
            console.error('Error:', err);
            loginMessage.innerText = 'Terjadi kesalahan koneksi ke server';
            loginMessage.className = 'login-message error';
        }
    });
}
