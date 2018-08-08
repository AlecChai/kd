<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8"/>
    <title>CD自动化刊登系统</title>
    <meta name="description" content="overview &amp; stats"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>

    <!-- css -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/reset.css');?>">
    <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/element-ui1413.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/login.css">
	
	
	<style type="text/css">
	
	
	</style>

</head>
<body>
<div id="app">

    <div class="body">
        <div class="content">
        
            <div class="login">
                <div class="title">
                    <h1>用户登录</h1>
                </div>
                <div class="login-form">
                    <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="100px" label-position="top"
                             class="demo-ruleForm">
						
                        <el-form-item label="登录名" prop="username">
                            <el-input size="large" v-model="ruleForm.username" placeholder="用户名"></el-input>
                        </el-form-item>


                        <el-form-item label="登录密码" prop="password">
                            <el-input size="large" type="password" v-model="ruleForm.password" placeholder="登录密码"></el-input>
                        </el-form-item>
                   
                        <el-form-item label="验证码" prop="validateCode" class="validate">
                            <el-input size="large" v-model="ruleForm.validateCode" placeholder="验证码"></el-input>
                                <img id="id_captcha" src="<?php echo base_url('login/showCaptcha');?>" onclick="changeCaptcha(this)" />
                        </el-form-item>

                        <div class="error">
                            <p v-if="error!=''">{{error}}</p>
                        </div>

                        <el-button size="large" type="primary" class="login-btn" @click="submitForm('ruleForm',ruleForm)">登录
                        </el-button>

                    
                    </el-form>
                </div>
            </div>
        </div>
    </div>
	
<!--    <div class="footer">-->
<!--        <p>Copyright &copy 2017 CD自动化刊登系统 粤ICP证16119776号</p>-->
<!--    </div>-->

</div>

<?php $this->load->view('common/common_js');?>

<!-- main.js -->
<script>
    var Main = {
        data: function () {
            return {
                error: '',
                ruleForm: {
                    username: '',
                    password: '',
                    validateCode: '',
                },
                rememberme:true,
                rules: {
                    username: [
                        {required: true, message: '请输入登录名', trigger: 'blur'},
                    ],
                    password: [
                        {required: true, message: '请输入登录密码', trigger: 'blur'},
                    ],
                    validateCode: [
                        {required: true, message: '请输入验证码', trigger: 'blur'},
                    ],
                }
            };
        },
        methods: {
            //操作状态提醒
            success: function (msg) {
                this.$notify({
                    title: '成功',
                    message: msg,
                    type: 'success'
                });
            },
            errors: function (msg) {
                this.$notify.error({
                    title: '失败',
                    message: msg,
                });
            },
	
			submitForm: function (formName, data) {
		
				var that = this;
				this.$refs[formName].validate(function (valid) {
					if (valid) {
					
						Vue.http.post('/login/login', data, {emulateJSON: true}).then(function (response) {
							console.log(response);
							if (response.body.error == 0) {
								//that.success('登陆成功');
								location.href = '/back/index';
								/*setTimeout(function () {
                                    location.href = '/back/index';
                                }, 100);*/
							} else {
								
								//that.errors(response.body.msg);
								that.$data.error = response.body.msg;
							}
						}, function (response) {
							//that.errors(response.body.msg);
							that.$data.error = response.body.msg;
						});
					} else {
						//that.errors('表单填写有误');
						that.$data.error = '表单填写有误';
						return false;
					}
				});
			},
			enter_keyup: function() {
				var that = this;
				document.addEventListener('keyup', function(e) {
					if(parseInt(e.keyCode) == 13) {
						that.submitForm('ruleForm',that.ruleForm);
					}
				}, false)
			},
        },
		
        created: function () {
            this.enter_keyup();
        }
    }
    
    var Ctor = Vue.extend(Main)
    new Ctor().$mount('#app')

    // 点击更换验证码
    function changeCaptcha(that) {

        var src_url = "<?php echo base_url('login/showCaptcha?');?>" + Math.random();
        document.getElementById('id_captcha').src = src_url;
    }

</script>
</body>
</html>
