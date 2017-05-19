<template>
	<div class="sign-in-wrapper">
		<div class="sign-in-inner">
			<div class="login-brand text-center">
				<!--<i class="fa fa-database m-right-xs" v-show="false"></i> 蓝光vr <strong class="text-skin">管理后台</strong>-->
			</div>

			<form>
				<div class="form-group col-lg-12 m-bottom-md">
					<i class="fa fa-user icon-absolute-left"></i>
					<input autocomplete="off" type="text" class="form-control" v-model="formData.userName" placeholder="用户名"
						v-validate="'required'" name="userName">
					<v-errorMsg 
					:errorMsgAlert="{'isShow':errors.has('userName'),'msg':[{'isShow':errors.has('userName:required'),'msg':errors.first('userName:required')}]}">
					</v-errorMsg>
				</div>
				<div class="form-group col-lg-12">
					<i class="fa  fa-lock icon-absolute-left"></i>
					<i class="fa fa-eye icon-absolute-right" v-chageInputTpye="'fa fa-eye-slash icon-absolute-right,fa fa-eye icon-absolute-right,password,text'"></i>
					<input autocomplete="off" type="password" @keyup.enter="login" class="form-control" v-model="formData.userPwd" placeholder="密码">
				</div>

				<div class="form-group col-lg-12">
					<div class="custom-checkbox">
						<input type="checkbox" id="chkRemember">
						<label for="chkRemember"></label>
					</div>
					记住密码
				</div>

				<div class="m-top-md p-top-sm col-lg-12">
					<a @click="login" class="btn btn-success block">
						<i class="fa fa-spinner fa-spin m-right-xs" v-show="loading"></i>登录</a>
				</div>

				<div class="m-top-md p-top-sm text-center">
					<span v-show="requestError" class="err_msg">
						{{errorMsg}}
					</span>
				</div>

				<div class="m-top-md p-top-sm col-lg-12">
					<div class="font-12 text-center m-bottom-xs" v-show="false">
						<a href="#" class="font-12">忘记密码</a>
					</div>
					<div class="font-12 text-center m-bottom-xs" v-show="false">没有帐号</div>
					<a href="#/signup" class="btn btn-default block">创建帐号</a>
				</div>
			</form>
		</div>
		<!-- ./sign-in-inner -->
	</div>
</template>
<script>
	import allAjax from '../api/request.js'
	export default {
		// 到时候还可以依据不同的情况返回不同的数据
		computed: {
			loading:function(){
				// 模块名称
				return this.$store.state.loading.loading
			}
		},
		data() {
			return {
				formData: {
					userName: "",
					userPwd: "",
				},
				loadding: false,
				requestError: false,
				errorMsg: ""
			}
		},
		methods: {
			login() {
				this.$validator.validateAll().then(() => {
                    // eslint-disable-next-line
					var resData = { 'type': "login_confirm", 'formdata': this.formData };
					this.loadding = true;
					var self = this;
					allAjax.userData.login.call(this, resData, function (response) {
						if (response.data.code === "200") {
							self.loadding = false;
							sessionStorage.accessToken = true;
							sessionStorage.userName = response.data.data.user_uname;
							// 在vue层面改变数据状态 既然参数传不过去，就直接改变他
							// self.updataData={"userName":sessionStorage.userName};
							self.$store.dispatch('UPDATEUSERINFO',{"userName":sessionStorage.userName});
							self.$store.dispatch('UPDATEUSERINFO');
							// 直接改变他 这样修改并不会修改 vuex 内部的值 只是在内部被改变了 其它地方没有生效
							// self.$store.state.userInfo.userName=response.data.data.user_uname;
							self.$router.push('main');
						}
						else {
							self.requestError = true;
							self.errorMsg = response.data.msg;
							self.loadding = false;
						}
					});
                }).catch(() => {
                    // eslint-disable-next-line
                    // alert('未通过');
					this.$message({
						showClose:true,
						message:'按照提示输入内容才能登陆',
						type:'warning'
					});
                });
				
			},
			// 键盘时间
		},

	}

</script>