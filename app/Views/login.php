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
                    <img src="<?= base_url('images/logo.png') ?>" alt="BiPayroll Logo" style="width: 48px; height: 48px; object-fit: contain; background: white; border-radius: 50%; padding: 4px; border: 1px solid rgba(226, 232, 240, 0.8); box-shadow: 0 4px 10px rgba(15, 23, 42, 0.05);">
                </div>
                
                <div class="brand-heading">
                    <h1>BiPayroll Platform<br><span>Integrated Digital</span></h1>
                    <p>A professional platform for automated, efficient, and transparent payroll management, attendance tracking, and employee remuneration slips.</p>
                </div>
                
                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Multi-Role Security</h4>
                            <p>RBAC authorization system with 7 main roles for secure data access control.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Dynamic Payroll Scheme</h4>
                            <p>Flexible automatic adjustment of allowances, BPJS, PPh 21, and minimum wage.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-circle-check"></i>
                        </div>
                        <div class="feature-text">
                            <h4>One-Click Slip Generation</h4>
                            <p>Automatic attendance calculation and instant digital payslip generation.</p>
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
                        <h2>Welcome</h2>
                        <p>Please sign in with your registered account</p>
                    </div>
                    <form id="loginForm">
                        <div class="input-group">
                            <label for="username">Username or Email</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope-open text-muted-icon"></i>
                                <input type="text" id="username" placeholder="Enter username or email" required autocomplete="username">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock text-muted-icon"></i>
                                <input type="password" id="password" placeholder="Enter your password" required autocomplete="current-password">
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
                            <a href="javascript:void(0)" onclick="showCard('forgotCard')" class="forgot-link">Forgot Password?</a>
                        </div>
                        <button type="submit" class="btn-primary-action">Sign In</button>
                    </form>
                    <div class="form-footer">
                        <p>Don't have an account? <a href="javascript:void(0)" onclick="showCard('registerCard')">Create New Account</a></p>
                    </div>
                </div>

                <!-- CARD 2: SIGN UP / REGISTER -->
                <div class="form-box" id="registerCard" style="display: none;">
                    <div class="form-header">
                        <h2>Create New Account</h2>
                        <p>Create your account. Admin will assign your role after registration</p>
                    </div>
                    <form id="registerForm">
                        <div class="input-group">
                            <label for="regUsername">Username <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-user text-muted-icon"></i>
                                <input type="text" id="regUsername" placeholder="Choose a unique username" required autocomplete="username">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="regFullName">Full Name</label>
                            <div class="input-wrapper">
                                <i class="fas fa-signature text-muted-icon"></i>
                                <input type="text" id="regFullName" placeholder="Full name as per ID" autocomplete="name">
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
                                <input type="password" id="regPassword" placeholder="Minimum 6 characters" minlength="6" required autocomplete="new-password">
                                <button type="button" class="btn-eye" onclick="togglePasswordInput('regPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="regConfirmPassword">Confirm Password <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-check-double text-muted-icon"></i>
                                <input type="password" id="regConfirmPassword" placeholder="Repeat password" minlength="6" required autocomplete="new-password">
                                <button type="button" class="btn-eye" onclick="togglePasswordInput('regConfirmPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary-action" style="margin-top: 10px;">Register Account</button>
                    </form>
                    <div class="form-footer">
                        <p>Already have an account? <a href="javascript:void(0)" onclick="showCard('loginCard')">Sign in here</a></p>
                    </div>
                </div>

                <!-- CARD 3: FORGOT PASSWORD -->
                <div class="form-box" id="forgotCard" style="display: none;">
                    <div class="form-header">
                        <h2>Forgot Password?</h2>
                        <p>Enter your registered email to request an OTP recovery code</p>
                    </div>
                    <form id="forgotForm">
                        <div class="input-group">
                            <label for="forgotEmail">Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope text-muted-icon"></i>
                                <input type="email" id="forgotEmail" placeholder="nama@domain.com" required autocomplete="email">
                            </div>
                        </div>
                        <button type="submit" class="btn-primary-action" style="margin-top: 10px;">Request OTP Code</button>
                        <button type="button" class="btn-neutral-back" onclick="showCard('loginCard')"><i class="fas fa-arrow-left"></i> Back to Login</button>
                    </form>
                </div>

                <!-- CARD 4: RESET PASSWORD -->
                <div class="form-box" id="resetCard" style="display: none;">
                    <div class="form-header">
                        <h2>Reset Password</h2>
                        <p>Enter the 6-digit OTP code and set your new password</p>
                    </div>
                    <form id="resetForm">
                        <input type="hidden" id="resetEmailHidden">
                        <div class="input-group">
                            <label for="resetOTP">OTP Verification Code</label>
                            <div class="input-wrapper">
                                <i class="fas fa-key text-muted-icon"></i>
                                <input type="text" id="resetOTP" placeholder="Enter 6 digits" maxlength="6" pattern="[0-9]{6}" required style="letter-spacing: 6px; text-align: center; font-weight: 700; font-size: 18px;">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="resetPassword">New Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock text-muted-icon"></i>
                                <input type="password" id="resetPassword" placeholder="Minimum 6 characters" minlength="6" required autocomplete="new-password">
                                <button type="button" class="btn-eye" onclick="togglePasswordInput('resetPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="resetConfirmPassword">Confirm New Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-check-double text-muted-icon"></i>
                                <input type="password" id="resetConfirmPassword" placeholder="Repeat new password" minlength="6" required autocomplete="new-password">
                                <button type="button" class="btn-eye" onclick="togglePasswordInput('resetConfirmPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary-action" style="margin-top: 10px;">Update Password</button>
                        <button type="button" class="btn-neutral-back" onclick="showCard('forgotCard')"><i class="fas fa-arrow-left"></i> Resend OTP</button>
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
