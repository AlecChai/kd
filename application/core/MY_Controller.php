<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/1
 * Time: 10:10
 */

class MY_Controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		
		if (isset($_GET['bend'])) $this->output->enable_profiler(true);
		
		if (!$_SESSION['id']) {
			
			if (in_array($this->router->class, ['back'])) {
				redirect('/login/index');
				exit("");
			}
			
			re_json(['login_status' => false], 1, '未登录');
			exit("");
			
		}
		
	}
	
	protected function getJoinData($table = "", $fields = "*", $join = '', $where = "", $order = '', $first_row = 0, $pagesize = 0)
	{
		$table = $table == '' ? $this->table : $table;
		
		if (is_array($join)) {
			foreach ($join as $key => $value) {
				if ($value[2]) {
					$this->db->join($value[0], $value[1], $value[2]);
				} else {
					$this->db->join($value[0], $value[1]);
				}
			}
		}
		
		if ($where) {
			$this->setWhere($where);
		}
		
		if ($order) {
			$this->db->order_by($order);
		}
		
		if ($pagesize > 0) {
			$this->db->limit($pagesize, $first_row);
		}
		
		$data = $this->db->select($fields)->get($table)->result_array();
		
		return $data;
	}
	
	protected function setWhere($whs)
	{
		if (!is_array($whs)) {
			$this->db->where($whs);
			return;
		}
		
		foreach ($whs as $key => $where) {
			if ($key == 'findinset') {
				$this->db->where("1", "1 AND FIND_IN_SET($where)", FALSE);
				continue;
			}
			if (is_array($where)) {
				if ($key == 'or') {
					$this->db->or_where($where[0], $where[1]);
				} elseif ($key == 'like') {
					foreach ($where as $k => $v) {
						$this->db->like($k, $v, 'both');
					}
				} elseif ($key == 'not in') {
					$this->db->where_not_in($where[0], $where[1]);
				} elseif($key == 'in') {
					$this->db->where_in($where[0], $where[1]);
				}
			} elseif ($key == 'str') {
				$this->db->where($where);
			} else {
				$this->db->where($key, $where);
			}
		}
	}
	
	
	protected function _echo_json($error = 0, $error_msg = '', $data = array())
	{
		echo json_encode(array('error' => (int)$error, 'msg' => (string)$error_msg, 'data' => (array)$data));
		exit;
	}
	
	protected function categorys($id = 0)
	{
		$ret = $this->db->select("id,name_cn")->get("t_category")->result_array();
		$ret2 = array_column($ret, 'name_cn', 'id');
		
		if ($id) {
			return $ret2[$id];
		}
		
		return $ret2;
	}
	
	protected function users($id = 0)
	{
		$ret = $this->db->select("id,realname")->get("t_user")->result_array();
		$ret2 = array_column($ret, 'realname', 'id');
		
		if ($id) {
			return $ret2[$id];
		}
		
		return $ret2;
	}
	
	protected function stores($id = 0)
	{
		$ret = $this->db->select("id,store_code")->get("t_store")->result_array();
		$ret2 = array_column($ret, 'store_code', 'id');
		
		if ($id) {
			return $ret2[$id];
		}
		
		return $ret2;
	}
	
	protected function brands($id = 0)
	{
		$ret = $this->db->select("id,brand_name	")->get("t_brand")->result_array();
		$ret2 = array_column($ret, 'brand_name	', 'id');
		
		if ($id) {
			return $ret2[$id];
		}
		
		return $ret2;
	}
	
	
}
