<?php 
class FetchController extends Controller {
    private $_result = array('error' => true);
    protected $path_gambar;

    public function __construct() {
        if (!isset($_SERVER['HTTP_REFERER']) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Redirect::to('donatur');
        }
    }

    private function removePathGambar() {
        if ($this->_result['uploaded'] == true) {
            foreach($this->path_gambar as $key => $path_file) {
                $this->removeFile(ROOT . DS . 'public' . DS . $path_file['path']);
            }

            $this->model('home');
            $j = 0;
            foreach($this->path_gambar as $key => $path_gambar) {
                $this->model->delete('gambar', array('nama', '=', Sanitize::escape2($path_gambar['name'])));
                if (!$this->model->affected()) {
                    $this->_error = true;
                    $this->_result['remove_file_message'] = 'Gambar gagal dihapus dari database';
                } else {
                    $j++;
                }
            }

            if ($j == count(is_countable($this->path_gambar) ? $this->path_gambar : [])) {
                $this->_result['remove_file_message'] = 'Gambar berhasil dihapus dari database dan direktory';
            }
        }
    }

    private function checkToken($token) {
        if (!Token::check($token)) {
            $this->_result['feedback'] = array(
                'message' => 'You not allowed to use fake token'
            );
            $this->result();
            return false;
        }
    }

    private function contentTypeJsonDecoded($server_content_type) {
        $contentType = isset($server_content_type) ? trim($server_content_type) : '';

        if ($contentType !== "application/json") {
            $this->_result['feedback'] = array(
                'message' => 'Unrecognize contentType JSON ONLY'
            );
            $this->result();
            return false;
        }

        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        return $decoded;
    }

    public function create($params = array()) {
        if (count(is_countable($params) ? $params : []) == 0) {
            $this->_result['feedback'] = array(
                'message' => 'Number of params not found'
            );
            $this->result();
            return false;
        }

        // Check Content Type and decode JSON to array
        $decoded = $this->contentTypeJsonDecoded($_SERVER["CONTENT_TYPE"]);

        // Check Token
        $this->checkToken($decoded['token']);

        switch ($params[0]) {
            case 'bantuan':
                // bantuanCreate
            break;

            case 'donasi':
                // donasiCreate
            break;
            
            default:
                $this->_result['feedback'] = array(
                    'message' => 'Unrecognize params '. $params[0]
                );
                $this->result();
                return false;
            break;
        }

        // prepare method create name
        $action = $params[0] . 'Create';
        // call method create
        $this->$action($decoded);

        return false;
    }

    public function update($params = array()) {
        if (count(is_countable($params) ? $params : []) == 0) {
            $this->_result['feedback'] = array(
                'message' => 'Number of params not found'
            );
            $this->result();
            return false;
        }

        // Check Content Type and decode JSON to array
        $decoded = $this->contentTypeJsonDecoded($_SERVER["CONTENT_TYPE"]);

        // Check Token
        $this->checkToken($decoded['token']);

        switch ($params[0]) {
            case 'bantuan':
                // bantuanUpdate
            break;
            
            default:
                $this->_result['feedback'] = array(
                    'message' => 'Unrecognize params '. $params[0]
                );
                $this->result();
                return false;
            break;
        }

        // prepare method update name
        $action = $params[0] . 'Update';
        // call method update
        $this->$action($decoded);

        return false;
    }

    public function verivikasi($params = array()) {
        if (count(is_countable($params) ? $params : []) == 0) {
            $this->_result['feedback'] = array(
                'message' => 'Number of params not found'
            );
            $this->result();
            return false;
        }

        // Check Content Type and decode JSON to array
        $decoded = $this->contentTypeJsonDecoded($_SERVER["CONTENT_TYPE"]);

        // Check Token
        $this->checkToken($decoded['token']);

        switch ($params[0]) {
            case 'donasi':
                // donasiVerivikasi
            break;
            
            default:
                $this->_result['feedback'] = array(
                    'message' => 'Unrecognize params '. $params[0]
                );
                $this->result();
                return false;
            break;
        }

        // prepare method verivikasi name
        $action = $params[0] . 'Verivikasi';
        // call method verivikasi
        $this->$action($decoded);

        return false;
    }

