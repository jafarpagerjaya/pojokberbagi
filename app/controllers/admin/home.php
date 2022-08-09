<?php
class HomeController extends Controller {
    public function __construct() {
        $this->title = 'Admin';

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
        
        $this->model("Auth");
        
        if (!$this->model->hasPermission('admin')) {
            if ($this->model->isSignIn()) {
                $this->model->getData('nama, id_pegawai', 'pegawai', array('email','=',$this->model->data()->email));
                Session::flash('error','Assalamualaikum, '. ucwords(strtolower($this->model->data()->nama)) .' [PBI-'. $this->model->data()->id_pegawai .']');
            }
            Redirect::to('donatur');
        }

        $this->data['akun'] = $this->model->data();

        $this->model("Admin");
        $this->model->getAllData('pegawai', array('email','=', $this->data['akun']->email));
        $this->data['pegawai'] = $this->model->data();

        $this->model->getData('alias', 'jabatan', array('id_jabatan','=',$this->data['pegawai']->id_jabatan));
        $this->data['admin_alias'] = $this->model->data()->alias;
    }

    public function index() {
        $this->script_action = array(
			array(
				'type' => 'text/javascript',
                'src' => '/assets/route/admin/pages/js/home.js'
			)
		);
        // $this->setKunjungan();
        // Track real path based on return php request for js dom
		$this->data['uri'] = base64_encode($this->getRealUri());

        switch (strtoupper($this->data['admin_alias'])) {
            case 'SYS':
                $this->sys();
                return VIEW_PATH.'admin'.DS.'home'.DS.'sys.html';
            break;

            case 'CRE':
                $this->cr();
                return VIEW_PATH.'admin'.DS.'home'.DS.'cre.html';
            break;

            case 'PRO':
                $this->program();
            break;
            
            default:
                Session::flash('error','Assalamualaikum, '. ucwords(strtolower($this->data['pegawai']->nama)) .' [PBI-'. $this->data['pegawai']->id_pegawai .']');
                Redirect::to('donatur');
                # code...
            break;
        }
    }

    public function program() {
        $this->title = 'Program';
    }

    public function cr() {
        $this->title = 'CR';
        $this->model('Cr');
        $this->data['info-card'] = array(
            'jumlah_akun' => $this->model->jumlahAkun(),
            'jumlah_donatur' => $this->model->jumlahDonatur(),
            'jumlah_donasi' => $this->model->jumlahDonasi(),
            'jumlah_bantuan' => $this->model->jumlahBantuanAktif()
        );
    }

    public function sys() {
        $this->title = 'SYS';
        $this->model('Sys');
        $this->data['info-card'] = array(
            'jumlah_akun' => $this->model->jumlahAkun(),
            'jumlah_donatur' => $this->model->jumlahDonatur(),
            'jumlah_donasi' => $this->model->jumlahDonasi(),
            'jumlah_bantuan' => $this->model->jumlahBantuanAktif()
        );
    }
}