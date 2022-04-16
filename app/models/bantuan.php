<?php
class BantuanModel extends HomeModel {

    private $_halaman = array(1,10),
            $_offset = 0,
            $_limit = 10,
            $_order = 1,
            $_order_direction = 'DESC',
            $_between = array(
                'start' => 1, 
                'end' => 10
            );

    private $_status = NULL;

    // Cara Offset dengan JOIN ke index
    public function setDataOffset($offset) {
        $this->_offset = Sanitize::toInt2($offset);
    }
    public function getDataOffset() {
        return $this->_offset;
    }
    public function setDataLimit($offset_limit) {
        $this->_limit = Sanitize::toInt2($offset_limit);
    }
    public function getDataLimit() {
        return $this->_limit;
    }
    public function setDataOffsetHalaman($halaman) {
        $halaman = Sanitize::toInt2($halaman);
        if ($halaman == 1) {
            $offset = 0;
        } else {
            $offset = (($halaman - 1) * $this->_limit);
        }
        $this->_offset = $offset;
    }
    public function getDataOffsetHalaman() {
        return $this->_offset;
    }
    public function newDataOffset() {
        $this->db->query("SELECT bo.*, 
        IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)) jumlah_pelaksanaan,
        SUM(d.jumlah_donasi) total_donasi, 
        SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END) donasi_terlaksana,
        SUM(CASE WHEN d.id_pelaksanaan IS NULL THEN d.jumlah_donasi END) saldo_donasi,
		TRUNCATE(COALESCE(IF(bo.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1))),0),0) persentase_pelaksanaan
		FROM bantuan bo JOIN (SELECT id_bantuan FROM bantuan ORDER BY {$this->_order} {$this->_order_direction} LIMIT {$this->_offset}, {$this->_limit}) bi ON(bo.id_bantuan = bi.id_bantuan)
        LEFT JOIN donasi d 
        ON(bo.id_bantuan = d.id_bantuan)
        LEFT JOIN pelaksanaan p
        ON(d.id_pelaksanaan = p.id_pelaksanaan)
        GROUP BY bo.id_bantuan ORDER BY {$this->_order} {$this->_order_direction}");
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return true;
        }
        return false;
        // Debug::pr($this->db);
        // die();
    }

    // Cara Seek Method dilarang ada penghapusan record
    public function setDataBetween($halaman) {
        $halaman = Sanitize::escape2($halaman);
        $start = (($halaman-1) * $this->getDataLimit()) + 1;
        if ($start < 0) {
            $start = 1;
        }
        $end = $halaman * $this->getDataLimit();
        if ($this->_order_direction == 'DESC') {
            $jumlah_record = $this->db->query("SELECT COUNT(*) jumlah_record FROM bantuan")->result()->jumlah_record + 1;
            $startD = $jumlah_record - $end;
            if ($startD <= 0) {
                $startD = 1;
            }
            $endD = $jumlah_record - $start;
            $start = $startD;
            $end = $endD;
        }
        $this->_between = array('start' => $start, 'end' => $end);
    }

    public function getDataBetween() {
        return $this->_between;
    }
    
    public function newDataSeek() {
        $this->db->query("SELECT b.id_bantuan, b.nama, b.jumlah_target, b.status, b.blokir, b.create_at,
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
        WHERE b.id_bantuan BETWEEN ? AND ?
        GROUP BY b.id_bantuan ORDER BY {$this->_order} {$this->_order_direction}", array(
            'between_start' => $this->_between['start'],
            'between_end' => $this->_between['end']
        ));
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return true;
        }
        return false;
    }

    public function dataBantuan() {
        $data = $this->db->query("SELECT b.id_bantuan, b.nama, b.jumlah_target, b.status, b.blokir, b.create_at,
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
        WHERE b.id_bantuan BETWEEN 1 AND ?
        GROUP BY b.id_bantuan ORDER BY b.id_bantuan DESC", array($this->getOffset()));
        if ($data->count()) {
            $this->data = $data->results();
            return $this->data;
        }
        return false;
    }

    // Old
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
        // Debug::pr($this->db);
        // die();
	}

    public function dataSektor() {
        $this->db->query("SELECT id_sektor, nama FROM sektor");
        $this->data = $this->db->results();
        return $this->data;
    }

    public function dataKategori() {
        $this->db->query("SELECT id_kategori, nama FROM kategori");
        $this->data = $this->db->results();
        return $this->data;
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
        $data = $this->db->query("SELECT b.*, s.nama layanan, gm.path_gambar path_gambar_medium, gm.nama nama_gambar_medium, gw.path_gambar path_gambar_wide, gw.nama nama_gambar_wide, IF(b.id_bantuan = 1, COUNT(d.id_donatur)+1999, COUNT(d.id_donatur)) jumlah_donatur, 
        IF(p2.nama IS NULL, 'Pojok Berbagi Indonesia', p2.nama) pengaju_bantuan,
        IF(b.id_pemohon IS NULL, '/assets/images/brand/pojok-berbagi-transparent.png', (SELECT path_gambar FROM gambar WHERE id_gambar = p2.id_gambar)) path_gambar_logo_pengaju_bantuan,
        COALESCE(b.jumlah_target,'Unlimited') jumlah_target,
        IF(b.jumlah_target IS NULL, 'Donasi', b.satuan_target) jenis_penyaluran,
        IF(TIMESTAMPDIFF(DAY,b.tanggal_akhir, NOW()) IS NULL, 'Unlimited', TIMESTAMPDIFF(DAY,b.tanggal_akhir, NOW())) sisa_waktu,
        IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)) jumlah_pelaksanaan,
        IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)) total_donasi, 
        IF(SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END) IS NULL, 0, SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)) donasi_terlaksana,
        COUNT(p.id_pelaksanaan) jumlah_pelaksanaan,
        IF(SUM(CASE WHEN d.id_pelaksanaan IS NULL THEN d.jumlah_donasi END) IS NULL, 0, SUM(CASE WHEN d.id_pelaksanaan IS NULL THEN d.jumlah_donasi END)) saldo_donasi,
        TRUNCATE(COALESCE(IF(b.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1))),0),0) persentase_pelaksanaan
        FROM pelaksanaan p RIGHT JOIN donasi d USING(id_pelaksanaan) 
        JOIN bantuan b USING(id_bantuan)
        LEFT JOIN sektor s USING(id_sektor)
        LEFT JOIN gambar gm ON (b.id_gambar_medium = gm.id_gambar)
        LEFT JOIN gambar gw ON (b.id_gambar_wide = gw.id_gambar)
        LEFT JOIN pemohon p2 USING(id_pemohon)
        WHERE b.id_bantuan = ? AND d.bayar = 1", array($params));
        if ($this->db->count()) {
            $this->data =  $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function setFilter($search) {
        $this->_search = Sanitize::escape2($search);
    }

    public function getFilter() {
        return $this->_search;
    }

    public function dataDonasiDonaturBantuan($id_bantuan) {
        $sql_index = "SELECT id_donasi FROM donasi WHERE bayar = ? AND id_bantuan = ? ORDER BY waktu_bayar {$this->_order_direction} LIMIT {$this->_offset}, {$this->_limit}";

        if (isset($this->_search)) {
            $column_filter = "CONCAT(d.id_donasi, IFNULL(d.id_donatur,''), d.jumlah_donasi, d.waktu_bayar, IFNULL(d2.nama, ''), IFNULL(d2.email, ''), IFNULL(d2.kontak,''), IFNULL(cp.jenis,''), IFNULL(gcp.nama,''))";
            $this->splits = $this->split($column_filter, ',');
            foreach ($this->splits as $splits_key => $splits_value) {
                if (strpos($splits_value, '(') !== false) {
                    $alias = $this->split($splits_value, ' ');
                    $splits_value = $alias[0];
                    $this->splits[$splits_key] = $splits_value;
                }
            }
            $this->splits = implode(', ', $this->splits);
            $sql_index = "SELECT d.id_donasi FROM donasi d LEFT JOIN channel_payment cp USING(id_cp) LEFT JOIN donatur d2 USING(id_donatur) LEFT JOIN gambar gcp ON(gcp.id_gambar = cp.id_gambar) WHERE d.bayar = ? AND d.id_bantuan = ? AND CONCAT({$this->splits}) LIKE '%{$this->_search}%' ORDER BY waktu_bayar {$this->_order_direction} LIMIT {$this->_offset}, {$this->_limit}";
        }

        $sql = "SELECT d.id_donasi, d.id_donatur, d.jumlah_donasi, IFNULL(ga.path_gambar, '/assets/images/default.png') path_gambar_akun, IFNULL(ga.nama,'default') nama_path_gambar_akun, d2.nama nama_donatur, d2.email, d2.kontak, d.waktu_bayar, cp.id_cp, cp.jenis, IFNULL(gcp.path_gambar, '/assets/images/brand/favicon-pojok-icon.ico') path_gambar_cp, IFNULL(gcp.nama, 'Kantor Pojok') nama_path_gambar_cp
        FROM donasi d JOIN ({$sql_index}) di ON (di.id_donasi = d.id_donasi)
        LEFT JOIN channel_payment cp USING(id_cp)
        LEFT JOIN donatur d2 USING(id_donatur) 
        LEFT JOIN gambar gcp ON(gcp.id_gambar = cp.id_gambar)
        LEFT JOIN akun a USING(id_akun)
        LEFT JOIN gambar ga ON(ga.id_gambar = a.id_gambar)
        ORDER BY d.waktu_bayar {$this->_order_direction} LIMIT {$this->_limit}";
        
		if (!$this->db->query($sql, array(
            'bayar' => $this->getStatus(),
            'id_bantuan' => Sanitize::escape2($id_bantuan)
        ))) {
            throw new Exception("Error Processing Read Data Donasi ID Bantuan " . $id_bantuan);
        }
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return $this->data;
        }
    }

    public function countRecordDataDonasiDonaturBantuan($id_bantuan) {
        $sql = "SELECT count(id_bantuan) jumlah_record FROM donasi WHERE bayar = ? AND id_bantuan = ?";
        if (isset($this->_search)) {
            $sql = "SELECT count(d.id_bantuan) jumlah_record FROM donasi d LEFT JOIN channel_payment cp USING(id_cp) LEFT JOIN donatur d2 USING(id_donatur) LEFT JOIN gambar gcp ON(gcp.id_gambar = cp.id_gambar) WHERE d.bayar = ? AND d.id_bantuan = ? AND CONCAT({$this->splits}) LIKE '%{$this->_search}%'";
        }
        $this->db->query($sql, array(
            'bayar' => Sanitize::escape2($this->getStatus()),
            'id_bantuan' => Sanitize::escape2($id_bantuan)
        ));
        if ($this->db->count()) {
            $this->data = $this->db->result()->jumlah_record;
            return $this->data;
        }
    }

    public function countDonasiBantuan($id_bantuan) {
        $this->db->query("SELECT count(id_bantuan) jumlah_record FROM donasi WHERE bayar = ? AND id_bantuan = ?", array(
            'bayar' => Sanitize::escape2($this->getStatus()),
            'id_bantuan' => Sanitize::escape2($id_bantuan)
        ));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
    }
    
    public function setOffset($offset) {
        $this->_offset = Sanitize::escape2($offset);
    }

    public function getOffset() {
        return $this->_offset;
    }

    public function setHalaman($halaman) {
        $halaman = Sanitize::escape2($halaman);
        $param1 = (($halaman-1) * $this->getOffset()) + 1;
        if ($param1 < 0) {
            $param1 = 0;
        }
        $param2 = $halaman * $this->getOffset();
        $this->_halaman = array($param1, $param2);
    }

    public function getHalaman() {
        return $this->_halaman;
    }

    public function setStatus($status) {
        $this->_status = Sanitize::escape2($status);
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

    public static function iconLayanan($id_sector) {
        if ($id_sector == 'S') {
            $icon = '<i class="lni lni-heart"></i>';
        } elseif ($id_sector == 'E') {
            $icon = '<i class="lni lni-bar-chart"></i>';
        } elseif ($id_sector == 'B') {
            $icon = '<i class="lni lni-warning"></i>';
        } elseif ($id_sector == 'K') {
            $icon = '<i class="lni lni-sthethoscope"></i>';
        } elseif ($id_sector == 'P') {
            $icon = '<i class="lni lni-graduation"></i>';
        } elseif ($id_sector == 'L') {
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
        $this->_order_direction = Sanitize::escape($direction);
    }

    public function getDirection() {
        return $this->_order_direction;
    }

    public function getListBantuan() {
        $data = $this->db->query("SELECT b.id_bantuan, s.id_sektor layanan, b.nama nama_bantuan, gm.path_gambar path_gambar_medium, gm.nama nama_gambar_medium, gw.path_gambar path_gambar_wide, gw.nama nama_gambar_wide, s.nama nama_sektor, k.nama nama_kategori, IF(k.warna IS NULL, '#e9ecef', k.warna) warna,
        IF(p2.id_gambar IS NULL, '/assets/images/brand/pojok-berbagi-transparent.png', gp2.path_gambar) path_gambar_pengaju,
        IF(p2.nama IS NULL, 'Pojok Berbagi Indonesia', p2.nama) pengaju_bantuan,
        SUM(CASE WHEN d.bayar = 1 THEN d.jumlah_donasi ELSE 0 END) total_donasi,
        SUM(CASE WHEN d.bayar = 1 AND d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi ELSE 0 END) donasi_disalurkan,
        IF(TIMESTAMPDIFF(DAY,b.tanggal_akhir, NOW()) IS NULL, 'Unlimited', TIMESTAMPDIFF(DAY,b.tanggal_akhir, NOW())) sisa_waktu,
        IF(b.jumlah_target IS NULL,
        IF(TRUNCATE((SUM(IF(d.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1) IS NULL, 0, TRUNCATE((SUM(IF(d.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1))
        , IF(TRUNCATE((SUM(p1.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p1.jumlah_pelaksanaan)/b.jumlah_target)*100,1))) persentase_donasi_dilaksanakan
        FROM bantuan b LEFT JOIN donasi d USING(id_bantuan)
        LEFT JOIN pelaksanaan p1 USING(id_pelaksanaan)
        LEFT JOIN gambar gm ON (b.id_gambar_medium = gm.id_gambar)
        LEFT JOIN gambar gw ON (b.id_gambar_wide = gw.id_gambar)
        LEFT JOIN pemohon p2 USING(id_pemohon)
        LEFT JOIN gambar gp2 ON (gp2.id_gambar = p2.id_gambar)
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

    public function getListBantuanKategori($nama_kategori = null) {
        $dataParams = array();
        $arrayLSQL = array("SELECT b.id_bantuan, s.id_sektor layanan, b.nama nama_bantuan, gm.path_gambar path_gambar_medium, gm.nama nama_gambar_medium, gw.path_gambar path_gambar_wide, gw.nama nama_gambar_wide, s.nama nama_sektor, k.nama nama_kategori, IF(k.warna IS NULL, '#e9ecef', k.warna) warna,
                            IF(p2.nama IS NULL, '/assets/images/brand/pojok-berbagi-transparent.png', g2.path_gambar) path_gambar_pengaju,
                            IF(p2.nama IS NULL, 'Pojok Berbagi Indonesia', p2.nama) pengaju_bantuan,
                            SUM(CASE WHEN d.bayar = 1 THEN d.jumlah_donasi ELSE 0 END) total_donasi,
                            SUM(CASE WHEN d.bayar = 1 AND d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi ELSE 0 END) donasi_disalurkan,
                            IF(TIMESTAMPDIFF(DAY,b.tanggal_akhir, NOW()) IS NULL, 'Unlimited', TIMESTAMPDIFF(DAY,b.tanggal_akhir, NOW())) sisa_waktu,
                            IF(b.jumlah_target IS NULL,
                            IF(TRUNCATE((SUM(IF(d.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1) IS NULL, 0, TRUNCATE((SUM(IF(d.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1))
                            , IF(TRUNCATE((SUM(p1.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p1.jumlah_pelaksanaan)/b.jumlah_target)*100,1))) persentase_donasi_dilaksanakan
                            FROM bantuan b LEFT JOIN donasi d USING(id_bantuan)
                            LEFT JOIN pelaksanaan p1 USING(id_pelaksanaan)
                            LEFT JOIN gambar gm ON (b.id_gambar_medium = gm.id_gambar)
                            LEFT JOIN gambar gw ON (b.id_gambar_wide = gw.id_gambar)
                            LEFT JOIN pemohon p2 USING(id_pemohon)
                            LEFT JOIN gambar g2 ON (g2.id_gambar = p2.id_gambar)
                            LEFT JOIN sektor s USING(id_sektor)
                            LEFT JOIN kategori k USING(id_kategori)
                            WHERE b.blokir IS NULL");
        
        if (isset($this->_status)) {
            array_push($arrayLSQL, "AND b.status = ?");
            array_push($dataParams, $this->_status);
        }

        if (!is_null($nama_kategori)) {
            array_push($arrayLSQL, "AND LOWER(k.nama) = LOWER(?)");
            array_push($dataParams, $nama_kategori);
        }

        if (isset($this->_halaman)) {
            array_push($arrayLSQL, "AND b.id_bantuan BETWEEN ? AND ? GROUP BY b.id_bantuan ORDER BY {$this->_order} {$this->_order_direction} LIMIT 10");
            array_push($dataParams, $this->getHalaman()[0], $this->getHalaman()[1]);
        }

        $sql = implode(' ', $arrayLSQL);

        $this->db->query($sql, $dataParams);
        if (!$this->db->count()) {
            return false;
        }
        $this->data = $this->db->results();
        return $this->data;
    }

    public function getBanner() {
        $data = $this->db->query("SELECT b.id_bantuan, b.jumlah_target, b.deskripsi, b.nama nama_bantuan, gm.path_gambar path_gambar_medium, gm.nama nama_gambar_medium, gw.path_gambar path_gambar_wide, gw.nama nama_gambar_wide, k.nama nama_kategori,
        CASE WHEN b.id_sektor = 'S' THEN 'Sosial Kemanusiaan' WHEN b.id_sektor = 'P' THEN 'Pendidikan Umat' WHEN b.id_sektor = 'E' THEN 'Pemandirian Ekonomi' WHEN b.id_sektor = 'K' THEN 'Kesehatan Masyarakat' WHEN b.id_sektor = 'L' THEN 'Lingkungan Asri' WHEN b.id_sektor = 'B' THEN 'Tanggap Bencana' END layanan,
		  TIMESTAMPDIFF(DAY,b.tanggal_akhir, NOW()) sisa_waktu,
        IF(b.id_bantuan = 1, COUNT(d.id_donatur)+1999, COUNT(d.id_donatur)) jumlah_donatur,
        SUM(CASE WHEN d.bayar = 1 THEN d.jumlah_donasi ELSE 0 END) total_donasi,
        IF(b.jumlah_target IS NULL, 'Donasi', b.satuan_target) jenis_penyaluran,
        IF(b.jumlah_target IS NULL, SUM(CASE WHEN d.bayar = 1 AND d.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi ELSE 0 END), IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan))) donasi_disalurkan,
        IF(b.jumlah_target IS NULL,
          IF(TRUNCATE((SUM(IF(d.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1) IS NULL, 0, TRUNCATE((SUM(IF(d.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1))
        , IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1))) persentase_donasi_dilaksanakan
        FROM bantuan b LEFT JOIN gambar gm ON (b.id_gambar_medium = gm.id_gambar)
        LEFT JOIN gambar gw ON (b.id_gambar_wide = gw.id_gambar)
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
    
    public function getResumeKategoriBantuan($nama_kategori) {
        $this->db->query("SELECT COUNT(id_bantuan) jumlah_bantuan_dibuka,
        IFNULL(SUM(IF(bantuan.status = 'D', 1, 0)), 0) jumlah_bantuan_berjalan,
        IFNULL(SUM(IF(bantuan.status = 'S', 1, 0)), 0) jumlah_bantuan_selesai,
        kategori.nama nama_kategori,
        kategori.warna
        FROM bantuan RIGHT JOIN kategori USING(id_kategori) 
        WHERE LOWER(kategori.nama) = LOWER(?)
        GROUP BY kategori.id_kategori", array('kategori.nama' => $nama_kategori));
        if (!$this->db->count()) {
            // nama_kategori unrecognize
            return false;
        }
        $this->data = $this->db->result();
        return $this->data;
    }

    public function getSaldoBantuan($id_bantuan) {
        $this->db->query("SELECT 
        IFNULL(SUM(IF(LOWER(cp.nama) = 'bank bjb', d.jumlah_donasi, 0)), 0) saldo_bjb, 
        IFNULL(SUM(IF(LOWER(cp.nama) = 'bank bsi', d.jumlah_donasi, 0)), 0) saldo_bsi,
        IFNULL(SUM(IF(LOWER(cp.nama) = 'bank bri', d.jumlah_donasi, 0)), 0) saldo_bri,
        (SELECT path_gambar FROM gambar JOIN channel_payment cptb USING(id_gambar) WHERE LOWER(cptb.nama) = 'bank bjb' AND cptb.jenis = 'TB') path_gambar_bjb,
        (SELECT path_gambar FROM gambar JOIN channel_payment cptb USING(id_gambar) WHERE LOWER(cptb.nama) = 'bank bsi' AND cptb.jenis = 'TB') path_gambar_bsi,
        (SELECT path_gambar FROM gambar JOIN channel_payment cptb USING(id_gambar) WHERE LOWER(cptb.nama) = 'bank bri' AND cptb.jenis = 'TB') path_gambar_bri
        FROM donasi d LEFT JOIN channel_payment cp ON(d.id_cp = cp.id_cp)
        WHERE d.bayar = 1 AND d.id_bantuan = ? AND d.id_pelaksanaan IS NULL", array('d.id_bantuan' => Sanitize::escape2($id_bantuan)));
        if (!$this->db->count()) {
            return false;
        }
        $this->data = $this->db->result();
        return $this->data;
    }
}