-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 03 Jun 2026 pada 16.46
-- Versi server: 9.5.0
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `catshopdb`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `batalkan_item_transaksi` (IN `p_id_dtl` VARCHAR(20))   BEGIN
    DECLARE v_id_prd VARCHAR(10);
    DECLARE v_id_trx VARCHAR(20);
    DECLARE v_qty INT;
    DECLARE v_subtotal DECIMAL(12,2);

    SELECT id_produk, id_transaksi, kuantitas, subtotal_harga
    INTO v_id_prd, v_id_trx, v_qty, v_subtotal
    FROM detail_transaksi
    WHERE id_detail_transaksi = p_id_dtl;

    IF v_id_trx IS NOT NULL THEN
         UPDATE produk
         SET stok_tersedia = stok_tersedia + v_qty
         WHERE id_produk = v_id_prd;

         UPDATE transaksi
         SET total_belanja = total_belanja - v_subtotal
         WHERE id_transaksi = v_id_trx;

         DELETE FROM detail_transaksi WHERE id_detail_transaksi = p_id_dtl;

         SELECT 'SUCCESS!: Item dibatalkan dan stok dikembalikan' AS Pesan;
    ELSE
         SIGNAL SQLSTATE '45000'
         SET MESSAGE_TEXT = 'ERROR!: ID Detail Transaksi tidak ditemukan!';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `tambah_barang_ke_nota` (IN `p_id_dtl` VARCHAR(20), IN `p_id_trx` VARCHAR(20), IN `p_id_prd` VARCHAR(10), IN `p_qty` INT)   BEGIN
    DECLARE v_stok INT;
    DECLARE v_harga DECIMAL(12,2);
    DECLARE v_subtotal DECIMAL(12,2);

    SELECT stok_tersedia, harga_jual INTO v_stok, v_harga
    FROM produk WHERE id_produk = p_id_prd;

    IF v_stok < p_qty THEN
         SIGNAL SQLSTATE '45000'
         SET MESSAGE_TEXT = 'ERROR! Stok barang tidak mencukupi!';
    ELSE
         SET v_subtotal = v_harga * p_qty;

         INSERT INTO detail_transaksi (id_detail_transaksi, id_transaksi, id_produk, kuantitas, subtotal_harga)
         VALUES (p_id_dtl, p_id_trx, p_id_prd, p_qty, v_subtotal);

         UPDATE transaksi
         SET total_belanja = total_belanja + v_subtotal
         WHERE id_transaksi = p_id_trx;
    END IF;
END$$

--
-- Fungsi
--
CREATE DEFINER=`root`@`localhost` FUNCTION `hitung_omset_harian` (`tgl_input` DATE) RETURNS DECIMAL(12,2) DETERMINISTIC BEGIN
    DECLARE total_omset DECIMAL(12,2);
    
  
    SELECT SUM(total_belanja) 
    INTO total_omset 
    FROM Transaksi 
    WHERE DATE(tanggal_waktu) = tgl_input;
    

    RETURN IFNULL(total_omset, 0);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `hitung_pendapatan_tanggal` (`tgl_input` DATE) RETURNS DECIMAL(12,2) DETERMINISTIC BEGIN
    DECLARE total_omzet DECIMAL(12,2);
    
    SELECT SUM(total_harga) 
    INTO total_omzet 
    FROM Transaksi 
    WHERE DATE(tanggal_waktu) = tgl_input;
    
    RETURN IFNULL(total_omzet, 0);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `hitung_shampoo` (`idproduk` INT) RETURNS INT DETERMINISTIC begin
declare total int;
select sum(harga_jual)
into total
from produk
where id_produk = 'PRD-008';
return ifnull (total,0);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `hitung_shampooo` (`idpro` VARCHAR(10)) RETURNS DECIMAL(12,2) DETERMINISTIC begin
declare total decimal(12,2);
select sum(harga_jual)
into total
from produk
where id_produk = idpro;
return ifnull(total,0);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `hitung_shampoooo` (`idproo` VARCHAR(10)) RETURNS DECIMAL(12,2) DETERMINISTIC begin
declare total decimal(12,2);
select sum(subtotal_harga)
into total
from detail_transaksi 
where id_produk = id_proo;
return ifnull(total,0);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `hitung_total` (`idtrx` VARCHAR(20)) RETURNS DECIMAL(12,2) DETERMINISTIC BEGIN
DECLARE total DECIMAL(12,2);
SELECT SUM(subtotal_harga)
      INTO total
   FROM Detail_Transaksi
WHERE id_transaksi = idtrx;
RETURN IFNULL(total, 0);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `akun_petshop`
--

CREATE TABLE `akun_petshop` (
  `id_user` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('owner','kasir') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `akun_petshop`
--

INSERT INTO `akun_petshop` (`id_user`, `username`, `password`, `role`) VALUES
(1, 'owner', 'owner', 'owner'),
(2, 'kasir', 'kasir', 'kasir');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail_transaksi` varchar(20) NOT NULL,
  `id_transaksi` varchar(20) NOT NULL,
  `id_produk` varchar(10) NOT NULL,
  `kuantitas` int NOT NULL,
  `subtotal_harga` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail_transaksi`, `id_transaksi`, `id_produk`, `kuantitas`, `subtotal_harga`) VALUES
