SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
START TRANSACTION;
SET time_zone = '+00:00';

-- PRIMARY KEYler otomatik INDEX kabul ediliyor.

-- Emlakçı Tablosu
CREATE TABLE `kullanıcılar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kullanıcı_adı` varchar(50) NOT NULL UNIQUE,
  `parola` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `tel` varchar(11) NOT NULL,
  `kayıt_tarihi` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Müşteri Tablosu
CREATE TABLE `müşteri` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kullanıcı_adı` varchar(20) NOT NULL,
  `parola` varchar(20) NOT NULL,
  `email` varchar(20) NOT NULL,
  `tel` varchar(20) NOT NULL,
  `kayıt_tarihi` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Mülk Tablosu
CREATE TABLE `mülk` (
  `id` int NOT NULL AUTO_INCREMENT,
  `emlakçı_id` int NOT NULL,
  `başlık` varchar(255) NOT NULL,
  `açıklama` text NOT NULL,
  `fiyat` decimal(10,0) NOT NULL,
  `odasayısı` varchar(10) NOT NULL,
  `m^2` int NOT NULL,
  `şehir` varchar(100) NOT NULL,
  `ilçe` varchar(100) NOT NULL,
  `tür` enum('Daire','Villa','Arsa','Dükkan','Müstakil Ev') NOT NULL,
  `durum` enum('Satılık','Kiralık','Satıldı/Kiralandı') NOT NULL,
  `aktif` tinyint DEFAULT 1,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`emlakçı_id`) REFERENCES `kullanıcılar` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Teklif Tablosu
CREATE TABLE `teklif` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ilan_id` int NOT NULL,
  `müşteri_id` int NOT NULL,
  `emlakçı_id` int NOT NULL,
  `teklif` decimal(10,0) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`ilan_id`) REFERENCES `mülk` (`id`),
  FOREIGN KEY (`müşteri_id`) REFERENCES `müşteri` (`id`),
  FOREIGN KEY (`emlakçı_id`) REFERENCES `kullanıcılar` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Satın Alım Tablosu
