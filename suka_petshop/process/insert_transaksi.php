<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_transaksi = trim($_POST['id_transaksi'] ?? '');
    $id_kasir     = $_POST['id_kasir'] ?? '';
    $metode       = $_POST['metode_pembayaran'] ?? 'Cash';
    
    // Ini nerima array dari JS Keranjang tadi
    $id_produk_arr = $_POST['id_produk'] ?? [];
    $kuantitas_arr = $_POST['kuantitas'] ?? [];

    if (empty($id_transaksi) || empty($id_kasir) || empty($id_produk_arr)) {
        echo json_encode(['status' => 'error', 'msg' => 'Data transaksi atau keranjang kosong!']); exit();
    }

    try {
        $conn->beginTransaction();

        $total_belanja_semua = 0;
        $items_data = [];

        // 1. Validasi & Hitung Total Dulu (Looping Keranjang)
        for ($i = 0; $i < count($id_produk_arr); $i++) {
            $id_p = $id_produk_arr[$i];
            $qty = intval($kuantitas_arr[$i]);

            $stmt_prd = $conn->prepare("SELECT harga_jual, stok_tersedia FROM Produk WHERE id_produk = ?");
            $stmt_prd->execute([$id_p]);
            $prd = $stmt_prd->fetch(PDO::FETCH_ASSOC);

            if (!$prd) throw new Exception("Barang [$id_p] tidak ditemukan di database!");
            if ($prd['stok_tersedia'] < $qty) throw new Exception("Stok barang [$id_p] tidak mencukupi!");

            $subtotal = $prd['harga_jual'] * $qty;
            $total_belanja_semua += $subtotal;

            // Simpan data sementara buat di insert nanti
            $items_data[] = [
                'id_produk' => $id_p,
                'kuantitas' => $qty,
                'subtotal'  => $subtotal
            ];
        }

        // 2. Insert ke tabel TRANSAKSI (Satu Nota Induk)
        $sql_trx = "INSERT INTO transaksi (id_transaksi, tanggal_waktu, metode_pembayaran, total_belanja, id_kasir) VALUES (?, NOW(), ?, ?, ?)";
        $conn->prepare($sql_trx)->execute([$id_transaksi, $metode, $total_belanja_semua, $id_kasir]);

        // 3. Looping Insert ke DETAIL_TRANSAKSI dan Potong STOK PRODUK
        foreach ($items_data as $idx => $item) {
            $id_detail = $id_transaksi . "-" . ($idx + 1); // Bikin ID detail unik, contoh: TRX-12345-1, TRX-12345-2

            $sql_dtl = "INSERT INTO detail_transaksi (id_detail_transaksi, id_transaksi, id_produk, kuantitas, subtotal_harga) VALUES (?, ?, ?, ?, ?)";
            $conn->prepare($sql_dtl)->execute([$id_detail, $id_transaksi, $item['id_produk'], $item['kuantitas'], $item['subtotal']]);

            $sql_update = "UPDATE Produk SET stok_tersedia = stok_tersedia - ? WHERE id_produk = ?";
            $conn->prepare($sql_update)->execute([$item['kuantitas'], $item['id_produk']]);
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'msg' => count($items_data) . ' macam barang berhasil dibayar dan masuk nota!']);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'msg' => 'Gagal: ' . $e->getMessage()]);
    }
}