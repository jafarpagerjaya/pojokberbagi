<?php 
class PublikasiModel extends HomeModel {
    public function getsArtikelList($list_id_artikel = array()) {
        $fields = "IFNULL(cte.viewer,0) jumlah_kunjungan, a.id_artikel, a.judul, formatTanggalFull(a.publish_at) publish_at, aktif, a.modified_at, timeAgo(a.modified_at) time_ago,
        id_author, pa.nama nama_author, ja.nama jabatan_author, ga.path_gambar path_gambar_author,
        id_editor, pe.nama nama_editor, je.nama jabatan_editor, ge.path_gambar path_gambar_editor";
        $tables = "artikel a
        LEFT JOIN pegawai pa ON(pa.id_pegawai = a.id_author) LEFT JOIN akun aa ON(aa.email = pa.email) LEFT JOIN gambar ga ON(ga.id_gambar = aa.id_gambar) LEFT JOIN jabatan ja ON(ja.id_jabatan = pa.id_jabatan)
        LEFT JOIN pegawai pe ON(pe.id_pegawai = a.id_editor) LEFT JOIN akun ae ON(ae.email = pe.email) LEFT JOIN gambar ge ON(ge.id_gambar = ae.id_gambar) LEFT JOIN jabatan je ON(je.id_jabatan = pe.id_jabatan)";
        $params = array();
        $seek = 'a.id_artikel BETWEEN ? AND ?';
        $offset = '';
        $filter = '';

        if (count(is_countable($list_id_artikel) ? $list_id_artikel : []) > 0) {
            $questionMarks = '';
            $xCol = 1;
            
            foreach ($list_id_artikel as $value) {
                $questionMarks .= "?";

                array_push($params, $value);

                if ($xCol < count($list_id_artikel)) {
                    $questionMarks .= ", ";
                }
                $xCol++;
            }

            $filter = "a.id_artikel IN({$questionMarks})";
            $seek = '';
            $offset = " LIMIT {$this->getHalaman()[0]}, {$this->getHalaman()[1]}";
        }

        if (is_null($this->getSearch())) {
            if (is_array($this->data)) {
                $jumlah_record = $this->data[0]->jumlah_record;
            } else {
                $jumlah_record = $this->data->jumlah_record;
            }
            $sql = "WITH cte AS (SELECT a.id_artikel, COUNT(DISTINCT(id_pengunjung)) viewer FROM artikel a LEFT JOIN kunjungan_artikel USING(id_artikel) WHERE {$filter} {$seek} GROUP BY a.id_artikel ORDER BY a.id_artikel DESC {$offset}) 
                    SELECT {$fields} FROM {$tables} JOIN cte ON (cte.id_artikel = a.id_artikel) ORDER BY a.id_artikel DESC";
            if (count($list_id_artikel) == 0) {
                $params = array_merge($params, $this->getHalaman());
            }
        } else {
            if (count(is_countable($list_id_artikel) ? $list_id_artikel : []) > 0) {
                $filter .= " AND"; 
            }
            $filter .= " LOWER(CONCAT( 
                IFNULL(cte.viewer,0), IFNULL(a.id_artikel,''), IFNULL(a.judul,''), formatTanggalFull(a.publish_at), IF(a.aktif = '1','aktif','non-'), IFNULL(timeAgo(a.modified_at),''),
                IFNULL(id_author,''), IFNULL(pa.nama,''), IFNULL(ja.nama,''), IFNULL(ga.path_gambar,''),
                IF(a.id_editor IS NOT NULL,'editor',''), IFNULL(pe.nama,''), IFNULL(je.nama,''), IFNULL(ge.path_gambar,'')
            )) LIKE LOWER(CONCAT('%',?,'%'))";
            $tables .= " LEFT JOIN (SELECT a.id_artikel, COUNT(DISTINCT(id_pengunjung)) viewer FROM artikel a LEFT JOIN kunjungan_artikel ka USING(id_artikel) GROUP BY a.id_artikel) cte USING(id_artikel)";
            array_push($params, $this->getSearch());
            $jumlah_record = $this->countData($tables, null, array($filter, $params))->jumlah_record;
            $filter = 'WHERE '. $filter;
            $sql = "SELECT {$fields} FROM {$tables} {$filter} ORDER BY a.id_artikel DESC LIMIT {$this->getOffset()}, {$this->getLimit()}";
        }

        $this->db->query($sql, $params);

        $dataArtikel = $this->db->results();
        $this->data = array(
            'data' => $dataArtikel,
            'total_record' => $jumlah_record,
            'limit' => $this->getLimit(),
            'pages' => ceil($jumlah_record/$this->getLimit())
        );

        if (!is_null($this->getSearch())) {
            $this->data['search'] = $this->getSearch();
        }

        return true;
    }

    public function isArtikelNotNull($list_id_artikel = array()) {
        $countID = count(is_countable($list_id_artikel) ? $list_id_artikel : []);
        if ($countID > 0) {
            if ($countID > 1) {
                $operator = 'IN';
            } else {
                $operator = '=';
            }
            $this->db->get("COUNT(id_artikel) jumlah_konten_kosong","artikel",array("id_artikel",$operator,$list_id_artikel),"AND",array("isi","IS",NULL));
            if ($this->db->count()) {
				if ($this->db->result()->jumlah_konten_kosong == 0) {
                    return true;
                }
            }
            return false;
        }
    }

    public function getArtikelYearDateList() {
        $this->db->get("DISTINCT(YEAR(publish_at)) tahun","artikel",array("aktif","=","1"));
        return $this->db->results();
    }

    public function getsCardArtikelList($filter = array(), $newListId = array()) {
        $params = array();
        $filters_stmt = '';
        $questionMarkNew = 'NULL';
        if (count($filter) > 0) {
            if (array_key_exists('years', $filter)) {
                $params = array_merge($params, $filter['years']);
                $years_question_mark = implode(",", array_map(function($value) { return '?'; }, $filter['years']));
                $filters_stmt = "AND YEAR(artikel.publish_at) IN ({$years_question_mark})";
            }
        }

        if (count(is_countable($newListId) ? $newListId : []) > 0) {
            $questionMarkNew = implode(",", array_map(function($value) { return '?'; }, $newListId));
            $params = array_merge($newListId, $params);
            if (count($filter) > 0) {
                if (array_key_exists('years', $filter)) {
                    $params = array_merge($params, $filter['years']);
                }
            }
        }

        $sql = "WITH cte AS (
            (SELECT id_artikel FROM artikel WHERE aktif = '1' AND id_artikel IN ($questionMarkNew) {$filters_stmt} ORDER BY 1 DESC)
            UNION
            (SELECT id_artikel FROM artikel WHERE aktif = '1' {$filters_stmt} ORDER BY 1 DESC LIMIT {$this->getOffset()}, {$this->getLimit()})
            ORDER BY 1 DESC
        ) 
        SELECT cte.id_artikel, judul, LOWER(REPLACE(TRIM(judul), ' ', '-')) link, IFNULL(path_gambar,'') path_gambar, IFNULL(nama, judul) nama_gambar, timeAgo(artikel.publish_at) time_ago
        FROM artikel JOIN cte USING(id_artikel) LEFT JOIN gambar ON(gambar.id_gambar = artikel.id_gambar_small) ORDER BY 1 DESC";

        $this->db->query($sql, $params);
        
        $dataArtikel = $this->db->results();

        $this->db->query("SELECT COUNT(*) jumlah_record FROM artikel WHERE aktif = '1' {$filters_stmt}");

        $jumlah_record = $this->db->result()->jumlah_record;

        $this->data = array(
            'data' => $dataArtikel,
            'total_record' => $jumlah_record,
            'load_more' => ((int) $this->getOffset() + (int) $this->getLimit() < $jumlah_record),
            'offset' => (int) $this->getOffset() + (int) $this->getLimit()
        );

        if (!is_null($this->getSearch())) {
            $this->data['search'] = $this->getSearch();
        }

        return true;
    }

    public function setPublikasiKunjungan($table, $deviceId, $id_key) {
        $this->db->query("SELECT k.id_pengunjung FROM pengunjung JOIN kunjungan_{$table} k USING(id_pengunjung) WHERE pengunjung.device_id = ? AND id_artikel = ? ORDER BY 1 DESC", array($deviceId, $id_key));
        if (!$this->db->count()) {
                return false;
            }
        return true;
    }

    public function getDataKontenArtikel($id_artikel) {
        $this->db->get(
            'judul, gw.path_gambar path_gambar_wide, gw.nama nama_gambar_wide, pa.nama nama_author, FormatTanggal(publish_at) publish_at, id_editor, artikel.modified_at, COUNT(DISTINCT(k.id_pengunjung)) jumlah_pengunjung, artikel.isi',
            'artikel LEFT JOIN gambar gw ON(gw.id_gambar = artikel.id_gambar_wide) LEFT JOIN pegawai pa ON(pa.id_pegawai = artikel.id_author) LEFT JOIN kunjungan_artikel k USING(id_artikel)',
            array('artikel.id_artikel','=',Sanitize::toInt2($id_artikel)), "AND", array('artikel.aktif','=','1'));
        if ($this->db->count()) {
            $this->data = $this->db->result();
            return true;
        }
        return false;
    }
}