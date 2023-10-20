-- DROP PROCEDURE ListPinbukCaPenarikan;
DELIMITER $$
CREATE PROCEDURE ListPinbukCaPenarikan(IN in_id_ca TINYINT)
    ListCaPinbuk:BEGIN
        INSERT INTO temp_id_donasi_pinbuk(id_donasi, nominal_pinbuk, id_ca) VALUES()
    END ListCaPinbuk$$
DELIMITER ;

-- DROP TRIGGER BeforeUpdateRencana;
DELIMITER $$
CREATE TRIGGER BeforeUpdateRencana
BEFORE UPDATE ON rencana FOR EACH ROW
BEGIN
    DECLARE t_total_rab BIGINT UNSIGNED;
    SELECT SUM(nominal_kebutuhan) INTO t_total_rab FROM rencana_anggaran_belanja WHERE id_rencana = OLD.id_rencana;

    IF t_total_rab <> NEW.total_anggaran THEN
        SIGNAL SQLSTATE '45004' SET MESSAGE_TEXT = 'Not allowed direct update total_rab on rencana table';
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER AfterUpdateRencana;
DELIMITER $$
CREATE TRIGGER AfterUpdateRencana
AFTER UPDATE ON rencana FOR EACH ROW
BEGIN
    DECLARE t_total_ap BIGINT UNSIGNED;
    DECLARE t_id_pelaksanaan, t_id_bantuan INT UNSIGNED;
    DECLARE c_pelaksanaan, c_penarikan TINYINT UNSIGNED DEFAULT 0;
    IF OLD.total_anggaran <> NEW.total_anggaran THEN
        SELECT COUNT(p.id_rencana) INTO c_pelaksanaan FROM pelaksanaan p WHERE p.status != 'TD' AND p.id_rencana = NEW.id_rencana;
        SELECT COUNT(p.id_pelaksanaan) INTO c_penarikan FROM pelaksanaan p LEFT JOIN penarikan pn USING(id_pelaksanaan) WHERE pn.status = '1' AND p.id_rencana = NEW.id_rencana;
        IF c_pelaksanaan >= 1 AND c_penarikan = 0 THEN
            UPDATE pelaksanaan SET total_anggaran = (SELECT total_anggaran FROM rencana WHERE id_rencana = NEW.id_rencana) WHERE id_rencana = NEW.id_rencana;
            IF ROW_COUNT() > 0 THEN
                SELECT id_pelaksanaan INTO t_id_pelaksanaan FROM pelaksanaan WHERE id_rencana = NEW.id_rencana;
                DELETE FROM anggaran_pelaksanaan_donasi WHERE id_pelaksanaan = t_id_pelaksanaan;
                IF ROW_COUNT() > 0 THEN
                    SELECT p.total_anggaran, r.id_bantuan INTO t_total_ap, t_id_bantuan FROM pelaksanaan p JOIN rencana r USING(id_rencana) WHERE p.id_pelaksanaan = t_id_pelaksanaan;
                    CALL ReKalkulasiAnggaranPelaksanaanDonasi(t_id_pelaksanaan, t_total_ap, t_id_bantuan);
                END IF;
            END IF;
        END IF;
    END IF;
END$$
DELIMITER ;

-- DROP PROCEDURE PelaksanaanPenarikanSelesai;
DELIMITER $$
CREATE PROCEDURE PelaksanaanPenarikanSelesai()
    PSelesaiLabel:BEGIN
        -- Menampung State Cursor
        DECLARE finished_PelaksanaanPenarikanSelesai TINYINT DEFAULT 0;
        -- Var untuk menampung isi cursor
        DECLARE cpp_id_pelaksanaan, cpp_id_pencairan INT UNSIGNED;

        DECLARE list_pp CURSOR FOR SELECT pl.id_pelaksanaan, pc.id_pencairan FROM pelaksanaan pl JOIN rencana r USING(id_rencana), pencairan pc WHERE r.status = 'SD' AND pl.status = 'S' AND pc.status = 'OP' AND pl.total_anggaran = pc.total AND pc.create_at = pl.create_at AND pl.status = 'S' AND pc.status = 'OP';

         -- declare NOT FOUND handler
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_PelaksanaanPenarikanSelesai = 1;
        OPEN list_pp;

        PSelesaiLoop:WHILE NOT finished_PelaksanaanPenarikanSelesai DO
            FETCH list_pp INTO cpp_id_pelaksanaan, cpp_id_pencairan;

            IF finished_PelaksanaanPenarikanSelesai = 1 THEN
                CLOSE list_pp;
                LEAVE PSelesaiLoop;
            END IF;

            CALL InsertPenarikan(cpp_id_pelaksanaan, 100, cpp_id_pencairan);

        END WHILE PSelesaiLoop;
    END PSelesaiLabel$$
DELIMITER ;


-- DROP PROCEDURE InsertDetilPenarikan;
DELIMITER $$
CREATE PROCEDURE InsertDetilPenarikan(IN in_id_pelaksanaan INT, IN in_id_penarikan INT, IN in_id_ca TINYINT, IN in_nominal INT)
    IDPenarikanLabel:BEGIN
        -- Menampung State Cursor
        DECLARE finished_detil_penarikan_insert TINYINT DEFAULT 0;
        -- Var untuk menampung isi cursor
        DECLARE ct_id_apd, ct_nominal, ct_saldo BIGINT UNSIGNED;

        DECLARE list_dtpa_select_insert CURSOR FOR 
        SELECT idpd.id_apd, idpd.nominal_penarikan_donasi, idpd.saldo FROM
        (
            (
                SELECT vadp.id_apd, vadp.saldo_penarikan nominal_penarikan_donasi, SUM(vadp2.saldo_penarikan) kumulatif, 0 saldo 
                FROM 
                    (SELECT a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) JOIN virtual_ca_donasi v USING(id_donasi) WHERE a.id_pelaksanaan = in_id_pelaksanaan AND V.id_ca = in_id_ca GROUP BY d.id_apd, a.id_apd HAVING saldo_penarikan > 0) vadp,
                    (SELECT a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) JOIN virtual_ca_donasi v USING(id_donasi) WHERE a.id_pelaksanaan = in_id_pelaksanaan AND V.id_ca = in_id_ca GROUP BY d.id_apd, a.id_apd HAVING saldo_penarikan > 0) vadp2
                WHERE vadp.id_apd >= vadp2.id_apd
                GROUP BY vadp.id_apd
                HAVING kumulatif < in_nominal
                ORDER BY vadp.id_apd
                ) UNION (
                SELECT vadp.id_apd, in_nominal - (SUM(vadp2.saldo_penarikan) - vadp.saldo_penarikan) nominal_penarikan_donasi, SUM(vadp2.saldo_penarikan) kumulatif, vadp.saldo_penarikan - (in_nominal - (SUM(vadp2.saldo_penarikan) - vadp.saldo_penarikan)) saldo FROM 
                    (SELECT a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) JOIN virtual_ca_donasi v USING(id_donasi) WHERE a.id_pelaksanaan = in_id_pelaksanaan AND V.id_ca = in_id_ca GROUP BY d.id_apd, a.id_apd HAVING saldo_penarikan > 0) vadp,
                    (SELECT a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) JOIN virtual_ca_donasi v USING(id_donasi) WHERE a.id_pelaksanaan = in_id_pelaksanaan AND V.id_ca = in_id_ca GROUP BY d.id_apd, a.id_apd HAVING saldo_penarikan > 0) vadp2
                WHERE vadp.id_apd >= vadp2.id_apd
                GROUP BY vadp.id_apd
                HAVING kumulatif >= in_nominal
                ORDER BY vadp.id_apd
                LIMIT 1
            )
        ) idpd
        ORDER BY id_apd;

         -- declare NOT FOUND handler
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_detil_penarikan_insert = 1;
        OPEN list_dtpa_select_insert;

        VCAPenarikanLoop:WHILE NOT finished_detil_penarikan_insert DO
            FETCH list_dtpa_select_insert INTO ct_id_apd, ct_nominal, ct_saldo;

            IF finished_detil_penarikan_insert = 1 THEN
                CLOSE list_dtpa_select_insert;
                LEAVE VCAPenarikanLoop;
            END IF;

            INSERT INTO detil_transaksi_penarikan_anggaran(id_penarikan, id_apd, nominal, saldo) VALUES(in_id_penarikan, ct_id_apd, ct_nominal, ct_saldo);
            IF ROW_COUNT() != 1 THEN
                CLOSE list_dtpa_select_insert;
                LEAVE VCAPenarikanLoop;
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to insert detil_transaksi_penarikan_anggaran';
            END IF;
        END WHILE VCAPenarikanLoop;
    END IDPenarikanLabel$$
DELIMITER ;

