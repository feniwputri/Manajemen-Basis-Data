<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (isset($_SESSION['role'])) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Suka Petshop | Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { background: linear-gradient(135deg, #fff1f2 0%, #e0e7ff 100%); font-family: 'Plus Jakarta Sans', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: white; border: none; border-radius: 24px; box-shadow: 0 10px 40px rgba(255, 117, 195, 0.12); width: 100%; max-width: 400px; }
        .btn-pink-gradient { background: linear-gradient(135deg, #ff75c3 0%, #b18cf0 100%); color: white; border: none; font-weight: 600; border-radius: 12px; padding: 12px; transition: 0.2s; }
    </style>
</head>
<body>
<div class="container p-3">
    <div class="login-card p-4 mx-auto">
        <h4 class="fw-bold text-center text-dark mb-4">Suka Petshop Login</h4>
        <form id="formLogin">
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Username</label>
                <input type="text" name="username" class="form-control bg-light" placeholder="Masukkan username..." required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary">Password</label>
                <input type="password" name="password" class="form-control bg-light" placeholder="Masukkan password..." required>
            </div>
            <button type="submit" class="btn btn-pink-gradient w-100">Masuk Aplikasi</button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('formLogin').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Perhatikan tambahan '../process/' di bawah ini!
        fetch('../process/login_process.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.msg, showConfirmButton: false, timer: 1200 })
                .then(() => { window.location.href = 'index.php?page=produk'; });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal Login!', text: data.msg, confirmButtonColor: '#ff75c3' });
            }
        });
    });
</script>
</script>
</body>
</html>