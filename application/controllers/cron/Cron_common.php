<?php

/**
 * Created by PhpStorm.
 * User: yangjiafei
 * Date: 2017/12/7
 * Time: 16:46
 * 公共定时任务，内容比较杂
 */
class Cron_common extends CI_Controller
{
	
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * 翻译
	 * php index.php cron cron_common translate
	 */
	public function translate($id=0)
	{
		$this->load->library('Baidu_translate', NULL, 'baidut');
		
		if($id) {
			$row = $this->db->query("select id,title1,title from cd_product where id=$id")->row_array();
			$ret = $this->baidut->translate($row['title1']."\n".$row['title'], 0, 'zh');
			print_r($ret);
			echo $this->db->last_query(), "\n";
			return;
		}
		
		$ret = $this->db->query("select id,title,title1 from cd_product where title_zh=''");
		
		while ($row = $ret->unbuffered_row('array')) {
			$data = [];
			
			$tran1 = $this->baidut->translate($row['title1']."\n".$row['title'], 0, 'zh');
			
			if($tran1['error_code']) {
				continue;
			}
			
			$data['title1_zh'] = $tran1['trans_result'][0]['dst'];
			$data['title_zh'] = $tran1['trans_result'][1]['dst'];
			
			
			$this->db->where(array('id'=>$row['id']))->set($data)->update('cd_product');
			echo $this->db->last_query(), "\n";
		}
		
		cron_logs("Cron_common translate \t". $this->db->last_query(),"\t", '0common.log');
	}
	
	
}
