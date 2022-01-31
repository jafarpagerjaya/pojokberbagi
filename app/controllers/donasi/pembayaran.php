<?php
class PembayaranController extends Controller {
    public function __construct() {
        $this->rel_controller = array(
            array(
                'href' => '/assets/pojok-berbagi-style.css'
            ),
            array(
                'href' => '/assets/route/donasi/core/css/donasi.css'
            )
        );
        $this->script_controller = array(
            array(
                'src' => '/assets/route/donasi/core/js/donasi.js'
            )
        );
    }

    public function index($params) {
        if (count($params) < 1) {
            Redirect::to('home');
        }
        
        if (!Input::exists()) {
            Redirect::to('donasi/buat/baru/' . $params[0]);
        }

        $this->model('Donasi');
        $data_bantuan = $this->model->isBantuanActive(Sanitize::escape($params[0]));
        if ($data_bantuan->status != 'D') {
            Session::flash('notifikasi', array(
                'pesan' => 'Bantuan <b>'. $data_bantuan->nama .'</b> ' . Utility::keteranganStatusBantuan($data_bantuan->status),
                'state' => 'warning'
            ));
            Redirect::to('home');
        }
        if ($data_bantuan == false) {
            Session::flash('notifikasi', array(
                'pesan' => 'Bantuan sudah tidak aktif',
                'state' => 'error'
            ));
            Redirect::to('home');
        }
        // Check data input awal
        $vali = new Validate();
        $validate = $vali->check($_POST, array(
            'jumlah_donasi' => array(
                'required' => true,
                'min_value' => $data_bantuan->min_donasi
            ),
            'metode_pembayaran' => array(
                'required' => true
            ),
            'nama' => array(
                'required' => true,
                'max' => 30
            ),
            'email' => array(
                'required' => true,
                'max' => 96
            ),
            'kontak' => array(
                'digit' => true,
                'min' => 11,
                'max' => 13,
                'unique' => 'donatur'
            )
        ), array('LOWER(email)', '!=', strtolower(Input::get('email'))));
        if (!$validate->passed()) {
            Session::put('error_feedback', $validate->getValueFeedback());
            Redirect::to('donasi/buat/baru/' . $params[0]);
        }

        $data = $this->model->getData('id_donatur, samaran, kontak','donatur', array('LOWER(email)','=', strtolower(trim(Input::get('email')))));
        if (!$data) {
            // Block Yang kemungkinan bisa di hapus karena kontak pasti beda
            // Check data email dan kontak sebelum create donatur
            $uVali = new Validate();
            $uniqueValidate = $uVali->check($_POST, array(
                'email' => array(
                    'required' => true,
                    'max' => 96,
                    'unique' => 'donatur'
                ),
                'kontak' => array(
                    'digit' => true,
                    'min' => 11,
                    'max' => 13,
                    'unique' => 'donatur'
                )
            ));
            if (!$uniqueValidate->passed()) {
                Session::put('error_feedback', $uniqueValidate->getValueFeedback());
                Redirect::to('donasi/buat/baru/' . $params[0]);
            }
            // Akhir Blok Yang kemungkinan bisa di hapus karena kontak pasti beda
            // Create donatur baru
            $create = $this->model->create('donatur', array(
                'nama' => strtoupper(Sanitize::noDblSpace2(Input::get('nama'))),
                'email' => strtolower(Sanitize::noSpace2(Input::get('email'))),
                'kontak' => Sanitize::toInt2(Input::get('kontak'))
            ));
            if (!$create) {
                Session::put('notifikasi', array(
                    'pesan' => 'Gagal Auto Create Donatur Di Pembayaran',
                    'state' => 'error'
                ));
                Redirect::to('donasi/buat/baru/' . $params[0]);
            }
            $id_donatur = $this->model->lastIID();
            $samaran = null;
        } else {
            $id_donatur = $data->id_donatur;
            $samaran = $data->samaran;
            $kontak = $data->kontak;
        }

        $dataDonasi = array(
            'jumlah_donasi' => Sanitize::toInt(trim(Input::get('jumlah_donasi'))),
            'doa' => trim(Input::get('pesan_atau_doa')),
            'id_bantuan' => trim(Sanitize::escape($params[0])),
            'id_donatur' => $id_donatur,
            'id_cp' => Sanitize::escape(trim(Input::get('metode_pembayaran')))
        );

        $dataCP = $this->model->getData('LOWER(jenis) jenis_payment','channel_payment', array('id_cp','=',$dataDonasi['id_cp']));
        if ($dataCP == false) {
            Session::put('notifikasi', array(
                'pesan' => 'Metode pembayaran tidak ditemukan mohon pilih metode lainnya',
                'state' => 'error'
            ));
            Redirect::to('donasi/buat/baru/' . $params[0]);
        }

        $jenis_payment = $dataCP->jenis_payment;

        // Jika input alias tidak dicentang
        if (Input::get('alias') != true) {
            $samaran = ucwords(strtolower(trim(Input::get('nama'))));
        }

        $dataDonasi['alias'] = $samaran;

        // Jika donatur mengisi kontak
        if (strlen(Input::get('kontak')) > 0) {
            $kontak = Sanitize::toInt(trim(Input::get('kontak')));
        }
        
        if (!is_null($kontak)) {
            $dataDonasi['kontak'] = $kontak;
        }

        // Cek jika open donasi sudah berakhir
        if (!is_null($data_bantuan->tanggal_akhir) && strtotime($data_bantuan->tanggal_akhir) <= time()) {
            Session::flash('notifikasi', array(
                'pesan' => 'Mohon maaf bantuan sudah berakhir',
                'state' => 'warning'
            ));
            Redirect::to('home');
        }

        $donasi = $this->model->create('donasi', $dataDonasi);
        
        if (!$donasi) {
            Session::put('notifikasi', array(
                'pesan' => 'Gagal Create Donasi',
                'state' => 'error'
            ));
            Redirect::to('donasi/buat/baru/' . $params[0]);
        }
        Session::put('notifikasi', array(
            'pesan' => 'Berhasil Create Donasi',
            'state' => 'success'
        ));
        $id_donasi = $this->model->lastIID();
        Redirect::to('donasi/pembayaran/tagihan/' . $jenis_payment . '/' . $id_donasi);
    }

