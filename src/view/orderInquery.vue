<template>
    <div>
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
        <!--数据展示区域-->
        <table class="table table-responsive table-condensed table-bordered table-hover">
            <thead>
                <tr>
                    <th>多选</th>
                    <th>编号</th>
                    <th>姓名</th>
                    <th>订单生成时间</th>
                    <th>性别</th>
                    <th>money</th>
                    <th>健康状态</th>
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
                    <td>{{item.uid}}</td>
                    <td>{{item.mid}}</td>
                    <td>{{item.mtime}}</td>
                    <td>{{item.paytype}}</td>
                    <td>{{item.money}}</td>
                    <td>{{item.uid |healthState(item.uid)}}</td>
                    <!--<td>
                        <button type="button" class="btn btn-danger btn-xs" @click="del(item.id)"> 
                            <i class="fa fa-trash-o fa-fw"></i>
                            删除
                        </button>
                        <button type="button" class="btn btn-warning btn-xs">
                            <i class="fa fa-edit fa-fw"></i>
                            修改
                        </button>
                    </td>-->
                </tr>
            </tbody>
        </table>
        <v-page :curPage="curpage" :allPage="allPage" @btn-click='listen'></v-page>
    </div>
</template>
<script>
    import allAjax from '../api/request.js'
    import { mapGetters, mapActions } from 'vuex'

    export default {
        data() {
            return {
                // 初始化导航栏数据
                toBreadcrumb: [
                    { path: 'main', name: '主页' },
                    { path: 'orderInquery', name: '订单查询' },
                ],
                // 列表数据
                dataList: [],
                chooseItem: '',
                searchData:{"page":'1',"btime":"","etime":"","paytype":"","mid":""},
                allPage:'',
                curpage:1
            }
        },
        created() {
            this.getData();
        },
        methods: {
            // 监视分页 点击事件
            listen(data){
                this.curpage=data;
                this.searchData.page=data;
                this.getData();
            },
            getData() {
                var self = this;
                allAjax.searchData.payorder.call(this,this.searchData ,function (response) {
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
                if(index){
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
    
    .fa-border {
        border: 1px solid #dfdfdf;
        padding: 3px;
    }
</style>