<?php
class BantuanController extends Controller {

    private $_bantuan,
            $_donatur;

    public function __construct() {
        $this->rel_controller = array(
            array(
                'href' => ASSET_PATH . basename(dirname(__FILE__)) . DS . 'core' . DS . 'css' . DS . 'admin-style.css'
            )
        );

        $this->title = 'Bantuan';
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
        
        $this->_bantuan = $this->model('Bantuan');
        $this->_bantuan->setOffset($this->getPageRecordLimit());
    }

    public function index() {
        $this->script_action = array(
            array(
                'src' => '/assets/pojok-berbagi-script.js'
            ),
            array(
                'src' => '/assets/route/admin/core/js/admin-script.js'
            )
        );

        $this->model('Sys');
        $this->data['info-card'] = array(
            'jumlah_bantuan' => $this->model->jumlahBantuan(),
            'jumlah_bantuan_menunggu' => $this->model->jumlahBantuanMenunggu(),
            'jumlah_bantuan_aktif' => $this->model->jumlahBantuanAktif(),
            'jumlah_bantuan_selesai' => $this->model->jumlahBantuanSelesai()
        );

        $this->data['bantuan'] = $this->_bantuan->dataBantuan();
        $this->data['halaman'] = 1;
        $this->data['record'] = $this->_bantuan->affected();
    }

    public function halaman($params = array()) {
        if (count($params)) {
            $dataBantuan = $this->_bantuan->dataHalaman($params[0]);
            if ($dataBantuan) {
                $this->data['record'] = $this->_bantuan->affected();
                $this->data['halaman'] = $params[0];
                $this->data['bantuan'] = $dataBantuan;

                return VIEW_PATH.'admin'.DS.'bantuan'.DS.'index.html';
            }
        }
        Redirect::to('admin/bantuan');
    }

    public function blok($params = array()) {
        if (count($params)) {
            $data = $this->_bantuan->getData('blokir, id_bantuan','bantuan',array('id_bantuan','=',$params[0]));
            if (is_null($data->blokir)) {
                $setBlokir = 1;
                $mode = 'blokir';
            } else {
                $setBlokir = NULL;
                $mode = 'unblokir';
            }

            $this->_bantuan->update('bantuan',array('blokir'=> $setBlokir), array('id_bantuan','=',$params[0]));
            if ($this->_bantuan->affected()) {
                Session::flash('success','Berhasil ' . $mode . ' bantuan [' . $data->id_bantuan . ']');
            } else {
                Session::flash('error','Tidak bisa blok saat ini');
            }
        }
        Redirect::to('admin/bantuan');
    }

    public function formulir($params = null) {
        $dataJenis = $this->_bantuan->dataJenis();
        $this->data['jenis_bantuan'] = $dataJenis;

        $this->rel_action = array(
            array(
                'href' => VENDOR_PATH.'cropper'.DS.'dist'.DS.'cropper.min.css'
            ),
            array(
                'href' => ASSET_PATH.'route'.DS.basename(dirname(__FILE__)).DS.'pages'.DS.'css'.DS.'formulir.css'
            )
        );

        $this->script_action = array(
            array(
				'type' => 'text/javascript',
                'src' => VENDOR_PATH.'bootstrap-datepicker'. DS .'dist'. DS .'js'. DS .'bootstrap-datepicker.min.js'
			),
            array(
				'type' => 'text/javascript',
                'charset' => 'UTF-8',
                'src' => VENDOR_PATH.'bootstrap-datepicker'. DS .'dist'. DS .'locales'. DS .'bootstrap-datepicker.id.min.js'
			),
            array(
				'type' => 'text/javascript',
                'src' => VENDOR_PATH.'cropper'. DS .'dist'. DS .'cropper.min.js'
			),
            array(
				'type' => 'text/javascript',
                'src' => ASSET_PATH.'route'.DS.basename(dirname(__FILE__)).DS.'pages'.DS.'js'.DS.'formulir.js'
			)
        );

        $this->data['token'] = Token::generate();

        $this->data['bantuan_berjalan'] = $this->_bantuan->dataBantuanBerjalan();
        

        if (count($params) > 0) {
            $this->formUpdate($params[0]);
            $min_jumlah_target = null;
            $this->_bantuan->getData('SUM(jumlah_pelaksanaan) min_jumlah_pelaksanaan','pelaksanaan JOIN donasi USING(id_pelaksanaan)',array('id_bantuan','=',Sanitize::escape($params[0])));
            if ($this->_bantuan->affected()) {
                $min_jumlah_target = $this->_bantuan->data()->min_jumlah_pelaksanaan;    
                $this->data['min_jumlah_target'] = $min_jumlah_target;
            }
            return VIEW_PATH.'admin'.DS.'bantuan'.DS.'form-update.html';
        }
    }

