<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') { header("Location: index.php"); exit(); }
require_once '../config/database.php';

try {
    $last_kat = $conn->query("SELECT id_kategori FROM Kategori ORDER BY id_kategori DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $next_num = $last_kat ? intval(substr($last_kat['id_kategori'], 4)) + 1 : 1;
    $auto_id = "KAT-" . str_pad($next_num, 3, "0", STR_PAD_LEFT);
} catch (PDOException $e) { 
    die("Error Database Kategori: " . $e->getMessage()); // Kalau error, pesannya akan muncul, bukan layar putih
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Suka Petshop | Tambah Kategori</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body { background: linear-gradient(135deg, #fff1f2 0%, #e0e7ff 100%); font-family: 'Plus Jakarta Sans', sans-serif; height: 100vh; display: flex; align-items: center; }
        .form-card { background: white; border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(255, 117, 195, 0.1); width: 100%; }
        .btn-pink-gradient { background: linear-gradient(135deg, #ff75c3 0%, #b18cf0 100%); color: white; border: none; font-weight: 600; border-radius: 12px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="form-card p-4">
                <h5 class="fw-bold mb-3">Tambah Kategori Baru</h5>
                <hr class="opacity-25">
                <form id="formKategori">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">ID Kategori</label>
                        <input type="text" name="id_kategori" class="form-control fw-bold bg-light" value="<?= $auto_id ?>" readonly>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Makanan Anjing" required>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="index.php?page=kategori" class="btn btn-light border rounded-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-pink-gradient rounded-pill px-4">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('formKategori').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('../process/insert_kategori.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.msg, confirmButtonColor: '#ff75c3' }).then(() => { window.location.href = 'index.php?page=kategori'; });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal!', text: data.msg, confirmButtonColor: '#ff75c3' });
            }
        });
    });
</script>
</body>
</html>