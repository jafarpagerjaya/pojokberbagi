<?php
class CrModel {
    private $_db,
            $_filter;

    public function __construct() {
        $this->_db = Database::getInstance();
    }

    public function jumlahAkun() {
        $this->_db->query('SELECT count("*") jumlah_akun FROM akun');
        if ($this->_db->count()) {
            return $this->_db->result()->jumlah_akun;
        }
        return false;
    }

    public function jumlahDonatur() {
        $this->_db->query('SELECT count("*") jumlah_donatur FROM donatur');
        if ($this->_db->count()) {
            return $this->_db->result()->jumlah_donatur;
        }
        return false;
    }

    public function jumlahDonasi() {
        $this->_db->query("SELECT SUM(jumlah_donasi) jumlah_donasi FROM donasi");
        if ($this->_db->count()) {
            return $this->_db->result()->jumlah_donasi;
        }
        return false;
    }

    public function jumlahBantuan() {
        $this->_db->query("SELECT COUNT(*) jumlah_bantuan FROM bantuan");
        if ($this->_db->count()) {
            return $this->_db->result()->jumlah_bantuan;
        }
        return false;
    }

    public function jumlahBantuanMenunggu() {
        $this->_db->query("SELECT COUNT(*) jumlah_bantuan_menunggu FROM bantuan WHERE status = 'B'");
        if ($this->_db->count()) {
            return $this->_db->result()->jumlah_bantuan_menunggu;
        }
        return false;
    }

    public function jumlahBantuanAktif() {
        $this->_db->query("SELECT COUNT(*) jumlah_bantuan_aktif FROM bantuan WHERE blokir IS NULL AND status = 'D'");
        if ($this->_db->count()) {
            return $this->_db->result()->jumlah_bantuan_aktif;
        }
        return false;
    }

    public function jumlahBantuanSelesai() {
        $this->_db->query("SELECT COUNT(*) jumlah_bantuan_selesai FROM bantuan WHERE status = 'S'");
        if ($this->_db->count()) {
            return $this->_db->result()->jumlah_bantuan_selesai;
        }
        return false;
    }

    public function jumlahAkunTerblock() {
        $this->_db->query("SELECT IFNULL(SUM(id_akun), '0') jumlah_akun_terblock FROM akun WHERE aktivasi != '1'");
        if ($this->_db->count()) {
            return $this->_db->result()->jumlah_akun_terblock;
        }
        return false;
    }

    public function jumlahAkunAdmin() {
        $this->_db->query("SELECT COUNT(id_akun) jumlah_akun_admin FROM akun WHERE hak_akses = 'A'");
        if ($this->_db->count()) {
            return $this->_db->result()->jumlah_akun_admin;
        }
        return false;
    }

    public function setFilterBy($filter) {
        $this->_filter = $filter;
    }
}