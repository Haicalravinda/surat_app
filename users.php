<?php
require_once 'config.php';
requireLogin();


if ($_SESSION['jabatan'] != 'kepala') {
    redirect('dashboard.php');
}

$success = '';
$error = '';


if (isset($_POST['add_user'])) {
    $nama = clean($_POST['nama']);
    $username = clean($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $jabatan = clean($_POST['jabatan']);
    $bagian_id = !empty($_POST['bagian_id']) ? (int)$_POST['bagian_id'] : 'NULL';
    $subbag_id = !empty($_POST['subbag_id']) ? (int)$_POST['subbag_id'] : 'NULL';
    
    
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = 'Username sudah digunakan!';
    } else {
        $query = "INSERT INTO users (nama, username, password, jabatan, bagian_id, subbag_id) 
                  VALUES ('$nama', '$username', '$password', '$jabatan', $bagian_id, $subbag_id)";
        if (mysqli_query($conn, $query)) {
            $success = 'User berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan user!';
        }
    }
}


if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id != $_SESSION['user_id']) {
        $query = "DELETE FROM users WHERE id_user = $id";
        if (mysqli_query($conn, $query)) {
            $success = 'User berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus user!';
        }
    } else {
        $error = 'Anda tidak dapat menghapus akun sendiri!';
    }
}


$query = "SELECT u.*, b.nama_bagian, s.nama_subbag 
          FROM users u 
          LEFT JOIN bagian b ON u.bagian_id = b.id_bagian
          LEFT JOIN subbag s ON u.subbag_id = s.id_subbag
          ORDER BY u.created_at DESC";
$users = mysqli_query($conn, $query);


$bagian_list = mysqli_query($conn, "SELECT * FROM bagian ORDER BY nama_bagian");
$subbag_list = mysqli_query($conn, "SELECT s.*, b.nama_bagian FROM subbag s LEFT JOIN bagian b ON s.id_bagian = b.id_bagian ORDER BY b.nama_bagian, s.nama_subbag");

$page_title = 'Kelola User';
include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="row mb-3">
        <div class="col-12">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-2"></i>Tambah User Baru
            </button>
        </div>
    </div>
    
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
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>Daftar User
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Jabatan</th>
                                    <th>Bagian</th>
                                    <th>Subag</th>
                                    <th>Terdaftar</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($user = mysqli_fetch_assoc($users)): 
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($user['nama']) ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= ucfirst($user['jabatan']) ?></span>
                                    </td>
                                    <td><?= $user['nama_bagian'] ?: '-' ?></td>
                                    <td><?= $user['nama_subbag'] ?: '-' ?></td>
                                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                    <td class="text-center">
                                        <?php if ($user['id_user'] != $_SESSION['user_id']): ?>
                                        <a href="?delete=<?= $user['id_user'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirmDelete('Hapus user <?= htmlspecialchars($user['nama']) ?>?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Anda</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Tambah User Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <select name="jabatan" class="form-select" id="jabatanSelect" required>
                            <option value="">-- Pilih Jabatan --</option>
                            <option value="kepala">Kepala</option>
                            <option value="sekretaris">Sekretaris</option>
                            <option value="kabag">Kepala Bagian</option>
                            <option value="kasubag">Kepala Subag</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="mb-3" id="bagianField">
                        <label class="form-label">Bagian</label>
                        <select name="bagian_id" class="form-select" id="bagianSelect">
                            <option value="">-- Pilih Bagian --</option>
                            <?php 
                            mysqli_data_seek($bagian_list, 0);
                            while ($bagian = mysqli_fetch_assoc($bagian_list)): 
                            ?>
                            <option value="<?= $bagian['id_bagian'] ?>"><?= $bagian['nama_bagian'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="subbagField">
                        <label class="form-label">Subag</label>
                        <select name="subbag_id" class="form-select" id="subbagSelect">
                            <option value="">-- Pilih Subag --</option>
                            <?php 
                            mysqli_data_seek($subbag_list, 0);
                            while ($subbag = mysqli_fetch_assoc($subbag_list)): 
                            ?>
                            <option value="<?= $subbag['id_subbag'] ?>" data-bagian="<?= $subbag['id_bagian'] ?>">
                                <?= $subbag['nama_subbag'] ?> (<?= $subbag['nama_bagian'] ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_user" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle jabatan selection to show/hide bagian and subbag fields
document.getElementById('jabatanSelect').addEventListener('change', function() {
    const jabatan = this.value;
    const bagianField = document.getElementById('bagianField');
    const subbagField = document.getElementById('subbagField');
    
    if (jabatan === 'kepala' || jabatan === 'sekretaris') {
        bagianField.style.display = 'none';
        subbagField.style.display = 'none';
        document.getElementById('bagianSelect').value = '';
        document.getElementById('subbagSelect').value = '';
    } else if (jabatan === 'kabag') {
        bagianField.style.display = 'block';
        subbagField.style.display = 'none';
        document.getElementById('subbagSelect').value = '';
    } else if (jabatan === 'kasubag' || jabatan === 'staff') {
        bagianField.style.display = 'block';
        subbagField.style.display = 'block';
    }
});

// Filter subbag based on selected bagian
document.getElementById('bagianSelect').addEventListener('change', function() {
    const selectedBagian = this.value;
    const subbagOptions = document.querySelectorAll('#subbagSelect option');
    
    subbagOptions.forEach(option => {
        if (option.value === '') {
            option.style.display = 'block';
        } else if (option.dataset.bagian === selectedBagian) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });
    
    document.getElementById('subbagSelect').value = '';
});


document.getElementById('bagianField').style.display = 'none';
document.getElementById('subbagField').style.display = 'none';
</script>

<?php include 'includes/footer.php'; ?>