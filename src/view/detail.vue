<template>
    <!--常用表单元素样式-->
    <!--通用导航条 -->
    <div>
        <v-breadcrumb :breadcrumbData="toBreadcrumb"></v-breadcrumb>
        <div class="col-md-12">
            <form class="form-horizontal no-margin" @submit.prevent="validateBeforeSubmit">
                <div class="form-group">
                    <label class="control-label col-lg-2">id</label>
                    <div class="col-lg-6">
                        <input type="text" name="id" v-model="formData.id" v-validate="'required'" class="form-control input-sm" placeholder="id">
                        <!--错误提示信息-->
                        <v-errorMsg :errorMsgAlert="{'isShow':errors.has('id'),'msg':[{'isShow':errors.has('id:required'),'msg':errors.first('id:required')}]}">
                        </v-errorMsg>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-2">姓名</label>
                    <div class="col-lg-6">
                        <input type="text" name="name" v-model="formData.name" v-validate="'required'" class="form-control input-sm" placeholder="姓名">
                        <!--错误提示信息-->
                        <v-errorMsg :errorMsgAlert="{'isShow':errors.has('name'),'msg':[{'isShow':errors.has('name:required'),'msg':errors.first('name:required')}]}">
                        </v-errorMsg>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-2">birthday</label>
                    <div class="col-lg-6">
                        <input type="text" name="birthday" v-model="formData.birthday" v-validate="'required'" class="form-control input-sm" placeholder="birthday">
                        <!--错误提示信息-->
                        <v-errorMsg :errorMsgAlert="{'isShow':errors.has('birthday'),'msg':[{'isShow':errors.has('birthday:required'),'msg':errors.first('birthday:required')}]}">
                        </v-errorMsg>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-2">birthday</label>
                    <div class="col-lg-10">
                        <!--<input type="text" name="sex" v-model="formData.sex" v-validate="'required'" class="form-control input-sm" placeholder="sex">-->
                        <div class="radio inline-block">
                            <div class="custom-radio m-right-xs">
                                <input type="radio" id="inlineRadio1" value="男" v-model="formData.sex" name="sex" v-validate="'required|in:男,女'">
                                <label for="inlineRadio1"></label>
                            </div>
                            <div class="inline-block vertical-top">男</div>
                        </div>
                        <div class="radio inline-block">
                            <div class="custom-radio m-right-xs">
                                <input type="radio" id="inlineRadio2" name="sex" value="女" v-model="formData.sex">
                                <label for="inlineRadio2"></label>
                            </div>
                            <div class="inline-block vertical-top">女</div>
                        </div>
                        <!--错误提示信息-->
                        <v-errorMsg :errorMsgAlert="{'isShow':errors.has('sex'),'msg':[{'isShow':errors.has('sex:required'),'msg':errors.first('sex:required')},{'isShow':errors.has('sex:in'),'msg':errors.first('sex:in')}]}">
                        </v-errorMsg>
                    </div>
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
</template>

<script>
    import allAjax from '../api/request.js'
    import breadcrumb from '../components/common/breadcrumb.vue'
    import errorMsg from '../components/common/formError.vue'
    export default {
        data() {
            return {
                // 初始化导航栏数据
                toBreadcrumb: [
                    { path: 'main', name: '主页' },
                    { path: 'formElement', name: 'formElement构建' },
                ],
                formData: {
                    id: "",
                    name: "",
                    birthday: "",
                    sex: ""
                }
            }
        },
        components: {
            'v-breadcrumb': breadcrumb,
            'v-errorMsg': errorMsg
        },
        mounted() {
            this.initData(this.$route.params.tableData);
        },
        methods: {
            validateBeforeSubmit() {
                this.$validator.validateAll().then(() => {
                    // eslint-disable-next-line eslint 不检测这一行
                    alert('ok');
                }).catch(() => {
                    // eslint-disable-next-line
                    alert('未通过');
                });
            },
            initData(index) {
                var self=this;
                if (index !== 'menu') {
                    var self = this;
                    allAjax.tableAjax.getSignl.call(this, index, function (response) {
                        (response.data).forEach(function (element) {
                            if (element.id === index) {
                                self.formData = element;
                            }
                        }, this);
                    });
                }
            }
        }
    }

</script>