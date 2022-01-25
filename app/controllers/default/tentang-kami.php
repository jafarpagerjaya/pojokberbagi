<?php
class TentangKamiController extends Controller {
    public function __construct() {
        $this->title = "Tentang Kami";
        $this->rel_controller = array(
            array(
                'href' => '/assets/pojok-berbagi-style.css'
            )
        );
    }
    public function index() {
        $this->rel_action = array(
            array(
                'href' => '/assets/route/default/pages/css/tentang-kami.css'
            )
        );

        $this->script_action = array(
            array(
                'src' => '/assets/route/default/pages/js/tantang-kami.js'
            )
        );
    }
}