    public function tambah() {
        if (Input::exists()) {
			if (Token::check(Input::get('token'))) {
				$vali = new Validate();
                $validate = $vali->check($_POST, array(
                    'nama' => array(
                        'required' => true,
                        'min' => 5,
                        'max' => 30,
                        'unique' => 'bantuan'
                    ),
                    'id_jenis' => array(
                        'required' => true
                    ),
                    'jumlah_target' => array(
                        'digit' => true
                    ),
                    'satuan_target' => array(
                        'min' => 1
                    ),
                    'total_rab' => array(
                        'digit' => true
                    ),
                    'min_donasi' => array(
                        'digit' => true
                    ),
                    'lama_penayangan' => array(
                        'digit' => true,
                        'max' => 180
                    ),
                    'deskripsi' => array(
                        'required' => true,
                        'min' => 50,
                        'max' => 255
                    )
                    // ,
                    // 'file_gambar' => array(
                    //     'required' => true,
                    //     'file' => array('.png','.jpg','.jpeg')
                    // )
                ));
                if (!$validate->passed()) {
                    Session::put('error_feedback', $validate->getValueFeedback());
                    Redirect::to('admin/bantuan/formulir' . (strlen($validate->getReturnError()) ? '#' . $validate->getReturnError() : ''));
                } else {
                    $this->_bantuan->create('bantuan', array(
                        'nama' => ucwords(Sanitize::escape(trim(Input::get('nama')))),
                        'id_jenis' => ucwords(Sanitize::escape(trim(Input::get('id_jenis')))),
                        'jumlah_target' => Sanitize::toInt(Sanitize::escape(trim(Input::get('jumlah_target')))),
                        'satuan_target' => ucwords(Sanitize::escape(trim(Input::get('satuan_target')))),
                        'total_rab' => Sanitize::toInt(Sanitize::escape(trim(Input::get('total_rab')))),
                        'lama_penayangan' => Sanitize::toInt(Sanitize::escape(trim(Input::get('lama_penayangan')))),
                        'deskripsi' => ucfirst(Sanitize::escape(trim(Input::get('deskripsi')))),
                    ));
                    if ($this->_bantuan->affected()) {
                        Session::flash('success', 'Berhasil menambahkan bantuan baru');
                    } else {
                        Session::flash('error', 'Gagal menambahkan bantuan baru');
                    }
                    Redirect::to('admin/bantuan');
                }
            }
        }
    }

    public function berjalan($params = array()) {
        if (count($params) > 1) {
            if ($params[0] == 'kategori') {
                $params[1] = str_replace("-", " ", $params[1]);
                $this->data['halaman'] = 1;
                if (isset($params[2]) && ctype_digit($params[2])) {
                    $this->_bantuan->setHalaman($params[2]);
                    $this->data['halaman'] = $params[2];
                    // return VIEW_PATH.'admin'.DS.'bantuan'.DS.'index.html';
                }
                $dataBantuanKategori = $this->_bantuan->dataBantuanKategori($params[1]);
                $this->data['record'] = $this->_bantuan->affected();
                $this->data['data_bantuan_kategori'] = $dataBantuanKategori;
            }
        } else {
            Redirect::to('admin/bantuan');
        }
    }

    public function data($params) {
        if (!$params) {
            Redirect::to('admin/bantuan');
        }
        $this->_bantuan->getDetilBantuan($params[0]);
        if (!$this->_bantuan->data()->id_bantuan) {
            Session::flash('error','Data bantuan dengan ID ['. $params[0] .'] tidak ditemukan');
            Redirect::to('admin/bantuan'); 
        }
        $this->data['detil_bantuan'] = $this->_bantuan->data();

        $this->_bantuan->setStatus(1);
        $dataDonatur = $this->_bantuan->dataDonasiDonaturBantuan($params[0]);
        $this->data['donasi_bantuan'] = $dataDonatur;

        $this->data['halaman'] = 1;
        $this->data['record'] = $this->_bantuan->affected();

        $this->rel_action = array(
            array(
                'href' => VENDOR_PATH.'chart.js'.DS.'dist'.DS.'Chart.min.css'
            )
        );

        $this->script_action = array(
            array(
				'type' => 'text/javascript',
                'src' => VENDOR_PATH.'chart.js'. DS .'dist'. DS .'Chart.min.js'
			),
            array(
				'type' => 'text/javascript',
                'src' => ASSET_PATH.'admin'. DS .'pages'. DS. 'js' . DS .'bantuan-data.js'
			)
        );
    }

