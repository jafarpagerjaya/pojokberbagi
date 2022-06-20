<?php
class FetchController extends Controller {
    private $_result = array('error' => true);

    public function __construct() {
        if (!isset($_SERVER['HTTP_REFERER']) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Redirect::to('donatur');
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

    private function result() {
        $this->_result[Config::get('session/token_name')] = Token::generate();
        echo json_encode($this->_result);
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
        $this->checkToken($decoded['token']);

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
                    // bantuan Params
                break;

                case 'kategori':
                    // kategori Params
                    if (isset($params[2])) {
                        $decoded['nama_kategori'] = $params[2];
                    }
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

    // getListbantuan(); Route Default HomeController
    private function bantuanListRead($decoded) {
        $decoded = Sanitize::thisArray($decoded);

        $this->model('Bantuan');
        $this->model->setOffset($decoded['offset']);
        $this->model->setLimit($decoded['limit']);
        $this->model->getListBantuan();

        if ($this->model->affected()) {
            $data = $this->model->data();
        }

        if (!isset($data['data'])) {
            $data['data'] = array();
        }

        if (!isset($data['total_record'])) {
            $data['total_record'] = $this->model->data()['record'];
        }

        if (!isset($data['load_more'])) {
            $data['load_more'] = $this->model->data()['load_more'];
        }

        if (!isset($data['offset'])) {
            $data['offset'] = $this->model->data()['offset'];
        }

        if (!isset($data['limit'])) {
            $data['limit'] = $this->model->data()['limit'];
        }

        $this->_result['error'] = false;
        $this->_result['feedback'] = array(
            'data' => $data['data'],
            'message' => 'ok',
            'limit' => $data['limit'],
            'offset' => $data['offset'],
            'total_record' => $data['record'],
            'load_more' => $data['load_more']
        );

        $this->result();
        
        return false;
    }

    // getListBantuanKategori(); Route Default BantuanController
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
        // check Last Showed ID in current time are still valid
        // $this->model->setOffset(0);
        // if ($decoded['offset'] > $decoded['limit']) {
        //     $limit = $decoded['offset'] - $decoded['limit'];
        // } else {
        //     $limit = $decoded['limit'];
        // }
        // $this->model->setLimit($limit);
        Debug::pr($decoded['list_id']);
        $this->model->getCurrentListIdBantuanKategori($program, $decoded['list_id']);
        $currentListId = $this->model->data();
        foreach ($currentListId as $value) {
            $arrayList[] = $value->id_bantuan;
        }
        $currentListId = $arrayList;
        Debug::pr($currentListId);

        // compare with the last list
        $newListId = array_diff($currentListId, $decoded['list_id']);
        $removeData = array_diff($decoded['list_id'], $currentListId);
        // Debug::pr($newListId);
        // Debug::pr($removeData);

        if (count(is_countable($newListId) ? $newListId : [])) {
            // new data founded
            // $this->model->setOffset(0);
            // $this->model->setLimit(count(is_countable($newListId) ? $newListId : []));
            // $this->model->getListBantuanKategori($program);
            // $newListId = $this->model->data()['data'];
            $this->model->getListIdBantuan($program, $newListId);
            $newestData = $this->model->data();
            if ($newestData) {
                $decoded['offset'] = count($currentListId);
                $limit = $decoded['limit'] - (count($newestData) % $decoded['limit']);
            }
        }

        if (count(is_countable($removeData) ? $removeData : [])) {
            $decoded['offset'] -= sizeof($removeData);
        }

        $this->model->setOffset($decoded['offset']);
        $this->model->setLimit($limit);
        $this->model->getListBantuanKategori($program);

        if ($this->model->affected()) {
            $data = $this->model->data();
        //     if (count(is_countable($data['data']) ? $data['data'] : [])) {
        //         // data ada
        //         $data['list_id'] = array_column($data['data'], 'id_bantuan');
        //         $removeList = array_intersect($data['list_id'], $decoded['list_id']);
        //         if (count(is_countable($removeList) ? $removeList : []) > 0) {
        //             $firstLimit = sizeof($removeList);
        //             // akan ada penghapusan result terhadap data yang sama akibat adanya penambahan data pada result query sehingga data yang sama muncul
        //             foreach($data['data'] as $list => $record) {
        //                 if (is_array($record)) {
        //                     if (in_array($record['id_bantuan'], $removeList)) {
        //                         unset($data['data'][$list]);
        //                         unset($removeList[$record['id_bantuan']]);
        //                     }
        //                 }
        //             }
        //             array_values($data['data']);
                    
        //             $this->model->setOffset(0);
        //             $this->model->getListBantuanKategori($program);
        //             $n_data['data'] = 
        //             $this->model->setOffset($decoded['offset']);
        //         }
                
        //         // Debug::pr($data['list_id']);
        //     } else {
        //         // data tidak ada akibat terjadi pengurangan jumlah result query, maka lakukan load data offset paling pertama
        //         $this->model->setOffset(0);
        //         $this->model->getListBantuanKategori($program);

        //         // reset
        //         $data['load_more'] = false;
        //     }
            if (count(is_countable($newListId) ? $newListId : []) == 0) {
                $decoded['list_id'] = array_map('intval', $decoded['list_id']);
                $list_id = $decoded['list_id'];
            } else {
                $list_id = $currentListId;
            }
            $data['list_id'] = base64_encode(json_encode(array_unique(array_merge($list_id, array_column($data['data'], 'id_bantuan')))));
        }

        if (!isset($data['data'])) {
            $data['data'] = array();
        }

        if (!isset($data['total_record'])) {
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

        if ($data['limit'] != $decoded['limit']) {
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