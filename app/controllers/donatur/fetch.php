<?php 
class FetchController extends Controller {
    private $_result = array('error' => true);

    public function __construct() {
        if (!isset($_SERVER['HTTP_REFERER']) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Redirect::to('donatur');
        }
    }

    public function update($params = array()) {
        if (count($params) == 0) {
            $this->_result['server_feedback'] = 'Number of params not found';
            $this->result();
            return false;
        }

        if ($params[0] != 'payment-method') {
            $this->_result['server_feedback'] = 'Unrecognize params '. $params[0];
            $this->result();
            return false;
        }

        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

        if ($contentType !== "application/json") {
            $this->_result['server_feedback'] = 'Unrecognize contentType JSON ONLY';
            $this->result();
            return false;
        }

        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);

        // Check Token
        if (!Token::check(Sanitize::escape2($decoded['token']))) {
            $this->_result['server_feedback'] = 'You not allowed to use fake token';
            $this->result();
            return false;
        }

        $this->model('Donasi');
        $this->model->update('donasi', array('id_cp' => Sanitize::escape2($decoded['id_cp'])), array('id_donasi','=',Sanitize::escape2($decoded['id_donasi'])));
        if ($this->model->affected()) {
            $this->_result['error'] = false;
            $this->_result['server_feedback'] = 'Metode pembayaran tagihan donasi berhasil diganti';
        } else {
            $this->_result['server_feedback'] = 'Terjadi kegagalan update disisi server';
        }
        
        $this->result();
        return false;
    }

    private function result() {
        $this->_result[Config::get('session/token_name')] = Token::generate();
        echo json_encode($this->_result);
    }

    // public function update($params = array()) {
    //     if (count($params) > 0) {
    //         if ($params[0] == 'payment-method') {
    //             $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    //             $result = array('error' => true);
    //             if ($contentType === "application/json") {
    //                 $content = trim(file_get_contents("php://input"));
    //                 $decoded = json_decode($content, true);

    //                 $this->model('Donasi');
    //                 $this->model->update('donasi', array('id_cp' => Sanitize::escape2($decoded['id_cp'])), array('id_donasi','=',Sanitize::escape2($decoded['id_donasi'])));
    //                 if ($this->model->affected()) {
    //                     $result['error'] = false;
    //                     $result['server_feedback'] = 'Metode pembayaran tagihan donasi berhasil diganti';
    //                 } else {
    //                     $result['error'] = true;
    //                     $result['server_feedback'] = 'Terjadi kegagalan update disisi server';
    //                 }
    //             } else {
    //                 $result['error'] = true;
    //                 $result['server_feedback'] = 'Unrecognize contentType => JSON ONLY';
    //             }
    //         } else {
    //             $result['error'] = true;
    //             $result['server_feedback'] = 'Unrecognize $params';
    //         }
    //     }
    //     $result[Config::get('session/token_name')] = Token::generate();
    //     echo json_encode($result);
    //     return false;
    // }
}