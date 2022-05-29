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
        if (count($params) == 0) {
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
}