<?php 
class SigninController extends Controller {
	private $_auth,
			$_device_update = true;

	public function __construct() {
		$this->_auth = $this->model('Auth');

		if ($this->_auth->isSignIn()) {
			if ($this->_auth->hasPermission('admin')) {
                Redirect::to('admin');
            } else {
                Redirect::to('home');
            }
        }
	}

    public function index() {
		$this->title = "Sign In";
        if (Input::exists()) {
			if (Token::check(Input::get('token'))) {
				$vali = new Validate();
				$validate = $vali->check($_POST, array(
					'email_username' => array(
						'required' => true,
						'min' => 5,
						'max' => 96
					),
					'password' => array(
						'required' => true,
						'min' => 8,
						'max' => 64
					),
					'remember_me' => array(
						'checked' => 'on'
					)
				));
				if (!$validate->passed()) {
					$this->data['email_usernameF'] = $validate->feedback('email_username');
					$this->data['email_usernameV'] = $validate->value('email_username');
					// $this->data['email_usernameV'] = 'Pojok Berbagi';
					$this->data['passwordF'] = $validate->feedback('password');
					$this->data['passwordV'] = $validate->value('password');
					// $this->data['passwordV'] = 'Passpojokberbagi@21';
					$this->data['remember_meV'] = $validate->value('remember_me');

					// Session::put('error_feedback', $validate->getValueFeedback());
				} else {
					$this->data['remember_meV'] = "";
					$remember = (Input::get('remember_me') === 'on') ? true : false;
					$signin = $this->_auth->signin(Input::get('email_username'), Input::get('password'), $remember);
					// Debug::vd($signin);
					if ($signin && $this->_auth->data()->aktivasi == true) {
						// $this->isExistsAkunPengunjung($this->_auth);
						$dataAkun = $this->_auth->data();

						$this->_auth->isStaff($dataAkun->email, 'email');
						$isStaff = $this->_auth->affected();
						switch ($isStaff) {
							case true:
								$route = 'admin';
								break;
							default:
								$route = 'donatur';
								break;
						}

						// Debug::vd($route);
						$this->setClientDeviceID();

						if (!Cookie::exists(Config::get('client/cookie_name'))) {
							// Cookie Tidak Ada
							$this->_auth->getIdAkunPengunjung($dataAkun->id_akun, Config::getClientIP(), Sanitize::escape(trim(Config::getClientDevice('os'))), Sanitize::escape(trim(Config::getClientDevice('browser'))));
							if (!$this->_auth->affected()) {
								// Akun Ini Belum Visit
								$this->create_pengunjung = true;
								// Debug::vd('Belum');
							} else {
								// Akun Ini Sudah Visit
								$client_key = json_decode(base64_decode($this->_auth->data()->client_key), true);
								// Debug::vd($client_key);
								$cookie_expiry = $client_key['expiry'];
								if ($cookie_expiry <= time()) {
									$cookie_expiry = time() + Config::get('client/cookie_expiry');
									$client_key['expiry'] = $cookie_expiry;
								}
								$id_pengunjung = $this->_auth->data()->id_pengunjung;
								if ($id_pengunjung != $client_key['id_pengunjung']) {
									$client_key['id_pengunjung'] = $id_pengunjung;
								}
								$client_key['auth'] = true;
								$client_key = base64_encode(json_encode($client_key));
								$data_cookie['client_key'] = $client_key;
								// Debug::vd('Sudah');
							}
						} else {
							// Cookie Ada
							$cookie = Cookie::get(Config::get('client/cookie_name'));
							$data_cookie['client_key'] = json_decode(base64_decode($cookie), true);

							$this->_auth->isCookieValid($dataAkun->id_akun, Config::getClientIP(), Sanitize::escape(trim(Config::getClientDevice('os'))), Sanitize::escape(trim(Config::getClientDevice('browser'))));
							if (!$this->_auth->affected()) {
								// Akun Ini Invalid Cookie (Waktu cookie habis/Device/OS/Browser Tidak Cocok) Atau Akun lain pakai device yang sama
								if ($this->getClientDeviceID() == $data_cookie['client_key']['device_id']) {
									// Device_id yang terdaftar sama (Beda akun sama device atau akun baru datang dari home)

									// Cek Device ID pada ID pengunjung cocok? ambil id_pengunjung
									$id_pengunjung = $this->_auth->getIdPengunjung($this->getClientDeviceID(), Sanitize::escape(trim($data_cookie['client_key']['id_pengunjung'])));

									$this->_auth->getData('client_key','pengunjung',array('id_pengunjung', '=', $id_pengunjung));
									$client_key = $this->_auth->data()->client_key;
									$data_client = json_decode(base64_decode($client_key), true);
									$cookie_expiry = $data_client['expiry'];
									if ($cookie_expiry <= time()) {
										$cookie_expiry = time() + Config::get('client/cookie_expiry');
										$data_client['expiry'] = $cookie_expiry;
										$client_key = base64_encode(json_encode($data_client));
									}
									$data_cookie['client_key'] = $client_key;
									$this->update_pengunjung = true;
									$this->_device_update = false;
								} else {
									// Device_id yang terdaftar beda (Cookie Palsu) Atau Window browser baru akibat delete cookie 
									$this->create_pengunjung = true;
								}
								// Debug::vd('Invalid Cookie');
							} else {
								// Akun Ini Menggunakan Cookie Yang Valid 
								$client_key = $this->_auth->data()->client_key;
								$id_pengunjung = $this->_auth->data()->id_pengunjung;
								$device_id = $this->_auth->data()->device_id;

								if ($client_key != $cookie) {
									$this->setClientDeviceID($data_cookie['client_key']['device_id']);
									// Data id_pengunjung di cookie tidak cocok dengan yang ada di database (cleint_key value)
									$valid = $this->_auth->isCookieDevice($data_cookie['client_key']['id_pengunjung'], $data_cookie['client_key']['device_id'],$cookie);
									if ($valid) {
										$id_pengunjung = $this->_auth->data()->id_pengunjung;
										$client_key = $this->_auth->data()->client_key;
									}
									$data_cookie['client_key'] = $client_key;
								} else {
									$data_cookie['client_key'] = $cookie;
								}
								$cookie_expiry = json_decode(base64_decode($data_cookie['client_key']), true)['expiry'];
								$this->update_pengunjung = true;
								$this->_device_update = false;
								// Debug::vd('Cookie Valid');
							}
						}

						if (isset($this->create_pengunjung)) {
							$data_cookie = array(
								'ip_address' => Config::getCLientIP(),
								'expiry' => time() + Config::get('client/cookie_expiry'),
								'auth' => true
							);
							$client_key = base64_encode(json_encode($data_cookie));
							$data_cookie['client_key'] = $client_key;

							$cookie_expiry = $data_cookie['expiry'];
							unset($data_cookie['expiry']);
							unset($data_cookie['auth']);

							$this->_auth->createDynamic('pengunjung', $data_cookie);
							$id_pengunjung = $this->_auth->lastIID();
							$this->update_pengunjung = true;
						}

						if (isset($this->update_pengunjung)) {
							$data_cookie['client_key'] = json_decode(base64_decode($data_cookie['client_key']), true);
							$data_cookie['client_key']['id_pengunjung'] = $id_pengunjung;
							$data_cookie['client_key']['auth'] = true;
							
							$this->model('Home');
							$device_id = $this->getClientDeviceID();

							if ($this->_device_update == true) {
								$found = true;
								do {
									$this->model->getData('COUNT(id_pengunjung) count', 'pengunjung', array('device_id', '=', $device_id));
									if ($this->model->data()->count != 0) {
										$device_id = Hash::unique();
									} else {
										$found = false;
									}
								} while ($found == true);
							}
							
							// Session::put(Config::get('session/device_name'), $device_id);
							$data_cookie['device_id'] = $device_id;
							$data_cookie['device_type'] = Config::getCLientDevice('device_type');
							$data_cookie['os'] = Config::getCLientDevice('os');
							$data_cookie['os_bit'] = Config::getCLientDevice('os_bit');
							$data_cookie['browser'] = Config::getCLientDevice('browser');
							$data_cookie['browser_version'] = Config::getCLientDevice('browser_version');
							$data_cookie['client_key']['device_id'] = $device_id;
							$data_cookie['client_key'] = base64_encode(json_encode($data_cookie['client_key']));
							$this->model->update('pengunjung', $data_cookie, array('id_pengunjung', '=', Sanitize::escape(trim($id_pengunjung))));
						}

						if ($cookie_expiry <= time()) {
							$cookie_expiry = time() + Config::get('client/cookie_expiry');
						}
						$cookie_value = $data_cookie['client_key'];
						Cookie::update(Config::get('client/cookie_name'), $cookie_value, $cookie_expiry);

						$data_client = json_decode(base64_decode($data_cookie['client_key']), true);
						if (isset($data_client['auth'])) {
							$this->setClientDeviceID($data_client['device_id']);
						}

						$this->_auth->createDynamic('akun_pengunjung', array(
							'id_akun' => $dataAkun->id_akun,
							'id_pengunjung' => $id_pengunjung
						));

						// $hasilCek = $this->_auth->cekAkunPengunjung($id_pengunjung, $dataAkun->id_akun);
						// if (!$hasilCek) {
							
						// }

						if (Session::exists('donasi')) {
							Redirect::to('donasi/buat/baru/'. Session::flash('donasi'));
						}

						Redirect::to($route);
					} elseif ($signin && $this->model->data()->aktivasi != true) {
						Session::flash('warning','Akun <span class="font-weight-bold">' . $validate->value('email_username') . '</span> Belum Diaktivasi');
					} else {
						Session::flash('error','Username dan Password Tidak Dikenali');
					}
				}
			}
		}

        $this->data['token'] = Token::generate();
		$countTimer = false;
		
		if (Session::exists('success')) {
			Session::put('notif-pesan', Session::flash('success'));
			$countTimer = true;
		} 

		if (Session::exists('warning')) {
			Session::put('notif-pesan', Session::flash('warning'));
		} 
		
		if (Session::exists('error')) {
			Session::put('notif-pesan', Session::flash('error'));
			$countTimer = true;
		}

		if ($countTimer) {
			$this->script_action = array(
				array(
					'src' => 'https://cdn.jsdelivr.net/gh/robbmj/simple-js-countdown-timer@master/countdowntimer.js',
					'source' => 'trushworty'
				),
				array(
					'src' => '/assets/route/auth/js/reset.js'
				)
			);
		}
    }

	private function isExistsAkunPengunjung($model) {
		$id_pengunjung = $model->getIdPengunjung();
		$id_akun = $model->data()->id_akun;
		$hasilCek = $model->cekAkunPengunjung($id_pengunjung, $id_akun);
		if (!$hasilCek) {
			$model->createDynamic('akun_pengunjung', array(
				'id_akun' => $id_akun,
				'id_pengunjung' => $id_pengunjung
			));
		}
	}

    // public function google() {

    // }

    // public function instagram() {

    // }

    // public function facebook() {

    // }
}