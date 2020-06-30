@extends('layouts.app')
@section('title', $title)
@section('content')
    <div class="col-md-6 offset-md-3">
        <div class="card">
            @if(request('type') === 'phone')
                <h5 class="card-header">{{ $title . '手机号'}}</h5>
                <div class="card-body">
                    <div class="form-group">
                        @if($user->phone)
                            <label for="phone">手机号</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="text" ref="phone" id="phone" class="form-control" value="{{ $user->phone }}" placeholder="手机号" disabled>
                                </div>
                                <div class="col-6 pl-0">
                                    <button class="btn btn-primary" @click="clickSendSms1(1)" ref="sendSms">发送短信</button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="newPhone">新手机号</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="text" v-model="newPhone" id="newPhone" class="form-control" placeholder="新手机号">
                                <span class="help-block invalid-feedback font-weight-bold" v-for="error of errors">@{{ error }}</span>
                            </div>
                            @if(! $user->phone)
                                <div class="col-6 pl-0">
                                    <button class="btn btn-primary" @click="clickSendSms1(2)" ref="sendSms">发送短信</button>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="verification_code">验证码</label>
                        <div class="row">
                            <div class="col-md-3 col-4">
                                <input type="text" v-model="verification_code" id="verification_code" class="form-control" placeholder="验证码">
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary" @click="clickSetPhone">提交</button>
                </div>
            @elseif(request('type') === 'password')
                <h5 class="card-header">{{ $title . '密码'}}</h5>
                <div class="card-body">
                    <div class="form-group">
                        <label for="phone">手机号</label>
                        <input type="text" ref="phone" id="phone" class="form-control" value="{{ $user->phone }}" placeholder="手机号" disabled>
                        <span class="help-block invalid-feedback font-weight-bold" v-for="error of errors">@{{ error }}</span>
                    </div>
                    <div class="form-group">
                        <label for="verification_code">验证码</label>
                        <div class="row">
                            <div class="col-md-3 col-4">
                                <input type="text" v-model="verification_code" id="verification_code" class="form-control" placeholder="验证码">
                            </div>
                            <div class="col-md-9 col-8 pl-0">
                                <button class="btn btn-primary" @click="clickSendSms2" ref="sendSms" {{ $user->phone ? '' : 'disabled' }}>发送短信</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">密码</label>
                        <input type="password" v-model="password" id="password" class="form-control" placeholder="密码">
                    </div>
                    <button class="btn btn-primary" @click="clickSetPassword">提交</button>
                </div>
            @elseif(request('type') === 'email')
                <h5 class="card-header">{{ $title . '邮箱'}}</h5>
                <div class="card-body">
                    <div class="form-group">
                        <label for="email">邮箱</label>
                        <input type="email" v-model="email" id="email" class="form-control" placeholder="邮箱">
                    </div>
                    <button class="btn btn-primary" @click="clickSetEmail">提交</button>
                </div>
            @endif
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
                newPhone: '',
                password: '',
                email: '',
                verification_key: '',
                verification_code: '',
                errors: [],
            },
            methods: {
                clickSendSms1 ($type) {
                    var auth = new Authorization('send-sms-verify', 'post');
                    if (auth.handle()) {
                        if ($type === 1) {
                            this.phone = this.$refs.phone.value;
                        } else if ($type === 2) {
                            this.phone = this.newPhone;
                        }
                        axios.post('/{{ get_api_prefix("/") }}send-sms-verify', {
                            'phone': this.phone,
                            'type': 1,
                        }).then((res) => {
                            if ($type === 2) {
                                $('#newPhone').removeClass('is-invalid');
                                this.newPhone = '';
                            }
                            this.errors = [];
                            var data = res.data.data;
                            this.verification_key = data['verification_key'];  // 把返回数据中的verification_key存储到vm对象里面
                            var sendSms = this.$refs.sendSms;
                            var startTime = 60;
                            $(sendSms).attr('disabled', true);
                            var interval = window.setInterval(function () {
                                if (startTime === 0) {
                                    $(sendSms).attr('disabled', false).text('发送短信');
                                    clearInterval(interval);
                                    return;
                                }
                                $(sendSms).text(startTime -- + '秒后可重新获取');
                            }, 1000);
                        }).catch((error) => {
                            console.log(error.response);
                            if (error.response.status === 422) {
                                this.errors = utils.collectAll(error.response.data.data);
                                if ($type === 2) {
                                    $('#newPhone').addClass('is-invalid');
                                }
                                return;
                            }
                            // 弹出错误提示
                            swal(error.response.data.msg, '', 'error');
                        });
                    }
                },
                clickSetPhone () {
                    var data = {
                        verification_key: this.verification_key,
                        verification_code: this.verification_code,
                        type: 'phone',
                    };
                    if (this.newPhone) {
                        data.phone = this.newPhone;
                    }
                    var auth = new Authorization('users/{user}/set', 'patch');
                    if (auth.handle()) {
                        axios.patch('/{{ get_api_prefix("/") }}users/{{ $user->id }}/set', data).then((res) => {
                            var data = res.data.data;
                            swal('设置用户手机号成功', '', 'success');
                        }).catch((error) => {
                            console.log(error.response);
                            swal(error.response.data.msg, '', 'error');
                        })
                    }

                },
                clickSendSms2 () {
                    var auth = new Authorization('send-sms-verify', 'post');
                    if (auth.handle()) {
                        this.phone = this.$refs.phone.value;
                        axios.post('/{{ get_api_prefix("/") }}send-sms-verify', {
                            'phone': this.phone,
                            'type': 2,
                        }).then((res) => {
                            this.errors = [];
                            var data = res.data.data;
                            this.verification_key = data['verification_key'];  // 把返回数据中的verification_key存储到vm对象里面
                            var sendSms = this.$refs.sendSms;
                            var startTime = 60;
                            $(sendSms).attr('disabled', true);
                            var interval = window.setInterval(function () {
                                if (startTime === 0) {
                                    $(sendSms).attr('disabled', false).text('发送短信');
                                    clearInterval(interval);
                                    return;
                                }
                                $(sendSms).text(startTime -- + '秒后可重新获取');
                            }, 1000);
                        }).catch((error) => {
                            console.log(error.response);
                            if (error.response.status === 422) {
                                this.errors = utils.collectAll(error.response.data.data);
                                $('#phone').addClass('is-invalid');
                                return;
                            }
                            // 弹出错误提示
                            swal(error.response.data.msg, '', 'error');
                        });;
                    }
                },
                clickSetPassword () {
                    var data = {
                        verification_key: this.verification_key,
                        verification_code: this.verification_code,
                        type: 'password',
                        password: this.password,
                    };
                    var auth = new Authorization('users/{user}/set', 'patch');
                    if (auth.handle()) {
                        axios.patch('/{{ get_api_prefix("/") }}users/{{ $user->id }}/set', data).then((res) => {
                            var data = res.data.data;
                            ls.removeItem('tokenInfo');
                            ls.removeItem('user');
                            swal('设置用户密码成功', '', 'success').then((res) => {
                                location.href = '{{ route("users.login") }}';
                            });
                        }).catch((error) => {
                            console.log(error.response);
                            swal(error.response.data.msg, '', 'error');
                        })
                    }
                }

            },
            created () {
                utils.getUser().then((res) => {
                    this.user = res;
                })
            },
        })
    </script>
@stop