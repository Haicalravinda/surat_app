<?php
require_once 'config.php';
requireLogin();

$success = '';
$error = '';
$user_id = $_SESSION['user_id'];
$user_info = getUserInfo($user_id);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $penerima_id = (int)$_POST['penerima_id'];
    $judul = clean($_POST['judul']);
    $isi = clean($_POST['isi']);
    
    if (!empty($penerima_id) && !empty($judul) && !empty($isi)) {
        // Get receiver info
        $penerima_info = getUserInfo($penerima_id);
        
        // Insert surat
        $query = "INSERT INTO surat (pengirim_id, penerima_id, judul, isi, tanggal_kirim) 
                  VALUES ($user_id, $penerima_id, '$judul', '$isi', NOW())";
        
        if (mysqli_query($conn, $query)) {
            $surat_id = mysqli_insert_id($conn);
            
            // Determine who should receive notifications based on organizational hierarchy
            $notif_users = [$penerima_id]; // Penerima always gets notified
            
            // Get organizational structure
            $pengirim_subbag = $user_info['subbag_id'];
            $pengirim_bagian = $user_info['bagian_id'];
            $penerima_subbag = $penerima_info['subbag_id'];
            $penerima_bagian = $penerima_info['bagian_id'];
            
            // Rule 1: Staff A (subag A) -> Staff D (subag B)
            // Notify: Kasubag A, Kasubag B, Kabag A, Kabag B, Sekretaris
            if ($pengirim_subbag != $penerima_subbag && $pengirim_bagian != $penerima_bagian) {
                // Get Kasubag pengirim
                if ($pengirim_subbag) {
                    $query = "SELECT id_user FROM users WHERE subbag_id = $pengirim_subbag AND jabatan = 'kasubag'";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $notif_users[] = $row['id_user'];
                    }
                }
                
                // Get Kasubag penerima
                if ($penerima_subbag) {
                    $query = "SELECT id_user FROM users WHERE subbag_id = $penerima_subbag AND jabatan = 'kasubag'";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $notif_users[] = $row['id_user'];
                    }
                }
                
                // Get Kabag pengirim
                if ($pengirim_bagian) {
                    $query = "SELECT id_user FROM users WHERE bagian_id = $pengirim_bagian AND jabatan = 'kabag'";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $notif_users[] = $row['id_user'];
                    }
                }
                
                // Get Kabag penerima
                if ($penerima_bagian) {
                    $query = "SELECT id_user FROM users WHERE bagian_id = $penerima_bagian AND jabatan = 'kabag'";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $notif_users[] = $row['id_user'];
                    }
                }
                
                // Get Sekretaris
                $query = "SELECT id_user FROM users WHERE jabatan = 'sekretaris'";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $notif_users[] = $row['id_user'];
                }
            }
            // Rule 2: Staff dalam subag yang sama
            // Notify: Kasubag saja
            elseif ($pengirim_subbag == $penerima_subbag && $pengirim_subbag != null) {
                $query = "SELECT id_user FROM users WHERE subbag_id = $pengirim_subbag AND jabatan = 'kasubag'";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $notif_users[] = $row['id_user'];
                }
            }
            
            // Rule 3: Kepala -> Staff
            // Notify: Kasubag, Kabag, Sekretaris
            if ($user_info['jabatan'] == 'kepala') {
                // Get Kasubag penerima
                if ($penerima_subbag) {
                    $query = "SELECT id_user FROM users WHERE subbag_id = $penerima_subbag AND jabatan = 'kasubag'";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $notif_users[] = $row['id_user'];
                    }
                }
                
                // Get Kabag penerima
                if ($penerima_bagian) {
                    $query = "SELECT id_user FROM users WHERE bagian_id = $penerima_bagian AND jabatan = 'kabag'";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $notif_users[] = $row['id_user'];
                    }
                }
                
                // Get Sekretaris
                $query = "SELECT id_user FROM users WHERE jabatan = 'sekretaris'";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $notif_users[] = $row['id_user'];
                }
            }
            
            // Remove duplicates and sender
            $notif_users = array_unique($notif_users);
            $notif_users = array_diff($notif_users, [$user_id]);
            
            // Insert notifications
            foreach ($notif_users as $notif_user) {
                $query = "INSERT INTO notifikasi (id_surat, id_user) VALUES ($surat_id, $notif_user)";
                mysqli_query($conn, $query);
            }
            
            $success = 'Surat berhasil dikirim!';
        } else {
            $error = 'Gagal mengirim surat: ' . mysqli_error($conn);
        }
    } else {
        $error = 'Harap isi semua field!';
    }
}

// Get list of users (exclude current user)
$query = "SELECT u.*, b.nama_bagian, s.nama_subbag 
          FROM users u 
          LEFT JOIN bagian b ON u.bagian_id = b.id_bagian
          LEFT JOIN subbag s ON u.subbag_id = s.id_subbag
          WHERE u.id_user != $user_id
          ORDER BY b.nama_bagian, s.nama_subbag, u.nama";
$users = mysqli_query($conn, $query);

$page_title = 'Buat Surat';
include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope-open-text me-2"></i>Buat Surat Baru
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="suratForm">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user me-2"></i>Kepada
                            </label>
                            <select name="penerima_id" class="form-select" required>
                                <option value="">-- Pilih Penerima --</option>
                                <?php 
                                $current_bagian = '';
                                $current_subbag = '';
                                while ($user = mysqli_fetch_assoc($users)): 
                                    // Group by bagian
                                    if ($user['nama_bagian'] != $current_bagian) {
                                        if ($current_bagian != '') echo '</optgroup>';
                                        $current_bagian = $user['nama_bagian'];
                                        echo '<optgroup label="' . ($current_bagian ?: 'Manajemen') . '">';
                                    }
                                    
                                    $label = $user['nama'] . ' (' . ucfirst($user['jabatan']) . ')';
                                    if ($user['nama_subbag']) {
                                        $label .= ' - ' . $user['nama_subbag'];
                                    }
                                ?>
                                    <option value="<?= $user['id_user'] ?>"><?= $label ?></option>
                                <?php endwhile; ?>
                                <?php if ($current_bagian != '') echo '</optgroup>'; ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-heading me-2"></i>Perihal
                            </label>
                            <input type="text" name="judul" class="form-control" 
                                   placeholder="Masukkan perihal surat" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-file-alt me-2"></i>Isi Surat
                            </label>
                            <textarea name="isi" class="form-control" rows="10" 
                                      placeholder="Tulis isi surat..." required></textarea>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Notifikasi akan dikirim secara otomatis ke pihak terkait sesuai hierarki organisasi
                            </small>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Surat
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Info Card -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-info-circle me-2"></i>Aturan Notifikasi
                    </h6>
                    <ul class="mb-0">
                        <li class="mb-2">Surat antar subag berbeda: Notifikasi ke Kasubag, Kabag, dan Sekretaris</li>
                        <li class="mb-2">Surat dalam subag sama: Notifikasi hanya ke Kasubag</li>
                        <li class="mb-2">Surat dari Kepala: Notifikasi ke Kasubag, Kabag, dan Sekretaris penerima</li>
                        <li>Penerima surat selalu mendapat notifikasi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>