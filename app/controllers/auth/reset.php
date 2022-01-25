<?php
class ResetController extends Controller {
    public function index() {
        if (Input::exists()) {
            if (Token::check(Input::get('token'))) {
                $vali = new Validate();
				$validate = $vali->check($_POST, array(
                    'email' => array(
                        'required' => true,
                        'min' => 10,
                        'max' => 96
                    )
                ));
                if (!$validate->passed()) {
                    Session::put('error_feedback',$validate->getValueFeedback());
                    Redirect::to('auth/reset');
                }

                $this->model('Auth');
                if ($this->model->isSignIn()) {
                    Redirect::to('auth/signin');
                }

                $this->model->getData('akun.id_akun, akun.email, akun.password, akun.salt, akun.hash_expiry, donatur.nama', 'akun LEFT JOIN donatur USING(id_akun)', array('LOWER(akun.email)', '=', strtolower(trim(Input::get('email')))));
                if (!$this->model->affected()) {
                    $validate->setValueFeedback('email', 'tidak ditemukan');
                    Session::put('error_feedback',$validate->getValueFeedback());
                    Redirect::to('auth/reset');
                }
                $data = $this->model->data();

                $password = $data->password;
                $hash_reset = Hash::unique();
                
                if (!is_null($data->hash_expiry) && strtotime(date('Y-m-d H:i:s', time())) < strtotime($data->hash_expiry)) {
                    $now = new DateTime(date('Y-m-d H:i:s'));
                    $expiry = new DateTime($data->hash_expiry);
                    $msInterval = $expiry->diff($now);
                    $inInterval = strtotime($data->hash_expiry) - strtotime(date('Y-m-d H:i:s', time()));
                    Session::put('error', '<p class="m-0">Tiket reset password masih berlaku cek <b class="text-info fw-bolder">email <span>'. $data->email .'</span></b>. <div class="fs-1">Tiket berlaku hingga Pukul <b>' .date('H:i:s', strtotime($data->hash_expiry)) . '</b>, dalam kurun <span data-interval="'. $inInterval .'" id="msInterval">('. $msInterval->format("%I") .':'.$msInterval->format("%S") .')</span></div></p>');
                    Redirect::to('auth/reset');
                }

                try {
                    $this->model->update(array('hash_reset' => $hash_reset, 'hash_expiry' => date('Y-m-d H:i:s', time() + 5 * 60)), $data->id_akun);
                    $Link = '<a href="pojokberbagi.id/auth/reset/renew/'. $data->email .'/'.$password.'/'.$hash_reset.'">Klik Disini</a> Untuk Reset Password';
                } catch (\Throwable $th) {
                    Session::put('error', $th->errorMessage());
                    Redirect::to('auth/reset');
                }

                if ($this->model->affected()) {
                    $now = new DateTime(date('Y-m-d H:i:s'));
                    $expiry = new DateTime(date('Y-m-d H:i:s', time() + 5 * 60));
                    $msInterval = $expiry->diff($now);
                    $inInterval = strtotime(date('Y-m-d H:i:s', time() + 5 * 60)) - strtotime(date('Y-m-d H:i:s', time()));
                    // Send it to mail here
                    $dataNotif = array(
                        'nama' => $data->nama,
                        'expiry' => date_format($expiry, 'd M Y H:i'),
                        'link' => Config::getHTTPHost() .'/auth/reset/renew/'. $data->email .'/'.$password.'/'.$hash_reset
                    );
                    $subject = "Reset Password Pojok Berbagi";
                    $headers = 'From: Pojok Berbagi <no-replay@pojokberbagi.id>' . "\r\n" . 'Reply-To: CS PBI <no-replay@pojokberbagi.id>' . "\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    $pesan = Ui::emailResetPassword($dataNotif);

                    if(mail($data->email, $subject, $pesan, $headers)) {
                        Session::put('success','<p class="m-0">Tiket konfirmasi reset password telah dikirim ke email <b class="text-info fw-bolder">' . Input::get('email') . '</b>. <div class="fs-1">Tiket berlaku hingga Pukul ' . $expiry->format('H:i') . ', dalam kurun <span data-interval="'. $inInterval .'" id="msInterval">('. $msInterval->format("%I") .':'.$msInterval->format("%S") .')</span></div></p>');
                    }
                }
                // Session::put('success', '<a href="/auth/reset/renew/'. $data->email .'/'.$password.'/'.$hash_reset.'">Klik Disini</a> Untuk Reset Password');
                Redirect::to('auth/signin');
            }
        }
        
        $this->data['token'] = Token::generate();

        if (Session::exists('success')) {
			Session::put('notif-pesan', Session::flash('success'));
		} 

		if (Session::exists('warning')) {
			Session::put('notif-pesan', Session::flash('warning'));
		} 
		
		if (Session::exists('error')) {
			Session::put('notif-pesan', Session::flash('error'));
		}

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

    public function renew($params) {
        if (count($params) < 2) {
            Session::put('error', 'Anda Tidak Berhak Mengubah Password Akun');
            Redirect::to('auth/signin');
        }

        $this->script_action = array(
            array(
                'src' => 'https://cdn.jsdelivr.net/gh/robbmj/simple-js-countdown-timer@master/countdowntimer.js',
                'source' => 'trushworty'
            ),
            array(
                'src' => '/assets/route/auth/js/renew.js'
            )
        );

        $this->model('Auth');
        $data = $this->model->getExpiryResetAkun('SELECT id_akun, hash_expiry, salt FROM akun WHERE LOWER(email) = ? AND password = ? AND hash_reset = ?', 
            array(
                strtolower(trim(Sanitize::escape($params[0]))),
                strtolower(trim(Sanitize::escape($params[1]))),
                strtolower(trim(Sanitize::escape($params[2])))
            )
        );

        if (!$this->model->affected()) {
            Session::put('warning', 'Tidak memiliki akses reset password');
            Redirect::to('auth/reset');
        }

        if (!is_null($data->hash_expiry) && strtotime(date('Y-m-d H:i:s', time())) > strtotime($data->hash_expiry)) {
            Session::put('error', 'Token reset password telah kadaluarsa');
            Redirect::to('auth/signin');
        }

        $now = new DateTime(date('Y-m-d H:i:s'));
        $expiry = new DateTime($data->hash_expiry);
        $msInterval = $expiry->diff($now);
        $inInterval = strtotime($data->hash_expiry) - strtotime(date('Y-m-d H:i:s', time()));

        $this->data['sisa_waktu']['interval'] = $inInterval;
        $this->data['sisa_waktu']['menit'] = $msInterval->format("%I");
        $this->data['sisa_waktu']['detik'] = $msInterval->format("%S");

        if (Input::exists()) {
            if (Token::check(Input::get('token'))) {
                $vali = new Validate();
                $validate = $vali->check($_POST, array(
                    'password' => array(
                        'required' => true,
                        'min' => 6,
                        'max' => 32
                    ),
                    'konfirmasi' => array(
                        'required' => true,
                        'min' => 6,
                        'max' => 32,
                        'matches' => 'password'
                    )
                ));

                if (!$validate->passed()) {
                    Session::put('error_feedback', $validate->getValueFeedback());
                    Redirect::to('auth/reset/renew/'.$params[0].'/'.$params[1].'/'.$params[2]);
                }

                if (Sanitize::noSpace(Hash::make(Input::get('password'), $data->salt)) == $params[1]) {
                    $validate->setValueFeedback('password','harus berbeda dengan yang lama');
                    Session::put('error_feedback', $validate->getValueFeedback());
                    Redirect::to('auth/reset/renew/'.$params[0].'/'.$params[1].'/'.$params[2]);
                }

                try {
                    $this->model->update(array(
                        'password' => Sanitize::noSpace(Hash::make(Input::get('password'), $data->salt)),
                        'hash_reset' => '',
                        'hash_expiry' => ''
                    ), $data->id_akun);
                    Session::put('success', 'Berhasil reset password');
                    Redirect::to('auth/signin');
                } catch (\Throwable $th) {
                    Session::put('error', $th->errorMessage());
                    Redirect::to('auth/reset/renew/'.$params[0].'/'.$params[1].'/'.$params[2]);
                }
            }
        }

        $this->data['token'] = Token::generate();
    }
}