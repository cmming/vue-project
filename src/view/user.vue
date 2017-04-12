<template>
    <div :class="{'wrapper':true,'preload':false,'display-left':menuParams.phoneMenuShow}">
        <!-- sidebar-right -->
        <!--顶部导航条-->
        <header-com :msg="menuParams"></header-com>
        <!--左侧显示的导航条 路由入口-->
        <menu-com :msg="menuParams"></menu-com>
        <!--网页右侧的主体部分-->
        <div class="main-container" style="min-height:1000px" @click="menuParams.phoneMenuShow=false">
            <div class="padding-md">
                <transition name="bounce">
                    <router-view v-loading="loading" element-loading-text="拼命加载中"></router-view>
                </transition>
            </div>
        </div>
    </div>
</template>
<script>
    import headerCom from '../components/common/header.vue'
    import menuCom from '../components/common/menu.vue'
    import { mapGetters, mapActions } from 'vuex'

    export default {
        data() {
            return {
                menuParams: {
                    phoneMenuShow: false
                }
                , loading2: true
            }
        },
        computed: mapGetters([
            'loading'
        ]),
        components: {
            'header-com': headerCom,
            'menu-com': menuCom
        }
    }

</script>
<style lang="css">
    .bounce-enter-active {
        animation: bounce-in .5s;
        -webkit-animation: bounce-in .5s;
    }
    
    .bounce-leave-active {
        animation: bounce-out .2s;
        -webkit-animation: bounce-out .2s;
    }
    
    @keyframes bounce-in {
        0% {
            transform: scale(0);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }
    
    @keyframes bounce-out {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(0.95);
        }
        100% {
            transform: scale(0);
        }
    }
</style>