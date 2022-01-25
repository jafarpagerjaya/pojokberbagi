<?php
class App {
	protected static $router;

	public static function getRouter() {
		return self::$router;
	}

	public static function run($uri) {
		self::$router = new Router($uri);
		$controller_object = self::$router->getControllerObject();
		$controller_method = self::$router->getAction();
		$controller_params = self::$router->getParams();
		// // Call it
		$path = $controller_object->$controller_method($controller_params);

		if ($path === false) {
			return false;
		} 
		// Build The View
		new View($path, $controller_object->getData(), $controller_object);
	}
}