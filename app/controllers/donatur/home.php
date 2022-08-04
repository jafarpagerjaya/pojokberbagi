<?php
class HomeController extends Controller {

    private $_id_donatur;

    public function __construct() {
		$this->title = 'Donatur';
		
        $this->rel_controller = array(
            array(
                'href' => '/assets/pojok-berbagi-style.css'
			)
        );

        $this->script_controller = array(
            array(
				'type' => 'text/javascript',
                'src' => '/assets/pojok-berbagi-script.js'
			),
			array(
				'type' => 'text/javascript',
                'src' => '/assets/route/donatur/pages/js/home.js'
			)
		);

        $this->_auth = $this->model("Auth");
        if (!$this->_auth->hasPermission('donatur')) {
            Redirect::to('home');
        }

        $this->data['akun'] = $this->_auth->data();

        $this->model("Donatur");
        $this->model->getAllData('donatur', array('email','=', $this->data['akun']->email));
        $this->data['donatur'] = $this->model->data();
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
                'href' => '/assets/main/css/kwitansi.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/admin-style.css'
			),
            array(
                'href' => '/assets/route/donatur/core/css/donatur.css'
			)
        );

        $this->script_action = array (
            array(
                'src' => '/assets/main/js/pagination.js'
			),
            array(
                'src' => 'https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js',
                'source' => 'trushworty'
            ),
            array(
                'src' => '/assets/route/donatur/core/js/kwitansi.js'
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
        $data_donasi = $this->_donasi->dataDonasi($this->_id_donatur);
        $this->data['donasi_donatur'] = $this->_donasi->data()['data'];
        $this->data['limit'] = $this->model->getLimit();
        $this->data['pages'] = ceil($this->_donasi->data()['total_record'] / $this->data['limit']);

        $this->_donasi->query("SELECT cp.id_cp, cp.nama nama_cp, cp.jenis jenis_cp, g.path_gambar  FROM channel_payment cp LEFT JOIN gambar g USING(id_gambar)");
        $this->data['channel_payment'] = $this->model->readAllData();

        // TToken for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();
        
        // $this->setKunjungan();
		// Track real path based on return php request for js dom
		$this->data['uri'] = base64_encode($this->getRealUri());
    }
}