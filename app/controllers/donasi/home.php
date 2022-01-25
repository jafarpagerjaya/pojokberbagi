<?php 
class HomeController extends Controller {
    public function index($params) {
        Redirect::to('donasi/buat/baru/'.$params[0]);
    }
}