-- DROP TRIGGER BeforeUpdatePelaksanaan;
DELIMITER $$
CREATE TRIGGER BeforeUpdatePelaksanaan
BEFORE UPDATE ON pelaksanaan FOR EACH ROW
BEGIN
    DECLARE c_penarikan TINYINT UNSIGNED;
    DECLARE t_total_anggaran_rencana, t_sum_nominal_penarikan BIGINT UNSIGNED;
    IF OLD.total_anggaran <> NEW.total_anggaran THEN
        SELECT total_anggaran FROM rencana WHERE id_rencana = NEW.id_rencana INTO t_total_anggaran_rencana;
        IF NEW.total_anggaran > t_total_anggaran_rencana THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to update pelaksanaan.total_anggaran must not greater than rencana.total_anggaran';
        END IF;

        SELECT COUNT(pn.id_penarikan), IFNULL(SUM(pn.nominal), 0)
        FROM penarikan pn JOIN pencairan pr USING(id_pencairan) 
        WHERE pn.status = '1' AND id_pelaksanaan = OLD.id_pelaksanaan
        GROUP BY pn.id_pelaksanaan INTO c_penarikan, t_sum_nominal_penarikan;
        
        IF c_penarikan > 0 AND NEW.total_anggaran < t_sum_nominal_penarikan THEN 
            SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Failed to update pelaksanaan.total_anggaran must greater than SUM(penarikan.nominal)';
        END IF;
    END IF;
END$$
DELIMITER ; 

-- DROP TRIGGER AfterUpdateRAB;
DELIMITER $$
CREATE TRIGGER AfterUpdateRAB
AFTER UPDATE ON rencana_anggaran_belanja FOR EACH ROW
BEGIN
    SET @TRTextUpdateRab = NULL;

    IF OLD.jumlah <> NEW.jumlah OR OLD.harga_satuan <> NEW.harga_satuan THEN
        UPDATE rencana SET status = 'BD', total_anggaran = (total_anggaran + NEW.nominal_kebutuhan) - OLD.nominal_kebutuhan WHERE id_rencana = NEW.id_rencana;
        IF (ROW_COUNT() != 0) THEN
            SET @TRTextUpdateRab = CONCAT_WS(' ','Success update RAB on id_rencana', NEW.id_rencana, 'with id_kebutuhan',NEW.id_kebutuhan);
        ELSE
            SET @TRTextUpdateRab = 'Falied to update RAB';
        END IF;
    END IF;
END$$

DELIMITER ;

-- DROP TRIGGER BeforeUpdatePenarikan;
DELIMITER $$
CREATE TRIGGER BeforeUpdatePenarikan
BEFORE UPDATE ON penarikan FOR EACH ROW
    BEGIN
    DECLARE t_total_pencairan, t_sum_nominal_penarikan BIGINT UNSIGNED;
    DECLARE t_status_pencairan VARCHAR(3);

    SELECT status FROM pencairan WHERE id_pencairan = OLD.id_pencairan INTO t_status_pencairan;
    IF UPPER(t_status_pencairan) = 'WTR' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Petugas pencairan belum siap untuk mencairkan';
    END IF;

    IF OLD.nominal < NEW.nominal THEN
        SELECT total FROM pencairan WHERE id_pencairan = OLD.id_pencairan INTO t_total_pencairan;
        SELECT (SUM(nominal) - OLD.nominal) + NEW.nominal FROM penarikan WHERE id_pencairan = OLD.id_pencairan GROUP BY id_pencairan INTO t_sum_nominal_penarikan;
    
        IF t_total_pencairan < t_sum_nominal_penarikan THEN
            SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Total penarikan should less then total_pencairan';
        END IF;
    END IF;

    -- CHECK sudah dibelanjakan atau belum?
    -- Jika Sudah dibelanjakan pakai SIGNAL gagal memperbaharui data nominal penarikan (Kemungkinan menambah atribut baru di apd bisa berupa status belanja atau membuat relasi dari apd dengan entitas belanja)
    
    END$$
DELIMITER ;

-- DROP TRIGGER AfterUpdatePenarikan
DELIMITER $$
CREATE TRIGGER AfterUpdatePenarikan
AFTER UPDATE ON penarikan FOR EACH ROW
BEGIN
    DECLARE t_new_id_transaksi_k, t_new_id_transaksi_m BIGINT UNSIGNED;

    IF ((OLD.nominal != NEW.nominal) AND OLD.status = '1') OR (NEW.status = '0' AND OLD.status = '1') THEN
        INSERT INTO transaksi(nominal,jenis,id_ca) VALUES(OLD.nominal,'M',OLD.id_ca);
        SELECT LAST_INSERT_ID() INTO t_new_id_transaksi_m;
        UPDATE channel_account SET saldo = saldo + OLD.nominal WHERE id_ca = NEW.id_ca;

        CALL VirtualCAPenarikan(OLD.id_penarikan, NEW.id_ca, 1);
        DELETE FROM detil_transaksi_penarikan_anggaran WHERE id_penarikan = OLD.id_penarikan;
        UPDATE detil_transaksi_pengembalian_penarikan_anggaran SET id_transaksi_m = t_new_id_transaksi_m WHERE id_penarikan = OLD.id_penarikan AND id_transaksi_m IS NULL;
    END IF;

    IF NEW.status = '1' AND OLD.status = '0' OR NEW.status = '1' AND (OLD.nominal != NEW.nominal) THEN
        INSERT INTO transaksi(nominal,jenis,id_ca) VALUES(NEW.nominal,'K',NEW.id_ca);
        SELECT LAST_INSERT_ID() INTO t_new_id_transaksi_k;
        UPDATE channel_account SET saldo = saldo - NEW.nominal WHERE id_ca = NEW.id_ca;

        CALL InsertDetilPenarikan(NEW.id_pelaksanaan, NEW.id_penarikan, NEW.id_ca, NEW.nominal, t_new_id_transaksi_k);

        CALL VirtualCAPenarikan(NEW.id_penarikan, NEW.id_ca, 0);
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER BeforeUpdateDetilTransaksiPenarikanAnggaran;
DELIMITER $$
CREATE TRIGGER BeforeUpdateDetilTransaksiPenarikanAnggaran
BEFORE UPDATE ON detil_transaksi_penarikan_anggaran FOR EACH ROW
BEGIN
    IF OLD.nominal <> NEW.nominal THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Not allowed direct update on detil_transaksi_penarikan_anggaran.nominal';
    END IF;

    IF OLD.saldo <> NEW.saldo THEN
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Not allowed to change detil_transaksi_penarikan_anggaran.saldo';
    END IF;

    IF (NEW.id_apd <> OLD.id_apd) THEN
    	SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_penarikan_anggaran.id_apd';
    END IF;

    IF (NEW.id_penarikan <> OLD.id_penarikan) THEN
    	SIGNAL SQLSTATE '45003' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_penarikan_anggaran.id_penarikan';
    END IF;

    IF (NEW.id_transaksi <> OLD.id_transaksi) THEN
    	SIGNAL SQLSTATE '45004' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_penarikan_anggaran.id_transaksi';
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER BeforeDeleteDetilTransaksiPenarikanAnggaran;
DELIMITER $$
CREATE TRIGGER BeforeDeleteDetilTransaksiPenarikanAnggaran
BEFORE DELETE ON detil_transaksi_penarikan_anggaran FOR EACH ROW
BEGIN
    DECLARE t_status CHAR(1);

    SELECT status FROM penarikan WHERE id_penarikan = OLD.id_penarikan INTO t_status;
    IF t_status = '1' THEN
        SET @err_mess = CONCAT_WS(' ','Failed to delete detil_transaksi_penarikan_anggaran: status penarikan sudah dilakukan pada id_penarikan',OLD.id_penarikan,'dengan id_dtpa',OLD.id_dtpa);
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @err_mess;
    END IF;

    INSERT INTO detil_transaksi_pengembalian_penarikan_anggaran(id_apd, id_transaksi_k, id_penarikan, saldo, nominal)
    VALUES(OLD.id_apd, OLD.id_transaksi, OLD.id_penarikan, OLD.saldo, OLD.nominal);
END$$
DELIMITER ;

-- DROP TRIGGER BeforeUpdateDTPPA;
DELIMITER $$
CREATE TRIGGER BeforeUpdateDTPPA
BEFORE UPDATE ON detil_transaksi_pengembalian_penarikan_anggaran FOR EACH ROW
BEGIN
    IF (NEW.nominal <> OLD.nominal) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Not allowed to update on detil_transaksi_pengembalian_penarikan_anggaran.nominal';
    END IF;

    IF (NEW.saldo <> OLD.saldo) THEN
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Not allowed to update on detil_transaksi_pengembalian_penarikan_anggaran.saldo';
    END IF;

    IF (NEW.id_penarikan <> OLD.id_penarikan) THEN
    	SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_pengembalian_penarikan_anggaran.id_penarikan';
    END IF;
    
    IF (NEW.id_transaksi_k <> OLD.id_transaksi_k) THEN
    	SIGNAL SQLSTATE '45003' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_pengembalian_penarikan_anggaran.id_transaksi_k';
    END IF;

    IF (NEW.id_transaksi_m <> OLD.id_transaksi_m) THEN
    	SIGNAL SQLSTATE '45004' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_pengembalian_penarikan_anggaran.id_transaksi_m';
    END IF;

    IF (NEW.id_apd <> OLD.id_apd) THEN
    	SIGNAL SQLSTATE '45004' SET MESSAGE_TEXT = 'Not allowed to direct update on detil_transaksi_pengembalian_penarikan_anggaran.id_apd';
    END IF;
END$$
DELIMITER ;

