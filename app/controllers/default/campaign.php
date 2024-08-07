<?php
class CampaignController extends Controller {

    private $_campaign;

    public function __construct() {
        $this->title = "Campaign";
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
        $this->_campaign = $this->model('Campaign');

        $this->_auth = $this->model('Auth');
		$this->data['signin'] = $this->_auth->isSignIn();
    }

    public function index($params) {
        if (count(is_countable($params) ? $params : []) > 0) {
            $this->tag($params);
            return VIEW_PATH.'default'.DS.'campaign'.DS.'tag.html';
        } else {
            Redirect::to('');
        }
    }

    private function tag($params) {
        $this->_campaign->countData('campaign JOIN bantuan USING(id_bantuan)',array('tag = ?', Sanitize::escape2($params[0])));
        if ($this->_campaign->getResult()->jumlah_record == 0) {
            Redirect::to('');
        }

        $this->rel_action = array(
            array(
                'href' => 'https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css',
                'source' => 'trushworty'
            ),
			array(
				'href' => '/assets/route/default/pages/css/tag.css'
			)
		);

		$this->script_action = array(
			array(
				'src' => '/assets/main/js/token.js'
			),
            array(
                'type' => 'text/javascript',
                'src' => 'https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js',
                'source' => 'trushworty'
            ),
			array(
				'src' => '/assets/route/default/pages/js/tag.js'
			)
		);

        $this->_campaign->getData('isi, tag, id_bantuan','campaign JOIN bantuan USING(id_bantuan)',array('tag','=',Sanitize::escape2($params[0])),'AND',array('aktif','=','1'));
        if (!$this->_campaign->affected()) {
            Session::flash('notifikasi', array(
                'pesan' => 'Halaman campaign sudah tidak aktif',
                'state' => 'warning'
            ));
            Redirect::to('campaign');
        }
        $dataCampaign = $this->_campaign->getResult();
        $this->data['campaign'] = $dataCampaign;
        $this->setKunjungan2($params);
    }
}