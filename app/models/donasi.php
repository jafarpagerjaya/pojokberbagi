<?php
class DonasiModel extends HomeModel {
    public function isBantuanActive($params) {
        $this->db->query("SELECT id_bantuan, nama, min_donasi, tanggal_akhir, status FROM bantuan WHERE id_bantuan = ? AND blokir IS NULL", array('id_bantuan' => $params));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function dataDonasi($id_donatur) {
        $this->db->get('bantuan.id_bantuan, bantuan.nama, donasi.id_donasi, donasi.jumlah_donasi, donasi.bayar, IFNULL(formatTanggalFull(donasi.waktu_bayar),"") waktu_bayar, formatTanggalFull(donasi.create_at) create_at, channel_payment.id_cp, channel_payment.nama nama_cp, channel_payment.jenis, IFNULL(gambar.path_gambar,"/assets/images/partners/pojok-berbagi-transparent.png") path_gambar ','bantuan JOIN donasi USING(id_bantuan) LEFT JOIN channel_payment USING(id_cp) LEFT JOIN gambar ON (channel_payment.id_gambar = gambar.id_gambar)', array('donasi.id_donatur','=', Sanitize::escape($id_donatur)));
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return $this->data;
        }
        return null;
    }

    public function dataTagihan($id_donatur, $status_tagihan) {
        $this->db->get('bantuan.id_bantuan, bantuan.nama, donasi.id_donasi, donasi.jumlah_donasi, donasi.bayar, IFNULL(formatTanggalFull(donasi.waktu_bayar),"") waktu_bayar, formatTanggalFull(donasi.create_at) create_at, channel_payment.id_cp, channel_payment.nama nama_cp, channel_payment.jenis, IFNULL(gambar.path_gambar,"/assets/images/partners/pojok-berbagi-transparent.png") path_gambar ','bantuan JOIN donasi USING(id_bantuan) LEFT JOIN channel_payment USING(id_cp) LEFT JOIN gambar ON (channel_payment.id_gambar = gambar.id_gambar)', array('donasi.id_donatur','=', Sanitize::escape($id_donatur)), 'AND', array('donasi.bayar', '=', Sanitize::escape($status_tagihan)));
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return $this->data;
        }
        return null;
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

