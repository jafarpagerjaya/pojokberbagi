<?php
class SignupController extends Controller {
	private $_auth;
    public function index() {
		$this->_auth = $this->model('Auth');

		if ($this->_auth->isSignIn()) {
			if ($this->_auth->hasPermission('admin')) {
				if (Session::exists('error')) {
					$pesan = Session::flash('error');
					$state = 'error';
				}
		
				if (Session::exists('danger')) {
					$pesan = Session::flash('danger');
					$state = 'danger';
				}
		
				if (Session::exists('warning')) {
					$pesan = Session::flash('warning');
					$state = 'warning';
				}

				if (!empty($pesan)) {
					Session::put('notifikasi', array(
						'pesan' => $pesan,
						'state' => $state
					));
				}

                Redirect::to('admin');
            } else {
                Redirect::to('donatur');
            }
        }

		$this->title = "Sign Up";
		$this->script_controller = array(
			array(
				'type' => 'text/javascript',
				'src' => VENDOR_PATH.'passy'.DS.'passy.min.js'
			),
			array(
				'type' => 'text/javascript',
				'src' => '/assets/route/auth/js/signup.js'
			)
		);

        if (Input::exists()) {
			if (Token::check(Input::get('token'))) {
				$vali = new Validate();
				$validate = $vali->check($_POST, array(
                    'username' => array(
						'required' => true,
						'min' => 5,
						'max' => 32,
						'unique' => 'akun'
					),
                    'email' => array(
						'required' => true,
						'min' => 10,
						'max' => 96,
						'unique' => 'akun'
					),
					'nama' => array(
						'required' => true,
						'min' => 5,
						'max' => 30
					),
					'password' => array(
						'required' => true,
						'min' => 8,
						'max' => 64
                    ),
					'ketentuan' => array(
						'required' => true
					)
				));
				if (!$validate->passed()) {
					$this->data['nameV'] = strtoupper($validate->value('nama'));
					$this->data['nameF'] = $validate->feedback('nama');
					$this->data['emailV'] = $validate->value('email');
					$this->data['emailF'] = $validate->feedback('email');
					$this->data['usernameV'] = $validate->value('username');
					$this->data['usernameF'] = $validate->feedback('username');
					$this->data['passwordV'] = $validate->value('password');
					$this->data['passwordF'] = $validate->feedback('password');
					$this->data['ketentuanV'] = $validate->feedback('ketentuan');
					$this->data['ketentuanF'] = $validate->feedback('ketentuan');
				} else {
					$this->data['nameV'] = "";
					$this->data['emailV'] = "";
					$this->data['usernameV'] = "";
					$this->data['passwordV'] = "";
					$this->data['ketentuanV'] = "";

					$salt = Hash::salt(32);
					try {
						$akunArray = array(
							'username' 	=> Sanitize::noDblSpace2(Input::get('username')),
							'password' 	=> Sanitize::noSpace2(Hash::make(Input::get('password'), $salt)),
							'salt' 		=> $salt,
							'email' 	=> strtolower(Sanitize::noSpace2(Input::get('email')))
						);
						// Check email is staff
						$staff = $this->_auth->isStaff(Sanitize::noSpace(Input::get('email')), 'email');

						if ($staff) {
							$id_pegawai = $this->_auth->data()->id_pegawai;
							$akunArray['hak_akses'] = 'A';
						}

						$pesan = 'Akun ';
						$this->_auth->create($akunArray);
						
						$id_akun = $this->_auth->lastIID();
						// $this->_auth->getIdBy(Sanitize::noSpace(Input::get('email')), 'email');
						// $id_akun = $this->_auth->data()->id_akun;
						
						if ($staff) {
							$id_pegawai = $this->_auth->data()->id_pegawai;
							$this->model('Admin');
							try {
								$this->model->create('admin', array(
									'id_pegawai'	=> $id_pegawai,
									'id_akun'		=> $id_akun
								));
								$createRole = 'admin';
								Session::put('success', $pesan . '<span class="font-weight-bold">' . $createRole . '</span> berhasil dibuat cek email <span class="font-weight-bolder">' . Sanitize::noSpace(Input::get('email')) . '</span> untuk mengaktifkan');
							} catch (Exception $e) {
								Session::put('error', $e->getMessage());
							}
						}

						$this->model('Donatur');
						try {
							if (!$staff) {
								$pesan .= '<span class="font-weight-bold">' . (!empty($createRole) ? $createRole : 'donatur') . '</span> baik berhasil dibuat';
							}
							$this->model->getData("id_donatur, nama","donatur",array('email','=',strtolower(Sanitize::noSpace(Input::get('email')))));
							if (!$this->model->affected()) {
								$this->model->create('donatur', array(
									'nama'		=> strtoupper(Sanitize::noDblSpace(Input::get('nama'))),
									'email' 	=> strtolower(Sanitize::noSpace(Input::get('email'))),
									'id_akun'	=> $id_akun
								));
								$id_donatur = $this->model->lastIID();
								if (!$staff) {
									try {
										$this->model->update('akun', array('aktivasi' => 1), array('id_akun', '=', $id_akun));
										$pesan .= ', ayo <span class="font-weight-bold">' . Input::get('username') . '</span> sekarang coba <span class="text-orange">Sign in (Masuk)</span>';
									} catch (Exception $e) {
										Session::put('error', $e->getMessage());
									}
								} else {
									$link = Config::getHTTPHost().'/auth/signup/activate/'. $id_pegawai .'/'. $id_akun .'/'. strtolower(Sanitize::noSpace(Input::get('email'))) .'/'. $salt;
									$dataActivate = array(
										'nama' => strtoupper(Sanitize::noDblSpace(Input::get('nama'))),
										'link' => $link
									);
									// Send mail Activasi
									$subject = "Aktivasi Akun Pojok Berbagi";
									$headers = 'From: Pojok Berbagi <no-replay@pojokberbagi.id>' . "\r\n" . 'Reply-To: No Replay <no-replay@pojokberbagi.id>' . "\r\n";
									$headers .= "MIME-Version: 1.0\r\n";
									$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
									$message = Ui::emailAktivasiAkun($dataActivate);
									if(mail(strtolower(Sanitize::noSpace2(Input::get('email'))), $subject, $message, $headers)) {
										$pesan = Session::flash('success');
									}
								}
							} else {
								// Donatur sudah pernah berdonasi
								try {
									$link = Config::getHTTPHost().'/auth/signup/hook/'. $this->model->data()->id_donatur .'/'. $id_akun .'/'. strtolower(Sanitize::noSpace(Input::get('email'))) .'/'. $salt;
									$dataHook = array(
										'nama' => $this->model->data()->nama,
										'link' => $link
									);
									// Send mail Hook
									$subject = "Kaitkan Akun Pojok Berbagi";
									$headers = 'From: Pojok Berbagi <no-replay@pojokberbagi.id>' . "\r\n" . 'Reply-To: No Replay <no-replay@pojokberbagi.id>' . "\r\n";
									$headers .= "MIME-Version: 1.0\r\n";
									$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
									$message = Ui::emailHookAkun($dataHook);
				
									if(mail(strtolower(Sanitize::noSpace2(Input::get('email'))), $subject, $message, $headers)) {
										$pesan .= ', cek email <b>'. Input::get('email') .'</b> untuk mengkaitkan dengan donasi yang sudah pernah anda lakukan';
									}
								} catch (Exception $e) {
									Session::put('error', $e->getMessage());
								}
							}
							Session::put('success', $pesan);
						} catch (Exception $e) {
							Session::put('error', $e->getMessage());
						}
					} catch (Exception $e) {
						Session::put('error', $e->getMessage());
					}
				}
			}
		}

		if (Session::exists('error')) {
			Session::put('notif-pesan', Session::flash('error'));
		}

		if (Session::exists('danger')) {
			Session::put('notif-pesan', Session::flash('danger'));
		}

		if (Session::exists('warning')) {
			Session::put('notif-pesan', Session::flash('warning'));
		}

		if (Session::exists('success')) {
			Session::put('notif-pesan', Session::flash('success'));
			Redirect::to('auth/signin');
		}

        $this->data['token'] = Token::generate();
    }

