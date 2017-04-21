<template>
    <div v-ansyimgpage="{'ansy':2000}">
        <v-breadcrumb :breadcrumbData="toBreadcrumb"></v-breadcrumb>
        <!--数据筛选区域-->
        <div></div>
        <!--数据操作区域-->
        <div>
            <button type="button" class="btn btn-danger btn-xs" @click="del(chooseItem)"> 
                <i class="fa fa-trash-o fa-fw"></i>
                删除
            </button>
            <button type="button" class="btn btn-warning btn-xs" @click="edit(chooseItem)">
                <i class="fa fa-edit fa-fw"></i>
                修改
            </button>
            <button type="button" class="btn btn-success btn-xs" @click="edit(chooseItem)">
                <i class="fa fa-check-circle-o fa-fw"></i>
                阅读
            </button>
        </div>
        <!--控制展示行-->
        <v-selectForShowCol :tableHeader="tableHeader"></v-selectForShowCol>
        <!--数据展示区域-->

        <div style="overflowX:auto">
            <table class="table table-responsive table-condensed table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>多选</th>
                        <th v-for="(headerItem,key) in tableHeader" v-show="headerItem.val">{{headerItem.name}}</th>
                        <!--<th>操作</th>-->
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item,key) in dataList">
                        <td>
                            <div class="custom-radio">
                                <input type="radio" :id="key" name="chooseItem" :value="item.id" v-model="chooseItem">
                                <label :for="key"></label>
                            </div>
                        </td>
                        <td v-show="tableHeader[0].val">{{item.uid}}</td>
                        <td v-show="tableHeader[1].val">{{item.id}}</td>
                        <td v-show="tableHeader[2].val">{{item.mtime}}</td>
                        <td v-show="tableHeader[3].val">{{item.gtime}}</td>
                        <td v-show="tableHeader[4].val">{{item.paytype}}</td>
                        <td v-show="tableHeader[5].val">{{item.money}}</td>
                        <td v-show="tableHeader[6].val">{{item.uid |healthState(item.uid)}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <v-page :curPage="curpage" :allPage="allPage" @btn-click='listen'></v-page>


    </div>
</template>
<script>
    import allAjax from '../api/request.js'
    import { mapGetters, mapActions } from 'vuex'
    // 引入图片
    import testSrc from '../assets/images/img11.jpg'

    export default {
        data() {
            return {
                // 初始化导航栏数据
                imgUrl: '//www.baidu.com/img/baidu_jgylogo3.gif',
                // imgUrl:testSrc,
                toBreadcrumb: [
                    { path: 'main', name: '主页' },
                    { path: 'orderInquery', name: '订单查询' },
                ],
                // 列表数据
                tableHeader: [
                    { 'name':'编号','val':1},
                    { 'name':'姓名','val':1},
                    { 'name':'订单生成时间','val':1},
                    { 'name':'剩余时间','val':1},
                    { 'name':'性别','val':1},
                    { 'name':'money','val':1},
                    { 'name':'健康状态','val':1},
                ],
                dataList: [],
                chooseItem: '',
                searchData: { "page": '1', "btime": "", "etime": "", "paytype": "102", "mid": "" },
                allPage: '',
                curpage: 1
            }
        },
        created() {
            this.getData();
        },
        methods: {
            // showCol(){
            //     console.log(this.tableHeader);
            // },
            // 监视分页 点击事件
            listen(data) {
                this.curpage = data;
                this.searchData.page = data;
                this.getData();
                if (data == this.allPage) {
                    this.$message({
                        type: 'warning',
                        message: '最后一页!'
                    });
                }
            },
            getData() {
                var self = this;
                allAjax.searchData.payorder.call(this, this.searchData, function (response) {
                    if (response.data.code === "200") {
                        self.dataList = response.data.data.data;
                        self.allPage = response.data.data.allpage;
                    }
                });
            },
            del(index) {
                this.$confirm('此操作将永久删除该文件, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {

                    this.$message({
                        type: 'success',
                        message: '删除成功!'
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });

            },
            edit(index) {
                // 1.利用一次的请求缓存 但是并不保险
                // this.$http.get(baseUrl + 'dataTable.php').then(response => {
                //     var self=this;
                //     (response.data).forEach(function(element) {
                //         if(element.id===index){
                //             self.$router.push('/tableDetail/'+JSON.stringify(element));
                //         }
                //     }, this);
                // }, response => {
                //     console.log(response);
                // })
                // 2.仅将主键传递到详情页面然后，详情页更具主键进行再次请求数据
                if (index) {
                    this.$router.push('/tableDetail/' + index);
                }
            }
        }
    }

</script>
<style lang="css" scoped>
    .table th,
    .table td {
        text-align: center;
        vertical-align: middle!important;
    }
</style>