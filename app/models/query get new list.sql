SELECT b.id_bantuan, b.action_at, b.status , b.prioritas 
FROM bantuan b JOIN kategori k ON (b.id_kategori = k.id_kategori) 
WHERE b.blokir IS NULL AND UPPER(b.status) = UPPER('D') AND LOWER(k.nama) = LOWER('pojok peduli berbagi') 
AND (b.action_at > (SELECT MIN(action_at) FROM bantuan WHERE id_bantuan IN (7, 1, 2) AND status = 'D'))
ORDER BY b.prioritas DESC, b.action_at DESC


SELECT id_pelaksanaan, total_anggaran FROM pelaksanaan WHERE id_pelaksanaan = 1;

SELECT id_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = (SELECT id_bantuan FROM pelaksanaan WHERE id_pelaksanaan = 1);

SELECT id_donasi, jumlah_donasi FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) WHERE bayar = 1 AND id_bantuan = (SELECT id_bantuan FROM pelaksanaan WHERE id_pelaksanaan = 1) AND a.id_pelaksanaan IS NULL


SET @a = 5000000000;
SET @id_bantuan = 1;

(SELECT d1.id_donasi, d1.jumlah_donasi, SUM(d2.jumlah_donasi) accumulator, IF((SUM(d2.jumlah_donasi) - @a) < 0, 0, (SUM(d2.jumlah_donasi) - @a)) saldo_donasi
FROM donasi d1 INNER JOIN donasi d2 ON(d1.id_donasi >= d2.id_donasi) LEFT JOIN anggaran_pelaksanaan_donasi a ON(a.id_donasi = d2.id_donasi)
WHERE d1.bayar = 1 AND d2.bayar = 1 AND d1.id_bantuan = 1 AND d2.id_bantuan = 1 AND a.id_pelaksanaan IS NULL
GROUP BY d1.id_donasi
HAVING accumulator <= @a)
UNION
(SELECT d1.id_donasi, d1.jumlah_donasi, SUM(d2.jumlah_donasi) accumulator, (SUM(d2.jumlah_donasi) - @a) saldo_donasi
FROM donasi d1 INNER JOIN donasi d2 ON(d1.id_donasi >= d2.id_donasi) LEFT JOIN anggaran_pelaksanaan_donasi a ON(a.id_donasi = d2.id_donasi)
WHERE d1.bayar = 1 AND d2.bayar = 1 AND d1.id_bantuan = 1 AND d2.id_bantuan = 1 AND a.id_pelaksanaan IS NULL
GROUP BY d1.id_donasi
HAVING accumulator >= @a
LIMIT 1);


-- INI UNTUK CEK LIST DONASI BESERTA SALDONYA YANG BISA DICAIRKAN SEJUMLAH TOTAL RAB
SET @total_rab = 100000;
SET @id_bantuan = 2;

(
	SELECT d.id_donasi, d.saldo_donasi, 0 AS saldo_donasi FROM (
        (
            SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = @id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
        )
        UNION
        (
            SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = @id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = @id_bantuan)
        ) 
    ) sd, 
    (
        (
            SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = @id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
        )
        UNION
        (
            SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = @id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = @id_bantuan)
        ) 
    ) d 
    WHERE d.id_donasi >= sd.id_donasi
    GROUP BY d.id_donasi, d.saldo_donasi
    HAVING SUM(sd.saldo_donasi) < @total_rab
)
UNION
(
	SELECT d.id_donasi, d.saldo_donasi, (SUM(sd.saldo_donasi) - @total_rab) AS saldo_donasi FROM (
        (
            SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = @id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
        )
        UNION
        (
            SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = @id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = @id_bantuan)
        ) 
    ) sd, 
    (
        (
            SELECT au.id_donasi, MIN(au.saldo_donasi) as saldo_donasi FROM pelaksanaan p JOIN rencana r USING(id_rencana) JOIN anggaran_pelaksanaan_donasi au ON(au.id_pelaksanaan = p.id_pelaksanaan) WHERE r.id_bantuan = @id_bantuan GROUP BY au.id_donasi HAVING MIN(au.saldo_donasi)
        )
        UNION
        (
            SELECT id_donasi, jumlah_donasi as saldo_donasi FROM donasi WHERE bayar = 1 AND id_bantuan = @id_bantuan AND id_donasi NOT IN (SELECT id_donasi FROM anggaran_pelaksanaan_donasi JOIN pelaksanaan USING(id_pelaksanaan) WHERE id_bantuan = @id_bantuan)
        ) 
    ) d 
    WHERE d.id_donasi >= sd.id_donasi
    GROUP BY d.id_donasi, d.saldo_donasi
    HAVING SUM(sd.saldo_donasi) >= @total_rab
    LIMIT 1
);
-- #################################################################################
              
