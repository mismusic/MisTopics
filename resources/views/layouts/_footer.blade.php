<script>
    // 注销登录
    $(document).ready(function () {
        // 注销登录
        $('#app').on('click', '#logout', function () {
            var auth = new Authorization('authorizations/logout', 'delete');
            if (auth.handle()) {
                swal({
                    title: '你确定要注销登录？',
                    buttons: {
                        confirm: {
                            text: '确定',
                            value: true,
                            closeModal: false,
                        },
                        cancel: '取消',
                    },
                }).then((res) => {
                    if (! res) return null;
                    return axios({
                        method: 'delete',
                        url: '{{ get_api_prefix("/") }}authorizations/logout',
                    }).then((res) => {
                        ls.removeItem('tokenInfo');
                        ls.removeItem('user');
                        swal('注销登录成功', '', 'success').then((res) => {
                            location.href = '/login';
                        });
                    }).catch((error) => {
                        console.log(error.response);
                        swal(error.response.data.msg, '', 'error');
                    });
                });
            }
        });
    });
</script>
<div class="footer">
    <p>
        MisTopics
    </p>
</div>