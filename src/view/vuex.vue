<template>
  <div>
    <h3>vuex 数据管理</h3>
    <code>数据请求流程 1.action(mapActions)->2.mutation（处理过程）->3.getter(获取请求的参数)</code>
    <br>
    <input type="button" value="增加" @click="increment">
    <input type="button" value="减少" @click="decrement">
    <input type="button" value="偶数才能点击+" @click="clickOdd">
    <input type="button" value="点击异步" @click="clickAsync">

    <div>
      现在数字为: {{count}}, 它现在是 {{getOdd}}
    </div>
  </div>
</template>

<script>
  import {mapGetters, mapActions} from 'vuex'

  export default{
    //   获取数据 相当于将data放在 vuex中去初始化
    computed:mapGetters([
      'count',
      'getOdd'
    ]),
    methods:mapActions([
      'increment',
      'decrement',
      'clickOdd',
      'clickAsync'
    ])
  }
</script>

<style>

</style>
