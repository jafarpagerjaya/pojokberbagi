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

    public static function keteranganJenisChannelPayment($params) {
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
        } else {
            $metode_bayar = "Unrecognize (Payment Method)";
        }
        return $metode_bayar;
    }

    public static function contackToLocal($kontak) {
        return preg_replace('/^0/', '+62', $kontak);
    }
}