<template>
    <div>
        <ul class="breadcrumb">
            <li><span class="primary-font"><i class="icon-home"></i></span><a href="index.html"> Home</a></li>
            <li>UI Elements</li>
            <li>Tab</li>
        </ul>
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
                    <th>生日</th>
                    <!--<th>生日</th>
                    <th>生日</th>
                    <th>生日</th>
                    <th>生日</th>
                    <th>生日</th>-->
                    <th>性别</th>
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
                    <td>{{item.id}}</td>
                    <td>{{item.name}}</td>
                    <td>{{item.birthday}}</td>
                    <!--<td>{{item.birthday}}</td>
                    <td>{{item.birthday}}</td>
                    <td>{{item.birthday}}</td>
                    <td>{{item.birthday}}</td>
                    <td>{{item.birthday}}</td>-->
                    <td>{{item.sex}}</td>
                    <td>{{item.state |healthState(item.state)}}</td>
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
        <v-deleteModel :confirmDel="deleteModel" :alertMsg="deleteModel.alertMsg" v-show="deleteModel.confirmDel">
            <h5 slot="alertMsg">{{deleteModel.alertMsg}}</h5>
            <span slot="warnning">{{deleteModel.warnning}}</span>
        </v-deleteModel>
    </div>
</template>
<script>
    import allAjax from '../api/request.js'
    import { mapGetters, mapActions } from 'vuex'

    export default {
        data() {
            return {
                // 列表数据
                dataList: [],
                chooseItem: '',
                deleteModel: { confirmDel: false ,index:'',alertMsg:"",warnning:""}
            }
        },
        computed: mapGetters([
            'tableData'
        ]),
        mounted() {
            this.getData();
        },
        watch: {
            deleteModel: {
                handler(curVal) {
                     var self = this;
                    if (!curVal.confirmDel&&curVal.index!=='') {
                        allAjax.tableAjax.del.call(this, curVal.index, function (response) {
                            self.dataList = response.data;
                        });
                    }else{
                        self.chooseItem=''
                    }
                },
                deep: true
            }
        },
        methods: {
            getData() {
                var self = this;
                allAjax.tableAjax.getData.call(this, function (response) {
                    self.dataList = response.data;
                });
            },
            del(index) {
                if(index){
                    this.deleteModel.confirmDel = true;
                    this.deleteModel.index = index;
                    this.deleteModel.alertMsg = '你确定删除序号为'+index+'的数据吗？';
                    this.deleteModel.warnning = '删除数据后将无法恢复';
                }
               
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