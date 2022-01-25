<?php
class Breadcrumb {
    public static function generate() {
        $route = strtolower(App::getRouter()->getRoute());
        $controller = strtolower(App::getRouter()->getController());
        $action = strtolower(App::getRouter()->getAction());
        $params = App::getRouter()->getParams();

        $breadcrumb = '';
        if (strtolower($controller) != "home") {
            $breadcrumb = '<li class="breadcrumb-item"><a href="/' . $route . '"><i class="fas fa-home"></i></a></li>';
        }
        if (strtolower($action) != "index") {
            $breadcrumb .= '<li class="breadcrumb-item"><a href="/' . $route . '/' . $controller . '">' . ucfirst($controller) . '</a></li>';
        }
        if (count($params)) {
            if (count($params) > 1) {
                $breadcrumb .= '<li class="breadcrumb-item"><a href="/' . $route . '/' . $controller . '/' . $action . '">' . ucfirst($action) . '</a></li>';
            }
            $i = 0;
            foreach($params as $param => $param_value) {
                if ($i == 0 && count($params) == 1 ) {
                    $breadcrumb .= '<li class="breadcrumb-item active">' . ucfirst($action) . '</li>';                
                } elseif ($i < count($params) - 1) {
                    $j = 0;
                    $link = array();
                    foreach($params as $keyParams => $valueParams) {
                        if ($j <= $i) {
                            array_push($link, $valueParams);
                            $j++;
                        }
                    }
                    $link = implode('/', $link);
                    $breadcrumb .= '<li class="breadcrumb-item"><a href="/' . $route . '/' . $controller . '/' . $action . '/' . $link . '">' . ucwords(str_replace("-", " ", $param_value)) . '</a></li>';
                } else {
                    $breadcrumb .= '<li class="breadcrumb-item active">' . ucwords(str_replace("-", " ", $param_value)) . '</li>';
                }
                $i++;
            }
        } else {
            $breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">' . (($action == "index") ? ucfirst($controller) : ucfirst($action)) . '</li>';
        }
        return $breadcrumb;
    }
}