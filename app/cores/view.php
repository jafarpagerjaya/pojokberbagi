<?php
class View {
	protected $data,
			  $path,
			  $title;
	
	private $_router,
			$_route,
			$_controller,
			$_action,
			$_params;

	private function getTitle() {
		return !empty($this->title) ? $this->title .' · ' : '';
	}
	
	protected function getDefaultPath() {
		if (!App::getRouter()) {
			return false;
		}

		$controller_dir = $this->getController();
		$route = $this->getRoute();
		$themplate_name = $this->getAction().'.html';

		return VIEW_PATH.$route.DS.$controller_dir.DS.$themplate_name;
	}

	public function rander() {
		$data = $this->data;

		ob_start();
		include_once($this->path);
		$content = ob_get_clean();

		return $content;
	}

	private function setPathData($path, $data, $action_view = null) {
		if (!$path) {
			$path = $this->getDefaultPath();
		}
		if (!file_exists($path)) {
			throw new Exception("Themeplate file not found in path ".$path);
		}
		$this->path = $path;
		$this->data = $data;
	}

	private function setCoreHtml() {
		$route = App::getRouter()->getRoute();
		$this->path = VIEW_PATH.$route.'.html';
	}

	private function autoVersion($file) {
		// if it is not a valid path (example: a CDN url)
		if (strpos($file, DS) !== 0 && !file_exists(BASEURL . $file) || strpos($file, DS) === 0 && !file_exists(BASEURL . ltrim($file, DS))) return $file;
	
		// retrieving the file modification time
		// https://www.php.net/manual/en/function.filemtime.php
		$mtime = filemtime(BASEURL . ltrim($file, DS));
	
		return sprintf("%s?v=%d", $file, $mtime);
	}

	private function getLinkRel($property_param) {
		$property = 'rel_'.$property_param;
		if (count(is_countable($this->$property) ? $this->$property : []) > 0) {
			echo '<!-- Added Linked Rel Based On '.ucfirst($property_param).' Style -->';
			foreach ($this->$property as $link_rel_key => $link_rel_value) {
				$rel = 'stylesheet';
				$type = 'text/css';
				$media = null;
				$title = null;
				$hreflang = null;
				$charset = null;
				if (is_array($link_rel_value)) {
					foreach ($link_rel_value as $items => $items_value ) {
						switch($items) {
							case 'type':
								$type = $items_value;
							break;
							case 'href':
								$href = $items_value;
							break;
							case 'media':
								$media = $items_value;
							break;
							case 'rel':
								$rel = $items_value;
							break;
							case 'title':
								$title = $items_value;
							break;
							case 'hreflang':
								$hreflang = $items_value;
							break;
							case 'charset':
								$charset = $items_value;
							break;
							case 'source':
								$source = $items_value;
							break;
							case 'integrity':
								$integrity = $items_value;
							break;
							case 'crossorigin':
								$crossorigin = $items_value;
							break;
							default:
							die('Key Item ' . $items . ' Are Not Recognized');
							break;
						}
					}
				}

				$href = $this->autoVersion($href);

				if (strpos($href, DS) !== 0 && (empty($integrity) || empty($crossorigin)) && strpos($href, 'https://') !== 0) {
					$href = DS . $href;
				}

				$link = '<link rel="' . $rel . '" type="' . $type . '" href="' . $href . '"';
				if (!empty($media)) {
					$link .= ' media="' . $media . '"';
				}
				if (!empty($title)) {
					$link .= ' title="' . $title . '"';
				}
				if (!empty($hreflang)) {
					$link .= ' hreflang="' . $hreflang . '"';
				}
				if (!empty($charset)) {
					$link .= ' charset="' . $charset . '"';
				}
				if (!empty($integrity)) {
					$link .= ' integrity="' . $integrity . '"';
				}
				if (!empty($crossorigin)) {
					$link .= ' crossorigin="' . $crossorigin . '"';
				}
				echo $link .= '>';
			}
		}
	}
	