	public function hook($params = array()) {
		if (count(is_countable($params) ? $params : []) < 4) {
			Session::flash('danger','Parameter salah');
			Redirect::to('auth/signup');
		}

		$this->model('Auth');

		$akunAda = $this->model->getIdBy(strtolower(Sanitize::noDblSpace2($params[2])), 'email');
		if (!$akunAda) {
			Session::flash('danger','Akun anda tidak ditemukan');
			Redirect::to('auth/signup');
		}

		$this->model->query('SELECT COUNT(id_akun) found FROM akun WHERE id_akun = ? AND email = ? AND salt = ? AND aktivasi = "0"', 
		array(
			Sanitize::escape2($params[1]),
			Sanitize::escape2($params[2]),
			Sanitize::escape2($params[3])
		));
		if ($this->model->data()->found == 0) {
			Session::flash('danger','Token Hook anda bermasalah');
			Redirect::to('auth/signup');
		}

		$this->model->update(array('aktivasi' => 1), Sanitize::escape2($params[1]));
		if (!$this->model->affected()) {
			Session::flash('error','Gagal aktivasi akun [hook]');
			Redirect::to('auth/signup');
		}

		$this->model('Donatur');
		$hasil = $this->model->update('donatur', array('id_akun' => Sanitize::escape2($params[1])), array('id_donatur','=', Sanitize::escape2($params[0])));
		if (!$hasil) {
			Session::flash('error','Gagal Update ID Akun Donatur [hook]');
		} else {
			Session::flash('success', 'Akun ' . Sanitize::escape2($params[2]) . '</span> berhasil dikaitkan');
		}

		if (Session::exists('success')) {
			Session::put('notif-pesan', Session::flash('success'));
			Redirect::to('auth/signin');
		}

		if (Session::exists('error')) {
			Session::put('notif-pesan', Session::flash('error'));
			Redirect::to('auth/signup');
		}

		if (Session::exists('error')) {
			Session::put('notif-pesan', Session::flash('error'));
			Redirect::to('auth/signup');
		}
	}

