// 所有请求的状态管理

import * as types from './types'

// 1.定义页面的数据 通过自定义 或者 asoist获取
const state = {
    loading: false
};

// 2.将定义的数据 制作出口
// getters

const getters = {
    loading: (state) => {
        return state.loading;
    }
};
// 3.页面的一些操作来处理计算页面的数据
// actions
const actions = {
    increment: ({
        commit
    }) => {
        commit(types.SHOWLOAGING);
    },
    decrement: ({
        commit
    }) => {
        commit(types.HIDELOADING);
    }
}

// 4.action 的具体操作
const mutations = {
    [types.SHOWLOAGING](state) {
        state.loading=true;
    },
    [types.SHOWLOAGING](state) {
        state.loading=false;
    }
};

export default {
    state,
    getters,
    actions,
    mutations
}