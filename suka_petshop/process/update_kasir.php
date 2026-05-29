<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id_kasir'] ?? '');
    $nama = trim($_POST['nama_kasir'] ?? '');
    $shift = $_POST['shift_kerja'] ?? '';
    $hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if (empty($id) || empty($nama)) { echo json_encode(['status' => 'error', 'msg' => 'Data wajib diisi!']); exit(); }

    try {
        $stmt = $conn->prepare("UPDATE Kasir SET nama_kasir = ?, shift_kerja = ?, no_hp = ?, alamat = ? WHERE id_kasir = ?");
        $stmt->execute([$nama, $shift, $hp, $alamat, $id]);
        echo json_encode(['status' => 'success', 'msg' => 'Data petugas kasir berhasil diperbarui!']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
    }
}