import Vue from 'vue'


// filter 传入参数 第一个默认参数为前面的值可以不写
Vue.filter('healthState',
  function (value) {
      var result="";
      //注意数值的转换 传进来的是string类型   
      value=Number(value);
      switch (value) { 
          case 1:
             result = "健康";
              break;
          case 2:
              result ="亚健康";
              break;
          case 3:
              result ="不健康";
              break;
          case 4:
              result ="很不健康";
              break;
          default:
              result ="未知";
              break;
      }
      return result ;
  });