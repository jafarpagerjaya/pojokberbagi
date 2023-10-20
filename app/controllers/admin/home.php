<?php
class HomeController extends Controller {
    public function __construct() {
        $this->title = 'Admin';

        $this->rel_controller = array(
            array(
                'href' => '/assets/pojok-berbagi-style.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/admin-style.css'
            )
        );

        $this->script_controller = array(
            array(
                'src' => '/assets/pojok-berbagi-script.js'
            ),
            array(
                'src' => '/assets/route/admin/core/js/admin-script.js'
            )
        );

        $this->_auth = $this->model("Auth");

        if (!$this->_auth->hasPermission('admin')) {
            if ($this->_auth->isSignIn()) {
                $this->_auth->getData('nama, id_pegawai', 'pegawai', array('email','=',$this->_auth->data()->email));
                Session::flash('error','Assalamualaikum, '. ucwords(strtolower($this->_auth->data()->nama)) .' [PBI-'. $this->_auth->data()->id_pegawai .']');
            }
            Redirect::to('donatur');
        }

        $this->data['akun'] = $this->_auth->data();

        $this->_admin = $this->model("Admin");
        $this->_admin->getAllData('pegawai', array('email','=', $this->data['akun']->email));
        $this->data['pegawai'] = $this->_admin->getResult();

        $this->_admin->getData('alias', 'jabatan', array('id_jabatan','=',$this->data['pegawai']->id_jabatan));
        $this->data['admin_alias'] = $this->_admin->getResult()->alias;
    }

    public function index() {
        $this->script_action = array(
			array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/pages/js/home.js'
			)
		);
        // $this->setKunjungan();
        // Track real path based on return php request for js dom
		$this->data['uri'] = base64_encode($this->getRealUri());

        switch (strtoupper($this->data['admin_alias'])) {
            case 'SYS':
                $this->sys();
                return VIEW_PATH.'admin'.DS.'home'.DS.'sys.html';
            break;

            case 'CRE':
                $this->cr();
                return VIEW_PATH.'admin'.DS.'home'.DS.'cre.html';
            break;

            case 'DE':
                $this->de();
                return VIEW_PATH.'admin'.DS.'home'.DS.'de.html';
            break;
            
            default:
                Session::flash('error','Assalamualaikum, '. ucwords(strtolower($this->data['pegawai']->nama)) .' [PBI-'. $this->data['pegawai']->id_pegawai .']');
                Redirect::to('donatur');
                # code...
            break;
        }
    }

    public function de() {
        $this->title = 'Direcktur Eksekutif';
    }

    public function cr() {
        $this->title = 'CR';
        $this->model('Cr');
        $this->data['info-card'] = array(
            'jumlah_akun' => $this->model->jumlahAkun(),
            'jumlah_donatur' => $this->model->jumlahDonatur(),
            'jumlah_donasi' => $this->model->jumlahDonasi(),
            'jumlah_bantuan' => $this->model->jumlahBantuanAktif()
        );
    }

    public function sys() {
        $this->title = 'SYS';
        $this->model('Sys');
        $this->data['info-card'] = array(
            'jumlah_akun' => $this->model->jumlahAkun(),
            'jumlah_donatur' => $this->model->jumlahDonatur(),
            'jumlah_donasi' => $this->model->jumlahDonasi(),
            'jumlah_bantuan' => $this->model->jumlahBantuanAktif()
        );

        
        // fontte
        
        // $token = "PztvP7T!cE!jqs2!WH4R";
        // $target = "085860774199";

        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        // CURLOPT_URL => 'https://api.fonnte.com/send',
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => '',
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 0,
        // CURLOPT_FOLLOWLOCATION => true,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => 'POST',
        // CURLOPT_POSTFIELDS => array(
        // 'target' => $target,
        // 'message' => 'Donasi anda telah kami terima. Eit tapi Boong', 
        // 'countryCode' => '62', //optional
        // ),
        // CURLOPT_HTTPHEADER => array(
        //     "Authorization: {$token}" //change TOKEN to your actual token
        // ),
        // ));

        // $response = curl_exec($curl);

        // curl_close($curl);
        // echo $response;
    }
}