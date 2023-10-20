<?php
class KebutuhanModel extends HomeModel {

    public function readKebutuhanRab($id_rencana, $search = array()) {
        $params = array(
            Sanitize::escape2($id_rencana)
        );
        $searchColumns = '';
        if (!empty($search)) {
            $searchColumns = $search['search_column'];
            array_push($params, $search['search_value']);
        }
        $this->query("SELECT k.id_kebutuhan, k.nama, IFNULL(kk.nama,'') kategori, IFNULL(k_rab.jumlah_item_rab_ini,0) jumlah_item_rab_ini 
        FROM kebutuhan k LEFT JOIN kategori_kebutuhan kk USING(id_kk) 
        LEFT JOIN 
        (
            SELECT id_kebutuhan, COUNT(id_rab) jumlah_item_rab_ini 
            FROM rencana_anggaran_belanja 
            WHERE id_rencana = ? 
            GROUP BY id_kebutuhan
        ) k_rab ON(k_rab.id_kebutuhan = k.id_kebutuhan) {$searchColumns}", $params);

        if (!$this->affected()) {
            return false;
        }

        return true;
    }

    public function countKebutuhanRab($id_rencana, $search) {
        $params = array(
            Sanitize::escape2($id_rencana)
        );
        $searchColumns = '';
        if (!empty($search)) {
            $searchColumns = $search['search_column'];
            array_push($params, $search['search_value']);
        }
        $this->query("SELECT COUNT(k.id_kebutuhan) jumlah_record
        FROM kebutuhan k LEFT JOIN kategori_kebutuhan kk USING(id_kk) 
        LEFT JOIN 
        (
            SELECT id_kebutuhan, COUNT(id_rab) jumlah_item_rab_ini 
            FROM rencana_anggaran_belanja 
            WHERE id_rencana = ? 
            GROUP BY id_kebutuhan
        ) k_rab ON(k_rab.id_kebutuhan = k.id_kebutuhan) {$searchColumns}", $params);

        if (!$this->affected()) {
            return false;
        }

        return true;
    }
}