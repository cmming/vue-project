<template>
    <div>
        <div id="hook-arguments-example"
             v-demo="2"></div>
        <div v-drag
             :style="{width:'100px', height:'100px', background:'aqua', position:'absolute', right:0, top:0}"> v-demo1v-demo1v-demo1v-demo1</div>

             <!--<h1>{{isMobile}}</h1>-->
    </div>
</template>
<script>

export default {
    
    mounted() {
        console.log(this.browser.versions.mobile);
    }
}

</script>   