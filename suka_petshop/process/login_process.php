<?php
require_once '../config/database.php';
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    try {
        $stmt = $conn->prepare("SELECT * FROM akun_petshop WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['password']) {
            $_SESSION['id_user']  = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role']; // Menyimpan string 'owner' atau 'kasir'
            echo json_encode(['status' => 'success', 'msg' => 'Autentikasi berhasil!']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Username atau password tidak valid!']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'msg' => 'Eror Database: ' . $e->getMessage()]);
    }
}