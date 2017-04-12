<template>
    <!--常用表单元素样式-->
    <!--通用导航条 -->
    <div>
        <v-breadcrumb :breadcrumbData="toBreadcrumb"></v-breadcrumb>
        <div class="col-md-12">
            <div class="smart-widget widget-purple">
                <div class="smart-widget-header">
                    form表单案例
                </div>
                <div class="smart-widget-inner">
                    <div class="smart-widget-body">
                        <!--为表单添加验证过滤-->
                        <form class="form-horizontal no-margin" @submit.prevent="validateBeforeSubmit">
                            <div class="form-group">
                                <label class="control-label col-lg-2">email</label>
                                <div :class="{'col-lg-6': true, 'has-error': errors.has('email') }">
                                    <i class="fa fa-envelope-o icon-absolute-left"></i>
                                    <i class="fa fa-envelope-o icon-absolute-right"></i>
                                    <input autocomplete="false" type="nonvoid" class="form-control input-sm" placeholder="非空的 email" name="email" v-model="email" v-validate="'required|email'">
                                    <!--错误提示信息-->
                                    <v-errorMsg 
                                    :errorMsgAlert="{'isShow':errors.has('email'),'msg':[{'isShow':errors.has('email:required'),'msg':errors.first('email:required')},{'isShow':errors.has('email:email'),'msg':errors.first('email:email')}]}">
                                    </v-errorMsg>
                                </div>
                            </div>
                            <!-- /form-group -->
                            <div class="form-group">
                                <label class="col-lg-2 control-label">水平 单选框</label>
                                <div class="col-lg-10">
                                    <div class="radio inline-block">
                                        <div class="custom-radio m-right-xs">
                                            <input type="radio" id="inlineRadio1" value="1" v-model="inlineRadio" name="inlineRadio" v-validate="'required|in:1,2'">
                                            <label for="inlineRadio1"></label>
                                        </div>
                                        <div class="inline-block vertical-top">单选框 1</div>
                                    </div>
                                    <div class="radio inline-block">
                                        <div class="custom-radio m-right-xs">
                                            <input type="radio" id="inlineRadio2" name="inlineRadio" value="2" v-model="inlineRadio">
                                            <label for="inlineRadio2"></label>
                                        </div>
                                        <div class="inline-block vertical-top">单选框 2 {{inlineRadio}}</div>
                                    </div>
                                    
                                    <v-errorMsg 
                                    :errorMsgAlert="{'isShow':errors.has('inlineRadio'),'msg':[{'isShow':errors.has('inlineRadio'),'msg':errors.first('inlineRadio:required')}]}">
                                    </v-errorMsg>
                                </div>
                                <!-- /.col -->
                            </div>
                            <!--复选框的验证-->
                            <div class="form-group">
                                <label class="col-lg-2 control-label">Inline Checkbox</label>
                                <div class="col-lg-10">
                                    <div class="checkbox inline-block">
                                        <div class="custom-checkbox">
                                            <input v-model="inlineCheckbox" name="inlineCheckbox" value="1" type="checkbox" id="inlineCheckbox1" class="checkbox-red" v-validate="'required'">
                                            <label for="inlineCheckbox1"></label>
                                        </div>
                                        <div class="inline-block vertical-top">
                                            Checkbox 1
                                        </div>
                                    </div>
                                    <div class="checkbox inline-block">
                                        <div class="custom-checkbox">
                                            <input v-model="inlineCheckbox" name="inlineCheckbox" value="2" type="checkbox" id="inlineCheckbox2">
                                            <label for="inlineCheckbox2"></label>
                                        </div>
                                        <div class="inline-block vertical-top">
                                            Checkbox 2
                                        </div>
                                    </div>
                                    <div class="checkbox inline-block">
                                        <div class="custom-checkbox">
                                            <input v-model="inlineCheckbox" name="inlineCheckbox" value="3" type="checkbox" id="inlineCheckbox3" class="checkbox-purple">
                                            <label for="inlineCheckbox3"></label>
                                        </div>
                                        <div class="inline-block vertical-top">
                                            Checkbox 3
                                        </div>
                                    </div>
                                    <div class="checkbox inline-block">
                                        <div class="custom-checkbox">
                                            <input v-model="inlineCheckbox" name="inlineCheckbox" value="4" type="checkbox" id="inlineCheckbox4" class="checkbox-blue">
                                            <label for="inlineCheckbox4"></label>
                                        </div>
                                        <div class="inline-block vertical-top">
                                            Checkbox 4
                                        </div>
                                    </div>
                                    <div class="checkbox inline-block">
                                        <div class="custom-checkbox">
                                            <input v-model="inlineCheckbox" name="inlineCheckbox" value="5" type="checkbox" id="inlineCheckbox5" class="checkbox-yellow">
                                            <label for="inlineCheckbox5"></label>
                                        </div>
                                        <div class="inline-block vertical-top">
                                            Checkbox 5
                                        </div>
                                    </div>
                                    <div class="checkbox inline-block">
                                        <div class="custom-checkbox">
                                            <input name="inlineCheckbox" value="6" v-model="inlineCheckbox" type="checkbox" id="inlineCheckbox6" class="checkbox-grey">
                                            <label for="inlineCheckbox6"></label>
                                        </div>
                                        <div class="inline-block vertical-top">
                                            Checkbox 6 {{inlineCheckbox}}
                                        </div>
                                    </div>
                                    <v-errorMsg 
                                    :errorMsgAlert="{'isShow':errors.has('inlineCheckbox'),'msg':[{'isShow':errors.has('inlineCheckbox:required'),'msg':errors.first('inlineCheckbox:required')}]}">
                                    </v-errorMsg>
                                </div>
                                <!-- /.col -->
                            </div>
                            <!-- /form-group -->
                            <!--自定义规则 通过写入正则-->
                            <div class="form-group">
                                <label class="control-label col-lg-2">自定规则过滤手机号</label>
                                <div :class="{'col-lg-6': true, 'has-error': (errors.has('userTel:required')|errors.has('userTel:regex'))}">
                                    <input v-validate="'required|regex:^[1][358][0-9]{9}$'" type="text" class="form-control input-sm" placeholder="手机号" name="userTel" v-model="userTel">
                                    
                                    <v-errorMsg 
                                    :errorMsgAlert="{'isShow':errors.has('userTel'),'msg':[{'isShow':errors.has('userTel:required'),'msg':errors.first('userTel:required')},{'isShow':errors.has('userTel:regex'),'msg':errors.first('userTel:regex')}]}">
                                    </v-errorMsg>
                                </div>
                                <!-- /.col -->
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-2">限制长度</label>
                                <div :class="{'col-lg-6': true, 'has-error': (errors.has('rangeLength:required')|errors.has('rangeLength:min')|errors.has('rangeLength:max'))}">
                                    <input name="rangeLength" v-validate="'required|min:5|max:10'" v-model="rangeLength" type="text" class="form-control input-sm" placeholder="长度 = [5,10]">
                                    
                                    <v-errorMsg 
                                    :errorMsgAlert="{'isShow':errors.has('rangeLength'),'msg':[{'isShow':errors.has('rangeLength:min'),'msg':errors.first('rangeLength:min')},{'isShow':errors.has('rangeLength:max'),'msg':errors.first('rangeLength:max')},{'isShow':errors.has('rangeLength:required'),'msg':errors.first('rangeLength:required')}]}">
                                    </v-errorMsg>
                                </div>
                                <!-- /.col -->
                            </div>
                            <!-- /form-group -->
                            <div class="form-group">
                                <label class="control-label col-lg-2">最小值 (number)</label>
                                <div class="col-lg-6">
                                    <input type="text" name="minVal" v-model="minVal" v-validate="'required|min_value:6'" class="form-control input-sm" placeholder="Min = 6">
                                    <!--错误提示信息-->
                                    <v-errorMsg 
                                    :errorMsgAlert="{'isShow':errors.has('minVal'),'msg':[{'isShow':errors.has('minVal:required'),'msg':errors.first('minVal:required')},{'isShow':errors.has('minVal:min_value'),'msg':errors.first('rangeLength:min_value')}]}">
                                    </v-errorMsg>
                                </div>
                                
                                <!-- /.col -->
                            </div>
                            <!-- /form-group -->
                            <div class="form-group">
                                <label class="control-label col-lg-2">最大值 (number)</label>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control input-sm" v-model="maxVal" name="maxVal" v-validate="'required|max_value:100'" placeholder="Max = 100">
                                    <span v-show="errors.has('maxVal')" class="err_msg">
                                        <!--不同类型的错误-->
                                        <span v-show="errors.has('maxVal:required')">不能为空 </span>
                                        <span v-show="errors.has('maxVal:max_value')">最大值为100 </span>
                                    </span>
                                </div>
                                <!-- /.col -->
                            </div>
                            <!-- /form-group -->
                            <div class="form-group">
                                <label class="control-label col-lg-2">数值范围 (number)</label>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control input-sm" name="rangeNum" v-model="rangeNum" v-validate="'between:6,100'" placeholder="range = [6,100]">
                                    <span v-show="errors.has('rangeNum')" class="err_msg">
                                        <!--不同类型的错误-->
                                        <span v-show="errors.has('rangeNum:between')">数值范围在6到100 </span>
                                    </span>
                                </div>
                                <!-- /.col -->
                            </div>
                            <!-- /form-group -->
                            <div class="form-group">
                                <label class="control-label col-lg-2">两个input框值相等</label>
                                <div class="col-lg-6">
                                    <input type="password" name="userPwd" v-model="userPwd" placeholder="密码" id="textbox1" class="form-control input-sm m-bottom-md">
                                    <input type="password" name="confirmPwd" v-model="confirmPwd" placeholder="确认密码" v-validate="'confirmed:userPwd'" class="form-control input-sm">
                                    <!--错误提示信息-->
                                    <span v-show="errors.has('confirmPwd')" class="err_msg">
                                        <!--不同类型的错误-->
                                        <span v-show="errors.has('confirmPwd:confirmed')">两次密码必须一致 </span>
                                    </span>
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
                Password: "",
                checkPassword: "",
                // validate 定义
                email: '',
                inlineRadio: '',
                inlineCheckbox:[],
                userTel:"",
                rangeLength:"",
                minVal:"",
                rangeNum:"",
                maxVal:"",
                userPwd:"",
                confirmPwd:"",
                name: '',
                phone: '',
                url: ''
            }
        },
        components: {
            'v-breadcrumb': breadcrumb,
            'v-errorMsg': errorMsg
        },
        methods: {
            validateBeforeSubmit() {
                this.$validator.validateAll().then(() => {
                    // eslint-disable-next-line
                    alert('ok');
                }).catch(() => {
                    // eslint-disable-next-line
                    alert('未通过');
                });
            }
        }
    }

</script>