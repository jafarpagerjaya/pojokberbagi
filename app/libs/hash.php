<?php
class Hash {
	public static function make($string, $salt = '') {
		return hash('md5', $string . $salt);
	}

	public static function salt($length = 32) {
		return substr(bin2hex(openssl_random_pseudo_bytes($length)), 0, $length);
	}

	public static function unique() {
		return self::make(uniqid());
	}
}