    public function tagihan($params) {
        if (count($params) < 2) {
            Session::flash('notifikasi', array(
                'pesan' => 'Parameter tidak cocok',
                'state' => 'error'
            ));
            Redirect::to('home');
        }

        if (!file_exists(VIEW_PATH.'donasi'.DS.'pembayaran'. DS . $params[0] . '.html')) {
            Session::flash('notifikasi', array(
                'pesan' => 'Halaman tagihan yang anda cari tidak ditemukan',
                'state' => 'danger'
            ));
            Redirect::to('home');
        }
    
        $this->model('Donasi');
        $donasi = $this->model->getDataTagihanDonasi($params[1]);
        
        if (!$donasi) {
            Session::flash('notifikasi', array(
                'pesan' => 'Data donasi <b>' . $params[1] . '</b> tidak ditemukan',
                'state' => 'danger'
            ));
            Redirect::to('home');
        }

        if ($donasi->bayar) {
            Session::flash('notifikasi', array(
                'pesan' => 'Donasi sudah dibayar',
                'state' => 'success'
            ));
            Redirect::to('donasi/pembayaran/transaksi/' . $params[1]);
        }

        // Jika sudah lebih dari 24 jam
        // $expiry = strtotime($donasi->create_at) + 86400;
        // if ($expiry < time()) {
        //     Session::flash('warning','Tagihan tidak valid sudah lebih dari 24 jam');
        //     Redirect::to('donasi/pembayaran/dibatalkan/' . $params[1]);
        // }

        $bantuan = $this->model->getData('id_bantuan, nama, nama_penerima, tanggal_akhir', 'bantuan', array('id_bantuan', '=', $donasi->id_bantuan));
        $this->data['bantuan'] = $bantuan;
        if (!is_null($bantuan->tanggal_akhir)) {
            $maxPembayaran = date('Y-m-d', strtotime($bantuan->tanggal_akhir));
            $donasi->max_pembayaran = $maxPembayaran;
            // Jika sudah lebih dari Batas Max Pengumpulan Donasi
            $expiry = strtotime($bantuan->tanggal_akhir);
            if ($maxPembayaran <= date('Y-m-d', time())) {
                Session::flash('notifikasi', array(
                    'pesan' => 'Mohon maaf Donasi bantuan '. $bantuan->nama .' sudah berakhir.',
                    'state' => 'warning'
                ));
                Redirect::to('donasi/pembayaran/dibatalkan/' . $params[1]);
            }
        }
        $this->data['tagihan_donasi'] = $donasi;
        
        $this->title = 'Tagihan';
        $this->rel_action = array(
            array(
                'href' => '/assets/route/donasi/pages/css/tagihan.css'
            )
        );
        $this->script_action = array(
            array(
                'src' => 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js',
                'source' => 'trushworty',
                'integrity' => 'sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p',
                'crossorigin' => 'anonymous'
            ),
            array(
                'src' => '/assets/route/default/core/js/bootstrap.min.js'
            ),
            array(
                'src' => '/assets/route/donasi/pages/js/tagihan.js'
            )
        );

        if (!is_null($donasi->notifikasi) && $donasi->notifikasi == 1) {
            return VIEW_PATH.'donasi'.DS.'pembayaran'. DS . $params[0] . '.html';
        }
        
        if ($donasi->jenis == 'TB') {
            $metode_bayar = "Transfer Bank";
        } else if ($donasi->jenis == 'RQ') {
            $metode_bayar = "QRIS";
        } else if ($donasi->jenis == 'EW') {
            $metode_bayar = "E-Wallet";
        } else if ($donasi->jenis == 'VA') {
            $metode_bayar = "Virtual Akun";
        } else if ($donasi->jenis == 'GM') {
            $metode_bayar = "Gerai Mart";
        } else if ($donasi->jenis == 'GI') {
            $metode_bayar = "Giro";
        } else {
            $metode_bayar = "Unknown";
        }
        
        $dataNotif = array(
            'nama_donatur' => $donasi->nama_donatur,
            'jumlah_donasi' => Output::tSparator($donasi->jumlah_donasi),
            'penerima_bantuan' => $bantuan->nama_penerima,
            'metode_bayar' => $metode_bayar,
            'nama_cp' => $donasi->nama_cp,
            'path_gambar_cp' => $donasi->path_gambar_cp,
            'nomor_tujuan_bayar' => $donasi->nomor,
            'atas_nama_tujuan_bayar' => $donasi->atas_nama,
            'samaran' => 'Sahabat Berbagi',
            'nama_bantuan' => $bantuan->nama
        );
        
        $dataFollow = array(
            'nama_karyawan' => 'Dinda',
            'nama_donatur' => $donasi->nama_donatur,
            'kontak_donatur' => (!is_null($donasi->kontak) ? $donasi->kontak : 'Tidak Ada Kontak'),
            'email_donatur' => $donasi->email,
            'nama_bantuan' => $bantuan->nama,
            'penerima_donasi' => $bantuan->nama_penerima,
            'doa_dan_pesan' => $donasi->doa,
            'id_donasi' => $donasi->id_donasi,
            'jumlah_donasi' => Output::tSparator($donasi->jumlah_donasi),
            'nama_cp' => $donasi->nama_cp
        );

        // Kirim email
        $subject = "[Info Donasi] Pojok Berbagi";
        $headers = 'From: Pojok Berbagi <no-replay@pojokberbagi.id>' . "\r\n" . 'Reply-To: CR Pojok Berbagi <cr@pojokberbagi.id>' . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $pesan = wordwrap(Ui::emailNotifDonasiDonatur($dataNotif), 70, "\r\n");

        $this->mailSended = false;
        
        if (mail($donasi->email, $subject, $pesan, $headers)) {
            $this->mailSended = true;
            $this->model->update('donasi', array(
                'notifikasi' => '1'
            ), array('id_donasi','=',Sanitize::escape2($donasi->id_donasi)));
        } else {
            Session::flash('notifikasi', array(
                'pesan' => 'Email ' . $donasi->email . ' tidak valid, mohon maaf anda tidak akan mendapatkan notifikasi info donasi',
                'state' => 'danger'
            ));
        }

        if ($this->mailSended == true) {
            $subject = "[Follow Up Donasi] Pojok Berbagi";
            $headers = 'From: Pojok Berbagi <no-replay@pojokberbagi.id>' . "\r\n" . 'Reply-To: No-Replay <no-replay@pojokberbagi.id>' . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $pesan = wordwrap(Ui::emailFollowUpDonasi($dataFollow), 70, "\r\n");
            mail('cr@pojokberbagi.id', $subject, $pesan, $headers);
        }

        return VIEW_PATH.'donasi'.DS.'pembayaran'. DS . $params[0] . '.html';
    }

