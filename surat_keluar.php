<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get surat keluar
$query = "SELECT s.*, 
          u.nama as penerima_nama, 
          u.jabatan as penerima_jabatan,
          b.nama_bagian as penerima_bagian,
          sb.nama_subbag as penerima_subbag
          FROM surat s
          LEFT JOIN users u ON s.penerima_id = u.id_user
          LEFT JOIN bagian b ON u.bagian_id = b.id_bagian
          LEFT JOIN subbag sb ON u.subbag_id = sb.id_subbag
          WHERE s.pengirim_id = $user_id
          ORDER BY s.tanggal_kirim DESC";
$surat_keluar = mysqli_query($conn, $query);

$page_title = 'Surat Keluar';
include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-paper-plane me-2"></i>Surat Keluar
                    </h5>
                    <a href="surat_buat.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Buat Surat Baru
                    </a>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($surat_keluar) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="30%">Perihal</th>
                                        <th width="20%">Kepada</th>
                                        <th width="15%">Tanggal Kirim</th>
                                        <th width="15%">Status</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while ($surat = mysqli_fetch_assoc($surat_keluar)): 
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($surat['judul']) ?></strong>
                                            <div class="small text-muted">
                                                <?= substr(htmlspecialchars($surat['isi']), 0, 80) ?>...
                                            </div>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($surat['penerima_nama']) ?>
                                            <div class="small text-muted">
                                                <?= ucfirst($surat['penerima_jabatan']) ?>
                                                <?php if ($surat['penerima_subbag']): ?>
                                                    - <?= htmlspecialchars($surat['penerima_subbag']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small><?= formatTanggal($surat['tanggal_kirim']) ?></small>
                                        </td>
                                        <td>
                                            <?php if ($surat['status'] == 'read'): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-double"></i> Dibaca
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock"></i> Belum Dibaca
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="surat_detail.php?id=<?= $surat['id_surat'] ?>" 
                                               class="btn btn-sm btn-primary" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-paper-plane fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum Ada Surat Keluar</h5>
                            <p class="text-muted mb-3">Mulai kirim surat dengan menekan tombol di bawah</p>
                            <a href="surat_buat.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Buat Surat Baru
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>