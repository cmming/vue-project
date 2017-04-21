import Vue from 'vue'

import { Loading,Select,Option ,DatePicker,Upload,Button,Autocomplete,Scrollbar,Form,FormItem,Input,Col,Switch,TimePicker,CheckboxGroup,Checkbox,RadioGroup,Radio,Tree,MessageBox,Message } from 'element-ui';

Vue.use(Loading,DatePicker,MessageBox,Message);
Vue.component(Select.name, Select)
Vue.component(Option.name, Option)
Vue.component(DatePicker.name, DatePicker)
Vue.component(Upload.name, Upload)
Vue.component(Button.name, Button)
Vue.component(Autocomplete.name, Autocomplete)
Vue.component(Scrollbar.name, Scrollbar)
Vue.component(Form.name, Form)
Vue.component(FormItem.name, FormItem)
Vue.component(Input.name, Input)
Vue.component(Col.name, Col)
Vue.component(Switch.name, Switch)
Vue.component(TimePicker.name, TimePicker)
Vue.component(CheckboxGroup.name, CheckboxGroup)
Vue.component(Checkbox.name, Checkbox)
Vue.component(RadioGroup.name, RadioGroup)
Vue.component(Radio.name, Radio)
Vue.component(Tree.name, Tree)

Vue.prototype.$loading = Loading.service
Vue.prototype.$msgbox = MessageBox
Vue.prototype.$alert = MessageBox.alert
Vue.prototype.$confirm = MessageBox.confirm
Vue.prototype.$prompt = MessageBox.prompt
Vue.prototype.$notify = Notification
Vue.prototype.$message = Message


// 路由跳转
import router from './routes'


// 表单验证自定义的指令
import './validate/index.js'

// 请求数据的处理
import './api/index.js';

import MyPlugin from '../src/assets/js/base.js'

Vue.use(MyPlugin)

// 数据状态集中处理
import store from './store'

import directive from './directive/open.js'

import directs from './directive/directive-demo.js'

// 引入自定义的过滤器
import filters from './filter'

// 自己定义的全局样式组件
import deleteModel from './components/common/delete-weight.vue'
import breadcrumb from './components/common/breadcrumb.vue'
import errorMsg from './components/common/formError.vue'
import page from './components/common/page.vue'
import selectForShowCol from './components/common/selectForShowCol.vue'


Vue.component('v-deleteModel', deleteModel)
Vue.component('v-breadcrumb', breadcrumb)
Vue.component('v-errorMsg', errorMsg)
Vue.component('v-page', page)
Vue.component('v-selectForShowCol', selectForShowCol)

// import  './element-ui.js'

import '../node_modules/element-ui/lib/theme-default/index.css'
import '../node_modules/bootstrap/dist/css/bootstrap.min.css'
import './assets/css/animate.min.css'
import './assets/css/simplify.min.css'
import './assets/css/font-awesome.min.css'
import './assets/css/cm.css'


import App from './App.vue'

var cmVue= new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')