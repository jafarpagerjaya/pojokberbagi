<?php
class TentangKamiController extends Controller {
    public function __construct() {
        $this->title = "Tentang Kami";
        $this->rel_controller = array(
            array(
                'href' => '/assets/pojok-berbagi-style.css'
            )
        );

        $this->_auth = $this->model('Auth');
		$this->data['signin'] = $this->_auth->isSignIn();
    }
    public function index() {
        $this->rel_action = array(
            array(
                'href' => '/assets/route/default/pages/css/tentang-kami.css'
            )
        );

        $this->script_action = array(
            array(
                'source' => 'trushworty',
                'src' => 'https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs',
                'type' => 'module'
            ),
            array(
                'src' => '/assets/route/default/pages/js/tantang-kami.js'
            )
        );
    }
}