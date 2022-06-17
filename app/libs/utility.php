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
            $status = "Open donasi telah disetujui untuk dibuka";
        } else if ($params == 'S') {
            $status = "Open donasinya sudah ditutup";
        } 
        return $status;
    }

    public static function statusBantuanClassTextColor($params) {
        if ($params == 'B') {
            $status_text = "belum disetujui";
            $class = 'text-black-50';
        } else if ($params == 'C') {
            $status_text = "cek petugas";
            $class = 'text-warning';
        } else if ($params == 'T') {
            $status_text = "ditolak";
            $class = 'text-danger';
        } else if ($params == 'D') {
            $status_text = "dibuka";
            $class = 'text-success';
        } else if ($params == 'S') {
            $status_text = "ditutup";
            $class = 'text-primary';
        }
        return array(
            'class' => $class,
            'text' => $status_text
        );
    }

    public static function statusBantuanClassTextBadge($status) {
        if(strtolower($status) == 'd') {
            $class = 'badge-primary';
            $status_text = 'Berjalan';
        } elseif (strtolower($status) == 's') {
            $class = 'badge-danger';
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

    public static function blokUnblockClassText($blokir) {
        if ($blokir != 1) {
            $status_blokir_class = 'text-danger'; $blokir_status_text = 'Blockir';
        } else {
            $status_blokir_class = 'text-warning'; $blokir_status_text = 'Unblock';
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

    public static function setProgressClass($value) {
        if ($value < 20) {
            $class = "bg-danger";
        } else if ($value >= 20 && $value < 40) {
            $class = "bg-warning";
        } else if ($value >= 40 && $value < 60) {
            $class = "bg-primary";
        } else if ($value >= 60 && $value < 80) {
            $class = "bg-success";
        } else {
            $class = "bg-info";
        }
        return $class;
    }

    public static function iconSektor($id_sector) {
        if ($id_sector == 'S') {
            $icon = '<i class="lni lni-heart"></i>';
        } elseif ($id_sector == 'E') {
            $icon = '<i class="lni lni-bar-chart"></i>';
        } elseif ($id_sector == 'B') {
            $icon = '<i class="lni lni-warning"></i>';
        } elseif ($id_sector == 'K') {
            $icon = '<i class="lni lni-sthethoscope"></i>';
        } elseif ($id_sector == 'P') {
            $icon = '<i class="lni lni-graduation"></i>';
        } elseif ($id_sector == 'L') {
            $icon = '<i class="lni lni-sprout"></i>';
        } else {
            $icon = '<i class="lni lni-support"></i>';
        }
        return $icon;
    }
}