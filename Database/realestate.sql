-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 12 May 2025, 11:50:59
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `realestate`
--

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `aktif_ilanlar`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `aktif_ilanlar` (
`id` int(11)
,`başlık` varchar(255)
,`açıklama` text
,`fiyat` decimal(10,0)
,`şehir` varchar(100)
,`ilçe` varchar(100)
,`tür` enum('Daire','Villa','Arsa','Dükkan','Müstakil Ev')
,`durum` enum('Satılık','Kiralık','Satıldı/Kiralandı')
,`odasayısı` varchar(10)
,`metrekare` int(11)
,`emlakci_adi` varchar(50)
,`emlakci_email` varchar(50)
,`emlakci_tel` varchar(11)
);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bildirimler`
--

CREATE TABLE `bildirimler` (
  `id` int(11) NOT NULL,
  `ilan_id` int(11) DEFAULT NULL,
  `emlakci_id` int(11) DEFAULT NULL,
  `musteri_id` int(11) DEFAULT NULL,
  `mesaj` text NOT NULL,
  `tarih` datetime NOT NULL,
  `okundu` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `bildirimler`
--

INSERT INTO `bildirimler` (`id`, `ilan_id`, `emlakci_id`, `musteri_id`, `mesaj`, `tarih`, `okundu`) VALUES
(1, 16, 2, 22, 'Satış işlemi gerçekleşti!', '2025-05-12 09:53:16', 0),
(2, 16, 2, 22, 'Satış işlemi gerçekleşti!', '2025-05-12 09:58:13', 0),
(3, 16, 2, 22, 'Satış işlemi gerçekleşti!', '2025-05-12 09:58:36', 0),
(4, 16, 2, 22, 'Satış işlemi gerçekleşti! Arsa - 750000 TL', '2025-05-12 10:04:06', 0),
(5, 5, 1, 22, 'Satış işlemi gerçekleşti! İşyeri Dükkan - 18000 TL', '2025-05-12 10:31:06', 0),
(6, 21, 2, 23, 'Satış işlemi gerçekleşti! Arsa - 400000 TL', '2025-05-12 11:55:19', 0);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `emlakci_istatistikleri`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `emlakci_istatistikleri` (
`emlakci_id` int(11)
,`emlakci_adi` varchar(50)
,`toplam_mulk` bigint(21)
,`satilik_mulk` bigint(21)
,`kiralik_mulk` bigint(21)
,`toplam_randevu` bigint(21)
,`toplam_teklif` bigint(21)
,`toplam_satis` bigint(21)
);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kullanicilar`
--

