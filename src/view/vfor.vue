<template>
    <div>
        <div v-for="(item,key) in items" @click.self="doSomething(key)">
        item
        <div v-show="showKey==key">子元素</div>
        </div>

        <input type="text" v-model="inputVal">
    </div>
</template>
<script>
    export default{
        data(){
            return {
                inputVal:"",
                showKey:"",
                items:["btn1","btn2","btn3"]
            }
        },
        methods:{
            doSomething(i){
                this.showKey=i;
                console.log("click");
            }
        },
        watch:{
            inputVal:function(a){
                console.log(a);
            }
        }
    }
</script>
<style lang="css" scoped>
    div{
        margin:10px;
    }
</style>