CREATE TABLE `satın_alım` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ilan_id` int NOT NULL,
  `müşteri_id` int NOT NULL,
  `emlakçı_id` int NOT NULL,
  `alım_tarihi` datetime NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`ilan_id`) REFERENCES `mülk` (`id`),
  FOREIGN KEY (`müşteri_id`) REFERENCES `müşteri` (`id`),
  FOREIGN KEY (`emlakçı_id`) REFERENCES `kullanıcılar` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Randevu Tablosu
CREATE TABLE `randevu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ilan_id` int NOT NULL,
  `müşteri_id` int NOT NULL,
  `emlakçı_id` int NOT NULL,
  `randevu_saat` time NOT NULL,
  `randevu_tarihi` date NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`ilan_id`) REFERENCES `mülk` (`id`),
  FOREIGN KEY (`müşteri_id`) REFERENCES `müşteri` (`id`),
  FOREIGN KEY (`emlakçı_id`) REFERENCES `kullanıcılar` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- İlan View
CREATE VIEW `aktif_ilanlar` AS SELECT
  m.`id`,
  m.`başlık`,
  m.`açıklama`,
  m.`fiyat`,
  m.`şehir`,
  m.`ilçe`,
  m.`tür`,
  m.`durum`,
  m.`odasayısı`,
  m.`m^2` AS `metrekare`,
  k.`kullanıcı_adı` AS `emlakçı_adi`,
  k.`email` AS `emlakçı_email`,
  k.`tel` AS `emlakçı_tel`
FROM `mülk` m
JOIN `kullanıcılar` k ON m.`emlakçı_id` = k.`id`
WHERE m.`aktif` = 1;

-- Emlakçı İstatistik View
CREATE VIEW `emlakçı_istatistikleri` AS SELECT
  k.`id` AS `emlakçı_id`,
  k.`kullanıcı_adı` AS `emlakçı_adı`,
  (SELECT COUNT(*) FROM `mülk` WHERE `emlakçı_id` = k.`id`) AS `toplam_mülk`,
  (SELECT COUNT(*) FROM `mülk` WHERE `emlakçı_id` = k.`id` AND `durum` = 'Satılık') AS `satılık_mülk`,
  (SELECT COUNT(*) FROM `mülk` WHERE `emlakçı_id` = k.`id` AND `durum` = 'Kiralık') AS `kiralık_mülk`,
  (SELECT COUNT(*) FROM `randevu` WHERE `emlakçı_id` = k.`id`) AS `toplam_randevu`,
  (SELECT COUNT(*) FROM `teklif` WHERE `emlakçı_id` = k.`id`) AS `toplam_teklif`,
  (SELECT COUNT(*) FROM `satın_alım` WHERE `emlakçı_id` = k.`id`) AS `toplam_satış`
FROM `kullanıcılar` k;

-- Satış Trigger
DELIMITER $$
CREATE TRIGGER `satis_gerceklesti` 
AFTER INSERT ON `satın_alım` FOR EACH ROW 
BEGIN
    -- Mülkün durumunu "Satıldı/Kiralandı" olarak günceller
    UPDATE mülk 
    SET 
        durum = 'Satıldı/Kiralandı',
        aktif = CASE 
                  WHEN durum = 'Satıldı/Kiralandı' THEN 0 
                  ELSE 1 
                END
    WHERE id = NEW.ilan_id;
END
$$
DELIMITER ;

-- Default Değerler
INSERT INTO `kullanıcılar` VALUES
(101, 'Emir Karaman', '$2y$10$/WbPshevhcQGdosZdwmS2eVTDUGdY8nWHIrcR/eTWmqokvCwwsE0e', '1@gmail.com', '1', '2025-04-29 19:11:05'),
(102, 'Boran Sert', '$2y$10$5Y0zKS/xU6l1hC3dNVsaGuTxme0IDRrioD0cCm1BQ9xM79No5v6A.', '2@gmail.com', '2', '2025-04-29 19:11:15'),
(103, 'Ömer Faruk Sarı', '$2y$10$Qp0EbEi/oCF0dj2xxePbdeTsfWnLjzfj12QjRYS8NeQ8wvixNjGFS', '3@gmail.com', '3', '2025-04-29 19:11:28');

INSERT INTO `müşteri` (`id`, `kullanıcı_adı`, `parola`, `email`, `tel`, `kayıt_tarihi`) VALUES
(101, 'qaz', '123', 'dsfasf@gmail.com', '65416514', '2025-05-07 09:25:24'),
(102, 'test', 'test123', '', '', '2025-05-07 09:25:25'),
(103, 'wsx', '123', 'asdfsdaf@gmail.com', '6515616', '2025-05-07 09:25:36'),
(104, 'edc', '123', 'dsfdsafasf@gmail.com', '56156232', '2025-05-07 09:26:01'),
(105, '12345', '12345', 'saasfasf@gmail.com', '12345', '2025-05-12 09:53:03'),
(106, 'rıdvan', '123', 'sadffasd@gmail.com', '15615615', '2025-05-12 11:55:05');

INSERT INTO `mülk` (`id`, `emlakçı_id`, `başlık`, `açıklama`, `fiyat`, `odasayısı`, `m^2`, `şehir`, `ilçe`, `tür`, `durum`, `aktif`) VALUES
(101, 101, 'Geniş Daire', 'Merkezde konforlu daire', 1500000, '3', 120, 'İstanbul', 'Kadıköy', 'Daire', 'Satılık', 1),
(102, 102, 'Lüks Villa', 'Havuzlu villa', 8500000, '6', 350, 'Antalya', 'Lara', 'Villa', 'Satılık', 1),
(103, 103, 'Yatırımlık Arsa', 'Ana yola yakın arsa', 500000, '0', 600, 'Ankara', 'Gölbaşı', 'Arsa', 'Satılık', 1),
(104, 101, 'İşyeri Dükkan', 'Cadde üstü kiralık dükkan', 18000, '0', 90, 'İzmir', 'Bornova', 'Dükkan', 'Satıldı/Kiralandı', 0),
(105, 103, 'Şehirden Uzak Müstakil', 'Doğa içinde huzurlu ev', 2200000, '4', 200, 'Muğla', 'Fethiye', 'Müstakil Ev', 'Satılık', 1),
(106, 102, 'Modern Daire', 'Yeni yapılmış modern daire', 1250000, '2', 95, 'Bursa', 'Nilüfer', 'Daire', 'Satılık', 1),
(107, 101, 'Kiralanacak Dükkan', 'Alışveriş merkezine yakın', 9500, '0', 70, 'İstanbul', 'Bakırköy', 'Dükkan', 'Kiralık', 1),
(108, 103, 'Yazlık Villa', 'Denize sıfır lüks villa', 10000000, '5', 400, 'İzmir', 'Çeşme', 'Villa', 'Satılık', 1),
(109, 102, 'İnşaatlık Arsa', 'Yüksek emsalli arsa', 1100000, '0', 700, 'Ankara', 'Etimesgut', 'Arsa', 'Satılık', 1),
(110, 102, 'Merkezde Daire', 'Ulaşımı kolay', 1350000, '3', 110, 'İstanbul', 'Şişli', 'Daire', 'Satılık', 1),
(111, 101, 'Sessiz Müstakil Ev', 'Sessiz mahallede ev', 1900000, '3', 180, 'Sakarya', 'Serdivan', 'Müstakil Ev', 'Satılık', 1),
(112, 102, 'Yeni Kiralık Daire', 'Site içinde daire', 8500, '2', 80, 'Antalya', 'Konyaaltı', 'Daire', 'Kiralık', 1),
(113, 101, 'Depolu Dükkan', 'İmalata uygun', 21000, '0', 130, 'Gaziantep', 'Şahinbey', 'Dükkan', 'Kiralık', 1),
(114, 101, 'Villa', 'Şehir manzaralı villa', 6500000, '4', 280, 'Trabzon', 'Ortahisar', 'Villa', 'Satılık', 1),
(115, 102, 'Arsa', 'Gelişmekte olan bölgede arsa', 750000, '0', 500, 'Eskişehir', 'Tepebaşı', 'Arsa', 'Satıldı/Kiralandı', 0),
(116, 101, 'Kiralık Daire', 'Kampüse yakın daire', 6000, '1', 60, 'Eskişehir', 'Odunpazarı', 'Daire', 'Kiralık', 1),
(117, 102, 'Satılık Villa', 'Geniş bahçeli', 7200000, '5', 320, 'Yalova', 'Termal', 'Villa', 'Satılık', 1),
(118, 103, 'Merkezi Dükkan', 'İşlek caddede', 28000, '0', 150, 'Ankara', 'Kızılay', 'Dükkan', 'Kiralık', 1),
(119, 103, 'Yatırımlık Daire', 'Yüksek kira getirisi', 980000, '2', 85, 'Kocaeli', 'Gebze', 'Daire', 'Satılık', 1),
(120, 102, 'Arsa', 'Tarla vasıflı ama imar yakında', 400000, '0', 1000, 'Balıkesir', 'Edremit', 'Arsa', 'Satıldı/Kiralandı', 0),
(121, 102, 'Kiralık Ev', 'Sakin sokakta müstakil ev', 7500, '3', 150, 'Konya', 'Selçuklu', 'Müstakil Ev', 'Kiralık', 1),
(122, 101, 'Dükkan', 'Pazar yeri karşısı', 16500, '0', 100, 'Adana', 'Seyhan', 'Dükkan', 'Kiralık', 1),
(123, 101, 'Villa', 'Şehir dışı lüks villa', 5800000, '4', 300, 'Denizli', 'Pamukkale', 'Villa', 'Satılık', 1),
(124, 103, 'Arsa', 'Organize sanayi yanında', 1300000, '0', 800, 'Kayseri', 'Melikgazi', 'Arsa', 'Satılık', 1),
(125, 102, 'Daire', 'Uygun fiyatlı aile evi', 890000, '3', 100, 'Mersin', 'Yenişehir', 'Daire', 'Satılık', 1),
(126, 101, 'Yazlık Ev', 'Şehire çokda uzak olmayan kafa dinlemelik ev', 2500000, '5', 220, 'Erzincan', 'Merkez', 'Müstakil Ev', 'Satılık', 1),
(127, 101, 'Ömerin yeni evi', 'Ömerin yan dairesi', 15000, '2', 100, 'Konya', 'Merkez', 'Daire', 'Kiralık', 1);

INSERT INTO `teklif` (`id`, `ilan_id`, `müşteri_id`, `emlakçı_id`, `teklif`) VALUES
(101, 105, 101, 101, 16000),
(102, 106, 102, 103, 2000000),
(103, 107, 103, 102, 1100000),
(104, 108, 102, 101, 6000000),
(105, 109, 101, 103, 900000);

INSERT INTO `satın_alım` (`id`, `ilan_id`, `müşteri_id`, `emlakçı_id`, `alım_tarihi`) VALUES
(101, 116, 101, 102, '2025-05-12 10:04:06'),
(102, 105, 102, 101, '2025-05-12 10:31:06'),
(103, 121, 103, 102, '2025-05-12 11:55:19');

INSERT INTO `randevu` (`id`, `ilan_id`, `müşteri_id`, `emlakçı_id`, `randevu_saat`, `randevu_tarihi`) VALUES
(101, 105, 101, 101, '12:23:00', '2025-08-16'),
(102, 106, 102, 103, '16:15:00', '2025-10-05'),
(103, 107, 103, 102, '20:25:00', '2025-05-25'),
(104, 108, 102, 101, '12:23:00', '2025-03-12'),
(105, 109, 101, 103, '12:33:00', '2026-02-18');
