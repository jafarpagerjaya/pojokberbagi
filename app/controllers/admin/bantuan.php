<?php
class BantuanController extends Controller {

    private $_bantuan,
            $_donatur;

    public function __construct() {
        $this->rel_controller = array(
            array(
                'href' => '/assets/route/admin/core/css/admin-style.css'
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
        $this->_bantuan->setDataLimit($this->getPageRecordLimit());
        $this->data['limit'] = $this->getPageRecordLimit();
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

        $this->data['halaman'] = 1;
        // Old
        // $this->data['bantuan'] = $this->_bantuan->dataBantuan();
        
        // New Via Offset
        // $this->_bantuan->setDataOffset(0);
        // Limit Wajib Di set Jika tidak ingin mengikuti nilai limit di controller
        // $this->_bantuan->setDataLimit(2);

        // $this->_bantuan->newDataOffset();

        // New Via Seek
        // set Direction dan Limit sebelum Betweem
        // Limit Wajib Di set Jika tidak ingin mengikuti nilai limit di construct
        // $this->_bantuan->setDataLimit(2);

        $this->data['limit'] = $this->_bantuan->getDataLimit();
        // $this->_bantuan->setDirection('ASC');
        $this->_bantuan->setDataBetween($this->data['halaman']);
        $this->_bantuan->newDataSeek();
        $this->data['bantuan'] = $this->_bantuan->data();
        
        if ($this->_bantuan->countData('bantuan') != false) {
            $this->data['record'] = $this->_bantuan->countData('bantuan')->jumlah_record;
        } else {
            $this->data['record'] = 0;
        }

        
    }

    public function halaman($params = array()) {
        if (count($params)) {
            $this->model('Sys');
            $this->data['info-card'] = array(
                'jumlah_bantuan' => $this->model->jumlahBantuan(),
                'jumlah_bantuan_menunggu' => $this->model->jumlahBantuanMenunggu(),
                'jumlah_bantuan_aktif' => $this->model->jumlahBantuanAktif(),
                'jumlah_bantuan_selesai' => $this->model->jumlahBantuanSelesai()
            );

            $this->script_action = array(
                array(
                    'src' => '/assets/pojok-berbagi-script.js'
                ),
                array(
                    'type' => 'text/javascript',
                    'src' => '/assets/route/admin/core/js/admin-script.js'
                )
            );
            // Old
            // $dataBantuan = $this->_bantuan->dataHalaman($params[0]);

            // New Via Offset Set Limit dulu Khawatir Offset ambil default = 10
            // $this->_bantuan->setDataLimit(1);

            // $this->_bantuan->setDataOffsetHalaman($params[0]);
            // $this->_bantuan->newDataOffset();

            // New Via Seek
            // Limit Wajib Di set Jika tidak ingin mengikuti nilai limit di construct
            // $this->_bantuan->setDataLimit(2);

            $this->data['limit'] = $this->_bantuan->getDataLimit();

            // Khusus seek method jika data yang ingin ditampilkan secara DESC maka wajib di set order_directionnya
            $this->_bantuan->setDirection('DESC');


            $this->_bantuan->setDataBetween($params[0]);
            $this->_bantuan->newDataSeek();
            $dataBantuan = $this->_bantuan->data();
            if ($dataBantuan) {
                if ($this->_bantuan->countData('bantuan') != false) {
                    $this->data['record'] = $this->_bantuan->countData('bantuan')->jumlah_record;
                } else {
                    $this->data['record'] = NULL;
                }
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
        // $dataJenis = $this->_bantuan->dataJenis();
        // $this->data['jenis_bantuan'] = $dataJenis;

        $dataKategori = $this->_bantuan->dataKategori();
        $this->data['kategori_bantuan'] = $dataKategori;
        $dataSektor = $this->_bantuan->dataSektor();
        $this->data['sektor_bantuan'] = $dataSektor;

        $this->rel_action = array(
            array(
                'href' => '/assets/main/css/utility.css'
            ),
            array(
                'href' => '/assets/main/css/inputGroup.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/form-element.css'
            ),
            array(
                'href' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
            ),
            array(
                'href' => VENDOR_PATH.'cropper'.DS.'dist'.DS.'cropper.min.css'
            ),
            array(
                'href' => '/assets/route/admin/pages/css/formulir.css'
            )
        );

        $this->script_action = array(
            // array(
			// 	'type' => 'text/javascript',
            //     'src' => VENDOR_PATH.'bootstrap-datepicker'. DS .'dist'. DS .'js'. DS .'bootstrap-datepicker.min.js'
			// ),
            // array(
			// 	'type' => 'text/javascript',
            //     'charset' => 'UTF-8',
            //     'src' => VENDOR_PATH.'bootstrap-datepicker'. DS .'dist'. DS .'locales'. DS .'bootstrap-datepicker.id.min.js'
			// ),
            array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/core/js/form-function.js'
			),
            array(
				'type' => 'text/javascript',
                'src' => '/assets/main/js/token.js'
			),
            array(
				'type' => 'text/javascript',
                'src' => VENDOR_PATH.'cropper'. DS .'dist'. DS .'cropper.min.js'
			),
            array(
                'source' => 'trushworty',
                'src' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'
            ),
            array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/pages/js/formulir.js'
			)
        );

        $this->data['bantuan_berjalan'] = $this->_bantuan->dataBantuanBerjalan();

        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();

        if (count($params) > 0) {
            $this->formUpdate($params[0]);
            $min_jumlah_target = null;
            $this->_bantuan->getData('SUM(jumlah_pelaksanaan) min_jumlah_pelaksanaan','pelaksanaan JOIN donasi USING(id_pelaksanaan)',array('id_bantuan','=',Sanitize::escape($params[0])));
            if ($this->_bantuan->affected()) {
                $min_jumlah_target = $this->_bantuan->data()->min_jumlah_pelaksanaan;    
                $this->data['min_jumlah_target'] = $min_jumlah_target;
            }
            array_push($this->script_action, array(
                'src' => '/assets/route/admin/pages/js/form-update.js'
            ));
            return VIEW_PATH.'admin'.DS.'bantuan'.DS.'form-update.html';
        }
    }

    // public function tambah() {
        // if (Input::exists()) {
		// 	if (Token::check(Input::get('token'))) {
		// 		$vali = new Validate();
        //         $validate = $vali->check($_POST, array(
        //             'nama' => array(
        //                 'required' => true,
        //                 'min' => 5,
        //                 'max' => 30,
        //                 'unique' => 'bantuan'
        //             ),
        //             'penerima_bantuan' => array(
        //                 'required' => true,
        //                 'min' => 5,
        //                 'max' => 50
        //             ),
        //             'id_kategori' => array(
        //                 'required' => true
        //             ),
        //             'id_sektor' => array(
        //                 'required' => true
        //             ),
        //             'deskripsi' => array(
        //                 'required' => true,
        //                 'min' => 50,
        //                 'max' => 255
        //             ),
        //             'jumlah_target' => array(
        //                 'digit' => true
        //             ),
        //             'satuan_target' => array(
        //                 'min' => 1
        //             ),
        //             'min_donasi' => array(
        //                 'digit' => true
        //             ),
        //             'lama_penayangan' => array(
        //                 'digit' => true,
        //                 'max' => 180
        //             ),
        //             'total_rab' => array(
        //                 'digit' => true
        //             )
        //             // ,
        //             // 'file_gambar' => array(
        //             //     'required' => true,
        //             //     'file' => array('.png','.jpg','.jpeg')
        //             // )
        //         ));
        //         if (!$validate->passed()) {
        //             Session::put('error_feedback', $validate->getValueFeedback());
        //             Redirect::to('admin/bantuan/formulir' . (strlen($validate->getReturnError()) ? '#' . $validate->getReturnError() : ''));
        //         } else {
        //             $this->_bantuan->create('bantuan', array(
        //                 'nama' => ucwords(Sanitize::escape(trim(Input::get('nama')))),
        //                 'id_jenis' => ucwords(Sanitize::escape(trim(Input::get('id_jenis')))),
        //                 'jumlah_target' => Sanitize::toInt(Sanitize::escape(trim(Input::get('jumlah_target')))),
        //                 'satuan_target' => ucwords(Sanitize::escape(trim(Input::get('satuan_target')))),
        //                 'total_rab' => Sanitize::toInt(Sanitize::escape(trim(Input::get('total_rab')))),
        //                 'lama_penayangan' => Sanitize::toInt(Sanitize::escape(trim(Input::get('lama_penayangan')))),
        //                 'deskripsi' => ucfirst(Sanitize::escape(trim(Input::get('deskripsi')))),
        //             ));
        //             if ($this->_bantuan->affected()) {
        //                 Session::flash('success', 'Berhasil menambahkan bantuan baru');
        //             } else {
        //                 Session::flash('error', 'Gagal menambahkan bantuan baru');
        //             }
        //             Redirect::to('admin/bantuan');
        //         }
        //     }
        // }
        // Debug::pr($_POST);
        // return false;
    // }

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
        $now = new DateTime(date('Y-m-d H:i:s'));
        $action = new DateTime($this->data['detil_bantuan']->action_at);
        $msInterval = $action->diff($now);
        $this->data['detil_bantuan']->telah_dikelola_selama = $msInterval->days;

        $dataSaldo = $this->_bantuan->getSaldoBantuan($params[0]);
        $this->data['saldo_bantuan'] = $dataSaldo;

        $this->_bantuan->setDataLimit(1);
        $this->_bantuan->setStatus(1);
        // $this->_bantuan->setDirection('DESC');
        // $this->_bantuan->setDataOffsetHalaman(1);

        $dataDonatur = $this->_bantuan->dataDonasiDonaturBantuan($params[0]);
        $this->data['donasi_bantuan'] = $dataDonatur;
        $this->data['halaman'] = 1;
        $this->data['record'] = $this->_bantuan->countDonasiBantuan($params[0]);
        $this->data['pages'] = ceil($this->data['record']->jumlah_record / $this->_bantuan->getDataLimit());
        $this->data['limit'] = $this->_bantuan->getDataLimit();

        $this->rel_action = array(
            array(
                'href' => VENDOR_PATH.'chart.js'.DS.'dist'.DS.'Chart.min.css'
            ),
            array(
                'href' => '/assets/main/css/pagination.css'
            ),
            array(
                'href' => '/assets/route/admin/pages/css/data.css'
            )
        );

        $this->script_action = array(
            array(
				'type' => 'text/javascript',
                'src' => VENDOR_PATH.'chart.js'. DS .'dist'. DS .'Chart.min.js'
			),
            array(
                'src' => '/assets/main/js/pagination.js'
            ),
            array(
                'src' => '/assets/main/js/token.js'
            ),
            array(
                'src' => '/assets/route/admin/core/js/form-function.js'
            ),
            array(
                'src' => '/assets/route/admin/pages/js/data.js'
			)
        );

        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();
    }

    public function formUpdate($id) {
        if (count($id[0])) {
            $hasil = $this->_bantuan->getData(
                'gm.nama nama_gambar_medium, gw.nama nama_gambar_wide, gm.path_gambar path_gambar_medium, gw.path_gambar path_gambar_wide, b.*', 
                'bantuan b LEFT JOIN gambar gm ON(b.id_gambar_medium = gm.id_gambar) LEFT JOIN gambar gw ON(b.id_gambar_wide = gw.id_gambar)', 
                array('b.id_bantuan', '=', Sanitize::escape2($id)),'AND',array('b.blokir','IS', NULL)
            );
            if (!$this->_bantuan->affected()) {
                Redirect::to('admin/bantuan/formulir');
            }
            $this->data['bantuan'] = $hasil;
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
                        // 'nama' => array(
        //                 'required' => true,
        //                 'min' => 5,
        //                 'max' => 30,
        //                 'unique' => 'bantuan'
        //             ),
        //             'penerima_bantuan' => array(
        //                 'required' => true,
        //                 'min' => 5,
        //                 'max' => 50
        //             ),
        //             'id_kategori' => array(
        //                 'required' => true
        //             ),
        //             'id_sektor' => array(
        //                 'required' => true
        //             ),
        //             'deskripsi' => array(
        //                 'required' => true,
        //                 'min' => 50,
        //                 'max' => 255
        //             ),
        //             'jumlah_target' => array(
        //                 'digit' => true
        //             ),
        //             'satuan_target' => array(
        //                 'min' => 1
        //             ),
        //             'min_donasi' => array(
        //                 'digit' => true
        //             ),
        //             'lama_penayangan' => array(
        //                 'digit' => true,
        //                 'max' => 180
        //             ),
        //             'total_rab' => array(
        //                 'digit' => true
        //             )
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