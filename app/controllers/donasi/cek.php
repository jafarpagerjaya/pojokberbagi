<?php 
class CekController extends Controller {
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
        if (count(is_countable($params) ? $params : [])) {
            $params = implode('/', $params);
        }
        Redirect::to('donasi/cek/kuitansi'. (!is_null($params) ? '/'. $params : ''));
    }

    public function kuitansi($params) {
        if (!count(is_countable($params) ? $params : [])) {
            Redirect::to('home');
        }

        $this->rel_action = array(
            array(
                'href' => '/assets/main/css/stepper.css'
            ),
            array(
                'href' => '/assets/route/donasi/pages/css/kuitansi.css'
            )
        );

        $this->script_action = array(
            array(
                'src' => '/assets/route/donasi/pages/js/kuitansi.js'
            )
        );

        $id_kuitansi = Sanitize::escape2($params[0]);
        $this->model('Donasi');
        $resumeKuitansi = $this->model->getResumeKuitansiDonasi($id_kuitansi);
        if (!$resumeKuitansi) {
            Session::flash('notifikasi', array(
                'pesan' => 'Nomor kuitansi <b>'. $data_bantuan->nama .'</b> tidak ditemukan',
                'state' => 'warning'
            ));
            Redirect::to('home');
        }
        $this->data['resume_kuitansi'] = $resumeKuitansi;

        $timeLineKuitansi = $this->model->getTimeLineDonationByIdKuitansi($id_kuitansi);
        if (!$timeLineKuitansi) {
            Session::flash('notifikasi', array(
                'pesan' => 'Terjadi kesalahan data nomor kuitansi disisi server',
                'state' => 'danger'
            ));
            Redirect::to('home');
        }
        $this->data['timeline_kuitansi'] = $timeLineKuitansi;
    }
}