<?php
class AuthModel {
	private $_db,
			$_data,
			$_sessionName,
			$_cookieName,
			$_isSignIn,
			$_accessRight;

	public function __construct($akun = null) {
		$this->_db = Database::getInstance();
		$this->_sessionName = Config::get('session/session_name');
		$this->_cookieName = Config::get('remember/cookie_name');

		if (!$akun) {
			if (Session::exists($this->_sessionName)) {
				$akun = Session::get($this->_sessionName);

				if ($this->findBy($akun)) {
					$this->_isSignIn = true;
				} else {
					// $this->logout();
					Session::delete($this->_sessionName);
				}
			}
		} else {
			$this->findBy($akun);
		}
	}

	public function create($fields = array()) {
		if (!$this->_db->insert('akun', $fields))  {
			throw new Exception("Error Processing Insert Akun");
		}
	}

	public function createDynamic($table, $fields = array()) {
		if (!$this->_db->insert($table, $fields))  {
			throw new Exception("Error Processing Insert ". $table);
		}
	}

	public function update($fields = array(), $id = null) {
		if (!$id && $this->isSignIn()) {
			$id = $this->data()->id_akun;
		}
		if (!$this->_db->update('akun', $fields, array('id_akun', '=', $id))) {
			throw new Exception("Error Processing Update");
		}
	}

	public function updateDynamic($table, $fields = array(), $id = null) {
		if (!$id && $this->isSignIn()) {
			$id = $this->data()->id_akun;
		}

		if (!$this->_db->update($table, $fields, array('id_akun', '=', $id))) {
			throw new Exception("Error Processing Update");
		}
	}

	public function delete($table = 'akun', $fields = array()) {
		if (!$this->_db->delete($table, $fields)) {
			throw new Exception("Error Processing Delete");
		}
	}

	public function otherPermission($current_hak_akses = null) {
		if ($this->isSignIn()) {
			$data =  $this->_db->get('izin', 'akses', array('hak_akses', '=', $this->data()->hak_akses));
			$this->_accessRight = $data->result()->izin;

			if ($this->_accessRight) {
				$permession = json_decode($this->_accessRight, true);
				if (array_key_exists($current_hak_akses, $permession)) {
					return array_diff_key($permession, array($current_hak_akses => 1));
				}
			}	
		}
		return false;
	}
	
	public function hasPermission($hak_akses = null) {
		if ($this->isSignIn()) {
			$data =  $this->_db->get('izin', 'akses', array('hak_akses', '=', $this->data()->hak_akses));
			$this->_accessRight = $data->result()->izin;

			if ($this->_accessRight) {
				$permession = json_decode($this->_accessRight, true);
				if (array_key_exists($hak_akses, $permession)) {
					return true;
				}
			}	
		}
		return false;
	}

	public function isStaff($data = null, $column_name = null) {
		if ($data) {
			$data = $this->_db->get('id_pegawai', 'pegawai', array($column_name, '=', $data));
			if ($data->count()) {
				$this->_data = $data->result();
				return true;
			}
		}
		return false;
	}

	public function affected() {
		return $this->_db->count();
	}

	public function findBy($akun = null, $column_name = null) {
		if ($akun) {
			$field = (is_numeric($akun)) ? 'id_akun' : $column_name;
			$data = $this->_db->getAll('akun', array($field,'=',$akun));
			if ($data->count()) {
				$this->_data = $data->result();
				return true;
			}
		}
		return false;
	}

	public function getIdBy($data = null, $column_name = null) {
		if ($data) {
			$field = (is_numeric($data)) ? 'id_akun' : $column_name;
			$data = $this->_db->get('id_akun', 'akun', array($field, '=', $data));
			if ($data->count()) {
				$this->_data = $data->result();
				return true;
			}
		}
		return false;
	}

	public function get($fields, $where) {
		$data = $this->_db->get($fields,'akun',$where);
		if ($data->count()) {
			$this->_data = $data->result();
			return $this->_data;
		}
		return false;
	}

	public function getDataStaff($data) {
		if ($data) {
			$data = $this->_db->getAll('pegawai', array('id_pegawai','=',$data));
			if ($data->count()) {
				$this->_data = $data->result();
				return $this->_data;
			}
		}
		return false;
	}

