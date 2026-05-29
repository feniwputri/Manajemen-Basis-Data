<?php
require_once '../config/database.php';
$id = $_GET['id'] ?? '';

if (!empty($id)) {
    try {
        $stmt = $conn->prepare("DELETE FROM Kasir WHERE id_kasir = ?");
        $stmt->execute([$id]);
        header("Location: ../public/index.php?page=kasir&status=success&msg=Petugas kasir berhasil dihapus!");
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            header("Location: ../public/index.php?page=kasir&status=error&msg=Gagal dihapus! Kasir masih terikat dengan data riwayat transaksi.");
        } else {
            header("Location: ../public/index.php?page=kasir&status=error&msg=" . urlencode($e->getMessage()));
        }
    }
} else { header("Location: ../public/index.php?page=kasir"); }