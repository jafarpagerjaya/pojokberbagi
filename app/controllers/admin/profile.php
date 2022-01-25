<?php
class ProfileController extends Controller {
    public function __construct() {
        $this->title = 'Profil';
        $this->title = 'Akun';
        $this->rel_action = array(
            array(
                'href' => '/assets/route/admin/core/css/admin-style.css'
            )
        );

        $this->_auth = $this->model("Auth");
        if (!$this->_auth->hasPermission('admin')) {
            Redirect::to('home');
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
                        'min' => 11,
                        'max' => 13,
                        'unique' => 'pegawai'
                    ),
                    'email' => array(
                        'required' => true,
                        'max' => 100,
                        'unique' => 'pegawai'
                    ),
                    'alamat' => array(
                        'required' => true,
                        'min' => 10,
                        'max' => 255
                    )
                ), array('id_pegawai','!=', $this->data['pegawai']->id_pegawai));
                if (!$validate1->passed()) {
                    Session::put('error_feedback', $validate1->getValueFeedback());
                    Redirect::to('admin/profile/unlock/'. Token::generate());
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
                    Redirect::to('admin/profile/unlock/'. Token::generate());
                }
                $this->model('Pegawai');

                $result1 = $this->model->update('pegawai', array(
                    'nama' => Sanitize::escape(trim(Input::get('nama'))),
                    'kontak' => Sanitize::escape(trim(Input::get('kontak'))),
                    'email' => Sanitize::escape(trim(Input::get('email'))),
                    'alamat' => Sanitize::escape(trim(Input::get('alamat')))
                    ), array('id_pegawai','=', $this->data['pegawai']->id_pegawai)
                );

                if (!is_null($this->data['akun']->id_donatur)) {
                    $checkIsDonatur = $this->model->getData('id_donatur','donatur', array('id_akun','=',$this->data['akun']->id_donatur));
                    if ($checkIsDonatur) {
                        $result11 = $this->model->update('donatur', array(
                            'nama' => Sanitize::escape(trim(Input::get('nama')))
                            ), array('id_pegawai','=', $this->data['pegawai']->id_pegawai)
                        );
                    }
                }

                $result2 = $this->model->update('akun', array(
                    'username' => Sanitize::escape(trim(Input::get('username'))),
                    'email' => Sanitize::escape(trim(Input::get('email')))
                    ), array('id_akun','=', $this->data['akun']->id_akun)
                );

                if ($result1 || $result2) {
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