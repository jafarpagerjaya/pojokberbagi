<?php 
class FetchController extends Controller {
    private $_result = array('error' => true);

    public function __construct() {
        if (!isset($_SERVER['HTTP_REFERER']) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Redirect::to('donatur');
        }
    }

    public function get($params = array()) {
        if (count(is_countable($params) ? $params : []) == 0) {
            $this->_result['feedback'] = array(
                'message' => 'Number of params not found'
            );
            $this->result();
            return false;
        }

        switch ($params[0]) {
            case 'kwitansi':
                // kwitansiGet Params
            break;
            
            default:
                $this->_result['feedback'] = array(
                    'message' => 'Unrecognize params '. $params[0]
                );
                $this->result();
                return false;
            break;
        }

        // Check Content Type and decode JSON to array
        $decoded = $this->contentTypeJsonDecoded($_SERVER["CONTENT_TYPE"]);

        // Check Token
        $this->checkToken($decoded['token']);

        // prepare method Get
        $action = $params[0] . 'Get';
        // call method Get
        $this->$action($decoded);

        return false;
    }

    public function update($params = array()) {
        if (count(is_countable($params) ? $params : []) == 0) {
            $this->_result['feedback'] = 'Number of params not found';
            $this->result();
            return false;
        }

        switch ($params[0]) {
            case 'payment-method':
                // paymentMethodUpdate
                $params[0] = 'paymentMethod';
            break;
            case 'kwitansi':
                // kwitansiUpdate
            break;
            
            default:
                $this->_result['feedback'] = array(
                    'message' => 'Unrecognize params '. $params[0]
                );
                $this->result();
                return false;
            break;
        }

        // Check Content Type and decode JSON to array
        $decoded = $this->contentTypeJsonDecoded($_SERVER["CONTENT_TYPE"]);

        // Check Token
        $this->checkToken($decoded['token']);

        // prepare method update name
        $action = $params[0] . 'Update';
        // call method update
        $this->$action($decoded);

        return false;
    }

    // Method Get
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
        return false;
    }

    // Method Update
    private function paymentMethodUpdate($decoded) {
        $this->model('Donasi');
        $this->model->update('donasi', array('id_cp' => Sanitize::escape2($decoded['id_cp'])), array('id_donasi','=',Sanitize::escape2($decoded['id_donasi'])));
        if ($this->model->affected()) {
            $this->_result['error'] = false;
            $this->_result['feedback'] = 'Metode pembayaran tagihan donasi berhasil diganti';
        } else {
            $this->_result['feedback'] = 'Terjadi kegagalan update disisi server';
        }
        
        $this->result();
        return false;
    }

    private function kwitansiUpdate($decoded) {
        $this->model('Donasi');
        $currentDate = new DateTime();
        $waktu_sekarang = $currentDate->format('Y-m-d H:i:s');
        $this->model->update('kwitansi', array('waktu_cetak' => $waktu_sekarang), array('id_kwitansi','=',$decoded['id_kwitansi']));
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
        $decoded = Sanitize::thisArray(json_decode($content, true));
        return $decoded;
    }

    private function result() {
        $this->_result[Config::get('session/token_name')] = Token::generate();
        echo json_encode($this->_result);
    }
}