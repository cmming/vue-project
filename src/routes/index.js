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
             meta: { auth: true,title:"主页",},
            component: resolve => require(['../components/view/main.vue'], resolve)
        }, {
            path: '/news',
            meta: { auth: true,title:"news",},
            component: resolve => require(['../components/view/News.vue'], resolve)
        }, {
            path: '/vfor',
            meta: { auth: true,title:"vfor",},
            component: resolve => require(['../view/vfor.vue'], resolve)
        }, {
            path: '/uidemo',
            meta: { auth: true,title:"uidemo",},
            component: resolve => require(['../view/uidemo.vue'], resolve)
        }, {
            path: '/table',
            meta: { auth: true,title:"table",},
            component: resolve => require(['../view/table.vue'], resolve)
        },{
            path: '/tableDetail/:tableData',
            meta: { auth: true,title:"tableDetail",},
            component: resolve => require(['../view/detail.vue'], resolve)
        }, {
            path: '/vuex',
            meta: { auth: true,title:"vuex",},
            component: resolve => require(['../view/vuex.vue'], resolve)
        }, {
            path: '/buttonPage',
            meta: { auth: true,title:"buttonPage",},
            component: resolve => require(['../view/button.vue'], resolve)
        },{
            path: '/formElement',
            meta: { auth: true,title:"formElement",},
            component: resolve => require(['../view/formElement.vue'], resolve)
        },{
            path: '/simpleForm',
            meta: { auth: true,title:"simpleForm",},
            component: resolve => require(['../view/simpleForm.vue'], resolve)
        }, {
            path: '/formVal',
            meta: { auth: true,title:"formVal",},
            component: resolve => require(['../view/formValidation.vue'], resolve)
        },{
            path: '/error404',
            meta: { auth: true,title:"error404",},
            component: resolve => require(['../view/error404.vue'], resolve)
        },{
            path: '/elementSelect',
            meta: { auth: true,title:"elementSelect",},
            component: resolve => require(['../view/elementSelect.vue'], resolve)
        },{
            path: '/directiveDemo',
            meta: { auth: true,title:"directiveDemo",},
            component: resolve => require(['../view/directive-demo.vue'], resolve)
        },{
            path: '/elementForm',
            meta: { auth: true,title:"elementForm",},
            component: resolve => require(['../view/elementForm.vue'], resolve)
        },{
            path: '/elementTree',
            meta: { auth: true,title:"elementTree",},
            component: resolve => require(['../view/elementTree.vue'], resolve)
        },{
            path: '/addTerm',
            meta: { auth: true,title:"添加设备",},
            component: resolve => require(['../view/addTerm.vue'], resolve)
        },{
            path: '/orderInquery',
            meta: { auth: true,title:"订单查询",},
            component: resolve => require(['../view/orderInquery.vue'], resolve)
        },{
            path: '/menuConfig',
            meta: { auth: true,title:"添加目录",},
            component: resolve => require(['../view/menuConfig.vue'], resolve)
        },{
            // 登陆后默认跳转的页面
            path: '/',
            redirect: '/main'
        }, ]
    },
    {
        path: '/login',
        meta: { auth: false ,title:"登录"},
        component: resolve => require(['../view/signin.vue'], resolve)
    },
    {
        path: '/signup',
        meta: { auth: false ,title:"注册"},
        component: resolve => require(['../view/signup.vue'], resolve)
    },
];

// 定义路由对象
const router = new Router({
    routes
})

// 路由登录验证 会有很多问题（用户可以篡改客户端的数据）->所以在最后要将 打包后的index.html  改为php 页面，一直验证session是否存在，如果不存在直接将 前端存储的登录状态清空（），同时跳转到登录页面
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
router.afterEach( route => {
    // 依据sessionStorage是否存在来判断用户是否登录
    console.log(route.meta.title);
    // 每次跳转后都修改页面的将页面的title转换
    document.getElementsByTagName('title')[0].innerHTML=route.meta.title;
    // setTitle('修改title');
    // if(to.title){
    //     console.log(to.title);
    //     setTitle(to.title);
    // }
});



export default router