<?php
if (!defined('DB_HOST')) {
    require_once '../config.php';
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$notif_count = countUnreadNotif($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Sistem Surat Menyurat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ===== ENHANCED HEADER STYLES ===== */
        
        /* Sidebar Header Enhancement */
        .sidebar-header-enhanced {
            padding: 2rem 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
            border-bottom: none;
        }

        .header-decoration {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .decoration-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        .circle-1 {
            width: 150px;
            height: 150px;
            top: -50px;
            right: -30px;
            animation-delay: 0s;
        }

        .circle-2 {
            width: 100px;
            height: 100px;
            bottom: -30px;
            left: -20px;
            animation-delay: 7s;
        }

        .circle-3 {
            width: 80px;
            height: 80px;
            top: 50%;
            left: 50%;
            animation-delay: 14s;
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) scale(1);
                opacity: 0.3;
            }
            33% {
                transform: translate(30px, -30px) scale(1.1);
                opacity: 0.5;
            }
            66% {
                transform: translate(-20px, 20px) scale(0.9);
                opacity: 0.4;
            }
        }

        .brand-container {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .brand-icon-wrapper {
            position: relative;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-icon-bg {
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            animation: pulse-glow 3s infinite;
        }

        @keyframes pulse-glow {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 12px 32px rgba(255, 255, 255, 0.3);
            }
        }

        .brand-icon {
            position: relative;
            color: white;
            font-size: 1.75rem;
            z-index: 1;
            animation: bounce-icon 2s infinite;
        }

        @keyframes bounce-icon {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        .brand-text {
            flex: 1;
        }

        .brand-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
            color: white;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .brand-subtitle {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 0;
            margin-top: 2px;
        }

        /* Menu Item Enhancement */
        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 0;
            position: relative;
            margin: 0.25rem 0;
        }

        .menu-icon-wrapper {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: transparent;
            transition: all 0.3s ease;
            margin-right: 0.75rem;
            position: relative;
        }

        .menu-icon-wrapper::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 10px;
            background: var(--primary);
            opacity: 0;
            transition: all 0.3s ease;
            transform: scale(0.8);
        }

        .menu-item:hover .menu-icon-wrapper::before,
        .menu-item.active .menu-icon-wrapper::before {
            opacity: 0.1;
            transform: scale(1);
        }

        .menu-icon-wrapper i {
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 70%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 0 4px 4px 0;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .menu-item:hover::before {
            width: 4px;
        }

        .menu-item.active::before {
            width: 4px;
        }

        .menu-item:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.08) 0%, transparent 100%);
            color: var(--primary);
        }

        .menu-item.active {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.12) 0%, transparent 100%);
            color: var(--primary);
            font-weight: 600;
        }

        .pulse-badge {
            animation: pulse-badge 2s infinite;
        }

        @keyframes pulse-badge {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        /* Menu Divider */
        .menu-divider {
            margin: 1.5rem 1.5rem 1rem;
            padding: 0.5rem 0;
            border-top: 2px solid #f0f2f5;
            position: relative;
        }

        .divider-text {
            font-size: 0.7rem;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            background: white;
            padding-right: 1rem;
            position: relative;
            display: inline-block;
        }

        /* User Profile Enhancement */
        .user-profile-enhanced {
            padding: 1.5rem;
            border-top: 1px solid #f0f2f5;
            background: linear-gradient(135deg, #fafbfc 0%, #f8f9fa 100%);
            margin-top: auto;
        }

        .user-profile-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .user-profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .user-profile-card:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .user-profile-card:hover::before {
            opacity: 1;
        }

        .user-avatar-enhanced {
            position: relative;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-bg {
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 13px;
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.35);
            animation: rotate-gradient 6s linear infinite;
        }

        @keyframes rotate-gradient {
            0% {
                filter: hue-rotate(0deg);
            }
            100% {
                filter: hue-rotate(360deg);
            }
        }

        .avatar-text {
            position: relative;
            z-index: 1;
            color: white;
            font-weight: 700;
            font-size: 1.3rem;
        }

        .status-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 13px;
            height: 13px;
            background: #10b981;
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
            animation: pulse-status 2s infinite;
        }

        @keyframes pulse-status {
            0%, 100% {
                box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
            }
            50% {
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.4);
            }
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 0.25rem;
        }

        .user-role {
            font-size: 0.8rem;
            color: #6b7280;
            display: flex;
            align-items: center;
        }

        .btn-logout-enhanced {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, #ef476f 0%, #dc2626 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(239, 71, 111, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-logout-enhanced::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-logout-enhanced:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-logout-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 71, 111, 0.45);
            color: white;
        }

        .btn-logout-enhanced span,
        .btn-logout-enhanced i {
            position: relative;
            z-index: 1;
        }

        /* Topbar Enhancement */
        .topbar-enhanced {
            background: white;
            padding: 1.25rem 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
            border-bottom: 1px solid #f0f2f5;
            background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .btn-sidebar-toggle {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #e9ecef;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #6b7280;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .btn-sidebar-toggle:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .topbar-title-section {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            line-height: 1.2;
        }

        .page-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #9ca3af;
        }

        .page-breadcrumb i {
            font-size: 0.75rem;
        }

        .separator {
            color: #d1d5db;
        }

        .topbar-right {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .topbar-search {
            position: relative;
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 0 1rem;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .topbar-search:focus-within {
            background: white;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .topbar-search i {
            color: #9ca3af;
            font-size: 0.9rem;
            margin-right: 0.75rem;
            transition: color 0.3s ease;
        }

        .topbar-search:focus-within i {
            color: #667eea;
        }

        .topbar-search input {
            border: none;
            background: transparent;
            outline: none;
            padding: 0.75rem 0;
            width: 250px;
            font-size: 0.9rem;
            color: var(--dark);
        }

        .topbar-search input::placeholder {
            color: #9ca3af;
        }

        .notification-btn-enhanced {
            position: relative;
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #e9ecef;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #6b7280;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .notification-btn-enhanced:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: scale(1.05) rotate(10deg);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .notification-btn-enhanced i {
            font-size: 1.1rem;
            animation: ring 3s infinite;
        }

        @keyframes ring {
            0%, 100% {
                transform: rotate(0deg);
            }
            10%, 30% {
                transform: rotate(-10deg);
            }
            20%, 40% {
                transform: rotate(10deg);
            }
        }

        .notification-badge-enhanced {
            position: absolute;
            top: -6px;
            right: -6px;
            background: linear-gradient(135deg, #ef476f 0%, #dc2626 100%);
            color: white;
            border-radius: 50%;
            min-width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
            border: 3px solid white;
            box-shadow: 0 3px 10px rgba(239, 71, 111, 0.5);
            animation: bounce-badge 2s infinite;
        }

        @keyframes bounce-badge {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.15);
            }
        }

        .user-menu {
            margin-left: 0.5rem;
        }

        .user-avatar-small {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.35);
        }

        .user-avatar-small:hover {
            transform: translateY(-3px) rotate(5deg);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.45);
        }

        /* Responsive */
        @media (max-width: 991px) {
            .topbar-search {
                display: none;
            }
        }

        @media (max-width: 767px) {
            .topbar-enhanced {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .page-breadcrumb {
                display: none;
            }

            .btn-sidebar-toggle {
                width: 40px;
                height: 40px;
            }

            .notification-btn-enhanced,
            .user-avatar-small {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- Enhanced Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header-enhanced">
            <div class="header-decoration">
                <div class="decoration-circle circle-1"></div>
                <div class="decoration-circle circle-2"></div>
                <div class="decoration-circle circle-3"></div>
            </div>
            <div class="brand-container">
                <div class="brand-icon-wrapper">
                    <div class="brand-icon-bg"></div>
                    <i class="fas fa-envelope-open-text brand-icon"></i>
                </div>
                <div class="brand-text">
                    <h4 class="brand-title">Surat App</h4>
                    <p class="brand-subtitle">Mail Management System</p>
                </div>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item <?= $current_page == 'dashboard' ? 'active' : '' ?>">
                <div class="menu-icon-wrapper">
                    <i class="fas fa-home"></i>
                </div>
                <span>Dashboard</span>
            </a>
            
            <a href="surat_buat.php" class="menu-item <?= $current_page == 'surat_buat' ? 'active' : '' ?>">
                <div class="menu-icon-wrapper">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <span>Buat Surat</span>
            </a>
            
            <a href="surat_masuk.php" class="menu-item <?= $current_page == 'surat_masuk' ? 'active' : '' ?>">
                <div class="menu-icon-wrapper">
                    <i class="fas fa-inbox"></i>
                </div>
                <span>Surat Masuk</span>
                <?php
                $query = "SELECT COUNT(*) as total FROM surat WHERE penerima_id = {$_SESSION['user_id']} AND status = 'unread'";
                $result = mysqli_query($conn, $query);
                $unread = mysqli_fetch_assoc($result)['total'];
                if ($unread > 0):
                ?>
                    <span class="badge bg-danger pulse-badge"><?= $unread ?></span>
                <?php endif; ?>
            </a>
            
            <a href="surat_keluar.php" class="menu-item <?= $current_page == 'surat_keluar' ? 'active' : '' ?>">
                <div class="menu-icon-wrapper">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <span>Surat Keluar</span>
            </a>
            
            <a href="notifikasi.php" class="menu-item <?= $current_page == 'notifikasi' ? 'active' : '' ?>">
                <div class="menu-icon-wrapper">
                    <i class="fas fa-bell"></i>
                </div>
                <span>Notifikasi</span>
                <?php if ($notif_count > 0): ?>
                    <span class="badge bg-danger pulse-badge"><?= $notif_count ?></span>
                <?php endif; ?>
            </a>
            
            <?php if (in_array($_SESSION['jabatan'], ['kepala', 'sekretaris', 'kabag'])): ?>
            <div class="menu-divider">
                <span class="divider-text">MANAJEMEN</span>
            </div>
            
            <?php if ($_SESSION['jabatan'] == 'kepala'): ?>
            <a href="users.php" class="menu-item <?= $current_page == 'users' ? 'active' : '' ?>">
                <div class="menu-icon-wrapper">
                    <i class="fas fa-users"></i>
                </div>
                <span>Kelola User</span>
            </a>
            <?php endif; ?>
            
           
            <?php endif; ?>
        </div>
        
        <div class="user-profile-enhanced">
            <div class="user-profile-card">
                <div class="user-avatar-enhanced">
                    <div class="avatar-bg"></div>
                    <span class="avatar-text"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></span>
                    <div class="status-indicator"></div>
                </div>
                <div class="user-info">
                    <div class="user-name"><?= $_SESSION['nama'] ?></div>
                    <div class="user-role">
                        <i class="fas fa-shield-alt me-1"></i>
                        <?= ucfirst($_SESSION['jabatan']) ?>
                    </div>
                </div>
            </div>
            <a href="logout.php" class="btn-logout-enhanced">
                <i class="fas fa-sign-out-alt me-2"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Enhanced Topbar -->
        <div class="topbar-enhanced">
            <div class="topbar-left">
                <button class="btn-sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-title-section">
                    <h2 class="page-title"><?= isset($page_title) ? $page_title : 'Dashboard' ?></h2>
                    <div class="page-breadcrumb">
                        <i class="fas fa-home"></i>
                        <span class="separator">/</span>
                        <span><?= isset($page_title) ? $page_title : 'Dashboard' ?></span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Cari surat...">
                </div>
                <a href="notifikasi.php" class="notification-btn-enhanced">
                    <i class="fas fa-bell"></i>
                    <?php if ($notif_count > 0): ?>
                        <span class="notification-badge-enhanced"><?= $notif_count ?></span>
                    <?php endif; ?>
                </a>
                <div class="user-menu">
                    <div class="user-avatar-small">
                        <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
                    </div>
                </div>
            </div>
        </div>