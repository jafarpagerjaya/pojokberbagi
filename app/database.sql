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
    CONSTRAINT NU_JENIS_KELAMIN UNIQUE(jenis_kelamin),
    CONSTRAINT NU_EMAIL_PEGAWAI UNIQUE(email),
    CONSTRAINT U_KONTAK_PEGAWAI UNIQUE(kontak),
    CONSTRAINT F_ID_JABATAN_PEGAWAI_ODN FOREIGN KEY(id_jabatan) REFERENCES jabatan(id_jabatan) ON DELETE SET NULL ON UPDATE CASCADE
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

CREATE TRIGGER GEMBOK_CHECK_INSERT 
BEFORE INSERT ON gambar
FOR EACH ROW
  SET NEW.gembok = IF(NEW.gembok = 1, NEW.gembok, NULL);

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
('single-wonder-mom-wide','/uploads/images/bantuan/wide/single-wonder-mom-wide.png','bantuan', NULL);


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

INSERT INTO akun(username,password,email,aktivasi,salt,hak_akses) VALUES('Pojok Berbagi', '595bfd08e98ea60c77d9949233761d0b', 'pojokberbagi.id@gmail.com', '1', '214fdcc52049c81fe814d92778168771', 'A');

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

CREATE TRIGGER ADMIN_CHECK_INSERT 
BEFORE INSERT ON admin
FOR EACH ROW
  SET NEW.level = IF(NEW.level = 'S', UPPER(NEW.level), 'N');

CREATE TRIGGER ADMIN_CHECK_UPDATE
BEFORE UPDATE ON admin
FOR EACH ROW
  SET NEW.level = IF(NEW.level = 'S', UPPER(NEW.level), 'N');

INSERT INTO admin(level,id_pegawai,id_akun) VALUES('S', 1, 1),
('N', 2, 2);

-- SELECT AUTO_INCREMENT FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'tes' AND TABLE_NAME = 'admin';

