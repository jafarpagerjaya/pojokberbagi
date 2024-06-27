<?php
class BuatController extends Controller {

    private $_minDonasi,
            $_auth,
            $_home;

    public function __construct() {
        $this->rel_controller = array(
            array(
                'href' => '/assets/pojok-berbagi-style.css'
            ),
            array(
                'href' => '/assets/route/donasi/core/css/donasi.css'
            )
        );
        $this->script_controller = array(
            array(
                'src' => '/assets/pojok-berbagi-script.js'
            ),
            array(
                'src' => '/assets/route/donasi/core/js/donasi.js'
            )
        );
    }

    public function index($params) {
        if (count(is_countable($params) ? $params : []) > 0) {
            $this->baru($params);
            return VIEW_PATH.'donasi'.DS.'buat'.DS.'baru.html';
        } else {
            Redirect::to('home');
        }
    }

    public function baru($params) {
        if (!count(is_countable($params) ? $params : [])) {
            Redirect::to('home');
        }
        
        $this->rel_action = array(
            array(
                'href' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
            ),
            array(
                'href' => '/assets/main/css/inputGroup.css'
            ),
            array(
                'href' => '/assets/route/donasi/pages/css/baru.css'
            )
        );
        
        $this->script_action = array(
            array(
                'src' => '/assets/main/js/terbilang.js'
            ),
            array(
                'src' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
                'source' => 'trushworty'
            ),
            array(
                'src' => '/vendors/crypto-js/js/crypto-js.js'
                // 'src' => 'https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.js',
                // 'source' => 'trushworty'
            ),
            array(
                'src' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                'source' => 'trushworty'
            ),
            array(
                'src' => '/assets/main/js/token.js'
            ),
            array(
                'src' => '/assets/route/donasi/pages/js/baru.js'
            )
        );

        $this->model('Donasi');
        $this->model->query("SELECT COUNT(id_bantuan) found, id_bantuan FROM bantuan WHERE id_bantuan = ? OR tag = ? AND status IN ('D','S') GROUP BY id_bantuan", array('id_bantuan' => $params[0], 'tag' => $params[0]));
        if ($this->model->getResult()->found == 0) {
            Session::flash('notifikasi', array(
                'pesan' => 'Halaman donasi yang anda cari tidak ditemukan',
                'state' => 'warning'
            ));
            Redirect::to('home');
        }
        $id_bantuan = $this->model->getResult()->id_bantuan;

        $data_bantuan = $this->model->isBantuanActive($id_bantuan);
        if ($data_bantuan == false) {
            Session::flash('notifikasi', array(
                'pesan' => 'Bantuan tidak ditemukan',
                'state' => 'warning'
            ));
            Redirect::to('home');
        }
        if ($data_bantuan->blokir == '1') {
            Session::flash('notifikasi', array(
                'pesan' => 'Bantuan <b>'. $data_bantuan->nama .'</b> dengan ' . Utility::keteranganStatusBantuan($data_bantuan->status) .' sedang diblokir',
                'state' => 'danger'
            ));
            Redirect::to('home');
        }
        // Cek jika bantuan masih dibuka
        if ($data_bantuan->status != 'D') {
            Session::flash('notifikasi', array(
                'pesan' => 'Bantuan <b>'. $data_bantuan->nama .'</b> ' . Utility::keteranganStatusBantuan($data_bantuan->status),
                'state' => 'warning'
            ));
            Redirect::to('home');
        }
        // Cek jika open donasi sudah berakhir
        if (!is_null($data_bantuan->tanggal_akhir) && strtotime($data_bantuan->tanggal_akhir) <= time()) {
            $this->model->update('bantuan', array(
                'status' => 'S'
            ), array('id_bantuan','=',$id_bantuan));
            Session::flash('notifikasi', array(
                'pesan' => 'Mohon maaf bantuan sudah berakhir',
                'state' => 'warning'
            ));
            Redirect::to('home');
        }
        if (!$data_bantuan) {
            Session::flash('notifikasi', array(
                'pesan' => 'ID Bantuan <b>'. $id_bantuan .'</b> tidak ditemukan',
                'state' => 'danger'
            ));
            Redirect::to('home');
        }

        $this->_home = $this->model('Home');
        $this->model('Auth');
        $this->data['akun'] = $this->model->data();
		if ($this->model->isSignIn()) {
            $staff = $this->model->isStaff($this->data['akun']->email, 'email');
            if ($staff) {
                $data = $this->_home->query('SELECT nama, email, kontak, (SELECT samaran FROM donatur WHERE email = ?) samaran FROM pegawai WHERE email = ?', array($this->data['akun'] ->email, $this->data['akun'] ->email));                
            } else {
                $data = $this->_home->getData('nama, email, kontak, samaran', 'donatur', array('id_akun','=', $this->data['akun']->id_akun));
            }
            if ($data) {
                $this->data['donatur'] = $this->_home->getResult();
            }
        }

        Session::put('donasi', $id_bantuan);
        $this->data['bantuan'] = $data_bantuan;
        $this->data[Config::get('session/token_name')] = Token::generate();
    }

