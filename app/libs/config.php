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

	public static function replaceKey($arr, $oldkey, $newkey) {
		if(array_key_exists( $oldkey, $arr)) {
			$keys = array_keys($arr);
			$keys[array_search($oldkey, $keys)] = $newkey;
			return array_combine($keys, $arr);	
		}
		return $arr;    
	}

	public static function recursiveChangeKey($arr, $set) {
        if (is_array($arr) && is_array($set)) {
    		$newArr = array();
    		foreach ($arr as $k => $v) {
    		    $key = array_key_exists( $k, $set) ? $set[$k] : $k;
    		    $newArr[$key] = is_array($v) ? self::recursiveChangeKey($v, $set) : $v;
    		}
    		return $newArr;
    	}
    	return $arr;    
    }

	public static function search($array, $key, $value)
	{
		$results = array();
		if (is_array($array)) {
			if (isset($array[$key]) && $array[$key] == $value) {
				$results[] = $array;
			}
			foreach ($array as $subarray) {
				$results = array_merge($results, self::search($subarray, $key, $value));
			}
		}
		return $results;
	}

	public static function array_flatten($array) {
		$result = [];
		foreach ($array as $element) {
			if (is_array($element)) {
			$result = array_merge($result, self::array_flatten($element));
			} else {
			$result[] = $element;
			}
		}
		return $result;
	}

	public static function move_file($path, $to){
		if (!rename(BASEURL . $path, BASEURL . $to)) {
			return false;
		}
		
		return true;
	}
}