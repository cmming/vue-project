<template>
    <div class="sign-in-wrapper">
        <div class="sign-in-inner">
            <div class="login-brand text-center">
                <!--蓝光vr <strong class="text-skin">管理后台</strong>-->
            </div>
            <form v-model="formData">
                <div class="form-group m-bottom-md col-lg-12" :class="{'has-error': (errors.has('userTel:required')|errors.has('userName:userName'))}">
                    <i class="fa fa-user icon-absolute-left"></i>
                    <!--<i class="fa fa-envelope-o icon-absolute-right"></i>-->
                    <input autocomplete="false" v-validate="'required|userName'" type="text" class="form-control input-sm" placeholder="用户名"
                        name="userName" v-model="formData.userName">

                    <v-errorMsg :errorMsgAlert="{'isShow':errors.has('userName'),'msg':[{'isShow':errors.has('userName:required'),'msg':errors.first('userName:required')},{'isShow':errors.has('userName:userName'),'msg':errors.first('userName:userName')}]}">
                    </v-errorMsg>
                </div>

                <div class="form-group m-bottom-md col-lg-12" :class="{'has-error': (errors.has('userTel:required')|errors.has('userTel:mobile'))}">
                    <i class="fa fa-mobile icon-absolute-left"></i>
                    <!--<i class="fa fa-envelope-o icon-absolute-right"></i>-->
                    <input autocomplete="false" v-validate="'required|mobile'" type="text" class="form-control input-sm" placeholder="用户手机号"
                        name="userTel" v-model="formData.userTel">

                    <v-errorMsg :errorMsgAlert="{'isShow':errors.has('userTel'),'msg':[{'isShow':errors.has('userTel:required'),'msg':errors.first('userTel:required')},{'isShow':errors.has('userTel:mobile'),'msg':errors.first('userTel:mobile')}]}">
                    </v-errorMsg>
                </div>
                <!-- /.col -->
                <!--<label class="control-label col-lg-2"></label>-->
                <div class="form-group m-bottom-md col-lg-12" :class="{'has-error': (errors.has('userEmail:required')|errors.has('userEmail:email'))}">
                    <i class="fa fa-envelope-o icon-absolute-left"></i>
                    <input v-validate="'required|email'" type="text" class="form-control input-sm" placeholder="用户邮箱" name="userEmail" v-model="formData.userEmail">

                    <v-errorMsg :errorMsgAlert="{'isShow':errors.has('userEmail'),'msg':[{'isShow':errors.has('userEmail:required'),'msg':errors.first('userEmail:required')},{'isShow':errors.has('userEmail:email'),'msg':errors.first('userEmail:email')}]}">
                    </v-errorMsg>
                </div>

                <div class="form-group m-bottom-md col-lg-12" :class="{'has-error': (errors.has('userPwd:required')|errors.has('userPwd:regex'))}">
                    <i class="fa  fa-lock icon-absolute-left"></i>
                    <i class="fa fa-eye icon-absolute-right" v-chageInputTpye="'fa fa-eye-slash icon-absolute-right,fa fa-eye icon-absolute-right,password,text'"></i>
                    <input autocomplete="false" v-validate="'required|regex:'" type="password" class="form-control input-sm" placeholder="用户密码"
                        name="userPwd" v-model="formData.userPwd">

                    <v-errorMsg :errorMsgAlert="{'isShow':errors.has('userPwd'),'msg':[{'isShow':errors.has('userPwd:required'),'msg':errors.first('userPwd:required')},{'isShow':errors.has('userPwd:regex'),'msg':errors.first('userPwd:regex')}]}">
                    </v-errorMsg>
                </div>
                <div class="form-group m-bottom-md col-lg-12" :class="{'has-error': (errors.has('userConfirmPwd:confirmed'))}">
                    <i class="fa  fa-lock icon-absolute-left"></i>
                    <i class="fa fa-eye icon-absolute-right" v-chageInputTpye="'fa fa-eye-slash icon-absolute-right,fa fa-eye icon-absolute-right,password,text'"></i>
                    <input autocomplete="false" v-validate="'confirmed:userPwd'" type="password" class="form-control input-sm" placeholder="确认密码"
                        name="userConfirmPwd" v-model="formData.userConfirmPwd">

                    <v-errorMsg :errorMsgAlert="{'isShow':errors.has('userConfirmPwd'),'msg':[{'isShow':errors.has('userConfirmPwd:confirmed'),'msg':'不能和用户密码匹配'}]}">
                    </v-errorMsg>
                </div>
                <div class="form-group col-lg-12">
                    <div class="custom-checkbox">
                        <input autocomplete="false" type="checkbox" id="chkAccept">
                        <label for="chkAccept"></label>
                    </div>
                    我同意注册协议
                </div>

                <div class="m-top-md p-top-sm col-lg-12">
                    <a @click.nactive="signup" class="btn btn-success block">
                        <i class="fa fa-spinner fa-spin m-right-xs" v-show="loading"></i>
                        创建一个帐号</a>
                </div>

                <div class="m-top-md p-top-sm col-lg-12">
                    <div class="font-12 text-center m-bottom-xs">已拥有一个帐号?</div>
                    <a href="#/login" class="btn btn-default block">登录</a>
                </div>
            </form>
        </div>
        <!-- ./sign-in-inner -->
    </div>
    <!-- ./sign-in-wrapper -->
</template>
<script>
    import allAjax from '../api/request.js'
    import { mapGetters } from 'vuex'
    export default {
        data() {
            return {
                formData: {
                    userName: "",
                    userTel: "",
                    userEmail: "",
                    userPwd: "",
                    userConfirmPwd: ""
                },
            }
        },
        computed: mapGetters([
            // 请求的状态
            'loading'
        ]),
        methods: {
            signup() {
                var self=this;
                this.$validator.validateAll().then(() => {
                    allAjax.userData.signup.call(this, {'type':"admin",'formdata':this.formData}, function (response) {
                            console.log(response.data);
                        if (response.data.code === "200") {
                            self.$router.push('login');
                        }
                        else {
                        }
                    });
                }).catch(() => {
                    
                    
                });
            },
            // 键盘时间

        }
    }

</script>