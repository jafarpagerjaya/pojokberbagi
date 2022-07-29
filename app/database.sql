CREATE DATABASE pojokberbagi;

USE pojokberbagi;

CREATE TABLE jabatan (
    id_jabatan TINYINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    alias VARCHAR(3),
    gajih_pokok INT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT NU_NAMA_ALIAS_JABATAN UNIQUE(nama, alias)
)ENGINE=INNODB;

INSERT INTO jabatan(nama,alias) VALUES('SYSTEM','SYS'),
('Direktur Eksekutif','DE'),
('Direktur Program & Kemitraan', NULL),
('Direktur Teknis & Operasi', NULL),
('Keuangan',NULL),
('Program','PRO'),
('Supervisor',NULL),
('Customer Relationship','CRE'),
('Digital Marketing','DM'),
('Informasi Teknologi','IT');

CREATE TABLE pegawai (
    id_pegawai SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    jenis_kelamin ENUM('P','L'),
    tanggal_lahir DATE NOT NULL,
    email VARCHAR(96) NOT NULL,
    kontak VARCHAR(13),
    alamat VARCHAR(255) NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_atasan SMALLINT UNSIGNED,
    id_jabatan TINYINT UNSIGNED,
    id_tanda_tangan INT UNSIGNED,
    CONSTRAINT NU_EMAIL_PEGAWAI UNIQUE(email),
    CONSTRAINT U_KONTAK_PEGAWAI UNIQUE(kontak),
    CONSTRAINT F_ID_JABATAN_PEGAWAI_ODN FOREIGN KEY(id_jabatan) REFERENCES jabatan(id_jabatan) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT F_ID_TANDA_TANGAN_PEGAWAI_ODN FOREIGN KEY(id_tanda_tangan) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=INNODB;

INSERT INTO pegawai(nama,jenis_kelamin,tanggal_lahir,email,kontak,alamat) VALUES
('SYSROOT',NULL,DATE_FORMAT(NOW(), '%Y-%m-%d'),'pojokberbagi.id@gmail.com',NULL,'Kantor'),
('JAFAR PAGER JAYA','L','1992-08-07','jafarpager@gmail.com','085322661186','BUMI HARAPAN BLOK DD 8 NO 10');

UPDATE pegawai SET id_jabatan = 1 WHERE nama = 'SYSROOT' AND email = 'pojokberbagi.id@gmail.com';

CREATE TABLE gambar (
    id_gambar INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    path_gambar VARCHAR(255),
    label VARCHAR(10),
    gembok CHAR(1) DEFAULT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT U_NAMA_PATH_GAMBAR UNIQUE(nama, path_gambar)
)ENGINE=INNODB;

-- DROP TRIGGER IF EXISTS GEMBOK_CHECK_INSERT;
CREATE TRIGGER GEMBOK_CHECK_INSERT 
BEFORE INSERT ON gambar
FOR EACH ROW
  SET NEW.gembok = IF(NEW.gembok = 1, NEW.gembok, NULL);

-- DROP TRIGGER IF EXISTS GEMBOK_CHECK_UPDATE;
CREATE TRIGGER GEMBOK_CHECK_UPDATE
BEFORE UPDATE ON gambar
FOR EACH ROW
  SET NEW.gembok = IF(NEW.gembok = 1, NEW.gembok, NULL);

INSERT INTO gambar(nama,path_gambar, label, gembok) VALUES('default','/assets/images/default.png','avatar','1'),
('female-avatar','/assets/images/female-avatar.jpg','avatar','1'),
('male-avatar','/assets/images/male-avatar.jpg','avatar','1'),
('bank-bjb','/assets/images/partners/bjb.png','partner','1'),
('bank-bsi','/assets/images/partners/bsi.png','partner','1'),
('bank-bri','/assets/images/partners/bri.png','partner','1'),
('satu-juta-sembako','/uploads/images/bantuan/medium/satu-juta-sembako-medium.jpg','bantuan', NULL),
('satu-juta-sembako-wide','/uploads/images/bantuan/wide/satu-juta-sembako-wide.png','bantuan', NULL),
('geberr','/uploads/images/bantuan/medium/geberr-medium.png','bantuan', NULL),
('geberr-wide','/uploads/images/bantuan/wide/geberr-wide.png','bantuan', NULL),
('semeru','/uploads/images/bantuan/medium/semeru-medium.png','bantuan', NULL),
('semeru-wide','/uploads/images/bantuan/wide/semeru-wide.png','bantuan', NULL),
('razka','/uploads/images/bantuan/medium/razka-medium.jpg','bantuan', NULL),
('razka-wide','/uploads/images/bantuan/wide/razka-wide.png','bantuan', NULL),
('single-wonder-mom','/uploads/images/bantuan/medium/single-wonder-mom-medium.jpeg','bantuan', NULL),
('single-wonder-mom-wide','/uploads/images/bantuan/wide/single-wonder-mom-wide.png','bantuan', NULL),
('uang tunai','/assets/images/partners/tunai.png','partner',1),
('GoPay','/assets/images/partners/gopay.png','partner',1),
('Dana','/assets/images/partners/dana.png','partner',1),
('Qris','/assets/images/partners/qris.png','partner',1);


CREATE TABLE akses (
    hak_akses CHAR(1) NOT NULL PRIMARY KEY,
    nama VARCHAR(10) NOT NULL,
    izin VARCHAR(255),
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT NU_NAMA_IZIN_AKSES UNIQUE(nama, izin)
)ENGINE=INNODB;

INSERT INTO akses(hak_akses,nama,izin) VALUES('A','Admin','{"admin": 1,"donatur": 1}'),
('D','Donatur','{"donatur": 1}'),
('P','Pemohon','{"donatur": 1,"pemohon": 1}');

CREATE TABLE akun (
    id_akun INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    username VARCHAR(32) NOT NULL,
    password VARCHAR(64) NOT NULL,
    email VARCHAR(96) NOT NULL,
    aktivasi CHAR(1) NOT NULL DEFAULT '0',
    pin CHAR(6),
    salt VARCHAR(255) NOT NULL,
    hash_reset VARCHAR(32) NULL,
    hash_expiry TIMESTAMP NULL DEFAULT NULL,
    hak_akses CHAR(1) NOT NULL DEFAULT 'D',
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_gambar INT UNSIGNED DEFAULT 1,
    CONSTRAINT NU_USERNAME_AKUN UNIQUE(username),
    CONSTRAINT F_ID_GAMBAR_AKUN_ODN FOREIGN KEY(id_gambar) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=INNODB;

INSERT INTO akun(username,password,email,aktivasi,salt,hak_akses) VALUES('Pojok Berbagi', '595bfd08e98ea60c77d9949233761d0b', 'pojokberbagi.id@gmail.com', '1', '214fdcc52049c81fe814d92778168771', 'A'),('Jadi Anak Sholeh', '595bfd08e98ea60c77d9949233761d0b', 'pojokberbagi.id@gmail.com', '1', '214fdcc52049c81fe814d92778168771', 'A');

CREATE TABLE sesi_akun(
    id_sesi INT UNSIGNED AUTO_INCREMENT,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_akun INT UNSIGNED,
    hash VARCHAR(64),
    PRIMARY KEY(id_sesi),
    CONSTRAINT F_ID_AKUN_SESI_AKUN_ODC FOREIGN KEY(id_akun) REFERENCES akun(id_akun) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB;

CREATE TABLE admin (
    id_admin INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    level CHAR(1) NOT NULL DEFAULT 'N',
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_pegawai SMALLINT UNSIGNED,
    id_akun INT UNSIGNED NOT NULL,
    CONSTRAINT U_ID_ID_PEGAWAI_ADMIN UNIQUE(id_pegawai),
    CONSTRAINT U_ID_ID_AKUN_ADMIN UNIQUE(id_akun),
    CONSTRAINT F_ID_PEGAWAI_ADMIN_ODC FOREIGN KEY(id_pegawai) REFERENCES pegawai(id_pegawai) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_AKUN_ADMIN_ODR FOREIGN KEY(id_akun) REFERENCES akun(id_akun) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

-- DROP TRIGGER IF EXISTS ADMIN_CHECK_INSERT;
CREATE TRIGGER ADMIN_CHECK_INSERT 
BEFORE INSERT ON admin
FOR EACH ROW
  SET NEW.level = IF(NEW.level = 'S', UPPER(NEW.level), 'N');

-- DROP TRIGGER IF EXISTS ADMIN_CHECK_UPDATE;
CREATE TRIGGER ADMIN_CHECK_UPDATE
BEFORE UPDATE ON admin
FOR EACH ROW
  SET NEW.level = IF(NEW.level = 'S', UPPER(NEW.level), 'N');

INSERT INTO admin(level,id_pegawai,id_akun) VALUES('S', 1, 1),('N', 2, 2);

-- SELECT AUTO_INCREMENT FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'tes' AND TABLE_NAME = 'admin';

CREATE TABLE donatur (
    id_donatur INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nama VARCHAR(30) NOT NULL,
    email VARCHAR(96),
    kontak VARCHAR(13),
    samaran VARCHAR(30),
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_akun INT UNSIGNED,
    CONSTRAINT U_EMAIL_DONATUR UNIQUE(email),
    CONSTRAINT U_KONTAK_DONATUR UNIQUE(kontak),
    CONSTRAINT U_ID_AKUN_DONATUR UNIQUE(id_akun),
    CONSTRAINT F_ID_AKUN_DONATUR_ODR FOREIGN KEY(id_akun) REFERENCES akun(id_akun) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

INSERT INTO donatur(nama,email,id_akun) VALUES('Bank BJB','csr@bjb.co.id',null),('Hamba Allah','handysantika@gmail.com',null),('SYSROOT','pojokberbagi.id@gmail.com',1),('Kiki Rejeki','rizky.edu@gmail.com',null),('Jafar Pager Jaya','jafarpager@gmail.com',2),('DINDA','maulinda.dinda98@gmail.com',null),('HAJI ARIEF','arifriandi834@gmail.com',null);
UPDATE donatur SET kontak = '085211307060' WHERE email = 'rizky.edu@gmail.com';
UPDATE donatur SET kontak = '082219069999' WHERE email = 'handysantika@gmail.com';
UPDATE donatur SET kontak = '0895389564902' WHERE email = 'maulinda.dinda98@gmail.com';

-- INSERT DONATUR LAMA
INSERT INTO donatur(nama,email,create_at) VALUES('HAMBA ALLAH','hambaallah@pojokberbagi.id','2021-12-20');
INSERT INTO donatur(nama, create_at) VALUES
('SUPRIYADI','2021-11-22'),
('RIDWAN','2021-11-22'),
('YBM BRI KC. ASIA AFRIKA','2021-11-28'),
('YANI SUMARYANI','2021-12-09'),
('MIKSA','2021-12-29'),
('PEPEN','2021-12-29'),
('WIDI HERMAWAN','2021-12-30'),
('ROHAYATI','2021-12-30'),
('NUNUNG SUTINI','2021-12-31'),
('UTI FARZA','2021-12-31'),
('YENI MARIYANI','2021-12-31'),
('YAHYA','2021-12-31'),
('SUJANAH','2021-12-31'),
('ASIH SALIMA NURRAHMAN','2022-01-05'),
('ENDANG NURAHMAN','2022-01-05'),
('NENENG HILAWATY DJAJADIJAKARTA','2022-01-05'),
('SUMIYATI','2022-01-13'),
('DITA HALIFATUS SADIAH','2022-01-14'),
('SELLY MARSELIANI','2022-01-20'),
('YOHANAH','2022-02-04'),
('WARDI (ALM)','2022-02-04'),
('OOM (ALM)','2022-02-18'),
('ENDANG KARTIWA (ALM)','2022-02-27'),
('TEH DANDANG','2022-04-09'),
('DAPUR AQIQAH','2022-04-21'),
('GIBRAN RIZKI PRATAMA','2022-04-22'),
('FSLDK','2022-04-25'),
('KAMMI','2022-04-25');

CREATE TABLE pemohon (
    id_pemohon INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    blokir CHAR(1),
    nama VARCHAR(100) NOT NULL,
    alamat VARCHAR(255) NOT NULL,
    kontak VARCHAR(13) NOT NULL,
    email VARCHAR(96) NOT NULL,
    legalitas VARCHAR(255) NOT NULL,
    npwp BIGINT(15) NOT NULL,
    no_ktp BIGINT(16) NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_akun INT UNSIGNED,
    id_gambar INT UNSIGNED,
    CONSTRAINT NU_NAMA_PEMOHON UNIQUE(nama),
    CONSTRAINT NU_KONTAK_PEMOHON UNIQUE(kontak),
    CONSTRAINT NU_EMAIL_PEMOHON UNIQUE(email),
    CONSTRAINT NU_LEGALITAS_PEMOHON UNIQUE(legalitas),
    CONSTRAINT NU_NPWP_PEMOHON UNIQUE(npwp),
    CONSTRAINT NU_NO_KTP_PEMOHON UNIQUE(no_ktp),
    CONSTRAINT F_ID_AKUN_PEMOHON_ODR FOREIGN KEY(id_akun) REFERENCES akun(id_akun) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_GAMBAR_PEMOHON_ODN FOREIGN KEY(id_gambar) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE upload_persyaratan_pemohon (
    nama VARCHAR(50) NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_pemohon INT UNSIGNED,
    id_gambar INT UNSIGNED,
    CONSTRAINT F_ID_PEMOHON_UPLOAD_PP_ODC FOREIGN KEY(id_pemohon) REFERENCES pemohon(id_pemohon) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_GAMBAR_UPLOAD_PP_ODC FOREIGN KEY(id_gambar) REFERENCES gambar(id_gambar) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE kategori (
    id_kategori TINYINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    warna VARCHAR(10) DEFAULT '#e9ecef',
    deskripsi VARCHAR(255),
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)ENGINE=INNODB;

INSERT INTO kategori(nama, warna) VALUES('Pojok Berdaya','#3080E3'),
('Pojok Peduli Yatim','#FFA842'),
('Pojok Peduli Berbagi','#fd723b'),
('Pojok Wakaf','#97D700'),
('Pojok Rescue','#d63384');

CREATE TABLE sektor (
    id_sektor CHAR(1) PRIMARY KEY,
    nama VARCHAR(30) NOT NULL,
    deskripsi VARCHAR(255),
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT UN_NAMA_SEKTOR UNIQUE(nama)
)ENGINE=INNODB;

INSERT INTO sektor(id_sektor, nama) VALUES('B','Bencana'),
('E','Ekonomi'),
('L','Lingkungan'),
('K','Kesehatan'),
('P','Pendidikan'),
('S','Sosial');

CREATE TABLE kebutuhan (
    id_kebutuhan INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT UN_NAMA_KEBUTUHAN UNIQUE(nama)
)ENGINE=INNODB;

-- FETURE
-- CREATE TABLE pengajuan (
--     id_pengajuan INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
--     nama VARCHAR(50) NOT NULL,
--     nama_penerima VARCHAR(50),
--     satuan_target VARCHAR(15),
--     jumlah_target INT UNSIGNED,
--     min_donasi INT UNSIGNED,
--     total_rab BIGINT UNSIGNED DEFAULT NULL,
--     lama_penayangan SMALLINT UNSIGNED,
--     tanggal_awal DATE,
--     tanggal_akhir DATE,
--     deskripsi VARCHAR(255) NOT NULL,
--     create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     action_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     id_gambar_medium INT UNSIGNED,
--     id_gambar_wide INT UNSIGNED,
--     id_sektor CHAR(1),
--     id_kategori TINYINT UNSIGNED,
--     id_pemohon INT UNSIGNED,
--     CONSTRAINT F_ID_GAMBAR_PENGAJUAN_MEDIUM_ODN FOREIGN KEY(id_gambar_medium) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE,
--     CONSTRAINT F_ID_GAMBAR_PENGAJUAN_WIDE_ODN FOREIGN KEY(id_gambar_wide) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE,
--     CONSTRAINT F_ID_SEKTOR_PENGAJUAN_ODR FOREIGN KEY(id_sektor) REFERENCES sektor(id_sektor) ON DELETE RESTRICT ON UPDATE CASCADE,
--     CONSTRAINT F_ID_KATEGORI_PENGAJUAN_ODR FOREIGN KEY(id_kategori) REFERENCES kategori(id_kategori) ON DELETE RESTRICT ON UPDATE CASCADE,
--     CONSTRAINT F_ID_PEMOHON_PENGAJUAN_ODN REFERENCES pemohon(id_pemohon) ON DELETE SET NULL ON UPDATE CASCADE
-- )ENGINE=INNODB;

CREATE TABLE bantuan (
    id_bantuan INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    id_pemohon INT UNSIGNED,
    nama VARCHAR(50) NOT NULL,
    tag VARCHAR(30),
    blokir ENUM('1') DEFAULT NULL,
    status ENUM('B','C','T','D','S') NOT NULL DEFAULT 'B',
    prioritas CHAR(1) DEFAULT NULL,
    nama_penerima VARCHAR(50),
    satuan_target VARCHAR(15),
    jumlah_target INT UNSIGNED,
    min_donasi INT UNSIGNED,
    total_rab BIGINT UNSIGNED DEFAULT NULL,
    lama_penayangan SMALLINT UNSIGNED,
    tanggal_awal DATE,
    tanggal_akhir DATE,
    deskripsi VARCHAR(255) NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    action_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_gambar_medium INT UNSIGNED,
    id_gambar_wide INT UNSIGNED,
    id_sektor CHAR(1),
    id_kategori TINYINT UNSIGNED,
    id_penangung_jawab SMALLINT UNSIGNED,
    id_pengawas SMALLINT UNSIGNED,
    CONSTRAINT U_TAG_BANTUAN UNIQUE(tag),
    CONSTRAINT F_ID_PEMOHON_BANTUAN_ODR FOREIGN KEY(id_pemohon) REFERENCES pemohon(id_pemohon) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_GAMBAR_BANTUAN_MEDIUM_ODN FOREIGN KEY(id_gambar_medium) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT F_ID_GAMBAR_BANTUAN_WIDE_ODN FOREIGN KEY(id_gambar_wide) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT F_ID_SEKTOR_BANTUAN_ODR FOREIGN KEY(id_sektor) REFERENCES sektor(id_sektor) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_KATEGORI_BANTUAN_ODR FOREIGN KEY(id_kategori) REFERENCES kategori(id_kategori) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_PENANGUNG_JAWAB_BANTUAN_ODN FOREIGN KEY(id_penangung_jawab) REFERENCES pegawai(id_pegawai) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT F_ID_PENGAWAS_BANTUAN_ODN FOREIGN KEY(id_pengawas) REFERENCES pegawai(id_pegawai) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=INNODB;

-- DROP TRIGGER IF EXISTS BANTUAN_CHECK_UPDATE;
DELIMITER $$
CREATE TRIGGER BANTUAN_CHECK_UPDATE
BEFORE UPDATE ON bantuan FOR EACH ROW
    BEGIN
        IF OLD.status <> NEW.status THEN
        SET NEW.action_at = NOW();
        END IF;
    END$$
DELIMITER ;

-- status tabel bantuan B = Belum Disetujui, C = Sedang Proses Cek Data, T = Tidak Disetujui, D = Disetujui, S = Proyek Bantuan sudah selesai
-- satuan_target = menyatakan sasaran dari jumlah target. contoh paket sembako

INSERT INTO bantuan(nama,status,satuan_target,jumlah_target,tanggal_awal,deskripsi,id_kategori,id_sektor) VALUES
('Satu Juta Sembako','D','Paket Sembako',1000000,'2021-08-01','Berbagi Sembako Bagi Masyarakat Terdampak COVID-19', '3', 'S'),
('Geberr','D',NULL,NULL,NULL,"Gerakan Berbagi Nasi Untuk Orang Lapar di Hari Jum'at Berkah", '3', 'S'),
('Peduli Semeru','D',NULL,NULL,NULL,'Bantuan Bencana Bagi Masyarakat Terdampak Letusan Gunung Semeru', '5', 'B'),
('Peduli Razka','D',NULL,NULL,NULL,'Bantuan Untuk Razka Pendrita Miningitis Berusia 6thn', '3', 'K');

-- INSERT bantuan non kategori
INSERT INTO bantuan(nama,status,min_donasi,create_at,deskripsi) VALUES
('Infaq','D',2000,'2021-09-09','Infaq'),
('Zakat Mal','D',2000,'2021-09-09','Mengeluarkan harta "zakat" seorang muslim sesuai dengan nisab dan haulnya adalah kewajiban'),
('Jemput Ambulance','D',5000,'2021-09-09','Berdonasi untuk layanan ambulance gratis');
-- INSERT bantuan non sektor
INSERT INTO bantuan(nama,status,min_donasi,create_at,deskripsi,id_kategori) VALUES
('Program Pojok Berdaya','D',10000,'2021-09-09','Program pemberdayaan masyarakan untuk mandiri dan unggul.',1),
('Program Pojok Rescue','D',10000,'2021-09-09','Program bantuan terhadap kebencanaan',5),
('Program Pojok Yatim','D',10000,'2021-09-09','Program bantuan untuk yatim',2),
('Program Pojok Peduli','D',10000,'2021-09-09','Program bantuan untuk berbagi kebahagianan dan peduli kepada sesama',3);
-- INSERT bantuan yang sudah ada donasinya dan belum dibuat
INSERT INTO bantuan(nama,status,min_donasi,create_at,deskripsi,id_kategori,id_sektor) VALUES
('Banjir Sukawening','S',10000,'2021-12-11','Bantu masyarakat terdampak banjir di Sukawening',5,'B');
-- INSERT bantuan yang sudah ada donasinya dan yang di buat oleh dinda
INSERT INTO bantuan(nama,status,min_donasi,create_at,deskripsi,id_kategori,id_sektor) VALUES
('Berbagi Hadiah Lebaran Untuk Yatim & Lansia','S',50000,'2022-01-05','Berbagi kebahagian kepada anak yatim dan lansia akhir bulan ramadhan dengan memberikan hadiah lebaran untuk mereka',3,'S'),
('BERBAGI 1.000 PAKET BERBUKA','S',10000,'2022-04-01','Hidangkan makanan berbuka puasa untuk saudara kita diluar sana yuk! Pojok Berbagi Indonesia menawarkan paket makanan berbuka lengkap yang terdiri dari takjil, makanan utama dan minuman untuk fakir miskin dan masyarakat lainnya.',3,'S');
("SEDEKAH AL-QUR'AN UNTUK ORANG TUA",'D',20000,'2022-04-18','*Wakaf Al-Qur&#039;an atas nama orang tua* bisa menjadi bentuk ikhtiar kita untuk membahagiakan kedua orang tua, hadiah luar biasa yang insya Allah pahalanya akan terus mengalir serta menjadi syafaat untuk keduanya di hari akhir nanti.',4,'S');

UPDATE bantuan SET action_at = create_at;
-- INSERT bantuan yang sudah ada donasinya dan yang di buat oleh dinda
INSERT INTO bantuan(nama, nama_penerima,status,prioritas,lama_penayangan,min_donasi,deskripsi,create_at,modified_at,action_at,id_sektor,id_kategori) VALUES('RAIH SURGA BERSAMA POJOK QURBAN','anak yatim, kaum dhuafa, dan mustahik','D',1,50,2750000,'Ayo gapai keberkahan Idul Adha dan hadirkan kebahagiaan dengan berikan qurban terbaikmu hingga ke pojok desa. &ldquo;Sesungguhnya Kami telah memberikan karunia sangat banyak kepadamu,&nbsp;maka sholatlah untuk Tuhanmu&nbsp;&nbsp;dan sembelihlah kurban.&rd','2022-05-25 16:06:10','2022-06-23 14:57:33','2022-05-25 16:06:10','S',3);

CREATE TABLE kebutuhan_bantuan (
    jumlah INT UNSIGNED NOT NULL DEFAULT 1,
    nominal_satuan INT UNSIGNED NOT NULL,
    sub_total INT UNSIGNED NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_bantuan INT UNSIGNED,
    id_kebutuhan INT UNSIGNED,
    CONSTRAINT F_ID_BANTUAN_KEBUTUHAN_BANTUAN_ODC FOREIGN KEY(id_bantuan) REFERENCES bantuan(id_bantuan) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_KEBUTUHAN_KEBUTUHAN_BANTUAN_ODC FOREIGN KEY(id_kebutuhan) REFERENCES kebutuhan(id_kebutuhan) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE pelaksanaan (
    id_pelaksanaan INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    deskripsi VARCHAR(255),
    jumlah_pelaksanaan INT,
    total_anggaran INT UNSIGNED NOT NULL,
    status ENUM('J','S') NOT NULL DEFAULT 'J',
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)ENGINE=INNODB;

-- status pelaksanaan J = Jalan/Sedang Berjalan, S = Selesai

INSERT INTO pelaksanaan(deskripsi, jumlah_pelaksanaan, status, total_anggaran) VALUE('Program sembako BJB Agustus', 15000, 'S', 2312500000),
('Program sembako BJB November', 25000, 'J', 3125000000),
('Geberr Baitul Mutaqin', 150, 'S', 750000);

CREATE TABLE channel_payment (
    id_cp TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(25) NOT NULL,
    kode CHAR(3) DEFAULT NULL,
    nomor VARCHAR(30) NOT NULL,
    atas_nama VARCHAR(30) NOT NULL,
    jenis ENUM('TB','VA','EW','QR','GM','GI','TN') NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_gambar INT UNSIGNED,
    CONSTRAINT U_NAMA_NOMOR_CHANNEL_PAYMENT UNIQUE(nama, nomor, jenis),
    CONSTRAINT F_ID_GAMBAR_CHANNEL_PAYMENT_ODN FOREIGN KEY(id_gambar) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=INNODB;

-- jenis channel_payment TB = Transfer Bank, VA = Virtual Acount, EW = E-Wallet, QR, Qris, GM = Gerai Mart, GI = GIRO, TN = Tunai

INSERT INTO channel_payment(nama, kode, nomor, jenis, atas_nama, id_gambar) VALUES
('Bank BJB', '110', '0001000080001', 'TB','POJOK BERBAGI INDONESIA', (SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%bjb%" AND label = 'partner')),
('Bank BJB Giro Payment', '110', '0001000080001', 'GI','POJOK BERBAGI INDONESIA', (SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%bjb%" AND label = 'partner')),
('Tunai Via CR', '1', '1', 'TN','CR POJOK BERBAGI KANTOR PUSAT', (SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%tunai%" AND label = 'partner')),
('Bank BSI','451','7400525255','TB','POJOK BERBAGI INDONESIA', (SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%bsi%" AND label = 'partner')),
('Bank BRI','002','107001000272300','TB','POJOK BERBAGI INDONESIA', (SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%bri%" AND label = 'partner')),
('GoPay','1','081233311113','EW','Pojok Berbagi',(SELECT id_gambar FROM gambar WHERE LOWER(nama) = 'gopay')),
('Dana','1','081233311113','EW','Pojok Berbagi',(SELECT id_gambar FROM gambar WHERE LOWER(nama) = 'dana')),
('Bank BRI','002','ID1022148253464','QR','POJOK BERBAGI INDONESIA',(SELECT id_gambar FROM gambar WHERE LOWER(nama) = 'qris'));

-- FEATURE
-- CREATE TABLE pembayaran (
--     kode_pembayaran VARCHAR(64) PRIMARY KEY,
--     id_donatur INT UNSIGNED NOT NULL,
--     id_bantuan INT UNSIGNED NOT NULL,
--     id_cp TINYINT UNSIGNED NOT NULL,
--     jumlah_donasi INT UNSIGNED NOT NULL,
--     email VARCHAR(96),
--     kontak VARCHAR(13),
--     doa VARCHAR(200),
--     alias VARCHAR(30),
--     status ENUM('BP','MP','SL') NOT NULL DEFAULT 'MP',
--     create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     CONSTRAINT F_ID_DONATUR_PEMBAYARAN_ODR FOREIGN KEY id_donatur REFERENCES donatur(id_donatur) ON DELETE RESTRICT ON UPDATE CASCADE,
--     CONSTRAINT F_ID_BANTUAN_PEMBAYARAN_ODR FOREIGN KEY id_bantuan REFERENCES bantuan(id_bantuan) ON DELETE RESTRICT ON UPDATE CASCADE,
--     CONSTRAINT F_ID_CP_PEMBAYARAN_ODR FOREIGN KEY id_cp REFERENCES channel_payment(id_cp) ON DELETE RESTRICT ON UPDATE CASCADE
-- )ENGINE=INNODB;

-- SET GLOBAL event_scheduler = ON
-- CREATE EVENT EVENT_DELETE_UNPAID ON SCHEDULE EVERY 1 MINUTE 
-- STARTS CONCAT(DATE(NOW()),' 00:00:00')
-- ENABLE
-- DO 
-- DELETE FROM pembayaran WHERE create_at < CURRENT_TIMESTAMP - INTERVAL 24 HOUR;

CREATE TABLE donasi (
    id_donasi BIGINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    kode_pembayaran VARCHAR(64),
    alias VARCHAR(30),
    kontak VARCHAR(13),
    doa VARCHAR(200),
    jumlah_donasi BIGINT UNSIGNED NOT NULL DEFAULT 1000,
    bayar TINYINT NOT NULL DEFAULT 0,
    waktu_bayar TIMESTAMP,
    notifikasi CHAR(1) DEFAULT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_bantuan INT UNSIGNED,
    id_donatur INT UNSIGNED,
    id_cp TINYINT UNSIGNED,
    CONSTRAINT U_KODE_PEMBAYARAN_DONASI UNIQUE(kode_pembayaran),
    CONSTRAINT F_ID_BANTUAN_DONASI_ODR FOREIGN KEY(id_bantuan) REFERENCES bantuan(id_bantuan) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_DONATUR_DONASI_ODR FOREIGN KEY(id_donatur) REFERENCES donatur(id_donatur) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_CP_DONASI_ODN FOREIGN KEY(id_cp) REFERENCES channel_payment(id_cp) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=INNODB;

DELIMITER $$
CREATE TRIGGER DONASI_CHECK_UPDATE
BEFORE UPDATE ON donasi FOR EACH ROW
    BEGIN
    DECLARE kwitansi_count TINYINT DEFAULT 0;
    SET kwitansi_count = (SELECT COUNT(id_kwitansi) FROM kwitansi WHERE id_donasi = OLD.id_donasi);
        IF OLD.bayar = 1 AND NEW.bayar = 0 THEN
        SET NEW.waktu_bayar = NULL;
            IF (kwitansi_count = 1) THEN
            UPDATE kwitansi SET create_at = NULL WHERE id_donasi = OLD.id_donasi;
            END IF;
        ELSE
            IF (kwitansi_count = 0) THEN
            INSERT INTO kwitansi(create_at,id_donasi) VALUES(NEW.waktu_bayar,OLD.id_donasi);
            ELSE 
            UPDATE kwitansi SET create_at = NEW.waktu_bayar WHERE id_donasi = OLD.id_donasi;
            END IF;
        END IF;
    END$$
DELIMITER ;

CREATE TABLE kwitansi (
    id_kwitansi BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    waktu_cetak TIMESTAMP,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_donasi BIGINT UNSIGNED,
    id_pengesah SMALLINT UNSIGNED,
    CONSTRAINT F_ID_DONASI_KWITANSI_ODR FOREIGN KEY(id_donasi) REFERENCES donasi(id_donasi) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_PENGESAH_KWITANSI_ODR FOREIGN KEY(id_pengesah) REFERENCES pegawai(id_pegawai) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

ALTER TABLE kwitansi AUTO_INCREMENT = 1001;

-- status donasi 0 = pembayaran belum dilakukan, 1 = pembayaran berhasil;

INSERT INTO donasi(id_bantuan,id_donatur,alias,jumlah_donasi, bayar, id_cp, create_at, waktu_bayar) VALUES(1,1,'CSR BJB',2312500000,'1',2,'2021-08-11','2021-08-11'),(1,1,'CSR BJB',3125000000,'1',2,'2021-10-10','2021-10-10'),(2,2,"PROGRAM",750000,'1',3,'2021-10-16','2021-10-16');
-- INSERT DONASI LAMA (Nama Program dan Nama Sementara Sebelum Ada yang Sama Atau Terganti)
INSERT INTO donasi(id_bantuan,id_donatur,id_cp,create_at,jumlah_donasi,bayar) VALUES
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'arifriandi834@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-11-01',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'maulinda.dinda98@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-11-11',60000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'jemput ambulance'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'SUPRIYADI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-11-22',250000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'jafarpager@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-11-26',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'RIDWAN'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-11-26',5000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'jemput ambulance'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'YBM BRI KC. ASIA AFRIKA'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-11-28',300000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'program pojok berdaya'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'rizky.edu@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-03',235000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli semeru'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'YANI SUMARYANI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2021-12-09',250000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'YANI SUMARYANI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2021-12-17',150000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'hambaallah@pojokberbagi.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2021-12-20',100000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'banjir sukawening'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'hambaallah@pojokberbagi.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2021-12-20',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'hambaallah@pojokberbagi.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2021-12-27',150000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'hambaallah@pojokberbagi.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%GOPAY%"),'2021-12-29',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'MIKSA'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-29',50000,1),
(2,(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'PEPEN'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-29',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'WIDI HERMAWAN'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2021-12-30',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ROHAYATI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2021-12-30',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ROHAYATI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2021-12-30',50000,1),
(2,(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'NUNUNG SUTINI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-31',100000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'program pojok peduli'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'hambaallah@pojokberbagi.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-31',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'UTI FARZA'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-31',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'YENI MARIYANI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-31',50000,1),
(2,(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'YAHYA'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-31',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli semeru'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'SUJANAH'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-31',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'hambaallah@pojokberbagi.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-31',10000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ASIH SALIMA NURRAHMAN'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2022-01-05',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli semeru'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ENDANG NURAHMAN'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2022-01-05',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli semeru'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'pojokberbagi.id@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-01-05',750000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'NENENG HILAWATY DJAJADIJAKARTA'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2022-01-05',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'jemput ambulance'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'SUPRIYADI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-01-12',250000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'jemput ambulance'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'SUMIYATI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-01-13',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'DITA HALIFATUS SADIAH'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%GOPAY%"),'2022-01-14',25000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'hambaallah@pojokberbagi.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%GOPAY%"),'2022-01-14',25000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'hambaallah@pojokberbagi.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2022-01-14',250000,1),
(2,(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'SELLY MARSELIANI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%DANA%"),'2022-01-20',10000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'jemput ambulance'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'SUPRIYADI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-01-21',250000,1),
(2,(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'hambaallah@pojokberbagi.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-31',10000,1),
(2,(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'hambaallah@pojokberbagi.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-31',10000,1),
(2,(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'hambaallah@pojokberbagi.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-31',10000,1),
(2,(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'hambaallah@pojokberbagi.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-31',10000,1),
(2,(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'hambaallah@pojokberbagi.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2021-12-31',10000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'jemput ambulance'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'SUPRIYADI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-01-22',250000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'jemput ambulance'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'SUPRIYADI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-01-22',200000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'DITA HALIFATUS SADIAH'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%GOPAY%"),'2022-01-28',25000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'DITA HALIFATUS SADIAH'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%GOPAY%"),'2022-01-28',25000,1),
(2,(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'DITA HALIFATUS SADIAH'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%GOPAY%"),'2022-01-28',50000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'YOHANAH'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BJB%"),'2022-01-05',25000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'WARDI (ALM)'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BJB%"),'2022-01-05',25000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'program pojok yatim'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'SELLY MARSELIANI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%DANA%"),'2022-02-08',10000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'OOM (ALM)'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-02-18',500000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'WARDI (ALM)'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%GOPAY%"),'2022-02-25',10000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'YOHANAH'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%GOPAY%"),'2022-02-26',10000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ROHAYATI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%GOPAY%"),'2022-02-26',10000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ENDANG KARTIWA (ALM)'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%GOPAY%"),'2022-02-27',10000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'maulinda.dinda98@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%GOPAY%"),'2022-02-28',10000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'jafarpager@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-03-08',1300000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'program pojok yatim'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'SELLY MARSELIANI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%DANA%"),'2022-03-14',10000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'WARDI (ALM)'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%DANA%"),'2022-03-18',20000,1),
(1,(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'csr@bjb.co.id'),(SELECT id_cp FROM channel_payment WHERE jenis = 'GI' AND UPPER(nama) LIKE "%BJB%"),'2022-03-18',3125000000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'YOHANAH'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%DANA%"),'2022-03-18',20000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ROHAYATI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%DANA%"),'2022-03-18',10000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ENDANG NURAHMAN'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BJB%"),'2022-03-21',20000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) LIKE "%lebaran untuk yatim%"),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ENDANG NURAHMAN'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BJB%"),'2022-03-21',250000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) LIKE "%qur'an%"),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ENDANG NURAHMAN'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BJB%"),'2022-03-21',100000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'arifriandi834@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-04-02',100000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'peduli razka'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'jafarpager@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-04-05',350000,1),
((SELECT id_bantuan FROM bantuan WHERE UPPER(nama) = 'BERBAGI 1.000 PAKET BERBUKA' AND blokir IS NULL),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'TEH DANDANG'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BJB%"),'2022-04-09',1000000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'program pojok berdaya'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'rizky.edu@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2022-04-11',500000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'BERBAGI 1.000 PAKET BERBUKA' AND blokir IS NULL),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'DAPUR AQIQAH'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BJB%"),'2022-04-21',1250000,1),
((SELECT id_bantuan FROM bantuan WHERE UPPER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'GIBRAN RIZKI PRATAMA'),(SELECT id_cp FROM channel_payment WHERE jenis = 'QR' AND UPPER(nama) LIKE "%BRI%"),'2022-04-22',250000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'arifriandi834@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-04-22',200000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'BERBAGI 1.000 PAKET BERBUKA' AND blokir IS NULL),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'FSLDK'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BRI%"),'2022-04-25',25000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'BERBAGI 1.000 PAKET BERBUKA' AND blokir IS NULL),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'KAMMI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BSI%"),'2022-04-25',250000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'maulinda.dinda98@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'EW' AND UPPER(nama) LIKE "%GOPAY%"),'2022-04-28',39750,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) LIKE "%lebaran untuk yatim & dhuafa%" AND blokir IS NULL),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'YBM BRI KC. ASIA AFRIKA'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BRI%"),'2022-04-28',6000000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'jafarpager@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-05-12',100000,1),
((SELECT id_bantuan FROM bantuan WHERE UPPER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'DINNY RESTY'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BRI%"),'2022-07-08',100000,1),
((SELECT id_bantuan FROM bantuan WHERE UPPER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ETI SUMIATI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BRI%"),'2022-07-08',300000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'arifriandi834@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-07-14',150000,1),
((SELECT id_bantuan FROM bantuan WHERE UPPER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ETI SUMIATI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BRI%"),'2022-07-14',300000,1);

UPDATE donasi SET waktu_bayar = create_at WHERE bayar = '1' AND waktu_bayar IS NULL;


CREATE TABLE anggaran_pelaksanaan_donasi (
    nominal_penggunaan_donasi INT UNSIGNED NOT NULL,
    nominal_kebutuhan INT UNSIGNED NOT NULL,
    saldo_kebutuhan INT UNSIGNED NOT NULL,
    saldo_donasi INT UNSIGNED NOT NULL,
    keterangan VARCHAR(50),
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_kebutuhan INT UNSIGNED,
    id_pelaksanaan INT UNSIGNED,
    id_donasi BIGINT UNSIGNED,
    CONSTRAINT F_ID_KEBUTUHAN_ANGGARAN_KEBUTUHAN_DONASI_ODN FOREIGN KEY(id_kebutuhan) REFERENCES kebutuhan(id_kebutuhan) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT F_ID_PELAKSANAAN_ANGGARAN_KEBUTUHAN_DONASI_ODR FOREIGN KEY(id_pelaksanaan) REFERENCES pelaksanaan(id_pelaksanaan) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_DONASI_ANGGARAN_KEBUTUHAN_DONASI_ODC FOREIGN KEY(id_donasi) REFERENCES donasi(id_donasi) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

INSERT INTO kebutuhan(nama) VALUES('RAB CSR BJB SEMBAKO'),
('RAB GEBERR'),('RAB CSR KURBAN');
INSERT INTO anggaran_pelaksanaan_donasi (id_donasi, id_pelaksanaan, id_kebutuhan, nominal_kebutuhan, nominal_penggunaan_donasi, saldo_kebutuhan, saldo_donasi, keterangan) VALUES
(1,1,1,2312500000,2312500000,0,0,"TOTAL RAB"),
(2,2,1,3125000000,3125000000,0,0,"TOTAL RAB"),
(3,3,1,750000,750000,0,0,"TOTAL RAB");

--
--
-- Data tabel pengunjung perlu ditambahkan kolom baru yaitu geoloc ip(kota, kodepos, lat, mat) isinya bisa dimasukan ke client_key, atau menjadi masing2 kolom tersendiri
--  
--

CREATE TABLE pengunjung (
    id_pengunjung INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(255) NOT NULL,
    client_key VARCHAR(255) NOT NULL,
    device_id VARCHAR(32),
    device_type VARCHAR(15),
    os VARCHAR(30),
    os_bit VARCHAR(15),
    browser VARCHAR(30),
    browser_version TINYINT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT UN_IP_ADDRESS_CLIENT_KEY_PENGUNJUNG UNIQUE(ip_address, client_key)
)ENGINE=INNODB;

CREATE TABLE amin (
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_pengunjung INT UNSIGNED,
    id_donasi BIGINT UNSIGNED,
    CONSTRAINT F_ID_PENGUNJUNG_AMIN_ODN FOREIGN KEY(id_pengunjung) REFERENCES pengunjung(id_pengunjung) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT F_ID_DONASI_AMIN_ODC FOREIGN KEY(id_donasi) REFERENCES donasi(id_donasi) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE akun_pengunjung (
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_akun INT UNSIGNED,
    id_pengunjung INT UNSIGNED,
    CONSTRAINT F_ID_AKUN_AKUN_PENGUNJUNG_ODR FOREIGN KEY(id_akun) REFERENCES akun(id_akun) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_PENGUNJUNG_AKUN_PENGUNJUNG_ODR FOREIGN KEY(id_pengunjung) REFERENCES pengunjung(id_pengunjung) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE halaman (
    id_halaman INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    uri VARCHAR(255) NOT NULL,
    path VARCHAR(255) NOT NULL,
    track CHAR(1) NOT NULL DEFAULT '1',
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT UN_URI_HALAMAN UNIQUE(uri),
    CONSTRAINT UN_PATH_HALAMAN UNIQUE(path)
)ENGINE=INNODB;

CREATE TABLE kunjungan (
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_pengunjung INT UNSIGNED NOT NULL,
    id_halaman INT UNSIGNED,
    CONSTRAINT F_ID_PENGUNJUNG_KUNJUNGAN_ODC FOREIGN KEY(id_pengunjung) REFERENCES pengunjung(id_pengunjung) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_HALAMAN_KUNJUNGAN_ODN FOREIGN KEY(id_halaman) REFERENCES halaman(id_halaman) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT U_ID_PENGUNJUNG_ID_HALAMAN_CREATE_AT UNIQUE(id_pengunjung,id_halaman,create_at)
)ENGINE=INNODB;

CREATE TABLE banner (
    id_banner BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_bantuan INT UNSIGNED,
    CONSTRAINT F_ID_BANTUAN_BANNER_ODC FOREIGN KEY(id_bantuan) REFERENCES bantuan(id_bantuan) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

INSERT INTO banner(id_bantuan) VALUES(1),
(2),
(3),
(4);

DROP FUNCTION IF EXISTS formatTanggalFull;
DELIMITER $$
CREATE FUNCTION formatTanggalFull(tanggal TIMESTAMP)
  RETURNS VARCHAR(255) DETERMINISTIC
BEGIN
  DECLARE varhasil varchar(255);

  SELECT CONCAT(
    CASE DAYOFWEEK(tanggal)
      WHEN 1 THEN 'Minggu'
      WHEN 2 THEN 'Senin'
      WHEN 3 THEN 'Selasa'
      WHEN 4 THEN 'Rabu'
      WHEN 5 THEN 'Kamis'
      WHEN 6 THEN 'Jumat'
      WHEN 7 THEN 'Sabtu'
    END,', ',
    DAY(tanggal),' ',
    CASE MONTH(tanggal) 
      WHEN 1 THEN 'Januari' 
      WHEN 2 THEN 'Februari' 
      WHEN 3 THEN 'Maret' 
      WHEN 4 THEN 'April' 
      WHEN 5 THEN 'Mei' 
      WHEN 6 THEN 'Juni' 
      WHEN 7 THEN 'Juli' 
      WHEN 8 THEN 'Agustus' 
      WHEN 9 THEN 'September'
      WHEN 10 THEN 'Oktober' 
      WHEN 11 THEN 'November' 
      WHEN 12 THEN 'Desember' 
    END,' ',
    YEAR(tanggal),' ',
    TIME(tanggal)
  ) INTO varhasil;

  RETURN varhasil;
END $$
DROP FUNCTION IF EXISTS formatTanggal $$
CREATE FUNCTION formatTanggal(tanggal DATE)
  RETURNS VARCHAR(255) DETERMINISTIC
BEGIN
  DECLARE varhasil varchar(255);

  SELECT CONCAT(
    CASE DAYOFWEEK(tanggal)
        WHEN 1 THEN 'Minggu'
        WHEN 2 THEN 'Senin'
        WHEN 3 THEN 'Selasa'
        WHEN 4 THEN 'Rabu'
        WHEN 5 THEN 'Kamis'
        WHEN 6 THEN 'Jumat'
        WHEN 7 THEN 'Sabtu'
    END,', ',
    DAY(tanggal),' ',
    CASE MONTH(tanggal) 
        WHEN 1 THEN 'Januari' 
        WHEN 2 THEN 'Februari' 
        WHEN 3 THEN 'Maret' 
        WHEN 4 THEN 'April' 
        WHEN 5 THEN 'Mei' 
        WHEN 6 THEN 'Juni' 
        WHEN 7 THEN 'Juli' 
        WHEN 8 THEN 'Agustus' 
        WHEN 9 THEN 'September'
        WHEN 10 THEN 'Oktober' 
        WHEN 11 THEN 'November' 
        WHEN 12 THEN 'Desember' 
    END,' ',
    YEAR(tanggal)
  ) INTO varhasil;

  RETURN varhasil;
END $$
DELIMITER ;

-- Catatan baris ini wajib dihapus jika sudah db host disesuaikan;
--
-- this query must run on host then delete it or comment it after run on server
--
-- delete from gambar;
-- alter table gambar AUTO_INCREMENT = 0;
-- INSERT INTO gambar(nama,path_gambar, label, gembok) VALUES('default','/assets/images/default.png','avatar','1'),

-- update channel_payment set id_gambar = (SELECT id_gambar FROM gambar WHERE LOWER(nama) = "bank-bjb") WHERE nama LIKE '%Bank BJB%';
-- update channel_payment set id_gambar = (SELECT id_gambar FROM gambar WHERE LOWER(nama) = "bank-bsi") WHERE nama LIKE '%Bank BSI%' AND jenis = 'TB';
-- update channel_payment set id_gambar = (SELECT id_gambar FROM gambar WHERE LOWER(nama) = "bank-bri") WHERE nama LIKE '%Bank BRI%' AND jenis = 'TB';

-- alter table bantuan ADD COLUMN action_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER modified_at;
-- update bantuan set action_at = create_at;

-- alter table bantuan modify column nama VARCHAR(50) NOT NULL;
-- alter table bantuan modify column satuan_target VARCHAR(15);

-- alter table bantuan drop INDEX UN_NAMA_BANTUAN;
-- alter table bantuan add column tag VARCHAR(30) after nama;
-- alter table bantuan add CONSTRAINT U_TAG_BANTUAN UNIQUE(tag);

-- alter table bantuan drop foreign key F_ID_GAMBAR_BANTUAN_ODN;
-- ALTER TABLE bantuan CHANGE id_gambar id_gambar_medium int unsigned;
-- alter table bantuan add CONSTRAINT F_ID_GAMBAR_MEDIUM_BANTUAN_ODN FOREIGN KEY(id_gambar_medium) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE;
-- alter table bantuan add column id_gambar_wide INT UNSIGNED AFTER id_gambar_medium;
-- alter table bantuan add CONSTRAINT F_ID_GAMBAR_WIDE_BANTUAN_ODN FOREIGN KEY(id_gambar_wide) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE;

-- update bantuan set id_gambar_medium = 7, id_gambar_wide = 8 where id_bantuan = 1;
-- update bantuan set id_gambar_medium = 9, id_gambar_wide = 10 where id_bantuan = 2;
-- update bantuan set id_gambar_medium = 11, id_gambar_wide = 12 where id_bantuan = 3;
-- update bantuan set id_gambar_medium = 13, id_gambar_wide = 14 where id_bantuan = 4;
-- update bantuan set id_gambar_medium = 15, id_gambar_wide = 16 where id_bantuan = 5;

-- update akun set id_gambar = 1;

-- Hapus kolom id_pelaksanaan foreign key id_pelaksanaan di tabel donasi lama salah juga nama Constrainnya
-- ALTER TABLE donasi DROP FOREIGN KEY F_ID_PELAKSANAAN_DONATUR_ODN;
-- ALTER TABLE donasi DROP INDEX F_ID_PELAKSANAAN_DONATUR_ODN;
-- ALTER TABLE donasi DROP COLUMN id_pelaksanaan;

-- Perbaikan salah penamaan constraint FK
-- ALTER TABLE donasi DROP FOREIGN KEY F_ID_BANTUAN_DONATUR_ODR;
-- ALTER TABLE donasi DROP INDEX F_ID_BANTUAN_DONATUR_ODR;
-- ALTER TABLE donasi ADD CONSTRAINT F_ID_BANTUAN_DONASI_ODR FOREIGN KEY(id_bantuan) REFERENCES bantuan(id_bantuan) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ALTER TABLE donasi DROP FOREIGN KEY F_ID_DONATUR_DONATUR_ODR;
-- ALTER TABLE donasi DROP INDEX F_ID_DONATUR_DONATUR_ODR;
-- ALTER TABLE donasi ADD CONSTRAINT F_ID_DONATUR_DONASI_ODR FOREIGN KEY(id_donatur) REFERENCES donatur(id_donatur) ON DELETE RESTRICT ON UPDATE CASCADE;

-- Penambahan Jenis CP
-- ALTER table channel_payment modify jenis ENUM('TB','VA','EW','QR','GM','GI','TN') NOT NULL;

-- Penambahan Total Anggaran
-- ALTER TABLE pelaksanaan ADD column total_anggaran INT UNSIGNED AFTER jumlah_pelaksanaan;
-- update pelaksanaan set total_anggaran = 2312500000 where id_pelaksanaan = 1;
-- update pelaksanaan set total_anggaran = 3125000000 where id_pelaksanaan = 2;
-- update pelaksanaan set total_anggaran = 750000 where id_pelaksanaan = 3;

-- Sementara Pakai RAB
-- INSERT INTO kebutuhan(nama) VALUES('RAB CSR BJB SEMBAKO'),
-- ('RAB GEBERR'),('SANTUNAN');

-- Tambah CP Tunai Via CR
-- INSERT INTO channel_payment(nama, kode, nomor, jenis) VALUES('Tunai Via CR', '1', '1', 'TN');

-- Jika Belum ada
-- INSERT INTO donasi(id_bantuan,id_donatur,alias,jumlah_donasi, bayar, id_cp, waktu_bayar) VALUES(2,2,"PROGRAM",750000,1, (SELECT id_cp FROM channel_payment WHERE nomor = 1 AND jenis = 'TN'), '2021-10-16');

-- Tambah BJB GI dan update BJB TB Sembako ke GI
-- ALTER TABLE channel_payment MODIFY nama VARCHAR(25) NOT NULL;
-- INSERT INTO channel_payment(nama, kode, nomor, atas_nama, jenis, id_gambar) VALUES('Bank BJB Giro Payment', '110', '0001000080001', 'POJOK BERBAGI INDONESIA', 'GI', 4);
-- UPDATE donasi JOIN bantuan USING(id_bantuan) SET id_cp = (SELECT id_cp FROM channel_payment WHERE kode = '110' AND jenis = 'GI') WHERE id_cp = 1 AND id_bantuan = 1;

-- ALTER CP Constraint 
-- ALTER TABLE channel_payment DROP INDEX U_NAMA_NOMOR_CHANNEL_PAYMENT;
-- ALTER TABLE channel_payment ADD CONSTRAINT U_NAMA_NOMOR_JENIS_CHANNEL_PAYMENT UNIQUE(nama,nomor,jenis);

-- INSERT INTO gambar(nama,path_gambar,label,gembok) VALUES('uang tunai','/assets/images/partners/tunai.png','partner',1);
-- UPDATE channel_payment SET id_gambar = (SELECT id_gambar FROM gambar WHERE nama = 'uang tunai') WHERE jenis = 'TN' AND nama LIKE '%Tunai%';


-- MENYESUAIKAN DENGAN DATA DONATUR YANG ADA
-- ALTER TABLE donatur MODIFY email VARCHAR(96);
-- ALTER TABLE donatur RENAME INDEX NU_EMAIL_DONATUR TO U_EMAIL_DONATUR;


-- DELIMITER $$
-- CREATE TRIGGER BANTUAN_CHECK_UPDATE
-- BEFORE UPDATE ON bantuan FOR EACH ROW
--     BEGIN
--         IF OLD.status <> NEW.status THEN
--         SET NEW.action_at = NOW();
--         END IF;
--     END$$
-- DELIMITER ;

-- ALTER TABLE bantuan ADD COLUMN prioritas CHAR(1) AFTER status;

-- KHUSUS LOCAL BIAR TIDAK CREATE EMAIL TERUS
ALTER TABLE donasi MODIFY notifikasi CHAR(1) DEFAULT '1';
-- KHUSUS SERVER WAJIB
ALTER TABLE donasi MODIFY notifikasi CHAR(1) DEFAULT NULL;

-- JIKA DONATUR SYSROOT BELUM ADA FK KE AKUN
-- UPDATE donatur SET id_akun = (SELECT id_akun FROM akun WHERE email = 'pojokberbagi.id@gmail.com') WHERE email = 'pojokberbagi.id@gmail.com';

-- ALTER TABLE pegawai DROP INDEX NU_JENIS_KELAMIN;

-- DELIMITER $$
-- CREATE TRIGGER DONASI_CHECK_UPDATE
-- BEFORE UPDATE ON donasi FOR EACH ROW
--     BEGIN
--     DECLARE kwitansi_count TINYINT DEFAULT 0;
--     SET kwitansi_count = (SELECT COUNT(id_kwitansi) FROM kwitansi WHERE id_donasi = OLD.id_donasi);
--         IF OLD.bayar = 1 AND NEW.bayar = 0 THEN
--         SET NEW.waktu_bayar = NULL;
--             IF (kwitansi_count = 1) THEN
--             UPDATE kwitansi SET create_at = NULL WHERE id_donasi = OLD.id_donasi;
--             END IF;
--         ELSE
--             IF (kwitansi_count = 0) THEN
--             INSERT INTO kwitansi(create_at,id_donasi) VALUES(NEW.waktu_bayar,OLD.id_donasi);
--             ELSE 
--             UPDATE kwitansi SET create_at = NEW.waktu_bayar WHERE id_donasi = OLD.id_donasi;
--             END IF;
--         END IF;
--     END$$
-- DELIMITER ;

-- ALTER TABLE bantuan MODIFY total_rab BIGINT UNSIGNED DEFAULT NULL;

CREATE TABLE amin (
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_pengunjung INT UNSIGNED,
    id_donasi BIGINT UNSIGNED,
    CONSTRAINT F_ID_PENGUNJUNG_AMIN_ODN FOREIGN KEY(id_pengunjung) REFERENCES pengunjung(id_pengunjung) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT F_ID_DONASI_AMIN_ODC FOREIGN KEY(id_donasi) REFERENCES donasi(id_donasi) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

-- CREATE TABLE kwitansi (
--     id_kwitansi BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     waktu_cetak TIMESTAMP NULL,
--     create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     id_donasi BIGINT UNSIGNED,
--     id_pengesah SMALLINT UNSIGNED,
--     CONSTRAINT F_ID_DONASI_KWITANSI_ODR FOREIGN KEY(id_donasi) REFERENCES donasi(id_donasi) ON DELETE RESTRICT ON UPDATE CASCADE,
--     CONSTRAINT F_ID_PENGESAH_KWITANSI_ODR FOREIGN KEY(id_pengesah) REFERENCES pegawai(id_pegawai) ON DELETE RESTRICT ON UPDATE CASCADE
-- )ENGINE=INNODB;

-- ALTER TABLE kwitansi AUTO_INCREMENT = 1001;

-- INSERT INTO kwitansi(create_at, id_donasi)
-- SELECT waktu_bayar, id_donasi FROM donasi WHERE waktu_bayar IS NOT NULL ORDER BY waktu_bayar ASC, id_donasi ASC;

UPDATE kwitansi SET id_pengesah = (SELECT id_pegawai FROM pegawai WHERE email = 'maulinda.dinda98@gmail.com');

-- DELIMITER $$
-- CREATE TRIGGER DONASI_CHECK_INSERT
-- AFTER INSERT ON donasi FOR EACH ROW
--     BEGIN
--     IF NEW.bayar = 1 AND NEW.waktu_bayar IS NOT NULL THEN
--         INSERT INTO kwitansi(create_at,id_donasi) VALUES(NEW.waktu_bayar,NEW.id_donasi);
--     END IF;
--     END$$
-- DELIMITER ;

-- CEK JIKA BELUM INSERT DONASI UNTUK BANTUAN KURBAN
-- INSERT INTO donasi(alias,id_donatur,jumlah_donasi,bayar,waktu_bayar,create_at,modified_at,id_cp,id_bantuan) VALUES
-- ((SELECT samaran FROM donatur WHERE email = 'csr@bjb.co.id'), (SELECT id_donatur FROM donatur WHERE email = 'csr@bjb.co.id'), 1185000000,1,'2022-07-08','2022-07-08','2022-07-08',(SELECT id_cp FROM channel_payment WHERE jenis = 'GI' AND nomor = '0001000080001'),(SELECT id_bantuan FROM bantuan WHERE UPPER(nama) = 'RAIH SURGA BERSAMA POJOK QURBAN'));

-- INSERT INTO pelaksanaan(deskripsi,jumlah_pelaksanaan,total_anggaran,status,create_at) VALUES('Program Kurban BJB 2022', 43, 1185000000, 'S', '2022-07-09');
-- INSERT INTO kebutuhan(nama) VALUES('RAB CSR KURBAN');
-- INSERT INTO anggaran_pelaksanaan_donasi (id_donasi, id_pelaksanaan, id_kebutuhan, nominal_kebutuhan, nominal_penggunaan_donasi, saldo_kebutuhan, saldo_donasi, keterangan) VALUES
-- ((SELECT id_donasi FROM donasi WHERE alias = (SELECT alias FROM donatur WHERE email = 'csr@bjb.co.id') AND waktu_bayar = '2022-07-08 00:00:00' AND jumlah_donasi = 1185000000),(SELECT id_pelaksanaan FROM pelaksanaan WHERE deskripsi = 'Program Kurban BJB 2022'),(SELECT id_kebutuhan FROM kebutuhan WHERE nama = 'RAB CSR KURBAN'),1185000000,1185000000,0,0,'TOTAL RAB');

-- UPDATE bantuan SET tanggal_awal = create_at, tanggal_akhir = DATE_ADD(create_at, INTERVAL lama_penayangan DAY)  WHERE lama_penayangan IS NOT NULL

-- ALTER TABLE pegawai ADD COLUMN id_tanda_tangan INT UNSIGNED;
-- INSERT INTO gambar(nama,path_gambar,label) VALUES('jafar-signature','/uploads/images/signature/jafar-signature.png','signature');
-- UPDATE pegawai SET id_tanda_tangan = (SELECT id_gambar FROM gambar WHERE label = 'signature' AND nama = 'jafar-signature') WHERE email = 'jafarpager@gmail.com';
-- ALTER TABLE pegawai ADD CONSTRAINT F_ID_TANDA_TANGAN_PEGAWAI_ODN FOREIGN KEY(id_tanda_tangan) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE;