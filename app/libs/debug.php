<?php 
class Debug {
    public static function pr($param) {
        echo '<pre>';
        print_r($param);
        echo '<br/>End param Debug</pre>';
    }

    public static function prd($param) {
        echo '<pre>';
        print_r($param);
        echo '<br/>End param Debug</pre>';
		die();
    }

    public static function vd($param) {
		echo '<pre>';
		var_dump($param);
		echo '</pre>';
		die();
	}
}