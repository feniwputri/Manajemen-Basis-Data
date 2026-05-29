<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') { header("Location: index.php"); exit(); }
require_once '../config/database.php';

$id = $_GET['id'] ?? '';
if (empty($id)) { header("Location: index.php?page=kasir"); exit(); }

try {
    $stmt = $conn->prepare("SELECT * FROM Kasir WHERE id_kasir = ?");
    $stmt->execute([$id]);
    $kasir = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$kasir) { header("Location: index.php?page=kasir&status=error&msg=Petugas tidak ditemukan!"); exit(); }
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Suka Petshop | Edit Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { background: linear-gradient(135deg, #fff1f2 0%, #e0e7ff 100%); font-family: 'Plus Jakarta Sans', sans-serif; height: 100vh; display: flex; align-items: center; }
        .form-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(255, 117, 195, 0.1); }
        .btn-pink-gradient { background: linear-gradient(135deg, #ff75c3 0%, #b18cf0 100%); color: white; border: none; font-weight: 600; transition: 0.2s; }
    </style>
</head>
<body>
<div class="container"><div class="row justify-content-center"><div class="col-md-5">
    <div class="form-card p-4">
        <h5 class="fw-bold mb-3">Ubah Data Kasir</h5><hr class="opacity-25">
        <form id="formEditKasir">
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">ID Kasir</label>
                <input type="text" name="id_kasir" class="form-control bg-light" value="<?= htmlspecialchars($kasir['id_kasir']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                <input type="text" name="nama_kasir" class="form-control" value="<?= htmlspecialchars($kasir['nama_kasir']) ?>" required>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Shift Kerja</label>
                    <select name="shift_kerja" class="form-select" required>
                        <option value="Pagi" <?= $kasir['shift_kerja'] == 'Pagi' ? 'selected' : '' ?>>Pagi</option>
                        <option value="Siang" <?= $kasir['shift_kerja'] == 'Siang' ? 'selected' : '' ?>>Siang</option>
                        <option value="Malam" <?= $kasir['shift_kerja'] == 'Malam' ? 'selected' : '' ?>>Malam</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">No. HP</label>
                    <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($kasir['no_hp'] ?? '') ?>" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary">Alamat</label>
                <textarea name="alamat" class="form-control" rows="2" required><?= htmlspecialchars($kasir['alamat']) ?></textarea>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="index.php?page=kasir" class="btn btn-light border rounded-pill px-4">Batal</a>
                <button type="submit" class="btn btn-pink-gradient rounded-pill px-4">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div></div></div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('formEditKasir').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('../process/update_kasir.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.msg, confirmButtonColor: '#ff75c3' }).then(() => { window.location.href = 'index.php?page=kasir'; });
            } else { Swal.fire({ icon: 'error', title: 'Gagal!', text: data.msg, confirmButtonColor: '#ff75c3' }); }
        });
    });
</script>
</body>
</html>