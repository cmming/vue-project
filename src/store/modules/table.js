// 获取table的数据

const state = {
    tableData: [{
        "id": "12",
        "name": "陈明",
        "birthday": "19930427",
        "sex": "男"
    }, {
        "id": "12",
        "name": "陈明",
        "birthday": "19930427",
        "sex": "男"
    }, {
        "id": "12",
        "name": "陈明",
        "birthday": "19930427",
        "sex": "男"
    }, {
        "id": "12",
        "name": "陈明",
        "birthday": "19930427",
        "sex": "男"
    }]
};
// 定义公用的getters处理 ,例如正常组件里想 computed 一个状态但在vuex里面不知道怎么处理，就可以在这里处理。
const getters = {
    tableData: (state) => {
        return state.tableData;
    }
};

// actions
const actions = {

}

const mutations = {

};

export default {
    state,
    getters,
    mutations,
    actions
}