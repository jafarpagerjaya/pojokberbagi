<?php
class RabModel extends HomeModel  {
    private $_between;

    public function getRencana($id_rencana) {
        $this->getData(
            'b.nama nama_bantuan, FORMAT(r.total_anggaran,0,"id_ID") total_anggaran, IFNULL(r.keterangan,"Tanpa keterangan") keterangan, p.nama nama_pembuat, p.id_pegawai, r.status, FormatTanggalFull(r.create_at) create_at',
            'rencana r JOIN bantuan b USING(id_bantuan) LEFT JOIN pegawai p ON(p.id_pegawai = r.id_pegawai)', 
            array('r.id_rencana','=', Sanitize::escape2($id_rencana))
        );
        if (!$this->affected()) {
            return false;
        }
        $this->data = $this->getResult();
        return true;
    }

    public function getRencanaDetil($id_rencana) {
        $this->query("WITH cte AS (
            SELECT * FROM rencana WHERE id_rencana = ?
        )
        SELECT k.*, IFNULL(j.total_teranggarkan,0) total_teranggarkan FROM
        (
            SELECT b.id_bantuan, FORMAT(SUM(saldo),0,'id_ID') max_anggaran, b.nama nama_bantuan, FORMAT(cte.total_anggaran,0,'id_ID') total_anggaran, keterangan, p.nama nama_pembuat, p.id_pegawai, cte.status, FormatTanggalFull(cte.create_at) create_at, FormatTanggalFull(cte.modified_at) modified_at
            FROM
            (
            SELECT d.id_bantuan, d.id_donasi, IFNULL(MIN(apd.saldo_donasi), d.jumlah_donasi) saldo 
            FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_donasi)
            GROUP BY d.id_donasi
            ) l JOIN bantuan b ON(b.id_bantuan = l.id_bantuan)
            JOIN cte ON (cte.id_bantuan = b.id_bantuan)
            LEFT JOIN pegawai p ON(p.id_pegawai = cte.id_pegawai)
            WHERE b.blokir IS NULL AND UPPER(b.status) = 'D'
            GROUP BY b.id_bantuan
        ) k LEFT JOIN (
            SELECT SUM(apd.nominal_penggunaan_donasi) total_teranggarkan, cte.id_bantuan
            FROM anggaran_pelaksanaan_donasi apd JOIN pelaksanaan pl USING(id_pelaksanaan) JOIN cte USING(id_rencana)
        ) j ON(k.id_bantuan = j.id_bantuan)", array(Sanitize::escape2($id_rencana)));

