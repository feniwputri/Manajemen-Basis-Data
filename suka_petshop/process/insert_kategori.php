<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kategori   = trim($_POST['id_kategori'] ?? '');
    $nama_kategori = trim($_POST['nama_kategori'] ?? '');

    if (empty($id_kategori) || empty($nama_kategori)) {
        echo json_encode(['status' => 'error', 'msg' => 'Data tidak boleh kosong!']);
        exit();
    }

    try {
        $stmt = $conn->prepare("INSERT INTO Kategori (id_kategori, nama_kategori) VALUES (?, ?)");
        $stmt->execute([$id_kategori, $nama_kategori]);
        echo json_encode(['status' => 'success', 'msg' => 'Kategori baru berhasil ditambahkan!']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'msg' => 'Error: ' . $e->getMessage()]);
    }
}