<?php
class FallbackController extends Controller {

    public function __construct() {
        $this->rel_controller = array(
            array(
                'href' => '/assets/pojok-berbagi-style.css'
            )
        );

        $this->_auth = $this->model('Auth');
		$this->data['signin'] = $this->_auth->isSignIn();
    }

    public function index() {
        Redirect::to('/');
    }

    public function offline() {
        $this->title = "Offline";

        $this->script_action = array(
            array(
                'source' => 'trushworty',
                'src' => 'https://unpkg.com/@dotlottie/player-component@2.7.11/dist/dotlottie-player.mjs',
                'type' => 'module'
            ),
            array(
                'src' => '/assets/main/js/fallback.js',
                'type' => 'text/javascript'
            )
        );
    }

    public function fetch() {
    echo '<pre>
    |   ####  #     ##### #   # #   #   | 
    |   #   # #     #   # ##  # ## #    | 
    |   ####  #     ##  # ##  # ###     |  
    |   ####  #     ##### # # # ###     |  
    |   #   # ##    #   # #  ## ## #    |  
    |   ####  ##### #   # #  ## #   #   |  
    </pre>';
        return false;
    }
}