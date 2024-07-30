<?php
class Database {
	private static $_instance = null;
	private $_pdo,
			$_query, 
			$_error = false, 
			$_results, 
			$_count = 0;

	final public function __construct() {
		try {
			$this->_pdo = new PDO('mysql:host=' . Config::get('mysql/host') . ';dbname=' . Config::get('mysql/db'), Config::get('mysql/username'), Config::get('mysql/password'));
		} catch(PDOException $e) {
			die($e->getMessage());
		}
	}

	final public static function getInstance() {
		if (!isset(self::$_instance)) {
			self::$_instance = new Database();
		}
		return self::$_instance;
	}

	final public function startTransaction() {
		$sql = "START TRANSACTION";
		return $this->query($sql);
	}

	final public function commit() {
		$sql = "COMMIT";
		return $this->query($sql);
	}

	final public function rollback() {
		$sql = "ROLLBACK";
		return $this->query($sql);
	}	

	public function prepared($sql) {
		$this->_query = $this->_pdo->prepare($sql);
		return $this;
	}

	public function executePrepared($params = array()) {
		$this->_error = false;
		$pos = 1;
		if (count(is_countable($params) ? $params : [])) {
			foreach ($params as $param) {
				$param = ($param != '' ? $param : NULL);
				$this->_query->bindValue($pos, $param);
				$pos++;
			}
		}

		if ($this->_query->execute()) {
			$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
			$this->_count = $this->_query->rowCount();
		} else {
			$this->_error = true;
		}

		return $this;
	}

	final public function query($sql, $params = array()) {
		$this->_error = false;
		if ($this->_query = $this->_pdo->prepare($sql)) {
			$pos = 1;
			if (count(is_countable($params) ? $params : [])) {
				foreach ($params as $param) {
					$param = ($param != '' ? $param : NULL);
					$this->_query->bindValue($pos, $param);
					$pos++;
				}
			}

			if ($this->_query->execute()) {
				$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
				$this->_count = $this->_query->rowCount();
			} else {
				$this->_error = true;
			}
		}

		return $this;
	}

	final public function lastInsertId(){
        return $this->_pdo->lastInsertId();
    }

	private function filter($where = array()) {
		$filter = array();
		$operators = array('=', '>', '<', '>=', '<=', '!=','IS','IN','NOT IN','LIKE','LIKEL','LIKEU');

		$field    = $where[0];
		$operator = $where[1];

		if (in_array($operator, $operators)) {
			if ($operator != 'IN' AND $operator != 'NOT IN') {
				$filter = array(
					"{$field} {$operator} ?",
					1
				);
			} else {
				if ($operator == 'LIKE') {
					$filter = array(
						"{$field} {$operator} CONCAT('%',?,'%')",
						1
					);
				}
				else if ($operator == 'LIKEL') {
					$filter = array(
						"{$field} {$operator} LOWER(CONCAT('%',?,'%'))",
						1
					);
				}
				else if ($operator == 'LIKEU') {
					$filter = array(
						"{$field} {$operator} UPPER(CONCAT('%',?,'%'))",
						1
					);
				} else {
					$questionMarks = '';
					$xCol = 1;

					foreach ($where[2] as $questionMark) {
						$questionMarks .= "?";
						if ($xCol < count(is_countable($where[2]) ? $where[2] : [])) {
							$questionMarks .= ", ";
						}
						$xCol++;
					}

					$filter = array(
						"{$field} {$operator} ({$questionMarks})",
						1
					);
				}
			}
		}
		return $filter;
	}

	final public function action($action, $table, $where = array(), $condition = null, $another_filter = array()) {
		if (count(is_countable($where) ? $where : []) === 3) {
			$value 	= array();
			if (is_array($where[2])) {
				$value = array_merge($value, $where[2]);
			} else {
				array_push($value, $where[2]);
			}
			$filter = $this->filter($where);
			$filter_sql = $filter[0];
			if (!is_null($condition)) {
				if (count(is_countable($another_filter) ? $another_filter : []) === 3) {
					if (is_array($another_filter[2])) {
						$value = array_merge($value, $another_filter[2]);
					} else {
						array_push($value, $another_filter[2]);
					}
					$another_filter = $this->filter($another_filter);
					if ($another_filter[1] == true) {
						$filter_sql = "{$filter_sql} {$condition} {$another_filter[0]}";
					}
				}
			}

			if ($filter[1] == true) {
				$sql = "{$action} FROM {$table} WHERE {$filter_sql}";
				if (!$this->query($sql, $value)->error()) {
					return $this;
				}
			}
		}

		return false;
	}

	final public function getAll($table, $where, $condition = null, $another_filter = array()) {
		return $this->action('SELECT *', $table, $where, $condition, $another_filter);
	}

	final public function get($fields, $tables, $where, $condition = null, $another_filter = array()) {
		return $this->action('SELECT ' . $fields, $tables, $where, $condition, $another_filter);
	}

	final public function delete($table, $where, $condition = null, $another_filter = array()) {
		return $this->action('DELETE ', $table, $where, $condition, $another_filter);
	}

	final public function insert($table, $fields = array()) {
		if (count(is_countable($fields) ? $fields : [])) {
			$keys = array_keys($fields);
			$values = '';
			$xCol = 1;

			foreach ($fields as $field) {
				$values .= "?";
				if ($xCol < count(is_countable($fields) ? $fields : [])) {
					$values .= ", ";
				}
				$xCol++;
			}

			$sql = "INSERT INTO {$table}(" . implode(", ", $keys) . ") VALUES({$values})";
			if (!$this->query($sql, $fields)->error()) {
				return true;
			}
		}
		return false;
	}

	// Multiple insert Jd PR
	final public function insertMultiple($table, $rows = array()) {
		if (count(is_countable($rows) ? $rows : []) > 0) {
			$keys = array_keys(current($rows));
			$values = '';
			$yRow = 1;
			foreach ($rows as $row => $record) {
				$values .= "(";
				$xCol = 1;
				if (is_array($record)) {
					foreach ($record as $fields => $field) {
						$values .= "?";

						if ($xCol < count(is_countable($record) ? $record : [])) {
							$values .= ", ";
						}
						$xCol++;
					}
				}
				$values .= ")";

				if ($yRow < count(is_countable($rows) ? $rows : [])) {
					$values .= ", ";
				}
				$yRow++;
			}
			$sql = "INSERT INTO {$table}(" . implode(", ", $keys) . ") VALUES {$values}";
			if (!$this->query($sql, Config::array_flatten($rows))->error()) {
				return true;
			}
		}
		return false;
	}

	final public function update($table, $fields, $where) {
		if (count(is_countable($fields) ? $fields : [])) {
			$set = '';
			$xSetCOl = 1;
			foreach ($fields as $column_name => $value) {
				$set .= "{$column_name} = ?";
				if ($xSetCOl < count(is_countable($fields) ? $fields : [])) {
					$set .= ", ";
				}
				$xSetCOl++;
			}
			if (count(is_countable($where) ? $where : []) === 3) {
				$filter = $this->filter($where);

				$field    = $where[0];
				$value    = $where[2];

				if (!is_array($value)) {
					$value = array($value);
				}

				if ($filter[1] == true) {
					$sql = "UPDATE {$table} SET {$set} WHERE {$filter[0]}";
					$fields = array_merge($fields, $value);
					if (!$this->query($sql, $fields)->error()) {
						return true;
					}
				}
			}
		}
		return false;
	}

	final public function results() {
		return $this->_results;
	}

	final public function result() {
		return $this->results()[0];
	}

	final public function error() {
		return $this->_error;
	}

	final public function count() {
		return $this->_count;
	}
}