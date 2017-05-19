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
    // pc 上的拖动事件
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
    } else {
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

// 延迟加载指令
// <img class="img1" v-imgsrc="{'url':imgUrl,ansy:6000}"  width="200px" height="200px" style="border:1px solid black">
// url 待加载的ur ansy :延迟加载的时间
// <img class="img2" v-imgsrc="{'url':imgUrl,ansy:3000}"  width="200px" height="200px" style="border:1px solid black">
import lodingImg from '../assets/images/loading2.gif'
Vue.directive('imgsrc', {
  update: function (el, binding, vnode, oldVnode) {
    el.setAttribute("src", lodingImg)
    window.setTimeout(function () {
      el.setAttribute("src", binding.value.url)
    }, binding.value.ansy)
  }
});


// 当前页面的img全部都是延迟加载 使用方式 一个标签底下的所有元素都会被延迟加载时间为指令后面的数字
// v-ansyimgpage="{'ansy':2000}" 使用json形式传递参数，便于后面的扩展性
//<img :src="imgUrl" width="200px" height="200px" style="border:1px solid black">
Vue.directive('ansyimgpage', {
  // 已经来就绑定
  bind: function (el, binding) {
    var elem_child = el.childNodes;
    for (var i = 0; i < elem_child.length; i++) { //遍历子元素
      if (elem_child[i].nodeName == "IMG") {
        var src = elem_child[i].getAttribute("src");
        elem_child[i].setAttribute('src', lodingImg);
        elem_child[i].setAttribute('data-src', src);
      }
    }
  },
  // 当前元素插入以后
  inserted: function (el, binding) {
    window.onscroll = function () {
      var sTop = document.body.scrollTop || document.documentElement.scrollTop;
      var cHeight = document.documentElement.clientHeight || document.body.clientHeight;
      var elem_child = el.childNodes;
      // 使用 块集变量 let 方便内部使用
      for (let i = 0; i < elem_child.length; i++) { //遍历子元素
        if (elem_child[i].nodeName == "IMG") {
          if (sTop + cHeight >= elem_child[i].offsetTop) {
            var datasrc = elem_child[i].getAttribute("data-src");
            window.setTimeout(function () {
              elem_child[i].setAttribute("src", datasrc);
            }, binding.value.ansy)
          }
        }
      }
    };
  },
});

// toggleClass

Vue.directive('toggleClass',{
  inserted:function(el,binding){
    
  }
});

Vue.directive('cmdemo', {
  // 已经来就绑定
  bind: function (el, binding) {
    console.log('bind:', binding.value);
  },
  // 当前元素插入以后
  inserted: function (el, binding) {
    console.log('insert:', binding.value);
  },
  // 元素的状态更新以后
  update: function (el, binding, vnode, oldVnode) {
    el.focus();
    console.log(el.dataset.name);//这里的数据是可以动态绑定的
    console.table({
      name: binding.name,
      value: binding.value,
      oldValue: binding.oldValue,
      expression: binding.expression,
      arg: binding.arg,
      modifiers: binding.modifiers,
      vnode: vnode,
      oldVnode: oldVnode
    });
  },
  // 组件 跟新以后会
  componentUpdated: function (el, binding) {
    console.log('componentUpdated:', binding.name);
  }
});