<?php
class DonasiController extends Controller {
    public function __construct() {
        $this->title = 'Admin';

        $this->rel_controller = array(
            array(
                'href' => '/assets/route/admin/core/css/admin-style.css'
			)
        );
        
        $this->model("Auth");
        
        if (!$this->model->hasPermission('admin')) {
            Redirect::to('home');
        }

        $this->data['akun'] = $this->model->data();

        $this->model("Admin");
        $this->model->getAllData('pegawai', array('email','=', $this->data['akun']->email));
        $this->data['pegawai'] = $this->model->data();

        $this->model->getData('alias', 'jabatan', array('id_jabatan','=',$this->data['pegawai']->id_jabatan));
        $this->data['admin_alias'] = $this->model->data()->alias;
    }

    public function index() {
        $this->title = 'Kelola Donasi';
        $this->rel_action = array(
            array(
                'href' => '/assets/route/admin/pages/css/data.css'
            ),
            array(
                'href' => '/assets/main/css/pagination.css'
            ),
            array(
                'href' => '/assets/route/admin/pages/css/donasi.css'
            )
        );
        $this->script_action = array(
            array(
                'src' => '/assets/pojok-berbagi-script.js'
            ),
            array(
                'src' => '/assets/main/js/pagination.js'
            ),
            array(
                'src' => '/assets/main/js/token.js'
            ),
            array(
                'src' => '/assets/route/admin/core/js/admin-script.js'
            ),
            array(
                'src' => '/assets/route/admin/core/js/form-function.js'
            ),
            array(
                'src' => '/assets/route/admin/pages/js/donasi.js'
            )
        );
        $this->model('Donasi');
        $result = $this->model->getCountUpDonasi();
        if ($result == true) {
            $this->data['countUpDonasi'] = $this->model->data();
        }
        $this->model->getSaldoDonasi();
        $this->data['saldo_donasi'] = $this->model->data();

        // $search_value = 'tr';
        // $this->model->setSearch($search_value);
        $this->model->setOffset(2);
        $this->model->setAscDsc('Desc');
        $table = 'donasi';
        $this->model->setHalaman(1, $table);
        $this->model->setOrderBy('d.create_at');
        $this->model->getListDonasi();
        $this->data['list_donasi'] = $this->model->data();
        $this->data['limit'] = $this->model->getOffset();
        $this->data['pages'] = ceil($this->data['list_donasi']['total_record'] / $this->model->getOffset());
        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();
    }
}