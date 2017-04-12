
// import Vue from 'vue'
// // 数据请求
// import axios from 'axios';
// // import VueAxios from 'vue-axios';

// // Vue.use(VueAxios, axios);

// // 修改原型链的方式
// axios.defaults.baseURL = 'https://api.example.com';
// axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
// //添加请求拦截器
// axios.interceptors.request.use(function (config) {
//   //在发送请求之前做某事
//   //  console.log(config);
//   return config;
// }, function (error) {
//   //请求错误时做些事
//   return Promise.reject(error);
// });

// //添加响应拦截器
// axios.interceptors.response.use(function (response) {
//   //对响应数据做些事 可以在这里做错误的请求跳转 根据不同的响应码进行跳转
//   //  console.log(response);
//   if (response.status !== 200) {
//     router.push('login');
//   }
//   return response;
// }, function (error) {
//   //请求错误时做些事
//   return Promise.reject(error);
// });
// Vue.prototype.$http = axios



// 接口的一级目录
import Vue from 'vue'
import axios from 'axios'
import VueAxios from 'vue-axios'
import store from '../store'
// 全局方法错误请求的方法
import { Notification } from 'element-ui';


var instance = axios.create({
  baseURL: '/api',
  // 规定所有的请求只有1s的等待
  timeout: 5000,
  // 设置请求的token
  headers: { 'X-Custom-Header': 'foobar', 'token': sessionStorage.getItem("token") },
  // headers: {'X-Requested-With': 'XMLHttpRequest'},
  // requestHeader:{'Content-Type':'application/json'},
  // 对所有的请求数据统一转换为json字符串
  transformRequest: [function (data) {
    // 对 data 进行任意转换处理
    return JSON.stringify(data);
  }],
});

// //添加请求拦截器
instance.interceptors.request.use(function (config) {
  // 在发送请求之前做某事
  // 1.加载一个loading的样式组件
  // store.dispatch.call(SHOWLOAGING);
   store.state.loading.loading = true;
  return config;
}, function (error) {
  //请求错误时做些事
  // 1.隐藏之前的loading组件 显示加载动画
   store.state.loading.loading = false;
  // 全局方法错误请求的方法
  Notification.error({
    title: '错误',
    message: '操作失败，联系管理员'
  });
  return Promise.reject(error);
});

//添加响应拦截器
instance.interceptors.response.use(function (response) {
  //对响应数据做些事 可以在这里做错误的请求跳转 根据不同的响应码进行跳转
  // if (response.data.code!==200) {
  //   // this.$router.push('login');
  //   window.location.href='#/login'
  // }
  // 隐藏加载动画
   store.state.loading.loading = false;
  return response;
}, function (error) {
  //请求错误时做些事 1。改变请求的全局状态
   store.state.loading.loading = false;
   // 全局方法错误请求的方法
  Notification.error({
    title: '错误',
    message: '操作失败，联系管理员'
  });
  return Promise.reject(error);
});

Vue.use(VueAxios, instance)