SET @total_rab = 225000;
SET @id_bantuan = 2;
SELECT *, IF(akumulatif <= @total_rab, nominal, nominal - (akumulatif - @total_rab)) nominal_pencairan FROM ((
    SELECT pencairan1.id_cp, pencairan1.nominal, SUM(pencairan2.nominal) akumulatif FROM 
    (
        SELECT id_cp, SUM(nominal) nominal FROM ((
            SELECT id_cp, MIN(a.saldo_donasi) nominal
            FROM pelaksanaan p LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_pelaksanaan) JOIN donasi USING(id_donasi) JOIN channel_payment USING(id_cp)
            WHERE p.id_bantuan = @id_bantuan AND bayar = 1
            GROUP BY a.id_donasi, id_cp
            HAVING MIN(a.saldo_donasi)
        )
        UNION
        (
            SELECT id_cp, SUM(jumlah_donasi) nominal
            FROM donasi LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi)
            WHERE id_bantuan = @id_bantuan AND bayar = 1 AND a.id_pelaksanaan IS NULL 
            GROUP BY id_cp
        )) pcr1
        GROUP BY id_cp
    ) pencairan1 JOIN (
        SELECT id_cp, SUM(nominal) nominal FROM ((
            SELECT id_cp, MIN(a.saldo_donasi) nominal
            FROM pelaksanaan p LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_pelaksanaan) JOIN donasi USING(id_donasi) JOIN channel_payment USING(id_cp)
            WHERE p.id_bantuan = @id_bantuan AND bayar = 1
            GROUP BY a.id_donasi, id_cp
            HAVING MIN(a.saldo_donasi)
        )
        UNION
        (
            SELECT id_cp, SUM(jumlah_donasi) nominal
            FROM donasi LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi)
            WHERE id_bantuan = @id_bantuan AND bayar = 1 AND a.id_pelaksanaan IS NULL 
            GROUP BY id_cp
        )) pcr2
        GROUP BY id_cp
    ) pencairan2 ON(pencairan1.id_cp >= pencairan2.id_cp)
    GROUP BY pencairan1.id_cp
    HAVING akumulatif < @total_rab
)
UNION
(
    SELECT pencairan1.id_cp, pencairan1.nominal, SUM(pencairan2.nominal) akumulatif FROM 
    (
        SELECT id_cp, SUM(nominal) nominal FROM ((
            SELECT id_cp, MIN(a.saldo_donasi) nominal
            FROM pelaksanaan p LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_pelaksanaan) JOIN donasi USING(id_donasi) JOIN channel_payment USING(id_cp)
            WHERE p.id_bantuan = @id_bantuan AND bayar = 1
            GROUP BY a.id_donasi, id_cp
            HAVING MIN(a.saldo_donasi)
        )
        UNION
        (
            SELECT id_cp, SUM(jumlah_donasi) nominal
            FROM donasi LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi)
            WHERE id_bantuan = @id_bantuan AND bayar = 1 AND a.id_pelaksanaan IS NULL 
            GROUP BY id_cp
        )) pcr1
        GROUP BY id_cp
    ) pencairan1 JOIN (
        SELECT id_cp, SUM(nominal) nominal FROM ((
            SELECT id_cp, MIN(a.saldo_donasi) nominal
            FROM pelaksanaan p LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_pelaksanaan) JOIN donasi USING(id_donasi) JOIN channel_payment USING(id_cp)
            WHERE p.id_bantuan = @id_bantuan AND bayar = 1
            GROUP BY a.id_donasi, id_cp
            HAVING MIN(a.saldo_donasi)
        )
        UNION
        (
            SELECT id_cp, SUM(jumlah_donasi) nominal
            FROM donasi LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi)
            WHERE id_bantuan = @id_bantuan AND bayar = 1 AND a.id_pelaksanaan IS NULL 
            GROUP BY id_cp
        )) pcr2
        GROUP BY id_cp
    ) pencairan2 ON(pencairan1.id_cp >= pencairan2.id_cp)
    GROUP BY pencairan1.id_cp
    HAVING akumulatif >= @total_rab
    LIMIT 1
)) pencairan_view_akhir;

