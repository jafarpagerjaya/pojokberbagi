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
        $this->model->getData('od.id_cp, LOWER(cp.jenis) jenis, cp.kode, od.end_at','order_donasi od JOIN channel_payment cp USING(id_cp)',array('id_order_donasi','=',Sanitize::escape2($decoded['id_order_donasi'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = 'Failed to get current jenis channel_payment on update payment method';
            $this->result();
            return false;
        }
        $currentDataCP = $this->model->getResult();

        if (!is_null($currentDataCP->end_at) && $currentDataCP->jenis != 'tb') {
            $expiry = strtotime($currentDataCP->end_at);
            if ($expiry > time()) {
                $this->_result['feedback'] = 'Metode pembayaran lama belum expired sehingga belum boleh diganti';
                $this->result();
                return false;
            }
        }

        $this->model->query("SELECT b.blokir, b.status, b.nama, b.tag, od.external_id, COALESCE(od.alias, d.samaran, d.nama) alias, od.jumlah_donasi, d.email FROM order_donasi od JOIN bantuan b USING(id_bantuan) JOIN donatur d USING(id_donatur) WHERE od.id_order_donasi = ?", array(
            'od.id_order_donasi' => Sanitize::escape2($decoded['id_order_donasi'])
        ));

        if (!$this->model->affected()) {
            $this->_result['feedback'] = 'Bantuan sudah tidak aktif';
            $this->result();
            return false;
        }

        $data_order_donasi = $this->model->getResult();

        if ($data_order_donasi->blokir == '1') {
            $this->_result['feedback'] = 'Bantuan <b>'. $data_order_donasi->nama .'</b> dengan ' . Utility::keteranganStatusBantuan($data_order_donasi->status) .' sedang diblokir';
            $this->result();
            return false;
        }

        if ($data_order_donasi->status != 'D') {
            $this->_result['feedback'] = 'Bantuan <b>'. $data_order_donasi->nama .'</b> ' . Utility::keteranganStatusBantuan($data_order_donasi->status);
            $this->result();
            return false;
        }

        $dataCP = $this->model->query("SELECT LOWER(cp.jenis) jenis_payment, cp.kode_paygate_brand FROM channel_payment cp JOIN channel_account ca USING(id_ca) JOIN penyelenggara_jasa_pembayaran pjp USING(id_pjp) WHERE (cp.id_cp = ? AND cp.kode = 'LIP') OR (cp.jenis = 'TB' AND cp.id_cp = ?)", 
            array(
                Sanitize::escape2($decoded['id_cp']),
                Sanitize::escape2($decoded['id_cp'])
            )
        );
        
        if ($dataCP == false) {
            $this->_result['feedback'] = 'Metode pembayaran tidak aktif, silahkan pilih metode lainnya';
            $this->result();
            return false;
        }

        $dataCP = $this->model->getResult();
        $jenis_payment = $dataCP->jenis_payment;

        $dataOrderDonasi = array(
            'id_cp' => Sanitize::escape2($decoded['id_cp']),
            'external_id' => NULL,
            'url' => NULL,
            'kode_pembayaran' => NULL,
            'end_at' => NULL
        );

        if ($jenis_payment != 'tb' && $jenis_payment != 'gi' && $jenis_payment != 'tn') {

            $secret_key = FLIP_API_KEY;

            $encoded_auth = base64_encode($secret_key.":");

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, FLIP_API."/v2/pwf/bill");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);

            curl_setopt($ch, CURLOPT_POST, TRUE);

            $hash_transaksi = $data_order_donasi->tag . '/' . Hash::unique();

            $payloads = [
                "title" => "Donasi ". $data_order_donasi->nama,
                "amount" => $data_order_donasi->jumlah_donasi,
                "type" => "SINGLE",
                "expired_date" => date('Y-m-d H:i', strtotime('+ 1 day')),
                // "redirect_url" => "https://pojokberbagi.id/donasi/pembayaran/transaksi/" . $hash_transaksi,
                "status" => "ACTIVE",
                "step" => 3,
                "is_address_required" => 1,
                "is_phone_number_required" => 0,
                "sender_name" => $data_order_donasi->alias,
                "sender_email" => $data_order_donasi->email,
                "sender_address" => Config::getHTTPHost(),
                // Ini untuk Step 3 namun Step 3 hanya bisa untuk VA dan QRIS
                "sender_bank" => ($dataCP->kode_paygate_brand == 'gopay' ? 'qris' : $dataCP->kode_paygate_brand),
                "sender_bank_type" => Utility::flipSenderBankType($jenis_payment)
            ];
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payloads));

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Basic ".$encoded_auth,
                "Content-Type: application/x-www-form-urlencoded"
            ));

            curl_setopt($ch, CURLOPT_USERPWD, $secret_key.":");

            $response = curl_exec($ch);
            curl_close($ch);
            $dataResponse = json_decode($response);

            if (property_exists($dataResponse, 'code')) {
                if ($dataResponse->code == 'VALIDATION_ERROR') {
                    $this->_result['feedback'] = "Flip Accept Payment [STEP 3] ". $dataResponse->errors[0]->message;
                    $this->result();
                    return false;
                }
            }

            $dataOrderDonasi['external_id'] = $dataResponse->link_id;
            $dataOrderDonasi['url'] = $dataResponse->link_url;
            $dataOrderDonasi['kode_pembayaran'] = $hash_transaksi;
            $dataOrderDonasi['end_at'] = $dataResponse->expired_date;
        }

        $this->model->startTransaction();
        $this->model->update('order_donasi', $dataOrderDonasi, array('id_order_donasi','=',Sanitize::escape2($decoded['id_order_donasi'])));
        if (!$this->model->affected()) {
            $this->_result['feedback'] = 'Terjadi kegagalan update order_donasi';
            $this->result();
            return false;
        }

        if ($jenis_payment == 'va') {
            $dataOrderVA = array(
                'account_number' => $dataResponse->bill_payment->receiver_bank_account->account_number
            );
        }

        if ($jenis_payment == 'qr') {
            $dataOrderQR = array(
                'qr_code' => $dataResponse->bill_payment->receiver_bank_account->qr_code_data
            );
        }

        if ($currentDataCP->jenis == $jenis_payment) {
            if ($jenis_payment == 'tb') {
                $this->model->commit();
                $this->_result['redirect'] = Config::getHTTPHost() . '/donasi/pembayaran/tagihan/'. $jenis_payment . '/' . Sanitize::escape2($decoded['id_order_donasi']);
            } else if ($jenis_payment == 'va') {
                $this->model->update('order_va', $dataOrderVA, array('id_order_donasi','=', Sanitize::escape2($decoded['id_order_donasi'])));
                if (!$this->model->affected()) {
                    $this->model->rollback();
                    $this->_result['feedback'] = 'Gagal mengganti channel_payment VA [update va]';
                    $this->result();
                    return false;
                }
            } else if ($jenis_payment == 'qr') {
                $this->model->update('order_qr', $dataOrderVA, array('id_order_donasi','=', Sanitize::escape2($decoded['id_order_donasi'])));
                if (!$this->model->affected()) {
                    $this->model->rollback();
                    $this->_result['feedback'] = 'Gagal mengganti channel_payment QRIS [update QRIS]';
                    $this->result();
                    return false;
                }
            } else {
                $this->model->rollback();
                $this->_result['feedback'] = 'Gagal mengganti channel_payment [unrecognize jenis_payment]';
                $this->result();
                return false;
            }
        } else {
            if ($jenis_payment == 'va') {
                $this->model->query("SELECT MAX(id_order_va)+1 as ov_sequence FROM order_va");
                if (!$this->model->affected()) {
                    $this->model->rollback();
                    $this->_result['feedback'] = 'Update Order Donasi Success But Failed Get Sequence OVA';
                    $this->result();
                    return false;
                } else {
                    $dataOrderVA['id_order_va'] = $this->model->getResult()->ov_sequence;
                }

                $dataOrderVA['id_order_donasi'] = Sanitize::escape2($decoded['id_order_donasi']);
                $this->model->create('order_va', $dataOrderVA);
                if (!$this->model->affected()) {
                    $this->model->rollback();
                    $this->_result['feedback'] = 'Update Order Donasi Success But Failed Create Order VA';
                    $this->result();
                    return false;
                }

                if ($currentDataCP->jenis == 'qr') {
                    $this->model->delete('order_qr', array('id_order_donasi','=',$dataOrderVA['id_order_donasi']));
                    if (!$this->model->affected()) {
                        $this->model->rollback();
                        $this->_result['feedback'] = 'Update Order Donasi Success But Failed Delete Order QR';
                        $this->result();
                        return false;
                    }
                }
            } else if ($jenis_payment == 'qr') {
                $this->model->query("SELECT MAX(id_order_qr)+1 as oq_sequence FROM order_qr");
                if (!$this->model->affected()) {
                    $this->model->rollback();
                    $this->_result['feedback'] = 'Update Order Donasi Success But Failed Get Sequence OQR';
                    $this->result();
                    return false;
                } else {
                    $dataOrderQR['id_order_qr'] = $this->model->getResult()->oq_sequence;
                }

                $dataOrderQR['id_order_donasi'] = Sanitize::escape2($decoded['id_order_donasi']);
                $this->model->create('order_qr', $dataOrderQR);
                if (!$this->model->affected()) {
                    $this->model->rollback();
                    $this->_result['feedback'] = 'Update Order Donasi Success But Failed Create Order QR';
                    $this->result();
                    return false;
                }

                if ($currentDataCP->jenis == 'va') {
                    $this->model->delete('order_va', array('id_order_donasi','=',$dataOrderQR['id_order_donasi']));
                    if (!$this->model->affected()) {
                        $this->model->rollback();
                        $this->_result['feedback'] = 'Update Order Donasi Success But Failed Delete Order VA';
                        $this->result();
                        return false;
                    }
                }
            } else if ($jenis_payment == 'tb') {
                if ($currentDataCP->jenis == 'qr') {
                    $this->model->delete('order_qr', array('id_order_donasi','=',Sanitize::escape2($decoded['id_order_donasi'])));
                    if (!$this->model->affected()) {
                        $this->model->rollback();
                        $this->_result['feedback'] = 'Update Order Donasi Success But Failed Delete Order QR';
                        $this->result();
                        return false;
                    }
                } else if ($currentDataCP->jenis == 'va') {
                    $this->model->delete('order_va', array('id_order_donasi','=',Sanitize::escape2($decoded['id_order_donasi'])));
                    if (!$this->model->affected()) {
                        $this->model->rollback();
                        $this->_result['feedback'] = 'Update Order Donasi Success But Failed Delete Order VA';
                        $this->result();
                        return false;
                    }
                }
            }
        }

        $this->model->commit();
        
        if ($jenis_payment == 'tb') {
            $id_update_record = Sanitize::escape2($decoded['id_order_donasi']);
        } else if ($jenis_payment == 'va' || $jenis_payment == 'qr') {
            $id_update_record = $dataOrderDonasi['external_id'];
        }

        $this->_result['error'] = false;
        $this->_result['redirect'] = Config::getHTTPHost() . '/donasi/pembayaran/tagihan/'. $jenis_payment . '/' . $id_update_record;
        $this->_result['feedback'] = 'Metode pembayaran berhasil diganti';
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

        $this->model->setLimit(5);
        $this->model->setOffset(($decoded['halaman'] - 1) * $this->model->getLimit());
        if ($decoded['tagihan_type'] == '1') {
            $this->model->dataTagihan($this->_id_donatur, $decoded['tagihan_type']);
        } else {
            $this->model->setDirection('DESC');
            $this->model->setOrder('od.create_at');
            $offset_mode = true;
            $this->model->setHalaman($decoded['halaman'], 'order_donasi', $offset_mode);
            $this->model->getListOrderDonasi($this->_id_donatur);
        }
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
            'total_record' => $data['total_record'],
            'target' => $decoded['target']
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