	private function getScript($property_param) {
		$property = 'script_'.$property_param;
		if (count(is_countable($this->$property) ? $this->$property : [])) {
			$type = 'text/javascript';
			echo '<!-- Added Script Src Based On '.ucfirst($property_param).' -->';
			foreach ($this->$property as $script_key => $script_value) {
				$charset = null;
				$source = null;
				$integrity = null;
				$crossorigin = null;
				if(is_array($script_value)) {
					foreach ($script_value as $items => $items_value) {
						switch($items) {
							case 'type':
								$type = $items_value;
							break;
							case 'src':
								$src = $items_value;
							break;
							case 'charset':
								$charset = $items_value;
							break;
							case 'source':
								$source = $items_value;
							break;
							case 'integrity':
								$integrity = $items_value;
							break;
							case 'crossorigin':
								$crossorigin = $items_value;
							break;
							default:
								die('Key Item ' . $items . ' Are Not Recognized => ' . $items_value);
							break;
						}
					}
					if (!file_exists(BASEURL.$src) && $source != 'trushworty') {
						exit("<pre/>Javascript file not found in path ".$src);
					}

					$src = $this->autoVersion($src);

					if (strpos($src, DS) !== 0 && $source != 'trushworty') {
						$src = DS . $src;
					}

					$script = '<script type="' . $type . '" src="' . $src . '"';
					
					if (!empty($charset)) {
						$script .= ' charset="' . $charset . '"';
					}
					if (!empty($source)) {
						$script .= ' source="' . $source . '"';
					}
					if (!empty($integrity)) {
						$script .= ' integrity="' . $integrity . '"';
					}
					if (!empty($crossorigin)) {
						$script .= ' crossorigin="' . $crossorigin . '"';
					}
					echo $script . '></script>';
				}
			}
		}
	}

	public function getNav() {
		if (count(is_countable($this->nav) ? $this->nav : [])) {
			$nav = '';
			foreach ($this->nav as $route => $controllers) {
				if ($this->getRoute() == $route) {
					if (is_array($controllers)) {
						foreach ($controllers as $controller => $menu_key) {
							if (($this->_route == 'admin') && (strtoupper($controller) == strtoupper($this->data['admin_alias'])) || ($this->_route == 'donatur') && (strtoupper($controller) == strtoupper($this->data['route_alias'])) || ($this->_route != 'admin') && ($this->_route != 'donatur') && strtolower($controller) == $this->getController()) {
								if (is_array($menu_key)) {
									foreach ($menu_key as $items => $items_value) {
										if (is_array($items_value)) {
											$href  = '';
											$title = '';
											$icon  = '';
											foreach ($items_value as $item => $item_value) {
												if ($items !== 'tree') {
													switch ($item) {
														case 'href':
															$href = $item_value;
														break;
														case 'title':
															$title = $item_value;
														break;
														case 'icon':
															$icon = $item_value;
														break;
														default:
															die($item . ' On Nav Not Recognize');
														break;
													}
												}
											}
											$nav .= '<li class="nav-item">
												<a href="'. $href .'" class="nav-link  ' . ((strtolower($items) == strtolower($this->getController())) ? 'active' : '') . '">'. (($this->_route == 'admin' || $this->_route == 'donatur') ? '<i class="' . $icon . '"></i>' : '') .'
													<span class="nav-link-inner--text">'. $title .'</span>
												</a>
											</li>';
											if (strtolower($items) == strtolower($this->getController())) {
												$this->page_name = $title;
											}
										}
									}
								}	
							}
						}
					}
				}
			}
			return $nav;
		}
	}

	

	public function getRoute() {
		return $this->_route;
	}

	public function getController() {
		return $this->_controller;
	}

	public function getAction() {
		return $this->_action;
	}

	public function getParams() {
		return $this->_params;
	}

	public function __construct($controller, $path = null, $data = array()) {
		$this->_route = App::getRouter()->getRoute();
		$this->_controller = App::getRouter()->getController();
		$this->_action = App::getRouter()->getAction();
		$this->_params = App::getRouter()->getParams();

		$this->setPathData($path, $data);
		$this->action_view = $this->rander();
		$this->rel_controller = $controller->getRelScript('rel_controller');
		$this->script_controller = $controller->getRelScript('script_controller');
		$this->rel_action = $controller->getRelScript('rel_action');
		$this->script_action = $controller->getRelScript('script_action');
		if (isset($controller->title)) {
			$this->title = $controller->title;
		}
		// $this->_router = App::getRouter();
		
		$this->nav = json_decode(file_get_contents(ROOT . 'app' . DS . 'cores' . DS . 'menu.alc.json'), true);
	}

	public function __destruct() {
		$this->setCoreHtml();
		$this->setPathData($this->path, $this->data, $this->action_view);
		echo $this->rander();
	}
}