-- Menampilkan Penarikan yang harus dilakukan
SELECT s_pencairan.id_ca, SUM(nominal_peggunaan) nominal_pencairan, ca.nama
FROM
(
    (
        SELECT s1.id_donasi, s1.id_ca, SUM(s2.saldo) saldo_akumulatif, s1.saldo nominal_peggunaan 
        FROM (
            SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, v.saldo
            FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) 
            WHERE d.bayar = 1 AND d.id_bantuan = @in_id_bantuan
            GROUP BY d.id_donasi, v.id_ca
            HAVING saldo_donasi > 0
            ORDER BY d.id_donasi
            ) s1, (
            SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, v.saldo
            FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) 
            WHERE d.bayar = 1 AND d.id_bantuan = @in_id_bantuan
            GROUP BY d.id_donasi, v.id_ca
            HAVING saldo_donasi > 0
            ORDER BY d.id_donasi
        ) s2
        WHERE s1.id_donasi >= s2.id_donasi
        GROUP BY s1.id_donasi, s1.id_ca
        HAVING saldo_akumulatif < @in_nominal
    ) UNION (
    SELECT s1.id_donasi, s1.id_ca, SUM(s2.saldo) saldo_akumulatif, @in_nominal - (SUM(s2.saldo) - s1.saldo) nominal_penggunaan 
    FROM (
        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, v.saldo
        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) 
        WHERE d.bayar = 1 AND d.id_bantuan = @in_id_bantuan
        GROUP BY d.id_donasi, v.id_ca
        HAVING saldo_donasi > 0
        ORDER BY d.id_donasi
        ) s1, (
        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, v.saldo
        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) 
        WHERE d.bayar = 1 AND d.id_bantuan = @in_id_bantuan
        GROUP BY d.id_donasi, v.id_ca
        HAVING saldo_donasi > 0
        ORDER BY d.id_donasi
    ) s2
    WHERE s1.id_donasi >= s2.id_donasi
    GROUP BY s1.id_donasi, s1.id_ca
    HAVING saldo_akumulatif >= @in_nominal
    LIMIT 1
    )
) s_pencairan,
channel_account ca
WHERE s_pencairan.id_ca = ca.id_ca
GROUP BY id_ca;
-- Akhir Menampilkan Penarikan yang harus dilakukan

