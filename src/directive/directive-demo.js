import Vue from 'vue'

// 检测浏览器的对象
var browser = {
    versions: function () {
        var u = navigator.userAgent, app = navigator.appVersion;
        return {//移动终端浏览器版本信息   
            trident: u.indexOf("Trident") > -1, //IE内核  
            presto: u.indexOf("Presto") > -1, //opera内核  
            webKit: u.indexOf("AppleWebKit") > -1, //苹果、谷歌内核  
            gecko: u.indexOf("Gecko") > -1 && u.indexOf("KHTML") == -1, //火狐内核  
            mobile: !!u.match(/AppleWebKit.*Mobile.*/), //是否为移动终端  
            ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端  
            android: u.indexOf("Android") > -1 || u.indexOf("Linux") > -1, //android终端或者uc浏览器  
            iPhone: u.indexOf("iPhone") > -1, //是否为iPhone或者QQHD浏览器  
            iPad: u.indexOf("iPad") > -1, //是否iPad  
            webApp: u.indexOf("Safari") == -1 //是否web应该程序，没有头部与底部  
        };
    }(),
    language: (navigator.browserLanguage || navigator.language).toLowerCase()
}

// el: 指令所绑定的元素，可以用来直接操作 DOM 。
// binding: 一个对象，包含以下属性：
Vue.directive('demo', {
  bind: function (el, binding, vnode) {
    var s = JSON.stringify
    el.innerHTML =
      'name: ' + s(binding.name) + '<br>' +
      'value: ' + s(binding.value) + '<br>' +
      'expression: ' + s(binding.expression) + '<br>' +
      'argument: ' + s(binding.arg) + '<br>' +
      'modifiers: ' + s(binding.modifiers) + '<br>' +
      'vnode keys: ' + Object.keys(vnode).join(', ')
  }
})

Vue.directive('demo1', {
  bind: function (el, binding, vnode) {
    var s = JSON.stringify
    el.innerHTML =
      "<div>内容</div>"
  }
})

// 拖动指令
Vue.directive('drag',
  function (el, binding) {
    if (!browser.versions.mobile) {
      el.onmousedown = function (ev) {
        var disX = ev.clientX - el.offsetLeft;
            var disY = ev.clientY - el.offsetTop;
        document.onmousemove = function (ev) {
          var l = ev.clientX - disX;
          var t = ev.clientY - disY;
          el.style.left = l + 'px';
          el.style.top = t + 'px';
        };
        document.onmouseup = function () {
          document.onmousemove = null;
          document.onmouseup = null;
        };
      }
    }else{
      el.ontouchstart = function (ev) {
        // var touch = e.touches[0];
        //  var x = Number(touch.clientX);
        //   var y = Number(touch.clientY);
        //    console.log("当前触摸点的横坐标"+x+"*****当前触摸点的纵坐标"+y);
        var disX = ev.clientX - el.offsetLeft;
            var disY = ev.clientY - el.offsetTop;
        document.ontouchmove = function (ev) {
          var l = ev.clientX - disX;
          var t = ev.clientY - disY;
          el.style.left = l + 'px';
          el.style.top = t + 'px';
        };
        document.ontouchend = function () {
          document.onmousemove = null;
          document.onmouseup = null;
        };
      }
    }
  });
