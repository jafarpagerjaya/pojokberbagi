<?php
class TransaksiController extends Controller {
    public function __construct() {
		$this->title = 'Donatur';
		


        $this->model("Auth");
        
        if (!$this->model->hasPermission('donatur')) {
            Redirect::to('home');
        }

        $this->data['akun'] = $this->model->data();

        $this->model("Donatur");
        $this->model->getAllData('donatur', array('email','=', $this->data['akun']->email));
        $this->data['donatur'] = $this->model->data();
        $this->data['route_alias'] = 'donatur';
	}

    public function index() {
        
    }
}