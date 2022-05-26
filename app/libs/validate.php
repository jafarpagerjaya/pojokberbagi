<?php
class Validate {
	private $_passed = false,
			$_error = array(),
			$_errorAtRule = array(),
			$_db = null,
			$_feedback = null,
			$_source,
			$_fields;

	public function __construct() {
		$this->_db = Database::getInstance();
	}

	public function check($source, $fields = array(), $addConditions = array()) {
		$this->_source = $source;
		$this->_fields = $fields;
		foreach ($this->_fields as $field => $rules) {
			foreach ($rules as $rule => $rule_value) {
				if (!isset($this->_source[$field])) {
					$this->_source[$field] = false;
				}
				$field = trim($field);
				$value = Sanitize::escape(trim($this->_source[$field]));
				if (($rule === 'required') && (empty($value) || (!isset($value)))) {
					$this->addError("{$field} harap diisi");
					$this->addErrorAtRule($field, $rule);
				} else if (!empty($value)) {
					switch ($rule) {
						case 'min':
							if (strlen($value) < $rule_value) {
								$this->addError("{$field} harus lebih dari {$rule_value} karakter");
								$this->addErrorAtRule($field, $rule);
							}
							break;
						case 'max':
							if (strlen($value) > $rule_value) {
								$this->addError("{$field} harus kurang dari {$rule_value} karakter");
								$this->addErrorAtRule($field, $rule);
							}
							break;
						case 'matches':
							if ($value != $this->_source[$rule_value]) {
								$this->addError("{$field} harus sama");
								$this->addErrorAtRule($field, $rule);
							}
							break;
						case 'unique':
							$sql = "SELECT {$field} FROM {$rule_value} WHERE {$field} = '{$value}'";
							if (count($addConditions) >= 3) {
								$operators = array('=', '>', '<', '>=', '<=', '!=');

								$addCfield    = trim($addConditions[0]);
								$addCoperator = trim($addConditions[1]);
								$addCvalue    = Sanitize::escape(trim($addConditions[2]));
								$addCmark	  = (isset($addConditions[3]) ? Sanitize::escape(trim($addConditions[3])) : 'AND');
								if (in_array($addCoperator, $operators)) {
									// Belum ter test $conditionFilterNull untuk semua kondisi
									$conditionFilterNull = ($addCmark == 'AND' ? 'OR' : 'AND' ) . " {$addCfield} IS NULL";
									$sql .= " {$addCmark} ({$addCfield} {$addCoperator} '{$addCvalue}' {$conditionFilterNull})";
								}
							}
							$uniqueCheck = $this->_db->query($sql);
							if ($uniqueCheck->count()) {
								$this->addError("{$field} {$rule_value} sudah terpakai");
								$this->addErrorAtRule($field, $rule);
							}
							break;
						case 'regex':
							if (!preg_match($rule_value, $value)) {
								$this->addError("{$field} harus sesuai format");
								$this->addErrorAtRule($field, $rule);
							}
							break;
						case 'exists':
							$sql = "SELECT {$field} FROM {$rule_value} WHERE {$field} = '{$value}'";
							$existsCheck = $this->_db->query($sql);
							if (!$existsCheck->count()) {
								$this->addError("{$field} tidak ditemukan");
								$this->addErrorAtRule($field, $rule);
							}
							break;
						case 'digit':
							if (!ctype_digit($value)) {
								$this->addError("{$field} harus berupa angka");
								$this->addErrorAtRule($field, $rule);
							}
							break;
						case 'min_value':
							if (Sanitize::toInt($value) < $rule_value) {
								$this->addError("{$field} kurang dari nilai minimum");
								$this->addErrorAtRule($field, $rule);
							}
							break;
						case 'max_value':
							if (Sanitize::toInt($value) > $rule_value) {
								$this->addError("{$field} melebihi batas maximum");
								$this->addErrorAtRule($field, $rule);
							}
							break;
						case 'checked': 
							if ($value != $rule_value) {
								$this->addError("{$field} harus dicentang");
								$this->addErrorAtRule($field, $rule);
							}
							break;
						case 'file':
							$extention = explode('.', $value);
							$extention = end($extention);
							if (!array_key_exists($extention, $rule_value)) {
								$this->addError("{$field} harus berisi berjenis file " . implode(', ', $rule_value));
								$this->addErrorAtRule($field, $rule);
							}
							break;
						default:
							# code...
							break;
					}
				}
			}
			$this->setValueFeedback($field);
		}

		if (!$this->errors()) {
			$this->_passed = true;
		}

		return $this;
	}

