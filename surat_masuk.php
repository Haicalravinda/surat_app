<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get surat masuk
$query = "SELECT s.*, 
          u.nama as pengirim_nama, 
          u.jabatan as pengirim_jabatan,
          b.nama_bagian as pengirim_bagian,
          sb.nama_subbag as pengirim_subbag
          FROM surat s
          LEFT JOIN users u ON s.pengirim_id = u.id_user
          LEFT JOIN bagian b ON u.bagian_id = b.id_bagian
          LEFT JOIN subbag sb ON u.subbag_id = sb.id_subbag
          WHERE s.penerima_id = $user_id
          ORDER BY s.tanggal_kirim DESC";
$surat_masuk = mysqli_query($conn, $query);

$page_title = 'Surat Masuk';
include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-inbox me-2"></i>Surat Masuk
                    </h5>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="filterSurat('all')">
                            <i class="fas fa-list me-1"></i>Semua
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="filterSurat('unread')">
                            <i class="fas fa-envelope me-1"></i>Belum Dibaca
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="filterSurat('read')">
                            <i class="fas fa-check-double me-1"></i>Sudah Dibaca
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($surat_masuk) > 0): ?>
                        <div id="suratContainer">
                            <?php while ($surat = mysqli_fetch_assoc($surat_masuk)): ?>
                                <div class="surat-item <?= $surat['status'] ?>" data-status="<?= $surat['status'] ?>">
                                    <div class="surat-header">
                                        <div>
                                            <div class="surat-title"><?= htmlspecialchars($surat['judul']) ?></div>
                                            <div class="surat-meta">
                                                <span>
                                                    <i class="fas fa-user"></i>
                                                    <?= htmlspecialchars($surat['pengirim_nama']) ?>
                                                    (<?= ucfirst($surat['pengirim_jabatan']) ?>)
                                                </span>
                                                <?php if ($surat['pengirim_subbag']): ?>
                                                <span>
                                                    <i class="fas fa-building"></i>
                                                    <?= htmlspecialchars($surat['pengirim_subbag']) ?>
                                                </span>
                                                <?php endif; ?>
                                                <span>
                                                    <i class="fas fa-clock"></i>
                                                    <?= formatTanggal($surat['tanggal_kirim']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($surat['status'] == 'unread'): ?>
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-circle"></i> Baru
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-check-double"></i> Dibaca
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="surat-preview">
                                        <?= substr(htmlspecialchars($surat['isi']), 0, 200) ?>...
                                    </div>
                                    <div class="surat-actions">
                                        <a href="surat_detail.php?id=<?= $surat['id_surat'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i>Lihat Detail
                                        </a>
                                        <button class="btn btn-sm btn-outline-primary" onclick="quickReply(<?= $surat['id_surat'] ?>, '<?= htmlspecialchars($surat['pengirim_nama']) ?>')">
                                            <i class="fas fa-reply me-1"></i>Balas
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak Ada Surat Masuk</h5>
                            <p class="text-muted">Surat yang dikirim ke Anda akan muncul di sini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterSurat(status) {
    const items = document.querySelectorAll('.surat-item');
    items.forEach(item => {
        if (status === 'all' || item.dataset.status === status) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function quickReply(suratId, pengirimNama) {
    if (confirm('Balas surat dari ' + pengirimNama + '?')) {
        window.location.href = 'surat_detail.php?id=' + suratId + '#reply';
    }
}
</script>

<?php include 'includes/footer.php'; ?>