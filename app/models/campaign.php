<?php
class CampaignModel extends HomeModel {
    public function readInformasiCampaign() {
        $fields = "c.id_campaign, c.aktif, c.id_bantuan, b.tag, b.nama nama_bantuan, b.status, c.modified_at, timeAgo(c.modified_at) time_ago, c.id_akun_maker, CASE WHEN a.hak_akses = 'M' THEN 'Digital Marketing' ELSE j.nama END jabatan_author, d.nama nama_author, g.path_gambar path_author";
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
}