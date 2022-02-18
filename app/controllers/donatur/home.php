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
                'href' => '/assets/route/donatur/core/css/donatur.css'
			)
        );

        $this->script_action = array(
			array(
				'type' => 'text/javascript',
                'src' => ASSET_PATH . 'route' . DS . basename(dirname(__FILE__)).DS.'pages'.DS.'js'.DS.'home.js'
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
        $this->model('Donatur');
        $this->data['info-card'] = array(
            'jumlah_tagihan_unpaid' => $this->model->countJumlahTagihan('0', $this->data['donatur']->id_donatur)->jumlah_tagihan,
            'jumlah_tagihan_paid' => $this->model->countJumlahTagihan('1', $this->data['donatur']->id_donatur)->jumlah_tagihan,
            'jumlah_total_donasi' => $this->model->getTotalDonasi($this->data['donatur']->id_donatur)->jumlah_total_donasi,
            'jumlah_info_bantuan' => $this->model->getJumlahDonasiTersalurkan($this->data['donatur']->id_donatur)->jumlah_info_bantuan
        );

        $this->_donasi = $this->model('Donasi');
        $data_donasi = $this->_donasi->dataDonasi($this->_id_donatur);
        $this->data['donasi_donatur'] = $data_donasi;
        $this->data['halaman'] = 1;
        $this->data['record'] = $this->_donasi->affected();

        // $this->setKunjungan();
		// Track real path based on return php request for js dom
		$this->data['uri'] = base64_encode($this->getRealUri());
    }
}