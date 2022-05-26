<?php 
class DonaturModel extends HomeModel {

    protected $interval_time = '3',
              $filter_time = 'MONTH',
              $last_fetch_id = '0';

    public function dataDonatur($halaman = null) {
        if (!is_null($halaman)) {
            $this->setHalaman($halaman, 'donatur');
        }
        $this->db->query('SELECT id_donatur, nama, email, IFNULL(kontak,"Belum Ada") kontak, create_at terdaftar_sejak, id_akun FROM donatur WHERE id_donatur BETWEEN ? AND ? ORDER BY ? ? LIMIT 10',
                            array($this->getHalaman()[0], $this->getHalaman()[1], $this->getOrderBy(), $this->getAscDsc())
                        );
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return $this->data;
        }
        return false;
    }

    // public function setPageLink() {
    //     $this->db->query("SELECT * 
    //     FROM ( 
    //         SELECT 
    //             @row := @row +1 AS rownum, id_donatur
    //         FROM ( 
    //             SELECT @row :=0) r, donatur 
    //         ) ranked 
    //     WHERE ranked.rownum % 9 = 1");

    //     if ($this->db->count()) {
    //         $this->data = $this->db->results();
    //         return $this->data;
    //     }
    //     return false;
    // }

    // public function dataDonatur($id_donatur = 1) {
    //     $id_donatur = Sanitize::escape($id_donatur);
    //     $this->db->query('SELECT id_donatur, nama, email, IFNULL(kontak,"Belum Ada") kontak, create_at terdaftar_sejak, id_akun FROM donatur WHERE id_donatur >= ? ORDER BY ? ? LIMIT 10',
    //                         array($id_donatur, $this->getOrderBy(), $this->getAscDsc())
    //                     );
    //     if ($this->db->count()) {
    //         $this->data = $this->db->results();
    //         return $this->data;
    //     }
    //     return false;
    // }

    public function donasiTerakhir($id_donatur) {
        $this->db->query('SELECT DATE_FORMAT(waktu_donasi, "%d-%m-%Y %H:%i:%s") waktu_donasi, nama FROM donasi JOIN bantuan USING(id_bantuan) WHERE id_donatur = ? AND bayar = 1 ORDER BY waktu_donasi LIMIT 1', array('id_donatur' => $id_donatur));
        if($this->db->count()) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    private function setFilterTime($time) {
        $this->filter_time = $time;
    }

    public function setFilterBy($filter = null, $jumlah_waktu = null) {
        if ($filter == 'bulan') {
            $this->setFilterTime('MONTH');
        } elseif ($filter == 'minggu') {
            $this->setFilterTime('WEEK');
        } elseif ($filter == 'hari') {
            $this->setFilterTime('DAY');
        } else {
            $this->setFilterTime(NULL);
        }
        $this->setFilterInterval($jumlah_waktu);
    }

    private function setFilterInterval($jumlah_interval) {
        $this->interval_time = $jumlah_interval;
    }

    public function getJumlahDonasiDonatur($id_donatur) {
        $data = $this->db->query("SELECT DATE_FORMAT(waktu_donasi,'%M') bulan, SUM(jumlah_donasi) FROM donasi WHERE id_donatur = ? AND waktu_donasi >= NOW()-INTERVAL ". $this->interval_time ." ".$this->filter_time." GROUP BY bulan", array('id_donatur' => $id_donatur));
        if ($data) {
            $this->data = $this->db->results();
            return $this->data;
        }
        return false;
    }

    // Donatur Method area
    public function countJumlahTagihan($status_bayar, $id_donatur) {
        $data = $this->db->query("SELECT COUNT('id_donasi') jumlah_tagihan FROM donasi WHERE bayar = ? AND id_donatur = ?", 
            array(
                'bayar' => Sanitize::escape($status_bayar), 
                'id_donatur' => Sanitize::escape($id_donatur)
            )
        );
        if ($data) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getTotalDonasi($id_donatur) {
        $data = $this->db->query("SELECT IFNULL(SUM('jumlah_donasi'), 0) jumlah_total_donasi FROM donasi WHERE bayar = '1' AND id_donatur = ?", 
            array(
                'id_donatur' => Sanitize::escape($id_donatur)
            )
        );
        if ($data) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function getJumlahDonasiTersalurkan($id_donatur) {
        $data = $this->db->query("SELECT COUNT(apd.id_donasi) jumlah_info_bantuan FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_donasi) WHERE d.bayar = '1' AND apd.id_pelaksanaan IS NOT NULL AND d.id_donatur = ?", 
            array(
                'id_donatur' => Sanitize::escape($id_donatur)
            )
        );
        if ($data) {
            $this->data = $this->db->result();
            return $this->data;
        }
        return false;
    }

    public function hasAccount($id_donatur) {
        $this->db->query("SELECT COUNT(id_donatur) account_found, id_akun FROM donatur WHERE id_donatur = ?", array($id_donatur));
        $this->data = $this->db->result();
        if ($this->data->account_found != 0) {
            return true;
        }
        return false;
    }

    public function isEmployee($id_akun) {
        $this->db->query("SELECT ad.id_pegawai FROM admin ad join akun ak USING(id_akun) WHERE ad.id_akun = ? AND UPPER(ak.hak_akses) = 'A'", array(Sanitize::escape2($id_akun))); 
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return true;
        }
        return false;
    }

    public function isPemohon($id_akun) {
        $this->db->query("SELECT pe.id_pemohon FROM akun ak join pemohon pe USING(id_akun) WHERE pe.id_akun = ? AND UPPER(ak.hak_akses) = 'P'", array(Sanitize::escape2($id_akun))); 
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return true;
        }
        return false;
    }
}