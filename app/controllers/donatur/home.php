<?php
class HomeController extends Controller {

    private $_id_donatur;

    public function __construct() {
		$this->title = 'Donatur';
		
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
				'type' => 'text/javascript',
                'src' => '/assets/pojok-berbagi-script.js'
			),
            array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/core/js/admin-script.js'
            ),
			array(
				'type' => 'text/javascript',
                'src' => '/assets/route/donatur/pages/js/home.js'
			)
		);

        $this->_auth = $this->model("Auth");
        if (!$this->_auth->hasPermission('donatur')) {
            Redirect::to('');
        }

        $this->data['other_role'] = $this->_auth->otherPermission('donatur');

        $this->data['akun'] = $this->_auth->data();

        $this->model("Donatur");
        if (is_null($this->data['akun']->email)) {
            $akun_value =  $this->data['akun']->kontak; 
            $akun_field = 'kontak';
        } else {
            $akun_value =  $this->data['akun']->email; 
            $akun_field = 'email';
        }
        $this->model->getAllData('donatur', array($akun_field,'=', $akun_value));
        $this->data['donatur'] = $this->model->getResult();
        $this->_id_donatur = $this->data['donatur']->id_donatur;
        $this->data['route_alias'] = 'donatur';
	}

    public function index() {
        $this->rel_action = array(
            array(
                'href' => '/assets/main/css/pagination.css'
			),
            array(
                'href' => '/assets/main/css/utility.css'
			),
            array(
                'href' => '/assets/main/css/inputGroup.css'
			),
            array(
                'href' => '/assets/main/css/kuitansi.css'
            ),
            array(
                'href' => '/assets/route/donatur/core/css/donatur.css'
			)
        );

        $this->script_action = array (
            array(
                'src' => '/assets/route/admin/core/js/form-function.js'
			),
            array(
                'src' => '/assets/main/js/pagination.js'
			),
            array(
                'src' => 'https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js',
                'source' => 'trushworty'
            ),
            array(
                'src' => '/assets/route/donatur/core/js/kuitansi.js'
            ),
            array(
                'src' => '/assets/route/donatur/core/js/donatur.js'
            )
        );

        $this->model('Donatur');
        $this->data['info-card'] = array(
            'jumlah_tagihan_unpaid' => $this->model->countJumlahTagihan('0', $this->data['donatur']->id_donatur)->jumlah_tagihan,
            'jumlah_tagihan_paid' => $this->model->countJumlahTagihan('1', $this->data['donatur']->id_donatur)->jumlah_tagihan,
            'jumlah_total_donasi' => $this->model->getTotalDonasi($this->data['donatur']->id_donatur)->jumlah_total_donasi,
            'jumlah_info_bantuan' => $this->model->getJumlahDonasiTersalurkan($this->data['donatur']->id_donatur)->jumlah_info_bantuan
        );

        // Tabel data D n T
        $this->_donasi = $this->model('Donasi');
        $this->_donasi->dataDonasi($this->_id_donatur);
        $this->data['donasi_donatur'] = $this->_donasi->data()['data'];
        $this->data['limit'] = $this->model->getLimit();
        $this->data['pages'] = ceil($this->_donasi->data()['total_record'] / $this->data['limit']);

        $this->_donasi->query("SELECT cp.id_cp, cp.nama nama_cp, cp.jenis jenis_cp, g.path_gambar path_gambar_cp  FROM channel_payment cp LEFT JOIN gambar g USING(id_gambar)");
        $this->data['channel_payment'] = $this->model->getResults();

        // TToken for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();
        
        // $this->setKunjungan();
		// Track real path based on return php request for js dom
		$this->data['uri'] = base64_encode($this->getRealUri());
    }
}