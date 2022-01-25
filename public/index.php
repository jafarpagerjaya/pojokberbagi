<?php
require_once '../app/init.php';
// $route = new Router($_SERVER['REQUEST_URI']);

// echo '<pre>';
// print_r('Route: '. $route->getRoute());
// echo '<br>';
// print_r('Languages: '. $route->getLanguages());
// echo '<br>';
// print_r('Controller: '. $route->getController());
// echo '<br>';
// print_r('Action: '. $route->getAction());
// echo '<br>';
// print_r('Action to be called/prefix: '. $route->getMethodPrefix().$route->getAction());
// echo '<br>';
// echo 'Params: ';
// print_r($route->getParams());

App::run($_SERVER['REQUEST_URI']);