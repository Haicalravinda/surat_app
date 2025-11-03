<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$jabatan = $_SESSION['jabatan'];

// Get statistics dengan prepared statements untuk keamanan
$stats = [
    'surat_masuk' => 0,
    'surat_keluar' => 0,
    'surat_unread' => 0,
    'notifikasi_unread' => 0
];

// Surat Masuk
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM surat WHERE penerima_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats['surat_masuk'] = mysqli_fetch_assoc($result)['total'];

// Surat Keluar
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM surat WHERE pengirim_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats['surat_keluar'] = mysqli_fetch_assoc($result)['total'];

// Surat Unread
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM surat WHERE penerima_id = ? AND status = 'unread'");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats['surat_unread'] = mysqli_fetch_assoc($result)['total'];

// Notifikasi Unread
$stats['notifikasi_unread'] = countUnreadNotif($user_id);

// Get recent surat dengan prepared statement
$stmt = mysqli_prepare($conn, "SELECT s.*, 
          u_pengirim.nama as pengirim_nama, 
          u_penerima.nama as penerima_nama,
          u_pengirim.jabatan as pengirim_jabatan,
          u_penerima.jabatan as penerima_jabatan
          FROM surat s
          LEFT JOIN users u_pengirim ON s.pengirim_id = u_pengirim.id_user
          LEFT JOIN users u_penerima ON s.penerima_id = u_penerima.id_user
          WHERE s.penerima_id = ? OR s.pengirim_id = ?
          ORDER BY s.tanggal_kirim DESC
          LIMIT 5");
mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
mysqli_stmt_execute($stmt);
$recent_surat = mysqli_stmt_get_result($stmt);

$page_title = 'Dashboard';
include 'includes/header.php';
?>

<style>
    /* Enhanced Dashboard Styles */
    .dashboard-wrapper {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .content-wrapper {
        padding: 2.5rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Stats Cards Enhancement */
    .stats-row {
        margin-bottom: 2.5rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.8);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--card-color-start), var(--card-color-end));
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    .stat-card.primary {
        --card-color-start: #6366f1;
        --card-color-end: #8b5cf6;
    }

    .stat-card.success {
        --card-color-start: #10b981;
        --card-color-end: #059669;
    }

    .stat-card.warning {
        --card-color-start: #f59e0b;
        --card-color-end: #d97706;
    }

    .stat-card.info {
        --card-color-start: #06b6d4;
        --card-color-end: #0891b2;
    }

    .stat-content {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .stat-icon {
        width: 80px;
        height: 80px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        position: relative;
        flex-shrink: 0;
    }

    .stat-card.primary .stat-icon {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(139, 92, 246, 0.15));
        color: #6366f1;
    }

    .stat-card.success .stat-icon {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
        color: #10b981;
    }

    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.15));
        color: #f59e0b;
    }

    .stat-card.info .stat-icon {
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.15), rgba(8, 145, 178, 0.15));
        color: #06b6d4;
    }

    .stat-icon::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 16px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .stat-card:hover .stat-icon::before {
        opacity: 0.1;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.1; }
        50% { transform: scale(1.1); opacity: 0.2; }
    }

    .stat-details {
        flex: 1;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
        line-height: 1;
        background: linear-gradient(135deg, var(--card-color-start), var(--card-color-end));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-label {
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .stat-trend {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: #10b981;
        font-weight: 500;
    }

    .stat-trend.down {
        color: #ef4444;
    }

    /* Welcome Card Enhancement */
    .welcome-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 2.5rem;
        color: white;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4);
        margin-bottom: 2.5rem;
        position: relative;
        overflow: hidden;
    }

    .welcome-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .welcome-content {
        display: flex;
        align-items: center;
        gap: 2rem;
        position: relative;
        z-index: 1;
    }

    .welcome-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
        border: 4px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }

    .welcome-info h2 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .welcome-meta {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        opacity: 0.95;
    }

    .welcome-badge {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .welcome-actions {
        margin-left: auto;
    }

    .btn-create-letter {
        background: white;
        color: #6366f1;
        padding: 0.875rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        border: none;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }

    .btn-create-letter:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        color: #6366f1;
    }

    /* Recent Surat Card */
    .recent-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .recent-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1.75rem 2rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .recent-header h5 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .recent-header i {
        color: #6366f1;
    }

    .recent-body {
        padding: 1.5rem;
    }

    /* Surat Item Enhancement */
    .surat-item {
        background: white;
        border-radius: 16px;
        padding: 1.75rem;
        margin-bottom: 1.25rem;
        border: 2px solid #f1f5f9;
        transition: all 0.3s ease;
        position: relative;
    }

    .surat-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        border-radius: 16px 0 0 16px;
        transition: all 0.3s ease;
    }

    .surat-item.unread::before {
        background: linear-gradient(180deg, #6366f1, #8b5cf6);
    }

    .surat-item.read::before {
        background: #e2e8f0;
    }

    .surat-item:hover {
        border-color: #6366f1;
        box-shadow: 0 8px 24px rgba(99, 102, 241, 0.15);
        transform: translateX(8px);
    }

    .surat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .surat-title {
        font-weight: 700;
        color: #1e293b;
        font-size: 1.15rem;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    .surat-meta {
        display: flex;
        gap: 2rem;
        color: #64748b;
        font-size: 0.9rem;
        flex-wrap: wrap;
    }

    .surat-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .surat-meta i {
        color: #94a3b8;
    }

    .surat-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .surat-badge.new {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .surat-badge.read {
        background: #f1f5f9;
        color: #64748b;
    }

    .surat-preview {
        color: #64748b;
        margin-bottom: 1.25rem;
        line-height: 1.7;
        font-size: 0.95rem;
    }

    .surat-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-detail {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        padding: 0.625rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }

    .btn-detail:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        color: white;
    }

    .btn-view-all {
        background: white;
        color: #6366f1;
        padding: 0.875rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        border: 2px solid #6366f1;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }

    .btn-view-all:hover {
        background: #6366f1;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-icon {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }

    .empty-icon i {
        font-size: 3rem;
        color: #94a3b8;
    }

    .empty-state h5 {
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #94a3b8;
        font-size: 0.95rem;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .welcome-content {
            flex-direction: column;
            text-align: center;
        }

        .welcome-actions {
            margin-left: 0;
        }

        .welcome-meta {
            justify-content: center;
        }
    }

    @media (max-width: 767px) {
        .content-wrapper {
            padding: 1.5rem;
        }

        .welcome-card {
            padding: 1.5rem;
        }

        .stat-card {
            margin-bottom: 1rem;
        }

        .stat-content {
            gap: 1rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 2rem;
        }

        .surat-meta {
            flex-direction: column;
            gap: 0.5rem;
        }

        .surat-actions {
            flex-direction: column;
        }

        .btn-detail,
        .btn-view-all {
            width: 100%;
            justify-content: center;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeInUp 0.6s ease backwards;
    }

    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }

    .surat-item:nth-child(1) { animation-delay: 0.1s; }
    .surat-item:nth-child(2) { animation-delay: 0.2s; }
    .surat-item:nth-child(3) { animation-delay: 0.3s; }
    .surat-item:nth-child(4) { animation-delay: 0.4s; }
    .surat-item:nth-child(5) { animation-delay: 0.5s; }
</style>

<div class="dashboard-wrapper">
    <div class="content-wrapper">
        <!-- Stats Cards -->
        <div class="row stats-row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card primary fade-in">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?= $stats['surat_masuk'] ?></div>
                            <div class="stat-label">Surat Masuk</div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+12% bulan ini</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card success fade-in">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?= $stats['surat_keluar'] ?></div>
                            <div class="stat-label">Surat Keluar</div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+8% bulan ini</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card warning fade-in">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?= $stats['surat_unread'] ?></div>
                            <div class="stat-label">Belum Dibaca</div>
                            <div class="stat-trend down">
                                <i class="fas fa-arrow-down"></i>
                                <span>-5% dari kemarin</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card info fade-in">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?= $stats['notifikasi_unread'] ?></div>
                            <div class="stat-label">Notifikasi Baru</div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+3 notifikasi baru</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Welcome Card -->
        <div class="welcome-card fade-in">
            <div class="welcome-content">
                <div class="welcome-avatar">
                    <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
                </div>
                <div class="welcome-info">
                    <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?>!</h2>
                    <div class="welcome-meta">
                        <div class="welcome-badge">
                            <i class="fas fa-user-tie me-2"></i>
                            <?= ucfirst(htmlspecialchars($_SESSION['jabatan'])) ?>
                        </div>
                        <div style="opacity: 0.9;">
                            <i class="fas fa-calendar me-2"></i>
                            <?= date('l, d F Y') ?>
                        </div>
                    </div>
                </div>
                <div class="welcome-actions">
                    <a href="surat_buat.php" class="btn-create-letter">
                        <i class="fas fa-plus-circle"></i>
                        <span>Buat Surat Baru</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Recent Surat -->
        <div class="recent-card fade-in">
            <div class="recent-header">
                <h5>
                    <i class="fas fa-history"></i>
                    Surat Terbaru
                </h5>
            </div>
            <div class="recent-body">
                <?php if (mysqli_num_rows($recent_surat) > 0): ?>
                    <?php while ($surat = mysqli_fetch_assoc($recent_surat)): ?>
                        <div class="surat-item <?= $surat['status'] ?> fade-in">
                            <div class="surat-header">
                                <div style="flex: 1;">
                                    <div class="surat-title"><?= htmlspecialchars($surat['judul']) ?></div>
                                    <div class="surat-meta">
                                        <div class="surat-meta-item">
                                            <i class="fas fa-user"></i>
                                            <span>
                                                <?php if ($surat['pengirim_id'] == $user_id): ?>
                                                    Kepada: <strong><?= htmlspecialchars($surat['penerima_nama']) ?></strong>
                                                <?php else: ?>
                                                    Dari: <strong><?= htmlspecialchars($surat['pengirim_nama']) ?></strong>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="surat-meta-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?= formatTanggal($surat['tanggal_kirim']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <?php if ($surat['status'] == 'unread' && $surat['penerima_id'] == $user_id): ?>
                                        <span class="surat-badge new">
                                            <i class="fas fa-star"></i>
                                            Baru
                                        </span>
                                    <?php else: ?>
                                        <span class="surat-badge read">
                                            <i class="fas fa-check-double"></i>
                                            Dibaca
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="surat-preview">
                                <?= substr(htmlspecialchars($surat['isi']), 0, 180) ?>...
                            </div>
                            <div class="surat-actions">
                                <a href="surat_detail.php?id=<?= $surat['id_surat'] ?>" class="btn-detail">
                                    <i class="fas fa-eye"></i>
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <div class="text-center mt-4">
                        <a href="surat_masuk.php" class="btn-view-all">
                            <span>Lihat Semua Surat</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="empty-state fade-in">
                        <div class="empty-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h5>Belum Ada Surat</h5>
                        <p>Surat yang Anda kirim atau terima akan muncul di sini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>