    public function get($params) {
		if (count(is_countable($params) ? $params : []) > 0) {
			$fetch = new Fetch();

			switch ($params[0]) {
				case 'channel-payment':
					// channelPayment Params
                    $params[0] = 'ChannelPayment';
				break;
				
				default:
					$this->_result['feedback'] = array(
						'message' => 'Unrecognize params '. $params[0]
					);
					$this->result();
					return false;
				break;
			}

			$decoded = $fetch->getDecoded();

			// prepare method Token name
			$action = __FUNCTION__ . $params[0];
			// call method Token
			$this->$action($decoded, $fetch);
			
			return false;
		} else {
			Redirect::to('/fallback/fetch');
		}
	}

    public function getChannelPayment($decoded, $fetch) {
        $this->model("Donasi");
        // Sementara TB dulu 
        // $dataCP = $this->model->query("SELECT cp.id_cp, cp.nama, cp.jenis, gambar.path_gambar, gambar.nama nama_partner FROM channel_payment cp JOIN channel_account ca USING(id_ca) LEFT JOIN gambar USING(id_gambar) WHERE cp.jenis = 'TB'", array());
        $dataCP = $this->model->query("SELECT cp.id_cp, cp.nama, cp.jenis, gambar.path_gambar, gambar.nama nama_partner FROM channel_payment cp JOIN channel_account ca USING(id_ca) LEFT JOIN gambar USING(id_gambar) WHERE cp.jenis = 'TB' OR ca.jenis = 'PG'", array());
        if (!$dataCP) {
            $fetch->addResults(array(
                'feedback' => array(
                    'massage' => 'Failed to get Channel Payment'
                )
            ));
        } else {
            $fetch->addResults(array(
                'error' => false
            ));
            $fetch->addResults(array(
                'feedback' => array(
                    'data' => $this->model->getResults()
                )
            ));
        }
        $fetch->result();
    }

    public function ajax() {
        if (!isset($_POST['email'])) {
            return false;
        }
        $this->model('Donasi');
        $data = $this->model->getSamaranDonatur($_POST['email']);
        echo $data;
        return false;
    }

    // public function flip() {
    //     $secret_key = FLIP_API_KEY;

    //     $encoded_auth = base64_encode($secret_key.":");

    //     $ch = curl_init();

    //     curl_setopt($ch, CURLOPT_URL, FLIP_API."/v2/pwf/112843/payment");
    //     // curl_setopt($ch, CURLOPT_URL, FLIP_API."/v2/pwf/112835/bill");
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    //     curl_setopt($ch, CURLOPT_HEADER, FALSE);

        // $payloads = [
        //     "title" => "Coffee Table 2",
        //     "amount" => 50000,
        //     "type" => "SINGLE",
        //     "expired_date" => date('Y-m-d H:i', strtotime('+ 1 day')),
        //     "redirect_url" => "https://someurl.com",
        //     "status" => "ACTIVE",
        //     "is_address_required" => 1,
        //     "is_phone_number_required" => 0,
        //     "sender_name" => 'Jafar',
        //     "sender_email" => 'jafarpager@gmail.com',
        //     "sender_address" => Config::getHTTPHost(),
        //     // Ini untuk Step 3 namun Step 3 hanya bisa untuk VA dan QRIS
        //     "step" => 3,
        //     "sender_bank" => 'bca',
        //     "sender_bank_type" => 'virtual_account'
        // ];

        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payloads));

    //     curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //         "Authorization: Basic ".$encoded_auth,
    //         "Content-Type: application/x-www-form-urlencoded"
    //     ));

    //     curl_setopt($ch, CURLOPT_USERPWD, $secret_key.":");

    //     $response = curl_exec($ch);
    //     curl_close($ch);
    //     $dataResponse = json_decode($response);

    //     Debug::prd($dataResponse);
    //     return false;
    // }
}