<?php

/** 判断一个字符串是否是手机号 */
function str_is_mobile($str)
{
    return 0 !== preg_match('/^(?:\+?86)?[1][0-9]\d{9}$/', $str);
}

/** 判断一个字符串是否是手机号 */
function str_is_email($str)
{
    // 从 ci/system/helpers/email_helper.php 中提取
    return 0 !== preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str);
}

/** 判断一个字符串是否为 QQ */
function str_is_qq($str)
{
    return 0 !== preg_match('/^[1][0-9]\d{5,8}$/', $str);
}

function pwd($str)
{
	return md5(sha1($str));
}

/**
 * 判断一个字符串是否是空字符串
 *
 * @param mixed $input
 * @return bool 指示该字符串是否是空字符串
 */
function empty_str($input)
{
    return empty($input) | 0 == strcasecmp('null', $input);
}

function post_param($key = null, $defaultValue = false)
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    } else {
        foreach ($_POST as $k => $v) {
            if (0 === strcasecmp($k, $key)) {
                return $v;
            }

        }
    }
    return $defaultValue;
}

function re_json($data=[], $code=0, $msg='' )
{
	echo json_encode(array('error' => (int)$code, 'msg' => (string)$msg, 'data' => (array)$data));
}

function getip()
{
	
	if ($_SERVER['REMOTE_ADDR']) $ip = $_SERVER['REMOTE_ADDR'];
	else if (getenv('HTTP_CLIENT_IP')) $ip = getenv('HTTP_CLIENT_IP');
	else if (getenv('HTTP_X_FORWARDED_FOR')) $ip = getenv('HTTP_X_FORWARDED_FOR');
	return $ip;
}

function db_size()
{
	global $db;
	$result = $db->query('show table status');
	$dbsize = 0;
	while ($row = $db->fetch_assoc($result))
	{
		$dbsize += $row['Data_length'] + $row['Index_length'];
	}
	return bsize($dbsize);
}

function bsize($str)
{
	foreach (array('', 'K', 'M', 'G') as $i => $k)
	{
		if ($str < 1024) break;
		$str /= 1024;
	}
	return sprintf("%5.2f %sB", $str, $k);
}

function getOs()
{
	if (!empty($_SERVER['HTTP_USER_AGENT'])) {
		$OS = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match('/win/i', $OS)) {
			$OS = 'Windows';
		} elseif (preg_match('/mac/i', $OS)) {
			$OS = 'MAC';
		} elseif (preg_match('/linux/i', $OS)) {
			$OS = 'Linux';
		} elseif (preg_match('/unix/i', $OS)) {
			$OS = 'Unix';
		} elseif (preg_match('/bsd/i', $OS)) {
			$OS = 'BSD';
		} else {
			$OS = 'Other';
		}
		return $OS;
	} else {
		return "获取访客操作系统信息失败！";
	}
}


function get_current_url()
{
	return 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . (($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443) ? '' : ':' . $_SERVER['SERVER_PORT']) . $_SERVER['REQUEST_URI'];
}

function msg($msg = '未授权', $url = -1, $type = 0)
{
	if (!$url || $url == -1) {
		$url = "javascript:history.back(-1);";
	}
	
	$style = 'background:#F2DEDF;border:1px solid #e1bcc2;color:#a40';
	if ($type == 1) {
		$style = 'background:#DEF0D8;border:1px solid #bcd1aa;color:#3C763C';
	}
	
	print <<<EOT
<!DOCTYPE html>
<html>
<head>
<title>跳转提示</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
*{margin:0;padding:0;border:none;}
body{background:#fff;font-family:"Microsoft YaHei",Tahoma,Helvetica;font-size:14px;color:#333;}
body a{text-decoration:none;color:#000;}
body a:hover{text-decoration:underline;}
#msgbox{width:50%;max-width:800px;margin:7% auto;$style;border-radius:3px;padding:20px 30px;overflow-x:hidden;}
#msgcenter{display:block;margin-top:10px;font-size:14px;}
.msg{line-height:1.7;padding:5px 0;}
</style>
</head>
<body>
<div id="msgbox">
	<div class="msg">$msg</div>
	<center id="msgcenter"><a href="$url">返回上一页</a></center>
</div>

</body></html>
EOT;
	die();
}

function printr($str)
{
    if (is_array($str) || is_object($str)) {
        echo '<pre>';
        print_r($str);
        echo '</pre>';
        return;
    }
    echo '<pre>';
    echo $str;
    echo '</pre>';
}

// PHP二维数组根据某个元素去重
function array_unset_tt($arr, $key)
{
    if (!$arr) {
      return;
    }
    $res = array();
    foreach ($arr as $value) {
        if (isset($res[$value[$key]])) {
            unset($value[$key]);
        } else {
            $res[$value[$key]] = $value;
        }
    }
    return array_values($res);
}

function array_unique_fb($array2D)
{
    if ($array2D) {
       return;
    }
    foreach ($array2D as $v) {
        $v      = join(',', $v);
        $temp[] = $v;
    }
    $temp = array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
    foreach ($temp as $k => $v) {
        $temp[$k] = explode(',', $v); //再将拆开的数组重新组装
    }
    return $temp;
}

/**
ids转成对应的values
 */
function ids2names($ids, $arr, $sep=", ") {
	if(!$arr) return;
	$ida = explode(',', $ids);
	foreach($ida as $v) {
		$ret[] = $arr[$v];
	}
	return implode($sep, $ret);
}



//设置2维数组的下标
function set_array2_key ($array, $key)
{
	$arr = [];
	foreach($array as $k => $v) {
		$arr[$v[$key]] = $v;
	}
	return $arr;
}

function youdao_translate($content=''){
	$q = $content;
	$from = 'auto';
	$to = 'zh-CHS';
	$appKey = '4fd6e9245f5c5840';
	$salt = rand(100000,900000);
	$key = 'dx4TIevsBqAsp0aRoX7UFNV9ukGcjulJ';
	$sign = $appKey.$q.$salt.$key;
	$sign = md5($sign);
	$content = urlencode($content);
	$url = 'http://openapi.youdao.com/api?q='.$content.'&from='.$from.'&to='.$to.'&appKey='.$appKey.'&salt='.$salt.'&sign='.$sign;
	$data = get_http($url);
	$data = json_decode($data,'array');
	
	if($data['errorCode']) {
		return $data['errorCode'];
	}
	
	return $data['translation'][0];
}
/**
 * http请求
 */
function get_http($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$r = curl_exec($ch);
	curl_close($ch);
	return $r;
}

function cron_logs($txt, $tsep="\t")
{
	$logfile = FCPATH."0cron.log";
	
	if(is_file($logfile) && filesize($logfile)>1024*50) {
		unlink($logfile);
	}
	
	$time = date('Y-m-d H:i:s',time()).$tsep;
	file_put_contents($logfile, $time.$txt. "\n", FILE_APPEND);
	
}

function rand1()
{
	list($u, $s) = explode(' ', microtime());
	return $s . substr($u, 2, 6) . mt_rand(10000, 99999);
}

function rand2()
{
	list($u, $s) = explode(' ', microtime());
	return date("YmdHis") . substr($u, 2, 6) . mt_rand(10000, 99999);
}



