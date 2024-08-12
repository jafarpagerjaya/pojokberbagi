<?php
class TransaksiController extends Controller {
    public function __construct() {
		$this->title = 'Donatur';
		


        $this->model("Auth");
        
        if (!$this->model->hasPermission('donatur')) {
            Redirect::to('');
        }

        $this->data['akun'] = $this->model->data();

        $this->model("Donatur");
        if (is_null($this->data['akun']->email)) {
            $akun_value =  $this->data['akun']->kontak; 
            $akun_field = 'kontak';
        } else {
            $akun_value =  $this->data['akun']->email; 
            $akun_field = 'email';
        }
        $this->model->getAllData('donatur', array($akun_field,'=', $akun_value));
        $this->data['donatur'] = $this->model->getResult();
        $this->data['route_alias'] = 'donatur';
	}

    public function index() {
        
    }
}