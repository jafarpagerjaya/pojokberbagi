<?php 
class DonaturModel extends HomeModel {

    protected $interval_time = '3',
              $filter_time = 'MONTH',
              $last_fetch_id = '0';

    public function dataDonatur($halaman = null) {
        if (!is_null($halaman)) {
            $this->setHalaman($halaman, 'donatur');
        }
        $this->db->query("SELECT id_donatur, nama, email, IFNULL(kontak,'Belum Ada') kontak, create_at terdaftar_sejak, id_akun FROM donatur WHERE id_donatur BETWEEN ? AND ? ORDER BY {$this->getOrder()} {$this->getDirection()} LIMIT {$this->getLimit()}",
                            array($this->getHalaman()[0], $this->getHalaman()[1])
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
    //                         array($id_donatur, $this->getOrder(), $this->getDirection())
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
        $this->db->query("SELECT COUNT(id_akun) account_found, id_akun FROM donatur JOIN akun USING(id_akun) WHERE id_donatur = ?", array($id_donatur));
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

    public function getListDonaturOnBantuanDetil($params = array()) {
        if (!isset($params['offset'])) {
            $params['offset'] = $this->getOffset();
        }

        $this->setOffset($params['offset']);

        if (!isset($params['limit'])) {
            $params['limit'] = $this->getLimit();
        }

        $this->setLimit($params['limit']);

        $values = array(
            $params['id_bantuan']
        );

        if ($params['signin']) {
            $sql = "WITH cte AS (
                SELECT id_donasi FROM donasi WHERE id_bantuan = ? AND bayar = 1 ORDER BY waktu_bayar DESC, id_donasi DESC LIMIT {$params['offset']}, {$params['limit']}
            ) SELECT dn.id_donasi, dn.id_donatur, IFNULL(dn.alias, dt.nama) nama_donatur, FORMAT(dn.jumlah_donasi,0,'id_ID') jumlah_donasi, dn.doa, COUNT(a.id_donasi) liked, CONCAT('avatar ',dt.nama) nama_avatar, IFNULL(gd.path_gambar,IF(dt.jenis_kelamin IS NULL,'/assets/images/default.png',IF(dt.jenis_kelamin = 'P','/assets/images/female-avatar.jpg','/assets/images/male-avatar.jpg'))) path_avatar, IF(aa.id_donasi IS NOT NULL,1,0) checked
            FROM cte JOIN donasi dn USING(id_donasi) JOIN donatur dt USING(id_donatur) LEFT JOIN akun ak USING(id_akun) LEFT JOIN gambar gd USING(id_gambar)
            LEFT JOIN amin a ON(a.id_donasi = cte.id_donasi) LEFT JOIN (
                SELECT id_donasi FROM amin WHERE id_akun = ?
            ) aa ON(cte.id_donasi = aa.id_donasi)
            GROUP BY cte.id_donasi
            ORDER BY dn.waktu_bayar DESC, dn.id_donasi DESC";
            array_push($values, $params['id_akun']);
        } else {
            if (Cookie::exists(Config::get('client/cookie_name'))) {
                $cookie_value = Sanitize::thisArray(json_decode(base64_decode(Cookie::get(Config::get('client/cookie_name')) ?? ''), true));
            }

            if (isset($cookie_value['id_pengunjung'])) {
                $sql = "WITH cte AS (
                    SELECT id_donasi FROM donasi WHERE id_bantuan = ? AND bayar = 1 ORDER BY waktu_bayar DESC, id_donasi DESC LIMIT {$params['offset']}, {$params['limit']}
                ) SELECT dn.id_donasi, dn.id_donatur, IFNULL(dn.alias, dt.nama) nama_donatur, FORMAT(dn.jumlah_donasi,0,'id_ID') jumlah_donasi, dn.doa, COUNT(a.id_donasi) liked, CONCAT('avatar ',dt.nama) nama_avatar, IFNULL(gd.path_gambar,IF(dt.jenis_kelamin IS NULL,'/assets/images/default.png',IF(dt.jenis_kelamin = 'P','/assets/images/female-avatar.jpg','/assets/images/male-avatar.jpg'))) path_avatar, IF(aa.id_donasi IS NOT NULL,1,0) checked
                FROM cte JOIN donasi dn USING(id_donasi) JOIN donatur dt USING(id_donatur) LEFT JOIN akun ak USING(id_akun) LEFT JOIN gambar gd USING(id_gambar)
                LEFT JOIN amin a ON(a.id_donasi = cte.id_donasi) LEFT JOIN (
                    SELECT id_donasi FROM amin WHERE id_akun IS NULL AND id_pengunjung = ?
                ) aa ON(cte.id_donasi = aa.id_donasi)
                GROUP BY cte.id_donasi
                ORDER BY dn.waktu_bayar DESC, dn.id_donasi DESC";
                array_push($values, $cookie_value['id_pengunjung']);
            } else {
                $sql = "WITH cte AS (
                    SELECT id_donasi FROM donasi WHERE id_bantuan = ? AND bayar = 1 ORDER BY waktu_bayar DESC, id_donasi DESC LIMIT {$params['offset']}, {$params['limit']}
                ) SELECT dn.id_donasi, dn.id_donatur, IFNULL(dn.alias, dt.nama) nama_donatur, FORMAT(dn.jumlah_donasi,0,'id_ID') jumlah_donasi, dn.doa, COUNT(a.id_donasi) liked, CONCAT('avatar ',dt.nama) nama_avatar, IFNULL(gd.path_gambar,IF(dt.jenis_kelamin IS NULL,'/assets/images/default.png',IF(dt.jenis_kelamin = 'P','/assets/images/female-avatar.jpg','/assets/images/male-avatar.jpg'))) path_avatar, 0 checked
                FROM cte JOIN donasi dn USING(id_donasi) JOIN donatur dt USING(id_donatur) LEFT JOIN akun ak USING(id_akun) LEFT JOIN gambar gd USING(id_gambar)
                LEFT JOIN amin a ON(a.id_donasi = cte.id_donasi)
                GROUP BY cte.id_donasi
                ORDER BY dn.waktu_bayar DESC, dn.id_donasi DESC";
            }
        }

        $this->db->query($sql, $values);
        if (!$this->db->count()) {
            return false;
        }

        $this->data->offset = $this->getOffset() + $this->getLimit();
        $this->data->limit = $this->getLimit();
        $this->data->list_donatur = $this->db->results();

        $this->db->query("SELECT COUNT(*) jumlah_record FROM donasi WHERE id_bantuan = ? AND bayar = 1", array('id_bantuan' => $params['id_bantuan']));
        if (!$this->db->count()) {
            return false;
        }

        $this->data->jumlah_record = $this->db->result()->jumlah_record;
        return true;
    }
}