('DTL-001', 'TRX-231101-001', 'PRD-001', 3, 75000.00),
('DTL-002', 'TRX-231101-002', 'PRD-002', 2, 120000.00),
('DTL-003', 'TRX-231101-003', 'PRD-003', 1, 95000.00),
('DTL-004', 'TRX-231101-004', 'PRD-004', 2, 60000.00),
('DTL-005', 'TRX-231101-005', 'PRD-005', 3, 180000.00),
('DTL-006', 'TRX-231101-006', 'PRD-006', 2, 220000.00),
('DTL-007', 'TRX-231101-007', 'PRD-001', 1, 85000.00),
('DTL-008', 'TRX-231101-008', 'PRD-003', 2, 140000.00),
('DTL-009', 'TRX-231101-009', 'PRD-007', 1, 300000.00),
('DTL-010', 'TRX-231101-010', 'PRD-002', 3, 90000.00),
('DTL-011', 'TRX-231102-011', 'PRD-001', 2, 65000.00),
('DTL-012', 'TRX-231102-012', 'PRD-002', 2, 110000.00),
('DTL-013', 'TRX-231102-013', 'PRD-003', 2, 130000.00),
('DTL-014', 'TRX-231102-014', 'PRD-004', 2, 70000.00),
('DTL-015', 'TRX-231102-015', 'PRD-005', 4, 200000.00),
('DTL-016', 'TRX-231102-016', 'PRD-006', 1, 175000.00),
('DTL-017', 'TRX-231102-017', 'PRD-001', 2, 80000.00),
('DTL-018', 'TRX-231102-018', 'PRD-003', 3, 150000.00),
('DTL-019', 'TRX-231102-019', 'PRD-007', 1, 280000.00),
('DTL-020', 'TRX-231102-020', 'PRD-002', 3, 95000.00),
('DTL-021', 'TRX-231103-021', 'PRD-001', 2, 70000.00),
('DTL-022', 'TRX-231103-022', 'PRD-002', 2, 115000.00),
('DTL-023', 'TRX-231103-023', 'PRD-003', 1, 125000.00),
('DTL-024', 'TRX-231103-024', 'PRD-004', 2, 68000.00),
('DTL-025', 'TRX-231103-025', 'PRD-005', 3, 210000.00),
('DTL-026', 'TRX-231103-026', 'PRD-006', 1, 185000.00),
('DTL-027', 'TRX-231103-027', 'PRD-001', 2, 82000.00),
('DTL-028', 'TRX-231103-028', 'PRD-003', 3, 145000.00),
('DTL-029', 'TRX-231103-029', 'PRD-007', 1, 320000.00),
('DTL-030', 'TRX-231103-030', 'PRD-002', 4, 100000.00),
('DTL-031', 'TRX-231104-031', 'PRD-001', 2, 72000.00),
('DTL-032', 'TRX-231104-032', 'PRD-002', 2, 118000.00),
('DTL-033', 'TRX-231104-033', 'PRD-003', 2, 140000.00),
('DTL-034', 'TRX-231104-034', 'PRD-004', 2, 65000.00),
('DTL-035', 'TRX-231104-035', 'PRD-005', 3, 205000.00),
('DTL-036', 'TRX-231104-036', 'PRD-006', 1, 190000.00),
('DTL-037', 'TRX-231104-037', 'PRD-001', 3, 87000.00),
('DTL-038', 'TRX-231104-038', 'PRD-003', 3, 155000.00),
('DTL-039', 'TRX-231104-039', 'PRD-007', 1, 310000.00),
('DTL-040', 'TRX-231104-040', 'PRD-002', 2, 98000.00),
('DTL-041', 'TRX-231105-041', 'PRD-001', 2, 68000.00),
('DTL-042', 'TRX-231105-042', 'PRD-002', 2, 125000.00),
('DTL-043', 'TRX-231105-043', 'PRD-003', 3, 135000.00),
('DTL-044', 'TRX-231105-044', 'PRD-004', 2, 70000.00),
('DTL-045', 'TRX-231105-045', 'PRD-005', 4, 220000.00),
('DTL-046', 'TRX-231105-046', 'PRD-006', 1, 200000.00),
('DTL-047', 'TRX-231105-047', 'PRD-001', 2, 85000.00),
('DTL-048', 'TRX-231105-048', 'PRD-003', 3, 165000.00),
('DTL-049', 'TRX-231105-049', 'PRD-007', 1, 330000.00),
('DTL-050', 'TRX-231105-050', 'PRD-002', 3, 102000.00),
('DTL-051', 'TRX-231106-051', 'PRD-001', 3, 75000.00),
('DTL-052', 'TRX-231106-052', 'PRD-002', 2, 130000.00),
('DTL-053', 'TRX-231106-053', 'PRD-003', 2, 145000.00),
('DTL-054', 'TRX-231106-054', 'PRD-004', 2, 72000.00),
('DTL-055', 'TRX-231106-055', 'PRD-005', 3, 215000.00),
('DTL-056', 'TRX-231106-056', 'PRD-006', 1, 210000.00),
('DTL-057', 'TRX-231106-057', 'PRD-001', 3, 90000.00),
('DTL-058', 'TRX-231106-058', 'PRD-003', 2, 170000.00),
('DTL-059', 'TRX-231106-059', 'PRD-007', 1, 340000.00),
('DTL-060', 'TRX-231106-060', 'PRD-002', 2, 110000.00),
('DTL-061', 'TRX-231107-061', 'PRD-001', 2, 70000.00),
('DTL-062', 'TRX-231107-062', 'PRD-002', 2, 120000.00),
('DTL-063', 'TRX-231107-063', 'PRD-003', 2, 150000.00),
('DTL-064', 'TRX-231107-064', 'PRD-004', 2, 68000.00),
('DTL-065', 'TRX-231107-065', 'PRD-005', 3, 225000.00),
('DTL-066', 'TRX-231107-066', 'PRD-006', 1, 220000.00),
('DTL-067', 'TRX-231107-067', 'PRD-001', 3, 87000.00),
('DTL-068', 'TRX-231107-068', 'PRD-003', 3, 180000.00),
('DTL-069', 'TRX-231107-069', 'PRD-007', 1, 350000.00),
('DTL-070', 'TRX-231107-070', 'PRD-002', 5, 115000.00),
('DTL-071', 'TRX-231108-071', 'PRD-001', 2, 73000.00),
('DTL-072', 'TRX-231108-072', 'PRD-002', 3, 135000.00),
('DTL-073', 'TRX-231108-073', 'PRD-003', 2, 160000.00),
('DTL-074', 'TRX-231108-074', 'PRD-004', 3, 75000.00),
('DTL-075', 'TRX-231108-075', 'PRD-005', 4, 230000.00),
('DTL-076', 'TRX-231108-076', 'PRD-006', 1, 225000.00),
('DTL-077', 'TRX-231108-077', 'PRD-001', 4, 92000.00),
('DTL-078', 'TRX-231108-078', 'PRD-003', 3, 185000.00),
('DTL-079', 'TRX-231108-079', 'PRD-007', 1, 360000.00),
('DTL-080', 'TRX-231108-080', 'PRD-002', 3, 120000.00),
('DTL-081', 'TRX-231109-081', 'PRD-001', 3, 75000.00),
('DTL-082', 'TRX-231109-082', 'PRD-002', 2, 140000.00),
('DTL-083', 'TRX-231109-083', 'PRD-003', 3, 165000.00),
('DTL-084', 'TRX-231109-084', 'PRD-004', 4, 80000.00),
('DTL-085', 'TRX-231109-085', 'PRD-005', 4, 240000.00),
('DTL-086', 'TRX-231109-086', 'PRD-006', 1, 230000.00),
('DTL-087', 'TRX-231109-087', 'PRD-001', 5, 95000.00),
('DTL-088', 'TRX-231109-088', 'PRD-003', 2, 190000.00),
('DTL-089', 'TRX-231109-089', 'PRD-007', 1, 370000.00),
('DTL-090', 'TRX-231109-090', 'PRD-002', 5, 125000.00),
('DTL-091', 'TRX-231110-091', 'PRD-001', 4, 80000.00),
('DTL-092', 'TRX-231110-092', 'PRD-002', 5, 145000.00),
('DTL-093', 'TRX-231110-093', 'PRD-003', 2, 170000.00),
('DTL-094', 'TRX-231110-094', 'PRD-004', 4, 82000.00),
('DTL-095', 'TRX-231110-095', 'PRD-005', 5, 250000.00),
('DTL-096', 'TRX-231110-096', 'PRD-006', 1, 240000.00),
('DTL-097', 'TRX-231110-097', 'PRD-001', 4, 100000.00),
('DTL-098', 'TRX-231110-098', 'PRD-003', 2, 200000.00),
('DTL-099', 'TRX-231110-099', 'PRD-007', 1, 380000.00),
('DTL-100', 'TRX-231110-100', 'PRD-002', 4, 130000.00),
('TRX-260531-101-1', 'TRX-260531-101', 'PRD-093', 1, 22000.00),
('TRX-260531-101-2', 'TRX-260531-101', 'PRD-045', 4, 140000.00),
('TRX-260531-102-1', 'TRX-260531-102', 'PRD-001', 15, 1800000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kasir`
--

CREATE TABLE `kasir` (
  `id_kasir` varchar(10) NOT NULL,
  `nama_kasir` varchar(50) NOT NULL,
  `shift_kerja` varchar(10) DEFAULT NULL,
  `alamat` text,
  `no_hp` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `kasir`
--

INSERT INTO `kasir` (`id_kasir`, `nama_kasir`, `shift_kerja`, `alamat`, `no_hp`) VALUES
('KSR-001', 'Zayden', 'Pagi', 'Jl. Tanray No. 10, Pontianak', '0812318380163'),
('KSR-002', 'Amirah Putri Cintia', 'Sore', 'Jl. Ahmad Yani No. 2, Pontianak', '085712345678');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` varchar(10) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
('KAT-001', 'Makanan Kucing (Kering)'),
('KAT-002', 'Makanan Kucing (Basah)'),
('KAT-003', 'Pasir Kucing'),
('KAT-004', 'Mainan & Aksesoris'),
('KAT-005', 'Obat & Vitamin'),
('KAT-006', 'Susu Kucing'),
('KAT-007', 'Perawatan Tubuh (Sampo/Sisir)'),
('KAT-008', 'Kandang & Pet Cargo'),
('KAT-009', 'Tempat Makan & Minum'),
('KAT-010', 'Snack Kucing');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` varchar(10) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `stok_tersedia` int NOT NULL DEFAULT '0',
  `foto_produk` varchar(255) DEFAULT NULL,
  `id_kategori` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `harga_jual`, `stok_tersedia`, `foto_produk`, `id_kategori`) VALUES
('PRD-001', 'Royal Canin Kitten 1kg', 120000.00, 0, 'PRD-001_1779962323.jpg', 'KAT-001'),
('PRD-002', 'Whiskas Tuna 1.2kg', 65000.00, 34, 'PRD-002_1779962369.jpg', 'KAT-001'),
('PRD-003', 'Pasir Zeolit Gumpal 5L', 35000.00, 46, 'PRD-003_1779964404.webp', 'KAT-003'),
('PRD-004', 'Tongkat Bulu Kucing', 15000.00, 100, 'PRD-004_1779963099.jpg', 'KAT-004'),
('PRD-005', 'Nutriplus Gel 120g', 150000.00, 7, 'PRD-005_1779964426.jpg', 'KAT-005'),
('PRD-006', 'Life Cat Kaleng 400g', 20000.00, 56, 'PRD-006_1779963122.jpg', 'KAT-002'),
('PRD-007', 'Susu Growssy 20g', 5000.00, 120, 'PRD-007_1779963132.jpg', 'KAT-006'),
('PRD-008', 'Sampo Tick & Flea 250ml', 45000.00, 25, 'PRD-008_1779963420.jpg', 'KAT-007'),
('PRD-009', 'Pet Cargo Plastik', 150000.00, 10, 'PRD-009_1779963528.jpg', 'KAT-008'),
('PRD-010', 'Dispenser Makan Otomatis', 85000.00, 15, 'PRD-010_1779963779.jpg', 'KAT-009'),
('PRD-011', 'Meo Creamy Treats 15g', 25000.00, 80, 'PRD-011_1779964260.jpg', 'KAT-010'),
('PRD-012', 'Me-O Dry Cat Food 1.2kg', 85000.00, 45, 'PRD-012_1779964272.jpg', 'KAT-001'),
('PRD-013', 'Pro Plan Kitten 1.5kg', 180000.00, 20, 'PRD-013_1779964289.webp', 'KAT-001'),
('PRD-014', 'Felibite Dry Food 500g', 18000.00, 60, 'PRD-014_1779964305.jpeg', 'KAT-001'),
('PRD-015', 'Equilibrio Kitten 1kg', 145000.00, 15, 'PRD-015_1779964741.webp', 'KAT-001'),
('PRD-016', 'Whiskas Junior 450g', 30000.00, 50, 'PRD-016_1779965057.webp', 'KAT-001'),
('PRD-017', 'Friskies Seafood 1.1kg', 75000.00, 35, 'PRD-017_1779965070.avif', 'KAT-001'),
('PRD-018', 'Royal Canin Persian 1kg', 165000.00, 10, 'PRD-018_1779965100.jpg', 'KAT-001'),
('PRD-019', 'Sheba Pouch 70g', 12000.00, 100, 'PRD-019_1779965116.webp', 'KAT-002'),
('PRD-020', 'Fancy Feast Can 85g', 22000.00, 80, 'PRD-020_1779965310.webp', 'KAT-002'),
('PRD-021', 'Life Cat Pouch 85g', 9000.00, 120, 'PRD-021_1779965324.jpeg', 'KAT-002'),
('PRD-022', 'Snappy Tom Pouch 85g', 11000.00, 90, 'PRD-022_1779965339.webp', 'KAT-002'),
('PRD-023', 'Gourmet Perle 85g', 25000.00, 40, 'PRD-023_1779965353.webp', 'KAT-002'),
('PRD-024', 'Maxi Cat Kaleng 400g', 22000.00, 55, 'PRD-024_1779965362.jpeg', 'KAT-002'),
('PRD-025', 'Pasir Wangi Bentonite 10L', 65000.00, 30, 'PRD-025_1779965624.jpeg', 'KAT-003'),
('PRD-026', 'Pasir Gumpal Bio-Sand 10L', 70000.00, 25, 'PRD-026_1779966808.jpeg', 'KAT-003'),
('PRD-027', 'Pasir Kucing Silica 5L', 85000.00, 20, 'PRD-027_1779966821.webp', 'KAT-003'),
('PRD-028', 'Sekop Pasir Kucing', 15000.00, 50, 'PRD-028_1779966835.jpeg', 'KAT-004'),
('PRD-029', 'Bola Mainan Kucing', 10000.00, 100, 'PRD-029_1779966846.webp', 'KAT-004'),
('PRD-030', 'Laser Pointer Kucing', 25000.00, 45, 'PRD-030_1780049234.jpg', 'KAT-004'),
('PRD-031', 'Cat Tree Minimalis', 450000.00, 5, NULL, 'KAT-004'),
('PRD-032', 'Terowongan Mainan Kucing', 75000.00, 15, NULL, 'KAT-004'),
('PRD-033', 'Ikan Mainan Catnip', 35000.00, 40, NULL, 'KAT-004'),
('PRD-034', 'Vitamin Gel Nutri-Plus 120g', 150000.00, 20, NULL, 'KAT-005'),
('PRD-035', 'Obat Cacing Drontal Cat', 25000.00, 50, NULL, 'KAT-005'),
('PRD-036', 'Vitamin Bulu Kucing', 45000.00, 30, NULL, 'KAT-005'),
('PRD-037', 'Obat Tetes Telinga', 35000.00, 25, NULL, 'KAT-005'),
('PRD-038', 'Multivitamin Kucing Tablet', 50000.00, 40, NULL, 'KAT-005'),
('PRD-039', 'Susu Kucing KMR 150g', 120000.00, 15, NULL, 'KAT-006'),
('PRD-040', 'Susu Bubuk PetLac', 95000.00, 20, NULL, 'KAT-006'),
('PRD-041', 'Botol Susu Kitten Set', 30000.00, 30, NULL, 'KAT-006'),
('PRD-042', 'Sisir Kutu Kucing', 20000.00, 60, NULL, 'KAT-007'),
('PRD-043', 'Shampo Kucing Anti Jamur', 55000.00, 35, NULL, 'KAT-007'),
('PRD-044', 'Gunting Kuku Kucing', 25000.00, 40, NULL, 'KAT-007'),
('PRD-045', 'Sikat Bulu Slicker Brush', 35000.00, 26, NULL, 'KAT-007'),
('PRD-046', 'Tisu Basah Hewan', 15000.00, 80, NULL, 'KAT-007'),
('PRD-047', 'Kandang Besi Lipat M', 350000.00, 8, NULL, 'KAT-008'),
('PRD-048', 'Pet Cargo Travel Bag', 250000.00, 10, NULL, 'KAT-008'),
('PRD-049', 'Tas Gendong Kucing Transparan', 300000.00, 7, NULL, 'KAT-008'),
('PRD-050', 'Kandang Kucing Tingkat', 850000.00, 3, NULL, 'KAT-008'),
('PRD-051', 'Tempat Makan Double Bowl', 35000.00, 50, NULL, 'KAT-009'),
('PRD-052', 'Tempat Minum Gantung', 25000.00, 40, NULL, 'KAT-009'),
('PRD-053', 'Dispenser Air Otomatis', 120000.00, 15, NULL, 'KAT-009'),
('PRD-054', 'Tempat Makan Keramik', 55000.00, 20, NULL, 'KAT-009'),
('PRD-055', 'Creamy Treats Rasa Tuna', 15000.00, 100, NULL, 'KAT-010'),
('PRD-056', 'Creamy Treats Rasa Salmon', 15000.00, 100, NULL, 'KAT-010'),
('PRD-057', 'Snack Dental Cat', 20000.00, 60, NULL, 'KAT-010'),
('PRD-058', 'Snack Catnip Ball', 12000.00, 70, NULL, 'KAT-010'),
('PRD-059', 'Frieskies Party Mix 60g', 25000.00, 50, NULL, 'KAT-010'),
('PRD-060', 'Pro Plan Sterilized 1.5kg', 210000.00, 15, NULL, 'KAT-001'),
('PRD-061', 'Beauty Cat 1kg', 60000.00, 40, NULL, 'KAT-001'),
('PRD-062', 'Bolt Cat Food 1kg', 22000.00, 100, NULL, 'KAT-001'),
('PRD-063', 'Kucingku Dry Food 1kg', 25000.00, 80, NULL, 'KAT-001'),
('PRD-064', 'Happy Cat 1.4kg', 190000.00, 12, NULL, 'KAT-001'),
('PRD-065', 'Kit Cat Pouch 85g', 12000.00, 90, NULL, 'KAT-002'),
('PRD-066', 'Felix Pouch 70g', 9500.00, 150, NULL, 'KAT-002'),
('PRD-067', 'Dewchick Kaleng 400g', 18000.00, 60, NULL, 'KAT-002'),
('PRD-068', 'Pasir Zeolit No 2 (20kg)', 50000.00, 20, NULL, 'KAT-003'),
('PRD-069', 'Pasir Wangi Lavender 5L', 35000.00, 40, NULL, 'KAT-003'),
('PRD-070', 'Tongkat Bulu Lonceng', 12000.00, 100, NULL, 'KAT-004'),
('PRD-071', 'Bola Kerincingan', 8000.00, 120, NULL, 'KAT-004'),
('PRD-072', 'Vitamin Mata Kucing', 40000.00, 25, NULL, 'KAT-005'),
('PRD-073', 'Obat Jamur Semprot', 45000.00, 30, NULL, 'KAT-005'),
('PRD-074', 'Obat Scabies 10ml', 30000.00, 20, NULL, 'KAT-005'),
('PRD-075', 'Susu Lactol 250g', 150000.00, 10, NULL, 'KAT-006'),
('PRD-076', 'Susu Kucing Sachet', 7000.00, 100, NULL, 'KAT-006'),
('PRD-077', 'Shampo Anti Kutu 200ml', 40000.00, 30, NULL, 'KAT-007'),
('PRD-078', 'Sisir Kutu Baja', 25000.00, 40, NULL, 'KAT-007'),
('PRD-079', 'Kandang Besi Lipat L', 450000.00, 5, NULL, 'KAT-008'),
('PRD-080', 'Pet Cargo Hardcase S', 180000.00, 10, NULL, 'KAT-008'),
('PRD-081', 'Tempat Makan Anti Semut', 40000.00, 40, NULL, 'KAT-009'),
('PRD-082', 'Dispenser Makanan Manual', 60000.00, 25, NULL, 'KAT-009'),
('PRD-083', 'Snack Biskuit Kucing', 18000.00, 50, NULL, 'KAT-010'),
('PRD-084', 'Purina Fancy Feast 85g', 24000.00, 30, NULL, 'KAT-002'),
('PRD-085', 'Pasir Bentonite Unscented', 30000.00, 50, NULL, 'KAT-003'),
('PRD-086', 'Mainan Tikus Kucing', 15000.00, 60, NULL, 'KAT-004'),
('PRD-087', 'Vitamin Penambah Nafsu Makan', 55000.00, 20, NULL, 'KAT-005'),
('PRD-088', 'Susu Rendah Laktosa 1L', 45000.00, 15, NULL, 'KAT-006'),
('PRD-089', 'Hairball Remedy Gel', 110000.00, 10, NULL, 'KAT-005'),
('PRD-090', 'Sisir Pembersih Bulu', 30000.00, 25, NULL, 'KAT-007'),
('PRD-091', 'Kandang Besi Lipat XL', 600000.00, 4, NULL, 'KAT-008'),
('PRD-092', 'Tempat Makan Stainless', 25000.00, 60, NULL, 'KAT-009'),
('PRD-093', 'Snack Soft Bites', 22000.00, 39, NULL, 'KAT-010'),
('PRD-094', 'Royal Canin Indoor 2kg', 320000.00, 8, NULL, 'KAT-001'),
('PRD-095', 'Life Cat Kaleng 400g Tuna', 20000.00, 70, NULL, 'KAT-002'),
('PRD-096', 'Pasir Gumpal Wangi Kopi', 40000.00, 30, NULL, 'KAT-003'),
('PRD-097', 'Mainan Terowongan Lipat', 90000.00, 10, NULL, 'KAT-004'),
('PRD-098', 'Multivitamin Kucing Cair', 60000.00, 20, NULL, 'KAT-005'),
('PRD-099', 'Botol Susu 50ml', 20000.00, 40, NULL, 'KAT-006'),
('PRD-100', 'Sikat Mandi Kucing', 15000.00, 50, NULL, 'KAT-007');

--
-- Trigger `produk`
--
DELIMITER $$
CREATE TRIGGER `trg_validasi_produk` BEFORE INSERT ON `produk` FOR EACH ROW BEGIN
IF NEW.stok_tersedia < 1 THEN
SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'ERROR STOK! TIDAK MENCUKUPI';
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` varchar(20) NOT NULL,
  `tanggal_waktu` datetime NOT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `total_belanja` decimal(12,2) NOT NULL,
  `id_kasir` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tanggal_waktu`, `metode_pembayaran`, `total_belanja`, `id_kasir`) VALUES
('TRX-231101-001', '2023-11-01 08:10:00', 'Cash', 75000.00, 'KSR-001'),
('TRX-231101-002', '2023-11-01 09:15:00', 'QRIS', 120000.00, 'KSR-001'),
('TRX-231101-003', '2023-11-01 10:20:00', 'Debit', 95000.00, 'KSR-001'),
('TRX-231101-004', '2023-11-01 11:30:00', 'Cash', 60000.00, 'KSR-001'),
('TRX-231101-005', '2023-11-01 13:00:00', 'QRIS', 180000.00, 'KSR-001'),
('TRX-231101-006', '2023-11-01 15:10:00', 'Debit', 220000.00, 'KSR-002'),
('TRX-231101-007', '2023-11-01 16:20:00', 'Cash', 85000.00, 'KSR-002'),
('TRX-231101-008', '2023-11-01 17:30:00', 'QRIS', 140000.00, 'KSR-002'),
('TRX-231101-009', '2023-11-01 18:45:00', 'Kartu Kredit', 300000.00, 'KSR-002'),
('TRX-231101-010', '2023-11-01 20:00:00', 'Cash', 90000.00, 'KSR-002'),
('TRX-231102-011', '2023-11-02 08:05:00', 'Cash', 65000.00, 'KSR-001'),
('TRX-231102-012', '2023-11-02 09:25:00', 'QRIS', 110000.00, 'KSR-001'),
('TRX-231102-013', '2023-11-02 10:40:00', 'Debit', 130000.00, 'KSR-001'),
('TRX-231102-014', '2023-11-02 11:50:00', 'Cash', 70000.00, 'KSR-001'),
('TRX-231102-015', '2023-11-02 13:10:00', 'QRIS', 200000.00, 'KSR-001'),
('TRX-231102-016', '2023-11-02 15:15:00', 'Debit', 175000.00, 'KSR-002'),
('TRX-231102-017', '2023-11-02 16:30:00', 'Cash', 80000.00, 'KSR-002'),
('TRX-231102-018', '2023-11-02 17:45:00', 'QRIS', 150000.00, 'KSR-002'),
('TRX-231102-019', '2023-11-02 18:55:00', 'Kartu Kredit', 280000.00, 'KSR-002'),
('TRX-231102-020', '2023-11-02 20:10:00', 'Cash', 95000.00, 'KSR-002'),
('TRX-231103-021', '2023-11-03 08:00:00', 'Cash', 70000.00, 'KSR-001'),
('TRX-231103-022', '2023-11-03 09:10:00', 'QRIS', 115000.00, 'KSR-001'),
('TRX-231103-023', '2023-11-03 10:25:00', 'Debit', 125000.00, 'KSR-001'),
('TRX-231103-024', '2023-11-03 11:35:00', 'Cash', 68000.00, 'KSR-001'),
('TRX-231103-025', '2023-11-03 13:05:00', 'QRIS', 210000.00, 'KSR-001'),
('TRX-231103-026', '2023-11-03 15:20:00', 'Debit', 185000.00, 'KSR-002'),
('TRX-231103-027', '2023-11-03 16:35:00', 'Cash', 82000.00, 'KSR-002'),
('TRX-231103-028', '2023-11-03 17:50:00', 'QRIS', 145000.00, 'KSR-002'),
('TRX-231103-029', '2023-11-03 19:00:00', 'Kartu Kredit', 320000.00, 'KSR-002'),
('TRX-231103-030', '2023-11-03 20:15:00', 'Cash', 100000.00, 'KSR-002'),
('TRX-231104-031', '2023-11-04 08:10:00', 'Cash', 72000.00, 'KSR-001'),
('TRX-231104-032', '2023-11-04 09:20:00', 'QRIS', 118000.00, 'KSR-001'),
('TRX-231104-033', '2023-11-04 10:30:00', 'Debit', 140000.00, 'KSR-001'),
('TRX-231104-034', '2023-11-04 11:45:00', 'Cash', 65000.00, 'KSR-001'),
('TRX-231104-035', '2023-11-04 13:15:00', 'QRIS', 205000.00, 'KSR-001'),
('TRX-231104-036', '2023-11-04 15:25:00', 'Debit', 190000.00, 'KSR-002'),
('TRX-231104-037', '2023-11-04 16:40:00', 'Cash', 87000.00, 'KSR-002'),
('TRX-231104-038', '2023-11-04 17:55:00', 'QRIS', 155000.00, 'KSR-002'),
('TRX-231104-039', '2023-11-04 19:10:00', 'Kartu Kredit', 310000.00, 'KSR-002'),
('TRX-231104-040', '2023-11-04 20:20:00', 'Cash', 98000.00, 'KSR-002'),
('TRX-231105-041', '2023-11-05 08:00:00', 'Cash', 68000.00, 'KSR-001'),
('TRX-231105-042', '2023-11-05 09:15:00', 'QRIS', 125000.00, 'KSR-001'),
('TRX-231105-043', '2023-11-05 10:25:00', 'Debit', 135000.00, 'KSR-001'),
('TRX-231105-044', '2023-11-05 11:40:00', 'Cash', 70000.00, 'KSR-001'),
('TRX-231105-045', '2023-11-05 13:00:00', 'QRIS', 220000.00, 'KSR-001'),
('TRX-231105-046', '2023-11-05 15:15:00', 'Debit', 200000.00, 'KSR-002'),
('TRX-231105-047', '2023-11-05 16:30:00', 'Cash', 85000.00, 'KSR-002'),
('TRX-231105-048', '2023-11-05 17:45:00', 'QRIS', 165000.00, 'KSR-002'),
('TRX-231105-049', '2023-11-05 19:00:00', 'Kartu Kredit', 330000.00, 'KSR-002'),
('TRX-231105-050', '2023-11-05 20:15:00', 'Cash', 102000.00, 'KSR-002'),
('TRX-231106-051', '2023-11-06 08:05:00', 'Cash', 75000.00, 'KSR-001'),
('TRX-231106-052', '2023-11-06 09:20:00', 'QRIS', 130000.00, 'KSR-001'),
('TRX-231106-053', '2023-11-06 10:35:00', 'Debit', 145000.00, 'KSR-001'),
('TRX-231106-054', '2023-11-06 11:50:00', 'Cash', 72000.00, 'KSR-001'),
('TRX-231106-055', '2023-11-06 13:10:00', 'QRIS', 215000.00, 'KSR-001'),
('TRX-231106-056', '2023-11-06 15:20:00', 'Debit', 210000.00, 'KSR-002'),
('TRX-231106-057', '2023-11-06 16:35:00', 'Cash', 90000.00, 'KSR-002'),
('TRX-231106-058', '2023-11-06 17:50:00', 'QRIS', 170000.00, 'KSR-002'),
('TRX-231106-059', '2023-11-06 19:05:00', 'Kartu Kredit', 340000.00, 'KSR-002'),
('TRX-231106-060', '2023-11-06 20:20:00', 'Cash', 110000.00, 'KSR-002'),
('TRX-231107-061', '2023-11-07 08:00:00', 'Cash', 70000.00, 'KSR-001'),
('TRX-231107-062', '2023-11-07 09:15:00', 'QRIS', 120000.00, 'KSR-001'),
('TRX-231107-063', '2023-11-07 10:30:00', 'Debit', 150000.00, 'KSR-001'),
('TRX-231107-064', '2023-11-07 11:45:00', 'Cash', 68000.00, 'KSR-001'),
('TRX-231107-065', '2023-11-07 13:00:00', 'QRIS', 225000.00, 'KSR-001'),
('TRX-231107-066', '2023-11-07 15:10:00', 'Debit', 220000.00, 'KSR-002'),
('TRX-231107-067', '2023-11-07 16:25:00', 'Cash', 87000.00, 'KSR-002'),
('TRX-231107-068', '2023-11-07 17:40:00', 'QRIS', 180000.00, 'KSR-002'),
('TRX-231107-069', '2023-11-07 18:55:00', 'Kartu Kredit', 350000.00, 'KSR-002'),
('TRX-231107-070', '2023-11-07 20:10:00', 'Cash', 115000.00, 'KSR-002'),
('TRX-231108-071', '2023-11-08 08:10:00', 'Cash', 73000.00, 'KSR-001'),
('TRX-231108-072', '2023-11-08 09:25:00', 'QRIS', 135000.00, 'KSR-001'),
('TRX-231108-073', '2023-11-08 10:40:00', 'Debit', 160000.00, 'KSR-001'),
('TRX-231108-074', '2023-11-08 11:55:00', 'Cash', 75000.00, 'KSR-001'),
('TRX-231108-075', '2023-11-08 13:10:00', 'QRIS', 230000.00, 'KSR-001'),
('TRX-231108-076', '2023-11-08 15:20:00', 'Debit', 225000.00, 'KSR-002'),
('TRX-231108-077', '2023-11-08 16:35:00', 'Cash', 92000.00, 'KSR-002'),
('TRX-231108-078', '2023-11-08 17:50:00', 'QRIS', 185000.00, 'KSR-002'),
('TRX-231108-079', '2023-11-08 19:05:00', 'Kartu Kredit', 360000.00, 'KSR-002'),
('TRX-231108-080', '2023-11-08 20:20:00', 'Cash', 120000.00, 'KSR-002'),
('TRX-231109-081', '2023-11-09 08:00:00', 'Cash', 75000.00, 'KSR-001'),
('TRX-231109-082', '2023-11-09 09:10:00', 'QRIS', 140000.00, 'KSR-001'),
('TRX-231109-083', '2023-11-09 10:25:00', 'Debit', 165000.00, 'KSR-001'),
('TRX-231109-084', '2023-11-09 11:40:00', 'Cash', 80000.00, 'KSR-001'),
('TRX-231109-085', '2023-11-09 13:00:00', 'QRIS', 240000.00, 'KSR-001'),
('TRX-231109-086', '2023-11-09 15:10:00', 'Debit', 230000.00, 'KSR-002'),
('TRX-231109-087', '2023-11-09 16:25:00', 'Cash', 95000.00, 'KSR-002'),
('TRX-231109-088', '2023-11-09 17:40:00', 'QRIS', 190000.00, 'KSR-002'),
('TRX-231109-089', '2023-11-09 18:55:00', 'Kartu Kredit', 370000.00, 'KSR-002'),
('TRX-231109-090', '2023-11-09 20:10:00', 'Cash', 125000.00, 'KSR-002'),
('TRX-231110-091', '2023-11-10 08:10:00', 'Cash', 80000.00, 'KSR-001'),
('TRX-231110-092', '2023-11-10 09:25:00', 'QRIS', 145000.00, 'KSR-001'),
('TRX-231110-093', '2023-11-10 10:40:00', 'Debit', 170000.00, 'KSR-001'),
('TRX-231110-094', '2023-11-10 11:55:00', 'Cash', 82000.00, 'KSR-001'),
('TRX-231110-095', '2023-11-10 13:10:00', 'QRIS', 250000.00, 'KSR-001'),
('TRX-231110-096', '2023-11-10 15:20:00', 'Debit', 240000.00, 'KSR-002'),
('TRX-231110-097', '2023-11-10 16:35:00', 'Cash', 100000.00, 'KSR-002'),
('TRX-231110-098', '2023-11-10 17:50:00', 'QRIS', 200000.00, 'KSR-002'),
('TRX-231110-099', '2023-11-10 19:05:00', 'Kartu Kredit', 380000.00, 'KSR-002'),
('TRX-231110-100', '2023-11-10 20:20:00', 'Cash', 130000.00, 'KSR-002'),
('TRX-260531-101', '2026-05-31 08:17:52', 'Kartu Kredit', 162000.00, 'KSR-001'),
('TRX-260531-102', '2026-05-31 09:33:40', 'QRIS', 1800000.00, 'KSR-001');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `akun_petshop`
--
ALTER TABLE `akun_petshop`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail_transaksi`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `kasir`
--
ALTER TABLE `kasir`
  ADD PRIMARY KEY (`id_kasir`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `idx_harga` (`harga_jual`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_kasir` (`id_kasir`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `akun_petshop`
--
ALTER TABLE `akun_petshop`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_kasir`) REFERENCES `kasir` (`id_kasir`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
