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
			$this->_publikasi->getData("id_artikel","artikel",array("LOWER(REPLACE(TRIM(judul), ' ', '-'))","=",Sanitize::escape2(strtolower($params[0]))),"AND",array("aktif","=","1"));		
			if (!$this->_publikasi->affected()) {
				Session::flash('notifikasi', array(
					'state' => 'warning',
					'pesan' => 'Artikel yang anda cari tidak ditemukan'
				));
				Redirect::to('publikasi/artikel');
			}
			$params[0] = $this->_publikasi->getResult()->id_artikel;
			$this->kontenArtikel($params);
			return VIEW_PATH . 'default' . DS . 'publikasi' . DS . 'konten-artikel.html';
		}

		$this->title = 'Artikel';
        $this->rel_action = array(
			array(
				'href' => '/assets/main/css/inputGroup.css'
			),
			array(
                'href' => '/assets/route/default/core/css/services.css'
            ),
			array(
                'href' => '/vendors/@fortawesome/fontawesome-free/css/all.min.css'
            ),
			array(
				'href' => '/assets/route/default/pages/css/artikel.css'
			)
		);

		$this->script_action = array(
			array(
				'src' => '/assets/main/js/token.js'
			),
			array(
				'src' => '/assets/main/js/function-libs.js'
			),
			array(
				'src' => '/assets/main/js/utility.js'
			),
			array(
				'src' => '/assets/route/default/pages/js/artikel.js'
			)
		);

		// Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();

		$this->data['filter_years'] = array_column($this->_publikasi->getArtikelYearDateList(), 'tahun');
    }

	public function fetch($params) {
		$fetch = new Fetch();
		if (!$fetch->tokenPassed()) {
			$fetch->result();
			return false;
		}

        switch ($params[0]) {
            case 'gets':
                // gets Params
            break;
			
			default:
				$fetch->addResults(array(
					'feedback' => array(
						'message' => 'Unrecognize params '. $params[0]
					)
				));
				$fetch->result();
				return false;
			break;
		}

		if (isset($params[1])) {
            if ($params[1] == 'artikel') {
                // fetchGetsArtikel
                $params[0] .= ucfirst(strtolower($params[1]));
            }
        }

		$decoded = $fetch->getDecoded();

        // prepare method Token name
        $action = __FUNCTION__ . ucfirst($params[0]);
        // call method Token
        $this->$action($decoded, $fetch);
        
        return false;
	}

	private function fetchGetsArtikel($decoded, $fetch) {
		$decoded = Sanitize::thisArray($decoded);
		if (isset($decoded['filter'])) {

		}

		if (isset($decoded['offset'])) {
			$this->_publikasi->setOffset($decoded['offset']);
		}

		$newListId = array();
		$this->_publikasi->setLimit(12);
		if (isset($decoded['list_id'])) {
            $decoded['list_id'] = Sanitize::thisArray($decoded['list_id']);
			$params = array_merge($decoded['list_id'], $decoded['list_id']);
			$ids_question_mark = implode(",", array_map(function($value) { return '?'; }, $decoded['list_id']));
			// Mencari record baru dan record yang sudah tidak akan masuk dalam percarian dari id lama
			$this->_publikasi->query("
				(SELECT id_artikel FROM artikel WHERE aktif = '1' AND id_artikel > ANY (SELECT id_artikel FROM artikel WHERE aktif = '1' AND id_artikel IN ({$ids_question_mark}) ORDER BY 1 DESC) ORDER BY 1 DESC LIMIT {$this->_publikasi->getLimit()})
				UNION
				(SELECT id_artikel FROM artikel WHERE aktif = '1' AND id_artikel IN ({$ids_question_mark}) ORDER BY 1 DESC)
				ORDER BY 1 DESC
			", $params);		
			if (!$this->_publikasi->affected()) {
				$fetch->addResults(array(
					'feedback' => array(
						'message' => 'Something goes wrong on getCurrent ID on [$fetch] publikasi'
					)
				));
				$fetch->result();
				return false;
			}
			$currentIds = array_column($this->_publikasi->data(), 'id_artikel');

			// compare with the last list
			$newListId = array_diff($currentIds, $decoded['list_id']);
			$removeData = Config::array_flatten(array_diff($decoded['list_id'], $currentIds));

			if (count(is_countable($newListId) ? $newListId : [])) {
				$decoded['offset'] += sizeof($newListId);
				$this->_publikasi->setOffset($decoded['offset']);

				$limit = $this->_publikasi->getLimit() - count($newListId);
				$this->_publikasi->setLimit($limit);
				
			}

			if (count(is_countable($removeData) ? $removeData : []) > 0) {
				$decoded['offset'] -= count($removeData);
				$this->_publikasi->setOffset($decoded['offset']);
				if (count(is_countable($newListId) ? $newListId : []) == 0) {
					$limit = $this->_publikasi->getLimit() + count($removeData);
					$this->_publikasi->setLimit($limit);
				}
			}
		}

		$this->_publikasi->getsCardArtikelList(array(), $newListId);
		$this->data['artikel'] = $this->_publikasi->data();

		if (!isset($decoded['list_id'])) {
            $list_id = array();
        }

		if (isset($removeData) && isset($decoded['list_id'])) {
			if (count(is_countable($removeData) ? $removeData : []) > 0) {
				$this->data['artikel']['remove_id'] = $removeData;
				$list_id = array_diff($decoded['list_id'], $removeData);
			} else {
				$list_id = $decoded['list_id'];
			}
		}

        $this->data['artikel']['list_id'] = array_unique(array_merge($list_id, array_column($this->data['artikel']['data'], 'id_artikel')));

		$fetch->addResults(array(
            'error' => false,
            'feedback' => $this->data['artikel']
        ));
		$fetch->result();
	}

	private function kontenArtikel($params) {
		$this->title = 'Konten Artikel';
		$this->rel_action = array(
			array(
				'href' => 'https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css',
			),
			array(
                'href' => '/assets/route/default/core/css/services.css'
            ),
			array(
                'href' => '/vendors/@fortawesome/fontawesome-free/css/all.min.css'
            ),
			array(
				'href' => 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css'
			),
			array(
				'href' => '/assets/route/default/pages/css/konten-artikel.css'
			)
		);

		$this->script_action = array(
			array(
				'src' => '/assets/main/js/token.js'
			),
			array(
				'src' => 'https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js',
				'source' => 'trushworty'
			),
			array(
				'src' => '/assets/route/default/pages/js/konten-artikel.js'
			)
		);

		if (!$this->_publikasi->setPublikasiKunjungan('artikel', $this->getClientDeviceID(), $params[0])) {
			$this->setKunjungan2(false, false, false, false);
			$id_pengunjung = Sanitize::thisArray(json_decode(base64_decode(Cookie::get(Config::get('client/cookie_name'))), true))['id_pengunjung'];
			$this->_publikasi->create("kunjungan_artikel", array("id_artikel" => $params[0], "id_pengunjung" => $id_pengunjung));
		}

		if (!$this->_publikasi->getDataKontenArtikel($params[0])) {
			Session::flash('notifikasi', array('state' => 'warning', 'pesan' => 'Terjadi kesalahan saat hendak mengambil data konten'));
			Redirect::to('publikasi/artikel');
		}

		$this->data['konten'] = $this->_publikasi->data();
		
		$this->_publikasi->query("SELECT judul, path_gambar, IFNULL(nama, judul) nama_gambar FROM artikel LEFT JOIN gambar ON(artikel.id_gambar_small = gambar.id_gambar) WHERE id_artikel < ? AND aktif = '1' ORDER BY id_artikel DESC LIMIT 4", array($params[0]));
		$this->data['artikel_lainnya'] = $this->_publikasi->data();
	}
}