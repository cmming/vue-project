// 核心文件，先引用vue和vuex 然后user(Vuex),把定义好的 modules 引入进来然后返回一个Vuex.store
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

import mutations from './modules/mutations'
import table from './modules/table'
import loading from './modules/loading.js'
import userInfo from './modules/userInfo.js'

// 导出多个模块的数据
export default new Vuex.Store({
    modules: {
        mutations,
        table,
        loading,
        userInfo
    },
});