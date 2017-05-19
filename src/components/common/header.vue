<template>
    <header class="top-nav">
            <div class="top-nav-inner">
                <!--导航左边部分-->
                <div class="nav-header">
                    <button type="button" class="navbar-toggle pull-left sidebar-toggle" id="sidebarToggleSM" @click="showMenu">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <ul class="nav-notification pull-right" @click="showRigthPhone=!showRigthPhone">
                        <li :class="{'open':showRigthPhone}">
                            <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog fa-lg"></i></a>
                            <span class="badge badge-danger bounceIn">1</span>
                            <ul class="dropdown-menu dropdown-sm pull-right user-dropdown">
                                <li class="user-avatar">
                                    <img src="" alt="" class="img-circle">
                                    <div class="user-content">
                                        <h5 class="no-m-bottom">{{userInfo.userName}}</h5>
                                        <div class="m-top-xs">
                                            <a href="profile.html" class="m-right-sm">Profile</a>
                                            <a @click="siginOut">退出</a>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <a href="inbox.html">
                                    Inbox
                                    <span class="badge badge-danger bounceIn animation-delay2 pull-right">1</span>
                                </a>
                                </li>
                                <li>
                                    <a href="#">
                                    Notification
                                    <span class="badge badge-purple bounceIn animation-delay3 pull-right">2</span>
                                </a>
                                </li>
                                <li>
                                    <a href="#" class="sidebarRight-toggle">
                                    消息
                                    <span class="badge badge-success bounceIn animation-delay4 pull-right">7</span>
                                </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="#">Setting</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <!--在pc上显示-->
                    <a href="index.html" class="brand">
                        <i class="fa fa-database"></i><span class="brand-name">蓝光vr</span>
                    </a>
                </div>
                <!--导航右边部分 三部分-->
                <div class="nav-container">
                    <!--出现mini导航栏的触发按钮-->
                    <button type="button" class="navbar-toggle pull-left sidebar-toggle" id="sidebarToggleLG">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <!--搜索按钮-->
                    <ul class="nav-notification">
                        <li class="search-list">
                            <div class="search-input-wrapper">
                                <div class="search-input">
                                    <input type="text" class="form-control input-sm inline-block">
                                    <a href="#" class="input-icon text-normal"><i class="ion-ios7-search-strong"></i></a>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <!--最右边的一列用户信息-->
                    <div class="pull-right m-right-sm">
                        <!--带用户头像的下拉-->
                        <div class="user-block hidden-xs">
                            <a href="#" id="userToggle" data-toggle="dropdown">
                                <img src="" alt="" class="img-circle inline-block user-profile-pic">
                                <div class="user-detail inline-block">
                                    {{userInfo.userName}}
                                    <i class="fa fa-angle-down"></i>
                                </div>
                            </a>
                            <div class="panel border dropdown-menu user-panel">
                                <div class="panel-body paddingTB-sm">
                                    <ul>
                                        <li>
                                            <a href="profile.html">
                                                <i class="fa fa-edit fa-lg"></i><span class="m-left-xs">My Profile</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="inbox.html">
                                                <i class="fa fa-inbox fa-lg"></i><span class="m-left-xs">Inboxes</span>
                                                <span class="badge badge-danger bounceIn animation-delay3">2</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a @click="siginOut">
                                                <i class="fa fa-power-off fa-lg"></i><span class="m-left-xs">登出</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!--右边的消息，提醒按钮-->
                        <ul class="nav-notification">
                            <li>
                                <a href="#" data-toggle="dropdown"><i class="fa fa-envelope fa-lg"></i></a>
                                <span class="badge badge-purple bounceIn animation-delay5 active">2</span>
                                <ul class="dropdown-menu message pull-right">
                                    <li><a>You have 4 new unread messages</a></li>
                                    <li>
                                        <a class="clearfix" href="#">
                                            <img src="" alt="User Avatar">
                                            <div class="detail">
                                                <strong>John Doe</strong>
                                                <p class="no-margin">
                                                    Lorem ipsum dolor sit amet...
                                                </p>
                                                <small class="text-muted"><i class="fa fa-check text-success"></i> 27m ago
                                            </small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="clearfix" href="#">
                                            <img src="" alt="User Avatar">
                                            <div class="detail">
                                                <strong>{{userInfo.userName}}</strong>
                                                <p class="no-margin">
                                                    Lorem ipsum dolor sit amet...
                                                </p>
                                                <small class="text-muted"><i class="fa fa-check text-success"></i> 5hr ago
                                            </small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="clearfix" href="#">
                                            <img src="" alt="User Avatar">
                                            <div class="detail m-left-sm">
                                                <strong>Bill Doe</strong>
                                                <p class="no-margin">
                                                    Lorem ipsum dolor sit amet...
                                                </p>
                                                <small class="text-muted"><i class="fa fa-reply"></i> Yesterday</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="clearfix" href="#">
                                            <img src="" alt="User Avatar">
                                            <div class="detail">
                                                <strong>Baby Doe</strong>
                                                <p class="no-margin">
                                                    Lorem ipsum dolor sit amet...
                                                </p>
                                                <small class="text-muted"><i class="fa fa-reply"></i> 9 Feb 2013</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li><a href="#">View all messages</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="#" data-toggle="dropdown"><i class="fa fa-bell fa-lg"></i></a>
                                <span class="badge badge-info bounceIn animation-delay6 active">4</span>
                                <ul class="dropdown-menu notification dropdown-3 pull-right">
                                    <li><a href="#">You have 5 new notifications</a></li>
                                    <li>
                                        <a href="#">
                                            <span class="notification-icon bg-warning">
													<i class="fa fa-warning"></i>
												</span>
                                            <span class="m-left-xs">Server #2 not responding.</span>
                                            <span class="time text-muted">Just now</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <span class="notification-icon bg-success">
													<i class="fa fa-plus"></i>
												</span>
                                            <span class="m-left-xs">New user registration.</span>
                                            <span class="time text-muted">2m ago</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <span class="notification-icon bg-danger">
													<i class="fa fa-bolt"></i>
												</span>
                                            <span class="m-left-xs">Application error.</span>
                                            <span class="time text-muted">5m ago</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <span class="notification-icon bg-success">
													<i class="fa fa-usd"></i>
												</span>
                                            <span class="m-left-xs">2 items sold.</span>
                                            <span class="time text-muted">1hr ago</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <span class="notification-icon bg-success">
													<i class="fa fa-plus"></i>
												</span>
                                            <span class="m-left-xs">New user registration.</span>
                                            <span class="time text-muted">1hr ago</span>
                                        </a>
                                    </li>
                                    <li><a href="#">View all notifications</a></li>
                                </ul>
                            </li>
                            <li class="chat-notification">
                                <a href="#" class="sidebarRight-toggle"><i class="fa fa-comments fa-lg"></i></a>
                                <span class="badge badge-danger bounceIn">1</span>

                                <div class="chat-alert">
                                    Hello, Are you there?
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- ./top-nav-inner -->
        </header>