	public function getDataAkun($param1 = 1, $param2 = 10) {
		$this->_db->query("SELECT donatur.nama, akun.id_akun, akun.username, akun.email, akun.aktivasi as status, gambar.nama nama_avatar, gambar.path_gambar path_avatar, akses.nama akses_utama FROM donatur JOIN akun USING(id_akun) LEFT JOIN gambar ON(gambar.id_gambar = akun.id_gambar) JOIN akses ON(akses.hak_akses = akun.hak_akses) WHERE id_akun BETWEEN ? AND ? ORDER BY id_akun ASC LIMIT 10", array($param1, $param2));
		if ($this->_db->count()) {
			$this->_data = $this->_db->results();
			return $this->_data;
		}
		return false;
	}

	public function countData() {
		$this->_db->query("SELECT COUNT(*) jumlah_record FROM akun");
		if ($this->_db->count()) {
			$this->_data = $this->_db->result();
			return $this->_data;
		}
		return false;
	}

	public function isCurrentAkun($id_akun) {
		if (Session::get($this->_sessionName) == $id_akun) {
			return true;
		}
		return false;
	}

	public function signin($email_username = null, $password = null, $remember = false) {
		
		if (!$email_username && !$password && $this->exists()) {
			Session::put($this->_sessionName, $this->data()->id_akun);
		} else {
			$user = $this->findBy($email_username, 'username');

			if (!$user) {
				$user = $this->findBy($email_username, 'email');
			}

			if ($user) {

				if ($this->data()->aktivasi == 0) {
					Session::flash('error','Akun harus diaktifkan terlebih dahulu');
					return true;
				}

				if ($this->data()->password === Hash::make($password, $this->data()->salt)) {
					Session::put($this->_sessionName, $this->data()->id_akun);

					if ($remember) {
						$hashCheck = $this->_db->getAll('sesi_akun', array('id_akun', '=', $this->data()->id_akun));

						if (!$hashCheck->count()) {
							$hash = Hash::unique();
							$this->_db->insert('sesi_akun', array(
								'id_akun' => $this->data()->id_akun,
								'hash' => $hash
							));
						} else {
							$hash = $hashCheck->result()->hash;
							if (is_null($hash)) {
								$hash = Hash::unique();
								$this->_db->update('sesi_akun', array('hash' => $hash), array('id_akun', '=', $hashCheck->result()->id_akun));
							}
						}

						Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
					}

					return true;
				}
			}
		}

		return false;
	}

	public function getIdPengunjung($device_id, $id_pengunjung) {
		$data = $this->_db->query('SELECT id_pengunjung FROM pengunjung WHERE ip_address = ? AND device_id = ? AND id_pengunjung = ?', 
		array(
			'ip_address' => Config::getClientIP(), 
			'device_id' => $device_id,
			'id_pengunjung' => $id_pengunjung
		));

		if ($this->_db->count()) {
			// Device ID VALID
			$id_pengunjung = $this->_db->result()->id_pengunjung;
		} else {
			// Device ID INVALID
			$this->_db->insert('pengunjung', array(
				'ip_address' => Config::getClientIP(),
				'client_key' => base64_encode(json_encode(array(
					'ip_address' => Config::getClientIP(),
					'auth' => true,
					'expiry' => time() + Config::get('client/cookie_expiry')
				)))
			));
			if ($this->_db->count()) {
				$id_pengunjung = $this->_db->lastInsertId();
			}
		}
		return $id_pengunjung;
	}

	public function cekAkunPengunjung($id_pengunjung, $id_akun) {
		$data = $this->_db->getAll('akun_pengunjung', array('id_pengunjung','=',$id_pengunjung),'AND',array('id_akun','=',$id_akun));
		if (!$this->_db->count()) {
			return false;
		}
		return true;
	}

	public function getIdAkunPengunjung($id_akun, $ip_address, $os, $browser) {
		$this->_db->query('SELECT DISTINCT(p.id_pengunjung), p.client_key FROM pengunjung p LEFT JOIN akun_pengunjung ap USING(id_pengunjung) WHERE ap.id_akun = ? AND p.ip_address = ? AND p.os = ? AND p.browser = ?',
		array(
			Sanitize::escape(trim($id_akun)),
			Sanitize::escape(trim($ip_address)),
			Sanitize::escape(trim($os)),
			Sanitize::escape(trim($browser))
		));
		if (!$this->_db->count()) {
			return false;
		}
		$this->_data = $this->_db->result();
		return true;
	}

