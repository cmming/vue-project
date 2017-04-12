import Vue from 'vue'

Vue.directive('toggleMenu', function(el, data) {
    console.log(el, data.value.className);
});

// 拖动指令
Vue.directive('chageClass',
  function (el, binding) {
      el.onclick=function(){
        var toggleClassArray=binding.value.split(',');
        console.log(el.className);
        el.className=el.className==toggleClassArray[0]?toggleClassArray[1]:toggleClassArray[0];
      }
  });
  // 用来显示密码输入框的状态
Vue.directive('chageInputTpye',
  function (el, binding) {
      el.onclick=function(){
        var toggleClassArray=binding.value.split(',');
        var input=el.nextElementSibling;
        el.className=el.className==toggleClassArray[0]?toggleClassArray[1]:toggleClassArray[0];
        input.type=input.type==toggleClassArray[2]?toggleClassArray[3]:toggleClassArray[2];
      }
  });


// Vue.directive('chageClass',{
//   　bind: function(el, binding){
// 　　　　console.log('bind:',binding.value);
// 　　},
// 　　inserted: function(el, binding){
// 　　　　console.log('insert:',binding.value);
// 　　},
// 　　update: function(el, binding, vnode, oldVnode){
// 　　　　el.focus();
// 　　　　console.log(el.dataset.name);//这里的数据是可以动态绑定的
// 　　　　console.table({
// 　　　　　　name:binding.name,
// 　　　　　　value:binding.value,
// 　　　　　　oldValue:binding.oldValue,
// 　　　　　　expression:binding.expression,
// 　　　　　　arg:binding.arg,
// 　　　　　　modifiers:binding.modifiers,
// 　　　　　　vnode:vnode,
// 　　　　　　oldVnode:oldVnode
// 　　　　});
// 　　},
// 　　componentUpdated: function(el, binding){
// 　　　　console.log('componentUpdated:',binding.name);
// 　　}
// });