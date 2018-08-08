<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8"/>
    <title>CD自动化刊登</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>

    <?php $this->load->view('common/common_css');?>
	
	<style type="text/css">
	
	
	</style>
</head>

<body>
<div id="app">
    <div class="header">
     
        <div class="title">
            <h1>多平台自动化刊登</h1>
        </div>
	
		<div id="notice" class="user">
		
			<div class="welcome">
				<el-dropdown>
                    <span class="el-dropdown-link">
                        欢迎您，{{username}}
                        <i class="fa fa-caret-down"></i>
                    </span>
					<el-dropdown-menu slot="dropdown" id="user">
						<li class="navli"><a id="change_pass_btn" href="/v/password.html">修改密码</a></li>
						<li class="navli"><a href="/login/logout">退出登录</a></li>
					</el-dropdown-menu>
				</el-dropdown>
		
		
			</div>
		</div>

	</div>
	
    <div class="nav">
		
		
        <?php $this->load->view('back_index_nav_menu');?>
	
	
		<el-tabs v-model="editableTabsValue2" type="border-card"  closable @tab-remove="removeTab" @tab-click="clickTab" style="width:100%">
			<el-tab-pane v-for="(item, index) in editableTabs2" :key="item.name" :label="item.title" :name="item.name">
				<iframe id="my-iframe" class="my-iframe" :src="item.src"></iframe>
			</el-tab-pane>
		</el-tabs>




	</div>
	
	
	
</div>

<?php $this->load->view('common/common_js');?>

<script src="<?php echo base_url('assets/js/mian.js?t='.(date('Ymd',time())));?>"></script>

<script>
	
    $(".el-submenu__title i").removeClass('el-icon-arrow-down').addClass("fa fa-caret-right");
    
    //点击修改密码按钮，打开左边的菜单栏
    $('#change_pass_btn').click(function(e) {
        e.preventDefault();
        $('#sys_manage').trigger('click');
        $('#change_password').trigger('click');
    });
   
    
</script>

</body>
</html>
