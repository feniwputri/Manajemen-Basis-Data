<?php
require_once '../config/database.php';
$id = $_GET['id'] ?? '';

if (!empty($id)) {
    try {
        $stmt = $conn->prepare("DELETE FROM Kategori WHERE id_kategori = ?");
        $stmt->execute([$id]);
        header("Location: ../public/index.php?page=kategori&status=success&msg=Kategori terhapus!");
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            header("Location: ../public/index.php?page=kategori&status=error&msg=Gagal! Masih ada produk di dalam kategori ini.");
        } else {
            header("Location: ../public/index.php?page=kategori&status=error&msg=" . urlencode($e->getMessage()));
        }
    }
} else { header("Location: ../public/index.php?page=kategori"); }