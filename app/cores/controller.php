<?php
class Controller {
	protected $data,
			  $model,
			//   $view,
			  $rel_controller,
			  $rel_action,
			  $script_controller,
			  $script_action,
			  $page_record_limit = 10,
			  $cookie_exists_signal = false;
	
	private $_create_pengunjung = false, 
			$_update_pengunjung = false,
			$_client = array();

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

	// protected function setKunjungan($params = null) {

	// 	$db = Database::getInstance();

    //     $cekPengunjung = $db->get('id_pengunjung','pengunjung',array('ip_address','=',Config::getClientIP()),'and',array('mac_address','=',Config::getClientMac()));
        
	// 	if (!$cekPengunjung->count()) {
	// 		$db->insert('pengunjung',array(
	// 			'ip_address' => Config::getClientIP(),
	// 			'mac_address' => Config::getClientMac()
	// 		));
    //     }

	// 	$pengunjung = $db->get('id_pengunjung','pengunjung',array('ip_address','=',Config::getClientIP()),'and',array('mac_address','=',Config::getClientMac()))->result();

	// 	$halaman = $db->get('id_halaman','halaman',array('uri','=',$this->getUri($params)));
	// 	if (!$halaman->count()) {
	// 		$db->insert('halaman',array(
	// 			'uri' => $this->getUri($params)
	// 		));
	// 		$halaman = $db->get('id_halaman','halaman',array('uri','=',$this->getUri($params)));
	// 	}
	// 	$halaman = $halaman->result();

	// 	$cekKunjungan = $db->query("SELECT count(*) cek FROM kunjungan WHERE id_pengunjung = ? AND id_halaman = ? AND DATE(create_at) = DATE(NOW())", array('id_pengunjung' => $pengunjung->id_pengunjung, 'id_halaman' => $halaman->id_halaman))->result();

	// 	if ($cekKunjungan->cek == 0) {
	// 		$db->insert('kunjungan',array(
	// 			'id_halaman' => $halaman->id_halaman,
	// 			'id_pengunjung' => $pengunjung->id_pengunjung
	// 		));
	// 	}
    // }

	protected function setKunjungan2($params = null) {
		if (!Cookie::exists(Config::get('client/cookie_name'))) {
			$client = json_encode(array(
				'client_ip' => Config::getClientIP(),
				'expiry' => time() + Config::get('client/cookie_expiry')
			));
			Cookie::put(Config::get('client/cookie_name'), base64_encode($client), Config::get('client/cookie_expiry'));
			$this->cookie_exists_signal = true;
		}

		if ($this->cookie_exists_signal) {
			$cookie_value = $client;
		} else {
			$cookie_value = base64_decode(Cookie::get(Config::get('client/cookie_name')));
		}

		$cookie_value = json_decode($cookie_value);

		// if (isset($cookie_value->auth)) {
		// 	$cookie_value	
		// }

		$client_key = base64_encode(json_encode($cookie_value));

		$db = Database::getInstance();
		// 
		// Data tabel pengunjung perlu ditambahkan kolom baru yaitu geoloc ip(kota, kodepos, lat, mat) 
		// 
		$cekPengunjung = $db->query("SELECT id_pengunjung FROM pengunjung WHERE ip_address = ? AND client_key = ? AND modified_at >= NOW() - INTERVAL 1 DAY", 
		array(
			Sanitize::escape(trim($cookie_value->client_ip)), 
			Sanitize::escape(trim($client_key))
		));

		if ($cekPengunjung->count() > 0) {
			$id_pengunjung = $cekPengunjung->result()->id_pengunjung;
		} else {
			if (!isset($cookie_value->auth)) {
				$db->insert('pengunjung',array(
					'ip_address' => Sanitize::escape(trim($cookie_value->client_ip)),
					'client_key' => Sanitize::escape(trim($client_key)),
					'os' => Config::getClientOS(),
					'browser' => Config::getClientBrowser()
				));
				$id_pengunjung = $db->lastInsertId();
			} else {
				// Rute Dari Signin.php Langsung Dengan auth True
				$id_pengunjung = $cookie_value->id_pengunjung;
			}
		}		
		
		if ($this->cookie_exists_signal) {
			// Debug::pr($cookie_value);
			
			// Add new property
			$cookie_value->id_pengunjung = $id_pengunjung;
			$client = base64_encode(json_encode($cookie_value));
			$expiry = $cookie_value->expiry;
			// Debug::pr($cookie_value);
			// Debug::vd('no cookie first');
			// Debug::vd($cookie_value);
			// Do Update Value Cookie
			Cookie::update(Config::get('client/cookie_name'), $client, $expiry);
			// Do Update client_key
			$db->update('pengunjung', array('client_key' => $client), array('id_pengunjung', '=', Sanitize::escape(trim($id_pengunjung))));
		}

		$dataHalaman = $db->get('id_halaman','halaman',array('uri','=',$this->getUri($params)));
		if (!$dataHalaman->count()) {
			try {
				$db->insert('halaman',array(
					'uri' => $this->getUri($params)
				));
				$id_halaman = $db->lastInsertId();
			} catch (\Throwable $e) {
				Session::flash('error', $e->getMessage());
				return $this;
			}
		} else {
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

			Cookie::update(Config::get('client/cookie_name'), $this->_client['client_key'], $this->_client['cookie_expiry']);
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