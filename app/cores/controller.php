<?php
class Controller {
	protected $data,
			  $model,
			//   $view,
			  $rel_controller,
			  $rel_action,
			  $script_controller,
			  $script_action,
			  $page_record_limit = 10;

	private $_create_pengunjung = false, 
			$_update_pengunjung = false,
			$_client = array(),
			$_update_client_key = false,
			$_set_cookie = true;

	public function getData() {
		return $this->data;
	}

	public function getModel() {
		return $this->model;
	}

	private function removeArray($array, $keys) {
		if (is_array($keys)) {
			foreach($keys as $key){
				unset($array[$key]);
			}
		}
		return $array;
	}

	protected function removeRelController($keys){
		$this->rel_controller = $this->removeArray($this->rel_controller, $keys);
	}

	protected function removeScriptController($keys){
		$this->script_controller = $this->removeArray($this->script_controller, $keys);
	}

	public function getRelScript($property) {
		if (!empty($this->$property)) {
			return $this->$property;
		}
	}

	protected function model($file) {
		// $route = App::getRouter()->getRoute();
		if (file_exists(ROOT . 'app'. DS .'models'. DS . strtolower($file) . '.php')) {
			require_once ROOT . 'app'. DS .'models'. DS . strtolower($file) . '.php';
			$class = ucfirst($file).'Model';
	       	return $this->model = new $class;
		}
	}

	protected function setKunjungan2($params = null, $js_uri = null, $js_path = null) {
		$db = Database::getInstance();

		if (!Cookie::exists(Config::get('client/cookie_name'))) {
			$this->_client = array(
				'client_ip' => Config::getClientIP(),
				'expiry' => time() + Config::get('client/cookie_expiry'),
				'device_id' => $this->getClientDeviceID()
			);

			$this->_create_pengunjung = true;

			$cookie_value = $this->_client;
		} else {
			$cookie_value = Sanitize::thisArray(json_decode(base64_decode(Cookie::get(Config::get('client/cookie_name'))), true));

			if (!isset($cookie_value['client_ip'])) {
				$cookie_value['client_ip'] = '0.0.0.0';
			}
			if (!isset($cookie_value['device_id'])) {
				$cookie_value['device_id'] = 'NULL';
			}
			$cekPengunjung = $db->query("SELECT COUNT(id_pengunjung) cek FROM pengunjung WHERE ip_address = ? AND device_id = ? AND client_key = ? AND modified_at >= NOW() - INTERVAL 1 YEAR", 
			array(
				$cookie_value['client_ip'], 
				$cookie_value['device_id'],
				Cookie::get(Config::get('client/cookie_name'))
			));
			
			if ($cekPengunjung->result()->cek == 0) {
				$this->_create_pengunjung = true;
				$this->_client = array(
					'client_ip' => Config::getClientIP(),
					'expiry' => time() + Config::get('client/cookie_expiry'),
					'device_id' => $this->getClientDeviceID()
				);
			} else {
				// Tiap cookie sudah expiry
				if (time() > $cookie_value['expiry']) {
					$cookie_value['expiry'] = time() + Config::get('client/cookie_expiry');
					$this->_update_client_key = true;
				} else {
					$this->_set_cookie = false;
				}
				
				// ** Optional **
				// Tiap Hari update cookie
				// $current_expires = date_create(date("Y-m-d", $cookie_value['expiry']));
				// $target_expires = date_create(date("Y-m-d", time() + Config::get('client/cookie_expiry')));
				// $date_diff = date_diff($current_expires, $target_expires);
				// if ($date_diff->format("%R%a") > 0) {
				// 	$cookie_value['expiry'] = time() + Config::get('client/cookie_expiry');
				// 	$this->_update_client_key = true;
				// } else {
				// 	$this->_set_cookie = false;
				// }

				$this->_client = $cookie_value;
				$this->setClientDeviceID($cookie_value['device_id']);
			}
		}

		if ($this->_create_pengunjung) {
			$this->_client_key = base64_encode(json_encode($this->_client));

			// Data tabel pengunjung perlu ditambahkan kolom baru yaitu geoloc ip (kota, kodepos, lat, mat) 
			$db->insert('pengunjung',array(
				'ip_address' => Sanitize::escape(trim($this->_client['client_ip'])),
				'client_key' => Sanitize::escape(trim($this->_client_key)),
				'device_id' => Sanitize::escape(trim($this->_client['device_id'])),
				'os' => Config::getClientDevice('os'),	
				'os_bit' => Config::getClientDevice('os_bit'),
				'browser' => Config::getClientDevice('browser'),
				'browser_version' => Config::getClientDevice('browser_version'),
				'device_type' => Config::getClientDevice('device_type')
			));
			$this->_client['id_pengunjung'] = $db->lastInsertId();
			$this->_update_client_key = true;
		}


		if ($this->_update_client_key) {
			$this->_client_key = base64_encode(json_encode($this->_client));

			$db->update('pengunjung', array(
				'client_key' => Sanitize::escape2(trim($this->_client_key))
			), array('id_pengunjung', '=', $this->_client['id_pengunjung']));
		}

		if ($this->_set_cookie) {
			Cookie::put(Config::get('client/cookie_name'), $this->_client_key, Config::get('client/cookie_expiry'));
		}

		$path = Sanitize::escape(trim($this->getPath($params, $js_path)));
		$uri = Sanitize::escape(trim($this->getUri($js_uri)));

		$dataHalaman = $db->get('id_halaman, track','halaman', array('path','=',$path));

		if (!$dataHalaman->count()) {
			try {
				$db->insert('halaman', array(
					// 'uri' => $this->getUri($params, $js_uri)
					'uri' => $uri,
					'path' => $path
				));
				$id_halaman = $db->lastInsertId();
			} catch (\Throwable $e) {
				Session::flash('error', $e->getMessage());
				return $this;
			}
		} else {
			if ($dataHalaman->result()->track != 1) {
				return false;
			}
			$id_halaman = $dataHalaman->result()->id_halaman;
		}

		$cekKunjungan = $db->query("SELECT count(*) cek FROM kunjungan WHERE id_pengunjung = ? AND id_halaman = ? AND DATE(create_at) = DATE(NOW())", array('id_pengunjung' => Sanitize::escape(trim($this->_client['id_pengunjung'])), 'id_halaman' => Sanitize::escape(trim($id_halaman))))->result();

		if ($cekKunjungan->cek == 0) {
			$db->insert('kunjungan',array(
				'id_halaman' => $id_halaman,
				'id_pengunjung' => $this->_client['id_pengunjung']
			));
		}
    }

