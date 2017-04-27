// cm edit 20170313 常用函数库
//选择器
/**
 * Created by Jason on 2016/12/31.
 */
//jquery 的构造函数 选择器
function Jquery(arg) {
    //用来存选出来的元素
    this.elemenets = [];
    switch (typeof arg) {
        case 'function':
            domReady(arg);
            break;
        case 'string':
            this.elements = getEle(arg);
            break;
        case 'object':
            this.elements.push(arg);
            break;
    }
}
//DOM ready onload 如果参数是函数，则进行domReady操作
function domReady(fn) {
    // FF chrome
    if (document.addEventListener) {
        //jquery中已经省略false，false解决低版本火狐兼容性问题
        document.addEventListener('DOMContentLoaded', fn, false);
    } else {
        document.attachEvent('onreadystatechange', function () {
            if (document.readyState == 'complete') {
                fn();
            }
        });
    }
}

function getByClass(oParent, sClass) {
    //高级浏览器支持getElementsByClassName直接使用
    if (oParent.getElementsByClassName) {
        return oParent.getElementsByClassName(sClass);
    } else {
        //不支持需要选中所有标签的类名来选取
        var res = [];
        var aAll = oParent.getElementsByTagName('*');
        for (var i = 0; i < aAll.length; i++) {
            //选中标签的全部类名是个str='btn on red'=aAll[i].className   使用正则  reg=/\b sClass \b/g
            var reg = new RegExp('\\b' + sClass + '\\b', 'g');
            if (reg.test(aAll[i].className)) {
                res.push(aAll[i]);
            }
        }
        return res;
    }
}

//如果参数是str 进行选择器的操作
function getByStr(aParent, str) {
    //用来存放选中的元素的数组 这个数组在getEle存在，为了每次执行的时候都需要清空，所以使用局部函数的变量
    var aChild = [];
    //aParent开始是[document],再执行完getByStr的时候getEle将aParent指向了getByStr函数的返回值aChild数组以确保循环父级下面的所有匹配元素
    for (var i = 0; i < aParent.length; i++) {
        switch (str.charAt(0)) {
            //id选择器  eg: #box  使用document.getElementById选取
            case '#':
                var obj = document.getElementById(str.substring(1));
                aChild.push(obj);
                break;
            //类选择器  eg: .box  使用上面封装的getByClass选取
            case '.':
                //由于一个标签可以有多个类选择器 所以需要进行循环选取
                var aRes = getByClass(aParent[i], str.substring(1));
                for (var j = 0; j < aRes.length; j++) {
                    aChild.push(aRes[j]);
                }
                break;
            //今天先简单的编写选择器  这里我们假设除了id和类选择器，就是标签选择器
            default:
                // 如果是li.red  那么用正则来判断
                if (/\w+\.\w+/g.test(str)) {
                    //先选择标签，在选择类选择器  使用类选择器的时候重复选择器函数即可
                    var aStr = str.split('.');
                    var aRes = aParent[i].getElementsByTagName(aStr[0]);
                    var reg = new RegExp('\\b' + aStr[1] + '\\b', 'g');
                    //循环选取标签，注意外层已经有i的循环
                    for (var j = 0; j < aRes.length; j++) {
                        if (reg.test(aRes[j].className)) {
                            aChild.push(aRes[j]);
                        }
                    }
                    //如果是li:eq(2) 或者 li:first这样的选择器   书写正则是的时候注意（）可有可以无为？ 有或者没有为* 至少有一个也就是若干个为+   {2,5}这种则为2-5个
                } else if (/\w+\:\w+(\(\d+\))?/g.test(str)) {
                    //讲str进行整理    [li,eq,2]  或者  [li,first]
                    var aStr = str.split(/\:|\(|\)/);
                    //aStr[2]是eq、lt、gt传入的参数，这里使用n来保存
                    var n = aStr[2];
                    //在父级下获取所有匹配aStr[0]项的标签
                    var aRes = aParent[i].getElementsByTagName(aStr[0]);
                    //这时候会循环判断aStr[1]项是的内容，jquery中经常使用的为eq、lt、gt、even、odd、first、last
                    switch (aStr[1]) {
                        //如果是eq则把第n项传入aChild数组即可
                        case 'eq':
                            aChild.push(aRes[n]);
                            break;
                        //如果是lt需要将aRes数组中获取到的小于n的标签循环推入aChild中
                        case 'lt':
                            for (var j = 0; j < n; j++) {
                                aChild.push(aRes[j]);
                            }
                            break;
                        //如果是gt则和lt相反
                        case 'gt':
                            for (var j = n; j < aRes.legth; j++) {
                                aChild.push(aRes[j]);
                            }
                            break;
                        //如果是event的话需要隔数添加，注意jquery中的event是从第0开始循环的
                        case 'event':
                            for (var j = 0; j < aRes.length; j += 2) {
                                aChild.push(aRes[j]);
                            }
                            break;
                        //如果是odd的和event不同的只是从第1项开始循环
                        case 'odd':
                            for (var j = 1; j < aRes.length; j += 2) {
                                aChild.push(aRes[j]);
                            }
                            break;
                        //如果是first，则将aRes[0]推入aChild
                        case 'first':
                            aChild.push(aRes[0]);
                            break;
                        case 'last':
                            aChild.push(aRes[aRes.length - 1]);
                            break;
                    }
                    //属性选择器  eg：input[type=button] 同样适用正则来判断
                } else if (/\w+\[\w+\=\w+\]/g.test(str)) {
                    //将属性选择器切成数组   [input,type,button]
                    var aStr = str.split(/\[|\=|\]/g);
                    var aRes = aParent[i].getElementsByTagName(aStr[0]);
                    //在选中标签中选出有aRes[1]的属性
                    for (var j = 0; j < aRes.length; j++) {
                        //把属性值为aRes[2]的标签推入aChild中
                        if (aRes[j].getAttribute(aStr[1]) == aStr[2]) {
                            aChild.push(aRes[j]);
                        }
                    }
                    //标签选择器  div、span
                } else {
                    var aRes = aParent[i].getElementsByTagName(str);
                    for (var j = 0; j < aRes.length; j++) {
                        aChild.push(aRes[j]);
                    }
                }
                break;
        }
    }
    return aChild;
}

