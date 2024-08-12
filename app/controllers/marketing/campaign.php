<?php
class CampaignController extends Controller {
    public function __construct() {
        $this->title = 'Marketing';

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

        $this->_auth = $this->model("Auth");
        if (!$this->_auth->hasPermission('marketing') && !$this->_auth->hasPermission('admin')) {
            Redirect::to('');
        }

        $this->data['akun'] = $this->_auth->data();

        $this->model("Donatur");
        if (is_null($this->data['akun']->email)) {
            $akun_value =  $this->data['akun']->kontak; 
            $akun_field = 'd.kontak';
        } else {
            $akun_value =  $this->data['akun']->email; 
            $akun_field = 'd.email';
        }
        $this->model->getAllData('donatur d JOIN akun a ON(d.id_akun = a.id_akun) LEFT JOIN marketing m ON(m.id_akun = a.id_akun)', array($akun_field,'=', $akun_value));
        $this->data['marketing'] = $this->model->getResult();
        $this->_id_donatur = $this->data['marketing']->id_donatur;
        $this->_id_marketing = $this->data['marketing']->id_marketing;
        $this->data['route_alias'] = 'marketing';
        $this->_auth = $this->model("Auth");

        $this->_campaign = $this->model('Campaign');
        $this->_campaign->setLimit(6);
        $this->data['limit'] = $this->getPageRecordLimit();
    }

    public function index($params) {
        if (count(is_countable($params) ? $params : []) > 0) {
            $this->hasil($params[0]);
            return VIEW_PATH .'admin'.DS.'campaign'.DS.'hasil.html';
        }
        $this->rel_action = array(
            array(
                'href' => 'https://cdn.quilljs.com/1.3.6/quill.snow.css'
            ),
            array(
                'href' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
            ),
            array(
                'href' => '/assets/main/css/utility.css'
            ),
            array(
                'href' => '/assets/main/css/pagination.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/editor.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/form-element.css'
            ),
            array(
                'href' => '/assets/route/admin/pages/css/campaign.css'
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
                'src' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                'source' => 'trushworty'
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
                'src' => '/assets/route/admin/pages/js/campaign.js'
			)
        );

        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();

        $this->_campaign->setHalaman(1, 'campaign');
        $this->_campaign->readInformasiCampaign();
        $this->data['campaign'] = $this->_campaign->data();
    }

    private function hasil($tag) {
        $this->rel_action = array(
            array(
                'href' => '/assets/route/admin/pages/css/hasil.css'
            )
        );
        $this->script_action = array(
            array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/pages/js/hasil.js'
			)
        );
        $this->_campaign->countData('campaign JOIN bantuan USING(id_bantuan)',array('bantuan.tag = ?',Sanitize::escape2($tag)));
        if ($this->_campaign->getResult()->jumlah_record == 0) {
            Redirect::to('admin/campaign');
        }

        $dataMarketing = $this->_campaign->isMarketer($this->data['akun']->email);
        if (!$dataMarketing) {
            $id_marketing = 1;
        } else {
            $id_marketing = $this->_campaign->getResult()->id_marketing;
        }

        $this->_campaign->getInfoCampaign($tag, $id_marketing);
        $this->data['info']['card'] = $this->_campaign->getResult();

        $this->_campaign->getDataCampaign($tag);
        $this->data['info']['img'] = $this->_campaign->getResult();

