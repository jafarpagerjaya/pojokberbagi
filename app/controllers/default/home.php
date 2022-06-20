<?php
class HomeController extends Controller {
	
	public function __construct() {
		$this->title = 'Home';
		$this->rel_controller = array(
            array(
                'href' => '/assets/pojok-berbagi-style.css'
			)
        );
		$this->script_controller = array(
			array(
				'src' => '/assets/pojok-berbagi-script.js'
			)
		);
	}

	public function index() {
		$this->rel_action = array(
			array(
				'href' => '/assets/route/default/pages/css/home.css'
			),
			array(
                'href' => '/assets/route/default/core/css/services.css'
            )
		);

		$this->script_action = array(
			array(
				'src' => '/assets/main/js/token.js'
			),
			array(
				'src' => '/assets/route/default/pages/js/home.js'
			)
		);
		
		$this->model('Bantuan');
		$this->model->getBanner();
		$this->data['banner'] = $this->model->data();
		
		// $this->model->setOrder('b.action_at');
		$this->model->setOffset(0);
		$this->model->setLimit(3);
		$this->model->getListBantuan();
        $this->data['list_bantuan'] = $this->model->data();
		// $this->setKunjungan();
		// Track real path based on return php request for js dom
		$this->data['uri'] = base64_encode($this->getRealUri());
		// Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();
	}

	public function kunjungan() {
		if (!isset($_POST['uri']) && !isset($_POST['path'])) {
			Redirect::to('/');
		}

		$uri = Sanitize::escape(trim($_POST['uri']));
		$path = Sanitize::escape(trim($_POST['path']));

		$this->model('Home');
		$this->setKunjungan(null, $uri, $path);
		return false;
	}
}