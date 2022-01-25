<?php
class Redirect {
	public static function to ($location = null) {
		if ($location) {
			switch($location) {
				case 403:
					header("HTTP/1.0 403 Forbidden");
					exit();
				break;
				case 404:
					header('HTTP/1.0 404 Not Found');
					exit();
				break;
			}
			header('location: ' . DS . $location);
			exit();
		}
	}
}