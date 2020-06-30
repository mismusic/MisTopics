@extends('layouts.app')
@section('title', $title)
@section('content')
    <div class="container">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="card-text">
                        <div class="mb-4">
                            <ul class="nav nav-pills" role="tablist">
                                <li class="nav-item">
                                    <a href="#code" class="nav-link" data-toggle="pill">短信登录</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#pw" class="nav-link" data-toggle="pill">密码登录</a>
                                </li>
                            </ul>
                            <hr />
                        </div>
                        <div class="tab-content">
                            <div id="code" class="container tab-pane active">
                                <div class="form-group">
                                    <label for="phone">手机号</label>
                                    <input type="text" id="phone1" class="form-control" v-model="phone" key="phone" />
                                    <span class="help-block invalid-feedback font-weight-bold" v-for="error of errors1">@{{ error }}</span>
                                </div>
                                <div class="form-group">
                                    <label for="verification_code">短信验证码</label>
                                    <div class="row">
                                        <div class="col-4">
                                            <input type="text" id="verification_code" class="form-control" v-model="verification_code" key="verification_code" />
                                        </div>
                                        <div class="col pl-1">
                                            <button type="button" class="btn btn-primary" @click="sendSmsVerify" ref="sendSms">发送</button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary" @click="smsLogin">登录 / 注册</button>
                                <span class="ml-4"><a href="#">找回密码？</a></span>
                                <div>
                                    <a href="#" class="card-link float-right mt-4">微信登录</a>
                                </div>
                            </div>
                            <div id="pw" class="container tab-pane fade">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">手机号</span>
                                    </div>
                                    <input type="text" id="phone2" class="form-control" v-model="phone" key="phone" />
                                    <span class="help-block invalid-feedback font-weight-bold" v-for="error of errors2.phone">@{{ error }}</span>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">密码</span>
                                    </div>
                                    <input type="password" id="password" class="form-control" v-model="password" key="password" />
                                    <span class="help-block invalid-feedback font-weight-bold" v-for="error of errors2.password">@{{ error }}</span>
                                </div>
                                <button type="button" class="btn btn-primary btn-block" @click="passwordLogin">登录 / 注册</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('javascript')
    <script>
        var vm = new Vue({

            el: '#app',
            data: {
                user: null,
                phone: '',
                'verification_key': '',
                'verification_code': '',
                password: '',
                errors1: [],
                errors2: [],
            },
            methods: {
                // 发送短信验证码
                sendSmsVerify () {
                    var auth = new Authorization('send-sms-verify', 'post');
                    if (auth.handle()) {  // 检查该请求接口是否需要进行token验证，如果本地token存在或者不需要验证就发送请求
                        axios.post('{{ get_api_prefix("/") }}send-sms-verify', {
                            'phone': this.phone,
                            'type': 1,
                        }).then((res) => {
                            $('#phone').removeClass('is-invalid');
                            this.errors1 = [];
                            var data = res.data.data;
                            this.verification_key = data['verification_key'];  // 把返回数据中的verification_key存储到vm对象里面
                            var sendSms = this.$refs.sendSms;
                            var startTime = 60;
                            $(sendSms).attr('disabled', true);
                            var interval = window.setInterval(function () {
                                if (startTime === 0) {
                                    $(sendSms).attr('disabled', false).text('发送');
                                    clearInterval(interval);
                                    return;
                                }
                                $(sendSms).text(startTime -- + '秒后可重新获取');
                            }, 1000);
                        }).catch((error) => {
                            console.log(error.response);
                            if (error.response.status === 422) {
                                this.errors1 = utils.collectAll(error.response.data.data);
                                $('#phone1').addClass('is-invalid');
                                console.log(this.errors1);
                                return;
                            }
                            // 弹出错误提示
                            swal(error.response.data.msg, '', 'error');
                        });
                    }
                },

                // 短信登录
                smsLogin () {
                    var auth = new Authorization('authorizations/token', 'post');
                    if (auth.handle()) {
                        axios.post('{{ get_api_prefix("/") }}authorizations/token', {
                            'verification_key': this.verification_key,
                            'verification_code': this.verification_code,
                        }).then((res) => {
                            var tokenInfo = res.data.data;
                            console.log(tokenInfo);
                            // 把token信息存储起来
                            ls.setItem('tokenInfo', tokenInfo);
                            swal('登录成功', '', 'success').then((res) => {
                                location.reload();
                            });
                        }).catch((error) => {
                            console.log(error.response);
                            swal(error.response.data.msg, '', 'error');
                        })
                    }
                },

                // 密码登录
                passwordLogin () {
                    var auth = new Authorization('authorizations/token', 'post');
                    if (auth.handle()) {
                        axios.post('{{ get_api_prefix("/") }}authorizations/token', {
                            'phone': this.phone,
                            'password': this.password,
                        }).then((res) => {
                            $('#phone2').removeClass('is-invalid');
                            $('#password').removeClass('is-invalid');
                            this.errors2 = [];
                            var tokenInfo = res.data.data;
                            console.log(tokenInfo);
                            // 把token信息存储起来
                            ls.setItem('tokenInfo', tokenInfo);
                            swal('登录成功', '', 'success').then((res) => {
                                location.reload();
                            });
                        }).catch((error) => {
                            console.log(error.response);
                            if (error.response.status === 422) {
                                this.errors2 = error.response.data.data;
                                if (this.errors2.phone) {
                                    $('#phone2').addClass('is-invalid');
                                }
                                if (this.errors2.password) {
                                    $('#password').addClass('is-invalid');
                                }
                                console.log(this.errors2);
                                return;
                            }
                            swal(error.response.data.msg, '', 'error');
                        })
                    }
                },

            },
            created: function () {
                utils.getUser().then((res) => {
                    console.log(res);
                    this.user = res;
                });
            },

        })
    </script>
@stop