</template>
<script>
    import allAjax from '../../api/request.js'
    import {mapGetters} from 'vuex'
    export default {
        data() {
            return {
                b: "",
                // 手机时候右边的下拉
                showRigthPhone: false,
                // userInfo:{
                //     userName:""
                // }
            }
        },
        props: ['msg'],
        computed: mapGetters([
            'loading',
            'userInfo'
        ]),
        mounted(){
            // console.log(this.$store.state);
            this.userInfo.userName=sessionStorage.userName;
        },
        methods: {
            showMenu: function() {
                this.msg.phoneMenuShow = !this.msg.phoneMenuShow;
                console.log(this.msg.phoneMenuShow);
            },
            // 退出登录
            siginOut:function(){
                var resData = {'type':"loginout",'formdata':'测试'};
				this.loadding = true;
				var self=this;
                allAjax.userData.signout.call(this,resData,function(response){
                    if (response.data.code === "200") {
                        sessionStorage.accessToken = '';
						self.loadding=false;
                        self.$router.push('login');
						// sessionStorage.accessToken = true;
						// self.$router.push('main');
					}
					else{
						self.requestError=true;
						self.errorMsg=response.data.msg;
						self.loadding=false;
					}
                });
            }
            // showRigth 手机时候的下拉
            // showRigth() {
            //     this.showRigthPhone = !this.showRigthPhone;
            // }
        }
    }
</script>