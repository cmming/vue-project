<template>
    <aside :class="{'sidebar-menu':true,'fixed':true,'active':msg.phoneMenuShow}" style="overflow:scroll">
        <div class="sidebar-inner scrollable-sidebar">
            <!--上半部分的导航条  bg-palette1控制颜色-->
            <div class="main-menu">
                <ul class="accordion">
                    <!--带事件控制-->
                    <!--<li  v-for='(menu,key) in menuData' -->
                    <li v-for='(menu,key) in menuData' class="openable" :class="menu.bgPalette">
                        <a v-if="menu.childMenu!==undefined" href="javascript:void(0)" @click="showMenu(key)">
                            <span class="menu-content block">
										<span class="menu-icon"><i class="block fa fa-lg" :class="menu.iconFont"></i></span>
                            <span class="text m-left-sm">{{menu.title}}</span>
                            <span class="submenu-icon" :class="{'downIcon':(showIndex===key)}"></span>
                            </span>
                            <span class="menu-content-hover block">
									{{menu.title}}
								</span>
                        </a>
                        <router-link v-if="menu.childMenu===undefined" :to="menu.path" href="javascript:void(0)" @click="showMenu(key)">
                            <span class="menu-content block">
                                        <span class="menu-icon"><i class="block fa fa-lg" :class="menu.iconFont"></i></span>
                            <span class="text m-left-sm">{{menu.title}}</span>
                            </span>
                            <span class="menu-content-hover block">
                                        Form
                                </span>
                        </router-link>
                        <ul v-if="menu.childMenu!==undefined" :class="{'submenu':true, 'bg-palette4':true,'block':true}" v-show="showIndex===key">
                            <li v-for="childMenu in menu.childMenu">
                                <router-link :to="childMenu.path"><span class="submenu-label">{{childMenu.title}}</span></router-link>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>

        </div>
        <!-- sidebar-inner -->
    </aside>
</template>
<script>
    export default {
        data() {
            return {
                //controll childMenu
                showIndex: '',
                childMenuShow: false,
                // 菜单文件必要的配置 from 后台传来的目录数据
                menuData: [{
                    title: "一级",
                    iconFont: "fa-bell",
                    childMenu: [{
                        title: "新闻",
                        path: "/news"
                    }]
                },{
                    title: "无子目录的",
                    path: "/table",
                    iconFont: "fa-check-circle",
                },{
                    title: "完整的实例页面",
                    iconFont: "fa-list",
                    childMenu: [{
                        title: "table",
                        path: "/table"
                    },{
                        title:"uidemo tableDetail构建",
                        path:"/tableDetail/menu"
                    }, {
                        title: "vfor动态绑定",
                        path: "/vfor"
                    },{
                        title: "login",
                        path: "/login"
                    },{
                        title:"vuex 数据处理",
                        path:"/vuex"
                    },{
                        title:"uidemo ui构建",
                        path:"/uidemo"
                    },{
                        title:"uidemo button构建",
                        path:"/buttonPage"
                    },{
                        title:"uidemo formElement构建",
                        path:"/formElement"
                    },{
                        title:"uidemo simpleForm构建",
                        path:"/simpleForm"
                    },{
                        title:"uidemo formVal构建",
                        path:"/formVal"
                    },{
                        title:"uidemo error404构建",
                        path:"/error404"
                    },{
                        title:"uidemo elementSelect构建",
                        path:"/elementSelect"
                    },{
                        title:"uidemo directiveDemo构建",
                        path:"/directiveDemo"
                    },{
                        title:"uidemo elementForm构建",
                        path:"/elementForm"
                    }]
                },{
                    title:"eleMent ui demo",
                    iconFont: "fa-bell",
                    childMenu: [{
                        title: "tree",
                        path: "/elementTree"
                    },{
                        title: "添加设备",
                        path: "/addTerm"
                    }]
                },{
                    title:"查询",
                    iconFont: "fa-search",
                    childMenu: [{
                        title: "订单查询",
                        path: "/orderInquery"
                    }]
                }],
            }
        },
        props: ['msg'],
        mounted() {
            this.menuData.forEach(function (element, index) {
                index = index % 4;
                element.bgPalette = "bg-palette" + (index + 1);
            }, this);
        },
        methods: {
            showMenu(index) {
                if (this.showIndex === index) {
                    this.showIndex = '';
                } else {
                    this.showIndex = index;
                }
            },
        },

    }

</script>