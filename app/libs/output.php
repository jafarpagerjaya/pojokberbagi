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
        $formatter = new IntlDateFormatter(LOCALE_IDT, IntlDateFormatter::LONG, IntlDateFormatter::NONE, "Asia/Jakarta");
        $time = new DateTime($time);
        return $formatter->format($time);
    }

    public static function strToFullLocalDate($time) {
        $formatter = new IntlDateFormatter(LOCALE_IDT, IntlDateFormatter::FULL, IntlDateFormatter::LONG, "Asia/Jakarta");
        $time = new DateTime($time);
        return $formatter->format($time);
    }

    public static function strToFullLocalDateTime($time) {
        $formatter = new IntlDateFormatter(LOCALE_IDT, IntlDateFormatter::FULL, IntlDateFormatter::FULL, "Asia/Jakarta");
        $time = new DateTime($time);
        return $formatter->format($time);
    }
}