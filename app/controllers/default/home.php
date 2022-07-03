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
		
		$this->model->setStatus(Sanitize::escape2('D'));
        $this->model->setOrder('b.action_at');
        $this->model->setDirection('DESC');
        $this->model->setOffset(0);
        $this->model->setLimit(6);
        $this->model->getListBantuan(null);
        $this->data['list_bantuan'] = $this->model->data();
		if (count(is_countable($this->data['list_bantuan']) ? $this->data['list_bantuan'] : [])) {
            $this->data['list_id'] = base64_encode(json_encode(array_column($this->data['list_bantuan']['data'], 'id_bantuan')));
        }
		// $this->setKunjungan();
		// Track real path based on return php request for js dom
		$this->data['uri'] = base64_encode($this->getRealUri());
		// Token for fetch
		if (!Session::exists(Config::get('session/token_name'))) {
			$this->data[Config::get('session/token_name')] = Token::generate();
		} else {
			$this->data[Config::get('session/token_name')] = Session::get(Config::get('session/token_name'));
		}
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