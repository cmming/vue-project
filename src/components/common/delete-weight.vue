/* 
*弹出面板 *params {object} 
*params obj.confirmDel {bool} 控制显示 
*params obj.index {num/string} 删除时发送请求的参数 
*params obj.alertMsg{num/string} 删除数据时候的提示
*params obj.warnning {num/string} 弹框的警告 
*通过slot 标签进行预留标签位置 方便自定义
*/

<template>
    <div class="mask">
        <div class="custom-popup delete-widget-popup delete-confirmation-popup">
            <div class="popup-header text-center">
                <span class="fa-stack fa-4x">
                        <i class="fa fa-circle fa-stack-2x"></i>
                        <i class="fa fa-lock fa-stack-1x fa-inverse"></i>
                    </span>
            </div>
            <div class="popup-body text-center">
                <!--<h5>{{alertMsg}}</h5>-->
                <slot name="alertMsg"></slot>
                <strong class="block m-top-xs"><i class="fa fa-exclamation-circle m-right-xs text-danger"></i> 
                   <slot name="warnning"></slot> 
                   <!--{{warnning}}-->
                </strong>

                <div class="text-center m-top-lg">
                    <a class="btn btn-success m-right-sm remove-widget-btn" @click="confirmState">确定</a>
                    <a class="btn btn-default deleteWidgetConfirm_close" @click="cancelState">取消</a>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: ["confirmDel"],
        methods: {
            confirmState() {
                this.confirmDel.confirmDel = false;
            },
            cancelState() {
                this.confirmDel.confirmDel = false;
                this.confirmDel.index = '';
            }
        }
    }

</script>