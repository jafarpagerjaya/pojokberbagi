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
        $this->db->get('bantuan.id_bantuan, bantuan.nama, donasi.id_donasi, donasi.jumlah_donasi, donasi.bayar, donasi.create_at, channel_payment.id_cp, channel_payment.nama nama_cp, channel_payment.jenis, gambar.path_gambar','bantuan JOIN donasi USING(id_bantuan) LEFT JOIN channel_payment USING(id_cp) JOIN gambar ON (channel_payment.id_gambar = gambar.id_gambar)', array('donasi.id_donatur','=', Sanitize::escape($id_donatur)));
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return $this->data;
        }
        return null;
    }

    public function dataTagihan($id_donatur, $status_tagihan) {
        $this->db->get('bantuan.nama, donasi.id_donasi, donasi.jumlah_donasi, donasi.bayar, donasi.create_at, channel_payment.nama nama_cp, channel_payment.jenis, gambar.path_gambar','bantuan JOIN donasi USING(id_bantuan) LEFT JOIN channel_payment USING(id_cp) JOIN gambar ON (channel_payment.id_gambar = gambar.id_gambar)', array('donasi.id_donatur','=', Sanitize::escape($id_donatur)), 'AND', array('donasi.bayar', '=', Sanitize::escape($status_tagihan)));
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
}