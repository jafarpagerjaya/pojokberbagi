<?php
class RabController extends Controller{
    private $_rencana;

    public function __construct() {
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

        $this->title = 'RAB';
        
        $this->_auth = $this->model("Auth");
        if (!$this->_auth->hasPermission('admin')) {
            Redirect::to('donatur');
        }

        $this->data['akun'] = $this->_auth->data();

        $this->_admin = $this->model("Admin");
        $this->_admin->getAllData('pegawai', array('email','=', $this->data['akun']->email));
        $this->data['pegawai'] = $this->_admin->getResult();

        if (is_null($this->data['pegawai']->id_jabatan)) {
            Redirect::to('donatur');
        }

        $this->_admin->getData('alias', 'jabatan', array('id_jabatan','=',$this->data['pegawai']->id_jabatan));
        $this->data['admin_alias'] = $this->_admin->getResult()->alias;
    }

    public function index() {
        $this->_rencana = $this->model('Rab');
        $this->data['halaman'] = 1;
        $this->_rencana->setLimit(6);
        // $this->_rencana->setSearch('Geber');
        $this->_rencana->setHalaman($this->data['halaman'], 'rencana');
        $this->_rencana->getListRencana();

        $this->data['list_rab'] = $this->_rencana->data();
        $this->data['list_rab']['limit'] = $this->_rencana->getLimit();
        $this->data['list_rab']['pages'] = ceil($this->data['list_rab']['total_record'] / $this->model->getLimit());

        $this->_rencana->resumeRab();
        $this->data['info_card'] = $this->_rencana->getResult();

        $this->rel_action = array(
            array(
                'href' => VENDOR_PATH.'bootstrap-datepicker'.DS.'dist'.DS.'css'.DS.'bootstrap-datepicker.min.css'
            ),
            array(
                'href' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
                'source' => 'trushworty'
            ),
            array(
                'href' => '/assets/main/css/pagination.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/form-element.css'
            ),
            array(
                'href' => '/assets/main/css/utility.css'
            ),
            array(
                'href' => '/assets/main/css/stepper.css'
            ),
            array(
                'href' => VENDOR_PATH.'cropper'.DS.'dist'.DS.'cropper.min.css'
            ),
            array(
                'href' => '/assets/main/css/inputGroup.css'
            ),
            array(
                'href' => '/assets/route/admin/pages/css/rab.css'
            )
        );
        $this->script_action = array(
            array(
                'src' => VENDOR_PATH.'bootstrap-datepicker'.DS.'dist'.DS.'js'.DS.'bootstrap-datepicker.min.js'
            ),
            array(
                'src' => VENDOR_PATH.'bootstrap-datepicker'.DS.'dist'.DS.'locales'.DS.'bootstrap-datepicker.id.min.js',
                'charset' => 'UTF-8'
            ),
            array(
                'src' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                'source' => 'trushworty'
            ),
            array(
                'src' => '/assets/main/js/pagination.js'
            ),
            array(
                'src' => '/assets/route/admin/core/js/form-function.js'
            ),
            array(
                'src' =>  VENDOR_PATH.'cropper'.DS.'dist'.DS.'cropper.min.js'
            ),
            array(
                'src' => '/assets/main/js/token.js'
            ),
            array(
                'src' => '/assets/main/js/stepper.js'
            ),
            array(
                'src' => '/assets/route/admin/pages/js/rab.js'
            )
        );
        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();
    }
}