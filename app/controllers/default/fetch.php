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
                    // list Params
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
                $decoded['offset'] = count($currentListId);
                if ((count($newestData) % $decoded['limit']) != 0) {
                    $limit += $decoded['limit'] - (count($newestData) % $decoded['limit']);
                }
            }
        }

        if (count(is_countable($removeData) ? $removeData : [])) {
            $decoded['offset'] -= sizeof($removeData);
            if (count(is_countable($newListId) ? $newListId : [])) {
                $limit += $decoded['limit'] - (count($removeData) % $decoded['limit']) - ($limit - $decoded['limit']);
            } else {
                $limit += $decoded['limit'] - (count($removeData) % $decoded['limit']);
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
                $decoded['offset'] = count($currentListId);
                if ((count($newestData) % $decoded['limit']) != 0) {
                    $limit += $decoded['limit'] - (count($newestData) % $decoded['limit']);
                }
            }
        }

        if (count(is_countable($removeData) ? $removeData : [])) {
            $decoded['offset'] -= sizeof($removeData);
            if (count(is_countable($newListId) ? $newListId : [])) {
                $limit += $decoded['limit'] - (count($removeData) % $decoded['limit']) - ($limit - $decoded['limit']);
            } else {
                $limit += $decoded['limit'] - (count($removeData) % $decoded['limit']);
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