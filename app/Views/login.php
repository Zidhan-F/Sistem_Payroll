<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiPayroll - Professional Remuneration Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/login.css?v=' . time()) ?>">
</head>
<body>
    <div class="split-container">
        <!-- LEFT PANEL: Brand Info & Features -->
        <div class="brand-panel">
            <div class="brand-overlay"></div>
            <div class="brand-content">
                <div class="brand-logo" style="display: flex; align-items: center; gap: 12px;">
                    <img src="<?= base_url('images/logo.png') ?>" alt="BiPayroll Logo" style="width: 46px; height: 46px; object-fit: contain; background: white; border-radius: 50%; padding: 3px;">
                    <span>BiPayroll</span>
                </div>
                
                <div class="brand-heading">
                    <h1>BiPayroll Platform<br><span>Terintegrasi Digital</span></h1>
                    <p>Platform profesional untuk pengelolaan gaji, kehadiran, dan slip remunerasi karyawan secara otomatis, efisien, dan transparan.</p>
                </div>
                
                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Multi-Role Security</h4>
                            <p>Sistem otorisasi RBAC 7 peran utama untuk pembatasan data yang aman.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Dynamic Payroll Scheme</h4>
                            <p>Penyesuaian otomatis tunjangan, BPJS, PPh 21, dan UMK secara fleksibel.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-circle-check"></i>
                        </div>
                        <div class="feature-text">
                            <h4>One-Click Slip Generation</h4>
                            <p>Kalkulasi absensi otomatis dan pencetakan slip gaji digital instan.</p>
                        </div>
                    </div>
                </div>
                
                <div class="brand-footer">
                    <p>&copy; 2026 BiPayroll. All rights reserved.</p>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: Forms Container -->
        <div class="form-panel">
            <div class="form-container-box">
                
                <!-- CARD 1: SIGN IN / LOGIN -->
                <div class="form-box" id="loginCard">
                    <div class="form-header">
                        <h2>Selamat Datang</h2>
                        <p>Silakan masuk menggunakan akun terdaftar Anda</p>
                    </div>
                    <form id="loginForm">
                        <div class="input-group">
                            <label for="username">Username or Email</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope-open text-muted-icon"></i>
                                <input type="text" id="username" placeholder="Masukkan username atau email" required autocomplete="username">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock text-muted-icon"></i>
                                <input type="password" id="password" placeholder="Masukkan kata sandi" required autocomplete="current-password">
                                <button type="button" class="btn-eye" onclick="togglePasswordInput('password', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="remember-forgot">
                            <label class="checkbox-container">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                Remember me
                            </label>
                            <a href="javascript:void(0)" onclick="showCard('forgotCard')" class="forgot-link">Lupa Password?</a>
                        </div>
                        <button type="submit" class="btn-primary-action">Masuk Sekarang</button>
                    </form>
                    <div class="form-footer">
                        <p>Belum memiliki akun? <a href="javascript:void(0)" onclick="showCard('registerCard')">Daftar Akun Baru</a></p>
                    </div>
                </div>

                <!-- CARD 2: SIGN UP / REGISTER -->
                <div class="form-box" id="registerCard" style="display: none;">
                    <div class="form-header">
                        <h2>Daftar Akun Baru</h2>
                        <p>Buat akun Anda, Admin akan memberikan peran (role) setelah pendaftaran</p>
                    </div>
                    <form id="registerForm">
                        <div class="input-group">
                            <label for="regUsername">Username <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-user text-muted-icon"></i>
                                <input type="text" id="regUsername" placeholder="Pilih username unik" required autocomplete="username">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="regFullName">Nama Lengkap</label>
                            <div class="input-wrapper">
                                <i class="fas fa-signature text-muted-icon"></i>
                                <input type="text" id="regFullName" placeholder="Nama lengkap sesuai identitas" autocomplete="name">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="regEmail">Email Address <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope text-muted-icon"></i>
                                <input type="email" id="regEmail" placeholder="nama@domain.com" required autocomplete="email">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="regPassword">Password <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock text-muted-icon"></i>
                                <input type="password" id="regPassword" placeholder="Minimal 6 karakter" minlength="6" required autocomplete="new-password">
                                <button type="button" class="btn-eye" onclick="togglePasswordInput('regPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="regConfirmPassword">Konfirmasi Password <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-check-double text-muted-icon"></i>
                                <input type="password" id="regConfirmPassword" placeholder="Ulangi password" minlength="6" required autocomplete="new-password">
                                <button type="button" class="btn-eye" onclick="togglePasswordInput('regConfirmPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary-action" style="margin-top: 10px;">Daftar Akun</button>
                    </form>
                    <div class="form-footer">
                        <p>Sudah memiliki akun? <a href="javascript:void(0)" onclick="showCard('loginCard')">Masuk di sini</a></p>
                    </div>
                </div>

                <!-- CARD 3: FORGOT PASSWORD -->
                <div class="form-box" id="forgotCard" style="display: none;">
                    <div class="form-header">
                        <h2>Lupa Kata Sandi?</h2>
                        <p>Masukkan email terdaftar untuk meminta kode OTP pemulihan sandi</p>
                    </div>
                    <form id="forgotForm">
                        <div class="input-group">
                            <label for="forgotEmail">Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope text-muted-icon"></i>
                                <input type="email" id="forgotEmail" placeholder="nama@domain.com" required autocomplete="email">
                            </div>
                        </div>
                        <button type="submit" class="btn-primary-action" style="margin-top: 10px;">Minta Kode OTP</button>
                        <button type="button" class="btn-neutral-back" onclick="showCard('loginCard')"><i class="fas fa-arrow-left"></i> Kembali ke Login</button>
                    </form>
                </div>

                <!-- CARD 4: RESET PASSWORD -->
                <div class="form-box" id="resetCard" style="display: none;">
                    <div class="form-header">
                        <h2>Atur Ulang Sandi</h2>
                        <p>Masukkan kode OTP 6-digit dan tetapkan kata sandi baru Anda</p>
                    </div>
                    <form id="resetForm">
                        <input type="hidden" id="resetEmailHidden">
                        <div class="input-group">
                            <label for="resetOTP">Kode OTP Verifikasi</label>
                            <div class="input-wrapper">
                                <i class="fas fa-key text-muted-icon"></i>
                                <input type="text" id="resetOTP" placeholder="Masukkan 6 digit" maxlength="6" pattern="[0-9]{6}" required style="letter-spacing: 6px; text-align: center; font-weight: 700; font-size: 18px;">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="resetPassword">Kata Sandi Baru</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock text-muted-icon"></i>
                                <input type="password" id="resetPassword" placeholder="Minimal 6 karakter" minlength="6" required autocomplete="new-password">
                                <button type="button" class="btn-eye" onclick="togglePasswordInput('resetPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="resetConfirmPassword">Konfirmasi Sandi Baru</label>
                            <div class="input-wrapper">
                                <i class="fas fa-check-double text-muted-icon"></i>
                                <input type="password" id="resetConfirmPassword" placeholder="Ulangi sandi baru" minlength="6" required autocomplete="new-password">
                                <button type="button" class="btn-eye" onclick="togglePasswordInput('resetConfirmPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary-action" style="margin-top: 10px;">Perbarui Kata Sandi</button>
                        <button type="button" class="btn-neutral-back" onclick="showCard('forgotCard')"><i class="fas fa-arrow-left"></i> Kirim Ulang OTP</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
    
    <script>
        const BASE_URL = "<?= base_url() ?>";
    </script>
    <script src="<?= base_url('js/login.js?v=' . time()) ?>"></script>
</body>
</html>