	private function unrecognizeDevice() {
		$device_id = $this->getClientDeviceID();
		$found = true;
		do {
			$this->model->getData('COUNT(id_pengunjung) count', 'pengunjung', array('device_id', '=', $device_id));
			if ($this->model->data()->count != 0) {
				$device_id = Hash::unique();
			} else {
				$found = false;
			}
		} while ($found == true);

		$data_client = array(
			'ip_address' => Config::getClientIP(),
			'expiry' => time() + Config::get('client/cookie_expiry'),
			'device_id' => $device_id
		);
		$cookie_expiry = $data_client['expiry'];
		$client_key = base64_encode(json_encode($data_client));
		// $data_client['client_key'] = $client_key;
		unset($data_client['expiry']);
		$data_client = array_merge($data_client, array(
			'os' => Config::getClientDevice('os'),	
			'os_bit' => Config::getClientDevice('os_bit'),
			'browser' => Config::getClientDevice('browser'),
			'browser_version' => Config::getClientDevice('browser_version'),
			'device_type' => Config::getClientDevice('device_type'),
		));
		$this->_create_pengunjung = true;
		$data = array(
			'cookie_expiry' => $cookie_expiry,
			'client_key' => $client_key,
			'data_client' => $data_client
		);

		$this->setClientDeviceID($device_id);

		return $this->_client = $data;
	}

