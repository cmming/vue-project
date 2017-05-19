<template>
    <div v-ansyimgpage="{'ansy':2000}">
        <v-breadcrumb :breadcrumbData="toBreadcrumb"></v-breadcrumb>
        <!--数据筛选区域-->
        <div class="container bg-white padding-md">
            <div class="row">
                <div class="col-md-1 col-sm-2 font-600">筛选区：</div>
                <div class="col-md-11 col-sm-10 pull-left">
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <span>开始日期：</span>
                            <el-date-picker type="date" placeholder="选择日期" v-model="searchData.btime"></el-date-picker>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <span>结束日期：</span>
                            <el-date-picker type="date" placeholder="选择日期" v-model="searchData.etime"></el-date-picker>
                        </div>
                        <div class="col-md-4">
                            <span>搜索内容: </span>
                            <el-autocomplete icon="search" v-model="searchData.mid" :fetch-suggestions="querySearchAsync" placeholder="请输入内容" @select="handleSelect" :on-icon-click="searchCal"></el-autocomplete>
                            <!--<button type="button" class="btn btn-success" @click="edit(chooseItem)">
                                <i class="fa fa-search fa-fw"></i>
                                搜索
                            </button>-->
                        </div>
                    </div>
                </div>
            </div>
            <!--数据操作区域-->
            <div class="m-top-xs row">
                <div class="font-600 col-md-1 col-sm-2">操作区:</div>
                <div class="col-md-11 col-sm-10">
                    <button type="button" class="btn btn-danger btn-sm" @click="del(chooseItem)"> 
                        <i class="fa fa-trash-o fa-fw"></i>
                        删除
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" @click="edit(chooseItem)">
                        <i class="fa fa-edit fa-fw"></i>
                        修改
                    </button>
                    <button type="button" class="btn btn-success btn-sm" @click="edit(chooseItem)">
                        <i class="fa fa-check-circle-o fa-fw"></i>
                        阅读
                    </button>
                </div>
            </div>
            <!--控制展示行-->
            <div class="m-top-xs row">
                <div class="font-600 col-md-1 col-sm-12">控制列:</div>
                <v-selectForShowCol class="inline-block col-md-11 col-sm-12" :tableHeader="tableHeader"></v-selectForShowCol>
            </div>
        </div>
        <!--数据展示区域-->

        <div class="m-top-md bg-white padding-xs" style="overflowX:auto">
            <div v-show="allPage">
                <table class="table table-responsive table-condensed table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>多选1</th>
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
                <!--分页组件-->
                <v-page :curPage="curpage" :allPage="allPage" @btn-click='listen'></v-page>
            </div>
            <div v-show="!allPage">
                <div class="alert" ng-hide="orderView">
                    <strong>抱歉！</strong> 没有相关数据
                </div>
            </div>
        </div>


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
                    { 'name': '编号', 'val': 1 },
                    { 'name': '姓名', 'val': 1 },
                    { 'name': '订单生成时间', 'val': 1 },
                    { 'name': '剩余时间', 'val': 1 },
                    { 'name': '性别', 'val': 1 },
                    { 'name': 'money', 'val': 1 },
                    { 'name': '健康状态', 'val': 1 },
                ],
                dataList: [],
                chooseItem: '',
                searchData: { "page": '1', "btime": "", "etime": "", "paytype": "102", "mid": "" },
                allPage: '',
                curpage: 1,
                restaurants:[]
                
            }
        },
        created() {
            this.getData();
            this.restaurants = this.loadAll();
        },
        methods: {
            searchCal(ev){
                console.log(ev);
                this.getData();
            },
             loadAll() {
                return [
                    { "value": "三全鲜食（北新泾店）", "address": "长宁区新渔路144号" },
                    { "value": "Hot honey 首尔炸鸡（仙霞路）", "address": "上海市长宁区淞虹路661号" },
                    { "value": "新旺角茶餐厅", "address": "上海市普陀区真北路988号创邑金沙谷6号楼113" },
                    { "value": "泷千家(天山西路店)", "address": "天山西路438号" },
                    { "value": "胖仙女纸杯蛋糕（上海凌空店）", "address": "上海市长宁区金钟路968号1幢18号楼一层商铺18-101" },
                    { "value": "贡茶", "address": "上海市长宁区金钟路633号" },
                    { "value": "豪大大香鸡排超级奶爸", "address": "上海市嘉定区曹安公路曹安路1685号" },
                    { "value": "茶芝兰（奶茶，手抓饼）", "address": "上海市普陀区同普路1435号" },
                ];
            },
            // 异步搜索框  输入框的内容每次改变都会触发事件
            querySearchAsync(queryString, cb){
                var restaurants = this.restaurants;
                console.log(queryString,restaurants.filter(this.createStateFilter(queryString)));
                // 对返回值进行处理 （排序）
                var results = queryString ? restaurants.filter(this.createStateFilter(queryString)) : restaurants;
                cb(results);
                // 制作延迟出现的代码 模拟实际请求效果
                // clearTimeout(this.timeout);
                // this.timeout = setTimeout(() => {
                // cb(results);
                // }, 3000 * Math.random());
            },
            // 对返回值进行处理
            createStateFilter(queryString) {
                return (state) => {
                return (state.value.indexOf(queryString.toLowerCase()) === 0);
                };
            },
            // 选中一个元素的返回值
            handleSelect(item){
                console.log(item);
            },
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
                    }else{
                        self.allPage=0;
                        self.$message({
                            type:"warning",
                            message:response.data.msg
                        });
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
    
    .font-600 {
        padding: 10px 0;
    }
</style>