    public function getKwitansiByIdDonasi($id_donasi) {
        $this->db->query("SELECT k.id_kwitansi, formatTanggal(k.create_at) create_kwitansi_at, dur.id_donatur, dur.nama nama_donatur, FORMAT(dsi.jumlah_donasi,0,'id_ID') jumlah_donasi, COALESCE(dsi.alias, dur.samaran, 'Sahabat Berbagi') samaran, COALESCE(dsi.kontak, dur.kontak,'') kontak, dur.email, b.nama nama_bantuan, cp.jenis, cp.nama nama_cp, cp.nomor, gcp.path_gambar path_gambar_cp FROM kwitansi k JOIN donasi dsi ON (k.id_donasi = dsi.id_donasi) LEFT JOIN donatur dur USING(id_donatur) LEFT JOIN channel_payment cp USING(id_cp) LEFT JOIN bantuan b USING(id_bantuan) LEFT JOIN gambar gcp ON(gcp.id_gambar = cp.id_gambar) WHERE k.id_donasi = ?", array('k.id_donasi' => Sanitize::escape2($id_donasi)));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getResumeKwitansiDonasi($id_kwitansi) {
        $this->db->query("SELECT b.nama nama_bantuan, k.id_kwitansi, FORMAT(d.jumlah_donasi,0,'id_ID') jumlah_donasi, gcp.path_gambar path_gambar_cp, cp.nama nama_cp, cp.jenis jenis_cp FROM kwitansi k JOIN donasi d USING(id_donasi) LEFT JOIN channel_payment cp USING(id_cp) LEFT JOIN gambar gcp ON(cp.id_gambar = gcp.id_gambar) JOIN bantuan b ON(d.id_bantuan = b.id_bantuan) WHERE k.id_kwitansi = ?", array('k.id_kwitansi' => $id_kwitansi));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getTimeLineDonationByIdKwitansi($id_kwitansi) {
        $this->db->query("SELECT CONCAT(formatTanggal(d.create_at), DATE_FORMAT(d.create_at, ' %H:%i')) siap_donasi, CONCAT(formatTanggal(d.waktu_bayar), DATE_FORMAT(d.waktu_bayar, ' %H:%i')) waktu_bayar, CONCAT(formatTanggal(k.create_at), DATE_FORMAT(k.create_at, ' %H:%i')) kwitansi_diterbitkan, CONCAT(formatTanggal(k.waktu_cetak), DATE_FORMAT(k.waktu_cetak, ' %H:%i')) waktu_cetak FROM kwitansi k JOIN donasi d USING(id_donasi) WHERE k.id_kwitansi = ?", array('k.id_kwitansi' => $id_kwitansi));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getDataTagihanDonasi($id_donasi) {
        $this->db->query('SELECT donasi.*, donatur.nama nama_donatur, donatur.email, channel_payment.jenis, 
        channel_payment.nama nama_cp, channel_payment.kode, channel_payment.nomor, channel_payment.atas_nama, gambar.path_gambar path_gambar_cp
        FROM donasi JOIN donatur USING(id_donatur) JOIN channel_payment USING(id_cp) LEFT JOIN gambar ON(gambar.id_gambar = channel_payment.id_gambar)
        WHERE donasi.id_donasi = ?', array('donasi.id_donasi' => Sanitize::escape(trim($id_donasi))));
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
        SUM(IF(LOWER(cp.nama) LIKE '%tunai%', d.jumlah_donasi, 0)) saldo_tunai,
        SUM(IF(LOWER(cp.nama) LIKE '%gopay%', d.jumlah_donasi, 0)) saldo_gopay,
        SUM(IF(LOWER(cp.nama) LIKE '%dana%', d.jumlah_donasi, 0)) saldo_dana,
        (SELECT cptb.nomor FROM channel_payment cptb WHERE LOWER(cptb.nama) = 'bank bjb' AND cptb.jenis = 'TB') nomor_bjb,
        (SELECT cptb.nomor FROM channel_payment cptb WHERE LOWER(cptb.nama) = 'bank bsi' AND cptb.jenis = 'TB') nomor_bsi,
        (SELECT cptb.nomor FROM channel_payment cptb WHERE LOWER(cptb.nama) = 'bank bri' AND cptb.jenis = 'TB') nomor_bri,
        (SELECT cptb.nomor FROM channel_payment cptb WHERE LOWER(cptb.nama) = 'tunai' AND cptb.jenis = 'TN') nomor_tunai,
        (SELECT cpew.nomor FROM channel_payment cpew WHERE LOWER(cpew.nama) = 'gopay' AND cpew.jenis = 'EW') nomor_gopay,
        (SELECT cpew.nomor FROM channel_payment cpew WHERE LOWER(cpew.nama) = 'dana' AND cpew.jenis = 'EW') nomor_dana
        FROM channel_payment cp LEFT JOIN donasi d ON(d.id_cp = cp.id_cp)
        WHERE d.bayar = 1 AND d.id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi)");
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return true;
        }
        return false;
    }

    public function getListDonasi() {
        $fields = "b.nama nama_bantuan, k.nama nama_kategori, IFNULL(k.warna,'#727272') warna, s.nama nama_sektor, d.id_donasi, formatTanggalFull(d.create_at) create_donasi_at, d.bayar, formatTanggalFull(d.waktu_bayar) waktu_bayar, FORMAT(d.jumlah_donasi,0,'id_ID') jumlah_donasi, d.id_bantuan, d.id_donatur, cp.jenis jenis_cp, IFNULL(gcp.path_gambar,'/assets/images/brand/favicon-pojok-icon.ico') path_gambar_cp, IFNULL(gcp.nama, CONCAT('Gambar ',cp.nama)) nama_path_gambar_cp";
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
            $search = "CONCAT(IFNULL(b.nama,''), IFNULL(k.nama,''), IFNULL(s.nama,''), IFNULL(formatTanggalFull(d.create_at),''), IFNULL(IF(d.bayar = 1, 'sudah diverivikasi','belum diverivikasi'),''), IFNULL(formatTanggalFull(d.waktu_bayar),''), IFNULL(FORMAT(d.jumlah_donasi,0,'id_ID'),''), IFNULL(cp.nama,''), IFNULL(CASE WHEN UPPER(cp.jenis) = 'TB' THEN 'Transfer Bank' WHEN UPPER(cp.jenis) = 'QR' THEN 'Qris' WHEN UPPER(cp.jenis) = 'VA' THEN 'Virtual Account' WHEN UPPER(cp.jenis) = 'GM' THEN 'Gerai Mart' WHEN UPPER(cp.jenis) = 'EW' THEN 'E-Wallet' WHEN UPPER(cp.jenis) = 'GI' THEN 'Giro' WHEN UPPER(cp.jenis) = 'TN' THEN 'Tunai' ELSE '' END,'')) LIKE '%{$this->getSearch()}%'";
            $result = $this->countData($tables, $where, $search);
            $sql = "SELECT {$fields} FROM {$tables} WHERE {$search} ORDER BY {$this->getOrder()} {$this->getDirection()}, d.id_donasi {$this->getDirection()} LIMIT {$this->getHalaman()[0]},{$this->getLimit()}";
            $params = array();
        } else {
            // SEEK
            $result = $this->countData($tables, $where);
            $sql = "SELECT {$fields} FROM {$tables} WHERE d.id_donasi BETWEEN ? AND ? ORDER BY {$this->getOrder()} {$this->getDirection()}";
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
        $this->db->query("SELECT FORMAT(d.jumlah_donasi,0,'id_ID') jumlah_donasi, formatTanggal(d.waktu_bayar) waktu_bayar, b.nama nama_bantuan, b.id_bantuan, cp.jenis jenis_cp, cp.nama nama_cp FROM donasi d JOIN bantuan b USING(id_bantuan) LEFT JOIN channel_payment cp USING(id_cp) WHERE d.bayar = 1 ORDER BY d.waktu_bayar DESC LIMIT {$limit}");
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return $this->data;
        }
        return false;
    }
}