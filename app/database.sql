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

CREATE TABLE gambar (
    id_gambar INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    path_gambar VARCHAR(255),
    label VARCHAR(11),
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

INSERT INTO gambar(nama,path_gambar, label, gembok) VALUES
('default','/assets/images/default.png','avatar','1'),
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
('Qris','/assets/images/partners/qris.png','partner',1),
('jafar-signature','/uploads/images/signature/jafar-signature.png','signature','1'),
('bank-mandiri','/assets/images/partners/mandiri.png','partner','1'),
('shopeepay','/assets/images/partners/shopeepay.png','partner','1');

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
('JAFAR PAGER JAYA','L','1992-08-07','jafarpager@gmail.com','085322661186','BUMI HARAPAN BLOK DD 8 NO 10', (SELECT id_gambar FROM gambar WHERE label = 'signature' AND nama = 'jafar-signature')),
('TAUFIK ISKANDAR DINATA .K', 'L','1985-07-31','taufik454@gmail.com','081320185886','Kp. Sadang No.5 RT.004/008 Kelurahan Margahayu, Bandung'),
('DINDA MAULINDA','P','1998-07-09','maulinda.dinda98@gmail.com','0895389564902','Kopo Permai 1 Blok A No.14 Sukamenak, Margahayu, Kab. Bandung');

UPDATE pegawai SET id_jabatan = 1 WHERE nama = 'SYSROOT' AND email = 'pojokberbagi.id@gmail.com';

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
    CONSTRAINT NU_EMAIL_AKUN UNIQUE(email),
    CONSTRAINT F_ID_GAMBAR_AKUN_ODN FOREIGN KEY(id_gambar) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=INNODB;

INSERT INTO akun(username,password,email,aktivasi,salt,hak_akses) VALUES('Pojok Berbagi', '595bfd08e98ea60c77d9949233761d0b', 'pojokberbagi.id@gmail.com', '1', '214fdcc52049c81fe814d92778168771', 'A'),('Jadi Anak Sholeh', '595bfd08e98ea60c77d9949233761d0b', 'jafarpager@gmail.com', '1', '214fdcc52049c81fe814d92778168771', 'A');

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
    jenis_kelamin ENUM('P','L'),
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
    nama VARCHAR(75) NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_pemohon INT UNSIGNED,
    id_gambar INT UNSIGNED,
    CONSTRAINT F_ID_PEMOHON_UPLOAD_PP_ODC FOREIGN KEY(id_pemohon) REFERENCES pemohon(id_pemohon) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_GAMBAR_UPLOAD_PP_ODC FOREIGN KEY(id_gambar) REFERENCES gambar(id_gambar) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE kategori (
    id_kategori TINYINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nama VARCHAR(75) NOT NULL,
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

CREATE TABLE kategori_kebutuhan (
    id_kk TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(20)
)ENGINE=INNODB;
INSERT INTO kategori_kebutuhan(nama) VALUES('Jasa'),('Alat'),('Makanan'),('Minuman'),('Barang'),('Kendaraan');

CREATE TABLE kebutuhan (
    id_kebutuhan INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nama VARCHAR(75) NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_kk TINYINT UNSIGNED,
    CONSTRAINT F_ID_KK_KEBUTUHAN_ODN FOREIGN KEY(id_kk) REFERENCES kategori_kebutuhan(id_kk) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT UN_NAMA_KEBUTUHAN UNIQUE(nama)
)ENGINE=INNODB;

-- FETURE
-- CREATE TABLE pengajuan (
--     id_pengajuan INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
--     nama VARCHAR(75) NOT NULL,
--     nama_penerima VARCHAR(75),
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
    nama VARCHAR(75) NOT NULL,
    tag VARCHAR(50),
    blokir ENUM('1') DEFAULT NULL,
    status ENUM('B','C','T','D','S') NOT NULL DEFAULT 'B',
    prioritas CHAR(1) DEFAULT NULL,
    nama_penerima VARCHAR(75),
    satuan_target VARCHAR(15),
    jumlah_target INT UNSIGNED,
    min_donasi INT UNSIGNED,
    total_rab BIGINT UNSIGNED DEFAULT NULL,
    lama_penayangan SMALLINT UNSIGNED,
    tanggal_awal DATE,
    tanggal_akhir DATE,
    deskripsi VARCHAR(255) NOT NULL,
    id_video_youtube VARCHAR(100),
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

CREATE TABLE deskripsi (
    id_deskripsi INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(100) NOT NULL,
    isi TEXT,
    id_bantuan INT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_BANTUAN_deskripsi_ODC FOREIGN KEY(id_bantuan) REFERENCES bantuan(id_bantuan) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE list_gambar_deskripsi (
    id_deskripsi INT UNSIGNED,
    id_gambar INT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_DESKRIPSI_LGS_ODN FOREIGN KEY(id_deskripsi) REFERENCES deskripsi(id_deskripsi) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT F_ID_GAMBAR_LGS_ODC FOREIGN KEY(id_gambar) REFERENCES gambar(id_gambar) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE video (
    id_video INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(255),
    label VARCHAR(10),
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT U_URL_VIDEO UNIQUE(url)
)ENGINE=INNODB;

CREATE TABLE video_deskripsi (
    id_video_deskripsi INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(255),
    id_deskripsi INT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_DESKRIPSI_VD_ODC FOREIGN KEY(id_deskripsi) REFERENCES deskripsi(id_deskripsi) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT U_URL_VD UNIQUE(url)
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
('BERBAGI 1.000 PAKET BERBUKA','S',10000,'2022-04-01','Hidangkan makanan berbuka puasa untuk saudara kita diluar sana yuk! Pojok Berbagi Indonesia menawarkan paket makanan berbuka lengkap yang terdiri dari takjil, makanan utama dan minuman untuk fakir miskin dan masyarakat lainnya.',3,'S'),
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

CREATE TABLE penyelenggara_jasa_pembayaran (
    id_pjp TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    kode CHAR(3) DEFAULT NULL,
    brand VARCHAR(50),
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT UN_NAMA_P UNIQUE(nama)
)ENGINE=INNODB;

INSERT INTO penyelenggara_jasa_pembayaran(nama, kode, brand) VALUES
('PT Bank Rakyat Indonesia (Persero), Tbk', '002', 'bri'),
('PT Bank Jabar Dan Banten', '110', 'bjb'),
('PT Bank Syariah Indonesia', '451', 'bsi'),
('PT Espay Debit Indonesia Koe', 'DAN', 'dana'),
('PT Dompet Anak Bangsa', 'GOP', 'gopay'),
('PT Bank Mandiri (Persero), Tbk','008', 'mandiri'),
('PT AirPay International Indonesia','SOP', 'shopeepay_app'),
('PT Fliptech Lentera Inspirasi Pertiwi', 'LIP', 'flip');
-- ('PT OVO', 'OVO');

CREATE TABLE channel_account (
    id_ca TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(30) NOT NULL,
    saldo BIGINT UNSIGNED NOT NULL DEFAULT 0,
    nomor VARCHAR(30) NOT NULL,
    atas_nama VARCHAR(30) NOT NULL,
    jenis ENUM('RB','EW','KT','PG') NOT NULL,
    block ENUM('0','1') NOT NULL DEFAULT '0',
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_pjp TINYINT UNSIGNED,
    CONSTRAINT F_ID_PJP_REKENING_ODR FOREIGN KEY(id_pjp) REFERENCES penyelenggara_jasa_pembayaran(id_pjp) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT U_NAMA_CHANNEL_ACCOUNT UNIQUE(nama)
)ENGINE=INNODB;

INSERT INTO channel_account(nama, nomor, atas_nama, jenis) VALUES
('Bank BRI', '107001000272300', 'POJOK BERBAGI INDONESIA', 'RB'),
('CR Kantor Pusat', 1, 'CR POJOK BERBAGI INDONESIA', 'KT'),
('Bank BJB', '0001000080001', 'POJOK BERBAGI INDONESIA', 'RB'),
('Bank BSI', '7400525255', 'POJOK BERBAGI INDONESIA', 'RB'),
('Dana', '081213331113', 'Pojok Berbagi', 'EW'),
('GoPay', '081213331113', 'Pojok Berbagi', 'EW'),
('Ovo', '081213331113', 'Pojok Berbagi', 'EW'),
('Bank Mandiri', '1320080829998', 'POJOK BERBAGI INDONESIA', 'RB'),
('ShopeePay','081213331113','Pojok Berbagi','EW'),
('Flip','pojokflip','PojokBerbagiID','PG');

UPDATE channel_account SET id_pjp = (SELECT id_pjp FROM penyelenggara_jasa_pembayaran WHERE kode = '002') WHERE nama LIKE '%BRI%';
UPDATE channel_account SET id_pjp = (SELECT id_pjp FROM penyelenggara_jasa_pembayaran WHERE kode = '110') WHERE nama LIKE '%BJB%';
UPDATE channel_account SET id_pjp = (SELECT id_pjp FROM penyelenggara_jasa_pembayaran WHERE kode = '451') WHERE nama LIKE '%BSI%';
UPDATE channel_account SET id_pjp = (SELECT id_pjp FROM penyelenggara_jasa_pembayaran WHERE nama = 'PT Espay Debit Indonesia Koe') WHERE nama LIKE '%Dana%';
UPDATE channel_account SET id_pjp = (SELECT id_pjp FROM penyelenggara_jasa_pembayaran WHERE nama = 'PT Dompet Anak Bangsa') WHERE nama LIKE '%GoPay%';
UPDATE channel_account SET id_pjp = (SELECT id_pjp FROM penyelenggara_jasa_pembayaran WHERE nama = 'OVO') WHERE nama LIKE '%OVO%';
UPDATE channel_account SET id_pjp = (SELECT id_pjp FROM penyelenggara_jasa_pembayaran WHERE kode = '008') WHERE nama LIKE '%Mandiri%';
UPDATE channel_account SET id_pjp = (SELECT id_pjp FROM penyelenggara_jasa_pembayaran WHERE nama = 'PT AirPay International Indonesia') WHERE nama LIKE '%ShopeePay%';
UPDATE channel_account SET id_pjp = (SELECT id_pjp FROM penyelenggara_jasa_pembayaran WHERE nama = 'PT Fliptech Lentera Inspirasi Pertiwi') WHERE nama LIKE '%Flip%';

CREATE TABLE channel_payment (
    id_cp TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(25) NOT NULL,
    kode CHAR(3) DEFAULT NULL,
    nomor VARCHAR(30) NOT NULL,
    atas_nama VARCHAR(30) NOT NULL,
    jenis ENUM('TB','VA','EW','QR','GM','GI','TN') NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_ca TINYINT UNSIGNED,
    id_gambar INT UNSIGNED,
    CONSTRAINT U_NAMA_NOMOR_JENIS_CHANNEL_PAYMENT UNIQUE(nama, nomor, jenis),
    CONSTRAINT F_ID_CA_CHANNEL_PAYMENT_ODR FOREIGN KEY(id_ca) REFERENCES channel_account(id_ca) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_GAMBAR_CHANNEL_PAYMENT_ODN FOREIGN KEY(id_gambar) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=INNODB;

-- jenis channel_payment TB = Transfer Bank, VA = Virtual Acount, EW = E-Wallet, QR, Qris, GM = Gerai Mart, GI = GIRO, TN = Tunai

INSERT INTO channel_payment(nama, kode, nomor, jenis, atas_nama, id_gambar) VALUES
('Bank BJB', '110', '0001000080001', 'TB','POJOK BERBAGI INDONESIA', (SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%bjb%" AND label = 'partner')),
('Bank BJB Giro Payment', '110', '0001000080001', 'GI','POJOK BERBAGI INDONESIA', (SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%bjb%" AND label = 'partner')),
('Tunai Via CR', '1', '1', 'TN','CR POJOK BERBAGI KANTOR PUSAT', (SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%tunai%" AND label = 'partner')),
('Bank BSI','451','7400525255','TB','POJOK BERBAGI INDONESIA', (SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%bsi%" AND label = 'partner')),
('Bank BRI','002','107001000272300','TB','POJOK BERBAGI INDONESIA', (SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%bri%" AND label = 'partner')),
('GoPay','GOP','081213331113','EW','Pojok Berbagi',(SELECT id_gambar FROM gambar WHERE LOWER(nama) = 'gopay')),
('Dana','DAN','081213331113','EW','Pojok Berbagi',(SELECT id_gambar FROM gambar WHERE LOWER(nama) = 'dana')),
('Bank BRI','002','ID1022148253464','QR','POJOK BERBAGI INDONESIA',(SELECT id_gambar FROM gambar WHERE LOWER(nama) = 'qris')),
('Bank Mandiri','008','1320080829998','TB','POJOK BERBAGI INDONESIA',(SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%mandiri%" AND label = 'partner')),
('ShopeePay','SOP','081213331113','EW','Pojok Berbagi',(SELECT id_gambar FROM gambar WHERE LOWER(nama) = 'shopeepay')),
('Bank BRI','LIP','pojokflip','VA','PojokBerbagiID',(SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%bri%" AND label = 'partner')),
('Bank BNI','LIP','pojokflip','VA','PojokBerbagiID',(SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%bni%" AND label = 'partner')),
('Bank BCA','LIP','pojokflip','VA','PojokBerbagiID',(SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%bca%" AND label = 'partner')),
('Bank Mandiri','LIP','pojokflip','VA','PojokBerbagiID',(SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%mandiri%" AND label = 'partner')),
('Bank BSI','LIP','pojokflip','VA','PojokBerbagiID',(SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%bsi%" AND label = 'partner')),
('Bank CIMB','LIP','pojokflip','VA','PojokBerbagiID',(SELECT id_gambar FROM gambar WHERE LOWER(nama) LIKE "%cimb%" AND label = 'partner'));

UPDATE channel_payment cp, channel_account ca LEFT JOIN penyelenggara_jasa_pembayaran pjp USING(id_pjp) SET cp.id_ca = ca.id_ca WHERE cp.nama LIKE CONCAT('%',ca.nama,'%');

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
    kode_pembayaran VARCHAR(82),
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

-- bayar donasi 0 = pembayaran belum dilakukan, 1 = pembayaran berhasil;

CREATE TABLE virtual_ca_donasi (
    saldo BIGINT UNSIGNED NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_donasi BIGINT UNSIGNED NOT NULL,
    id_ca TINYINT UNSIGNED NOT NULL,
    CONSTRAINT F_ID_DONASI_VIRTUAL_CA_DONASI_ODR FOREIGN KEY(id_donasi) REFERENCES donasi(id_donasi) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_CA_VIRTUAL_CA_DONASI_ODR FOREIGN KEY(id_donasi) REFERENCES donasi(id_donasi) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT UN_ID_DONASI_ID_CA UNIQUE(id_donasi,id_ca)
)ENGINE=INNODB;

CREATE TABLE order_donasi (
    id_order_donasi BIGINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    url VARCHAR(255),
    external_id BIGINT UNSIGNED,
    kode_pembayaran VARCHAR(82),
    alias VARCHAR(30),
    kontak VARCHAR(13),
    doa VARCHAR(200),
    jumlah_donasi BIGINT UNSIGNED NOT NULL,
    status ENUM('PENDING','SUCCESSFUL','FAILED') DEFAULT 'PENDING',
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_bantuan INT UNSIGNED,
    id_donatur INT UNSIGNED,
    id_cp TINYINT UNSIGNED,
    CONSTRAINT U_EXTERNAL_ID_ORDER_DONASI UNIQUE(external_id),
    CONSTRAINT U_KODE_PEMBAYARAN_ORDER_DONASI UNIQUE(kode_pembayaran),
    CONSTRAINT F_ID_BANTUAN_ORDER_DONASI_ODR FOREIGN KEY(id_bantuan) REFERENCES bantuan(id_bantuan) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_DONATUR_ORDER_DONASI_ODR FOREIGN KEY(id_donatur) REFERENCES donatur(id_donatur) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_CP_ORDER_DONASI_ODN FOREIGN KEY(id_cp) REFERENCES channel_payment(id_cp) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=INNODB;

DROP TRIGGER BeforeInsertDonasi;
DELIMITER $$
CREATE TRIGGER BeforeInsertDonasi
BEFORE INSERT ON donasi FOR EACH ROW
    BEGIN
    DECLARE t_bantuan_blokir TINYINT UNSIGNED;
    SELECT blokir FROM bantuan WHERE id_bantuan = NEW.id_bantuan INTO t_bantuan_blokir;
    IF t_bantuan_blokir = '1' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to insert data: status campaign sedang diblokir!';
    END IF;
    END$$
DELIMITER ;

DROP TRIGGER DONASI_CHECK_INSERT;
DELIMITER $$
CREATE TRIGGER DONASI_CHECK_INSERT
AFTER INSERT ON donasi FOR EACH ROW
    BEGIN
    DECLARE t_id_ca TINYINT UNSIGNED;
    IF NEW.bayar = '1' AND NEW.waktu_bayar IS NOT NULL THEN
        SELECT id_ca FROM channel_payment WHERE id_cp = NEW.id_cp INTO t_id_ca;
        INSERT INTO kuitansi(create_at,id_donasi) VALUES(NEW.waktu_bayar,NEW.id_donasi);
        INSERT INTO virtual_ca_donasi(saldo,id_donasi,id_ca) VALUES(NEW.jumlah_donasi, NEW.id_donasi, t_id_ca);
        UPDATE channel_account SET saldo = saldo + NEW.jumlah_donasi WHERE id_ca = t_id_ca;
    END IF;
    END$$
DELIMITER ;

DROP TRIGGER DONASI_CHECK_UPDATE;
DELIMITER $$
CREATE TRIGGER DONASI_CHECK_UPDATE
BEFORE UPDATE ON donasi FOR EACH ROW
    LabelTBUDonasi:BEGIN
    DECLARE count_pelaksanaan TINYINT UNSIGNED;
    DECLARE c_pinbuk SMALLINT UNSIGNED;
    DECLARE t_pengunaan_donasi, t_saldo BIGINT UNSIGNED;
    SELECT IFNULL(COUNT(DISTINCT(apd.id_pelaksanaan)), 0) INTO count_pelaksanaan FROM donasi d JOIN anggaran_pelaksanaan_donasi apd USING(id_donasi) WHERE apd.id_donasi = OLD.id_donasi;

    IF count_pelaksanaan > 0 THEN
        SELECT SUM(nominal_penggunaan_donasi) FROM anggaran_pelaksanaan_donasi WHERE id_donasi = OLD.id_donasi INTO t_pengunaan_donasi;
        IF NEW.jumlah_donasi < t_pengunaan_donasi THEN
            SET @message_text = CONCAT_WS(' ','Donasi tidak bisa diubah jumlahnya karena sudah teranggarkan sejumlah', FORMAT(SUM(t_pengunaan_donasi),0,'id_ID'));
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message_text;
        END IF;
    END IF;

    SELECT ca.saldo FROM channel_payment cp JOIN channel_account ca USING(id_ca) WHERE cp.id_cp = OLD.id_cp INTO t_saldo;
    IF (NEW.jumlah_donasi - t_pengunaan_donasi) < t_saldo THEN
        SET @message_text = CONCAT_WS(' ','Donasi tidak bisa diubah jumlahnya saldo CA tidak mencukupi. [BeforeUpdate]');
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = @message_text;
    END IF;

    IF OLD.bayar = '1' AND NEW.bayar = '0' THEN
        SELECT COUNT(DISTINCT(id_donasi)) id_donasi FROM detil_pinbuk JOIN pinbuk WHERE status != 'OK' AND id_donasi = OLD.id_donasi INTO c_pinbuk;
        IF c_pinbuk > 0 THEN
            SET @message_text = CONCAT_WS(' ','Donasi tidak bisa dicencel karena tergabung dalam tahap pinbuk yang belum selesai. [BeforeUpdate]');
            SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = @message_text;
        END IF;

        SET NEW.waktu_bayar = NULL;  
    ELSEIF OLD.bayar = '0' AND NEW.bayar = '1' THEN
        SET NEW.waktu_bayar = NOW();
    ELSE
        LEAVE LabelTBUDonasi;
    END IF;
    END$$
DELIMITER ;

DROP TRIGGER AfterUpdateDonasi;
DELIMITER $$
CREATE TRIGGER AfterUpdateDonasi
AFTER UPDATE ON donasi FOR EACH ROW
LabelTAUDonasi:BEGIN
    DECLARE kuitansi_count, c_id_ca, t_id_ca, t_old_id_ca TINYINT UNSIGNED DEFAULT 0;
    DECLARE t_pengunaan_donasi BIGINT UNSIGNED;

    SELECT COUNT(id_kuitansi) FROM kuitansi WHERE id_donasi = NEW.id_donasi INTO kuitansi_count;

    IF OLD.bayar = '1' AND NEW.bayar = '0' THEN
        IF (kuitansi_count = 1) THEN
            UPDATE kuitansi SET create_at = NULL, id_pengesah = NULL WHERE id_donasi = NEW.id_donasi;
        END IF;

        UPDATE channel_account ca JOIN virtual_ca_donasi v USING(id_ca) SET ca.saldo = ca.saldo - v.saldo WHERE v.id_donasi = NEW.id_donasi;
        UPDATE virtual_ca_donasi SET saldo = 0 WHERE id_donasi = NEW.id_donasi;
    ELSEIF OLD.bayar = '0' AND NEW.bayar = '1' THEN
        IF (kuitansi_count = 1) THEN
            UPDATE kuitansi SET create_at = NOW() WHERE id_donasi = NEW.id_donasi;
        ELSE
            INSERT INTO kuitansi(create_at,id_donasi) VALUES(NEW.waktu_bayar,NEW.id_donasi);
        END IF;

        SELECT IFNULL(SUM(nominal_penggunaan_donasi),0) FROM anggaran_pelaksanaan_donasi WHERE id_donasi = NEW.id_donasi INTO t_pengunaan_donasi;
        SELECT id_ca FROM channel_payment WHERE id_cp = NEW.id_cp INTO t_id_ca;
        SELECT COUNT(id_ca) FROM virtual_ca_donasi WHERE id_donasi = NEW.id_donasi AND id_ca = t_id_ca INTO c_id_ca;
        IF c_id_ca = 0 THEN
            INSERT INTO virtual_ca_donasi(saldo,id_donasi,id_ca) VALUES(NEW.jumlah_donasi - t_pengunaan_donasi, NEW.id_donasi, t_id_ca);
        ELSE
            UPDATE virtual_ca_donasi SET saldo = NEW.jumlah_donasi - t_pengunaan_donasi WHERE id_donasi = NEW.id_donasi AND id_ca = t_id_ca;
        END IF;

        UPDATE channel_account SET saldo = saldo + NEW.jumlah_donasi - t_pengunaan_donasi WHERE id_ca = t_id_ca;
    ELSE
        IF NEW.bayar = '1' THEN
            SELECT IFNULL(SUM(nominal_penggunaan_donasi),0) FROM anggaran_pelaksanaan_donasi WHERE id_donasi = NEW.id_donasi INTO t_pengunaan_donasi;
            SELECT id_ca FROM channel_payment WHERE id_cp = NEW.id_cp INTO t_id_ca;
            SELECT id_ca FROM channel_payment WHERE id_cp = OLD.id_cp INTO t_old_id_ca;
            SELECT COUNT(id_ca) FROM virtual_ca_donasi WHERE id_donasi = NEW.id_donasi AND id_ca = t_id_ca INTO c_id_ca;

            UPDATE virtual_ca_donasi SET saldo = 0 WHERE id_donasi = NEW.id_donasi AND id_ca = t_old_id_ca;

            IF c_id_ca = 0 THEN
                INSERT INTO virtual_ca_donasi(saldo,id_donasi,id_ca) VALUES(NEW.jumlah_donasi - t_pengunaan_donasi, NEW.id_donasi, t_id_ca);
            ELSE
                UPDATE virtual_ca_donasi SET saldo = NEW.jumlah_donasi - t_pengunaan_donasi WHERE id_donasi = NEW.id_donasi AND id_ca = t_id_ca;
            END IF;

            UPDATE channel_account SET saldo = saldo - (OLD.jumlah_donasi - t_pengunaan_donasi) WHERE id_ca = t_old_id_ca;
            UPDATE channel_account SET saldo = saldo + (NEW.jumlah_donasi - t_pengunaan_donasi) WHERE id_ca = t_id_ca;
        END IF;
        LEAVE LabelTAUDonasi;
    END IF;
END$$
DELIMITER ;

CREATE TABLE kuitansi (
    id_kuitansi BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    waktu_cetak TIMESTAMP NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_donasi BIGINT UNSIGNED,
    id_pengesah SMALLINT UNSIGNED,
    id_pencetak SMALLINT UNSIGNED,
    CONSTRAINT F_ID_DONASI_KWITANSI_ODR FOREIGN KEY(id_donasi) REFERENCES donasi(id_donasi) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_PENGESAH_KWITANSI_ODR FOREIGN KEY(id_pengesah) REFERENCES pegawai(id_pegawai) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_PENGESAH_KWITANSI_ODN FOREIGN KEY(id_pencetak) REFERENCES pegawai(id_pegawai) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

ALTER TABLE kuitansi AUTO_INCREMENT = 1001;

CREATE TABLE permohonan_rencana (
    id_permohonan_rencana INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    id_bantuan INT UNSIGNED,
    id_pemohon INT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_BANTUAN_PERMOHONAN_RENCANA_ODR FOREIGN KEY(id_bantuan) REFERENCES bantuan(id_bantuan) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_PEMOHON_PERMOHONAN_RENCANA_ODR FOREIGN KEY(id_pemohon) REFERENCES pemohon(id_pemohon) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE ruang_diskusi_permohonan_rencana(
    id_ruang_diskusi INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    status ENUM('C','O') DEFAULT 'O' NOT NULL,
    id_permohonan_rencana INT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_PERMOHONAN_RENCANA_ODC FOREIGN KEY(id_ruang_diskusi) REFERENCES permohonan_rencana(id_permohonan_rencana) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE diskusi_permohonan_rencana(
    id_diskusi_permohonan_rencana INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    id_ruang_diskusi INT UNSIGNED,
    id_akun INT UNSIGNED,
    pesan TEXT NOT NULL,
    dibaca ENUM('1',NULL) DEFAULT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_RUANG_DISKUSI_DPR_ODC FOREIGN KEY(id_ruang_diskusi) REFERENCES ruang_diskusi_permohonan_rencana(id_ruang_diskusi_pr) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_AKUN_DPR_ODN FOREIGN KEY(id_akun) REFERENCES akun(id_akun) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE rencana (
    id_rencana INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    total_anggaran BIGINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('BD','SD','BP','TD') NOT NULL DEFAULT 'BD',
    keterangan VARCHAR(75),
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_bantuan INT UNSIGNED,
    id_pegawai SMALLINT UNSIGNED NOT NULL,
    CONSTRAINT F_ID_BANTUAN_RENCANA_ODR FOREIGN KEY(id_bantuan) REFERENCES bantuan(id_bantuan) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_PEMBUAT_RENCANA_ODR FOREIGN KEY(id_pegawai) REFERENCES pegawai(id_pegawai) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE perbaikan_rencana (
    id_perbaikan INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    pesan VARCHAR(200),
    dilihat TINYINT DEFAULT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_rencana INT UNSIGNED NOT NULL,
    CONSTRAINT F_ID_PERBAIKAN_PERBAIKAN_RENCANA_ODC FOREIGN KEY(id_rencana) REFERENCES rencana(id_rencana) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE rencana_anggaran_belanja (
    id_rab BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nominal_kebutuhan BIGINT UNSIGNED NOT NULL,
    harga_satuan BIGINT UNSIGNED NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    keterangan VARCHAR(75),
    id_kebutuhan INT UNSIGNED,
    id_rencana INT UNSIGNED NOT NULL,
    CONSTRAINT F_ID_KEBUTUHAN_RENCANA_ANGGARAN_BELANJA_ODR FOREIGN KEY(id_kebutuhan) REFERENCES kebutuhan(id_kebutuhan) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_RENCANA_RENCANA_ANGGARAN_BELANJA_ODC FOREIGN KEY(id_rencana) REFERENCES rencana(id_rencana) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT U_ID_KEBUTUHAN_ID_RENCANA_KETERANGAN_RENCANA_ANGGARAN_BELANJA UNIQUE(id_kebutuhan, id_rencana, keterangan)
)ENGINE=INNODB;

CREATE TABLE pelaksanaan (
    id_pelaksanaan INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    deskripsi VARCHAR(255),
    jumlah_pelaksanaan INT,
    total_anggaran BIGINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('P','J','S') NOT NULL DEFAULT 'P',
    id_rencana INT UNSIGNED NOT NULL,
    tanggal_eksekusi DATE,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_RENCANA_PELAKSANAAN_ODR FOREIGN KEY(id_rencana) REFERENCES rencana(id_rencana) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE anggaran_pelaksanaan_donasi (
    id_apd BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nominal_penggunaan_donasi INT UNSIGNED NOT NULL,
    nominal_kebutuhan BIGINT UNSIGNED NOT NULL,
    saldo_kebutuhan BIGINT UNSIGNED NOT NULL,
    saldo_donasi INT UNSIGNED NOT NULL,
    keterangan VARCHAR(75),
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_kebutuhan INT UNSIGNED,
    id_pelaksanaan INT UNSIGNED,
    id_donasi BIGINT UNSIGNED,
    CONSTRAINT F_ID_KEBUTUHAN_ANGGARAN_PELAKSANAAN_DONASI_ODR FOREIGN KEY(id_kebutuhan) REFERENCES kebutuhan(id_kebutuhan) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_PELAKSANAAN_ANGGARAN_PELAKSANAAN_DONASI_ODC FOREIGN KEY(id_pelaksanaan) REFERENCES pelaksanaan(id_pelaksanaan) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_DONASI_ANGGARAN_PELAKSANAAN_DONASI_ODR FOREIGN KEY(id_donasi) REFERENCES donasi(id_donasi) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

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
    id_akun INT UNSIGNED,
    CONSTRAINT F_ID_PENGUNJUNG_AMIN_ODN FOREIGN KEY(id_pengunjung) REFERENCES pengunjung(id_pengunjung) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT F_ID_DONASI_AMIN_ODC FOREIGN KEY(id_donasi) REFERENCES donasi(id_donasi) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_AKUN_AMIN_ODN FOREIGN KEY(id_akun) REFERENCES akun(id_akun) ON DELETE SET NULL ON UPDATE CASCADE
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
    id_banner TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_bantuan INT UNSIGNED,
    CONSTRAINT F_ID_BANTUAN_BANNER_ODC FOREIGN KEY(id_bantuan) REFERENCES bantuan(id_bantuan) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

INSERT INTO banner(id_bantuan) VALUES(1),
(2),
(3),
(4);

CREATE TABLE pencairan(
    id_pencairan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    total BIGINT UNSIGNED NOT NULL DEFAULT 10000,
    keterangan VARCHAR(255) NOT NULL,
    status ENUM('WTR','OP','OK') DEFAULT 'WTR',
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_petugas SMALLINT UNSIGNED,
    CONSTRAINT F_ID_PETUGAS_PENCAIRAN_ODR FOREIGN KEY(id_petugas) REFERENCES pegawai(id_pegawai) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE petugas_pencairan(
    id_petugas SMALLINT UNSIGNED,
    id_pencairan INT UNSIGNED,
    status ENUM('D','R') DEFAULT 'D',
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_PETUGAS_PETUGAS_PENCAIRAN_ODR FOREIGN KEY(id_petugas) REFERENCES pegawai(id_pegawai) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_PENCAIRAN_PETUGAS_PENCAIRAN_ODC FOREIGN KEY(id_pencairan) REFERENCES pencairan(id_pencairan) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

-- Cant' Create Trigger AfterInsertPetugasPencairan Conflick in Pencairan
-- status petugas_pencairan D = Perintah pencairan belum dibaca, R = Perintah pencairan sudah dibaca dan siap
-- DROP TRIGGER BeforeUpdatePetugasPencairan;
DELIMITER $$
CREATE TRIGGER BeforeUpdatePetugasPencairan
BEFORE UPDATE ON petugas_pencairan FOR EACH ROW
BEGIN
    DECLARE c_status TINYINT DEFAULT 0;
	IF UPPER(NEW.status) = 'R' AND UPPER(OLD.status) = 'D' THEN
        SELECT COUNT(p.id_pencairan) FROM pencairan p JOIN petugas_pencairan pp ON(p.id_pencairan = pp.id_pencairan) WHERE p.status = 'WTR' AND pp.status = 'D' AND p.id_pencairan = NEW.id_pencairan INTO c_status;
        IF c_status > 0 THEN
            UPDATE pencairan SET status = 'OP' WHERE id_pencairan = NEW.id_pencairan;
        END IF;
    END IF;
END$$
DELIMITER ;

DROP FUNCTION IF EXISTS timeAgo;
DELIMITER $$
CREATE FUNCTION timeAgo(waktu TIMESTAMP)
    RETURNS VARCHAR(100) DETERMINISTIC
BEGIN
    DECLARE time_ago VARCHAR(100);

    SELECT CASE 
        WHEN timestampdiff(year, waktu, current_timestamp) > 0 THEN CONCAT_WS(' ', timestampdiff(year, waktu, current_timestamp), 'tahun yang lalu')
        WHEN timestampdiff(day, waktu, current_timestamp) > 0 THEN CONCAT_WS(' ', timestampdiff(day, waktu, current_timestamp), 'hari yang lalu')
        WHEN timestampdiff(hour, waktu, current_timestamp) > 0 THEN CONCAT_WS(' ', timestampdiff(hour, waktu, current_timestamp), 'jam yang lalu')
        WHEN timestampdiff(minute, waktu, current_timestamp) > 0 THEN CONCAT_WS(' ', timestampdiff(minute, waktu, current_timestamp), 'menit yang lalu')
        WHEN waktu > current_timestamp THEN 'yang akan datang'
        ELSE 'beberapa saat yang lalu'
        END
    INTO time_ago;
    
    RETURN time_ago;
END $$

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

DELIMITER $$
CREATE PROCEDURE check_table_exists(IN table_name VARCHAR(100), OUT table_exists TINYINT) 
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLSTATE '42S02' SET @err = 1;
    SET @err = 0;
    SET @table_name = table_name;
    SET @sql_query = CONCAT('SELECT 1 FROM ',@table_name);
    PREPARE stmt1 FROM @sql_query;
    IF (@err = 1) THEN
        SET table_exists = 0;
    ELSE
        SET table_exists = 1;
        DEALLOCATE PREPARE stmt1;
    END IF;
END $$
DELIMITER ;

-- Procedur Ini Tidak boleh Langsung Diakses, Pengaksesan Wajib Lewat Procedure TotalAggaranBantuanPelaksanaan
-- DROP PROCEDURE KalkulasiAnggaranPelaksanaanDonasi;
DELIMITER $$
CREATE PROCEDURE KalkulasiAnggaranPelaksanaanDonasi(IN in_id_pelaksanaan INT, IN in_total_ap BIGINT, IN in_id_bantuan INT)
    BlockDonasi:BEGIN
        DECLARE t_nominal_penggunaan_donasi, t_id_rencana INT UNSIGNED;
        DECLARE total_penggunaan_donasi BIGINT DEFAULT 0;
        -- Menampung State Cursor
        DECLARE finished_donasi, finished_rab TINYINT DEFAULT 0;
        -- Var untuk menampung isi cursor donasi
        DECLARE cd_id_donasi INT;
        DECLARE cd_nominal_donasi INT;
        DECLARE cd_saldo_donasi INT;
        -- Var untuk menampung isi cursor rab
        DECLARE cr_id_kebutuhan INT;
        DECLARE cr_nominal_kebutuhan INT;
        DECLARE cr_keterangan VARCHAR(50);
        -- Var untuk menampung isi cursor hasil kalkulasi
        DECLARE ck_saldo_donasi INT;
        DECLARE ck_saldo_kebutuhan INT;

        DECLARE list_donasi CURSOR FOR
        (
            SELECT d.id_donasi, d.saldo_donasi penggunaan_donasi, 0 saldo_donasi FROM (
                (
                    SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = in_id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
                )
                UNION
                (
                    SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = in_id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = in_id_bantuan)
                ) 
            ) sd, 
            (
                (
                    SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = in_id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
                )
                UNION
                (
                    SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = in_id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = in_id_bantuan)
                ) 
            ) d 
            WHERE d.id_donasi >= sd.id_donasi
            GROUP BY d.id_donasi, d.saldo_donasi
            HAVING SUM(sd.saldo_donasi) < in_total_ap
            ORDER BY d.id_donasi
        )
        UNION
        (
            SELECT d.id_donasi, d.saldo_donasi - (SUM(sd.saldo_donasi) - in_total_ap) penggunaan_donasi, (SUM(sd.saldo_donasi) - in_total_ap) saldo_donasi FROM (
                (
                    SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = in_id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
                )
                UNION
                (
                    SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = in_id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = in_id_bantuan)
                ) 
            ) sd, 
            (
                (
                    SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = in_id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
                )
                UNION
                (
                    SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = in_id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = in_id_bantuan)
                ) 
            ) d 
            WHERE d.id_donasi >= sd.id_donasi
            GROUP BY d.id_donasi, d.saldo_donasi
            HAVING SUM(sd.saldo_donasi) >= in_total_ap
            ORDER BY d.id_donasi
            LIMIT 1
        );

         -- declare NOT FOUND handler
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_donasi = 1;

        SELECT id_rencana INTO t_id_rencana FROM pelaksanaan WHERE id_pelaksanaan = in_id_pelaksanaan;

        OPEN list_donasi;

        SET @insertQuery = "INSERT INTO anggaran_pelaksanaan_donasi(id_pelaksanaan,id_kebutuhan,id_donasi,nominal_kebutuhan,nominal_penggunaan_donasi,saldo_kebutuhan,saldo_donasi,keterangan) VALUES(?,?,?,?,?,?,?,?)";
        PREPARE stmt FROM @insertQuery;
        
        ListDonasiLoop:WHILE NOT finished_donasi DO
            FETCH list_donasi INTO cd_id_donasi, cd_nominal_donasi, cd_saldo_donasi;

            IF finished_donasi = 1 THEN
                LEAVE ListDonasiLoop;
            END IF;

            BlockRAB:BEGIN
                DECLARE list_rab CURSOR FOR 
                WITH cte AS (
                    SELECT MIN(saldo_kebutuhan) saldo_kebutuhan, id_kebutuhan FROM anggaran_pelaksanaan_donasi WHERE id_pelaksanaan = in_id_pelaksanaan GROUP BY id_kebutuhan
                ) 
                SELECT r3.nominal_kebutuhan, r3.id_kebutuhan, keterangan
                FROM
                (
                    ( 
                    SELECT r1.id_rab, r1.id_kebutuhan, r1.nominal_kebutuhan
                    FROM (
                        SELECT rab.id_rab, rab.id_kebutuhan, IFNULL(cte.saldo_kebutuhan, rab.nominal_kebutuhan) nominal_kebutuhan FROM 
                        rencana_anggaran_belanja rab 
                        LEFT JOIN cte USING(id_kebutuhan)
                        WHERE id_rencana = t_id_rencana
                        HAVING nominal_kebutuhan > 0
                        ORDER BY 1 ASC
                    ) r1, (
                        SELECT rab.id_rab, rab.id_kebutuhan, IFNULL(cte.saldo_kebutuhan, rab.nominal_kebutuhan) nominal_kebutuhan FROM 
                        rencana_anggaran_belanja rab 
                        LEFT JOIN cte USING(id_kebutuhan)
                        WHERE id_rencana = t_id_rencana
                        HAVING nominal_kebutuhan > 0
                        ORDER BY 1 ASC
                    ) r2
                    WHERE r1.id_rab >= r2.id_rab
                    GROUP BY r1.id_rab, r1.nominal_kebutuhan
                    HAVING SUM(r2.nominal_kebutuhan) < in_total_ap
                    ORDER BY 1 ASC
                    ) UNION (
                    SELECT r1.id_rab, r1.id_kebutuhan, r1.nominal_kebutuhan
                    FROM (
                        SELECT rab.id_rab, rab.id_kebutuhan, IFNULL(cte.saldo_kebutuhan, rab.nominal_kebutuhan) nominal_kebutuhan FROM 
                        rencana_anggaran_belanja rab 
                        LEFT JOIN cte USING(id_kebutuhan)
                        WHERE id_rencana = t_id_rencana
                        HAVING nominal_kebutuhan > 0
                        ORDER BY 1 ASC
                    ) r1, (
                        SELECT rab.id_rab, rab.id_kebutuhan, IFNULL(cte.saldo_kebutuhan, rab.nominal_kebutuhan) nominal_kebutuhan FROM 
                        rencana_anggaran_belanja rab 
                        LEFT JOIN cte USING(id_kebutuhan)
                        WHERE id_rencana = t_id_rencana
                        HAVING nominal_kebutuhan > 0
                        ORDER BY 1 ASC
                    ) r2
                    WHERE r1.id_rab >= r2.id_rab
                    GROUP BY r1.id_rab, r1.nominal_kebutuhan
                    HAVING SUM(r2.nominal_kebutuhan) >= in_total_ap
                    ORDER BY 1 ASC
                    LIMIT 1
                    )
                ) r3 LEFT JOIN rencana_anggaran_belanja rab USING(id_rab)
                ORDER BY id_rab ASC;

                -- declare NOT FOUND handler
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_rab = 1;

                OPEN list_rab;
                
                ListRabLoop:WHILE NOT finished_rab DO
                    FETCH list_rab INTO cr_nominal_kebutuhan, cr_id_kebutuhan, cr_keterangan;

                    IF total_penggunaan_donasi = in_total_ap THEN
                        SET finished_rab = 1;
                    END IF;

                    IF finished_rab = 1 THEN
                        LEAVE ListRabLoop;
                    END IF;

                    IF ck_saldo_kebutuhan IS NULL THEN
                        SET ck_saldo_kebutuhan = cr_nominal_kebutuhan;
                    ELSE
                        SET cr_nominal_kebutuhan = @saldo_kebutuhan;
                    END IF;

                    IF ck_saldo_donasi IS NULL THEN
                        SET ck_saldo_donasi = cd_nominal_donasi;
                    END IF;

                    IF ck_saldo_donasi >= ck_saldo_kebutuhan THEN
                        SET t_nominal_penggunaan_donasi = ck_saldo_kebutuhan;
                        SET ck_saldo_donasi = ck_saldo_donasi - ck_saldo_kebutuhan;
                        SET ck_saldo_kebutuhan = 0;		
                    ELSE
                        SET t_nominal_penggunaan_donasi = ck_saldo_donasi;
                        SET ck_saldo_kebutuhan = ck_saldo_kebutuhan - ck_saldo_donasi;
                        SET ck_saldo_donasi = 0;
                        
                        IF cd_saldo_donasi > 0 THEN
                            SET ck_saldo_donasi = cd_saldo_donasi;
                        END IF;
                    END IF;
                    

                    SET @id_pelaksanaan = in_id_pelaksanaan;
                    SET @id_kebutuhan = cr_id_kebutuhan;
                    SET @id_donasi = cd_id_donasi;
                    SET @nominal_kebutuhan = cr_nominal_kebutuhan;
                    SET @nominal_penggunaan_donasi = t_nominal_penggunaan_donasi;
                    SET @saldo_kebutuhan = ck_saldo_kebutuhan;
                    SET @saldo_donasi = ck_saldo_donasi;
                    SET @keterangan = cr_keterangan;
                    

                    -- SQL
                    EXECUTE stmt USING @id_pelaksanaan, @id_kebutuhan, @id_donasi, @nominal_kebutuhan, @nominal_penggunaan_donasi, @saldo_kebutuhan, @saldo_donasi, @keterangan;
                    IF ROW_COUNT() = 0 THEN
                        SELECT CONCAT("Failed to insert data! KalkulasiAnggaranPelaksanaanDonasi") MESSAGE_TEXT;
                        -- SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to insert data!';
                        DEALLOCATE PREPARE stmt;
                        CLOSE list_rab;
                        CLOSE list_donasi;
                        ROLLBACK;
                        LEAVE BlockDonasi;
                    END IF;

                    SET total_penggunaan_donasi = total_penggunaan_donasi + t_nominal_penggunaan_donasi;

                    IF ck_saldo_kebutuhan = 0 AND ck_saldo_donasi > 0 THEN
                        SET ck_saldo_kebutuhan = NULL;
                        ITERATE ListRabLoop;
                    END IF;

                    IF ck_saldo_donasi = 0 AND ck_saldo_kebutuhan > 0 THEN
                        SET ck_saldo_donasi = NULL;
                        LEAVE ListRabLoop;
                    END IF;
                    
                    IF ck_saldo_donasi = 0 AND ck_saldo_kebutuhan = 0 THEN
                        SET ck_saldo_donasi = NULL;
                        SET ck_saldo_kebutuhan = NULL;
                        LEAVE ListRabLoop;
                    END IF;    
                END WHILE ListRabLoop;

                CLOSE list_rab;
            END BlockRAB;
   
        END WHILE ListDonasiLoop;

        CLOSE list_donasi;
        DEALLOCATE PREPARE stmt;
    END BlockDonasi$$
DELIMITER ;

-- DROP PROCEDURE KalkulasiAnggaranPelaksanaanDonasiTemp;
DELIMITER $$
CREATE PROCEDURE KalkulasiAnggaranPelaksanaanDonasiTemp(IN in_id_pelaksanaan INT, IN in_total_ap BIGINT, IN in_id_bantuan INT)
    BlockDonasi:BEGIN
        DECLARE t_nominal_penggunaan_donasi, t_id_rencana INT UNSIGNED;
        DECLARE total_penggunaan_donasi BIGINT;
        -- Menampung State Cursor
        DECLARE finished_donasi, finished_rab TINYINT DEFAULT 0;
        -- Var untuk menampung isi cursor donasi
        DECLARE cd_id_donasi INT;
        DECLARE cd_nominal_donasi INT;
        DECLARE cd_saldo_donasi INT;
        -- Var untuk menampung isi cursor rab
        DECLARE cr_id_kebutuhan INT;
        DECLARE cr_nominal_kebutuhan INT;
        DECLARE cr_keterangan VARCHAR(50);
        -- Var untuk menampung isi cursor hasil kalkulasi
        DECLARE ck_saldo_donasi INT;
        DECLARE ck_saldo_kebutuhan INT;

        DECLARE list_donasi CURSOR FOR
        (
            SELECT d.id_donasi, d.saldo_donasi penggunaan_donasi, 0 saldo_donasi FROM (
                (
                    SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = in_id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
                )
                UNION
                (
                    SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = in_id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = in_id_bantuan)
                ) 
            ) sd, 
            (
                (
                    SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = in_id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
                )
                UNION
                (
                    SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = in_id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = in_id_bantuan)
                ) 
            ) d 
            WHERE d.id_donasi >= sd.id_donasi
            GROUP BY d.id_donasi, d.saldo_donasi
            HAVING SUM(sd.saldo_donasi) < in_total_ap
            ORDER BY d.id_donasi
        )
        UNION
        (
            SELECT d.id_donasi, d.saldo_donasi - (SUM(sd.saldo_donasi) - in_total_ap) penggunaan_donasi, (SUM(sd.saldo_donasi) - in_total_ap) saldo_donasi FROM (
                (
                    SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = in_id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
                )
                UNION
                (
                    SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = in_id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = in_id_bantuan)
                ) 
            ) sd, 
            (
                (
                    SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = in_id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
                )
                UNION
                (
                    SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = in_id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = in_id_bantuan)
                ) 
            ) d 
            WHERE d.id_donasi >= sd.id_donasi
            GROUP BY d.id_donasi, d.saldo_donasi
            HAVING SUM(sd.saldo_donasi) >= in_total_ap
            ORDER BY d.id_donasi
            LIMIT 1
        );

         -- declare NOT FOUND handler
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_donasi = 1;

        SELECT id_rencana INTO t_id_rencana FROM pelaksanaan WHERE id_pelaksanaan = in_id_pelaksanaan;

        OPEN list_donasi;

        SET @insertQuery = "INSERT INTO anggaran_pelaksanaan_donasi(id_pelaksanaan,id_kebutuhan,id_donasi,nominal_kebutuhan,nominal_penggunaan_donasi,saldo_kebutuhan,saldo_donasi,keterangan) VALUES(?,?,?,?,?,?,?,?)";
        PREPARE stmt FROM @insertQuery;
        
        ListDonasiLoop:WHILE NOT finished_donasi DO
            FETCH list_donasi INTO cd_id_donasi, cd_nominal_donasi, cd_saldo_donasi;

            IF finished_donasi = 1 THEN
                LEAVE ListDonasiLoop;
            END IF;

            BlockRAB:BEGIN
                DECLARE list_rab CURSOR FOR SELECT rab.nominal_kebutuhan, rab.id_kebutuhan, rab.keterangan FROM rencana r JOIN rencana_anggaran_belanja rab USING(id_rencana) JOIN tempSelectedRAB USING(id_rab) WHERE rab.id_rencana = t_id_rencana AND id_kebutuhan NOT IN (SELECT id_kebutuhan FROM anggaran_pelaksanaan_donasi WHERE id_pelaksanaan = in_id_pelaksanaan AND saldo_kebutuhan = 0);

                -- declare NOT FOUND handler
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_rab = 1;

                OPEN list_rab;
                
                ListRabLoop:WHILE NOT finished_rab DO
                    FETCH list_rab INTO cr_nominal_kebutuhan, cr_id_kebutuhan, cr_keterangan;

                    IF finished_rab = 1 THEN
                        LEAVE ListRabLoop;
                    END IF;


                    IF ck_saldo_kebutuhan IS NULL THEN
                        SET ck_saldo_kebutuhan = cr_nominal_kebutuhan;
                    ELSE
                        SET cr_nominal_kebutuhan = @saldo_kebutuhan;
                    END IF;

                    IF ck_saldo_donasi IS NULL THEN
                        SET ck_saldo_donasi = cd_nominal_donasi;
                    END IF;

                    IF ck_saldo_donasi >= ck_saldo_kebutuhan THEN
                        SET t_nominal_penggunaan_donasi = ck_saldo_kebutuhan;
                        SET ck_saldo_donasi = ck_saldo_donasi - ck_saldo_kebutuhan;
                        SET ck_saldo_kebutuhan = 0;		
                    ELSE
                        SET t_nominal_penggunaan_donasi = ck_saldo_donasi;
                        SET ck_saldo_kebutuhan = ck_saldo_kebutuhan - ck_saldo_donasi;
                        SET ck_saldo_donasi = 0;
                        
                        IF cd_saldo_donasi > 0 THEN
                            SET ck_saldo_donasi = cd_saldo_donasi;
                        END IF;
                    END IF;
                    

                    SET @id_pelaksanaan = in_id_pelaksanaan;
                    SET @id_kebutuhan = cr_id_kebutuhan;
                    SET @id_donasi = cd_id_donasi;
                    SET @nominal_kebutuhan = cr_nominal_kebutuhan;
                    SET @nominal_penggunaan_donasi = t_nominal_penggunaan_donasi;
                    SET @saldo_kebutuhan = ck_saldo_kebutuhan;
                    SET @saldo_donasi = ck_saldo_donasi;
                    SET @keterangan = cr_keterangan;
                    

                    -- SQL
                    EXECUTE stmt USING @id_pelaksanaan, @id_kebutuhan, @id_donasi, @nominal_kebutuhan, @nominal_penggunaan_donasi, @saldo_kebutuhan, @saldo_donasi, @keterangan;
                    IF ROW_COUNT() = 0 THEN
                        SELECT CONCAT("Failed to insert data! KalkulasiAnggaranPelaksanaanDonasiTemp") MESSAGE_TEXT;
                        -- SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to insert data!';
                        DEALLOCATE PREPARE stmt;
                        CLOSE list_rab;
                        CLOSE list_donasi;
                        ROLLBACK;
                        LEAVE BlockDonasi;
                    END IF;

                    IF ck_saldo_kebutuhan = 0 AND ck_saldo_donasi > 0 THEN
                        SET ck_saldo_kebutuhan = NULL;
                        ITERATE ListRabLoop;
                    END IF;

                    IF ck_saldo_donasi = 0 AND ck_saldo_kebutuhan > 0 THEN
                        SET ck_saldo_donasi = NULL;
                        LEAVE ListRabLoop;
                    END IF;
                    
                    IF ck_saldo_donasi = 0 AND ck_saldo_kebutuhan = 0 THEN
                        SET ck_saldo_donasi = NULL;
                        SET ck_saldo_kebutuhan = NULL;
                        LEAVE ListRabLoop;
                    END IF;    
                END WHILE ListRabLoop;

                CLOSE list_rab;
            END BlockRAB;
   
        END WHILE ListDonasiLoop;

        CLOSE list_donasi;
        DEALLOCATE PREPARE stmt;
    END BlockDonasi$$
DELIMITER ;

-- DROP PROCEDURE ReKalkulasiAnggaranPelaksanaanDonasi;
DELIMITER $$
CREATE PROCEDURE ReKalkulasiAnggaranPelaksanaanDonasi(IN in_id_pelaksanaan INT, IN in_total_ap BIGINT, IN in_id_bantuan INT)
    BlockDonasi:BEGIN
        DECLARE t_nominal_penggunaan_donasi, t_id_rencana INT UNSIGNED;
        DECLARE total_penggunaan_donasi BIGINT DEFAULT 0;
        -- Menampung State Cursor
        DECLARE finished_donasi, finished_rab TINYINT DEFAULT 0;
        -- Var untuk menampung isi cursor donasi
        DECLARE cd_id_donasi INT;
        DECLARE cd_nominal_donasi INT;
        DECLARE cd_saldo_donasi INT;
        -- Var untuk menampung isi cursor rab
        DECLARE cr_id_kebutuhan INT;
        DECLARE cr_nominal_kebutuhan INT;
        DECLARE cr_keterangan VARCHAR(50);
        -- Var untuk menampung isi cursor hasil kalkulasi
        DECLARE ck_saldo_donasi INT;
        DECLARE ck_saldo_kebutuhan INT;

        DECLARE list_donasi CURSOR FOR
        (
            SELECT d.id_donasi, d.saldo_donasi penggunaan_donasi, 0 saldo_donasi FROM (
                (
                    SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = in_id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
                )
                UNION
                (
                    SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = in_id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = in_id_bantuan)
                ) 
            ) sd, 
            (
                (
                    SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = in_id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
                )
                UNION
                (
                    SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = in_id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = in_id_bantuan)
                ) 
            ) d 
            WHERE d.id_donasi >= sd.id_donasi
            GROUP BY d.id_donasi, d.saldo_donasi
            HAVING SUM(sd.saldo_donasi) < in_total_ap
            ORDER BY d.id_donasi
        )
        UNION
        (
            SELECT d.id_donasi, d.saldo_donasi - (SUM(sd.saldo_donasi) - in_total_ap) penggunaan_donasi, (SUM(sd.saldo_donasi) - in_total_ap) saldo_donasi FROM (
                (
                    SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = in_id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
                )
                UNION
                (
                    SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = in_id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = in_id_bantuan)
                ) 
            ) sd, 
            (
                (
                    SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = in_id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
                )
                UNION
                (
                    SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = in_id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = in_id_bantuan)
                ) 
            ) d 
            WHERE d.id_donasi >= sd.id_donasi
            GROUP BY d.id_donasi, d.saldo_donasi
            HAVING SUM(sd.saldo_donasi) >= in_total_ap
            ORDER BY d.id_donasi
            LIMIT 1
        );

         -- declare NOT FOUND handler
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_donasi = 1;

        SELECT id_rencana INTO t_id_rencana FROM pelaksanaan WHERE id_pelaksanaan = in_id_pelaksanaan;

        OPEN list_donasi;
                
        ListDonasiLoop:WHILE NOT finished_donasi DO
            FETCH list_donasi INTO cd_id_donasi, cd_nominal_donasi, cd_saldo_donasi;

            IF total_penggunaan_donasi = in_total_ap THEN
                SET finished_rab = 1;
            END IF;

            IF finished_donasi = 1 THEN
                LEAVE ListDonasiLoop;
            END IF;

            BlockRAB:BEGIN
                DECLARE list_rab CURSOR FOR 
                WITH cte AS (
                    SELECT MIN(saldo_kebutuhan) saldo_kebutuhan, id_kebutuhan FROM anggaran_pelaksanaan_donasi WHERE id_pelaksanaan = in_id_pelaksanaan GROUP BY id_kebutuhan
                ) 
                SELECT r3.nominal_kebutuhan, r3.id_kebutuhan, keterangan
                FROM
                (
                    ( 
                    SELECT r1.id_rab, r1.id_kebutuhan, r1.nominal_kebutuhan
                    FROM (
                        SELECT rab.id_rab, rab.id_kebutuhan, IFNULL(cte.saldo_kebutuhan, rab.nominal_kebutuhan) nominal_kebutuhan FROM 
                        rencana_anggaran_belanja rab 
                        LEFT JOIN cte USING(id_kebutuhan)
                        WHERE id_rencana = t_id_rencana
                        HAVING nominal_kebutuhan > 0
                        ORDER BY 1 ASC
                    ) r1, (
                        SELECT rab.id_rab, rab.id_kebutuhan, IFNULL(cte.saldo_kebutuhan, rab.nominal_kebutuhan) nominal_kebutuhan FROM 
                        rencana_anggaran_belanja rab 
                        LEFT JOIN cte USING(id_kebutuhan)
                        WHERE id_rencana = t_id_rencana
                        HAVING nominal_kebutuhan > 0
                        ORDER BY 1 ASC
                    ) r2
                    WHERE r1.id_rab >= r2.id_rab
                    GROUP BY r1.id_rab, r1.nominal_kebutuhan
                    HAVING SUM(r2.nominal_kebutuhan) < in_total_ap
                    ORDER BY 1 ASC
                    ) UNION (
                    SELECT r1.id_rab, r1.id_kebutuhan, r1.nominal_kebutuhan
                    FROM (
                        SELECT rab.id_rab, rab.id_kebutuhan, IFNULL(cte.saldo_kebutuhan, rab.nominal_kebutuhan) nominal_kebutuhan FROM 
                        rencana_anggaran_belanja rab 
                        LEFT JOIN cte USING(id_kebutuhan)
                        WHERE id_rencana = t_id_rencana
                        HAVING nominal_kebutuhan > 0
                        ORDER BY 1 ASC
                    ) r1, (
                        SELECT rab.id_rab, rab.id_kebutuhan, IFNULL(cte.saldo_kebutuhan, rab.nominal_kebutuhan) nominal_kebutuhan FROM 
                        rencana_anggaran_belanja rab 
                        LEFT JOIN cte USING(id_kebutuhan)
                        WHERE id_rencana = t_id_rencana
                        HAVING nominal_kebutuhan > 0
                        ORDER BY 1 ASC
                    ) r2
                    WHERE r1.id_rab >= r2.id_rab
                    GROUP BY r1.id_rab, r1.nominal_kebutuhan
                    HAVING SUM(r2.nominal_kebutuhan) >= in_total_ap
                    ORDER BY 1 ASC
                    LIMIT 1
                    )
                ) r3 LEFT JOIN rencana_anggaran_belanja rab USING(id_rab)
                ORDER BY id_rab ASC;

                -- declare NOT FOUND handler
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_rab = 1;

                OPEN list_rab;
                
                ListRabLoop:WHILE NOT finished_rab DO
                    FETCH list_rab INTO cr_nominal_kebutuhan, cr_id_kebutuhan, cr_keterangan;

                    IF finished_rab = 1 THEN
                        LEAVE ListRabLoop;
                    END IF;

                    IF ck_saldo_kebutuhan IS NULL THEN
                        SET ck_saldo_kebutuhan = cr_nominal_kebutuhan;
                    ELSE
                        SET cr_nominal_kebutuhan = @saldo_kebutuhan;
                    END IF;

                    IF ck_saldo_donasi IS NULL THEN
                        SET ck_saldo_donasi = cd_nominal_donasi;
                    END IF;

                    IF ck_saldo_donasi >= ck_saldo_kebutuhan THEN
                        SET t_nominal_penggunaan_donasi = ck_saldo_kebutuhan;
                        SET ck_saldo_donasi = ck_saldo_donasi - ck_saldo_kebutuhan;
                        SET ck_saldo_kebutuhan = 0;		
                    ELSE
                        SET t_nominal_penggunaan_donasi = ck_saldo_donasi;
                        SET ck_saldo_kebutuhan = ck_saldo_kebutuhan - ck_saldo_donasi;
                        SET ck_saldo_donasi = 0;
                        
                        IF cd_saldo_donasi > 0 THEN
                            SET ck_saldo_donasi = cd_saldo_donasi;
                        END IF;
                    END IF;
                    

                    SET @id_pelaksanaan = in_id_pelaksanaan;
                    SET @id_kebutuhan = cr_id_kebutuhan;
                    SET @id_donasi = cd_id_donasi;
                    SET @nominal_kebutuhan = cr_nominal_kebutuhan;
                    SET @nominal_penggunaan_donasi = t_nominal_penggunaan_donasi;
                    SET @saldo_kebutuhan = ck_saldo_kebutuhan;
                    SET @saldo_donasi = ck_saldo_donasi;
                    SET @keterangan = cr_keterangan;
                    

                    -- SQL
                    INSERT INTO anggaran_pelaksanaan_donasi(id_pelaksanaan,id_kebutuhan,id_donasi,nominal_kebutuhan,nominal_penggunaan_donasi,saldo_kebutuhan,saldo_donasi,keterangan) VALUES(@id_pelaksanaan, @id_kebutuhan, @id_donasi, @nominal_kebutuhan, @nominal_penggunaan_donasi, @saldo_kebutuhan, @saldo_donasi, @keterangan);
                    IF ROW_COUNT() = 0 THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to insert data!';
                        CLOSE list_rab;
                        CLOSE list_donasi;
                        LEAVE BlockDonasi;
                    END IF;

                    SET total_penggunaan_donasi = total_penggunaan_donasi + t_nominal_penggunaan_donasi;

                    IF ck_saldo_kebutuhan = 0 AND ck_saldo_donasi > 0 THEN
                        SET ck_saldo_kebutuhan = NULL;
                        ITERATE ListRabLoop;
                    END IF;

                    IF ck_saldo_donasi = 0 AND ck_saldo_kebutuhan > 0 THEN
                        SET ck_saldo_donasi = NULL;
                        LEAVE ListRabLoop;
                    END IF;
                    
                    IF ck_saldo_donasi = 0 AND ck_saldo_kebutuhan = 0 THEN
                        SET ck_saldo_donasi = NULL;
                        SET ck_saldo_kebutuhan = NULL;
                        LEAVE ListRabLoop;
                    END IF;    
                END WHILE ListRabLoop;

                CLOSE list_rab;
            END BlockRAB;
   
        END WHILE ListDonasiLoop;

        CLOSE list_donasi;
    END BlockDonasi$$
DELIMITER ;

-- DROP PROCEDURE TotalAggaranBantuanPelaksanaan;
DELIMITER $$
CREATE PROCEDURE TotalAggaranBantuanPelaksanaan(IN in_id_pelaksanaan INT)
TABPLabel:BEGIN
    DECLARE t_total_ap, t_saldo_donasi, t_total_penggunaan_donasi BIGINT;
    DECLARE t_id_bantuan INT;
    DECLARE temp_exists TINYINT;

    START TRANSACTION;
    -- SET AUTOCOMMIT = 0;
    
    SELECT p.total_anggaran, r.id_bantuan INTO t_total_ap, t_id_bantuan FROM pelaksanaan p JOIN rencana r USING(id_rencana) WHERE p.id_pelaksanaan = in_id_pelaksanaan;
    SELECT SUM(saldo_donasi) FROM (
        (
            SELECT SUM(jumlah_donasi) as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = t_id_bantuan AND id_donasi NOT IN (SELECT DISTINCT(id_donasi) FROM pelaksanaan)
        ) UNION (
            SELECT MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = t_id_bantuan AND au.id_pelaksanaan = in_id_pelaksanaan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
        )
    ) sdba INTO t_saldo_donasi;

    IF t_saldo_donasi < t_total_ap THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Saldo donasi tidak mencukupi RAB yang ada';
        LEAVE TABPLabel;
    END IF;

    CALL check_table_exists('tempSelectedRAB', @tempSelect);
    SELECT @tempSelect INTO temp_exists;
    IF temp_exists = 0 THEN
        CALL KalkulasiAnggaranPelaksanaanDonasi(in_id_pelaksanaan, t_total_ap, t_id_bantuan);
    ELSE
        CALL KalkulasiAnggaranPelaksanaanDonasiTemp(in_id_pelaksanaan, t_total_ap, t_id_bantuan);
    END IF;

    SELECT IFNULL(SUM(nominal_penggunaan_donasi), 0) FROM anggaran_pelaksanaan_donasi WHERE id_pelaksanaan = in_id_pelaksanaan INTO t_total_penggunaan_donasi;
    IF t_total_penggunaan_donasi <> t_total_ap THEN
        ROLLBACK;
        ALTER TABLE anggaran_pelaksanaan_donasi AUTO_INCREMENT = 1;
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Saldo donasi tidak mencukupi RAB yang ada, hasil kalkulasi dibatalkan';
        LEAVE TABPLabel;
    END IF;
    
    COMMIT;
    SELECT 'Donasi berhasil dibuat daftar rinciannya anggarannya' MESSAGE_TEXT;
END TABPLabel$$
DELIMITER ;

-- DROP TRIGGER BeforeUpdateRencana;
DELIMITER $$
CREATE TRIGGER BeforeUpdateRencana
BEFORE UPDATE ON rencana FOR EACH ROW
BEGIN
    DECLARE t_total_rab BIGINT UNSIGNED;
    SELECT SUM(nominal_kebutuhan) INTO t_total_rab FROM rencana_anggaran_belanja WHERE id_rencana = OLD.id_rencana;

    IF t_total_rab <> NEW.total_anggaran THEN
        SIGNAL SQLSTATE '45004' SET MESSAGE_TEXT = 'Not allowed direct update total_rab on rencana table';
    END IF;

    IF OLD.status = 'SD' AND OLD.total_anggaran <> NEW.total_anggaran AND NEW.status != 'BP' THEN
        SET NEW.status = 'BD';
    END IF;
END$$
DELIMITER ;

-- Wajib dicek soalnya ada Call Ke SP TotalAggaranBantuanPelaksanaan -> KalkulasiAnggaranPelaksanaanDonasi ada TRANSACTION dan SELECT returnnya bisa error
-- Ganti SELECT MESSAGTE_TEXTnya dengan SIGNAL
-- atau 
-- DROP TRIGGER AfterUpdateRencana;
DELIMITER $$
CREATE TRIGGER AfterUpdateRencana
AFTER UPDATE ON rencana FOR EACH ROW
BEGIN
    DECLARE t_total_ap BIGINT UNSIGNED;
    DECLARE t_id_pelaksanaan, t_id_bantuan INT UNSIGNED;
    DECLARE c_pelaksanaan, c_penarikan TINYINT UNSIGNED DEFAULT 0;
    IF OLD.total_anggaran <> NEW.total_anggaran THEN
        SELECT COUNT(p.id_pelaksanaan) INTO c_pelaksanaan FROM pelaksanaan p WHERE p.status != 'TD' AND p.id_rencana = NEW.id_rencana;
        SELECT COUNT(pn.id_penarikan) INTO c_penarikan FROM pelaksanaan p LEFT JOIN penarikan pn USING(id_pelaksanaan) WHERE pn.status = '1' AND p.id_rencana = NEW.id_rencana;
        IF c_pelaksanaan >= 1 AND c_penarikan = 0 THEN
            UPDATE pelaksanaan SET total_anggaran = (SELECT total_anggaran FROM rencana WHERE id_rencana = NEW.id_rencana) WHERE id_rencana = NEW.id_rencana;
            IF ROW_COUNT() > 0 THEN
                SELECT id_pelaksanaan INTO t_id_pelaksanaan FROM pelaksanaan WHERE id_rencana = NEW.id_rencana;
                DELETE FROM anggaran_pelaksanaan_donasi WHERE id_pelaksanaan = t_id_pelaksanaan;
                IF ROW_COUNT() > 0 THEN
                    SELECT p.total_anggaran, r.id_bantuan INTO t_total_ap, t_id_bantuan FROM pelaksanaan p JOIN rencana r USING(id_rencana) WHERE p.id_pelaksanaan = t_id_pelaksanaan;
                    CALL ReKalkulasiAnggaranPelaksanaanDonasi(t_id_pelaksanaan, t_total_ap, t_id_bantuan);
                END IF;
            END IF;
        END IF;
    END IF;
END$$
DELIMITER ;

-- OP = On Proccess (Seorang petrugas sudah mengkonfirm untuk siap melakukan pinbuk), 
-- WTV = Waiting to Verivication (Pinbuk sudah dilakukan dan sudah mengupload bukti pinbuk namun belum di verivikasi), 
-- OK = All Clear (Sudah diverivikasi)
-- CL = Canceled (Sudah diverivikasi namun dibatalkan verivikasinya)
CREATE TABLE pinbuk (
    id_pinbuk INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    total_pinbuk BIGINT UNSIGNED NOT NULL,
    status ENUM('OP','WTV','OK','CL') DEFAULT 'OP',
    penyesuaian_total_pinbuk TINYINT DEFAULT 0,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    keterangan VARCHAR(150),
    id_ca_pengirim TINYINT UNSIGNED,
    id_ca_penerima TINYINT UNSIGNED,
    id_gambar INT UNSIGNED,
    id_pelaksanaan INT UNSIGNED,
    id_bantuan INT UNSIGNED,
    CONSTRAINT F_ID_CA_PENGIRIM_PINBUK_ODR FOREIGN KEY(id_ca_pengirim) REFERENCES channel_account(id_ca) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_CA_PENERIMA_PINBUK_ODR FOREIGN KEY(id_ca_penerima) REFERENCES channel_account(id_ca) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_GAMBAR_PINBUK_ODN FOREIGN KEY(id_gambar) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT F_ID_PELAKSANAAN_PINBUK_ODN FOREIGN KEY(id_pelaksanaan) REFERENCES pelaksanaan(id_pelaksanaan) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT F_ID_BANTUAN_PINBUK_ODR FOREIGN KEY(id_bantuan) REFERENCES bantuan(id_bantuan) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE detil_pinbuk (
    nominal_pindah INT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_donasi BIGINT UNSIGNED,
    id_pinbuk INT UNSIGNED,
    CONSTRAINT F_ID_DONASI_DETIL_PINBUK_ODR FOREIGN KEY(id_donasi) REFERENCES donasi(id_donasi) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_PINBUK_DETIL_PINBUK_ODC FOREIGN KEY(id_pinbuk) REFERENCES pinbuk(id_pinbuk) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE penarikan (
    id_penarikan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nominal BIGINT UNSIGNED,
    status ENUM('0','1') NOT NULL DEFAULT '0',
    waktu_penarikan TIMESTAMP,
    id_ca TINYINT UNSIGNED,
    id_pencairan INT UNSIGNED,
    id_pelaksanaan INT UNSIGNED,
    id_petugas SMALLINT UNSIGNED,
    id_gambar INT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_CP_PENARIKAN_ODR FOREIGN KEY(id_ca) REFERENCES channel_account(id_ca) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_PENCAIRAN_PENARIKAN_ODC FOREIGN KEY(id_pencairan) REFERENCES pencairan(id_pencairan) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_PELAKSANAAN_PENARIKAN_ODR FOREIGN KEY(id_pelaksanaan) REFERENCES pelaksanaan(id_pelaksanaan) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_PETUGAS_PENARIKAN_ODR FOREIGN KEY(id_petugas) REFERENCES pegawai(id_pegawai) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_GAMBAR_PENARIKAN_ODN FOREIGN KEY(id_gambar) REFERENCES gambar(id_gambar) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT U_ID_GAMBAR_PENARIKAN UNIQUE(id_gambar)
)ENGINE=INNODB;

CREATE TABLE pengadaan (
    id_pengadaan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    keterangan VARCHAR(255),
    nominal BIGINT UNSIGNED NOT NULL DEFAULT 0,
    saldo BIGINT UNSIGNED NOT NULL DEFAULT 0,
    id_pengesah SMALLINT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_PENGESAH_PENGADAAN_ODR FOREIGN KEY(id_pengesah) REFERENCES pegawai(id_pegawai) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE petugas_pengadaan (
    id_petugas_pengadaan INT UNSIGNED PRIMARY KEY,
    id_pengadaan INT UNSIGNED,
    id_pegawai SMALLINT UNSIGNED,
    nama VARCHAR(50) NOT NULL,
    status ENUM('R','D'),
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_PENGADAAN_PETUGAS_PENGADAAN_ODC FOREIGN KEY(id_pengadaan) REFERENCES pengadaan(id_pengadaan) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_PEGAWAI_PETUGAS_PENGADAAN_ODN FOREIGN KEY(id_pegawai) REFERENCES pegawai(id_pegawai) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT U_ID_PENGADAAN_ID_PEGAWAI_PETUGAS_PENGADAAN UNIQUE(id_pengadaan, id_pegawai)
)ENGINE=INNODB;

CREATE TABLE penyerahan (
    id_penarikan INT UNSIGNED,
    id_pengadaan INT UNSIGNED,
    nominal BIGINT UNSIGNED NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_PENARIKAN_PENYERAHAN_ODC FOREIGN KEY(id_penarikan) REFERENCES penarikan(id_penarikan) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_PENGADAAN_PENYERAHAN_ODC FOREIGN KEY(id_pengadaan) REFERENCES pengadaan(id_pengadaan) ON DELETE CASCADE ON UPDATE CASCADE,
)ENGINE=INNODB;

CREATE TABLE belanja (
    id_belanja BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nominal BIGINT UNSIGNED,
    saldo_rab INT,
    id_petugas_pengadaan INT UNSIGNED,
    id_rab BIGINT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_PETUGAS_PENGADAAN_BELANJA_ODR FOREIGN KEY(id_petugas_pengadaan) REFERENCES petugas_pengadaan(id_petugas_pengadaan) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_RAB_BELANJA_ODC FOREIGN KEY(id_rab) REFERENCES rencana_anggaran_belanja(id_rab) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE list_gambar_belanja (
    id_belanja BIGINT UNSIGNED,
    id_gambar INT UNSIGNED NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_BELANJA_LGB_ODR FOREIGN KEY(id_belanja) REFERENCES belanja(id_belanja) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_GAMBAR_BELANJA_ODR FOREIGN KEY(id_gambar) REFERENCES gambar(id_gambar) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE transaksi (
    id_transaksi BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nominal BIGINT UNSIGNED NOT NULL,
    jenis ENUM('M','K') NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_ca TINYINT UNSIGNED NOT NULL,
    CONSTRAINT F_ID_CA_TRANSAKSI_ODR FOREIGN KEY(id_ca) REFERENCES channel_account(id_ca) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE detil_transaksi_penarikan_anggaran(
    id_dtpa BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    saldo INT UNSIGNED DEFAULT 0,
    nominal INT UNSIGNED NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_apd BIGINT UNSIGNED,
    id_penarikan INT UNSIGNED,
    id_transaksi BIGINT UNSIGNED,
    CONSTRAINT F_ID_APD_DETIL_TRANSAKSI_PENARIKAN_ANGGARAN_ODR FOREIGN KEY(id_apd) REFERENCES anggaran_pelaksanaan_donasi(id_apd) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_PENARIKAN_DETIL_TRANSAKSI_PENARIKAN_ANGGARAN_ODC FOREIGN KEY(id_penarikan) REFERENCES penarikan(id_penarikan) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_TRANSAKSI_DETIL_TRANSAKSI_PENARIKAN_ANGGARAN_ODR FOREIGN KEY(id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE detil_transaksi_pengembalian_penarikan_anggaran(
    id_dtppa BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    saldo INT UNSIGNED DEFAULT 0,
    nominal INT UNSIGNED NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_apd BIGINT UNSIGNED,
    id_penarikan INT UNSIGNED,
    id_transaksi_k BIGINT UNSIGNED,
    id_transaksi_m BIGINT UNSIGNED,
    CONSTRAINT F_ID_APD_DETIL_TRANSAKSI_PENGEMBALIAN_P_ANGGARAN_ODC FOREIGN KEY(id_apd) REFERENCES anggaran_pelaksanaan_donasi(id_apd) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_PENARIKAN_DETIL_TRANSAKSI_PENGEMBALIAN_P_ANGGARAN_ODC FOREIGN KEY(id_penarikan) REFERENCES penarikan(id_penarikan) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_TRANSAKSI_DETIL_TRANSAKSI_K_PENGEMBALIAN_P_ANGGARAN_ODR FOREIGN KEY(id_transaksi_k) REFERENCES transaksi(id_transaksi) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_TRANSAKSI_DETIL_TRANSAKSI_M_PENGEMBALIAN_P_ANGGARAN_ODR FOREIGN KEY(id_transaksi_m) REFERENCES transaksi(id_transaksi) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE informasi (
    id_informasi INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(75) NOT NULL,
    isi TEXT,
    label ENUM('I','PN','PD','PL'),
    id_bantuan INT UNSIGNED,
    id_author SMALLINT UNSIGNED,
    id_editor SMALLINT UNSIGNED,
    publis_at TIMESTAMP,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_BANTUAN_INFORMASI_ODN FOREIGN KEY(id_bantuan) REFERENCES bantuan(id_bantuan) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT F_ID_AUTHOR_INFORMASI_ODR FOREIGN KEY(id_author) REFERENCES pegawai(id_pegawai) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT F_ID_EDITOR_INFORMASI_ODR FOREIGN KEY(id_editor) REFERENCES pegawai(id_pegawai) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE list_gambar_informasi(
    id_informasi INT UNSIGNED,
    id_gambar INT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_INFORMASI_LGI_ODN FOREIGN KEY(id_informasi) REFERENCES informasi(id_informasi) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT F_ID_GAMBAR_LGI_ODC FOREIGN KEY(id_gambar) REFERENCES gambar(id_gambar) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE informasi_penarikan(
    id_ipn INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_informasi INT UNSIGNED,
    id_penarikan INT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_INFORMASI_IPN_ODC FOREIGN KEY(id_informasi) REFERENCES informasi(id_informasi) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_PENCAIRAN_IPN_ODC FOREIGN KEY(id_penarikan) REFERENCES penarikan(id_penarikan) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE informasi_pengadaan(
    id_ipd INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_informasi INT UNSIGNED,
    id_pengadaan INT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_INFORMASI_IPD_ODC FOREIGN KEY(id_informasi) REFERENCES informasi(id_informasi) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_PENGADAAN_IPD_ODC FOREIGN KEY(id_pengadaan) REFERENCES pengadaan(id_pengadaan) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE informasi_pelaksanaan(
    id_ipl INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_informasi INT UNSIGNED,
    id_pelaksanaan INT UNSIGNED,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT F_ID_INFORMASI_IPL_ODC FOREIGN KEY(id_informasi) REFERENCES informasi(id_informasi) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT F_ID_PELAKSANAAN_IPL_ODC FOREIGN KEY(id_pelaksanaan) REFERENCES pelaksanaan(id_pelaksanaan) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=INNODB;

-- DROP TRIGGER AfterUpdateInformasi;
DELIMITER $$
CREATE TRIGGER AfterUpdateInformasi
AFTER UPDATE ON informasi FOR EACH ROW
    BEGIN
    IF NEW.label <> OLD.label AND OLD.label <> 'I' THEN
        IF OLD.label = 'PL' THEN
            DELETE FROM informasi_pelaksanaan WHERE id_informasi = OLD.id_informasi;
        ELSEIF OLD.label = 'PN' THEN
            DELETE FROM informasi_penarikan WHERE id_informasi = OLD.id_informasi;
        ELSEIF OLD.label = 'PD' THEN
            DELETE FROM informasi_pengadaan WHERE id_informasi = OLD.id_informasi;
        ELSE
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Label tidak diketahui';
        END IF;
    END IF;
    END$$
DELIMITER ;

-- DROP PROCEDURE VirtualCAPenarikan;
DELIMITER $$
CREATE PROCEDURE VirtualCAPenarikan(IN in_id_penarikan INT, IN in_id_ca TINYINT, IN in_restore TINYINT)
    VCAPenarikanLabel:BEGIN
        -- Menampung State Cursor
        DECLARE finished_vca_reduce TINYINT DEFAULT 0;
        -- Var untuk menampung isi cursor
        DECLARE ct_id_donasi, ct_nominal BIGINT UNSIGNED;

        DECLARE list_detil_transaksi_penarikan_anggaran CURSOR FOR SELECT a.id_donasi, d.nominal FROM detil_transaksi_penarikan_anggaran d JOIN anggaran_pelaksanaan_donasi a USING(id_apd) WHERE id_penarikan = in_id_penarikan;

         -- declare NOT FOUND handler
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_vca_reduce = 1;
        OPEN list_detil_transaksi_penarikan_anggaran;

        VCAPenarikanLoop:WHILE NOT finished_vca_reduce DO
            FETCH list_detil_transaksi_penarikan_anggaran INTO ct_id_donasi, ct_nominal;

            IF finished_vca_reduce = 1 THEN
                CLOSE list_detil_transaksi_penarikan_anggaran;
                LEAVE VCAPenarikanLoop;
            END IF;

            IF in_restore = 1 THEN
                UPDATE virtual_ca_donasi SET saldo = saldo + ct_nominal WHERE id_donasi = ct_id_donasi AND id_ca = in_id_ca;
            ELSE
                UPDATE virtual_ca_donasi SET saldo = saldo - ct_nominal WHERE id_donasi = ct_id_donasi AND id_ca = in_id_ca;
            END IF;

        END WHILE VCAPenarikanLoop;
    END VCAPenarikanLabel$$
DELIMITER ;

-- DROP PROCEDURE InsertDetilPenarikan;
DELIMITER $$
CREATE PROCEDURE InsertDetilPenarikan(IN in_id_pelaksanaan INT, IN in_id_penarikan INT, IN in_id_ca TINYINT, IN in_nominal INT, IN in_id_transaksi BIGINT)
    IDPenarikanLabel:BEGIN
        -- Menampung State Cursor
        DECLARE finished_detil_penarikan_insert TINYINT DEFAULT 0;
        -- Var untuk menampung isi cursor
        DECLARE ct_id_apd, ct_nominal, ct_saldo BIGINT UNSIGNED;

        DECLARE list_dtpa_select_insert CURSOR FOR 
        SELECT idpd.id_apd, idpd.nominal_penarikan_donasi, idpd.saldo FROM
        (
            (
                SELECT vadp.id_apd, vadp.saldo_penarikan nominal_penarikan_donasi, SUM(vadp2.saldo_penarikan) kumulatif, 0 saldo 
                FROM 
                    (SELECT a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) JOIN virtual_ca_donasi v USING(id_donasi) WHERE a.id_pelaksanaan = in_id_pelaksanaan AND V.id_ca = in_id_ca GROUP BY d.id_apd, a.id_apd HAVING saldo_penarikan > 0) vadp,
                    (SELECT a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) JOIN virtual_ca_donasi v USING(id_donasi) WHERE a.id_pelaksanaan = in_id_pelaksanaan AND V.id_ca = in_id_ca GROUP BY d.id_apd, a.id_apd HAVING saldo_penarikan > 0) vadp2
                WHERE vadp.id_apd >= vadp2.id_apd
                GROUP BY vadp.id_apd
                HAVING kumulatif < in_nominal
                ORDER BY vadp.id_apd
                ) UNION (
                SELECT vadp.id_apd, in_nominal - (SUM(vadp2.saldo_penarikan) - vadp.saldo_penarikan) nominal_penarikan_donasi, SUM(vadp2.saldo_penarikan) kumulatif, vadp.saldo_penarikan - (in_nominal - (SUM(vadp2.saldo_penarikan) - vadp.saldo_penarikan)) saldo FROM 
                    (SELECT a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) JOIN virtual_ca_donasi v USING(id_donasi) WHERE a.id_pelaksanaan = in_id_pelaksanaan AND V.id_ca = in_id_ca GROUP BY d.id_apd, a.id_apd HAVING saldo_penarikan > 0) vadp,
                    (SELECT a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) JOIN virtual_ca_donasi v USING(id_donasi) WHERE a.id_pelaksanaan = in_id_pelaksanaan AND V.id_ca = in_id_ca GROUP BY d.id_apd, a.id_apd HAVING saldo_penarikan > 0) vadp2
                WHERE vadp.id_apd >= vadp2.id_apd
                GROUP BY vadp.id_apd
                HAVING kumulatif >= in_nominal
                ORDER BY vadp.id_apd
                LIMIT 1
            )
        ) idpd
        ORDER BY id_apd;

         -- declare NOT FOUND handler
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_detil_penarikan_insert = 1;
        OPEN list_dtpa_select_insert;

        VCAPenarikanLoop:WHILE NOT finished_detil_penarikan_insert DO
            FETCH list_dtpa_select_insert INTO ct_id_apd, ct_nominal, ct_saldo;

            IF finished_detil_penarikan_insert = 1 THEN
                CLOSE list_dtpa_select_insert;
                LEAVE VCAPenarikanLoop;
            END IF;

            INSERT INTO detil_transaksi_penarikan_anggaran(id_penarikan, id_apd, nominal, saldo, id_transaksi) VALUES(in_id_penarikan, ct_id_apd, ct_nominal, ct_saldo, in_id_transaksi);
            IF ROW_COUNT() != 1 THEN
                CLOSE list_dtpa_select_insert;
                LEAVE VCAPenarikanLoop;
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to insert detil_transaksi_penarikan_anggaran';
            END IF;
        END WHILE VCAPenarikanLoop;
    END IDPenarikanLabel$$
DELIMITER ;

-- DROP TRIGGER AfterInsertPenarikan;
DELIMITER $$
CREATE TRIGGER AfterInsertPenarikan
AFTER INSERT ON penarikan FOR EACH ROW
    BEGIN
    DECLARE t_new_id_transaksi BIGINT;
    IF NEW.status = '1' THEN
        INSERT INTO transaksi(nominal,jenis,id_ca) VALUES(NEW.nominal,'K',NEW.id_ca);
        SELECT LAST_INSERT_ID() INTO t_new_id_transaksi;
        UPDATE channel_account SET saldo = saldo - NEW.nominal WHERE id_ca = NEW.id_ca;
        
        CALL InsertDetilPenarikan(NEW.id_pelaksanaan, NEW.id_penarikan, NEW.id_ca, NEW.nominal, t_new_id_transaksi);

        CALL VirtualCAPenarikan(NEW.id_penarikan, NEW.id_ca);
    END IF;
    END$$
DELIMITER ;

-- DROP TRIGGER BeforeUpdatePenarikan;
DELIMITER $$
CREATE TRIGGER BeforeUpdatePenarikan
BEFORE UPDATE ON penarikan FOR EACH ROW
    BEGIN
    DECLARE t_new_id_transaksi_k, t_new_id_transaksi_m, t_total_pencairan, t_sum_nominal_penarikan BIGINT UNSIGNED;
    DECLARE t_status_pencairan VARCHAR(3);

    SELECT status FROM pencairan WHERE id_pencairan = OLD.id_pencairan INTO t_status_pencairan;
    IF UPPER(t_status_pencairan) = 'WTR' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Petugas pencairan belum siap untuk mencairkan';
    END IF;

    IF OLD.nominal < NEW.nominal THEN
        SELECT total FROM pencairan WHERE id_pencairan = OLD.id_pencairan INTO t_total_pencairan;
        SELECT (SUM(nominal) - OLD.nominal) + NEW.nominal FROM penarikan WHERE id_pencairan = OLD.id_pencairan GROUP BY id_pencairan INTO t_sum_nominal_penarikan;
    
        IF t_total_pencairan < t_sum_nominal_penarikan THEN
            SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Total penarikan should less then total_pencairan';
        END IF;
    END IF;

    IF ((OLD.nominal != NEW.nominal) AND OLD.status = '1') OR (NEW.status = '0' AND OLD.status = '1') THEN
        -- CHECK sudah dibelanjakan atau belum?
        -- Jika Sudah dibelanjakan pakai SIGNAL gagal memperbaharui data nominal penarikan (Kemungkinan menambah atribut baru di apd bisa berupa status belanja atau membuat relasi dari apd dengan entitas belanja)


        INSERT INTO transaksi(nominal,jenis,id_ca) VALUES(OLD.nominal,'M',OLD.id_ca);
        SELECT LAST_INSERT_ID() INTO t_new_id_transaksi_m;
        UPDATE channel_account SET saldo = saldo + OLD.nominal WHERE id_ca = NEW.id_ca;

        CALL VirtualCAPenarikan(OLD.id_penarikan, NEW.id_ca, 1);
        DELETE FROM detil_transaksi_penarikan_anggaran WHERE id_penarikan = OLD.id_penarikan;
        UPDATE detil_transaksi_pengembalian_penarikan_anggaran SET id_transaksi_m = t_new_id_transaksi_m WHERE id_penarikan = OLD.id_penarikan AND id_transaksi_m IS NULL;
    END IF;

    IF NEW.status = '1' AND OLD.status = '0' OR NEW.status = '1' AND (OLD.nominal != NEW.nominal) THEN
        INSERT INTO transaksi(nominal,jenis,id_ca) VALUES(NEW.nominal,'K',NEW.id_ca);
        SELECT LAST_INSERT_ID() INTO t_new_id_transaksi_k;
        UPDATE channel_account SET saldo = saldo - NEW.nominal WHERE id_ca = NEW.id_ca;

        CALL InsertDetilPenarikan(NEW.id_pelaksanaan, NEW.id_penarikan, NEW.id_ca, NEW.nominal, t_new_id_transaksi_k);

        CALL VirtualCAPenarikan(NEW.id_penarikan, NEW.id_ca, 0);
    END IF;
    END$$
DELIMITER ;

-- DROP PROCEDURE InsertRAB;
DELIMITER $$
CREATE PROCEDURE InsertRAB(IN in_id_rencana INT, IN in_id_kebutuhan INT, IN in_harga_satuan INT, IN in_jumlah INT, IN in_keterangan VARCHAR(50), IN in_pengirim CHAR(1), OUT out_id_rab BIGINT UNSIGNED)
LabelRAB:BEGIN
    DECLARE t_saldo, c_nominal_kebutuhan, t_total_kebutuhan BIGINT DEFAULT 0;
    SET c_nominal_kebutuhan = in_harga_satuan * in_jumlah;

    SELECT saldo INTO t_saldo FROM 
    (
        (
            SELECT SUM(d.jumlah_donasi) saldo 
            FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) 
            WHERE d.id_bantuan = (SELECT id_bantuan FROM rencana WHERE id_rencana = in_id_rencana) AND a.id_pelaksanaan IS NULL
            HAVING saldo > 0
        )
        UNION
        (
            SELECT MIN(a.saldo_donasi) saldo 
            FROM donasi d JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) 
            WHERE d.id_bantuan = (SELECT id_bantuan FROM rencana WHERE id_rencana = in_id_rencana)
            HAVING saldo > 0
        )
    ) s1 FOR UPDATE;
    SELECT SUM(nominal_kebutuhan) FROM rencana_anggaran_belanja JOIN rencana USING(id_rencana) WHERE id_rencana = in_id_rencana INTO t_total_kebutuhan;

    IF (t_total_kebutuhan + c_nominal_kebutuhan) > t_saldo AND UPPER(in_pengirim) = 'E' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Jumlah saldo tidak mencukupi';
        LEAVE LabelRAB;
    END IF;
    INSERT INTO rencana_anggaran_belanja(id_rencana,id_kebutuhan,harga_satuan,jumlah,nominal_kebutuhan,keterangan) VALUES(in_id_rencana,in_id_kebutuhan,in_harga_satuan,in_jumlah,c_nominal_kebutuhan,in_keterangan);
    SELECT CONCAT_WS(' ','ID kebutuhan<b>',in_id_kebutuhan,'</b>sejumlah<span class="font-weight-bolder">',FORMAT(c_nominal_kebutuhan,0,'id_ID'),'</span>berhasil ditambahkan') MESSAGE_TEXT;
    SET out_id_rab = (SELECT LAST_INSERT_ID());
END LabelRAB$$
DELIMITER ;

-- DROP PROCEDURE DeleteRAB;
DELIMITER $$
CREATE PROCEDURE DeleteRAB(IN in_id_rab BIGINT)
LabelRAB:BEGIN
    DECLARE c_penarikan TINYINT UNSIGNED DEFAULT 0;
    SELECT COUNT(pn.id_pelaksanaan) INTO c_penarikan FROM pelaksanaan p LEFT JOIN penarikan pn USING(id_pelaksanaan) WHERE pn.status = '1' AND p.id_rencana = (SELECT id_rencana FROM rencana_anggaran_belanja WHERE id_rab = in_id_rab);
    IF c_penarikan = 1 THEN
        SELECT 'Failed to delete: Sudah ada penarikan' MESSAGE_TEXT;
        LEAVE LabelRAB;
    END IF;

    DELETE FROM rencana_anggaran_belanja WHERE id_rab = in_id_rab;
    IF ROW_COUNT() = 0 THEN
        SELECT 'Failed to delete RAB' MESSAGE_TEXT;
        LEAVE LabelRAB;
    END IF;

    SELECT 'Success to delete RAB' MESSAGE_TEXT;
END LabelRAB$$
DELIMITER ;

-- DROP TRIGGER BeforeDeleteRAB;
DELIMITER $$
CREATE TRIGGER BeforeDeleteRAB
BEFORE DELETE ON rencana_anggaran_belanja FOR EACH ROW
BEGIN
    DECLARE c_penarikan TINYINT DEFAULT 0;

    SELECT COUNT(p.id_pelaksanaan) INTO c_penarikan FROM rencana r LEFT JOIN pelaksanaan p USING(id_rencana) LEFT JOIN penarikan pn USING(id_pelaksanaan) WHERE pn.status = '1' AND r.id_rencana = OLD.id_rencana;
    IF c_penarikan > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to delete RAB: penarikan sudah dilakukan';
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER AfterDeleteRAB;
DELIMITER $$
CREATE TRIGGER AfterDeleteRAB
AFTER DELETE ON rencana_anggaran_belanja FOR EACH ROW
BEGIN
    UPDATE rencana SET total_anggaran = total_anggaran - OLD.nominal_kebutuhan WHERE id_rencana = OLD.id_rencana;
END$$
DELIMITER ;

-- DROP TRIGGER AfterInsertRAB;
DELIMITER $$
CREATE TRIGGER AfterInsertRAB
AFTER INSERT ON rencana_anggaran_belanja FOR EACH ROW
BEGIN
    UPDATE rencana SET total_anggaran = total_anggaran + NEW.nominal_kebutuhan WHERE id_rencana = NEW.id_rencana;
END$$
DELIMITER ;

-- DROP TRIGGER BeforeUpdateRAB;
DELIMITER $$
CREATE TRIGGER BeforeUpdateRAB
BEFORE UPDATE ON rencana_anggaran_belanja FOR EACH ROW
BlockTrigger:BEGIN
    DECLARE c_penarikan TINYINT DEFAULT 0;
    DECLARE t_status CHAR(2);
    DECLARE t_id_pemohon INT UNSIGNED;
    DECLARE t_saldo, t_total_kebutuhan BIGINT UNSIGNED;

    SELECT r.status, b.id_pemohon FROM rencana r JOIN bantuan b USING(id_bantuan) WHERE r.id_rencana = NEW.id_rencana INTO t_status, t_id_pemohon;
    SELECT COUNT(p.id_pelaksanaan) INTO c_penarikan FROM rencana r LEFT JOIN pelaksanaan p USING(id_rencana) LEFT JOIN penarikan pn USING(id_pelaksanaan) WHERE pn.status = '1' AND r.id_rencana = NEW.id_rencana;

    SET @TRTextUpdateRab = NULL;

    IF UPPER(t_status) = 'TD' OR c_penarikan > 0 THEN
        IF c_penarikan > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falied to update RAB: Sudah ada penarikan';
            -- SET @TRTextUpdateRab = 'Falied to update RAB: Sudah ada penarikan';
        ELSE
            SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Falied to update RAB: Status rencana tidak disetujui';
            -- SET @TRTextUpdateRab = 'Falied to update RAB: Status rencana tidak disetujui';
        END IF;
    	-- SET NEW.jumlah = OLD.jumlah;
        -- SET NEW.harga_satuan = OLD.harga_satuan;
        -- SET NEW.nominal_kebutuhan = OLD.nominal_kebutuhan;
        -- SET NEW.create_at = OLD.create_at;
        LEAVE BlockTrigger;
    END IF;

    IF OLD.jumlah <> NEW.jumlah OR OLD.harga_satuan <> NEW.harga_satuan THEN
        SET NEW.nominal_kebutuhan = NEW.jumlah * NEW.harga_satuan;
        IF NEW.nominal_kebutuhan > OLD.nominal_kebutuhan AND t_id_pemohon IS NOT NULL THEN
            SELECT SUM(saldo) INTO t_saldo FROM 
            (
                (
                    SELECT SUM(d.jumlah_donasi) saldo 
                    FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) 
                    WHERE d.id_bantuan = (SELECT id_bantuan FROM rencana WHERE id_rencana = OLD.id_rencana) AND a.id_pelaksanaan IS NULL
                    HAVING saldo > 0
                )
                UNION
                (
                    SELECT MIN(a.saldo_donasi) saldo 
                    FROM donasi d JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) 
                    WHERE d.id_bantuan = (SELECT id_bantuan FROM rencana WHERE id_rencana = OLD.id_rencana)
                    HAVING saldo > 0
                )
            ) s1 FOR UPDATE;
            
            SELECT (total_anggaran - OLD.nominal_kebutuhan) + NEW.nominal_kebutuhan INTO t_total_kebutuhan FROM rencana WHERE id_rencana = OLD.id_rencana;            
            IF t_total_kebutuhan > t_saldo THEN
                -- SET NEW.jumlah = OLD.jumlah;
                -- SET NEW.harga_satuan = OLD.harga_satuan;
                -- SET NEW.nominal_kebutuhan = OLD.nominal_kebutuhan;
                -- SET NEW.create_at = OLD.create_at;
                -- SET @TRTextUpdateRab = 'Falied to update RAB: Sisa saldo tidak mencukupi';
                SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'Falied to update RAB: Sisa saldo tidak mencukupi';
                LEAVE BlockTrigger;
            END IF;
        END IF;
    END IF;
END BlockTrigger$$
DELIMITER ;

-- DROP TRIGGER AfterUpdateRAB;
DELIMITER $$
CREATE TRIGGER AfterUpdateRAB
AFTER UPDATE ON rencana_anggaran_belanja FOR EACH ROW
BEGIN
    SET @TRTextUpdateRab = NULL;

    IF OLD.jumlah <> NEW.jumlah OR OLD.harga_satuan <> NEW.harga_satuan THEN
        UPDATE rencana SET status = 'BD', total_anggaran = (total_anggaran + NEW.nominal_kebutuhan) - OLD.nominal_kebutuhan WHERE id_rencana = NEW.id_rencana;
        IF (ROW_COUNT() != 0) THEN
            SET @TRTextUpdateRab = CONCAT_WS(' ','Success update RAB on id_rencana', NEW.id_rencana, 'with id_kebutuhan',NEW.id_kebutuhan);
        ELSE
            SET @TRTextUpdateRab = 'Falied to update RAB';
        END IF;
    END IF;
END$$
DELIMITER ;

-- DROP PROCEDURE KalkulasiPinbuk;
DELIMITER $$
CREATE PROCEDURE KalkulasiPinbuk(IN in_id_pinbuk INT,IN in_nominal BIGINT, IN in_id_ca_pengirim TINYINT, IN in_id_bantuan INT, IN in_id_pelaksanaan INT)
    BEGIN
        IF in_id_bantuan IS NOT NULL AND in_id_pelaksanaan IS NOT NULL THEN
            INSERT INTO detil_pinbuk(id_pinbuk,id_donasi,nominal_pindah)
            SELECT in_id_pinbuk, id_donasi, nominal_peggunaan FROM
            ( 
                (
                    SELECT s1.id_donasi, s1.id_ca, SUM(s2.saldo_pinbuk) saldo_pinbuk_akumulatif, s1.saldo_pinbuk nominal_peggunaan 
                    FROM (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND d.id_bantuan = in_id_bantuan AND v.saldo > 0 AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL) AND pl.id_pelaksanaan = in_id_pelaksanaan
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi >= 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                        ) s1, (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND d.id_bantuan = in_id_bantuan AND v.saldo > 0 AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL) AND pl.id_pelaksanaan = in_id_pelaksanaan
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi >= 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                    ) s2
                    WHERE s1.id_donasi >= s2.id_donasi
                    GROUP BY s1.id_donasi, s1.id_ca, s1.saldo_pinbuk
                    HAVING saldo_pinbuk_akumulatif < in_nominal
                    ORDER BY s1.id_donasi
                ) UNION (
                    SELECT s1.id_donasi, s1.id_ca, SUM(s2.saldo_pinbuk) saldo_pinbuk_akumulatif, in_nominal - (SUM(s2.saldo_pinbuk) - s1.saldo_pinbuk) nominal_penggunaan 
                    FROM (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND d.id_bantuan = in_id_bantuan AND v.saldo > 0 AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL) AND pl.id_pelaksanaan = in_id_pelaksanaan
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi >= 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                        ) s1, (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND d.id_bantuan = in_id_bantuan AND v.saldo > 0 AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL) AND pl.id_pelaksanaan = in_id_pelaksanaan
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi >= 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                    ) s2
                    WHERE s1.id_donasi >= s2.id_donasi
                    GROUP BY s1.id_donasi, s1.id_ca, s1.saldo_pinbuk
                    HAVING saldo_pinbuk_akumulatif >= in_nominal
                    ORDER BY s1.id_donasi
                    LIMIT 1
                )
            ) s4
            ORDER BY id_donasi;
        ELSEIF in_id_bantuan IS NOT NULL AND in_id_pelaksanaan IS NULL THEN
            INSERT INTO detil_pinbuk(id_pinbuk,id_donasi,nominal_pindah)
            SELECT in_id_pinbuk, id_donasi, nominal_peggunaan FROM
            ( 
                (
                    SELECT s1.id_donasi, s1.id_ca, SUM(s2.saldo_pinbuk) saldo_pinbuk_akumulatif, s1.saldo_pinbuk nominal_peggunaan 
                    FROM (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND d.id_bantuan = in_id_bantuan AND v.saldo > 0 AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL)
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi >= 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                        ) s1, (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND d.id_bantuan = in_id_bantuan AND v.saldo > 0 AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL)
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi >= 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                    ) s2
                    WHERE s1.id_donasi >= s2.id_donasi
                    GROUP BY s1.id_donasi, s1.id_ca, s1.saldo_pinbuk
                    HAVING saldo_pinbuk_akumulatif < in_nominal
                    ORDER BY s1.id_donasi
                ) UNION (
                    SELECT s1.id_donasi, s1.id_ca, SUM(s2.saldo_pinbuk) saldo_pinbuk_akumulatif, in_nominal - (SUM(s2.saldo_pinbuk) - s1.saldo_pinbuk) nominal_penggunaan 
                    FROM (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND d.id_bantuan = in_id_bantuan AND v.saldo > 0 AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL)
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi >= 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                        ) s1, (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND d.id_bantuan = in_id_bantuan AND v.saldo > 0 AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL)
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi >= 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                    ) s2
                    WHERE s1.id_donasi >= s2.id_donasi
                    GROUP BY s1.id_donasi, s1.id_ca, s1.saldo_pinbuk
                    HAVING saldo_pinbuk_akumulatif >= in_nominal
                    ORDER BY s1.id_donasi
                    LIMIT 1
                )
            ) s4
            ORDER BY id_donasi;
        ELSE
        INSERT INTO detil_pinbuk(id_pinbuk,id_donasi,nominal_pindah)
            SELECT in_id_pinbuk, id_donasi, nominal_peggunaan FROM
            ( 
                (
                    SELECT s1.id_donasi, s1.id_ca, SUM(s2.saldo_pinbuk) saldo_pinbuk_akumulatif, s1.saldo_pinbuk nominal_peggunaan 
                    FROM (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND v.saldo > 0 AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL)
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi >= 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                        ) s1, (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND v.saldo > 0 AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL)
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi >= 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                    ) s2
                    WHERE s1.id_donasi >= s2.id_donasi
                    GROUP BY s1.id_donasi, s1.id_ca, s1.saldo_pinbuk
                    HAVING saldo_pinbuk_akumulatif < in_nominal
                    ORDER BY s1.id_donasi
                ) UNION (
                    SELECT s1.id_donasi, s1.id_ca, SUM(s2.saldo_pinbuk) saldo_pinbuk_akumulatif, in_nominal - (SUM(s2.saldo_pinbuk) - s1.saldo_pinbuk) nominal_penggunaan 
                    FROM (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND v.saldo > 0 AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL)
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi >= 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                        ) s1, (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND v.saldo > 0 AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL)
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi >= 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                    ) s2
                    WHERE s1.id_donasi >= s2.id_donasi
                    GROUP BY s1.id_donasi, s1.id_ca, s1.saldo_pinbuk
                    HAVING saldo_pinbuk_akumulatif >= in_nominal
                    ORDER BY s1.id_donasi
                    LIMIT 1
                )
            ) s4
            ORDER BY id_donasi;
        END IF;
        SET @ROW_COUNT = (SELECT ROW_COUNT());
        IF @ROW_COUNT = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to insert detil_pinbuk';
        END IF;
    END$$
DELIMITER ;

-- CURSOR
-- DROP PROCEDURE VirtualCADonasi;
DELIMITER $$
CREATE PROCEDURE VirtualCADonasi(IN in_id_pinbuk INT)
    BlockVirtual:BEGIN
        -- Menampung State Cursor
        DECLARE finished_VCAD INT DEFAULT 0;
        -- Var untuk menampung isi cursor donasi
        DECLARE cdp_id_donasi BIGINT;
        DECLARE cdp_nominal INT;
        DECLARE cdp_id_ca_pengirim, cdp_id_ca_penerima TINYINT UNSIGNED;
        -- Var untuk menampung isi cursor hasil kalkulasi
        DECLARE ck_saldo_donasi INT;
        
        DECLARE t_id_ca_pengirim TINYINT;
        DECLARE c_id_ca TINYINT DEFAULT 0;
        DECLARE t_saldo BIGINT;

        DECLARE list_detil_pinbuk CURSOR FOR SELECT dp.id_donasi, dp.nominal_pindah, pb.id_ca_pengirim, pb.id_ca_penerima FROM detil_pinbuk dp JOIN pinbuk pb USING(id_pinbuk) WHERE pb.id_pinbuk = in_id_pinbuk;
        
        -- declare NOT FOUND handler
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_VCAD = 1;

        SET @insertQuery = "INSERT INTO virtual_ca_donasi(saldo,id_donasi,id_ca) VALUES(?,?,?)";
        PREPARE stmtI FROM @insertQuery;
        
        SET @updateQueryMinus = "UPDATE virtual_ca_donasi SET saldo = saldo - ? WHERE id_donasi = ? AND id_ca = ?";
        PREPARE stmtUM FROM @updateQueryMinus;
        SET @updateQueryAdd = "UPDATE virtual_ca_donasi SET saldo = saldo + ? WHERE id_donasi = ? AND id_ca = ?";
        PREPARE stmtUA FROM @updateQueryAdd;

        OPEN list_detil_pinbuk;

        ListDP:WHILE NOT finished_VCAD DO
            FETCH list_detil_pinbuk INTO cdp_id_donasi, cdp_nominal, cdp_id_ca_pengirim, cdp_id_ca_penerima;
            
            IF finished_VCAD = 1 THEN
                LEAVE ListDP;
            END IF;

            SELECT id_ca, saldo FROM virtual_ca_donasi WHERE id_donasi = cdp_id_donasi AND id_ca = cdp_id_ca_pengirim FOR UPDATE INTO t_id_ca_pengirim, t_saldo;
            IF c_id_ca IS NULL THEN
                ROLLBACK;
                SELECT 'ID CA pengirim tidak ditemukan di Virtual CAD' MESSAGE_TEXT;
                LEAVE ListDP;
            END IF;

            IF t_saldo < cdp_nominal THEN
                ROLLBACK;
                SELECT CONCAT_WS(' ','Saldo Virtual pengirim tidak cukup',cdp_nominal,t_saldo) MESSAGE_TEXT;
                LEAVE ListDP;
            END IF;

            SET @nominal = cdp_nominal;
            SET @id_donasi = cdp_id_donasi;
            SET @id_ca_pengirim = cdp_id_ca_pengirim;
            SET @id_ca_penerima = cdp_id_ca_penerima;

            SELECT COUNT(id_ca) INTO c_id_ca FROM virtual_ca_donasi WHERE id_donasi = cdp_id_donasi AND id_ca = cdp_id_ca_penerima;
            IF c_id_ca = 0 THEN
                -- UPDATE virtual_ca_donasi SET saldo = saldo - cdp_nominal WHERE id_donasi = cdp_id_donasi AND id_cp = cdp_id_ca_pengirim;
                EXECUTE stmtUM USING @nominal, @id_donasi, @id_ca_pengirim;
                IF ROW_COUNT() != 1 THEN
                    ROLLBACK;
                    SELECT 'Gagal update before insert saldo Virtual CA baru' MESSAGE_TEXT;
                    LEAVE ListDP;
                END IF;
                -- INSERT INTO virtual_ca_donasi(saldo,id_donasi,id_ca) VALUES(cdp_nominal, cdp_id_donasi,cdp_id_ca_penerima);
                EXECUTE stmtI USING @nominal, @id_donasi, @id_ca_penerima;
                IF ROW_COUNT() != 1 THEN
                    ROLLBACK;
                    SELECT 'Gagal insert data Virtual CA baru' MESSAGE_TEXT;
                    LEAVE ListDP;
                END IF;
            ELSE
                -- UPDATE virtual_ca_donasi SET saldo = saldo - cdp_nominal WHERE id_donasi = cdp_id_donasi AND id_cp = cdp_id_ca_pengirim;
                EXECUTE stmtUM USING @nominal, @id_donasi, @id_ca_pengirim;
                IF ROW_COUNT() != 1 THEN
                    ROLLBACK;
                    SELECT 'Gagal update saldo Virtual CA pengirim' MESSAGE_TEXT;
                    LEAVE ListDP;
                END IF;

                -- UPDATE virtual_ca_donasi SET saldo = saldo + cdp_nominal WHERE id_donasi = cdp_id_donasi AND id_cp = cdp_id_ca_penerima;
                EXECUTE stmtUA USING @nominal, @id_donasi, @id_ca_penerima;
                IF ROW_COUNT() != 1 THEN
                    ROLLBACK;
                    SELECT 'Gagal update saldo Virtual CA penerima' MESSAGE_TEXT;
                    LEAVE ListDP;
                END IF;
            END IF;
        END WHILE ListDP;        
        CLOSE list_detil_pinbuk;
        DEALLOCATE PREPARE stmtI;
        DEALLOCATE PREPARE stmtUM;
        DEALLOCATE PREPARE stmtUA;
    END BlockVirtual$$
DELIMITER ;

-- DROP TRIGGER BeforeInsertPinbuk;
DELIMITER $$
CREATE TRIGGER BeforeInsertPinbuk
BEFORE INSERT ON pinbuk FOR EACH ROW
BEGIN
    DECLARE c_pengirim, c_penerima TINYINT;
    DECLARE t_saldo, sum_saldo, t_sum_nominal_pinbuk BIGINT;
    DECLARE t_pelaksanaan_bantuan INT;

    IF UPPER(NEW.status) = 'OK' AND NEW.id_gambar IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Id gambar pinbuk wajib diisi untuk menambah data dengan status OK';
    END IF;

    SELECT 
        (SELECT COUNT(id_ca) FROM channel_account WHERE id_ca = NEW.id_ca_pengirim), 
        (SELECT COUNT(id_ca) FROM channel_account WHERE id_ca = NEW.id_ca_penerima)
        INTO c_pengirim, c_penerima
    FROM DUAL; 

    IF c_pengirim = 0 OR c_penerima = 0 OR NEW.id_ca_pengirim = NEW.id_ca_penerima THEN
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'ID CA tidak valid';
    END IF;

    IF NEW.penyesuaian_total_pinbuk != 1 AND NEW.penyesuaian_total_pinbuk != 0 AND NEW.penyesuaian_total_pinbuk IS NOT NULL THEN
        SET NEW.penyesuaian_total_pinbuk = 0;
    END IF;

    SELECT saldo INTO t_saldo FROM channel_account WHERE id_ca = NEW.id_ca_pengirim FOR UPDATE;

    IF NEW.total_pinbuk > t_saldo THEN
        IF NEW.penyesuaian_total_pinbuk = 0 THEN
            SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'Saldo di channael_account tidak mencukupi untuk dipinbuk';
        ELSE
            SET NEW.total_pinbuk = t_saldo;
        END IF;
    END IF;

    IF NEW.id_pelaksanaan IS NULL AND NEW.id_bantuan IS NULL THEN
        SELECT IFNULL(SUM(saldo), 0) FROM virtual_ca_donasi WHERE id_ca = NEW.id_ca_pengirim INTO sum_saldo;
    ELSEIF NEW.id_pelaksanaan IS NULL AND NEW.id_bantuan != NULL THEN
        SELECT IFNULL(SUM(v.saldo), 0) FROM virtual_ca_donasi v JOIN (
            SELECT DISTINCT(d.id_donasi), d.jumlah_donasi, pl.id_pelaksanaan, pl.status status_pl, pn.id_penarikan, pn.nominal, pn.status status_pn
            FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan)
            WHERE d.bayar = 1 AND d.id_bantuan = NEW.id_bantuan AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL)
        ) iv USING(id_donasi) 
        WHERE v.saldo > 0 AND v.id_ca = NEW.id_ca_pengirim INTO sum_saldo;
    ELSE
        SELECT r.id_bantuan FROM pelaksanaan pl JOIN rencana r USING(id_rencana) WHERE pl.id_pelaksanaan = NEW.id_pelaksanaan INTO t_pelaksanaan_bantuan;
        IF t_pelaksanaan_bantuan <> NEW.id_bantuan THEN
            SIGNAL SQLSTATE '45003' SET MESSAGE_TEXT = 'Id bantuan pinbuk not qeual with pelaksanaan rencana bantuan';
        ELSE 
            SELECT IFNULL(SUM(v.saldo), 0) FROM virtual_ca_donasi v JOIN (
                SELECT DISTINCT(d.id_donasi), d.jumlah_donasi, pl.id_pelaksanaan, pl.status status_pl, pn.id_penarikan, pn.nominal, pn.status status_pn
                FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_donasi) LEFT JOIN pelaksanaan pl USING(id_pelaksanaan) LEFT JOIN penarikan pn ON(pn.id_pelaksanaan = pl.id_pelaksanaan)
                WHERE d.bayar = 1 AND d.id_bantuan = NEW.id_bantuan AND pl.id_pelaksanaan = NEW.id_pelaksanaan AND (pn.status IS NULL OR pn.status = '0') AND (pl.status != 'S' OR pl.status IS NULL)
            ) iv USING(id_donasi) 
            WHERE v.saldo > 0 AND v.id_ca = NEW.id_ca_pengirim INTO sum_saldo;
        END IF;
    END IF;

    IF NEW.penyesuaian_total_pinbuk = 0 THEN
        SELECT IFNULL(SUM(total_pinbuk),0) INTO t_sum_nominal_pinbuk FROM pinbuk p WHERE p.status != 'OK' AND p.status != 'CL' AND p.id_ca_pengirim = NEW.id_ca_pengirim;
        IF (sum_saldo - t_sum_nominal_pinbuk) < NEW.total_pinbuk THEN
            SIGNAL SQLSTATE '45004' SET MESSAGE_TEXT = 'Saldo daftar pinbuk di Virtual CA tidak cukup';
        END IF;
    ELSE
        IF sum_saldo = 0 THEN
            SIGNAL SQLSTATE '45005' SET MESSAGE_TEXT = 'Saldo daftar pinbuk di Virtual CA tidak ada';
        END IF;
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER AfterInsertPinbuk;
DELIMITER $$
CREATE TRIGGER AfterInsertPinbuk
AFTER INSERT ON pinbuk FOR EACH ROW
BEGIN
    CALL KalkulasiPinbuk(NEW.id_pinbuk, NEW.total_pinbuk, NEW.id_ca_pengirim, NEW.id_bantuan, NEW.id_pelaksanaan);
END$$
DELIMITER ;

-- DROP PROCEDURE Pinbuk;
DELIMITER $$
CREATE PROCEDURE Pinbuk(IN in_nominal BIGINT, IN in_id_ca_pengirim TINYINT, IN in_id_ca_penerima TINYINT, IN in_ket VARCHAR(50), IN in_id_bantuan INT, IN in_id_gambar INT, IN in_penyesuaian TINYINT)
    PinBuk:BEGIN
        DECLARE t_sum_nominal_pinbuk, saldo_pengirim BIGINT;
        DECLARE t_id_pinbuk, c_inserted_dp_rows INT;
        DECLARE c_pengirim, c_penerima, id_ca_penerima, id_ca_pengirim TINYINT;

        SELECT (SELECT COUNT(id_ca) FROM channel_account WHERE id_ca = in_id_ca_pengirim), (SELECT COUNT(id_ca) FROM channel_account WHERE id_ca = in_id_ca_penerima) INTO c_pengirim, c_penerima FROM DUAL; 
        IF c_pengirim = 0 OR c_penerima = 0 OR in_id_ca_pengirim = in_id_ca_penerima THEN
            SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'ID CA tidak valid';
            LEAVE PinBuk;
        END IF;

        SELECT saldo INTO saldo_pengirim FROM channel_account WHERE id_ca = in_id_ca_pengirim FOR UPDATE;
        IF saldo_pengirim < in_nominal THEN
            SIGNAL SQLSTATE '45003' SET MESSAGE_TEXT = 'Saldo CA tidak cukup';
            LEAVE PinBuk;
        END IF;

        START TRANSACTION;
        -- SET AUTOCOMMIT = 0;

        INSERT INTO pinbuk(total_pinbuk,keterangan,id_ca_pengirim,id_ca_penerima,status,id_gambar,id_bantuan,penyesuaian_total_pinbuk) VALUES(in_nominal,in_ket,in_id_ca_pengirim,in_id_ca_penerima,'OK',in_id_gambar,in_id_bantuan,in_penyesuaian);
        IF ROW_COUNT() = 0 THEN
            ROLLBACK;
            SIGNAL SQLSTATE '45005' SET MESSAGE_TEXT = 'Failed to insert pinbuk';
            LEAVE PinBuk;
        END IF;

        SELECT LAST_INSERT_ID() INTO t_id_pinbuk;

        SELECT SUM(nominal_pindah) INTO t_sum_nominal_pinbuk FROM detil_pinbuk WHERE id_pinbuk = t_id_pinbuk;
        IF in_penyesuaian = 0 THEN
            IF t_sum_nominal_pinbuk <> in_nominal THEN
                ROLLBACK;
                SIGNAL SQLSTATE '45006' SET MESSAGE_TEXT = 'Failed to insert equal total pinbuk with sum nominal detil_pinbuk';
                LEAVE PinBuk;
            END IF;
        ELSE
            IF t_sum_nominal_pinbuk < in_nominal THEN
                UPDATE pinbuk SET total_pinbuk = t_sum_nominal_pinbuk WHERE id_pinbuk = t_id_pinbuk;
                IF ROW_COUNT() != 1 THEN
                    SIGNAL SQLSTATE '45007' SET MESSAGE_TEXT = 'Failed to adjust total_pinbuk';
                END IF;
            END IF;
        END IF;

        CALL VirtualCADonasi(t_id_pinbuk);

        UPDATE channel_account SET saldo = saldo - t_sum_nominal_pinbuk WHERE id_ca = in_id_ca_pengirim;
        IF ROW_COUNT() != 1 THEN
            ROLLBACK;
            SIGNAL SQLSTATE '45008' SET MESSAGE_TEXT = 'Gagal update saldo pengirim';
            LEAVE PinBuk;
        END IF;
        
        UPDATE channel_account SET saldo = saldo + t_sum_nominal_pinbuk WHERE id_ca = in_id_ca_penerima;
        IF ROW_COUNT() != 1 THEN
            ROLLBACK;
            SIGNAL SQLSTATE '45009' SET MESSAGE_TEXT = 'Gagal update saldo penerima';
            LEAVE PinBuk;
        END IF;

        COMMIT;
        SELECT CONCAT_WS(' ','Pinbuk success',@c_inserted_dp_rows,'rows inserted to detil_pinbuk with pinbuk id',t_id_pinbuk) MESSAGE_TEXT;
    END PinBuk$$
DELIMITER ;

-- DROP PROCEDURE PinbukByUpdate;
DELIMITER $$
CREATE PROCEDURE PinbukByUpdate(IN in_id_pinbuk INT, IN in_status VARCHAR(3))
    PinBuk:BEGIN
        DECLARE t_total_pinbuk, t_sum_nominal_pinbuk, saldo_pengirim, saldo_penerima BIGINT;
        DECLARE c_inserted_dp_rows, t_id_bantuan INT;
        DECLARE c_pengirim, c_penerima, t_id_ca_penerima, t_id_ca_pengirim, t_penyesuaian TINYINT;
        DECLARE t_status VARCHAR(3);

        SELECT id_ca_pengirim, id_ca_penerima, total_pinbuk, status, id_bantuan, penyesuaian_total_pinbuk FROM pinbuk WHERE id_pinbuk = in_id_pinbuk INTO t_id_ca_pengirim, t_id_ca_penerima, t_total_pinbuk, t_status, t_id_bantuan, t_penyesuaian;

        SELECT (SELECT COUNT(id_ca) FROM channel_account WHERE id_ca = t_id_ca_pengirim), (SELECT COUNT(id_ca) FROM channel_account WHERE id_ca = t_id_ca_penerima) INTO c_pengirim, c_penerima FROM DUAL; 
        IF c_pengirim = 0 OR c_penerima = 0 OR t_id_ca_pengirim = t_id_ca_penerima THEN
            SELECT 'ID CA tidak valid' MESSAGE_TEXT;
            LEAVE PinBuk;
        END IF;

        IF in_status = t_status THEN
            SELECT 'Status pinbuk masih sama dengan yang lama' MESSAGE_TEXT;
            LEAVE Pinbuk;
        END IF;

        START TRANSACTION;
        -- SET AUTOCOMMIT = 0;

        UPDATE pinbuk SET status = in_status WHERE id_pinbuk = in_id_pinbuk;
        IF ROW_COUNT() != 1 THEN
            ROLLBACK;
            SELECT 'Gagal update status pinbuk' MESSAGE_TEXT;
            LEAVE PinBuk;
        END IF;

        IF t_status != 'OK' AND in_status = 'OK' THEN
            SELECT saldo INTO saldo_pengirim FROM channel_account WHERE id_ca = t_id_ca_pengirim FOR UPDATE;

            SELECT SUM(nominal_pindah) INTO t_sum_nominal_pinbuk FROM detil_pinbuk WHERE id_pinbuk = in_id_pinbuk;
            IF saldo_pengirim < t_sum_nominal_pinbuk THEN
                ROLLBACK;
                SELECT 'Saldo CA pengirim tidak cukup' MESSAGE_TEXT;
                LEAVE PinBuk;
            END IF;

            IF t_penyesuaian = 0 THEN
                IF t_total_pinbuk <> t_sum_nominal_pinbuk THEN
                    ROLLBACK;
                    SELECT 'Failed to insert equal total pinbuk with sum nominal detil_pinbuk' MESSAGE_TEXT;
                    LEAVE PinBuk;
                END IF;
            ELSE
                IF t_total_pinbuk <> t_sum_nominal_pinbuk THEN
                    UPDATE pinbuk SET total_pinbuk = t_sum_nominal_pinbuk WHERE id_pinbuk = in_id_pinbuk;
                    IF ROW_COUNT() != 1 THEN
                        ROLLBACK;
                        SELECT 'Failed to adjust total_pinbuk on pinbuk' MESSAGE_TEXT;
                        LEAVE PinBuk;
                    END IF;
                    SET t_total_pinbuk = t_sum_nominal_pinbuk;
                END IF;
            END IF;

            CALL VirtualCADonasi(in_id_pinbuk);

            UPDATE channel_account SET saldo = saldo - t_total_pinbuk WHERE id_ca = t_id_ca_pengirim;
            IF ROW_COUNT() != 1 THEN
                ROLLBACK;
                SELECT 'Gagal update saldo pengirim' MESSAGE_TEXT;
                LEAVE PinBuk;
            END IF;
            
            UPDATE channel_account SET saldo = saldo + t_total_pinbuk WHERE id_ca = t_id_ca_penerima;
            IF ROW_COUNT() != 1 THEN
                ROLLBACK;
                SELECT 'Gagal update saldo penerima' MESSAGE_TEXT;
                LEAVE PinBuk;
            END IF;

            COMMIT;
            SELECT CONCAT_WS(' ','Pinbuk success',@c_inserted_dp_rows,'rows inserted to detil_pinbuk with pinbuk id',in_id_pinbuk,'') MESSAGE_TEXT;
            LEAVE PinBuk;
        END IF;
        
        IF t_status = 'OK' AND in_status != 'OK' THEN
            ROLLBACK;
            SELECT 'Status pinbuk sudah OK, tidak bisa diubah';
            LEAVE Pinbuk;
        END IF;

        IF t_status != 'OK' AND in_status = 'CL' THEN
            DELETE FROM detil_pinbuk WHERE id_pinbuk = in_id_pinbuk;
            SET @c_deleted_dp_rows = ROW_COUNT();
            IF @c_deleted_dp_rows = 0 THEN
                ROLLBACK;
                SELECT 'Failed to delete detil_pinbuk on RevokePinbuk' MESSAGE_TEXT;
                LEAVE PinBuk;
            ELSE
                COMMIT;
                SELECT CONCAT_WS(' ','Revoke Pinbuk success',@c_deleted_dp_rows,'rows are deleted on detil_pinbuk with pinbuk id',in_id_pinbuk) MESSAGE_TEXT;
            END IF;
        END IF;
    END PinBuk$$
DELIMITER ;

-- DROP PROCEDURE BeforePenarikan;
DELIMITER $$
CREATE PROCEDURE BeforePenarikan(IN in_id_pelaksanaan INT, IN t_nominal_penarikan BIGINT, IN in_id_pencairan INT)
    PBLabel:BEGIN
        DECLARE t_count TINYINT DEFAULT 0;

        CALL check_table_exists('temp_penarikan_vl1', @table_exists);
        SELECT @table_exists INTO t_count;
        IF t_count = 0 THEN
            CREATE TEMPORARY TABLE temp_penarikan_vl1(
                id BIGINT UNSIGNED,
                id_donasi INT UNSIGNED,
                id_ca TINYINT UNSIGNED,
                nominal BIGINT UNSIGNED
            );
        ELSE
            DELETE FROM temp_penarikan_vl1;
        END IF;

        INSERT INTO temp_penarikan_vl1
        WITH cte AS (
            SELECT ROW_NUMBER() OVER (ORDER BY id_donasi) id, v.id_donasi, v.id_ca, IF(sum_saldo_penarikan > v.saldo, v.saldo, sum_saldo_penarikan) nominal_penarikan FROM
                (
                    SELECT id_donasi, SUM(saldo_penarikan) sum_saldo_penarikan FROM
                    (
                        (
                                SELECT vadp.id_donasi, vadp.id_apd, vadp.saldo_penarikan, SUM(vadp2.saldo_penarikan) kumulatif FROM 
                                (SELECT a.id_donasi, a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) WHERE a.id_pelaksanaan = in_id_pelaksanaan GROUP BY a.id_apd HAVING saldo_penarikan > 0) vadp,
                                (SELECT a.id_donasi, a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) WHERE a.id_pelaksanaan = in_id_pelaksanaan GROUP BY a.id_apd HAVING saldo_penarikan > 0) vadp2
                                WHERE vadp.id_apd >= vadp2.id_apd
                                GROUP BY vadp.id_apd
                                HAVING kumulatif < t_nominal_penarikan
                                ORDER BY vadp.id_apd
                        )
                        UNION
                        (
                                SELECT vadp.id_donasi, vadp.id_apd, t_nominal_penarikan - (SUM(vadp2.saldo_penarikan) - vadp.saldo_penarikan) saldo_penarikan, SUM(vadp2.saldo_penarikan) kumulatif FROM 
                                (SELECT a.id_donasi, a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) WHERE a.id_pelaksanaan = in_id_pelaksanaan GROUP BY a.id_apd HAVING saldo_penarikan > 0) vadp,
                                (SELECT a.id_donasi, a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) WHERE a.id_pelaksanaan = in_id_pelaksanaan GROUP BY a.id_apd HAVING saldo_penarikan > 0) vadp2
                                WHERE vadp.id_apd >= vadp2.id_apd
                                GROUP BY vadp.id_apd
                                HAVING kumulatif >= t_nominal_penarikan
                                ORDER BY vadp.id_apd
                                LIMIT 1
                        )
                    ) s_penarikan
                    GROUP BY id_donasi
                    ORDER BY id_donasi
                ) s_penarikan2 JOIN virtual_ca_donasi v USING(id_donasi)
                WHERE v.saldo > 0
        ) 
        (
            SELECT vl1.* FROM cte vl1, cte vl2
            WHERE vl1.id >= vl2.id
            GROUP BY vl1.id_ca, vl1.id_donasi, vl1.nominal_penarikan
            HAVING SUM(vl2.nominal_penarikan) < t_nominal_penarikan
            ORDER BY vl1.id
        )
        UNION
        (
            SELECT vl1.id, vl1.id_donasi, vl1.id_ca, t_nominal_penarikan - (SUM(vl2.nominal_penarikan) - vl1.nominal_penarikan) FROM cte vl1, cte vl2
            WHERE vl1.id >= vl2.id
            GROUP BY vl1.id_ca, vl1.id_donasi, vl1.nominal_penarikan
            HAVING SUM(vl2.nominal_penarikan) >= t_nominal_penarikan
            ORDER BY vl1.id
            LIMIT 1
        )
        ORDER BY id, id_donasi, id_ca;

        IF ROW_COUNT() = 0 THEN
            SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'INSERT TABLE temp_penarikan_vl1 are failed';
            LEAVE PBLabel;
        END IF;

        CALL check_table_exists('temp_penarikan_vresult1', @table_exists);
        SELECT @table_exists INTO t_count;
        IF t_count = 0 THEN
            CREATE TEMPORARY TABLE temp_penarikan_vresult1(
                id_ca TINYINT UNSIGNED,
                id_donasi BIGINT UNSIGNED,
                nominal BIGINT,
                id_pelaksanaan INT UNSIGNED,
                id_pencairan INT UNSIGNED
            );
        ELSE
            DELETE FROM temp_penarikan_vresult1;
        END IF;

        INSERT INTO temp_penarikan_vresult1
        SELECT id_ca, id_donasi, nominal, in_id_pelaksanaan, in_id_pencairan FROM temp_penarikan_vl1;

        IF ROW_COUNT() = 0 THEN
            SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'INSERT TABLE temp_penarikan_vresult1 are failed';
            LEAVE PBLabel;
        END IF;

        CALL check_table_exists('temp_penarikan_vresult2', @table_exists);
        SELECT @table_exists INTO t_count;
        IF t_count = 0 THEN
            CREATE TEMPORARY TABLE temp_penarikan_vresult2(
                id_ca TINYINT UNSIGNED,
                id_donasi BIGINT UNSIGNED,
                nominal BIGINT
            );
        ELSE
            DELETE FROM temp_penarikan_vresult2;
        END IF;

        INSERT INTO temp_penarikan_vresult2
        SELECT id_ca, id_donasi, nominal FROM temp_penarikan_vresult1;

        IF ROW_COUNT() = 0 THEN
            SIGNAL SQLSTATE '45004' SET MESSAGE_TEXT = 'INSERT TABLE temp_penarikan_vresult2 are failed';
            LEAVE PBLabel;
        END IF;
    END PBLabel$$
DELIMITER ;

-- DROP PROCEDURE KalkulasiPenarikan;
DELIMITER $$
CREATE PROCEDURE KalkulasiPenarikan(IN in_id_pelaksanaan INT UNSIGNED, IN in_persentase_penarikan DECIMAL(5,2), IN in_id_pencairan INT UNSIGNED)
KPLabel:BEGIN
    DECLARE t_nominal_penarikan, t_total, t_total_penarikan_pencairan, t_total_anggaran, t_total_penarikan_pelaksanaan,  t_saldo_pencairan, t_saldo_pelaksanaan, t_virtual_penarikan BIGINT UNSIGNED;
    DECLARE t_status CHAR(2);
    DECLARE t_id_bantuan INT UNSIGNED;
    
    IF (SELECT COUNT(id_pelaksanaan) FROM pelaksanaan WHERE id_pelaksanaan = in_id_pelaksanaan) = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Id Pelaksanaan tidak ditemukan';
        LEAVE KPLabel;
    END IF;

    IF (SELECT COUNT(id_pencairan) FROM pencairan WHERE id_pencairan = in_id_pencairan) = 0 THEN
        SIGNAL SQLSTATE '45001'
            SET MESSAGE_TEXT = 'Id Pencairan tidak ditemukan';
        LEAVE KPLabel;
    END IF;

    SELECT 
        IF(pr.total * (in_persentase_penarikan / 100) > pr.total - plc.total_penarikan_pr, 
            IF(pr.total - plc.total_penarikan_pr > pll.total_anggaran - pll.total_penarikan_pl,
                    pll.total_anggaran - pll.total_penarikan_pl,
                    pr.total - plc.total_penarikan_pr
            ),
            IF(pr.total * (in_persentase_penarikan / 100) > pll.total_anggaran - pll.total_penarikan_pl,
                pll.total_anggaran - pll.total_penarikan_pl,
                FLOOR(pr.total * (in_persentase_penarikan / 100))
            )
        ), 
        pll.id_bantuan, pll.status, pr.total, plc.total_penarikan_pr, pr.total - plc.total_penarikan_pr saldo_pencairan, pll.total_anggaran, pll.total_penarikan_pl, pll.total_anggaran - pll.total_penarikan_pl saldo_pelaksanaan
        INTO t_nominal_penarikan, t_id_bantuan, t_status, t_total, t_total_penarikan_pencairan, t_saldo_pencairan, t_total_anggaran, t_total_penarikan_pelaksanaan, t_saldo_pelaksanaan
    FROM pencairan pr, 
    (SELECT SUM(IFNULL(pn.nominal,0)) total_penarikan_pr FROM pencairan pr LEFT JOIN penarikan pn USING(id_pencairan) WHERE pr.id_pencairan = in_id_pencairan AND pr.status != 'OK' GROUP BY pn.id_pencairan) plc,
    (SELECT SUM(IFNULL(pn.nominal,0)) total_penarikan_pl, pl.total_anggaran, rn.status, rn.id_bantuan FROM rencana rn JOIN pelaksanaan pl USING(id_rencana) LEFT JOIN penarikan pn USING(id_pelaksanaan) WHERE pl.id_pelaksanaan = in_id_pelaksanaan AND rn.status = 'SD' GROUP BY pl.id_pelaksanaan) pll
    WHERE pr.status != 'OK' AND pr.id_pencairan = in_id_pencairan FOR UPDATE;

    IF t_status != 'SD' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Status rencana belum disetujui';
        LEAVE KPLabel;
    END IF;

    CALL BeforePenarikan(in_id_pelaksanaan, t_nominal_penarikan, in_id_pencairan);

    SELECT b_penarikan.id_ca, nominal, cp.nomor, cp.atas_nama, cp.jenis, gp.path_gambar, gp.nama,
    id_pinbuk_k, status_k, nominal_pindah_keluar sum_nominal_pindah_keluar, id_ca_penerima_keluar, gcpk.path_gambar path_gambar_keluar, gcpk.nama nama_keluar,
    id_pinbuk_m, status_m, nominal_pindah_masuk sum_nominal_pindah_masuk, id_ca_pengirim_masuk, gcpm.path_gambar path_gambar_masuk, gcpm.nama nama_masuk
    FROM
    (
        SELECT rlp.*, 
		    ldp_k.id_pinbuk id_pinbuk_k, ldp_k.status status_k, ldp_k.nominal_pindah_keluar, ldp_k.id_ca_penerima id_ca_penerima_keluar,
			ldp_m.id_pinbuk id_pinbuk_m, ldp_m.status status_m, ldp_m.nominal_pindah_masuk, ldp_m.id_ca_pengirim id_ca_pengirim_masuk
        FROM
        (
            SELECT id_ca, SUM(nominal) nominal
            FROM temp_penarikan_vl1
            GROUP BY id_ca
            ORDER BY id_ca
        ) rlp LEFT JOIN (
            SELECT v1.id_ca, p.id_pinbuk, p.status, SUM(IF(dp.nominal_pindah > v1.nominal, v1.nominal, dp.nominal_pindah)) nominal_pindah_keluar, p.id_ca_penerima
            FROM temp_penarikan_vresult1 v1 LEFT JOIN detil_pinbuk dp ON(dp.id_donasi = v1.id_donasi) LEFT JOIN pinbuk p USING(id_pinbuk)
            WHERE p.status != 'OK' AND v1.id_ca = p.id_ca_pengirim
            GROUP BY v1.id_ca, p.id_pinbuk
            ORDER BY p.id_pinbuk, v1.id_ca
        ) ldp_k ON (rlp.id_ca = ldp_k.id_ca) LEFT JOIN (
            SELECT v2.id_ca, p.id_pinbuk, p.status, SUM(dp.nominal_pindah) nominal_pindah_masuk, p.id_ca_pengirim
            FROM temp_penarikan_vresult2 v2 LEFT JOIN detil_pinbuk dp ON(dp.id_donasi = v2.id_donasi) LEFT JOIN pinbuk p USING(id_pinbuk)
            WHERE p.status != 'OK' AND v2.id_ca = p.id_ca_penerima
            GROUP BY p.id_pinbuk, v2.id_ca
        ) ldp_m ON (rlp.id_ca = ldp_m.id_ca)
        ORDER BY id_pinbuk_m, id_ca
    ) b_penarikan 
    LEFT JOIN channel_payment cp ON(cp.id_ca = b_penarikan.id_ca) LEFT JOIN gambar gp ON(gp.id_gambar = cp.id_gambar) 
    LEFT JOIN channel_payment cpk ON(cpk.id_ca = b_penarikan.id_ca_penerima_keluar) LEFT JOIN gambar gcpk ON(gcpk.id_gambar = cpk.id_gambar)
    LEFT JOIN channel_payment cpm ON(cpm.id_ca = b_penarikan.id_ca_pengirim_masuk) LEFT JOIN gambar gcpm ON(gcpm.id_gambar = cpm.id_gambar)
    WHERE cp.jenis NOT IN ('QR','GI') AND ((cpk.jenis NOT IN ('QR','GI') OR cpk.jenis IS NULL) AND (cpm.jenis NOT IN ('QR','GI') OR cpm.jenis IS NULL))
    ORDER BY id_pinbuk_m, id_ca;
END KPLabel$$
DELIMITER ;

-- DROP PROCEDURE InsertPenarikan;
DELIMITER $$
CREATE PROCEDURE InsertPenarikan(IN in_id_pelaksanaan INT UNSIGNED, IN in_persentase_penarikan DECIMAL(5,2), IN in_id_pencairan INT UNSIGNED)
    IPLabel:BEGIN
        DECLARE t_total_penarikan BIGINT UNSIGNED;
        DECLARE t_status CHAR(2);
        DECLARE t_id_bantuan, c_pinbuk_list INT UNSIGNED;
        DECLARE t_saldo_pencairan, t_saldo_pelaksanaan BIGINT UNSIGNED;

        IF (SELECT COUNT(id_pelaksanaan) FROM pelaksanaan WHERE id_pelaksanaan = in_id_pelaksanaan) = 0 THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Id Pelaksanaan tidak ditemukan';
            LEAVE IPLabel;
        END IF;

        IF (SELECT COUNT(id_pencairan) FROM pencairan WHERE id_pencairan = in_id_pencairan) = 0 THEN
            SIGNAL SQLSTATE '45001'
                SET MESSAGE_TEXT = 'Id Pencairan tidak ditemukan';
            LEAVE IPLabel;
        END IF;

        SELECT 
            IF(pr.total * (in_persentase_penarikan / 100) > pr.total - plc.total_penarikan_pr, 
                IF(pr.total - plc.total_penarikan_pr > pll.total_anggaran - pll.total_penarikan_pl,
                        pll.total_anggaran - pll.total_penarikan_pl,
                        pr.total - plc.total_penarikan_pr
                ),
                IF(pr.total * (in_persentase_penarikan / 100) > pll.total_anggaran - pll.total_penarikan_pl,
                    pll.total_anggaran - pll.total_penarikan_pl,
                    FLOOR(pr.total * (in_persentase_penarikan / 100))
                )
            ) total_penarikan, 
            pr.total - plc.total_penarikan_pr saldo_pencairan, pll.total_anggaran - pll.total_penarikan_pl saldo_pelaksanaan, pll.id_bantuan, pll.status INTO t_total_penarikan, t_saldo_pencairan, t_saldo_pelaksanaan, t_id_bantuan, t_status
        FROM pencairan pr, 
        (SELECT SUM(IFNULL(pn.nominal,0)) total_penarikan_pr FROM pencairan pr LEFT JOIN penarikan pn USING(id_pencairan) WHERE pr.id_pencairan = in_id_pencairan AND pr.status != 'OK' GROUP BY pn.id_pencairan) plc,
        (SELECT SUM(IFNULL(pn.nominal,0)) total_penarikan_pl, pl.total_anggaran, rn.status, rn.id_bantuan FROM rencana rn JOIN pelaksanaan pl USING(id_rencana) LEFT JOIN penarikan pn USING(id_pelaksanaan) WHERE pl.id_pelaksanaan = in_id_pelaksanaan AND rn.status = 'SD' GROUP BY pl.id_pelaksanaan) pll
        WHERE pr.status != 'OK' AND pr.id_pencairan = in_id_pencairan;

        IF t_status != 'SD' THEN
            SIGNAL SQLSTATE '45002'
                SET MESSAGE_TEXT = 'Status rencana belum disetujui';
            LEAVE IPLabel;
        END IF;

        IF t_saldo_pencairan = 0 THEN
            SIGNAL SQLSTATE '45003'
                SET MESSAGE_TEXT = 'Saldo ID Pencairan sudah habis';
            LEAVE IPLabel;
        END IF;

        IF t_saldo_pelaksanaan = 0 THEN
            SIGNAL SQLSTATE '45004'
                SET MESSAGE_TEXT = 'Saldo ID Pelaksanaan sudah habis';
            LEAVE IPLabel;
        END IF;

        IF t_saldo_pencairan < t_total_penarikan THEN
            SIGNAL SQLSTATE '45005'
                SET MESSAGE_TEXT = 'Saldo pencairan tidak mencukupi';
            LEAVE IPLabel;
        END IF;

        IF t_saldo_pelaksanaan < t_total_penarikan THEN
            SIGNAL SQLSTATE '45006'
                SET MESSAGE_TEXT = 'Saldo pelaksanaan tidak mencukup';
            LEAVE IPLabel;
        END IF;

        CALL BeforePenarikan(in_id_pelaksanaan, t_total_penarikan, in_id_pencairan);

        SELECT COUNT(id_pinbuk) FROM temp_penarikan_vresult1 tpv1 JOIN detil_pinbuk dp USING(id_donasi) JOIN pinbuk p USING(id_pinbuk) WHERE p.status != 'OK' AND tpv1.id_ca = p.id_ca_pengirim GROUP BY tpv1.id_pelaksanaan, id_pencairan INTO c_pinbuk_list;
        IF c_pinbuk_list > 0 THEN
            SET @err_mess = CONCAT_WS(' ', 'Terdapat sejumlah',c_pinbuk_list,'donasi yang sedang dalam proses pinbuk');
            SIGNAL SQLSTATE '45007'
                SET MESSAGE_TEXT = @err_mess;
                SET @err_mess = NULL;
            LEAVE IPLabel;
        END IF;

        INSERT INTO penarikan(id_pelaksanaan,id_ca,nominal,id_pencairan)
        SELECT in_id_pelaksanaan, id_ca, SUM(nominal), in_id_pencairan
        FROM temp_penarikan_vresult1
        GROUP BY id_ca;
    END IPLabel$$
DELIMITER ;

-- DROP TRIGGER BeforeInsertPelaksanaan;
DELIMITER $$
CREATE TRIGGER BeforeInsertPelaksanaan
BEFORE INSERT ON pelaksanaan FOR EACH ROW
BEGIN 
    DECLARE t_total_anggaran_rencana BIGINT;
    DECLARE t_status_rencana CHAR(2);
    SELECT total_anggaran, status FROM rencana WHERE id_rencana = NEW.id_rencana INTO t_total_anggaran_rencana, t_status_rencana;

    IF t_total_anggaran_rencana < NEW.total_anggaran THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NEW.total_anggaran must less then rencana.total_anggaran';
    END IF;

    IF t_status_rencana != 'SD' THEN
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Status need an agreement';
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER BeforeUpdatePelaksanaan;
DELIMITER $$
CREATE TRIGGER BeforeUpdatePelaksanaan
BEFORE UPDATE ON pelaksanaan FOR EACH ROW
BEGIN
    DECLARE c_penarikan TINYINT UNSIGNED;
    DECLARE t_total_anggaran_rencana, t_sum_nominal_penarikan BIGINT UNSIGNED;
    IF OLD.total_anggaran <> NEW.total_anggaran THEN
        SELECT total_anggaran FROM rencana WHERE id_rencana = NEW.id_rencana INTO t_total_anggaran_rencana;
        IF NEW.total_anggaran > t_total_anggaran_rencana THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to update pelaksanaan.total_anggaran must not greater than rencana.total_anggaran';
        END IF;

        SELECT COUNT(pn.id_penarikan), IFNULL(SUM(pn.nominal), 0)
        FROM penarikan pn JOIN pencairan pr USING(id_pencairan) 
        WHERE pn.status = '1' AND id_pelaksanaan = OLD.id_pelaksanaan
        GROUP BY pn.id_pelaksanaan INTO c_penarikan, t_sum_nominal_penarikan;
        
        IF c_penarikan > 0 AND NEW.total_anggaran < t_sum_nominal_penarikan THEN 
            SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Failed to update pelaksanaan.total_anggaran must greater than SUM(penarikan.nominal)';
        END IF;
    END IF;
END$$
DELIMITER ; 

-- DROP PROCEDURE ReInsertPenarikan;
DELIMITER $$
CREATE PROCEDURE ReInsertPenarikan(IN in_old_jumlah_anggaran BIGINT, IN in_new_jumlah_anggaran BIGINT, IN in_id_pelaksanaan INT)
    ReInsertPenarikanLabel:BEGIN
        -- Menampung State Cursor
        DECLARE finished_ReInsertPenarikan TINYINT DEFAULT 0;
        -- Var untuk menampung isi cursor
        DECLARE crip_id_pencairan INT UNSIGNED;
        DECLARE crip_persentase_pencairan TINYINT DEFAULT 0;

        DECLARE list_rip CURSOR FOR 
            SELECT pn.id_pencairan, (SUM(pn.nominal) / pr.total) * 100
            FROM penarikan pn JOIN pencairan pr USING(id_pencairan) 
            WHERE pn.id_pelaksanaan = in_id_pelaksanaan AND pn.status = '0' 
            GROUP BY pn.id_pencairan;
         -- declare NOT FOUND handler
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_ReInsertPenarikan = 1;
        OPEN list_rip;

        RIPLoop:WHILE NOT finished_ReInsertPenarikan DO
            FETCH list_rip INTO crip_id_pencairan, crip_persentase_pencairan;

            IF finished_ReInsertPenarikan = 1 THEN
                CLOSE list_rip;
                LEAVE RIPLoop;
            END IF;

            UPDATE pencairan SET total = (total - in_old_jumlah_anggaran) + in_new_jumlah_anggaran WHERE id_pencairan = crip_id_pencairan;
            DELETE FROM penarikan WHERE id_pelaksanaan = in_id_pelaksanaan AND id_pencairan = crip_id_pencairan;

            CALL InsertPenarikan(in_id_pelaksanaan, crip_persentase_pencairan, crip_id_pencairan);
        END WHILE RIPLoop;
    END ReInsertPenarikanLabel$$
DELIMITER ;

-- DROP TRIGGER BeforeDeletePencairan;
DELIMITER $$
CREATE TRIGGER BeforeDeletePencairan
BEFORE DELETE ON pencairan FOR EACH ROW
BEGIN
    DECLARE c_penarikan SMALLINT UNSIGNED;

    SELECT COUNT(pn.id_penarikan) FROM pencairan pr JOIN penarikan pn USING(id_pencairan) WHERE pr.id_pencairan = OLD.id_pencairan AND pn.status = '1' INTO c_penarikan;
    IF c_penarikan > 0 THEN 
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to delete pencairan: sudah ada penarikan';
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER BeforeDeletePenarikan;
DELIMITER $$
CREATE TRIGGER BeforeDeletePenarikan
BEFORE DELETE ON penarikan FOR EACH ROW
BEGIN
    IF OLD.status = '1' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to delete penarikan: status penarikan sudah dilakukan';
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER AfterDeletePenarikan;
DELIMITER $$
CREATE TRIGGER AfterDeletePenarikan
AFTER DELETE ON penarikan FOR EACH ROW
BEGIN
    DECLARE t_nominal BIGINT;

    SELECT IFNULL(SUM(nominal), 0) INTO t_nominal FROM penarikan WHERE id_pencairan = OLD.id_pencairan;
    IF (t_nominal < 1) THEN
        DELETE FROM pencairan WHERE id_pencairan = OLD.id_pencairan;
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER AfterUpdatePelaksanaan;
DELIMITER $$
CREATE TRIGGER AfterUpdatePelaksanaan
AFTER UPDATE ON pelaksanaan FOR EACH ROW
BEGIN
    DECLARE c_penarikan, c_pencairan, t_persentase_pencairan_penarikan TINYINT DEFAULT 0;
    DECLARE t_sum_nominal_penarikan INT UNSIGNED;

    IF OLD.total_anggaran <> NEW.total_anggaran THEN
        SELECT COUNT(p.id_pelaksanaan) INTO c_penarikan FROM pelaksanaan p LEFT JOIN penarikan pn USING(id_pelaksanaan) WHERE pn.status = '1' AND p.id_pelaksanaan = NEW.id_pelaksanaan;
        IF c_penarikan = 0 THEN
            SELECT SUM(vlt.count) INTO c_pencairan FROM (
                SELECT COUNT(DISTINCT(pn.id_pencairan)) count FROM penarikan pn JOIN pencairan pr USING(id_pencairan) WHERE pn.id_pelaksanaan = NEW.id_pelaksanaan AND pn.status = '0' GROUP BY pr.id_pencairan
            ) vlt;
            
            IF c_pencairan > 0 THEN
                UPDATE pencairan pr, penarikan pn SET pr.total = (pr.total - pn.nominal) WHERE pr.id_pencairan = pn.id_pencairan AND pn.id_pelaksanaan = NEW.id_pelaksanaan;
                DELETE FROM penarikan WHERE id_pelaksanaan = NEW.id_pelaksanaan;
            END IF;
        END IF;
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER BeforeUpdateDetilTransaksiPenarikanAnggaran;
DELIMITER $$
CREATE TRIGGER BeforeUpdateDetilTransaksiPenarikanAnggaran
BEFORE UPDATE ON detil_transaksi_penarikan_anggaran FOR EACH ROW
BEGIN
    IF OLD.nominal <> NEW.nominal THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Not allowed direct update on detil_transaksi_penarikan_anggaran.nominal';
    END IF;

    IF OLD.saldo <> NEW.saldo THEN
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Not allowed to change detil_transaksi_penarikan_anggaran.saldo';
    END IF;

    IF (NEW.id_apd <> OLD.id_apd) THEN
    	SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_penarikan_anggaran.id_apd';
    END IF;

    IF (NEW.id_penarikan <> OLD.id_penarikan) THEN
    	SIGNAL SQLSTATE '45003' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_penarikan_anggaran.id_penarikan';
    END IF;

    IF (NEW.id_transaksi <> OLD.id_transaksi) THEN
    	SIGNAL SQLSTATE '45004' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_penarikan_anggaran.id_transaksi';
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER BeforeDeleteDetilTransaksiPenarikanAnggaran;
DELIMITER $$
CREATE TRIGGER BeforeDeleteDetilTransaksiPenarikanAnggaran
BEFORE DELETE ON detil_transaksi_penarikan_anggaran FOR EACH ROW
BEGIN
    DECLARE t_status CHAR(1);

    SELECT status FROM penarikan WHERE id_penarikan = OLD.id_penarikan INTO t_status;
    IF t_status = '1' THEN
        SET @err_mess = CONCAT_WS(' ','Failed to delete detil_transaksi_penarikan_anggaran: status penarikan sudah dilakukan pada id_penarikan',OLD.id_penarikan,'dengan id_dtpa',OLD.id_dtpa);
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @err_mess;
    END IF;

    INSERT INTO detil_transaksi_pengembalian_penarikan_anggaran(id_apd, id_transaksi_k, id_penarikan, saldo, nominal)
    VALUES(OLD.id_apd, OLD.id_transaksi, OLD.id_penarikan, OLD.saldo, OLD.nominal);
END$$
DELIMITER ;

-- DROP TRIGGER BeforeUpdateDTPPA;
DELIMITER $$
CREATE TRIGGER BeforeUpdateDTPPA
BEFORE UPDATE ON detil_transaksi_pengembalian_penarikan_anggaran FOR EACH ROW
BEGIN
    IF (NEW.nominal <> OLD.nominal) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Not allowed to update on detil_transaksi_pengembalian_penarikan_anggaran.nominal';
    END IF;

    IF (NEW.saldo <> OLD.saldo) THEN
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Not allowed to update on detil_transaksi_pengembalian_penarikan_anggaran.saldo';
    END IF;

    IF (NEW.id_penarikan <> OLD.id_penarikan) THEN
    	SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_pengembalian_penarikan_anggaran.id_penarikan';
    END IF;
    
    IF (NEW.id_transaksi_k <> OLD.id_transaksi_k) THEN
    	SIGNAL SQLSTATE '45003' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_pengembalian_penarikan_anggaran.id_transaksi_k';
    END IF;

    IF (NEW.id_transaksi_m <> OLD.id_transaksi_m) THEN
    	SIGNAL SQLSTATE '45004' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_pengembalian_penarikan_anggaran.id_transaksi_m';
    END IF;

    IF (NEW.id_apd <> OLD.id_apd) THEN
    	SIGNAL SQLSTATE '45004' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_pengembalian_penarikan_anggaran.id_apd';
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER BeforeUpdatePencairan
DELIMITER $$
CREATE TRIGGER BeforeUpdatePencairan
BEFORE UPDATE ON pencairan FOR EACH ROW
BEGIN
    DECLARE cd_id_pelaksanaan, c_belum_ditarik SMALLINT UNSIGNED;
    DECLARE t_total_penarikan BIGINT UNSIGNED;

    IF OLD.status = NEW.status AND NEW.status = 'OK' AND OLD.total != NEW.total THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot update total on OK status pencairan';
    END IF;

    SELECT SUM(nominal) FROM penarikan WHERE id_pencairan = OLD.id_pencairan INTO t_total_penarikan;
    IF OLD.total > NEW.total AND t_total_penarikan > NEW.total THEN
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Total pencairan must greater or equal to total penarikan';
    END IF;

    IF UPPER(OLD.status) = 'OP' AND UPPER(NEW.status) = 'OK' THEN
        SELECT COUNT(DISTINCT(id_pelaksanaan)), SUM(nominal), SUM(CASE WHEN status = '0' THEN 1 ELSE 0 END) c_belum_ditarik FROM penarikan WHERE id_pencairan = OLD.id_pencairan INTO cd_id_pelaksanaan, t_total_penarikan, c_belum_ditarik;
        IF c_belum_ditarik != 0 THEN
            SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'Status penarikan belum semuanya selesai';
        END IF;

        IF cd_id_pelaksanaan = 1 AND t_total_penarikan < NEW.total THEN
            SET NEW.total = t_total_penarikan;
        END IF;
    END IF;
END$$
DELIMITER ;

INSERT INTO donasi(id_bantuan,id_donatur,alias,jumlah_donasi, bayar, id_cp, create_at, waktu_bayar) VALUES(1,1,'CSR BJB',2312500000,'1',2,'2021-08-11','2021-08-11'),(1,1,'CSR BJB',3125000000,'1',2,'2021-10-10','2021-10-10'),(2,1,"PROGRAM",750000,'1',3,'2021-10-16','2021-10-16');
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
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) LIKE "%lebaran untuk yatim & lansia%" AND blokir IS NULL),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'YBM BRI KC. ASIA AFRIKA'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BRI%"),'2022-04-28',6000000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'jafarpager@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-05-12',100000,1),
((SELECT id_bantuan FROM bantuan WHERE UPPER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'DINNY RESTY'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BRI%"),'2022-07-08',100000,1),
((SELECT id_bantuan FROM bantuan WHERE UPPER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ETI SUMIATI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BRI%"),'2022-07-08',300000,1),
((SELECT id_bantuan FROM bantuan WHERE LOWER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE LOWER(email) = 'arifriandi834@gmail.com'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TN'),'2022-07-14',150000,1),
((SELECT id_bantuan FROM bantuan WHERE UPPER(nama) = 'infaq'),(SELECT id_donatur FROM donatur WHERE UPPER(nama) = 'ETI SUMIATI'),(SELECT id_cp FROM channel_payment WHERE jenis = 'TB' AND UPPER(nama) LIKE "%BRI%"),'2022-07-14',300000,1);

UPDATE donasi SET waktu_bayar = create_at WHERE bayar = '1' AND waktu_bayar IS NULL;

INSERT INTO donasi(alias,id_donatur,jumlah_donasi,bayar,waktu_bayar,create_at,modified_at,id_cp,id_bantuan) VALUES
((SELECT samaran FROM donatur WHERE email = 'csr@bjb.co.id'), (SELECT id_donatur FROM donatur WHERE email = 'csr@bjb.co.id'), 1185000000,1,'2022-07-08','2022-07-08','2022-07-08',(SELECT id_cp FROM channel_payment WHERE jenis = 'GI' AND nomor = '0001000080001'),(SELECT id_bantuan FROM bantuan WHERE UPPER(nama) = 'RAIH SURGA BERSAMA POJOK QURBAN'));

DELETE FROM petugas_pencairan;
DELETE FROM pencairan;
ALTER TABLE pencairan AUTO_INCREMENT = 1;
DELETE FROM pelaksanaan;
ALTER TABLE pelaksanaan AUTO_INCREMENT = 1;
ALTER TABLE anggaran_pelaksanaan_donasi AUTO_INCREMENT = 1;
DELETE FROM rencana;
ALTER TABLE rencana AUTO_INCREMENT = 1;
ALTER TABLE rencana_anggaran_belanja AUTO_INCREMENT = 1;
DELETE FROM kebutuhan;
ALTER TABLE kebutuhan AUTO_INCREMENT = 1;

INSERT INTO kebutuhan(nama) VALUES('RAB Full Cover ada di Internal');
INSERT INTO kebutuhan(nama,id_kk) VALUES
('Operasional',1),
('Box Snak',5),
('Snak',3),
('Air mineral',4);

INSERT INTO rencana(keterangan, status, create_at, id_pembuat) VALUE('Program sembako BJB Agustus 2021', 'SD', '2021-08-11', 1),
('Program sembako BJB November 2021', 'SD', '2021-11-10', 1),
('Geberr Baitul Mutaqin', 'SD', '2021-11-16', 1),
('Program sembako BJB Maret 2022','SD','2022-03-18', 1),
('Program Kurban BJB 2022','SD','2022-07-08', 1);

UPDATE rencana SET id_bantuan = 1 WHERE id_rencana IN ('1','2','4') AND LOWER(keterangan) LIKE '%sembako bjb%';
UPDATE rencana SET id_bantuan = 2 WHERE id_rencana IN ('3') AND LOWER(keterangan) LIKE '%geberr%';
UPDATE rencana SET id_bantuan = (SELECT id_bantuan FROM bantuan WHERE nama = 'RAIH SURGA BERSAMA POJOK QURBAN') WHERE LOWER(keterangan) LIKE '%kurban bjb%';

INSERT INTO rencana_anggaran_belanja(nominal_kebutuhan, harga_satuan, keterangan, id_kebutuhan, id_rencana) VALUES
(2312500000, 2312500000, 'TOTAL RAB', 1, 1),
(3125000000, 3125000000, 'TOTAL RAB', 1, 2),
(750000, 750000, 'TOTAL RAB', 1, 3),
(3125000000, 3125000000, 'TOTAL RAB', 1, 4),
(1185000000, 1185000000, 'TOTAL RAB', 1, 5);

UPDATE rencana SET status = 'SD' WHERE status = 'BD';

INSERT INTO pelaksanaan(deskripsi, jumlah_pelaksanaan, status, total_anggaran) VALUE('Program sembako BJB Agustus 2021', 15000, 'S', 2312500000),
('Program sembako BJB November 2021', 25000, 'S', 3125000000),
('Geberr Baitul Mutaqin', 150, 'S', 750000),
('Program sembako BJB Maret 2022',25000,'S',3125000000),
('Program Kurban BJB 2022',38,'S',1185000000);
UPDATE pelaksanaan p, rencana r SET p.id_rencana = r.id_rencana, p.create_at = r.create_at WHERE p.deskripsi = r.keterangan AND p.id_pelaksanaan = r.id_rencana;
-- status pelaksanaan J = Jalan/Sedang Berjalan, S = Selesai

INSERT INTO anggaran_pelaksanaan_donasi (id_donasi, id_pelaksanaan, id_kebutuhan, nominal_kebutuhan, nominal_penggunaan_donasi, saldo_kebutuhan, saldo_donasi, keterangan) VALUES
(1,1,1,2312500000,2312500000,0,0,"TOTAL RAB"),
(2,2,1,3125000000,3125000000,0,0,"TOTAL RAB"),
(3,3,1,750000,750000,0,0,"TOTAL RAB"),
((SELECT id_donasi FROM donasi WHERE create_at = '2022-03-18' AND id_bantuan = 1),4,1,3125000000,3125000000,0,0,"TOTAL RAB"),
((SELECT d.id_donasi FROM donasi d JOIN bantuan b USING(id_bantuan) WHERE d.create_at = '2022-07-08' AND UPPER(b.nama) LIKE '%RAIH SURGA BERSAMA POJOK QURBAN%'),5,1,1185000000,1185000000,0,0,"TOTAL RAB");

UPDATE anggaran_pelaksanaan_donasi a JOIN pelaksanaan p USING(id_pelaksanaan) SET a.create_at = p.create_at;

INSERT INTO pencairan(total, create_at)
SELECT total_anggaran, create_at FROM pelaksanaan WHERE status = 'S';

INSERT INTO petugas_pencairan(id_pencairan, id_petugas, create_at)
SELECT id_pencairan, (SELECT id_pegawai FROM pegawai WHERE email = 'jafarpager@gmail.com'), create_at FROM pencairan WHERE id_pencairan NOT IN (SELECT id_pencairan FROM petugas_pencairan);

UPDATE petugas_pencairan  SET status = 'R' WHERE status = 'D';

-- LOOP PR ATAU ENGGA
CALL InsertPenarikan(@id_pelaksanaan,100,@id_pencairan);
-- 

-- Contoh UPDATE WITH JOIN
-- UPDATE donasi JOIN bantuan USING(id_bantuan) SET id_cp = (SELECT id_cp FROM channel_payment WHERE kode = '110' AND jenis = 'GI') WHERE id_cp = 1 AND id_bantuan = 1;

-- ALTER CP Constraint 
-- ALTER TABLE channel_payment DROP INDEX U_NAMA_NOMOR_CHANNEL_PAYMENT;
-- ALTER TABLE channel_payment ADD CONSTRAINT U_NAMA_NOMOR_JENIS_CHANNEL_PAYMENT UNIQUE(nama,nomor,jenis);

-- KHUSUS LOCAL BIAR TIDAK CREATE EMAIL TERUS
ALTER TABLE donasi MODIFY notifikasi CHAR(1) DEFAULT '1';
-- KHUSUS SERVER WAJIB
ALTER TABLE donasi MODIFY notifikasi CHAR(1) DEFAULT NULL;

-- UPDATE bantuan SET tanggal_awal = create_at, tanggal_akhir = DATE_ADD(create_at, INTERVAL lama_penayangan DAY)  WHERE lama_penayangan IS NOT NULL

ALTER TABLE anggaran_pelaksanaan_donasi CONSTRAINT U_ID_KEBUTUHAN_ID_RENCANA_KETERANGAN_ANGGARAN_PELAKSANAAN_DONASI UNIQUE(id_kebutuhan, id_rencana, keterangan);

-- UPDATE INI UNTUK CEK TANGGAL PELAKSANAAN JIKA BELUM COCOK
-- UPDATE anggaran_pelaksanaan_donasi a LEFT JOIN donasi d USING(id_donasi) SET a.create_at = d.waktu_bayar WHERE a.id_pelaksanaan IS NOT NULL;
-- UPDATE pelaksanaan p JOIN anggaran_pelaksanaan_donasi a USING(id_pelaksanaan) SET p.create_at = a.create_at;

-- INSERT INTO virtual_ca_donasi(saldo,id_donasi,id_ca,create_at)
-- SELECT IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi), d.id_donasi, cp.id_ca, d.waktu_bayar 
-- FROM donasi d JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) 
-- WHERE d.bayar = 1 GROUP BY d.id_donasi ORDER BY id_donasi;

-- INSERT INI DI RUN JIKA TABEL penarikan SUDAH DIBUAT DAN ADA ISINYA DAN MENGINGINKAN RECORD TRANSAKSI DAN TRIGGER di penarikan BELUM DIBUAT ATAU TRIGER donasi BELUM DIBUAT
-- INSERT INTO transaksi(nominal,jenis,create_at,id_ca)
-- SELECT d.jumlah_donasi, 'M', d.waktu_bayar, cp.id_ca FROM donasi d JOIN channel_payment cp USING(id_cp) JOIN channel_account ca USING(id_ca) WHERE d.bayar = 1;
-- INSERT INTO transaksi(nominal,jenis,create_at,id_ca)
-- SELECT nominal,'K',waktu_penarikan, id_ca FROM penarikan;

-- INI UNTUK MEREORDER ID_DONASI JIKA ADA PENGHAPUSAN AKIBAT KESALAHAN DATA DONASI
-- ###########################################################
-- SET  @id_donasi := 177;
-- SET  @num := @id_donasi;
-- UPDATE donasi SET bayar = 0 WHERE id_donasi > @num;
-- DELETE FROM virtual_ca_donasi WHERE id_donasi > @num;
-- UPDATE donasi SET id_donasi = @num := (@num+1) WHERE id_donasi > @num;
-- ALTER TABLE donasi AUTO_INCREMENT = 1;
-- UPDATE donasi SET bayar = 1, modified_at = create_at WHERE id_donasi > @id_donasi;
-- UPDATE kuitansi k JOIN donasi d ON d.id_donasi = k.id_donasi SET k.create_at = d.create_at WHERE d.id_donasi > @id_donasi;
-- ###########################################################
-- AKHIR REORDER WAJIB REKONSIALISADI SESUAI KONDISI DTPA OR NON DTPA


-- UPDATE INI DI RUN UNTUK REKONSIALISASI SALDO DONASI (BUTUH PERBAIUKAN UNTUK MENGAMBIL KE DETIL PINBUK JUGA)
-- VERSI TABEL BELUM ADA TABEL DTPA
UPDATE channel_account, 
    (SELECT SUM(sd.saldo_donasi) saldo, sd.id_ca, sd.nama FROM 
        (
        (SELECT MIN(a.saldo_donasi) saldo_donasi, cp.id_ca, ca.nama FROM channel_account ca JOIN channel_payment cp USING(id_ca) JOIN donasi d ON(d.id_cp = cp.id_cp) JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) WHERE d.bayar = 1 GROUP BY cp.id_ca, a.id_donasi HAVING saldo_donasi)
        UNION
        (SELECT SUM(d.jumlah_donasi), cp.id_ca, ca.nama FROM channel_account ca JOIN channel_payment cp USING(id_ca) JOIN donasi d ON(d.id_cp = cp.id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) WHERE d.bayar = 1 AND a.id_pelaksanaan IS NULL GROUP BY cp.id_ca)
        ) sd
        GROUP BY sd.id_ca, sd.nama
    ) s 
SET channel_account.saldo = s.saldo WHERE channel_account.nama = s.nama;
-- VERSI TABEL DTPA SUDAH ADA
-- UPDATE channel_account, 
-- 	 (SELECT SUM(sd.saldo_donasi) saldo, sd.id_ca, sd.nama FROM 
--         (
--         (SELECT MIN(dtpa.saldo) saldo_donasi, cp.id_ca, ca.nama FROM channel_account ca JOIN channel_payment cp USING(id_ca) JOIN donasi d ON(d.id_cp = cp.id_cp) JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) JOIN detil_transaksi_penarikan_anggaran dtpa USING(id_apd) WHERE d.bayar = 1 GROUP BY cp.id_ca, a.id_donasi HAVING saldo_donasi)
--         UNION
--         (SELECT SUM(d.jumlah_donasi) saldo_donasi, cp.id_ca, ca.nama FROM channel_account ca JOIN channel_payment cp USING(id_ca) JOIN donasi d ON(d.id_cp = cp.id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) LEFT JOIN detil_transaksi_penarikan_anggaran dtpa USING(id_apd) WHERE d.bayar = 1 AND dtpa.id_apd IS NULL GROUP BY cp.id_ca, dtpa.id_dtpa)
--         ) sd
--         GROUP BY sd.id_ca, sd.nama
--     ) s
-- SET channel_account.saldo = s.saldo WHERE channel_account.nama = s.nama;

-- UPDATE INI UNTUK MENYESUAIKAN VCA dengan donasi
-- UPDATE virtual_ca_donasi v, 
-- (SELECT d.id_donasi, d.jumlah_donasi, d.create_at FROM donasi d JOIN kuitansi k USING(id_donasi) WHERE d.bayar = 1 AND k.id_donasi = d.id_donasi) d 
-- SET v.saldo = d.jumlah_donasi
-- WHERE v.id_donasi = d.id_donasi;

-- cek lagi F_ID_APD_DETIL_TRANSAKSI_PENARIKAN_ANGGARAN_ODC atau F_ID_APD_DETIL_TRANSAKSI_PENARIKAN_ANGGARAN_ODR
-- CREATE TABLE detil_transaksi_penarikan_anggaran(
--     id_dtpa BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     saldo INT UNSIGNED DEFAULT 0,
--     nominal INT UNSIGNED NOT NULL,
--     create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     id_apd BIGINT UNSIGNED,
--     id_penarikan INT UNSIGNED,
--     id_transaksi BIGINT UNSIGNED,
--     CONSTRAINT F_ID_APD_DETIL_TRANSAKSI_PENARIKAN_ANGGARAN_ODC FOREIGN KEY(id_apd) REFERENCES anggaran_pelaksanaan_donasi(id_apd) ON DELETE CASCADE ON UPDATE CASCADE,
--     CONSTRAINT F_ID_PENARIKAN_DETIL_TRANSAKSI_PENARIKAN_ANGGARAN_ODC FOREIGN KEY(id_penarikan) REFERENCES penarikan(id_penarikan) ON DELETE CASCADE ON UPDATE CASCADE,
--     CONSTRAINT F_ID_TRANSAKSI_DETIL_TRANSAKSI_PENARIKAN_ANGGARAN_ODR FOREIGN KEY(id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE RESTRICT ON UPDATE CASCADE
-- )ENGINE=INNODB;

-- TESTER ONLY ALUR
-- NANTI GET ID PEMBUAT BERDAASARKAN SESI
SET @id_pembuat = (SELECT id_pegawai FROM pegawai WHERE email = 'pojokberbagi.id@gmail.com');
-- INSERT PELAKSANAAN
INSERT INTO rencana(id_pembuat,id_bantuan) VALUES(@id_pembuat,2);
SET @id_rencana = LAST_INSERT_ID();
-- INSERT RAB
SET @pengirim = 'E'; -- Eksternal
CALL InsertRAB(@id_rencana, (SELECT id_kebutuhan FROM kebutuhan WHERE LOWER(nama) = 'box snak'), 2000, 10,NULL, @pengirim);
CALL InsertRAB(@id_rencana, (SELECT id_kebutuhan FROM kebutuhan WHERE LOWER(nama) = 'snak'), 1500, 50,NULL, @pengirim);
CALL InsertRAB(@id_rencana, (SELECT id_kebutuhan FROM kebutuhan WHERE LOWER(nama) = 'air mineral'), 500, 10,NULL, @pengirim);
CALL InsertRAB(@id_rencana, (SELECT id_kebutuhan FROM kebutuhan WHERE LOWER(nama) = 'operasional'), 100000, 1,NULL, @pengirim);
-- INSERT INTO rencana_anggaran_belanja (id_rencana,id_kebutuhan,harga_satuan,jumlah,nominal_kebutuhan) VALUES
-- (@id_rencana, (SELECT id_kebutuhan FROM kebutuhan WHERE LOWER(nama) = 'box snak'), 2000, 10, harga_satuan*jumlah),
-- (@id_rencana, (SELECT id_kebutuhan FROM kebutuhan WHERE LOWER(nama) = 'snak'), 1500, 50, harga_satuan*jumlah),
-- (@id_rencana, (SELECT id_kebutuhan FROM kebutuhan WHERE LOWER(nama) = 'air mineral'), 500, 10, harga_satuan*jumlah);
-- UPDATE total_anggaran sementara nanti via trigger setelah insert ke rencana_anggaran_belanja
-- KARENA SUDAH VIA TRIGGER JADI UPDATE INI ENGGA
-- UPDATE rencana SET total_anggaran = (SELECT SUM(nominal_kebutuhan) FROM rencana_anggaran_belanja WHERE id_pembuat = @id_pembuat AND id_rencana = @id_rencana GROUP BY id_rencana) WHERE id_rencana = @id_rencana;
-- INSERT pelaksanaan
INSERT INTO pelaksanaan(id_rencana,jumlah_pelaksanaan,total_anggaran) VALUES(@id_rencana,1,(SELECT total_anggaran FROM rencana WHERE id_rencana = @id_rencana));
SET @id_pelaksanaan = LAST_INSERT_ID();
-- Petakan Anggaran Donasi
CALL TotalAggaranBantuanPelaksanaan(@id_pelaksanaan);
-- Buat Pencairan
INSERT INTO pencairan(total, create_at)
SELECT total_anggaran, create_at FROM pelaksanaan WHERE id_pelaksanaan = 6;
SET @id_pencairan = LAST_INSERT_ID();
-- Pilih petugas pencairan
INSERT INTO petugas_pencairan(id_pencairan, id_petugas)
SELECT id_pencairan, (SELECT id_pegawai FROM pegawai WHERE email = 'jafarpager@gmail.com') FROM pencairan WHERE id_pencairan NOT IN (SELECT id_pencairan FROM petugas_pencairan);
-- UPDATE PERINTAH PENCAIRAN SUDAH DI BACA
UPDATE petugas_pencairan SET status = 'R' WHERE id_petugas = (SELECT id_pegawai FROM pegawai WHERE email = 'jafarpager@gmail.com') AND id_pencairan = 6;
-- Tampilkan dulu simulasi nominal dan rekening pencarian donasi
CALL KalkulasiPenarikan(@id_pelaksanaan,100);
Resutl awal ada 10k di dana 90k tunai
-- PINBUK AREA
-- Jika donasi terpisah2 akibat aktifitas pinbuk atau ingin melakukan penarikan di satu rekening untuk seruruh donasinya maka pinbuk dulu ke rekening yang ditujunya
-- in_id_gambar pada pinbuk merupakan id_gambar bukti pinbuk yang telah diuplaod
CALL Pinbuk(in_nominal, in_id_ca_pengirim, in_id_ca_penerima, in_ket, in_id_bantuan, in_id_gambar, in_penyesuaian);
...
CALL Pinbuk(in_nominal, in_id_ca_pengirim, in_id_ca_penerima, in_ket, in_id_bantuan, in_id_gambar, in_penyesuaian);
-- PINBUK AREA END

-- PINBUK AREA WITH PROSES BY UPDATE 
INSERT INTO pinbuk(total_pinbuk,keterangan,id_bantuan) VALUES(in_total_pinbuk, in_ket, in_id_bantuan);
@id_pinbuk = LAST_INSERT_ID();
-- AFTER UPLOAD gambar resi pinbuk
UPDATE pinbuk SET id_gambar = in_id_gambar WHERE id_pinbuk = @id_pinbuk;
-- AFTER CHECK THAT UPLOAD resi is OK
UPDATE pinbuk SET status = 'OK' WHERE id_pinbuk = @id_pinbuk;
-- END PINBUK AREA PROSES BY UPDATE

-- Jika sudah yakin penarikan langsung dimasukan
CALL InsertPenarikan(@id_pelaksanaan,100,@id_pencairan);
-- Update tiap masing2 ca yang sudah di tarik
SET @id_petugas = (SELECT id_petugas FROM petugas_pencairan WHERE id_petugas = ((SELECT id_pegawai FROM pegawai WHERE email = 'jafarpager@gmail.com')) AND id_pencairan = @id_pencairan);
UPDATE penarikan SET status = '1', id_petugas = @id_petugas WHERE id_pencairan = @id_pencairan AND id_pelaksanaan = @id_pelaksanaan AND id_ca = 1;
UPDATE penarikan SET status = '1', id_petugas = @id_petugas WHERE id_pencairan = @id_pencairan AND id_pelaksanaan = @id_pelaksanaan AND id_ca = 2;


-- INSERT INTO penarikan(nominal,status,waktu_penarikan,id_ca,id_pencairan,id_pelaksanaan, id_petugas);
-- SELECT SUM(apd.nominal_penggunaan_donasi), '1', plpc.create_at, cp.id_ca, plpc.id_pencairan, plpc.id_pelaksanaan, (SELECT id_pegawai FROM pegawai WHERE email = 'jafarpager@gmail.com') id_petugas FROM donasi d JOIN channel_payment cp USING(id_cp) JOIN anggaran_pelaksanaan_donasi apd ON(d.id_donasi = apd.id_donasi), (SELECT DISTINCT(pl.id_pelaksanaan) id_pelaksanaan, pl.total_anggaran, id_pencairan, pc.create_at FROM pelaksanaan pl LEFT JOIN anggaran_pelaksanaan_donasi a ON(a.id_pelaksanaan = pl.id_pelaksanaan), pencairan pc WHERE pc.total = pl.total_anggaran AND pc.create_at = pl.create_at) plpc WHERE plpc.id_pelaksanaan = apd.id_pelaksanaan GROUP BY cp.id_ca, plpc.id_pelaksanaan, plpc.id_pencairan ORDER BY plpc.id_pencairan;

-- Ini kemungkinan dibatalkan karena Procedure yang dipanggil terlalu komplex
-- DROP TRIGGER BeforeUpdatePinbuk;
-- DELIMITER $$
-- CREATE TRIGGER BeforeUpdatePinbuk
-- BEFORE UPDATE ON pinbuk FOR EACH ROW
-- BEGIN
--     DECLARE t_total_saldo_list_donasi BIGINT UNSIGNED;
--     DECLARE t_last_id_pinbuk_list INT UNSIGNED;

--     IF UPPER(NEW.status = 'CL') AND UPPER(OLD.status) = 'OK' THEN
--         -- Get last id_pinbuk from each id_donasi in this pinbuk
--         SELECT DISTINCT(id_pinbuk) FROM detil_pinbuk WHERE id_donasi IN (SELECT id_donasi FROM detil_pinbuk WHERE id_pinbuk = OLD.id_pinbuk) ORDER BY 1 DESC LIMIT 1 INTO t_last_id_pinbuk_list;
--         IF OLD.id_pinbuk != t_last_id_pinbuk_list THEN
--             SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'Cannot revoke this pinbuk';
--         ELSE
--             SELECT SUM(saldo) FROM virtual_ca_donasi WHERE id_donasi IN (SELECT id_donasi FROM detil_pinbuk WHERE id_pinbuk = OLD.id_pinbuk) INTO t_total_saldo_list_donasi;
--             IF t_total_saldo_list_donasi >= OLD.total_pinbuk THEN
--                 CALL RevokePinbuk(OLD.id_pinbuk);
--             ELSE
--                 SIGNAL SQLSTATE '45003' SET MESSAGE_TEXT = 'Failed to cencel pinbuk not equal balance pinbuk';
--             END IF;
--         END IF;
--     END IF;
-- END$$
-- DELIMITER ;

-- Ini nanti dibatalkan karena Procedure yang dipanggil terlalu komplex, langsung saja pangil proceduenya dan perbaiki
-- DROP TRIGGER AfterUpdatePinbuk;
-- DELIMITER $$
-- CREATE TRIGGER AfterUpdatePinbuk
-- AFTER UPDATE ON pinbuk FOR EACH ROW
-- BEGIN
--     IF UPPER(NEW.status) = 'OK' AND UPPER(OLD.status) = 'WTV' OR UPPER(NEW.status) = 'OK' AND UPPER(OLD.status) = 'CL' THEN
--         CALL PinbukByUpdate(NEW.id_pinbuk, NEW.id_bantuan);
--     END IF;
-- END$$
-- DELIMITER ;


-- DELIMITER $$
-- CREATE TRIGGER CHACK_PELAKSANAAN_DELETE
-- BEFORE DELETE ON pelaksanaan FOR EACH ROW
-- BEGIN
--     SELECT COUNT(dp.id_donasi) FROM anggaran_pelaksanaan_donasi apd JOIN donasi d ON(d.id_donasi = apd.id_donasi) JOIN detil_pinbuk dp ON(dp.id_donasi = d.id_donasi)
--     WHERE apd.id_pelaksanaan = OLD.id_pelaksanaan
-- END$$
-- DELIMITER ;