CREATE TABLE `kullanicilar` (
  `id` int(11) NOT NULL,
  `kullanici_adi` varchar(50) NOT NULL,
  `parola` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `tel` varchar(11) NOT NULL,
  `kayıt_tarihi` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `kullanicilar`
--

INSERT INTO `kullanicilar` (`id`, `kullanici_adi`, `parola`, `email`, `tel`, `kayıt_tarihi`) VALUES
(1, 'Emir Karaman', '$2y$10$/WbPshevhcQGdosZdwmS2eVTDUGdY8nWHIrcR/eTWmqokvCwwsE0e', '1@gmail.com', '1', '2025-04-29 19:11:05'),
(2, 'Boran Sert', '$2y$10$5Y0zKS/xU6l1hC3dNVsaGuTxme0IDRrioD0cCm1BQ9xM79No5v6A.', '2@gmail.com', '2', '2025-04-29 19:11:15'),
(3, 'Ömer Faruk Sarı', '$2y$10$Qp0EbEi/oCF0dj2xxePbdeTsfWnLjzfj12QjRYS8NeQ8wvixNjGFS', '3@gmail.com', '3', '2025-04-29 19:11:28');

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `musteri_detay`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `musteri_detay` (
`id` int(11)
,`kullanici_adi` varchar(20)
,`email` varchar(20)
,`tel` varchar(20)
,`toplam_randevu` bigint(21)
,`toplam_teklif` bigint(21)
,`toplam_satin_alim` bigint(21)
);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `mülk`
--

CREATE TABLE `mülk` (
  `id` int(11) NOT NULL,
  `emlakçı_id` int(20) NOT NULL,
  `başlık` varchar(255) NOT NULL,
  `açıklama` text NOT NULL,
  `fiyat` decimal(10,0) NOT NULL,
  `odasayısı` varchar(10) NOT NULL,
  `m^2` int(11) NOT NULL,
  `şehir` varchar(100) NOT NULL,
  `ilçe` varchar(100) NOT NULL,
  `tür` enum('Daire','Villa','Arsa','Dükkan','Müstakil Ev') NOT NULL,
  `durum` enum('Satılık','Kiralık','Satıldı/Kiralandı') NOT NULL,
  `aktif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `mülk`
--

INSERT INTO `mülk` (`id`, `emlakçı_id`, `başlık`, `açıklama`, `fiyat`, `odasayısı`, `m^2`, `şehir`, `ilçe`, `tür`, `durum`, `aktif`) VALUES
(2, 1, 'Geniş Daire', 'Merkezde konforlu daire', 1500000, '3', 120, 'İstanbul', 'Kadıköy', 'Daire', 'Satılık', 1),
(3, 2, 'Lüks Villa', 'Havuzlu villa', 8500000, '6', 350, 'Antalya', 'Lara', 'Villa', 'Satılık', 1),
(4, 3, 'Yatırımlık Arsa', 'Ana yola yakın arsa', 500000, '0', 600, 'Ankara', 'Gölbaşı', 'Arsa', 'Satılık', 1),
(5, 1, 'İşyeri Dükkan', 'Cadde üstü kiralık dükkan', 18000, '0', 90, 'İzmir', 'Bornova', 'Dükkan', 'Satıldı/Kiralandı', 0),
(6, 3, 'Şehirden Uzak Müstakil', 'Doğa içinde huzurlu ev', 2200000, '4', 200, 'Muğla', 'Fethiye', 'Müstakil Ev', 'Satılık', 1),
(7, 2, 'Modern Daire', 'Yeni yapılmış modern daire', 1250000, '2', 95, 'Bursa', 'Nilüfer', 'Daire', 'Satılık', 1),
(8, 1, 'Kiralanacak Dükkan', 'Alışveriş merkezine yakın', 9500, '0', 70, 'İstanbul', 'Bakırköy', 'Dükkan', 'Kiralık', 1),
(9, 3, 'Yazlık Villa', 'Denize sıfır lüks villa', 10000000, '5', 400, 'İzmir', 'Çeşme', 'Villa', 'Satılık', 1),
(10, 2, 'İnşaatlık Arsa', 'Yüksek emsalli arsa', 1100000, '0', 700, 'Ankara', 'Etimesgut', 'Arsa', 'Satılık', 1),
(11, 2, 'Merkezde Daire', 'Ulaşımı kolay', 1350000, '3', 110, 'İstanbul', 'Şişli', 'Daire', 'Satılık', 1),
(12, 1, 'Sessiz Müstakil Ev', 'Sessiz mahallede ev', 1900000, '3', 180, 'Sakarya', 'Serdivan', 'Müstakil Ev', 'Satılık', 1),
(13, 2, 'Yeni Kiralık Daire', 'Site içinde daire', 8500, '2', 80, 'Antalya', 'Konyaaltı', 'Daire', 'Kiralık', 1),
(14, 1, 'Depolu Dükkan', 'İmalata uygun', 21000, '0', 130, 'Gaziantep', 'Şahinbey', 'Dükkan', 'Kiralık', 1),
(15, 1, 'Villa', 'Şehir manzaralı villa', 6500000, '4', 280, 'Trabzon', 'Ortahisar', 'Villa', 'Satılık', 1),
(16, 2, 'Arsa', 'Gelişmekte olan bölgede arsa', 750000, '0', 500, 'Eskişehir', 'Tepebaşı', 'Arsa', 'Satıldı/Kiralandı', 0),
(17, 1, 'Kiralık Daire', 'Kampüse yakın daire', 6000, '1', 60, 'Eskişehir', 'Odunpazarı', 'Daire', 'Kiralık', 1),
(18, 2, 'Satılık Villa', 'Geniş bahçeli', 7200000, '5', 320, 'Yalova', 'Termal', 'Villa', 'Satılık', 1),
(19, 3, 'Merkezi Dükkan', 'İşlek caddede', 28000, '0', 150, 'Ankara', 'Kızılay', 'Dükkan', 'Kiralık', 1),
(20, 3, 'Yatırımlık Daire', 'Yüksek kira getirisi', 980000, '2', 85, 'Kocaeli', 'Gebze', 'Daire', 'Satılık', 1),
(21, 2, 'Arsa', 'Tarla vasıflı ama imar yakında', 400000, '0', 1000, 'Balıkesir', 'Edremit', 'Arsa', 'Satıldı/Kiralandı', 0),
(22, 2, 'Kiralık Ev', 'Sakin sokakta müstakil ev', 7500, '3', 150, 'Konya', 'Selçuklu', 'Müstakil Ev', 'Kiralık', 1),
(23, 1, 'Dükkan', 'Pazar yeri karşısı', 16500, '0', 100, 'Adana', 'Seyhan', 'Dükkan', 'Kiralık', 1),
(24, 1, 'Villa', 'Şehir dışı lüks villa', 5800000, '4', 300, 'Denizli', 'Pamukkale', 'Villa', 'Satılık', 1),
(25, 3, 'Arsa', 'Organize sanayi yanında', 1300000, '0', 800, 'Kayseri', 'Melikgazi', 'Arsa', 'Satılık', 1),
(26, 2, 'Daire', 'Uygun fiyatlı aile evi', 890000, '3', 100, 'Mersin', 'Yenişehir', 'Daire', 'Satılık', 1),
(27, 1, 'Yazlık Ev', 'Şehire çokda uzak olmayan kafa dinlemelik ev', 2500000, '5', 220, 'Erzincan', 'Merkez', 'Müstakil Ev', 'Satılık', 1),
(29, 1, 'ömerin yeni evi', 'ömerin yan dairesi', 15000, '2', 100, 'Konya', 'Merkez', 'Daire', 'Kiralık', 1);

--
-- Tetikleyiciler `mülk`
--
DELIMITER $$
CREATE TRIGGER `mulk_silme_kontrol` BEFORE DELETE ON `mülk` FOR EACH ROW BEGIN
    -- İlgili randevuları sil
    DELETE FROM randevu WHERE ilan_id = OLD.id;
    
    -- İlgili teklifleri sil
    DELETE FROM teklif WHERE ilan_id = OLD.id;
    
    -- İlgili satın alımları sil
    DELETE FROM satın_alım WHERE ilan_id = OLD.id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `müşteri`
--

CREATE TABLE `müşteri` (
  `id` int(11) NOT NULL,
  `kullanici_adi` varchar(20) NOT NULL,
  `parola` varchar(20) NOT NULL,
  `email` varchar(20) NOT NULL,
  `tel` varchar(20) NOT NULL,
  `kayit_tarihi` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `müşteri`
--

INSERT INTO `müşteri` (`id`, `kullanici_adi`, `parola`, `email`, `tel`, `kayit_tarihi`) VALUES
(1, 'qaz', '123', 'dsfasf@gmail.com', '65416514', '2025-05-07 09:25:24'),
(2, 'test', 'test123', '', '', '2025-05-07 09:25:25'),
(3, 'wsx', '123', 'asdfsdaf@gmail.com', '6515616', '2025-05-07 09:25:36'),
(21, 'edc', '123', 'dsfdsafasf@gmail.com', '56156232', '2025-05-07 09:26:01'),
(22, '12345', '12345', 'saasfasf@gmail.com', '12345', '2025-05-12 09:53:03'),
(23, 'rıdvan', '123', 'sadffasd@gmail.com', '15615615', '2025-05-12 11:55:05');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `randevu`
--

CREATE TABLE `randevu` (
  `id` int(11) NOT NULL,
  `ilan_id` int(11) NOT NULL,
  `müşteri_id` int(11) NOT NULL,
  `emlakçı_id` int(11) NOT NULL,
  `randevu_saat` time NOT NULL,
  `randevu_tarihi` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `randevu`
--

INSERT INTO `randevu` (`id`, `ilan_id`, `müşteri_id`, `emlakçı_id`, `randevu_saat`, `randevu_tarihi`) VALUES
(9, 4, 1, 3, '12:23:00', '2025-08-16'),
(10, 6, 1, 3, '16:15:00', '2025-10-05'),
(11, 7, 3, 2, '20:25:00', '2025-05-25'),
(12, 2, 2, 1, '12:23:00', '2025-03-12'),
(13, 4, 1, 3, '12:33:00', '2026-02-18');

--
-- Tetikleyiciler `randevu`
--
DELIMITER $$
CREATE TRIGGER `yeni_randevu_bildirim` AFTER INSERT ON `randevu` FOR EACH ROW BEGIN
    -- Bildirimler tablosu yoksa, bu kısmı uygun şekilde değiştirin veya tabloyu oluşturun
    INSERT INTO bildirimler (ilan_id, emlakci_id, musteri_id, mesaj, tarih, okundu)
    VALUES (
        NEW.ilan_id, 
        NEW.emlakçı_id, 
        NEW.müşteri_id, 
        CONCAT('Yeni randevu oluşturuldu: ', DATE_FORMAT(NEW.randevu_tarihi, '%d.%m.%Y'), ' ', TIME_FORMAT(NEW.randevu_saat, '%H:%i')),
        NOW(),
        0
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `satın_alım`
--

CREATE TABLE `satın_alım` (
  `id` int(11) NOT NULL,
  `ilan_id` int(11) NOT NULL,
  `müşteri_id` int(11) NOT NULL,
  `emlakçı_id` int(11) NOT NULL,
  `alım_tarihi` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `satın_alım`
--

INSERT INTO `satın_alım` (`id`, `ilan_id`, `müşteri_id`, `emlakçı_id`, `alım_tarihi`) VALUES
(10, 16, 22, 2, '2025-05-12 10:04:06'),
(11, 5, 22, 1, '2025-05-12 10:31:06'),
(12, 21, 23, 2, '2025-05-12 11:55:19');

--
-- Tetikleyiciler `satın_alım`
--
DELIMITER $$
CREATE TRIGGER `satis_gerceklesti` AFTER INSERT ON `satın_alım` FOR EACH ROW BEGIN
    -- Mülkün durumunu "Satıldı/Kiralandı" olarak güncelle
    UPDATE mülk 
    SET 
        durum = 'Satıldı/Kiralandı',
        aktif = CASE 
                  WHEN durum = 'Satıldı/Kiralandı' THEN 0 
                  ELSE 1 
                END
    WHERE id = NEW.ilan_id;
    
    -- Bildirim ekle
    INSERT INTO bildirimler (ilan_id, emlakci_id, musteri_id, mesaj, tarih, okundu)
    VALUES (
        NEW.ilan_id, 
        NEW.emlakçı_id, 
        NEW.müşteri_id, 
        CONCAT('Satış işlemi gerçekleşti! ', 
               (SELECT başlık FROM mülk WHERE id = NEW.ilan_id), 
               ' - ', 
               (SELECT fiyat FROM mülk WHERE id = NEW.ilan_id), 
               ' TL'),
        NOW(),
        0
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `teklif`
--

CREATE TABLE `teklif` (
  `id` int(11) NOT NULL,
  `ilan_id` int(11) NOT NULL,
  `müşteri_id` int(11) NOT NULL,
  `emlakçı_id` int(11) NOT NULL,
  `Teklif` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `teklif`
--

INSERT INTO `teklif` (`id`, `ilan_id`, `müşteri_id`, `emlakçı_id`, `Teklif`) VALUES
(9, 5, 21, 1, 16000),
(10, 6, 1, 3, 2000000),
(11, 7, 3, 2, 1100000),
(12, 15, 21, 1, 6000000),
(13, 20, 21, 3, 900000);

--
-- Tetikleyiciler `teklif`
--
DELIMITER $$
CREATE TRIGGER `yeni_teklif_bildirim` AFTER INSERT ON `teklif` FOR EACH ROW BEGIN
    -- Bildirimler tablosu yoksa, bu kısmı uygun şekilde değiştirin veya tabloyu oluşturun
    INSERT INTO bildirimler (ilan_id, emlakci_id, musteri_id, mesaj, tarih, okundu)
    VALUES (
        NEW.ilan_id, 
        NEW.emlakçı_id, 
        NEW.müşteri_id, 
        CONCAT('Yeni teklif geldi: ', FORMAT(NEW.Teklif, 0), ' TL'),
        NOW(),
        0
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `yaklasan_randevular`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `yaklasan_randevular` (
`id` int(11)
,`randevu_tarihi` date
,`randevu_saat` time
,`mulk_adi` varchar(255)
,`şehir` varchar(100)
,`ilçe` varchar(100)
,`fiyat` decimal(10,0)
,`musteri_adi` varchar(20)
,`musteri_email` varchar(20)
,`musteri_tel` varchar(20)
,`emlakci_adi` varchar(50)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı `aktif_ilanlar`
--
DROP TABLE IF EXISTS `aktif_ilanlar`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `aktif_ilanlar`  AS SELECT `m`.`id` AS `id`, `m`.`başlık` AS `başlık`, `m`.`açıklama` AS `açıklama`, `m`.`fiyat` AS `fiyat`, `m`.`şehir` AS `şehir`, `m`.`ilçe` AS `ilçe`, `m`.`tür` AS `tür`, `m`.`durum` AS `durum`, `m`.`odasayısı` AS `odasayısı`, `m`.`m^2` AS `metrekare`, `k`.`kullanici_adi` AS `emlakci_adi`, `k`.`email` AS `emlakci_email`, `k`.`tel` AS `emlakci_tel` FROM (`mülk` `m` left join `kullanicilar` `k` on(`m`.`emlakçı_id` = `k`.`id`)) WHERE `m`.`aktif` = 1 ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `emlakci_istatistikleri`
--
DROP TABLE IF EXISTS `emlakci_istatistikleri`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `emlakci_istatistikleri`  AS SELECT `k`.`id` AS `emlakci_id`, `k`.`kullanici_adi` AS `emlakci_adi`, (select count(0) from `mülk` where `mülk`.`emlakçı_id` = `k`.`id`) AS `toplam_mulk`, (select count(0) from `mülk` where `mülk`.`emlakçı_id` = `k`.`id` and `mülk`.`durum` = 'satılık') AS `satilik_mulk`, (select count(0) from `mülk` where `mülk`.`emlakçı_id` = `k`.`id` and `mülk`.`durum` = 'kiralık') AS `kiralik_mulk`, (select count(0) from `randevu` where `randevu`.`emlakçı_id` = `k`.`id`) AS `toplam_randevu`, (select count(0) from `teklif` where `teklif`.`emlakçı_id` = `k`.`id`) AS `toplam_teklif`, (select count(0) from `satın_alım` where `satın_alım`.`emlakçı_id` = `k`.`id`) AS `toplam_satis` FROM `kullanicilar` AS `k` ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `musteri_detay`
--
DROP TABLE IF EXISTS `musteri_detay`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `musteri_detay`  AS SELECT `m`.`id` AS `id`, `m`.`kullanici_adi` AS `kullanici_adi`, `m`.`email` AS `email`, `m`.`tel` AS `tel`, count(distinct `r`.`id`) AS `toplam_randevu`, count(distinct `t`.`id`) AS `toplam_teklif`, count(distinct `s`.`id`) AS `toplam_satin_alim` FROM (((`müşteri` `m` left join `randevu` `r` on(`m`.`id` = `r`.`müşteri_id`)) left join `teklif` `t` on(`m`.`id` = `t`.`müşteri_id`)) left join `satın_alım` `s` on(`m`.`id` = `s`.`müşteri_id`)) GROUP BY `m`.`id`, `m`.`kullanici_adi`, `m`.`email`, `m`.`tel` ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `yaklasan_randevular`
--
DROP TABLE IF EXISTS `yaklasan_randevular`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `yaklasan_randevular`  AS SELECT `r`.`id` AS `id`, `r`.`randevu_tarihi` AS `randevu_tarihi`, `r`.`randevu_saat` AS `randevu_saat`, `m`.`başlık` AS `mulk_adi`, `m`.`şehir` AS `şehir`, `m`.`ilçe` AS `ilçe`, `m`.`fiyat` AS `fiyat`, `mu`.`kullanici_adi` AS `musteri_adi`, `mu`.`email` AS `musteri_email`, `mu`.`tel` AS `musteri_tel`, `k`.`kullanici_adi` AS `emlakci_adi` FROM (((`randevu` `r` left join `mülk` `m` on(`r`.`ilan_id` = `m`.`id`)) left join `müşteri` `mu` on(`r`.`müşteri_id` = `mu`.`id`)) left join `kullanicilar` `k` on(`r`.`emlakçı_id` = `k`.`id`)) WHERE `r`.`randevu_tarihi` >= curdate() ORDER BY `r`.`randevu_tarihi` ASC, `r`.`randevu_saat` ASC ;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `bildirimler`
--
ALTER TABLE `bildirimler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ilan_id` (`ilan_id`),
  ADD KEY `emlakci_id` (`emlakci_id`),
  ADD KEY `musteri_id` (`musteri_id`);

--
-- Tablo için indeksler `kullanicilar`
--
ALTER TABLE `kullanicilar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kullanıcı_adı` (`kullanici_adi`);

--
-- Tablo için indeksler `mülk`
--
ALTER TABLE `mülk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mulk_durum` (`durum`),
  ADD KEY `idx_mulk_sehir_ilce` (`şehir`,`ilçe`),
  ADD KEY `idx_mulk_fiyat` (`fiyat`),
  ADD KEY `idx_mulk_emlakci` (`emlakçı_id`);

--
-- Tablo için indeksler `müşteri`
--
ALTER TABLE `müşteri`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_musteri_kullanici_adi` (`kullanici_adi`);

--
-- Tablo için indeksler `randevu`
--
ALTER TABLE `randevu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `randevu` (`ilan_id`),
  ADD KEY `idx_randevu_tarih` (`randevu_tarihi`,`randevu_saat`),
  ADD KEY `idx_randevu_musteri` (`müşteri_id`),
  ADD KEY `idx_randevu_emlakci` (`emlakçı_id`),
  ADD KEY `idx_randevu_ilan` (`ilan_id`);

--
-- Tablo için indeksler `satın_alım`
--
ALTER TABLE `satın_alım`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emlakçı_id` (`emlakçı_id`),
  ADD KEY `müşteri_id` (`müşteri_id`),
  ADD KEY `idx_satin_alim_tarih` (`alım_tarihi`),
  ADD KEY `idx_satin_alim_ilan` (`ilan_id`);

--
-- Tablo için indeksler `teklif`
--
ALTER TABLE `teklif`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ilan_id` (`ilan_id`),
  ADD KEY `idx_teklif_ilan` (`ilan_id`),
  ADD KEY `idx_teklif_musteri` (`müşteri_id`),
  ADD KEY `idx_teklif_emlakci` (`emlakçı_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `bildirimler`
--
ALTER TABLE `bildirimler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `kullanicilar`
--
ALTER TABLE `kullanicilar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Tablo için AUTO_INCREMENT değeri `mülk`
--
ALTER TABLE `mülk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Tablo için AUTO_INCREMENT değeri `müşteri`
--
ALTER TABLE `müşteri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Tablo için AUTO_INCREMENT değeri `randevu`
--
ALTER TABLE `randevu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Tablo için AUTO_INCREMENT değeri `satın_alım`
--
ALTER TABLE `satın_alım`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `teklif`
--
ALTER TABLE `teklif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `bildirimler`
--
ALTER TABLE `bildirimler`
  ADD CONSTRAINT `bildirimler_ibfk_1` FOREIGN KEY (`ilan_id`) REFERENCES `mülk` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bildirimler_ibfk_2` FOREIGN KEY (`emlakci_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bildirimler_ibfk_3` FOREIGN KEY (`musteri_id`) REFERENCES `müşteri` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `mülk`
--
ALTER TABLE `mülk`
  ADD CONSTRAINT `mülk_ibfk_1` FOREIGN KEY (`emlakçı_id`) REFERENCES `kullanicilar` (`id`);

--
-- Tablo kısıtlamaları `randevu`
--
ALTER TABLE `randevu`
  ADD CONSTRAINT `randevu` FOREIGN KEY (`ilan_id`) REFERENCES `mülk` (`id`),
  ADD CONSTRAINT `randevu_ibfk_1` FOREIGN KEY (`müşteri_id`) REFERENCES `müşteri` (`id`),
  ADD CONSTRAINT `randevu_ibfk_2` FOREIGN KEY (`müşteri_id`) REFERENCES `kullanicilar` (`id`);

--
-- Tablo kısıtlamaları `satın_alım`
--
ALTER TABLE `satın_alım`
  ADD CONSTRAINT `satın_alım_ibfk_1` FOREIGN KEY (`emlakçı_id`) REFERENCES `kullanicilar` (`id`),
  ADD CONSTRAINT `satın_alım_ibfk_2` FOREIGN KEY (`ilan_id`) REFERENCES `mülk` (`id`),
  ADD CONSTRAINT `satın_alım_ibfk_3` FOREIGN KEY (`müşteri_id`) REFERENCES `müşteri` (`id`);

--
-- Tablo kısıtlamaları `teklif`
--
ALTER TABLE `teklif`
  ADD CONSTRAINT `teklif_ibfk_1` FOREIGN KEY (`ilan_id`) REFERENCES `mülk` (`id`),
  ADD CONSTRAINT `teklif_ibfk_2` FOREIGN KEY (`müşteri_id`) REFERENCES `müşteri` (`id`),
  ADD CONSTRAINT `teklif_ibfk_3` FOREIGN KEY (`emlakçı_id`) REFERENCES `kullanicilar` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
