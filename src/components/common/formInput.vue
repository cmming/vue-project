<!--封装一个input输入框
    label:表示前面框
-->

<template>
    <div class="form-group">
        <label class="control-label col-lg-2">{{label}}</label>
        <div :class="{'col-lg-6': true, 'has-error': errors.has('email') }">
            <i class="fa fa-envelope-o icon-absolute-left"></i>
            <i class="fa fa-envelope-o icon-absolute-right"></i>
            <input type="nonvoid" class="form-control input-sm" placeholder="非空的 email" name="email" v-model="email" v-validate="'required|email'">
            <!--错误提示信息-->
            <v-errorMsg :errorMsgAlert="{'isShow':errors.has('email'),'msg':[{'isShow':errors.has('email:required'),'msg':errors.first('email:required')},{'isShow':errors.has('email:email'),'msg':errors.first('email:email')}]}">
            </v-errorMsg>
        </div>
    </div>
</template>
<script>
    export default{
        data(){
            return {

            }
        },
        props:["label"],
    }
</script>