<?php
class ProfileController extends Controller {
    public function __construct() {
		$this->title = 'Profile';
		
        

        $this->_auth = $this->model("Auth");
        if (!$this->_auth->hasPermission('donatur')) {
            Redirect::to('home');
        }

        $this->data['akun'] = $this->_auth->data();

        $this->model("Donatur");
        $this->model->getAllData('donatur', array('email','=', $this->data['akun']->email));
        $this->data['donatur'] = $this->model->data();
        $this->data['route_alias'] = 'donatur';
	}

    public function index() {

        $this->script_action = array(
            array(
                'src' => '/assets/route/donatur/pages/js/profile.js'
            )
        );

        $dataGambar = $this->model->getData('path_gambar','gambar',array('id_gambar','=', $this->data['akun']->id_gambar));
        if ($dataGambar) {
            $this->data['gambar'] = $dataGambar;
        }
    }

    public function unlock($params) {
        if (!Token::check($params[0])) {
            Redirect::to('donatur/profile');
        }

        $this->script_action = array(
            array(
                'src' => '/assets/route/donatur/pages/js/profile.js'
            )
        );
        
        $dataGambar = $this->model->getData('path_gambar','gambar',array('id_gambar','=', $this->data['akun']->id_gambar));
        if ($dataGambar) {
            $this->data['gambar'] = $dataGambar;
        }
        return VIEW_PATH.'donatur'.DS.'profile'.DS.'form-edit-profile.html';
    }

    public function update() {
        if (Input::exists()) {
            if (Token::check(Input::get('token'))) {
                $vali = new Validate();
                $validate1 = $vali->check($_POST, array(
                    'nama' => array(
                        'required' => true,
                        'min' => 3,
                        'max' => 30
                    ),
                    'kontak' => array(
                        'digit' => true,
                        'min' => 11,
                        'max' => 13,
                        'unique' => 'donatur'
                    ),
                    'email' => array(
                        'required' => true,
                        'max' => 100,
                        'unique' => 'donatur'
                    ),
                    'samaran' => array(
                        'min' => 1,
                        'max' => 30
                    )
                ), array('id_donatur','!=', $this->data['donatur']->id_donatur));
                if (!$validate1->passed()) {
                    Session::put('error_feedback', $validate1->getValueFeedback());
                    Redirect::to('donatur/profile/unlock/'. Token::generate());
                }
                $vali2 = new Validate();
                $validate2 = $vali2->check($_POST, array(
                    'username' => array(
                        'required' => true,
                        'min' => 4,
                        'max' => 50,
                        'unique' => 'akun'
                    ),
                    'email' => array(
                        'required' => true,
                        'max' => 100,
                        'unique' => 'akun'
                    ),
                ), array('id_akun','!=', $this->data['akun']->id_akun));
                if (!$validate2->passed()) {
                    Session::put('error_feedback', $validate2->getValueFeedback());
                    Redirect::to('donatur/profile/unlock/'. Token::generate());
                }
                $this->model('Donatur');

                $result1 = $this->model->update('donatur', array(
                    'nama' => Sanitize::escape(Sanitize::noDblSpace(trim(Input::get('nama')))),
                    'kontak' => Sanitize::escape(Sanitize::noSpace(trim(Input::get('kontak')))),
                    'email' => Sanitize::escape(Sanitize::noSpace(trim(Input::get('email')))),
                    'samaran' => Sanitize::escape(Sanitize::noDblSpace(trim(Input::get('samaran'))))
                    ), array('id_donatur','=', $this->data['donatur']->id_donatur)
                );

                if (!is_null($this->data['akun']->id_donatur)) {
                    $checkIsDonatur = $this->model->getData('id_donatur','donatur', array('id_akun','=',$this->data['akun']->id_donatur));
                    if ($checkIsDonatur) {
                        $result11 = $this->model->update('donatur', array(
                            'nama' => Sanitize::escape(Sanitize::noDblSpace(trim(Input::get('nama'))))
                            ), array('id_donatur','=', $this->data['donatur']->id_donatur)
                        );
                    }
                }

                $result2 = $this->model->update('akun', array(
                    'username' => Sanitize::escape(Sanitize::noDblSpace(trim(Input::get('username')))),
                    'email' => Sanitize::escape(Sanitize::noSpace(trim(Input::get('email'))))
                    ), array('id_akun','=', $this->data['akun']->id_akun)
                );

                if ($result1 || $result2) {
                    Session::flash('success', 'Data Profil Berhasil Diupdate');
                } else {
                    Session::flash('error', 'Data Profil Gagal Diupdate');
                }
                Redirect::to('donatur/profile');
            }
            Redirect::to('donatur/profile/unblock/' . Token::generate());
        } else {
            Redirect::to('donatur/profile');
        }
    }

    public function password($params) {
        if (count($params)) {
            if ($params[0] == "update") {
                if (Input::exists()) {
                    $vali = new Validate();
                    $validasi = $vali->check($_POST, array(
                        'password_baru' => array(
                            'required' => true,
                            'min' => 4,
                            'max' => 50,
                        ),
                        'konfirmasi_ulang' => array(
                            'required' => true,
                            'min' => 4,
                            'max' => 50,
                            'matches' => 'password_baru'
                        ),
                        'password_lama' => array(
                            'required' => true,
                            'min' => 4,
                            'max' => 50
                        )
                    ));

                    if (!$validasi->passed()) {
                        Session::put('error_feedback', $validasi->getValueFeedback());
                        Redirect::to('donatur/profile/#kelola-password');
                    }

                    if (Hash::make(Input::get('password_lama'), $this->data['akun']->salt) !== $this->data['akun']->password) {
                        $validasi->setValueFeedback('password_lama', 'Password lama salah');
                        Session::put('error_feedback', $validasi->getValueFeedback());
                        Redirect::to('donatur/profile/#kelola-password');
                    }

                    if (Input::get('password_baru') === Input::get('password_lama')) {
                        $validasi->setValueFeedback('password_baru', 'Password baru masih sama');
                        Session::put('error_feedback', $validasi->getValueFeedback());
                        Redirect::to('donatur/profile/#kelola-password');
                    }

                    $salt = Hash::salt(32);
                    $this->_auth->update(array(
                        'password' => Hash::make(Sanitize::noSpace(Input::get('password_baru')), $salt),
                        'salt' => $salt
                    ));

                    if (!$this->_auth->affected()) {
                        Session::flash('error','Gagal Update Password');
                    } else {
                        Session::flash('success','Berhasil Update Password');
                    }
                    Redirect::to('donatur/profile');
                }
            }
        }
        Redirect::to('donatur/profile/#kelola-password');
    }
}