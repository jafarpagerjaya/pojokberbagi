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
            Redirect::to('donatur');
        }

        $this->data['akun'] = $this->model->data();

        $this->model("Admin");
        $this->model->getAllData('pegawai', array('email','=', $this->data['akun']->email));
        $this->data['pegawai'] = $this->model->data();

        $this->model->getData('alias', 'jabatan', array('id_jabatan','=',$this->data['pegawai']->id_jabatan));
        $this->data['admin_alias'] = $this->model->data()->alias;
    }

    public function index($params) {
        $this->title = 'Kelola Donasi';
        $this->rel_action = array(
            array(
                'href' => '/assets/main/css/pagination.css'
            ),
            array(
                'href' => '/assets/route/admin/pages/css/donasi.css'
            ),
            array(
                'href' => '/vendors/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css'
            ),
            array(
                'href' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css'
            ),
            array(
                'href' => '/assets/main/css/utility.css'
            ),
            array(
                'href' => '/assets/main/css/inputGroup.css'
            ),
            array(
                'href' => '/assets/route/admin/pages/css/verivikasi-donasi.css'
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
            ),
            array(
                'src' => '/vendors/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js'
            ),
            array(
                'src' => '/vendors/bootstrap-datepicker/dist/locales/bootstrap-datepicker.id.min.js'
            ),
            array(
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.0/moment-with-locales.js',
                'source' => 'trushworty'
            ),
            array(
                'src' => 'https://cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/5a991bff/src/js/bootstrap-datetimepicker.js',
                'source' => 'trushworty'
            ),
            array(
                'src' => '/assets/route/admin/pages/js/verivikasi-donasi.js'
            )
        );
        $this->model('Donasi');
        $result = $this->model->getCountUpDonasi();
        if ($result == true) {
            $this->data['countUpDonasi'] = $this->model->data();
        }
        $this->model->getSaldoDonasi();
        $this->data['saldo_donasi'] = $this->model->data();

        if (isset($params[0])) {
            switch ($params[0]) {
                case 'terverivikasi':
                    $search_value = 'sudah diverivikasi';
                    break;
                case 'belum-verivikasi':
                    $search_value = 'belum diverivikasi';
                    break;
                default:
                    break;
            }
            if (!empty($search_value)) {
                $this->model->setSearch($search_value);
                $this->data['search'] = $search_value;
            }
        }
        // $search_value = 'tr';
        // $this->model->setSearch($search_value);
        $this->model->setLimit(5);
        $this->model->setDirection('Desc');
        $table = 'donasi';
        $this->model->setHalaman(1, $table);
        $this->model->setOrder('d.create_at');
        $this->model->getListDonasi();

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
                'href' => '/assets/main/css/utility.css'
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

        $donatur = $this->model->query("SELECT id_donatur, nama, email, kontak FROM donatur ORDER BY id_donatur DESC LIMIT 25");
        if ($donatur) {
            $this->data['data_donatur'] = $this->model->readAllData();
        }

        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();
    }
}