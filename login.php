<?php
require_once 'config.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request!';
    } else {
        $username = clean($_POST['username']);
        $password = $_POST['password'];
        
        if (!empty($username) && !empty($password)) {
            // Gunakan prepared statement untuk mencegah SQL Injection
            $query = "SELECT * FROM users WHERE username = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                
                if (password_verify($password, $user['password'])) {
                    // Regenerate session ID untuk keamanan
                    session_regenerate_id(true);
                    
                    // Set session
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['jabatan'] = $user['jabatan'];
                    $_SESSION['bagian_id'] = $user['bagian_id'];
                    $_SESSION['subbag_id'] = $user['subbag_id'];
                    $_SESSION['last_activity'] = time();
                    
                    redirect('dashboard.php');
                } else {
                    $error = 'Password salah!';
                }
            } else {
                $error = 'Username tidak ditemukan!';
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Harap isi semua field!';
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login - Sistem Surat Menyurat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --accent: #ec4899;
            --success: #10b981;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
            --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.15);
            --shadow-xl: 0 25px 50px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--gradient-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Elements */
        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        body::before {
            width: 400px;
            height: 400px;
            top: -100px;
            right: -100px;
        }

        body::after {
            width: 300px;
            height: 300px;
            bottom: -80px;
            left: -80px;
            animation-delay: -10s;
            animation-direction: reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        /* Login Container */
        .login-wrapper {
            max-width: 1100px;
            width: 100%;
            position: relative;
            z-index: 1;
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        /* Brand Section */
        .brand-section {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.95) 0%, rgba(139, 92, 246, 0.95) 100%);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
            min-height: 600px;
        }

        .brand-section::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 40px 40px;
            animation: moveGrid 30s linear infinite;
            opacity: 0.3;
        }

        @keyframes moveGrid {
            0% { transform: translate(0, 0); }
            100% { transform: translate(40px, 40px); }
        }

        .brand-content {
            position: relative;
            z-index: 1;
            color: white;
        }

        .brand-logo {
            width: 90px;
            height: 90px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            animation: pulse 3s infinite;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); }
            50% { transform: scale(1.05); box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3); }
        }

        .brand-logo i {
            font-size: 42px;
        }

        .brand-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: 16px;
            opacity: 0.95;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .features-list {
            list-style: none;
            padding: 0;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 15px;
            opacity: 0.95;
            animation: fadeInLeft 0.6s ease backwards;
        }

        .feature-item:nth-child(1) { animation-delay: 0.2s; }
        .feature-item:nth-child(2) { animation-delay: 0.3s; }
        .feature-item:nth-child(3) { animation-delay: 0.4s; }
        .feature-item:nth-child(4) { animation-delay: 0.5s; }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 0.95;
                transform: translateX(0);
            }
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .feature-item:hover .feature-icon {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        /* Form Section */
        .form-section {
            padding: 60px 50px;
            background: white;
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .form-subtitle {
            color: #64748b;
            font-size: 15px;
        }

        /* Alert Styles */
        .alert-custom {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 1px solid #fca5a5;
            border-radius: 12px;
            padding: 16px 20px;
            color: #dc2626;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.5s, fadeIn 0.3s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-custom i {
            font-size: 20px;
            flex-shrink: 0;
        }

        .btn-close-custom {
            margin-left: auto;
            background: none;
            border: none;
            color: #dc2626;
            cursor: pointer;
            padding: 5px;
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .btn-close-custom:hover {
            opacity: 1;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
            font-size: 14px;
            display: block;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            z-index: 2;
            transition: color 0.3s;
        }

        .form-control-custom {
            width: 100%;
            padding: 15px 20px 15px 52px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .form-control-custom:focus + .input-icon {
            color: var(--primary);
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 18px;
            padding: 8px;
            z-index: 2;
            transition: all 0.3s;
            border-radius: 6px;
        }

        .password-toggle:hover {
            color: var(--primary);
            background: rgba(99, 102, 241, 0.1);
        }

        /* Checkbox & Remember */
        .remember-section {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .custom-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }

        .custom-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .custom-checkbox label {
            font-size: 14px;
            color: #64748b;
            cursor: pointer;
            margin: 0;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Demo Info */
        .demo-info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .demo-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 12px;
            color: #1e40af;
            font-size: 14px;
            font-weight: 600;
        }

        .demo-credentials {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .credential {
            font-size: 13px;
            color: #475569;
        }

        .credential strong {
            color: var(--primary);
            font-weight: 600;
            padding: 2px 8px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 4px;
        }

        /* Loading Spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-submit.loading .spinner {
            display: block;
        }

        .btn-submit.loading .btn-text {
            display: none;
        }

        /* Responsive Design */
        @media (max-width: 991px) {
            .brand-section {
                padding: 40px 30px;
                text-align: center;
                min-height: auto;
            }

            .brand-logo {
                margin-left: auto;
                margin-right: auto;
            }

            .feature-item {
                justify-content: center;
            }

            .brand-title {
                font-size: 28px;
            }
        }

        @media (max-width: 767px) {
            body {
                padding: 15px;
            }

            .form-section {
                padding: 40px 25px;
            }

            .form-title {
                font-size: 24px;
            }

            .brand-section {
                display: none;
            }

            .demo-credentials {
                flex-direction: column;
                gap: 10px;
            }

            .form-control-custom {
                padding: 14px 18px 14px 48px;
            }

            .btn-submit {
                padding: 15px;
            }
        }

        @media (max-width: 380px) {
            .form-section {
                padding: 30px 20px;
            }

            .alert-custom {
                padding: 14px 16px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="row g-0">
                <!-- Left Side - Branding -->
                <div class="col-lg-5 brand-section">
                    <div class="brand-content">
                        <div class="brand-logo">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <h1 class="brand-title">Sistem Surat<br>Menyurat</h1>
                        <p class="brand-subtitle">Platform manajemen surat digital yang modern, aman, dan efisien untuk organisasi Anda</p>
                        
                        <ul class="features-list">
                            <li class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <span>Notifikasi Real-time</span>
                            </li>
                            <li class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <span>Keamanan Terjamin</span>
                            </li>
                            <li class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <span>Tracking & Monitoring</span>
                            </li>
                            <li class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-users-cog"></i>
                                </div>
                                <span>Multi-level Approval</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Right Side - Login Form -->
                <div class="col-lg-7 form-section">
                    <div class="form-header">
                        <h2 class="form-title">Selamat Datang Kembali</h2>
                        <p class="form-subtitle">Silakan masuk ke akun Anda untuk melanjutkan</p>
                    </div>
                    
                    <?php if ($error): ?>
                    <div class="alert-custom" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                        <button type="button" class="btn-close-custom" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="loginForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="form-group">
                            <label class="form-label" for="username">Username</label>
                            <div class="input-wrapper">
                                <input type="text" 
                                       class="form-control-custom" 
                                       id="username"
                                       name="username" 
                                       placeholder="Masukkan username Anda" 
                                       required 
                                       autofocus
                                       autocomplete="username">
                                <i class="fas fa-user input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-wrapper">
                                <input type="password" 
                                       class="form-control-custom" 
                                       id="password"
                                       name="password" 
                                       placeholder="Masukkan password Anda" 
                                       required
                                       autocomplete="current-password">
                                <i class="fas fa-lock input-icon"></i>
                                <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="remember-section">
                            <div class="custom-checkbox">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">Ingat saya</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-submit" id="submitBtn">
                            <div class="spinner"></div>
                            <span class="btn-text">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Masuk ke Dashboard</span>
                            </span>
                        </button>
                        
                       
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            
            passwordInput.type = type;
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
        
        // Form submission with loading state
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        
        loginForm.addEventListener('submit', function(e) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });
        
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert-custom');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
</body>
</html>