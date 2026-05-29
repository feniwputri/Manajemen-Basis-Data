<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') { header("Location: index.php"); exit(); }

require_once '../config/database.php';
$id = isset($_GET['id']) ? $_GET['id'] : '';
if (empty($id)) { header("Location: index.php?page=produk"); exit(); }

try {
    $stmt = $conn->prepare("SELECT * FROM Produk WHERE id_produk = ?");
    $stmt->execute([$id]);
    $produk = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$produk) { header("Location: index.php?page=produk&status=error&msg=Produk tidak ada!"); exit(); }
    $kategori = $conn->query("SELECT * FROM Kategori")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Suka Petshop | Edit Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <h5 class="fw-bold m-0">Ubah Data Produk</h5>
                </div>
                <hr class="opacity-25">
                
                <form id="formEditProduk">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">ID Produk</label>
                        <input type="text" name="id_produk" id="id_produk" class="form-control bg-light" value="<?= htmlspecialchars($produk['id_produk']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Kategori Kelompok</label>
                        <select name="id_kategori" class="form-select" required>
                            <?php foreach($kategori as $kat): ?>
                                <option value="<?= $kat['id_kategori'] ?>" <?= $kat['id_kategori'] == $produk['id_kategori'] ? 'selected' : '' ?>><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Harga (Rp)</label>
                            <input type="text" name="harga_jual" id="harga_jual" class="form-control" value="<?= number_format($produk['harga_jual'], 0, '', '.') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Stok</label>
                            <input type="number" name="stok_tersedia" id="stok_tersedia" class="form-control" value="<?= $produk['stok_tersedia'] ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Ubah Foto Produk (Opsional)</label>
                        <input type="file" name="foto_produk" class="form-control" accept="image/*">
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="index.php?page=produk" class="btn btn-light border rounded-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-pink-gradient rounded-pill px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('formEditProduk').addEventListener('submit', function(e) {
        e.preventDefault(); 
        const stokInput = parseInt(document.getElementById('stok_tersedia').value);

        if (stokInput < 0) {
            Swal.fire({ icon: 'error', title: 'Stok Gak Boleh Minus!', text: 'Jumlah barang minimal kosong (0).', confirmButtonColor: '#ff75c3' });
            return;
        }

        fetch('../process/update.php', { method: 'POST', body: new FormData(this) })
        .then(response => response.json()) 
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.msg, confirmButtonColor: '#ff75c3' }).then(() => { window.location.href = 'index.php?page=produk'; });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal Update!', text: data.msg, confirmButtonColor: '#ff75c3' });
            }
        });
    });
</script>
</body>
</html>