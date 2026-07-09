const loginForm = document.getElementById('loginForm');
const forgotForm = document.getElementById('forgotForm');
const resetForm = document.getElementById('resetForm');
const API_URL = BASE_URL + 'index.php/api';

/**
 * Toast Notification Helper
 */
function showToast(message, type = 'success', duration = 5000) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    // Support HTML content inside toast for simulation notifications
    toast.innerHTML = `
        <i class="fas ${icon}"></i>
        <div style="display: flex; flex-direction: column; gap: 2px;">
            <span>${message}</span>
        </div>
    `;

    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, duration);
}

/**
 * Switch between Card Panels
 */
function showCard(cardId) {
    const cards = ['loginCard', 'forgotCard', 'resetCard', 'registerCard'];
    cards.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            if (id === cardId) {
                el.style.display = 'block';
                el.classList.add('fade-in');
            } else {
                el.style.display = 'none';
                el.classList.remove('fade-in');
            }
        }
    });
}

/**
 * Toggle Password Input Visibility
 */
function togglePasswordInput(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

/**
 * Email Validation Helper
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Handler 1: Sign In Form Submit
 */
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const usernameOrEmail = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        
        // Email format validation check if input contains '@'
        if (usernameOrEmail.includes('@')) {
            if (!isValidEmail(usernameOrEmail)) {
                showToast('Invalid email format. Use format name@domain.com', 'error');
                return;
            }
        }
        
        try {
            const response = await fetch(`${API_URL}/login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: usernameOrEmail, password })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                showToast('Login successful! Redirecting...', 'success', 2000);
                localStorage.clear(); // Clear all old workspace/view states
                localStorage.setItem('user', JSON.stringify(data.user));
                document.cookie = "user_role=" + data.user.role + "; path=/; max-age=31536000";
                
                setTimeout(() => {
                    window.location.href = BASE_URL + 'index.php/dashboard';
                }, 1500);
            } else {
                showToast(data.message || data.messages?.error || 'Login failed', 'error');
            }
        } catch (err) {
            console.error('Error:', err);
            showToast('A server connection error occurred', 'error');
        }
    });
}

/**
 * Handler 2: Forgot Password Form Submit
 */
if (forgotForm) {
    forgotForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('forgotEmail').value.trim();
        
        if (!isValidEmail(email)) {
            showToast('Invalid email format', 'error');
            return;
        }
        
        try {
            const response = await fetch(`${API_URL}/auth/forgot-password`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email })
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                // Display simulated OTP with a special large & long-lasting toast
                showToast(
                    `<strong>OTP Sent! (SIMULATION)</strong><br>Check email: <strong>${data.otp}</strong>`,
                    'success',
                    15000 // 15 seconds so user can easily read the simulated OTP
                );

                // Insert email into reset form hidden input
                document.getElementById('resetEmailHidden').value = email;
                document.getElementById('resetOTP').value = '';
                document.getElementById('resetPassword').value = '';
                document.getElementById('resetConfirmPassword').value = '';
                
                // Switch to reset password card
                setTimeout(() => showCard('resetCard'), 1000);
            } else {
                showToast(data.message || data.messages?.error || 'Failed to request OTP', 'error');
            }
        } catch (err) {
            console.error('Error:', err);
            showToast('A server connection error occurred', 'error');
        }
    });
}

/**
 * Handler 3: Reset Password Form Submit
 */
if (resetForm) {
    resetForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('resetEmailHidden').value;
        const token = document.getElementById('resetOTP').value.trim();
        const password = document.getElementById('resetPassword').value;
        const confirmPassword = document.getElementById('resetConfirmPassword').value;
        
        if (!token || token.length !== 6) {
            showToast('OTP code must be 6 digits', 'error');
            return;
        }

        if (password.length < 6) {
            showToast('New password must be at least 6 characters', 'error');
            return;
        }

        if (password !== confirmPassword) {
            showToast('New password confirmation does not match', 'error');
            return;
        }
        
        try {
            const response = await fetch(`${API_URL}/auth/reset-password`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email,
                    token,
                    password,
                    confirm_password: confirmPassword
                })
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                showToast(data.message || 'Password updated successfully!', 'success');
                setTimeout(() => showCard('loginCard'), 1500);
            } else {
                showToast(data.message || data.messages?.error || 'Reset password failed', 'error');
            }
        } catch (err) {
            console.error('Error:', err);
            showToast('A server connection error occurred', 'error');
        }
    });
}

/**
 * Handler 4: Register Form Submit
 */
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const username = document.getElementById('regUsername').value.trim();
        const fullName = document.getElementById('regFullName').value.trim();
        const email = document.getElementById('regEmail').value.trim();
        const password = document.getElementById('regPassword').value;
        const confirmPassword = document.getElementById('regConfirmPassword').value;
        
        if (username.length < 3) {
            showToast('Username must be at least 3 characters', 'error');
            return;
        }

        if (!isValidEmail(email)) {
            showToast('Invalid email format', 'error');
            return;
        }

        if (password.length < 6) {
            showToast('Password must be at least 6 characters', 'error');
            return;
        }

        if (password !== confirmPassword) {
            showToast('Password confirmation does not match', 'error');
            return;
        }
        
        try {
            const response = await fetch(`${API_URL}/auth/register`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    username,
                    full_name: fullName,
                    email,
                    password,
                    confirm_password: confirmPassword
                })
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                showToast(data.message || 'Registration successful!', 'success', 6000);
                // Return to login panel
                setTimeout(() => showCard('loginCard'), 1500);
            } else {
                showToast(data.message || data.messages?.error || 'Registration failed', 'error');
            }
        } catch (err) {
            console.error('Error:', err);
            showToast('A server connection error occurred', 'error');
        }
    });
}