	private function addErrorAtRule($name, $rule) {
		return $this->_errorAtRule[$name] = $rule;
	}

	private function errorRule($name) {
		return $this->_errorAtRule[$name];
	}

	private function addError($error) {
		return $this->_error[] = $error;
	}

	public function errors() {
		return $this->_error;
	}

	public function passed() {
		return $this->_passed;
	}

	public function feedback($name, $showName = false) {
		if (count($this->_error) > 0) {
			$this->_feedback = $name;
			foreach ($this->_error as $feedback) {
				if (preg_match("~\b" . $this->_feedback . "\b~", $feedback)) {
					if ($showName == true) {
						$feedback = str_ireplace($this->_feedback, "", $feedback); 
					}
					return ucfirst(str_replace("_", " ", $feedback));
				}
			}
		}
	}

	public function value($name) {
		return $this->_source[$name];
	}

	public static function isUrlExists($url){
		$file_headers = @get_headers($url);
		if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
			$exists = false;
		}
		else {
			$exists = true;
		}
	
		return $exists;
	}

	public function setValueFeedback($name, $feedback = null) {
		if (is_null($feedback)) {
			$this->validateData[$name] = array(
				'value' => $this->value($name),
				'feedback' => $this->feedback($name)
			);
		} else {
			$this->validateData[$name] = array(
				'value' => $this->value($name),
				'feedback' => $feedback
			);
		}
		if (strlen($this->feedback($name))) {
			$this->validateData[$name]['rule'] = $this->errorRule($name);
		}
	}

	public function getValueFeedback() {
		return $this->validateData;
	}

	public function getReturnError() {
		$inputName = null;
		foreach($this->validateData as $key_name => $name_value) {
			foreach($name_value as $key_item => $item_value) {
				if (strlen($key_item) > 0 && strtolower($key_item) == 'rule') {
					$inputName = $key_name;
				}
			}
			if (strlen($inputName)) {
				break;
			}
		}
		return $inputName;
	}

	public static function errorArrayRule ($arrayFeed) {
		$arrayRuleError = array();
		foreach($arrayFeed as $key => $value) {
			if (is_array($value)) {
				foreach($value as $rule => $rule_value) {
					if ($rule == 'rule') {
						$arrayRuleError[$key] = $value;
						unset($arrayFeed[$key]);
					}
				}
			}
		}
		return array($arrayFeed, $arrayRuleError);
	}

	public static function errorArrayRuleList($list_validate_error = array()) {
		if (count($list_validate_error) > 0) {
			$newListValidateError = array();
			$listValidateRule = array();
			foreach($list_validate_error as $validate_error_key => $validate_error_value) {
				array_push($newListValidateError, self::errorArrayRule($validate_error_value)[0]);
				array_push($listValidateRule, self::errorArrayRule($validate_error_value)[1]);
			}
			// if (count($listValidateRule) > 0) {
			//     $newListValidateRule = array();
			//     $listValidateRule = array_reverse($listValidateRule);
			//     foreach($listValidateRule as $validateRuleKey => $validateRuleValue) {
			//         $newListValidateRule = array_merge($newListValidateRule, $validateRuleValue);
			//     }
			//     $listValidateRule = $newListValidateRule;
			// }
			$newListValidateError = call_user_func_array('array_merge', $newListValidateError);
			$listValidateRule = call_user_func_array('array_merge', array_reverse($listValidateRule));
			return array_merge($newListValidateError, $listValidateRule);
		}
	}
}