-- KALKULASI PINBUK
SET @in_id_pinbuk = 1;
SET @in_id_bantuan = 2;
SET @in_nominal = 100000;
SET @in_id_ca_pengirim = 2;
-- BANTUAN IS NULL UNTUK PINBUK
(
    SELECT @in_id_pinbuk id_pinbuk, s1.id_donasi, s1.saldo_donasi nominal_pindah, 0 nominal_sisa FROM
    (
        SELECT d.id_donasi, IFNULL(IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) - SUM(dp.nominal_pindah),IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi)) saldo_donasi
        FROM donasi d JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) LEFT JOIN detil_pinbuk dp ON(dp.id_donasi = d.id_donasi) LEFT JOIN pinbuk pb ON(pb.id_pinbuk = dp.id_pinbuk)
        WHERE d.bayar = 1 AND (cp.id_ca = @in_id_ca_pengirim) OR (pb.id_ca_penerima = @in_id_ca_pengirim AND cp.id_ca != @in_id_ca_pengirim AND dp.id_donasi = d.id_donasi)
        GROUP BY d.id_donasi
        HAVING saldo_donasi > 0
    ) s1,
    (
        SELECT d.id_donasi, IFNULL(IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) - SUM(dp.nominal_pindah),IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi)) saldo_donasi
        FROM donasi d JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) LEFT JOIN detil_pinbuk dp ON(dp.id_donasi = d.id_donasi) LEFT JOIN pinbuk pb ON(pb.id_pinbuk = dp.id_pinbuk)
        WHERE d.bayar = 1 AND (cp.id_ca = @in_id_ca_pengirim) OR (pb.id_ca_penerima = @in_id_ca_pengirim AND cp.id_ca != @in_id_ca_pengirim AND dp.id_donasi = d.id_donasi)
        GROUP BY d.id_donasi
        HAVING saldo_donasi > 0
    ) s2
    WHERE s1.id_donasi >= s2.id_donasi
    GROUP BY s1.id_donasi, s1.saldo_donasi
    HAVING SUM(s2.saldo_donasi) < @in_nominal
)
UNION
(
    SELECT @in_id_pinbuk id_pinbuk, s1.id_donasi, s1.saldo_donasi - (SUM(s2.saldo_donasi) - @in_nominal) nominal_pindah, SUM(s2.saldo_donasi) - @in_nominal nominal_sisa FROM
    (
        SELECT d.id_donasi, IFNULL(IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) - SUM(dp.nominal_pindah),IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi)) saldo_donasi
        FROM donasi d JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) LEFT JOIN detil_pinbuk dp ON(dp.id_donasi = d.id_donasi) LEFT JOIN pinbuk pb ON(pb.id_pinbuk = dp.id_pinbuk)
        WHERE d.bayar = 1 AND (cp.id_ca = @in_id_ca_pengirim) OR (pb.id_ca_penerima = @in_id_ca_pengirim AND cp.id_ca != @in_id_ca_pengirim AND dp.id_donasi = d.id_donasi)
        GROUP BY d.id_donasi
        HAVING saldo_donasi > 0
    ) s1,
    (
        SELECT d.id_donasi, IFNULL(IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) - SUM(dp.nominal_pindah),IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi)) saldo_donasi
        FROM donasi d JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) LEFT JOIN detil_pinbuk dp ON(dp.id_donasi = d.id_donasi) LEFT JOIN pinbuk pb ON(pb.id_pinbuk = dp.id_pinbuk)
        WHERE d.bayar = 1 AND (cp.id_ca = @in_id_ca_pengirim) OR (pb.id_ca_penerima = @in_id_ca_pengirim AND cp.id_ca != @in_id_ca_pengirim AND dp.id_donasi = d.id_donasi)
        GROUP BY d.id_donasi
        HAVING saldo_donasi > 0
    ) s2
    WHERE s1.id_donasi >= s2.id_donasi
    GROUP BY s1.id_donasi, s1.saldo_donasi
    HAVING SUM(s2.saldo_donasi) >= @in_nominal
    LIMIT 1
);