	public function isCookieValid($id_akun, $ip_address, $os, $browser) {
		$this->_db->query('SELECT DISTINCT(p.id_pengunjung), p.client_key, p.device_id FROM pengunjung p LEFT JOIN akun_pengunjung ap USING(id_pengunjung) WHERE p.create_at >= NOW() - INTERVAL 1 YEAR AND ap.id_akun = ? AND p.ip_address = ? AND p.os = ? AND p.browser = ?', 
		array(
			Sanitize::escape(trim($id_akun)),
			Sanitize::escape(trim($ip_address)),
			Sanitize::escape(trim($os)),
			Sanitize::escape(trim($browser))
		));
		if (!$this->_db->count()) {
			return false;
		}
		$this->_data = $this->_db->result();
		return true;
	}

	public function isCookieDevice($id_pengunjung, $device_id, $client_key) {
		$this->_db->query('SELECT id_pengunjung, client_key FROM pengunjung WHERE id_pengunjung = ? AND device_id = ? AND client_key = ?', 
		array(
			Sanitize::escape(trim($id_pengunjung)),
			Sanitize::escape(trim($device_id)),
			Sanitize::escape(trim($client_key))
		));
		if (!$this->_db->count()) {
			return false;
		}
		$this->_data = $this->_db->result();
		return true;
	}

	public function getTodayIdPengunjungAkun($id_akun, $ip_address, $os, $browser) {
		$this->_db->query('SELECT p.id_pengunjung, p.client_key FROM pengunjung p LEFT JOIN akun_pengunjung ap USING(id_pengunjung) WHERE DATE_FORMAT(p.create_at, "%m-%d-%Y") = DATE_FORMAT(NOW(), "%m-%d-%Y") AND ap.id_akun = ? AND p.ip_address = ? AND p.os = ? AND p.browser = ?', 
		array(
			Sanitize::escape(trim($id_akun)),
			Sanitize::escape(trim($ip_address)),
			Sanitize::escape(trim($os)),
			Sanitize::escape(trim($browser))
		));
		if (!$this->_db->count()) {
			return false;
		}
		$this->_data = $this->_db->result();
		return true;
	}

	public function isTodayCookieValid($id_akun, $ip_address, $os, $browser) {
		$this->_db->query('SELECT p.id_pengunjung, p.client_key FROM pengunjung p LEFT JOIN akun_pengunjung ap USING(id_pengunjung) WHERE p.create_at >= NOW() - INTERVAL 1 DAY AND ap.id_akun = ? AND p.ip_address = ? AND p.os = ? AND p.browser = ?', 
		array(
			Sanitize::escape(trim($id_akun)),
			Sanitize::escape(trim($ip_address)),
			Sanitize::escape(trim($os)),
			Sanitize::escape(trim($browser))
		));
		if (!$this->_db->count()) {
			return false;
		}
		$this->_data = $this->_db->result();
		return true;
	}

	public function getData($fields, $table, $where) {
		$this->_db->get($fields, $table, $where);
		if (!$this->_db->count()) {
			return false;
		}
		$this->_data = $this->_db->result();
		return true;
	}

	public function query($sql, $params) {
		$this->_db->query($sql, $params);
		if (!$this->_db->count()) {
			return false;
		}
		$this->_data = $this->_db->result();
		return true;
	}

	public function getExpiryResetAkun($sql, $params) {
		$this->_db->query($sql, $params);
		if (!$this->_db->count()) {
			return false;
		}
		return $this->_db->result();
	}

	public function lastIID() {
		return $this->_db->lastInsertId();
	}

	public function exists() {
		return (!empty($this->_data)) ? true : false;
	}

	public function signout() {
		$this->_db->update('sesi_akun', array('hash' => NULL), array('id_akun', '=', $this->data()->id_akun));

		Session::delete($this->_sessionName);
		Cookie::delete($this->_cookieName);
	}

	public function data() {
		return $this->_data;
	}

	public function isSignIn() {
		return $this->_isSignIn;
	}
}