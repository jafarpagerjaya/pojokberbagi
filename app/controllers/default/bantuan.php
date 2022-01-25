<?php
class BantuanController extends Controller {

    public function __construct() {
        $this->title = "Bantuan";
        $this->rel_controller = array(
            array(
                'href' => '/assets/pojok-berbagi-style.css'
            ),
            array(
                'href' => '/assets/route/default/pages/css/bantuan.css'
            )
        );
        $this->script_controller = array(
            array(
                'src' => '/assets/pojok-berbagi-script.js'
            )
        );
    }

    public function index() {
        Redirect::to('home');
    }

    public function detil($params) {
        if (count($params)) {
            $this->rel_action = array(
                array(
                    'href' => '/assets/route/default/pages/css/detil.css'
                )
            );

            $this->script_action = array(
                array(
                    'src' => '/assets/route/default/pages/js/detil.js'
                )
            );

            // $this->setKunjungan();

            $this->model('Bantuan');
            $this->setKunjungan($params);
            $this->model->getDetilBantuan($params[0]);
            if ($this->model->affected()) {
                $this->data['detil_bantuan'] = $this->model->data();
            }
        }
    }
}