	protected function setKunjungan($params = null, $js_uri = null, $js_path = null) {
		// Debug::pr('<center>'.$this->getClientDeviceID().'</center>');
		$db = Database::getInstance();
		if (!Cookie::exists(Config::get('client/cookie_name'))) {
			// Cookie Not Exists
			$db->query('SELECT id_pengunjung, client_key FROM pengunjung WHERE ip_address = ? AND os = ? AND browser = ? AND device_type = ? AND device_id = ?', array(
				Config::getClientIP(), 
				Sanitize::escape(trim(Config::getClientDevice('os'))), 
				Sanitize::escape(trim(Config::getClientDevice('browser'))),
				Sanitize::escape(trim(Config::getClientDevice('device_type'))),
				$this->getClientDeviceID()
			));
			if ($db->count()) {
				// Device Recognize
				$id_pengunjung = $db->result()->id_pengunjung;
				$this->_client['client_key'] = $db->result()->client_key;
				$this->_client['cookie_expiry'] = json_decode(base64_decode($this->_client['client_key']), true)['expiry'];
				$this->_update_pengunjung = true;
			} else {
				// Device Unrecognize
				$this->unrecognizeDevice();
			}
		} else {
			// Cookie Exists
			$data_cookie = Cookie::get(Config::get('client/cookie_name'));
			$data_client = json_decode(base64_decode($data_cookie), true);
			if ($this->getClientDeviceID() == $data_client['device_id']) {
				// Real Cookies
				$data_client['ip_address'] = Config::getClientIP();
				$db->query('SELECT COUNT(id_pengunjung) found FROM pengunjung WHERE id_pengunjung = ? AND ip_address = ? AND device_id = ?', 
				array(
					Sanitize::escape(trim($data_client['id_pengunjung'])), 
					Sanitize::escape(trim($data_client['ip_address'])),
					Sanitize::escape(trim($data_client['device_id']))
				));
				if ($db->result()->found == 0) {
					// Device Unrecognize
					$this->unrecognizeDevice();
				} else {
					$db->query('SELECT id_pengunjung, client_key FROM pengunjung WHERE id_pengunjung = ? AND os = ? AND browser = ? AND os_bit = ? AND device_type = ?', array(
						Sanitize::escape(trim($data_client['id_pengunjung'])), 
						Sanitize::escape(trim(Config::getClientDevice('os'))),
						Sanitize::escape(trim(Config::getClientDevice('browser'))),
						Sanitize::escape(trim(Config::getClientDevice('os_bit'))),
						Sanitize::escape(trim(Config::getClientDevice('device_type')))
					));
					if ($db->count()) {
						$id_pengunjung = $db->result()->id_pengunjung;
						$this->_client['client_key'] = $db->result()->client_key;
						$this->_client['cookie_expiry'] = json_decode(base64_decode($this->_client['client_key']), true)['expiry'];
						$this->_update_pengunjung = true;
					} else {
						// Device Unrecognize
						$this->unrecognizeDevice();
					}
				}
			} else {
				// Fake Cookies
				$this->unrecognizeDevice();
			}
		}

		if ($this->_create_pengunjung == true) {
			$this->_client['data_client']['client_key'] = $this->_client['client_key'];
			try {
				$db->insert('pengunjung', $this->_client['data_client']);
				$id_pengunjung = $db->lastInsertId();
				$this->_update_pengunjung = true;
			} catch (\Throwable $th) {
				Session::flash('error', $th->getMessage());
			}
		}

		if ($this->_update_pengunjung == true) {
			if ($this->_client['cookie_expiry'] <= time()) {
				$this->_client['cookie_expiry'] = time() + Config::get('client/cookie_expiry');
			}

			$this->_client['client_key'] = json_decode(base64_decode($this->_client['client_key']), true);
			$this->_client['client_key']['expiry'] = $this->_client['cookie_expiry'];
			$this->_client['client_key']['id_pengunjung'] = $id_pengunjung;
			$this->_client['client_key'] = base64_encode(json_encode($this->_client['client_key']));
			$db->update('pengunjung', array('client_key' => $this->_client['client_key']), array('id_pengunjung', '=', Sanitize::escape(trim($id_pengunjung))));

			Cookie::update(Config::get('client/cookie_name'), $this->_client['client_key'], time() + $this->_client['cookie_expiry']);
		}

		// $dataHalaman = $db->get('id_halaman','halaman',array('uri','=',$this->getUri($params, $js_uri)));
		$path = Sanitize::escape(trim($this->getPath($params, $js_path)));
		$uri = Sanitize::escape(trim($this->getUri($js_uri)));

		$dataHalaman = $db->get('id_halaman, track','halaman', array('path','=',$path));

		if (!$dataHalaman->count()) {
			try {
				$db->insert('halaman', array(
					// 'uri' => $this->getUri($params, $js_uri)
					'uri' => $uri,
					'path' => $path
				));
				$id_halaman = $db->lastInsertId();
			} catch (\Throwable $e) {
				Session::flash('error', $e->getMessage());
				return $this;
			}
		} else {
			if ($dataHalaman->result()->track != 1) {
				return false;
			}
			$id_halaman = $dataHalaman->result()->id_halaman;
		}

		$cekKunjungan = $db->query("SELECT count(*) cek FROM kunjungan WHERE id_pengunjung = ? AND id_halaman = ? AND DATE(create_at) = DATE(NOW())", array('id_pengunjung' => Sanitize::escape(trim($id_pengunjung)), 'id_halaman' => Sanitize::escape(trim($id_halaman))))->result();

		if ($cekKunjungan->cek == 0) {
			$db->insert('kunjungan',array(
				'id_halaman' => $id_halaman,
				'id_pengunjung' => $id_pengunjung
			));
		}
    }

