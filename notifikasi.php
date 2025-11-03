<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Mark all as read if requested
if (isset($_GET['mark_all_read'])) {
    $query = "UPDATE notifikasi SET status = 'read' WHERE id_user = $user_id";
    mysqli_query($conn, $query);
    redirect('notifikasi.php');
}

// Get notifications
$query = "SELECT n.*, s.judul, s.tanggal_kirim,
          u_pengirim.nama as pengirim_nama,
          u_pengirim.jabatan as pengirim_jabatan,
          u_penerima.nama as penerima_nama
          FROM notifikasi n
          LEFT JOIN surat s ON n.id_surat = s.id_surat
          LEFT JOIN users u_pengirim ON s.pengirim_id = u_pengirim.id_user
          LEFT JOIN users u_penerima ON s.penerima_id = u_penerima.id_user
          WHERE n.id_user = $user_id
          ORDER BY n.created_at DESC";
$notifications = mysqli_query($conn, $query);

$page_title = 'Notifikasi';
include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-bell me-2"></i>Notifikasi
                    </h5>
                    <?php
                    $unread_count = countUnreadNotif($user_id);
                    if ($unread_count > 0):
                    ?>
                    <a href="?mark_all_read=1" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-check-double me-2"></i>Tandai Semua Dibaca
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (mysqli_num_rows($notifications) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($notif = mysqli_fetch_assoc($notifications)): ?>
                            <a href="surat_detail.php?id=<?= $notif['id_surat'] ?>" 
                               class="list-group-item list-group-item-action <?= $notif['status'] == 'unread' ? 'bg-light' : '' ?>">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <?php if ($notif['status'] == 'unread'): ?>
                                                <span class="badge bg-primary me-2">Baru</span>
                                            <?php endif; ?>
                                            <h6 class="mb-0"><?= htmlspecialchars($notif['judul']) ?></h6>
                                        </div>
                                        <p class="mb-2 text-muted">
                                            <i class="fas fa-user-circle me-1"></i>
                                            <strong><?= htmlspecialchars($notif['pengirim_nama']) ?></strong>
                                            (<?= ucfirst($notif['pengirim_jabatan']) ?>)
                                            mengirim surat ke
                                            <strong><?= htmlspecialchars($notif['penerima_nama']) ?></strong>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= formatTanggal($notif['created_at']) ?>
                                        </small>
                                    </div>
                                    <div class="ms-3">
                                        <i class="fas fa-chevron-right text-muted"></i>
                                    </div>
                                </div>
                            </a>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak Ada Notifikasi</h5>
                            <p class="text-muted">Notifikasi akan muncul ketika ada surat baru yang relevan dengan Anda</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Info Card -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-info-circle me-2"></i>Tentang Notifikasi
                    </h6>
                    <p class="mb-2">Anda akan menerima notifikasi untuk:</p>
                    <ul class="mb-0">
                        <li>Surat yang ditujukan langsung kepada Anda</li>
                        <li>Surat yang melibatkan bagian/subag Anda (jika Anda adalah Kepala Bagian/Subag)</li>
                        <li>Semua surat yang dikirim dari/ke staff (jika Anda adalah Sekretaris)</li>
                        <li>Surat yang melibatkan bawahan Anda dalam hierarki organisasi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>