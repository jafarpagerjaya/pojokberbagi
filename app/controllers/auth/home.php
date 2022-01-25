<?php
class HomeController extends Controller {
    public function index() {
        $this->model('Auth');
        if (!$this->model->isSignIn()) {
            Redirect::to('auth'. DS .'signin');
        } else {
            if ($this->model->hasPermission('admin')) {
                Redirect::to('admin');
            } else {
                Redirect::to('donatur');
            }
        }
    }
}