	public function activate($params = array()) {
		if (count(is_countable($params) ? $params : []) < 4) {
			Session::flash('danger','Parameter salah');
			Redirect::to('auth/signup');
		}

		$this->model('Auth');

		$akunAda = $this->model->getIdBy(strtolower(Sanitize::noDblSpace2($params[2])), 'email');
		if (!$akunAda) {
			Session::flash('danger','Akun anda tidak ditemukan');
			Redirect::to('auth/signup');
		}

		$this->model->query("SELECT COUNT(id_akun) found FROM akun JOIN admin USING(id_akun) WHERE admin.id_pegawai = ? AND akun.id_akun = ? AND akun.email = ? AND akun.salt = ? AND akun.aktivasi = '0'", 
		array(
			Sanitize::escape2($params[0]),
			Sanitize::escape2($params[1]),
			Sanitize::escape2($params[2]),
			Sanitize::escape2($params[3])
		));
		if ($this->model->data()->found == 0) {
			Session::flash('danger','Token Aktivasi anda bermasalah');
			Redirect::to('auth/signup');
		}

		$this->model->update(array('aktivasi' => 1), Sanitize::escape2($params[1]));
		if (!$this->model->affected()) {
			Session::flash('error','Gagal aktivasi akun [admin]');
			Redirect::to('auth/signup');
		} else {
			Session::flash('success','Akun <span class="font-weight-bold>"' . Sanitize::escape2($params[2]) . '</span> berhasil diaktifkan');
		}

		if (Session::exists('success')) {
			Session::put('notif-pesan', Session::flash('success'));
			Redirect::to('auth/signin');
		}

		if (Session::exists('error')) {
			Session::put('notif-pesan', Session::flash('error'));
			Redirect::to('auth/signup');
		}

		if (Session::exists('error')) {
			Session::put('notif-pesan', Session::flash('error'));
			Redirect::to('auth/signup');
		}
	}

    // public function google() {

    // }

    // public function instagram() {

    // }

    // public function facebook() {

    // }
}