    public function read($params = array()) {
        if (count(is_countable($params) ? $params : []) == 0) {
            $this->_result['feedback'] = array(
                'message' => 'Number of params not found'
            );
            $this->result();
            return false;
        }

        // Check Content Type and decode JSON to array
        $decoded = $this->contentTypeJsonDecoded($_SERVER["CONTENT_TYPE"]);

        // Check Token
        $this->checkToken($decoded['token']);

        switch ($params[0]) {
            case 'donasi':
                // donasi Params
            break;

            case 'channel-payment':
                // donasi Params
                $params[0] = 'channelPayment';
            break;
            
            default:
                $this->_result['feedback'] = array(
                    'message' => 'Unrecognize params '. $params[0]
                );
                $this->result();
                return false;
            break;
        }

        if (isset($params[1])) {
            switch ($params[1]) {
                case 'bantuan':
                    // bantuan Params
                break;

                case 'list':
                    // bantuan Params
                break;
                
                default:
                    $this->_result['feedback'] = array(
                        'message' => 'Unrecognize params '. $params[1]
                    );
                    $this->result();
                    return false;
                break;
            }

            $params[0] .= ucfirst($params[1]);
        }

        // prepare method create name
        $action = $params[0] . 'Read';
        // call method create
        $this->$action($decoded);

        return false;
    }

    public function get($params = array()) {
        if (count(is_countable($params) ? $params : []) == 0) {
            $this->_result['feedback'] = array(
                'message' => 'Number of params not found'
            );
            $this->result();
            return false;
        }

        // Check Content Type and decode JSON to array
        $decoded = $this->contentTypeJsonDecoded($_SERVER["CONTENT_TYPE"]);

        // Check Token
        $this->checkToken($decoded['token']);

        switch ($params[0]) {
            case 'donasi':
                // donasi Params
            break;
            
            default:
                $this->_result['feedback'] = array(
                    'message' => 'Unrecognize params '. $params[0]
                );
                $this->result();
                return false;
            break;
        }

        // prepare method create name
        $action = $params[0] . 'Get';
        // call method create
        $this->$action($decoded);

        return false;
    }

    private function removeFile($path_gambar = null) {
        if (is_null($path_gambar)) {
            return false;
        }

        if (file_exists($path_gambar)) {
            unlink($path_gambar);
        } else {
            echo "Failed to remove file";
        }
    }

    private function uploadDataUrlIntoServer($params = array()) {
        if (count(is_countable($params) ? $params : []) == 0) {
            return false;
        }

        $i = 0;

        $arrayPath = array();

        foreach($params as $key => $fileImg) {
            if ($key == 'gambar_medium') {
                $directory_name = 'medium';
            } else if ($key == 'gambar_wide') {
                $directory_name = 'wide';
            }
            
            $fileImg = str_replace('data:image/jpeg;base64,', '', $fileImg);
            $fileImg = str_replace(' ', '+', $fileImg);
            $fileData = base64_decode($fileImg);

            $upload_directory = BASEURL . 'uploads' . DS . 'images' . DS . 'bantuan' . DS . $directory_name;

            if (!is_dir($upload_directory)) {
                mkdir($upload_directory, 0777, $rekursif = true);
            }

            $path_gambar = $upload_directory. DS . time() . '-' . $directory_name . '.jpeg';

            $uploaded = file_put_contents($path_gambar, $fileData);

            if (!$uploaded) {
                break;
            } else {
                $path_gambar = '/uploads/images/bantuan/' . $directory_name . '/' . time() . '-' . $directory_name . '.jpeg';
                $arrayPath[$key] = array(
                    'name' => time() . '-' . $directory_name,
                    'path' => $path_gambar
                );
                $i++;
            }
        }

        $this->path_gambar = $arrayPath;

        if (count(is_countable($params) ? $params : []) != $i) {
            foreach($this->path_gambar as $key => $path_file) {
                $this->removeFile(ROOT . DS . 'public' . DS . $path_file['path']);
            }
            return false;
        }

        return true;
    }

    private function result() {
        $this->_result[Config::get('session/token_name')] = Token::generate();
        $toast = array(
            'error' => $this->_result['error'],
            'feedback' => $this->_result['feedback']
        );
        if (array_key_exists('message', $toast['feedback'])) {
            Session::put('toast', $toast);
        }
        echo json_encode($this->_result);
    }

