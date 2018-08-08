/**
 * Created by Administrator on 2017/3/8.
 */
var Main = {
	data: function () {
		return {
			spanLeft: 5,
			isDisabled: true,
			opened: ['1'],
			isrc: 'back/info',
			
			show_dialog: false,
			msgs: [],
			msgs_count: 0,
			notice: {},
			loginout_url: '',
			username: '',
			init_flag: true,
			editableTabsValue2: '/back/home.html',
			editableTabs2: [],
			
		}
	},
	methods: {
		toggleClick: function () {
			if (this.spanLeft === 5) {
				this.spanLeft = 1;
				this.isDisabled = false;
			} else {
				this.spanLeft = 5;
				this.isDisabled = true;
			}
		},
		changeUrl: function (res) {
			
			if (res.index.indexOf(".html") > 0) {
				this.isrc = '/' + res.index + '?auto=' + (new Date()).getTime();
			} else {
				this.isrc = res.index + '?auto=' + (new Date()).getTime();
			}
			
			this.addTab(res)
		},

		//通知
		notice_click: function(id) {
			
			this.show_dialog = true;
			var that = this;
			Vue.http.post('/message/read_msg', {id:id}, {emulateJSON:true}).then(function () {
				that.get_init({params:{id:id}});
			});
		},
		notice_close: function() {
			
			this.show_dialog = false;
		},
		get_init: function(option) {
			this.$http.get('/back/get_init', option).then(function (response) {
				
				if(response.body.error == 0) {
					this.msgs = response.body.data.msgs;
					this.msgs_count = response.body.data.msgs_count;
					this.notice = response.body.data.notice;
					this.username = response.body.data.username;
					
					this.init_flag = false; //只运行一次
					
				}
				
			}, function (response) {
				console.log(response)
			});
		},
		addTab: function(targetName) {
			var that = this
			let src = targetName.index.search('/proManage') >= 0 ? '/sck-shiyong/' + targetName.index : '/' + targetName.index;
			
			for (var a = 0; a < this.editableTabs2.length; a++) {
				if (this.editableTabs2[a].src === src) {
					that.removeTab(src, false)
					setTimeout(function () {
						that.editableTabs2.push({
							title: targetName.$el.innerText,
							name: src,
							src: src
						});
						that.editableTabsValue2 = src;
						that.isrc = that.editableTabsValue2
					})
					return
				}
			}
			this.editableTabs2.push({
				title: targetName.$el.innerText,
				name: src,
				src: src
			});
			this.editableTabsValue2 = src;
		},
		removeTab: function(targetName, isLimitOnlyOne) {
			if (isLimitOnlyOne) {
				if (this.editableTabs2.length === 1) {
					return
				}
			}
			let tabs = this.editableTabs2;
			let activeName = this.editableTabsValue2;
			if (activeName === targetName) {
				tabs.forEach(function (tab, index)  {
					if (tab.name === targetName) {
						let nextTab = tabs[index + 1] || tabs[index - 1];
						if (nextTab) {
							activeName = nextTab.name;
						}
					}
				});
			}
			
			this.editableTabsValue2 = activeName;
			this.editableTabs2 = tabs.filter(function(tab) { return tab.name !== targetName } );
			this.isrc = this.editableTabsValue2;
		},
		clickTab: function () {
			this.isrc = this.editableTabsValue2
			console.log(this.editableTabsValue2)
		}
	},
	
	// watch: {
	//     msgs: function(oldValue,newValue) {
	//         //有消息就打开列表
	//         if(oldValue.length != newValue.length && this.msgs.length > 0) {
	//             $('.bell-box').trigger('click');
	//             console.log('有消息就打开列表')
	//         }
	//     }
	// },
	
	created: function () {
		//判断是否为ie浏览器
		if (!!window.ActiveXObject || "ActiveXObject" in window) {
			alert('为了更好的使用本系统，强烈建议使用谷歌、火狐浏览器');
		}

		this.get_init({});
		var that = this;
		
		// setInterval(function() {
		//     that.get_init({});
		// }, 3000000);
	},
	
	mounted: function(){
		
		$('#homepage').trigger('click');
		
	},
	
};

var Ctor = Vue.extend(Main);
new Ctor().$mount('#app');
