<?php
class TagihanController extends Controller {
    public function __construct() {

        $this->_auth = $this->model("Auth");
        if (!$this->_auth->hasPermission('donatur')) {
            Redirect::to('home');
        }

        $this->title = 'Tagihan';
        $this->rel_controller = array(
            array(
                'href' => '/assets/pojok-berbagi-style.css'
			),
            array(
                'href' => '/assets/route/donatur/core/css/donatur.css'
			)
        );

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
                'href' => '/assets/main/css/utility.css'
			),
            array(
                'href' => '/assets/main/css/inputGroup.css'
			),
            array(
                'href' => '/assets/route/donatur/core/css/donatur.css'
			)
        );

        $this->script_action = array (
            array(
                'src' => '/assets/route/donatur/core/js/donatur.js'
            )
            // array(
                //         'href' => '/assets/route/donatur/pages/css/tagihan.css'
                //     )
        );

        $this->_donasi = $this->model('Donasi');
        $dataTagihanUnpaid = $this->_donasi->dataTagihan($this->data['donatur']->id_donatur, '0');
        $dataTagihanUnpaidRecord = $this->_donasi->countRecordTagihan($this->data['donatur']->id_donatur, '0')->jumlah_record;
        $this->data['tagihan_unpaid'] = array(
            'halaman' => 1,
            'data' => $dataTagihanUnpaid,
            'record' => $dataTagihanUnpaidRecord
        );

        $dataTagihanPaid = $this->_donasi->dataTagihan($this->data['donatur']->id_donatur, '1');
        $dataTagihanPaidRecord = $this->_donasi->countRecordTagihan($this->data['donatur']->id_donatur, '0')->jumlah_record;
        $this->data['tagihan_paid'] = array(
            'halaman' => 1,
            'data' => $dataTagihanPaid,
            'record' => $dataTagihanPaidRecord
        );

        $this->_donasi->query("SELECT cp.id_cp, cp.nama nama_cp, cp.jenis jenis_cp, g.path_gambar  FROM channel_payment cp LEFT JOIN gambar g USING(id_gambar)");
        $this->data['channel_payment'] = $this->model->readAllData();

        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();
    }
}