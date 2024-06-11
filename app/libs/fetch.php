<?php
class Fetch {
    private $_result = array('error' => true),
            $_decoded;

    public function __construct($token = true) {
        if (!isset($_SERVER['HTTP_REFERER']) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Redirect::to('/');
        }

        // Check Content Type and decode JSON to array
        $this->_decoded = $this->contentTypeJsonDecoded($_SERVER["CONTENT_TYPE"]);

        // Check Token
        if ($token) {
            if (!$this->checkToken($this->_decoded['token'])) { return false; }
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

    public function getDecoded() {
        return $this->_decoded;
    }

    public function addResults($array) {
        $this->_result = array_merge($this->_result, $array);
    }

    public function result() {
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
}