        if (!$this->affected()) {
            return false;
        }
        $this->data = $this->getResult();
        return true;
    }

    public function getRabList($id_rencana, $teranggarkan = null) {
        if (!is_null($teranggarkan)) {
            $teranggarkan = Sanitize::toInt2($teranggarkan);
            $this->query('WITH cte AS
            (	
                SELECT
                rab.id_rab, k.nama nama_kebutuhan, IFNULL(rab.keterangan,"") keterangan, FORMAT(rab.harga_satuan,0,"id_ID") harga_satuan, FORMAT(rab.jumlah,0,"id_ID") jumlah, rab.nominal_kebutuhan nominal_kebutuhan
                FROM rencana r JOIN rencana_anggaran_belanja rab USING(id_rencana) LEFT JOIN kebutuhan k USING(id_kebutuhan) 
                WHERE rab.id_rencana = ?
            ) 
            (
                SELECT c1.id_rab, c1.nama_kebutuhan, c1.keterangan, c1.harga_satuan, c1.jumlah, FORMAT(c1.nominal_kebutuhan,0,"id_ID") nominal_kebutuhan, 1 teranggarkan 
                FROM cte c1 JOIN cte c2 ON(c1.id_rab >= c2.id_rab)
                GROUP BY c1.id_rab
                HAVING SUM(c2.nominal_kebutuhan) <= ?
                ORDER BY c1.id_rab
            ) UNION (
                SELECT c1.id_rab, c1.nama_kebutuhan, c1.keterangan, c1.harga_satuan, c1.jumlah, FORMAT(c1.nominal_kebutuhan,0,"id_ID") nominal_kebutuhan, 0 teranggarkan 
                FROM cte c1 JOIN cte c2 ON(c1.id_rab >= c2.id_rab)
                GROUP BY c1.id_rab
                HAVING SUM(c2.nominal_kebutuhan) > ?
                ORDER BY c1.id_rab
            )', array(
                Sanitize::escape2($id_rencana),
                $teranggarkan,
                $teranggarkan
            ));
        } else {
            $this->getData(
                'rab.id_rab, k.nama nama_kebutuhan, IFNULL(rab.keterangan,"") keterangan, FORMAT(rab.harga_satuan,0,"id_ID") harga_satuan, FORMAT(rab.jumlah,0,"id_ID") jumlah, FORMAT(rab.nominal_kebutuhan,0,"id_ID") nominal_kebutuhan',
                'rencana r JOIN rencana_anggaran_belanja rab USING(id_rencana) LEFT JOIN kebutuhan k USING(id_kebutuhan)', 
                array('r.id_rencana','=',Sanitize::escape2($id_rencana))
            );
        }
        
        if (!$this->affected()) {
            return false;
        }
        $this->data = $this->getResults();
        return true;
    }

    public function getListRencana() {
        $this->_between = $this->getHalaman();

        if (is_null($this->getSearch())) {
            $data = $this->countData('rencana');
            $result['total_record'] = $data->jumlah_record;

            $this->query("WITH cte AS (
                SELECT pl.id_pelaksanaan, r.id_rencana, COUNT(rab.id_rab) jumlah_item
                FROM rencana r JOIN bantuan b  USING(id_bantuan) LEFT JOIN rencana_anggaran_belanja rab USING(id_rencana) 
                LEFT JOIN pelaksanaan pl USING(id_rencana)
                WHERE r.id_rencana BETWEEN ? AND ?
                GROUP BY r.id_rencana, pl.id_pelaksanaan
                ORDER BY r.create_at DESC
            )
            SELECT 
            SUM(IF(pn.status = '1', nominal, 0)) total_penarikan,
            IFNULL(b.banyak_penarikan,0) banyak_penarikan, IFNULL(b.banyak_pengadaan,0) banyak_pengadaan, IFNULL(b.total_pengadaan,0) total_pengadaan, cte.id_pelaksanaan, cte.id_rencana, CONCAT(cte.jumlah_item, ' item') jumlah_item, rn.id_rencana, CONCAT('Rp. ',FORMAT(rn.total_anggaran,0,'id_ID')) total_anggaran, rn.status status_rencana, rn.keterangan keterangan_rencana, formatTanggal(rn.create_at) create_at_rencana, bn.nama nama_bantuan, pl.status status_pelaksanaan,
            IF(cte.id_pelaksanaan IS NULL, 
                'Perencanaan', 
                IF(pl.status = 'S',
                    'Selesai',
                    IF(pn.id_pelaksanaan IS NULL, 
                        'Persiapan', 
                        IF(SUM(IF(pn.status = '1', nominal, 0)) <> rn.total_anggaran AND b.total_pengadaan IS NULL, 
                            'Pencairan', 
                            IF(b.total_pengadaan < SUM(IF(pn.status = '1', nominal, 0)) AND rn.total_anggaran <> SUM(IF(pn.status = '1', nominal, 0)), 
                                'Pengadaan Lanjutan',
                                IF(b.total_pengadaan < SUM(IF(pn.status = '1', nominal, 0)) AND SUM(IF(pn.status = '1', nominal, 0)) = rn.total_anggaran,
                                    'Pengadaan',
                                    IF(b.total_pengadaan = SUM(IF(pn.status = '1', nominal, 0)) AND rn.total_anggaran <> SUM(IF(pn.status = '1', nominal, 0)),
                                        'Pencairan Lanjutan',
                                        'Eksekusi'))))))) tahap
            FROM cte JOIN rencana rn ON(rn.id_rencana = cte.id_rencana) JOIN bantuan bn USING(id_bantuan) LEFT JOIN pelaksanaan pl ON(pl.id_pelaksanaan = cte.id_pelaksanaan) LEFT JOIN penarikan pn ON (pn.id_pelaksanaan = cte.id_pelaksanaan)
            LEFT JOIN
            (
                SELECT id_pelaksanaan, COUNT(id_penarikan) banyak_penarikan, SUM(jumlah_pengadaan) banyak_pengadaan, SUM(total_pengadaan) total_pengadaan
                FROM
                (
                    SELECT id_penarikan, id_pelaksanaan, COUNT(pg.id_pengadaan) jumlah_pengadaan, SUM(pg.nominal) total_pengadaan
                    FROM penarikan pn JOIN cte USING(id_pelaksanaan) LEFT JOIN penyerahan py USING(id_penarikan) LEFT JOIN pengadaan pg USING(id_pengadaan)
                    WHERE status = '1' AND id_pengadaan IS NOT NULL
                    GROUP BY id_pelaksanaan, id_penarikan
                ) a
                GROUP BY a.id_pelaksanaan
            ) b ON (b.id_pelaksanaan = cte.id_pelaksanaan)
            GROUP BY cte.id_pelaksanaan, b.banyak_penarikan, cte.id_rencana
            ORDER BY rn.id_rencana DESC", array(
                $this->_between[0],
                $this->_between[1]
            ));            
        } else {
            $search = "CONCAT_WS(' ', (SELECT SUM(IF(pn.status = '1', nominal, 0))), b.id_pelaksanaan, b.banyak_penarikan, b.banyak_pengadaan, b.total_pengadaan, cte.id_pelaksanaan, cte.id_rencana, CONCAT(cte.jumlah_item, ' item'), rn.id_rencana, CONCAT('Rp. ',FORMAT(rn.total_anggaran,0,'id_ID')), rn.status, rn.keterangan, formatTanggal(rn.create_at), bn.nama, pl.status, 
            IF(cte.id_pelaksanaan IS NULL, 
                'Perencanaan', 
                IF(pl.status = 'S',
                    'Selesai',
                    IF(pn.id_pelaksanaan IS NULL, 
                        'Persiapan', 
                        IF((SELECT SUM(IF(pn.status = '1', nominal, 0))) <> rn.total_anggaran AND b.total_pengadaan IS NULL, 
                            'Pencairan', 
                            IF(b.total_pengadaan < (SELECT SUM(IF(pn.status = '1', nominal, 0))) AND rn.total_anggaran <> (SELECT SUM(IF(pn.status = '1', nominal, 0))), 
                                'Pengadaan Lanjutan',
                                IF(b.total_pengadaan < (SELECT SUM(IF(pn.status = '1', nominal, 0))) AND (SELECT SUM(IF(pn.status = '1', nominal, 0))) = rn.total_anggaran,
                                    'Pengadaan',
                                    IF(b.total_pengadaan = (SELECT SUM(IF(pn.status = '1', nominal, 0))) AND rn.total_anggaran <> (SELECT SUM(IF(pn.status = '1', nominal, 0))),
                                        'Pencairan Lanjutan',
                                        'Eksekusi')))))))) LIKE '%{$this->getSearch()}%'";

            $this->query("WITH cte AS (
                SELECT pl.id_pelaksanaan, r.id_rencana, COUNT(rab.id_rab) jumlah_item
                FROM rencana r JOIN bantuan b  USING(id_bantuan) LEFT JOIN rencana_anggaran_belanja rab USING(id_rencana) 
                LEFT JOIN pelaksanaan pl USING(id_rencana)
                GROUP BY r.id_rencana, pl.id_pelaksanaan
                ORDER BY r.create_at DESC
            ) SELECT COUNT(*) jumlah_record
            FROM (
                SELECT COUNT(cte.id_rencana) 
                FROM cte JOIN rencana rn ON(rn.id_rencana = cte.id_rencana) JOIN bantuan bn USING(id_bantuan) LEFT JOIN pelaksanaan pl ON(pl.id_pelaksanaan = cte.id_pelaksanaan) LEFT JOIN penarikan pn ON (pn.id_pelaksanaan = cte.id_pelaksanaan)
                LEFT JOIN
                (
                    SELECT id_pelaksanaan, COUNT(id_penarikan) banyak_penarikan, SUM(jumlah_pengadaan) banyak_pengadaan, SUM(total_pengadaan) total_pengadaan
                    FROM
                    (
                        SELECT id_penarikan, id_pelaksanaan, COUNT(pg.id_pengadaan) jumlah_pengadaan, SUM(pg.nominal) total_pengadaan
                        FROM penarikan pn JOIN cte USING(id_pelaksanaan) LEFT JOIN penyerahan py USING(id_penarikan) LEFT JOIN pengadaan pg USING(id_pengadaan)
                        WHERE status = '1' AND id_pengadaan IS NOT NULL
                        GROUP BY id_pelaksanaan, id_penarikan
                    ) a
                    GROUP BY a.id_pelaksanaan
                ) b ON (b.id_pelaksanaan = cte.id_pelaksanaan)
                WHERE {$search}
                GROUP BY cte.id_pelaksanaan, b.banyak_penarikan, cte.id_rencana
                ORDER BY rn.id_rencana
            ) row_result", array());

            $result['total_record'] = $this->getResult()->jumlah_record;

            $this->query("WITH cte AS (
                SELECT pl.id_pelaksanaan, r.id_rencana, COUNT(rab.id_rab) jumlah_item
                FROM rencana r JOIN bantuan b  USING(id_bantuan) LEFT JOIN rencana_anggaran_belanja rab USING(id_rencana) 
                LEFT JOIN pelaksanaan pl USING(id_rencana)
                GROUP BY r.id_rencana, pl.id_pelaksanaan
                ORDER BY r.create_at DESC
            )
            SELECT 
            SUM(IF(pn.status = '1', nominal, 0)) total_penarikan,
            IFNULL(b.banyak_penarikan,0) banyak_penarikan, IFNULL(b.banyak_pengadaan,0) banyak_pengadaan, IFNULL(b.total_pengadaan,0) total_pengadaan, 
            cte.id_pelaksanaan, cte.id_rencana, CONCAT(cte.jumlah_item, ' item') jumlah_item, rn.id_rencana, CONCAT('Rp. ',FORMAT(rn.total_anggaran,0,'id_ID')) total_anggaran, rn.status status_rencana, rn.keterangan keterangan_rencana, formatTanggal(rn.create_at) create_at_rencana, bn.nama nama_bantuan, pl.status status_pelaksanaan,
            IF(cte.id_pelaksanaan IS NULL, 
                'Perencanaan', 
                IF(pl.status = 'S',
                    'Selesai',
                    IF(pn.id_pelaksanaan IS NULL, 
                        'Persiapan', 
                        IF(SUM(IF(pn.status = '1', nominal, 0)) <> rn.total_anggaran AND b.total_pengadaan IS NULL, 
                            'Pencairan', 
                            IF(b.total_pengadaan < SUM(IF(pn.status = '1', nominal, 0)) AND rn.total_anggaran <> SUM(IF(pn.status = '1', nominal, 0)), 
                                'Pengadaan Lanjutan',
                                IF(b.total_pengadaan < SUM(IF(pn.status = '1', nominal, 0)) AND SUM(IF(pn.status = '1', nominal, 0)) = rn.total_anggaran,
                                    'Pengadaan',
                                    IF(b.total_pengadaan = SUM(IF(pn.status = '1', nominal, 0)) AND rn.total_anggaran <> SUM(IF(pn.status = '1', nominal, 0)),
                                        'Pencairan Lanjutan',
                                        'Eksekusi'))))))) tahap
            FROM cte JOIN rencana rn ON(rn.id_rencana = cte.id_rencana) JOIN bantuan bn USING(id_bantuan) LEFT JOIN pelaksanaan pl ON(pl.id_pelaksanaan = cte.id_pelaksanaan) LEFT JOIN penarikan pn ON (pn.id_pelaksanaan = cte.id_pelaksanaan)
            LEFT JOIN
            (
                SELECT id_pelaksanaan, COUNT(id_penarikan) banyak_penarikan, SUM(jumlah_pengadaan) banyak_pengadaan, SUM(total_pengadaan) total_pengadaan
                FROM
                (
                    SELECT id_penarikan, id_pelaksanaan, COUNT(pg.id_pengadaan) jumlah_pengadaan, SUM(pg.nominal) total_pengadaan
                    FROM penarikan pn JOIN cte USING(id_pelaksanaan) LEFT JOIN penyerahan py USING(id_penarikan) LEFT JOIN pengadaan pg USING(id_pengadaan)
                    WHERE status = '1' AND id_pengadaan IS NOT NULL
                    GROUP BY id_pelaksanaan, id_penarikan
                ) a
                GROUP BY a.id_pelaksanaan
            ) b ON (b.id_pelaksanaan = cte.id_pelaksanaan)
            WHERE {$search}
            GROUP BY cte.id_pelaksanaan, b.banyak_penarikan, cte.id_rencana
            ORDER BY rn.id_rencana DESC LIMIT {$this->getHalaman()[0]},{$this->getLimit()}", array());
        }

        $result['data'] = $this->getResults();

        $this->data = $result;
        
        return true;
    }

    public function getRab($id_rab) {
        $this->query('WITH cte AS (
            SELECT FORMAT(rab.harga_satuan,0,"id_ID") harga_satuan, FORMAT(rab.jumlah,0,"id_ID") jumlah, IFNULL(rab.keterangan,"") keterangan, rab.id_kebutuhan, rab.id_rencana, k.nama, kk.nama kategori
            FROM rencana_anggaran_belanja rab JOIN kebutuhan k USING(id_kebutuhan) LEFT JOIN kategori_kebutuhan kk USING(id_kk)
            WHERE id_rab = ?
            ) 
            SELECT cte.*, COUNT(rab.id_kebutuhan) jumlah_item_rab_ini 
            FROM cte JOIN rencana_anggaran_belanja rab USING(id_kebutuhan) 
            WHERE rab.id_rencana = cte.id_rencana 
            GROUP BY cte.id_rencana', array(Sanitize::escape2($id_rab)));
        
        if (!$this->affected()) {
            return false;
        }
        $this->data = $this->getResult();
        return true;
    }

    public function resumeRab() {
        $this->query("SELECT COUNT(id_rencana) total_rencana, SUM(IF(UPPER(STATUS) = 'BD', 1, 0)) tugas, SUM(IF(UPPER(STATUS) = 'BP', 1, 0)) perbaikan, SUM(IF(UPPER(STATUS) = 'SD', 1, 0)) disetujui FROM rencana");
        if (!$this->affected()) {
            return false;
        }
        return true;
    }
}