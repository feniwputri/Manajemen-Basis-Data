<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kasir    = trim($_POST['id_kasir'] ?? '');
    $nama_kasir  = trim($_POST['nama_kasir'] ?? '');
    $shift_kerja = $_POST['shift_kerja'] ?? '';
    $no_hp       = trim($_POST['no_hp'] ?? '');
    $alamat      = trim($_POST['alamat'] ?? '');

    if (empty($id_kasir) || empty($nama_kasir)) {
        echo json_encode(['status' => 'error', 'msg' => 'ID dan Nama Kasir wajib diisi!']);
        exit();
    }

    try {
        $sql = "INSERT INTO Kasir (id_kasir, nama_kasir, shift_kerja, alamat, no_hp) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_kasir, $nama_kasir, $shift_kerja, $alamat, $no_hp]);

        echo json_encode(['status' => 'success', 'msg' => 'Data petugas kasir berhasil masuk database!']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'msg' => 'Gagal simpan ke DB: ' . $e->getMessage()]);
    }
}