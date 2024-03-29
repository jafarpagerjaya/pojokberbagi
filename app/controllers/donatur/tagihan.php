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
                'href' => '/assets/route/admin/core/css/admin-style.css'
            ),
            array(
                'href' => '/assets/route/donatur/core/css/donatur.css'
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
            )
        );

        $this->data['akun'] = $this->_auth->data();

        $this->model("Donatur");
        $this->model->getAllData('donatur', array('email','=', $this->data['akun']->email));
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
            ),
            array(
                'src' => '/assets/route/donatur/pages/js/tagihan.js'
            )
        );

        $this->_donasi = $this->model('Donasi');
        $this->_donasi->dataTagihan($this->data['donatur']->id_donatur, '0');
        $dataTagihanUnpaid = $this->_donasi->data();
        // $dataTagihanUnpaidRecord = $this->_donasi->countRecordTagihan($this->data['donatur']->id_donatur, '0')->jumlah_record;
        $this->data['tagihan_unpaid'] = array(
            'halaman' => 1,
            'data' => $dataTagihanUnpaid['data'],
            'record' => $dataTagihanUnpaid['total_record'],
            'limit' => $this->model->getLimit(),
            'pages' => ceil($dataTagihanUnpaid['total_record'] / $this->model->getLimit())
        );

        $dataTagihanPaid = $this->_donasi->dataTagihan($this->data['donatur']->id_donatur, '1');
        $dataTagihanPaid = $this->_donasi->data();
        // $dataTagihanPaidRecord = $this->_donasi->countRecordTagihan($this->data['donatur']->id_donatur, '1')->jumlah_record;
        $this->data['tagihan_paid'] = array(
            'halaman' => 1,
            'data' => $dataTagihanPaid['data'],
            'record' => $dataTagihanPaid['total_record'],
            'limit' => $this->model->getLimit(),
            'pages' => ceil($dataTagihanPaid['total_record'] / $this->model->getLimit())
        );

        $this->_donasi->query("SELECT cp.id_cp, cp.nama nama_cp, cp.jenis jenis_cp, g.path_gambar path_gambar_cp  FROM channel_payment cp LEFT JOIN gambar g USING(id_gambar)");
        $this->data['channel_payment'] = $this->model->getResults();

        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();
    }
}