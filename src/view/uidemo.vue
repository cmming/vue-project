<template>
    <div class="col-lg-6">
        <div class="smart-widget widget-purple" v-show="smallShow">
            <div class="smart-widget-header" :style="{'backgroundColor':headerBg}">
                模块名称
                <span class="smart-widget-option">
                    <!--设置颜色的按钮-->
                    <a href="javascript:void(0)" @click="colorList=!colorList" class="widget-toggle-hidden-option">
                        <i class="fa fa-cog"></i>
                    </a>
                    <!--关闭展开按钮-->
                    <a href="javascript:void(0)" @click="contentShow=!contentShow" class="widget-collapse-option" data-toggle="collapse">
                        <i class="fa" :class="{'fa-chevron-up':contentShow,'fa-chevron-down':!contentShow}"></i>
                    </a>
                    <!--刷新按钮-->
                    <a href="javascript:void(0)" class="widget-refresh-option">
                        <i class="fa fa-refresh"></i>
                    </a>
                    <!--关闭按钮-->
                    <a href="javascript:void(0)" class="widget-remove-option" @click="smallShow=!smallShow">
                        <i class="fa fa-times"></i>
                    </a>
                </span>
            </div>
            <div class="smart-widget-inner" :class="{'hide_panel':contentShow}">
                <div class="smart-widget-hidden-section" :class="{'show_block':colorList}">
                    <ul class="widget-color-list clearfix">
                        <li style="background-color:#20232b;" data-color="widget-dark" @click="headerBg='#20232b'"></li>
                        <li style="background-color:#4c5f70;" data-color="widget-dark-blue" @click="headerBg='#4c5f70'"></li>
                        <li style="background-color:#23b7e5;" data-color="widget-blue" @click="headerBg='#23b7e5'"></li>
                        <li style="background-color:#2baab1;" data-color="widget-green" @click="headerBg='#edbc6c'"></li>
                        <li style="background-color:#edbc6c;" data-color="widget-yellow" @click="headerBg='#edbc6c'"></li>
                        <li style="background-color:#fbc852;" data-color="widget-orange" @click="headerBg='#fbc852'"></li>
                        <li style="background-color:#e36159;" data-color="widget-red" @click="headerBg='#e36159'"></li>
                        <li style="background-color:#7266ba;" data-color="widget-purple" @click="headerBg='#f5f5f5'"></li>
                        <li style="background-color:#f5f5f5;" data-color="widget-light-grey" @click="headerBg='#fff'"></li>
                        <li style="background-color:#fff;" data-color="reset"></li>
                    </ul>
                </div>
                <div class="smart-widget-body no-padding">
                    <div class="padding-md">
                        <div id="placeholder" style="height: 250px; padding: 0px; position: relative;">
                            内容区域 多个这种组件的唯一性该怎么判断？？
                        </div>
                    </div>

                    <div class="bg-grey">
                        <div class="row">
                            <div class="col-xs-4 text-center">
                                <h3 class="m-top-sm">3491</h3>
                                <small class="m-bottom-sm block">Total Sales</small>
                            </div>
                            <div class="col-xs-4 text-center">
                                <h3 class="m-top-sm">721</h3>
                                <small class="m-bottom-sm block">New Orders</small>
                            </div>
                            <div class="col-xs-4 text-center">
                                <h3 class="m-top-sm">$8103</h3>
                                <small class="m-bottom-sm block">Total Earnings</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./smart-widget-inner -->
        </div>
        <!-- ./smart-widget -->
    </div>
</template>
<script>
    export default {
        data() {
            return {
                colorList: false,
                contentShow: false,
                smallShow:true,
                headerBg:""
            }
        },
    }

</script>