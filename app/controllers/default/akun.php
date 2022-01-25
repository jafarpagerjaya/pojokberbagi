<?php 
class AkunController extends Controller {
    public function index() {
        $this->model('Auth');

        if (!$this->model->hasPermission('donatur')) {
            Redirect::to('home');
        }
        
        $this->data['text'] = "AkunController => Route Default [" . $this->model->data()->username . "]";
    }
}