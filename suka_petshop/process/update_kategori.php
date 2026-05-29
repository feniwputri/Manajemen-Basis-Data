<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id_kategori'] ?? '');
    $nama = trim($_POST['nama_kategori'] ?? '');

    if (empty($id) || empty($nama)) { echo json_encode(['status' => 'error', 'msg' => 'Form tidak lengkap!']); exit(); }

    try {
        $stmt = $conn->prepare("UPDATE Kategori SET nama_kategori = ? WHERE id_kategori = ?");
        $stmt->execute([$nama, $id]);
        echo json_encode(['status' => 'success', 'msg' => 'Kategori berhasil diperbarui!']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
    }
}