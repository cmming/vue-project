// 路由配置
import Home from '../../components/tpl/home.vue'
// var Home= { template: '<div class="default">default</div>' }

const routes = [
    // { path: '/home', component: require('../tpl/home.vue') },
    { path: '/home', component: Home },
    { path: '*', redirect: '/home' } //404
];
export default {
  routes
}
