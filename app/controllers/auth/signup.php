<?php
class SignupController extends Controller {
    public function index() {
		$this->model('Auth');

		if ($this->model->isSignIn()) {
			if ($this->model->hasPermission('admin')) {
                Redirect::to('admin');
            } else {
                Redirect::to('home');
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
					$this->data['nameV'] = $validate->value('nama');
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
						$this->model->create(array(
							'username' 	=> Sanitize::noDblSpace2(Input::get('username')),
							'password' 	=> Sanitize::noSpace2(Hash::make(Input::get('password'), $salt)),
							'salt' 		=> $salt,
							'email' 	=> strtolower(Sanitize::noSpace2(Input::get('email')))
						));
						$pesan = 'Akun ';

						$this->model->getIdBy(Sanitize::noSpace(Input::get('email')), 'email');
						$id_akun = $this->model->data()->id_akun;
						$this->model->isStaff(Sanitize::noSpace(Input::get('email')), 'email');
						
						if ($this->model->affected()) {
							$id_pegawai = $this->model->data()->id_pegawai;
							$this->model('Admin');
							try {
								$this->model->create('admin', array(
									'id_pegawai'	=> $id_pegawai,
									'id_akun'		=> $id_akun
								));
								$createRole = 'admin';
								Session::flash('success', $pesan . '<span class="font-weight-bold">' . (!empty($createRole) ? $createRole : 'donatur') . '</span> berhasil dibuat cek email <span class="font-weight-bolder">' . Sanitize::noSpace(Input::get('email')) . '</span> untuk mengaktifkan');
							} catch (Exception $e) {
								Session::flash('error', $e->getMessage());
							}
						}

						$this->model('Donatur');
						try {
							$createRole = 'donatur';
							$pesan .= '<span class="font-weight-bold">' . (!empty($createRole) ? $createRole : 'donatur') . '</span> baik berhasil dibuat';
							$this->model->getData("id_donatur, nama","donatur",array('email','=',strtolower(Sanitize::noSpace(Input::get('email')))));
							if (!$this->model->affected()) {
								$this->model->create('donatur', array(
									'nama'		=> Sanitize::noDblSpace(Input::get('nama')),
									'email' 	=> strtolower(Sanitize::noSpace(Input::get('email'))),
									'id_akun'	=> $id_akun
								));
								try {
									$this->model->update('akun', array('aktivasi' => 1), array('id_akun', '=', $id_akun));
									$pesan .= ', ayo <span class="font-weight-bold">' . Input::get('username') . '</span> sekarang coba <span class="text-orange">Sign in (Masuk)</span>';
								} catch (Exception $e) {
									Session::flash('error', $e->getMessage());
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
									Session::flash('error', $e->getMessage());
								}
							}
							Session::flash('success', $pesan);
						} catch (Exception $e) {
							Session::flash('error', $e->getMessage());
						}
					} catch (Exception $e) {
						Session::flash('error', $e->getMessage());
					}
				}
			}
		}
		
		if (Session::exists('success')) {
			Session::put('notif-pesan', Session::flash('success'));
			Redirect::to('auth/signin');
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

        $this->data['token'] = Token::generate();
    }

	public function hook($params = array()) {
		if (count($params) < 4) {
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

    // public function google() {

    // }

    // public function instagram() {

    // }

    // public function facebook() {

    // }
}