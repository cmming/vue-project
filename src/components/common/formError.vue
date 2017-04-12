<template>
    <span>
        <span v-show="errorMsgAlert.isShow" class="err_msg">
            <!--不同类型的错误-->
            <span v-for="mesItem in errorMsgAlert.msg" v-show="mesItem.isShow">
                <i class="fa fa-exclamation-circle m-right-xs"></i>{{mesItem.msg}}</span>
        </span>
    </span>
</template>
<script>
    export default{
        // data(){
        //     return {
        //         // errorMsgAlert:{'isShow':['rangeLength'],'msg':['required','min','max']}
        //     }
        // },
        props:['errorMsgAlert'],
        //  watch:{
        //     errorMsgAlert:function(){
        //         console.log(this.errorMsgAlert);
        //     }
        // }
    }
</script>