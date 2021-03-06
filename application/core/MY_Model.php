<?php if ( ! defined ( 'BASEPATH' ) ) exit ( 'No direct script access allowed' );

class MY_Model extends CI_Model {

	private $db;
	public $table = NULL;
	public $native = FALSE;
	public $return_id = FALSE;
	public $select = NULL;
	public $select_max = NULL;
	public $select_min = NULL;
	public $select_avg = NULL;
	public $select_sum = NULL;
	public $distinct = FALSE;
	public $set = array();
	public $join = array();
	public $where = array();
	public $or_where = array();
	public $where_in = array();
	public $or_where_in = array();
	public $where_not_in = array();
	public $or_where_not_in = array();
	public $like = array();
	public $or_like = array();
	public $not_like = array();
	public $or_not_like = array();
	public $having = array();
	public $or_having = array();
	public $limit = NULL;
	public $offset = NULL;
	public $group_by = NULL;
	public $order_by = array();
	public $string = NULL;
	public $values = array();
	public $key = NULL;
	public $platform;
	public $version;
	public $conn_id;

	public function __construct() {
		parent::__construct();
		$this->load->config ( 'database', FALSE, TRUE );
		$dbparam = ! config_item ( 'default' ) ? '' : config_item ( 'default' );
		unset ( $this->db );
		$this->db = $this->load->database ( $dbparam, TRUE );
		$this->platform = $this->db->platform();
		$this->version = $this->db->version();
		$this->conn_id = $this->db->conn_id;
	}

	public function __destruct() {
		$this->db->save_queries = FALSE;
	}

	private function initiate_query ( $method ) {
		// Select Query
		if ( in_array ( $method, array ( 'get' ) ) ) {
			if ( ! is_null ( $this->select ) ) {
				$this->db->select ( $this->select );
			}
			elseif ( ! is_null ( $this->select_max ) ) {
				$this->db->select_max ( $this->select_max );
			}
			elseif ( ! is_null ( $this->select_min ) ) {
				$this->db->select_min ( $this->select_min );
			}
			elseif ( ! is_null ( $this->select_avg ) ) {
				$this->db->select_avg ( $this->select_avg );
			}
			elseif ( ! is_null ( $this->select_sum ) ) {
				$this->db->select_sum ( $this->select_sum );
			}
			elseif ( $this->distinct !== FALSE ) {
				$this->db->distinct();
			}
		}

		// From Query
		if ( in_array ( $method, array ( 'get', 'count', 'delete', 'empty_table', 'truncate' ) ) ) {
			$this->db->from ( $this->table );
		}

		// Join Query
		if ( in_array ( $method, array ( 'get' ) ) ) {
			if ( isset ( $this->join[0] ) ) {
				if ( is_array ( $this->join[0] ) ) {
					foreach ( $this->join as $j ) {
						$this->db->join ( $j[0], $j[1], ( isset ( $j[2] ) ? $j[2] : NULL ) );
					}
				} else {
					$this->db->join ( $this->join[0], $this->join[1], ( isset ( $this->join[2] ) ? $this->join[2] : NULL ) );
				}
			}
		}

		// Set Query
		if ( in_array ( $method, array ( 'insert', 'update' ) ) ) {
			if ( ! is_null ( $this->set ) AND coutn ( $this->set ) > 0 ) {
				$this->db->set ( $this->set );
			}
		}

		// Where Query
		if ( in_array ( $method, array ( 'get', 'count', 'update', 'delete' ) ) ) {
			if ( ! is_null ( $this->where ) AND count ( $this->where ) > 0 ) {
				$this->db->where ( $this->where );
			}

			if ( ! is_null ( $this->or_where ) AND count ( $this->or_where ) > 0 ) {
				$this->db->or_where ( $this->or_where );
			}

			if ( ! is_null ( $this->where_in ) AND count ( $this->where_in ) > 0 ) {
				$this->db->where_in ( $this->where_in );
			}

			if ( ! is_null ( $this->or_where_in ) AND count ( $this->or_where_in ) > 0 ) {
				$this->db->or_where_in ( $this->or_where_in );
			}

			if ( ! is_null ( $this->where_not_in ) AND count ( $this->where_not_in ) > 0 ) {
				$this->db->where_not_in ( $this->where_not_in );
			}

			if ( ! is_null ( $this->or_where_not_in ) AND count ( $this->or_where_not_in ) > 0 ) {
				$this->db->or_where_not_in ( $this->or_where_not_in );
			}

			if ( ! is_null ( $this->like ) AND count ( $this->like ) > 0 ) {
				$this->db->like ( $this->like );
			}

			if ( ! is_null ( $this->or_like ) AND count ( $this->or_like ) > 0 ) {
				$this->db->or_like ( $this->or_like );
			}

			if ( ! is_null ( $this->not_like ) AND count ( $this->not_like ) > 0 ) {
				$this->db->not_like ( $this->not_like );
			}

			if ( ! is_null ( $this->or_not_like ) AND count ( $this->or_not_like ) > 0 ) {
				$this->db->or_not_like ( $this->or_not_like );
			}
		}

		// Having Query
		if ( in_array ( $method, array ( 'get', 'count' ) ) ) {
			if ( ! is_null ( $this->having ) ) {
				$this->db->having ( $this->having );
			}

			if ( ! is_null ( $this->or_having ) ) {
				$this->db->or_having ( $this->or_having );
			}
		}

		// Group By Query
		if ( in_array ( $method, array ( 'get', 'count' ) ) ) {
			if ( ! is_null ( $this->group_by ) ) {
				$this->db->group_by ( $this->group_by );
			}
		}

		// Order By Query
		if ( in_array ( $method, array ( 'get', 'count' ) ) ) {
			if ( ! is_null ( $this->group_by ) ) {
				$this->db->group_by ( $this->group_by );
			}
		}

		// Limit Query
		if ( in_array ( $method, array ( 'get', 'count' ) ) ) {
			if ( ! is_null ( $this->limit ) ) {
				$this->db->limit ( $this->limit, $this->offset );
			}
		}
	}

