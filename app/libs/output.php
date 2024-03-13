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

    public static function decodeEscape($string) {
        return html_entity_decode($string ?? '', ENT_QUOTES, "UTF-8");
    }

    public static function decodeEscapeArray($array) {
        $result = [];
		foreach ($array as $key => $element) {
			if (is_array($element)) {
			    $result = array_merge($result, self::decodeEscapeArray($element));
			} else {
			    $result[$key] = self::decodeEscape($element);
			}
		}
		return $result;
    }

    public static function getYoutubeIdFromUrl($url) {
        $parts = parse_url($url);
        if(isset($parts['query'])){
            parse_str($parts['query'], $qs);
            if(isset($qs['v'])){
                return $qs['v'];
            }else if(isset($qs['vi'])){
                return $qs['vi'];
            }
        }
        if(isset($parts['path'])){
            $path = explode('/', trim($parts['path'], '/'));
            return $path[count($path)-1];
        }
        return false;
    }
}