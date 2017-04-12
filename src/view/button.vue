<template>
    <div>
        <!--通用导航条-->
        <v-breadcrumb :breadcrumbData="toBreadcrumb"></v-breadcrumb>
        <!--集中颜色的button-->
        <div class="smart-widget-body col-lg-10">
            <select class="select2 m-top-md width-100" v-model="btnSetting">
                <optgroup label="按钮颜色">
                    <option value="btn-default">默认颜色</option>
                    <option value="btn-primary">btn-primary（蓝色）</option>
                    <option value="btn-info">btn-info（浅蓝色）</option>
                    <option value="btn-success">btn-success（绿色）</option>
                    <option value="btn-warning">btn-warning（黄色）</option>
                    <option value="btn-danger">btn-danger（红色）</option>
                </optgroup>
            </select>
            <select class="select2 m-top-md width-100" v-model="btnSize">
                <optgroup label="按钮大小">
                    <option value="btn-lg">超大</option>
                    <option value="">默认</option>
                    <option value="btn-sm">小图标</option>
                    <option value="btn-xs">超小</option>
                </optgroup>
            </select>
            <button type="submit" class="btn btn-default marginTB-xs" :class="btnSetting">{{btnSetting}}</button>
            <button type="submit" class="btn btn-default marginTB-xs" :class="btnSize">{{btnSize}}</button>
            <br> button with icon
            <br><br><br>
            <button type="button" class="btn btn-primary marginTB-xs">
                <i class="fa fa-save m-right-xs"></i>
                Save
            </button>

            <button type="button" class="btn btn-success marginTB-xs">
                Next<i class="fa fa-angle-double-right m-left-xs"></i>
            </button>

            <button type="button" class="btn btn-default marginTB-xs"><i class="fa fa-spinner fa-spin m-right-xs"></i>Loading</button>
        </div>
    </div>
</template>
<script>
    import breadcrumb from '../components/common/breadcrumb.vue'
    export default {
        data() {
            return {
                toBreadcrumb: [
                    { path: 'main', name: '主页' },
                    { path: 'buttonPage', name: 'button构建' },
                ],
                btnSetting: "btn-primary",
                btnSize: ""
            }
        },
        components: {
            'v-breadcrumb': breadcrumb
        },
    }

</script>