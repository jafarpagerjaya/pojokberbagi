<?php
class FetchController extends Controller {
    private $_result = array('error' => true);

    public function __construct() {
        if (!isset($_SERVER['HTTP_REFERER']) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Redirect::to('donatur');
        }
    }

    public function index() {
        $this->_result['feedback'] = array(
            'message' => 'Fetch ke Method index ?, mohon periksa kembali tujuan fetchnya, sudah ada atau belum'
        );
        $this->result();
        return false;
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

    private function result() {
        $this->_result[Config::get('session/token_name')] = Token::generate();
        $toast = array(
            'error' => $this->_result['error'],
            'data_toast' => 'feedback',
            'id' => Hash::unique()
        );

        if (isset($this->_result['feedback'])) {
            $toast['feedback'] = $this->_result['feedback'];
        }

        $this->_result['toast'] = $toast;
        if (isset($toast['feedback'])) {
            if (array_key_exists('message', $toast['feedback'])) {
                if (!$this->_result['error']) {
                    Session::put('toast', $toast);
                }
            }
        }
        echo json_encode($this->_result);
    }

    public function delete($params) {
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
            case 'amin':
                // aminDelete Params
            break;
            
            default:
                $this->_result['feedback'] = array(
                    'message' => 'Unrecognize params '. $params[0]
                );
                $this->result();
                return false;
            break;
        }

        // prepare method Delete name
        $action = $params[0] . 'Delete';
        // call method Delete
        $this->$action($decoded);

        return false;
    }

    private function aminDelete($decoded) {
        $decoded = Sanitize::thisArray($decoded['fields']);

        if (!isset($decoded['id_donasi'])) {
            $this->_result['feedback'] = array(
                'message' => 'Id donasi tidak ditemukan'
            );
            $this->result();
            return false;
        }

        if (!isset($decoded['id_bantuan'])) {
            $this->_result['feedback'] = array(
                'message' => 'Id bantuan tidak ditemukan'
            );
            $this->result();
            return false;
        }

        $decoded['id_donasi'] = base64_decode(strrev($decoded['id_donasi']));

        $this->model('Auth');
        $this->_auth = $this->model('Auth');

        $this->model('Donasi');
        $this->model->query("SELECT COUNT(*) count FROM donasi WHERE id_bantuan = ? AND id_donasi = ?", array('id_bantuan' => $decoded['id_bantuan'], 'id_donasi' => $decoded['id_donasi']));
        if ($this->model->getResult()->count == 0) {
            $this->_result['feedback']['message'] = 'Donasi bantuan tidak ditemukan';
            $this->result();
            return false;
        }

        if ($this->_auth->isSignIn()) {
            $decoded['id_akun'] = $this->_auth->data()->id_akun;   
        }

        if (Cookie::exists(Config::get('client/cookie_name'))) {
			$cookie_value = Sanitize::thisArray(json_decode(base64_decode(Cookie::get(Config::get('client/cookie_name'))), true));
            $decoded['id_pengunjung'] = $cookie_value['id_pengunjung'];
        } else {
            $this->_result['error'] = false;
            $this->_result['feedback'] = array(
                'message' => 'Cookie not exists check local storage',
                'lsc' => true,
                'uri' => base64_encode('/bantuan/detil/' . $decoded['id_bantuan'])
            );
            $this->result();
            return false;
        }

        unset($decoded['id_bantuan']);

        try {
            $filter = '';
            try {
                $xCol = 1;
                foreach ($decoded as $key => $value) {
                    $filter .= "{$key} = ?";
                    if ($xCol < count(is_countable($decoded) ? $decoded : [])) {
                        $filter .= " AND ";
                    }
                    $xCol++;
                }

                if (!isset($decoded['id_akun'])) {
                    $filter .= " AND id_akun IS NULL";
                }
                
                $sql = "SELECT COUNT(*) count FROM amin WHERE {$filter}";
                $this->model->query($sql, $decoded);
            } catch (\Throwable $th) {
                $pesan = explode(':',$th->getMessage());
                $this->_result['feedback'] = array(
                    'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
                );
                $this->result();
                return false;
            }

            if ($this->model->getResult()->count == 0) {
                $this->_result['feedback'] = array(
                    'message' => 'Failed to delete loved donasi, loved donasi belum ada'
                );
                $this->result();
                return false;
            }

            $this->model->query("DELETE FROM amin WHERE {$filter}", $decoded);
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
                'message' => 'Failed to create loved donasi'
            );
            $this->result();
            return false;
        }

