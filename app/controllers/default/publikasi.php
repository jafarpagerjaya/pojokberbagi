<?php
class PublikasiController extends Controller {
    private $_auth;
	
	public function __construct() {
		$this->title = 'Publikasi';
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
		$this->_auth = $this->model('Auth');
		$this->data['signin'] = $this->_auth->isSignIn();
        $this->_publikasi = $this->model('Publikasi');
	}

    public function index($params) {
        if (count(is_countable($params) ? $params : []) > 0) {
            if ($params[0] == 'artikel') {
                Redirect::to('publikasi/'.implode('/',$params));
            }
        }
		// Sementara karena halaman publikasi belum ada
		Redirect::to('publikasi/artikel');
    }

    public function artikel($params = array()) {
        if (count(is_countable($params) ? $params : []) > 0) {
			$this->_publikasi->getData("id_artikel","artikel",array("LOWER(judul)","=",Sanitize::escape2(strtolower($params[0]))));
			if (!$this->_publikasi->affected()) {
				Sassion::flash('notification', array(
					'state' => 'warning',
					'pesan' => 'Artikel yang anda cari tidak ditemukan'
				));
				Redirect::to('publikasi/artikel');
			}
			$this->contentArtikel($params);
			return VIEW_PATH . 'publikasi' . DS . 'content-artikel.html';
		}

		$this->title = 'Artikel';
        $this->rel_action = array(
			array(
				'href' => '/assets/route/default/pages/css/artikel.css'
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
				'src' => '/assets/route/default/pages/js/artikel.js'
			)
		);
    }

	private function contentArtikel($params) {
		$this->title = 'Artikel';
	}
}