<?php
class BantuanController extends Controller {

    private $_bantuan;

    public function __construct() {
        $this->title = "Bantuan";
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
        $this->_bantuan = $this->model('Bantuan');

        $this->_auth = $this->model('Auth');
		$this->data['signin'] = $this->_auth->isSignIn();
    }

    private function kategori() {
        $this->rel_action = array(
            array(
                'href' => '/assets/route/default/pages/css/kategori.css'
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
                'src' => '/assets/route/default/pages/js/kategori.js'
            )
        );
        // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();
    }

    public function index() {
        Redirect::to('home');
    }

    public function detil($params) {
        if (count(is_countable($params) ? $params : [])) {
            // Token for fetch
        $this->data[Config::get('session/token_name')] = Token::generate();

            $this->rel_action = array(
                array(
                    'href' => '/assets/route/default/pages/css/detil.css'
                )
            );

            $this->script_action = array(
                array(
                    'type' => 'text/javascript',
                    'src' => '/assets/main/js/token.js'
                ),
                array(
                    'type' => 'text/javascript',
                    'src' => 'https://cdn.quilljs.com/1.3.6/quill.js',
                    'source' => 'trushworty'
                ),
                array(
                    'src' => '/assets/route/default/pages/js/detil.js'
                )
            );

            $this->_bantuan->getData('COUNT(id_bantuan) found','bantuan',array('id_bantuan','=',Sanitize::escape2($params[0])),'AND',array('status','IN',array('D','S')));
            if ($this->_bantuan->getResult()->found == 0) {
                Session::flash('notifikasi', array(
                    'pesan' => 'Halaman detil bantuan yang anda cari tidak ditemukan',
                    'state' => 'warning'
                ));
                Redirect::to('home');
            }
            $this->setKunjungan2($params);
            $this->_bantuan->getDetilBantuan($params[0]);
            if ($this->_bantuan->affected()) {
                $this->data['detil_bantuan'] = $this->_bantuan->data();
            }

            $this->_bantuan->getData('judul, FormatTanggal(create_at) create_at', 'deskripsi', array('id_bantuan','=',$params[0]));
            if ($this->_bantuan->affected()) {
                $this->data['deskripsi'] = $this->_bantuan->getResult();
            }
        } else {
            Redirect::to('home');
        }
    }

    public function berdaya($params = array()) {
        $this->title = 'Berdaya';
        $this->kategori();

        $program = Sanitize::escape2('Pojok Berdaya');
        $this->_bantuan->setStatus(Sanitize::escape2('D'));
        $this->_bantuan->setOrder('b.action_at');
        $this->_bantuan->setDirection('DESC');
        $this->_bantuan->setOffset(0);
        $this->_bantuan->setLimit(6);
        $this->_bantuan->getListBantuan($program);
        $this->data['list_bantuan'] = $this->_bantuan->data();
        if (count(is_countable($this->data['list_bantuan']) ? $this->data['list_bantuan'] : [])) {
            $this->data['list_id'] = base64_encode(json_encode(array_column($this->data['list_bantuan']['data'], 'id_bantuan')));
        }

        $this->_bantuan->getResumeKategoriBantuan($program);
        if (is_null($this->_bantuan->data())) {
            Debug::vd('Unrecognize Kategori Name');
        }
        $this->data['resume_kb'] = $this->_bantuan->data();
        $this->data['resume_kb']->deskripsi = "Program pemberdayaan sebagai upaya dalam meningkatkan kemampuan dan ketahanan masyarakat guna menjadikan  masyarakat yang Tangguh dan mandiri serta menjaga <i>sustainability</i> program.";

        return VIEW_PATH.'default'.DS.'bantuan'.DS.'kategori.html';
    }

    public function peduli_berbagi($params = array()) {
        $this->title = 'Peduli Berbagi';
        $this->kategori();

        $program = Sanitize::escape2('Pojok Peduli Berbagi');
        $this->_bantuan->setStatus(Sanitize::escape2('D'));
        $this->_bantuan->setOrder('b.action_at');
        $this->_bantuan->setDirection('DESC');
        $this->_bantuan->setOffset(0);
        $this->_bantuan->setLimit(6);
        $this->_bantuan->getListBantuan($program);
        $this->data['list_bantuan'] = $this->_bantuan->data();
        if (count(is_countable($this->data['list_bantuan']) ? $this->data['list_bantuan'] : [])) {
            $this->data['list_id'] = base64_encode(json_encode(array_column($this->data['list_bantuan']['data'], 'id_bantuan')));
        }

        $this->_bantuan->getResumeKategoriBantuan($program);
        if (is_null($this->_bantuan->data())) {
            Debug::vd('Unrecognize Kategori Name');
        }
        $this->data['resume_kb'] = $this->_bantuan->data();
        $this->data['resume_kb']->deskripsi = "Program kepedulian berupa bantuan dibidang ekonomi, kesehatan, dan pendidikan yang bersifat darurat dan <i>charity</i> (pemberian). Biaya Kesehatan kuratif dan preventif, bantuan pangan dan biaya pendidikan.";

        return VIEW_PATH.'default'.DS.'bantuan'.DS.'kategori.html';
    }

