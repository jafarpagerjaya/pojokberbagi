<?php
class SysModel {
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

    public function jumlahBantuanAktif() {
        $this->_db->query("SELECT COUNT(*) jumlah_bantuan FROM bantuan WHERE blokir IS NULL AND status = 'D'");
        if ($this->_db->count()) {
            return $this->_db->result()->jumlah_bantuan;
        }
        return false;
    }

    public function setFilterBy($filter) {
        $this->_filter = $filter;
    }
}