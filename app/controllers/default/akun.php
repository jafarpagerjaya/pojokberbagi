<?php 
class AkunController extends Controller {
    public function index() {
        // sementara di redirect dulu, kedepannya bisa melihat resume akun tersebut berdonasi
        Redirect::to();

        $this->model('Auth');

        if (!$this->model->hasPermission('donatur')) {
            Redirect::to();
        }
        
        $this->data['text'] = "AkunController => Route Default [" . ($this->model->data()->username ?? '') . "]";
    }
}