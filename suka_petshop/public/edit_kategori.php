<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') { header("Location: index.php"); exit(); }
require_once '../config/database.php';

$id = $_GET['id'] ?? '';
if (empty($id)) { header("Location: index.php?page=kategori"); exit(); }

try {
    $stmt = $conn->prepare("SELECT * FROM Kategori WHERE id_kategori = ?");
    $stmt->execute([$id]);
    $kategori = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$kategori) { header("Location: index.php?page=kategori&status=error&msg=Data tidak ditemukan!"); exit(); }
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Suka Petshop | Edit Kategori</title>
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
<div class="container"><div class="row justify-content-center"><div class="col-md-4">
    <div class="form-card p-4">
        <h5 class="fw-bold mb-3">Ubah Data Kategori</h5><hr class="opacity-25">
        <form id="formEditKategori">
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">ID Kategori</label>
                <input type="text" name="id_kategori" class="form-control bg-light" value="<?= htmlspecialchars($kategori['id_kategori']) ?>" readonly>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary">Nama Kategori</label>
                <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($kategori['nama_kategori']) ?>" required>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="index.php?page=kategori" class="btn btn-light border rounded-pill px-4">Batal</a>
                <button type="submit" class="btn btn-pink-gradient rounded-pill px-4">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div></div></div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('formEditKategori').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('../process/update_kategori.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.msg, confirmButtonColor: '#ff75c3' }).then(() => { window.location.href = 'index.php?page=kategori'; });
            } else { Swal.fire({ icon: 'error', title: 'Gagal!', text: data.msg, confirmButtonColor: '#ff75c3' }); }
        });
    });
</script>
</body>
</html>