<?php 
class DonaturController extends Controller {
    private $_donatur;

    public function __construct() {
        $this->rel_controller = array(
            array(
                'href' => ASSET_PATH . 'route' . DS . basename(dirname(__FILE__)) . DS . 'core' . DS . 'css' . DS . 'admin-style.css'
            )
        );

        $this->script_controller = array(
			array(
				'type' => 'text/javascript',
                'src' => '/assets/pojok-berbagi-script.js'
			)
		);

        $this->title = 'Donatur';
        $auth = $this->model('Auth');
        if (!$auth->hasPermission('admin')) {
            Redirect::to('donatur');
        }

        $this->data['akun'] = $auth->data();

        $admin = $this->model("Admin");
        $this->model->getAllData('pegawai', array('email','=', $auth->data()->email));
        $this->data['pegawai'] = $admin->data();

        $this->model->getData('alias', 'jabatan', array('id_jabatan','=',$admin->data()->id_jabatan));
        $this->data['admin_alias'] = $admin->data()->alias;

        $this->_donatur = $this->model('Donatur');
    }

    public function index() {
        $this->script_action = array(
			array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/core/js/admin-script.js'
			)
		);

        switch (strtoupper($this->data['admin_alias'])) {
            case 'SYS':
                $this->sys();
                return VIEW_PATH.'admin'.DS.'donatur'.DS.'sys.html';
            break;

            case 'CRE':
                $this->cr();
                return VIEW_PATH.'admin'.DS.'donatur'.DS.'cre.html';
            break;
            
            default:
                # code...
            break;
        }
    }

    public function cr() {
        $this->title = 'Donatur By CR';
        $this->model('Cr');
        $this->data['info-card'] = array(
            'jumlah_donatur' => $this->model->jumlahDonatur(),
            'jumlah_akun' => $this->model->jumlahAkun(),
        );

        $this->data['halaman'] = 1;
        $this->_donatur->dataDonatur(1);
        $this->data['donatur'] = $this->_donatur->data();
        $this->_donatur->countData('donatur');
        $this->data['record'] = $this->_donatur->data()->jumlah_record;
        $this->data['token'] = Token::generate();
    }

    public function sys() {
        $this->title = 'Donatur By Sys';
        
        $this->model('Sys');
        $this->data['info-card'] = array(
            'jumlah_donatur' => $this->model->jumlahDonatur(),
            'jumlah_akun' => $this->model->jumlahAkun(),
        );

        $this->data['halaman'] = 1;
        $this->_donatur->dataDonatur(1);
        $this->data['donatur'] = $this->_donatur->data();
        $this->_donatur->countData('donatur');
        $this->data['record'] = $this->_donatur->data()->jumlah_record;
        $this->data['token'] = Token::generate();
        // $this->_donatur->setPageLink();
        // $this->data['pageLink'] = json_decode(json_encode($this->_donatur->data()), true);
    }

    public function halaman($params) {
        if (count(is_countable($params) ? $params : [])) {
            $this->script_action = array(
                array(
                    'type' => 'text/javascript',
                    'src' => '/assets/route/admin/core/js/admin-script.js'
                )
            );
            $this->model('Cr');
            $this->data['info-card'] = array(
                'jumlah_donatur' => $this->model->jumlahDonatur(),
                'jumlah_akun' => $this->model->jumlahAkunDonatur(),
            );
            $this->_donatur->dataDonatur($params[0]);
            $this->data['donatur'] = $this->_donatur->data();
            $this->_donatur->countData('donatur');
            $this->data['record'] = $this->_donatur->data()->jumlah_record;
            $this->data['halaman'] = $params[0];
            $this->data['token'] = Token::generate();
            // $this->_donatur->setPageLink();
            // $this->data['pageLink'] = json_decode(json_encode($this->_donatur->data()), true);
            return VIEW_PATH . 'admin' . DS . 'donatur' . DS . 'cre.html';
        }
        Redirect::to('admin/donatur');
    }

    public function formulir($params = null) {
        $this->data['token'] = Token::generate();
        if (count(is_countable($params) ? $params : [])) {
            $this->formUpdate($params[0]);
            return VIEW_PATH.'admin'.DS.'donatur'.DS.'form-update.html';
        }
    }

    public function formUpdate($id_donatur) {
        $data = $this->_donatur->getData('id_donatur,nama,kontak,email','donatur',array('id_donatur','=',$id_donatur));
        if ($data) {
            $this->data['data_donatur'] = $this->_donatur->data();
        }
    }

    public function update($params) {
        if (count(is_countable($params) ? $params : [])) {
            if (Input::exists()) {
                if (Token::check(Input::get('token'))) {
                    if ($params[0] != Input::get('id_donatur')) {
                        Session::flash('error','Id Donatur Tidak Cocok');
                        Redirect::to('admin/donatur');
                    }
                    $vali = new Validate();
                    $validate = $vali->check($_POST, array(
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
                            'max' => 100,
                            'unique' => 'donatur'
                        )
                    ), array('id_donatur','!=', Input::get('id_donatur')));
                    if (!$validate->passed()) {
                        Session::put('error_feedback', $validate->getValueFeedback());
                        Redirect::to('admin/donatur/formulir/'.$params[0]);      
                    } else {
                        $result = $this->_donatur->update('donatur', array(
                            'nama' => Sanitize::escape(trim(Input::get('nama'))),
                            'kontak' => Sanitize::escape(trim(Input::get('kontak'))),
                            'email' => Sanitize::escape(trim(Input::get('email')))
                            ), array('id_donatur','=', Sanitize::escape(Input::get('id_donatur')))
                        );
                        if ($result) {
                            $this->_donatur->hasAccount(Sanitize::escape(Input::get('id_donatur')));
                            if ($this->_donatur->data()->account_found != 0) {
                                $id_akun = $this->_donatur->data()->id_akun;
                                if ($this->_donatur->isEmployee($id_akun)) {
                                    $id_pegawai = $this->_donatur->data()->id_pegawai;
                                    $this->_donatur->update('pegawai', array(
                                        'nama' => Sanitize::escape2(Input::get('nama')),
                                        'kontak' => Sanitize::escape2(Input::get('kontak')),
                                        'email' => Sanitize::escape2(Input::get('email'))
                                    ), array(
                                        'id_pegawai','=', Sanitize::escape2($id_pegawai)
                                    ));
                                }
                                Debug::vd($this->_donatur->data()->account_found);
                                $this->_donatur->update('akun', array(
                                    'email' => Sanitize::escape2(Input::get('email'))
                                ), array('id_akun', '=', Sanitize::escape2($id_akun)));
                            }
                            Session::flash('success', 'Data donatur dengan [ID] <b>'. Sanitize::escape2(Input::get('id_donatur')) .'</b> berhasil diupdate.');
                        } else {
                            Session::flash('error', 'Data donatur dengan [ID] <b>'. Sanitize::escape2(Input::get('id_donatur')) .'</b> gagal diupdate.');
                        }
                        Redirect::to('admin/donatur');
                    }
                }
            }
            Redirect::to('admin/donatur/formulir/' . $params[0]);
        } else {
            Redirect::to('admin/donatur');
        }
    }

    public function tambah() {
        if (Input::exists()) {
			if (Token::check(Input::get('token'))) {
				$vali = new Validate();
                $validate = $vali->check($_POST, array(
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
                        'max' => 100,
                        'unique' => 'donatur'
                    )
                ));
                if (!$validate->passed()) {
                    Session::put('error_feedback', $validate->getValueFeedback());
                    Redirect::to('admin/donatur/formulir');
                } else {
                    $hasil = $this->_donatur->create('donatur', array(
                        'nama' => ucwords(trim(Input::get('nama'))),
                        'kontak' => Sanitize::toInt(trim(Input::get('kontak'))),
                        'email' => strtolower(trim(Input::get('email')))
                    ));
                    if ($hasil) {
                        Session::flash('success','Berhasil Tambah Data');
                        Redirect::to('admin/donatur/formulir');
                    } else {
                        Session::flash('error','Gagal Tambah Data');
                        Redirect::to('admin/donatur/formulir');
                    }
                }
            }
        }
        Redirect::to('admin/donatur');
    }

    public function data($params) {
        if (count(is_countable($params[0]) ? $params[0] : [])) {
            $this->rel_action = array(
                array(
                    'href' => ASSET_PATH.basename(dirname(__FILE__)).DS.'pages'.DS.'css'.DS.'data.css'
                ),
                array(
                    'href' => VENDOR_PATH.'chart.js'.DS.'dist'.DS.'Chart.min.css'
                )
            );

            $this->script_action = array(
                array(
                    'src' => VENDOR_PATH.'chart.js'.DS.'dist'.DS.'Chart.min.js'
                ),
                array(
                    'src' => ASSET_PATH.basename(dirname(__FILE__)).DS.'pages'.DS.'js'.DS.'data.js'
                )
            );

            $data = $this->_donatur->getData('id_donatur, nama, IFNULL(kontak,"Belum Ada") kontak, create_at, email, IFNULL(id_akun,"Belum Punya Akun") id_akun','donatur', array('id_donatur','=',$params[0]));
            if ($data) {
                $this->data['data_donatur'] = $data;
            }
            $donasiTerakhir = $this->_donatur->donasiTerakhir($params[0]);
            $this->data['donasi_terakhir'] = $donasiTerakhir;

            $this->_donatur->setFilterBy('bulan',3);
            $donasiTBTerakhir = $this->_donatur->getJumlahDonasiDonatur($params[0]);
        } else {
            Redirect::to('admin/donatur');
        }
    }

    // Dinon aktifkan karena auth/signup/hook butuh create akun lalu bagaimana dengan data username dan passowrd dari akunnya
    // public function kaitkan($params) {
    //     if (!count(is_countable($params) ? $params : []) > 1) {
    //         Redirect::to('admin/donatur');
    //     }
    //     if (Token::check2($params[1])) {
    //         $this->_donatur->getData('nama, email','donatur',array('id_donatur','=', intval($params[0])),'AND',array('id_akun','IS',NULL));
    //         if ($this->_donatur->affected()) {
    //             if (!is_null($this->_donatur->data()->email)) {
    //                 $this->model('Auth');
    //                 $salt = Hash::salt(32);
    //                 $akunArray = array(
    //                     'username' 	=> strtolower($this->_donatur->data()->email),
    //                     'password' 	=> Sanitize::noSpace2(Hash::make(strtolower($this->_donatur->data()->email), $salt)),
    //                     'salt' 		=> $salt,
    //                     'email' 	=> strtolower($this->_donatur->data()->email)
    //                 );
    //                 // Check email is staff
    //                 $staff = $this->_auth->isStaff(Sanitize::noSpace(Input::get('email')), 'email');

    //                 if ($staff) {
    //                     $id_pegawai = $this->_auth->data()->id_pegawai;
    //                     $akunArray['hak_akses'] = 'A';
    //                 }
                    
    //                 $this->_auth->create($akunArray);
                    
    //                 $id_akun = $this->_auth->lastIID();
    //                 // Send Mail here
    //                 $pengirim = "pojokberbagi.id";    
    //                 $penerima = $this->_donatur->data()->email;    
    //                 $subjek = "Mengkaitkan Akun Pojok Berbagi";    
    //                 $pesan = "Klik <a href='https://pojokberbagi.id/auth/signup/hook/". $params[0]. "/" . $id_akun . "/" . $this->_donatur->data()->email . "/" . $salt ."'>disini</a> untuk mengkaitkan akunmu.";   
    //                 $headers = "Dari :" . $pengirim;    
    //                 mail($penerima,$subjek,$pesan, $headers);
    //                 Session::put('notifikasi', array(
    //                     'pesan' => 'Cek email <b>' . $this->_donatur->data()->email . '</b> untuk mengkaitkan akun baru',
    //                     'state' => 'success'
    //                 ));
    //             } else {
    //                 Session::put('notifikasi', array(
    //                     'pesan' => 'Donatur <b>' . $this->_donatur->data()->nama . '</b> belum terdata alamat emailnya sehingga gagal dikaitkan',
    //                     'state' => 'warning'
    //                 ));
    //             }
    //         } else {
    //             Session::put('notifikasi', array(
    //                 'pesan' => 'Akun donatur <b>' . $this->_donatur->data()->nama . '</b> tidak ditemukan sehingga gagal dikaitkan',
    //                 'state' => 'danger'
    //             ));
    //         }
    //     }
    //     Redirect::to('admin/donatur');
    // }
}