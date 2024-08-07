<?php 
class DonaturController extends Controller {
    private $_donatur;

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

        $this->title = 'Donatur';
        
        $this->_auth = $this->model("Auth");
        if (!$this->_auth->hasPermission('admin')) {
            Redirect::to('donatur');
        }

        $this->data['akun'] = $this->_auth->data();

        $this->_admin = $this->model("Admin");
        $this->_admin->getAllData('pegawai', array('email','=', $this->data['akun']->email));
        $this->data['pegawai'] = $this->_admin->getResult();

        if (is_null($this->data['pegawai']->id_jabatan)) {
            Redirect::to('donatur');
        }

        $this->_admin->getData('alias', 'jabatan', array('id_jabatan','=',$this->data['pegawai']->id_jabatan));
        $this->data['admin_alias'] = $this->_admin->getResult()->alias;

        $this->_donatur = $this->model('Donatur');
    }

    public function index() {
        switch (strtoupper($this->data['admin_alias'])) {
            case 'SYS':
                $this->sys();
                return VIEW_PATH.'admin'.DS.'donatur'.DS.'sys.html';
            break;

            case 'CRE':
                $this->cr();
                return VIEW_PATH.'admin'.DS.'donatur'.DS.'cre.html';
            break;

            case 'IT':
                $this->sys();
                return VIEW_PATH.'admin'.DS.'donatur'.DS.'sys.html';
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
            switch (strtoupper($this->data['admin_alias'])) {
                case 'SYS':
                    return VIEW_PATH.'admin'.DS.'donatur'.DS.'sys.html';
                break;
    
                case 'CRE':
                    return VIEW_PATH.'admin'.DS.'donatur'.DS.'cre.html';
                break;
    
                case 'IT':
                    return VIEW_PATH.'admin'.DS.'donatur'.DS.'sys.html';
                break;
                
                default:
                    # code...
                break;
            }
        }
        Redirect::to('admin/donatur');
    }

    public function formulir($params = null) {
        $this->rel_action = array(
            array(
                'href' => '/assets/route/admin/core/css/form-element.css'
            ),
            array(
                'href' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
            )
        );
        $this->script_action = array(
            array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/core/js/form-function.js'
			),
            array(
                'source' => 'trushworty',
                'src' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'
            ),
            array(
                'src' => '/assets/route/admin/pages/js/donatur.js'
            )
        );
        
        $this->data['token'] = Token::generate();
        if (count(is_countable($params) ? $params : [])) {
            $this->formUpdate($params[0]);
            return VIEW_PATH.'admin'.DS.'donatur'.DS.'form-update.html';
        }
    }

    public function formUpdate($id_donatur) {
        $data = $this->_donatur->getData('id_donatur,nama,kontak,email,jenis_kelamin','donatur',array('id_donatur','=',$id_donatur));
        if ($data) {
            $this->data['data_donatur'] = $this->_donatur->getResult();
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
                        $validate->setValueFeedback('jenis_kelamin');
                        Session::put('error_feedback', $validate->getValueFeedback());
                        Redirect::to('admin/donatur/formulir/'.$params[0]);      
                    } else {
                        $jenis_kelamin = strtoupper(Sanitize::escape2(Input::get('jenis_kelamin')));
                        if (!empty($jenis_kelamin)) {
                            if ($jenis_kelamin != 'L' && $jenis_kelamin != 'P') {
                                $jenis_kelamin = null;
                            }
                        }
                        $this->_donatur->update('donatur', array(
                            'nama' => Sanitize::escape2(Input::get('nama')),
                            'kontak' => Sanitize::escape2(Input::get('kontak')),
                            'email' => Sanitize::escape2(Input::get('email')),
                            'jenis_kelamin' => $jenis_kelamin
                            ), array('id_donatur','=', Sanitize::escape2(Input::get('id_donatur')))
                        );
                        if ($this->_donatur->affected()) {
                            $this->_donatur->hasAccount(Sanitize::escape2(Input::get('id_donatur')));
                            if ($this->_donatur->data()->account_found != 0) {
                                $id_akun = $this->_donatur->data()->id_akun;
                                if ($this->_donatur->isEmployee($id_akun)) {
                                    $id_pegawai = $this->_donatur->data()->id_pegawai;
                                    $this->_donatur->update('pegawai', array(
                                        'nama' => Sanitize::escape2(Input::get('nama')),
                                        'kontak' => Sanitize::escape2(Input::get('kontak')),
                                        'email' => Sanitize::escape2(Input::get('email')),
                                        'jenis_kelamin' => $jenis_kelamin
                                    ), array(
                                        'id_pegawai','=', Sanitize::escape2($id_pegawai)
                                    ));
                                }
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
                    $jenis_kelamin = strtoupper(Sanitize::escape2(Input::get('jenis_kelamin')));
                    if (!empty($jenis_kelamin)) {
                        if ($jenis_kelamin != 'L' && $jenis_kelamin != 'P') {
                            $jenis_kelamin = null;
                        }
                    }
                    $hasil = $this->_donatur->create('donatur', array(
                        'nama' => ucwords(trim(Input::get('nama'))),
                        'kontak' => Sanitize::toInt(trim(Input::get('kontak'))),
                        'email' => strtolower(trim(Input::get('email'))),
                        'jenis_kelamin' => $jenis_kelamin
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
    public function kaitkan($params) {
        if (!count(is_countable($params) ? $params : []) > 1) {
            Redirect::to('admin/donatur');
        }

        if (Token::check2($params[1])) {
            $this->_donatur->getData('nama, email, kontak','donatur',array('id_donatur','=', intval($params[0])),'AND',array('id_akun','IS',NULL));
            if ($this->_donatur->affected()) {
                $dataDonatur = $this->_donatur->getResult();
                $this->model('Auth');
                $salt = Hash::salt(32);

                if (!is_null($dataDonatur->email)) {     
                    $akunArray = array(
                        'username' 	=> str_pad(strtolower($dataDonatur->nama),5,Hash::unique()),
                        'password' 	=> Sanitize::noSpace2(Hash::make(strtolower($dataDonatur->email), $salt)),
                        'salt' 		=> $salt,
                        'email' 	=> strtolower($dataDonatur->email)
                    );

                    // Check email is staff
                    $staff = $this->_auth->isStaff(Sanitize::noSpace(Input::get('email')), 'email');

                    if ($staff) {
                        $id_pegawai = $this->_auth->getResult()->id_pegawai;
                        $akunArray['hak_akses'] = 'A';
                    }

                    $this->_donatur->getData('id_akun','akun',array('email','=',$dataDonatur->email));
                    if ($this->_donatur->affected()) {
                        $id_akun = $this->_donatur->getResult()->id_akun;
                    } else {
                        $this->_auth->create($akunArray);
                        if (!$this->_auth->affected()) {
                            Session::put('notifikasi', array(
                                'pesan' => 'Failed create akun By Email',
                                'state' => 'warning'
                            ));
                            Redirect::to('admin/donatur');
                        }
                        $id_akun = $this->_auth->lastIID();
                    }
                    $link = Config::getHTTPHost() ."/auth/signup/hook/". $params[0]. DS . $id_akun . DS . "email" . DS . $dataDonatur->email . DS . $salt;
                    $dataHook = array(
                        'nama' => $dataDonatur->nama,
                        'link' => $link,
                        'username' => $akunArray['username'],
                        'password' => strtolower($dataDonatur->email)
                    );
                    // Send mail Hook
                    $headers = 'From: Pojok Berbagi <no-replay@pojokberbagi.id>' . "\r\n" . 'Reply-To: No Replay <no-replay@pojokberbagi.id>' . "\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    $message = Ui::emailHookAkun($dataHook);
                    $penerima = $dataDonatur->email;    
                    $subjek = "Mengkaitkan Akun Pojok Berbagi";    
                    mail($penerima,$subjek,$message, $headers);
                    Session::put('notifikasi', array(
                        'pesan' => 'Cek email <b>' . $dataDonatur->email . '</b> untuk mengkaitkan akun baru',
                        'state' => 'success'
                    ));
                } else if (!is_null($dataDonatur->kontak) && is_null($dataDonatur->email)) {
                    $akunArray = array(
                        'username' 	=> str_pad(strtolower($dataDonatur->nama),5,Hash::unique()),
                        'password' 	=> Sanitize::noSpace2(Hash::make(Sanitize::escape2($dataDonatur->kontak), $salt)),
                        'salt' 		=> $salt,
                        'kontak' 	=> strtolower($dataDonatur->kontak)
                    );

                    // Check email is staff
                    $staff = $this->_auth->isStaff(Sanitize::escape2(Sanitize::noSpace2(Input::get('kontak'))), 'kontak');

                    if ($staff) {
                        $id_pegawai = $this->_auth->getResult()->id_pegawai;
                        $akunArray['hak_akses'] = 'A';
                    }

                    $this->_donatur->getData('id_akun','akun',array('kontak','=',$dataDonatur->kontak));
                    if ($this->_donatur->affected()) {
                        $id_akun = $this->_donatur->getResult()->id_akun;
                    } else {
                        $this->_auth->create($akunArray);
                        if (!$this->_auth->affected()) {
                            Session::put('notifikasi', array(
                                'pesan' => 'Failed create akun By Kontak',
                                'state' => 'warning'
                            ));
                            Redirect::to('admin/donatur');
                        }
                        $id_akun = $this->_auth->lastIID();
                    }
                    
                    // Send WA here
                    if (json_decode(Fonnte::check($akunArray['kontak']))->status != true) {
                        Session::put('notifikasi', array(
                            'pesan' => 'Failed to send WA notification, kontak WA tidak terdaftar',
                            'state' => 'warning'
                        ));
                    } else {
                        $text_pesan = "Hi *{$dataDonatur->nama}*,
Terimakasih telah menghubungi kami.
Berikut ini adalah data akun yang telah dihubungkan dengan donasimu
`Username: {$akunArray['username']}`
`Password: {$dataDonatur->kontak}`
Mohon klik link berikut untuk menyelesaikan proses aktivasi akun
```".Config::getHTTPHost() ."/auth/signup/hook/". $params[0]. DS . $id_akun . DS . "kontak" . DS . $dataDonatur->kontak . DS . $salt ."```";
                        $waResponse = Fonnte::send(Sanitize::toInt2($akunArray['kontak']), $text_pesan);
                    }
                } else {
                    Session::put('notifikasi', array(
                        'pesan' => 'Donatur <b>' . $dataDonatur->nama . '</b> belum terdata alamat emailnya maupun kontak Whatsapp sehingga gagal dikaitkan',
                        'state' => 'warning'
                    ));
                }
            } else {
                Session::put('notifikasi', array(
                    'pesan' => 'Akun donatur <b>' . $dataDonatur->nama . '</b> tidak ditemukan sehingga gagal dikaitkan',
                    'state' => 'danger'
                ));
            }
        }
        Redirect::to('admin/donatur');
    }
}