-- BANTUAN IS NOT NULL UNTUK PINBUK
(
    SELECT @in_id_pinbuk id_pinbuk, s1.id_donasi, s1.saldo_donasi nominal_pindah, 0 nominal_sisa FROM
    (
        SELECT d.id_donasi, IFNULL(IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) - SUM(dp.nominal_pindah),IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi)) saldo_donasi
        FROM donasi d JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) LEFT JOIN detil_pinbuk dp ON(dp.id_donasi = d.id_donasi) LEFT JOIN pinbuk pb ON(pb.id_pinbuk = dp.id_pinbuk)
        WHERE d.bayar = 1 AND d.id_bantuan = @in_id_bantuan AND (cp.id_ca = @in_id_ca_pengirim) OR (pb.id_ca_penerima = @in_id_ca_pengirim AND cp.id_ca != @in_id_ca_pengirim AND dp.id_donasi = d.id_donasi)
        GROUP BY d.id_donasi
        HAVING saldo_donasi > 0
    ) s1,
    (
        SELECT d.id_donasi, IFNULL(IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) - SUM(dp.nominal_pindah),IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi)) saldo_donasi
        FROM donasi d JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) LEFT JOIN detil_pinbuk dp ON(dp.id_donasi = d.id_donasi) LEFT JOIN pinbuk pb ON(pb.id_pinbuk = dp.id_pinbuk)
        WHERE d.bayar = 1 AND d.id_bantuan = @in_id_bantuan AND (cp.id_ca = @in_id_ca_pengirim) OR (pb.id_ca_penerima = @in_id_ca_pengirim AND cp.id_ca != @in_id_ca_pengirim AND dp.id_donasi = d.id_donasi)
        GROUP BY d.id_donasi
        HAVING saldo_donasi > 0
    ) s2
    WHERE s1.id_donasi >= s2.id_donasi
    GROUP BY s1.id_donasi, s1.saldo_donasi
    HAVING SUM(s2.saldo_donasi) < @in_nominal
)
UNION
(
    SELECT @in_id_pinbuk id_pinbuk, s1.id_donasi, s1.saldo_donasi - (SUM(s2.saldo_donasi) - @in_nominal) nominal_pindah, SUM(s2.saldo_donasi) - @in_nominal nominal_sisa FROM
    (
        SELECT d.id_donasi, IFNULL(IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) - SUM(dp.nominal_pindah),IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi)) saldo_donasi
        FROM donasi d JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) LEFT JOIN detil_pinbuk dp ON(dp.id_donasi = d.id_donasi) LEFT JOIN pinbuk pb ON(pb.id_pinbuk = dp.id_pinbuk)
        WHERE d.bayar = 1 AND d.id_bantuan = @in_id_bantuan AND (cp.id_ca = @in_id_ca_pengirim) OR (pb.id_ca_penerima = @in_id_ca_pengirim AND cp.id_ca != @in_id_ca_pengirim AND dp.id_donasi = d.id_donasi)
        GROUP BY d.id_donasi
        HAVING saldo_donasi > 0
    ) s1,
    (
        SELECT d.id_donasi, IFNULL(IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) - SUM(dp.nominal_pindah),IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi)) saldo_donasi
        FROM donasi d JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a USING(id_donasi) LEFT JOIN detil_pinbuk dp ON(dp.id_donasi = d.id_donasi) LEFT JOIN pinbuk pb ON(pb.id_pinbuk = dp.id_pinbuk)
        WHERE d.bayar = 1 AND d.id_bantuan = @in_id_bantuan AND (cp.id_ca = @in_id_ca_pengirim) OR (pb.id_ca_penerima = @in_id_ca_pengirim AND cp.id_ca != @in_id_ca_pengirim AND dp.id_donasi = d.id_donasi)
        GROUP BY d.id_donasi
        HAVING saldo_donasi > 0
    ) s2
    WHERE s1.id_donasi >= s2.id_donasi
    GROUP BY s1.id_donasi, s1.saldo_donasi
    HAVING SUM(s2.saldo_donasi) >= @in_nominal
    LIMIT 1
);
-- END KALKULASI PINBUK

-- FOR INSERT INTO pencairan data lama
SELECT DISTINCT(pl.id_pelaksanaan) id_pelaksanaan, pl.total_anggaran, a.id_donasi, id_pencairan, pl.id_bantuan FROM pelaksanaan pl LEFT JOIN anggaran_pelaksanaan_donasi a ON(a.id_pelaksanaan = pl.id_pelaksanaan), pencairan pc WHERE pc.total = pl.total_anggaran AND pc.create_at = pl.create_at;

SELECT SUM(apd.nominal_penggunaan_donasi), '1', plpc.create_at, cp.id_ca, plpc.id_pencairan, plpc.id_pelaksanaan FROM donasi d JOIN channel_payment cp USING(id_cp) JOIN anggaran_pelaksanaan_donasi apd ON(d.id_donasi = apd.id_donasi), (SELECT DISTINCT(pl.id_pelaksanaan) id_pelaksanaan, pl.total_anggaran, id_pencairan, pc.create_at FROM pelaksanaan pl LEFT JOIN anggaran_pelaksanaan_donasi a ON(a.id_pelaksanaan = pl.id_pelaksanaan), pencairan pc WHERE pc.total = pl.total_anggaran AND pc.create_at = pl.create_at) plpc WHERE plpc.id_pelaksanaan = apd.id_pelaksanaan GROUP BY cp.id_ca, plpc.id_pelaksanaan, plpc.id_pencairan ORDER BY plpc.id_pencairan;