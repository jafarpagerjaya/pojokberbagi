<?php 
class InformasiModel extends HomeModel {
    
    public function getListInformasi($filter_by, $decoded) {
        if (!empty($filter_by)) {
            $params = array(
                $decoded['filter_value'], 
                $decoded['id_bantuan']
            );
        } else {
            $params = array(
                $decoded['id_bantuan']
            );
        }
        
        $this->db->query("SELECT i.id_informasi, i.label, i.judul, DATE_FORMAT(i.modified_at, '%Y-%m-%d') tanggal_publikasi, FormatTanggal(i.modified_at) waktu_publikasi, i.id_author, i.id_editor, i.isi FROM informasi i WHERE {$filter_by} i.id_bantuan = ? ORDER BY i.modified_at DESC LIMIT {$this->getOffset()}, {$this->getLimit()}", $params);
        if (!$this->db->count()) {
            return false;
        }

        $return['data'] = $this->db->results();

        $list_id = array_column($return['data'],'id_informasi');

        if (count($list_id)) {
            $questionMarks = '';
            $xCol = 1;

            foreach ($list_id as $questionMark) {
                $questionMarks .= "?";
                if ($xCol < count($list_id)) {
                    $questionMarks .= ", ";
                }
                $xCol++;
            }
        }

        if (!empty($filter_by)) {
            $countValues = array(
                $decoded['filter_value'], 
                $decoded['id_bantuan']
            );
            $result = $this->countData("informasi i", array("{$filter_by} i.id_bantuan = ?", $countValues));
        } else {
            $countValues = array(
                $decoded['id_bantuan'],
            );
            $result = $this->countData("informasi i", array("i.id_bantuan = ?", $countValues));
        }

        $return['record'] = $result->jumlah_record;
        $return['load_more'] = ($return['record'] > ($this->getOffset() + $this->getLimit()) ? true : false);
        $return['offset'] = $this->getOffset();
        $return['limit'] = $this->getLimit();

        $this->data = $return;
        return $this->data;
    }

    public function getCurrentListId($filter_by, $decoded, $list_id) {
        $params = $list_id;

        if (!empty($filter_by)) {
            $params = array(
                $decoded['filter_value'], 
                $decoded['id_bantuan'],
            );
        } else {
            $params = array(
                $decoded['id_bantuan'],
            );
        }

        $copyParams = $params;
        $copyMarkValue = array();

        if (count($list_id)) {
            $questionMarks = '';
            $xCol = 1;
            
            foreach ($list_id as $questionMark) {
                $questionMarks .= "?";

                array_push($params, $questionMark);
                array_push($copyMarkValue, $questionMark);

                if ($xCol < count($list_id)) {
                    $questionMarks .= ", ";
                }
                $xCol++;
            }
        }

        $params = array_merge($params, $copyParams);
        $params = array_merge($params, $copyMarkValue);

        // $sql = "SELECT * FROM (
        //     (SELECT i.id_informasi FROM informasi i WHERE {$filter_by} i.id_bantuan = ? AND i.id_informasi > ANY (SELECT MAX(id_informasi) FROM informasi WHERE id_informasi IN({$questionMarks}) AND /* i.id_editor IS NOT NULL */) /* i.id_editor IS NOT NULL */ ORDER BY 1 ASC LIMIT {$this->getLimit()})
        //         UNION
        //     (SELECT i.id_informasi FROM informasi i WHERE {$filter_by} i.id_bantuan = ? AND i.id_informasi IN ({$questionMarks}) AND /* i.id_editor IS NOT NULL */ ORDER BY 1 DESC)
        // ) b ORDER BY 1 DESC";

        $sql = "SELECT * FROM (
            (SELECT i.id_informasi FROM informasi i WHERE {$filter_by} i.id_bantuan = ? AND i.id_informasi > ANY (SELECT MAX(id_informasi) FROM informasi WHERE id_informasi IN({$questionMarks})) ORDER BY 1 ASC LIMIT {$this->getLimit()})
                UNION
            (SELECT i.id_informasi FROM informasi i WHERE {$filter_by} i.id_bantuan = ? AND i.id_informasi IN ({$questionMarks}) ORDER BY 1 DESC)
        ) b ORDER BY 1 DESC";

        $this->db->query($sql, $params);
        if (!$this->db->count()) {
            return false;
        }

        return $this->db->results();
    }

    public function getListInformasiById($filter_by, $decoded, $list_id) {
        if (!empty($filter_by)) {
            $params = array(
                $decoded['filter_value'], 
                $decoded['id_bantuan'],
            );
        } else {
            $params = array(
                $decoded['id_bantuan'],
            );
        }
        
        if (count($list_id)) {
            $questionMarks = '';
            $xCol = 1;
            
            foreach ($list_id as $questionMark) {
                $questionMarks .= "?";

                array_push($params, $questionMark);

                if ($xCol < count($list_id)) {
                    $questionMarks .= ", ";
                }
                $xCol++;
            }
        }

        $this->db->query("SELECT * FROM (SELECT i.id_informasi, i.label, i.judul, DATE_FORMAT(i.modified_at, '%Y-%m-%d') tanggal_publikasi, FormatTanggal(i.modified_at) waktu_publikasi, i.id_author, i.id_editor, i.isi FROM informasi i WHERE {$filter_by} i.id_bantuan = ? AND i.id_informasi IN ({$questionMarks}) ORDER BY i.modified_at ASC LIMIT {$this->getLimit()}) b ORDER BY id_informasi DESC", $params);
        if (!$this->db->count()) {
            return false;
        }

        return $this->db->results();
    }
}