    public function transaksi($params) {
        if (count($params) < 1) {
            Redirect::to('home');
        }

        $this->model('Donasi');
        $donasi = $this->model->getDataTransaksiDonasi($params[0]);
        
        if (!$donasi->bayar) {
            Session::flash('notifikasi', array(
                'pesan' => 'Transaksi dengan ID ' . $params[0] . ' tidak ditemukan',
                'state' => 'warning'
            ));
            Redirect::to('home');
        }

        $this->title = 'Transaksi';
        // Jika sudah lebih dari 1 minggu link ini akan ditutup
        $expiry_time = strtotime($donasi->create_at) + 86400*7;
        if ($expiry_time < time()) {
            Session::put('notifikasi', array(
                'pesan' => 'Donasi anda telah kami terima silahkan cek history transaksi pada akun anda',
                'state' => 'success'
            ));
            Redirect::to('home');
        }

        $now = new DateTime(date('Y-m-d H:i:s'));
        $expiry = new DateTime(date('Y-m-d H:i:s', $expiry_time));
        $msInterval = $expiry->diff($now);
        $inInterval = $expiry_time - strtotime(date('Y-m-d H:i:s', time()));

        $format = array();
        if ($msInterval->y > 0) {
            array_push($format,"%y tahun");
        } else
        if ($msInterval->m > 0) {
            array_push($format,"%m bulan");
        } else
        if ($msInterval->d > 0) {
            array_push($format,"%d hari");
        } else
        if ($msInterval->h > 0) {
            array_push($format,"%h jam");
        } else
        if ($msInterval->i > 0) {
            array_push($format,"(%I : %S)");
        }
        $format = implode(' ', $format);
        
        $donasi->interval = $inInterval;
        $donasi->kurun_waktu = $msInterval->format($format);

        $this->data['transaksi_donasi'] = $donasi;

        $this->script_action = array(
            array(
                'src' => 'https://cdn.jsdelivr.net/gh/robbmj/simple-js-countdown-timer@master/countdowntimer.js',
                'source' => 'trushworty'
            ),
            array(
                'src' => '/assets/route/donasi/pages/js/transaksi.js'
            )
        );
    }

