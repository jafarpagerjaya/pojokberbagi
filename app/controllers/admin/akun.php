<?php
class AkunController extends Controller {

    private $_auth;

    public function __construct() {
        $this->rel_controller = array(
            array(
                'href' => '/assets/pojok-berbagi-style.css'
            ),
            array(
                'href' => '/assets/route/admin/core/css/admin-style.css'
            )
        );

        $this->script_controller = array(
            array(
                'src' => '/assets/pojok-berbagi-script.js'
            ),
            array(
                'src' => '/assets/route/admin/core/js/admin-script.js'
            )
        );

        $this->title = 'Akun';

        $this->_auth = $this->model("Auth");
        if (!$this->_auth->hasPermission('admin')) {
            Redirect::to('donatur');
        }

        $this->data['akun'] = $this->_auth->data();

        $this->_admin = $this->model("Admin");
        $this->_admin->getAllData('pegawai', array('email','=', $this->data['akun']->email));
        $this->data['pegawai'] = $this->_admin->getResult();

        if (is_null($this->data['pegawai']->id_jabatan)) {
            Redirect::to('donatur');
        }

        $this->_admin->getData('alias', 'jabatan', array('id_jabatan','=',$this->data['pegawai']->id_jabatan));
        $this->data['admin_alias'] = $this->_admin->getResult()->alias;
    }

    public function index() {
        $this->rel_action = array(
            array(
                'href' => '/assets/route/admin/pages/css/akun.css'
            )
        );

        $this->model('Sys');
        $this->data['info-card'] = array(
            'jumlah_akun' => $this->model->jumlahAkun(),
            'jumlah_akun_terblock' => $this->model->jumlahAkunTerblock(),
            'jumlah_akun_admin' => $this->model->jumlahAkunAdmin()
        );

        $dataAkun = $this->_auth->getDataAkun();
        $this->data['halaman'] = 1;
        $this->data['record'] = $this->_auth->countData();
        if ($dataAkun != false) {
            $this->data['list_akun'] = $dataAkun;
        }
    }

    public function blok($params = array()) {
        if (count(is_countable($params) ? $params : [])) {
            $data = $this->_auth->get('aktivasi, username',array('id_akun','=',$params[0]));
            if ($data->aktivasi == 1) {
                $setAktivasi = '0';
                $mode = 'blokir';
            } else {
                $setAktivasi = '1';
                $mode = 'unblokir';
            }
            $cekCurrentId = $this->_auth->isCurrentAkun($params[0]);
            if (!$cekCurrentId) {
                $this->_auth->update(array('aktivasi' => $setAktivasi), $params[0]);
                if ($this->_auth->affected()) {
                    Session::flash('success','Akun [<b>' . $data->username . '</b>] telah di<span class="font-weight-bold ' . ($setAktivasi == '1' ? 'text-green' : 'text-danger') . '">' . $mode . '</span>');
                }
            } else {
                Session::flash('error','Tidak bisa blok diri sendiri saat online');
            }
        }
        Redirect::to('admin/akun');
    }

    public function halaman($params = array()) {
        if (count(is_countable($params) ? $params : [])) {
            if ($params[0] == 1) {
                $param1 = 1;
                $param2 = $param1 + $this->getPageRecordLimit() - 1;
            } else {
                $param1 = $params[0] * $this->getPageRecordLimit();
                $param2 = $param1 + $this->getPageRecordLimit();
            }

            $this->model('Sys');
            $this->data['info-card'] = array(
                'jumlah_akun' => $this->model->jumlahAkun(),
                'jumlah_akun_terblock' => $this->model->jumlahAkunTerblock(),
                'jumlah_akun_admin' => $this->model->jumlahAkunAdmin()
            );
            
            $dataAkun = $this->_auth->getDataAkun($param1, $param2);
            if ($dataAkun) {
                $this->data['record'] = $this->_auth->countData();
                $this->data['halaman'] = $params[0];
                $this->data['list_akun'] = $dataAkun;

                return VIEW_PATH.'admin'.DS.'akun'.DS.'index.html';
            }
        }
        Redirect::to('admin/akun');
    }

    public function aktivasi($params) {
        if (count(is_countable($params) ? $params : [])) {
            $this->_auth->update('akun',array(
                    'pin'=> '',
                    'aktivasi' => 1
                ), $params[0]
            );
            if ($this->_auth->count()) {
                Session::flash('success','Akun berhasil diaktivkan');
            } else {
                Session::flash('error','Akun gagal diaktivkan');
            }
            Redirect::to('auth/signin');
        }
        Redirect::to('admin/akun');
    }
}