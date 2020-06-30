class Authorization
{

    /**
     * 初始化属性
     * @param requestUrl
     * @param method
     */
    constructor (requestUrl, method = 'get', redirect = '/login') {
        this.requestUrl = requestUrl;
        this.method = method;
        this.redirect = redirect;
        this.requestUrlAuthList = [
            "delete authorizations/logout",
            "patch authorizations/refresh",
            "get users/current/user",
            "post users/{user}",
            "patch users/{user}/set",
            "get notifications",
            "delete notifications/{notification}",
            "post topics",
            "patch topics/{topic}",
            "delete topics/{topic}",
            "post resources",
            "post topics/{topic}/reply",
            "delete topics/{topic}/reply/{reply}",
        ];  // 需要验证的请求url列表
    }

    /**
     * 检查路由是否需要进行验证
     * @returns {boolean}
     */
    checkAuth () {
        var url = this.method + ' ' +  this.requestUrl;
        if (this.requestUrlAuthList.indexOf(url) === -1) {
            return false;
        } else {
            return true;
        }
    }

    handle () {
        window.axios.defaults.headers.common['Accept-language'] = 'zh-CN';  // 要求服务器返回中文的提升信息
        if (this.checkAuth()) {
            var tokenInfo = window.ls.getItem('tokenInfo');
            if (! tokenInfo) {
                swal('Token已失效，重新获取！', '', 'warning').then((res) => {
                    if (res) {
                        location.href = this.redirect;  // 如果Token失效需要跳转的地址
                    }
                });
                return false;
            }
            // 每次发送请求的时候，带上需要验证的token值
            window.axios.defaults.headers.common['Authorization'] = tokenInfo['token_type'] + ' ' + tokenInfo['access_token'];
            return true;
        } else {
            return true;
        }
    }
}

export default Authorization;