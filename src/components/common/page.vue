<!--分页的组件-->

<template>
    <div class="page-bar">
        <ul>
            <li><a :class="{'page-button-disabled':cur == 1}" v-on:click="prvePage(cur)">上一页</a></li>
            <li v-for="index in indexs" v-bind:class="{ active: cur == index }">
                <a v-on:click="btnClick(index)">{{ index < 1 ? "..." : index }}</a>
            </li>
            <li><a :class="{'page-button-disabled':cur == all}" v-on:click="nextPage(cur)">下一页</a></li>
        </ul>
    </div>
</template>
<style lang="css" scoped>
    .page-bar {
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        display: flex;
    }
    .page-bar ul{
        margin: 0 auto;
    }
    
    .page-button-disabled {
        color: #ddd !important;
    }
    
    .page-bar li {
        list-style: none;
        display: inline-block;
    }
    
    .page-bar li:first-child>a {
        margin-left: 0px;
    }
    
    .page-bar a {
        border: 1px solid #ddd;
        text-decoration: none;
        position: relative;
        float: left;
        padding: 6px 12px;
        margin-left: -1px;
        line-height: 1.42857143;
        color: #337ab7;
        cursor: pointer;
    }
    
    .page-bar a:hover {
        background-color: #eee;
    }
    
    .page-bar .active a {
        color: #fff;
        cursor: default;
        background-color: #337ab7;
        border-color: #337ab7;
    }
    
    .page-bar i {
        font-style: normal;
        color: #d44950;
        margin: 0px 4px;
        font-size: 12px;
    }
</style>
<script>
    export default {
        props: ['curPage', 'allPage'],
        data(){
            return{
                cur:'',
                all:''
            }
        },
        computed: {
            indexs: function () {
                this.cur=this.curPage;
                this.all=this.allPage;
                var left = 1
                var right = this.all
                var ar = []
                if (this.all >= 11) {
                    if (this.cur > 5 && this.cur < this.all - 4) {
                        left = this.cur - 5
                        right = this.cur + 4
                    } else {
                        if (this.cur <= 5) {
                            left = 1
                            right = 10
                        } else {
                            right = this.all
                            left = this.all - 9
                        }
                    }
                }
                while (left <= right) {
                    ar.push(left)
                    left++
                }
                if (ar[0] > 1) {
                    ar[0] = 1;
                    ar[1] = -1;
                }
                if (ar[ar.length - 1] < this.all) {
                    ar[ar.length - 1] = this.all;
                    ar[ar.length - 2] = 0;
                }
                return ar
            }
        },
        methods: {
            // 页码点击事件
            btnClick: function (data) {
                if (data < 1) return;
                if (data != this.cur) {
                    this.cur = data
                    this.$emit('btn-click',data);
                    // this.$dispatch('btn-click', data)
                }
            },
            // 下一页
            nextPage: function (data) {
                if (this.cur >= this.all) return;
                this.btnClick(this.cur + 1);
            },
            // 上一页
            prvePage: function (data) {
                if (this.cur <= 1) return;
                this.btnClick(this.cur - 1);
            },
            // 设置按钮禁用样式
            setButtonClass: function (isNextButton) {
                if (isNextButton) {
                    return this.cur >= this.all ? "page-button-disabled" : ""
                }
                else {
                    return this.cur <= 1 ? "page-button-disabled" : ""
                }
            }
        }
    }

</script>