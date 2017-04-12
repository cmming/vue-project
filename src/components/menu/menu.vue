<template>
    <div>
        <div v-for="(menu,key) in menuData" v-toggleMenu="{className:'child'}">
            <div @click="open(key)">{{menu.name}}</div>
            <child-menu v-show="showMenuIndex===key" class="child" :msg="menu.childMenu"></child-menu>
        </div>
    <div>
</template>
<script>
    import childMenu from './childMenu.vue'
    export default {
        components: {
            childMenu: childMenu
        },
        data() {
            return {
                showMenuIndex: '',
                menuData: [{
                    name: '一级目',
                    childMenu: [
                        '二级目录1',
                        '二级目录1',
                        '二级目录1'
                    ]
                }, {
                    name: '一级目1',
                    childMenu: [
                        '二级目录2',
                        '二级目录2',
                        '二级目录2'
                    ]
                }],
                msgs: []
            }
        },
        methods: {
            open: function(key) {
                this.showMenuIndex = key;
            },
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