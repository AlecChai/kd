<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/1
 * Time: 10:14
 */


class Login extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->library('captcha');

	}

	public function index()
	{
		$this->load->view('login');
	}

	public function login()
	{

		if (!$_POST) return;

		extract($_POST);


		if (!$username || !$password) {
			$arr_error = ['login_error' => '用户与密码不能为空'];
		}

		if($_SESSION['id']) {
			$this->_echo_json(0, '已经登录过', array('url' => 'back/index'));
		}

		$captcha = strtolower($this->session->userdata('code'));

		$form_captcha = strtolower($validateCode);

		if ($captcha != $form_captcha) {
			$this->_echo_json(1, '验证码不正确', []);
		}


		$user = $this->db->limit(1)->where(['username' => $username])->get('t_user')->row();
//		printr($user);

		if ($user && $user->password == pwd($password)) {

			if ($user->status != 1) {
				$this->_echo_json(1, '该账号已被禁用', []);
			}

			$_SESSION['last_time'] = $user->last_time;

			//是否是超级管理员
//			$user->is_admin = $user->user_group_id;

			$up_datas = [
				'last_time' => date('Y-m-d H:i:s',time()),
			];

			$this->db->set($up_datas)->limit(1)->where('id', $user->id)->update('t_user');

			$sedata = array(
				'id'  => $user->id,
				'role_id'  => $user->role_id,
				'username'  => $user->username,
				'realname'     => $user->realname,
			);

			$this->session->set_userdata($sedata);

			/*redirect(base_url('back/index'));exit;*/

			$this->_echo_json(0, '', array('url' => 'back/index'));

		} else {

			$msg = '用户与密码不匹配';
		}

		$this->_echo_json(1, $msg, $arr_error);
	}

	// 校验验证码是否正确
	private function verify_captcha($code)
	{
		if ($this->session->userdata('code') == $code) {
			echo 'well done.';
		}
	}

	// 获取验证码
	public function showCaptcha()
	{
		echo $this->captcha->make();
	}

	public function logout()
	{
//		setcookie("logined", "1", time()-1);
		$_SESSION = array();
		session_destroy();
		redirect('login/index');
	}


	/**
	 * @param int $error
	 * @param string $error_msg
	 * @param array $data
	 */
	protected function _echo_json($error = 0, $error_msg = '', $data = array())
	{
		echo json_encode(array('error' => (int)$error, 'msg' => (string)$error_msg, 'data' => (array)$data));
		exit;
	}
}


