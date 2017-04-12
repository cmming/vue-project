// cm edit 20170313
//  封装通用导航条
//  @param  {array}   breadcrumbData      该数组的每个子类都需要有path:路由跳转的位置 name:显示在页面上的文字
//  demo <v-breadcrumb :breadcrumbData="toBreadcrumb"></v-breadcrumb>
<template>
    <ul class="breadcrumb">
        <li v-for="breadcrumbItem in breadcrumbData"> 
            <router-link :to="breadcrumbItem.path">{{breadcrumbItem.name}}</router-link>
        </li>
    </ul>
</template>
<script>
    export default{
        props:["breadcrumbData"]
    }
</script>