import {
    user as UserApi
} from '../../config/request.js';

export default{
    name: 'login',
    data() {
        return {
            userName: "",
            userPwd: ""
        }
    },
    methods: {
        login() {
            var resData = {
                userName: this.userName,
                userPwd: this.userPwd
            };

            this.$http.post('http://127.0.0.1/vue/day07/elemnt-ui/api/sigin.php?userPwd=' + this.userPwd + '&userName=' + this.userName).then(response => {
                if (response.data.code === "200") {
                    sessionStorage.accessToken = true;
                    this.$router.push('manager');
                }
            }, response => {
                console.log(response);
            });
        },
        // 键盘时间

    }
}