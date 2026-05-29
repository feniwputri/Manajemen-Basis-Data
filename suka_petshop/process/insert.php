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

    if (empty($id_produk) || empty($nama_produk) || $harga_jual <= 0 || $stok_tersedia < 1) {
        echo json_encode(['status' => 'error', 'msg' => "Input data tidak valid atau stok kurang dari 1!"]);
        exit();
    }

    try {
        $conn->beginTransaction();

        $check_id = $conn->prepare("SELECT COUNT(*) FROM Produk WHERE id_produk = ?");
        $check_id->execute([$id_produk]);
        if ($check_id->fetchColumn() > 0) {
            throw new Exception("ID Produk [$id_produk] sudah digunakan!");
        }

        $check_nama = $conn->prepare("SELECT COUNT(*) FROM Produk WHERE nama_produk = ?");
        $check_nama->execute([$nama_produk]);
        if ($check_nama->fetchColumn() > 0) {
            throw new Exception("Nama produk sudah terdaftar!");
        }

        $nama_foto = null; 
        if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] == 0) {
            $target_dir = "../public/images/";
            $ext = pathinfo($_FILES["foto_produk"]["name"], PATHINFO_EXTENSION);
            $nama_foto = $id_produk . "_" . time() . "." . $ext; 
            move_uploaded_file($_FILES["foto_produk"]["tmp_name"], $target_dir . $nama_foto);
        }

        $sql = "INSERT INTO Produk (id_produk, id_kategori, nama_produk, harga_jual, stok_tersedia, foto_produk) VALUES (?, ?, ?, ?, ?, ?)";
        $conn->prepare($sql)->execute([$id_produk, $id_kategori, $nama_produk, $harga_jual, $stok_tersedia, $nama_foto]);

        $conn->commit();
        echo json_encode(['status' => 'success', 'msg' => "Produk berhasil ditambahkan!"]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
    }
}