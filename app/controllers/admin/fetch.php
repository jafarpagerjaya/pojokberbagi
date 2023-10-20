<?php 
class FetchController extends Controller {
    private $_result = array('error' => true), $_auth;
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
                    $this->_result['feedback'] = array(
                        'message' => 'Gambar gagal dihapus dari database'
                    );
                } else {
                    $j++;
                }
            }

            if ($j == count(is_countable($this->path_gambar) ? $this->path_gambar : [])) {
                $this->_result['feedback'] = array(
                    'message' => 'Gambar berhasil dihapus dari database dan direktory'
                );
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
        return true;
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
        if (!$this->checkToken($decoded['token'])) { return false; }

        switch ($params[0]) {
            case 'bantuan':
                // bantuanCreate
                if (isset($params[1])) {
                    if ($params[1] == 'deskripsi-selengkapnya') {
                        $params[0] .= 'DeskripsiSelengkapnya';
                        // bantuanDeskripsiSelengkapnyaCreate
                    }
                }
            break;

            case 'donasi':
                // donasiCreate
            break;

            case 'rencana':
                // rencanaCreate
                $decoded = Sanitize::thisArray($decoded['fields']);
            break;

            case 'kebutuhan':
                // kebutuhanCreate
                $decoded = Sanitize::thisArray($decoded['fields']);
            break;

            case 'rencana_anggaran_belanja':
                // rabCreate
                $params[0] = 'rab';
                $decoded = Sanitize::thisArray($decoded['fields']);
            break;

            case 'pelaksanaan':
                if (isset($params[1]) != 'apd' || !isset($params[1])) {
                    $params[0] = 'checkBeforePelaksanaan';
                    // checkBeforePelaksanaanCreate
                }
                // else pelaksanaanCreate
            break;

            case 'pencairan':
                // pencairanCreate
                $decoded = Sanitize::thisArray($decoded['fields']);
            break;

            case 'pinbuk':
                // pinbukCreate
                $decoded = Sanitize::thisArray($decoded['fields']);
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

    public function delete($params = array()) {
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
        if (!$this->checkToken($decoded['token'])) { return false; }

        switch ($params[0]) {
            case 'rencana_anggaran_belanja':
                $params[0] = 'rab';
                // rabDelete
            break;
            
            default:
                $this->_result['feedback'] = array(
                    'message' => 'Unrecognize params '. $params[0]
                );
                $this->result();
                return false;
            break;
        }

        // prepare method delete name
        $action = $params[0] . 'Delete';
        // call method delete
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
        if (!$this->checkToken($decoded['token'])) { return false; }

        switch ($params[0]) {
            case 'bantuan':
                // bantuanUpdate
            break;
            case 'kwitansi':
                // kwitansiUpdate
            break;
            case 'rab':
                // rabUpdate
            break;
            case 'rencana':
                // rencanaUpdate
            break;

            case 'rencana_anggaran_belanja':
                // rabUpdate
                $params[0] = 'rab';
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
        if (!$this->checkToken($decoded['token'])) { return false; }

        switch ($params[0]) {
            case 'donasi':
                // donasi Params
            break;

            case 'channel-payment':
                // donasi Params
                $params[0] = 'channelPayment';
            break;

            case 'rab':
                // rab Params
            break;

            case 'rekalkulasi-penarikan':
                // rekalkulasiPenarikanRead Params
                $params[0] = 'rekalkulasiPenarikan';

                if (isset($params[1])) {
                    // detilPinbukRead Params
                    if ($params[1] == 'detil-penarikan-pinbuk') {
                        unset($params[1]);
                        $params[0] = 'detilPinbuk';
                    }
                }
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
                    // list Params
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

        // prepare method read name
        $action = $params[0] . 'Read';
        // call method read
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
        if (!$this->checkToken($decoded['token'])) { return false; }

        switch ($params[0]) {
            case 'kwitansi':
                // kwitansi Params
            break;

            case 'donasi':
                // donasi Params
            break;

            case 'rencana':
                // rencana Params
                if (isset($params[1])) {
                    if ($params[1] == 'pelaksanaan') {
                        $params[0] .= 'Pelaksanaan';
                    }
                }
            break;

            case 'rab':
                // rab Params
                if (isset($params[1])) {
                    if ($params[1] == 'detil') {
                        $params[0] .= 'Detil';
                    } else if ($params[1] == 'for-delete') {
                        $params[0] .= 'ForDelete';
                    }
                }
            break;

            case 'kebutuhan':
                // rab Params
                if (isset($params[1])) {
                    if ($params[1] == 'cek') {
                        $params[0] .= 'Cek';
                    }
                }
            break;

            case 'kalkulasi':
                if (isset($params[1])) {
                    if ($params[1] == 'penarikan-channel-account') {
                        $params[0] .= 'PenarikanCa';
                    }
                }
                // kalkulasiPenarikanCaGet Params
            break;
            
            default:
                $this->_result['feedback'] = array(
                    'message' => 'Unrecognize params '. $params[0]
                );
                $this->result();
                return false;
            break;
        }

        // prepare method Get
        $action = $params[0] . 'Get';
        // call method Get
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

    private function uploadDataUrlIntoServer($params = array(), $path_dir = 'bantuan', $file_name = '') {
        if (count(is_countable($params) ? $params : []) == 0) {
            return false;
        }

        $i = 0;

        $arrayPath = array();

        foreach($params as $directory_name => $fileImg) {

            $fileImg = str_replace('data:image/jpeg;base64,', '', $fileImg);
            $fileImg = str_replace(' ', '+', $fileImg);
            $fileData = base64_decode($fileImg);

            $upload_directory = BASEURL . "uploads" . DS . "images" . DS . "{$path_dir}" . DS . $directory_name;

            if (!is_dir($upload_directory)) {
                mkdir($upload_directory, 0777, $rekursif = true);
            }

            $path_gambar = $upload_directory. DS . time() . '-' . $directory_name .''. $file_name . '.jpeg';

            $uploaded = file_put_contents($path_gambar, $fileData);

            if (!$uploaded) {
                break;
            } else {
                $path_gambar = "/uploads/images/{$path_dir}/" . $directory_name . "/" . time() . "-" . $directory_name .''. $file_name . ".jpeg";
                $arrayPath[$directory_name] = array(
                    'name' => time() . '-' . $directory_name .''. $file_name,
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
            'feedback' => $this->_result['feedback'],
            'data_toast' => 'feedback',
            'id' => Hash::unique()
        );
        $this->_result['toast'] = $toast;
        if (array_key_exists('message', $toast['feedback'])) {
            if (!$this->_result['error']) {
                Session::put('toast', $toast);
            }
        }
        echo json_encode($this->_result);
    }

    private function bantuanDeskripsiSelengkapnyaCreate($decoded) {
        $decoded = Sanitize::thisArray($decoded['deskripsi']);

        if (empty($decoded['id_bantuan']) || !isset($decoded['id_bantuan'])) {
            $this->_result['feedback'] = array(
                'message' => 'Id bantuan selengkapnya wajib diisi'
            );
            $this->result();
            return false;
        }

        if (empty($decoded['judul']) || !isset($decoded['judul'])) {
            $this->_result['feedback'] = array(
                'message' => 'Judul selengkapnya wajib diisi'
            );
            $this->result();
            return false;
        }

        
        $this->model('Bantuan');
        $this->model->countData('bantuan',array('id_bantuan = ?', Sanitize::escape2($decoded['id_bantuan'])));
        if ($this->model->data()->jumlah_record < 1) {
            $this->_result['feedback'] = array(
                'message' => 'Id Bantuan tidak ditemukan'
            );
            $this->result();
            return false;
        }

        $content = $decoded['isi'];
        
        $counterImg = 1;
        $array_id_gambar = array();

        foreach ($content['ops'] as $key => $value) {
            if (is_array($value)) {
                if (array_key_exists('insert', $value)) {
                    foreach($value as $keyInsert => $insert) {
                        if (is_array($insert)) {
                            if (array_key_exists('image', $insert)) {
                                
                                $dataUrl = array(
                                    'deskripsi' => $insert['image']
                                );

                                $name_to_add = '-selengkapnya-' . $counterImg;
                        
                                $uploaded = $this->uploadDataUrlIntoServer($dataUrl, 'bantuan', $name_to_add);
                        
                                $this->_result['uploaded'] = $uploaded;
                        
                                if (!$uploaded) {
                                    $this->_result['feedback'] = array(
                                        'message' => 'Terjadi kegagalan upload file',
                                    );
                                    $this->result();
                                    return false;
                                }

                                if (count(is_countable($this->path_gambar) ? $this->path_gambar : []) > 0) {
                                    $this->_result['error'] = true;
                                    $this->model->create('gambar', array(
                                        'nama' => Sanitize::escape2($this->path_gambar['deskripsi']['name']), 
                                        'path_gambar' => Sanitize::escape2($this->path_gambar['deskripsi']['path']),
                                        'label' => 'deskripsi'
                                    ));
                                    if ($this->model->affected()) {

                                            $array_id_gambar[$counterImg] = array(
                                                'id_gambar' => $this->model->lastIID(),
                                                'array_path' => $this->path_gambar['deskripsi']
                                            );

                                            $this->_result['error'] = false;
                                            $this->_result['feedback'] = array(
                                                'message' => 'Sucess Insert All path_gambar artikel deskripsi'
                                            );
                                            
                                            // reset data menjadi path gambar
                                            $content['ops'][$key]['insert']['image'] = $this->path_gambar['deskripsi']['path'];

                                            $counterImg++;

                                    } else {
                                        $this->_result['feedback'] = array(
                                            'message' => 'Failed to INSERT path_gambar artikel deskripsi => ' . $this->path_gambar['deskripsi']['name']
                                        );
                                        break;
                                    }
                                }

                                if ($this->_result['error'] == true) {
                                    if (count(is_countable($array_id_gambar) ? $array_id_gambar : []) > 0) {
                                        foreach($array_id_gambar as $key => $gambar) {
                                            $this->path_gambar = $gambar['array_path'];
                                            $this->removePathGambar();
                                        }
                                    }
                                    $this->result();
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->_result['error'] = true;

        $decoded['isi'] = json_encode($content);
                
        $this->model->create('deskripsi', $decoded);

        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Terjadi kesalahan create deskripsi'
            );
            if (count(is_countable($array_id_gambar) ? $array_id_gambar : []) > 0) {
                foreach($array_id_gambar as $key => $gambar) {
                    $this->path_gambar = $gambar['array_path'];
                    $this->removePathGambar();
                }
            }
            $this->result();
            return false;
        }

        $new_id_deskripsi_selengkapnya = $this->model->lastIID();

        foreach($array_id_gambar as $key => $value) {
            $this->model->create('list_gambar_deskripsi', array('id_deskripsi' => Sanitize::escape2($new_id_deskripsi_selengkapnya), 'id_gambar' => Sanitize::escape2($value['id_gambar'])));
            if (!$this->model->affected()) {
                $this->_result['error'] = true;
                $this->_result['feedback'] = array(
                    'message' => 'Failed to INSERT list_gambar_deskripsi id_gambar => ' . $value['id_gambar']
                );
                break;
            }
            $this->_result['error'] = false;
        }
        
        if ($this->_result['error'] == true) {
            if (count(is_countable($array_id_gambar) ? $array_id_gambar : []) > 0) {
                foreach($array_id_gambar as $key => $gambar) {
                    $this->path_gambar = $gambar['array_path'];
                    $this->removePathGambar();
                }
                $this->_result['feedback'] = array(
                    'message' => 'Failed to create list_gambar_deskripsi, gambar langsung dihapus'
                );
                $this->result();
                return false;
            }
        }
        
        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'message' => 'Berhasil menambahkan data selengkapnya (<span class="font-weight-bold" data-id-target="'. $new_id_deskripsi_selengkapnya .'">#' . $decoded['judul'] . '</span>)'
        );
        $this->result();
        return false;
    }

    private function pinbukCreate($decoded) {

        if (!isset($decoded['id_ca_pengirim'])) {
            $this->_result['feedback'] = array(
                'message' => 'Id ca pengirim wajib ada'
            );
            $this->result();
            return false;
        }

        if (!isset($decoded['id_ca_penerima'])) {
            $this->_result['feedback'] = array(
                'message' => 'Id ca penerima wajib ada'
            );
            $this->result();
            return false;
        }

        if (!isset($decoded['nominal_pinbuk'])) {
            $this->_result['feedback'] = array(
                'message' => 'Nominal pinbuk wajib diisi'
            );
            $this->result();
            return false;
        }

        if (!isset($decoded['id_ca_pengirim'])) {
            $this->_result['feedback'] = array(
                'message' => 'Id ca wajib ada'
            );
            $this->result();
            return false;
        }

        if (!isset($decoded['id_pelaksanaan'])) {
            $this->_result['feedback'] = array(
                'message' => 'Failed to pinbuk, Id pelaksanaan wajib disertakan'
            );
            $this->result();
            return false;
        };

        unset($decoded['max_pinbuk']);
        $decoded = Config::replaceKey($decoded, 'nominal_pinbuk', 'total_pinbuk');

        $this->model('Pencairan');
        if (!isset($decoded['id_gambar'])) {
            try {
                $this->model->create('pinbuk', $decoded);
            } catch (\Throwable $th) {
                $pesan = explode(':',$th->getMessage());
                $this->_result['feedback'] = array(
                    'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
                );
                $this->result();
                return false;
            }
            $new_id_pinbuk = $this->model->lastIID();
            $pesan = "Pinbuk berhasil dibuat dengan ID = <b>{$new_id_pinbuk}</b>, mohon untuk segera verivikasi hasil pinbuk";
        } else {
            try {

                $dataUrl = array(
                    'bukti' => $decoded['id_gambar']
                );
        
                $uploaded = $this->uploadDataUrlIntoServer($dataUrl, 'pinbuk');
        
                $this->_result['uploaded'] = $uploaded;
        
                if (!$uploaded) {
                    $this->_result['feedback'] = array(
                        'message' => 'Terjadi kegagalan upload file',
                    );
                    $this->result();
                    return false;
                }
        
                $array_id_gambar = array();

                $loop = 1;
                foreach($this->path_gambar as $key => $value) {
                    $this->model->create('gambar', array(
                        'nama' => Sanitize::escape2($value['name']), 
                        'path_gambar' => Sanitize::escape2($value['path']),
                        'label' => 'pinbuk'
                    ));
                    if ($this->model->affected()) {
                        $array_id_gambar[$key] = $this->model->lastIID();
                        if ($loop == count(is_countable($this->path_gambar) ? $this->path_gambar : [])) {
                            $this->_result['error'] = false;
                            $this->_result['feedback'] = array(
                                'message' => 'Sucess Insert All path_gambar bukti pinbuk'
                            );
                        }
                    } else {
                        $this->_result['feedback'] = array(
                            'message' => 'Failed to INSERT path_gambar bukti pinbuk => ' . $value
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

                $this->_result['error'] = true;

                unset($decoded['bukti']);
                unset($decoded['token']);

                $decoded['id_bukti_pinbuk'] = $array_id_gambar['bukti'];

                // Entah $this->_result['path_gambar'] dipakai atau engga nantinya
                $this->_result['path_gambar'] = $this->path_gambar;
            } catch (\Throwable $th) {
                $pesan = explode(':',$th->getMessage());
                $this->_result['feedback'] = array(
                    'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
                );
                $this->result();
                return false;
            }

            if (!isset($decoded['id_bukti_pinbuk'])) {
                $this->removePathGambar();
                $this->result();
                return false;
            }

            try {
                $this->model->query('CALL pinbuk(?, ?, ?, ?, ?, ?, ?)', array(
                    $decoded['total_pinbuk'],
                    $decoded['id_ca_pengirim'], 
                    $decoded['id_ca_penerima'], 
                    $decoded['keterangan'], 
                    $decoded['id_bantuan'], 
                    $decoded['id_bukti_pinbuk'], 
                    0
                ));
            } catch (\Throwable $th) {
                $pesan = explode(':',$th->getMessage());
                $this->_result['feedback'] = array(
                    'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
                );
                $this->result();
                return false;
            }

            $pesan = $this->model->getResult()->MESSAGE_TEXT;
        }

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'message' => $pesan
        );
        $this->result();
        return false;
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
            'medium' => $decoded['card_img'],
            'wide' => $decoded['wide_img']
        );

        $uploaded = $this->uploadDataUrlIntoServer($dataUrl, 'bantuan');

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

        $decoded['id_gambar_medium'] = $array_id_gambar['medium'];
        $decoded['id_gambar_wide'] = $array_id_gambar['wide'];

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
        if ($this->model->getResult()->found_nama >= 1) {
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
                $id_gambar = $this->model->getResult()->id_gambar;
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

        $dateDiff = strtotime($decoded['waktu_bayar']) - strtotime(date("Y-m-d"));
        $difference = floor($dateDiff/(60*60*24));
        // waktu_bayar = hari ini
        if ($difference == '0') {
            $decoded['waktu_bayar'] = date('Y-m-d H:i:s');
        } else {
            $create_at = new DateTime(date('Y-m-d H:i:s', strtotime($decoded['waktu_bayar'])));
            $decoded['create_at'] = $create_at->format('Y-m-d H:i:s');
        }

        $waktu_bayar = new DateTime(date('Y-m-d H:i:s', strtotime($decoded['waktu_bayar'])));
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
        $dataBantuan = $this->model->getResult();
        if ($dataBantuan->min_donasi > $decoded['jumlah_donasi']) {
            $this->_result['feedback'] = array(
                'message' => 'Jumlah donasi <span class="font-weight-bold">'. ucwords(strtolower($dataBantuan->nama_bantuan ?? '')) .'</span> minimal '. Output::tSparator($dataBantuan->min_donasi)
            );
            $this->result();
            return false;
        }

        $this->model->getData('nama nama_donatur','donatur',array('id_donatur','=',$decoded['id_donatur']));
        $dataDonatur = $this->model->getResult();

        $this->model->getData('nama nama_cp, jenis jenis_cp','channel_payment',array('id_cp','=',$decoded['id_cp']));
        $dataCP = $this->model->getResult();

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

    private function rencanaCreate($decoded) {
        if (!isset($decoded['id_bantuan'])) {
            $this->_result['feedback'] = array(
                'message' => 'Program bantuan wajib dipilih'
            );
            $this->result();
            return false;
        }

        $this->model('Auth');
        $hasil = $this->model->getData('adm.id_pegawai, p.id_jabatan, p.id_atasan','akun JOIN admin adm USING(id_akun) JOIN pegawai p USING(id_pegawai)',array('id_akun','=', $this->model->data()->id_akun));
        if ($hasil) {
            $decoded['id_pegawai'] = $this->model->data()->id_pegawai;
        }

        if (!is_null($this->model->data()->id_atasan) && ($this->model->data()->id_jabatan != 3 || $this->model->data()->id_jabatan != 6)) {
            $this->_result['feedback'] = array(
                'message' => 'Anda tidak memiliki akses membuat RAB'
            );
            $this->result();
            return false;
        }

        $this->model('Bantuan');
        $this->model->countData('bantuan',array('id_bantuan = ?', $decoded['id_bantuan']));
        if ($this->model->data()->jumlah_record < 1) {
            $this->_result['feedback'] = array(
                'message' => 'Id Bantuan tidak ditemukan'
            );
            $this->result();
            return false;
        }
        
        $create = $this->model->create('rencana', $decoded);
        if (!$create) {
            $this->_result['feedback'] = array(
                'message' => 'Failed to create new rencana'
            );
        } else {
            $id_rencana = $this->model->lastIID();
            $this->_result['error'] = false;
            $this->_result['feedback'] = array(
                'message' => 'Rencana baru berhasil ditambahkan'
            );
            $this->_result['feedback']['id_rencana'] = $id_rencana;
            $this->_result['feedback']['input'] = $decoded;
        }
        
        $this->result();
        return false;
    }

    private function kebutuhanCreate($decoded) {
        if (!isset($decoded['id_kk'])) {
            $this->_result['feedback'] = array(
                'message' => 'Kategori bantuan wajib dipilih'
            );
            $this->result();
            return false;
        }

        $this->model('Kebutuhan');
        $this->model->countData('kategori_kebutuhan',array('id_kk = ?', $decoded['id_kk']));
        if ($this->model->data()->jumlah_record < 1) {
            $this->_result['feedback'] = array(
                'message' => 'Id kategori tidak ditemukan'
            );
            $this->result();
            return false;
        }

        $this->model->getData('COUNT(id_kebutuhan) jumlah_record','kebutuhan', array('nama','=',$decoded['nama']));
        if ($this->model->getResult()->jumlah_record > 0) {
            $this->_result['feedback'] = array(
                'message' => 'Kebutuhan sudah ada'
            );
            $this->result();
            return false;
        }
        
        $create = $this->model->create('kebutuhan', $decoded);
        if (!$create) {
            $this->_result['feedback'] = array(
                'message' => 'Failed to create new kebutuhan'
            );
        } else {
            $id_rencana = $this->model->lastIID();
            $this->_result['error'] = false;
            $this->_result['feedback'] = array(
                'message' => 'Kebutuhan baru berhasil ditambahkan'
            );
            $this->_result['feedback']['id_rencana'] = $id_rencana;
            $this->_result['feedback']['input'] = $decoded;
        }
        
        $this->result();
        return false;
    }

    private function rabCreate($decoded) {
        if (!isset($decoded['id_rencana'])) {
            $this->_result['feedback'] = array(
                'message' => 'Id rencana tidak ditemukan'
            );
            $this->result();
            return false;   
        }
        
        if (!isset($decoded['id_kebutuhan'])) {
            $this->_result['feedback'] = array(
                'message' => 'Kebutuhan item rab wajib dipilih'
            );
            $this->result();
            return false;   
        }

        if (!isset($decoded['harga_satuan'])) {
            $this->_result['feedback'] = array(
                'message' => 'Harga satuan item rab wajib diisi'
            );
            $this->result();
            return false;   
        }

        if (!isset($decoded['jumlah'])) {
            $this->_result['feedback'] = array(
                'message' => 'Jumlah item rab wajib diisi'
            );
            $this->result();
            return false;   
        }

        if (!isset($decoded['keterangan'])) {
            $this->_result['feedback'] = array(
                'message' => 'Keterangan/Spec item bantuan wajib diisi'
            );
            $this->result();
            return false;   
        }

        $decoded['harga_satuan'] = Sanitize::toInt2($decoded['harga_satuan']);
        $decoded['jumlah'] = Sanitize::toInt2($decoded['jumlah']);

        $decoded['nominal_kebutuhan'] = $decoded['jumlah'] * $decoded['harga_satuan'];

        $this->model('Rab');
        $this->model->query('SELECT COUNT(rab.id_rab) jumlah_record, k.nama FROM rencana_anggaran_belanja rab JOIN kebutuhan k ON(k.id_kebutuhan = rab.id_kebutuhan) WHERE rab.id_kebutuhan = ? AND rab.keterangan = ? AND rab.id_rencana = ?', array('id_kebutuhan' => $decoded['id_kebutuhan'], 'keterangan' => $decoded['keterangan'], 'id_rencana' => $decoded['id_rencana']));
        if ($this->model->getResult()->jumlah_record > 0) {
            $this->_result['feedback'] = array(
                'message' => '<b>' . $this->model->getResult()->nama . '</b> dengan spec <b>' . $decoded['keterangan'] . '</b> sudah ada dalam daftar RAB'
            );
            $this->result();
            return false;
        }

        try {
            $decoded['pengirim'] = 'I';
            $this->model->query("Call InsertRAB(?,?,?,?,?,?, @id_rab_baru)", 
                array(
                    $decoded['id_rencana'],
                    $decoded['id_kebutuhan'],
                    $decoded['harga_satuan'],
                    $decoded['jumlah'],
                    $decoded['keterangan'],
                    $decoded['pengirim']
                )
            );
        } catch (\Throwable $th) {
            $pesan = explode(':',$th->getMessage());
            $this->_result['feedback'] = array(
                'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
            );
            $this->result();
            return false;
        }

        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed to create new kebutuhan'
            );
            $this->result();
            return false;
        }

        $pesan = $this->model->getResult()->MESSAGE_TEXT;
        $this->model->query('SELECT @id_rab_baru id_rab');
        $decoded['id_rab'] = $this->model->getResult()->id_rab;

        $this->model->query('SELECT SUM(nominal_kebutuhan) sum_nominal_kebutuhan, FormatTanggalFull(r.modified_at) modified_at, r.status FROM rencana_anggaran_belanja rab JOIN rencana r USING(id_rencana) WHERE id_rencana = ? GROUP BY id_rencana', array('id_rencana' => Sanitize::escape2($decoded['id_rencana'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed get sum_nominal_kebutuhan'
            );
            $this->result();
            return false;
        }

        $decoded['total_rab'] = (int) $this->model->getResult()->sum_nominal_kebutuhan;
        $decoded['modified_at'] = $this->model->getResult()->modified_at;
        $decoded['status'] = json_decode(json_encode((object) Utility::statusRencanaText($this->model->getResult()->status)), FALSE);

        $this->model->query("WITH cte AS (
            SELECT r.id_bantuan, pl.id_pelaksanaan, id_rencana 
            FROM rencana r JOIN bantuan b USING(id_bantuan) LEFT JOIN pelaksanaan pl USING(id_rencana) 
            WHERE id_rencana = ? AND b.blokir IS NULL AND UPPER(b.status) = 'D'
        ) 
        SELECT id_bantuan, SUM(IFNULL(nominal_penggunaan_donasi, 0)) total_teranggarkan, IFNULL(max_anggaran,0) max_anggaran 
        FROM cte 
        LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_pelaksanaan)
        LEFT JOIN
        (
            SELECT l.id_bantuan, SUM(saldo) max_anggaran FROM (
                SELECT d.id_bantuan, d.id_donasi, IFNULL(MIN(apd.saldo_donasi), d.jumlah_donasi) saldo
                FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_donasi) JOIN cte ON(cte.id_bantuan = d.id_bantuan)
                LEFT JOIN pelaksanaan pl ON(pl.id_pelaksanaan = apd.id_pelaksanaan)
                GROUP BY d.id_donasi
                HAVING saldo > 0
            ) l
            GROUP BY l.id_bantuan
        ) m USING(id_bantuan)
        GROUP BY m.max_anggaran", array('id_rencana' => Sanitize::escape2($decoded['id_rencana'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed get max_saldo_anggaran after delete RAB'
            );
            $this->result();
            return false;
        }

        $decoded['max_anggaran'] = (int) $this->model->getResult()->max_anggaran;
        $decoded['total_teranggarkan'] = (int) $this->model->getResult()->total_teranggarkan;

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'data' => $decoded,
            'message' => $pesan
        );
        $this->result();
        return false;
    }

    private function checkBeforePelaksanaanCreate($decoded) {
        if (count(is_countable($decoded['fields']) ? $decoded['fields'] : []) < 1) {
            $this->_result['feedback'] = array(
                'message' => 'Daftar fields tidak ditemukan'
            );
            $this->result();
            return false;   
        }
        $decoded['fields'] = Sanitize::thisArray($decoded['fields']);
        
        if (!isset($decoded['rab'])) {
            $this->_result['feedback'] = array(
                'message' => 'Daftar Rab wajib dianggarkan'
            );
            $this->result();
            return false;   
        }

        if (!is_array($decoded['rab'])) {
            $this->_result['feedback'] = array(
                'message' => 'Daftar Rab tidak ditemukan dalam array rab'
            );
            $this->result();
            return false;   
        }

        if (count(is_countable($decoded['rab']) ? $decoded['rab'] : []) < 1) {
            $this->_result['feedback'] = array(
                'message' => 'Daftar Rab wajib dipilih'
            );
            $this->result();
            return false;   
        }

        $decoded['rab'] = Sanitize::thisArray($decoded['rab']);

        $this->model('Pelaksanaan');
        $this->model->query("WITH cte AS (
            SELECT r.id_bantuan, pl.id_pelaksanaan, id_rencana, r.total_anggaran
            FROM rencana r JOIN bantuan b USING(id_bantuan) LEFT JOIN pelaksanaan pl USING(id_rencana) 
            WHERE id_rencana = ? AND b.blokir IS NULL AND UPPER(b.status) = 'D'
        ) 
        SELECT id_bantuan, cte.total_anggaran, SUM(IFNULL(nominal_penggunaan_donasi, 0)) total_teranggarkan, IFNULL(max_anggaran,0) max_anggaran 
        FROM cte 
        LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_pelaksanaan)
        LEFT JOIN
        (
            SELECT l.id_bantuan, SUM(saldo) max_anggaran FROM (
                SELECT d.id_bantuan, d.id_donasi, IFNULL(MIN(apd.saldo_donasi), d.jumlah_donasi) saldo
                FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_donasi) JOIN cte ON(cte.id_bantuan = d.id_bantuan)
                LEFT JOIN pelaksanaan pl ON(pl.id_pelaksanaan = apd.id_pelaksanaan)
                GROUP BY d.id_donasi
                HAVING saldo > 0
            ) l
            GROUP BY l.id_bantuan
        ) m USING(id_bantuan)
        GROUP BY m.max_anggaran", array('id_rencana' => Sanitize::escape2($decoded['fields']['id_rencana'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed get max_saldo_anggaran rencanaPelaksanaanGet'
            );
            $this->result();
            return false;
        }

        $data['total_anggaran'] = $this->model->getResult()->total_anggaran;
        $data['saldo_total_rab'] = ($this->model->getResult()->total_anggaran - $this->model->getResult()->total_teranggarkan);
        $data['saldo_anggaran'] = $this->model->getResult()->max_anggaran;

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'data' => $data
        );
        $this->result();
        return false;
    }

    private function pelaksanaanCreate($decoded) {
        if (count(is_countable($decoded['fields']) ? $decoded['fields'] : []) < 1) {
            $this->_result['feedback'] = array(
                'message' => 'Daftar fields tidak ditemukan'
            );
            $this->result();
            return false;   
        }
        $decoded['fields'] = Sanitize::thisArray($decoded['fields']);
        
        if (!isset($decoded['rab'])) {
            $this->_result['feedback'] = array(
                'message' => 'Daftar Rab wajib dianggarkan'
            );
            $this->result();
            return false;   
        }

        if (!is_array($decoded['rab'])) {
            $this->_result['feedback'] = array(
                'message' => 'Daftar Rab tidak ditemukan dalam array rab'
            );
            $this->result();
            return false;   
        }

        if (count(is_countable($decoded['rab']) ? $decoded['rab'] : []) < 1) {
            $this->_result['feedback'] = array(
                'message' => 'Daftar Rab wajib dipilih'
            );
            $this->result();
            return false;   
        }

        $decoded['rab'] = Sanitize::thisArray($decoded['rab']);
        
        $this->model('Auth');
        $hasil = $this->model->getData('adm.id_pegawai, p.id_jabatan, p.id_atasan','akun JOIN admin adm USING(id_akun) JOIN pegawai p USING(id_pegawai)',array('id_akun','=', $this->model->data()->id_akun));
        if ($hasil) {
            $pegawai = $this->model->data();
        }

        $this->model('Pelaksanaan');
        if (is_null($pegawai->id_atasan) || $pegawai->id_jabatan == 2) {
            $this->model->getData('status','rencana',array('id_rencana', '=', Sanitize::escape2($decoded['fields']['id_rencana'])));
            if ($this->model->getResult()->status != 'SD') {
                $this->model->update('rencana', array('status' => 'SD'), array('id_rencana', '=', Sanitize::escape2($decoded['fields']['id_rencana'])));
                if (!$this->model->affected()) {
                    $this->_result['feedback'] = array(
                        'message' => 'Failed to update status rencana on create pelaksanaan'
                    );
                    $this->result();
                    return false;
                }
            }
        }

        $mode = null;
        try {
            $this->model->query("CALL check_table_exists('tempSelectedRAB', @OUT)");
            try {
                $this->model->query("SELECT @OUT table_exists");
                $exists = $this->model->getResult()->table_exists;
                if ($exists == 1) {
                    $mode = 'insert';
                } else if ($exists == 0) {
                    $mode = 'create';
                } else {
                    throw new Exception('check_table_exists Failed');
                }
            } catch (Exception $e){
                throw new Exception( 'Unable to Map Table tempSelectedRAB', 0, $e);
            }
        } catch (\Throwable $th) {
            $pesan = $e->getMessage();
            $prev_pesan = implode(' ', $e->getPrevious());
            $this->_result['feedback'] = array(
                'message' => '<b>'. $pesan .'</b> '. $prev_pesan
            );
            $this->result();
            return false;
        }

        if ($mode == 'create') {
            $sql = "CREATE TEMPORARY TABLE tempSelectedRAB SELECT id_rab, id_rencana FROM rencana_anggaran_belanja WHERE id_rab IN(";
            $xCol = 1;
            foreach ($decoded['rab'] as $questionMark) {
                $sql .= "?";
                if ($xCol < count(is_countable($decoded['rab']) ? $decoded['rab'] : [])) {
                    $sql .= ", ";
                }
                $xCol++;
            }
            $sql .= ")";
            $this->model->query($sql, $decoded['rab']);
            if (!$this->model->affected()) {
                $this->_result['feedback'] = array(
                    'message' => 'Faled to create pelaksanaan'
                );
                $this->result();
                return false;
            }
        } else if ($mode == 'insert') {
            $hasil = $this->model->countData('tempSelectedRAB', array('id_rencana = ?', $decoded['fields']['id_rencana']));
            if ($hasil->jumlah_record > 0) {
                $this->model->delete('tempSelectedRAB', array('id_rencana' => $decoded['fields']['id_rencana']));
                if (!$this->model->affected()) {
                    $this->_result['feedback'] = array(
                        'message' => 'Faled to create pelaksanaan'
                    );
                    $this->result();
                    return false;
                }
            }

            $sql = "INSERT INTO tempSelectedRAB SELECT id_rab, id_rencana FROM rencana_anggaran_belanja WHERE id_rencana = ? AND id_rab IN (";
            $xCol = 1;
            foreach ($decoded['rab'] as $questionMark) {
                $sql .= "?";
                if ($xCol < count(is_countable($decoded['rab']) ? $decoded['rab'] : [])) {
                    $sql .= ", ";
                }
                $xCol++;
            }
            $sql .= ")";
            $this->model->query($sql, array_merge(array($decoded['fields']['id_rencana']), $decoded['rab']));
            if (!$this->model->affected()) {
                $this->_result['feedback'] = array(
                    'message' => 'Faled to insert tempSelectedRAB'
                );
                $this->result();
                return false;
            }
        }

        if (isset($decoded['fields']['jumlah_pelaksanaan'])) {
            $decoded['fields']['jumlah_pelaksanaan'] = Sanitize::toInt2($decoded['fields']['jumlah_pelaksanaan']);
        }

        $decoded['fields']['tanggal_pelaksanaan'] = strtotime($decoded['fields']['tanggal_pelaksanaan']); 
        $decoded['fields']['tanggal_pelaksanaan'] = date("Y-m-d", $decoded['fields']['tanggal_pelaksanaan']);
        $decoded['fields'] = Config::replaceKey($decoded['fields'], 'tanggal_pelaksanaan', 'tanggal_eksekusi');

        try {
            $this->model->create('pelaksanaan', $decoded['fields']);
        } catch (\Throwable $th) {
            $pesan = explode(':',$th->getMessage());
            $this->_result['feedback'] = array(
                'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
            );
            $this->result();
            return false;
        }

        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Faled to create pelaksanaan'
            );
            $this->result();
            return false;
        }

        $new_id_pelaksanaan = $this->model->lastIID();

        try {
            $this->model->query("CALL TotalAggaranBantuanPelaksanaan(?)", array($new_id_pelaksanaan));
        } catch (\Throwable $th) {
            $pesan = explode(':',$th->getMessage());
            $this->_result['feedback'] = array(
                'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
            );
            $this->result();
            return false;
        }

        $pesan = $this->model->getResult()->MESSAGE_TEXT;

        $this->model->query("WITH cte AS (
            SELECT r.id_bantuan, pl.id_pelaksanaan, id_rencana, r.total_anggaran total_anggaran_r, pl.total_anggaran total_anggaran_pl
            FROM rencana r JOIN bantuan b USING(id_bantuan) LEFT JOIN pelaksanaan pl USING(id_rencana) 
            WHERE id_rencana = ? AND b.blokir IS NULL AND UPPER(b.status) = 'D'
        ) 
        SELECT id_bantuan, cte.total_anggaran_r, cte.total_anggaran_pl, SUM(IFNULL(nominal_penggunaan_donasi, 0)) total_teranggarkan, IFNULL(max_anggaran,0) max_anggaran 
        FROM cte 
        LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_pelaksanaan)
        LEFT JOIN
        (
            SELECT l.id_bantuan, SUM(saldo) max_anggaran FROM (
                SELECT d.id_bantuan, d.id_donasi, IFNULL(MIN(apd.saldo_donasi), d.jumlah_donasi) saldo
                FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_donasi) JOIN cte ON(cte.id_bantuan = d.id_bantuan)
                LEFT JOIN pelaksanaan pl ON(pl.id_pelaksanaan = apd.id_pelaksanaan)
                GROUP BY d.id_donasi
                HAVING saldo > 0
            ) l
            GROUP BY l.id_bantuan
        ) m USING(id_bantuan)
        GROUP BY m.max_anggaran, cte.id_pelaksanaan", array('id_rencana' => Sanitize::escape2($decoded['fields']['id_rencana'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed get max_saldo_anggaran rencanaPelaksanaanGet'
            );
            $this->result();
            return false;
        }

        $data['saldo_total_rab'] = ($this->model->getResult()->total_anggaran_r - $this->model->getResult()->total_teranggarkan);
        $data['saldo_anggaran'] = $this->model->getResult()->max_anggaran;
        $data['total_anggaran'] = $this->model->getResult()->total_anggaran_pl;
        $data['id_pelaksanaan'] = $new_id_pelaksanaan;

        $this->model->query("SELECT COUNT(id_rencana)+1 urutan_pencairan FROM pelaksanaan JOIN penarikan USING(id_pelaksanaan) WHERE id_rencana = ?", array('id_rencana' => Sanitize::escape2($decoded['fields']['id_rencana'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed to get count pencairan'
            );
            $this->result();
            return false;
        }

        $data['urutan_pencairan'] = $this->model->getResult()->urutan_pencairan;

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'data' => $data,
            'message' => $pesan
        );
        $this->result();
        return false;
    }

    private function pencairanCreate($decoded) {
        if (count(is_countable($decoded['petugas_pencairan']) ? $decoded['petugas_pencairan'] : []) < 1) {
            $this->_result['feedback'] = array(
                'message' => 'Daftar petugas pencairan tidak ditemukan'
            );
            $this->result();
            return false;   
        }

        $this->model('Pencairan');
        $this->model->create('pencairan', array(
            'total' => $decoded['total'],
            'keterangan' => $decoded['keterangan']
        ));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed to create pencairan'
            );
            $this->result();
            return false; 
        }

        $new_id_pencairan = $this->model->lastIID();
        $params = array();
        $petugas = "(";
        $xCol = 1;
        foreach ($decoded['petugas_pencairan'] as $questionMark => $value) {
            $petugas .= "?";
            if ($xCol < count(is_countable($decoded['petugas_pencairan']) ? $decoded['petugas_pencairan'] : [])) {
                $petugas .= ", ";
            }
            $xCol++;
            array_push($params, $value);
        }
        $petugas .= ")";

        $this->model->query("INSERT INTO petugas_pencairan(id_pencairan, id_petugas) SELECT {$new_id_pencairan}, id_pegawai FROM pegawai WHERE id_pegawai IN {$petugas}", $params);
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed to create petugas_pencairan'
            );
            $this->result();
            return false; 
        }

        try {
            $this->model->query("CALL KalkulasiPenarikan(?,?,?)", 
                array(
                    $decoded['id_pelaksanaan'], 
                    $decoded['persentase_penarikan'], 
                    $new_id_pencairan
                )
            );
        } catch (\Throwable $th) {
            $pesan = explode(':',$th->getMessage());
            $this->_result['feedback'] = array(
                'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
            );
            $this->result();
            return false;
        }

        $data = array(
            'id_pencairan' => $new_id_pencairan,
            'id_pelaksanaan' => $decoded['id_pelaksanaan'],
            'persentase_penarikan' => $decoded['persentase_penarikan'],
            'total' => $decoded['total'],
            'list_kalkulasi_penarikan' => $this->model->data()
        );

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'data' => $data,
            'message' => 'Pencairan berhasil dibuat'
        );
        $this->result();
        return false;
    }

    private function rabDelete($decoded) {
        if (!isset($decoded['id_rab'])) {
            $this->_result['feedback'] = array(
                'message' => 'Id RAB tidak ditemukan'
            );
            $this->result();
            return false;   
        }

        if (!isset($decoded['id_rencana'])) {
            $this->_result['feedback'] = array(
                'message' => 'Id Rencana tidak ditemukan'
            );
            $this->result();
            return false;   
        }

        $this->model('Rab');
        $this->model->getData('k.nama, rab.keterangan','rencana_anggaran_belanja rab JOIN kebutuhan k USING(id_kebutuhan)',array('rab.id_rab','=',Sanitize::escape2($decoded['id_rab'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed get data Item RAB'
            );
            $this->result();
            return false;
        }
        $dataRab = $this->model->getResult();


        try {
            $this->model->query('Call DeleteRAB(?)', 
                array(
                    Sanitize::escape2($decoded['id_rab'])
                )
            );
        } catch (\Throwable $th) {
            $this->_result['feedback'] = array(
                'message' => $th->getMessage()
            );
            $this->result();
            return false;
        }

        // $this->model->delete('rencana_anggaran_belanja', array('id_rab','=', Sanitize::escape2($decoded['id_rab'])));
        // if (!$this->model->affected()) {
        //     $this->_result['feedback'] = array(
        //         'message' => 'Failed to delete Item RAB <b><span class="text-orange">' . $dataRab->nama .'</span> ' . $dataRab->keterangan . '</b>'
        //     );
        //     $this->result();
        //     return false;
        // }

        $this->model->getData('total_anggaran, FormatTanggalFull(modified_at) modified_at, status','rencana', array('id_rencana','=', Sanitize::escape2($decoded['id_rencana'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed get total_anggaran after delete RAB'
            );
            $this->result();
            return false;
        }

        $decoded['total_rab'] = $this->model->getResult()->total_anggaran;
        $decoded['modified_at'] = $this->model->getResult()->modified_at;
        $decoded['status'] = json_decode(json_encode((object) Utility::statusRencanaText($this->model->getResult()->status)), FALSE);

        $this->model->query("WITH cte AS (
            SELECT r.id_bantuan, pl.id_pelaksanaan, id_rencana 
            FROM rencana r JOIN bantuan b USING(id_bantuan) LEFT JOIN pelaksanaan pl USING(id_rencana) 
            WHERE id_rencana = ? AND b.blokir IS NULL AND UPPER(b.status) = 'D'
        ) 
        SELECT id_bantuan, SUM(IFNULL(nominal_penggunaan_donasi, 0)) total_teranggarkan, IFNULL(max_anggaran,0) max_anggaran 
        FROM cte 
        LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_pelaksanaan)
        LEFT JOIN
        (
            SELECT l.id_bantuan, SUM(saldo) max_anggaran FROM (
                SELECT d.id_bantuan, d.id_donasi, IFNULL(MIN(apd.saldo_donasi), d.jumlah_donasi) saldo
                FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_donasi) JOIN cte ON(cte.id_bantuan = d.id_bantuan)
                LEFT JOIN pelaksanaan pl ON(pl.id_pelaksanaan = apd.id_pelaksanaan)
                GROUP BY d.id_donasi
                HAVING saldo > 0
            ) l
            GROUP BY l.id_bantuan
        ) m USING(id_bantuan)
        GROUP BY m.max_anggaran", array('id_rencana' => Sanitize::escape2($decoded['id_rencana'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed get max_saldo_anggaran after delete RAB'
            );
            $this->result();
            return false;
        }

        $decoded['max_anggaran'] = $this->model->getResult()->max_anggaran;
        $decoded['total_teranggarkan'] = $this->model->getResult()->total_teranggarkan;

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'data' => $decoded,
            'message' => 'Item RAB <b><span class="text-orange">' . $dataRab->nama .'</span> ' . $dataRab->keterangan . '</b> berhasil dihapus'
        );
        $this->result();
        return false;
    }

    private function bantuanUpdate($decoded) {
        if (isset($decoded['card_img'])) {
            $dataUrl['medium'] = $decoded['card_img'];
            $fieldsGambar['medium'] = array(
                'b.id_gambar_medium',
                'gm.path_gambar path_gambar_medium' 
            );
        }

        if (isset($decoded['wide_img'])) {
            $dataUrl['wide'] = $decoded['wide_img'];
            $fieldsGambar['wide'] = array(
                'b.id_gambar_wide',
                'gw.path_gambar path_gambar_wide' 
            );
        }

        $this->model('Bantuan');
        $id_bantuan = $decoded['id_bantuan'];
        unset($decoded['id_bantuan']);
        unset($decoded['token']);

        if (isset($dataUrl)) {
            $uploaded = $this->uploadDataUrlIntoServer($dataUrl, 'bantuan');

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
                if (isset($dataUrl['medium'])) {
                    $old_id_gambar['medium'] = array(
                        'id_gambar' => $this->model->getResult()->id_gambar_medium,
                        'path_gambar' => $this->model->getResult()->path_gambar_medium
                    );
                }
                if (isset($dataUrl['wide'])) {
                    $old_id_gambar['wide'] = array(
                        'id_gambar' => $this->model->getResult()->id_gambar_wide,
                        'path_gambar' => $this->model->getResult()->path_gambar_wide
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
        $decoded['nama'] = $this->model->getResult()->nama;

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'message' => 'Bantuan <span class="font-weight-bold" data-id-target="'. $id_bantuan .'">' . $decoded['nama'] . '</span> berhasil di perbaharui.'
        );
        $this->_result['feedback']['id_bantuan'] = $id_bantuan;

        $this->model->query("SELECT count(*) records FROM bantuan WHERE id_bantuan > ?", array($id_bantuan));
        $count = $this->model->getResult();
        $halaman = ceil(($count->records + 1) / $this->model->getLimit());
        $this->_result['feedback']['halaman'] = ceil(($count->records + 1) / $this->model->getLimit());

        $this->result();
        return false;
    }

    private function kwitansiUpdate($decoded) {
        $this->model('Auth');
        $this->_auth = $this->model;
        $this->_auth->getData('p.id_pegawai','pegawai p JOIN admin a USING(id_pegawai)',array('a.id_akun','=',$this->_auth->data()->id_akun));
        $id_pegawai = Sanitize::escape2($this->_auth->data()->id_pegawai);
        $decoded = Sanitize::thisArray($decoded);
        $this->model('Donasi');
        $currentDate = new DateTime();
        $waktu_sekarang = $currentDate->format('Y-m-d H:i:s');
        $this->model->update('kwitansi', array('waktu_cetak' => $waktu_sekarang, 'id_pengesah' => $id_pegawai), array('id_kwitansi','=',$decoded['id_kwitansi']));
        if ($this->model->affected()) {
            $this->_result['error'] = false;
            $this->_result['feedback'] = array(
                'message' => 'Kwitansi <span class="font-weight-bolder">#' . $decoded['id_kwitansi'] . '</span> dicetak pada <span class="font-weight-bold">' . $waktu_sekarang . '</span>'
            );
        } else {
            $this->_result['feedback'] = array(
                'message' => 'Failed to Update waktu cetak kwitansi'
            );
        }

        $this->result();

        if (Session::exists('toast')) {
            Session::delete('toast');
        }
        return false;
    }

    private function rabUpdate($decoded) {
        if (!isset($decoded['id_rab'])) {
            $this->_result['feedback'] = array(
                'message' => 'Id RAB tidak ditemukan'
            );
            $this->result();
            return false;   
        }
        unset($decoded['fields']['nama_kebutuhan']);
        $decoded['fields'] = Sanitize::thisArray($decoded['fields']);

        $this->model('Rab');
        $this->model->getData('id_rencana, id_kebutuhan, keterangan', 'rencana_anggaran_belanja', array('id_rab','=',Sanitize::escape2($decoded['id_rab'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Ada kesalahan saat get data pengecekan update RAB'
            );
            $this->result();
            return false;
        }
        
        if (!isset($decoded['fields']['id_kebutuhan'])) {
            $decoded['fields']['id_kebutuhan'] = $this->model->getResult()->id_kebutuhan;
        }

        if (!isset($decoded['fields']['keterangan'])) {
            $decoded['fields']['keterangan'] = $this->model->getResult()->keterangan;
        }

        $this->model->query('SELECT COUNT(rab.id_rab) jumlah_record, k.nama FROM rencana_anggaran_belanja rab JOIN kebutuhan k ON(k.id_kebutuhan = rab.id_kebutuhan) WHERE rab.id_kebutuhan = ? AND rab.keterangan = ? AND rab.id_rencana = ? AND rab.id_rab != ?', array('id_kebutuhan' => $decoded['fields']['id_kebutuhan'], 'keterangan' => $decoded['fields']['keterangan'], 'id_rencana' => Sanitize::escape2($decoded['id_rencana']), 'id_rab' => Sanitize::escape2($decoded['id_rab'])));
        if ($this->model->getResult()->jumlah_record > 0) {
            $this->_result['feedback'] = array(
                'message' => '<b>' . $this->model->getResult()->nama . '</b> dengan spec <b>' . $decoded['fields']['keterangan'] . '</b> sudah ada dalam daftar RAB'
            );
            $this->result();
            return false;
        }

        if (isset($decoded['fields']['harga_satuan'])) {
            $decoded['fields']['harga_satuan'] = Sanitize::toInt2($decoded['fields']['harga_satuan']);
        }

        if (isset($decoded['fields']['jumlah'])) {
            $decoded['fields']['jumlah'] = Sanitize::toInt2($decoded['fields']['jumlah']);
        }

        try {
            $this->model->update('rencana_anggaran_belanja', $decoded['fields'], array('id_rab','=',Sanitize::escape2($decoded['id_rab'])));
        } catch (Exception $e) {
            $pesan = explode(':',$e->getMessage());
            $this->_result['feedback'] = array(
                'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
            );
            $this->result();
            return false;
        }

        $this->_result['feedback'] = array(
            'message' => 'Data RAB <span class="font-weight-bolder">#' . $decoded['id_rab'] . '</span> berhasil diperbaharui'
        );

        $this->model->query('SELECT SUM(nominal_kebutuhan) sum_nominal_kebutuhan, FormatTanggalFull(r.modified_at) modified_at, r.status FROM rencana_anggaran_belanja rab JOIN rencana r USING(id_rencana) WHERE id_rencana = ? GROUP BY id_rencana', array('id_rencana' => Sanitize::escape2($decoded['id_rencana'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed get sum_nominal_kebutuhan'
            );
            $this->result();
            return false;
        }

        $decoded['total_rab'] = $this->model->getResult()->sum_nominal_kebutuhan;
        $decoded['modified_at'] = $this->model->getResult()->modified_at;
        $decoded['status'] = json_decode(json_encode((object) Utility::statusRencanaText($this->model->getResult()->status)), FALSE);

        $this->model->query("WITH cte AS (
            SELECT r.id_bantuan, pl.id_pelaksanaan, id_rencana 
            FROM rencana r JOIN bantuan b USING(id_bantuan) LEFT JOIN pelaksanaan pl USING(id_rencana) 
            WHERE id_rencana = ? AND b.blokir IS NULL AND UPPER(b.status) = 'D'
        ) 
        SELECT id_bantuan, SUM(IFNULL(nominal_penggunaan_donasi, 0)) total_teranggarkan, IFNULL(max_anggaran,0) max_anggaran 
        FROM cte 
        LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_pelaksanaan)
        LEFT JOIN
        (
            SELECT l.id_bantuan, SUM(saldo) max_anggaran FROM (
                SELECT d.id_bantuan, d.id_donasi, IFNULL(MIN(apd.saldo_donasi), d.jumlah_donasi) saldo
                FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_donasi) JOIN cte ON(cte.id_bantuan = d.id_bantuan)
                LEFT JOIN pelaksanaan pl ON(pl.id_pelaksanaan = apd.id_pelaksanaan)
                GROUP BY d.id_donasi
                HAVING saldo > 0
            ) l
            GROUP BY l.id_bantuan
        ) m USING(id_bantuan)
        GROUP BY m.max_anggaran", array('id_rencana' => Sanitize::escape2($decoded['id_rencana'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed get max_saldo_anggaran after delete RAB'
            );
            $this->result();
            return false;
        }

        $decoded['max_anggaran'] = $this->model->getResult()->max_anggaran;
        $decoded['total_teranggarkan'] = $this->model->getResult()->total_teranggarkan;

        $this->_result['error'] = false;
        $this->_result['feedback']['data'] = $decoded;

        $this->result();

        if (Session::exists('toast')) {
            Session::delete('toast');
        }
        return false;
    }

    private function rencanaUpdate($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $this->model('Rab');
        $this->model->update($decoded['table'], $decoded['fields'], array('id_rencana','=',$decoded['id_rencana']));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed to Update data RAB'
            );
        } else {
            $this->_result['error'] = false;
            $this->_result['feedback'] = array(
                'message' => 'Data RAB <span class="font-weight-bolder">#' . $decoded['id_rab'] . '</span> berhasil diperbaharui'
            );
        }

        if (Session::exists('toast')) {
            Session::delete('toast');
        }

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

        $this->model->getData('COALESCE(d.kontak, d2.kontak) kontak, COALESCE(d.alias, d2.samaran, d2.nama) nama, b.nama nama_bantuan','donasi d JOIN donatur d2 USING(id_donatur) JOIN bantuan b USING(id_bantuan)', array('d.id_donasi','=',$decoded['id_donasi']));
        if (!is_null($this->model->getResult()->kontak)) {
            // Kirim Notifikasi WA VIA fonnte
            $text_pesan = 'Hi, '. Sanitize::escape2($this->model->getResult()->nama) .' donasimu telah kami terima, makasih ya kamu berpartisipasi di program *' . Sanitize::escape2($this->model->getResult()->nama_bantuan) . '*. Gunakan akun berbagi di https://pojokberbagi.id untuk melihat perkembangan dari donasimu.';
            $response = Fonnte::send(Sanitize::toInt2($this->model->getResult()->kontak), $text_pesan);
            $this->_result['wa-api'] = $response;
        }

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

        $this->model->setLimit($decoded['limit']);
        $this->model->setStatus($decoded['bayar']);
        $this->model->setOffsetByHalaman($decoded['halaman']);
        $this->model->dataDonasiDonaturBantuan($decoded['id_bantuan']);
        if ($this->model->affected()) {
            $data = $this->model->data();
        }

        $this->model->countRecordDataDonasiDonaturBantuan($decoded['id_bantuan']);
        $records = $this->model->data();

        $pages = ceil($records/$this->model->getLimit());
        
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

        $dataCp = $this->model->getResults();

        $this->_result['error'] = false;        
        $this->_result['feedback'] = array(
            'data' => $dataCp
        );

        $this->result();
        return false;
    }

    // Daftar Seluruh RAB
    private function rabListRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);
        
        if (!isset($decoded['limit'])) {
            $decoded['limit'] = 1;
        }

        if (!isset($decoded['halaman'])) {
            $decoded['halaman'] = 1;
        }

        $this->model('Rab');

        if (isset($decoded['search'])) {
            $this->model->setSearch($decoded['search']);
        }

        $data = array();

        $this->model->setLimit($decoded['limit']);
        $this->model->setHalaman($decoded['halaman'], 'rencana');
        $this->model->getListRencana();

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
            'message' => 'ok',
            'pages' => $pages,
            'total_record' => $data['total_record']
        );

        $this->result();

        if ($this->_result['error'] == false) {
            Session::delete('toast');
        }
        return false;
    }

    // Daftar Rekalkulasi Penarikan
    private function rekalkulasiPenarikanRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);
        
        try {
            $this->model('Pencairan');
            $this->model->query("Call KalkulasiPenarikan(?,?,?)", array(
                $decoded['id_pelaksanaan'], 
                $decoded['persentase_penarikan'],
                $decoded['id_pencairan'], 
            ));
        } catch (\Throwable $th) {
            $pesan = explode(':',$th->getMessage());
            $this->_result['feedback'] = array(
                'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
            );
            $this->result();
            return false;
        }

        $this->_result['error'] = false;
        $this->_result['feedback'] = array (
            'data' => $this->model->data()
        );

        $this->result();
        if ($this->_result['error'] == false) {
            Session::delete('toast');
        }
        return false;
    }

    // Daftar Get List Detil Pinbuk penarikan
    private function detilPinbukRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        try {
            $this->model('Pencairan');
            $this->model->query("Call KalkulasiPenarikan(?,?,?)", array(
                $decoded['id_pelaksanaan'], 
                $decoded['persentase_pencairan'],
                $decoded['id_pencairan'], 
            ));
        } catch (\Throwable $th) {
            $pesan = explode(':',$th->getMessage());
            $this->_result['feedback'] = array(
                'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
            );
            $this->result();
            return false;
        }

        $data = json_decode(json_encode($this->model->data()), true);

        $dataDetil = Config::search($data,'id_ca',$decoded['id_ca']);

        $this->_result['error'] = false;
        $this->_result['feedback'] = array (
            'data' => $dataDetil
        );

        $this->result();
        if ($this->_result['error'] == false) {
            Session::delete('toast');
        }
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
        // Comment Check Token Jika Select2 Error
        if (!$this->checkToken($decoded['token'])) { return false; }

        switch ($params[0]) {
            case 'bantuan':
                // bantuan Params
                if (isset($params[1])) {
                    if ($params[1] == 'rab') {
                        $params[0] .= 'Rab'; 
                    }
                }
            break;

            case 'donatur':
                // donatur Params
            break;

            case 'kebutuhan':
                // kebutuhan Params
                if (isset($params[1])) {
                    if ($params[1] == 'rab') {
                        $params[0] .= 'Rab'; 
                    }
                } else {
                    // Sementara Diarahkan ke Rab Sebelum ada kebutuhan ajax kebutuhanRead
                    $params[0] .= 'Rab';
                }
            break;

            case 'kategori-kebutuhan':
                // kategori kebutuhan Params
                $params[0] = 'kategoriKebutuhan';
                // kategoriKebutuhanRead
            break;

            case 'petugas-pencairan':
                $params[0] = 'petugasPencairan';
                // petugasPencairanRead
            break;

            case 'channel-account':
                if (isset($params[1])) {
                    if ($params[1] == 'penerima-pinbuk-kalkulasi') {
                        $params[0] = 'channelAccountPenerima';
                        // channelAccountPenerimaRead
                    }
                }
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

    private function channelAccountPenerimaRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $search = null;
        $search_columnQ = "WHERE ca.id_ca != ? AND cp.jenis != 'GI' AND cp.jenis != 'QR'";
        $params = array(
            $decoded['id_ca_pengirim']
        );
        $filter = array(
            "ca.id_ca != ? AND cp.jenis != 'GI' AND cp.jenis != 'QR'",
            $params
        );
        if (!empty($decoded['search'])) {
            $search_value = $decoded['search'];
            $search_column = "ca.id_ca != ? AND cp.jenis != 'GI' AND cp.jenis != 'QR' AND LOWER(CONCAT(IFNULL(ca.nama,''),IFNULL(ca.atas_nama,''),IFNULL(ca.nomor,''))) LIKE LOWER(CONCAT('%',?,'%'))";
            $search_columnQ = "WHERE {$search_column}";
            array_push($params, $search_value);
            $search = array(
                $search_column,
                $search_value
            );
            $filter = $search;
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

        $this->model('Pencairan');
        $this->model->query("SELECT ca.id_ca, ca.nama, ca.nomor, ca.jenis, ca.atas_nama, IFNULL(g.path_gambar,'') path_gambar FROM channel_account ca LEFT JOIN channel_payment cp USING(id_ca) LEFT JOIN gambar g USING(id_gambar) {$search_columnQ} ORDER BY nama ASC LIMIT {$offset}, {$limit}", $params);

        $dataCaPenerima = $this->model->getResults();

        $count = $this->model->countData('channel_account ca LEFT JOIN channel_payment cp USING(id_ca)',null, $filter);
        
        $this->_result['error'] = false;        
        $this->_result['feedback'] = array(
            'data' => $dataCaPenerima
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

    private function petugasPencairanRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $params = array();

        $petugas = '';
        if (isset($decoded['petugas'])) {
            if (count(is_countable($decoded['petugas']) ? $decoded['petugas'] : []) > 0) {
                $decoded['petugas'] = Sanitize::thisArray($decoded['petugas']);
                $petugas = " AND p.id_pegawai NOT IN (";
                $xCol = 1;
                foreach ($decoded['petugas'] as $questionMark) {
                    $petugas .= "?";
                    if ($xCol < count(is_countable($decoded['petugas']) ? $decoded['petugas'] : [])) {
                        $petugas .= ", ";
                    }
                    $xCol++;
                    
                }
                $petugas .= ")";
            }
        }

        $search = null;
        $search_columnQ = "WHERE p.id_pegawai != 1{$petugas}";
        if (!empty($decoded['search'])) {
            $search_value = $decoded['search'];
            $search_column = "LOWER(CONCAT(p.nama, IFNULL(j.nama,''))) LIKE LOWER(CONCAT('%',?,'%'))";
            $search_columnQ = "WHERE {$search_column} AND p.id_pegawai != 1 {$petugas}";
            array_push($params, $search_value);
            $search = array(
                $search_column,
                $search_value
            );
        }

        if ($petugas != '') {
            $params = array_merge($params, $decoded['petugas']);
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

        $this->model('Pegawai');
        $this->model->query("SELECT p.id_pegawai, p.nama nama_pegawai, j.nama nama_jabatan FROM pegawai p JOIN admin a USING(id_pegawai) LEFT JOIN jabatan j USING(id_jabatan) {$search_columnQ} ORDER BY p.nama ASC LIMIT {$offset}, {$limit}", $params);

        $dataKK = $this->model->getResults();

        $count = $this->model->countData('pegawai p JOIN admin a USING(id_pegawai)',null, $search);
        
        $this->_result['error'] = false;        
        $this->_result['feedback'] = array(
            'data' => $dataKK
        );

        if (isset($count)) {
            $this->_result['feedback']['record'] = $count->jumlah_record;
            $this->_result['feedback']['offset'] = (int) $offset;
            $this->_result['feedback']['limit'] = $limit;
            $this->_result['feedback']['load_more'] = ($count->jumlah_record > (int) $offset + $limit);
        }

        if (!is_null($search)) {
            $this->_result['feedback']['search'] = $search_value;
        }

        $this->result();
        return false;
    }

    private function kategoriKebutuhanRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $search = null;
        $search_columnQ = '';
        $params = array();
        if (!empty($decoded['search'])) {
            $search_value = $decoded['search'];
            $search_column = "LOWER(nama) LIKE LOWER(CONCAT('%',?,'%'))";
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

        $this->model('Kebutuhan');
        $this->model->query("SELECT id_kk, nama FROM kategori_kebutuhan {$search_columnQ} ORDER BY nama ASC LIMIT {$offset}, {$limit}", $params);

        $dataKK = $this->model->getResults();

        $count = $this->model->countData('kategori_kebutuhan',null, $search);
        
        $this->_result['error'] = false;        
        $this->_result['feedback'] = array(
            'data' => $dataKK
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

    private function kebutuhanRabRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        if (empty($decoded['id_rencana'])) {
            $this->_result['feedback']['message'] = 'Id rencana tidak diketahui';
            $this->result();
            return false;
        }

        $this->model('Kebutuhan');

        $search = null;
        $search_columnQ = '';
        $params = array();
        if (!empty($decoded['search'])) {
            $search_value = $decoded['search'];
            $search_column = "LOWER(CONCAT(k.nama, IFNULL(kk.nama,''), IFNULL(k_rab.jumlah_item_rab_ini,0))) LIKE LOWER(CONCAT('%',?,'%'))";
            $search_columnQ = "WHERE {$search_column}";
            array_push($params, $search_value);
            $search = array(
                'search_column' => $search_columnQ,
                'search_value' => $search_value
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

        $this->model->readKebutuhanRab($decoded['id_rencana'], $search);

        if (!$this->model->affected()) {
            Debug::prd($this->model);
            $this->_result['feedback']['message'] = 'Terjadi kesalahan dalam membaca data kebutuhan RAB';
            $this->result();
            return false;
        }

        $dataKebutuhanRab = $this->model->data();

        $this->model->countKebutuhanRab($decoded['id_rencana'], $search);
        $count = $this->model->getResult();

        if (!$this->model->affected()) {
            $this->_result['feedback']['message'] = 'Terjadi kesalahan dalam membaca jumlah data kebutuhan RAB';
            $this->result();
            return false;
        }

        $this->_result['error'] = false;        
        $this->_result['feedback'] = array(
            'data' => $dataKebutuhanRab
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

    private function bantuanRabRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $search = null;
        $search_columnQ = '';
        $params = array();
        if (!empty($decoded['search'])) {
            $search_value = $decoded['search'];
            $search_column = "LOWER(CONCAT_WS(' ',IFNULL(nama, ''), IFNULL(sektor, ''), IFNULL(kategori, ''), max_anggaran))";
            $search_columnQ = "WHERE {$search_column} LIKE LOWER(CONCAT('%',?,'%'))";
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

        $this->model->query("SELECT * FROM (
            SELECT id_bantuan, SUM(saldo) max_anggaran, b.nama, s.nama sektor, k.nama kategori, b.prioritas, b.create_at
            FROM
            (
                SELECT d.id_bantuan, d.id_donasi, IFNULL(MIN(apd.saldo_donasi), d.jumlah_donasi) saldo 
                FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_donasi)
                GROUP BY d.id_donasi
            ) l JOIN bantuan b USING(id_bantuan)
            LEFT JOIN sektor s USING(id_sektor)
            LEFT JOIN kategori k USING(id_kategori)
            WHERE b.blokir IS NULL AND UPPER(b.status) = 'D'
            GROUP BY id_bantuan
        ) a {$search_columnQ} ORDER BY prioritas DESC, create_at DESC, id_bantuan ASC LIMIT {$offset}, {$limit}", $params);

        $dataBantuan = $this->model->getResults();

        $this->model->query("SELECT COUNT(*) jumlah_record FROM (
            SELECT id_bantuan, SUM(saldo) max_anggaran, b.nama, s.nama sektor, k.nama kategori, b.prioritas, b.create_at
            FROM
            (
                SELECT d.id_bantuan, d.id_donasi, IFNULL(MIN(apd.saldo_donasi), d.jumlah_donasi) saldo 
                FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_donasi)
                GROUP BY d.id_donasi
            ) l JOIN bantuan b USING(id_bantuan)
            LEFT JOIN sektor s USING(id_sektor)
            LEFT JOIN kategori k USING(id_kategori)
            WHERE b.blokir IS NULL AND UPPER(b.status) = 'D'
            GROUP BY id_bantuan
        ) a {$search_columnQ}", $params);

        $count = $this->model->getResult();

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

        $dataBantuan = $this->model->getResults();

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

        $dataBantuan = $this->model->getResults();

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

    private function kwitansiGet($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $this->model('Donasi');
        $this->model->getKwitansiByIdDonasi($decoded['id_donasi']);
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

    private function donasiGet($decoded) {
        $decoded = Sanitize::thisArray($decoded);
        
        $this->model('Donasi');
        $this->model->getDataTagihanDonasiDonatur($decoded['id_donasi']);
        
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

    private function rencanaGet($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $this->model('Auth');
        $hasil = $this->model->getData('adm.id_pegawai, p.id_jabatan','akun JOIN admin adm USING(id_akun) JOIN pegawai p USING(id_pegawai)',array('id_akun','=', $this->model->data()->id_akun));
        
        if ($hasil) {
            $this->data['pegawai'] = $this->model->data();
        }

        $this->model('Rab');
        $this->model->getRencana($decoded['id_rencana']);
        
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Data rencana gagal dimuat'
            );
            $this->result();
            return false;
        }

        $this->data['rencana'] = $this->model->data();
        $this->data['rencana']->status = json_decode(json_encode((object) Utility::statusRencanaText($this->data['rencana']->status)), FALSE);
        $this->model->getRabList($decoded['id_rencana']);

        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Data rab gagal dimuat'
            );
        }

        $this->data['rab_list'] = $this->model->data();
        
        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'data' => $this->data
        );

        $this->result();
        if ($this->_result['error'] == false) {
            Session::delete('toast');
        }
        return false;
    }

    private function rabDetilGet($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $this->model('Rab');
        $this->model->getRencanaDetil($decoded['id_rencana']);
        
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Data rencana gagal dimuat'
            );
            $this->result();
            return false;
        }

        $this->data['rencana'] = $this->model->data();
        $this->data['rencana']->status = json_decode(json_encode((object) Utility::statusRencanaText($this->data['rencana']->status)), FALSE);
        $this->data['rencana']->id_rencana = (int) $decoded['id_rencana'];

        $this->model->getRabList($decoded['id_rencana'], $this->data['rencana']->total_teranggarkan);
        
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Data rab gagal dimuat'
            );
        }

        $this->data['rab_list'] = $this->model->data();
        
        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'data' => $this->data
        );

        $this->result();
        if ($this->_result['error'] == false) {
            Session::delete('toast');
        }
        return false;
    }

    private function rabGet($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $this->model('Rab');
        $this->model->getRab($decoded['id_rab']);

        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Data rencana gagal dimuat'
            );
            $this->result();
            return false;
        }

        $data = $this->model->data();
        
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

    private function rabForDeleteGet($decoded) {
        $decoded = Sanitize::thisArray($decoded);
        $this->model('Rab');
        $this->model->getData('id_rab, k.nama, keterangan', 'rencana_anggaran_belanja rab JOIN kebutuhan k USING(id_kebutuhan)', array('id_rab','=',$decoded['id_rab']));

        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Data RAB For Delete gagal dimuat'
            );
            $this->result();
            return false;
        }

        $data = $this->model->getResult();
        
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

    private function kebutuhanCekGet($decoded) {
        $decoded = Sanitize::thisArray($decoded['fields']);

        $this->model('Kebutuhan');
        $this->model->getData('COUNT(id_kebutuhan) jumlah_record','kebutuhan', array('nama','=',$decoded['nama']));
        if ($this->model->getResult()->jumlah_record > 0) {
            $this->_result['feedback'] = array(
                'message' => 'kebutuhan sudah ada'
            );
            $this->result();
            return false;
        }
        
        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'message' => 'ok'
        );
        if ($this->_result['error'] == false) {
            Session::delete('toast');
        }
        $this->result();
        return false;
    }

    private function rencanaPelaksanaanGet($decoded) {

        $this->model('Rab');
        $this->model->query("SELECT COUNT(pl.id_pelaksanaan)+1 urutan_pelaksanaan FROM rencana r LEFT JOIN pelaksanaan pl USING(id_rencana) WHERE id_bantuan = (SELECT id_bantuan FROM rencana r WHERE id_rencana = ?)", array('id_rencana' => Sanitize::escape2($decoded['id_rencana'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Data urutan pelaskanaan error'
            );
            $this->result();
            return false;
        }

        $data['urutan_pelaksanaan'] = $this->model->getResult()->urutan_pelaksanaan;

        $this->model->query('SELECT SUM(nominal_kebutuhan) sum_nominal_kebutuhan, r.modified_at, r.status FROM rencana_anggaran_belanja rab JOIN rencana r USING(id_rencana) WHERE id_rencana = ? GROUP BY id_rencana', array('id_rencana' => Sanitize::escape2($decoded['id_rencana'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed get sum_nominal_kebutuhan rencanaPelaksanaanGet'
            );
            $this->result();
            return false;
        }

        $data['total_rab'] = $this->model->getResult()->sum_nominal_kebutuhan;
        $data['modified_at'] = $this->model->getResult()->modified_at;

        $this->model->query("WITH cte AS (
            SELECT r.id_bantuan, pl.id_pelaksanaan, id_rencana, r.total_anggaran
            FROM rencana r JOIN bantuan b USING(id_bantuan) LEFT JOIN pelaksanaan pl USING(id_rencana) 
            WHERE id_rencana = ? AND b.blokir IS NULL AND UPPER(b.status) = 'D'
        ) 
        SELECT id_bantuan, cte.total_anggaran, SUM(IFNULL(nominal_penggunaan_donasi, 0)) total_teranggarkan, IFNULL(max_anggaran,0) max_anggaran 
        FROM cte 
        LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_pelaksanaan)
        LEFT JOIN
        (
            SELECT l.id_bantuan, SUM(saldo) max_anggaran FROM (
                SELECT d.id_bantuan, d.id_donasi, IFNULL(MIN(apd.saldo_donasi), d.jumlah_donasi) saldo
                FROM donasi d LEFT JOIN anggaran_pelaksanaan_donasi apd USING(id_donasi) JOIN cte ON(cte.id_bantuan = d.id_bantuan)
                LEFT JOIN pelaksanaan pl ON(pl.id_pelaksanaan = apd.id_pelaksanaan)
                GROUP BY d.id_donasi
                HAVING saldo > 0
            ) l
            GROUP BY l.id_bantuan
        ) m USING(id_bantuan)
        GROUP BY m.max_anggaran", array('id_rencana' => Sanitize::escape2($decoded['id_rencana'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed get max_saldo_anggaran rencanaPelaksanaanGet'
            );
            $this->result();
            return false;
        }

        $data['saldo_total_rab'] = ($this->model->getResult()->total_anggaran - $this->model->getResult()->total_teranggarkan);
        $data['saldo_anggaran'] = $this->model->getResult()->max_anggaran;

        $this->model->getRabList(Sanitize::escape2($decoded['id_rencana']));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Failed to read getListRab rencanaPelaksanaanGet'
            );
            $this->result();
            return false;
        }

        $data['list_rab'] = $this->model->data();

        // Menampilkat data jumlah_target bantuan dan target capaian bantuan dari rencana (Belum ada fungsinya di method ini mungkin di method lain akan dipakai Who knows.)
        // $this->model->query("SELECT br.jumlah_target, SUM(IF(br.jumlah_target IS NULL, NULL, pl.jumlah_pelaksanaan)) jumlah_target_diselesaikan FROM (
        //     SELECT id_bantuan, jumlah_target FROM bantuan JOIN rencana USING(id_bantuan) WHERE id_rencana = ?
        // )br LEFT JOIN rencana r USING(id_bantuan) LEFT JOIN pelaksanaan pl USING(id_rencana)", array('id_rencana' => Sanitize::escape2($decoded['id_rencana'])));
        // if (!$this->model->affected()) {
        //     $this->_result['feedback'] = array(
        //         'message' => 'Failed to get jumlah_target and jumlah_target_diselesaikan on rencanaPelaksanaanGet'
        //     );
        //     $this->result();
        //     return false;
        // }
        // $data['target_bantuan'] = $this->model->getResult();

        $this->_result['error'] = false;
        $this->_result['feedback']['data'] = $data;

        $this->result();

        if (Session::exists('toast')) {
            Session::delete('toast');
        }
        return false;
    }

    private function kalkulasiPenarikanCaGet($decoded) {
        $decoded = Sanitize::thisArray($decoded);
        
        try {
            $this->model('Pencairan');
            $this->model->query("Call KalkulasiPenarikan(?,?,?)", array(
                $decoded['id_pelaksanaan'], 
                $decoded['persentase_penarikan'],
                $decoded['id_pencairan'], 
            ));
        } catch (\Throwable $th) {
            $pesan = explode(':',$th->getMessage());
            $this->_result['feedback'] = array(
                'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
            );
            $this->result();
            return false;
        }

        $key = array_search($decoded['pengirim']['id_ca'], array_column($this->model->data(), 'id_ca'));

        if ($this->model->data()[$key]->id_ca != $decoded['pengirim']['id_ca']) {
            $this->_result['feedback'] = array(
                'message' => 'Id channel payment tidak sesuai'
            );
            $this->result();
            return false;
        }
        
        if ($this->model->data()[$key]->nama != $decoded['pengirim']['nama_ca']) {
            $this->_result['feedback'] = array(
                'message' => 'Data channel payment tidak sesuai dengan ketentuan'
            );
            $this->result();
            return false;
        }

        $data = array(
            'id_ca' => $this->model->data()[$key]->id_ca,
            'nama' => $this->model->data()[$key]->nama,
            'atas_nama' => $this->model->data()[$key]->atas_nama,
            'nominal' => $this->model->data()[$key]->nominal,
            'nomor' => $this->model->data()[$key]->nomor,
            'jenis' => $this->model->data()[$key]->jenis,
            'path_gambar' => $this->model->data()[$key]->path_gambar
        );

        $this->_result['error'] = false;
        $this->_result['feedback'] = array (
            'data' => $data
        );

        $this->result();
        if ($this->_result['error'] == false) {
            Session::delete('toast');
        }
        return false;
    }
}