<?php
class PengunjungController extends Controller {
    
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

        $this->title = 'Akun';
        
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
        $this->rel_action = array(
            array(
                // 'href' => '/assets/route/admin/pages/css/pengunjung.css'
            )
        );

        $this->model('Sys');
        $this->data['info-card'] = array(
            // 'total_pengunjung' => $this->model->totalPengunjung(),
            // 'jumlah_pengunjung_bulan' => $this->model->jumlahPengunjungBulan(),
            // 'jumlah_pengunjung_minggu' => $this->model->jumlahPengunjungMinggu()
        );

        $dataPengunjung = $this->_auth->getDataPengunjung();
        $this->data['halaman'] = 1;
        $this->data['record'] = $this->_auth->countData();
        if ($dataPengunjung != false) {
            $this->data['list_pengunjung'] = $dataPengunjung;
        }
    }
}