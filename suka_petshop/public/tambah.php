<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') { header("Location: index.php"); exit(); }

require_once '../config/database.php';
try {
    $stmt_next = $conn->query("SELECT id_produk FROM Produk WHERE id_produk LIKE 'PRD-%' ORDER BY id_produk DESC LIMIT 1");
    $last_code = $stmt_next->fetch(PDO::FETCH_ASSOC);
    $next_number = $last_code ? intval(substr($last_code['id_produk'], 4)) + 1 : 100;
    $auto_id = "PRD-" . $next_number;
    $kategori = $conn->query("SELECT * FROM Kategori")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Suka Petshop | Tambah Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { background: linear-gradient(135deg, #fff1f2 0%, #e0e7ff 100%); font-family: 'Plus Jakarta Sans', sans-serif; height: 100vh; display: flex; align-items: center; }
        .form-card { background: white; border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(255, 117, 195, 0.1); width: 100%; }
        .btn-pink-gradient { background: linear-gradient(135deg, #ff75c3 0%, #b18cf0 100%); color: white; border: none; font-weight: 600; border-radius: 12px; transition: 0.2s; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="form-card p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-box-seam-fill text-danger fs-3"></i>
                    <h5 class="fw-bold m-0">Tambah Produk Baru</h5>
                </div>
                <hr class="opacity-25">
                
                <form id="formTambahProduk">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">ID Produk</label>
                        <input type="text" name="id_produk" id="id_produk" class="form-control fw-bold text-primary bg-light" value="<?= $auto_id ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control" placeholder="Masukkan nama barang..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Kategori Kelompok</label>
                        <select name="id_kategori" class="form-select" required>
                            <?php foreach($kategori as $kat): ?><option value="<?= $kat['id_kategori'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Harga (Rp)</label>
                            <input type="text" name="harga_jual" id="harga_jual" class="form-control" placeholder="Contoh: 10.000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Stok Awal</label>
                            <input type="number" name="stok_tersedia" id="stok_tersedia" class="form-control" value="1" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Foto Produk</label>
                        <input type="file" name="foto_produk" class="form-control" accept="image/*">
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="index.php?page=produk" class="btn btn-light border rounded-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-pink-gradient rounded-pill px-4">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('formTambahProduk').addEventListener('submit', function(e) {
        e.preventDefault();

        const idInput = document.getElementById('id_produk').value.trim();
        const hargaInput = document.getElementById('harga_jual').value.trim();
        const stokInput = parseInt(document.getElementById('stok_tersedia').value);

        const regexID = /^PRD-\d+$/;
        if (!regexID.test(idInput)) {
            Swal.fire({ icon: 'error', title: 'Format ID Salah!', text: 'ID harus diawali "PRD-" diikuti angka (Contoh: PRD-100).', confirmButtonColor: '#ff75c3' });
            return;
        }

        if (stokInput <= 0) {
            Swal.fire({ icon: 'error', title: 'Stok Gak Valid!', text: 'Produk baru minimal harus ada 1 item.', confirmButtonColor: '#ff75c3' });
            return;
        }

        const regexPrice = /^\d+(\.\d{3})*$/;
        if (!regexPrice.test(hargaInput.replace(/,/g, ''))) {
            Swal.fire({ icon: 'error', title: 'Format Harga Salah!', text: 'Gunakan angka polos atau format pemisah titik ribuan.', confirmButtonColor: '#ff75c3' });
            return;
        }

        fetch('../process/insert.php', { method: 'POST', body: new FormData(this) })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.msg, confirmButtonColor: '#ff75c3' }).then(() => { window.location.href = 'index.php?page=produk'; });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal Simpan!', text: data.msg, confirmButtonColor: '#ff75c3' });
            }
        });
    });
</script>
</body>
</html>