-- DROP PROCEDURE ReInsertPenarikan;
DELIMITER $$
CREATE PROCEDURE ReInsertPenarikan(IN in_old_jumlah_anggaran BIGINT, IN in_new_jumlah_anggaran BIGINT, IN in_id_pelaksanaan INT)
    ReInsertPenarikanLabel:BEGIN
        -- Menampung State Cursor
        DECLARE finished_ReInsertPenarikan TINYINT DEFAULT 0;
        -- Var untuk menampung isi cursor
        DECLARE crip_id_pencairan INT UNSIGNED;
        DECLARE crip_persentase_pencairan TINYINT DEFAULT 0;

        DECLARE list_rip CURSOR FOR 
            SELECT pn.id_pencairan, (SUM(pn.nominal) / pr.total) * 100
            FROM penarikan pn JOIN pencairan pr USING(id_pencairan) 
            WHERE pn.id_pelaksanaan = in_id_pelaksanaan AND pn.status = '0' 
            GROUP BY pn.id_pencairan;
         -- declare NOT FOUND handler
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_ReInsertPenarikan = 1;
        OPEN list_rip;

        RIPLoop:WHILE NOT finished_ReInsertPenarikan DO
            FETCH list_rip INTO crip_id_pencairan, crip_persentase_pencairan;

            IF finished_ReInsertPenarikan = 1 THEN
                CLOSE list_rip;
                LEAVE RIPLoop;
            END IF;

            UPDATE pencairan SET total = (total - in_old_jumlah_anggaran) + in_new_jumlah_anggaran WHERE id_pencairan = crip_id_pencairan;
            DELETE FROM penarikan WHERE id_pelaksanaan = in_id_pelaksanaan AND id_pencairan = crip_id_pencairan;

-- Cuma buat tester penarikan yang asli pakai insertpenarikan
            -- CALL InsertPenarikanLoop(in_id_pelaksanaan, crip_persentase_pencairan, crip_id_pencairan);
        END WHILE RIPLoop;
    END ReInsertPenarikanLabel$$
DELIMITER ;

-- DROP TRIGGER BeforeDeletePencairan;
DELIMITER $$
CREATE TRIGGER BeforeDeletePencairan
BEFORE DELETE ON pencairan FOR EACH ROW
BEGIN
    DECLARE c_penarikan SMALLINT UNSIGNED;

    SELECT COUNT(pn.id_penarikan) FROM pencairan pr JOIN penarikan pn USING(id_pencairan) WHERE pr.id_pencairan = OLD.id_pencairan AND pn.status = '1' INTO c_penarikan;
    IF c_penarikan > 0 THEN 
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to delete pencairan: sudah ada penarikan';
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER BeforeDeletePenarikan;
DELIMITER $$
CREATE TRIGGER BeforeDeletePenarikan
BEFORE DELETE ON penarikan FOR EACH ROW
BEGIN
    IF OLD.status = '1' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to delete penarikan: status penarikan sudah dilakukan';
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER AfterDeletePenarikan;
DELIMITER $$
CREATE TRIGGER AfterDeletePenarikan
AFTER DELETE ON penarikan FOR EACH ROW
BEGIN
    DECLARE t_nominal BIGINT;

    SELECT IFNULL(SUM(nominal), 0) INTO t_nominal FROM penarikan WHERE id_pencairan = OLD.id_pencairan;
    IF (t_nominal < 1) THEN
        DELETE FROM pencairan WHERE id_pencairan = OLD.id_pencairan;
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER AfterUpdatePelaksanaan;
DELIMITER $$
CREATE TRIGGER AfterUpdatePelaksanaan
AFTER UPDATE ON pelaksanaan FOR EACH ROW
BEGIN
    DECLARE c_penarikan, c_pencairan, t_persentase_pencairan_penarikan TINYINT DEFAULT 0;
    DECLARE t_sum_nominal_penarikan INT UNSIGNED;

    IF OLD.total_anggaran <> NEW.total_anggaran THEN

        SELECT COUNT(p.id_pelaksanaan) INTO c_penarikan FROM pelaksanaan p LEFT JOIN penarikan pn USING(id_pelaksanaan) WHERE pn.status = '1' AND p.id_pelaksanaan = NEW.id_pelaksanaan;
        IF c_penarikan = 0 THEN
            SELECT SUM(vlt.count) INTO c_pencairan FROM (
                SELECT COUNT(DISTINCT(pn.id_pencairan)) count FROM penarikan pn JOIN pencairan pr USING(id_pencairan) WHERE pn.id_pelaksanaan = NEW.id_pelaksanaan AND pn.status = '0' GROUP BY pr.id_pencairan
            ) vlt;
            
            IF c_pencairan > 0 THEN
                UPDATE pencairan pr, penarikan pn SET pr.total = (pr.total - pn.nominal) WHERE pr.id_pencairan = pn.id_pencairan AND pn.id_pelaksanaan = NEW.id_pelaksanaan;
                DELETE FROM penarikan WHERE id_pelaksanaan = NEW.id_pelaksanaan;
            END IF;
        END IF;
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER BeforeDeleteRAB;
DELIMITER $$
CREATE TRIGGER BeforeDeleteRAB
BEFORE DELETE ON rencana_anggaran_belanja FOR EACH ROW
BEGIN
    DECLARE c_penarikan TINYINT DEFAULT 0;

    SELECT COUNT(p.id_pelaksanaan) INTO c_penarikan FROM rencana r LEFT JOIN pelaksanaan p USING(id_rencana) LEFT JOIN penarikan pn USING(id_pelaksanaan) WHERE pn.status = '1' AND r.id_rencana = OLD.id_rencana;
    IF c_penarikan > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to delete RAB: penarikan sudah dilakukan';
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER AfterDeleteRAB;
DELIMITER $$
CREATE TRIGGER AfterDeleteRAB
AFTER DELETE ON rencana_anggaran_belanja FOR EACH ROW
BEGIN
    SET @TRTextUpdateRab = NULL;

    UPDATE rencana SET total_anggaran = total_anggaran - OLD.nominal_kebutuhan WHERE id_rencana = OLD.id_rencana;
    IF (ROW_COUNT() != 0) THEN
        SET @TRTextUpdateRab = CONCAT_WS(' ','Success delete RAB on id_rencana', OLD.id_rencana, 'with id_kebutuhan',OLD.id_kebutuhan);
    ELSE
        SET @TRTextUpdateRab = 'Falied to delete RAB';
    END IF;
END$$
DELIMITER ;

-- UNTUK DI KALKULASI PENARIKAN

-- SELECT 
--     IF(pr.total * (in_persentase_penarikan / 100) > pr.total - plc.total_penarikan_pr, 
--         IF(pr.total - plc.total_penarikan_pr > pll.total_anggaran - pll.total_penarikan_pl,
-- 		  		  pll.total_anggaran - pll.total_penarikan_pl,
-- 				  pr.total - plc.total_penarikan_pr
-- 		  ),
--         IF(pr.total * (in_persentase_penarikan / 100) > pll.total_anggaran - pll.total_penarikan_pl,
--             pll.total_anggaran - pll.total_penarikan_pl,
--             FLOOR(pr.total * (in_persentase_penarikan / 100))
--         )
--     ), 
--     pr.total, plc.total_penarikan_pr, pr.total - plc.total_penarikan_pr saldo_pencairan, pll.total_anggaran, pll.total_penarikan_pl, pll.total_anggaran - pll.total_penarikan_pl saldo_pelaksanaan, pll.id_bantuan, pll.status
-- FROM pencairan pr, 
-- (SELECT SUM(IFNULL(pn.nominal,0)) total_penarikan_pr FROM pencairan pr LEFT JOIN penarikan pn USING(id_pencairan) WHERE pr.id_pencairan = in_id_pencairan AND pr.status != 'OK' GROUP BY pn.id_pencairan) plc,
-- (SELECT SUM(IFNULL(pn.nominal,0)) total_penarikan_pl, pl.total_anggaran, rn.status, rn.id_bantuan FROM rencana rn JOIN pelaksanaan pl USING(id_rencana) LEFT JOIN penarikan pn USING(id_pelaksanaan) WHERE pl.id_pelaksanaan = in_id_pelaksanaan AND rn.status = 'SD' GROUP BY pl.id_pelaksanaan) pll
-- WHERE pr.status != 'OK' AND pr.id_pencairan = in_id_pencairan

-- DROP TRIGGER BeforeUpdatePencairan
DELIMITER $$
CREATE TRIGGER BeforeUpdatePencairan
BEFORE UPDATE ON pencairan FOR EACH ROW
BEGIN
    DECLARE cd_id_pelaksanaan, c_belum_ditarik SMALLINT UNSIGNED;
    DECLARE t_total_penarikan BIGINT UNSIGNED;

    IF OLD.status = NEW.status AND NEW.status = 'OK' AND OLD.total != NEW.total THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot update total on OK status pencairan';
    END IF;

    SELECT SUM(nominal) FROM penarikan WHERE id_pencairan = OLD.id_pencairan INTO t_total_penarikan;
    IF OLD.total > NEW.total AND t_total_penarikan > NEW.total THEN
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Total pencairan must greater or equal to total penarikan';
    END IF;

    IF UPPER(OLD.status) = 'OP' AND UPPER(NEW.status) = 'OK' THEN
        SELECT COUNT(DISTINCT(id_pelaksanaan)), SUM(nominal), SUM(CASE WHEN status = '0' THEN 1 ELSE 0 END) c_belum_ditarik FROM penarikan WHERE id_pencairan = OLD.id_pencairan INTO cd_id_pelaksanaan, t_total_penarikan, c_belum_ditarik;
        IF c_belum_ditarik != 0 THEN
            SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'Status penarikan belum semuanya selesai';
        END IF;

        IF cd_id_pelaksanaan = 1 AND t_total_penarikan < NEW.total THEN
            SET NEW.total = t_total_penarikan;
        END IF;
    END IF;
