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
                    'href' => 'https://cdn.quilljs.com/1.3.7/quill.snow.css',
                    'source' => 'trushworty'
                ),
                array(
                    'href' => 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
                    'source' => 'trushworty'
                ),
                array(
                    'href' => '/assets/main/css/utility.css'
                ),
                array(
                    'href' => '/assets/main/css/timeline.css'
                ),
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
                    'src' => 'https://cdn.quilljs.com/1.3.7/quill.js',
                    'source' => 'trushworty'
                ),
                array(
                    'src' => '/assets/main/js/main.js'
                ),
                array(
                    'src' => '/assets/main/js/utility.js'
                ),
                array(
                    'src' => '/assets/route/default/pages/js/detil.js'
                )
            );

            $params = Sanitize::thisArray($params);
            $this->_bantuan->getData('COUNT(id_bantuan) found','bantuan',array('id_bantuan','=',$params[0]),'AND',array('status','IN',array('D','S')));
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

            // Meta
            $this->meta = array(
				array(
					'name' => 'description',
					'content' => 'Tempat bagi gerakan kebaikan (social movement) dan kemanusiaan.'
				),
				array(
					'property' => 'og:title',
					'content' => 'Pojok Berbagi Indonesia'
				),
				array(
					'property' => 'og:url',
					'content' => $this->getMetaUri()
				),
				array(
					'property' => 'og:description',
					'content' => $this->data['detil_bantuan']->deskripsi
				),
				array(
					'property' => 'og:image',
					'content' => Config::getHTTPHost() . $this->data['detil_bantuan']->path_gambar_medium
				),
				array(
					'property' => 'og:image:width',
					'content' => '300'
				),
				array(
					'property' => 'og:image:height',
					'content' => '169'
				),
				array(
					'property' => 'og:image:alt',
					'content' => $this->data['detil_bantuan']->nama_gambar_medium
				),
				array(
					'property' => 'og:type',
					'content' => 'website'
				)
            );

            $this->_bantuan->getData('LENGTH(isi) len, judul, FormatTanggal(create_at) create_at', 'deskripsi', array('id_bantuan','=',$params[0]));
            if ($this->_bantuan->affected()) {
                $this->data['deskripsi'] = $this->_bantuan->getResult();
            }
            
            $values = array(
                $params[0]
            );

            if ($this->_auth->isSignIn()) {
                $sql = "WITH cte AS (
                    SELECT id_donasi FROM donasi WHERE id_bantuan = ? AND bayar = 1 ORDER BY waktu_bayar DESC, id_donasi DESC LIMIT 3
                ) SELECT dn.id_donasi, dn.id_donatur, IFNULL(dn.alias, dt.nama) nama_donatur, FORMAT(dn.jumlah_donasi,0,'id_ID') jumlah_donasi, dn.doa, COUNT(a.id_donasi) liked, CONCAT('avatar ',dt.nama) nama_avatar, IFNULL(gd.path_gambar,IF(dt.jenis_kelamin IS NULL,'/assets/images/default.png',IF(dt.jenis_kelamin = 'P','/assets/images/female-avatar.jpg','/assets/images/male-avatar.jpg'))) path_avatar, IF(aa.id_donasi IS NOT NULL,1,0) checked
                FROM cte JOIN donasi dn USING(id_donasi) JOIN donatur dt USING(id_donatur) LEFT JOIN akun ak USING(id_akun) LEFT JOIN gambar gd USING(id_gambar)
                LEFT JOIN amin a ON(a.id_donasi = cte.id_donasi) LEFT JOIN (
                    SELECT id_donasi FROM amin WHERE id_akun = ?
                ) aa ON(cte.id_donasi = aa.id_donasi)
                GROUP BY cte.id_donasi
                ORDER BY dn.waktu_bayar DESC, dn.id_donasi DESC";
                array_push($values, $this->_auth->data()->id_akun);
            } else {
                if (Cookie::exists(Config::get('client/cookie_name'))) {
                    $cookie_value = Sanitize::thisArray(json_decode(base64_decode(Cookie::get(Config::get('client/cookie_name')) ?? ''), true));
                }

                if (isset($cookie_value['id_pengunjung'])) {
                    $sql = "WITH cte AS (
                        SELECT id_donasi FROM donasi WHERE id_bantuan = ? AND bayar = 1 ORDER BY waktu_bayar DESC, id_donasi DESC LIMIT 3
                    ) SELECT dn.id_donasi, dn.id_donatur, IFNULL(dn.alias, dt.nama) nama_donatur, FORMAT(dn.jumlah_donasi,0,'id_ID') jumlah_donasi, dn.doa, COUNT(a.id_donasi) liked, CONCAT('avatar ',dt.nama) nama_avatar, IFNULL(gd.path_gambar,IF(dt.jenis_kelamin IS NULL,'/assets/images/default.png',IF(dt.jenis_kelamin = 'P','/assets/images/female-avatar.jpg','/assets/images/male-avatar.jpg'))) path_avatar, IF(aa.id_donasi IS NOT NULL,1,0) checked
                    FROM cte JOIN donasi dn USING(id_donasi) JOIN donatur dt USING(id_donatur) LEFT JOIN akun ak USING(id_akun) LEFT JOIN gambar gd USING(id_gambar)
                    LEFT JOIN amin a ON(a.id_donasi = cte.id_donasi) LEFT JOIN (
                        SELECT id_donasi FROM amin WHERE id_akun IS NULL AND id_pengunjung = ?
                    ) aa ON(cte.id_donasi = aa.id_donasi)
                    GROUP BY cte.id_donasi
                    ORDER BY dn.waktu_bayar DESC, dn.id_donasi DESC";
                    array_push($values, $cookie_value['id_pengunjung']);
                } else {
                    $sql = "WITH cte AS (
                        SELECT id_donasi FROM donasi WHERE id_bantuan = ? AND bayar = 1 ORDER BY waktu_bayar DESC, id_donasi DESC LIMIT 3
                    ) SELECT dn.id_donasi, dn.id_donatur, IFNULL(dn.alias, dt.nama) nama_donatur, FORMAT(dn.jumlah_donasi,0,'id_ID') jumlah_donasi, dn.doa, COUNT(a.id_donasi) liked, CONCAT('avatar ',dt.nama) nama_avatar, IFNULL(gd.path_gambar,IF(dt.jenis_kelamin IS NULL,'/assets/images/default.png',IF(dt.jenis_kelamin = 'P','/assets/images/female-avatar.jpg','/assets/images/male-avatar.jpg'))) path_avatar, 0 checked
                    FROM cte JOIN donasi dn USING(id_donasi) JOIN donatur dt USING(id_donatur) LEFT JOIN akun ak USING(id_akun) LEFT JOIN gambar gd USING(id_gambar)
                    LEFT JOIN amin a ON(a.id_donasi = cte.id_donasi)
                    GROUP BY cte.id_donasi
                    ORDER BY dn.waktu_bayar DESC, dn.id_donasi DESC";
                }
            }
            $this->_bantuan->query($sql, $values);
            if ($this->_bantuan->affected()) {
                $this->data['list_donatur'] = $this->_bantuan->data();
            }

            $this->_bantuan->query("SELECT id_informasi, judul, isi, label, FormatTanggal(publish_at) waktu_publikasi, DATE_FORMAT(publish_at, '%Y-%m-%d') tanggal_publikasi FROM informasi WHERE id_bantuan = ? AND publish_at IS NOT NULL AND id_editor IS NOT NULL ORDER BY publish_at DESC LIMIT 3", array('id_bantuan' => $params[0]));
            if ($this->_bantuan->affected()) {
                $dataInformasi = $this->_bantuan->data();
            }

            if (isset($dataInformasi)) {
                $result = $this->_bantuan->countData('informasi', array('id_bantuan = ? AND publish_at IS NOT NULL AND id_editor IS NOT NULL', array($params[0])));
                if ($this->_bantuan->affected()) {
                    $this->data['top_update_terbaru'] = array(
                        'data' => $dataInformasi,
                        'record' => $result->jumlah_record
                    );
                }
            }

            if (isset($params[1]) && isset($params[2])) {
                switch ($params[1]) {
                    case 'informasi':
                        $params[2] = base64_decode(strrev($params[2]));
                        $this->_bantuan->getData('id_informasi, judul, isi, i.label, FormatTanggal(i.modified_at) modified_at, pa.nama nama_author, ga.path_gambar path_author','informasi i LEFT JOIN pegawai pa ON(pa.id_pegawai = i.id_author) LEFT JOIN admin adm ON(adm.id_pegawai = pa.id_pegawai) LEFT JOIN akun a ON(a.id_akun = adm.id_akun) LEFT JOIN gambar ga ON(ga.id_gambar = a.id_gambar)', array('i.id_informasi','=',Sanitize::escape2($params[2])));
                        if ($this->_bantuan->affected()) {
                            $this->data['modal_update_berita'] = $this->_bantuan->getResult();
                        }
                    break;
                    
                    default:
                        # do nothing
                    break;
                }
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