	protected function setClientDeviceID($params = null) {
		if (!is_null($params)) {
			Session::put(Config::get('session/device_name'), $params);
		}
		if (!Session::exists(Config::get('session/device_name'))) {
			Session::put(Config::get('session/device_name'), Hash::unique());
		}
	}

	protected function getClientDeviceID() {
		if (!Session::exists(Config::get('session/device_name'))) {
			Session::put(Config::get('session/device_name'), Hash::unique());
		}
		return Session::get(Config::get('session/device_name'));
	}

	// private function getUri($params = null, $js_uri = null) {
	// 	if (is_null($js_uri)) {
	// 		$uri = array(
	// 			App::getRouter()->getRoute(),
	// 			App::getRouter()->getController(),
	// 			App::getRouter()->getAction()
	// 		);
	
	// 		if (count(is_countable($params) ? $params : [])) {
	// 			if (!is_array($params)) {
	// 				$params = array($params);
	// 			}
	// 			$params = implode('/',$params);
	// 			$uri[] = $params;
	// 		}
	// 		$uri = implode('/', $uri);
	// 	} else {
	// 		$uri = $js_uri;
	// 	}
	// 	return $uri;
	// }

	protected function getRealUri() {
		return $this->getPath();
	}

	protected function getMetaUri() {
		$path = array(
			Config::getHTTPHost()
		);
		
		if (App::getRouter()->getLanguages() != 'id') {
			array_push($path, App::getRouter()->getLanguages());
		}

		if (App::getRouter()->getRoute() != 'default') {
			array_push($path, App::getRouter()->getRoute());
		}

		$path = array_merge($path, array(App::getRouter()->getController(), App::getRouter()->getAction(), App::getRouter()->getParams()[0]));

		return implode('/', $path);
	}

	private function getPath($params = null, $js_path = null) {
		if (is_null($js_path)) {
			$path = array(
				App::getRouter()->getRoute(),
				App::getRouter()->getController(),
				App::getRouter()->getAction()
			);
			// PHP 7.2 + count()
			// count(is_countable($params) ? $params : [])
			if (count(is_countable($params) ? $params : [])) {
				if (!is_array($params)) {
					$params = array($params);
				}
				$params = implode('/',$params);
				$path[] = $params;
			}
			$path = implode('/', $path);
		} else {
			$path = $js_path;
		}
		return $path;
	}

	private function getUri($js_uri = null) {
		if (is_null($js_uri)) {
			$uri = App::getRouter()->getUri();
			if ($uri == '') {
				$uri = Config::getHTTProtocol();
				$uri .= $_SERVER['HTTP_HOST'];
			}
		} else {
			$uri = $js_uri;
		}
		return $uri;
	}

	protected function setPageRecordLimit($limit) {
		$this->page_record_limit = $limit;
	}

	protected function getPageRecordLimit() {
		return $this->page_record_limit;
	}
}