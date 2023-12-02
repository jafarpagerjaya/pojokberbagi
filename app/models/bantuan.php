<?php
class BantuanModel extends HomeModel {

    private $_halaman = array(1,10),
            $_order = 1,
            $_between = array(
                'start' => 1, 
                'end' => 10
            );

    private $_status = NULL;

    // Cara Offset dengan JOIN ke index
    public function setOffsetByHalaman($halaman) {
        $halaman = Sanitize::toInt2($halaman);
        if ($halaman == 1) {
            $offset = 0;
        } else {
            $offset = (($halaman - 1) * $this->getLimit());
        }
        $this->_offset = $offset;
    }

    public function newDataOffset() {
        $this->db->query("SELECT bo.*, 
        IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)) jumlah_pelaksanaan,
        SUM(d.jumlah_donasi) total_donasi, 
        SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END) total_donasi_digunakan,
        SUM(CASE WHEN apd.id_pelaksanaan IS NULL THEN d.jumlah_donasi END) saldo_donasi,
		TRUNCATE(COALESCE(IF(bo.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1))),0),0) persentase_pelaksanaan
		FROM bantuan bo JOIN (SELECT id_bantuan FROM bantuan ORDER BY {$this->_order} {$this->getDirection()} LIMIT {$this->getOffset()}, {$this->getLimit()}) bi ON(bo.id_bantuan = bi.id_bantuan)
        LEFT JOIN donasi d 
        ON(bo.id_bantuan = d.id_bantuan)
        LEFT JOIN anggaran_pelaksanaan_donasi apd ON (apd.id_donasi = d.id_donasi)
        LEFT JOIN pelaksanaan p
        ON(apd.id_pelaksanaan = p.id_pelaksanaan)
        GROUP BY bo.id_bantuan ORDER BY {$this->_order} {$this->getDirection()}");
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return true;
        }
        return false;
    }

    // Cara Seek Method dilarang ada penghapusan record
    public function setDataBetween($halaman) {
        $halaman = Sanitize::escape2($halaman);
        $start = (($halaman-1) * $this->getLimit()) + 1;
        if ($start < 0) {
            $start = 1;
        }
        $end = $halaman * $this->getLimit();
        if ($this->getDirection() == 'DESC') {
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

        $this->db->query("WITH bil AS (
            SELECT id_bantuan
            FROM bantuan
            WHERE id_bantuan BETWEEN ? AND ?
            ORDER BY id_bantuan DESC
        )   SELECT  
            FORMAT(IFNULL(bil_tpdb.total_penggunaan_donasi,0),0,'id_ID') total_penggunaan_donasi,
            IFNULL(sekian_kali_pelaksanaan, 0) sekian_kali_pelaksanaan,
            COUNT(DISTINCT(d.id_donatur)) jumlah_donatur,
            bol.create_at create_bantuan_at, bol.nama_penerima, bol.status, bol.blokir, IFNULL(FORMAT(bol.jumlah_target,0,'id_ID'),'Tanpa batas') jumlah_target, bol.satuan_target,
            IFNULL(ddibpl.total_pelaksanaan,0) total_pelaksanaan,
            FORMAT(IFNULL(SUM(d.jumlah_donasi),0) - IFNULL(bil_tpdb.total_penggunaan_donasi,0),0,'id_ID') saldo_donasi,
            IF(bol.jumlah_target IS NULL, 
                TRUNCATE(IFNULL((IFNULL(bil_tpdb.total_penggunaan_donasi,0)/SUM(d.jumlah_donasi)),0)*100,2), 
                IF(ddibpl.total_pelaksanaan IS NULL, 0, TRUNCATE((IFNULL(ddibpl.total_pelaksanaan,0)/bol.jumlah_target)*100,2))
            ) persentase_pelaksanaan,
            FORMAT(IFNULL(SUM(d.jumlah_donasi),0),0,'id_ID') total_donasi,
            bil.id_bantuan, bol.nama nama_bantuan, 
            IF(bol.tanggal_akhir IS NULL, 'Unlimited', CASE WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(bol.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) < 0 THEN 'Sudah lewat' WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(bol.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) = 0 THEN 'Terakhir hari ini' ELSE TIMESTAMPDIFF(DAY,NOW(), CONCAT(bol.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) END ) sisa_waktu,
            IF(k.warna IS NULL, '#727272', k.warna) warna,
            IF(pmh.nama IS NULL, '/assets/images/brand/pojok-berbagi-transparent.png', gp.path_gambar) path_gambar_pengaju,
            IF(pmh.nama IS NULL, 'Pojok Berbagi Indonesia', pmh.nama) pengaju_bantuan,
            s.nama nama_sektor, k.nama nama_kategori
            FROM bil
            JOIN bantuan bol ON(bil.id_bantuan = bol.id_bantuan) 
            LEFT JOIN donasi d ON(d.id_bantuan = bol.id_bantuan)
            LEFT JOIN pemohon pmh USING(id_pemohon)
            LEFT JOIN gambar gp ON (gp.id_gambar = pmh.id_gambar)
            LEFT JOIN sektor s USING(id_sektor)
            LEFT JOIN kategori k USING(id_kategori)
            LEFT JOIN (
                    (
                    SELECT bil.id_bantuan, IFNULL(SUM(apd.nominal_penggunaan_donasi),0) total_penggunaan_donasi
                    FROM bil JOIN donasi d ON(d.id_bantuan = bil.id_bantuan) LEFT JOIN anggaran_pelaksanaan_donasi apd ON(d.id_donasi = apd.id_donasi)
                    WHERE d.bayar = 1
                    GROUP BY bil.id_bantuan
                    )
            ) bil_tpdb ON (bil_tpdb.id_bantuan = bil.id_bantuan)
            LEFT JOIN (
                    SELECT SUM(pls.jumlah_pelaksanaan) total_pelaksanaan, COUNT(pls.id_pelaksanaan) sekian_kali_pelaksanaan, pls.id_bantuan FROM (
                        SELECT DISTINCT(bil.id_bantuan), id_pelaksanaan, pl.jumlah_pelaksanaan 
                        FROM bil LEFT JOIN rencana USING(id_bantuan) JOIN pelaksanaan pl USING(id_rencana)
                    ) pls
                    GROUP BY pls.id_bantuan
            ) ddibpl ON (ddibpl.id_bantuan = bil.id_bantuan)
            WHERE d.bayar = 1
            GROUP BY bil.id_bantuan
          ORDER BY {$this->_order} {$this->getDirection()}", array(
            'between_start' => $this->_between['start'],
            'between_end' => $this->_between['end']
        ));
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return true;
        }
        return false;
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

    public function getJumlahDataBantuan($status = null) {
        $params = array();
        if (!is_null($status)) {
            $sql = "SELECT IFNULL(kategori.nama, 'Tanpa Kategori') nama, SUM(CASE WHEN bantuan.status = ? THEN 1 ELSE 0 END) jumlah_kategori FROM kategori RIGHT JOIN bantuan USING(id_kategori) LEFT JOIN sektor USING(id_sektor) GROUP BY kategori.nama";
            $params = array(
                'bantuan.status' => Sanitize::escape2($status)
            );
        } else {
            $sql = "SELECT IFNULL(k.nama, 'Tanpa Kategori') nama, COUNT(IFNULL(b.id_kategori, 0)) jumlah_kategori FROM kategori k RIGHT JOIN bantuan b ON(k.id_kategori = b.id_kategori) GROUP BY k.id_kategori";
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
        // $filter = array();
        // $column = "bo.id_bantuan, bo.nama nama_bantuan, IFNULL(FORMAT(bo.jumlah_target, 0, 'id_ID'),'Tanpa batas') jumlah_target, bo.blokir, bo.satuan_target, formatTanggal(bo.create_at) create_bantuan_at, bo.status status_bantuan, k.nama nama_kategori, k.warna, s.nama nama_sektor, IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)) jumlah_pelaksanaan, TRUNCATE(COALESCE(IF(bo.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1))),0),0) persentase_pelaksanaan, IFNULL(FORMAT(SUM(d.jumlah_donasi),0,'id_ID'),0) jumlah_donasi, IFNULL(FORMAT(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END),0,'id_ID'),0) total_donasi_digunakan, IFNULL(FORMAT(SUM(CASE WHEN apd.id_pelaksanaan IS NULL THEN d.jumlah_donasi END),0,'id_ID'),0) saldo_donasi, IFNULL(COUNT(d.id_donatur),0) jumlah_donatur";
        // $tables = "JOIN kategori k USING(id_kategori) LEFT JOIN sektor s USING(id_sektor) LEFT JOIN donasi d ON(d.id_bantuan = bo.id_bantuan) LEFT JOIN anggaran_pelaksanaan_donasi apd ON (d.id_donasi = apd.id_donasi) LEFT JOIN pelaksanaan p USING(id_pelaksanaan)";
        // $search = "CONCAT( bo.id_bantuan, IFNULL(bo.nama,''), IFNULL(bo.nama_penerima,''), CASE WHEN UPPER(bo.status) = 'B' THEN 'belum disetujui' WHEN UPPER(bo.status) = 'C' THEN 'dalam penilaian' WHEN UPPER(bo.status) = 'T' THEN 'tidak disetujui' WHEN UPPER(bo.status) = 'D' THEN 'berjalan' WHEN UPPER(bo.status = 'S') THEN 'selesai' ELSE '' END, IFNULL(bo.jumlah_target,'Tanpa Batas'), IFNULL(bo.satuan_target,''), IFNULL(bo.lama_penayangan,''), IFNULL(bo.tanggal_akhir,''), IFNULL(bo.tanggal_awal,''), IFNULL(bo.total_rab,''), IFNULL(bo.min_donasi,''), IFNULL(k.nama,''), IFNULL(s.nama,''), IF(SUM(p.jumlah_pelaksanaan) IS NULL, 0, SUM(p.jumlah_pelaksanaan)), TRUNCATE(COALESCE(IF(bo.jumlah_target IS NULL, TRUNCATE(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END)/SUM(d.jumlah_donasi)*100,1), IF(TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1) IS NULL, 0, TRUNCATE((SUM(p.jumlah_pelaksanaan)/bo.jumlah_target)*100,1))),0),0), IFNULL(SUM(d.jumlah_donasi),0), IFNULL(FORMAT(SUM(CASE WHEN apd.id_pelaksanaan IS NOT NULL THEN d.jumlah_donasi END),0,'id_ID'),0), IFNULL(FORMAT(SUM(CASE WHEN apd.id_pelaksanaan IS NULL THEN d.jumlah_donasi END),0,'id_ID'),0), IFNULL(COUNT(d.id_donatur),0) ) LIKE CONCAT('%',?,'%')";
        
        // $sql_inner = "SELECT bi.id_bantuan FROM bantuan bi {$tables} GROUP BY bi.id_bantuan ORDER BY {$this->_order} {$this->getDirection()} LIMIT {$this->getOffset()},{$this->getLimit()}";

        // $left = '';
        // if (!is_null($this->getSearch())) {
        //     if (is_null($kategori)) {
        //         $left = "LEFT";
        //     }
        //     $sql = "SELECT {$column} FROM bantuan bo {$left} {$tables} WHERE {$search} GROUP BY bo.id_bantuan ORDER BY {$this->_order} {$this->getDirection()} LIMIT {$this->getOffset()},{$this->getLimit()}";
        //     $filter['search'] = Sanitize::escape2($this->getSearch());
        //     $filter = array(
        //         $filter['search']
        //     );
        //     // $kategori = Sanitize::escape2($kategori);
        //     // $record = $this->countData($tables, "LOWER(kategori.nama) = '{$kategori}'", $search);
        // } else {
        //     if (is_null($kategori)) {
        //         $sql_inner = "SELECT id_bantuan FROM bantuan LEFT JOIN kategori USING(id_kategori) ORDER BY {$this->_order} {$this->getDirection()} LIMIT {$this->getOffset()},{$this->getLimit()}";
        //         $left = "LEFT";
        //     } else {
        //         $sql_inner = "SELECT id_bantuan FROM bantuan JOIN kategori USING(id_kategori) WHERE LOWER(kategori.nama) = ? ORDER BY {$this->_order} {$this->getDirection()} LIMIT {$this->getOffset()},{$this->getLimit()}";
        //         $where = "WHERE LOWER(k.nama) = ?";
        //         $filter = array(
        //             'LOWER(kategori.nama)' => Sanitize::escape2($kategori),
        //             'LOWER(k.nama)' => Sanitize::escape2($kategori)
        //         );
        //     }
        //     $kategori = Sanitize::escape2($kategori);
        //     $record = $this->countData("bantuan LEFT JOIN kategori USING(id_kategori)", "LOWER(kategori.nama) = '{$kategori}'");
        //     $data['record'] = $record->jumlah_record;
        //     $sql = "SELECT {$column} FROM ({$sql_inner}) bi JOIN bantuan bo ON (bo.id_bantuan = bi.id_bantuan) {$left} {$tables} {$where} GROUP BY bo.id_bantuan ORDER BY {$this->_order} {$this->getDirection()} LIMIT {$this->getLimit()}";
        // }

        // {$tables} GROUP BY bo.id_bantuan ORDER BY {$this->_order} {$this->getDirection()} LIMIT {$this->getOffset()}, {$this->getLimit()}
        $values = array();
        $kategori_filter = "";
        $search_fields = "";
        if (!is_null($kategori)) {
            if ($kategori == 'tanpa kategori') {
                $kategori_filter = "WHERE k.nama IS NULL";
            } else {
                $kategori_filter = "WHERE LOWER(k.nama) = LOWER(?)";
                array_push($values, Sanitize::escape2($kategori));
            }
        }
        if (!is_null($this->getSearch())) {
            $search_fields = "CONCAT( 
                COUNT(DISTINCT(d.id_donatur)), formatTanggal(b.create_at), b.status, k.warna, k.nama, s.nama, cte_b.id_bantuan, IFNULL(SUM(d.jumlah_donasi),0), IFNULL(total_penggunaan_donasi,0), IFNULL(total_pelaksanaan,0), IFNULL(sekian_kali_pelaksanaan,0), IFNULL((SUM(d.jumlah_donasi) - total_penggunaan_donasi),0), b.nama, blokir, IFNULL(FORMAT(jumlah_target, 0, 'id_ID'),'Tanpa batas'), satuan_target,
                IF(b.jumlah_target IS NULL, 
                    TRUNCATE(IFNULL((IFNULL(btpd.total_penggunaan_donasi,0)/SUM(d.jumlah_donasi)),0)*100,2), 
                    IF(bjpskp.total_pelaksanaan IS NULL, 0, TRUNCATE((IFNULL(bjpskp.total_pelaksanaan,0)/b.jumlah_target)*100,2))
                ) persentase_pelaksanaan
             ) LIKE CONCAT('%',?,'%')";
            array_push($values, Sanitize::escape2($this->getSearch()));
        }
        $sql = "
        WITH cte_b AS (
           SELECT id_bantuan 
           FROM bantuan b LEFT JOIN kategori k USING(id_kategori) 
           {$kategori_filter}
           ORDER BY b.action_at DESC LIMIT {$this->getOffset()}, {$this->getLimit()}
        ) SELECT COUNT(DISTINCT(d.id_donatur)) jumlah_donatur, formatTanggal(b.create_at) bantuan_create_at, b.status status_bantuan, k.warna, k.nama nama_kategori, s.nama nama_sektor, cte_b.id_bantuan, IFNULL(SUM(d.jumlah_donasi),0) total_donasi, IFNULL(total_penggunaan_donasi,0) total_penggunaan_donasi, IFNULL(total_pelaksanaan,0) total_pelaksanaan, IFNULL(sekian_kali_pelaksanaan,0) sekian_kali_pelaksanaan, IFNULL((SUM(d.jumlah_donasi) - total_penggunaan_donasi),0) saldo_donasi, b.nama nama_bantuan, blokir, IFNULL(FORMAT(jumlah_target, 0, 'id_ID'),'Tanpa batas') jumlah_target, satuan_target,
          IF(b.jumlah_target IS NULL, 
              TRUNCATE(IFNULL((IFNULL(btpd.total_penggunaan_donasi,0)/SUM(d.jumlah_donasi)),0)*100,2), 
              IF(bjpskp.total_pelaksanaan IS NULL, 0, TRUNCATE((IFNULL(bjpskp.total_pelaksanaan,0)/b.jumlah_target)*100,2))
          ) persentase_pelaksanaan
          FROM cte_b LEFT JOIN donasi d ON(d.id_bantuan = cte_b.id_bantuan)
          LEFT JOIN (
             SELECT cte_b.id_bantuan, IFNULL(SUM(apd.nominal_penggunaan_donasi),0) total_penggunaan_donasi
              FROM cte_b JOIN donasi d ON(d.id_bantuan = cte_b.id_bantuan) LEFT JOIN anggaran_pelaksanaan_donasi apd ON(d.id_donasi = apd.id_donasi)
              GROUP BY cte_b.id_bantuan
          ) btpd ON(btpd.id_bantuan = cte_b.id_bantuan)
          LEFT JOIN (
              SELECT SUM(pls.jumlah_pelaksanaan) total_pelaksanaan, COUNT(pls.id_pelaksanaan) sekian_kali_pelaksanaan, pls.id_bantuan 
              FROM (
                      SELECT DISTINCT(cte_b.id_bantuan), id_pelaksanaan, pl.jumlah_pelaksanaan 
                      FROM cte_b LEFT JOIN rencana USING(id_bantuan) JOIN pelaksanaan pl USING(id_rencana)
              ) pls
              GROUP BY pls.id_bantuan
          ) bjpskp ON(bjpskp.id_bantuan = cte_b.id_bantuan)
          JOIN bantuan b ON(b.id_bantuan = cte_b.id_bantuan)
          LEFT JOIN kategori k USING(id_kategori)
          LEFT JOIN sektor s USING(id_sektor)
          WHERE d.bayar = 1 AND d.id_donasi IS NOT NULL OR d.id_donasi IS NULL {$search_fields}
          GROUP BY cte_b.id_bantuan
          ORDER BY b.action_at DESC
        ";

        if (!is_null($kategori)) {
            if ($kategori == 'tanpa kategori') {
                $record = $this->countData("bantuan LEFT JOIN kategori USING(id_kategori)", "LOWER(kategori.nama) IS NULL");
            } else {
                $record = $this->countData("bantuan LEFT JOIN kategori USING(id_kategori)", array("LOWER(kategori.nama) = ?", $kategori));
            }
            $data['record'] = $record->jumlah_record;
        }

        $this->db->query($sql, $values);
        $data['data'] = $this->db->results();
        $this->data = $data;
        return $this->data;
    }

    public function dataBantuanKategori($nama_kategori, $status_bantuan = 'D') {
        if ($nama_kategori != 'tanpa kategori') {
            $params = array($nama_kategori, $status_bantuan);
            $filters = "LOWER(k.nama) = LOWER(?)";
        } else {
            $params = array($status_bantuan);
            $filters = "k.nama IS NULL";
        }
        $sql = "WITH cte_b AS (
            SELECT id_bantuan 
            FROM bantuan b LEFT JOIN kategori k USING(id_kategori) 
            WHERE {$filters} AND LOWER(b.status) = LOWER(?)
            ORDER BY b.action_at DESC LIMIT {$this->getOffset()}, {$this->getLimit()}
            ) SELECT cte_b.id_bantuan, IFNULL(SUM(d.jumlah_donasi),0) total_donasi, IFNULL(total_penggunaan_donasi,0) total_penggunaan_donasi, IFNULL(total_pelaksanaan,0) total_pelaksanaan, IFNULL(sekian_kali_pelaksanaan,0) sekian_kali_pelaksanaan, IFNULL((SUM(d.jumlah_donasi) - total_penggunaan_donasi),0) saldo_donasi, b.nama nama_bantuan, blokir, jumlah_target, satuan_target,
          IF(b.jumlah_target IS NULL, 
              TRUNCATE(IFNULL((IFNULL(btpd.total_penggunaan_donasi,0)/SUM(d.jumlah_donasi)),0)*100,2), 
              IF(bjpskp.total_pelaksanaan IS NULL, 0, TRUNCATE((IFNULL(bjpskp.total_pelaksanaan,0)/b.jumlah_target)*100,2))
          ) persentase_pelaksanaan
          FROM cte_b LEFT JOIN donasi d ON(d.id_bantuan = cte_b.id_bantuan)
          LEFT JOIN (
             SELECT cte_b.id_bantuan, IFNULL(SUM(apd.nominal_penggunaan_donasi),0) total_penggunaan_donasi
              FROM cte_b JOIN donasi d ON(d.id_bantuan = cte_b.id_bantuan) LEFT JOIN anggaran_pelaksanaan_donasi apd ON(d.id_donasi = apd.id_donasi)
              GROUP BY cte_b.id_bantuan
          ) btpd ON(btpd.id_bantuan = cte_b.id_bantuan)
          LEFT JOIN (
              SELECT SUM(pls.jumlah_pelaksanaan) total_pelaksanaan, COUNT(pls.id_pelaksanaan) sekian_kali_pelaksanaan, pls.id_bantuan 
              FROM (
                      SELECT DISTINCT(cte_b.id_bantuan), id_pelaksanaan, pl.jumlah_pelaksanaan 
                      FROM cte_b LEFT JOIN rencana USING(id_bantuan) JOIN pelaksanaan pl USING(id_rencana)
              ) pls
              GROUP BY pls.id_bantuan
          ) bjpskp ON(bjpskp.id_bantuan = cte_b.id_bantuan)
          JOIN bantuan b ON(b.id_bantuan = cte_b.id_bantuan)
          WHERE d.bayar = 1 AND d.id_donasi IS NOT NULL OR d.id_donasi IS NULL
          GROUP BY cte_b.id_bantuan";

        $data = $this->db->query($sql, $params);
        if ($data->count()) {
            $this->data = $data->results();
            return $this->data;
        }
        return false;
    }

    public function getDetilBantuan($params) {
        $data = $this->db->query("WITH cte AS (
            SELECT IF(d.bayar = 1,d.id_donasi,NULL) id_donasi, b.id_bantuan, SUM(IF(d.bayar = 1,d.jumlah_donasi,0)) jumlah_donasi, IF(d.bayar = 1,d.id_donatur,NULL) id_donatur, d.bayar
           FROM bantuan b LEFT JOIN donasi d USING(id_bantuan)
           WHERE id_bantuan = ?
           GROUP BY d.id_donasi
           ORDER BY d.id_donasi
       ) 
       SELECT 
           IF(b.jumlah_target IS NULL, COALESCE(ctx.total_donasi_disalurkan,0), SUM(pl.jumlah_pelaksanaan)) donasi_disalurkan,
           ctx.total_donasi, COALESCE(ctx.total_donasi_disalurkan,0) total_donasi_disalurkan, (ctx.total_donasi - COALESCE(ctx.total_donasi_disalurkan,0)) saldo_donasi, ctx.id_bantuan, ctx.sekian_kali_pelaksanaan, SUM(pl.jumlah_pelaksanaan) jumlah_pelaksanaan, b.id_pemohon, 
           IF(pmh.nama IS NULL, 'Pojok Berbagi Indonesia', pmh.nama) pengaju_bantuan,
           IF(b.id_pemohon IS NULL, '/assets/images/brand/pojok-berbagi-transparent.png', gpmh.path_gambar) path_gambar_logo_pengaju_bantuan,
           COALESCE(b.jumlah_target,'Unlimited') jumlah_target,
           IF(b.jumlah_target IS NULL, 'Donasi', b.satuan_target) jenis_penyaluran,
           IF(b.tanggal_akhir IS NULL, 'Unlimited', CASE WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) < 0 THEN 'Sudah lewat' WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) = 0 THEN 'Terakhir hari ini' ELSE TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) END ) sisa_waktu,
           IF(b.jumlah_target IS NULL, 
               TRUNCATE((COALESCE(ctx.total_donasi_disalurkan,0)/ctx.total_donasi)*100,0), 
               IF(SUM(pl.jumlah_pelaksanaan) IS NULL, 0, TRUNCATE((SUM(pl.jumlah_pelaksanaan)/b.jumlah_target)*100,0))
           ) persentase_pelaksanaan, 
           IF(b.id_bantuan = 1, jumlah_donatur+1999, jumlah_donatur) jumlah_donatur,
           b.nama, b.status, b.satuan_target, b.total_rab, b.deskripsi, b.create_at, b.action_at,
           s.nama layanan, gm.path_gambar path_gambar_medium, IFNULL(gm.nama, b.nama) nama_gambar_medium, IFNULL(gw.nama, b.nama) nama_gambar_wide, gw.path_gambar path_gambar_wide
       FROM (
           SELECT 
           SUM(jumlah_donasi) total_donasi, 
           SUM(total_penggunaan_anggaran) total_donasi_disalurkan,
           COUNT(DISTINCT(cte.id_donatur)) jumlah_donatur,
           id_bantuan,
           (SELECT COUNT(DISTINCT(apd.id_pelaksanaan)) FROM anggaran_pelaksanaan_donasi apd JOIN cte ON(cte.id_donasi = apd.id_donasi)) sekian_kali_pelaksanaan
           FROM cte LEFT JOIN
           (
                   SELECT apd.id_donasi, SUM(apd.nominal_penggunaan_donasi) total_penggunaan_anggaran FROM anggaran_pelaksanaan_donasi apd JOIN cte c1 ON(apd.id_donasi = c1.id_donasi) GROUP BY apd.id_donasi
           ) ax ON(ax.id_donasi = cte.id_donasi)
           GROUP BY id_bantuan
       ) ctx LEFT JOIN rencana rn ON(rn.id_bantuan = ctx.id_bantuan) LEFT JOIN pelaksanaan pl USING(id_rencana)
       JOIN bantuan b ON(b.id_bantuan = ctx.id_bantuan)
       LEFT JOIN sektor s USING(id_sektor)
       LEFT JOIN gambar gm ON (b.id_gambar_medium = gm.id_gambar)
       LEFT JOIN gambar gw ON (b.id_gambar_wide = gw.id_gambar)
       LEFT JOIN pemohon pmh USING(id_pemohon)
       LEFT JOIN gambar gpmh ON (pmh.id_gambar = gpmh.id_gambar)
       GROUP BY ctx.id_bantuan", array($params));
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

    public function dataDonasiDonaturBantuan($id_bantuan) {
        $sql_index = "SELECT id_donasi FROM donasi WHERE bayar = ? AND id_bantuan = ? ORDER BY waktu_bayar {$this->getDirection()}, id_donasi {$this->getDirection()} LIMIT {$this->getOffset()}, {$this->getLimit()}";

        if (isset($this->_search)) {
            $column_filter = "d.id_donasi, IFNULL(d.id_donatur,''), FORMAT(d.jumlah_donasi,0,'id_ID'), IFNULL(formatTanggalFull(d.waktu_bayar),''), IFNULL(d2.nama, ''), IFNULL(d2.email, ''), IFNULL(d2.kontak,''), IFNULL(CASE WHEN UPPER(cp.jenis) = 'TB' THEN 'Transfer Bank' WHEN UPPER(cp.jenis) = 'QR' THEN 'Qris' WHEN UPPER(cp.jenis) = 'VA' THEN 'Virtual Account' WHEN UPPER(cp.jenis) = 'GM' THEN 'Gerai Mart' WHEN UPPER(cp.jenis) = 'EW' THEN 'E-Wallet' WHEN UPPER(cp.jenis) = 'GI' THEN 'Giro' WHEN UPPER(cp.jenis) = 'TN' THEN 'Tunai' ELSE '' END,''), IFNULL(gcp.nama,''), IFNULL(cp.nama,'')";
            $this->splits = $column_filter;
            $sql_index = "SELECT d.id_donasi FROM donasi d LEFT JOIN channel_payment cp USING(id_cp) LEFT JOIN donatur d2 USING(id_donatur) LEFT JOIN gambar gcp ON(gcp.id_gambar = cp.id_gambar) WHERE d.bayar = ? AND d.id_bantuan = ? AND CONCAT({$this->splits}) LIKE '%{$this->_search}%' ORDER BY waktu_bayar {$this->getDirection()} LIMIT {$this->getOffset()}, {$this->getLimit()}";
        }

        $sql = "SELECT d.id_donasi, d.id_donatur, FORMAT(d.jumlah_donasi, 0, 'id_ID') jumlah_donasi, IFNULL(ga.path_gambar, '/assets/images/default.png') path_gambar_akun, IFNULL(ga.nama,'default') nama_path_gambar_akun, d2.nama nama_donatur, d2.email, d2.kontak, formatTanggalFull(d.waktu_bayar) waktu_bayar, cp.id_cp, cp.jenis, IFNULL(gcp.path_gambar, '/assets/images/brand/favicon-pojok-icon.ico') path_gambar_cp, IFNULL(gcp.nama, cp.nama) nama_path_gambar_cp
        FROM donasi d JOIN ({$sql_index}) di ON (di.id_donasi = d.id_donasi)
        LEFT JOIN channel_payment cp USING(id_cp)
        LEFT JOIN donatur d2 USING(id_donatur) 
        LEFT JOIN gambar gcp ON(gcp.id_gambar = cp.id_gambar)
        LEFT JOIN akun a USING(id_akun)
        LEFT JOIN gambar ga ON(ga.id_gambar = a.id_gambar)
        ORDER BY d.waktu_bayar {$this->getDirection()}, d.id_donasi {$this->getDirection()} LIMIT {$this->getLimit()}";
        
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

    public function getCurrentListIdBantuan($nama_kategori = null, $list_id = array()) {
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

            array_push($innerArrayFilter, "AND b.action_at > (
                SELECT IFNULL(
                    (
                        SELECT MAX(action_at) FROM bantuan WHERE prioritas IS NULL AND id_bantuan IN ({$questionMarks}){$status} AND blokir IS NULL
                    ),
                    (
                        SELECT MAX(action_at) FROM bantuan WHERE id_bantuan NOT IN($questionMarks){$status} AND blokir IS NULL
                    )
                )
            ) OR b.id_bantuan IN (SELECT id_bantuan FROM bantuan WHERE id_bantuan IN ({$questionMarks}){$status}) AND blokir IS NULL");
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
        
        if (isset($this->_status)) {
            array_push($innerArrayFilter, "AND status = UPPER(?)");
            array_push($values, $this->_status);
        }

        $kategoriTable = '';
        if (!is_null($nama_kategori)) {
            array_push($innerArrayFilter, "AND LOWER(kategori.nama) = LOWER(?)");
            array_push($values, $nama_kategori);
            $kategoriTable = "JOIN kategori USING(id_kategori)";
        }

        $innerArrayFilter = implode(' ', $innerArrayFilter);

        $sql = "WITH bil AS (
            SELECT id_bantuan, bantuan.nama nama_bantuan, jumlah_target, id_pemohon, id_sektor, id_kategori, id_gambar_medium, id_gambar_wide, tanggal_akhir, prioritas, action_at
            FROM bantuan {$kategoriTable}
            WHERE blokir IS NULL {$innerArrayFilter} AND (tanggal_akhir IS NULL OR TIMESTAMPDIFF(DAY,NOW(), CONCAT(tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) >= 0)
            ORDER BY prioritas {$this->getDirection()}, action_at {$this->getDirection()}, id_bantuan ASC
            LIMIT {$this->getOffset()}, {$this->getLimit()}
        ) SELECT 
          IF(bil.jumlah_target IS NULL, 
              TRUNCATE((IFNULL(bil_tpdb.total_penggunaan_donasi,0)/IFNULL(SUM(IF(d.bayar = 1,d.jumlah_donasi,0)),0))*100,2), 
              IF(ddibpl.total_pelaksanaan IS NULL, 0, TRUNCATE((IFNULL(ddibpl.total_pelaksanaan,0)/bil.jumlah_target)*100,2))
          ) persentase_donasi_disalurkan,
          IFNULL(FORMAT(SUM(IF(d.bayar = 1,d.jumlah_donasi,0)),0,'id_ID'),0) total_donasi,
          bil.id_bantuan, bil.nama_bantuan, bil.id_sektor, 
          IF(bil.tanggal_akhir IS NULL, 'Unlimited', CASE WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(bil.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) < 0 THEN 'Sudah lewat' WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(bil.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) = 0 THEN 'Terakhir hari ini' ELSE CONCAT(TIMESTAMPDIFF(DAY,NOW(), CONCAT(bil.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))),' hari') END ) sisa_waktu,
          IF(k.warna IS NULL, '#727272', k.warna) warna,
          IF(pmh.nama IS NULL, '/assets/images/brand/pojok-berbagi-transparent.png', gp.path_gambar) path_gambar_pengaju,
          IF(pmh.nama IS NULL, 'Pojok Berbagi Indonesia', pmh.nama) pengaju_bantuan,
          gm.path_gambar path_gambar_medium, IFNULL(gm.nama,CONCAT('Gambar ',bil.nama_bantuan)) nama_gambar_medium, gw.path_gambar path_gambar_wide, IFNULL(gw.nama,CONCAT('Gambar ',bil.nama_bantuan)) nama_gambar_wide, s.nama nama_sektor, k.nama nama_kategori
          FROM bil
          LEFT JOIN donasi d ON(d.id_bantuan = bil.id_bantuan)
          LEFT JOIN pemohon pmh USING(id_pemohon)
          LEFT JOIN gambar gp ON (gp.id_gambar = pmh.id_gambar)
          LEFT JOIN gambar gm ON (bil.id_gambar_medium = gm.id_gambar)
          LEFT JOIN gambar gw ON (bil.id_gambar_wide = gw.id_gambar)
          LEFT JOIN sektor s USING(id_sektor)
          LEFT JOIN kategori k USING(id_kategori)
          LEFT JOIN (
                  (
                 SELECT bil.id_bantuan, IFNULL(SUM(apd.nominal_penggunaan_donasi),0) total_penggunaan_donasi
                 FROM bil JOIN donasi d ON(d.id_bantuan = bil.id_bantuan) LEFT JOIN anggaran_pelaksanaan_donasi apd ON(d.id_donasi = apd.id_donasi)
                 WHERE d.bayar = 1
                 GROUP BY bil.id_bantuan
                )
          ) bil_tpdb ON (bil_tpdb.id_bantuan = bil.id_bantuan)
          LEFT JOIN (
                SELECT SUM(pls.jumlah_pelaksanaan) total_pelaksanaan, pls.id_bantuan FROM (
                    SELECT DISTINCT(bil.id_bantuan), id_pelaksanaan, pl.jumlah_pelaksanaan 
                    FROM bil LEFT JOIN rencana USING(id_bantuan) JOIN pelaksanaan pl USING(id_rencana)
                ) pls
                GROUP BY pls.id_bantuan
          ) ddibpl ON (ddibpl.id_bantuan = bil.id_bantuan)
          GROUP BY bil.id_bantuan
          ORDER BY prioritas {$this->getDirection()}, action_at {$this->getDirection()}, id_bantuan ASC";
        
        $this->db->query($sql, $values);

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
            $result = $this->countData("bantuan b JOIN kategori k USING(id_kategori)", array("(b.blokir IS NULL OR b.blokir != '1') AND b.status = ? AND k.nama = ?", $countValues));
        } else {
            $countValues = array(
                Sanitize::escape2($this->_status),
            );
            // $countValues = array_merge($countValues, $list_id);
            // array_push($countValues, Sanitize::escape2($this->_status));
            $result = $this->countData("bantuan b LEFT JOIN kategori k USING(id_kategori)", array("(b.blokir IS NULL OR b.blokir != '1') AND b.status = ?", $countValues));
        }

        $return['record'] = $result->jumlah_record;
        $return['load_more'] = ($return['record'] > ($this->getOffset() + $this->getLimit()) ? true : false);
        $return['offset'] = $this->getOffset();
        $return['limit'] = $this->getLimit();

        $this->data = $return;
        return $this->data;
    }

    public function getBanner() {
        $data = $this->db->query("WITH donasiDonaturInBanner AS (
            SELECT id_donasi, id_bantuan, jumlah_donasi, id_donatur
            FROM donasi
            WHERE id_bantuan IN (SELECT id_bantuan FROM banner ORDER BY modified_at ASC) AND bayar = 1 
            ORDER BY id_donasi
        ) 	SELECT
            ddibpl.total_pelaksanaan,
            IF(b.id_bantuan = 1, COUNT(DISTINCT(ddib.id_donatur))+1999, COUNT(DISTINCT(ddib.id_donatur))) jumlah_donatur, SUM(ddib.jumlah_donasi) total_donasi, IFNULL(SUM(ddib.penggunaan_anggaran_donasi),0) total_penggunaan_anggaran, ddib.id_bantuan, 
            b.nama nama_bantuan, b.jumlah_target, b.deskripsi, gm.path_gambar path_gambar_medium, IFNULL(gm.nama,CONCAT('Gambar ',b.nama)) nama_gambar_medium, gw.path_gambar path_gambar_wide, IFNULL(gw.nama,CONCAT('Gambar ',b.nama)) nama_gambar_wide, k.nama nama_kategori,
            CASE WHEN b.id_sektor = 'S' THEN 'Sosial Kemanusiaan' WHEN b.id_sektor = 'P' THEN 'Pendidikan Umat' WHEN b.id_sektor = 'E' THEN 'Pemandirian Ekonomi' WHEN b.id_sektor = 'K' THEN 'Kesehatan Masyarakat' WHEN b.id_sektor = 'L' THEN 'Lingkungan Asri' WHEN b.id_sektor = 'B' THEN 'Tanggap Bencana' END layanan,
            IF(b.tanggal_akhir IS NULL, 'Unlimited', CASE WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) < 0 THEN 'Sudah lewat' WHEN TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) = 0 THEN 'Terakhir hari ini' ELSE TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) END ) sisa_waktu,
            IF(b.jumlah_target IS NULL, 'Donasi', b.satuan_target) jenis_penyaluran,
            IF(b.jumlah_target IS NULL, IFNULL(SUM(ddib.penggunaan_anggaran_donasi),0), ddibpl.total_pelaksanaan) donasi_disalurkan,
            IF(b.jumlah_target IS NULL, 
              TRUNCATE((IFNULL(SUM(ddib.penggunaan_anggaran_donasi),0)/SUM(ddib.jumlah_donasi))*100,2), 
              IF(ddibpl.total_pelaksanaan IS NULL, 0, TRUNCATE((ddibpl.total_pelaksanaan/b.jumlah_target)*100,2))
           ) persentase_donasi_disalurkan
            FROM (
                SELECT cte.id_donatur, cte.id_donasi, cte.jumlah_donasi, SUM(apd.nominal_penggunaan_donasi) penggunaan_anggaran_donasi, cte.id_bantuan
                FROM donasiDonaturInBanner cte LEFT JOIN anggaran_pelaksanaan_donasi apd ON(apd.id_donasi = cte.id_donasi)
                GROUP BY cte.id_donasi, apd.id_donasi
                ORDER BY cte.id_donasi
            ) ddib JOIN bantuan b ON(b.id_bantuan = ddib.id_bantuan)
            LEFT JOIN gambar gm ON (b.id_gambar_medium = gm.id_gambar)
           LEFT JOIN gambar gw ON (b.id_gambar_wide = gw.id_gambar)
           LEFT JOIN kategori k USING(id_kategori)
           LEFT JOIN (
                SELECT SUM(pls.jumlah_pelaksanaan) total_pelaksanaan, pls.id_bantuan FROM (
                    SELECT DISTINCT(cte.id_bantuan), id_pelaksanaan, pl.jumlah_pelaksanaan 
                    FROM donasiDonaturInBanner cte LEFT JOIN rencana USING(id_bantuan) JOIN pelaksanaan pl USING(id_rencana)
                ) pls
                GROUP BY pls.id_bantuan
            ) ddibpl ON (ddibpl.id_bantuan = b.id_bantuan)
            WHERE b.status = 'D' AND b.blokir IS NULL AND (b.tanggal_akhir IS NULL OR TIMESTAMPDIFF(DAY,NOW(), CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s'))) >= 0)
            GROUP BY ddib.id_bantuan, ddibpl.total_pelaksanaan
            LIMIT 10");
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
        IFNULL(SUM(IF(LOWER(cp.nama) LIKE '%bank bjb%', d.jumlah_donasi, 0)), 0) saldo_bjb, 
        IFNULL(SUM(IF(LOWER(cp.nama) LIKE '%bank bsi%', d.jumlah_donasi, 0)), 0) saldo_bsi,
        IFNULL(SUM(IF(LOWER(cp.nama) LIKE '%bank bri%', d.jumlah_donasi, 0)), 0) saldo_bri,
        IFNULL(SUM(IF(LOWER(cp.nama) LIKE '%bank mandiri%', d.jumlah_donasi, 0)), 0) saldo_mandiri,
        IFNULL(SUM(IF(LOWER(cp.jenis) = 'tn', d.jumlah_donasi, 0)), 0) saldo_tunai,
        IFNULL(SUM(IF(LOWER(cp.jenis) = 'ew' AND LOWER(cp.nama) LIKE '%gopay%', d.jumlah_donasi, 0)), 0) saldo_gopay,
        IFNULL(SUM(IF(LOWER(cp.jenis) = 'ew' AND LOWER(cp.nama) LIKE '%dana%', d.jumlah_donasi, 0)), 0) saldo_dana,
        (SELECT g.path_gambar FROM gambar g JOIN channel_payment cptb USING(id_gambar) WHERE LOWER(cptb.nama) = 'bank bjb' AND cptb.jenis = 'TB') path_gambar_bjb,
        (SELECT g.path_gambar FROM gambar g JOIN channel_payment cptb USING(id_gambar) WHERE LOWER(cptb.nama) = 'bank bsi' AND cptb.jenis = 'TB') path_gambar_bsi,
        (SELECT g.path_gambar FROM gambar g JOIN channel_payment cptb USING(id_gambar) WHERE LOWER(cptb.nama) = 'bank bri' AND cptb.jenis = 'TB') path_gambar_bri,
        (SELECT g.path_gambar FROM gambar g JOIN channel_payment cptb USING(id_gambar) WHERE LOWER(cptb.nama) = 'bank mandiri' AND cptb.jenis = 'TB') path_gambar_mandiri,
        '/assets/images/brand/pojok-berbagi-transparent.png' path_gambar_tunai,
        (SELECT g.path_gambar FROM gambar g JOIN channel_payment cpew USING(id_gambar) WHERE LOWER(cpew.nama) = 'gopay' AND cpew.jenis = 'EW') path_gambar_gopay,
        (SELECT g.path_gambar FROM gambar g JOIN channel_payment cpew USING(id_gambar) WHERE LOWER(cpew.nama) = 'dana' AND cpew.jenis = 'EW') path_gambar_dana
        FROM donasi d LEFT JOIN channel_payment cp ON(d.id_cp = cp.id_cp)
        WHERE d.bayar = 1 AND d.id_bantuan = ? AND d.id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi)", array('d.id_bantuan' => Sanitize::escape2($id_bantuan)));
        if (!$this->db->count()) {
            return false;
        }
        $this->data = $this->db->result();
        return $this->data;
    }

    public function anggaranPelaksanaanList() {
        $this->db->query("
        SELECT p.id_pelaksanaan, p.status status_pelaksanaan, p.create_at, p.modified_at FROM pelaksanaan p JOIN bantuan b ON(p.id_bantuan = b.id_bantuan) LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_pelaksanaan)
        WHERE id_pelaksanaan BETWEEN ? AND ?
        GROUP BY id_pelaksanaan ORDER BY {$this->_order} {$this->getDirection()}", array(
            'between_start' => $this->_between['start'],
            'between_end' => $this->_between['end']
        ));
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return true;
        }
        return false;
    }

    public function readDeskripsiList() {
        $fields = "d.id_deskripsi, d.id_bantuan, b.nama nama_bantuan, d.judul, IFNULL(formatTanggalFull(d.create_at),'') create_at, LENGTH(TRIM(d.isi)) isi_length";
        $tables = "deskripsi d LEFT JOIN bantuan b ON(d.id_bantuan = b.id_bantuan)";
        // Where bisa di set jika perlu;
        $where = null;
        $data['data'] = array();
        if ($this->getSearch() != null) {
            // OFSET
            $search = "CONCAT(IFNULL(b.nama,''), IFNULL(d.judul,''), IFNULL(formatTanggalFull(d.create_at),''), IFNULL(d.isi,'')) LIKE '%{$this->getSearch()}%'";
            $result = $this->countData($tables, $where, $search);
            $sql = "SELECT {$fields} FROM {$tables} WHERE {$search} ORDER BY {$this->getOrder()} {$this->getDirection()}, d.id_deskripsi {$this->getDirection()} LIMIT {$this->getHalaman()[0]},{$this->getLimit()}";
            $params = array();
        } else {
            // SEEK
            $result = $this->countData($tables, $where);
            $sql = "SELECT {$fields} FROM {$tables} WHERE d.id_deskripsi BETWEEN ? AND ? ORDER BY {$this->getOrder()} {$this->getDirection()}";
            $params = array(
                'between_start' => $this->getHalaman()[0],
                'between_end' => $this->getHalaman()[1]
            );
        }

        $data['total_record'] = $result->jumlah_record;
        
        $this->db->query($sql, $params);

        if ($this->db->count()) {
            $data['data'] = $this->db->results();
        }

        $this->data = $data;
        return true;
    }
}