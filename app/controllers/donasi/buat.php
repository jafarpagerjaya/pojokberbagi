<?php
class BuatController extends Controller {

    private $_minDonasi,
            $_auth,
            $_home;

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
        if (count($params)) {
            $params = implode('/', $params);
        }
        Redirect::to('donasi/buat/baru'. (!is_null($params) ? '/'. $params : ''));
    }

    public function baru($params) {
        if (!count($params)) {
            Redirect::to('home');
        }
        
        $this->rel_action = array(
            array(
                'href' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
            ),
            array(
                'href' => '/assets/route/donasi/pages/css/baru.css'
            )
        );
        
        $this->script_action = array(
            array(
                'src' => '/assets/route/default/core/js/bootstrap.min.js'
            ),
            array(
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.js',
                'source' => 'trushworty'
            ),
            array(
                'src' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                'source' => 'trushworty'
            ),
            array(
                'src' => '/assets/route/donasi/pages/js/baru.js'
            )
        );

        $id_bantuan = Sanitize::escape($params[0]);

        $this->model('Donasi');
        $data_bantuan = $this->model->isBantuanActive($id_bantuan);
        if ($data_bantuan->status != 'D') {
            Session::flash('notifikasi', array(
                'pesan' => 'Bantuan <b>'. $data_bantuan->nama .'</b> ' . Utility::keteranganStatusBantuan($data_bantuan->status),
                'state' => 'warning'
            ));
            Redirect::to('home');
        }
        if (!$data_bantuan) {
            Session::flash('notifikasi', array(
                'pesan' => 'ID Bantuan <b>'. $id_bantuan .'</b> tidak ditemukan',
                'state' => 'danger'
            ));
            Redirect::to('home');
        }

        $this->_home = $this->model('Home');
        $this->model('Auth');
        $this->data['akun'] = $this->model->data();
		if ($this->model->isSignIn()) {
            $staff = $this->model->isStaff($this->data['akun']->email, 'email');
            if ($staff) {
                $data = $this->_home->query('SELECT nama, email, kontak, (SELECT samaran FROM donatur WHERE email = ?) samaran FROM pegawai WHERE email = ?', array($this->data['akun'] ->email, $this->data['akun'] ->email));                
            } else {
                $data = $this->_home->getData('nama, email, kontak, samaran', 'donatur', array('id_akun','=', $this->data['akun']->id_akun));
            }
            if (count($data)) {
                $this->data['donatur'] = $data;
            }
        }

        Session::put('donasi', $id_bantuan);
        $this->data['bantuan'] = $data_bantuan;
        $this->data[Config::get('session/token_name')] = Token::generate();
    }

    public function ajax() {
        if (!isset($_POST['email'])) {
            return false;
        }
        $this->model('Donasi');
        $data = $this->model->getSamaranDonatur($_POST['email']);
        echo $data;
        return false;
    }
}