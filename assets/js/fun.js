
function setQuery(q, name, v) {
	var reg = new RegExp("(^|&)" + name + "=[^&]*(&|$)");
	if (v === null) {
		q = q.replace(reg, "&");
	} else if (q.length && reg.test(q)) {
		q = q.replace(reg, "$1" + name + "=" + v + "$2");
	} else {
		q += q.length ? "&": "";
		q += encodeURIComponent(name) + "=" + encodeURIComponent(v);
	}
	q = q.replace("&&", "&");
	if (q.slice( - 1) == "&") q = q.slice(0, -1);
	if (q.slice(0, 1) == "&") q = q.slice(1);
	return q;
}

function getRequestParam(name) {
	var url = location.search; //获取url中"?"符后的字串
	if (url.indexOf("?") != -1) {    //判断是否有参数
		var str = url.substr(1); //从第一个字符开始 因为第0个是?号 获取所有除问号的所有符串
		strs1 = str.split("&");
		var param = {};
		for (var i = 0; i < strs1.length; i++) {
			var keyValue = strs1[i].split("=");
			param[keyValue[0]] = keyValue[1];
		}
		return param[name];
	} else {
		return '';
	}
}

function getHash(name) {
	var url = location.hash; 		//获取url中"#"符后的字串
	if (url) {
		var str = url.substr(1);
		strs1 = str.split("&");
		var param = {};
		for (var i = 0; i < strs1.length; i++) {
			var keyValue = strs1[i].split("=");
			param[keyValue[0]] = keyValue[1];
		}
		return param[name];
	} else {
		return '';
	}
}


function formatOdate(dateString) {
	if (dateString != '') {
		var obj = new Date(dateString);
		return obj.getFullYear() + '-' + (obj.getMonth() + 1) + '-' + obj.getDate();
	} else {
		return dateString;
	}
}

function formatDate(str, d) {
	if (!str) str = new Date().getTime() / 1000;
	obj = new Date(str * 1000)
	var year = obj.getFullYear(),
		month = obj.getMonth() + 1,
		date = obj.getDate(),
		hour = obj.getHours(),
		minute = obj.getMinutes(),
		second = obj.getSeconds();
		month = month < 10 ? '0' + month : month;
		date = date < 10 ? '0' + date : date;
		hour = hour < 10 ? '0' + hour : hour;
		minute = minute < 10 ? '0' + minute : minute;
		second = second < 10 ? '0' + second : second;
		if (d) return year + "-" + month + "-" + date;
	return year + "-" + month + "-" + date + " " + hour + ":" + minute ;
	// return year + "-" + month + "-" + date + " " + hour + ":" + minute + ":" + second;
}

function fordate(date) {
	var y = date.getFullYear();
	var m = date.getMonth() + 1;
	m = m < 10 ? ('0' + m) : m;
	var d = date.getDate();
	d = d < 10 ? ('0' + d) : d;
	var h = date.getHours();
	var minute = date.getMinutes();
	minute = minute < 10 ? ('0' + minute) : minute;
	var s = date.getSeconds();
	s = s < 10 ? ('0' + s) : s;
	return y + '-' + m + '-' + d;
}

/**
 ids转成对应的values
 */
function ids2names(ids, arr) {
	if(!arr) return;
	var ret = [];
	var ida = ids.split(',');
	for(var i=0, len=ida.length; i<len; i++) {
		ret[i] = arr[ida[i]];
	}
	return ret.join(",");
}

function find_json_by_key(arr, key, val) {
	if(!arr || !key) {
		return;
	}

	for(var k in arr) {
		if(arr[k][key] == val) {
			return arr[k];
		}
	}
}

function get_json_by_key(jstr, sit) {
	var i = 0;
	for(var k in jstr) {
		if(i==sit) {
			return jstr[k]
		}
		i++
	}
}


function set_back_hash(jso, page, pagesize)
{
	var s = "#p=" + page;
	if(pagesize) s += "&ps=" + pagesize;
	for(var k in jso) {
		if(k=="page" || k=="row_num" || !jso[k]) {
			continue;
		}
		
		// console.log(typeof jso[k], ', ',k,  ': ' , jso[k]);
		if(typeof jso[k] == "object") {
			continue;
		}
		
		s +=  "&" + k + '=' + jso[k];
	}
	return s;
}

function before_today(sdate) {
	if(!sdate) return;
	var today = (new Date());
	today.setHours(0, 0, 0, 0)
	
	// console.log(sdate, ',  dddwwww');
	if(!sdate.getTime) {
		return;
	}
	
	return (sdate && (sdate.getTime() < today.getTime()))
}


/**
 * 定义Vue公共方法、变量
 * 2018/2/11 10:23
 */
var myMixin = {
	data: function () {
		return {
			getRequestParam: getRequestParam,
			getHash: getHash,
			formatDate: formatDate,
			total: 0,
			pageSize: parseInt(getHash('ps')) || 10,
			currentPage: parseInt(getHash('p')) || 1,
			back: location.hash,
		}
	},
	methods: {
		success: function (msg, dutime) {
			this.$notify({
				title: '成功',
				duration: dutime * 1000 || '2000',
				message: msg,
				type: 'success'
			});
		},
		error: function (msg, dutime) {
			this.$notify({
				title: '错误',
				duration: dutime * 1000 || '3000',
				message: msg,
				type: 'error'
			});
		},
		open5: function (msg, dutime) {
			this.$notify.info({
				title: '消息',
				duration: dutime * 1000 || '5000',
				message: msg,
			});
		},
		open2: function (message) {
			this.$message({
				message: message,
				type: 'success'
			});
		},
		open3: function (message) {
			this.$message({
				message: message,
				type: 'warning'
			});
		},
		open4: function (message) {
			this.$message({
				message: message,
			});
		},
		trim: function (str) {
			return str.replace(/(^\s*)|(\s*$)/g, "");
		},
		
		
		updated: function () {
			
			if (!$(".el-pager li.active")[0] || !this.formData.page) {
				return;
			}
			
			
			// console.log("updated , ",this.formData.page);
			
			// location.hash = setQuery(location.hash.substr(1), "p", this.formData.page);
			// location.hash = "" ;
			
			// $(".el-pager li").removeClass("active").eq(this.formData.page-1).addClass("active");
			// location.hash = "p=" + (this.formData.page||1);
			
			// document.title = this.currentPage + ', ' + this.formData.page;
			
			this.back = set_back_hash(this.formData, this.formData.page, this.formData.row_num || this.formData.pageSize || 10);
			
		},
		
		// mounted: function(){
		// 	// this.currentPage = 3;
		// 	// console.log(this.currentPage);
		// },
		
	}
	
}