    private function bantuanCreate($decoded) {
        // if (!isset($decoded['card_img'])) {
        //     $this->_result['feedback'] = array(
        //         'message' => 'data file gambar (<span class="font-weight-bold">16:9</span>) wajib diisi',
        //         'rule' => 'required',
        //         'name' => 'card_img'
        //     );
        //     $this->result();
        //     return false;
        // }

        // if (!isset($decoded['wide_img'])) {
        //     $this->_result['feedback'] = array(
        //         'message' => 'data file gambar (<span class="font-weight-bold">486:139</span>) wajib diisi',
        //         'rule' => 'required',
        //         'name' => 'wide_img'
        //     );
        //     $this->result();
        //     return false;
        // }

        $dataUrl = array(
            'gambar_medium' => $decoded['card_img'],
            'gambar_wide' => $decoded['wide_img']
        );

        $uploaded = $this->uploadDataUrlIntoServer($dataUrl);

        $this->_result['uploaded'] = $uploaded;

        if (!$uploaded) {
            $this->_result['feedback'] = array(
                'message' => 'Terjadi kegagalan upload file',
            );
            $this->result();
            return false;
        }

        $array_id_gambar = array();

        $this->model('Bantuan');

        $loop = 1;
        foreach($this->path_gambar as $key => $value) {
            $this->model->create('gambar', array(
                'nama' => Sanitize::escape2($value['name']), 
                'path_gambar' => Sanitize::escape2($value['path']),
                'label' => 'bantuan'
            ));
            if ($this->model->affected()) {
                $array_id_gambar[$key] = $this->model->lastIID();
                if ($loop == count(is_countable($this->path_gambar) ? $this->path_gambar : [])) {
                    $this->_result['error'] = false;
                    $this->_result['feedback'] = array(
                        'message' => 'Sucess Insert All path_gambar donasi'
                    );
                }
            } else {
                $this->_result['feedback'] = array(
                    'message' => 'Failed to INSERT path_gambar donasi => ' . $value
                );
                break;
            }
            $loop++;
        }

        if ($this->_result['error'] == true) {
            $this->removePathGambar();
            $this->result();
            return false;
        }

        unset($decoded['card_img']);
        unset($decoded['wide_img']);
        unset($decoded['token']);

        $decoded['id_gambar_medium'] = $array_id_gambar['gambar_medium'];
        $decoded['id_gambar_wide'] = $array_id_gambar['gambar_wide'];

        $decoded = Sanitize::thisArray($decoded, 'escape');

        if (isset($decoded['lama_penayangan'])) {
            $decoded['lama_penayangan'] = Sanitize::toInt2($decoded['lama_penayangan']);
            $decoded['tanggal_awal'] = date('Y-m-d');
            $endDate = date_create($decoded['tanggal_awal']);
            date_add($endDate, date_interval_create_from_date_string($decoded['lama_penayangan'] . " day"));
            $decoded['tanggal_akhir'] = date_format($endDate, 'Y-m-d');
        }

        if (isset($decoded['min_donasi'])) {
            $decoded['min_donasi'] = Sanitize::toInt2($decoded['min_donasi']);
        }

        if (isset($decoded['jumlah_target'])) {
            $decoded['jumlah_target'] = Sanitize::toInt2($decoded['jumlah_target']);
        }

        if (isset($decoded['total_rab'])) {
            $decoded['total_rab'] = Sanitize::toInt2($decoded['total_rab']);
        }

        $this->_result['path_gambar'] = $this->path_gambar;

        if (isset($decoded['tag'])) {
            $decoded['tag'] = Sanitize::noDblSpace2($decoded['tag']);
            $this->model->query('SELECT COUNT(id_bantuan) found_tag FROM bantuan WHERE tag = ?', array('tag' => $decoded['tag']));
            if ($this->model->result()->found_tag >= 1) {
                $this->_result['error'] = true;
                $this->_result['feedback'] = array(
                    'message' => 'Nama Tag bantuan sudah terpakai, coba ganti dengan nama tag yang lain.',
                    'rule' => 'unique',
                    'name' => 'tag'
                );
                $this->removePathGambar();
                $this->result();
                return false;
            }
        }

        // Experimental
        $decoded['nama'] = Sanitize::noDblSpace2($decoded['nama']);
        $this->model->query('SELECT COUNT(id_bantuan) found_nama FROM bantuan WHERE nama = ?', array('nama' => $decoded['nama']));
        if ($this->model->data()->found_nama >= 1) {
            $this->_result['error'] = true;
            $this->_result['feedback'] = array(
                'message' => 'Nama nama bantuan sudah terpakai, coba ganti dengan nama nama yang lain.',
                'rule' => 'unique',
                'name' => 'nama'
            );
            $this->removePathGambar();
            $this->result();
            return false;
        }

        $decoded['status'] = 'D';
        $this->model->create('bantuan', $decoded);

        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Terjadi kegagalan create disisi server saat create bantuan'
            );
            $this->_result['error'] = true;
            $this->model->delete('gambar', array('id_gambar','IN', implode(',',$array_id_gambar)));
            if (!$this->model->affetced()) {
                $this->_result['feedback'] = array(
                    'message' => $this->_result['feedback']['message'] . ' failed to delete data gambar.'
                );
            } else {
                $this->model->query("SELECT MAX(id_gambar) id_gambar FROM gambar");
                $id_gambar = $this->model->data()->id_gambar;
                $this->model->query("ALTER TABLE gambar AUTO_INCREMENT = {$id_gambar}");
                $this->_result['feedback'] = array(
                    'message' => $this->_result['feedback']['message'] . ' success delete data gambar.'
                );
            }
            $this->removePathGambar();
            $this->result();
            return false;
        }

        $id_bantuan = $this->model->lastIID();
        $this->_result['feedback'] = array(
            'message' => 'Berhasil menambahkan data bantuan (<span class="font-weight-bold" data-id-target="'. $id_bantuan .'">' . $decoded['nama'] . '</span>)'
        );

        $this->_result['error'] = false;
        $this->_result['feedback']['id_bantuan'] = $id_bantuan;
        $this->result();
        return false;
    }

    private function donasiCreate($decoded) {
        $decoded = Sanitize::thisArray($decoded['data']);

        if (!isset($decoded['id_bantuan'])) {
            $this->_result['feedback'] = array(
                'message' => 'Program bantuan wajib dipilih'
            );
            $this->result();
            return false;
        }

        if (!isset($decoded['jumlah_donasi'])) {
            $this->_result['feedback'] = array(
                'message' => 'Jumlah donasi wajib diisi'
            );
            $this->result();
            return false;
        }

        if (!isset($decoded['waktu_bayar'])) {
            $this->_result['feedback'] = array(
                'message' => 'Waktu bayar wajib diisi'
            );
            $this->result();
            return false;
        }

        if (!isset($decoded['id_cp'])) {
            $this->_result['feedback'] = array(
                'message' => 'Channel Payment wajib dipilih'
            );
            $this->result();
            return false;
        }

        if (!isset($decoded['id_donatur'])) {
            $this->_result['feedback'] = array(
                'message' => 'Donatur wajib dipilih'
            );
            $this->result();
            return false;
        }

        $waktu_bayar = new DateTime(date('Y-m-d', strtotime($decoded['waktu_bayar'])));
        $decoded['waktu_bayar'] = $waktu_bayar->format('Y-m-d H:i:s');
        $decoded['jumlah_donasi'] = Sanitize::toInt2($decoded['jumlah_donasi']);
        $this->model('Donasi');

        $dataFindId = $this->model->query("SELECT (SELECT count(id_bantuan) FROM bantuan WHERE id_bantuan = ?) bantuan_count, (SELECT count(id_cp) FROM channel_payment WHERE id_cp = ?) cp_count, (SELECT count(id_donatur) FROM donatur WHERE id_donatur = ?) donatur_count", array('id_bantuan' => $decoded['id_bantuan'], 'id_cp' => $decoded['id_cp'], 'id_donatur' => $decoded['id_donatur']));

        if (!$dataFindId->bantuan_count) {
            $this->_result['feedback'] = array(
                'message' => 'Data bantuan terpilih tidak ditemukan'
            );
            $this->result();
            return false;
        }

        if (!$dataFindId->cp_count) {
            $this->_result['feedback'] = array(
                'message' => 'Data channel payment terpilih tidak ditemukan'
            );
            $this->result();
            return false;
        }

        if (!$dataFindId->donatur_count) {
            $this->_result['feedback'] = array(
                'message' => 'Data donatur terpilih tidak ditemukan'
            );
            $this->result();
            return false;
        }

        $this->model->getData('nama nama_bantuan, min_donasi','bantuan',array('id_bantuan','=',$decoded['id_bantuan']));
        $dataBantuan = $this->model->data();
        if ($dataBantuan->min_donasi > $decoded['jumlah_donasi']) {
            $this->_result['feedback'] = array(
                'message' => 'Jumlah donasi <span class="font-weight-bold">'. ucwords(strtolower($dataBantuan->nama_bantuan ?? '')) .'</span> minimal '. Output::tSparator($dataBantuan->min_donasi)
            );
            $this->result();
            return false;
        }

        $this->model->getData('nama nama_donatur','donatur',array('id_donatur','=',$decoded['id_donatur']));
        $dataDonatur = $this->model->data();

        $this->model->getData('nama nama_cp, jenis jenis_cp','channel_payment',array('id_cp','=',$decoded['id_cp']));
        $dataCP = $this->model->data();

        // create donasi
        $decoded['bayar'] = 1;
        $create = $this->model->create('donasi', $decoded);
        if (!$create) {
            $this->_result['feedback'] = array(
                'message' => 'Failed to create new donation'
            );
        } else {
            $id_donasi = $this->model->lastIID();
            $this->_result['error'] = false;
            $this->_result['feedback'] = array(
                'message' => 'Donasi <span class="font-weight-bold text-orange">' . (isset($decoded['alias']) ? $decoded['alias'] : $dataDonatur->nama_donatur) . '</span> untuk <span class="font-weight-bold" data-id-target="'. $id_donasi .'">' . $dataBantuan->nama_bantuan . '</span> sejumlah <span class="font-weight-bold" style="display: inline-block;">Rp. '. Output::tSparator($decoded['jumlah_donasi']) .'</span> ('. Utility::keteranganJenisChannelPayment($dataCP->jenis_cp) .' - '. $dataCP->nama_cp .') telah ditambahkan'
            );
            $this->_result['feedback']['id_bantuan'] = $id_donasi;
        }
        
        $this->result();
        return false;
    }

    private function bantuanUpdate($decoded) {
        if (isset($decoded['card_img'])) {
            $dataUrl['gambar_medium'] = $decoded['card_img'];
            $fieldsGambar['gambar_medium'] = array(
                'b.id_gambar_medium',
                'gm.path_gambar path_gambar_medium' 
            );
        }

        if (isset($decoded['wide_img'])) {
            $dataUrl['gambar_wide'] = $decoded['wide_img'];
            $fieldsGambar['gambar_wide'] = array(
                'b.id_gambar_wide',
                'gw.path_gambar path_gambar_wide' 
            );
        }

        $this->model('Bantuan');
        $id_bantuan = $decoded['id_bantuan'];
        unset($decoded['id_bantuan']);
        unset($decoded['token']);

        if (isset($dataUrl)) {
            $uploaded = $this->uploadDataUrlIntoServer($dataUrl);

            $this->_result['uploaded'] = $uploaded;

            if (!$uploaded) {
                $this->_result['feedback'] = array(
                    'message' => 'Terjadi kegagalan upload file',
                );
                $this->result();
                return false;
            }

            $fields = array();

            foreach($fieldsGambar as $key => $value) {
                foreach($value as $field_name) {
                    array_push($fields, $field_name);
                }
            }

            $fields = implode(',', $fields);

            // Get old data gambar
            $this->model->getData($fields, 'bantuan b LEFT JOIN gambar gm ON(b.id_gambar_medium = gm.id_gambar) LEFT JOIN gambar gw ON(b.id_gambar_wide = gw.id_gambar)', array('b.id_bantuan', '=', Sanitize::escape2($id_bantuan)));
            if ($this->model->affected()) {
                $old_id_gambar = array();
                if (isset($dataUrl['gambar_medium'])) {
                    $old_id_gambar['gambar_medium'] = array(
                        'id_gambar' => $this->model->data()->id_gambar_medium,
                        'path_gambar' => $this->model->data()->path_gambar_medium
                    );
                }
                if (isset($dataUrl['gambar_wide'])) {
                    $old_id_gambar['gambar_wide'] = array(
                        'id_gambar' => $this->model->data()->id_gambar_wide,
                        'path_gambar' => $this->model->data()->path_gambar_wide
                    );
                }
            }

            if (count(is_countable($old_id_gambar) ? $old_id_gambar : []) > 0) {
                $loop = 1;
                foreach($old_id_gambar as $key => $value) {
                    if (is_null($value['id_gambar'])) {
                        $this->model->create('gambar', array(
                            'nama' => Sanitize::escape2($this->path_gambar[$key]['name']),
                            'path_gambar' => Sanitize::escape2($this->path_gambar[$key]['path']),
                            'label' => 'bantuan'
                        ));
                        $value['id_gambar'] = $this->model->lastIID();
                        $decoded['id_' . $key] = $value['id_gambar'];
                    } else {
                        $this->model->update('gambar', array(
                            'nama' => Sanitize::escape2($this->path_gambar[$key]['name']),
                            'path_gambar' => Sanitize::escape2($this->path_gambar[$key]['path'])
                        ), array('id_gambar', '=', Sanitize::escape2($value['id_gambar'])));
                        if ($this->model->affected()) {
                            if ($loop == count(is_countable($this->path_gambar) ? $this->path_gambar : [])) {
                                $this->_result['error'] = false;
                                $this->_result['feedback'] = array(
                                    'message' => 'Sucess Update All path_gambar bantuan'
                                );
                            }
                        } else {
                            $this->_result['error'] = true;
                            $this->_result['feedback'] = array(
                                'message' => 'Failed to Update path_gambar bantuan => from ' . $value . ' into ' . $this->path_gambar[$key]['path']
                            );
                            break;
                        }
                    }
                    if (empty($this->path_gambar[$key]['path'])) {
                        continue;
                    }

                    if (empty($value['path_gambar'])) {
                        continue;
                    }
                    // remove old file 
                    $this->removeFile(ROOT . DS . 'public' . DS . $value['path_gambar']);
                    $loop++;
                }
            }
        }

        // Penyesuaian data before update
        $decoded = Sanitize::thisArray($decoded, 'escape3');

        if (isset($decoded['lama_penayangan'])) {
            $decoded['lama_penayangan'] = Sanitize::toInt2($decoded['lama_penayangan']);
        }

        if (isset($decoded['min_donasi'])) {
            $decoded['min_donasi'] = Sanitize::toInt2($decoded['min_donasi']);
        }

        if (isset($decoded['jumlah_target'])) {
            $decoded['jumlah_target'] = Sanitize::toInt2($decoded['jumlah_target']);
        }

        if (isset($decoded['total_rab'])) {
            $decoded['total_rab'] = Sanitize::toInt2($decoded['total_rab']);
        }

        if (isset($decoded['card_img'])) {
            unset($decoded['card_img']);
        }

        if (isset($decoded['wide_img'])) {
            unset($decoded['wide_img']);
        }

        // Update
        if (count(is_countable($decoded) ? $decoded : []) > 0) {
            $this->model->update('bantuan', $decoded, array('id_bantuan', '=', Sanitize::escape2($id_bantuan)));
        
            if (!$this->model->affected()) {
                $this->_result['error'] = true;
                $this->_result['feedback'] = array(
                    'message' => 'Failed to update data bantuan [<span class="font-weight-bold">'. $id_bantuan .'</span>]'
                );
                if ($uploaded) {
                    $this->removePathGambar();
                }
                $this->result();
                return false;
            }
        }

        $this->model->getData('nama','bantuan', array('id_bantuan','=',Sanitize::escape2($id_bantuan)));
        $decoded['nama'] = $this->model->data()->nama;

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'message' => 'Bantuan <span class="font-weight-bold" data-id-target="'. $id_bantuan .'">' . $decoded['nama'] . '</span> berhasil di perbaharui.'
        );
        $this->_result['feedback']['id_bantuan'] = $id_bantuan;

        $count = $this->model->query("SELECT count(*) records FROM bantuan WHERE id_bantuan > ?", array($id_bantuan));
        $halaman = ceil(($count->records + 1) / $this->model->getLimit());
        $this->_result['feedback']['halaman'] = ceil(($count->records + 1) / $this->model->getLimit());

        $this->result();
        return false;
    }

    private function donasiVerivikasi($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        unset($decoded['payment_date']);
        unset($decoded['payment_time']);

        $waktu_bayar = new DateTime(date('Y-m-d', strtotime($decoded['waktu_bayar'])));
        $decoded['waktu_bayar'] = $waktu_bayar->format('Y-m-d H:i:s');

        $this->model('Donasi');
        $this->model->update('donasi', array(
            'bayar' => '1',
            'waktu_bayar' => $decoded['waktu_bayar']
        ), array('id_donasi','=',$decoded['id_donasi']));

        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Gagal melakukan verivikasi donasi'
            );
            $this->result();
            return false;
        }

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'message' => 'Donasi berhasil diverivikasi secara manual',
            'data' => array('id_donasi' => $decoded['id_donasi'])
        );
        $this->result();
        Session::delete('toast');
        return false;
    }

    // Daftar Seluruh Donasi
    private function donasiListRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        if (!isset($decoded['limit'])) {
            $decoded['limit'] = 1;
        }

        if (!isset($decoded['halaman'])) {
            $decoded['halaman'] = 1;
        }

        $this->model('Donasi');

        if (isset($decoded['search'])) {
            $this->model->setSearch($decoded['search']);
        }

        $data = array();

        $this->model->setLimit($decoded['limit']);
        $this->model->setDirection('DESC');
        $this->model->setHalaman($decoded['halaman'], 'donasi');
        $this->model->setOrder('d.create_at');
        $this->model->getListDonasi();

        if ($this->model->affected()) {
            $data = $this->model->data();
        }

        if (!isset($data['data'])) {
            $data['data'] = array();
        }

        if (!isset($data['total_record'])) {
            $data['total_record'] = $this->model->data()['total_record'];
        }

        $pages = ceil($data['total_record']/$decoded['limit']);

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'data' => $data['data'],
            // 'message' => 'ok',
            'pages' => $pages,
            'total_record' => $data['total_record']
        );

        $this->result();

        if ($this->_result['error'] == false) {
            Session::delete('toast');
        }
        return false;
    }

    // Daftar Donasi Per Bantuan
    private function donasiBantuanRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $this->model('Bantuan');
        $this->model->getData('COUNT(id_bantuan) found','bantuan',array('id_bantuan', '=', $decoded['id_bantuan']));
        if (!$this->model->data() > 0) {
            $this->_result['feedback'] = array(
                'message' => 'ID Bantuan Not Found'
            );
            $this->result();
            return false;
        }
        
        if (!isset($decoded['limit'])) {
            $decoded['limit'] = 1;
        }

        if (!isset($decoded['bayar'])) {
            $decoded['bayar'] = 1;
        }

        if (!isset($decoded['halaman'])) {
            $decoded['halaman'] = 1;
        }

        if (isset($decoded['search'])) {
            $this->model->setFilter($decoded['search']);
        }

        $data = array();

        $this->model->setDataLimit($decoded['limit']);
        $this->model->setStatus($decoded['bayar']);
        $this->model->setDataOffsetHalaman($decoded['halaman']);
        $this->model->dataDonasiDonaturBantuan($decoded['id_bantuan']);
        if ($this->model->affected()) {
            $data = $this->model->data();
        }

        $this->model->countRecordDataDonasiDonaturBantuan($decoded['id_bantuan']);
        $records = $this->model->data();

        $pages = ceil($records/$this->model->getDataLimit());
        
        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'data' => $data,
            // 'message' => 'ok',
            'pages' => $pages
        );

        $this->result();

        if ($this->_result['error'] == false) {
            Session::delete('toast');
        }
        return false;
    }

    // Daftar Channel Payment
    private function channelPaymentRead($params = array()) {
        $this->model('Donasi');
        $cp = $this->model->query("SELECT cp.id_cp, cp.nama, cp.jenis, g.path_gambar FROM channel_payment cp LEFT JOIN gambar g USING(id_gambar)");
        if (!$cp) {
            $this->_result['feedback'] = array(
                'message' => 'There is something wrong on the server side'
            );
            $this->result();
            return false;
        }

        $dataCp = $this->model->readAllData();

        $this->_result['error'] = false;        
        $this->_result['feedback'] = array(
            'data' => $dataCp
        );

        $this->result();
        return false;
    }

    public function ajax($params = array()) {
        if (count(is_countable($params) ? $params : []) == 0) {
            $this->_result['feedback'] = array(
                'message' => 'Number of params not found'
            );
            $this->result();
            return false;
        }

        // Check Content Type and decode JSON to array
        $decoded = $this->contentTypeJsonDecoded($_SERVER["CONTENT_TYPE"]);

        // Check Token
        $this->checkToken($decoded['token']);

        switch ($params[0]) {
            case 'bantuan':
                // bantuan Params
            break;

            case 'donatur':
                // donatur Params
            break;
            
            default:
                $this->_result['feedback'] = array(
                    'message' => 'Unrecognize params '. $params[0]
                );
                $this->result();
                return false;
            break;
        }

        // prepare method create name
        $action = $params[0] . 'Read';
        // call method create
        $this->$action($decoded);

        return false;
    }

    private function bantuanRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $search = null;
        $search_columnQ = '';
        $params = array();
        if (!empty($decoded['search'])) {
            $search_value = $decoded['search'];
            $search_column = "LOWER(CONCAT(IFNULL(b.nama, ''),CASE WHEN UPPER(b.status) = 'D' THEN 'berjalan' WHEN UPPER(b.status) = 'S' THEN 'selesai' ELSE '' END)) LIKE LOWER(CONCAT('%',?,'%'))";
            $search_columnQ = "AND {$search_column}";
            array_push($params, $search_value);
            $search = array(
                $search_column,
                $search_value
            );
        }

        if (isset($decoded['offset'])) {
            $offset = $decoded['offset'];
        } else {
            $offset = 0;
        }

        if (isset($decoded['limmit'])) {
            $limmit = $decoded['limmit'];
        } else {
            $limit = 25;
        }

        $this->model('Bantuan');
        $this->model->query("SELECT b.id_bantuan, b.nama nama_bantuan, b.status, IFNULL(b.min_donasi,'') min_donasi FROM bantuan b WHERE b.blokir IS NULL AND UPPER(b.status) IN ('D','S') {$search_columnQ} ORDER BY b.prioritas DESC, b.create_at DESC, b.id_bantuan ASC LIMIT {$offset}, {$limit}", $params);

        $dataBantuan = $this->model->readAllData();

        $count = $this->model->countData('bantuan b','b.blokir IS NULL', $search);
        
        $this->_result['error'] = false;        
        $this->_result['feedback'] = array(
            'data' => $dataBantuan
        );

        if (isset($count)) {
            $this->_result['feedback']['record'] = $count->jumlah_record;
            $this->_result['feedback']['offset'] = $offset;
            $this->_result['feedback']['limit'] = $limit;
            $this->_result['feedback']['load_more'] = ($count->jumlah_record > $offset + $limit);
        }

        if (!is_null($search)) {
            $this->_result['feedback']['search'] = $search_value;
        }

        $this->result();
        return false;
    }

    private function donaturRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $search = null;
        $search_columnQ = '';
        $params = array();
        if (!empty($decoded['search'])) {
            $search_value = $decoded['search'];
            $search_column = "LOWER(CONCAT(IFNULL(d.nama, ''), IFNULL(d.email, ''), IFNULL(d.kontak, ''), IFNULL(d.samaran, ''))) LIKE LOWER(CONCAT('%',?,'%'))";
            $search_columnQ = "WHERE {$search_column}";
            array_push($params, $search_value);
            $search = array(
                $search_column,
                $search_value
            );
        }

        if (isset($decoded['offset'])) {
            $offset = $decoded['offset'];
        } else {
            $offset = 0;
        }

        if (isset($decoded['limmit'])) {
            $limmit = $decoded['limmit'];
        } else {
            $limit = 25;
        }

        $this->model('Donatur');
        $this->model->query("SELECT d.id_donatur, d.nama nama_donatur, IFNULL(d.email,'') email, IFNULL(d.kontak,'') kontak, IFNULL(d.samaran,'Sahabat Berbagi') samaran FROM donatur d {$search_columnQ} ORDER BY d.create_at DESC, d.modified_at DESC, d.id_donatur ASC LIMIT {$offset}, {$limit}", $params);

        $dataBantuan = $this->model->readAllData();

        $count = $this->model->countData('donatur d', null, $search);
        
        $this->_result['error'] = false;        
        $this->_result['feedback'] = array(
            'data' => $dataBantuan
        );

        if (isset($count)) {
            $this->_result['feedback']['record'] = $count->jumlah_record;
            $this->_result['feedback']['offset'] = $offset;
            $this->_result['feedback']['limit'] = $limit;
            $this->_result['feedback']['load_more'] = ($count->jumlah_record > $offset + $limit);
        }

        if (!is_null($search)) {
            $this->_result['feedback']['search'] = $search_value;
        }

        $this->result();
        return false;
    }

    private function donasiGet($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $this->model('Donasi');
        $this->model->getTagihan($decoded['id_donasi']);
        if ($this->model->affected()) {
            $data = $this->model->data();
        }

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'data' => $data
        );

        $this->result();

        if ($this->_result['error'] == false) {
            Session::delete('toast');
        }
        return false;
    }
}