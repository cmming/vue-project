<template>
    <div>

        <button v-for='item in msg' type="button" class="btn btn-default" :name1="item.name1">{{item.name}}</button>
    </div>
    
</template>
<script>
    export default {
        props: ['msg'],

    }
</script>