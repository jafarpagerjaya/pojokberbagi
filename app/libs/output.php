<?php
class Output {
    public static function tSparator($int) {
        return number_format($int,0,',','.');
    }

    public static function timeNullUnlimited($date) {
        return (is_null($date) ? 'Unlimited' : $date);
    }

    public static function timeToLocal($time) {
        return date('d/m/Y',strtotime($time));
    }

    public static function timeToLocalFull($time) {
        return date('d F Y',strtotime($time));
    }
}