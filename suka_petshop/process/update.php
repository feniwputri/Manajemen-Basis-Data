<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produk     = trim($_POST['id_produk'] ?? '');
    $id_kategori   = $_POST['id_kategori'] ?? '';
    $nama_produk   = trim($_POST['nama_produk'] ?? '');
    $harga_raw     = $_POST['harga_jual'] ?? '0';
    $harga_clean   = str_replace(['.', ','], '', $harga_raw);
    $harga_jual    = floatval($harga_clean);
    $stok_tersedia = intval($_POST['stok_tersedia'] ?? 0);

    if (empty($id_produk) || empty($nama_produk) || $harga_jual <= 0 || $stok_tersedia < 0) {
        echo json_encode(['status' => 'error', 'msg' => "Validasi gagal. Periksa kembali isi form!"]);
        exit();
    }

    try {
        $conn->beginTransaction();

        $check_nama = $conn->prepare("SELECT COUNT(*) FROM Produk WHERE nama_produk = ? AND id_produk != ?");
        $check_nama->execute([$nama_produk, $id_produk]);
        if ($check_nama->fetchColumn() > 0) {
            throw new Exception("Nama produk sudah dipakai produk lain!");
        }
if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] == 0) {
            $target_dir = "../public/images/";
            
            // 1. CARI DAN HAPUS FOTO LAMA DI FOLDER
            $stmt_old = $conn->prepare("SELECT foto_produk FROM Produk WHERE id_produk = ?");
            $stmt_old->execute([$id_produk]);
            $old_foto = $stmt_old->fetchColumn();
            
            if (!empty($old_foto) && file_exists($target_dir . $old_foto)) {
                unlink($target_dir . $old_foto); // Ini fungsi ajaib untuk hapus file fisik!
            }

            // 2. PROSES UPLOAD FOTO BARU
            $ext = pathinfo($_FILES["foto_produk"]["name"], PATHINFO_EXTENSION);
            $nama_foto = $id_produk . "_" . time() . "." . $ext;
            
            if (move_uploaded_file($_FILES["foto_produk"]["tmp_name"], $target_dir . $nama_foto)) {
                $sql = "UPDATE Produk SET id_kategori = ?, nama_produk = ?, harga_jual = ?, stok_tersedia = ?, foto_produk = ? WHERE id_produk = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id_kategori, $nama_produk, $harga_jual, $stok_tersedia, $nama_foto, $id_produk]);
            }
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'msg' => "Data produk diperbarui!"]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
    }
}