import Vue from 'vue'
import Router from 'vue-router'
// 设置title脚本
import setTitle from '../util/setTitle/setTitle.js'
Vue.use(Router);

// 路由配置  this.$router.push('home')在js中设置路由跳转  this.$route.params
const routes = [
    { path: '/', redirect: '/login' },
    {
        path: '/manager',
        component: resolve => require(['../view/user.vue'], resolve),
        children: [{
            path: '/main',
            title:"主页",
            component: resolve => require(['../components/view/main.vue'], resolve)
        }, {
            path: '/news',
            title:"news",
            component: resolve => require(['../components/view/News.vue'], resolve)
        }, {
            path: '/vfor',
            title:"vfor",
            component: resolve => require(['../view/vfor.vue'], resolve)
        }, {
            path: '/uidemo',
            title:"uidemo",
            component: resolve => require(['../view/uidemo.vue'], resolve)
        }, {
            path: '/table',
            title:"table",
            component: resolve => require(['../view/table.vue'], resolve)
        },{
            path: '/tableDetail/:tableData',
            title:"tableDetail",
            component: resolve => require(['../view/detail.vue'], resolve)
        }, {
            path: '/vuex',
            title:"vuex",
            component: resolve => require(['../view/vuex.vue'], resolve)
        }, {
            path: '/buttonPage',
            title:"buttonPage",
            component: resolve => require(['../view/button.vue'], resolve)
        },{
            path: '/formElement',
            title:"formElement",
            component: resolve => require(['../view/formElement.vue'], resolve)
        },{
            path: '/simpleForm',
            title:"simpleForm",
            component: resolve => require(['../view/simpleForm.vue'], resolve)
        }, {
            path: '/formVal',
            title:"formVal",
            component: resolve => require(['../view/formValidation.vue'], resolve)
        },{
            path: '/error404',
            title:"error404",
            component: resolve => require(['../view/error404.vue'], resolve)
        },{
            path: '/elementSelect',
            title:"elementSelect",
            component: resolve => require(['../view/elementSelect.vue'], resolve)
        },{
            path: '/directiveDemo',
            title:"directive",
            component: resolve => require(['../view/directive-demo.vue'], resolve)
        },{
            path: '/elementForm',
            title:"elementForm",
            component: resolve => require(['../view/elementForm.vue'], resolve)
        },{
            path: '/elementTree',
            title:"elementTree",
            component: resolve => require(['../view/elementTree.vue'], resolve)
        },{
            path: '/addTerm',
            title:"添加设备",
            component: resolve => require(['../view/addTerm.vue'], resolve)
        },{
            path: '/orderInquery',
            title:"订单查询",
            component: resolve => require(['../view/orderInquery.vue'], resolve)
        },{
            // 登陆后默认跳转的页面
            path: '/',
            redirect: '/main'
        }, ]
    },
    {
        path: '/login',
        meta: { auth: false },
        component: resolve => require(['../view/signin.vue'], resolve)
    },
    {
        path: '/signup',
        meta: { auth: false },
        component: resolve => require(['../view/signup.vue'], resolve)
    },
];

// 定义路由对象
const router = new Router({
    routes
})

// 路由登录验证
router.beforeEach(({ meta, path }, from, next) => {
    window.scroll(0, 0);
    // 依据sessionStorage是否存在来判断用户是否登录
    var { auth = true } = meta;
    var isLogin = Boolean(sessionStorage.getItem('accessToken'));
    if (auth && !isLogin && path !== '/login') {
        return next({ path: '/login' })
            // 只要跳到登录页面就自动清除sessionStorage
    } else if (path === '/login') {
        sessionStorage.accessToken = "";
    }
    next()
});
// router.afterEach( route => {
//     // 依据sessionStorage是否存在来判断用户是否登录
//     console.log(route);
//     setTitle('修改title');
//     // if(to.title){
//     //     console.log(to.title);
//     //     setTitle(to.title);
//     // }
// });
// router.afterEach((transition) => {
//     console.log(transition.title);
// //   let title = transition.to.title + '-Custom-Suffix'
// //   setTitle(title)
// })


export default router