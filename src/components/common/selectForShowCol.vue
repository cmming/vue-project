<template>
    <div>
        <div class="header" @click="clickShow">
            选择显示的列
            <i :class="{'fa headerIcon':true,'fa-sort-desc':!colsShow,'fa-sort-up':colsShow}"></i> 
        </div>
        <ul class="ulContainer" v-show="colsShow">
            <!--<optgroup label="显示的列">-->
            <li v-for="(itemHeader,k) in tableHeader" name="showCol" value="itemHeader.val"><input :checked="itemHeader.val" @click="changeState(k)" type="checkbox">{{itemHeader.name}}</li>
            <!--</optgroup>-->
        </ul>
    </div>
</template>
<script>
    export default{
        props:['tableHeader'],
        data(){
            return {
                headerData:[],
                colsShow:false,
            }
        },
        created(){
            this.headerData=this.tableHeader
        },
        methods:{
            clickShow(){
                this.colsShow=!this.colsShow;
            },
            changeState(i){
                this.tableHeader[i]["val"]=!(this.tableHeader[i]["val"])
                console.log(this.tableHeader);
            }
        }

    }
</script>
<style lang="css" scoped>
    template>div{
        position: relative;
    }
    .header{
        width:150px;
        padding:5px 10px;
        border:1px solid #dfdfdf;
        background-color: #ffffff;
        cursor: pointer
    }
    .ulContainer{
        position: absolute;
        width:150px;
        
        background-color: #ffffff;
        border:1px solid #dfdfdf;
        z-index: 99
    }
    .ulContainer li{
        padding:5px 10px;
        border-bottom:1px solid #dfdfdf;
    }
    .ulContainer li input{
        vertical-align: sub
    }
    .headerIcon{
        float: right;
    }
</style>