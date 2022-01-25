<?php 
class DonaturController extends Controller {
    private $_donatur;

    public function __construct() {
        $this->rel_controller = array(
            array(
                'href' => ASSET_PATH . basename(dirname(__FILE__)) . DS . 'core' . DS . 'css' . DS . 'admin-style.css'
            )
        );

        $this->title = 'Donatur';
        $auth = $this->model('Auth');
        if (!$auth->hasPermission('admin')) {
            Redirect::to('home');
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
        switch (strtoupper($this->data['admin_alias'])) {
            case 'SYS':
                $this->sys();
                return VIEW_PATH.'admin'.DS.'donatur'.DS.'sys.html';
            break;

            case 'CRE':
                $this->cr();
            break;
            
            default:
                # code...
            break;
        }
    }

    public function cr() {
        $this->title = 'Donatur By CR';
    }

    public function sys() {
        $this->title = 'Donatur By Sys';
        $this->_donatur->dataDonatur();
        $this->data['donatur'] = $this->_donatur->data();
        $this->_donatur->countData('donatur');
        $this->data['record'] = $this->_donatur->data()->jumlah_record;
        $this->data['halaman'] = 1;
        // $this->_donatur->setPageLink();
        // $this->data['pageLink'] = json_decode(json_encode($this->_donatur->data()), true);
    }

    public function halaman($params) {
        if (count($params)) {
            $this->_donatur->dataDonatur($params[0]);
            $this->data['donatur'] = $this->_donatur->data();
            $this->_donatur->countData('donatur');
            $this->data['record'] = $this->_donatur->data()->jumlah_record;
            $this->data['halaman'] = $params[0];
            // $this->_donatur->setPageLink();
            // $this->data['pageLink'] = json_decode(json_encode($this->_donatur->data()), true);
            return VIEW_PATH . 'admin' . DS . 'donatur' . DS . 'sys.html';
        }
        Redirect::to('admin/donatur');
    }

    public function formulir($params = null) {
        $this->data['token'] = Token::generate();
        if (count($params)) {
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
        if (count($params[0])) {
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
                            'required' => true,
                            'digit' => true,
                            'min' => 11,
                            'max' => 13,
                            'unique' => 'donatur'
                        ),
                        'email' => array(
                            'required' => true,
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
                            Session::flash('success', 'Data Bantuan Berhasil Diupdate');
                        } else {
                            Session::flash('error', 'Data Bantuan Gagal Diupdate');
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
                        'required' => true,
                        'digit' => true,
                        'min' => 11,
                        'max' => 13,
                        'unique' => 'donatur'
                    ),
                    'email' => array(
                        'required' => true,
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
        if (count($params[0])) {
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

    public function kaitkan($params) {
        if (!count($params)>1) {
            Redirect::to('admin/donatur');
        }
        if (Token::check($params[1])) {
            $this->_donatur->getData('email','donatur',array('id_donatur','=', intval($params[0])),'AND',array('id_akun','IS',NULL));
            if ($this->_donatur->affected()) {
                // Send Mail here
                $pengirim = "pojokberbagi.id";    
                $penerima = $this->_donatur->data()->email;    
                $subjek = "Mengkaitkan Akun Pojok Berbagi";    
                $pesan = "Klik <a href='www.pojokberbagi.id/auth/signup/hook/". $params[0]. "'>disini</a> untuk mengkaitkan akunmu.";   
                $headers = "Dari :" . $pengirim;    
                mail($penerima,$subjek,$pesan, $headers);
                Session::flash('success','Cek email ' . $this->_donatur->data()->email . 'untuk mengkaitkan akun baru');  
            } else {
                Session::flash('error','Akun donatur ' . $this->_donatur->data()->email . 'gagal dikaitkan');
            }
        }
        Redirect::to('admin/donatur');
    }
}