    public function notif($params) {
        $arrayNotif = array(
            'nama_donatur' => "Arief Riandi",
            'jumlah_donasi' => Output::tSparator(1500000),
            'penerima_donasi' => "Raska",
            'metode_bayar' => "Transfer",
            'nama_partner' => "Bank BJB",
            'path_gambar_cp' => "/assets/images/partners/bjb.png",
            'nomor_tujuan_bayar' => "0001000080001",
            'atas_nama_tujuan_bayar' => "Pojok Berbagi Indonesia",
            'samaran' => "Haji Arief",
            'nama_bantuan' => "Peduli Razka"
        );
        
        $kontak = "085322661186";
        $email = "arifriandi834@gmail.com";
        
        
        $arrayFollow = array(
            'nama_karyawan' => 'Dinda',
            'kontak_donatur' => (!is_null($kontak) ? $kontak : 'Tidak Ada Kontak'),
            'email_donatur' => $email,
            'doa_dan_pesan' => "Semoga razka dapat tersenyum kembali dan dapat bermain lagi dengan kakanya. Sang kaka (teteh Razka) semoga kamu dapat menggapai semua impianmu dimasa depan kelah amin. Jagain terus ya razkanya",
            'id_donasi' => 10
        );
        
        $arrayFollow = array_merge($arrayFollow, $arrayNotif);
        
        $subject = "Pojok Berbagi Donasi Payment Notification";
        $headers = 'From: Pojok Berbagi <no-replay@pojokberbagi.id>' . "\r\n" . 'Reply-To: CR PBI <cr@pojokberbagi.id>' . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $pesan = Ui::emailFollowUpDonasi($arrayFollow);

        if (mail($email, $subject, $pesan, $headers)) {
            echo "Email donasi " . $params[0] . " terkitim";
        }
    }
}