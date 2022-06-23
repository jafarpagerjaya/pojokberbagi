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
        SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END) donasi_terlaksana,
        SUM(CASE WHEN apd.id_pelaksanaan IS NULL THEN d.jumlah_donasi END) saldo_donasi,
		TRUNCATE(COALESCE(IF(bo.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1))),0),0) persentase_pelaksanaan
		FROM bantuan bo JOIN (SELECT id_bantuan FROM bantuan ORDER BY {$this->_order} {$this->_order_direction} LIMIT {$this->_offset}, {$this->_limit}) bi ON(bo.id_bantuan = bi.id_bantuan)
        LEFT JOIN donasi d 
        ON(bo.id_bantuan = d.id_bantuan)
        LEFT JOIN anggaran_pelaksanaan_donasi apd ON (apd.id_donasi = d.id_donasi)
        LEFT JOIN pelaksanaan p
        ON(apd.id_pelaksanaan = p.id_pelaksanaan)
        GROUP BY bo.id_bantuan ORDER BY {$this->_order} {$this->_order_direction}");
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return true;
        }
        return false;
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
        $this->db->query("SELECT b.id_bantuan, b.nama, IFNULL(formatTanggalFull(b.create_at),'') create_bantuan_at, b.nama_penerima, b.status, b.blokir, IFNULL(FORMAT(b.jumlah_target,0,'id_ID'),'Tanpa batas') jumlah_target, b.satuan_target,
        s.nama nama_sektor,
        k.warna, k.nama nama_kategori,
        COUNT(p.id_pelaksanaan) sekian_kali_pencairan,
        IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)) jumlah_pelaksanaan,
        FORMAT(SUM(CASE WHEN d.bayar = 1 THEN d.jumlah_donasi ELSE 0 END),0,'id_ID') total_donasi, 
        FORMAT(COUNT(DISTINCT(d.id_donatur)),0,'id_ID') total_donatur,
        FORMAT(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END),0,'id_ID') donasi_terlaksana,
        IFNULL(FORMAT(SUM(CASE WHEN apd.id_pelaksanaan IS NULL THEN d.jumlah_donasi END),0,'id_ID'),0) saldo_donasi,
		TRUNCATE(COALESCE(IF(b.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1))),0),0) persentase_pelaksanaan
		FROM bantuan b 
        LEFT JOIN donasi d 
        ON(b.id_bantuan = d.id_bantuan)
        LEFT JOIN anggaran_pelaksanaan_donasi apd ON (apd.id_donasi = d.id_donasi)
        LEFT JOIN pelaksanaan p
        ON(apd.id_pelaksanaan = p.id_pelaksanaan)
        LEFT JOIN sektor s USING(id_sektor)
        LEFT JOIN kategori k USING(id_kategori)
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
        SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END) donasi_terlaksana,
        SUM(CASE WHEN apd.id_pelaksanaan IS NULL THEN d.jumlah_donasi END) saldo_donasi,
		TRUNCATE(COALESCE(IF(b.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1))),0),0) persentase_pelaksanaan
		FROM bantuan b 
        LEFT JOIN donasi d 
        ON(b.id_bantuan = d.id_bantuan)
        LEFT JOIN anggaran_pelaksanaan_donasi apd ON (apd.id_donasi = d.id_donasi)
        LEFT JOIN pelaksanaan p
        ON(apd.id_pelaksanaan = p.id_pelaksanaan)
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
        if (count(is_countable($halaman) ? $halaman : [])) {
            $this->setHalaman($halaman, 'bantuan');
        }
		$this->db->query("SELECT b.id_bantuan, b.nama, b.jumlah_target, b.status, b.blokir, 
        b.jumlah_target,
        b.satuan_target,
        IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)) jumlah_pelaksanaan,
        SUM(d.jumlah_donasi) total_donasi, 
        SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END) donasi_terlaksana,
        SUM(CASE WHEN apd.id_pelaksanaan IS NULL THEN d.jumlah_donasi END) saldo_donasi,
        IF(b.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1))) persentase_pelaksanaan
		FROM bantuan b 
        LEFT JOIN donasi d 
        ON(b.id_bantuan = d.id_bantuan)
        LEFT JOIN pelaksanaan p
        ON(apd.id_pelaksanaan = p.id_pelaksanaan)
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

    // Old ENd

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

    public function getJumlahDataBantuan($status = null) {
        $params = array();
        if (!is_null($status)) {
            $sql = "SELECT kategori.nama, SUM(CASE WHEN bantuan.status = ? THEN 1 ELSE 0 END) jumlah_kategori FROM kategori LEFT JOIN bantuan USING(id_kategori) LEFT JOIN sektor USING(id_sektor) GROUP BY kategori.nama";
            $params = array(
                'bantuan.status' => Sanitize::escape2($status)
            );
        } else {
            $sql = "SELECT k.nama, IFNULL(COUNT(b.id_kategori),0) jumlah_kategori FROM kategori k LEFT JOIN bantuan b ON(k.id_kategori = b.id_kategori) GROUP BY k.id_kategori";
        }
        $data = $this->db->query($sql, $params);
        // $data = $this->db->query('SELECT k.nama, COUNT(b.id_bantuan) jumlah_kategori_berjalan FROM kategori k JOIN jenis j ON(k.id_kategori=j.id_kategori) JOIN bantuan b ON(j.id_jenis=b.id_jenis) WHERE UPPER(b.status) = "D" GROUP BY k.nama');
        if ($data->count()) {
            $this->data = $data->results();
            return $this->data;
        }
        return false;
    }

    public function readDataBantuanKategori($kategori = null) {
        $filter = array();
        $column = "bo.id_bantuan, bo.nama nama_bantuan, IFNULL(FORMAT(bo.jumlah_target, 0, 'id_ID'),'Tanpa batas') jumlah_target, bo.blokir, bo.satuan_target, formatTanggal(bo.create_at) create_bantuan_at, bo.status status_bantuan, k.nama nama_kategori, k.warna, s.nama nama_sektor, IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)) jumlah_pelaksanaan, TRUNCATE(COALESCE(IF(bo.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1))),0),0) persentase_pelaksanaan, IFNULL(FORMAT(SUM(d.jumlah_donasi),0,'id_ID'),0) jumlah_donasi, IFNULL(FORMAT(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END),0,'id_ID'),0) donasi_terlaksana, IFNULL(FORMAT(SUM(CASE WHEN apd.id_pelaksanaan IS NULL THEN d.jumlah_donasi END),0,'id_ID'),0) saldo_donasi, IFNULL(COUNT(d.id_donatur),0) jumlah_donatur";
        $tables = "JOIN kategori k USING(id_kategori) LEFT JOIN sektor s USING(id_sektor) LEFT JOIN donasi d ON(d.id_bantuan = bo.id_bantuan) LEFT JOIN anggaran_pelaksanaan_donasi apd ON (d.id_donasi = apd.id_donasi) LEFT JOIN pelaksanaan p USING(id_pelaksanaan)";
        $search = "CONCAT( bo.id_bantuan, IFNULL(bo.nama,''), IFNULL(bo.nama_penerima,''), CASE WHEN UPPER(bo.status) = 'B' THEN 'belum disetujui' WHEN UPPER(bo.status) = 'C' THEN 'dalam penilaian' WHEN UPPER(bo.status) = 'T' THEN 'tidak disetujui' WHEN UPPER(bo.status) = 'D' THEN 'berjalan' WHEN UPPER(bo.status = 'S') THEN 'selesai' ELSE '' END, IFNULL(bo.jumlah_target,'Tanpa Batas'), IFNULL(bo.satuan_target,''), IFNULL(bo.lama_penayangan,''), IFNULL(bo.tanggal_akhir,''), IFNULL(bo.tanggal_awal,''), IFNULL(bo.total_rab,''), IFNULL(bo.min_donasi,''), IFNULL(k.nama,''), IFNULL(s.nama,''), IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)), TRUNCATE(COALESCE(IF(bo.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1))),0),0), IFNULL(SUM(d.jumlah_donasi),0), IFNULL(FORMAT(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END),0,'id_ID'),0), IFNULL(FORMAT(SUM(CASE WHEN apd.id_pelaksanaan IS NULL THEN d.jumlah_donasi END),0,'id_ID'),0), IFNULL(COUNT(d.id_donatur),0) ) LIKE CONCAT('%',?,'%')";
        
        $sql_inner = "SELECT bi.id_bantuan FROM bantuan bi {$tables} GROUP BY bi.id_bantuan ORDER BY {$this->_order} {$this->_order_direction} LIMIT {$this->_offset},{$this->_limit}";

        $left = '';
        if (!is_null($this->getSearch())) {
            if (is_null($kategori)) {
                $left = "LEFT";
            }
            $sql = "SELECT {$column} FROM bantuan bo {$left} {$tables} WHERE {$search} GROUP BY bo.id_bantuan ORDER BY {$this->_order} {$this->_order_direction} LIMIT {$this->_offset},{$this->_limit}";
            $filter['search'] = Sanitize::escape2($this->getSearch());
            $filter = array(
                $filter['search']
            );
            // $kategori = Sanitize::escape2($kategori);
            // $record = $this->countData($tables, "LOWER(kategori.nama) = '{$kategori}'", $search);
        } else {
            if (is_null($kategori)) {
                $sql_inner = "SELECT id_bantuan FROM bantuan LEFT JOIN kategori USING(id_kategori) ORDER BY {$this->_order} {$this->_order_direction} LIMIT {$this->_offset},{$this->_limit}";
                $left = "LEFT";
            } else {
                $sql_inner = "SELECT id_bantuan FROM bantuan JOIN kategori USING(id_kategori) WHERE LOWER(kategori.nama) = ? ORDER BY {$this->_order} {$this->_order_direction} LIMIT {$this->_offset},{$this->_limit}";
                $where = "WHERE LOWER(k.nama) = ?";
                $filter = array(
                    'LOWER(kategori.nama)' => Sanitize::escape2($kategori),
                    'LOWER(k.nama)' => Sanitize::escape2($kategori)
                );
            }
            $kategori = Sanitize::escape2($kategori);
            $record = $this->countData("bantuan LEFT JOIN kategori USING(id_kategori)", "LOWER(kategori.nama) = '{$kategori}'");
            $data['record'] = $record->jumlah_record;
            $sql = "SELECT {$column} FROM ({$sql_inner}) bi JOIN bantuan bo ON (bo.id_bantuan = bi.id_bantuan) {$left} {$tables} {$where} GROUP BY bo.id_bantuan ORDER BY {$this->_order} {$this->_order_direction} LIMIT {$this->_limit}";
        }

        // {$tables} GROUP BY bo.id_bantuan ORDER BY {$this->_order} {$this->_order_direction} LIMIT {$this->_offset}, {$this->_limit}

        $this->db->query($sql, $filter);
        $data['data'] = $this->db->results();
        $this->data = $data;
        return $this->data;
    }

    public function dataBantuanKategori($nama_kategori, $status_bantuan = 'D') {
        $sql = "SELECT b.id_bantuan, b.nama, b.jumlah_target, b.blokir, b.jumlah_target, b.satuan_target,
        IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)) jumlah_pelaksanaan,
        SUM(d.jumlah_donasi) total_donasi, 
        SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END) donasi_terlaksana,
        SUM(CASE WHEN apd.id_pelaksanaan IS NULL THEN d.jumlah_donasi END) saldo_donasi,
        TRUNCATE(COALESCE(IF(b.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1))),0),0) persentase_pelaksanaan
        FROM kategori k JOIN bantuan b USING(id_kategori)
        JOIN (SELECT bi.id_bantuan FROM bantuan bi JOIN kategori ki ON(ki.id_kategori = bi.id_kategori) WHERE LOWER(ki.nama) = ? ORDER BY bi.create_at DESC LIMIT {$this->_offset}, {$this->_limit}) bo ON(bo.id_bantuan = b.id_bantuan)
        LEFT JOIN donasi d 
        ON(b.id_bantuan = d.id_bantuan)
        LEFT JOIN anggaran_pelaksanaan_donasi apd ON (d.id_donasi = apd.id_donasi)
        LEFT JOIN pelaksanaan p
        ON(apd.id_pelaksanaan = p.id_pelaksanaan)
        WHERE b.status = ?
        GROUP BY b.id_bantuan ORDER BY b.create_at DESC LIMIT {$this->getOffset()}";
        $data = $this->db->query($sql, array($nama_kategori, $status_bantuan));
        if ($data->count()) {
            $this->data = $data->results();
            return $this->data;
        }
        return false;
    }

    public function getDetilBantuan($params) {
        $data = $this->db->query("SELECT b.*, s.nama layanan, gm.path_gambar path_gambar_medium, IFNULL(gm.nama, b.nama) nama_gambar_medium, gw.path_gambar path_gambar_wide, IFNULL(gw.nama, b.nama) nama_gambar_wide, IF(b.id_bantuan = 1, COUNT(d.id_donatur)+1999, COUNT(DISTINCT(d.id_donatur))) jumlah_donatur, 
        IF(p2.nama IS NULL, 'Pojok Berbagi Indonesia', p2.nama) pengaju_bantuan,
        IF(b.id_pemohon IS NULL, '/assets/images/brand/pojok-berbagi-transparent.png', (SELECT path_gambar FROM gambar WHERE id_gambar = p2.id_gambar)) path_gambar_logo_pengaju_bantuan,
        COALESCE(b.jumlah_target,'Unlimited') jumlah_target,
        IF(b.jumlah_target IS NULL, 'Donasi', b.satuan_target) jenis_penyaluran,
        IF(b.tanggal_akhir IS NULL, 'Unlimited', CASE WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) < 0 THEN 'Sudah lewat' WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) = 0 THEN 'Terakhir hari ini' ELSE TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) END ) sisa_waktu,
        IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)) jumlah_pelaksanaan,
        IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)) total_donasi, 
        IF(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END) IS NULL, 0, SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)) donasi_terlaksana,
        COUNT(p.id_pelaksanaan) sekian_kali_pencairan,
        IF(SUM(CASE WHEN apd.id_pelaksanaan IS NULL THEN d.jumlah_donasi END) IS NULL, 0, SUM(CASE WHEN apd.id_pelaksanaan IS NULL THEN d.jumlah_donasi END)) saldo_donasi,
        TRUNCATE(COALESCE(IF(b.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1))),0),0) persentase_pelaksanaan
        FROM pelaksanaan p RIGHT JOIN anggaran_pelaksanaan_donasi apd ON (apd.id_pelaksanaan = p.id_pelaksanaan)
        RIGHT JOIN donasi d USING(id_donasi) 
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

    public function setStatus($status) {
        $this->_status = Sanitize::escape2($status);
    }

    public function getStatus() {
        return $this->_status;
    }

    public function setDirection($direction) {
        $this->_order_direction = Sanitize::escape($direction);
    }

    public function getDirection() {
        return $this->_order_direction;
    }

    public function dataDonasiDonaturBantuan($id_bantuan) {
        $sql_index = "SELECT id_donasi FROM donasi WHERE bayar = ? AND id_bantuan = ? ORDER BY waktu_bayar {$this->_order_direction} LIMIT {$this->_offset}, {$this->_limit}";

        if (isset($this->_search)) {
            $column_filter = "d.id_donasi, IFNULL(d.id_donatur,''), FORMAT(d.jumlah_donasi,0,'id_ID'), IFNULL(formatTanggalFull(d.waktu_bayar),''), IFNULL(d2.nama, ''), IFNULL(d2.email, ''), IFNULL(d2.kontak,''), IFNULL(CASE WHEN UPPER(cp.jenis) = 'TB' THEN 'Transfer Bank' WHEN UPPER(cp.jenis) = 'QR' THEN 'Qris' WHEN UPPER(cp.jenis) = 'VA' THEN 'Virtual Account' WHEN UPPER(cp.jenis) = 'GM' THEN 'Gerai Mart' WHEN UPPER(cp.jenis) = 'EW' THEN 'E-Wallet' WHEN UPPER(cp.jenis) = 'GI' THEN 'Giro' WHEN UPPER(cp.jenis) = 'TN' THEN 'Tunai' ELSE '' END,''), IFNULL(gcp.nama,''), IFNULL(cp.nama,'')";
            $this->splits = $column_filter;
            $sql_index = "SELECT d.id_donasi FROM donasi d LEFT JOIN channel_payment cp USING(id_cp) LEFT JOIN donatur d2 USING(id_donatur) LEFT JOIN gambar gcp ON(gcp.id_gambar = cp.id_gambar) WHERE d.bayar = ? AND d.id_bantuan = ? AND CONCAT({$this->splits}) LIKE '%{$this->_search}%' ORDER BY waktu_bayar {$this->_order_direction} LIMIT {$this->_offset}, {$this->_limit}";
        }

        $sql = "SELECT d.id_donasi, d.id_donatur, FORMAT(d.jumlah_donasi, 0, 'id_ID') jumlah_donasi, IFNULL(ga.path_gambar, '/assets/images/default.png') path_gambar_akun, IFNULL(ga.nama,'default') nama_path_gambar_akun, d2.nama nama_donatur, d2.email, d2.kontak, formatTanggalFull(d.waktu_bayar) waktu_bayar, cp.id_cp, cp.jenis, IFNULL(gcp.path_gambar, '/assets/images/brand/favicon-pojok-icon.ico') path_gambar_cp, IFNULL(gcp.nama, cp.nama) nama_path_gambar_cp
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

    public function getCurrentListIdBantuanKategori($nama_kategori = null, $list_id = array()) {
        $values = array();
        $innerArrayFilter = array();
        $status = '';
        if (isset($this->_status)) {
            array_push($innerArrayFilter, "AND UPPER(b.status) = UPPER(?)");
            array_push($values, $this->_status);
            $status = " AND status = ?";
        }

        if (!is_null($nama_kategori)) {
            array_push($innerArrayFilter, "AND LOWER(k.nama) = LOWER(?)");
            array_push($values, $nama_kategori);
        }

        if (count(is_countable($list_id) ? $list_id : []) > 0) {
            $questionMarks = '';
            $xCol = 1;

            foreach ($list_id as $questionMark) {
                $questionMarks .= "?";
                if ($xCol < count($list_id)) {
                    $questionMarks .= ", ";
                }
                $xCol++;
            }

            $lastListId = array_reverse($list_id)[0];

            array_push($innerArrayFilter, "AND b.action_at >= (
                SELECT IFNULL(
                    (
                        SELECT MAX(action_at) FROM bantuan WHERE prioritas IS NULL AND id_bantuan IN ({$questionMarks}){$status}
                    ),
                    (
                        SELECT MAX(action_at) FROM bantuan WHERE id_bantuan NOT IN($questionMarks){$status}
                    )
                )
            ) OR b.id_bantuan IN (SELECT id_bantuan FROM bantuan WHERE id_bantuan IN ({$questionMarks}){$status})");
            $values = array_merge($values, $list_id);
            if (!is_null($status)) {
                array_push($values, $this->_status);
            }
            $values = array_merge($values, $list_id);
            if (!is_null($status)) {
                array_push($values, $this->_status);
            }
            $values = array_merge($values, $list_id);
            if (!is_null($status)) {
                array_push($values, $this->_status);
            }
        }

        $innerArrayFilter = implode(' ', $innerArrayFilter);

        $sql = "SELECT b.id_bantuan FROM bantuan b LEFT JOIN kategori k ON (b.id_kategori = k.id_kategori) WHERE b.blokir IS NULL {$innerArrayFilter} ORDER BY b.prioritas {$this->getDirection()}, {$this->getOrder()} {$this->getDIrection()}, b.id_bantuan ASC";
        $this->db->query($sql, $values);

        if (!$this->db->count()) {
            return false;
        }

        $this->data = $this->db->results();
        return $this->data;
    }

    public function getListIdBantuan($nama_kategori = null, $list_id = array()) {
        $innerArrayFilter = array();
        $values = array();
        $columns ="b.id_bantuan, s.id_sektor layanan, b.nama nama_bantuan, gm.path_gambar path_gambar_medium, IFNULL(gm.nama,CONCAT('Gambar ',b.nama)) nama_gambar_medium, gw.path_gambar path_gambar_wide, IFNULL(gw.nama,CONCAT('Gambar ',b.nama)) nama_gambar_wide, s.nama nama_sektor, k.nama nama_kategori, IF(k.warna IS NULL, '#727272', k.warna) warna,
        IF(p2.nama IS NULL, '/assets/images/brand/pojok-berbagi-transparent.png', g2.path_gambar) path_gambar_pengaju,
        IF(p2.nama IS NULL, 'Pojok Berbagi Indonesia', p2.nama) pengaju_bantuan,
        FORMAT(SUM(CASE WHEN d.bayar = 1 THEN d.jumlah_donasi ELSE 0 END),0,'id_ID') total_donasi,
        SUM(CASE WHEN d.bayar = 1 AND apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi ELSE 0 END) donasi_disalurkan,
        IF(b.tanggal_akhir IS NULL, 'Unlimited', CASE WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) < 0 THEN 'Sudah lewat' WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) = 0 THEN 'Terakhir hari ini' ELSE CONCAT(TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))),' hari lagi') END ) sisa_waktu,
        IF(b.jumlah_target IS NULL,
        IF(TRUNCATE((SUM(IF(apd.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1) IS NULL, 0, TRUNCATE((SUM(IF(apd.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1))
        , IF(TRUNCATE((SUM(p1.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p1.jumlah_pelaksanaan)/b.jumlah_target)*100,1))) persentase_donasi_dilaksanakan";
        $tables = "bantuan b LEFT JOIN donasi d ON (b.id_bantuan = d.id_bantuan)
        LEFT JOIN anggaran_pelaksanaan_donasi apd ON (d.id_donasi = apd.id_donasi)
        LEFT JOIN pelaksanaan p1 USING(id_pelaksanaan)
        LEFT JOIN gambar gm ON (b.id_gambar_medium = gm.id_gambar)
        LEFT JOIN gambar gw ON (b.id_gambar_wide = gw.id_gambar)
        LEFT JOIN pemohon p2 USING(id_pemohon)
        LEFT JOIN gambar g2 ON (g2.id_gambar = p2.id_gambar)
        LEFT JOIN sektor s USING(id_sektor)
        LEFT JOIN kategori k USING(id_kategori)";

        if (isset($this->_status)) {
            array_push($innerArrayFilter, "AND UPPER(b.status) = UPPER(?)");
            array_push($values, $this->_status);
        }

        if (!is_null($nama_kategori)) {
            array_push($innerArrayFilter, "AND LOWER(k.nama) = LOWER(?)");
            array_push($values, $nama_kategori);
        }

        if(count(is_countable($list_id) ? $list_id : [])) {
            $questionMarks = '';
            $xCol = 1;

            foreach ($list_id as $questionMark) {
                $questionMarks .= "?";
                if ($xCol < count($list_id)) {
                    $questionMarks .= ", ";
                }
                $xCol++;
            }
            array_push($innerArrayFilter, "AND b.id_bantuan IN ($questionMarks)");
            $values = array_merge($values, $list_id);
        }

        $innerArrayFilter = implode(' ', $innerArrayFilter);

        $sql = "SELECT {$columns} FROM {$tables} WHERE b.blokir IS NULL {$innerArrayFilter} GROUP BY b.id_bantuan ORDER BY b.prioritas {$this->getDirection()}, {$this->getOrder()} {$this->getDirection()}, b.id_bantuan ASC";
        $this->db->query($sql, $values);

        if (!$this->db->count()) {
            return false;
        }

        $this->data = $this->db->results();
        return $this->data;
    }

    public function getListBantuan($nama_kategori = null) {
        $values = array();
        $innerArrayFilter = array();
        $columns ="b.id_bantuan, s.id_sektor layanan, b.nama nama_bantuan, gm.path_gambar path_gambar_medium, IFNULL(gm.nama,CONCAT('Gambar ',b.nama)) nama_gambar_medium, gw.path_gambar path_gambar_wide, IFNULL(gw.nama,CONCAT('Gambar ',b.nama)) nama_gambar_wide, s.nama nama_sektor, k.nama nama_kategori, IF(k.warna IS NULL, '#727272', k.warna) warna,
        IF(p2.nama IS NULL, '/assets/images/brand/pojok-berbagi-transparent.png', g2.path_gambar) path_gambar_pengaju,
        IF(p2.nama IS NULL, 'Pojok Berbagi Indonesia', p2.nama) pengaju_bantuan,
        FORMAT(SUM(CASE WHEN d.bayar = 1 THEN d.jumlah_donasi ELSE 0 END),0,'id_ID') total_donasi,
        SUM(CASE WHEN d.bayar = 1 AND apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi ELSE 0 END) donasi_disalurkan,
        IF(b.tanggal_akhir IS NULL, 'Unlimited', CASE WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) < 0 THEN 'Sudah lewat' WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) = 0 THEN 'Terakhir hari ini' ELSE CONCAT(TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))),' hari lagi') END ) sisa_waktu,
        IF(b.jumlah_target IS NULL,
        IF(TRUNCATE((SUM(IF(apd.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1) IS NULL, 0, TRUNCATE((SUM(IF(apd.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1))
        , IF(TRUNCATE((SUM(p1.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p1.jumlah_pelaksanaan)/b.jumlah_target)*100,1))) persentase_donasi_dilaksanakan";
        $tables = "LEFT JOIN donasi d ON (b.id_bantuan = d.id_bantuan)
        LEFT JOIN anggaran_pelaksanaan_donasi apd ON (d.id_donasi = apd.id_donasi)
        LEFT JOIN pelaksanaan p1 USING(id_pelaksanaan)
        LEFT JOIN gambar gm ON (b.id_gambar_medium = gm.id_gambar)
        LEFT JOIN gambar gw ON (b.id_gambar_wide = gw.id_gambar)
        LEFT JOIN pemohon p2 USING(id_pemohon)
        LEFT JOIN gambar g2 ON (g2.id_gambar = p2.id_gambar)
        LEFT JOIN sektor s USING(id_sektor)
        LEFT JOIN kategori k USING(id_kategori)";

        if (isset($this->_status)) {
            array_push($innerArrayFilter, "AND UPPER(bi.status) = UPPER(?)");
            array_push($values, $this->_status);
        }

        if (!is_null($nama_kategori)) {
            array_push($innerArrayFilter, "AND LOWER(ki.nama) = LOWER(?)");
            array_push($values, $nama_kategori);
        }

        $innerArrayFilter = implode(' ', $innerArrayFilter);

        $innerTable = "(SELECT bi.id_bantuan FROM bantuan bi LEFT JOIN kategori ki ON (bi.id_kategori = ki.id_kategori) WHERE bi.blokir IS NULL {$innerArrayFilter} ORDER BY bi.prioritas {$this->getDirection()}, bi.action_at {$this->getDirection()}, bi.id_bantuan ASC LIMIT {$this->getOffset()}, {$this->getLimit()})";

        $sql = "SELECT {$columns} FROM {$innerTable} bin JOIN bantuan b ON (bin.id_bantuan = b.id_bantuan) {$tables} GROUP BY b.id_bantuan ORDER BY b.prioritas {$this->getDirection()}, {$this->getOrder()} {$this->getDirection()}, b.id_bantuan ASC LIMIT {$this->getLimit()}";
        $this->db->query($sql, $values);

        // Debug::pr($this->db);

        if (!$this->db->count()) {
            return false;
        }

        $return['data'] = $this->db->results();

        $list_id = array_column($return['data'],'id_bantuan');

        if (count($list_id)) {
            $questionMarks = '';
            $xCol = 1;

            foreach ($list_id as $questionMark) {
                $questionMarks .= "?";
                if ($xCol < count($list_id)) {
                    $questionMarks .= ", ";
                }
                $xCol++;
            }
        }
        
        if (!is_null($nama_kategori)) {
            $countValues = array(
                Sanitize::escape2($this->_status),
                $nama_kategori
            );
            // $countValues = array_merge($countValues, $list_id);
            // array_push($countValues, Sanitize::escape2($this->_status));
            $result = $this->countData("bantuan JOIN kategori USING(id_kategori)", array("(blokir IS NULL OR blokir != '1') AND status = ? AND kategori.nama = ?", $countValues));
        } else {
            $countValues = array(
                Sanitize::escape2($this->_status),
            );
            // $countValues = array_merge($countValues, $list_id);
            // array_push($countValues, Sanitize::escape2($this->_status));
            $result = $this->countData("bantuan LEFT JOIN kategori USING(id_kategori)", array("(blokir IS NULL OR blokir != '1') AND status = ?", $countValues));
        }

        $return['record'] = $result->jumlah_record;
        $return['load_more'] = ($return['record'] > ($this->getOffset() + $this->getLimit()) ? true : false);
        $return['offset'] = $this->getOffset();
        $return['limit'] = $this->getLimit();

        $this->data = $return;
        return $this->data;
    }

    public function getBanner() {
        $data = $this->db->query("SELECT b.id_bantuan, b.jumlah_target, b.deskripsi, b.nama nama_bantuan, gm.path_gambar path_gambar_medium, IFNULL(gm.nama,CONCAT('Gambar ',b.nama)) nama_gambar_medium, gw.path_gambar path_gambar_wide, IFNULL(gw.nama,CONCAT('Gambar ',b.nama)) nama_gambar_wide, k.nama nama_kategori,
        CASE WHEN b.id_sektor = 'S' THEN 'Sosial Kemanusiaan' WHEN b.id_sektor = 'P' THEN 'Pendidikan Umat' WHEN b.id_sektor = 'E' THEN 'Pemandirian Ekonomi' WHEN b.id_sektor = 'K' THEN 'Kesehatan Masyarakat' WHEN b.id_sektor = 'L' THEN 'Lingkungan Asri' WHEN b.id_sektor = 'B' THEN 'Tanggap Bencana' END layanan,
		IF(b.tanggal_akhir IS NULL, 'Unlimited', CASE WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) < 0 THEN 'Sudah lewat' WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) = 0 THEN 'Terakhir hari ini' ELSE TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) END ) sisa_waktu,
        IF(b.id_bantuan = 1, COUNT(d.id_donatur)+1999, COUNT(d.id_donatur)) jumlah_donatur,
        SUM(CASE WHEN d.bayar = 1 THEN d.jumlah_donasi ELSE 0 END) total_donasi,
        IF(b.jumlah_target IS NULL, 'Donasi', b.satuan_target) jenis_penyaluran,
        IF(b.jumlah_target IS NULL, SUM(CASE WHEN d.bayar = 1 AND apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi ELSE 0 END), IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan))) donasi_disalurkan,
        IF(b.jumlah_target IS NULL,
          IF(TRUNCATE((SUM(IF(apd.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1) IS NULL, 0, TRUNCATE((SUM(IF(apd.id_pelaksanaan IS NULL, 0, d.jumlah_donasi))/IF(SUM(d.jumlah_donasi) IS NULL, 0, SUM(d.jumlah_donasi)))*100,1))
        , IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/b.jumlah_target)*100,1))) persentase_donasi_dilaksanakan
        FROM bantuan b LEFT JOIN gambar gm ON (b.id_gambar_medium = gm.id_gambar)
        LEFT JOIN gambar gw ON (b.id_gambar_wide = gw.id_gambar)
        LEFT JOIN kategori k USING(id_kategori)
        LEFT JOIN donasi d USING(id_bantuan)
        LEFT JOIN anggaran_pelaksanaan_donasi apd ON (d.id_donasi = apd.id_donasi)
        LEFT JOIN pelaksanaan p USING(id_pelaksanaan)
        WHERE b.id_bantuan IN (SELECT id_bantuan FROM banner ORDER BY modified_at ASC)
        AND b.status = 'D' AND b.blokir IS NULL AND (b.tanggal_akhir IS NULL OR TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) >= 0)
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
        WHERE LOWER(kategori.nama) = LOWER(?) AND blokir IS NULL
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
        IFNULL(SUM(IF(LOWER(cp.jenis) = 'tn', d.jumlah_donasi, 0)), 0) saldo_tunai,
        (SELECT path_gambar FROM gambar JOIN channel_payment cptb USING(id_gambar) WHERE LOWER(cptb.nama) = 'bank bjb' AND cptb.jenis = 'TB') path_gambar_bjb,
        (SELECT path_gambar FROM gambar JOIN channel_payment cptb USING(id_gambar) WHERE LOWER(cptb.nama) = 'bank bsi' AND cptb.jenis = 'TB') path_gambar_bsi,
        (SELECT path_gambar FROM gambar JOIN channel_payment cptb USING(id_gambar) WHERE LOWER(cptb.nama) = 'bank bri' AND cptb.jenis = 'TB') path_gambar_bri,
        '/assets/images/brand/pojok-berbagi-transparent.png' path_gambar_tunai
        FROM donasi d LEFT JOIN channel_payment cp ON(d.id_cp = cp.id_cp)
        WHERE d.bayar = 1 AND d.id_bantuan = ? AND d.id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi)", array('d.id_bantuan' => Sanitize::escape2($id_bantuan)));
        if (!$this->db->count()) {
            return false;
        }
        $this->data = $this->db->result();
        return $this->data;
    }
}