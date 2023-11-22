<?php
class BantuanController extends Controller {

    private $_bantuan,
            $_donatur;

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

        $this->title = 'Bantuan';
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
        
        $this->_bantuan = $this->model('Bantuan');
        $this->_bantuan->setLimit(10);
        $this->data['limit'] = $this->getPageRecordLimit();
    }

    public function index() {
        $this->script_action = array(
            array(
                'src' => '/assets/main/js/token.js'
            ),
            array(
                'src' => '/assets/main/js/token-action-updater.js'
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
        
        // New Via Offset
        // $this->_bantuan->setOffset(0);
        // Limit Wajib Di set Jika tidak ingin mengikuti nilai limit di controller
        // $this->_bantuan->setLimit(2);

        // $this->_bantuan->newDataOffset();

        // New Via Seek
        // set Direction dan Limit sebelum Betweem
        // Limit Wajib Di set Jika tidak ingin mengikuti nilai limit di construct
        // $this->_bantuan->setLimit(2);

        $this->data['limit'] = $this->_bantuan->getLimit();
        // $this->_bantuan->setDirection('ASC');
        $this->_bantuan->setDataBetween($this->data['halaman']);
        $this->_bantuan->newDataSeek();
        $this->data['bantuan'] = $this->_bantuan->data();
        
        if ($this->_bantuan->countData('bantuan') != false) {
            $this->data['record'] = $this->_bantuan->countData('bantuan')->jumlah_record;
        } else {
            $this->data['record'] = 0;
        }

        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();

        Cookie::delete('deskripsi-selengkapnya', '/admin/bantuan/selengkapnya');
    }

    public function halaman($params = array()) {
        if (count(is_countable($params) ? $params : [])) {
            $this->model('Sys');
            $this->data['info-card'] = array(
                'jumlah_bantuan' => $this->model->jumlahBantuan(),
                'jumlah_bantuan_menunggu' => $this->model->jumlahBantuanMenunggu(),
                'jumlah_bantuan_aktif' => $this->model->jumlahBantuanAktif(),
                'jumlah_bantuan_selesai' => $this->model->jumlahBantuanSelesai()
            );

            $this->script_action = array(
                array(
                    'src' => '/assets/main/js/token.js'
                )
            );

            // New Via Offset Set Limit dulu Khawatir Offset ambil default = 10
            // $this->_bantuan->setLimit(1);

            // $this->_bantuan->setOffsetByHalaman($params[0]);
            // $this->_bantuan->newDataOffset();

            // New Via Seek
            // Limit Wajib Di set Jika tidak ingin mengikuti nilai limit di construct
            // $this->_bantuan->setLimit(2);

            $this->data['limit'] = $this->_bantuan->getLimit();

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

                // Token for fetch
                $this->data[Config::get('session/token_name')] = Token::generate();
                return VIEW_PATH.'admin'.DS.'bantuan'.DS.'index.html';
            }
        }
        Redirect::to('admin/bantuan');
    }

    public function blok($params = array()) {
        if (count(is_countable($params) ? $params : [])) {
            $data = $this->_bantuan->getData('blokir, id_bantuan','bantuan',array('id_bantuan','=',$params[0]));
            $data = $this->_bantuan->getResult();
            if (is_null($data->blokir)) {
                $setBlokir = 1;
                $mode = 'blokir';
            } else {
                $setBlokir = NULL;
                $mode = 'unblokir';
            }

            $this->_bantuan->update('bantuan',array('blokir'=> $setBlokir), array('id_bantuan','=',$params[0]));
            if ($this->_bantuan->affected()) {
                Session::flash('success','Berhasil ' . $mode . ' bantuan <b>#' . $params[0] . '</b>');
            } else {
                Session::flash('error','Tidak bisa blok saat ini');
            }
        }
        Redirect::to('admin/bantuan');
    }

    public function formulir($params = null) {
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

        $this->data['bantuan_diajukan'] = $this->_bantuan->getJumlahDataBantuan();
        $this->data['bantuan_berjalan'] = $this->_bantuan->getJumlahDataBantuan('D');
        $this->data['bantuan_selesai'] = $this->_bantuan->getJumlahDataBantuan('S');

        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();

        if (count(is_countable($params) ? $params : []) > 0) {
            $this->formUpdate($params[0]);
            $min_jumlah_target = null;
            $this->_bantuan->getData('SUM(p.jumlah_pelaksanaan) min_jumlah_pelaksanaan','pelaksanaan p JOIN rencana r USING(id_rencana)',array('r.id_bantuan','=',Sanitize::escape($params[0])));

            if ($this->_bantuan->affected()) {
                $min_jumlah_target = $this->_bantuan->getResult()->min_jumlah_pelaksanaan;    
                $this->data['min_jumlah_target'] = $min_jumlah_target;
            }

            array_push($this->script_action, array(
                'src' => '/assets/route/admin/pages/js/form-update.js'
            ));
            
            return VIEW_PATH.'admin'.DS.'bantuan'.DS.'form-update.html';
        }
    }

    public function selengkapnya($params = array()) {
        $this->rel_action = array(
            array(
                'href' => '/assets/main/css/utility.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/form-element.css'
            ),
            // array(
            //     'href' => VENDOR_PATH.'cropper'.DS.'dist'.DS.'cropper.min.css'
            // ),
            array(
                'href' => '/assets/route/admin/core/css/form-element.css'
            ),
            array(
                'href' => 'https://cdn.quilljs.com/1.3.7/quill.snow.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/editor.css'
            ),
            array(
                'href' => '/assets/main/css/pagination.css'
            ),
            array(
                'href' => '/assets/route/admin/pages/css/selengkapnya.css'
            )
        );

        $this->script_action = array(
            array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/core/js/form-function.js'
			),
            array(
				'type' => 'text/javascript',
                'src' => '/assets/main/js/token.js'
			),
            // array(
			// 	'type' => 'text/javascript',
            //     'src' => VENDOR_PATH.'cropper'. DS .'dist'. DS .'cropper.min.js'
			// ),
            array(
                'type' => 'text/javascript',
                'src' => 'https://cdn.quilljs.com/1.3.7/quill.js',
                'source' => 'trushworty'
            ),
            array(
                'type' => 'text/javascript',
                'src' => '/assets/main/js/function-libs.js'
            ),
            array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/core/js/editor.js'
			),
            array(
				'type' => 'text/javascript',
                'src' => '/vendors/quill/js/image-drop.js'
            ),
            array(
				'type' => 'text/javascript',
                'src' => '/vendors/quill/js/image-resize.js'
            ),
            array(
                'src' => '/assets/main/js/pagination.js'
            ),
            array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/pages/js/selengkapnya.js'
			)
        );
        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();

        if (Cookie::exists('deskripsi-selengkapnya')) {
            $cookieValue = json_decode(base64_decode(Cookie::get('deskripsi-selengkapnya')));
            $this->_bantuan->setLimit($cookieValue->limit);
            if (isset($cookieValue->search)) {
                $this->_bantuan->setSearch($cookieValue->search);
            }
            $this->_bantuan->setHalaman($cookieValue->active_page, 'deskripsi');
            $active_page = $cookieValue->active_page;
        } else {
            $this->_bantuan->setLimit(8);
            $this->_bantuan->setHalaman(1, 'deskripsi');
            $active_page = 1;
        }

        $this->_bantuan->setDirection('DESC');
        $this->_bantuan->setOrder('d.create_at');

        $data = array();

        $this->_bantuan->readDeskripsiList();
        
        if ($this->_bantuan->affected()) {
            $data = $this->_bantuan->data();
        }

        if (!isset($data['data'])) {
            $data['data'] = array();
        }

        if (!isset($data['total_record'])) {
            $data['total_record'] = $this->_bantuan->data()['total_record'];
        }

        $data['pages'] = ceil($data['total_record']/$this->_bantuan->getLimit());
        $data['limit'] = $this->_bantuan->getLimit();
        if ($this->_bantuan->getSearch() !== null) {
            $data['search'] = $this->_bantuan->getSearch();
        }

        $this->data['deskripsi'] = $data;

        if ($data['pages'] > 0) {
            $cookieValue = array(
                'active_page' => Sanitize::toInt2($active_page),
                'pages' => $data['pages'], 
                'limit' => $data['limit']
            );
            if (isset($data['search'])) {
                $cookieValue = array_merge($cookieValue, array('search' => $data['search']));
            }
            Cookie::put('deskripsi-selengkapnya', base64_encode(json_encode($cookieValue)), 3600, DS . $this->getRealUri());
        } else {
            if (Cookie::exists('deskripsi-selengkapnya')) {
                Cookie::delete('deskripsi-selengkapnya');
            }
        }
    }

    public function kategori($params = array()) {
        $kategori = null;
        if (count(is_countable($params) ? $params : []) > 0) {
            $params = Sanitize::thisArray($params);
            $kategori = strtolower(str_replace("-", " ", $params[0]));
            $halaman = 1;
            if (isset($params[1]) && ctype_digit($params[1])) {
                $halaman = $params[1];
                $this->_bantuan->setHalaman(Sanitize::escape2($halaman), 'bantuan');
            }
            $this->data['halaman'] = $halaman;
            // $this->_bantuan->setSearch('Qr');
            $this->_bantuan->setOrder(1);
            $this->_bantuan->setDirection('ASC');
            $this->_bantuan->setOffsetByHalaman($halaman);
            $this->_bantuan->setLimit(10);
            $this->_bantuan->readDataBantuanKategori($kategori);
            $this->data['kategori'] = ucwords($kategori);
            
            if (empty($this->_bantuan->data()['data'])) {
                $halaman = $this->data['halaman'] - 1;
                if ($halaman < 1) {
                    $halaman = 1;
                }
                Redirect::to('admin/bantuan/kategori/' . $params[0] . '/' . $halaman);
            }

            $this->data['record'] = $this->_bantuan->data()['record'];
            $this->data['list_kategori'] = $this->_bantuan->data()['data'];

            // Token for fetch
            $this->data[Config::get('session/token_name')] = Token::generate();
            $this->script_action = array(
                array(
                    'src' => '/assets/main/js/token.js'
                )
            );
        } else {
            // Sementara nanti buat halaman kategori yang menampilkan seluruh info bantuan tentang kategori
            Redirect::to('admin/bantuan');
        }
    }

    public function berjalan($params = array()) {
        if (count(is_countable($params) ? $params : []) > 1) {
            if ($params[0] == 'kategori') {
                $kategori_param = $params[1];
                $params[1] = str_replace("-", " ", $params[1]);
                $this->data['halaman'] = 1;
                if (isset($params[2]) && ctype_digit($params[2])) {
                    $this->_bantuan->setHalaman(Sanitize::escape2($params[2]), 'bantuan');
                    $this->data['halaman'] = Sanitize::escape2($params[2]);
                    // return VIEW_PATH.'admin'.DS.'bantuan'.DS.'index.html';
                }
                $this->_bantuan->setLimit(10);
                $this->_bantuan->setOffsetByHalaman($this->data['halaman']);
                $dataBantuanKategori = $this->_bantuan->dataBantuanKategori(Sanitize::escape2($params[1]));

                if (empty($dataBantuanKategori)) {
                    $halaman = $this->data['halaman'] - 1;
                    if ($halaman < 1) {
                        $halaman = 1;
                    }
                    Redirect::to('admin/bantuan/berjalan/' . $params[0] . '/' . $kategori_param . '/' . $halaman);
                }
                if ($params[1] != 'tanpa kategori') {
                    $this->data['record'] = $this->_bantuan->countData("bantuan b LEFT JOIN kategori k USING(id_kategori)",array("k.nama = ? AND b.blokir IS NULL AND LOWER(b.status) = LOWER('D')",Sanitize::escape2($params[1])))->jumlah_record;
                } else if ($params[1] == 'tanpa kategori') {
                    $this->data['record'] = $this->_bantuan->countData("bantuan b LEFT JOIN kategori k USING(id_kategori)","k.nama IS NULL AND b.blokir IS NULL AND LOWER(b.status) = LOWER('D')")->jumlah_record;
                } else {
                    $this->data['record'] = 0;
                }
                $this->data['data_bantuan_kategori'] = $dataBantuanKategori;
                $this->data['limit'] = $this->_bantuan->getLimit();

                // Token for fetch
                $this->data[Config::get('session/token_name')] = Token::generate();
                $this->script_action = array(
                    array(
                        'src' => '/assets/main/js/token.js'
                    )
                );
            }
        } else {
            Redirect::to('admin/bantuan');
        }
    }

    public function selesai($params = array()) {
        if (count(is_countable($params) ? $params : []) > 1) {
            if ($params[0] == 'kategori') {
                $kategori_param = $params[1];
                $params[1] = str_replace("-", " ", $params[1]);
                $this->data['halaman'] = 1;
                if (isset($params[2]) && ctype_digit($params[2])) {
                    $this->_bantuan->setHalaman(Sanitize::escape2($params[2]), 'bantuan');
                    $this->data['halaman'] = Sanitize::escape2($params[2]);
                    // return VIEW_PATH.'admin'.DS.'bantuan'.DS.'index.html';
                }
                $this->_bantuan->setLimit(10);
                $this->_bantuan->setOffsetByHalaman($this->data['halaman']);
                $dataBantuanKategori = $this->_bantuan->dataBantuanKategori(Sanitize::escape2($params[1]), 'S');

                if (empty($dataBantuanKategori)) {
                    $halaman = $this->data['halaman'] - 1;
                    if ($halaman < 1) {
                        $halaman = 1;
                    }
                    Redirect::to('admin/bantuan/selesai/' . $params[0] . '/' . $kategori_param . '/' . $halaman);
                }
                $this->data['record'] = $this->_bantuan->affected();
                $this->data['data_bantuan_kategori'] = $dataBantuanKategori;
                // Token for fetch
                $this->data[Config::get('session/token_name')] = Token::generate();
                $this->script_action = array(
                    array(
                        'src' => '/assets/main/js/token.js'
                    )
                );
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
        $this->data['detil_bantuan']->telah_dikelola_selama = ($msInterval->days > 0 ? $msInterval->days . ' hari yang lalu' : 'baru saja hari ini');

        $dataSaldo = $this->_bantuan->getSaldoBantuan($params[0]);
        $this->data['saldo_bantuan'] = $dataSaldo;

        $this->_bantuan->setLimit(3);
        $this->_bantuan->setStatus(1);
        $this->_bantuan->setDirection('DESC');
        // $this->_bantuan->setOffsetByHalaman(1);

        $dataDonatur = $this->_bantuan->dataDonasiDonaturBantuan($params[0]);
        $this->data['donasi_bantuan'] = $dataDonatur;
        $this->data['halaman'] = 1;
        $this->data['record'] = $this->_bantuan->countDonasiBantuan($params[0]);
        $this->data['pages'] = ceil($this->data['record']->jumlah_record / $this->_bantuan->getLimit());
        $this->data['limit'] = $this->_bantuan->getLimit();

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
            // array(
			// 	'type' => 'text/javascript',
            //     'src' => VENDOR_PATH.'chart.js'. DS .'dist'. DS .'Chart.min.js'
			// ),
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

    public function tutup($params) {
        if (!$params) {
            Redirect::to('admin/bantuan');
        }

        if (!Token::check($params[1])) {
            Redirect::to('admin/bantuan');
        }

        $this->model('Bantuan');

        $data = $this->model->getData('nama nama_bantuan', 'bantuan', array('id_bantuan','=',Sanitize::escape2($params[0])));

        if ($data == false) {
            Session::put('notifikasi', array(
                'pesan' => 'Data bantuan tidak ditemukan',
                'state' => 'warning'
            ));
            Redirect::to('admin/bantuan');
        }

        $data = $this->model->getResult();

        $this->model->update('bantuan', array(
            'status' => 'S'
        ), array(
            'id_bantuan','=',Sanitize::toInt2($params[0])
        ));

        if ($this->model->affected()) {
            Session::put('notifikasi', array(
                'pesan' => 'Bantuan <b class="text-dark">'.$data->nama_bantuan.'</b> telah di <span class="font-weight-bolder text-orange">take down</span>',
                'state' => 'success',
                'id' => $params[0]
            ));
        } else {
            Session::put('notifikasi', array(
                'pesan' => 'Gagal take down bantuan',
                'state' => 'error'
            ));
        }

        Redirect::to('admin/bantuan');
    }

    public function formUpdate($id = null) {
        if (!is_null($id)) {
            $this->_bantuan->getData(
                'gm.nama nama_gambar_medium, gw.nama nama_gambar_wide, gm.path_gambar path_gambar_medium, gw.path_gambar path_gambar_wide, b.*', 
                'bantuan b LEFT JOIN gambar gm ON(b.id_gambar_medium = gm.id_gambar) LEFT JOIN gambar gw ON(b.id_gambar_wide = gw.id_gambar)', 
                array('b.id_bantuan', '=', Sanitize::escape2($id)),'AND',array('b.blokir','IS', NULL)
            );

            if (!$this->_bantuan->affected()) {
                Redirect::to('admin/bantuan/formulir');
            }

            $this->data['bantuan'] = $this->_bantuan->getResult();
        }
    }
}