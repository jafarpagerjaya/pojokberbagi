<?php 
class FetchController extends Controller {
    private $_result = array('error' => true),
            $_auth,
            $_id_donatur;

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
            case 'kuitansi':
                // kuitansiGet Params
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
            case 'kuitansi':
                // kuitansiUpdate
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

    public function read($params = array()) {
        if (count(is_countable($params) ? $params : []) == 0) {
            $this->_result['feedback'] = array(
                'message' => 'Number of params not found'
            );
            $this->result();
            return false;
        }

        switch ($params[0]) {
            case 'donasi-tagihan':
                // donasiTagihanRead
                $params[0] = 'donasiTagihan';
            break;
            case 'tagihan-type':
                // tagihanTypeRead
                $params[0] = 'tagihanType';
                if (isset($params[1])) {
                    switch ($params[1]) {
                        case 'unpaid':
                            // unpaid Params
                            $tagihanType = '0';
                        break;
        
                        case 'paid':
                            // paid Params
                            $tagihanType = '1';

                        break;
                        
                        default:
                            $this->_result['feedback'] = array(
                                'message' => 'Unrecognize params '. $params[1]
                            );
                            $this->result();
                            return false;
                        break;
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

        // Check Content Type and decode JSON to array
        $decoded = $this->contentTypeJsonDecoded($_SERVER["CONTENT_TYPE"]);

        // Check Token
        $this->checkToken($decoded['token']);

        // prepare method update name
        $action = $params[0] . 'Read';
        // call method update
        
        if (isset($tagihanType)) {
            $decoded['tagihan_type'] = $tagihanType;
        }
        
        $this->$action($decoded);

        return false;
    }

    // Method Get
    private function kuitansiGet($decoded) {
        $this->model('Donasi');
        $this->model->getKuitansiByIdDonasi($decoded['id_donasi']);
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

    // Method Read
    private function donasiTagihanRead($decoded) {
        $this->_auth = $this->model('Auth');
        $this->_auth->getData('id_donatur','donatur',array('email','=',$this->_auth->data()->email));
        $this->_id_donatur = $this->_auth->data()->id_donatur;

        $this->model('Donasi');
        
        if (isset($decoded['search'])) {
            $this->model->setSearch($decoded['search']);
        }

        if (!isset($decoded['limit'])) {
            $decoded['limit'] = 1;
        }

        if (!isset($decoded['halaman'])) {
            $decoded['halaman'] = 1;
        }

        $this->model->setOffset(($decoded['halaman'] - 1) * $this->model->getLimit());
        $this->model->dataDonasi($this->_id_donatur);
        $this->data['donasi_donatur'] = $this->model->data()['data'];

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
            'pages' => $pages,
            'total_record' => $data['total_record']
        );

        $this->result();

        if ($this->_result['error'] == false) {
            Session::delete('toast');
        }
        return false;
    }

    private function tagihanTypeRead($decoded) {
        $this->_auth = $this->model('Auth');
        $this->_auth->getData('id_donatur','donatur',array('email','=',$this->_auth->data()->email));
        $this->_id_donatur = $this->_auth->data()->id_donatur;

        $this->model('Donasi');
        
        if (isset($decoded['search'])) {
            $this->model->setSearch($decoded['search']);
        }

        if (!isset($decoded['halaman'])) {
            $decoded['halaman'] = 1;
        }

        $this->model->setOffset(($decoded['halaman'] - 1) * $this->model->getLimit());
        $this->model->dataTagihan($this->_id_donatur, $decoded['tagihan_type']);
        $this->data['donasi_donatur'] = $this->model->data()['data'];

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
            'pages' => $pages,
            'total_record' => $data['total_record']
        );

        $this->result();

        if ($this->_result['error'] == false) {
            Session::delete('toast');
        }
        return false;
    }

    private function kuitansiUpdate($decoded) {
        $this->model('Donasi');
        $currentDate = new DateTime();
        $waktu_sekarang = $currentDate->format('Y-m-d H:i:s');
        $this->model->update('kuitansi', array('waktu_cetak' => $waktu_sekarang), array('id_kuitansi','=',$decoded['id_kuitansi']));
        if ($this->model->affected()) {
            $this->_result['error'] = false;
            $this->_result['feedback'] = array(
                'message' => 'Kuitansi <span class="font-weight-bolder">#' . $decoded['id_kuitansi'] . '</span> dicetak pada <span class="font-weight-bold">' . $waktu_sekarang . '</span>'
            );
        } else {
            $this->_result['feedback'] = array(
                'message' => 'Failed to Update waktu cetak kuitansi'
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