        $this->_campaign->readCampaignKujungan($tag);
        $this->data['kunjungan'] = $this->_campaign->getResults();
    }

    public function fetch($params) {
        $fetch = new Fetch();

        switch ($params[0]) {
            case 'bantuan':
                // bantuan Params
                $params[0] = 'BantuanReadList';
            break;

            case 'get':
                // get Params
                $params[0] = 'get';
                if (isset($params[1])) {
                    if ($params[1] == 'bantuan') {
                        // fetchGetBantuan Params
                        $params[0] .= 'Bantuan';
                    }
                } else {
                    // fetchGet Params
                }
            break;

            case 'create':
                // fetchCreate Params
            break;

            case 'gets':
                // fetchGets Params
            break;

            case 'update-aktif':
                // fetchUpdateAktif Params
                $params[0] = 'UpdateAktif';
            break;

            case 'update':
                // fetchUpdate Params
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

        $decoded = $fetch->getDecoded();

        // prepare method Token name
        $action = __FUNCTION__ . ucfirst($params[0]);
        // call method Token
        $this->$action($decoded, $fetch);
        
        return false;
    }

    private function fetchUpdate($decoded, $fetch) {
        $decoded = $decoded['campaign'];
        if (count(is_countable($decoded) ? $decoded : []) == 0) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Data wajib berisi beda'
                )
            ));
            $fetch->result();
            return false;
        }

        $id_campaign = Sanitize::toInt2(base64_decode(strrev($decoded['id_campaign'])));
        unset($decoded['id_campaign']);

        $emailAkun = $this->_auth->data()->email;
        // lewati dulu sementara, sebelum route digital-partner-marketing ada
        // if (!$this->_auth->affected()) {
        //     $marketer = $this->_campaign->isMarketer($this->_auth->data()->email);
        //     if (!$marketer) {
        //         $fetch->addResults(array(
        //             'feedback' => array(
        //                 'message' => 'Id editor tidak ditemukan'
        //             )
        //         ));
        //     }
            
        //     $fetch->result();
        //     return false;
        // }

        $decoded['id_akun_editor'] = $this->_auth->data()->id_akun;

        $person = $this->_auth->getData("d.nama nama_editor, g.path_gambar path_editor, IF(akun.hak_akses = 'A', j.nama, 'Digital Marketing') jabatan_editor",'donatur d JOIN akun USING(id_akun) LEFT JOIN gambar g USING(id_gambar) LEFT JOIN pegawai p ON(akun.email = p.email) LEFT JOIN jabatan j USING(id_jabatan)',array('akun.id_akun','=',$decoded['id_akun_editor']));
        if (!$person) {
            $person = (object) array(
                'nama_editor' => 'Tim Digital',
                'jabatan_editor' => ''
            );
        } else {
            $person = $this->_auth->data();
        }

        $this->_campaign->query('SELECT COUNT(id_campaign) jumlah_record, b.id_bantuan, b.nama nama_bantuan, isi, publish_at, id_akun_maker, b.status, aktif
        FROM campaign LEFT JOIN bantuan b USING(id_bantuan) 
        WHERE id_campaign = ? AND b.blokir IS NULL', array(
            'id_campaign' => $id_campaign
        ));
        if ($this->_campaign->getResult()->jumlah_record == 0) {
            $fetch->addResult(array(
                'feedback' => array(
                    'message' => 'Id campaign tidak ditemukan'
                )
            ));
            $fetch->result();
            return false;
        }

        $old_id_bantuan = $this->_campaign->getResult()->id_bantuan;
        $old_nama_bantuan = $this->_campaign->getResult()->nama_bantuan;
        $old_status = $this->_campaign->getResult()->status;
        $publish_at = $this->_campaign->getResult()->publish_at;
        $id_akun_maker = $this->_campaign->getResult()->id_akun_maker;
        $aktif = ($this->_campaign->getResult()->aktif == '1' ? array('text'=>'aktif','class'=>'badge-success','value'=>'1'):array('text'=>'non-aktif','class'=>'badge-danger','value'=>'0'));

        if ($id_akun_maker == $decoded['id_akun_editor']) {
            unset($decoded['id_akun_editor']);
        }

        if (isset($decoded['id_akun_editor'])) {
            if (is_null($publish_at)) {
                $decoded['publish_at'] = date('Y-m-d H-i-s');
            }
        }

        // ada pergantian id_bantuan campaign
        if (isset($decoded['id_bantuan'])) {
            $this->_campaign->getData('nama nama_bantuan, tag, status','bantuan',array('id_bantuan','=',Sanitize::toInt2($decoded['id_bantuan'])),'AND',array('blokir','IS',NULL));
            if (!$this->_campaign->affected()) {
                $fetch->addResult(array(
                    'feedback' => array(
                        'message' => 'Id bantuan tidak ditemukan'
                    )
                ));
                $fetch->result();
                return false;
            }

            $selectedBantuan = $this->_campaign->getResult();
            $selected_bantuan = $selectedBantuan->nama_bantuan;
        }

        if (isset($decoded['isi'])) {
            $content = $decoded['isi'];
            $counterImg = 1;
            $array_id_gambar = array();
            $path_list = array();
            $label = 'campaign';
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
        
                                        $name_to_add = '-marketing-' . $counterImg;
                        
                                        $uploaded = $fetch->uploadDataUrlIntoServer($dataUrl, 'bantuan', $name_to_add);
                                        
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
                $this->_campaign->getData('g.id_gambar, g.nama name, g.path_gambar path', 'gambar g RIGHT JOIN list_gambar_campaign lgc USING(id_gambar) RIGHT JOIN campaign c USING(id_campaign)', array('g.path_gambar','NOT IN', $path_list), 'AND', array('c.id_campaign', '=', $id_campaign));
                if ($this->_campaign->affected()) {
                    $fetch->path_gambar = json_decode(json_encode($this->_campaign->data()), true);
                    $fetch->removePathGambar();
                }
            } else {
                // Guarantine delete img if on list_gambar_campaign exists but on isi file not exists
                $this->_campaign->countData('list_gambar_campaign', array('id_campaign = ?', array('id_campaign' => $id_campaign)));
                if ($this->_campaign->getResult()->jumlah_record > 0) {
                    $this->_campaign->getData('lgc.id_gambar, g.path_gambar path','list_gambar_campaign lgc JOIN gambar g USING(id_gambar)',array('lgc.id_campaign','=',$id_campaign));
                    if (!$this->_campaign->affected()) {
                        $fetch->addResults(array(
                            'feedback' => array(
                                'message' => 'Failed to get list_gambar_campaign, update campaign dibatalkan'
                            )
                        ));
                        $fetch->result();
                        return false;
                    }
                    $dataGambar = $this->_campaign->data();
                    $this->_campaign->prepareStmt("DELETE FROM gambar WHERE id_gambar = ?");
                    foreach($dataGambar as $index => $value) {
                        $fetch->removeFile(ROOT . DS . 'public' . DS . $value->path);
                        $this->_campaign->executeStmt(array('id_gambar' => Sanitize::toInt2($value->id_gambar)));
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

        $this->_campaign->startTransaction();

        if (count(is_countable($decoded) ? $decoded : []) > 0) {
            try {
                $this->_campaign->update('campaign' ,$decoded, array(
                    'id_campaign','=',$id_campaign
                ));
            } catch (\Throwable $th) {
                $this->_campaign->rollback();
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
                    $this->_campaign->createMultiple('gambar', $array_id_gambar);
                    try {
                        $this->_campaign->query("INSERT INTO list_gambar_campaign (id_campaign, id_gambar) SELECT ?, id_gambar FROM gambar WHERE create_at IN (SELECT create_at FROM gambar WHERE id_gambar = ? AND label = ?)", array(
                                'id_campaign' => $id_campaign,
                                'id_gambar' => $this->_campaign->lastIID(),
                                'label' => $label
                            )
                        );
                    } catch (\Throwable $th) {
                        $this->_campaign->rollback();
    
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
                    $this->model->rollback();
    
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

        $this->_campaign->commit();

        if (isset($decoded['isi'])) {
            unset($decoded['isi']);
        }

        $decoded['id_campaign'] = strrev(base64_encode($id_campaign));
        $decoded['modified_at'] = date('Y-m-d H:i:s');
        $decoded['aktif'] = $aktif;
        if (isset($decoded['id_akun_editor'])) {
            $decoded['nama_editor'] = $person->nama_editor;
            $decoded['jabatan_editor'] = $person->jabatan_editor;
            $decoded['path_editor'] = $person->path_editor;
        }

        if (isset($selected_bantuan)) {
            $decoded['nama_bantuan'] = Output::decodeEscape($selected_bantuan);
            $decoded['tag'] = $selectedBantuan->tag;
            $decoded['status'] = Utility::statusBantuanClassTextBadge($selectedBantuan->status);
        } else {
            $decoded['status'] = Utility::statusBantuanClassTextBadge($old_status);
        }
        
        $fetch->addResults(array(
            'error' => false,
            'feedback' => array(
                'message' => "Berhasil update data campaign",
                'data' => $decoded
            )
        ));

        $fetch->result();
        return false;
    }

    private function fetchGet($decoded, $fetch) {
        $dataCampaign = $this->_campaign->query("SELECT c.isi, b.id_bantuan id, b.nama text, tag FROM campaign c LEFT JOIN bantuan b USING(id_bantuan) WHERE id_campaign = ?", array('id_campaign' => Sanitize::toInt2(base64_decode(strrev($decoded['id_campaign'])))));
        if (!$dataCampaign) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Failed to get data Campaign'
                )
            ));
            $fetch->result();
        }
        
        $fetch->addResults(array(
            'error' => false,
            'feedback' => array(
                'data' => $this->_campaign->getResult()
            )
        ));

        $fetch->result();
    }

    private function fetchUpdateAktif($decoded, $fetch) {
        $this->_campaign->update('campaign',array('aktif' => Sanitize::escape2($decoded['fields']['aktif'])),array(
            'id_campaign', '=', Sanitize::toInt2(base64_decode(strrev($decoded['id_campaign'])))));
        if (!$this->_campaign->affected()) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Failed to update status aktif campaign'
                )
            ));
            $fetch->result();
        }
        if ($decoded['fields']['aktif'] == '1') {
            $status = 'gaktifkan';
            $aktif = array(
                'class' => 'badge-success',
                'text' => 'aktif',
                'value' => '1'
            );
        } else {
            $status = 'on-aktifkan';
            $aktif = array(
                'class' => 'badge-danger',
                'text' => 'non-aktif',
                'value' => '0'
            );
        }
        $fetch->addResults(array(
            'error' => false,
            'feedback' => array(
                'data' => array(
                    'aktif' => $aktif,
                    'id_campaign' => $decoded['id_campaign'],
                    'modified_at' => date('Y-m-d H:i:s')
                ),
                'message' => "Berhasil men{$status} campaign"
            )
        ));
        $fetch->result();
    }

    private function fetchCreate($decoded, $fetch) {
        $decoded = $decoded['campaign'];
        
        if (!isset($decoded['id_bantuan'])) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Id bantuan wajib ditentukan'
                )
            ));
            $fetch->result();
            return false;
        }
        $emailAkun = $this->_auth->data()->email;
        // lewati dulu sementara, sebelum route digital-partner-marketing ada
        // if (!$this->_auth->affected()) {
        //     $marketer = $this->_campaign->isMarketer($this->_auth->data()->email);
        //     if (!$marketer) {
        //         $fetch->addResults(array(
        //             'feedback' => array(
        //                 'message' => 'Id author tidak ditemukan'
        //             )
        //         ));
        //     }
            
        //     $fetch->result();
        //     return false;
        // }

        $decoded['id_akun_maker'] = $this->_auth->data()->id_akun;
        $person = $this->_auth->getData("IFNULL(d.nama, p.nama) nama_maker, g.path_gambar path_maker, IF(a.hak_akses = 'A', j.nama, 'Digital Marketing') jabatan_maker",'akun a LEFT JOIN donatur d USING(id_akun) LEFT JOIN gambar g USING(id_gambar) LEFT JOIN pegawai p ON(a.email = p.email) LEFT JOIN jabatan j USING(id_jabatan)',array('a.id_akun','=',$decoded['id_akun_maker']));
        if (!$person) {
            $person = (object) array(
                'nama_maker' => 'Tim Digital',
                'jabatan_maker' => '',
                'path_maker' => ''
            );
        } else {
            $person = $this->_auth->data();
        }

        $content = $decoded['isi'];
        $counterImg = 1;
        $array_id_gambar = array();
        $label = 'campaign';
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

                                $name_to_add = '-marketing-' . $counterImg;
                        
                                $uploaded = $fetch->uploadDataUrlIntoServer($dataUrl, 'bantuan', $name_to_add);
                                
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
        $decoded['id_bantuan'] = Sanitize::toInt2($decoded['id_bantuan']);

        try {
            $params = array(
                'isi' => $decoded['isi'],
                'id_akun_maker' => $decoded['id_akun_maker'],
                'id_bantuan' => $decoded['id_bantuan']
            );
            
            if (isset($decoded['id_akun_maker'])) {
                $params['publish_at'] = date('Y-m-d H-i-s');
            }

            $this->_campaign->create('campaign', $params);
            
            $id_campaign = $this->_campaign->lastIID();

            if (count(is_countable($array_id_gambar) ? $array_id_gambar : []) > 0) {
                
                $fetch->addResults(array(
                    'uploaded' => $uploaded
                ));

                try {
                    $this->_campaign->createMultiple('gambar', $array_id_gambar);
                    
                    try {
                        $this->_campaign->query("INSERT INTO list_gambar_campaign (id_campaign, id_gambar) SELECT ?, id_gambar FROM gambar WHERE create_at IN (SELECT create_at FROM gambar WHERE id_gambar = ? AND label = ?)", array(
                                'id_campaign' => $id_campaign,
                                'id_gambar' => $this->_campaign->lastIID(),
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

        $this->_campaign->getData("c.id_bantuan, b.nama nama_bantuan, b.tag, b.status, c.modified_at, timeAgo(c.modified_at) time_ago, c.aktif","campaign c JOIN bantuan b USING(id_bantuan)",array("c.id_bantuan","=",$decoded['id_bantuan']));
        $dataCampaign = $this->_campaign->getResult();
        $dataCampaign->aktif = ($dataCampaign->aktif == '1' ? array('class'=>'badge-success','text'=>'aktif','value'=>'1') : array('class'=>'badge-danger','text'=>'non-aktif','value'=>'0'));

        $result = $this->_campaign->countData('campaign',array('aktif = ?', array('1')));
        $pages = ceil($result->jumlah_record/$this->_campaign->getLimit());

        $fetch->addResults(array(
            'error' => false,
            'feedback' => array(
                'message' => '<b class="text-capitalize">' . $label .'</b> <span class="text-orange">'. Output::decodeEscape($dataCampaign->nama_bantuan) .'</span>, berhasil ditambahkan',
                'data' => array(
                    'id_campaign' => $id_campaign,
                    'nama_bantuan' => Output::decodeEscape($dataCampaign->nama_bantuan),
                    'id_akun_maker' => $decoded['id_akun_maker'],
                    'nama_author' => $person->nama_maker,
                    'path_author' => $person->path_maker,
                    'jabatan_author' => $person->jabatan_maker,
                    'id_bantuan' => $decoded['id_bantuan'],
                    'tag' => $dataCampaign->tag,
                    'aktif' => $dataCampaign->aktif,
                    'status' => Utility::statusBantuanClassTextBadge($dataCampaign->status),
                    'time_ago' => $dataCampaign->time_ago,
                    'modified_at' => $dataCampaign->modified_at
                ),
                'pages' => $pages
            )
        ));

        $fetch->result();
        return false;
    }

    private function fetchGets($decoded, $fetch) {
        $decoded = Sanitize::thisArray($decoded['fields']);

        if (!isset($decoded['limit'])) {
            $decoded['limit'] = $this->_campaign->getLimit();
        }


        if (!isset($decoded['active_page'])) {
            $decoded['active_page'] = 1;
        }

        if (isset($decoded['search'])) {
            $this->_campaign->setSearch($decoded['search']);
        }

        $this->_campaign->setLimit($decoded['limit']);
        $this->_campaign->setHalaman($decoded['active_page'], 'campaign');
        $this->_campaign->readInformasiCampaign();
        $this->data['campaign'] = $this->_campaign->data();
        $fetch->addResults(array(
            'error' => false,
            'feedback' => $this->data['campaign']
        ));

        $fetch->result();
    }

    private function fetchBantuanReadList($decoded, $fetch) {
        if (isset($decoded['offset'])) {
            $offset = Sanitize::toInt2($decoded['offset']);
        } else {
            $offset = 0;
        }

        if (isset($decoded['limit'])) {
            $limit = Sanitize::toInt2($decoded['limit']);
        } else {
            $limit = 6;
        }

        $this->model('Bantuan');
        $search = null;
        $search_columnQ = '';
        $params = array();
        if (isset($decoded['search'])) {
            $this->_campaign->setSearch($decoded['search']);
            $search_columnQ = "AND LOWER(CONCAT(IFNULL(b.id_bantuan,''),IFNULL(b.nama,''),IFNULL(b.tag,''))) LIKE LOWER(CONCAT('%',?,'%'))";
            array_push($params, $this->_campaign->getSearch());
            $search = array(
                'search_column' => $search_columnQ,
                'search_value' => $this->_campaign->getSearch()
            );
        }

        $dataBantuan = $this->_campaign->query("SELECT b.id_bantuan id, b.nama text, tag FROM bantuan b LEFT JOIN campaign c USING(id_bantuan) WHERE status = 'D' AND c.id_bantuan IS NULL {$search_columnQ} ORDER BY b.id_bantuan DESC LIMIT {$offset},{$limit}", $params);
        if (!$dataBantuan) {
            $fetch->addResults(array(
                'feedback' => array(
                    'message' => 'Failed to read Campaign Bantuan'
                )
            ));
        } else {
            $data = $this->_campaign->getResults();
            $this->_campaign->query("SELECT COUNT(*) jumlah_record FROM bantuan b LEFT JOIN campaign c USING(id_bantuan) WHERE status = 'D' AND c.id_bantuan IS NULL {$search_columnQ}", $params);
            if (!$this->_campaign->affected()) {
                $fetch->addResults(array(
                    'feedback' => array(
                        'message' => 'Failed to count Campaign Bantuan'
                    )
                ));
            } else {
                $count = $this->_campaign->getResult();
                $fetch->addResults(array(
                    'error' => false
                ));

                $feedback = array(
                    'feedback' => array(
                        'data' => $data,
                        'limit' => $limit,
                        'offset' => (int) $offset,
                        'record' => $count->jumlah_record,
                        'load_more' => ($count->jumlah_record > $offset + $limit)
                    )
                );

                if (!is_null($search)) {
                    $feedback['feedback']['search'] = $search['search_value'];
                }

                $fetch->addResults($feedback);
            }
        }
        $fetch->result();
    }
}