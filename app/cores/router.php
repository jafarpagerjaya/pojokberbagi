<?php
	class Router {
		protected $uri,
				  $controller,
				  $controller_class,
				  $controller_object,
				  $action,
				  $params,
				  $route,
				  $method_prefix,
				  $languages;

		public function getUri() {
			return $this->uri;
		}

		public function getController() {
			return $this->controller;
		}

		public function getControllerClass() {
			return $this->controller_class;
		}

		public function getControllerObject() {
			return $this->controller_object;
		}

		public function getAction() {
			return $this->action;
		}

		public function getParams() {
			return $this->params;
		}

		public function getRoute() {
			return $this->route;
		}

		public function getMethodPrefix() {
			return $this->method_prefix;
		}

		public function getLanguages() {
			return $this->languages;
		}		

		public function __construct($uri) {
			$this->uri = urldecode(trim($uri, '/'));

			// get defaults
			$routes = Config::get('routes');
			$this->route = Config::get('defaults/default_route');
			$this->method_prefix = isset($routes[$this->route]) ? $routes[$this->route] : '';
			$this->languages = Config::get('defaults/default_languages');
			$this->controller = Config::get('defaults/default_controller');
			$this->action = Config::get('defaults/default_action');

			$uri_parts = explode('?', $this->uri);

			// Get path like lang/controller/action/param1/param2/...
			$path = $uri_parts[0];

			$path_parts = explode('/', $path);

			if (count($path_parts)) {
				// Get route or lang at first el
				if (in_array(strtolower(current($path_parts)), array_keys($routes))) {
					$this->route = strtolower(current($path_parts));
					$this->method_prefix = isset($routes[$this->route]) ? $routes[$this->route] : '';
					array_shift($path_parts);
					if (in_array(strtolower(current($path_parts)), Config::get('languages'))) {
						$this->languages = strtolower(current($path_parts));
						array_shift($path_parts);
					}
				} elseif (in_array(strtolower(current($path_parts)), Config::get('languages'))) {
					$this->languages = strtolower(current($path_parts));
					array_shift($path_parts);
				}
				// Get controller and next el of array
				if (current($path_parts)) {
					if (file_exists(ROOT . 'app'. DS .'controllers'. DS . $this->route . DS . strtolower(current($path_parts)) . '.php')) {
						$this->controller = strtolower(current($path_parts));
						array_shift($path_parts);
					} else {
						unset($path_parts);
					}
				}

				// Explode Controller if dash are founded and set Capital First for Each Word
				$controller_array = array_map('ucfirst', explode('-', $this->controller));
				$controller = implode('-', $controller_array);
				$this->controller_class = str_ireplace('-', '', $controller);
				$this->controller_class = $this->controller_class.'Controller';
				
				// Load Controller Class
				require_once ROOT . 'app'. DS .'controllers'. DS . $this->route . DS . strtolower($this->controller) . '.php';
				// Calling controller method
				$this->controller_object = new $this->controller_class();
				// Stop route if array path parts is empty
				if (empty($path_parts)) {
					return;
				}
				// Get action
				if (!empty($path_parts)) {
					$controller_method = strtolower(current($path_parts));
					$controller_method = str_ireplace('-', '_', $controller_method);
					if (method_exists($this->controller_object, $controller_method)) {
						$this->action = $controller_method;
						array_shift($path_parts);
					} else {
						unset($path_parts);
					}
				}
				// Get params - all of the rest
				if (!empty($path_parts)) {
					$this->params = $path_parts;
				}
			}
		}
	}