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
        Redirect::to('donasi/cek/kwitansi'. (!is_null($params) ? '/'. $params : ''));
    }

    public function kwitansi($params) {
        if (!count(is_countable($params) ? $params : [])) {
            Redirect::to('home');
        }

        $this->rel_action = array(
            array(
                'href' => '/assets/main/css/stepper.css'
            ),
            array(
                'href' => '/assets/route/donasi/pages/css/kwitansi.css'
            )
        );

        $this->script_action = array(
            array(
                'src' => '/assets/route/donasi/pages/js/kwitansi.js'
            )
        );

        $id_kwitansi = Sanitize::escape2($params[0]);
        $this->model('Donasi');
        $resumeKwitansi = $this->model->getResumeKwitansiDonasi($id_kwitansi);
        if (!$resumeKwitansi) {
            Session::flash('notifikasi', array(
                'pesan' => 'Nomor kwitansi <b>'. $data_bantuan->nama .'</b> tidak ditemukan',
                'state' => 'warning'
            ));
            Redirect::to('home');
        }
        $this->data['resume_kwitansi'] = $resumeKwitansi;

        $timeLineKwitansi = $this->model->getTimeLineDonationByIdKwitansi($id_kwitansi);
        if (!$timeLineKwitansi) {
            Session::flash('notifikasi', array(
                'pesan' => 'Terjadi kesalahan data nomor kwitansi disisi server',
                'state' => 'danger'
            ));
            Redirect::to('home');
        }
        $this->data['timeline_kwitansi'] = $timeLineKwitansi;
    }
}