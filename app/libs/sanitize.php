<?php
class Sanitize {
	public static function escape($string) {
		return htmlentities($string, ENT_QUOTES, 'UTF-8');
	}

	public static function noSpace($string){
		return trim(preg_replace('/\s+/', '', $string));
	}

	public static function noDblSpace($string){
		return trim(preg_replace('/\s+/', ' ', $string));
	}

	public static function toInt($string) {
		return preg_replace("/[^0-9]/", '', $string);
	}

	public static function escape2($string) {
		return htmlentities(trim($string), ENT_QUOTES, 'UTF-8');
	}

	public static function noSpace2($string){
		return Self::escape2(preg_replace('/\s+/', '', $string));
	}

	public static function noDblSpace2($string){
		return Self::escape2(preg_replace('/\s+/', ' ', $string));
	}

	public static function toInt2($string) {
		return Self::escape2(preg_replace("/[^0-9]/", '', $string));
	}
}