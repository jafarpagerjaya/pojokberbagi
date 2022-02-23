<?php
class BantuanController extends Controller {

    private $_bantuan;

    public function __construct() {
        $this->title = "Bantuan";
        $this->rel_controller = array(
            array(
                'href' => '/assets/pojok-berbagi-style.css?v=1'
            )
        );
        $this->script_controller = array(
            array(
                'src' => '/assets/pojok-berbagi-script.js?v=1'
            )
        );
        $this->_bantuan = $this->model('Bantuan');
    }

    private function kategori() {
        $this->rel_action = array(
            array(
                'href' => '/assets/route/default/pages/css/kategori.css'
            ),
            array(
                'href' => '/assets/route/default/core/css/services.css?v=1'
            )
        );
        $this->script_action = array(
            array(
                'src' => '/assets/route/default/pages/js/kategori.js'
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
                    'href' => '/assets/route/default/pages/css/detil.css?v=1'
                )
            );

            $this->script_action = array(
                array(
                    'src' => '/assets/route/default/pages/js/detil.js?v=1'
                )
            );

            $this->model('Bantuan');
            $this->setKunjungan($params);
            $this->model->getDetilBantuan($params[0]);
            if ($this->model->affected()) {
                $this->data['detil_bantuan'] = $this->model->data();
            }
        }
    }

    public function berdaya($params = array()) {
        $this->title = 'Berdaya';
        $this->kategori();

        $program = Sanitize::escape2('Pojok Berdaya');
        $this->_bantuan->setStatus(Sanitize::escape2('D'));
        $this->_bantuan->getListBantuanKategori($program);
        $this->data['list_bantuan'] = $this->_bantuan->data();

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
        $this->_bantuan->getListBantuanKategori($program);
        $this->data['list_bantuan'] = $this->_bantuan->data();

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
        $this->_bantuan->getListBantuanKategori($program);
        $this->data['list_bantuan'] = $this->_bantuan->data();

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
        $this->_bantuan->getListBantuanKategori($program);
        $this->data['list_bantuan'] = $this->_bantuan->data();

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
        $this->_bantuan->getListBantuanKategori($program);
        $this->data['list_bantuan'] = $this->_bantuan->data();

        $this->_bantuan->getResumeKategoriBantuan($program);
        if (is_null($this->_bantuan->data())) {
            Debug::vd('Unrecognize Kategori Name');
        }
        $this->data['resume_kb'] = $this->_bantuan->data();
        $this->data['resume_kb']->deskripsi = "Bantuan <i>emergency</i> respon terhadap kejadian bencana alam dan sosial untuk mengurangi dan meringankan dampak dari terjadinya bencana yang meliputi Edukasi dan Penguatan Kapasitas masyarakat SAR dan Evakuasi (Pra Bencana) dan (Saat Bencana) dapur gizi, hunian sementara, sanitasi serta (Pasca Bencana) Rehabilitasi dan Recovery.";

        return VIEW_PATH.'default'.DS.'bantuan'.DS.'kategori.html';
    }
}