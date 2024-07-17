<?php
class CampaignModel extends HomeModel {
    public function readInformasiCampaign() {
        $fields = "c.id_campaign, c.aktif, c.id_bantuan, b.tag, b.nama nama_bantuan, b.status, c.modified_at, timeAgo(c.modified_at) time_ago, c.id_akun_maker, CASE WHEN a.hak_akses = 'M' THEN 'Digital Marketing' ELSE j.nama END jabatan_author, IFNULL(d.nama, p.nama) nama_author, g.path_gambar path_author";
        $tables = "campaign c LEFT JOIN bantuan b USING(id_bantuan) LEFT JOIN akun a ON(a.id_akun = c.id_akun_maker) LEFT JOIN donatur d ON(d.id_akun = a.id_akun) LEFT JOIN gambar g USING(id_gambar) LEFT JOIN admin adm ON(a.id_akun = adm.id_akun) LEFT JOIN pegawai p USING(id_pegawai) LEFT JOIN jabatan j USING(id_jabatan)";
        $params = array();
        $filter = '';

        if (is_null($this->getSearch())) {
            $jumlah_record = $this->data->jumlah_record;
            $sql = "WITH cte AS (SELECT id_campaign FROM campaign WHERE id_campaign BETWEEN ? AND ? ORDER BY id_campaign DESC) 
                    SELECT {$fields} FROM {$tables} RIGHT JOIN cte ON (cte.id_campaign = c.id_campaign) ORDER BY c.id_campaign DESC";
            $params = $this->getHalaman();
        } else {
            $filter = "LOWER(CONCAT(IF(c.aktif = '1','aktif','non-aktif'), IFNULL(b.tag,''), IFNULL(b.nama,''), IFNULL(timeAgo(c.modified_at),''), IFNULL(CASE WHEN a.hak_akses = 'M' THEN 'Digital Marketing' ELSE j.nama END,''), IFNULL(d.nama,'') )) LIKE LOWER(CONCAT('%',?,'%'))";
            array_push($params, $this->getSearch());
            $jumlah_record = $this->countData($tables, null, array($filter, $this->getSearch()))->jumlah_record;
            $filter = 'WHERE '. $filter;
            $sql = "SELECT {$fields} FROM {$tables} {$filter} ORDER BY c.id_campaign DESC LIMIT {$this->getOffset()}, {$this->getLimit()}";
        }

        $this->db->query($sql, $params);

        $dataCampaign = $this->db->results();
        $this->data = array(
            'data' => $dataCampaign,
            'total_record' => $jumlah_record,
            'limit' => $this->getLimit(),
            'pages' => ceil($jumlah_record/$this->getLimit())
        );

        if (!is_null($this->getSearch())) {
            $this->data['search'] = $this->getSearch();
        }

        return true;
    }

    public function isMarketer($email) {
        $this->db->get('id_marketing','marketing JOIN akun USING(id_akun)',array('email','=',strtolower(Sanitize::escape2($email))));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return true;
        }

        return false;
    }

    public function getInfoCampaign($tag, $marketing) {
        $this->db->query("SELECT 
            tdtcs.*, 
            (
                SELECT COUNT(id_pengunjung) 
                FROM kunjungan JOIN halaman USING(id_halaman)
                WHERE uri LIKE CONCAT('donasi/buat/',?,'%') AND uri LIKE CONCAT('%','inbound-marketing')
            ) total_cta,
            (
                SELECT COUNT(id_pengunjung)  
                FROM kunjungan JOIN halaman USING(id_halaman)
                WHERE uri = CONCAT('campaign/',?)
            ) total_kunjungan
        FROM 
        (
            SELECT IFNULL(SUM(d.jumlah_donasi),0) total_donasi, COUNT(d.id_donasi) total_cta_closing 
            FROM donasi d JOIN bantuan b ON(d.id_bantuan = b.id_bantuan) LEFT JOIN campaign c ON(c.id_bantuan = b.id_bantuan) LEFT JOIN marketing m ON(m.id_marketing = d.id_marketing)
            WHERE d.bayar = '1' AND b.tag = ? AND m.id_marketing = ?
        ) tdtcs", array($tag, $tag, $tag, $marketing));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return true;
        }

        return false;
    }

    public function getDataCampaign($tag) {
        $this->db->get(
            "gw.nama nama_gambar_wide, gw.path_gambar path_gambar_wide, gm.nama nama_gambar_medium, gm.path_gambar path_gambar_medium",
            "campaign c JOIN bantuan b USING(id_bantuan) LEFT JOIN gambar gw ON(gw.id_gambar = b.id_gambar_wide) LEFT JOIN gambar gm ON(gm.id_gambar = b.id_gambar_medium)",
            array('b.tag', '=', Sanitize::escape2($tag)));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return true;
        }

        return false;
    }

    public function readCampaignKujungan($tag) {
        $tag = Sanitize::escape2($tag);
        $sql = "SELECT pengunjung.device_type, COUNT(id_pengunjung) kunjungan, COUNT(DISTINCT(id_pengunjung)) kunjungan_unik, IFNULL(ROUND(one_time_visit/COUNT(id_pengunjung)*100,2),0) bounch_rate
        FROM kunjungan JOIN halaman USING(id_halaman) JOIN pengunjung USING(id_pengunjung) LEFT JOIN (
            SELECT SUM(total_kunjungan) one_time_visit, device_type
            FROM (
                SELECT COUNT(k2.id_pengunjung) total_kunjungan, pengunjung.id_pengunjung, pengunjung.device_type
                FROM kunjungan JOIN halaman USING(id_halaman) JOIN pengunjung USING(id_pengunjung) JOIN kunjungan k2 ON(k2.id_pengunjung = pengunjung.id_pengunjung)
                WHERE uri = CONCAT('campaign/',?)
                GROUP BY pengunjung.id_pengunjung, device_type
                HAVING total_kunjungan < 2
            ) br
            GROUP BY device_type
        ) br_kunjungan ON (br_kunjungan.device_type = pengunjung.device_type)
        WHERE uri = CONCAT('campaign/',?)
        GROUP BY pengunjung.device_type, one_time_visit";
        $this->db->query($sql, array($tag, $tag));
        // $this->db->query("SELECT device_type, COUNT(id_pengunjung) jumlah_kunjungan, COUNT(DISTINCT(id_pengunjung)) jumlah_pengunjung
        // FROM kunjungan JOIN halaman USING(id_halaman) JOIN pengunjung USING(id_pengunjung)
        // WHERE uri = CONCAT('campaign/',?)
        // GROUP BY device_type", array('uri' => Sanitize::escape2($tag)));
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return true;
        }

        return false;
    }
}