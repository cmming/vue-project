<template>
    <!--常用表单元素样式-->
    <!--通用导航条 -->
    <div>
        <v-breadcrumb :breadcrumbData="toBreadcrumb"></v-breadcrumb>
        <div class="col-md-12">
            <div class="smart-widget widget-purple">
                <div class="smart-widget-header">
                    添加设备
                </div>
                <div class="smart-widget-inner">
                    <div class="smart-widget-body">
                        <!--为表单添加验证过滤-->
                        <form class="form-horizontal no-margin" @submit.prevent="validateBeforeSubmit">
                            <div class="form-group">
                                <label class="control-label col-lg-2">终端设备标识：</label>
                                <div :class="{'col-lg-6': true, 'has-error': errors.has('termid') }">
                                    <input autocomplete="off" type="nonvoid" class="form-control input-sm" placeholder="请输入终端设备标识" name="termid" v-model="formdata.termid" v-validate="'required'">
                                    <!--错误提示信息-->
                                    <v-errorMsg 
                                    :errorMsgAlert="{'isShow':errors.has('termid'),'msg':[{'isShow':errors.has('termid:required'),'msg':errors.first('termid:required')}]}">
                                    </v-errorMsg>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-2">设备名称：</label>
                                <div :class="{'col-lg-6': true, 'has-error': errors.has('name') }">
                                    <input autocomplete="off" type="nonvoid" class="form-control input-sm" placeholder="请输入终端设备标识" name="name" v-model="formdata.name" v-validate="'required'">
                                    <!--错误提示信息-->
                                    <v-errorMsg 
                                    :errorMsgAlert="{'isShow':errors.has('name'),'msg':[{'isShow':errors.has('name:required'),'msg':errors.first('name:required')}]}">
                                    </v-errorMsg>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 control-label">水平 单选框</label>
                                <div class="col-lg-10">
                                    <div class="radio inline-block" v-for="radioItem in radioGroup">
                                        <div class="custom-radio m-right-xs">
                                            <input v-if="radioItem.value==1" checked="checked" type="radio" :id="'inlineRadio'+radioItem.value" :value="radioItem.value" v-model="formdata.enable" name="inlineRadio" v-validate="'required'">
                                            <input v-if="radioItem.value!=1" type="radio" :id="'inlineRadio'+radioItem.value" :value="radioItem.value" v-model="formdata.enable" name="inlineRadio" v-validate="'required'">
                                            <label :for="'inlineRadio'+radioItem.value"></label>
                                        </div>
                                        <div class="inline-block vertical-top">{{radioItem.name}}</div>
                                    </div>
                                    <v-errorMsg 
                                    :errorMsgAlert="{'isShow':errors.has('inlineRadio'),'msg':[{'isShow':errors.has('inlineRadio'),'msg':errors.first('inlineRadio:required')}]}">
                                    </v-errorMsg>
                                </div>
                                <!-- /.col -->
                            </div>
                            
                            <!-- /form-group -->
                            <div class="form-group">
                                <div class="text-center m-top-md col-lg-9">
                                    <button type="submit" class="btn btn-info">提交</button>
                                    <button type="reset" class="btn btn-danger">重置</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
                <!-- ./smart-widget-inner -->
            </div>
            <!-- ./smart-widget -->
        </div>
    </div>
</template>

<script>
    import allAjax from '../api/request.js'
    export default {
        data() {
            return {
                // 初始化导航栏数据
                toBreadcrumb: [
                    { path: 'main', name: '主页' },
                    { path: 'addTerm', name: '添加设备' },
                ],
                formdata:{
                    termid:"",
                    name:"",
                    spid:"",
                    enable:""
                },
                radioGroup:[
                    {value:'0',name:"不可用"},
                    {value:'1',name:"正常启用"},
                    {value:'2',name:"试用状态"},
                    {value:'3',name:"暂停使用"},
                ],
            }
        },
        methods: {
            validateBeforeSubmit() {
                this.$validator.validateAll().then(() => {
                    // eslint-disable-next-line
                    console.log(this.formdata);
                    var self=this;
                    allAjax.addData.addTerm.call(this, this.formdata, function (response) {
                            console.log(response.data);
                        if (response.data.code === "200") {
                            self.$message({
                                type: 'success',
                                message: '设备添加成功',
                                showClose:true
                            });
                            self.$router.push('/addTerm')
                        }
                        else {
                            self.$message({
                                type: 'error',
                                message: response.data.msg+'！设备添加失败',
                                showClose:true
                            });
                        }
                    });
                }).catch(() => {
                    // eslint-disable-next-line
                    alert('未通过');
                });
            }
        }
    }

</script>