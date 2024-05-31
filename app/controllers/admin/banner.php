<?php
class BannerController extends COntroller {

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

        $this->title = 'Banner';
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
        
        $this->_banner = $this->model('Banner');
        $this->_banner->setLimit(10);
        $this->data['limit'] = $this->getPageRecordLimit();
    }

    public function index() {
        $this->rel_action = array(
            array(
                'href' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
            ),
            array(
                'href' => '/assets/main/css/utility.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/form-element.css'
            ),
            array(
                'href' => '/assets/route/admin/pages/css/banner.css'
            )
        );

        $this->script_action = array(
            array(
                'type' => 'text/javascript',
                'src' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                'source' => 'trushworty'
            ),
            array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/core/js/form-function.js'
			),
            array(
				'type' => 'text/javascript',
                'src' => '/assets/main/js/token.js'
			),
            array(
                'type' => 'text/javascript',
                'src' => '/assets/route/admin/pages/js/banner.js'
            )
        );

        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();

        $this->data['data_banner'] = array_map(function($array) {
                if ($array->status == 'aktif') {
                    $array->status = array(
                        'text' => $array->status,
                        'class' => 'badge-success'
                    );
                } else if ($array->status == 'belum aktif') {
                    $array->status = array(
                        'text' => $array->status,
                        'class' => 'badge-warning'
                    );
                } else if ($array->status == 'kadaluarsa') {
                    $array->status = array(
                        'text' => $array->status,
                        'class' => 'badge-danger'
                    );
                } else if ($array->status == 'aktif') {
                    $array->status = array(
                        'text' => $array->status,
                        'class' => 'badge-secondary'
                    );
                } else if ($array->status == 'sudah ditakedown') {
                    $array->status = array(
                        'text' => $array->status,
                        'class' => 'badge-neutral'
                    );
                } else {
                    $array->status = array(
                        'text' => 'kosong',
                        'class' => 'badge-primary'
                    );
                }
            return $array;
        }, $this->_banner->readBanner());
    }
}