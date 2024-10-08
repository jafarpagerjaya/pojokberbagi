<?php
class ProfileController extends Controller {
    public function __construct() {
        $this->rel_controller = array(
            array(
                'href' => '/assets/pojok-berbagi-style.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/admin-style.css'
            )
        );

        $this->script_controller = array(
            array(
                'src' => '/assets/pojok-berbagi-script.js'
            ),
            array(
                'src' => '/assets/route/admin/core/js/admin-script.js'
            )
        );
		$this->title = 'Profile';

        $this->_auth = $this->model("Auth");
        if (!$this->_auth->hasPermission('donatur')) {
            Redirect::to('');
        }

        $this->data['other_role'] = $this->_auth->otherPermission('donatur');

        $this->data['akun'] = $this->_auth->data();

        $this->model("Donatur");
        if (is_null($this->data['akun']->email)) {
            $akun_value =  $this->data['akun']->kontak; 
            $akun_field = 'kontak';
        } else {
            $akun_value =  $this->data['akun']->email; 
            $akun_field = 'email';
        }
        $this->model->getAllData('donatur', array($akun_field,'=', $akun_value));
        $this->data['donatur'] = $this->model->getResult();
        $this->data['route_alias'] = 'donatur';
        $checkIsPegawai = $this->model->isEmployee($this->data['akun']->id_akun);
        if ($checkIsPegawai) {
            $this->data['id_pegawai'] = $this->model->data()->id_pegawai;
        }
        $checkIsPemohon = $this->model->isPemohon($this->data['akun']->id_akun);
        if ($checkIsPemohon) {
            $this->data['id_pemohon'] = $this->model->data()->id_pemohon;
        }
	}

    public function index() {

        $this->script_action = array(
            array(
                'src' => '/assets/route/donatur/pages/js/profile.js'
            )
        );

        $dataGambar = $this->model->getData('path_gambar','gambar',array('id_gambar','=', ($this->data['akun']->id_gambar ?? '1')));
        if ($dataGambar) {
            $this->data['gambar'] = $this->model->getResult();
        }
    }

