// 将所有的请求写在这里
// 接口的二级目录
const allAjax = {
    userData: {
        
        /**
         * 登录接口
         * 
         * @param {json} data 发送的数据
         * @param {Function} fn 接口的成功回调函数
         */
        login(data, fn) {
            this.$http.post('/addAdminData.php', data).then(fn);
        },
        /**
         * 用户注册
         * 
         * @param {json} data 用户注册信息 
         * @param {any} fn  接口成功回调
         */
        signup(data,fn){
            this.$http.post('/addAdminData.php',data).then(fn);
        },
        signout(data,fn){
            this.$http.post('/addAdminData.php',data).then(fn);
        }
    },

    tableAjax: {
        /**
         * 表格初始化数据
         * @param {any} fn  请求成功的回调
         */
        getData(fn) {
            this.$http.get('/dataTable.php').then(fn)
        },
        /**
         * 删除数据
         * @param {any} index  要删除项目的id
         * @param {any} fn    成功请求的回调
         */
        del(index, fn,errorFn) {
            this.$http.post('/dataTable.php', {
                del_id: index,
                type: 'tableDate',
                act: 'del'
            }).then(fn)
        },
        /**
         * getSignl:获取一条数据
         * @param {any} index 
         * @param {any} fn 成功请求的回调
         */
        getSignl(index, fn) {
            this.$http.get('/dataTable.php').then(fn)
        }
    },

    // 添加数据的api 集合 将类型放在这里
    addData:{
        /**
         * 添加设备
         * 
         * @param {data} 表单数据 
         * @param {fn} 成功回调 
         */
        addTerm(data,fn){
            this.$http.post('/addData.php',{
                'type':"addTerm",'formdata':data
            }).then(fn)
        }
    },
    searchData:{
        /**
         * 订单查询
         * 
         * @param {any} data 
         * @param {any} fn 
         */
        payorder(data,fn){
            this.$http.post('/searchData.php',{
                'type':"payorder",'search':data
            }).then(fn)
        }
    }
};

export default allAjax;