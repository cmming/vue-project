import * as types from './types'

// 1.定义页面的数据 通过自定义 或者 asoist获取
const state = {
    count: 20
};

// 2.将定义的数据 制作出口
// 作用：主要是对于state中数据的一种过滤，属于一种加强属性
// getters

const getters = {
    count: (state) => {
        return state.count;
    },
    getOdd: (state) => {
        return state.count % 2 == 0 ? '偶数' : '奇数'
    }
};
// 3.页面的一些操作来处理计算页面的数据
// actions
const actions = {
    increment: ({
        commit
    }) => {
        commit(types.INCREMENT);
    },
    decrement: ({
        commit
    }) => {
        commit(types.DECREMENT);
    },
    clickOdd: ({
        commit,
        state
    }) => {
        if (state.count % 2 == 0) {
            commit(types.INCREMENT);
        }
    },
    clickAsync: ({
        commit
    }) => {
        new Promise((resolve) => {
            setTimeout(function() {
                commit(types.INCREMENT);
            }, 1000);
        })
    }
}

// 4.action 的具体操作
const mutations = {
    [types.INCREMENT](state) {
        state.count++;
    },
    [types.DECREMENT](state) {
        state.count--;
    }
};

export default {
    state,
    getters,
    actions,
    mutations
}