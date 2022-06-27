<?php
class DonasiController extends Controller {
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
        $this->model->setLimit(5);
        $this->model->setAscDsc('Desc');
        $table = 'donasi';
        $this->model->setHalaman(1, $table);
        $this->model->setOrderBy('d.create_at');
        $this->model->getListDonasi();

        // Debug::pr($this->model);die();

        $this->data['list_donasi'] = $this->model->data();
        $this->data['limit'] = $this->model->getLimit();
        $this->data['pages'] = ceil($this->data['list_donasi']['total_record'] / $this->model->getLimit());
        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();
    }

    public function buat() {
        $this->title = 'Buat Donasi Via CR';
        $this->rel_action = array(
            array(
                'href' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
            ),
            array(
                'href' => '/vendors/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/form-element.css'
            ),
            array(
                'href' => '/assets/route/admin/pages/css/buat-donasi.css'
            )
        );
        $this->script_action = array(
            array(
                'src' => '/assets/main/js/token.js'
            ),
            array(
                'src' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                'source' => 'trushworty'
            ),
            array(
                'src' => '/vendors/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js'
            ),
            array(
                'src' => '/vendors/bootstrap-datepicker/dist/locales/bootstrap-datepicker.id.min.js',
                'charset' => 'UTF-8'
            ),
            array(
                'src' => '/assets/pojok-berbagi-script.js'
            ),
            array(
                'src' => '/assets/route/admin/core/js/admin-script.js'
            ),
            array(
                'src' => '/assets/route/admin/core/js/form-function.js'
            ),
            array(
                'src' => '/assets/route/admin/pages/js/buat-donasi.js'
            )
        );

        $this->model('Donasi');

        $lastDonasi = $this->model->getLastDonasi(5);
        if ($lastDonasi) {
            $this->data['donasi_terverivikasi_terakhir'] = $lastDonasi;
        }

        $bantuan = $this->model->query("SELECT b.id_bantuan, b.nama nama_bantuan, IFNULL(k.nama, '') nama_kategori, IFNULL(s.nama,'') nama_sektor FROM bantuan b LEFT JOIN kategori k USING(id_kategori) LEFT JOIN sektor s USING(id_sektor) WHERE blokir IS NULL ORDER BY b.prioritas DESC, b.create_at DESC, b.id_bantuan ASC LIMIT 15");
        if ($bantuan) {
            $this->data['data_bantuan'] = $this->model->readAllData();
        }

        $donatur = $this->model->query("SELECT id_donatur, nama, email, kontak FROM donatur ORDER BY id_donatur DESC LIMIT 15");
        if ($donatur) {
            $this->data['data_donatur'] = $this->model->readAllData();
        }

        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();
    }
}