    public function formUpdate($id) {
        if (count($id[0])) {
            $hasil = $this->_bantuan->getData('gambar.path_gambar, gambar.nama, bantuan.*, jenis.nama nama_jenis, jenis.layanan','gambar RIGHT JOIN bantuan USING(id_gambar) JOIN jenis USING(id_jenis)', array('id_bantuan','=',$id[0]));
            if ($this->_bantuan->affected()) {
                $this->data['bantuan'] = $hasil;
            }
        }
    }

    public function update($params) {
        if (count($params[0])) {
            if (Input::exists()) {
                if (Token::check(Input::get('token'))) {
                    $min_jumlah_target = null;
                    $this->_bantuan->getData('SUM(jumlah_pelaksanaan) min_jumlah_pelaksanaan','pelaksanaan JOIN donasi USING(id_pelaksanaan)',array('id_bantuan','=',Sanitize::escape($params[0])));
                    if ($this->_bantuan->affected()) {
                        $min_jumlah_target = $this->_bantuan->data()->min_jumlah_pelaksanaan;
                    }
                    $vali = new Validate();
                    $validate = $vali->check($_POST, array(
                        'nama' => array(
                            'required' => true,
                            'min' => 5,
                            'max' => 30,
                            'unique' => 'bantuan'
                        ),
                        'id_jenis' => array(
                            'required' => true
                        ),
                        'jumlah_target' => array(
                            'digit' => true,
                            'min_value' => $min_jumlah_target
                        ),
                        'satuan_target' => array(
                            'min' => 1
                        ),
                        'total_rab' => array(
                            'digit' => true
                        ),
                        'min_donasi' => array(
                            'digit' => true
                        ),
                        'lama_penayangan' => array(
                            'digit' => true,
                            'max' => 180
                        ),
                        // 'tanggal_awal' => array(
                        //     'required' => true
                        // ),
                        // 'tanggal_akhir' => array(
                        //     'min' => 1
                        // ),
                        'deskripsi' => array(
                            'required' => true,
                            'min' => 50,
                            'max' => 255
                        )
                        // ,
                        // 'file_gambar' => array(
                        //     'required' => true
                        // )
                    ), array('id_bantuan','!=', Input::get('id_bantuan')));
                    if (!$validate->passed()) {
                        Session::put('error_feedback', $validate->getValueFeedback());
                        Redirect::to('admin/bantuan/formulir/'.$params[0] . (strlen($validate->getReturnError()) ? '#' . $validate->getReturnError() : ''));
                        
                    } else {
                        $jumlah_target = Sanitize::toInt(Input::get('jumlah_target'));
                        $min_jumlah_target = null;
                        $this->_bantuan->getData('SUM(jumlah_pelaksanaan) min_jumlah_pelaksanaan','pelaksanaan JOIN donasi USING(id_pelaksanaan)',array('id_bantuan','=',Sanitize::escape($params[0])));
                        if ($this->_bantuan->affected()) {
                            $min_jumlah_target = $this->_bantuan->data()->min_jumlah_pelaksanaan;
                        }
                        if (!is_null($min_jumlah_target)) {
                            $jumlah_target = ($jumlah_target < $min_jumlah_target ? $min_jumlah_target : Sanitize::toInt(Input::get('jumlah_target')));
                        }
                        if ($jumlah_target == 0) {
                            $jumlah_target = '';
                        }
                        $result = $this->_bantuan->update('bantuan', array(
                                'nama' => Input::get('nama'),
                                'id_jenis' => Input::get('id_jenis'),
                                'jumlah_target' => $jumlah_target,
                                'satuan_target' => Input::get('satuan_target'),
                                'total_rab' => Sanitize::toInt(Input::get('total_rab')),
                                'min_donasi' => Sanitize::toInt(Input::get('min_donasi')),
                                'lama_penayangan' => Sanitize::toInt(Input::get('lama_penayangan')),
                                'deskripsi' => Input::get('deskripsi')
                            ), array('id_bantuan','=', Input::get('id_bantuan'))
                        );
                        if ($result) {
                            Session::flash('success', 'Data Bantuan Berhasil Diupdate');
                        } else {
                            Session::flash('error', 'Data Bantuan Gagal Diupdate');
                        }
                    }
                }
            }
            Redirect::to('admin/bantuan/formulir/' . $params[0]);
        } else {
            Redirect::to('admin/bantuan');
        }
    }
}