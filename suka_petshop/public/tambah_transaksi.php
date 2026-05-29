<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kasir') { header("Location: index.php"); exit(); }
require_once '../config/database.php';

try {
    $today = date('ymd');
    $stmt_last = $conn->prepare("SELECT id_transaksi FROM transaksi WHERE id_transaksi LIKE ? ORDER BY id_transaksi DESC LIMIT 1");
    $stmt_last->execute(["TRX-" . $today . "-%"]);
    $last_data = $stmt_last->fetch(PDO::FETCH_ASSOC);

    $next_number = $last_data ? intval(explode('-', $last_data['id_transaksi'])[2]) + 1 : 101;
    $auto_id = "TRX-" . $today . "-" . $next_number;

    $produk_list = $conn->query("SELECT * FROM Produk WHERE stok_tersedia > 0")->fetchAll(PDO::FETCH_ASSOC);
    $kasir_list = $conn->query("SELECT id_kasir, nama_kasir FROM Kasir")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { die("Error Database: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Suka Petshop | Transaksi Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { background: linear-gradient(135deg, #fff1f2 0%, #e0e7ff 100%); font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; display: flex; align-items: center; padding: 20px 0; }
        .form-card { background: white; border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(255, 117, 195, 0.1); padding: 24px; height: 100%; }
        .btn-pink-gradient { background: linear-gradient(135deg, #ff75c3 0%, #b18cf0 100%); color: white; border: none; font-weight: 600; border-radius: 12px; transition: 0.2s; }
        .btn-pink-gradient:hover, .btn-pink-gradient:disabled { opacity: 0.9; color: white; }
    </style>
</head>
<body>
<div class="container">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="form-card">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-cart-plus-fill text-danger fs-4"></i>
                    <h5 class="fw-bold m-0">Input Barang</h5>
                </div>
                <hr class="opacity-25">
                
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Pilih Produk (Ketik ID/Nama)</label>
                    <input list="data_produk" id="input_produk" class="form-control bg-light" placeholder="Pilih produk...">
                    <datalist id="data_produk">
                        <?php foreach($produk_list as $p): ?>
                            <option value="<?= $p['id_produk'] ?>"><?= htmlspecialchars($p['nama_produk']) ?> | Rp<?= $p['harga_jual'] ?> | Stok:<?= $p['stok_tersedia'] ?></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Jumlah (Pcs)</label>
                    <input type="number" id="input_qty" class="form-control bg-light" value="1" min="1">
                </div>

                <button type="button" class="btn btn-outline-primary w-100 fw-semibold rounded-pill" onclick="tambahKeKeranjang()">+ Masukkan ke Nota</button>
            </div>
        </div>

        <div class="col-md-8">
            <div class="form-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0">Detail Pembayaran</h5>
                    <span class="badge bg-light text-primary border px-3 py-2 fs-6 rounded-pill">REF: <?= $auto_id ?></span>
                </div>
                <hr class="opacity-25">
                
                <div class="table-responsive mb-3" style="max-height: 250px; overflow-y: auto;">
                    <table class="table align-middle">
                        <thead class="table-light"><tr><th class="small text-secondary">ID ITEM</th><th class="small text-secondary">JUMLAH</th><th class="small text-secondary text-end">AKSI</th></tr></thead>
                        <tbody id="cart_body"></tbody>
                    </table>
                </div>
                
                <form id="formCheckout">
                    <input type="hidden" name="id_transaksi" value="<?= $auto_id ?>">
                    <div id="hidden_inputs_area"></div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Operator Kasir</label>
                            <select name="id_kasir" class="form-select bg-light" required>
                                <option value="">-- Pilih Petugas --</option>
                                <?php foreach($kasir_list as $k): ?><option value="<?= $k['id_kasir'] ?>"><?= htmlspecialchars($k['nama_kasir']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Metode Pembayaran</label>
                            <select name="metode_pembayaran" class="form-select bg-light">
                                <option value="Cash">Cash</option>
                                <option value="QRIS">QRIS</option>
                                <option value="Kartu Kredit">Kartu Kredit</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php?page=transaksi" class="btn btn-light border rounded-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-pink-gradient rounded-pill px-4" id="btn_submit" disabled>Selesaikan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let cart = []; 
    function tambahKeKeranjang() {
        const idProd = document.getElementById('input_produk').value.trim();
        const qty = parseInt(document.getElementById('input_qty').value);
        if (!idProd || isNaN(qty) || qty <= 0) return;

        const existing = cart.find(item => item.id === idProd);
        if (existing) { existing.qty += qty; } else { cart.push({ id: idProd, qty: qty }); }
        
        renderCart();
        document.getElementById('input_produk').value = '';
        document.getElementById('input_qty').value = 1;
    }

    function hapusItem(index) { cart.splice(index, 1); renderCart(); }

    function renderCart() {
        const tbody = document.getElementById('cart_body');
        const hiddenArea = document.getElementById('hidden_inputs_area');
        const btnSubmit = document.getElementById('btn_submit');
        tbody.innerHTML = ''; hiddenArea.innerHTML = '';
        
        if (cart.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted small">Keranjang belanja masih kosong</td></tr>';
            btnSubmit.disabled = true; return;
        }
        
        btnSubmit.disabled = false;
        cart.forEach((item, index) => {
            tbody.innerHTML += `<tr><td class="fw-bold text-primary">${item.id}</td><td>${item.qty} Pcs</td><td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="hapusItem(${index})">Hapus</button></td></tr>`;
            hiddenArea.innerHTML += `<input type="hidden" name="id_produk[]" value="${item.id}"><input type="hidden" name="kuantitas[]" value="${item.qty}">`;
        });
    }

    document.getElementById('formCheckout').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('../process/insert_transaksi.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.msg, confirmButtonColor: '#ff75c3' }).then(() => { window.location.href = 'index.php?page=transaksi'; });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal!', text: data.msg, confirmButtonColor: '#ff75c3' });
            }
        });
    });
</script>
</body>
</html>