    public function wakaf($params = array()) {
        $this->title = 'Wakaf';
        $this->kategori();

        $program = Sanitize::escape2('Pojok Wakaf');
        $this->_bantuan->setStatus(Sanitize::escape2('D'));
        $this->_bantuan->setOrder('b.action_at');
        $this->_bantuan->setDirection('DESC');
        $this->_bantuan->setOffset(0);
        $this->_bantuan->setLimit(6);
        $this->_bantuan->getListBantuan($program);
        $this->data['list_bantuan'] = $this->_bantuan->data();
        if (count(is_countable($this->data['list_bantuan']) ? $this->data['list_bantuan'] : [])) {
            $this->data['list_id'] = base64_encode(json_encode(array_column($this->data['list_bantuan']['data'], 'id_bantuan')));
        }

        $this->_bantuan->getResumeKategoriBantuan($program);
        if (is_null($this->_bantuan->data())) {
            Debug::vd('Unrecognize Kategori Name');
        }
        $this->data['resume_kb'] = $this->_bantuan->data();
        $this->data['resume_kb']->deskripsi = "Program pembangunan/perbaikan fisik yang kemanfaatannya dalam jangka panjang mulai dari bedah Madrasah dan Sekolah terlantar, Masjid dan Mushola yang makmur desa, pembangunan sanitasi, Asrama Dan Sekolah Yatim Preneur hingga Wakaf Produktif.";

        return VIEW_PATH.'default'.DS.'bantuan'.DS.'kategori.html';
    }

    public function peduli_yatim($params = array()) {
        $this->title = 'Yatim';
        $this->kategori();

        $program = Sanitize::escape2('Pojok Peduli Yatim');
        $this->_bantuan->setStatus(Sanitize::escape2('D'));
        $this->_bantuan->setOrder('b.action_at');
        $this->_bantuan->setDirection('DESC');
        $this->_bantuan->setOffset(0);
        $this->_bantuan->setLimit(6);
        $this->_bantuan->getListBantuan($program);
        $this->data['list_bantuan'] = $this->_bantuan->data();
        if (count(is_countable($this->data['list_bantuan']) ? $this->data['list_bantuan'] : [])) {
            $this->data['list_id'] = base64_encode(json_encode(array_column($this->data['list_bantuan']['data'], 'id_bantuan')));
        }

        $this->_bantuan->getResumeKategoriBantuan($program);
        if (is_null($this->_bantuan->data())) {
            Debug::vd('Unrecognize Kategori Name');
        }
        $this->data['resume_kb'] = $this->_bantuan->data();
        $this->data['resume_kb']->deskripsi = "Setiap anak memiliki hak yang sama untuk mendapatkan perlindungan, pemenuhan kebutuhan dan pendidikan yang layak. Namun tidak bagi mereka yang telah kehilangan orang tuanya, oleh sebab itu dalam <b>Pojok Peduli Yatim</b> bagi anak Yatim piatu dan duafa kami dorong mereka dengan pemberian biaya <b>kelangsungan hidup</b>, bantuan <b>biaya pendidikan</b>, dan <b>pembentukan karakter</b> yang tidak dapat mereka dapatkan dari orang tuanya.";

        return VIEW_PATH.'default'.DS.'bantuan'.DS.'kategori.html';
    }

    public function rescue($params = array()) {
        $this->title = 'Rescue';
        $this->kategori();

        $program = Sanitize::escape2('Pojok Rescue');
        $this->_bantuan->setStatus(Sanitize::escape2('D'));
        $this->_bantuan->setOrder('b.action_at');
        $this->_bantuan->setDirection('DESC');
        $this->_bantuan->setOffset(0);
        $this->_bantuan->setLimit(6);
        $this->_bantuan->getListBantuan($program);
        $this->data['list_bantuan'] = $this->_bantuan->data();
        if (count(is_countable($this->data['list_bantuan']) ? $this->data['list_bantuan'] : [])) {
            $this->data['list_id'] = base64_encode(json_encode(array_column($this->data['list_bantuan']['data'], 'id_bantuan')));
        }

        $this->_bantuan->getResumeKategoriBantuan($program);
        if (is_null($this->_bantuan->data())) {
            Debug::vd('Unrecognize Kategori Name');
        }
        $this->data['resume_kb'] = $this->_bantuan->data();
        $this->data['resume_kb']->deskripsi = "Bantuan <i>emergency</i> respon terhadap kejadian bencana alam dan sosial untuk mengurangi dan meringankan dampak dari terjadinya bencana yang meliputi Edukasi dan Penguatan Kapasitas masyarakat SAR dan Evakuasi (Pra Bencana) dan (Saat Bencana) dapur gizi, hunian sementara, sanitasi serta (Pasca Bencana) Rehabilitasi dan Recovery.";

        return VIEW_PATH.'default'.DS.'bantuan'.DS.'kategori.html';
    }
}