function getEle(str) {
    //如果是字符串的话先要去除收尾空格  eg:"   on replace   index  play auto   "
    var arr = str.replace(/^\s+|\s+$/g, '').split(/\s+/g);
    var aChild = [];
    var aParent = [document];
    for (var i = 0; i < arr.length; i++) {
        aChild = getByStr(aParent, arr[i]);
        aParent = aChild
    }
    return aChild;
}

//实现jquery $符号的写法 
function $(arg) {
    return new Jquery(arg);
}


//css方法
Jquery.prototype.css=function(name,value){
    //设置单个样式的时候使用  oEle.css(name,value); 注意可能是一个元素，也可能是一组元素，需要循环添加
    if(arguments.length==2){
        for(var i=0;i<this.elements.length;i++){
            this.elements[i].style[name]=value;
        }
    }else{
        //当参数为字符串是获取样式  oEle.css(name); 当参数为object的时候是批量添加样式{name1:value1,name2:value2} 这里判断的是参数类型
        switch(typeof name){
            //获取元素的样式只要返回获取的样式的值即可,jqeury中当选中一组元素获取样式的时候是默认返回第一个元素的样式
            case 'string':
                return this.elements[0].style[name];
                break;
            //当为参数为json的时候循环json添加样式
            case 'object':
                for(var i=0;i<this.elelemts.length;i++){
                    for(var item in name){
                        this.elements[i].style[item]=name[item];
                    }
                }
                break;
        }
    }
};

//attr  获取属性  和css的思路是一样的，这里就不过多的注释了
Jquery.prototype.attr=function(name,value){
    if(arguments.length==2){
        for(var i=0;i<this.elements.length;i++){
            this.elements[i].setAttribute[name]=value;
        }
    }else{
        switch(typeof name){
            case 'string':
                return this.elements[0].getAttribute[name];
                break;
            case 'object':
                for(var i=0;i<this.elements.length;i++){
                    for(var item in name){
                        this.elements.setAttribute[item]=item;
                    }
                }
                break;
        }
    }
};

//事件，使用了下面的事件绑定函数封装   jquery中事件全是绑定的
'mouseover mouseout click keydown keyup resize scroll load change'.replace(/\w+/g,function(sEv){
    Jquery.prototype[sEv]=function(fn){
        for(var i=0;i<this.elements.length;i++){
            addEvent(this.elements[i],sEv,fn);
        }
    }
});

//事件绑定函数的封装
function addEvent(obj,sEv,fn){
    //高级浏览器支持addEventListener
    if(obj.addEventListener){
        obj.addEventListener(sEv,function(ev){
            var ev=ev || event;
            fn.apply(obj,arguments);
        },false);
    }else{
        obj.attachEvent('on'+sEv,function(ev){
            var ev=ev || event;
            fn.apply(obj,arguments);
        });
    }
}
//onmouseenter事件的添加，onouseenter是进入范围后只触发一次，这里使用onmouseover实现
Jquery.prototype.mouseenter=function(fn){
    for(var i=0;i<this.elements.length;i++){
        addEvent(this.elements[i],'mouseenter',function(ev){
            //获取鼠标上一次的位置
            var ev=ev || event;
            var oFrom=ev.fromElement || ev.relatedTarget;
            //在构造函数中，如果有return就直接返回return后面的东西，如果没有return会返回构造函数的属性值；这里判断上一次鼠标的位置是不是在this的范围之内，如果是就直接返回空结束onmouseenter事件，若果没有就修改this的指向
            if(this.contains(oFrom)){
                return;
            }
            fn&&fn.apply(this,arguments);
        });
    }
};