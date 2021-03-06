<?php
	class Input {
		public static function exists($type = 'post') {
			switch ($type) {
				case 'post':
					return (!empty($_POST)) ? true : false;
					break;
				case 'get':
					return (!empty($_GET)) ? true : false;
					break;
				default:
					return false;
					break;
			}
		}

		public static function get($field) {
			if (isset($_POST[$field])) {
				return Sanitize::escape($_POST[$field]);
			} else if (isset($_GET[$field])) {
				return Sanitize::escape($_GET[$field]);
			}
			return '';
		}
	}