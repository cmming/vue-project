<template>
    <div>
        <!--data:显示的数据-->
        <v-breadcrumb :breadcrumbData="toBreadcrumb"></v-breadcrumb>
        <el-tree :data="data2" :props="defaultProps" show-checkbox node-key="id" default-expand-all :expand-on-click-node="false"
            :render-content="renderContent">
        </el-tree>

    </div>
</template>

<script>
  let id = 1000;

  export default {
    data() {
      return {
         // 初始化导航栏数据
        toBreadcrumb: [
            { path: 'main', name: '主页' },
            { path: 'elementTree', name: 'elementTree构建' },
        ],
        data2: [{
          id: 1,
          label: '一级 1',
          children: [{
            id: 4,
            label: '二级 1-1',
            children: [{
              id: 9,
              label: '三级 1-1-1'
            }, {
              id: 10,
              label: '三级 1-1-2'
            }]
          }]
        }, {
          id: 2,
          label: '一级 2',
          children: [{
            id: 5,
            label: '二级 2-1'
          }, {
            id: 6,
            label: '二级 2-2'
          }]
        }, {
          id: 3,
          label: '一级 3',
          children: [{
            id: 7,
            label: '二级 3-1'
          }, {
            id: 8,
            label: '二级 3-2'
          }]
        }],
        defaultProps: {
          children: 'children',
          label: 'label'
        }
      }
    },

    methods: {
      append(store, data) {

          this.$prompt('网址', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          inputPattern: /[\w!#$%&'*+/=?^_`{|}~-]+(?:\.[\w!#$%&'*+/=?^_`{|}~-]+)*@(?:[\w](?:[\w-]*[\w])?\.)+[\w](?:[\w-]*[\w])?/,
          inputErrorMessage: '邮箱格式不正确'
        }).then(({ value }) => {
          this.$message({
            type: 'success',
            message: '你的邮箱是: ' + value
          });
          store.append({ id: id++, label: value, children: [] }, data);
        }).catch(() => {
          this.$message({
            type: 'info',
            message: '取消输入'
          });       
        });
        
      },

      remove(store, data) {
        store.remove(data);
      },

      open3() {
        this.$prompt('请输入邮箱', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          inputPattern: /[\w!#$%&'*+/=?^_`{|}~-]+(?:\.[\w!#$%&'*+/=?^_`{|}~-]+)*@(?:[\w](?:[\w-]*[\w])?\.)+[\w](?:[\w-]*[\w])?/,
          inputErrorMessage: '邮箱格式不正确'
        }).then(({ value }) => {
          this.$message({
            type: 'success',
            message: '你的邮箱是: ' + value
          });
        }).catch(() => {
          this.$message({
            type: 'info',
            message: '取消输入'
          });       
        });
      },
    //   自定义标签
      renderContent(h, { node, data, store }) {
        return (
         <span>
            <span>
              <span>{node.label}</span>
            </span>
            <span style="float: right; margin-right: 20px">
              <el-button size="mini" on-click={ () => this.append(store, data) }>添加</el-button>
              <el-button size="mini" on-click={ () => this.append(store, data) }>修改</el-button>
            
              <el-button size="mini" on-click={ () => this.remove(store, data) }>删除</el-button>
            </span>
          </span>);
      }
    }
  };
</script>