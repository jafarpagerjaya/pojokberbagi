<?php
class Utility {
    public static function keteranganStatusBantuan($params) {
        if ($params == 'B') {
            $status = "Belum Disetujui oleh admin";
        } else if ($params == 'C') {
            $status = "Sedang dalam proses cek administrasi";
        } else if ($params == 'T') {
            $status = "Ditolak oleh admin";
        } else if ($params == 'D') {
            $status = "Disetujui oleh admin";
        } else if ($params == 'S') {
            $status = "Sudah selesai dilaksanakan";
        } 
        return $status;
    }

    public static function statusBantuan($params) {
        if ($params == 'B') {
            $status = "belum disetujui";
        } else if ($params == 'C') {
            $status = "cek petugas";
        } else if ($params == 'T') {
            $status = "ditolak";
        } else if ($params == 'D') {
            $status = "dibuka";
        } else if ($params == 'S') {
            $status = "ditutup";
        }
        return $status;
    }

    public static function statusBantuanClassText($status) {
        if(strtolower($status) == 'd') {
            $class = 'badge-primary';
            $status_text = 'Berjalan';
        } elseif (strtolower($status) == 's') {
            $class = 'badge-success';
            $status_text = "Selesai";
        } elseif (strtolower($status) == 'b') {
            $class = 'badge-warning'; 
            $status_text = "Menunggu Penilaian";
        } elseif (strtolower($status) == 'c') {
            $class = 'badge-info'; 
            $status_text = "Penilaian";
        } else {
            $status_text = "Ditolak";
            $class = 'badge-secondary'; 
        }
        return array(
            'class' => $class, 
            'text' => $status_text
        );
    }

    public static function keteranganJenisChannelPayment($params) {
        $params = strtoupper($params);
        if ($params == 'TB') {
            $metode_bayar = "Transfer Bank";
        } else if ($params == 'QR') {
            $metode_bayar = "QRIS";
        } else if ($params == 'EW') {
            $metode_bayar = "E-Wallet";
        } else if ($params == 'VA') {
            $metode_bayar = "Virtual Akun";
        } else if ($params == 'GM') {
            $metode_bayar = "Gerai Mart";
        } else if ($params == 'GI') {
            $metode_bayar = "Giro";
        } else if ($params == 'TN') {
            $metode_bayar = "Tunai";
        } else {
            $metode_bayar = "Unrecognize (Payment Method)";
        }
        return $metode_bayar;
    }

    public static function contackToLocal($kontak) {
        return preg_replace('/^0/', '+62', $kontak);
    }

    public static function blokirClassText($blokir) {
        if ($blokir == 1) {
            $status_blokir_class = 'bg-danger'; $blokir_status_text = 'Diblok';
        } else {
            $status_blokir_class = 'bg-success'; $blokir_status_text = 'Aktif';
        }
        return array(
            'class' => $status_blokir_class,
            'text' => $blokir_status_text
        );
    }

    public static function imploderArray($params = array(), $implodeby = ' - ') {
        $array = array();
        return implode($implodeby, $params);
    }
}