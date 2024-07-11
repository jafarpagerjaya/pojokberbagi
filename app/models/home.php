<?php
class HomeModel {
    protected $db,
              $data,
              $cookieName,
              $_offset = 0,
              $_limit = 10,
              $_order = 1,
              $_order_direction = 'DESC';

    private $_halaman = array(1,10),
            $_search;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function checkParams($table, $fields) {
        return (isset($table) && is_array($fields)) ? true : false;
    }

    public function startTransaction() {
        $this->db->startTransaction();
    }

    public function commit() {
        $this->db->commit();
    }

    public function rollback() {
        $this->db->rollback();
    }

    public function prepareStmt($sql) {
        if (!$this->db->prepared($sql))  {
            throw new Exception("Error Processing Prepared " . $sql);
        }
        return true;
    }

    public function executeStmt($params = array()) {
        if (!$this->db->executePrepared($params))  {
            throw new Exception("Error Processing execute Prepared STMT");
        }
        return true;
    }

    public function create($table, $fields = array()) {
        if ($this->checkParams($table, $fields)) {
            if (!$this->db->insert($table, $fields))  {
                throw new Exception("Error Processing Insert " . $table);
            }
            return true;
        }
        return false;
	}

    public function createMultiple($table, $rows = array()) {
        if ($this->checkParams($table, $rows)) {
            if (!$this->db->insertMultiple($table, $rows))  {
                throw new Exception("Error Processing Insert Multiple " . $table);
            }
            return true;
        }
        return false;
	}

	public function delete($table, $fields = array()) {
        if ($this->checkParams($table, $fields)) {
            if (!$this->db->delete($table, $fields)) {
                throw new  Exception("Error Processing Delete " . $table);
            }
            return true;
        }
        return false;
	}

    public function update($table, $fields = array(), $where = array(), $condition = null, $another_filter = array()) {
		if ($this->checkParams($table, $fields) && is_array($where)) {
            $oldData = $this->getData(implode(',', array_keys($fields)), $table, $where, $condition, $another_filter);
            $fields = array_diff_assoc($fields, json_decode(json_encode($oldData), true));
            if (!empty($fields)) {
                if (!$this->db->update($table, $fields, $where)) {
                    throw new  Exception("Error Processing Update " . $table);
                }
                return true;
            }
        }
        return false;
	}

    public function getResults() {
        return $this->db->results();
    }

    public function getResult() {
        return $this->db->result();
    }

    public function getAllData($table, $where = array(), $condition = null, $another_filter = array()) {
		if ($this->checkParams($table, $where)) {
            if (!$this->db->getAll($table, $where, $condition, $another_filter)) {
                throw new Exception("Error Processing Read All Data " . $table);
            }
            if ($this->db->count()) {
                $this->data = $this->db->results();
                return $this->data;
            }
        }
        return false;
    }

    public function getData($fields, $table, $where = array(), $condition = null, $another_filter = array()) {
		if ($this->checkParams($table, $where) && isset($fields)) {
            if (!$this->db->get($fields, $table, $where, $condition, $another_filter)) {
                throw new Exception("Error Processing Read Data " . $table);
            }
            if ($this->db->count()) {
                $this->data = $this->db->results();
                return $this->data;
            }
        }
        return false;
    }

    public function countData($table, $where = null, $search = null) {
        $table = Sanitize::escape2($table);
        $sql = array("SELECT COUNT(*) jumlah_record FROM {$table}");
        $values = array();
        $filter = null;
        if (!is_null($where)) {
            if (is_array($where)) {
                if (is_array($where[1])) {
                    $values = array_merge($values, $where[1]);
                } else {
                    array_push($values, $where[1]);
                }
                $where = $where[0];
            }
            $filter = "WHERE {$where}";
            array_push($sql, $filter);
        }
        
        if (!is_null($search)) {
            if (is_array($search)) {
                if (is_array($search[1])) {
                    $values = array_merge($values, $search[1]);
                } else {
                    array_push($values, $search[1]);
                }
                $search = $search[0];
            }
            if (!is_null($filter)) {
                $filter = "AND {$search}";
            } else {
                $filter = "WHERE {$search}";
            }
            array_push($sql, $filter);
        }
        
        $sql = implode(' ', $sql);

		$this->db->query($sql, $values);
        
		if ($this->db->count()) {
			$this->data = $this->db->result();
			return $this->data;
		}
		return false;
	}

    public function affected() {
		return $this->db->count();
	}

    public function error() {
		return $this->db->error();
    }

    public function data() {
        return $this->data;
    }

    public function lastIID() {
        return $this->db->lastInsertId();
    }

    public function query($sql, $params = array()) {
        $this->db->query($sql, $params);
        if ($this->db->count()) {
			$this->data = $this->db->results();
			return $this->data;
		}
		return false;
    }

    protected function split($string, $spliters) {
		return preg_split('~'.$spliters.'(?![^()]*\))~', trim($string));
	}

    // Setter And Getter
    public function setSearch($search) {
        $this->_search = Sanitize::escape2($search);
    }

    public function getSearch() {
        return $this->_search;
    }

    public function setOffset($offset) {
        $this->_offset = Sanitize::toInt2($offset);
    }

    public function getOffset() {
        return $this->_offset;
    }

    public function setLimit($limit) {
        $this->_limit = Sanitize::toInt2($limit);
    }

    public function getLimit() {
        return $this->_limit;
    }

    public function setDirection($direction) {
        $this->_order_direction = strtoupper(Sanitize::escape2($direction));
    }

    public function getDirection() {
        return $this->_order_direction;
    }

    public function setOrder($order_by) {
        $this->_order = $order_by;
    }

    public function getOrder() {
        return $this->_order;
    }

    public function setHalaman($params, $table, $offset = false) { 
        $param1 = (($params-1) * $this->getLimit()) + 1;
        if ($param1 < 0) {
            $param1 = 0;
        }
        $param2 = $params * $this->getLimit();
        // Cek apakah OFFSET ATAU SEEK
        if (is_null($this->getSearch()) && $offset === false) {
            // SEEK
            // Cek Direction
            if ($this->_order_direction == 'DESC') {
                // Dibalik
                $result = $this->countData($table);
                $bStart = $result->jumlah_record - $param2 + 1;
                if ($bStart <= 0) {
                    $bStart = 1;
                }
                $bEnd = $result->jumlah_record - $param1 + 1;
                if ($bEnd <= 0) {
                    $bEnd = $param2;
                }
                $param1 = $bStart;
                $param2 = $bEnd;
            }
        } else {
            // OFFSET USE $param2
            $param1--;
        }
        $this->_halaman = array($param1, $param2);
    }

    public function getHalaman() {
        return $this->_halaman;
    }

    public function resetHalaman($array) {
        $this->_halaman = $array;
    }
}