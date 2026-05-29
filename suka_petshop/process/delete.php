<?php
require_once '../config/database.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    try {
        $conn->beginTransaction();

        $sql = "DELETE FROM Produk WHERE id_produk = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);

        $conn->commit();
        header("Location: ../public/index.php?page=produk&status=success&msg=Produk berhasil dihapus dari sistem.");
    } catch (PDOException $e) {
        $conn->rollBack();
        if ($e->getCode() == '23000') {
            header("Location: ../public/index.php?page=produk&status=error&msg=Gagal Hapus! Produk ini berelasi dengan riwayat nota kasir.");
        } else {
            header("Location: ../public/index.php?page=produk&status=error&msg=" . urlencode($e->getMessage()));
        }
    }
} else {
    header("Location: ../public/index.php?page=produk");
}