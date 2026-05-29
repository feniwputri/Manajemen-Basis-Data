<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
session_unset();
session_destroy();
header("Location: ../public/login.php?status=success&msg=Berhasil keluar sistem!");
exit();