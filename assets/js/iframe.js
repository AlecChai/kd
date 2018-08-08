
function layerIframe() {
    var all = document.getElementsByClassName("iframe-self")[0];
    var iframe = document.getElementsByClassName("layer-iframe-s")[0];
    var close = iframe.getElementsByClassName("iframe-close");
    var fullScreenBtn = iframe.getElementsByClassName("full-screen")[0];
    var resizeBtn = iframe.getElementsByClassName("footer-resize-btn")[0];
    var content = iframe.getElementsByClassName("iframe-content")[0];
    var mock = document.getElementsByClassName("mock-iframe")[0];
    var mockWhite = document.getElementsByClassName("mock-iframe-white")[0];
    var header = iframe.getElementsByClassName("title")[0];
    var dataLocation = {}; //用于存储元素的位置信息

    //初始点击按钮后，显示弹窗，并完成添加事件的初始化操作
    showIframeFun();
    fullScreenInit();


    for (var i = 0; i < close.length; i++) {
        close[i].addEventListener("click", hideIframeFun, false);
    }
    fullScreenBtn.addEventListener('click', fullScreenInit, false);
    header.addEventListener('mousedown', onmousedownFun, false);

    //获取浏览器窗口实际尺寸
    function getScreenSize() {
        var obj = {};
        obj.width = document.documentElement.clientWidth;
        obj.height = document.documentElement.clientHeight;
        return obj;
    }

    //显示
    function showIframeFun() {
        content.style.width = 800 + 'px';
        content.style.height = 400 + 'px';
        fullScreenBtn.classList.remove('exit');
        fullScreenBtn.innerHTML = "全屏";
        header.addEventListener('mousedown', onmousedownFun, false);
        dataLocation.left = (getScreenSize().width - 800) / 2 ;
        dataLocation.top = (getScreenSize().height - 600) / 2 ;

        setLocation(iframe, dataLocation);
        all.classList.remove('hidden-i');
       
    }

    //影藏和移除所有的事件绑定
    function hideIframeFun() {
        all.classList.add('hidden-i');   
        for (var i = 0; i < close.length; i++) {
            close[i].removeEventListener("click", hideIframeFun, false);
        }
        fullScreenBtn.removeEventListener('click', fullScreenInit, false);
        header.removeEventListener('mousedown', onmousedownFun, false);
    }

    //设置位置
    function setLocation(node, data) {
        for (var i in data) {
            node.style[i] = data[i] + 'px';
        }
    }

    //获取位置信息
    function getLocation(node) {
        var data = {};
        data.left = parseInt(getComputedStyle(node)["left"]);
        data.top = parseInt(getComputedStyle(node)["top"]);
        return data;
    }

    
    //全屏和退出全屏
    function fullScreenInit() {
        scroll(0, 0);
        var classArr = fullScreenBtn.classList;
        if (classArr.length === 1) {
            dataLocation = getLocation(iframe);
            setLocation(iframe, {
                "left": 0,
                "top": 0
            });
            var size = getScreenSize();
            mock.classList.add('hidden-i');
            content.style.width = size.width + 'px';
            content.style.height = size.height - 87 + 'px';
            fullScreenBtn.classList.add('exit');
            fullScreenBtn.innerHTML = "退出全屏";
            header.removeEventListener("mousedown", onmousedownFun, false);
        } else {
            setLocation(iframe, dataLocation);
            content.style.width = 800 + 'px';
            content.style.height = 400 + 'px';
            fullScreenBtn.classList.remove('exit');
            fullScreenBtn.innerHTML = "全屏";
            header.addEventListener('mousedown', onmousedownFun, false);
            mock.classList.remove('hidden-i');
        } 
    }


    function getPos(ev) {
        var scrollTop = document.documentElement.scrollTop || window.pageYOffset || document.body.scrollTop;
        var scrollLeft = document.documentElement.scrollLeft || window.pageXOffset || document.body.scrollLeft;
        return {
            x: ev.clientX + scrollLeft,
            y: ev.clientY + scrollTop
        }
    }

    //拖拽实现
    function onmousedownFun(ev) {
        ev.stopImmediatePropagation();
        mockWhite.classList.remove("hidden-i");
        document.body.className = "hidden-x";
        var oEvent = ev || event;
        var disX = 0;
        var disY = 0;
        var pos = getPos(oEvent);
        disX = pos.x - iframe.offsetLeft;
        disY = pos.y - iframe.offsetTop;

        document.onmousemove = function (ev) {
            var oEvent = ev || event;
            var pos = getPos(oEvent);
            var l = pos.x - disX;
            var t = pos.y - disY;

            if (l <= 0) {
                l = 0;

            } else if (l > document.documentElement.clientWidth - iframe.offsetWidth ) {
                l = document.documentElement.clientWidth - iframe.offsetWidth
            }
            if (t <= 0 ) {
                t = 0;
            } else if (t > document.documentElement.clientHeight - iframe.offsetHeight ) {
                t = document.documentElement.clientHeight - iframe.offsetHeight
            }
            iframe.style.left = l + 'px';
            iframe.style.top = t + 'px';

        };
        document.onmouseup = function () {
            document.body.className = "";
            mockWhite.classList.add("hidden-i");
            document.onmousemove = null;
            document.onmouseup = null;
        }
    }
}