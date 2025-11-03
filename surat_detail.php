<?php
require_once 'config.php';
requireLogin();

$surat_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

if (!$surat_id) {
    redirect('dashboard.php');
}

// Get surat detail
$query = "SELECT s.*, 
          u_pengirim.nama as pengirim_nama, 
          u_pengirim.jabatan as pengirim_jabatan,
          u_pengirim.bagian_id as pengirim_bagian_id,
          u_pengirim.subbag_id as pengirim_subbag_id,
          u_penerima.nama as penerima_nama,
          u_penerima.jabatan as penerima_jabatan,
          b_pengirim.nama_bagian as pengirim_bagian,
          sb_pengirim.nama_subbag as pengirim_subbag,
          b_penerima.nama_bagian as penerima_bagian,
          sb_penerima.nama_subbag as penerima_subbag
          FROM surat s
          LEFT JOIN users u_pengirim ON s.pengirim_id = u_pengirim.id_user
          LEFT JOIN users u_penerima ON s.penerima_id = u_penerima.id_user
          LEFT JOIN bagian b_pengirim ON u_pengirim.bagian_id = b_pengirim.id_bagian
          LEFT JOIN subbag sb_pengirim ON u_pengirim.subbag_id = sb_pengirim.id_subbag
          LEFT JOIN bagian b_penerima ON u_penerima.bagian_id = b_penerima.id_bagian
          LEFT JOIN subbag sb_penerima ON u_penerima.subbag_id = sb_penerima.id_subbag
          WHERE s.id_surat = $surat_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    redirect('dashboard.php');
}

$surat = mysqli_fetch_assoc($result);

// Check if user has access to this letter
$has_access = ($surat['pengirim_id'] == $user_id || $surat['penerima_id'] == $user_id);

// Check if user has notification for this letter (means they should have access)
if (!$has_access) {
    $query = "SELECT * FROM notifikasi WHERE id_surat = $surat_id AND id_user = $user_id";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $has_access = true;
    }
}

if (!$has_access) {
    redirect('dashboard.php');
}

// Mark as read if user is the receiver
if ($surat['penerima_id'] == $user_id && $surat['status'] == 'unread') {
    $query = "UPDATE surat SET status = 'read' WHERE id_surat = $surat_id";
    mysqli_query($conn, $query);
    $surat['status'] = 'read';
}

// Mark notification as read
$query = "UPDATE notifikasi SET status = 'read' WHERE id_surat = $surat_id AND id_user = $user_id";
mysqli_query($conn, $query);

// Get read status information
$query = "SELECT COUNT(*) as total_notif, 
          SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count
          FROM notifikasi WHERE id_surat = $surat_id";
$read_info = mysqli_fetch_assoc(mysqli_query($conn, $query));

$page_title = 'Detail Surat';
include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Surat Detail Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-envelope-open-text me-2"></i>Detail Surat
                        </h5>
                        <div>
                            <?php if ($surat['status'] == 'read'): ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-check-double"></i> Dibaca
                                </span>
                            <?php else: ?>
                                <span class="badge bg-primary">
                                    <i class="fas fa-circle"></i> Baru
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Sender & Receiver Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="fw-bold mb-2">
                                    <i class="fas fa-user-circle me-2"></i>Pengirim
                                </h6>
                                <div class="mb-1"><?= htmlspecialchars($surat['pengirim_nama']) ?></div>
                                <small class="text-muted">
                                    <?= ucfirst($surat['pengirim_jabatan']) ?>
                                    <?php if ($surat['pengirim_subbag']): ?>
                                        - <?= htmlspecialchars($surat['pengirim_subbag']) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="fw-bold mb-2">
                                    <i class="fas fa-user-check me-2"></i>Penerima
                                </h6>
                                <div class="mb-1"><?= htmlspecialchars($surat['penerima_nama']) ?></div>
                                <small class="text-muted">
                                    <?= ucfirst($surat['penerima_jabatan']) ?>
                                    <?php if ($surat['penerima_subbag']): ?>
                                        - <?= htmlspecialchars($surat['penerima_subbag']) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Date -->
                    <div class="mb-4">
                        <small class="text-muted">
                            <i class="fas fa-clock me-2"></i><?= formatTanggal($surat['tanggal_kirim']) ?>
                        </small>
                    </div>
                    
                    <!-- Subject -->
                    <div class="mb-4">
                        <h4 class="fw-bold"><?= htmlspecialchars($surat['judul']) ?></h4>
                    </div>
                    
                    <!-- Content -->
                    <div class="border-top pt-4">
                        <div style="white-space: pre-wrap; line-height: 1.8;">
<?= htmlspecialchars($surat['isi']) ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex gap-2">
                        <a href="<?= $surat['penerima_id'] == $user_id ? 'surat_masuk.php' : 'surat_keluar.php' ?>" 
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        
                        <?php if ($surat['penerima_id'] == $user_id): ?>
                        <a href="surat_buat.php?reply=<?= $surat['id_surat'] ?>" class="btn btn-primary">
                            <i class="fas fa-reply me-2"></i>Balas Surat
                        </a>
                        <?php endif; ?>
                        
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Cetak
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Read Status Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-eye me-2"></i>Status Pembacaan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Penerima Notifikasi:</span>
                            <strong><?= $read_info['total_notif'] + 1 ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Sudah Dibaca:</span>
                            <strong class="text-success"><?= $read_info['read_count'] + ($surat['status'] == 'read' ? 1 : 0) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Belum Dibaca:</span>
                            <strong class="text-warning"><?= ($read_info['total_notif'] - $read_info['read_count']) + ($surat['status'] == 'unread' ? 1 : 0) ?></strong>
                        </div>
                    </div>
                    
                    <?php
                    // Get detailed read status
                    $query = "SELECT u.nama, u.jabatan, n.status, n.created_at,
                              CASE WHEN n.status = 'read' THEN 1 ELSE 0 END as is_read
                              FROM notifikasi n
                              LEFT JOIN users u ON n.id_user = u.id_user
                              WHERE n.id_surat = $surat_id
                              ORDER BY is_read DESC, u.nama";
                    $notif_users = mysqli_query($conn, $query);
                    ?>
                    
                    <?php if (mysqli_num_rows($notif_users) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Penerima</th>
                                    <th>Jabatan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Main receiver -->
                                <tr>
                                    <td><?= htmlspecialchars($surat['penerima_nama']) ?></td>
                                    <td><?= ucfirst($surat['penerima_jabatan']) ?></td>
                                    <td>
                                        <?php if ($surat['status'] == 'read'): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Dibaca
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> Belum Dibaca
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                
                                <!-- Notification receivers -->
                                <?php while ($notif = mysqli_fetch_assoc($notif_users)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($notif['nama']) ?></td>
                                    <td><?= ucfirst($notif['jabatan']) ?></td>
                                    <td>
                                        <?php if ($notif['status'] == 'read'): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Dibaca
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> Belum Dibaca
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .topbar, .card-footer, .btn, .badge {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>