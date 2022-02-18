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

    public static function strToLocalDate($time) {
        return strftime("%d-%b-%Y", strtotime($time));
    }

    public static function strToFullLocalDate($time) {
        return strftime("%A, %d %B %Y", strtotime($time));
    }

    public static function strToFullLocalDateTime($time) {
        return strftime("%A, %d %B %Y %H:%M:%S", strtotime($time));
    }
}