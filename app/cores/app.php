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
		// Check it Method Scope
		if ($controller_method != 'index') {
			$reflection = new ReflectionMethod($controller_object, $controller_method);
			if (!$reflection->isPublic()) {
				Redirect::to('home');
			}
		}
		// Call it
		$path = $controller_object->$controller_method($controller_params);

		if ($path === false) {
			return false;
		} 
		// Build The View
		new View($controller_object, $path, $controller_object->getData());
	}
}