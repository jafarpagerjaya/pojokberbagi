<?php
class BannerModel extends HomeModel {

    public function getBanner() {
        $data = $this->db->query("WITH donasiDonaturInBanner AS (
            SELECT id_donasi, id_bantuan, jumlah_donasi, id_donatur
            FROM donasi
            WHERE id_bantuan IN (SELECT id_bantuan FROM banner ORDER BY modified_at ASC) AND bayar = 1 
            ORDER BY id_donasi
        ) 	SELECT
            bn.id_banner,
            IFNULL(ddibpl.total_pelaksanaan,0) total_pelaksanaan,
            IF(b.id_bantuan = 1, COUNT(DISTINCT(ddib.id_donatur))+1999, COUNT(DISTINCT(ddib.id_donatur))) jumlah_donatur, IFNULL(SUM(ddib.jumlah_donasi),0) total_donasi, IFNULL(SUM(ddib.penggunaan_anggaran_donasi),0) total_penggunaan_anggaran, b.id_bantuan, 
            b.nama nama_bantuan, b.jumlah_target, b.deskripsi, gm.path_gambar path_gambar_medium, IFNULL(gm.nama,CONCAT('Gambar ',b.nama)) nama_gambar_medium, gw.path_gambar path_gambar_wide, IFNULL(gw.nama,CONCAT('Gambar ',b.nama)) nama_gambar_wide, k.nama nama_kategori,
            CASE WHEN b.id_sektor = 'S' THEN 'Sosial Kemanusiaan' WHEN b.id_sektor = 'P' THEN 'Pendidikan Umat' WHEN b.id_sektor = 'E' THEN 'Pemandirian Ekonomi' WHEN b.id_sektor = 'K' THEN 'Kesehatan Masyarakat' WHEN b.id_sektor = 'L' THEN 'Lingkungan Asri' WHEN b.id_sektor = 'B' THEN 'Tanggap Bencana' END layanan,
            IF(b.tanggal_akhir IS NULL, 'Unlimited', CASE WHEN TIMESTAMPDIFF(DAY,NOW(), STR_TO_DATE(CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s')),'%Y-%m-%d %H:%i:%s')) < 0 THEN 'Sudah lewat' WHEN TIMESTAMPDIFF(DAY,NOW(), STR_TO_DATE(CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s')),'%Y-%m-%d %H:%i:%s')) = 0 THEN 'Terakhir hari ini' ELSE TIMESTAMPDIFF(DAY,NOW(), STR_TO_DATE(CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s')),'%Y-%m-%d %H:%i:%s')) END ) sisa_waktu,
            IF(b.jumlah_target IS NULL, 'Donasi', b.satuan_target) jenis_penyaluran,
            IF(b.jumlah_target IS NULL, IFNULL(SUM(ddib.penggunaan_anggaran_donasi),0), ddibpl.total_pelaksanaan) donasi_disalurkan,
            IF(b.jumlah_target IS NULL, 
                TRUNCATE(IFNULL(SUM(ddib.penggunaan_anggaran_donasi)/SUM(ddib.jumlah_donasi),0)*100,2), 
                IF(ddibpl.total_pelaksanaan IS NULL, 0, TRUNCATE((ddibpl.total_pelaksanaan/b.jumlah_target)*100,2))
            ) persentase_donasi_disalurkan
            FROM (
                SELECT cte.id_donatur, cte.id_donasi, cte.jumlah_donasi, SUM(apd.nominal_penggunaan_donasi) penggunaan_anggaran_donasi, cte.id_bantuan
                FROM donasiDonaturInBanner cte LEFT JOIN anggaran_pelaksanaan_donasi apd ON(apd.id_donasi = cte.id_donasi)
                GROUP BY cte.id_donasi, apd.id_donasi
                ORDER BY cte.id_donasi
            ) ddib RIGHT JOIN bantuan b ON(b.id_bantuan = ddib.id_bantuan)
            JOIN banner bn ON (b.id_bantuan = bn.id_bantuan)
            LEFT JOIN gambar gm ON (b.id_gambar_medium = gm.id_gambar)
            LEFT JOIN gambar gw ON (b.id_gambar_wide = gw.id_gambar)
            LEFT JOIN kategori k USING(id_kategori)
            LEFT JOIN (
                SELECT SUM(pls.jumlah_pelaksanaan) total_pelaksanaan, pls.id_bantuan FROM (
                    SELECT DISTINCT(cte.id_bantuan), id_pelaksanaan, pl.jumlah_pelaksanaan 
                    FROM donasiDonaturInBanner cte LEFT JOIN rencana USING(id_bantuan) JOIN pelaksanaan pl USING(id_rencana)
                ) pls
                GROUP BY pls.id_bantuan
            ) ddibpl ON (ddibpl.id_bantuan = b.id_bantuan)
            WHERE b.status = 'D' AND b.blokir IS NULL AND (b.tanggal_akhir IS NULL OR TIMESTAMPDIFF(DAY,NOW(), STR_TO_DATE(CONCAT(b.tanggal_akhir,DATE_FORMAT(NOW(),' %H:%i:%s')),'%Y-%m-%d %H:%i:%s')) >= 0)
            GROUP BY b.id_bantuan, ddibpl.total_pelaksanaan, bn.id_banner
            LIMIT 10");
        if ($data->count()) {
            $this->data = $data->results();
            return $this->data;
        }
        return false;
    }

    public function readBanner() {
        $this->db->query("SELECT bn.id_banner, b.id_bantuan, b.nama nama_bantuan, IF(bn.id_bantuan IS NOT NULL,FormatTanggalFull(bn.modified_at),NULL) modified_at, 
        CASE 
            WHEN b.status <> 'D' AND b.status <> 'S' THEN
                'belum aktif'
            WHEN b.status = 'S' THEN
                'sudah ditakedown'
            ELSE
                IF (b.tanggal_akhir IS NOT NULL, IF(TIMESTAMPDIFF(DAY, NOW(), b.tanggal_akhir) < 0,'kadaluarsa','aktif'), IF(bn.id_bantuan IS NOT NULL, 'aktif',NULL))
        END status
        FROM banner bn LEFT JOIN bantuan b USING(id_bantuan)");
        
        if ($this->db->count()) {
            $this->data = $this->db->results();
            return $this->data;
        }
        return false;
    }
}