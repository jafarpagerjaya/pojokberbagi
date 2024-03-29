<?php
class Cookie {
	public static function exists($name) {
		return (isset($_COOKIE[$name])) ? true : false;
	}

	public static function get($name) {
		return $_COOKIE[$name];
	}

	public static function put($name, $value, $expiry, $path = '/') {
		if (setcookie($name, $value, time() + $expiry, $path)) {
			return true;
		}
	}

	public static function update($name, $value, $expiry, $path = '/') {
		if (setcookie($name, $value, $expiry, $path)) {
			return true;
		}
	}

	public static function delete($name, $path = '/') {
		self::put($name, '', time() - 1, $path);
	}
}