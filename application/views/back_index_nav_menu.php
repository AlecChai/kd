<div class="nav-list" :class="{'layout-hide-text': spanLeft < 5}">
    <div class="layout-header">
        <i class="fa fa-navicon" @click.prevent="toggleClick" :class="{'icon-rotate': spanLeft < 5}"></i>
    </div>
    <el-menu :default-active="isrc.replace('/assets/components/', '').replace(/\?auto=([0-9]+)/, '')"
             :default-openeds="opened"
             class="el-menu-vertical-demo"
             theme="dark"
             :unique-opened="true">
		
        <el-tooltip id="homepage" class="item home" effect="dark" content="首页" placement="right-start" :disabled="isDisabled">
            <el-menu-item index="back/info" @click="changeUrl">
                <i class="fa fa-home"></i>
                <span class="layout-text-block">首页</span>
            </el-menu-item>
        </el-tooltip>
		
        <el-tooltip class="item" effect="dark" content="CDiscount" placement="right-start" :disabled="isDisabled">
            <el-submenu index="1">
                
                <template slot="title"><span class="layout-text">CDiscount</span></template>
    
                <el-tooltip class="item" effect="light" content="类别管理" placement="right"
                            :disabled="isDisabled">
                    <el-menu-item index="v/category.html" @click="changeUrl">
                        <i class="fa fa-pencil"></i>
                        <span class="layout-text-block">类别管理</span>
                    </el-menu-item>
                </el-tooltip>
                <el-tooltip class="item" effect="light" content="EAN管理" placement="right"
                            :disabled="isDisabled">
                    <el-menu-item index="v/ean.html" @click="changeUrl">
                        <i class="fa fa-pencil"></i>
                        <span class="layout-text-block">EAN管理</span>
                    </el-menu-item>
                </el-tooltip>
                <el-tooltip class="item" effect="light" content="店铺管理" placement="right"
                            :disabled="isDisabled">
                    <el-menu-item index="v/store.html" @click="changeUrl">
                        <i class="fa fa-pencil"></i>
                        <span class="layout-text-block">店铺管理</span>
                    </el-menu-item>
                </el-tooltip>
                <el-tooltip class="item" effect="light" content="设置采集" placement="right"
                            :disabled="isDisabled">
                    <el-menu-item index="v/collection_set.html" @click="changeUrl">
                        <i class="fa fa-pencil"></i>
                        <span class="layout-text-block">设置采集</span>
                    </el-menu-item>
                </el-tooltip>
                <el-tooltip class="item" effect="light" content="采集列表" placement="right"
                            :disabled="isDisabled">
                    <el-menu-item index="v/collection.html" @click="changeUrl">
                        <i class="fa fa-pencil"></i>
                        <span class="layout-text-block">采集列表</span>
                    </el-menu-item>
                </el-tooltip>
                <el-tooltip class="item" effect="light" content="刊登列表" placement="right"
                            :disabled="isDisabled">
                    <el-menu-item index="v/productkd.html" @click="changeUrl">
                        <i class="fa fa-pencil"></i>
                        <span class="layout-text-block">刊登列表</span>
                    </el-menu-item>
                </el-tooltip>
                <el-tooltip class="item" effect="light" content="跟卖列表" placement="right"
                            :disabled="isDisabled">
                    <el-menu-item index="v/gm.html" @click="changeUrl">
                        <i class="fa fa-pencil"></i>
                        <span class="layout-text-block">跟卖列表</span>
                    </el-menu-item>
                </el-tooltip>
                
			
	
				
				
				
                
               
                
              
    
               
               
      
            </el-submenu>
        </el-tooltip>
        <el-tooltip class="item" effect="dark" content="Walmart" placement="right-start" :disabled="isDisabled">
            <el-submenu index="5">
                <template slot="title"><span class="layout-text" id="walmart_manage">Walmart</span></template>
            
                <el-tooltip class="item" effect="light" content="用户管理" placement="right"
                            :disabled="isDisabled">
                    <el-menu-item index="v/walmart/set.html" @click="changeUrl">
                        <i class="fa fa-pencil"></i>
                        <span class="layout-text-block">设置链接</span>
                    </el-menu-item>
                </el-tooltip>
            
                <el-tooltip class="item" effect="light" content="用户管理" placement="right"
                            :disabled="isDisabled">
                    <el-menu-item index="v/walmart/goods.html" @click="changeUrl">
                        <i class="fa fa-pencil"></i>
                        <span class="layout-text-block">产品管理</span>
                    </el-menu-item>
                </el-tooltip>
    
     
        

        

            </el-submenu>
        </el-tooltip>
    
        <el-tooltip class="item" effect="dark" content="系统设置" placement="right-start" :disabled="isDisabled">
            <el-submenu index="4">
                <template slot="title"><span class="layout-text" id="sys_manage">系统设置</span></template>
            
                <?php if(in_array($_SESSION['id'],[1])) { ?>
                
                    <el-tooltip class="item" effect="light" content="用户管理" placement="right"
                                :disabled="isDisabled">
                        <el-menu-item index="v/users.html" @click="changeUrl">
                            <i class="fa fa-pencil"></i>
                            <span class="layout-text-block">用户管理</span>
                        </el-menu-item>
                    </el-tooltip>
                
                    <!--                <el-tooltip class="item" effect="light" content="角色管理" placement="right"-->
                    <!--                            :disabled="isDisabled">-->
                    <!--                    <el-menu-item index="role/seller_role.html" @click="changeUrl">-->
                    <!--                        <i class="fa fa-pencil"></i>-->
                    <!--                        <span class="layout-text-block">角色管理</span>-->
                    <!--                    </el-menu-item>-->
                    <!--                </el-tooltip>-->
            
                <?php } ?>
            
                <el-tooltip class="item" id="change_password" effect="light" content="修改密码" placement="right"
                            :disabled="isDisabled">
                    <el-menu-item index="v/password.html" @click="changeUrl">
                        <i class="fa fa-pencil"></i>
                        <span class="layout-text-block">修改密码</span>
                    </el-menu-item>
                </el-tooltip>
            </el-submenu>
        </el-tooltip>
    </el-menu>
</div>
