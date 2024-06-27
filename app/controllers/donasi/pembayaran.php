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
        if (count(is_countable($params) ? $params : []) < 1) {
            Redirect::to('home');
        }

        if (ctype_digit($params[0])) {
            $redirectLink = 'donasi/buat/baru/' . implode('/', $params);
        } else {
            $redirectLink = 'donasi/buat/' . implode('/', $params);
        }
        
        if (!Input::exists()) {
            Redirect::to($redirectLink);
        }

        $this->model('Donasi');
        $data_bantuan = $this->model->isBantuanActive(Sanitize::escape2($params[0]));
        if ($data_bantuan->blokir == '1') {
            Session::flash('notifikasi', array(
                'pesan' => 'Bantuan <b>'. $data_bantuan->nama .'</b> dengan ' . Utility::keteranganStatusBantuan($data_bantuan->status) .' sedang diblokir',
                'state' => 'danger'
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
        if ($data_bantuan->status != 'D') {
            Session::flash('notifikasi', array(
                'pesan' => 'Bantuan <b>'. $data_bantuan->nama .'</b> ' . Utility::keteranganStatusBantuan($data_bantuan->status),
                'state' => 'warning'
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
            ),
            'pesan_atau_doa' => array(
                'max' => 200
            )
        ), array('LOWER(email)', '!=', strtolower(Input::get('email'))));
        if (!$validate->passed()) {
            Session::put('error_feedback', $validate->getValueFeedback());
            Redirect::to($redirectLink);
        }

        $this->model->getData('id_donatur, samaran, kontak','donatur', array('LOWER(email)','=', strtolower(trim(Input::get('email')))));
        $data = $this->model->getResult();
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
                Redirect::to($redirectLink);
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
                Redirect::to($redirectLink);
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
            'id_bantuan' => $data_bantuan->id_bantuan,
            'id_donatur' => $id_donatur,
            'id_cp' => Sanitize::escape(trim(Input::get('metode_pembayaran')))
        );

        // Sementara pakai condisional tambahan AND jenis = 'TB'
        $dataCP = $this->model->query("SELECT LOWER(cp.jenis) jenis_payment, cp.kode_paygate_brand FROM channel_payment cp JOIN channel_account ca USING(id_ca) JOIN penyelenggara_jasa_pembayaran pjp USING(id_pjp) WHERE (cp.id_cp = ? AND cp.kode = 'LIP') OR (cp.jenis = 'TB' AND cp.id_cp = ?) AND cp.aktif = '1'", 
            array(
                $dataDonasi['id_cp'],
                $dataDonasi['id_cp']
            )
        );
        
        if ($dataCP == false) {
            Session::put('notifikasi', array(
                'pesan' => 'Metode pembayaran tidak aktif, silahkan pilih metode lainnya',
                'state' => 'error'
            ));
            Redirect::to($redirectLink);
        }

        $dataCP = $this->model->getResult();
        $jenis_payment = $dataCP->jenis_payment;

        // Jika input alias tidak dicentang
        if (Input::get('alias') != true) {
            $samaran = ucwords(strtolower(trim(Input::get('nama'))));
        }

        $dataDonasi['alias'] = (!is_null($samaran) ? Sanitize::escape2($samaran) : NULL);

        // Jika donatur mengisi kontak
        if (strlen(Input::get('kontak')) > 0) {
            $kontak = Sanitize::toInt2(Input::get('kontak'));
        }
        
        if (isset($kontak)) {
            $dataDonasi['kontak'] = $kontak;
        }

        // Cek jika open donasi sudah berakhir
        if (!is_null($data_bantuan->tanggal_akhir) && strtotime($data_bantuan->tanggal_akhir) <= time()) {
            $this->model->update('bantuan', array(
                'status' => 'S'
            ), array('id_bantuan','=',$id_bantuan));
            Session::flash('notifikasi', array(
                'pesan' => 'Mohon maaf bantuan sudah berakhir',
                'state' => 'warning'
            ));
            Redirect::to('home');
        }

        if ($jenis_payment != 'tb' && $jenis_payment != 'gi' && $jenis_payment != 'tn') {

            $secret_key = FLIP_API_KEY;

            $encoded_auth = base64_encode($secret_key.":");

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, FLIP_API."/v2/pwf/bill");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);

            curl_setopt($ch, CURLOPT_POST, TRUE);

            $hash_transaksi = $data_bantuan->tag . '/' . Hash::unique();

            // sender_bank sementara khusus e-wallet jadi qris
            $payloads = [
                "title" => "Donasi ". $data_bantuan->nama,
                "amount" => $dataDonasi['jumlah_donasi'],
                "type" => "SINGLE",
                "expired_date" => date('Y-m-d H:i', strtotime('+ 1 day')),
                // "redirect_url" => "https://pojokberbagi.id/donasi/pembayaran/transaksi/" . $hash_transaksi,
                "is_address_required" => 1,
                "is_phone_number_required" => 0,
                "step" => 3,
                "sender_name" => Input::get('nama'),
                "sender_email" => strtolower(trim(Input::get('email'))),
                "sender_address" => Config::getHTTPHost(),
                // Ini untuk Step 3 namun Step 3 hanya bisa untuk VA dan QRIS
                "sender_bank" => (($dataCP->kode_paygate_brand == 'gopay' || $jenis_payment == 'ew') ? 'qris' : $dataCP->kode_paygate_brand),
                "sender_bank_type" => Utility::flipSenderBankType($jenis_payment)
            ];

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payloads));

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Basic ".$encoded_auth,
                "Content-Type: application/x-www-form-urlencoded"
            ));

            curl_setopt($ch, CURLOPT_USERPWD, $secret_key.":");

            $response = curl_exec($ch);
            curl_close($ch);

            $dataResponse = json_decode($response);

            // Debug::pr($payloads);
            // Debug::prd($dataResponse);

            if (property_exists($dataResponse, 'code')) {
                if ($dataResponse->code == 'VALIDATION_ERROR') {
                    Session::flash('notifikasi', array(
                        'pesan' => "Flip Accept Payment [STEP 3] ". $dataResponse->errors[0]->message,
                        'state' => 'warning'
                    ));
                    Redirect::to('home');
                }
            } else if ($dataResponse->status == '401') {
                Session::flash('notifikasi', array(
                    'pesan' => "Flip Accept Payment [STEP 3] ". $dataResponse->message,
                    'state' => 'warning'
                ));
                Redirect::to('home');
            }

            $dataDonasi['external_id'] = $dataResponse->link_id;
            $dataDonasi['url'] = $dataResponse->link_url;
            $dataDonasi['kode_pembayaran'] = $hash_transaksi;
            $dataDonasi['end_at'] = $dataResponse->expired_date;
        }

        $this->model->startTransaction();
        $order = $this->model->create('order_donasi', $dataDonasi);
        
        if (!$order) {
            $this->model->rollback();
            Session::put('notifikasi', array(
                'pesan' => 'Gagal Create Order Donasi',
                'state' => 'error'
            ));
            Redirect::to('donasi/buat/'. (!is_null($data_bantuan) ? $data_bantuan->tag : 'baru/'. $data_bantuan->id_bantuan));
        }

        Cookie::update(Config::get('donasi/cookie_name'),'',-1,'/donasi/buat/'. (!is_null($data_bantuan) ? $data_bantuan->tag : 'baru/'. $data_bantuan->id_bantuan));

        Session::put('notifikasi', array(
            'pesan' => 'Berhasil Create Order Donasi',
            'state' => 'success'
        ));
        
        if ($jenis_payment == 'tb') {
            $this->model->commit();
            $id_create_record = $this->model->lastIID();
        } else {
            // bill link id from flip
            $id_create_record = $dataResponse->link_id;
            if ($jenis_payment == 'va') {
                $dataOrderVA = array(
                    'id_order_donasi' => $this->model->lastIID(),
                    'account_number' => $dataResponse->bill_payment->receiver_bank_account->account_number
                );
                $this->model->create('order_va', $dataOrderVA);
                if (!$this->model->affected()) {
                    $this->model->rollback();
                    Session::put('notifikasi', array(
                        'pesan' => 'Create Order Donasi Success But Failed in Create Order VA',
                        'state' => 'warning'
                    ));
                    Redirect::to($redirectLink);
                }
                $this->model->commit();
                // Sementara EW dan QR nyatu
            } else if ($jenis_payment == 'qr' || $jenis_payment == 'ew') {
                $dataOrderQR = array(
                    'id_order_donasi' => $this->model->lastIID(),
                    'qr_code' => $dataResponse->bill_payment->receiver_bank_account->qr_code_data
                );
                $this->model->startTransaction();
                $this->model->create('order_qr', $dataOrderQR);
                if (!$this->model->affected()) {
                    $this->model->rollback();
                    Session::put('notifikasi', array(
                        'pesan' => 'Create Order Donasi Success But Failed in Create Order QR',
                        'state' => 'warning'
                    ));
                    Redirect::to($redirectLink);
                }
                $this->model->commit();
            }
        }
        Redirect::to('donasi/pembayaran/tagihan/' . $jenis_payment . '/' . $id_create_record);
    }

    private function getBillPayment($link_id) {
        $secret_key = FLIP_API_KEY;

        $encoded_auth = base64_encode($secret_key.":");

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, FLIP_API."/v2/pwf/{$link_id}/payment");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Basic ".$encoded_auth,
            "Content-Type: application/x-www-form-urlencoded"
        ));

        curl_setopt($ch, CURLOPT_USERPWD, $secret_key.":");

        $response = curl_exec($ch);
        curl_close($ch);
        $dataResponse = json_decode($response);

        return $dataResponse->data[0];
    }

    public function tagihan($params) {
        // Debug::prd($params);
        if (count(is_countable($params) ? $params : []) < 2) {
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

        if (strtolower($params[0]) == 'tb') {
            $donasi = $this->model->getOrderDonasi($params[1]);
        } else {
            // link id 
            $this->model->query("SELECT 'od',IF(status != 'SUCCESSFUL','0','1') bayar, external_id link_id, notifikasi, order_donasi.STATUS, id_bantuan, order_donasi.kontak, doa, cp.jenis, cp.nama nama_cp, cp.nomor, cp.atas_nama, g.path_gambar path_gambar_cp, d.email
            FROM order_donasi LEFT JOIN channel_payment cp USING(id_cp) LEFT JOIN donatur d ON (d.id_donatur = order_donasi.id_donatur) LEFT JOIN gambar g ON(g.id_gambar = cp.id_gambar) WHERE external_id = ?
            UNION
            SELECT 'op', bayar, link_id, notifikasi, order_paygate.STATUS, id_bantuan, d.kontak, doa, cp.jenis, cp.nama nama_cp, cp.nomor, cp.atas_nama, g.path_gambar path_gambar_cp, d.email
            FROM order_paygate JOIN donasi USING(id_order_paygate) LEFT JOIN channel_payment cp USING(id_cp) LEFT JOIN donatur d ON (d.id_donatur = donasi.id_donatur) LEFT JOIN gambar g ON(g.id_gambar = cp.id_gambar) WHERE link_id = ?", 
                array(
                    'external_id' => Sanitize::escape2($params[1]), 
                    'link_id' => Sanitize::escape2($params[1])
                )
            );
            if (!$this->model->affected()) {
                Session::flash('notifikasi', array(
                    'pesan' => 'Tagihan yang anda cari tidak ditemukan',
                    'state' => 'danger'
                ));
                Redirect::to('home');
            }
            $donasi = $this->model->getResult();
            // Get Payment
            $billPayment = $this->getBillPayment($params[1]);
            $donasi = (object) array_merge((array) $donasi, (array) $billPayment);
            $donasi->nama_donatur = $donasi->sender_name;
            $donasi->jumlah_donasi = $donasi->amount;
            $donasi->atas_nama = $billPayment->sender_name;
            $donasi->nomor = $billPayment->virtual_account_number;
            if ($billPayment->sender_bank_type == 'virtual_account') {
                $donasi->atas_nama = $billPayment->sender_name;
                $donasi->nomor = $billPayment->virtual_account_number;
            }
            unset($donasi->amount);
            unset($donasi->sender_name);
            unset($donasi->bill_link);
            unset($donasi->settlement_status);
            unset($donasi->sender_bank);
        }
        
        if (!$donasi) {
            Session::flash('notifikasi', array(
                'pesan' => 'Data donasi <b>' . $params[1] . '</b> tidak ditemukan',
                'state' => 'danger'
            ));
            Redirect::to('home');
        }

        if ($donasi->status == 'SUCCESSFUL' && strtolower($params[0]) == 'tb' || strtolower($params[0]) != 'tb' && $billPayment->status == 'SUCCESSFUL') {
            Session::flash('notifikasi', array(
                'pesan' => 'Donasi sudah dibayar',
                'state' => 'success'
            ));
            Redirect::to('donasi/pembayaran/transaksi/' . $params[1]);
        }

        // Jika sudah lebih dari 24 jam
        if (isset($donasi->end_at)) {
            $expiry = strtotime($donasi->end_at) + 86400;
            if ($expiry < time()) {
                Session::flash('notifikasi', array(
                    'pesan' => 'Tagihan tidak valid sudah lebih dari 24 jam',
                    'state' => 'warning'
                ));
                Redirect::to('donasi/pembayaran/dibatalkan/' . $params[1]);
            }
        }

        $this->model->getData('id_bantuan, nama, nama_penerima, tanggal_akhir', 'bantuan', array('id_bantuan', '=', $donasi->id_bantuan));
        $bantuan = $this->model->getResult();
        
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
            if ($donasi->jenis == 'TB') {
                return VIEW_PATH.'donasi'.DS.'pembayaran'. DS . $params[0] . '.html';
            } else {
                header("Location: ". $billPayment->payment_url);
            }
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
            'nama_karyawan' => 'Dewi',
            'nama_donatur' => $donasi->nama_donatur,
            'kontak_donatur' => $donasi->kontak,
            'email_donatur' => $donasi->email,
            'nama_bantuan' => $bantuan->nama,
            'penerima_donasi' => $bantuan->nama_penerima,
            'doa_dan_pesan' => $donasi->doa,
            'jumlah_donasi' => Output::tSparator($donasi->jumlah_donasi),
            'nama_cp' => $donasi->nama_cp
        );

        if (isset($billPayment)) {
            $dataFollow['id'] = $billPayment->link_id;
            $filter = array(
                'key' => 'external_id',
                'value' => $dataFollow['id']
            );
        } else {
            $filter = array(
                'key' => 'id_order_donasi',
                'value' => Sanitize::escape2($donasi->id_order_donasi)
            );
            $dataFollow['id_order_donasi'] = Sanitize::escape2($donasi->id_order_donasi);
        }


        // Kirim email
        $subject = "[Info Donasi] Pojok Berbagi";
        $headers = 'From: Pojok Berbagi <no-replay@pojokberbagi.id>' . "\r\n" . 'Reply-To: CR Pojok Berbagi <cr@pojokberbagi.id>' . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $pesan = wordwrap(Ui::emailNotifDonasiDonatur($dataNotif), 70, "\r\n");

        $this->mailSended = false;
        
        if (mail($donasi->email, $subject, $pesan, $headers)) {
            $this->mailSended = true;
            $this->model->update('order_donasi', array(
                'notifikasi' => '1'
            ), array($filter['key'],'=',$filter['value']));
        } else {
            Session::flash('notifikasi', array(
                'pesan' => 'Email ' . $donasi->email . ' tidak valid, mohon maaf anda tidak akan mendapatkan notifikasi info donasi',
                'state' => 'danger'
            ));
        }

        if (strtolower($donasi->jenis) == 'tb') {
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

        header("Location: ". $billPayment->payment_url);
        return false;
    }

    public function transaksi($params) {
        if (count(is_countable($params) ? $params : []) < 1) {
            Redirect::to('home');
        }

        $this->model('Donasi');
        if (!ctype_digit($params[0])) {
            $this->model->getData('id_donasi','donasi',array('kode_pembayaran','=',implode('/',$params)));
            if (!$this->model->affected()) {
                Session::flash('notifikasi', array(
                    'pesan' => 'Kode Pembayaran tidak ditemukan',
                    'state' => 'warning'
                ));
                Redirect::to('home');
            }
            $params[0] = $this->model->getResult()->id_donasi;
        }

        $donasi = $this->model->getDataTransaksiDonasi(Sanitize::toInt2($params[0]));
        
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

    // Action untuk mengirim notifikasi donasi
    public function notif() {
        $data = isset($_POST['data']) ? $_POST['data'] : null;
        $token = isset($_POST['token']) ? $_POST['token'] : null;
        if($token !== FLIP_TOKEN){
            $result = json_encode(array(
                'error' => true,
                'message' => 'Token flip salah'
            ));
            echo $result;
            Redirect::to('home');
            return false;
        }
        $decoded_data = json_decode($data);
        $params = (array)$decoded_data;
        if (count(is_countable($params) ? $params : []) < 1) {
            $result = json_encode(array(
                'error' => true,
                'message' => 'Callback data are empty'
            ));
            echo $result;
            return false;
        }

        $this->model('Donasi');
        $this->model->query("SELECT order_donasi.status, external_id, url, kode_pembayaran, alias, order_donasi.kontak, doa, notifikasi, jumlah_donasi, end_at, id_bantuan, bantuan.nama nama_bantuan, nama_penerima, id_cp, id_donatur, d.nama, d.email, cp.nama nama_cp, gcp.path_gambar path_gambar_cp FROM order_donasi JOIN donatur d USING(id_donatur) LEFT JOIN bantuan USING(id_bantuan) LEFT JOIN channel_payment cp USING(id_cp) LEFT JOIN gambar gcp ON(gcp.id_gambar = cp.id_gambar) WHERE external_id = ?", array($decoded_data->bill_link_id));
        if (!$this->model->affected()) {
            $result = json_encode(array(
                'error' => true,
                'message' => 'Callback Unrecognize external_id'
            ));
            echo $result;
            return false;    
        }

        $dataOrderDonasi = $this->model->getResult();

        if ($decoded_data->status == 'FAILED') {
            $this->model->update('order_donasi',array('status'=>'FAILED'),array('external_id','=',$dataOrderDonasi->external_id));
            if (!$this->model->affected()) {
                $result = json_encode(array(
                    'error' => true,
                    'message' => 'CALLBACK CENCELED bill, failed to update status order_donasi'
                ));
                echo $result;
                return false;    
            }
            $result = json_encode(array(
                'error' => true,
                'message' => 'Payment bill dibatalkan'
            ));
            echo $result;
            return false; 
        }

        // Check Status Payment
        if ($decoded_data->status != 'SUCCESSFUL') {
            $result = json_encode(array(
                'error' => true,
                'message' => 'Callback bill belum lunas'
            ));
            echo $result;
            return false; 
        }

        if ($dataOrderDonasi->status != 'SUCCESSFUL') {
            $this->model->update('order_donasi',array('status' => 'SUCCESSFUL'), array('external_id','=',$dataOrderDonasi->external_id),'AND',array('kode_pembayaran','=',$dataOrderDonasi->kode_pembayaran));
            if (!$this->model->affected()) {
                Redirect::to('home');
                $result = json_encode(array(
                    'error' => true,
                    'message' => 'Failed to update callback order status'
                ));
                echo $result;
                return false; 
            }
        }

        try {
            $this->model->create('order_paygate', array(
                'payment_id' => $decoded_data->id,
                'redirect_url' => $dataOrderDonasi->url,
                'link_id' => $dataOrderDonasi->external_id,
                'status' => $decoded_data->status,
                'expiry_at' => $dataOrderDonasi->end_at,
                'completed_at' => $decoded_data->created_at
            ));
            $last = $this->model->lastIID();
        } catch (\Throwable $th) {
            throw $th;
            return false;
        }
        
        if (isset($dataOrderDonasi->kontak)) {
            if (json_decode(Fonnte::check($dataOrderDonasi->kontak))->status != true) {
                Session::put('notifikasi', array(
                    'pesan' => 'Failed to send WA notification kontak WA tidak terdaftar',
                    'state' => 'warning'
                ));
            } else {
                $text_pesan = 'Hi, *'. Sanitize::escape2($dataOrderDonasi->nama) .'* donasimu telah kami terima, makasih ya kamu berpartisipasi di program *' . Sanitize::escape2($dataOrderDonasi->nama_bantuan) . '*. Gunakan akun berbagi di https://pojokberbagi.id untuk melihat perkembangan dari donasimu atau scan QR yang ada di kuitansimu 🙆🏻‍♂️';
                $waResponse = Fonnte::send(Sanitize::toInt2($dataOrderDonasi->kontak), $text_pesan);
                // Debug::pr($waResponse);
            }
        }

        $this->model->query("SELECT DISTINCT(COUNT(id_donatur)) total_donatur, SUM(jumlah_donasi) total_donasi FROM donasi WHERE donasi.id_bantuan = ?", array($dataOrderDonasi->id_bantuan));
        if (!$this->model->affected()) {
            $result = json_encode(array(
                'error' => true,
                'message' => 'Callback resume donasi failed'
            ));
            echo $result;
            return false; 
        }

        $resumeDonasi = $this->model->getResult();

        $this->model->getData('id_kuitansi','kuitansi',array('id_donasi','=',$last));
        if (!$this->model->affected()) {
            $result = json_encode(array(
                'error' => true,
                'message' => 'Callback get kuitansi failed'
            ));
            echo $result;
            return false; 
        }

        $dataKuitansi = $this->model->getResult();

        $billPayment = $this->getBillPayment($dataOrderDonasi->external_id);
        $arrayNotif = array(
            'nama_donatur' => $dataOrderDonasi->nama,
            'jumlah_donasi' => Output::tSparator($dataOrderDonasi->jumlah_donasi),
            'metode_bayar' => str_replace('_',' ',$billPayment->sender_bank_type),
            'nama_cp' => $dataOrderDonasi->nama_cp,
            'path_gambar_cp' => $dataOrderDonasi->path_gambar_cp,
            // 'nomor_tujuan_bayar' => $billPayment->virtual_account_number,
            // 'atas_nama_tujuan_bayar' => "PojokBerbagiID",
            // 'samaran' => $billPayment->sender_name,
            'nama_bantuan' => $dataOrderDonasi->nama_bantuan,
            // 'payment_id' => $billPayment->id,
            'waktu_bayar' => $decoded_data->created_at,
            'jumlah_donatur' => Output::tSparator($resumeDonasi->total_donatur),
            'total_donasi' => Output::tSparator($resumeDonasi->total_donasi),
            'id_kuitansi' => $dataKuitansi->id_kuitansi,
            'link_kuitansi' => Config::getHTTPHost() .'/donasi/cek/kuitansi/'.$dataKuitansi->id_kuitansi
        );
        
        $subject = "Pojok Berbagi Donasi Payment Notification";
        $headers = 'From: Pojok Berbagi <no-replay@pojokberbagi.id>' . "\r\n" . 'Reply-To: CR PBI <cr@pojokberbagi.id>' . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $pesan = Ui::emailNotifDonasiDiterima($arrayNotif);

        if (mail($dataOrderDonasi->email, $subject, $pesan, $headers)) {
            echo "Email Payment Notification  terkitim";
        }
        return false;
    }
}