CREATE TABLE donatur (
    id_donatur INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nama VARCHAR(30) NOT NULL,
    email VARCHAR(96) NOT NULL,
    kontak VARCHAR(13),
    samaran VARCHAR(30),
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_akun INT UNSIGNED,
    CONSTRAINT NU_EMAIL_DONATUR UNIQUE(email),
    CONSTRAINT U_KONTAK_DONATUR UNIQUE(kontak),
    CONSTRAINT U_ID_AKUN_DONATUR UNIQUE(id_akun),
    CONSTRAINT F_ID_AKUN_DONATUR_ODR FOREIGN KEY(id_akun) REFERENCES akun(id_akun) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=INNODB;

INSERT INTO donatur(nama,email,id_akun) VALUES('Bank BJB','csr@bjb.co.id',null),('SYSROOT','pojokberbagi.id@gmail.com',1);

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
--     total_rab INT UNSIGNED DEFAULT NULL,
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
    nama_penerima VARCHAR(50),
    satuan_target VARCHAR(15),
    jumlah_target INT UNSIGNED,
    min_donasi INT UNSIGNED,
    total_rab INT UNSIGNED DEFAULT NULL,
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

-- status tabel bantuan B = Belum Disetujui, T = Tidak Disetujui, D = Disetujui, S = Proyek Bantuan sudah selesai
-- satuan_target = menyatakan sasaran dari jumlah target. contoh paket sembako

INSERT INTO bantuan(nama,status,satuan_target,jumlah_target,tanggal_awal,deskripsi,id_kategori,id_sektor) VALUES
('Satu Juta Sembako','D','Paket Sembako',1000000,'2021-08-01','Berbagi Sembako Bagi Masyarakat Terdampak COVID-19', '3', 'S'),
('Geberr','D',NULL,NULL,NULL,"Gerakan Berbagi Nasi Untuk Orang Lapar di Hari Jum'at Berkah", '3', 'S'),
('Peduli Semeru','D',NULL,NULL,NULL,'Bantuan Bencana Bagi Masyarakat Terdampak Letusan Gunung Semeru', '5', 'B'),
('Peduli Razka','D',NULL,NULL,NULL,'Bantuan Untuk Razka Pendrita Miningitis Berusia 6thn', '3', 'K');

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
    nama VARCHAR(20) NOT NULL,
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

INSERT INTO channel_payment(nama, kode, nomor, jenis, atas_nama) VALUES('Bank BJB', '110', '0001000080001', 'TB','POJOK BERBAGI INDONESIA'),('Bank BJB Giro Payment', '110', '0001000080001', 'GI','POJOK BERBAGI INDONESIA'),('Tunai Via CR', '1', '1', 'TN','CR POJOK BERBAGI KANTOR PUSAT'),('Bank BSI','451','7400525255','TB','POJOK BERBAGI INDONESIA'),('Bank BRI','002','107001000272300','TB','POJOK BERBAGI INDONESIA');

CREATE TABLE donasi (
    id_donasi BIGINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    kode_pembayaran VARCHAR(64),
    alias VARCHAR(30),
    kontak VARCHAR(13),
    doa VARCHAR(200),
    jumlah_donasi BIGINT UNSIGNED NOT NULL DEFAULT 1000,
    bayar TINYINT NOT NULL DEFAULT 0,
    waktu_bayar TIMESTAMP,
    notifikasi CHAR(1) DEFAULT '1',
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

-- status donasi 0 = pembayaran belum dilakukan, 1 = pembayaran berhasil;

-- INSERT INTO donasi(id_bantuan,id_donatur,alias,jumlah_donasi, id_pelaksanaan, bayar, id_cp) VALUES(1,1,'CSR BJB',2312500000,1,'1'),(1,1,'CSR BJB',3125000000,2,'1','1');
INSERT INTO donasi(id_bantuan,id_donatur,alias,jumlah_donasi, bayar, id_cp, waktu_bayar) VALUES(1,1,'CSR BJB',2312500000,'1',1,'2021-08-11'),(1,1,'CSR BJB',3125000000,'1',1, '2021-10-10'),(2,2,"PROGRAM",750000,'1',2,'2021-10-16');

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
('RAB GEBERR');
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
-- ('female-avatar','/assets/images/female-avatar.jpg','avatar','1'),
-- ('male-avatar','/assets/images/male-avatar.jpg','avatar','1'),
-- ('bank-bjb','/assets/images/partners/bjb.png','partner','1'),
-- ('bank-bsi','/assets/images/partners/bsi.png','partner','1'),
-- ('bank-bri','/assets/images/partners/bri.png','partner','1'),
-- ('satu-juta-sembako','/uploads/images/bantuan/medium/satu-juta-sembako-medium.jpg','bantuan', NULL),
-- ('satu-juta-sembako-wide','/uploads/images/bantuan/wide/satu-juta-sembako-wide.png','bantuan', NULL),
-- ('geberr','/uploads/images/bantuan/medium/geberr-medium.png','bantuan', NULL),
-- ('geberr-wide','/uploads/images/bantuan/wide/geberr-wide.png','bantuan', NULL),
-- ('semeru','/uploads/images/bantuan/medium/semeru-medium.png','bantuan', NULL),
-- ('semeru-wide','/uploads/images/bantuan/wide/semeru-wide.png','bantuan', NULL),
-- ('razka','/uploads/images/bantuan/medium/razka-medium.jpg','bantuan', NULL),
-- ('razka-wide','/uploads/images/bantuan/wide/razka-wide.png','bantuan', NULL),
-- ('single-wonder-mom','/uploads/images/bantuan/medium/single-wonder-mom-medium.jpeg','bantuan', NULL),
-- ('single-wonder-mom-wide','/uploads/images/bantuan/wide/single-wonder-mom-wide.png','bantuan', NULL);
--

-- update channel_payment set id_gambar = (SELECT id_gambar FROM gambar WHERE LOWER(nama) = "bank-bjb") WHERE nama LIKE '%Bank BJB%';
-- update channel_payment set id_gambar = (SELECT id_gambar FROM gambar WHERE LOWER(nama) = "bank-bsi") WHERE nama LIKE '%Bank BSI%' AND jenis = 'TB';
-- update channel_payment set id_gambar = (SELECT id_gambar FROM gambar WHERE LOWER(nama) = "bank-bri") WHERE nama LIKE '%Bank BRI%' AND jenis = 'TB';

-- alter table bantuan ADD COLUMN action_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER modified_at;
-- update bantuan set action_at = create_at;

alter table bantuan modify column nama VARCHAR(50) NOT NULL;
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