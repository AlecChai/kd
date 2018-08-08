<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>info</title>
	<meta name="Keywords" content="" />
	<meta name="Description" content="" />
	<link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css"/>

</head>
<body>


<style type="text/css">
	/* table */
	img{border:none; }
	ol,ul,li{list-style:none outside none;}
	#stable{border-collapse:collapse;border-spacing:0}
	caption,td,th{text-align:left;vertical-align:middle}
	#stable{width:96%;margin:20px 20px;}
	#stable th{background:#eee;width:90%;}
	#stable td{padding:7px 15px;border:1px solid #ddd;text-align:left;}
	td.td{width:20%;color:#333;}
</style>

<?php
$ip = getip();
?>


	
	<table class="table table-bordered table-striped table-hover" id="stable">
		
		<tbody>
		
		<tr>
			<th colspan="4">基本信息</th>
		</tr>
		<tr><td class="td">Name</td>	<td class="td1" colspan=3><?=$username?></td>	</tr>
		<tr><td class="td">真实姓名</td>	<td class="td1" colspan=3><?=$realname?></td>	</tr>
		<tr><td class="td">Email</td>	<td class="td1" colspan=3><?=$email?></td>	</tr>
		<tr><td class="td">添加时间</td>	<td class="td1" colspan=3><?php echo $create_time; ?></td>	</tr>
	
		
		<tr>
			<td class="td">上次登录时间</td>
			<td class="td1" colspan=3><?php echo $_SESSION['last_time']; ?></td>
		</tr>
		
		<tr>
			<td class="td">登录IP</td>
			<td class="td1" colspan=3>
				<a target="_blank" href="http://www.ip138.com/ips138.asp?ip=<?php echo $ip; ?>"><?php echo $ip; ?></a>
			</td>
		</tr>
		
		<tr>
			<th colspan="4">系统信息</th>
		</tr>
		
		<tr>
			<td class="td">系统时间</td>
			<td class="td1" colspan=3><?php echo date('Y-m-d H:i',time()); ?></td>
		</tr>
		
		<tr>
			<td class="td">内存消耗</td>
			<td class="td1" colspan=3>
				<?php
				echo bsize(memory_get_usage());
				?>
			</td>
		</tr>
		
		<tr>
			<td class="td">客户端CPU</td>
			<td class="td1" colspan=3>
				<span id="cpu"></span>
			</td>
		</tr>
		
		<tr>
			<td class="td">客户端操作系统</td>
			<td class="td1" colspan=3>
				<?=getOs()?>
			</td>
		</tr>
		
		<tr>
			<td class="td">客户端浏览器</td>
			<td class="td1" colspan=3>
				<?=$_SERVER['HTTP_USER_AGENT']?>
			</td>
		</tr>
		
		
		</tbody>
	</table>


<script src="/assets/js/jquery-2.1.4.min.js"></script>

<script>
	
	function getCpu() {
		return navigator["cpuClass"] || (navigator["hardwareConcurrency"]+"核");
	}
	
	$("#cpu").html(getCpu());



</script>


</body>
</html>

