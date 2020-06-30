var utils = {
    collectAll: function (collcation) {
        var errors = [];
        for (let key in collcation) {
            collcation[key].forEach(function (item, index) {
                errors.push(item);
            });
        }
        return errors;
    },
    getUser: async function () {
        var tokenInfo = ls.getItem('tokenInfo');
        if (! tokenInfo) {
            return null;
        } else {
            var user = ls.getItem('user');
            if (user) {
                return user;
            } else {
                // 根据token来获取当前用户信息
                window.axios.defaults.headers.common['Authorization'] = tokenInfo['token_type'] + ' ' + tokenInfo['access_token'];
                var res = await axios.get('api/v1/users/current/user');
                var user = res.data.data;
                ls.setItem('user', user);  // 把用户信息存储到localStorage
                return user;
            }
        }
    }
}

export default utils;