	public function set_query ( $query = array() ) {
		foreach ( $query as $var => $value ) {
			$this->$var = $value;
		}
	}

	public function last_query() {
		return $this->db->last_query();
	}

	public function get() {
		$this->initiate_query ( __FUNCTION__ );
		return $this->db->get();
	}

	public function count() {
		$this->initiate_query ( __FUNCTION__ );
		return $this->db->count_all_result();
	}

	public function count_all() {
		return $this->db->count_all ( $this->table );
	}

	public function insert() {
		$return = ! $this->return_id ? 'affected_rows' : 'insert_id';
		$this->db->insert ( $this->table, $this->values );
		return $this->db->$return();
	}

	public function insert_batch() {
		if ( FALSE !== $this->native ) {
			$insert_id = array();
			foreach ( $this->values as $val ) {
				$return = ! $this->return_id ? 'affected_rows' : 'insert_id';
				$this->db->insert ( $this->table, $val );
				$insert_id[] = $this->db->$return();
			}
			return $insert_id;
		}
		$this->db->trans_start();
		$this->db->insert_batch ( $this->table, $this->values );
		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	public function insert_string() {
		return $this->db->insert_string ( $this->table, $this->values );
	}

	public function update() {
		$this->db->update ( $this->table, $this->value );
		return $this->db->affected_rows();
	}

	public function update_batch() {
		$this->db->trans_start();
		$this->db->update_batch ( $this->table, $this->values, $this->key );
		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	public function update_string() {
		return $this->db->update_string ( $this->table, $this->values, $this->where );
	}

	public function delete() {
		return $this->db->delete ( $this->table );
	}

	public function empty_table() {
		return $this->db->empty_table ( $this->table );
	}

	public function truncate() {
		return $this->db->truncate();
	}

	public function cache ( $trigger ) {
		$cache = $trigger.'_cache';
		$this->db->$cache();
	}

	public function flush() {
		$this->db->flush_cache();
	}

	public function query() {
		if ( ! is_null ( $this->string ) ) {
			return $this->db->query ( $this->string );
		}
	}

	public function simple_query() {
		if ( ! is_null ( $this->string ) ) {
			return $this->db->simple_query ( $this->string );
		}
	}

	public function transaction ( $state, $param = FALSE ) {
		$method = 'trans_'.$state;
		return $this->db->$method($param);
	}

	public function call() {
		return call_user_func_array ( array ( &$this->db, 'call_function' ), func_get_args() );
	}

	public function dbcache ( $state, $param = array() ) {
		$method = 'cache_'.$state;
		if ( count ( $param ) > 0 ) {
			return call_user_func_array ( array ( &$this->db, $method ), $param );
		}
		return $this->db->$method();
	}
}

class Model extends MY_Model {
	public function __construct() {
		parent::__construct();
	}
}