        $this->model->query("SELECT COUNT(*) liked FROM amin WHERE id_donasi = ?", array($decoded['id_donasi']));
        $this->_result['feedback'] = array(
            'data' => array(
                'liked' => $this->model->getResult()->liked
            )
        );
        $this->_result['error'] = false;
        $this->result();
        return false;
    }

    public function create($params) {
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
            case 'amin':
                // aminCreate Params
            break;
            
            default:
                $this->_result['feedback'] = array(
                    'message' => 'Unrecognize params '. $params[0]
                );
                $this->result();
                return false;
            break;
        }

        // prepare method Create name
        $action = $params[0] . 'Create';
        // call method Create
        $this->$action($decoded);

        return false;
    }

    private function aminCreate($decoded) {
        $decoded = Sanitize::thisArray($decoded['fields']);

        if (!isset($decoded['id_donasi'])) {
            $this->_result['feedback'] = array(
                'message' => 'Id donasi tidak ditemukan'
            );
            $this->result();
            return false;
        }

        if (!isset($decoded['id_bantuan'])) {
            $this->_result['feedback'] = array(
                'message' => 'Id bantuan tidak ditemukan'
            );
            $this->result();
            return false;
        }

        $decoded['id_donasi'] = base64_decode(strrev($decoded['id_donasi']));

        $this->model('Auth');
        $this->_auth = $this->model('Auth');

        $this->model('Donasi');
        $this->model->query("SELECT COUNT(*) count FROM donasi WHERE id_bantuan = ? AND id_donasi = ?", array('id_bantuan' => $decoded['id_bantuan'], 'id_donasi' => $decoded['id_donasi']));
        if ($this->model->getResult()->count == 0) {
            $this->_result['feedback']['message'] = 'Donasi bantuan tidak ditemukan';
            $this->result();
            return false;
        }
        
        if ($this->_auth->isSignIn()) {
            $decoded['id_akun'] = $this->_auth->data()->id_akun;   
        }

        if (Cookie::exists(Config::get('client/cookie_name'))) {
			$cookie_value = Sanitize::thisArray(json_decode(base64_decode(Cookie::get(Config::get('client/cookie_name'))), true));
            $decoded['id_pengunjung'] = $cookie_value['id_pengunjung'];
        } else {
            $this->_result['error'] = false;
            $this->_result['feedback'] = array(
                'message' => 'Cookie not exists check local storage',
                'lsc' => true,
                'uri' => base64_encode('/bantuan/detil/' . $decoded['id_bantuan'])
            );
            $this->result();
            return false;
        }

        unset($decoded['id_bantuan']);

        try {
            try {
                $filter = '';
                $xCol = 1;
                foreach ($decoded as $key => $value) {
                    $filter .= "{$key} = ?";
                    if ($xCol < count(is_countable($decoded) ? $decoded : [])) {
                        $filter .= " AND ";
                    }
                    $xCol++;
                }

                if (!isset($decoded['id_akun'])) {
                    $filter .= " AND id_akun IS NULL";
                }
                
                $sql = "SELECT COUNT(*) count FROM amin WHERE {$filter}";
                $this->model->query($sql, $decoded);
            } catch (\Throwable $th) {
                $pesan = explode(':',$th->getMessage());
                $this->_result['feedback'] = array(
                    'message' => '<b>'. current($pesan) .'</b> '. end($pesan)
                );
                $this->result();
                return false;
            }

            if ($this->model->getResult()->count > 0) {
                $this->_result['feedback'] = array(
                    'message' => 'Failed to create loved donasi, loved donasi sudah ada'
                );
                $this->result();
                return false;
            }

            $this->model->create('amin', $decoded);
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
                'message' => 'Failed to create loved donasi'
            );
            $this->result();
            return false;
        }

        $this->model->query("SELECT COUNT(*) liked FROM amin WHERE id_donasi = ?", array($decoded['id_donasi']));
        $this->_result['feedback'] = array(
            'data' => array(
                'liked' => $this->model->getResult()->liked
            )
        );
        $this->_result['error'] = false;
        $this->result();
        return false;
    }

    public function read($params) {
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
                // bantuan Params
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
                case 'list':
                    // list Params
                break;

                case 'kategori':
                    // kategori Params
                    if (isset($params[2])) {
                        $decoded['nama_kategori'] = $params[2];
                    }
                break;

                case 'deskripsi':
                    // deskripsi Params
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

        // prepare method Read name
        $action = $params[0] . 'Read';
        // call method Read
        $this->$action($decoded);

        return false;
    }

    // getDeskripsi(); Route Default BantuanController
    private function bantuanDeskripsiRead($decoded) {
        $this->model('Bantuan');
        $this->model->getAllData('deskripsi', array('id_bantuan', '=', $decoded['id_bantuan']));
        if (!$this->model->affected()) {
            $this->result();
            return false;
        }

        $this->_result['error'] = false;
        $this->data['deskripsi'] = $this->model->getResult();
        if (!is_null($this->data['deskripsi'])) {
            $this->_result['feedback'] = array(
                'data' => Output::decodeEscape($this->data['deskripsi']->isi)
            );
        }
        
        $this->result();
        return false;
    }

    // getListbantuan(); Route Default HomeController
    private function bantuanListRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);
        
        $program = null;

        if (isset($decoded['nama_kategori'])) {
            $program = explode('-', $decoded['nama_kategori']);
            array_unshift($program, 'pojok');
            $program = implode(' ', $program);
        }

        $decoded['list_id'] = Sanitize::thisArray(json_decode(base64_decode($decoded['list_id'])));
        $limit = $decoded['limit'];

        $this->model('Bantuan');
        $this->model->setStatus(Sanitize::escape2('D'));
        $this->model->setOrder('b.action_at');
        $this->model->getCurrentListIdBantuan($program, $decoded['list_id']);

        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Bantuan method getCurrentListIdBantuan goes wrong'
            );
            $this->result();
            return false;
        }

        $currentListId = $this->model->data();
        foreach ($currentListId as $value) {
            $arrayList[] = $value->id_bantuan;
        }
        $currentListId = $arrayList;

        // compare with the last list
        $newListId = array_diff($currentListId, $decoded['list_id']);
        $removeData = array_diff($decoded['list_id'], $currentListId);

        if (count(is_countable($newListId) ? $newListId : [])) {
            // new data founded
            $this->model->getListIdBantuan($program, $newListId);

            if (!$this->model->affected()) {
                $this->_result['feedback'] = array(
                    'message' => 'Bantuan method getListIdBantuan goes wrong'
                );
                $this->result();
                return false;
            }

            $newestData = $this->model->data();
            if ($newestData) {
                $decoded['offset'] += sizeof($newestData);
                if ((count($newestData) % $limit) != 0) {
                    $limit -= (count($newestData) % $limit);
                    if ($limit < $decoded['limit'] / 2) {
                        $limit += $decoded['limit'];
                    }
                } else {
                    $limit = $decoded['limit'];
                }
            }
        }

        if (count(is_countable($removeData) ? $removeData : [])) {
            $decoded['offset'] -= count($removeData);
            if (count(is_countable($newListId) ? $newListId : [])) {
                $limit += count($removeData);
            } else {
                $limit = $decoded['limit'];
            }
        }

        $this->model->setOffset($decoded['offset']);
        $this->model->setLimit($limit);
        $this->model->getListBantuan($program);

        if ($this->model->affected()) {
            $data = $this->model->data();
            if (count(is_countable($newListId) ? $newListId : []) == 0) {
                $decoded['list_id'] = array_map('intval', $decoded['list_id']);
                $list_id = $decoded['list_id'];
            } else {
                $intersetct = array_intersect(array_column($data['data'], 'id_bantuan'), $currentListId);
                if (count(is_countable($intersetct) ? $intersetct : [])) {
                    foreach($intersetct as $key => $value) {
                        foreach($newestData as $listNewst => $newestRecord) {
                            if ($newestRecord->id_bantuan == $value) {
                                array_splice($newestData, $listNewst, 1);
                            }
                        }
                    }
                }
                $list_id = $currentListId;
            }
            $data['list_id'] = base64_encode(json_encode(array_unique(array_merge($list_id, array_column($data['data'], 'id_bantuan')))));
        } else {
            $this->_result['feedback'] = array(
                'message' => 'Bantuan method getListBantuan goes wrong'
            );
            $this->result();
            return false;
        }

        if (!isset($data['data'])) {
            $data['data'] = array();
        }

        if (!isset($data['record'])) {
            $data['record'] = count($currentListId);
        }

        if (!isset($data['load_more'])) {
            $data['load_more'] = false;
        }

        if (!isset($data['offset'])) {
            $data['offset'] = $decoded['offset'];
        }

        if (!isset($data['limit'])) {
            $data['limit'] = $decoded['limit'];
        }

        if (!isset($data['list_id'])) {
            $data['list_id'] = base64_encode(json_encode($currentListId));
        }

        // $data['limit'] == $limit
        // setel ulang limit dan offset ke aturan awal yakni 6
        if ($data['limit'] != $decoded['limit']) {
            $data['offset'] += $limit - $decoded['limit'];
            $data['limit'] = $decoded['limit'];
        }

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'data' => $data['data'],
            'message' => 'ok',
            'limit' => $data['limit'],
            'offset' => $data['offset'],
            'total_record' => $data['record'],
            'load_more' => $data['load_more'],
            'list_id' => $data['list_id']
        );

        if (isset($newestData)) {
            $this->_result['feedback']['newest_data'] = array_reverse($newestData);
        }

        if (count(is_countable($removeData) ? $removeData : [])) {
            $this->_result['feedback']['removed_id'] = $removeData;
        }

        $this->result();
        
        return false;
    }

    // getListBantuan(); Route Default BantuanController
    private function bantuanKategoriRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $program = null;

        if (isset($decoded['nama_kategori'])) {
            $program = explode('-', $decoded['nama_kategori']);
            array_unshift($program, 'pojok');
            $program = implode(' ', $program);
        }

        $decoded['list_id'] = Sanitize::thisArray(json_decode(base64_decode($decoded['list_id'])));
        $limit = $decoded['limit'];

        $this->model('Bantuan');
        $this->model->setStatus(Sanitize::escape2('D'));
        $this->model->setOrder('b.action_at');
        $this->model->getCurrentListIdBantuan($program, $decoded['list_id']);

        if (!$this->model->affected()) {
            $this->_result['feedback'] = array(
                'message' => 'Bantuan method getCurrentListIdBantuan goes wrong'
            );
            $this->result();
            return false;
        }

        $currentListId = $this->model->data();
        foreach ($currentListId as $value) {
            $arrayList[] = $value->id_bantuan;
        }
        $currentListId = $arrayList;

        // compare with the last list
        $newListId = array_diff($currentListId, $decoded['list_id']);
        $removeData = array_diff($decoded['list_id'], $currentListId);

        if (count(is_countable($newListId) ? $newListId : [])) {
            // new data founded
            $this->model->getListIdBantuan($program, $newListId);

            if (!$this->model->affected()) {
                $this->_result['feedback'] = array(
                    'message' => 'Bantuan method getListIdBantuan goes wrong'
                );
                $this->result();
                return false;
            }

            $newestData = $this->model->data();
            if ($newestData) {
                $decoded['offset'] += sizeof($newestData);
                if ((count($newestData) % $limit) != 0) {
                    $limit -= (count($newestData) % $limit);
                    if ($limit < $decoded['limit'] / 2) {
                        $limit += $decoded['limit'];
                    }
                } else {
                    $limit = $decoded['limit'];
                }
            }
        }

        if (count(is_countable($removeData) ? $removeData : [])) {
            $decoded['offset'] -= count($removeData);
            if (count(is_countable($newListId) ? $newListId : [])) {
                $limit += count($removeData);
            } else {
                $limit = $decoded['limit'];
            }
        }

        $this->model->setOffset($decoded['offset']);
        $this->model->setLimit($limit);
        $this->model->getListBantuan($program);

        if ($this->model->affected()) {
            $data = $this->model->data();
            if (count(is_countable($newListId) ? $newListId : []) > 0) {
                $intersetct = array_intersect(array_column($data['data'], 'id_bantuan'), $currentListId);
                if (count(is_countable($intersetct) ? $intersetct : [])) {
                    foreach($intersetct as $key => $value) {
                        foreach($newestData as $listNewst => $newestRecord) {
                            if ($newestRecord->id_bantuan == $value) {
                                array_splice($newestData, $listNewst, 1);
                            }
                        }
                    }
                }
            }

            $list_id = $currentListId;

            $data['list_id'] = base64_encode(json_encode(array_unique(array_merge($list_id, array_column($data['data'], 'id_bantuan')))));
        } else {
            $this->_result['feedback'] = array(
                'message' => 'Bantuan method getListBantuan goes wrong'
            );
            $this->result();
            return false;
        }

        if (!isset($data['data'])) {
            $data['data'] = array();
        }

        if (!isset($data['record'])) {
            $data['record'] = count($currentListId);
        }

        if (!isset($data['load_more'])) {
            $data['load_more'] = false;
        }

        if (!isset($data['offset'])) {
            $data['offset'] = $decoded['offset'];
        }

        if (!isset($data['limit'])) {
            $data['limit'] = $decoded['limit'];
        }

        if (!isset($data['list_id'])) {
            $data['list_id'] = base64_encode(json_encode($currentListId));
        }

        // $data['limit'] == $limit
        // setel ulang limit dan offset ke aturan awal yakni 6
        if ($data['limit'] != $decoded['limit']) {
            $data['offset'] += $limit - $decoded['limit'];
            $data['limit'] = $decoded['limit'];
        }

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'data' => $data['data'],
            'message' => 'ok',
            'limit' => $data['limit'],
            'offset' => $data['offset'],
            'total_record' => $data['record'],
            'load_more' => $data['load_more'],
            'list_id' => $data['list_id']
        );

        if (isset($newestData)) {
            $this->_result['feedback']['newest_data'] = array_reverse($newestData);
        }

        if (count(is_countable($removeData) ? $removeData : [])) {
            $this->_result['feedback']['removed_id'] = $removeData;
        }

        $this->result();

        return false;
    }
}