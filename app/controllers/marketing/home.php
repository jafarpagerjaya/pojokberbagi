<?php
class HomeController extends Controller {
    public function __construct() {
        $this->title = 'Marketing';

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

        $this->_auth = $this->model("Auth");
        if (!$this->_auth->hasPermission('marketing') && !$this->_auth->hasPermission('admin')) {
            Redirect::to('');
        }

        $this->data['akun'] = $this->_auth->data();

        $this->model("Donatur");
        if (is_null($this->data['akun']->email)) {
            $akun_value =  $this->data['akun']->kontak; 
            $akun_field = 'd.kontak';
        } else {
            $akun_value =  $this->data['akun']->email; 
            $akun_field = 'd.email';
        }
        $this->model->getAllData('donatur d JOIN akun a ON(d.id_akun = a.id_akun) LEFT JOIN marketing m ON(m.id_akun = a.id_akun)', array($akun_field,'=', $akun_value));
        $this->data['marketing'] = $this->model->getResult();
        $this->_id_donatur = $this->data['marketing']->id_donatur;
        $this->_id_marketing = $this->data['marketing']->id_marketing;
        $this->data['route_alias'] = 'marketing';
        $this->_auth = $this->model("Auth");

        $this->_campaign = $this->model('Campaign');
        $this->_campaign->setLimit(6);
        $this->data['limit'] = $this->getPageRecordLimit();
    }

    public function index() {
        Redirect::to('marketing/campaign');
    }
}