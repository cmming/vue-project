<template>
    <div>
        父组件
        <chiltpl :msg="menuParams">
           
        </chiltpl>
    </div>
</template>
<script>
    import chiltpl from './child.vue'
    export default {
        data() {
            return {
                menuParams: [{
                    'name1 ': 'ww',
                    'name': 'cm'
                }, {
                    'name1 ': 'cc ',
                    'name': 'cm'
                }, {
                    'name1 ': 'kk',
                    'name': 'cm'
                }]
            }
        },

        components: {
            chiltpl: chiltpl
        }
    }
</script>