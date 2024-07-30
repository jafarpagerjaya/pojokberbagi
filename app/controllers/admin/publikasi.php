<?php
class PublikasiController extends Controller {
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

        $this->title = 'Publikasi';
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
        
        $this->_publikasi = $this->model('Publikasi');
        $this->_publikasi->setLimit(6);
        $this->data['limit'] = $this->getPageRecordLimit();
    }

    public function index() {
        // sementara sebelum halaman publikasi ada
        Redirect::to('admin/publikasi/artikel');
    }

    public function artikel() {
        $this->title = 'Artikel';

        $this->rel_action = array(
            array(
                'href' => 'https://cdn.quilljs.com/1.3.6/quill.snow.css'
            ),
            array(
                'href' => '/assets/main/css/utility.css'
            ),
            array(
                'href' => '/assets/main/css/pagination.css'
            ),
            array(
                'href' => '/assets/main/css/inputGroup.css'
            ),
            array(
                'href' => '/assets/main/css/checkbox.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/editor.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/form-element.css'
            ),
            array(
                'href' => '/assets/route/admin/pages/css/artikel.css'
            )
        );

        $this->script_action = array(
            array(
				'type' => 'text/javascript',
                'src' => '/assets/main/js/token.js'
			),
            array(
                'src' => '/assets/main/js/pagination.js'
            ),
            array(
                'type' => 'text/javascript',
                'src' => 'https://cdn.quilljs.com/1.3.7/quill.js',
                'source' => 'trushworty'
            ),
            array(
                'type' => 'text/javascript',
                'src' => '/assets/main/js/utility.js'
            ),
            array(
                'type' => 'text/javascript',
                'src' => '/assets/main/js/function-libs.js'
            ),
            array(
                'type' => 'text/javascript',
                'src' => '/assets/main/js/checkbox.js'
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
				'type' => 'text/javascript',
                'src' => '/vendors/quill/js/image-compress.js'
            ),
            array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/core/js/form-function.js'
			),
            array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/pages/js/artikel.js'
			)
        );

        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();

        $this->_publikasi->setHalaman(1, 'artikel');
        $this->_publikasi->getsArtikelList();
        $this->data['artikel'] = $this->_publikasi->data();
    }

    public function fetch($params) {
        $fetch = new Fetch();

        switch ($params[0]) {
            case 'get':
                // get Params
            break;

            case 'create':
                // fetchCreate Params
            break;

            case 'gets':
                // fetchGets Params
            break;

            case 'update':
                // fetchUpdate Params
            break;

            case 'reset':
                // fetchReset Params
            break;
            
            default:
                $fetch->addResults(array(
                    'feedback' => array(
                        'message' => 'Unrecognize params '. $params[0]
                    )
                ));
                $fetch->result();
                return false;
            break;
        }

        if (isset($params[2])) {
            if ($params[2] == 'resume') {
                // fetchGetResumeArtikel
                $params[0] .= ucfirst(strtolower($params[2]));
            }

            if ($params[2] == 'pilihan') {
                // fetchGetsPilihanArtikel
                $params[0] .= ucfirst(strtolower($params[2]));
            }
        }

        if (isset($params[1])) {
            if ($params[1] == 'artikel') {
                // fetchGetArtikel, fetchCreateArtikel, fetchUpdateArtikel, fetchResetArtikel Params
                $params[0] .= ucfirst(strtolower($params[1]));
            }
        }

        $decoded = $fetch->getDecoded();

        // prepare method Token name
        $action = __FUNCTION__ . ucfirst($params[0]);
        // call method Token
        $this->$action($decoded, $fetch);
        
        return false;
    }

    private function fetchCreateArtikel($decoded, $fetch) {
        $decoded = $decoded['artikel'];
        $pegawai = $this->data['pegawai'];

        $decoded['judul'] = Sanitize::escape2($decoded['judul']);
        $this->_publikasi->countData('artikel', array('judul = ?',$decoded['judul']));
        if ($this->_publikasi->getResult()->jumlah_record == 1) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Nama judul sudah terpakai'
                )
            ));

            $fetch->result();
            return false;
        }
        
        $content = $decoded['isi'];
        $counterImg = 1;
        $array_id_gambar = array();
        $label = 'artikel';
        $array_video = array();

        foreach ($content['ops'] as $key => $value) {
            if (is_array($value)) {
                if (array_key_exists('insert', $value)) {
                    foreach($value as $keyInsert => $insert) {
                        if (is_array($insert)) {
                            if (array_key_exists('image', $insert)) {
                                
                                $dataUrl = array(
                                    $label => $insert['image']
                                );

                                $name_to_add = '-artikel-' . $counterImg;
                        
                                $uploaded = $fetch->uploadDataUrlIntoServer($dataUrl, 'publikasi', $name_to_add);
                                
                                $array_id_gambar[$counterImg] = array(
                                    'nama' => $fetch->path_gambar[$label]['name'],
                                    'path_gambar' => $fetch->path_gambar[$label]['path'],
                                    'label' => $label
                                );
                                
                                // reset data menjadi path gambar
                                $content['ops'][$key]['insert']['image'] = $fetch->path_gambar[$label]['path'];

                                $counterImg++;
                            } else if (array_key_exists('video', $insert)) {
                                if (str_contains($insert['video'], 'youtube.com')) {
                                    $insert['video'] = str_replace('youtube.com','youtube-nocookie.com', $insert['video']);
                                    $content['ops'][$key]['insert']['video'] = $insert['video'];
                                }
                                array_push($array_video, $insert['video']);
                            }
                        }
                    }
                }
            }
        }

        $fetch->addResults(array(
            'error' => true
        ));

        $decoded['isi'] = str_replace('\/','/', json_encode($content));

        $decoded['isi'] = Sanitize::escape2($decoded['isi']);

        try {
            $params = array(
                'isi' => $decoded['isi'],
                'judul' => $decoded['judul'],
                'id_author' => $pegawai->id_pegawai,
                'publish_at' => date('Y-m-d H-i-s')
            );

            $this->_publikasi->create('artikel', $params);
            
            $id_artikel = $this->_publikasi->lastIID();

            if (count(is_countable($array_id_gambar) ? $array_id_gambar : []) > 0) {
                
                $fetch->addResults(array(
                    'uploaded' => $uploaded
                ));

                try {
                    $this->_publikasi->createMultiple('gambar', $array_id_gambar);
                    
                    try {
                        $this->_publikasi->query("INSERT INTO list_gambar_artikel (id_artikel, id_gambar) SELECT ?, id_gambar FROM gambar WHERE create_at IN (SELECT create_at FROM gambar WHERE id_gambar = ? AND label = ?)", array(
                                'id_artikel' => $id_artikel,
                                'id_gambar' => $this->_publikasi->lastIID(),
                                'label' => $label
                            )
                        );
                    } catch (\Throwable $th) {
                        $fetch->addResults(array(
                            'feedback' => array(
                                'message' => 'Failed get all id_gambar after inserted'
                            )
                        ));
    
                        if (count(is_countable($array_id_gambar) ? $array_id_gambar : []) > 0) {
                            $fetch->path_gambar = Config::recursiveChangeKey($array_id_gambar, array(
                                'nama' => 'name',
                                'path_gambar' => 'path'
                            ));
                            $fetch->removePathGambar();
                            $fetch->path_gambar = array();
                        }
    
                        $fetch->result();
                        return false;
                    }
                } catch (\Throwable $th) {
                    $pesan = explode(':',$th->getMessage());
                    $fetch->addResults(array(
                        'feedback' => array(
                            'message' => '<b>'. current($pesan) .'</b> '. end($pesan) . ' [$fetch]'
                        )
                    ));
                    if (count(is_countable($array_id_gambar) ? $array_id_gambar : []) > 0) {
                        $fetch->path_gambar = Config::recursiveChangeKey($array_id_gambar, array(
                            'nama' => 'name',
                            'path_gambar' => 'path'
                        ));
                        $fetch->removePathGambar();
                        $fetch->path_gambar = array();
                    }
                    
                    $fetch->result();
                    return false;
                }   
            }
        } catch (\Throwable $th) {
            //throw $th;
            $pesan = explode(':',$th->getMessage());
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => '<b>'. current($pesan) .'</b> '. end($pesan) . ' [$fetch]'
                )
            ));
            if (count(is_countable($array_id_gambar) ? $array_id_gambar : []) > 0) {
                $fetch->path_gambar = Config::recursiveChangeKey($array_id_gambar, array(
                    'nama' => 'name',
                    'path_gambar' => 'path'
                ));
                $fetch->removePathGambar();
                $fetch->path_gambar = array();
            }
            
            $fetch->result();
            return false;
        }

        $this->_publikasi->query("SELECT g.path_gambar, g.nama, FormatTanggalFull(a.publish_at) publish_at, a.id_artikel, COUNT(ka.id_pengunjung) jumlah_pengunjung FROM artikel a LEFT JOIN admin adm ON(adm.id_pegawai = a.id_author) LEFT JOIN akun USING(id_akun) LEFT JOIN gambar g ON(g.id_gambar = akun.id_gambar) LEFT JOIN kunjungan_artikel ka USING(id_artikel) WHERE a.id_author = ? AND a.id_artikel = ? GROUP BY id_artikel", array('id_pegawai' => $pegawai->id_pegawai, 'a.id_artikel' => $id_artikel));
        if (!$this->_publikasi->affected()) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Failed to get created data artikel with author id'
                )
            ));
            $fetch->result();
            return false;
        }

        $dataArtikel = array(
            'id_artikel' => $id_artikel,
            'judul' => Output::decodeEscape($decoded['judul']),
            'id_author' => $pegawai->id_pegawai,
            'nama_author' => $pegawai->nama,
            'path_gambar_author' => $this->_publikasi->getResult()->path_gambar,
            'nama_gambar_author' => $this->_publikasi->getResult()->nama,
            'publish_at' => $this->_publikasi->getResult()->publish_at,
            'aktif' => array(
                'class' => 'badge-success',
                'text' => 'aktif',
                'value' => '1'
            ),
            'jumlah_pengunjung' => $this->_publikasi->getResult()->jumlah_pengunjung
        );

        $result = $this->_publikasi->countData('artikel',array('aktif = ?', array('1')));
        $pages = ceil($result->jumlah_record/$this->_publikasi->getLimit());

        $fetch->addResults(array(
            'error' => false,
            'feedback' => array(
                'message' => '<b class="text-capitalize">' . $label .'</b> <span class="text-orange">'. Output::decodeEscape($dataArtikel['judul']) .'</span>, berhasil ditambahkan',
                'data' => $dataArtikel,
                'pages' => $pages
            )
        ));

        $fetch->result();
        return false;
    }

    private function fetchUpdateArtikel($decoded, $fetch) {
        $decoded = $decoded['artikel'];
        if (count(is_countable($decoded) ? $decoded : []) == 0) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Data wajib berisi beda'
                )
            ));
            $fetch->result();
            return false;
        }

        if (empty($decoded['id_artikel']) || !isset($decoded['id_artikel'])) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Id artikel selengkapnya wajib diisi'
                )
            ));
            $fetch->result();
            return false;
        }

        $id_artikel = Sanitize::toInt2($decoded['id_artikel']);
        unset($decoded['id_artikel']);

        $this->_publikasi->query("SELECT COUNT(id_artikel) jumlah_record, judul, isi, publish_at, aktif, id_author
        FROM artikel 
        WHERE id_artikel = ?", array(
            'id_artikel' => $id_artikel
        ));
        if ($this->_publikasi->getResult()->jumlah_record == 0) {
            $fetch->addResult(array(
                'feedback' => array(
                    'message' => 'Id artikel tidak ditemukan'
                )
            ));
            $fetch->result();
            return false;
        }

        $oldDataArtikel = $this->_publikasi->getResult();
        $oldDataArtikel->aktif = ($this->_publikasi->getResult()->aktif == '1' ? array('text'=>'aktif','class'=>'badge-success','value'=>'1'):array('text'=>'non-aktif','class'=>'badge-danger','value'=>'0'));

        $person = array();
        if ($oldDataArtikel->id_author != $this->data['pegawai']->id_pegawai) {
            $decoded['id_editor'] = $this->data['pegawai']->id_pegawai;
            $this->_publikasi->getData('j.nama, path_gambar', 'pegawai p LEFT JOIN jabatan j USING(id_jabatan) LEFT JOIN akun a USING(id_akun) LEFT JOIN gambar g USING(id_gambar)', array('a.id_akun','=',$this->data['pegawai']->id_akun));
            $person = array(
                'nama_editor' => $this->data['pegawai']->nama,
                'jabatan_editor' => $this->_publikasi->getResult()->nama,
                'path_editor' => $this->_publikasi->getResult()->path_gambar
            );
        }

        if (isset($decoded['id_editor'])) {
            if (is_null($oldDataArtikel->publish_at)) {
                $decoded['publish_at'] = date('Y-m-d H-i-s');
            }
        }
        

        $array_id_gambar = array();
        $label = 'artikel';

        if (isset($decoded['isi'])) {
            $content = $decoded['isi'];
            $counterImg = 1;
            $path_list = array();
            $array_video = array();
            
            foreach ($content['ops'] as $key => $value) {
                if (is_array($value)) {
                    if (array_key_exists('insert', $value)) {
                        foreach($value as $keyInsert => $insert) {
                            if (is_array($insert)) {
                                if (array_key_exists('image', $insert)) {
                                    if (explode('/', $insert['image'])[0] == 'data:image') {
                                        $dataUrl = array(
                                            $label => $insert['image']
                                        );
        
                                        $name_to_add = '-artikel-' . $counterImg;
                        
                                        $uploaded = $fetch->uploadDataUrlIntoServer($dataUrl, 'publikasi', $name_to_add);
                                        
                                        $array_id_gambar[$counterImg] = array(
                                            'nama' => $fetch->path_gambar[$label]['name'],
                                            'path_gambar' => $fetch->path_gambar[$label]['path'],
                                            'label' => $label
                                        );
                                        
                                        // reset data menjadi path gambar
                                        $content['ops'][$key]['insert']['image'] = $fetch->path_gambar[$label]['path'];

                                        $counterImg++;
                                    } else {
                                        array_push($path_list, $insert['image']);
                                    }
                                } else if (array_key_exists('video', $insert)) {
                                    if (str_contains($insert['video'], 'youtube.com')) {
                                        $insert['video'] = str_replace('youtube.com','youtube-nocookie.com', $insert['video']);
                                        $content['ops'][$key]['insert']['video'] = $insert['video'];
                                    }
                                    array_push($array_video, $insert['video']);
                                }
                            }
                        }
                    }
                }
            }

            // Delete img except found list
            if (count(is_countable($path_list) ? $path_list : []) > 0) {
                $this->_publikasi->getData('g.id_gambar, g.nama name, g.path_gambar path', 'gambar g RIGHT JOIN list_gambar_artikel lga USING(id_gambar) RIGHT JOIN artikel a USING(id_artikel)', array('g.path_gambar','NOT IN', $path_list), 'AND', array('a.id_artikel', '=', $id_artikel));
                if ($this->_publikasi->affected()) {
                    $fetch->path_gambar = json_decode(json_encode($this->_publikasi->data()), true);
                    $fetch->removePathGambar();
                }
            } else {
                // Guarantine delete img if on list_gambar_artikel exists but on isi file not exists
                $this->_publikasi->countData('list_gambar_artikel', array('id_artikel = ?', array('id_artikel' => $id_artikel)));
                if ($this->_publikasi->getResult()->jumlah_record > 0) {
                    $this->_publikasi->getData('lga.id_gambar, g.path_gambar path','list_gambar_artikel lga JOIN gambar g USING(id_gambar)',array('lga.id_artikel','=',$id_artikel));
                    if (!$this->_publikasi->affected()) {
                        $fetch->addResults(array(
                            'feedback' => array(
                                'message' => 'Failed to get list_gambar_artikel, update artikel dibatalkan'
                            )
                        ));
                        $fetch->result();
                        return false;
                    }
                    $dataGambar = $this->_publikasi->data();
                    $this->_publikasi->prepareStmt("DELETE FROM gambar WHERE id_gambar = ?");
                    foreach($dataGambar as $index => $value) {
                        $fetch->removeFile(ROOT . DS . 'public' . DS . $value->path);
                        $this->_publikasi->executeStmt(array('id_gambar' => Sanitize::toInt2($value->id_gambar)));
                    }
                }
            }

            $decoded['isi'] = str_replace('\/','/', json_encode($content));

            if (count(is_countable($array_video) ? $array_video : []) > 0) {
                if (Config::no_dupes($array_video) !== true) {
                    if (count(is_countable($array_id_gambar) ? $array_id_gambar : []) > 0) {
                        $fetch->path_gambar = $array_id_gambar;
                        $fetch->removePathGambar();
                    }

                    $fetch->addResults(array(
                        'feedback' => array(
                            'message' => "Video deskripsi harus berbeda"
                        )
                    ));
                    $fetch->result();
                    return false;
                }
            }
        }

        $decoded = Sanitize::thisArray($decoded);
        
        $this->_publikasi->startTransaction();

        if (count(is_countable($decoded) ? $decoded : []) > 0) {
            try {
                $this->_publikasi->update('artikel' ,$decoded, array(
                    'id_artikel','=',$id_artikel
                ));
            } catch (\Throwable $th) {
                $this->_publikasi->rollback();
                $pesan = explode(':',$th->getMessage());
                $fetch->addResults(array(
                    'feedback' => array(
                        'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
                    )
                ));
                $fetch->result();
                return false;
            }
        }

        // Insert New Img
        if (isset($array_id_gambar)) {
            if (count(is_countable($array_id_gambar) ? $array_id_gambar : []) > 0) {
                
                $fetch->addResults(array('uploaded' => $uploaded));
    
                try {
                    $this->_publikasi->createMultiple('gambar', $array_id_gambar);
                    try {
                        $this->_publikasi->query("INSERT INTO list_gambar_artikel (id_artikel, id_gambar) SELECT ?, id_gambar FROM gambar WHERE create_at IN (SELECT create_at FROM gambar WHERE id_gambar = ? AND label = ?)", array(
                                'id_artikel' => $id_artikel,
                                'id_gambar' => $this->_publikasi->lastIID(),
                                'label' => $label
                            )
                        );
                    } catch (\Throwable $th) {
                        $this->_publikasi->rollback();
    
                        if (count(is_countable($array_id_gambar) ? $array_id_gambar : []) > 0) {
                            $fetch->path_gambar = Config::recursiveChangeKey($array_id_gambar, array(
                                'nama' => 'name',
                                'path_gambar' => 'path'
                            ));
                            $fetch->removePathGambar();
                            $fetch->path_gambar = array();
                        }
                        
                        $fetch->addResults(array(
                            'feedback' => array(
                                'message' => 'Failed get all id_gambar after inserted'
                            )
                        ));
    
                        $fetch->result();
                        return false;
                    }
                } catch (\Throwable $th) {
                    $this->_publikasi->rollback();
    
                    if (count(is_countable($array_id_gambar) ? $array_id_gambar : []) > 0) {
                        $fetch->path_gambar = Config::recursiveChangeKey($array_id_gambar, array(
                            'nama' => 'name',
                            'path_gambar' => 'path'
                        ));
                        $fetch->removePathGambar();
                        $fetch->path_gambar = array();
                    }
    
                    $pesan = explode(':',$th->getMessage());
                    $fetch->addResults(array(
                        'feedback' => array(
                            'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
                        )
                    ));
                    
                    $fetch->result();
                    return false;
                }   
            }
        }

        $this->_publikasi->commit();

        if (isset($decoded['isi'])) {
            unset($decoded['isi']);
        }

        $decoded['id_artikel'] = $id_artikel;
        $decoded['publish_at'] = preg_replace('/\./i',':',Output::strToFullLocalDate(date('Y-m-d H:i:s')));
        $decoded['aktif'] = $oldDataArtikel->aktif;
        if (isset($decoded['id_editor'])) {
            $decoded['nama_editor'] = $person->nama_editor;
            $decoded['jabatan_editor'] = $person->jabatan_editor;
            $decoded['path_editor'] = $person->path_editor;
        }

        $fetch->addResults(array(
            'error' => false,
            'feedback' => array(
                'message' => "Berhasil update data artikel",
                'data' => $decoded
            )
        ));

        $fetch->result();
        return false;
    }

    private function fetchGetArtikel($decoded, $fetch) {
        $dataArtikel = $this->_publikasi->query("SELECT id_artikel, isi, judul FROM artikel WHERE id_artikel = ?", array('id_artikel' => Sanitize::toInt2($decoded['id_artikel'])));
        if (!$dataArtikel) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Failed to get data Artikel'
                )
            ));
            $fetch->result();
        }
        
        $fetch->addResults(array(
            'error' => false,
            'feedback' => array(
                'data' => $this->_publikasi->getResult()
            )
        ));

        $fetch->result();
    }

    private function fetchGetResumeArtikel($decoded, $fetch) {
        $dataArtikel = $this->_publikasi->query("SELECT a.judul, a.aktif, IF(a.isi IS NULL,1,0) reset_status, g.path_gambar path_gambar_author, p.nama nama_author, timeAgo(a.publish_at) publish_at_ago, a.id_artikel, COUNT(ka.id_pengunjung) jumlah_kunjungan FROM artikel a LEFT JOIN pegawai p ON(p.id_pegawai = a.id_author) LEFT JOIN admin adm ON(adm.id_pegawai = p.id_pegawai) LEFT JOIN akun USING(id_akun) LEFT JOIN gambar g ON(g.id_gambar = akun.id_gambar) LEFT JOIN kunjungan_artikel ka USING(id_artikel) WHERE a.id_artikel = ? GROUP BY id_artikel", array('a.id_artikel' => Sanitize::toInt2($decoded['id_artikel'])));
        if (!$dataArtikel) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Failed to get resume Artikel'
                )
            ));
            $fetch->result();
        }
        
        $fetch->addResults(array(
            'error' => false,
            'feedback' => array(
                'data' => $this->_publikasi->getResult()
            )
        ));

        $fetch->result();
    }

    private function fetchGetsArtikel($decoded, $fetch) {
        if (isset($decoded['fields'])) {
            $decoded = Sanitize::thisArray($decoded['fields']);
        }

        if (!isset($decoded['active_page'])) {
            $decoded['active_page'] = 1;
        }

        if (isset($decoded['search'])) {
            $this->_publikasi->setSearch($decoded['search']);
        }

        $this->_publikasi->setHalaman(Sanitize::toInt2($decoded['active_page']), 'artikel');
        $this->_publikasi->getsArtikelList();
        $this->data['artikel'] = $this->_publikasi->data();
            $fetch->addResults(array(
                'error' => false,
                'feedback' => $this->data['artikel']
            ));

        $fetch->result();
    }

    private function fetchGetsPilihanArtikel($decoded, $fetch) {
        if (isset($decoded['fields'])) {
            $decoded = Sanitize::thisArray($decoded['fields']);
        }

        if (!isset($decoded['list_id_artikel'])) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Failed to gets data id pilihan Artikel wajib dipilih'
                )
            ));
            $fetch->result();
        }

        if (!isset($decoded['active_page'])) {
            $decoded['active_page'] = 1;
        }

        if (isset($decoded['search'])) {
            $this->_publikasi->setSearch($decoded['search']);
        }

        $this->_publikasi->setHalaman(Sanitize::toInt2($decoded['active_page']), 'artikel', true);
        $this->_publikasi->getData('COUNT(*) jumlah_record','artikel',array('id_artikel','IN', $decoded['list_id_artikel']));
        $this->_publikasi->getsArtikelList($decoded['list_id_artikel']);
        $this->data['artikel'] = $this->_publikasi->data();
            $fetch->addResults(array(
                'error' => false,
                'feedback' => $this->data['artikel']
            ));

        $fetch->result();
    }

    private function fetchResetArtikel($decoded, $fetch) {
        $decoded = $decoded['artikel'];
        if (count(is_countable($decoded) ? $decoded : []) == 0) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Decoded data are empty'
                )
            ));
            $fetch->result();
            return false;
        }

        if (!isset($decoded['id_artikel'])) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Id artikel selengkapnya wajib dipilih on reset'
                )
            ));
            $fetch->result();
            return false;
        }

        $id_artikel = Sanitize::thisArray($decoded['id_artikel']);
        unset($decoded['id_artikel']);

        $dataReset = array();
        if (isset($decoded['reset'])) {
            if ($decoded['reset'] == true) {

                $this->_publikasi->getData('id_gambar, path_gambar','list_gambar_artikel lga JOIN gambar USING(id_gambar)',array('lga.id_artikel','IN',$id_artikel));
                if ($this->_publikasi->affected()) {
                    $id_gambar = array();
                    foreach($this->_publikasi->getResults() as $key => $value) {
                        $fetch->removeFile(ROOT . DS . 'public' . DS . $value->path_gambar);
                        array_push($id_gambar, $value->id_gambar);
                    }
    
                    $this->_publikasi->delete('gambar',array('id_gambar','IN',$id_gambar));
                    if (!$this->_publikasi->affected()) {
                        $fetch->addResults(array(
                            'feedback' => array(
                                'message' => 'Failed to delete gambar on reset'
                            )
                        ));
                        $fetch->result();
                        return false;
                    }
                }
    
                $dataReset['isi'] = NULL;
                unset($dataReset['reset']);
            } else {
                if (!isset($decoded['aktif']) || $decoded['aktif'] == false) {
                    $hasilCek = $this->_publikasi->isArtikelNotNull($id_artikel);
                    if (!$hasilCek) {
                        $fetch->addResults(array(
                            'feedback' => array(
                                'message' => 'Opsi [<b>jangan reset</b>] hanya boleh jika isi artikel belum direset atau ada isi kontennya'
                            )
                        ));
                        $fetch->result();
                        return false;
                    }
                }
            }
        }

        if (isset($decoded['aktif'])) {
            if ($decoded['aktif'] == true) {
                $hasilCek = $this->_publikasi->isArtikelNotNull($id_artikel);
                if (!$hasilCek) {
                    $fetch->addResults(array(
                        'feedback' => array(
                            'message' => 'Opsi [<b>aktifkan</b>] hanya boleh jika konten artikel ada isinya'
                        )
                    ));
                    $fetch->result();
                    return false;
                }
            } else {
                $decoded['aktif'] = '0';
            }

            $dataReset['aktif'] = $decoded['aktif'];

            $decoded['aktif'] = ($decoded['aktif'] == true ? array('text'=>'aktif','class'=>'badge-success','value'=>'1'):array('text'=>'non-aktif','class'=>'badge-danger','value'=>'0'));
        }

        if (count($id_artikel) > 1) {
            $operator = 'IN';
        } else {
            $operator = '=';
        }

        $this->_publikasi->update('artikel', $dataReset, array('id_artikel', $operator, $id_artikel));
        if (!$this->_publikasi->affected()) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Failed to update on reset'
                )
            ));
            $fetch->result();
            return false;
        }

        $decoded['id_artikel'] = $id_artikel;

        $fetch->addResults(array(
            'error' => false,
            'feedback' => array(
                'message' => "Berhasil reset ". (isset($decoded['aktif']) ? '<b>'.($decoded['aktif']['value'] == '1' ? 'mengaktifkan':'non-aktifkan').'</b>':'')." {$this->_publikasi->affected()} data artikel",
                'data' => $decoded
            )
        ));

        $fetch->result();
        return false;
    }
}