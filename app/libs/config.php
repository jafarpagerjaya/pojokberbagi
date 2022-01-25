<?php
class Config {
	private static $_device;

	final public static function get($path = null) {
		if ($path) {
			$config = $GLOBALS['config'];
			$path = explode('/', $path);

			foreach ($path as $bit) {
				if (isset($config[$bit])) {
					$config = $config[$bit];
				}
			}

			return $config;
		}

		return false;
	}

	final public static function getClientIP() {
		if (isset($_SERVER)) {

			if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
				return $_SERVER["HTTP_X_FORWARDED_FOR"];
	
			if (isset($_SERVER["HTTP_CLIENT_IP"]))
				return $_SERVER["HTTP_CLIENT_IP"];
	
			return $_SERVER["REMOTE_ADDR"];
		}
	
		if (getenv('HTTP_X_FORWARDED_FOR'))
			return getenv('HTTP_X_FORWARDED_FOR');
	
		if (getenv('HTTP_CLIENT_IP'))
			return getenv('HTTP_CLIENT_IP');
	
		return getenv('REMOTE_ADDR');
	}

	final public static function getClientDevice($params = null) {
		if (!isset(self::$_device)) {
			$Browser = new foroco\BrowserDetection();

			// Get all possible environment data (array):
			$result = $Browser->getAll($_SERVER['HTTP_USER_AGENT']);

			$device = array(
				'os' => $result['os_title'],
				'os_bit' => ($result['64bits_mode'] == 1 ? '64 bit (x64)' : '32 bit (x86)'),
				'browser' => $result['browser_name'],
				'browser_version' => $result['browser_version'],
				'device_type' => $result['device_type']
			);
			self::$_device = $device;
		}
		if (isset($params)) {
			return self::$_device[$params];
		} else {
			return self::$_device;
		}
	}

	final public static function getHTTProtocol() {
		if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
			$protocol = 'https://';
		}
		else {
			$protocol = 'http://';
		}
		return $protocol;
	}

	final public static function getHTTPHost() {
		return Config::getHTTProtocol(). ''. $_SERVER['HTTP_HOST'];
	}
}