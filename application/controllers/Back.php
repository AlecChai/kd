<?php


	class Back extends MY_Controller
	{
		
		public function __construct()
		{
			parent::__construct();
			
		}
		
		public function index()
		{
			$this->load->view("home");
		}
		
		public function info()
		{
			
			$user = $this->db->limit(1)->where(['id' => $_SESSION['id']])->get('t_user')->row_array();
			
			$this->load->view("info", $user);
			
		}
		
		public function get_init() {
			
            //站内消息
            
           re_json(array(
                'msgs' => $msgs,
                'msgs_count' => 0,
                'notice' => $notice,
                'username' => $this->session->username,
//                'type' => $this->session->type,
            ));
		}
	}
