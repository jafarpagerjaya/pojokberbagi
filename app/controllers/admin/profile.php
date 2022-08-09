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

        $this->title = 'Profil';

        $this->_auth = $this->model("Auth");
        if (!$this->_auth->hasPermission('admin')) {
            Redirect::to('donatur');
        }

        $this->data['akun'] = $this->_auth->data();

        $admin = $this->model("Admin");
        $this->model->getAllData('pegawai', array('email','=', $this->_auth->data()->email));
        $this->data['pegawai'] = $admin->data();

        $this->model->getData('alias', 'jabatan', array('id_jabatan','=',$admin->data()->id_jabatan));
        $this->data['admin_alias'] = $admin->data()->alias;
    }

    public function index() {
        $this->script_action = array(
            array(
                'src' => '/assets/route/admin/pages/js/profile.js'
            )
        );

        $data = $this->model->getData('nama','jabatan',array('id_jabatan','=',$this->data['pegawai']->id_jabatan));
        $this->data['jabatan'] = $data;
        if ($this->data['akun']->id_gambar == 1) {
            $id_gambar = ((strtolower($this->data['pegawai']->jenis_kelamin) == 'p') ? 2 : 3);
        } else {
            $id_gambar = $this->data['akun']->id_gambar;
        }
        $dataGambar = $this->model->getData('path_gambar','gambar',array('id_gambar','=',$id_gambar));
        if ($dataGambar) {
            $this->data['gambar'] = $dataGambar;
        }
    }

    public function unlock($params) {
        if (!Token::check($params[0])) {
            Redirect::to('admin/profile');
        }
        $data = $this->model->getData('nama','jabatan',array('id_jabatan','=',$this->data['pegawai']->id_jabatan));
        $this->data['jabatan'] = $data;
        if ($this->data['akun']->id_gambar == 1) {
            $id_gambar = ((strtolower($this->data['pegawai']->jenis_kelamin) == 'p') ? 2 : 3);
        } else {
            $id_gambar = $this->data['akun']->id_gambar;
        }
        $dataGambar = $this->model->getData('path_gambar','gambar',array('id_gambar','=',$id_gambar));
        if ($dataGambar) {
            $this->data['gambar'] = $dataGambar;
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
                'src' => '/assets/main/js/unlock-profile.js'
            ),
            array(
                'src' => '/assets/route/admin/pages/js/unlock.js'
            )
        );
        return VIEW_PATH.'admin'.DS.'profile'.DS.'form-edit-profile.html';
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
                        'required' => true,
                        'digit' => true,
                        'min' => 5,
                        'max' => 13,
                        'unique' => 'pegawai'
                    ),
                    'email' => array(
                        'required' => true,
                        'max' => 100,
                        'unique' => 'pegawai',
                        'regex' => '/^([^\.\_\-\@])+([^\.\@\_\-])*((([^\d\@]){0,1})[a-z0-9]{2,}){0,1}((@([a-zA-Z]{2,})+(\.([a-z]{2,})){1,2}|@(\d{3}.){1,3})|(@([0-9]{1,3})+(\.([0-9]{1,3})){3}))$/'
                    ),
                    'alamat' => array(
                        'required' => true,
                        'min' => 10,
                        'max' => 255
                    )
                ), array('id_pegawai','!=', $this->data['pegawai']->id_pegawai));

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

                $vali3 = new Validate();
                $validate3 = $vali3->check($_POST, array(
                    'email' => array(
                        'required' => true,
                        'max' => 100,
                        'unique' => 'donatur',
                        'regex' => '/^([^\.\_\-\@])+([^\.\@\_\-])*((([^\d\@]){0,1})[a-z0-9]{2,}){0,1}((@([a-zA-Z]{2,})+(\.([a-z]{2,})){1,2}|@(\d{3}.){1,3})|(@([0-9]{1,3})+(\.([0-9]{1,3})){3}))$/'
                    ),
                    'kontak' => array(
                        'required' => true,
                        'digit' => true,
                        'min' => 11,
                        'max' => 13,
                        'unique' => 'donatur'
                    )
                ), array('id_akun','!=', $this->data['akun']->id_akun));

                if (!$validate2->passed() || !$validate1->passed() || !$validate3->passed()) {
                    $validateList = array($validate1->getValueFeedback(), $validate2->getValueFeedback(), $validate3->getValueFeedback());
                    Session::put('error_feedback', Validate::errorArrayRuleList($validateList));
                    Redirect::to('admin/profile/unlock/'. Token::generate());
                }

                $this->model('Pegawai');

                $updatePegawai = $this->model->update('pegawai', array(
                    'nama' => strtoupper(Sanitize::noDblSpace2(Input::get('nama'))),
                    'kontak' => Sanitize::toInt2(Input::get('kontak')),
                    'email' => strtolower(Sanitize::noSpace2(Input::get('email'))),
                    'alamat' => Sanitize::escape3(Sanitize::escape2(Input::get('alamat')))
                    ), array('id_pegawai','=', $this->data['pegawai']->id_pegawai)
                );

                if (!is_null($this->data['akun']->id_akun)) {
                    $checkIsDonatur = $this->model->getData('id_donatur','donatur', array('id_akun','=',$this->data['akun']->id_akun));
                    if ($checkIsDonatur) {
                        $updateDonatur = $this->model->update('donatur', array(
                            'nama' => strtoupper(Sanitize::escape2(Input::get('nama'))),
                            'email' => strtolower(Sanitize::escape2(Input::get('email'))),
                            'kontak' => Sanitize::toInt2(Input::get('kontak')),
                            ), array('id_akun','=', $this->data['akun']->id_akun)
                        );
                    }
                }

                $updateAkun = $this->model->update('akun', array(
                    'username' => Sanitize::escape3(Sanitize::escape2(Input::get('username'))),
                    'email' => strtolower(Sanitize::escape2(Input::get('email')))
                    ), array('id_akun','=', $this->data['akun']->id_akun)
                );

                if ($updatePegawai || $updateAkun || $updateDonatur) {
                    Session::flash('success', 'Data Profil Berhasil Diupdate');
                } else {
                    Session::flash('error', 'Data Profil Gagal Diupdate');
                }
                
                Redirect::to('admin/profile');
            }
            Redirect::to('admin/profile/unblock/' . Token::generate());
        } else {
            Redirect::to('admin/profile');
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
                        Redirect::to('admin/profile/#kelola-password');
                    }

                    if (Hash::make(Input::get('password_lama'), $this->data['akun']->salt) !== $this->data['akun']->password) {
                        $validasi->setValueFeedback('password_lama', 'Password lama salah');
                        Session::put('error_feedback', $validasi->getValueFeedback());
                        Redirect::to('admin/profile/#kelola-password');
                    }

                    if (Input::get('password_baru') === Input::get('password_lama')) {
                        $validasi->setValueFeedback('password_baru', 'Password baru masih sama');
                        Session::put('error_feedback', $validasi->getValueFeedback());
                        Redirect::to('admin/profile/#kelola-password');
                    }

                    $salt = Hash::salt(32);
                    $this->_auth->update(array(
                        'password' => Hash::make(Input::get('password_baru'), $salt),
                        'salt' => $salt
                    ));

                    if (!$this->_auth->affected()) {
                        Session::flash('error','Gagal Update Password');
                    } else {
                        Session::flash('success','Berhasil Update Password');
                    }
                    Redirect::to('admin/profile');
                }
            }
        }
        Redirect::to('admin/profile/#kelola-password');
    }
}