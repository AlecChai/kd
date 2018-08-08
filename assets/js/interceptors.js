/**
 * Created by Yangjiafei on 2017/7/12.
 */
Vue.http.interceptors.push(function(request,next){

    next(function (response) {
        if(response.body.error == 1 && response.body.data.login_status == true) {

            if(typeof this.error == 'function') {
                this.error(response.body.msg);
            } else {
                alert(response.body.msg);
            }

        } else if(response.body.error == 1 && response.body.data.login_status == false) {
			// alert(response.body.msg);
            window.top.location.href = "/login/index";
        }
    })
})
