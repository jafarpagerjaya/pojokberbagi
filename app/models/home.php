<?php
class HomeModel {
    protected $db,
              $data,
              $cookieName;

    private $_halaman = array(1,10),
            $_offset = 10,
            $_orderBy = 1,
            $_ascDsc = 'ASC';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function checkParams($table, $fields) {
        return (isset($table) && is_array($fields)) ? true : false;
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

	public function delete($table, $fields = array()) {
        if ($this->checkParams($table, $fields)) {
            if (!$this->db->delete($table, $fields)) {
                throw new  Exception("Error Processing Delete " . $table);
            }
            return true;
        }
        return false;
	}

    public function update($table, $fields = array(), $where = array()) {
		if ($this->checkParams($table, $fields) && is_array($where)) {
            $oldData = $this->getData(implode(',', array_keys($fields)), $table, $where);
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

    public function readAllData() {
        return $this->db->results();
    }

    public function getAllData($table, $where = array()) {
		if ($this->checkParams($table, $where)) {
            if (!$this->db->getAll($table, $where)) {
                throw new Exception("Error Processing Read All Data " . $table);
            }
            if ($this->db->count()) {
                $this->data = $this->db->result();
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
                $this->data = $this->db->result();
                return $this->data;
            }
        }
        return false;
    }

    public function countData($table) {
		$this->db->query("SELECT COUNT(*) jumlah_record FROM ".$table);
		if ($this->db->count()) {
			$this->data = $this->db->result();
			return $this->data;
		}
		return false;
	}

    public function affected() {
		return $this->db->count();
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
			$this->data = $this->db->result();
			return $this->data;
		}
		return false;
    }

    protected function setOffset($offset) {
        $this->_offset = $offset;
    }

    protected function getOffset() {
        return $this->_offset;
    }

    protected function setHalaman($params) {   
        $param1 = (($params-1) * $this->getOffset()) + 1;
        if ($param1 < 0) {
            $param1 = 0;
        }
        $param2 = $params * $this->getOffset();
        $this->_halaman = array($param1, $param2);
    }

    protected function getHalaman() {
        return $this->_halaman;
    }

    protected function setOrderBy($order_by) {
        $this->_orderBy = $order_by;
    }

    protected function getOrderBy() {
        return $this->_orderBy;
    }

    protected function setAscDsc($asc_dsc) {
        $this->_ascDsc = $asc_dsc;
    }

    protected function getAscDsc() {
        return $this->_ascDsc;
    }
}