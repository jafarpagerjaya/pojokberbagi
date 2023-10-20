<?php
class AnggaranController extends Controller {
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

        $this->title = 'Bantuan';
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
        
        $this->_bantuan = $this->model('Bantuan');
        $this->_bantuan->setLimit($this->getPageRecordLimit());
        $this->data['limit'] = $this->getPageRecordLimit();
    }

    public function index() {
        $this->model('Bantuan');
        $this->data['halaman'] = 1;
        $this->model->setDataBetween($this->data['halaman']);
        $this->model->anggaranPelaksanaanList();
        $this->data['pelaksanaan'] = $this->model->data();
        
        if ($this->model->countData('pelaksanaan') != false) {
            $this->data['record'] = $this->model->countData('pelaksanaan')->jumlah_record;
        } else {
            $this->data['record'] = 0;
        }

        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();
    }
}