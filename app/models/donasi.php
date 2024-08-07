<?php
class DonasiModel extends HomeModel {

    public function isBantuanActive($params) {
        $this->db->query("SELECT tag, id_bantuan, nama, min_donasi, tanggal_akhir, status, blokir FROM bantuan WHERE id_bantuan = ? OR tag = ?", array('id_bantuan' => $params, 'tag' => $params));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    // dataDonasi Call In Donatur Route
    public function dataDonasi($id_donatur) {
        $fields = "bantuan.id_bantuan, bantuan.nama nama_bantuan, donasi.id_donasi, FORMAT(donasi.jumlah_donasi,0,'id_ID') jumlah_donasi, donasi.bayar, IFNULL(formatTanggalFull(donasi.waktu_bayar),'') waktu_bayar, formatTanggalFull(donasi.create_at) create_at, channel_payment.id_cp, channel_payment.nama nama_cp, channel_payment.jenis jenis_cp, IFNULL(gambar.path_gambar,'/assets/images/partners/pojok-berbagi-transparent.png') path_gambar_cp";
        // Where bisa di set jika perlu;
        $where = null;
        $search = null;
        $data['data'] = array();
        if ($this->getSearch() != null) {
            $search = "CONCAT(IFNULL(bantuan.nama,''), IFNULL(donasi.id_donasi,''), CAST(IFNULL(FORMAT(donasi.jumlah_donasi,0,'id_ID'),'') AS CHAR CHARACTER SET UTF8MB4), IFNULL(formatTanggalFull(donasi.waktu_bayar),''), IFNULL(formatTanggalFull(donasi.create_at),''), IFNULL(IF(donasi.bayar = 1, 'Sudah Bayar','Belum Bayar'),''), IFNULL(channel_payment.nama,''), IFNULL(CASE WHEN UPPER(channel_payment.jenis) = 'TB' THEN 'Transfer Bank' WHEN UPPER(channel_payment.jenis) = 'QR' THEN 'Qris' WHEN UPPER(channel_payment.jenis) = 'VA' THEN 'Virtual Account' WHEN UPPER(channel_payment.jenis) = 'GM' THEN 'Gerai Mart' WHEN UPPER(channel_payment.jenis) = 'EW' THEN 'E-Wallet' WHEN UPPER(channel_payment.jenis) = 'GI' THEN 'Giro' WHEN UPPER(channel_payment.jenis) = 'TN' THEN 'Tunai' ELSE '' END,'')) LIKE '%{$this->getSearch()}%'";
            $tables = "donasi LEFT JOIN bantuan USING(id_bantuan) LEFT JOIN channel_payment USING(id_cp) LEFT JOIN gambar ON (channel_payment.id_gambar = gambar.id_gambar)";
        } else {
            $tables = "(SELECT id_donasi FROM donasi WHERE id_donatur = ? ORDER BY bayar ASC, id_donasi DESC LIMIT {$this->getOffset()}, {$this->getLimit()}) di JOIN donasi ON(di.id_donasi = donasi.id_donasi) LEFT JOIN bantuan USING(id_bantuan) LEFT JOIN channel_payment USING(id_cp) LEFT JOIN gambar ON (channel_payment.id_gambar = gambar.id_gambar)";
        }

        $result = $this->countData('donasi JOIN bantuan USING(id_bantuan) LEFT JOIN channel_payment USING(id_cp)', array('donasi.id_donatur = ?', $id_donatur), $search);

        if (!is_null($search)) {
            $search = "WHERE {$search} AND donasi.id_donatur = ?";
        }

        $sql = "SELECT {$fields} FROM {$tables} {$search} ORDER BY donasi.bayar ASC, donasi.id_donasi {$this->getDirection()}";

        if (($this->getSearch() != null)) {
            $sql .= " LIMIT {$this->getOffset()}, {$this->getLimit()}";
        }

        $params = array('id_donatur' => $id_donatur);

        $data['total_record'] = $result->jumlah_record;
        $this->db->query($sql, $params);

        if ($this->db->count()) {
            $data['data'] = $this->db->results();
        }

        $this->data = $data;
        return true;
    }

    public function dataTagihan($id_donatur, $status_tagihan) {
        $fields = "bantuan.id_bantuan, bantuan.nama nama_bantuan, bantuan.tag, donasi.id_donasi, FORMAT(donasi.jumlah_donasi,0,'id_ID') jumlah_donasi, donasi.bayar, IFNULL(formatTanggalFull(donasi.waktu_bayar),'') waktu_bayar, formatTanggalFull(donasi.create_at) create_donasi_at, channel_payment.id_cp, channel_payment.nama nama_cp, channel_payment.jenis jenis_cp, IFNULL(gambar.path_gambar,'/assets/images/partners/pojok-berbagi-transparent.png') path_gambar_cp, channel_payment.nama nama_path_gambar_cp, IF(channel_payment.kode = 'LIP','flip',NULL) flip";
        // Where bisa di set jika perlu;
        $where = null;
        $search = null;
        $data['data'] = array();
        if ($this->getSearch() != null) {
            $search = "CONCAT(IFNULL(IF(channel_payment.kode = 'LIP','flip',NULL),''), IFNULL(channel_payment.nama,''), IFNULL(bantuan.nama,''), IFNULL(bantuan.tag,''), IFNULL(donasi.id_donasi,''), CAST(IFNULL(FORMAT(donasi.jumlah_donasi,0,'id_ID'),'') AS CHAR CHARACTER SET UTF8MB4), IFNULL(formatTanggalFull(donasi.waktu_bayar),''), IFNULL(formatTanggalFull(donasi.create_at),''), IFNULL(IF(donasi.bayar = 1, 'Sudah Bayar','Belum Bayar'),''), IFNULL(channel_payment.nama,''), IFNULL(CASE WHEN UPPER(channel_payment.jenis) = 'TB' THEN 'Transfer Bank' WHEN UPPER(channel_payment.jenis) = 'QR' THEN 'Qris' WHEN UPPER(channel_payment.jenis) = 'VA' THEN 'Virtual Account' WHEN UPPER(channel_payment.jenis) = 'GM' THEN 'Gerai Mart' WHEN UPPER(channel_payment.jenis) = 'EW' THEN 'E-Wallet' WHEN UPPER(channel_payment.jenis) = 'GI' THEN 'Giro' WHEN UPPER(channel_payment.jenis) = 'TN' THEN 'Tunai' ELSE '' END,'')) LIKE '%{$this->getSearch()}%' AND donasi.bayar = ?";
            $search = array($search, array($status_tagihan));
            $tables = "donasi LEFT JOIN bantuan USING(id_bantuan) LEFT JOIN channel_payment USING(id_cp) LEFT JOIN gambar ON (channel_payment.id_gambar = gambar.id_gambar)";
        } else {
            $tables = "(SELECT id_donasi FROM donasi WHERE bayar = ? AND id_donatur = ? ORDER BY bayar ASC, id_donasi DESC LIMIT {$this->getOffset()}, {$this->getLimit()}) di JOIN donasi ON(di.id_donasi = donasi.id_donasi) LEFT JOIN bantuan USING(id_bantuan) LEFT JOIN channel_payment USING(id_cp) LEFT JOIN gambar ON (channel_payment.id_gambar = gambar.id_gambar)";
        }

        $result = $this->countData('donasi JOIN bantuan USING(id_bantuan) LEFT JOIN channel_payment USING(id_cp)', array('donasi.id_donatur = ? AND donasi.bayar = ?', array($id_donatur, $status_tagihan)), $search);

        if (!is_null($search)) {
            $search = "WHERE {$search[0]} AND donasi.id_donatur = ?";
        }

        $sql = "SELECT {$fields} FROM {$tables} {$search} ORDER BY donasi.bayar ASC, donasi.id_donasi {$this->getDirection()}";
        
        $params = array(
            'bayar' => Sanitize::escape2($status_tagihan),
            'id_donatur' => $id_donatur
        );

        if ($this->getSearch() != null) {
            $sql .= " LIMIT {$this->getOffset()}, {$this->getLimit()}";
        }

        $data['total_record'] = $result->jumlah_record;
        $this->db->query($sql, $params);

        if ($this->db->count()) {
            $data['data'] = $this->db->results();
        }

        $this->data = $data;
        return true;
    }

    public function countRecordTagihan($id_donatur, $status_tagihan) {
        $this->db->query('SELECT COUNT(id_donasi) jumlah_record FROM donasi WHERE id_donatur = ? AND bayar = ?', array('id_donatur' => Sanitize::escape($id_donatur), 'bayar' => Sanitize::escape($status_tagihan)));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getTagihan($id_donasi) {
        $this->db->query('SELECT IFNULL(ga.path_gambar,"") path_gambar_avatar, IFNULL(ga.nama,"Donatur") nama_avatar, bantuan.nama nama_bantuan, bantuan.status, donasi.id_donasi, donasi.create_at, FORMAT(donasi.jumlah_donasi,0,"id_ID") jumlah_donasi, donasi.doa, donasi.alias, donatur.nama nama_donatur, donatur.email, channel_payment.jenis, 
        channel_payment.nama nama_cp, gcp.path_gambar path_gambar_cp
        FROM bantuan JOIN donasi USING(id_bantuan) JOIN donatur USING(id_donatur) JOIN channel_payment USING(id_cp) LEFT JOIN gambar gcp ON(gcp.id_gambar = channel_payment.id_gambar) LEFT JOIN akun USING(id_akun) LEFT JOIN gambar ga ON(akun.id_gambar = ga.id_gambar)
        WHERE donasi.id_donasi = ?', array('donasi.id_donasi' => Sanitize::escape2($id_donasi)));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getKuitansiByIdDonasi($id_donasi) {
        $this->db->query("SELECT k.id_kuitansi, formatTanggal(k.create_at) create_kuitansi_at, dur.id_donatur, dur.nama nama_donatur, FORMAT(dsi.jumlah_donasi,0,'id_ID') jumlah_donasi, COALESCE(dsi.alias, dur.samaran, 'Sahabat Berbagi') samaran, COALESCE(dsi.kontak, dur.kontak,'') kontak, dur.email, b.nama nama_bantuan, cp.jenis, cp.nama nama_cp, cp.nomor, gcp.path_gambar path_gambar_cp, IFNULL(gp.nama,'') nama_gambar_signature, IFNULL(LOWER(p.nama), '') nama_pengesah, IFNULL(gp.path_gambar, '') path_gambar_signature, IFNULL(j.alias, '') alias_jabatan FROM kuitansi k JOIN donasi dsi ON (k.id_donasi = dsi.id_donasi) LEFT JOIN donatur dur USING(id_donatur) LEFT JOIN channel_payment cp USING(id_cp) LEFT JOIN bantuan b USING(id_bantuan) LEFT JOIN gambar gcp ON(gcp.id_gambar = cp.id_gambar) LEFT JOIN pegawai p ON(k.id_pengesah = p.id_pegawai) LEFT JOIN gambar gp ON(p.id_tanda_tangan = gp.id_gambar) LEFT JOIN jabatan j USING(id_jabatan) WHERE k.id_donasi = ?", array('k.id_donasi' => Sanitize::escape2($id_donasi)));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getResumeKuitansiDonasi($id_kuitansi) {
        $this->db->query("SELECT b.nama nama_bantuan, k.id_kuitansi, FORMAT(d.jumlah_donasi,0,'id_ID') jumlah_donasi, gcp.path_gambar path_gambar_cp, cp.nama nama_cp, cp.jenis jenis_cp FROM kuitansi k JOIN donasi d USING(id_donasi) LEFT JOIN channel_payment cp USING(id_cp) LEFT JOIN gambar gcp ON(cp.id_gambar = gcp.id_gambar) JOIN bantuan b ON(d.id_bantuan = b.id_bantuan) WHERE k.id_kuitansi = ?", array('k.id_kuitansi' => $id_kuitansi));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getTimeLineDonationByIdKuitansi($id_kuitansi) {
        $this->db->query("SELECT CONCAT(formatTanggal(d.create_at), DATE_FORMAT(d.create_at, ' %H:%i')) siap_donasi, CONCAT(formatTanggal(d.waktu_bayar), DATE_FORMAT(d.waktu_bayar, ' %H:%i')) waktu_bayar, CONCAT(formatTanggal(k.create_at), DATE_FORMAT(k.create_at, ' %H:%i')) kuitansi_diterbitkan, CONCAT(formatTanggal(k.waktu_cetak), DATE_FORMAT(k.waktu_cetak, ' %H:%i')) waktu_cetak FROM kuitansi k JOIN donasi d USING(id_donasi) WHERE k.id_kuitansi = ?", array('k.id_kuitansi' => $id_kuitansi));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getOrderDonasi($id_order_donasi) {
        $this->db->query('SELECT order_donasi.*, donatur.nama nama_donatur, donatur.email, channel_payment.jenis, 
        channel_payment.nama nama_cp, channel_payment.kode, channel_payment.nomor, channel_payment.atas_nama, gambar.path_gambar path_gambar_cp
        FROM order_donasi JOIN donatur USING(id_donatur) JOIN channel_payment USING(id_cp) LEFT JOIN gambar ON(gambar.id_gambar = channel_payment.id_gambar)
        WHERE order_donasi.id_order_donasi = ?', array('order_donasi.id_order_donasi' => Sanitize::escape2($id_order_donasi)));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getDataTagihanOrderDonasiDonatur($id_order_donasi) {
        $fields = "IF(ca.jenis = 'PG', ca.nama, NULL) pay_gate, dt.nama nama_donatur, dt.email, (CASE WHEN (gad.path_gambar IS NULL AND dt.jenis_kelamin IS NULL) THEN '/assets/images/default.png' WHEN (gad.path_gambar IS NULL AND dt.jenis_kelamin = 'P') THEN '/assets/images/female-avatar.jpg' WHEN (gad.path_gambar IS NULL AND dt.jenis_kelamin = 'L') THEN '/assets/images/male-avatar.jpg' ELSE gad.path_gambar END) path_gambar_akun, (CASE WHEN (gad.nama IS NULL AND dt.jenis_kelamin IS NULL) THEN 'default' WHEN (gad.nama IS NULL AND dt.jenis_kelamin = 'P') THEN 'female-avatar' WHEN (gad.nama IS NULL AND dt.jenis_kelamin = 'L') THEN 'male-avatar' ELSE gad.nama END) nama_path_gambar_akun, b.nama nama_bantuan, FORMAT(od.jumlah_donasi,0,'id_ID') jumlah_donasi, od.doa, od.create_at, cp.jenis, gcp.path_gambar path_gambar_cp, cp.nama nama_path_gambar_cp";
        $tables = "order_donasi od JOIN donatur dt USING(id_donatur) LEFT JOIN akun a USING(id_akun) LEFT JOIN gambar gad ON(a.id_gambar = gad.id_gambar) JOIN bantuan b USING(id_bantuan) JOIN channel_payment cp USING(id_cp) LEFT JOIN gambar gcp ON(cp.id_gambar = gcp.id_gambar) LEFT JOIN channel_account ca USING(id_ca)";

        $this->db->get($fields, $tables, array('od.id_order_donasi', '=', $id_order_donasi));
        
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getDataTagihanDonasiDonatur($id_donasi) {
        $fields = "IF(ca.jenis = 'PG', ca.nama, NULL) pay_gate, dt.nama nama_donatur, dt.email, (CASE WHEN (gad.path_gambar IS NULL AND dt.jenis_kelamin IS NULL) THEN '/assets/images/default.png' WHEN (gad.path_gambar IS NULL AND dt.jenis_kelamin = 'P') THEN '/assets/images/female-avatar.jpg' WHEN (gad.path_gambar IS NULL AND dt.jenis_kelamin = 'L') THEN '/assets/images/male-avatar.jpg' ELSE gad.path_gambar END) path_gambar_akun, (CASE WHEN (gad.nama IS NULL AND dt.jenis_kelamin IS NULL) THEN 'default' WHEN (gad.nama IS NULL AND dt.jenis_kelamin = 'P') THEN 'female-avatar' WHEN (gad.nama IS NULL AND dt.jenis_kelamin = 'L') THEN 'male-avatar' ELSE gad.nama END) nama_path_gambar_akun, b.nama nama_bantuan, FORMAT(dn.jumlah_donasi,0,'id_ID') jumlah_donasi, dn.doa, dn.create_at, cp.jenis, gcp.path_gambar path_gambar_cp, cp.nama nama_path_gambar_cp";
        $tables = "donasi dn JOIN donatur dt USING(id_donatur) LEFT JOIN akun a USING(id_akun) LEFT JOIN gambar gad ON(a.id_gambar = gad.id_gambar) JOIN bantuan b USING(id_bantuan) JOIN channel_payment cp USING(id_cp) LEFT JOIN gambar gcp ON(cp.id_gambar = gcp.id_gambar) LEFT JOIN channel_account ca USING(id_ca)";

        $this->db->get($fields, $tables, array('dn.id_donasi', '=', $id_donasi));
        
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getDataTransaksiDonasi($id_donasi) {
        $this->db->query('SELECT donasi.alias, donasi.jumlah_donasi, donasi.bayar, donasi.waktu_bayar, donasi.create_at, donatur.nama nama_donatur, channel_payment.jenis, 
        channel_payment.nama nama_cp, gambar.path_gambar path_cp, bantuan.nama nama_bantuan, bantuan.nama_penerima
        FROM donatur JOIN donasi USING(id_donatur) JOIN bantuan USING(id_bantuan) JOIN channel_payment USING(id_cp) LEFT JOIN gambar ON(gambar.id_gambar = channel_payment.id_gambar)
        WHERE donasi.id_donasi = ? AND donasi.bayar = 1', array('donasi.id_donasi' => Sanitize::escape(trim($id_donasi))));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getSamaranDonatur($email) {
        $this->db->get('samaran', 'donatur', array('email','=',strtolower(Sanitize::escape(trim($email)))));
        if ($this->db->count()) {
            $this->data = $this->db->result()->samaran;
            return $this->data;
        }
        return null;
    }

    // Admin
    public function getCountUpDonasi() {
        $this->db->query("SELECT SUM(IF(bayar = 0, 1, 0)) sum_belum_terverivikasi, SUM(IF(bayar = 1, 1, 0)) sum_sudah_terverivikasi, COUNT(id_donasi) count_donasi FROM donasi");
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return true;
        }
        return false;
    }

    public function getSaldoDonasi() {
        $this->db->query("SELECT 
        SUM(IF(LOWER(cp.nama) LIKE '%bjb%', d.jumlah_donasi, 0)) saldo_bjb,
        SUM(IF(LOWER(cp.nama) LIKE '%bsi%', d.jumlah_donasi, 0)) saldo_bsi,
        SUM(IF(LOWER(cp.nama) LIKE '%bri%', d.jumlah_donasi, 0)) saldo_bri,
        SUM(IF(LOWER(cp.nama) LIKE '%mandiri%', d.jumlah_donasi, 0)) saldo_mandiri,
        SUM(IF(LOWER(cp.nama) LIKE '%kantor%', d.jumlah_donasi, 0)) saldo_tunai,
        SUM(IF(LOWER(cp.nama) LIKE '%gopay%', d.jumlah_donasi, 0)) saldo_gopay,
        SUM(IF(LOWER(cp.nama) LIKE '%dana%', d.jumlah_donasi, 0)) saldo_dana,
        SUM(IF(LOWER(cp.nama) LIKE '%shopeepay%', d.jumlah_donasi, 0)) saldo_shopeepay,
        SUM(IF(LOWER(cp.kode) LIKE '%LIP%', d.jumlah_donasi, 0)) saldo_flip,
        (SELECT cptb.nomor FROM channel_payment cptb WHERE LOWER(cptb.nama) = 'bank bjb' AND cptb.jenis = 'TB' AND cptb.kode != 'LIP') nomor_bjb,
        (SELECT cptb.nomor FROM channel_payment cptb WHERE LOWER(cptb.nama) = 'bank bsi' AND cptb.jenis = 'TB' AND cptb.kode != 'LIP') nomor_bsi,
        (SELECT cptb.nomor FROM channel_payment cptb WHERE LOWER(cptb.nama) = 'bank bri' AND cptb.jenis = 'TB' AND cptb.kode != 'LIP') nomor_bri,
        (SELECT cptb.nomor FROM channel_payment cptb WHERE LOWER(cptb.nama) = 'bank mandiri' AND cptb.jenis = 'TB' AND cptb.kode != 'LIP') nomor_mandiri,
        (SELECT cptb.nomor FROM channel_payment cptb WHERE LOWER(cptb.nama) = 'cr kantor pusat' AND cptb.jenis = 'TN' AND cptb.kode != 'LIP') nomor_tunai,
        (SELECT cpew.nomor FROM channel_payment cpew WHERE LOWER(cpew.nama) = 'gopay' AND cpew.jenis = 'EW' AND cpew.kode != 'LIP') nomor_gopay,
        (SELECT cpew.nomor FROM channel_payment cpew WHERE LOWER(cpew.nama) = 'dana' AND cpew.jenis = 'EW' AND cpew.kode != 'LIP') nomor_dana,
        (SELECT cpew.nomor FROM channel_payment cpew WHERE LOWER(cpew.nama) = 'shopeepay' AND cpew.jenis = 'EW' AND cpew.kode != 'LIP') nomor_shopeepay,
        (SELECT DISTINCT(cppg.atas_nama) FROM channel_payment cppg WHERE LOWER(cppg.kode) = 'LIP') id_flip
        FROM channel_payment cp LEFT JOIN donasi d ON(d.id_cp = cp.id_cp)
        WHERE d.bayar = 1 AND d.id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi)");
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return true;
        }
        return false;
    }

    public function getListOrderDonasi($id_donatur = null) {
        $fields = "od.id_order_donasi, od.id_cp, b.nama nama_bantuan, b.tag, k.nama nama_kategori, IFNULL(k.warna,'#727272') warna, s.nama nama_sektor, od.external_id, formatTanggalFull(od.create_at) create_order_at, od.status, FORMAT(od.jumlah_donasi,0,'id_ID') jumlah_donasi, od.id_bantuan, od.id_donatur, IF(cp.kode = 'LIP','flip',NULL) flip, cp.jenis jenis_cp, IFNULL(gcp.path_gambar,'/assets/images/brand/favicon-pojok-icon.ico') path_gambar_cp, IFNULL(cp.nama, '') nama_path_gambar_cp";
        $tables = "bantuan b
        LEFT JOIN sektor s ON(s.id_sektor = b.id_sektor)
        LEFT JOIN kategori k ON (k.id_kategori = b.id_kategori)
        RIGHT JOIN order_donasi od ON(od.id_bantuan = b.id_bantuan)
        LEFT JOIN channel_payment cp ON(cp.id_cp = od.id_cp) 
        LEFT JOIN gambar gcp ON(gcp.id_gambar = cp.id_gambar)";
        $data['data'] = array();
        $filter = null;
        $where = null;
        $params = array();
        if (isset($id_donatur)) {
            $where = array(
                'od.id_donatur = ?',
                Sanitize::escape2($id_donatur)
            );
        }
        if ($this->getSearch() != null) {
            $filter = array(
                "CONCAT(IFNULL(od.id_order_donasi,''), IFNULL(b.nama,''), IFNULL(b.tag,''), IFNULL(k.nama,''), IFNULL(s.nama,''), IFNULL(od.external_id,''), IFNULL(formatTanggalFull(od.create_at),''), IFNULL(od.status,''), CAST(IFNULL(FORMAT(od.jumlah_donasi,0,'id_ID'),'') AS CHAR CHARACTER SET UTF8MB4), IFNULL(IF(cp.kode = 'LIP','flip',NULL),''), IFNULL(cp.nama,''), IFNULL(CASE WHEN UPPER(cp.jenis) = 'TB' THEN 'Transfer Bank' WHEN UPPER(cp.jenis) = 'QR' THEN 'Qris' WHEN UPPER(cp.jenis) = 'VA' THEN 'Virtual Account' WHEN UPPER(cp.jenis) = 'GM' THEN 'Gerai Mart' WHEN UPPER(cp.jenis) = 'EW' THEN 'E-Wallet' WHEN UPPER(cp.jenis) = 'GI' THEN 'Giro' WHEN UPPER(cp.jenis) = 'TN' THEN 'Tunai' ELSE '' END,'')) LIKE '%{$this->getSearch()}%'"
            );
            if (!is_null($where)) {
                $filter[0] .= " AND {$where[0]}";
                $filter[1] = $where[1];
                array_push($params, $where[1]);
                $filter = implode(' ', $filter);
            } else {
                $filter = $filter[0];
            }
        }

        $result = $this->countData($tables, $where, $filter);
        $data['total_record'] = $result->jumlah_record;
        if (is_null($filter)) {
            $filter = '';
            if (!is_null($where)) {
                $filter = "WHERE {$where[0]}";
                array_push($params, $where[1]);
            }
        } else {
            $filter = 'WHERE '.$filter;
        }
        
        $sql = "SELECT {$fields} FROM {$tables} {$filter} ORDER BY od.id_order_donasi {$this->getDirection()}, {$this->getOrder()} {$this->getDirection()} LIMIT {$this->getHalaman()[0]},{$this->getLimit()}";        
        $this->db->query($sql, $params);
        
        if ($this->db->count()) {
            $data['data'] = $this->db->results();
        }

        $this->data = $data;
        return true;
    }

    public function getListDonasi() {
        $fields = "b.nama nama_bantuan, k.nama nama_kategori, IFNULL(k.warna,'#727272') warna, s.nama nama_sektor, d.id_donasi, formatTanggalFull(d.create_at) create_donasi_at, d.bayar, formatTanggalFull(d.waktu_bayar) waktu_bayar, FORMAT(d.jumlah_donasi,0,'id_ID') jumlah_donasi, d.id_bantuan, d.id_donatur, IF(cp.kode = 'LIP','flip',NULL) flip, cp.jenis jenis_cp, IFNULL(gcp.path_gambar,'/assets/images/brand/favicon-pojok-icon.ico') path_gambar_cp, IFNULL(gcp.nama, CONCAT('Gambar ',cp.nama)) nama_path_gambar_cp";
        $tables = "bantuan b
        LEFT JOIN sektor s ON(s.id_sektor = b.id_sektor)
        LEFT JOIN kategori k ON (k.id_kategori = b.id_kategori)
        RIGHT JOIN donasi d ON(d.id_bantuan = b.id_bantuan)
        LEFT JOIN channel_payment cp ON(cp.id_cp = d.id_cp) 
        LEFT JOIN gambar gcp ON(gcp.id_gambar = cp.id_gambar)";
        // Where bisa di set jika perlu;
        $where = null;
        $data['data'] = array();
        if ($this->getSearch() != null) {
            // OFSET
            $search = "CONCAT(IFNULL(b.nama,''), IFNULL(k.nama,''), IFNULL(s.nama,''), IFNULL(formatTanggalFull(d.create_at),''), IFNULL(IF(d.bayar = 1, 'sudah diverivikasi','belum diverivikasi'),''), IFNULL(formatTanggalFull(d.waktu_bayar),''), CAST(IFNULL(FORMAT(d.jumlah_donasi,0,'id_ID'),'') AS CHAR CHARACTER SET UTF8MB4), IFNULL(IF(cp.kode = 'LIP','flip',NULL),''), IFNULL(cp.nama,''), IFNULL(CASE WHEN UPPER(cp.jenis) = 'TB' THEN 'Transfer Bank' WHEN UPPER(cp.jenis) = 'QR' THEN 'Qris' WHEN UPPER(cp.jenis) = 'VA' THEN 'Virtual Account' WHEN UPPER(cp.jenis) = 'GM' THEN 'Gerai Mart' WHEN UPPER(cp.jenis) = 'EW' THEN 'E-Wallet' WHEN UPPER(cp.jenis) = 'GI' THEN 'Giro' WHEN UPPER(cp.jenis) = 'TN' THEN 'Tunai' ELSE '' END,'')) LIKE '%{$this->getSearch()}%'";
            $result = $this->countData($tables, $where, $search);
            $sql = "SELECT {$fields} FROM {$tables} WHERE {$search} ORDER BY d.id_donasi {$this->getDirection()}, {$this->getOrder()} {$this->getDirection()} LIMIT {$this->getHalaman()[0]},{$this->getLimit()}";
            $params = array();
        } else {
            // SEEK
            $result = $this->countData($tables, $where);
            $sql = "SELECT {$fields} FROM {$tables} WHERE d.id_donasi BETWEEN ? AND ? ORDER BY d.id_donasi {$this->getDirection()}, {$this->getOrder()} {$this->getDirection()}";
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
        // return false;
    }

    public function getLastDonasi($limit) {
        $this->db->query("SELECT FORMAT(d.jumlah_donasi,0,'id_ID') jumlah_donasi, formatTanggal(d.waktu_bayar) waktu_bayar, b.nama nama_bantuan, b.id_bantuan, ca.jenis, IF(ca.jenis = 'PG', ca.nama, NULL) pay_gate, cp.jenis jenis_cp, cp.nama nama_cp FROM donasi d JOIN bantuan b USING(id_bantuan) LEFT JOIN channel_payment cp USING(id_cp) LEFT JOIN channel_account ca USING(id_ca) WHERE d.bayar = 1 ORDER BY d.waktu_bayar DESC LIMIT {$limit}");
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return $this->data;
        }
        return false;
    }
}