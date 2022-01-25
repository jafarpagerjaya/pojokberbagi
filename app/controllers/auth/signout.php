<?php 
class SignoutController extends Controller {

    public function index() {
        $this->model('Auth');

        if ($this->model->isSignIn()) {
            $this->model->signout();
            if (Cookie::exists(Config::get('client/cookie_name'))) {
                $client = json_decode(base64_decode(Cookie::get(Config::get('client/cookie_name'))), true);
                $expiry = $client['expiry'];
                unset($client['auth']);
                if (count($client) < 2) {
                    Cookie::delete(Config::get('client/cookie_name'));
                } else {
                    $id_pengunjung = $client['id_pengunjung'];
                    $client = base64_encode(json_encode($client));
                    Cookie::update(Config::get('client/cookie_name'), $client, $expiry);
                    if (isset($id_pengunjung)) {
                        $this->model('Home');
                        try {
                            $this->model->update('pengunjung', array(
                                'client_key' => Sanitize::escape(trim($client))
                            ), array('id_pengunjung', '=', Sanitize::escape(trim($id_pengunjung))));
                        } catch (\Throwable $th) {
                            Session::flash('error', $th->getMessage());
                        }
                    }
                }
            }
        }

        Redirect::to('auth/signin');
    }
}