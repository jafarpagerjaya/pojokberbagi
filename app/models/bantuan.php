<?php
class BantuanModel extends HomeModel {

    private $_halaman = array(1,10),
            $_offset = 10,
            $_status = NULL,
            $_order = 1,
            $_order_direction = 'ASC';

    public function dataBantuan() {
        $data = $this->db->query('SELECT b.id_bantuan, b.nama, b.jumlah_target, b.status, b.blokir, b.create_at,
        b.jumlah_target,
        b.satuan_target,
        IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)) jumlah_pelaksanaan,
        SUM(d.jumlah_donasi) total_donasi, 
        SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END) donasi_terlaksana,
        SUM(CASE WHEN d.id_pelaksanaan IS NULL THEN d.jumlah_donasi END) saldo_donasi,
		TRUNCATE(COALESCE(IF(b.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1))),0),0) persentase_pelaksanaan
		FROM bantuan b 
        LEFT JOIN donasi d 
        ON(b.id_bantuan = d.id_bantuan)
        LEFT JOIN pelaksanaan p
        ON(d.id_pelaksanaan = p.id_pelaksanaan)
        WHERE b.id_bantuan BETWEEN 1 AND 10
        GROUP BY b.id_bantuan ORDER BY b.id_bantuan DESC');
        if ($data->count()) {
            $this->data = $data->results();
            return $this->data;
        }
        return false;
    }

    public function dataHalaman($halaman = null) {
        if (count($halaman)) {
            $this->setHalaman($halaman);
        }
		$this->db->query("SELECT b.id_bantuan, b.nama, b.jumlah_target, b.status, b.blokir, 
        b.jumlah_target,
        b.satuan_target,
        IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)) jumlah_pelaksanaan,
        SUM(d.jumlah_donasi) total_donasi, 
        SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END) donasi_terlaksana,
        SUM(CASE WHEN d.id_pelaksanaan IS NULL THEN d.jumlah_donasi END) saldo_donasi,
        IF(b.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1))) persentase_pelaksanaan
		FROM bantuan b 
        LEFT JOIN donasi d 
        ON(b.id_bantuan = d.id_bantuan)
        LEFT JOIN pelaksanaan p
        ON(d.id_pelaksanaan = p.id_pelaksanaan)
        WHERE b.id_bantuan BETWEEN ? AND ?
        GROUP BY b.id_bantuan ORDER BY b.id_bantuan ASC LIMIT {$this->getOffset()}", array($this->getHalaman()[0], $this->getHalaman()[1]));
		if ($this->db->count()) {
			$this->data = $this->db->results();
			return $this->data;
		}
		return false;
	}

    public function dataSektor() {
        $data = $this->db->query("SELECT id_sektor, nama FROM sektor");
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return $this->data;
        }
        return false;
    }

    // Sementara
    public function dataJenis() {
        $data = $this->db->query("SELECT id_jenis, nama, layanan FROM jenis");
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return $this->data;
        }
        return false;
    }

    public function dataBantuanBerjalan() {
        $data = $this->db->query("SELECT kategori.nama, SUM(CASE WHEN bantuan.status = 'D' THEN 1 ELSE 0 END) jumlah_kategori_berjalan FROM kategori LEFT JOIN bantuan USING(id_kategori) LEFT JOIN sektor USING(id_sektor) GROUP BY kategori.nama");
        // $data = $this->db->query('SELECT k.nama, COUNT(b.id_bantuan) jumlah_kategori_berjalan FROM kategori k JOIN jenis j ON(k.id_kategori=j.id_kategori) JOIN bantuan b ON(j.id_jenis=b.id_jenis) WHERE UPPER(b.status) = "D" GROUP BY k.nama');
        if ($data->count()) {
            $this->data = $data->results();
            return $this->data;
        }
        return false;
    }

    public function dataBantuanKategori($nama_kategori, $status_bantuan = 'D') {
        $data = $this->db->query('SELECT b.id_bantuan, b.nama, b.jumlah_target, b.blokir, 
        b.jumlah_target,
        b.satuan_target,
        IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)) jumlah_pelaksanaan,
        SUM(d.jumlah_donasi) total_donasi, 
        SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END) donasi_terlaksana,
        SUM(CASE WHEN d.id_pelaksanaan IS NULL THEN d.jumlah_donasi END) saldo_donasi,
        TRUNCATE(COALESCE(IF(b.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1))),0),0) persentase_pelaksanaan
        FROM kategori k JOIN bantuan b USING(id_kategori)
        LEFT JOIN donasi d 
        ON(b.id_bantuan = d.id_bantuan)
        LEFT JOIN pelaksanaan p
        ON(d.id_pelaksanaan = p.id_pelaksanaan)
        WHERE b.status = ? AND LOWER(k.nama) = ? AND b.id_bantuan BETWEEN ? AND ?
        GROUP BY b.id_bantuan ORDER BY b.id_bantuan ASC LIMIT 10', array($status_bantuan, $nama_kategori, $this->getHalaman()[0], $this->getHalaman()[1]));
        if ($data->count()) {
            $this->data = $data->results();
            return $this->data;
        }
        return false;
    }

    public function getDetilBantuan($params) {
        $data = $this->db->query("SELECT b.*, s.nama layanan, g.path_gambar, g.nama nama_gambar, IF(b.id_bantuan = 1, COUNT(d.id_donatur)+1999, COUNT(d.id_donatur)) jumlah_donatur, 
        IF(p2.nama IS NULL, 'Pojok Berbagi Indonesia', p2.nama) pengaju_bantuan,
        IF(b.id_pemohon IS NULL, '/assets/images/brand/pojok-berbagi-transparent.png', (SELECT path_gambar FROM gambar WHERE id_gambar = p2.id_gambar)) path_gambar_logo_pengaju_bantuan,
        COALESCE(b.jumlah_target,'Unlimited') jumlah_target,
        IF(TIMESTAMPDIFF(DAY,b.tanggal_akhir, NOW()) IS NULL, 'Unlimited', TIMESTAMPDIFF(DAY,b.tanggal_akhir, NOW())) sisa_waktu,
        IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)) jumlah_pelaksanaan,
        IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)) total_donasi, 
        IF(SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END) IS NULL, 0, SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)) donasi_terlaksana,
        IF(SUM(CASE WHEN d.id_pelaksanaan IS NULL THEN d.jumlah_donasi END) IS NULL, 0, SUM(CASE WHEN d.id_pelaksanaan IS NULL THEN d.jumlah_donasi END)) saldo_donasi,
        TRUNCATE(COALESCE(IF(b.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1))),0),0) persentase_pelaksanaan
        FROM pelaksanaan p RIGHT JOIN donasi d USING(id_pelaksanaan) 
        JOIN bantuan b USING(id_bantuan)
        LEFT JOIN sektor s USING(id_sektor)
        JOIN gambar g USING(id_gambar)
        LEFT JOIN pemohon p2 USING(id_pemohon)
        WHERE b.id_bantuan = ? AND d.bayar = 1", array($params));
        if ($this->db->count()) {
            $this->data =  $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function dataDonasiDonaturBantuan($id_bantuan, $halaman = null) {
        if (count($halaman)) {
            $this->setHalaman($halaman);
        }
		if (!$this->db->query('SELECT id_donasi, d.id_donatur, nama, jumlah_donasi donasi, d2.kontak, waktu_donasi 
        FROM donasi d JOIN donatur d2 USING(id_donatur) 
        WHERE bayar = ? AND id_bantuan = ? AND id_donasi BETWEEN ? AND ? 
        ORDER BY id_donasi ASC LIMIT 10', array($this->getStatus(), $id_bantuan, $this->getHalaman()[0], $this->getHalaman()[1]))) {
            throw new Exception("Error Processing Read Data Donasi ID Bantuan " . $id_bantuan);
        }
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return $this->data;
        }
    }
    
    public function setOffset($offset) {
        $this->_offset = $offset;
    }

    public function getOffset() {
        return $this->_offset;
    }

    public function setHalaman($params) {   
        $param1 = (($params-1) * $this->getOffset()) + 1;
        if ($param1 < 0) {
            $param1 = 0;
        }
        $param2 = $params * $this->getOffset();
        $this->_halaman = array($param1, $param2);
    }

    public function getHalaman() {
        return $this->_halaman;
    }

    public function setStatus($status) {
        $this->_status = $status;
    }

    public function getStatus() {
        return $this->_status;
    }

    public static function jenisLayanan($layanan) {
        if ($layanan == 'S') {
            $layanan = 'Sosial';
        } elseif ($layanan == 'E') {
            $layanan = 'Ekonomi';
        } elseif ($layanan == 'D') {
            $layanan = 'Bencana';
        } elseif ($layanan == 'K') {
            $layanan = 'Kesehatan';
        } elseif ($layanan == 'P') {
            $layanan = 'Pendidikan';
        } else {
            $laayanan = 'Tidak Terdefinisikan';
        }
        return $layanan;
    }

    public static function iconLayanan($layanan) {
        if ($layanan == 'S') {
            $icon = '<i class="lni lni-heart"></i>';
        } elseif ($layanan == 'E') {
            $icon = '<i class="lni lni-bar-chart"></i>';
        } elseif ($layanan == 'D') {
            $icon = '<i class="lni lni-warning"></i>';
        } elseif ($layanan == 'K') {
            $icon = '<i class="lni lni-sthethoscope"></i>';
        } elseif ($layanan == 'P') {
            $icon = '<i class="lni lni-graduation"></i>';
        } elseif ($layanan == 'L') {
            $icon = '<i class="lni lni-sprout"></i>';
        } else {
            $icon = '<i class="lni lni-support"></i>';
        }
        return $icon;
    }

    public function setOrder($order) {
        $this->_order = Sanitize::toInt(Sanitize::escape($order));
    }

    public function setDirection($direction) {
        $this->_order_dorection = Sanitize::escape($direction);
    }

    public function getListBantuan() {
        $data = $this->db->query("SELECT b.id_bantuan, s.id_sektor layanan, b.nama nama_bantuan, g.path_gambar, g.nama nama_gambar, s.nama nama_sektor, k.nama nama_kategori, IF(k.warna IS NULL, '#e9ecef', k.warna) warna,
        IF(p2.nama IS NULL, 'Pojok Berbagi Indonesia', p2.nama) pengaju_bantuan,
        SUM(CASE WHEN d.bayar = 1 THEN d.jumlah_donasi ELSE 0 END) total_donasi,
        SUM(CASE WHEN d.bayar = 1 AND d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi ELSE 0 END) donasi_disalurkan,
        IF(TIMESTAMPDIFF(DAY,b.tanggal_akhir, NOW()) IS NULL, 'Unlimited', TIMESTAMPDIFF(DAY,b.tanggal_akhir, NOW())) sisa_waktu,
        IF(b.jumlah_target IS NULL,
        IF(TRUNCATE((SUM(IF(d.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1) IS NULL, 0, TRUNCATE((SUM(IF(d.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1))
        , IF(TRUNCATE((SUM(p1.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p1.jumlah_pelaksanaan)/b.jumlah_target)*100,1))) persentase_donasi_dilaksanakan
        FROM bantuan b LEFT JOIN donasi d USING(id_bantuan)
        LEFT JOIN pelaksanaan p1 USING(id_pelaksanaan)
        JOIN gambar g USING(id_gambar)
        LEFT JOIN pemohon p2 USING(id_pemohon) 
        LEFT JOIN sektor s USING(id_sektor)
        LEFT JOIN kategori k USING(id_kategori)
        WHERE b.blokir IS NULL AND b.status = 'D'
        AND b.id_bantuan BETWEEN ? AND ?
        GROUP BY b.id_bantuan
        ORDER BY {$this->_order} {$this->_order_direction} LIMIT 10", array($this->getHalaman()[0], $this->getHalaman()[1]));
        if ($data->count()) {
            $this->data = $data->results();
            return $this->data;
        }
        return false;
    }

    public function getBanner() {
        $data = $this->db->query("SELECT b.id_bantuan, b.jumlah_target, b.deskripsi, b.nama nama_bantuan, g.path_gambar, g.nama nama_gambar, k.nama nama_kategori,
        CASE WHEN b.id_sektor = 'S' THEN 'Sosial Kemanusiaan' WHEN b.id_sektor = 'P' THEN 'Pendidikan Umat' WHEN b.id_sektor = 'E' THEN 'Pemandirian Ekonomi' WHEN b.id_sektor = 'K' THEN 'Kesehatan Masyarakat' WHEN b.id_sektor = 'L' THEN 'Lingkungan Asri' WHEN b.id_sektor = 'B' THEN 'Tanggap Bencana' END layanan,
		  TIMESTAMPDIFF(DAY,b.tanggal_akhir, NOW()) sisa_waktu,
        IF(b.id_bantuan = 1, COUNT(d.id_donatur)+1999, COUNT(d.id_donatur)) jumlah_donatur,
        SUM(CASE WHEN d.bayar = 1 THEN d.jumlah_donasi ELSE 0 END) total_donasi,
        IF(b.jumlah_target IS NULL, 'Donasi (Rp)', b.satuan_target) jenis_penyaluran,
        IF(b.jumlah_target IS NULL, SUM(CASE WHEN d.bayar = 1 AND d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi ELSE 0 END), IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan))) donasi_disalurkan,
        IF(b.jumlah_target IS NULL,
          IF(TRUNCATE((SUM(IF(d.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1) IS NULL, 0, TRUNCATE((SUM(IF(d.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1))
        , IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1))) persentase_donasi_dilaksanakan
        FROM bantuan b LEFT JOIN gambar g USING(id_gambar)
        LEFT JOIN kategori k USING(id_kategori)
        LEFT JOIN donasi d USING(id_bantuan)
        LEFT JOIN pelaksanaan p USING(id_pelaksanaan)
        WHERE b.id_bantuan IN (SELECT id_bantuan FROM banner ORDER BY modified_at ASC)
        AND b.status = 'D' AND b.blokir IS NULL
        GROUP BY b.id_bantuan LIMIT 10");
        if ($data->count()) {
            $this->data = $data->results();
            return $this->data;
        }
        return false;
    }
}