    public function unlock($params) {
        if (!Token::check($params[0])) {
            Redirect::to('donatur/profile');
        }

        $this->rel_action = array(
            array(
                'href' => '/assets/route/admin/core/css/form-element.css'
            )
        );

        $this->script_action = array(
            array(
                'src' => '/assets/route/admin/core/js/form-function.js'
            ),
            array(
                'src' => '/assets/route/donatur/pages/js/profile.js'
            ),
            array(
                'src' => '/assets/main/js/unlock-profile.js'
            )
        );
        
        $this->model->getData('path_gambar','gambar',array('id_gambar','=', $this->data['akun']->id_gambar));
        $this->data['gambar'] = $this->model->getResult();
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
                        'unique' => 'donatur',
                        'regex' => '/^([^\.\_\-\@])+([^\.\@\_\-])*((([^\d\@]){0,1})[a-z0-9]{2,}){0,1}((@([a-zA-Z]{2,})+(\.([a-z]{2,})){1,2}|@(\d{3}.){1,3})|(@([0-9]{1,3})+(\.([0-9]{1,3})){3}))$/'
                    ),
                    'samaran' => array(
                        'min' => 1,
                        'max' => 30
                    )
                ), array('id_donatur','!=', $this->data['donatur']->id_donatur));
                // if (!$validate1->passed()) {
                //     Session::put('error_feedback', $validate1->getValueFeedback());
                //     Redirect::to('donatur/profile/unlock/'. Token::generate());
                // }
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
                        'unique' => 'akun',
                        'regex' => '/^([^\.\_\-\@])+([^\.\@\_\-])*((([^\d\@]){0,1})[a-z0-9]{2,}){0,1}((@([a-zA-Z]{2,})+(\.([a-z]{2,})){1,2}|@(\d{3}.){1,3})|(@([0-9]{1,3})+(\.([0-9]{1,3})){3}))$/'
                    ),
                ), array('id_akun','!=', $this->data['akun']->id_akun));
                // if (!$validate2->passed()) {
                //     Session::put('error_feedback', $validate2->getValueFeedback());
                //     Redirect::to('donatur/profile/unlock/'. Token::generate());
                // }
                if (isset($this->data['id_pegawai'])) { 
                    $vali3 = new Validate();
                    $validate3 = $vali3->check($_POST, array(
                        'nama' => array(
                            'required' => true,
                            'min' => 3,
                            'max' => 30
                        ),
                        'email' => array(
                            'required' => true,
                            'max' => 100,
                            'unique' => 'pegawai',
                            'regex' => '/^([^\.\_\-\@])+([^\.\@\_\-])*((([^\d\@]){0,1})[a-z0-9]{2,}){0,1}((@([a-zA-Z]{2,})+(\.([a-z]{2,})){1,2}|@(\d{3}.){1,3})|(@([0-9]{1,3})+(\.([0-9]{1,3})){3}))$/'
                        ),
                        'kontak' => array(
                            'required' => true,
                            'digit' => true,
                            'min' => 11,
                            'max' => 13,
                            'unique' => 'pegawai'
                        )
                    ), array('id_pegawai','!=', $this->data['id_pegawai']));
                    // if (!$validate3->passed()) {
                    //     Session::put('error_feedback', $validate3->getValueFeedback());
                    //     Redirect::to('donatur/profile/unlock/'. Token::generate());
                    // }
                }

                if (isset($this->data['id_pegawai'])) { 
                    $vali4 = new Validate();
                    $validate4 = $vali4->check($_POST, array(
                        'nama' => array(
                            'required' => true,
                            'min' => 3,
                            'max' => 30
                        ),
                        'email' => array(
                            'required' => true,
                            'max' => 100,
                            'unique' => 'pemohon',
                            'regex' => '/^([^\.\_\-\@])+([^\.\@\_\-])*((([^\d\@]){0,1})[a-z0-9]{2,}){0,1}((@([a-zA-Z]{2,})+(\.([a-z]{2,})){1,2}|@(\d{3}.){1,3})|(@([0-9]{1,3})+(\.([0-9]{1,3})){3}))$/'
                        ),
                        'kontak' => array(
                            'required' => true,
                            'digit' => true,
                            'min' => 11,
                            'max' => 13,
                            'unique' => 'pemohon'
                        )
                    ), array('id_pemohon','!=', $this->data['id_pemohon']));
                    // if (!$validate3->passed()) {
                    //     Session::put('error_feedback', $validate3->getValueFeedback());
                    //     Redirect::to('donatur/profile/unlock/'. Token::generate());
                    // }
                }

                $validateList = array();

                if (!is_null($validate1)) {
                    if (!$validate1->passed()) {
                        array_push($validateList, $validate1->getValueFeedback());
                    }
                }

                if (!is_null($validate2)) {
                    if (!$validate2->passed()) {
                        array_push($validateList, $validate2->getValueFeedback());
                    }
                }

                if (!is_null($validate3)) {
                    if (!$validate3->passed()) {
                        array_push($validateList, $validate3->getValueFeedback());
                    }
                }

                if (!is_null($validate4)) {
                    if (!$validate4->passed()) {
                        array_push($validateList, $validate4->getValueFeedback());
                    }
                }

                if (count(is_countable($validateList) ? $validateList : [])) {
                    Session::put('error_feedback', Validate::errorArrayRuleList($validateList));
                    Redirect::to('donatur/profile/unlock/'. Token::generate());
                }

                $updateDonatur = $this->model->update('donatur', array(
                    'nama' => strtoupper(Sanitize::noDblSpace2(Input::get('nama'))),
                    'kontak' => Sanitize::toInt2(Input::get('kontak')),
                    'email' => strtolower(Sanitize::noSpace2(Input::get('email'))),
                    'samaran' => Sanitize::escape3(Sanitize::noDblSpace2(Input::get('samaran')))
                    ), array('id_donatur','=', $this->data['donatur']->id_donatur)
                );

                $updateAkun = $this->model->update('akun', array(
                    'username' => Sanitize::escape3(Sanitize::escape2(Input::get('username'))),
                    'email' => strtolower(Sanitize::escape2(Input::get('email')))
                    ), array('id_akun','=', $this->data['akun']->id_akun)
                );

                if (!is_null($this->data['id_pegawai'])) {
                    $updatePegawai = $this->model->update('pegawai', array(
                        'nama' => strtoupper(Sanitize::noDblSpace2(Input::get('nama'))),
                        'kontak' => Sanitize::toInt2(Input::get('kontak')),
                        'email' => strtolower(Sanitize::noSpace2(Input::get('email')))
                        ), array('id_pegawai','=', $this->data['id_pegawai'])
                    );
                }

                if (!is_null($this->data['id_pemohon'])) {
                    $updatePemohon = $this->model->update('pegawai', array(
                        'nama' => strtoupper(Sanitize::noDblSpace2(Input::get('nama'))),
                        'kontak' => Sanitize::toInt2(Input::get('kontak')),
                        'email' => strtolower(Sanitize::noSpace2(Input::get('email')))
                        ), array('id_pemohon','=', $this->data['id_pemohon'])
                    );
                }

                if ($updateDonatur || $updateAkun) {
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
        if (count(is_countable($params) ? $params : [])) {
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