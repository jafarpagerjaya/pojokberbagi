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
}