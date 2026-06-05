<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role'])) { header("Location: login.php"); exit(); }

// Menghubungkan ke file database dengan aman keluar satu folder dari public/
require_once '../config/database.php';
$role = $_SESSION['role'];
$page = isset($_GET['page']) ? $_GET['page'] : 'produk';

// Proteksi Menu: Kasir HANYA diblokir dari menu Data Kasir
if ($role === 'kasir' && $page === 'kasir') {
    header("Location: index.php?page=produk&status=error&msg=Akses Ditolak!");
    exit();
}

try {
    $total_produk = $conn->query("SELECT COUNT(*) as total FROM Produk")->fetch()['total'] ?? 0;
    $estimasi_nilai = $conn->query("SELECT SUM(harga_jual * stok_tersedia) as total_val FROM Produk")->fetch()['total_val'] ?? 0;
    
    // =========================================================================
    // 1. HITUNG STOK KRITIS BARANG NON-FOOD (Kategori 'KAT-004', 'KAT-008', 'KAT-009' < 4)
    // =========================================================================
    $kritis_perlengkapan = $conn->query("
        SELECT COUNT(*) as total 
        FROM Produk 
        WHERE id_kategori IN ('KAT-004', 'KAT-008', 'KAT-009') AND stok_tersedia < 4 AND stok_tersedia > 0
    ")->fetch()['total'] ?? 0;

    // =========================================================================
    // 2. HITUNG STOK KRITIS BARANG FOOD/LAIN (Selain kategori non-food dan < 10)
    // =========================================================================
    $kritis_barang_lain = $conn->query("
        SELECT COUNT(*) as total 
        FROM Produk 
        WHERE id_kategori NOT IN ('04', '08', '09', '004', '008', '009', 'KAT-004', 'KAT-008', 'KAT-009') AND stok_tersedia < 10 AND stok_tersedia > 0
    ")->fetch()['total'] ?? 0;

    // =========================================================================
    // 3. HITUNG STOK KOSONG / HABIS MURNI (= 0)
    // =========================================================================
    $stok_kosong = $conn->query("
        SELECT COUNT(*) as total 
        FROM Produk 
        WHERE stok_tersedia = 0
    ")->fetch()['total'] ?? 0;

    if ($page == 'produk') {
        $data_view = $conn->query("SELECT p.*, k.nama_kategori FROM Produk p LEFT JOIN Kategori k ON p.id_kategori = k.id_kategori")->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($page == 'kategori') {
        $data_view = $conn->query("SELECT k.*, COUNT(p.id_produk) as jumlah_produk FROM Kategori k LEFT JOIN Produk p ON k.id_kategori = p.id_kategori GROUP BY k.id_kategori")->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($page == 'kasir' && $role === 'owner') {
        $data_view = $conn->query("SELECT * FROM Kasir")->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($page == 'transaksi') {
        $data_view = $conn->query("SELECT t.*, k.nama_kasir FROM transaksi t LEFT JOIN Kasir k ON t.id_kasir = k.id_kasir ORDER BY t.tanggal_waktu DESC")->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($page == 'detail_transaksi') {
        $id_trx_filter = $_GET['id_trx'] ?? '';
        if ($id_trx_filter) {
            $stmt = $conn->prepare("SELECT dt.*, p.nama_produk FROM detail_transaksi dt LEFT JOIN Produk p ON dt.id_produk = p.id_produk WHERE dt.id_transaksi = ? ORDER BY dt.id_detail_transaksi ASC");
            $stmt->execute([$id_trx_filter]);
            $data_view = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $data_view = $conn->query("SELECT dt.*, p.nama_produk FROM detail_transaksi dt LEFT JOIN Produk p ON dt.id_produk = p.id_produk ORDER BY dt.id_detail_transaksi DESC")->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    die("Koneksi bermasalah: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Suka Petshop | Panel <?= ucfirst($role) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { background-color: #fff1f2; font-family: 'Plus Jakarta Sans', sans-serif; height: 100vh; overflow: hidden; }
        .app-wrapper { display: flex; height: 100vh; width: 100vw; }
        .sidebar-left { width: 260px; background: linear-gradient(180deg, #ff75c3 0%, #b18cf0 60%, #79e3f7 100%); color: white; display: flex; flex-direction: column; padding: 24px 16px; flex-shrink: 0; }
        
        /* FIX CSS SIDEBAR BRAND: Memberi tata letak flex yang rapi dengan pembatas block baris baru */
        .sidebar-brand { font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 10px; margin-bottom: 32px; }
        .sidebar-brand span { font-size: 0.85rem; display: block; opacity: 0.8; font-weight: 400; margin-top: 2px; }
        
        .nav-menu-container { display: flex; flex-direction: column; gap: 6px; flex-grow: 1; }
        .nav-link-custom { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: rgba(255, 255, 255, 0.9); text-decoration: none; border-radius: 14px; font-weight: 600; transition: 0.2s; }
        .nav-link-custom:hover, .nav-link-custom.active { background-color: white; color: #ff75c3; box-shadow: 0 4px 12px rgba(255, 117, 195, 0.15); }
        .content-right-area { flex-grow: 1; display: flex; flex-direction: column; overflow: hidden; }
        .top-bar-header { background-color: white; padding: 16px 32px; border-bottom: 1px solid #ffe4e6; display: flex; justify-content: space-between; align-items: center; }
        .scrollable-inner-content { padding: 32px; overflow-y: auto; flex-grow: 1; }
        
        .summary-card { 
            background: white; 
            border: none; 
            border-radius: 16px; 
            padding: 16px; 
            border-left: 5px solid #ff75c3; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 105px;
        }
        
        .main-data-card { background: white; border-radius: 20px; padding: 28px; box-shadow: 0 6px 20px rgba(0,0,0,0.01); }
        .btn-add-custom { background: linear-gradient(135deg, #ff75c3 0%, #b18cf0 100%); color: white; font-weight: 600; border: none; padding: 8px 20px; border-radius: 24px; text-decoration: none; }
        .table th { font-weight: 600; color: #a3b2c2; font-size: 0.85rem; text-transform: uppercase; border-bottom: 2px solid #fff1f2; }
        .prod-img-thumb { width: 65px; height: 65px; object-fit: cover; border-radius: 50%; border: 2px solid #fff1f2; box-shadow: 0 3px 8px rgba(255, 117, 195, 0.2); }
    </style>
</head>
<body>

<div class="app-wrapper">
    <div class="sidebar-left">
        <!-- FIX BRAND TEXT: Menggunakan kontainer div pembatas agar tulisan tidak menyatu kesamping -->
        <div class="sidebar-brand">
            <i class="bi bi-paw-fill fs-3"></i>
            <div>
                Suka Petshop
                <span>Panel <?= ucfirst($role) ?></span>
            </div>
        </div>
        <div class="nav-menu-container">
            <a href="index.php?page=produk" class="nav-link-custom <?= $page == 'produk' ? 'active' : '' ?>"><i class="bi bi-box-seam-fill"></i> Daftar Produk</a>
            <a href="index.php?page=kategori" class="nav-link-custom <?= $page == 'kategori' ? 'active' : '' ?>"><i class="bi bi-collection-fill"></i> Kategori Barang</a>
            <?php if ($role === 'owner'): ?>
                <a href="index.php?page=kasir" class="nav-link-custom <?= $page == 'kasir' ? 'active' : '' ?>"><i class="bi bi-person-badge-fill"></i> Data Kasir</a>
            <?php endif; ?>
            <a href="index.php?page=transaksi" class="nav-link-custom <?= $page == 'transaksi' ? 'active' : '' ?>"><i class="bi bi-cart-fill"></i> Nota Penjualan</a>
            <a href="index.php?page=detail_transaksi" class="nav-link-custom <?= $page == 'detail_transaksi' ? 'active' : '' ?>"><i class="bi bi-list-stars"></i> Detail Transaksi</a>
        </div>
        <div class="mt-auto"><a href="../process/logout.php" class="btn btn-danger w-100 rounded-pill py-2 fw-semibold"><i class="bi bi-box-arrow-left me-1"></i> Log Out</a></div>
    </div>

    <div class="content-right-area">
        <div class="top-bar-header">
            <h5 class="m-0 fw-bold text-secondary"><i class="bi bi-laptop me-2"></i> POS Suka Petshop</h5>
            <span class="text-secondary small">User: <b class="text-dark"><?= htmlspecialchars($_SESSION['username']) ?></b></span>
        </div>
        
        <div class="scrollable-inner-content">
            <div class="row g-3 mb-4">
                <div class="col" style="width: 20%;"><div class="summary-card"><span class="text-muted small d-block mb-1">Total Barang</span><h5 class="fw-bold m-0"><?= $total_produk ?> Item</h5></div></div>
                <div class="col" style="width: 20%;"><div class="summary-card" style="border-color:#79e3f7;"><span class="text-muted small d-block mb-1">Estimasi Nilai Jual</span><h5 class="fw-bold m-0" style="font-size:1rem;">Rp <?= number_format($estimasi_nilai, 0, ',', '.') ?></h5></div></div>
                <div class="col" style="width: 20%;"><div class="summary-card" style="border-color:#b18cf0;"><span class="text-muted small d-block mb-1">Stok Hampir Habis (Aksesoris/Non-Food) (&lt;4)</span><h5 class="fw-bold m-0" style="color:#b18cf0;"><?= $kritis_perlengkapan ?> Item</h5></div></div>
                <div class="col" style="width: 20%;"><div class="summary-card" style="border-color:#ffa64d;"><span class="text-muted small d-block mb-1">Stok Hampir Habis (Food Product) (&lt;10)</span><h5 class="fw-bold text-warning m-0"><?= $kritis_barang_lain ?> Item</h5></div></div>
                <div class="col" style="width: 20%;"><div class="summary-card" style="border-color:#dc3545;"><span class="text-muted small d-block mb-1">Stok Kosong</span><h5 class="fw-bold text-danger m-0"><?= $stok_kosong ?> Item</h5></div></div>
            </div>

            <div class="main-data-card">
                <?php if ($page == 'produk'): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0 text-dark">Kelola Data Produk</h5>
                        <?php if ($role == 'owner'): ?><a href="tambah.php" class="btn btn-add-custom">+ Tambah Produk</a><?php endif; ?>
                    </div>
                    <table class="table align-middle">
                        <!-- FIX HEADER TABEL: Kolom AKSI hanya dimunculkan murni jika yang login adalah owner -->
                        <thead><tr><th>ID</th><th>Foto</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Stok</th><?php if($role == 'owner') echo "<th>Aksi</th>"; ?></tr></thead>
                        <tbody>
                            <?php foreach($data_view as $row): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($row['id_produk']) ?></code></td>
                                <td>
                                    <?php if (!empty($row['foto_produk']) && file_exists("images/" . $row['foto_produk'])): ?>
                                        <img src="images/<?= htmlspecialchars($row['foto_produk']) ?>?t=<?= time() ?>" class="prod-img-thumb" alt="Product">
                                    <?php else: ?>
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width:65px; height:65px;"><i class="bi bi-paw-fill text-muted fs-4"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($row['nama_produk']) ?></td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($row['nama_kategori'] ?? 'Umum') ?></span></td>
                                <td>Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?></td>
                                <td>
                                    <?php 
                                    if ($row['stok_tersedia'] == 0) {
                                        $badge_color = 'bg-dark'; 
                                    } else {
                                        $limit_kritis = in_array($row['id_kategori'], ['KAT-004', 'KAT-008', 'KAT-009']) ? 4 : 10;

                                        if ($row['stok_tersedia'] < $limit_kritis) {
                                            $badge_color = 'bg-danger'; 
                                        } else {
                                            $badge_color = 'bg-success'; 
                                        }
                                    }
                                    ?>
                                    <span class="badge <?= $badge_color ?>"><?= $row['stok_tersedia'] == 0 ? 'HABIS' : $row['stok_tersedia'] ?></span>
                                </td>
                                <!-- FIX BARIS DATA: Tombol aksi murni hilang total tanpa teks pengunci gantung jika login kasir -->
                                <?php if ($role == 'owner'): ?>
                                <td>
                                    <a href="edit.php?id=<?= urlencode($row['id_produk']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Edit</a>
                                    <button onclick="konfirmasiHapus('<?= $row['id_produk'] ?>')" class="btn btn-sm btn-outline-danger rounded-pill px-3">Hapus</button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php elseif ($page == 'kategori'): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0 text-dark">Kategori Barang</h5>
                        <?php if ($role == 'owner'): ?><a href="tambah_kategori.php" class="btn btn-add-custom">+ Tambah Kategori</a><?php endif; ?>
                    </div>
                    <table class="table align-middle">
                        <thead><tr><th>ID Kategori</th><th>Nama Kategori</th><th>Jumlah Item</th><?php if($role=='owner') echo "<th>Aksi</th>";?></tr></thead>
                        <tbody>
                            <?php foreach($data_view as $row): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($row['id_kategori']) ?></code></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['nama_kategori']) ?></td>
                                <td><?= $row['jumlah_produk'] ?> Item</td>
                                <?php if ($role == 'owner'): ?>
                                <td>
                                    <a href="edit_kategori.php?id=<?= urlencode($row['id_kategori']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Edit</a>
                                    <a href="../process/delete_kategori.php?id=<?= urlencode($row['id_kategori']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php elseif ($page == 'kasir' && $role == 'owner'): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0 text-dark">Data Petugas Kasir</h5>
                        <a href="tambah_kasir.php" class="btn btn-add-custom">+ Tambah Kasir</a>
                    </div>
                    <table class="table align-middle">
                        <thead><tr><th>ID Kasir</th><th>Nama Pegawai</th><th>Shift</th><th>No. HP</th><th>Alamat</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php foreach($data_view as $row): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($row['id_kasir']) ?></code></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['nama_kasir']) ?></td>
                                <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($row['shift_kerja']) ?></span></td>
                                <td><?= htmlspecialchars($row['no_hp'] ?? '-') ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($row['alamat']) ?></td>
                                <td>
                                    <a href="edit_kasir.php?id=<?= urlencode($row['id_kasir']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Edit</a>
                                    <a href="../process/delete_kasir.php?id=<?= urlencode($row['id_kasir']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Hapus kasir?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php elseif ($page == 'transaksi'): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0 text-dark">Riwayat Nota Jual</h5>
                        <?php if ($role == 'kasir'): ?>
                            <a href="tambah_transaksi.php" class="btn btn-add-custom">+ Transaksi Baru</a>
                        <?php endif; ?>
                    </div>
                    <table class="table align-middle">
                        <thead><tr><th>ID Transaksi</th><th>Tanggal Waktu</th><th>Metode</th><th>Total Belanja</th><th>Nama Kasir</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php foreach($data_view as $row): ?>
                            <tr>
                                <td><b><?= htmlspecialchars($row['id_transaksi']) ?></b></td>
                                <td class="small text-muted"><?= $row['tanggal_waktu'] ?></td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($row['metode_pembayaran']) ?></span></td>
                                <td class="fw-bold text-success">Rp <?= number_format($row['total_belanja'], 0, ',', '.') ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($row['nama_kasir'] ?? 'System') ?></small></td>
                                <td>
                                    <a href="index.php?page=detail_transaksi&id_trx=<?= urlencode($row['id_transaksi']) ?>" class="btn btn-sm btn-info text-white rounded-pill px-3">Lihat Detail</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php elseif ($page == 'detail_transaksi'): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0 text-dark">Rincian Detail Isi Nota <?= isset($_GET['id_trx']) ? "({$_GET['id_trx']})" : "" ?></h5>
                        <?php if(isset($_GET['id_trx'])): ?>
                            <a href="index.php?page=transaksi" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Kembali ke Transaksi</a>
                        <?php endif; ?>
                    </div>
                    <table class="table align-middle">
                        <thead><tr><th>ID Detail</th><th>ID Transaksi</th><th>Nama Produk</th><th>Kuantitas</th><th>Subtotal</th></tr></thead>
                        <tbody>
                            <?php foreach($data_view as $row): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($row['id_detail_transaksi']) ?></code></td>
                                <td><b><?= htmlspecialchars($row['id_transaksi']) ?></b></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['nama_produk'] ?? 'Produk Dihapus') ?></td>
                                <td><?= htmlspecialchars($row['kuantitas']) ?> pcs</td>
                                <td class="text-primary fw-bold">Rp <?= number_format($row['subtotal_harga'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function konfirmasiHapus(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Data produk " + id + " akan terhapus permanen dari sistem!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff75c3',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../process/delete.php?id=' + encodeURIComponent(id);
            }
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'success') { Swal.fire({ icon: 'success', title: 'Berhasil!', text: urlParams.get('msg'), confirmButtonColor: '#ff75c3' }); } 
    else if (urlParams.get('status') === 'error') { Swal.fire({ icon: 'error', title: 'Gagal!', text: urlParams.get('msg'), confirmButtonColor: '#ff75c3' }); }
</script>
</body>
</html>