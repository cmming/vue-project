<template>
    <div class="none">
        <div v-for="menuChild in menu">{{menuChild}}</div>
    <div>
</template>
<script>
    export default {
        data() {
            return {
                isOpen: true,
                menu: ''
            }
        },
        props: ['msg'],
        mounted() {
            this.menu = this.msg;
        },
        methods: {
            open: function() {
                this.isOpen = !this.isOpen
            }
        }
    }
</script>
<style lang="css">
    .none {
        display: none
    }
    
    .block {
        display: block
    }
</style>