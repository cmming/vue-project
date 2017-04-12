
import * as types from './types'

// 定义数据
// 获取用户信息
const state = {
    userInfo: [{
        "userName": ''
    }]
};
// 定义公用的getters处理 ,例如正常组件里想 computed 一个状态但在vuex里面不知道怎么处理，就可以在这里处理。
const getters = {
    userInfo: (state) => {
        return state.userInfo;
    }
};

// 3.页面的一些操作来处理计算页面的数据 action 怎么接受参数还不知 这是关键

const actions = {
    UPDATEUSERINFO: ({
        commit
    },updataData) => {
        commit(types.UPDATEUSERINFO,updataData);
    }
}

// 4.action 的具体操作
const mutations = {
    [types.UPDATEUSERINFO](state,data) {
        state.userInfo.userName=data.userName;
    }
};

export default {
    state,
    getters,
    mutations,
    actions
}