END$$
DELIMITER ;


-- DROP TRIGGER BeforeUpdatePinbuk;
DELIMITER $$
CREATE TRIGGER BeforeUpdatePinbuk
BEFORE UPDATE ON pinbuk FOR EACH ROW
BEGIN
    DECLARE t_total_saldo_list_donasi BIGINT UNSIGNED;
    DECLARE t_last_id_pinbuk_list INT UNSIGNED;
    IF UPPER(NEW.status) = 'CL' AND UPPER(OLD.status) = 'OK' THEN
        -- Get last id_pinbuk from each id_donasi in this pinbuk
        SELECT DISTINCT(id_pinbuk) FROM detil_pinbuk WHERE id_donasi IN (SELECT id_donasi FROM detil_pinbuk WHERE id_pinbuk = OLD.id_pinbuk) ORDER BY 1 DESC LIMIT 1 INTO t_last_id_pinbuk_list;
        IF OLD.id_pinbuk != t_last_id_pinbuk_list THEN
            SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'Cannot revoke this pinbuk';
        ELSE
            SELECT SUM(saldo) FROM virtual_ca_donasi WHERE id_donasi IN (SELECT id_donasi FROM detil_pinbuk WHERE id_pinbuk = OLD.id_pinbuk) INTO t_total_saldo_list_donasi;
            IF t_total_saldo_list_donasi >= OLD.total_pinbuk THEN
                CALL RevokePinbuk(OLD.id_pinbuk);
            ELSE
                SIGNAL SQLSTATE '45003' SET MESSAGE_TEXT = 'Failed to cencel pinbuk not equal balance pinbuk';
            END IF;
        END IF;
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER AfterUpdatePinbuk;
DELIMITER $$
CREATE TRIGGER AfterUpdatePinbuk
AFTER UPDATE ON pinbuk FOR EACH ROW
BEGIN
    IF UPPER(NEW.status) = 'OK' AND UPPER(OLD.status) = 'WTV' OR UPPER(NEW.status) = 'OK' AND UPPER(OLD.status) = 'CL' THEN
        CALL PinbukByUpdate(NEW.id_pinbuk, NEW.id_bantuan);
    END IF;
END$$
DELIMITER ;


--- NEW
-- DROP PROCEDURE KalkulasiPinbuk;
DELIMITER $$
CREATE PROCEDURE KalkulasiPinbuk(IN in_id_pinbuk INT,IN in_nominal BIGINT, IN in_id_ca_pengirim TINYINT, IN in_id_bantuan INT)
    BEGIN
        IF in_id_bantuan IS NOT NULL THEN
        INSERT INTO detil_pinbuk(id_pinbuk,id_donasi,nominal_pindah)
            SELECT in_id_pinbuk, id_donasi, nominal_peggunaan FROM
            ( 
                (
                    SELECT s1.id_donasi, s1.id_ca, SUM(s2.saldo_pinbuk) saldo_pinbuk_akumulatif, s1.saldo_pinbuk nominal_peggunaan 
                    FROM (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND d.id_bantuan = in_id_bantuan AND v.saldo > 0
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi > 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                        ) s1, (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND d.id_bantuan = in_id_bantuan AND v.saldo > 0
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi > 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                    ) s2
                    WHERE s1.id_donasi >= s2.id_donasi
                    GROUP BY s1.id_donasi, s1.id_ca, s1.saldo_pinbuk
                    HAVING saldo_pinbuk_akumulatif < in_nominal
                    ORDER BY s1.id_donasi
                ) UNION (
                    SELECT s1.id_donasi, s1.id_ca, SUM(s2.saldo_pinbuk) saldo_pinbuk_akumulatif, in_nominal - (SUM(s2.saldo_pinbuk) - s1.saldo_pinbuk) nominal_penggunaan 
                    FROM (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND d.id_bantuan = in_id_bantuan AND v.saldo > 0
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi > 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                        ) s1, (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND d.id_bantuan = in_id_bantuan AND v.saldo > 0
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi > 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                    ) s2
                    WHERE s1.id_donasi >= s2.id_donasi
                    GROUP BY s1.id_donasi, s1.id_ca, s1.saldo_pinbuk
                    HAVING saldo_pinbuk_akumulatif >= in_nominal
                    ORDER BY s1.id_donasi
                    LIMIT 1
                )
            ) s4

            ORDER BY id_donasi;
        ELSE
        INSERT INTO detil_pinbuk(id_pinbuk,id_donasi,nominal_pindah)
            SELECT in_id_pinbuk, id_donasi, nominal_peggunaan FROM
            ( 
                (
                    SELECT s1.id_donasi, s1.id_ca, SUM(s2.saldo_pinbuk) saldo_pinbuk_akumulatif, s1.saldo_pinbuk nominal_peggunaan 
                    FROM (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND v.saldo > 0
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi > 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                        ) s1, (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND v.saldo > 0
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi > 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                    ) s2
                    WHERE s1.id_donasi >= s2.id_donasi
                    GROUP BY s1.id_donasi, s1.id_ca, s1.saldo_pinbuk
                    HAVING saldo_pinbuk_akumulatif < in_nominal
                    ORDER BY s1.id_donasi
                ) UNION (
                    SELECT s1.id_donasi, s1.id_ca, SUM(s2.saldo_pinbuk) saldo_pinbuk_akumulatif, in_nominal - (SUM(s2.saldo_pinbuk) - s1.saldo_pinbuk) nominal_penggunaan 
                    FROM (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND v.saldo > 0
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi > 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                        ) s1, (
                        SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_donasi, v.id_ca, IFNULL(v.saldo - dp.nominal_pindah, v.saldo) saldo_pinbuk
                        FROM virtual_ca_donasi v JOIN donasi d ON(d.id_donasi = v.id_donasi) JOIN channel_payment cp USING(id_cp) LEFT JOIN anggaran_pelaksanaan_donasi a ON(d.id_donasi = a.id_donasi) LEFT JOIN (SELECT id_donasi, SUM(nominal_pindah) nominal_pindah FROM pinbuk JOIN detil_pinbuk USING(id_pinbuk) WHERE (STATUS != 'OK' AND STATUS != 'CL') AND id_ca_pengirim = in_id_ca_pengirim GROUP BY id_donasi ORDER BY id_donasi) dp ON(dp.id_donasi = d.id_donasi)
                        WHERE d.bayar = 1 AND v.id_ca = in_id_ca_pengirim AND v.saldo > 0
                        GROUP BY d.id_donasi, v.id_ca, dp.nominal_pindah
                        HAVING saldo_donasi > 0 AND saldo_pinbuk > 0
                        ORDER BY d.id_donasi
                    ) s2
                    WHERE s1.id_donasi >= s2.id_donasi
                    GROUP BY s1.id_donasi, s1.id_ca, s1.saldo_pinbuk
                    HAVING saldo_pinbuk_akumulatif >= in_nominal
                    ORDER BY s1.id_donasi
                    LIMIT 1
                )
            ) s4

            ORDER BY id_donasi;
        END IF;
        SET @ROW_COUNT = (SELECT ROW_COUNT());
        IF @ROW_COUNT = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to insert detil_pinbuk';
        END IF;
    END$$
DELIMITER ;

-- DROP TRIGGER BeforeInsertPinbuk;
DELIMITER $$
CREATE TRIGGER BeforeInsertPinbuk
BEFORE INSERT ON pinbuk FOR EACH ROW
BEGIN
    DECLARE c_pengirim, c_penerima TINYINT;
    DECLARE t_saldo, sum_saldo, t_sum_nominal_pinbuk, v_total_pinbuk BIGINT;

    IF UPPER(NEW.status) = 'OK' AND NEW.id_gambar IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Id gambar pinbuk wajib diisi untuk menambah data dengan status OK';
    END IF;

    SELECT 
        (SELECT COUNT(id_ca) FROM channel_account WHERE id_ca = NEW.id_ca_pengirim), 
        (SELECT COUNT(id_ca) FROM channel_account WHERE id_ca = NEW.id_ca_penerima)
        INTO c_pengirim, c_penerima
    FROM DUAL; 

    IF c_pengirim = 0 OR c_penerima = 0 OR NEW.id_ca_pengirim = NEW.id_ca_penerima THEN
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'ID CA tidak valid';
    END IF;

    IF NEW.penyesuaian_total_pinbuk != 1 AND NEW.penyesuaian_total_pinbuk != 0 AND NEW.penyesuaian_total_pinbuk IS NOT NULL THEN
        SET NEW.penyesuaian_total_pinbuk = 0;
    END IF;

    SELECT saldo INTO t_saldo FROM channel_account WHERE id_ca = NEW.id_ca_pengirim FOR UPDATE;

    IF NEW.total_pinbuk > t_saldo THEN
        IF NEW.penyesuaian_total_pinbuk = 0 THEN
            SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'Saldo di channael_account tidak mencukupi untuk dipinbuk';
        ELSE
            SET NEW.total_pinbuk = t_saldo;
        END IF;
    END IF;

    IF NEW.id_bantuan IS NULL THEN
        SELECT IFNULL(SUM(saldo), 0) FROM virtual_ca_donasi WHERE id_ca = NEW.id_ca_pengirim INTO sum_saldo;
    ELSE
        SELECT IFNULL(SUM(v.saldo), 0) FROM virtual_ca_donasi v JOIN (
            SELECT d.id_donasi, IFNULL(MIN(a.saldo_donasi), d.jumlah_donasi) saldo_pelaksanaan 
            FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi a ON(a.id_donasi = d.id_donasi)
            WHERE d.bayar = 1 AND d.id_bantuan = NEW.id_bantuan GROUP BY d.id_donasi HAVING saldo_pelaksanaan > 0 ORDER BY d.id_donasi
        ) iv USING(id_donasi) 
        WHERE v.saldo > 0 AND v.id_ca = NEW.id_ca_pengirim INTO sum_saldo;
    END IF;

    SELECT IFNULL(SUM(total_pinbuk), 0) INTO t_sum_nominal_pinbuk FROM pinbuk p WHERE p.status != 'OK' AND p.status != 'CL' AND p.id_ca_pengirim = NEW.id_ca_pengirim;
    
    SET v_total_pinbuk = (sum_saldo - t_sum_nominal_pinbuk);

    IF NEW.penyesuaian_total_pinbuk = 0 THEN
        IF v_total_pinbuk < NEW.total_pinbuk THEN
            SIGNAL SQLSTATE '45003' SET MESSAGE_TEXT = 'Saldo daftar pinbuk di Virtual CA tidak cukup';
        END IF;
    ELSE
        IF v_total_pinbuk <= 0 THEN
            SIGNAL SQLSTATE '45004' SET MESSAGE_TEXT = 'Saldo daftar pinbuk di Virtual CA tidak ada';
        END IF;
        
        IF v_total_pinbuk < NEW.total_pinbuk THEN
            SET NEW.total_pinbuk = v_total_pinbuk;
        END IF;
    END IF;
END$$
DELIMITER ;

-- DROP TRIGGER AfterInsertPinbuk;
DELIMITER $$
CREATE TRIGGER AfterInsertPinbuk
AFTER INSERT ON pinbuk FOR EACH ROW
BEGIN
    CALL KalkulasiPinbuk(NEW.id_pinbuk, NEW.total_pinbuk, NEW.id_ca_pengirim, NEW.id_bantuan);
END$$
DELIMITER ;

-- DROP PROCEDURE VirtualCADonasi;
DELIMITER $$
CREATE PROCEDURE VirtualCADonasi(IN in_id_pinbuk INT)
    BlockVirtual:BEGIN
        -- Menampung State Cursor
        DECLARE finished_VCAD INT DEFAULT 0;
        -- Var untuk menampung isi cursor donasi
        DECLARE cdp_id_donasi BIGINT;
        DECLARE cdp_nominal INT;
        DECLARE cdp_id_ca_pengirim, cdp_id_ca_penerima TINYINT UNSIGNED;
        -- Var untuk menampung isi cursor hasil kalkulasi
        DECLARE ck_saldo_donasi INT;
        
        DECLARE t_id_ca_pengirim TINYINT;
        DECLARE c_id_ca TINYINT DEFAULT 0;
        DECLARE t_saldo BIGINT;

        DECLARE list_detil_pinbuk CURSOR FOR SELECT dp.id_donasi, dp.nominal_pindah, pb.id_ca_pengirim, pb.id_ca_penerima FROM detil_pinbuk dp JOIN pinbuk pb USING(id_pinbuk) WHERE pb.id_pinbuk = in_id_pinbuk;
        
        -- declare NOT FOUND handler
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished_VCAD = 1;

        SET @insertQuery = "INSERT INTO virtual_ca_donasi(saldo,id_donasi,id_ca) VALUES(?,?,?)";
        PREPARE stmtI FROM @insertQuery;
        
        SET @updateQueryMinus = "UPDATE virtual_ca_donasi SET saldo = saldo - ? WHERE id_donasi = ? AND id_ca = ?";
        PREPARE stmtUM FROM @updateQueryMinus;
        SET @updateQueryAdd = "UPDATE virtual_ca_donasi SET saldo = saldo + ? WHERE id_donasi = ? AND id_ca = ?";
        PREPARE stmtUA FROM @updateQueryAdd;

        OPEN list_detil_pinbuk;

        ListDP:WHILE NOT finished_VCAD DO
            FETCH list_detil_pinbuk INTO cdp_id_donasi, cdp_nominal, cdp_id_ca_pengirim, cdp_id_ca_penerima;
            
            IF finished_VCAD = 1 THEN
                LEAVE ListDP;
            END IF;

            SELECT id_ca, saldo FROM virtual_ca_donasi WHERE id_donasi = cdp_id_donasi AND id_ca = cdp_id_ca_pengirim FOR UPDATE INTO t_id_ca_pengirim, t_saldo;
            IF c_id_ca IS NULL THEN
                ROLLBACK;
                SELECT 'ID CA pengirim tidak ditemukan di Virtual CAD' MESSAGE_TEXT;
                LEAVE ListDP;
            END IF;

            IF t_saldo < cdp_nominal THEN
                ROLLBACK;
                SELECT CONCAT_WS(' ','Saldo Virtual pengirim tidak cukup',cdp_nominal,t_saldo) MESSAGE_TEXT;
                LEAVE ListDP;
            END IF;

            SET @nominal = cdp_nominal;
            SET @id_donasi = cdp_id_donasi;
            SET @id_ca_pengirim = cdp_id_ca_pengirim;
            SET @id_ca_penerima = cdp_id_ca_penerima;

            SELECT COUNT(id_ca) INTO c_id_ca FROM virtual_ca_donasi WHERE id_donasi = cdp_id_donasi AND id_ca = cdp_id_ca_penerima;
            IF c_id_ca = 0 THEN
                -- UPDATE virtual_ca_donasi SET saldo = saldo - cdp_nominal WHERE id_donasi = cdp_id_donasi AND id_cp = cdp_id_ca_pengirim;
                EXECUTE stmtUM USING @nominal, @id_donasi, @id_ca_pengirim;
                IF ROW_COUNT() != 1 THEN
                    ROLLBACK;
                    SELECT 'Gagal update before insert saldo Virtual CA baru' MESSAGE_TEXT;
                    LEAVE ListDP;
                END IF;
                -- INSERT INTO virtual_ca_donasi(saldo,id_donasi,id_ca) VALUES(cdp_nominal, cdp_id_donasi,cdp_id_ca_penerima);
                EXECUTE stmtI USING @nominal, @id_donasi, @id_ca_penerima;
                IF ROW_COUNT() != 1 THEN
                    ROLLBACK;
                    SELECT 'Gagal insert data Virtual CA baru' MESSAGE_TEXT;
                    LEAVE ListDP;
                END IF;
            ELSE
                -- UPDATE virtual_ca_donasi SET saldo = saldo - cdp_nominal WHERE id_donasi = cdp_id_donasi AND id_cp = cdp_id_ca_pengirim;
                EXECUTE stmtUM USING @nominal, @id_donasi, @id_ca_pengirim;
                IF ROW_COUNT() != 1 THEN
                    ROLLBACK;
                    SELECT 'Gagal update saldo Virtual CA pengirim' MESSAGE_TEXT;
                    LEAVE ListDP;
                END IF;

                -- UPDATE virtual_ca_donasi SET saldo = saldo + cdp_nominal WHERE id_donasi = cdp_id_donasi AND id_cp = cdp_id_ca_penerima;
                EXECUTE stmtUA USING @nominal, @id_donasi, @id_ca_penerima;
                IF ROW_COUNT() != 1 THEN
                    ROLLBACK;
                    SELECT 'Gagal update saldo Virtual CA penerima' MESSAGE_TEXT;
                    LEAVE ListDP;
                END IF;
            END IF;
        END WHILE ListDP;        
        CLOSE list_detil_pinbuk;
        DEALLOCATE PREPARE stmtI;
        DEALLOCATE PREPARE stmtUM;
        DEALLOCATE PREPARE stmtUA;
    END BlockVirtual$$
DELIMITER ;

-- DROP PROCEDURE Pinbuk;
DELIMITER $$
CREATE PROCEDURE Pinbuk(IN in_nominal BIGINT, IN in_id_ca_pengirim TINYINT, IN in_id_ca_penerima TINYINT, IN in_ket VARCHAR(50), IN in_id_bantuan INT, IN in_id_gambar INT, IN in_penyesuaian TINYINT)
    PinBuk:BEGIN
        DECLARE t_sum_nominal_pinbuk, saldo_pengirim BIGINT;
        DECLARE t_id_pinbuk, c_inserted_dp_rows INT;
        DECLARE c_pengirim, c_penerima, id_ca_penerima, id_ca_pengirim TINYINT;

        SELECT (SELECT COUNT(id_ca) FROM channel_account WHERE id_ca = in_id_ca_pengirim), (SELECT COUNT(id_ca) FROM channel_account WHERE id_ca = in_id_ca_penerima) INTO c_pengirim, c_penerima FROM DUAL; 
        IF c_pengirim = 0 OR c_penerima = 0 OR in_id_ca_pengirim = in_id_ca_penerima THEN
            SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'ID CA tidak valid';
            LEAVE PinBuk;
        END IF;

        SELECT saldo INTO saldo_pengirim FROM channel_account WHERE id_ca = in_id_ca_pengirim FOR UPDATE;
        IF saldo_pengirim < in_nominal THEN
            SIGNAL SQLSTATE '45003' SET MESSAGE_TEXT = 'Saldo CA tidak cukup';
            LEAVE PinBuk;
        END IF;

        START TRANSACTION;
        SET AUTOCOMMIT = 0;

        INSERT INTO pinbuk(total_pinbuk,keterangan,id_ca_pengirim,id_ca_penerima,status,id_gambar,id_bantuan,penyesuaian_total_pinbuk) VALUES(in_nominal,in_ket,in_id_ca_pengirim,in_id_ca_penerima,'OK',in_id_gambar,in_id_bantuan,in_penyesuaian);
        IF ROW_COUNT() = 0 THEN
            ROLLBACK;
            SIGNAL SQLSTATE '45005' SET MESSAGE_TEXT = 'Failed to insert pinbuk';
            LEAVE PinBuk;
        END IF;

        SELECT LAST_INSERT_ID() INTO t_id_pinbuk;

        SELECT SUM(nominal_pindah) INTO t_sum_nominal_pinbuk FROM detil_pinbuk WHERE id_pinbuk = t_id_pinbuk;
        IF in_penyesuaian = 0 THEN
            IF t_sum_nominal_pinbuk <> in_nominal THEN
                ROLLBACK;
                SIGNAL SQLSTATE '45006' SET MESSAGE_TEXT = 'Failed to insert equal total pinbuk with sum nominal detil_pinbuk';
                LEAVE PinBuk;
            END IF;
        ELSE
            IF t_sum_nominal_pinbuk < in_nominal THEN
                UPDATE pinbuk SET total_pinbuk = t_sum_nominal_pinbuk WHERE id_pinbuk = t_id_pinbuk;
                IF ROW_COUNT() != 1 THEN
                    SIGNAL SQLSTATE '45007' SET MESSAGE_TEXT = 'Failed to adjust total_pinbuk';
                END IF;
            END IF;
        END IF;

        CALL VirtualCADonasi(t_id_pinbuk);

        UPDATE channel_account SET saldo = saldo - t_sum_nominal_pinbuk WHERE id_ca = in_id_ca_pengirim;
        IF ROW_COUNT() != 1 THEN
            ROLLBACK;
            SIGNAL SQLSTATE '45008' SET MESSAGE_TEXT = 'Gagal update saldo pengirim';
            LEAVE PinBuk;
        END IF;
        
        UPDATE channel_account SET saldo = saldo + t_sum_nominal_pinbuk WHERE id_ca = in_id_ca_penerima;
        IF ROW_COUNT() != 1 THEN
            ROLLBACK;
            SIGNAL SQLSTATE '45009' SET MESSAGE_TEXT = 'Gagal update saldo penerima';
            LEAVE PinBuk;
        END IF;

        COMMIT;
        SELECT CONCAT_WS(' ','Pinbuk success',@c_inserted_dp_rows,'rows inserted to detil_pinbuk with pinbuk id',t_id_pinbuk);
    END PinBuk$$
DELIMITER ;

-- DROP PROCEDURE PinbukByUpdate;
DELIMITER $$
CREATE PROCEDURE PinbukByUpdate(IN in_id_pinbuk INT, IN in_status VARCHAR(3))
    PinBuk:BEGIN
        DECLARE t_total_pinbuk, t_sum_nominal_pinbuk, saldo_pengirim, saldo_penerima BIGINT;
        DECLARE c_inserted_dp_rows, t_id_bantuan INT;
        DECLARE c_pengirim, c_penerima, t_id_ca_penerima, t_id_ca_pengirim, t_penyesuaian TINYINT;
        DECLARE t_status VARCHAR(3);

        SELECT id_ca_pengirim, id_ca_penerima, total_pinbuk, status, id_bantuan, penyesuaian_total_pinbuk FROM pinbuk WHERE id_pinbuk = in_id_pinbuk INTO t_id_ca_pengirim, t_id_ca_penerima, t_total_pinbuk, t_status, t_id_bantuan, t_penyesuaian;

        SELECT (SELECT COUNT(id_ca) FROM channel_account WHERE id_ca = t_id_ca_pengirim), (SELECT COUNT(id_ca) FROM channel_account WHERE id_ca = t_id_ca_penerima) INTO c_pengirim, c_penerima FROM DUAL; 
        IF c_pengirim = 0 OR c_penerima = 0 OR t_id_ca_pengirim = t_id_ca_penerima THEN
            SELECT 'ID CA tidak valid' MESSAGE_TEXT;
            LEAVE PinBuk;
        END IF;

        IF in_status = t_status THEN
            SELECT 'Status pinbuk masih sama dengan yang lama' MESSAGE_TEXT;
            LEAVE Pinbuk;
        END IF;

        START TRANSACTION;
        SET AUTOCOMMIT = 0;

        UPDATE pinbuk SET status = in_status WHERE id_pinbuk = in_id_pinbuk;
        IF ROW_COUNT() != 1 THEN
            ROLLBACK;
            SELECT 'Gagal update status pinbuk' MESSAGE_TEXT;
            LEAVE PinBuk;
        END IF;

        IF t_status != 'OK' AND in_status = 'OK' THEN
            SELECT saldo INTO saldo_pengirim FROM channel_account WHERE id_ca = t_id_ca_pengirim FOR UPDATE;

            SELECT SUM(nominal_pindah) INTO t_sum_nominal_pinbuk FROM detil_pinbuk WHERE id_pinbuk = in_id_pinbuk;
            IF saldo_pengirim < t_sum_nominal_pinbuk THEN
                ROLLBACK;
                SELECT 'Saldo CA pengirim tidak cukup' MESSAGE_TEXT;
                LEAVE PinBuk;
            END IF;

            IF t_penyesuaian = 0 THEN
                IF t_total_pinbuk <> t_sum_nominal_pinbuk THEN
                    ROLLBACK;
                    SELECT 'Failed to insert equal total pinbuk with sum nominal detil_pinbuk' MESSAGE_TEXT;
                    LEAVE PinBuk;
                END IF;
            ELSE
                IF t_total_pinbuk <> t_sum_nominal_pinbuk THEN
                    UPDATE pinbuk SET total_pinbuk = t_sum_nominal_pinbuk WHERE id_pinbuk = in_id_pinbuk;
                    IF ROW_COUNT() != 1 THEN
                        ROLLBACK;
                        SELECT 'Failed to adjust total_pinbuk on pinbuk' MESSAGE_TEXT;
                        LEAVE PinBuk;
                    END IF;
                    SET t_total_pinbuk = t_sum_nominal_pinbuk;
                END IF;
            END IF;

            CALL VirtualCADonasi(in_id_pinbuk);

            UPDATE channel_account SET saldo = saldo - t_total_pinbuk WHERE id_ca = t_id_ca_pengirim;
            IF ROW_COUNT() != 1 THEN
                ROLLBACK;
                SELECT 'Gagal update saldo pengirim' MESSAGE_TEXT;
                LEAVE PinBuk;
            END IF;
            
            UPDATE channel_account SET saldo = saldo + t_total_pinbuk WHERE id_ca = t_id_ca_penerima;
            IF ROW_COUNT() != 1 THEN
                ROLLBACK;
                SELECT 'Gagal update saldo penerima' MESSAGE_TEXT;
                LEAVE PinBuk;
            END IF;

            COMMIT;
            SELECT CONCAT_WS(' ','Pinbuk success',@c_inserted_dp_rows,'rows inserted to detil_pinbuk with pinbuk id',in_id_pinbuk,'') MESSAGE_TEXT;
            LEAVE PinBuk;
        END IF;
        
        IF t_status = 'OK' AND in_status != 'OK' THEN
            ROLLBACK;
            SELECT 'Status pinbuk sudah OK, tidak bisa diubah';
            LEAVE Pinbuk;
        END IF;

        IF t_status != 'OK' AND in_status = 'CL' THEN
            DELETE FROM detil_pinbuk WHERE id_pinbuk = in_id_pinbuk;
            SET @c_deleted_dp_rows = ROW_COUNT();
            IF @c_deleted_dp_rows = 0 THEN
                ROLLBACK;
                SELECT 'Failed to delete detil_pinbuk on RevokePinbuk' MESSAGE_TEXT;
                LEAVE PinBuk;
            ELSE
                COMMIT;
                SELECT CONCAT_WS(' ','Revoke Pinbuk success',@c_deleted_dp_rows,'rows are deleted on detil_pinbuk with pinbuk id',in_id_pinbuk) MESSAGE_TEXT;
            END IF;
        END IF;
    END PinBuk$$
DELIMITER ;

-- DROP PROCEDURE check_table_exists;
DELIMITER $$
CREATE PROCEDURE check_table_exists(IN table_name VARCHAR(100)) 
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLSTATE '42S02' SET @err = 1;
    SET @err = 0;
    SET @table_name = table_name;
    SET @sql_query = CONCAT('SELECT 1 FROM ',@table_name);
    PREPARE stmt1 FROM @sql_query;
    IF (@err = 1) THEN
        SET @table_exists = 0;
    ELSE
        SET @table_exists = 1;
        DEALLOCATE PREPARE stmt1;
    END IF;
END $$
DELIMITER ;

-- DROP PROCEDURE BeforePenarikan;
DELIMITER $$
CREATE PROCEDURE BeforePenarikan(IN in_id_pelaksanaan INT, IN t_nominal_penarikan BIGINT, IN in_id_pencairan INT)
    PBLabel:BEGIN
        DECLARE t_count TINYINT DEFAULT 0;

        CALL check_table_exists('temp_penarikan_vl1');
        SELECT @table_exists INTO t_count;
        IF t_count = 0 THEN
            CREATE TEMPORARY TABLE temp_penarikan_vl1(
                id BIGINT UNSIGNED,
                id_donasi INT UNSIGNED,
                id_ca TINYINT UNSIGNED,
                nominal BIGINT UNSIGNED
            );
        ELSE
            DELETE FROM temp_penarikan_vl1;
        END IF;

        INSERT INTO temp_penarikan_vl1
        WITH cte AS (
            SELECT ROW_NUMBER() OVER (ORDER BY id_donasi) id, v.id_donasi, v.id_ca, IF(sum_saldo_penarikan > v.saldo, v.saldo, sum_saldo_penarikan) nominal_penarikan FROM
                (
                    SELECT id_donasi, SUM(saldo_penarikan) sum_saldo_penarikan FROM
                    (
                        (
                                SELECT vadp.id_donasi, vadp.id_apd, vadp.saldo_penarikan, SUM(vadp2.saldo_penarikan) kumulatif FROM 
                                (SELECT a.id_donasi, a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) WHERE a.id_pelaksanaan = in_id_pelaksanaan GROUP BY a.id_apd HAVING saldo_penarikan > 0) vadp,
                                (SELECT a.id_donasi, a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) WHERE a.id_pelaksanaan = in_id_pelaksanaan GROUP BY a.id_apd HAVING saldo_penarikan > 0) vadp2
                                WHERE vadp.id_apd >= vadp2.id_apd
                                GROUP BY vadp.id_apd
                                HAVING kumulatif < t_nominal_penarikan
                                ORDER BY vadp.id_apd
                        )
                        UNION
                        (
                                SELECT vadp.id_donasi, vadp.id_apd, t_nominal_penarikan - (SUM(vadp2.saldo_penarikan) - vadp.saldo_penarikan) saldo_penarikan, SUM(vadp2.saldo_penarikan) kumulatif FROM 
                                (SELECT a.id_donasi, a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) WHERE a.id_pelaksanaan = in_id_pelaksanaan GROUP BY a.id_apd HAVING saldo_penarikan > 0) vadp,
                                (SELECT a.id_donasi, a.id_apd, IFNULL(MIN(d.saldo), a.nominal_penggunaan_donasi) saldo_penarikan FROM anggaran_pelaksanaan_donasi a LEFT JOIN detil_transaksi_penarikan_anggaran d USING(id_apd) WHERE a.id_pelaksanaan = in_id_pelaksanaan GROUP BY a.id_apd HAVING saldo_penarikan > 0) vadp2
                                WHERE vadp.id_apd >= vadp2.id_apd
                                GROUP BY vadp.id_apd
                                HAVING kumulatif >= t_nominal_penarikan
                                ORDER BY vadp.id_apd
                                LIMIT 1
                        )
                    ) s_penarikan
                    GROUP BY id_donasi
                    ORDER BY id_donasi
                ) s_penarikan2 JOIN virtual_ca_donasi v USING(id_donasi)
                WHERE v.saldo > 0
        ) 
        (
            SELECT vl1.* FROM cte vl1, cte vl2
            WHERE vl1.id >= vl2.id
            GROUP BY vl1.id_ca, vl1.id_donasi, vl1.nominal_penarikan
            HAVING SUM(vl2.nominal_penarikan) < t_nominal_penarikan
            ORDER BY vl1.id
        )
        UNION
        (
            SELECT vl1.id, vl1.id_donasi, vl1.id_ca, t_nominal_penarikan - (SUM(vl2.nominal_penarikan) - vl1.nominal_penarikan) FROM cte vl1, cte vl2
            WHERE vl1.id >= vl2.id
            GROUP BY vl1.id_ca, vl1.id_donasi, vl1.nominal_penarikan
            HAVING SUM(vl2.nominal_penarikan) >= t_nominal_penarikan
            ORDER BY vl1.id
            LIMIT 1
        )
        ORDER BY id, id_donasi, id_ca;

        IF ROW_COUNT() = 0 THEN
            SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'INSERT TABLE temp_penarikan_vl1 are failed';
            LEAVE PBLabel;
        END IF;

        CALL check_table_exists('temp_penarikan_vresult1');
        SELECT @table_exists INTO t_count;
        IF t_count = 0 THEN
            CREATE TEMPORARY TABLE temp_penarikan_vresult1(
                id_ca TINYINT UNSIGNED,
                id_donasi BIGINT UNSIGNED,
                nominal BIGINT,
                id_pelaksanaan INT UNSIGNED,
                id_pencairan INT UNSIGNED
            );
        ELSE
            DELETE FROM temp_penarikan_vresult1;
        END IF;

        INSERT INTO temp_penarikan_vresult1
        SELECT id_ca, id_donasi, nominal, in_id_pelaksanaan, in_id_pencairan FROM temp_penarikan_vl1;

        IF ROW_COUNT() = 0 THEN
            SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = 'INSERT TABLE temp_penarikan_vresult1 are failed';
            LEAVE PBLabel;
        END IF;

        CALL check_table_exists('temp_penarikan_vresult2');
        SELECT @table_exists INTO t_count;
        IF t_count = 0 THEN
            CREATE TEMPORARY TABLE temp_penarikan_vresult2(
                id_ca TINYINT UNSIGNED,
                id_donasi BIGINT UNSIGNED,
                nominal BIGINT
            );
        ELSE
            DELETE FROM temp_penarikan_vresult2;
        END IF;

        INSERT INTO temp_penarikan_vresult2
        SELECT id_ca, id_donasi, nominal FROM temp_penarikan_vresult1;

        IF ROW_COUNT() = 0 THEN
            SIGNAL SQLSTATE '45004' SET MESSAGE_TEXT = 'INSERT TABLE temp_penarikan_vresult2 are failed';
            LEAVE PBLabel;
        END IF;
    END PBLabel$$
DELIMITER ;

-- DROP PROCEDURE KalkulasiPenarikan;
DELIMITER $$
CREATE PROCEDURE KalkulasiPenarikan(IN in_id_pelaksanaan INT UNSIGNED, IN in_persentase_penarikan DECIMAL(5,2), IN in_id_pencairan INT UNSIGNED)
KPLabel:BEGIN
    DECLARE t_nominal_penarikan, t_total, t_total_penarikan_pencairan, t_total_anggaran, t_total_penarikan_pelaksanaan,  t_saldo_pencairan, t_saldo_pelaksanaan, t_virtual_penarikan BIGINT UNSIGNED;
    DECLARE t_status CHAR(2);
    DECLARE t_id_bantuan INT UNSIGNED;
    
    IF (SELECT COUNT(id_pelaksanaan) FROM pelaksanaan WHERE id_pelaksanaan = in_id_pelaksanaan) = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Id Pelaksanaan tidak ditemukan';
        LEAVE KPLabel;
    END IF;

    IF (SELECT COUNT(id_pencairan) FROM pencairan WHERE id_pencairan = in_id_pencairan) = 0 THEN
        SIGNAL SQLSTATE '45001'
            SET MESSAGE_TEXT = 'Id Pencairan tidak ditemukan';
        LEAVE KPLabel;
    END IF;

    SELECT 
        IF(pr.total * (in_persentase_penarikan / 100) > pr.total - plc.total_penarikan_pr, 
            IF(pr.total - plc.total_penarikan_pr > pll.total_anggaran - pll.total_penarikan_pl,
                    pll.total_anggaran - pll.total_penarikan_pl,
                    pr.total - plc.total_penarikan_pr
            ),
            IF(pr.total * (in_persentase_penarikan / 100) > pll.total_anggaran - pll.total_penarikan_pl,
                pll.total_anggaran - pll.total_penarikan_pl,
                FLOOR(pr.total * (in_persentase_penarikan / 100))
            )
        ), 
        pll.id_bantuan, pll.status, pr.total, plc.total_penarikan_pr, pr.total - plc.total_penarikan_pr saldo_pencairan, pll.total_anggaran, pll.total_penarikan_pl, pll.total_anggaran - pll.total_penarikan_pl saldo_pelaksanaan
        INTO t_nominal_penarikan, t_id_bantuan, t_status, t_total, t_total_penarikan_pencairan, t_saldo_pencairan, t_total_anggaran, t_total_penarikan_pelaksanaan, t_saldo_pelaksanaan
    FROM pencairan pr, 
    (SELECT SUM(IFNULL(pn.nominal,0)) total_penarikan_pr FROM pencairan pr LEFT JOIN penarikan pn USING(id_pencairan) WHERE pr.id_pencairan = in_id_pencairan AND pr.status != 'OK' GROUP BY pn.id_pencairan) plc,
    (SELECT SUM(IFNULL(pn.nominal,0)) total_penarikan_pl, pl.total_anggaran, rn.status, rn.id_bantuan FROM rencana rn JOIN pelaksanaan pl USING(id_rencana) LEFT JOIN penarikan pn USING(id_pelaksanaan) WHERE pl.id_pelaksanaan = in_id_pelaksanaan AND rn.status = 'SD' GROUP BY pl.id_pelaksanaan) pll
    WHERE pr.status != 'OK' AND pr.id_pencairan = in_id_pencairan FOR UPDATE;

    IF t_status != 'SD' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Status rencana belum disetujui';
        LEAVE KPLabel;
    END IF;

    CALL BeforePenarikan(in_id_pelaksanaan, t_nominal_penarikan, in_id_pencairan);

    SELECT b_penarikan.id_ca, nominal, gp.path_gambar, gp.nama,
    id_pinbuk_k, status_k, nominal_pindah_keluar sum_nominal_pindah_keluar, id_ca_penerima_keluar, gcpk.path_gambar path_gambar_keluar, gcpk.nama nama_keluar,
    id_pinbuk_m, status_m, nominal_pindah_masuk sum_nominal_pindah_masuk, id_ca_pengirim_masuk, gcpm.path_gambar path_gambar_masuk, gcpm.nama nama_masuk
    FROM
    (
        SELECT rlp.*, 
		    ldp_k.id_pinbuk id_pinbuk_k, ldp_k.status status_k, ldp_k.nominal_pindah_keluar, ldp_k.id_ca_penerima id_ca_penerima_keluar,
			ldp_m.id_pinbuk id_pinbuk_m, ldp_m.status status_m, ldp_m.nominal_pindah_masuk, ldp_m.id_ca_pengirim id_ca_pengirim_masuk
        FROM
        (
            SELECT id_ca, SUM(nominal) nominal
            FROM temp_penarikan_vl1
            GROUP BY id_ca
            ORDER BY id_ca
        ) rlp LEFT JOIN (
            SELECT v1.id_ca, p.id_pinbuk, p.status, SUM(IF(dp.nominal_pindah > v1.nominal, v1.nominal, dp.nominal_pindah)) nominal_pindah_keluar, p.id_ca_penerima
            FROM temp_penarikan_vresult1 v1 LEFT JOIN detil_pinbuk dp ON(dp.id_donasi = v1.id_donasi) LEFT JOIN pinbuk p USING(id_pinbuk)
            WHERE p.status != 'OK' AND v1.id_ca = p.id_ca_pengirim
            GROUP BY v1.id_ca, p.id_pinbuk
            ORDER BY p.id_pinbuk, v1.id_ca
        ) ldp_k ON (rlp.id_ca = ldp_k.id_ca) LEFT JOIN (
            SELECT v2.id_ca, p.id_pinbuk, p.status, SUM(dp.nominal_pindah) nominal_pindah_masuk, p.id_ca_pengirim
            FROM temp_penarikan_vresult2 v2 LEFT JOIN detil_pinbuk dp ON(dp.id_donasi = v2.id_donasi) LEFT JOIN pinbuk p USING(id_pinbuk)
            WHERE p.status != 'OK' AND v2.id_ca = p.id_ca_penerima
            GROUP BY p.id_pinbuk, v2.id_ca
        ) ldp_m ON (rlp.id_ca = ldp_m.id_ca)
        ORDER BY id_pinbuk_m, id_ca
    ) b_penarikan 
    LEFT JOIN channel_payment cp ON(cp.id_ca = b_penarikan.id_ca) LEFT JOIN gambar gp ON(gp.id_gambar = cp.id_gambar) 
    LEFT JOIN channel_payment cpk ON(cpk.id_ca = b_penarikan.id_ca_penerima_keluar) LEFT JOIN gambar gcpk ON(gcpk.id_gambar = cpk.id_gambar)
    LEFT JOIN channel_payment cpm ON(cpm.id_ca = b_penarikan.id_ca_pengirim_masuk) LEFT JOIN gambar gcpm ON(gcpm.id_gambar = cpm.id_gambar)
    WHERE cp.jenis NOT IN ('QR','GI') AND ((cpk.jenis NOT IN ('QR','GI') OR cpk.jenis IS NULL) AND (cpm.jenis NOT IN ('QR','GI') OR cpm.jenis IS NULL))
    ORDER BY id_pinbuk_m, id_ca;
END KPLabel$$
DELIMITER ;

-- DROP PROCEDURE InsertPenarikan;
DELIMITER $$
CREATE PROCEDURE InsertPenarikan(IN in_id_pelaksanaan INT UNSIGNED, IN in_persentase_penarikan DECIMAL(5,2), IN in_id_pencairan INT UNSIGNED)
    IPLabel:BEGIN
        DECLARE t_total_penarikan BIGINT UNSIGNED;
        DECLARE t_status CHAR(2);
        DECLARE t_id_bantuan, c_pinbuk_list INT UNSIGNED;
        DECLARE t_saldo_pencairan, t_saldo_pelaksanaan BIGINT UNSIGNED;

        IF (SELECT COUNT(id_pelaksanaan) FROM pelaksanaan WHERE id_pelaksanaan = in_id_pelaksanaan) = 0 THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Id Pelaksanaan tidak ditemukan';
            LEAVE IPLabel;
        END IF;

        IF (SELECT COUNT(id_pencairan) FROM pencairan WHERE id_pencairan = in_id_pencairan) = 0 THEN
            SIGNAL SQLSTATE '45001'
                SET MESSAGE_TEXT = 'Id Pencairan tidak ditemukan';
            LEAVE IPLabel;
        END IF;

        SELECT 
            IF(pr.total * (in_persentase_penarikan / 100) > pr.total - plc.total_penarikan_pr, 
                IF(pr.total - plc.total_penarikan_pr > pll.total_anggaran - pll.total_penarikan_pl,
                        pll.total_anggaran - pll.total_penarikan_pl,
                        pr.total - plc.total_penarikan_pr
                ),
                IF(pr.total * (in_persentase_penarikan / 100) > pll.total_anggaran - pll.total_penarikan_pl,
                    pll.total_anggaran - pll.total_penarikan_pl,
                    FLOOR(pr.total * (in_persentase_penarikan / 100))
                )
            ) total_penarikan, 
            pr.total - plc.total_penarikan_pr saldo_pencairan, pll.total_anggaran - pll.total_penarikan_pl saldo_pelaksanaan, pll.id_bantuan, pll.status INTO t_total_penarikan, t_saldo_pencairan, t_saldo_pelaksanaan, t_id_bantuan, t_status
        FROM pencairan pr, 
        (SELECT SUM(IFNULL(pn.nominal,0)) total_penarikan_pr FROM pencairan pr LEFT JOIN penarikan pn USING(id_pencairan) WHERE pr.id_pencairan = in_id_pencairan AND pr.status != 'OK' GROUP BY pn.id_pencairan) plc,
        (SELECT SUM(IFNULL(pn.nominal,0)) total_penarikan_pl, pl.total_anggaran, rn.status, rn.id_bantuan FROM rencana rn JOIN pelaksanaan pl USING(id_rencana) LEFT JOIN penarikan pn USING(id_pelaksanaan) WHERE pl.id_pelaksanaan = in_id_pelaksanaan AND rn.status = 'SD' GROUP BY pl.id_pelaksanaan) pll
        WHERE pr.status != 'OK' AND pr.id_pencairan = in_id_pencairan;

        IF t_status != 'SD' THEN
            SIGNAL SQLSTATE '45002'
                SET MESSAGE_TEXT = 'Status rencana belum disetujui';
            LEAVE IPLabel;
        END IF;

        IF t_saldo_pencairan = 0 THEN
            SIGNAL SQLSTATE '45003'
                SET MESSAGE_TEXT = 'Saldo ID Pencairan sudah habis';
            LEAVE IPLabel;
        END IF;

        IF t_saldo_pelaksanaan = 0 THEN
            SIGNAL SQLSTATE '45004'
                SET MESSAGE_TEXT = 'Saldo ID Pelaksanaan sudah habis';
            LEAVE IPLabel;
        END IF;

        IF t_saldo_pencairan < t_total_penarikan THEN
            SIGNAL SQLSTATE '45005'
                SET MESSAGE_TEXT = 'Saldo pencairan tidak mencukupi';
            LEAVE IPLabel;
        END IF;

        IF t_saldo_pelaksanaan < t_total_penarikan THEN
            SIGNAL SQLSTATE '45006'
                SET MESSAGE_TEXT = 'Saldo pelaksanaan tidak mencukup';
            LEAVE IPLabel;
        END IF;

        CALL BeforePenarikan(in_id_pelaksanaan, t_total_penarikan, in_id_pencairan);

        SELECT COUNT(id_pinbuk) FROM temp_penarikan_vresult1 tpv1 JOIN detil_pinbuk dp USING(id_donasi) JOIN pinbuk p USING(id_pinbuk) WHERE p.status != 'OK' AND tpv1.id_ca = p.id_ca_pengirim GROUP BY id_pelaksanaan, id_pencairan INTO c_pinbuk_list;
        IF c_pinbuk_list > 0 THEN
            SET @err_mess = CONCAT_WS(' ', 'Terdapat sejumlah',c_pinbuk_list,'donasi yang sedang dalam proses pinbuk');
            SIGNAL SQLSTATE '45007'
                SET MESSAGE_TEXT = @err_mess;
                SET @err_mess = NULL;
            LEAVE IPLabel;
        END IF;

        INSERT INTO penarikan(id_pelaksanaan,id_ca,nominal,id_pencairan)
        SELECT in_id_pelaksanaan, id_ca, SUM(nominal), in_id_pencairan
        FROM temp_penarikan_vresult1
        GROUP BY id_ca;
    END IPLabel$$
DELIMITER ;