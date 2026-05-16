<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Payroll</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/login.css') ?>">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="logo-circle">
                    <i class="fas fa-building"></i>
                </div>
                <h1>Payroll System</h1>
                <p>Silakan login untuk mengelola payroll</p>
            </div>
            <form id="loginForm">
                <div class="input-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" placeholder="Masukkan username" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" placeholder="Masukkan password" required>
                    </div>
                </div>
                <div class="remember-forgot">
                    <label><input type="checkbox"> Ingat saya</label>
                    <a href="#">Lupa password?</a>
                </div>
                <button type="submit" class="btn-login">Login Sekarang</button>
            </form>
            <div style="margin-top: 20px; text-align: center; border-top: 1px solid #eee; padding-top: 20px;">
                <p style="color: #666; font-size: 14px; margin-bottom: 10px;">Ingin cek simulasi gaji daerah kamu?</p>
                <a href="<?= base_url('index.php/calculator') ?>" style="display: inline-block; padding: 10px 20px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; color: #2c3e50; text-decoration: none; font-weight: 600; transition: 0.3s; font-size: 14px;">
                    <i class="fas fa-calculator" style="margin-right: 8px; color: #2c3e50;"></i> Buka Kalkulator Gaji
                </a>
            </div>
            <div id="loginMessage" class="login-message"></div>
        </div>
    </div>
    
    <script>
        const BASE_URL = "<?= base_url() ?>";
    </script>
    <script src="<?= base_url('js/login.js') ?>"></script>
</body>
</html>
